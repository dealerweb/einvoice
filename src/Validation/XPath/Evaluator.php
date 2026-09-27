<?php

declare(strict_types=1);

namespace Dealerweb\EInvoice\Validation\XPath;

use Dealerweb\EInvoice\Xml;
use DOMAttr;
use DOMDocument;
use DOMElement;
use DOMNode;
use DOMProcessingInstruction;
use Generator;

/**
 * Executes Saxon's compiled expression trees (as the rule sets hold them) the way Saxon 12 executes them, on documents
 * without schema types: element and attribute values are xs:untypedAtomic, decimals stay exact.
 *
 * Saxon reads sequences item by item and stops as early as it can: exists() reads one item, a general comparison
 * stops at the first match, "A and B" does not evaluate B when A is false. An error therefore depends on what is read.
 * The evaluator computes a sequence at once but stops at the first failing item and keeps the error as a Failure at
 * the end of the list; a reader that gets that far raises it, one that stops earlier does not. Errors Saxon raises
 * before any item is read (a cardinality check reads two items at once, a sort reads all) are thrown directly.
 * Each node type follows its Saxon evaluator: which operand first, what an empty operand does, which errors are
 * swallowed ("A or B" is true when B is true even if A failed - Saxon 12 applies the XPath 4.0 rule).
 *
 * Variables live in slots of a frame (Saxon's slot numbers): let, for, some and every bind them, a function call gets
 * a new frame with its arguments in slots 0 ... Lazy variables are computed when first read.
 *
 * @internal
 */
final class Evaluator
{
    private const KIND_ELEMENT = 1;
    private const KIND_ATTRIBUTE = 2;
    private const KIND_TEXT = 4;
    private const KIND_COMMENT = 8;
    private const KIND_PI = 16;
    private const KIND_DOCUMENT = 32;

    /** Atomic types of the plans (alpha codes) => the names of Value::cast(). */
    private const CAST_TYPES = ['AS' => 'string', 'AZ' => 'untypedAtomic', 'AB' => 'boolean', 'AD' => 'decimal', 'ADI' => 'integer', 'AO' => 'double', 'AA' => 'date', 'AU' => 'string'];

    /** Java's Integer.MAX_VALUE: Saxon counts positions in ints. */
    private const JAVA_INT_MAX = 2147483647;

    /** @var array<int, Tree> spl_object_id of a document => its tree */
    private array $trees = [];

    /** @var array<int, int> spl_object_id of a node => document order, over all trees */
    private array $order = [];

    private int $nextBase = 0;

    private ?DOMDocument $document = null;

    /** @var array<string, list<mixed>> values of the global variables computed so far */
    private array $globalValues = [];

    /** @var array<string, true> global variables being computed (cycle guard) */
    private array $computing = [];

    /** @var array<string, DOMDocument> documents loaded by document() */
    private array $loadedDocuments = [];

    /** @var array<string, list<mixed>> the values of the paths Invariants marked, by number and document */
    private array $memo = [];

    /**
     * @var array<string, array<array-key, list<DOMNode>>> the nodes of a step from a node by the value of an attribute
     *                                                     - for a predicate "[@value = $x]"
     */
    private array $indexes = [];

    /** @var array<string, Decimal> */
    private static array $decimals = [];

    /**
     * @param array<string, list<mixed>> $functions "Q{uri}local#arity" => the body (arguments in slots 0 ..)
     * @param array<string, list<mixed>> $globals name => expression of the global variable
     * @param array<string, string> $documents what document() may load: the name used in the rules => file path
     */
    public function __construct(
        private readonly array $functions = [],
        private readonly array $globals = [],
        private readonly array $documents = [],
    ) {}

    /**
     * The document the global variables are computed for (their context item), before the rules run on it.
     */
    public function bind(DOMDocument $document): void
    {
        $this->document = $document;
        $this->globalValues = [];
        $this->computing = [];
        $this->memo = [];
        $this->indexes = [];
        $this->tree($document);
        // as Saxon: the offset of the default timezone at the time of the run
        Date::setImplicitTimezone(intdiv((int) date('Z'), 60));
    }

    /**
     * The sequence of an expression; the last entry may be a Failure (an error raised only when that item is read).
     *
     * @param list<mixed> $expression
     * @param array<int, list<mixed>|Lazy> $frame
     *
     * @return list<mixed>
     */
    public function evaluate(array $expression, mixed $item = null, array $frame = []): array
    {
        return $this->ev($expression, $item, 1, 1, $frame);
    }

    /**
     * The expression as a condition (Saxon's boolean evaluation of it).
     *
     * @param list<mixed> $expression
     * @param array<int, list<mixed>|Lazy> $frame
     *
     * @throws DynamicError
     */
    public function test(array $expression, mixed $item = null, array $frame = []): bool
    {
        return $this->bool($expression, $item, 1, 1, $frame);
    }

    /**
     * The value of an expression as text (xsl:value-of of an already joined expression).
     *
     * @param list<mixed> $expression
     * @param array<int, list<mixed>|Lazy> $frame
     *
     * @throws DynamicError
     */
    public function text(array $expression, mixed $item, array $frame): string
    {
        $text = '';
        foreach (self::all($this->ev($expression, $item, 1, 1, $frame)) as $value) {
            $text .= Value::string($value);
        }

        return $text;
    }

    /**
     * A variable of a frame bound lazily (its value computed on first read), eagerly or - Saxon's default - learning.
     *
     * @param list<mixed> $expression
     * @param array<int, list<mixed>|Lazy> $frame
     *
     * @return list<mixed>|Lazy
     */
    public function bindVariable(array $expression, string $mode, mixed $item, int $pos, int $size, array $frame): array|Lazy
    {
        if ($mode === 'eager') {
            return $this->ev($expression, $item, $pos, $size, $frame);
        }

        return new Lazy(fn(): array => $this->ev($expression, $item, $pos, $size, $frame));
    }

    /**
     * Whether a node matches a pattern of a template rule. Errors are thrown (the caller treats them as no match,
     * like Saxon), except where Saxon itself swallows them.
     *
     * @param list<mixed> $pattern
     *
     * @throws DynamicError
     */
    public function matches(array $pattern, DOMNode $node): bool
    {
        switch ($pattern[0]) {
            case 'pnode':
                return self::passes($node, $pattern[1]);
            case 'ppred':
                return $this->matches($pattern[1], $node) && $this->bool($pattern[2], $node, 1, 1, []);
            case 'pvenn':
                return match ($pattern[1]) {
                    'union' => $this->matches($pattern[2], $node) || $this->matches($pattern[3], $node),
                    'intersect' => $this->matches($pattern[2], $node) && $this->matches($pattern[3], $node),
                    default => $this->matches($pattern[2], $node) && ! $this->matches($pattern[3], $node),
                };
            case 'pupper':
                return $this->matchesUpper($pattern, $node);
            case 'pgenpos':
                return $this->matchesPositional($pattern, $node);
            case 'pnodeset':
                // Saxon's NodeSetPattern treats an error as no match
                try {
                    foreach (self::all($this->ev($pattern[1], $node, 1, 1, [])) as $candidate) {
                        if ($candidate instanceof DOMNode && $candidate->isSameNode($node)) {
                            return true;
                        }
                    }
                } catch (DynamicError) {
                }

                return false;
        }

        throw new DynamicError('XTSE0340', "Unknown pattern {$pattern[0]}.");
    }

    /**
     * A pattern with an upper part (a/b, a//b): both parts must match. If one part fails and the other does not
     * match, the result is false; if the other matches, the error stands (Saxon's AncestorQualifiedPattern, the same
     * whichever part it tries first).
     *
     * @param list<mixed> $pattern ['pupper', axis, upFirst, base, upper]
     */
    private function matchesUpper(array $pattern, DOMNode $node): bool
    {
        [, $axis, $upFirst, $base, $upper] = $pattern;
        $first = $upFirst ? fn(): bool => $this->matchesAbove($axis, $upper, $node) : fn(): bool => $this->matches($base, $node);
        $second = $upFirst ? fn(): bool => $this->matches($base, $node) : fn(): bool => $this->matchesAbove($axis, $upper, $node);
        try {
            $ok = $first();
        } catch (DynamicError $e) {
            if ($second()) {
                throw $e;
            }

            return false;
        }

        return $ok && $second();
    }

    /**
     * @param list<mixed> $upper
     */
    private function matchesAbove(string $axis, array $upper, DOMNode $node): bool
    {
        $parent = $this->parentOf($node);
        if ($axis === 'parent') {
            return $parent !== null && $this->matches($upper, $parent);
        }
        for (; $parent !== null; $parent = $this->parentOf($parent)) {
            if ($this->matches($upper, $parent)) {
                return true;
            }
        }

        return false;
    }

    /**
     * A pattern whose predicate may be a position (Saxon's GeneralPositionalPattern): an error means no match.
     *
     * @param list<mixed> $pattern ['pgenpos', nodeTest, predicate]
     */
    private function matchesPositional(array $pattern, DOMNode $node): bool
    {
        if (! self::passes($node, $pattern[1])) {
            return false;
        }
        try {
            $position = 1;
            $parent = $this->parentOf($node);
            if ($parent !== null && ! $node instanceof DOMAttr) {
                foreach ($this->treeOf($node)->children[spl_object_id($parent)] ?? [] as $sibling) {
                    if ($sibling->isSameNode($node)) {
                        break;
                    }
                    if (self::passes($sibling, $pattern[1])) {
                        $position++;
                    }
                }
            }
            $value = self::head($this->ev($pattern[2], $node, $position, $position, []));
            if (Value::isNumeric($value)) {
                return Value::compareNumbers($value, $position) === 0;
            }

            return Value::ebv($value === null ? [] : [$value]);
        } catch (DynamicError) {
            return false;
        }
    }

    // ------------------------------------------------------------------ sequences

    /**
     * @param list<mixed> $e
     * @param array<int, list<mixed>|Lazy> $frame
     *
     * @return list<mixed>
     */
    private function ev(array $e, mixed $item, int $pos, int $size, array $frame): array
    {
        switch ($e[0]) {
            case 'str':
            case 'int':
                return [$e[1]];
            case 'dbl':
                return [is_string($e[1]) ? Value::parseDouble($e[1]) : $e[1]];
            case 'atomic':
                return [$this->castTo($e[1], $e[2])];
            case 'range':
                return $e[1] > $e[2] ? [] : range($e[1], $e[2]);
            case 'isLast':
                return [($pos === $size) === $e[1]];
            case 'nc':
                return $this->nodeComparison($e, $item, $pos, $size, $frame);
            case 'neg':
                $value = $this->one($e[1], $item, $pos, $size, $frame);

                return $value === null ? [] : [self::negate($value)];
            case 'homCheck':
                return $this->homogeneous($this->ev($e[1], $item, $pos, $size, $frame));
            case 'tail':
                $values = $this->ev($e[1], $item, $pos, $size, $frame);
                for ($i = 0; $i < $e[2] - 1 && isset($values[$i]); $i++) {
                    if ($values[$i] instanceof Failure) {
                        return [$values[$i]];
                    }
                }

                return array_slice($values, $e[2] - 1);
            case 'dec':
                return [self::$decimals[$e[1]] ??= Decimal::of($e[1])];
            case 'big':
                return [Decimal::integer(str_starts_with($e[1], '-'), ltrim($e[1], '-'))];
            case 'true':
                return [true];
            case 'false':
                return [false];
            case 'empty':
                return [];
            case 'literal':
                $values = [];
                foreach ($e[1] as $part) {
                    array_push($values, ...$this->ev($part, $item, $pos, $size, $frame));
                }

                return $values;
            case 'dot':
                if ($item === null) {
                    throw new DynamicError('XPDY0002', 'The context item is absent.');
                }

                return [$item];
            case 'root':
                return [$this->root($item)];
            case 'var':
                $value = $frame[$e[1]] ?? throw new DynamicError('XPST0008', "Unbound variable in slot {$e[1]}.");

                return $value instanceof Lazy ? $value->get() : $value;
            case 'gvar':
                return $this->global($e[1]);
            case 'axis':
                if (! $item instanceof DOMNode) {
                    throw new DynamicError($item === null ? 'XPDY0002' : 'XPTY0020', 'An axis step needs a node as context item.');
                }

                return $this->axis($item, $e[1], $e[2]);
            case 'slash':
                return $this->slash($e[1], $e[2], $item, $pos, $size, $frame);
            case 'filter':
                return $this->filter($e[1], $e[2], $e[3], $item, $pos, $size, $frame);
            case 'first':
                $value = self::head($this->firstItems($e[1], 1, $item, $pos, $size, $frame));

                return $value === null ? [] : [$value];
            case 'lastOf':
                $values = self::all($this->ev($e[1], $item, $pos, $size, $frame));

                return $values === [] ? [] : [$values[count($values) - 1]];
            case 'subscript':
                return $this->subscript($e, $item, $pos, $size, $frame);
            case 'docOrder':
                return $this->sortNodes(self::nodesOf(self::all($this->ev($e[1], $item, $pos, $size, $frame)), 'XPTY0004'));
            case 'union':
            case 'intersect':
            case 'except':
                return $this->venn($e[0], self::all($this->ev($e[1], $item, $pos, $size, $frame)), self::all($this->ev($e[2], $item, $pos, $size, $frame)));
            case 'to':
                return $this->range($e, $item, $pos, $size, $frame);
            case 'and':
            case 'or':
            case 'gc':
            case 'instance':
            case 'castable':
            case 'some':
            case 'every':
                return [$this->bool($e, $item, $pos, $size, $frame)];
            case 'vc':
                $result = $this->valueComparison($e, $item, $pos, $size, $frame);

                return $result === null ? [] : [$result];
            case 'arith':
                $result = $this->arithmetic($e, $item, $pos, $size, $frame);

                return $result === null ? [] : [$result];
            case 'treat':
                return $this->treat($e, $item, $pos, $size, $frame);
            case 'cast':
                $value = $this->one($e[1], $item, $pos, $size, $frame);
                if ($value === null) {
                    if ($e[3]) {
                        return [];
                    }
                    throw new DynamicError('XPTY0004', 'An empty sequence is not allowed as the operand of "cast as".');
                }

                return [$this->castTo($value, $e[2])];
            case 'cvUntyped':
                return $this->mapItems($this->ev($e[1], $item, $pos, $size, $frame), fn(mixed $value): mixed => $value instanceof Untyped ? $this->castTo($value, $e[2], $e[3]) : $value);
            case 'convert':
                return $this->mapItems($this->ev($e[1], $item, $pos, $size, $frame), fn(mixed $value): mixed => $this->convertItem($value, $e[3], $e[5]));
            case 'data':
                return $this->mapItems($this->ev($e[1], $item, $pos, $size, $frame), fn(mixed $value): mixed => $value instanceof DOMNode ? new Untyped(Value::nodeString($value)) : $value);
            case 'atomSing':
                $value = $this->singleAtom($e, $item, $pos, $size, $frame);

                return $value === null ? [] : [$value];
            case 'check':
                return $this->checkCardinality($this->ev($e[1], $item, $pos, $size, $frame), $e[2], $e[3]);
            case 'attVal':
                if (! $item instanceof DOMNode) {
                    throw new DynamicError($item === null ? 'XPDY0002' : 'XPTY0020', 'The context item is not a node.');
                }
                if (! $item instanceof DOMElement) {
                    return [];
                }
                $attribute = $e[1] === '' ? $item->getAttributeNode($e[2]) : $item->getAttributeNodeNS($e[1], $e[2]);

                return $attribute instanceof DOMAttr ? [new Untyped($attribute->value)] : [];
            case 'fn':
                return $this->call($e[1], $e[2], $item, $pos, $size, $frame);
            case 'ufCall':
                return $this->callUserFunction($e[1], $e[2], $item, $pos, $size, $frame);
            case 'let':
                $frame[$e[1]] = $this->bindVariable($e[2], $e[4], $item, $pos, $size, $frame);

                return $this->ev($e[3], $item, $pos, $size, $frame);
            case 'for':
                return $this->forExpression($e, $item, $pos, $size, $frame);
            case 'choose':
                $count = count($e[1]);
                for ($i = 0; $i < $count; $i += 2) {
                    if ($this->bool($e[1][$i], $item, $pos, $size, $frame)) {
                        return $this->ev($e[1][$i + 1], $item, $pos, $size, $frame);
                    }
                }

                return [];
            case 'seq':
                // Saxon's Block evaluates its members as they are read: an error of a later member is deferred
                $values = [];
                foreach ($e[1] as $part) {
                    try {
                        $part = $this->ev($part, $item, $pos, $size, $frame);
                    } catch (DynamicError $error) {
                        $values[] = new Failure($error);

                        return $values;
                    }
                    foreach ($part as $value) {
                        $values[] = $value;
                        if ($value instanceof Failure) {
                            return $values;
                        }
                    }
                }

                return $values;
            case 'mergeAdj':
                return $this->ev($e[1], $item, $pos, $size, $frame);
            case 'memo':
                return $this->memoized($e, $item, $pos, $size, $frame);
        }

        throw new DynamicError('XPST0003', "Unknown expression \"{$e[0]}\".");
    }

    /**
     * A path that depends on the document alone (Invariants): computed once per document. An error is not kept - the
     * next reader computes it again and meets it again.
     *
     * @param list<mixed> $e ['memo', number, path, whether it reads the document of the focus]
     * @param array<int, list<mixed>|Lazy> $frame
     *
     * @return list<mixed>
     */
    private function memoized(array $e, mixed $item, int $pos, int $size, array $frame): array
    {
        if (! $e[3]) {
            return $this->memo[(string) $e[1]] ??= $this->ev($e[2], $item, $pos, $size, $frame);
        }
        if (! $item instanceof DOMNode) {
            // "/" without a node as focus: the error of the path
            return $this->ev($e[2], $item, $pos, $size, $frame);
        }

        return $this->memo[$e[1] . ':' . spl_object_id($this->root($item))] ??= $this->ev($e[2], $item, $pos, $size, $frame);
    }

    /**
     * Node comparison is, <<, >>: the left operand first, empty gives empty.
     *
     * @param list<mixed> $e ['nc', 'is'|'precedes'|'follows', a, b]
     * @param array<int, list<mixed>|Lazy> $frame
     *
     * @return list<bool>
     */
    private function nodeComparison(array $e, mixed $item, int $pos, int $size, array $frame): array
    {
        $a = $this->one($e[2], $item, $pos, $size, $frame);
        if ($a === null) {
            return [];
        }
        $b = $this->one($e[3], $item, $pos, $size, $frame);
        if ($b === null) {
            return [];
        }
        if (! $a instanceof DOMNode || ! $b instanceof DOMNode) {
            throw new DynamicError('XPTY0004', 'The operands of a node comparison must be nodes.');
        }

        return [match ($e[1]) {
            'is' => $a->isSameNode($b),
            'precedes' => $this->orderOf($a) < $this->orderOf($b),
            default => $this->orderOf($a) > $this->orderOf($b),
        }];
    }

    private static function negate(mixed $value): mixed
    {
        if (! Value::isNumeric($value)) {
            throw new DynamicError('XPTY0004', 'The operand of unary minus must be a number.');
        }

        return match (true) {
            is_int($value) => $value === PHP_INT_MIN ? Decimal::fromInt($value)->negate()->toInteger() : -$value,
            is_float($value) => -$value,
            default => $value->negate(),
        };
    }

    /**
     * The result of a path must be all nodes (then sorted) or all atomic values (Saxon's HomogeneityChecker).
     *
     * @param list<mixed> $values
     *
     * @return list<mixed>
     */
    private function homogeneous(array $values): array
    {
        if ($values === [] || $values[0] instanceof Failure) {
            return $values;
        }
        if ($values[0] instanceof DOMNode) {
            // Saxon reads all nodes before the first is delivered
            foreach ($values as $value) {
                if ($value instanceof Failure) {
                    return [$value];
                }
                if (! $value instanceof DOMNode) {
                    return [new Failure(new DynamicError('XPTY0018', 'Cannot mix nodes and atomic values in the result of a path expression.'))];
                }
            }
            /** @var list<DOMNode> $values */
            return $this->sortNodes($values);
        }

        return $this->mapItems($values, function (mixed $value): mixed {
            if ($value instanceof DOMNode) {
                throw new DynamicError('XPTY0018', 'Cannot mix nodes and atomic values in the result of a path expression.');
            }

            return $value;
        });
    }

    /**
     * The first item, null for an empty sequence; a failure there is raised.
     *
     * @param list<mixed> $e
     * @param array<int, list<mixed>|Lazy> $frame
     */
    private function one(array $e, mixed $item, int $pos, int $size, array $frame): mixed
    {
        return self::head($this->ev($e, $item, $pos, $size, $frame));
    }

    /**
     * @param list<mixed> $values
     */
    private static function head(array $values): mixed
    {
        if ($values === []) {
            return null;
        }
        if ($values[0] instanceof Failure) {
            throw $values[0]->error;
        }

        return $values[0];
    }

    /**
     * All items: a failure is raised.
     *
     * @param list<mixed> $values
     *
     * @return list<mixed>
     */
    public static function all(array $values): array
    {
        $count = count($values);
        if ($count > 0 && $values[$count - 1] instanceof Failure) {
            throw $values[$count - 1]->error;
        }

        return $values;
    }

    /**
     * Maps every item (a lazy mapping in Saxon): an error of item k ends the sequence there as a failure.
     *
     * @param list<mixed> $values
     * @param callable(mixed): mixed $map
     *
     * @return list<mixed>
     */
    private function mapItems(array $values, callable $map): array
    {
        $result = [];
        foreach ($values as $value) {
            if ($value instanceof Failure) {
                $result[] = $value;

                break;
            }
            try {
                $result[] = $map($value);
            } catch (DynamicError $error) {
                $result[] = new Failure($error);

                break;
            }
        }

        return $result;
    }

    /**
     * start/step, the step evaluated for each item of start (Saxon's simple mapping; sorting, where the path needs
     * it, is a docOrder node of its own).
     *
     * @param list<mixed> $start
     * @param list<mixed> $step
     * @param array<int, list<mixed>|Lazy> $frame
     *
     * @return list<mixed>
     */
    private function slash(array $start, array $step, mixed $item, int $pos, int $size, array $frame): array
    {
        $contexts = $this->ev($start, $item, $pos, $size, $frame);
        $count = count($contexts);
        if ($count === 0) {
            return [];
        }
        $failed = $contexts[$count - 1] instanceof Failure;
        if ($failed && self::usesLast($step)) {
            // last() reads the whole sequence first - its error comes before anything else
            return [$contexts[$count - 1]];
        }
        $size = $failed ? $count - 1 : $count;

        // the common case: an axis step from nodes
        if ($step[0] === 'axis' && ! $failed) {
            $result = [];
            foreach ($contexts as $context) {
                if (! $context instanceof DOMNode) {
                    $result[] = new Failure(new DynamicError('XPTY0020', 'An axis step needs a node as context item.'));

                    return $result;
                }
                foreach ($this->axis($context, $step[1], $step[2]) as $node) {
                    $result[] = $node;
                }
            }

            return $result;
        }

        $result = [];
        foreach ($contexts as $index => $context) {
            if ($context instanceof Failure) {
                $result[] = $context;

                return $result;
            }
            try {
                $values = $this->ev($step, $context, $index + 1, $size, $frame);
            } catch (DynamicError $error) {
                $result[] = new Failure($error);

                return $result;
            }
            foreach ($values as $value) {
                $result[] = $value;
                if ($value instanceof Failure) {
                    return $result;
                }
            }
        }

        return $result;
    }

    /**
     * base[predicate]: a boolean predicate (Saxon knows it is never a number) or one tested like a position.
     *
     * @param list<mixed> $base
     * @param list<mixed> $predicate
     * @param array<int, list<mixed>|Lazy> $frame
     *
     * @return list<mixed>
     */
    private function filter(array $base, array $predicate, bool $boolean, mixed $item, int $pos, int $size, array $frame): array
    {
        $values = $this->ev($base, $item, $pos, $size, $frame);
        $count = count($values);
        if ($count === 0) {
            return [];
        }
        $failed = $values[$count - 1] instanceof Failure;
        if ($failed && self::usesLast($predicate)) {
            // last() reads the whole sequence first - its error comes before anything else
            return [$values[$count - 1]];
        }
        $size = $failed ? $count - 1 : $count;
        $result = [];
        foreach ($values as $index => $value) {
            if ($value instanceof Failure) {
                $result[] = $value;

                break;
            }
            try {
                $keep = $boolean
                    ? $this->bool($predicate, $value, $index + 1, $size, $frame)
                    : self::predicateValue($this->ev($predicate, $value, $index + 1, $size, $frame), $index + 1);
            } catch (DynamicError $error) {
                $result[] = new Failure($error);

                break;
            }
            if ($keep) {
                $result[] = $value;
            }
        }

        return $result;
    }

    /**
     * The value of a predicate that may be a position (Saxon's FilterIterator.testPredicateValue).
     *
     * @param list<mixed> $values
     */
    private static function predicateValue(array $values, int $position): bool
    {
        $first = self::head($values);
        if ($first === null) {
            return false;
        }
        if ($first instanceof DOMNode) {
            return true;
        }
        if (count($values) > 1) {
            if ($values[1] instanceof Failure) {
                throw $values[1]->error;
            }
            throw new DynamicError('FORG0006', 'Effective boolean value is not defined for a sequence of two or more items starting with ' . Value::typeName($first) . '.');
        }
        if (Value::isNumeric($first)) {
            return Value::compareNumbers($first, $position) === 0;
        }

        return Value::ebv([$first]);
    }

    /**
     * The items of a sequence in the order Saxon reads them, one after the other: a path whose last step goes along an
     * axis - with a boolean predicate - is walked context node by context node, so that a reader who stops early
     * (exists(), the effective boolean value) does not compute the rest. Other expressions are computed at once.
     *
     * @param list<mixed> $e
     * @param array<int, list<mixed>|Lazy> $frame
     *
     * @return iterable<mixed> the last item a Failure where reading fails
     */
    private function items(array $e, mixed $item, int $pos, int $size, array $frame): iterable
    {
        if ($e[0] === 'slash' && self::isNodeStep($e[2])) {
            return $this->walkPath($e[1], $e[2], $item, $pos, $size, $frame);
        }
        if ($e[0] === 'filter' && self::isNodeStep($e) && $item instanceof DOMNode) {
            return $this->stepItems($e, $item, $frame);
        }

        return $this->ev($e, $item, $pos, $size, $frame);
    }

    /**
     * At most the first $limit items of a sequence, read one after the other - a node as first item ends the
     * reading, as for the effective boolean value that is all it needs.
     *
     * @param list<mixed> $e
     * @param array<int, list<mixed>|Lazy> $frame
     *
     * @return list<mixed>
     */
    private function firstItems(array $e, int $limit, mixed $item, int $pos, int $size, array $frame): array
    {
        $values = [];
        foreach ($this->items($e, $item, $pos, $size, $frame) as $value) {
            $values[] = $value;
            if (count($values) >= $limit || $value instanceof Failure || $value instanceof DOMNode) {
                break;
            }
        }

        return $values;
    }

    /**
     * start/step read lazily: the step for one context item after the other.
     *
     * @param list<mixed> $start
     * @param list<mixed> $step an axis step (isNodeStep())
     * @param array<int, list<mixed>|Lazy> $frame
     *
     * @return Generator<int, mixed>
     */
    private function walkPath(array $start, array $step, mixed $item, int $pos, int $size, array $frame): Generator
    {
        foreach ($this->items($start, $item, $pos, $size, $frame) as $context) {
            if ($context instanceof Failure) {
                yield $context;

                return;
            }
            if (! $context instanceof DOMNode) {
                yield new Failure(new DynamicError('XPTY0020', 'An axis step needs a node as context item.'));

                return;
            }
            foreach ($this->stepItems($step, $context, $frame) as $value) {
                yield $value;
                if ($value instanceof Failure) {
                    return;
                }
            }
        }
    }

    /**
     * The nodes of an axis step from one context node, a boolean predicate tested node by node.
     *
     * @param list<mixed> $step
     * @param array<int, list<mixed>|Lazy> $frame
     *
     * @return Generator<int, mixed>
     */
    private function stepItems(array $step, DOMNode $context, array $frame): Generator
    {
        if ($step[0] === 'axis') {
            foreach ($this->axis($context, $step[1], $step[2]) as $node) {
                yield $node;
            }

            return;
        }
        $nodes = $this->axis($context, $step[1][1], $step[1][2]);
        $lookup = self::attributeLookup($step[2]);
        if ($lookup !== null && $nodes !== []) {
            $found = $this->lookup($nodes, $context, $step[1], $lookup, $frame);
            if ($found !== null) {
                foreach ($found as $value) {
                    yield $value;
                }

                return;
            }
        }
        $size = count($nodes);
        foreach ($nodes as $index => $node) {
            try {
                $keep = $this->bool($step[2], $node, $index + 1, $size, $frame);
            } catch (DynamicError $error) {
                yield new Failure($error);

                return;
            }
            if ($keep) {
                yield $node;
            }
        }
    }

    /**
     * A predicate "[@name eq value]" whose value reads neither the focus nor a node - a variable or a text:
     * [namespace, name, value]; null for any other predicate.
     *
     * @param list<mixed> $predicate
     *
     * @return array{string, string, list<mixed>}|null
     */
    private static function attributeLookup(array $predicate): ?array
    {
        if ($predicate[0] !== 'vc' || $predicate[1] !== 'eq' || $predicate[4] !== false) {
            return null;
        }
        [, $attribute, $type, $allowEmpty] = $predicate[2] + [null, null, null, null];
        if ($predicate[2][0] !== 'cast' || ! is_array($attribute) || $attribute[0] !== 'attVal' || $type !== 'AS' || $allowEmpty !== true) {
            return null;
        }
        if (! in_array($predicate[3][0], ['var', 'str'], true)) {
            return null;
        }

        return [$attribute[1], $attribute[2], $predicate[3]];
    }

    /**
     * The nodes a predicate "[@name eq value]" keeps, found in an index of the values of the attribute - built once
     * per step and context node: FeRD checks its code lists value by value. As the predicate does: a node without the
     * attribute is not kept, the value is read only when a node has it.
     *
     * @param non-empty-list<DOMNode> $nodes the nodes of the step
     * @param list<mixed> $axis the axis step
     * @param array{string, string, list<mixed>} $lookup attributeLookup()
     * @param array<int, list<mixed>|Lazy> $frame
     *
     * @return list<mixed>|null null where the value is no text - the predicate is then tested node by node
     */
    private function lookup(array $nodes, DOMNode $context, array $axis, array $lookup, array $frame): ?array
    {
        [$namespace, $name, $value] = $lookup;
        $key = spl_object_id($context) . json_encode([$axis, $namespace, $name]);
        if (! isset($this->indexes[$key])) {
            $index = [];
            foreach ($nodes as $node) {
                $attribute = ! $node instanceof DOMElement ? null : ($namespace === '' ? $node->getAttributeNode($name) : $node->getAttributeNodeNS($namespace, $name));
                if ($attribute instanceof DOMAttr) {
                    $index[$attribute->value][] = $node;
                }
            }
            $this->indexes[$key] = $index;
        }
        if ($this->indexes[$key] === []) {
            return [];
        }

        try {
            $text = $this->one($value, $context, 1, 1, $frame);
        } catch (DynamicError $error) {
            return [new Failure($error)];
        }
        if ($text instanceof Untyped) {
            $text = $text->value;
        }
        if ($text === null) {
            return [];
        }

        return is_string($text) ? $this->indexes[$key][$text] ?? [] : null;
    }

    /**
     * An axis step, or one with a boolean predicate (Saxon knows it is never a number).
     *
     * @param list<mixed> $e
     */
    private static function isNodeStep(array $e): bool
    {
        return $e[0] === 'axis' || ($e[0] === 'filter' && $e[1][0] === 'axis' && $e[3] === true);
    }

    /**
     * Whether an expression asks for the size of its focus - last() -, not counting where a path step or a predicate
     * inside it gets a focus of its own.
     *
     * @param array<mixed> $e
     */
    private static function usesLast(array $e): bool
    {
        if (is_string($e[0] ?? null)) {
            if ($e[0] === 'isLast' || ($e[0] === 'fn' && ($e[1] ?? null) === 'last')) {
                return true;
            }
            foreach ($e as $index => $part) {
                if (is_array($part) && ! ($index === 2 && ($e[0] === 'slash' || $e[0] === 'filter')) && self::usesLast($part)) {
                    return true;
                }
            }

            return false;
        }
        foreach ($e as $part) {
            if (is_array($part) && self::usesLast($part)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param list<mixed> $e ['subscript', sequence, index]
     * @param array<int, list<mixed>|Lazy> $frame
     *
     * @return list<mixed>
     */
    private function subscript(array $e, mixed $item, int $pos, int $size, array $frame): array
    {
        $values = $this->ev($e[1], $item, $pos, $size, $frame);
        $index = $this->one($e[2], $item, $pos, $size, $frame);
        if (! Value::isNumeric($index)) {
            throw new DynamicError('XPTY0004', 'The index of a subscript must be a number.');
        }
        /** @var int|float|Decimal $index */
        $position = Value::toFloat($index);
        if ($position != floor($position) || $position < 1) {
            return [];
        }
        // the items up to the position are read - all of them where it lies beyond the end (a position too large
        // for an int included)
        $count = count($values);
        $read = $position > $count ? $count : (int) $position;
        for ($i = 0; $i < $read; $i++) {
            if ($values[$i] instanceof Failure) {
                throw $values[$i]->error;
            }
        }

        return $position > $count ? [] : [$values[$read - 1]];
    }

    /**
     * @param list<mixed> $e ['to', from, to]
     * @param array<int, list<mixed>|Lazy> $frame
     *
     * @return list<int>
     */
    private function range(array $e, mixed $item, int $pos, int $size, array $frame): array
    {
        $start = $this->one($e[1], $item, $pos, $size, $frame);
        $end = $this->one($e[2], $item, $pos, $size, $frame);
        if ($start === null || $end === null) {
            return [];
        }
        if (! is_int($start) || ! is_int($end)) {
            throw new DynamicError('XPTY0004', 'The operands of "to" must be integers.');
        }
        if ($start > $end) {
            return [];
        }
        if ($end - $start > 10_000_000) {
            throw new DynamicError('XPDY0130', 'The range is too large.');
        }

        return range($start, $end);
    }

    /**
     * @param list<mixed> $e ['for', slot, in, body]
     * @param array<int, list<mixed>|Lazy> $frame
     *
     * @return list<mixed>
     */
    private function forExpression(array $e, mixed $item, int $pos, int $size, array $frame): array
    {
        $result = [];
        foreach ($this->ev($e[2], $item, $pos, $size, $frame) as $value) {
            if ($value instanceof Failure) {
                $result[] = $value;

                return $result;
            }
            $frame[$e[1]] = [$value];
            try {
                $values = $this->ev($e[3], $item, $pos, $size, $frame);
            } catch (DynamicError $error) {
                $result[] = new Failure($error);

                return $result;
            }
            foreach ($values as $each) {
                $result[] = $each;
                if ($each instanceof Failure) {
                    return $result;
                }
            }
        }

        return $result;
    }

    /**
     * Saxon's ItemChecker ("treat" in the plan): every item of the required item type, checked as it is read; the
     * number of items is a check of its own.
     *
     * @param list<mixed> $e ['treat', expression, sequenceType (its item type counts), errorCode]
     * @param array<int, list<mixed>|Lazy> $frame
     *
     * @return list<mixed>
     */
    private function treat(array $e, mixed $item, int $pos, int $size, array $frame): array
    {
        $itemType = $e[2][1];

        return $this->mapItems($this->ev($e[1], $item, $pos, $size, $frame), function (mixed $value) use ($itemType, $e): mixed {
            if (! $this->isOfType($value, $itemType)) {
                throw new DynamicError($e[3], 'The value ' . Value::typeName($value) . ' does not match the required type.');
            }

            return $value;
        });
    }

    /**
     * A cardinality check (Saxon's CardinalityChecker): reads the first two items at once.
     *
     * @param list<mixed> $values
     *
     * @return list<mixed>
     */
    private function checkCardinality(array $values, string $cardinality, string $code): array
    {
        if ($cardinality === '*') {
            return $values;
        }
        $first = $values[0] ?? null;
        if ($first instanceof Failure) {
            throw $first->error;
        }
        if ($first === null) {
            if ($cardinality === '1' || $cardinality === '+') {
                throw new DynamicError($code, 'An empty sequence is not allowed here.');
            }

            return $values;
        }
        if ($cardinality === '0') {
            throw new DynamicError($code, 'Only an empty sequence is allowed here.');
        }
        $second = $values[1] ?? null;
        if ($second instanceof Failure) {
            throw $second->error;
        }
        if ($second !== null && ($cardinality === '?' || $cardinality === '1')) {
            throw new DynamicError($code, 'A sequence of more than one item is not allowed here.');
        }

        return $values;
    }

    /**
     * Saxon's SingletonAtomizer: atomizes item by item; a second value is an error.
     *
     * @param list<mixed> $e ['atomSing', expression, allowsEmpty, errorCode]
     * @param array<int, list<mixed>|Lazy> $frame
     */
    private function singleAtom(array $e, mixed $item, int $pos, int $size, array $frame): mixed
    {
        $result = null;
        $found = 0;
        foreach ($this->ev($e[1], $item, $pos, $size, $frame) as $value) {
            if ($value instanceof Failure) {
                throw $value->error;
            }
            $found++;
            if ($found > 1) {
                throw new DynamicError($e[3], 'A sequence of more than one item is not allowed here.');
            }
            $result = $value instanceof DOMNode ? new Untyped(Value::nodeString($value)) : $value;
        }
        if ($found === 0 && ! $e[2]) {
            throw new DynamicError($e[3], 'An empty sequence is not allowed here.');
        }

        return $result;
    }

    // ------------------------------------------------------------------ conditions

    /**
     * An expression as a condition, per node type as Saxon evaluates it.
     *
     * @param list<mixed> $e
     * @param array<int, list<mixed>|Lazy> $frame
     */
    private function bool(array $e, mixed $item, int $pos, int $size, array $frame): bool
    {
        switch ($e[0]) {
            case 'true':
                return true;
            case 'false':
                return false;
            case 'and':
                // Saxon 12: an error of one operand does not count if the other is false
                $failed = null;
                try {
                    if (! $this->bool($e[1], $item, $pos, $size, $frame)) {
                        return false;
                    }
                } catch (DynamicError $error) {
                    $failed = $error;
                }
                if (! $this->bool($e[2], $item, $pos, $size, $frame)) {
                    return false;
                }
                if ($failed !== null) {
                    throw $failed;
                }

                return true;
            case 'or':
                // Saxon 12: an error of one operand does not count if the other is true
                $failed = null;
                try {
                    if ($this->bool($e[1], $item, $pos, $size, $frame)) {
                        return true;
                    }
                } catch (DynamicError $error) {
                    $failed = $error;
                }
                if ($this->bool($e[2], $item, $pos, $size, $frame)) {
                    return true;
                }
                if ($failed !== null) {
                    throw $failed;
                }

                return false;
            case 'vc':
                return $this->valueComparison($e, $item, $pos, $size, $frame) ?? false;
            case 'gc':
                return $this->generalComparison($e, $item, $pos, $size, $frame);
            case 'fn':
                switch ($e[1]) {
                    case 'not':
                        return ! $this->bool($e[2][0], $item, $pos, $size, $frame);
                    case 'boolean':
                        return $this->bool($e[2][0], $item, $pos, $size, $frame);
                    case 'exists':
                    case 'empty':
                        // Saxon asks the iterator whether an item is there; only subsequence's one reads ahead
                        // when an item is taken, which is not done here
                        $argument = $e[2][0];
                        $values = $argument[0] === 'fn' && $argument[1] === 'subsequence'
                            ? $this->subsequenceOf($argument[2], $item, $pos, $size, $frame, false)
                            : $this->firstItems($argument, 1, $item, $pos, $size, $frame);

                        return (self::head($values) !== null) === ($e[1] === 'exists');
                }
                break;
            case 'docOrder':
                // Saxon's DocumentSorter: the order does not change whether there is a node - the base is read as it
                // comes, not sorted
                return $this->bool($e[1], $item, $pos, $size, $frame);
            case 'instance':
                $values = $this->ev($e[1], $item, $pos, $size, $frame);

                return $this->instanceOf($values, $e[2]);
            case 'castable':
                return $this->castable($e, $item, $pos, $size, $frame);
            case 'some':
            case 'every':
                $some = $e[0] === 'some';
                foreach ($this->items($e[2], $item, $pos, $size, $frame) as $value) {
                    if ($value instanceof Failure) {
                        throw $value->error;
                    }
                    $frame[$e[1]] = [$value];
                    if ($this->bool($e[3], $item, $pos, $size, $frame) === $some) {
                        return $some;
                    }
                }

                return ! $some;
            case 'let':
                $frame[$e[1]] = $this->bindVariable($e[2], $e[4], $item, $pos, $size, $frame);

                return $this->bool($e[3], $item, $pos, $size, $frame);
            case 'choose':
                $count = count($e[1]);
                for ($i = 0; $i < $count; $i += 2) {
                    if ($this->bool($e[1][$i], $item, $pos, $size, $frame)) {
                        return $this->bool($e[1][$i + 1], $item, $pos, $size, $frame);
                    }
                }

                return false;
        }

        // the effective boolean value reads a second item only after an atomic first one
        return self::effectiveBooleanValue($this->firstItems($e, 2, $item, $pos, $size, $frame));
    }

    /**
     * Effective boolean value as Saxon reads it: the first item, a second only where it decides.
     *
     * @param list<mixed> $values
     */
    public static function effectiveBooleanValue(array $values): bool
    {
        $first = self::head($values);
        if ($first === null) {
            return false;
        }
        if ($first instanceof DOMNode) {
            return true;
        }
        if (isset($values[1])) {
            if ($values[1] instanceof Failure) {
                throw $values[1]->error;
            }
        }

        return Value::ebv(isset($values[1]) ? [$first, $values[1]] : [$first]);
    }

    /**
     * Value comparison (Saxon's ValueComparison): the left operand first; empty gives the "on empty" result or
     * none, without evaluating the other operand.
     *
     * @param list<mixed> $e ['vc', op, a, b, onEmpty]
     * @param array<int, list<mixed>|Lazy> $frame
     */
    private function valueComparison(array $e, mixed $item, int $pos, int $size, array $frame): ?bool
    {
        $a = $this->one($e[2], $item, $pos, $size, $frame);
        if ($a === null) {
            return $e[4];
        }
        $b = $this->one($e[3], $item, $pos, $size, $frame);
        if ($b === null) {
            return $e[4];
        }

        return self::holds($e[1], self::compareValues($a instanceof DOMNode ? new Untyped(Value::nodeString($a)) : $a, $b instanceof DOMNode ? new Untyped(Value::nodeString($b)) : $b, false));
    }

    /**
     * General comparison, in the order Saxon evaluates it for the cardinality it inferred.
     *
     * @param list<mixed> $e ['gc', op, a, b, card]
     * @param array<int, list<mixed>|Lazy> $frame
     */
    private function generalComparison(array $e, mixed $item, int $pos, int $size, array $frame): bool
    {
        $operator = $e[1];
        switch ($e[4]) {
            case '1:1':
                $a = $this->one($e[2], $item, $pos, $size, $frame);
                if ($a === null) {
                    return false;
                }
                $b = $this->one($e[3], $item, $pos, $size, $frame);
                if ($b === null) {
                    return false;
                }

                return self::holds($operator, self::compareValues(Value::atomizeItem($a), Value::atomizeItem($b), true));
            case 'N:1':
                $many = $this->ev($e[2], $item, $pos, $size, $frame);
                $b = $this->one($e[3], $item, $pos, $size, $frame);
                if ($b === null) {
                    return false;
                }
                $b = Value::atomizeItem($b);
                if (is_float($b) && is_nan($b) && $operator !== 'ne') {
                    return false;
                }
                foreach ($many as $a) {
                    if ($a instanceof Failure) {
                        throw $a->error;
                    }
                    if (self::holds($operator, self::compareValues(Value::atomizeItem($a), $b, true))) {
                        return true;
                    }
                }

                return false;
            default:
                // many to many: the operands are read alternately, each new item compared with what the other
                // operand delivered so far
                $left = $this->ev($e[2], $item, $pos, $size, $frame);
                $right = $this->ev($e[3], $item, $pos, $size, $frame);
                $seenLeft = [];
                $seenRight = [];
                $i = 0;
                $j = 0;
                $doneLeft = false;
                $doneRight = false;
                while (true) {
                    if (! $doneLeft) {
                        if (! isset($left[$i])) {
                            if ($doneRight) {
                                return false;
                            }
                            $doneLeft = true;
                        } else {
                            if ($left[$i] instanceof Failure) {
                                throw $left[$i]->error;
                            }
                            $a = Value::atomizeItem($left[$i++]);
                            foreach ($seenRight as $b) {
                                if (self::holds($operator, self::compareValues($a, $b, true))) {
                                    return true;
                                }
                            }
                            if (! $doneRight) {
                                $seenLeft[] = $a;
                            }
                        }
                    }
                    if (! $doneRight) {
                        if (! isset($right[$j])) {
                            if ($doneLeft) {
                                return false;
                            }
                            $doneRight = true;
                        } else {
                            if ($right[$j] instanceof Failure) {
                                throw $right[$j]->error;
                            }
                            $b = Value::atomizeItem($right[$j++]);
                            foreach ($seenLeft as $a) {
                                if (self::holds($operator, self::compareValues($a, $b, true))) {
                                    return true;
                                }
                            }
                            if (! $doneLeft) {
                                $seenRight[] = $b;
                            }
                        }
                    }
                }
        }
    }

    /**
     * Compares two atomic values: in a general comparison xs:untypedAtomic takes the type of the other operand (a
     * number: see untypedWithNumber()), two untyped values compare as strings; in a value comparison untyped is a
     * string.
     */
    private static function compareValues(mixed $a, mixed $b, bool $general): ?int
    {
        if ($a instanceof Untyped || $b instanceof Untyped) {
            if ($a instanceof Untyped && $b instanceof Untyped) {
                return Value::compare($a->value, $b->value);
            }
            if (! $general) {
                return Value::compare($a instanceof Untyped ? $a->value : $a, $b instanceof Untyped ? $b->value : $b);
            }
            if ($a instanceof Untyped) {
                return Value::isNumeric($b) ? self::untypedWithNumber($a, $b) : Value::compare(self::untypedAs($a, $b), $b);
            }
            if (Value::isNumeric($a)) {
                $comparison = self::untypedWithNumber($b, $a);

                return $comparison === null ? null : -$comparison;
            }

            return Value::compare($a, self::untypedAs($b, $a));
        }

        return Value::compare($a, $b);
    }

    /**
     * Saxon's comparison of xs:untypedAtomic with a number in a general comparison (UntypedNumericComparer): a plain
     * integer of at most 15 digits against an xs:integer compares as integers, everything else as xs:double with
     * Java's Double.compare - so "-0.00" lies below 0 and "NaN" above every number, where XPath calls the first equal
     * and the second unordered. Null where the number is NaN (only != holds).
     *
     * @param int|float|Decimal $number
     *
     * @throws DynamicError FORG0001 the text is no xs:double
     */
    private static function untypedWithNumber(Untyped $untyped, mixed $number): ?int
    {
        $other = Value::toFloat($number);
        if (is_nan($other)) {
            return null;
        }
        $text = trim($untyped->value, " \t\r\n");
        if (is_int($number) && preg_match('/^-?[0-9]{1,15}$/D', $text) === 1) {
            return (int) $text <=> $number;
        }
        $value = Value::cast($untyped, 'double');
        if ($value < $other) {
            return -1;
        }
        if ($value > $other) {
            return 1;
        }
        if ($value == $other) {
            // equal as numbers; Double.compare still puts -0.0 below 0.0
            return $value == 0.0 ? (fdiv(1.0, $other) < 0) <=> (fdiv(1.0, $value) < 0) : 0;
        }

        return 1; // NaN, above everything
    }

    /**
     * An untyped value converted for a comparison with $other.
     */
    private static function untypedAs(Untyped $value, mixed $other): mixed
    {
        return match (true) {
            is_string($other) => $value->value,
            Value::isNumeric($other) => Value::cast($value, 'double'),
            is_bool($other) => Value::cast($value, 'boolean'),
            $other instanceof Date => Value::cast($value, 'date'),
            default => throw new DynamicError('XPTY0004', 'Cannot compare xs:untypedAtomic with ' . Value::typeName($other) . '.'),
        };
    }

    private static function holds(string $operator, ?int $comparison): bool
    {
        return match ($operator) {
            'eq' => $comparison === 0,
            'ne' => $comparison !== 0,
            'lt' => $comparison === -1,
            'le' => $comparison === -1 || $comparison === 0,
            'gt' => $comparison === 1,
            default => $comparison === 1 || $comparison === 0,
        };
    }

    /**
     * @param list<mixed> $values
     * @param array{0: string, 1: list<mixed>} $type
     */
    private function instanceOf(array $values, array $type): bool
    {
        [$occurrence, $itemType] = $type;
        $count = 0;
        foreach ($values as $value) {
            if ($value instanceof Failure) {
                throw $value->error;
            }
            $count++;
            if ($occurrence === '0' || ($count > 1 && ($occurrence === '1' || $occurrence === '?'))) {
                return false;
            }
            if (! $this->isOfType($value, $itemType)) {
                return false;
            }
        }

        return $count > 0 || $occurrence === '?' || $occurrence === '*' || $occurrence === '0';
    }

    /**
     * @param list<mixed> $itemType
     */
    private function isOfType(mixed $value, array $itemType): bool
    {
        return match ($itemType[0]) {
            'item' => true,
            'node' => $value instanceof DOMNode && self::passes($value, $itemType[1]),
            'numeric' => Value::isNumeric($value),
            default => ! $value instanceof DOMNode && str_starts_with(self::typeCode($value), $itemType[1]),
        };
    }

    /**
     * The alpha code of the type of an atomic value.
     */
    private static function typeCode(mixed $value): string
    {
        return match (true) {
            is_string($value) => 'AS',
            $value instanceof Untyped => 'AZ',
            is_bool($value) => 'AB',
            is_int($value) => 'ADI',
            $value instanceof Decimal => $value->integer ? 'ADI' : 'AD',
            is_float($value) => 'AO',
            $value instanceof Date => 'AA',
            default => '',
        };
    }

    /**
     * castable as (Saxon's own atomization): more than one value or a value that cannot be cast is false.
     *
     * @param list<mixed> $e ['castable', expression, type, allowsEmpty]
     * @param array<int, list<mixed>|Lazy> $frame
     */
    private function castable(array $e, mixed $item, int $pos, int $size, array $frame): bool
    {
        $count = 0;
        foreach ($this->ev($e[1], $item, $pos, $size, $frame) as $value) {
            if ($value instanceof Failure) {
                throw $value->error;
            }
            $count++;
            if ($count > 1) {
                return false;
            }
            try {
                $this->castTo(Value::atomizeItem($value), $e[2]);
            } catch (DynamicError) {
                return false;
            }
        }

        return $count !== 0 || $e[3];
    }

    // ------------------------------------------------------------------ conversions and arithmetic

    /**
     * A cast of an atomic value to an alpha-coded type.
     */
    private function castTo(mixed $value, string $type, string $code = 'FORG0001'): mixed
    {
        if ($value instanceof DOMNode) {
            $value = new Untyped(Value::nodeString($value));
        }
        if ($type === 'A') {
            return $value;
        }
        $target = self::CAST_TYPES[$type] ?? throw new DynamicError('XPST0080', "Unsupported target type $type.");
        try {
            return Value::cast($value, $target);
        } catch (DynamicError $error) {
            if ($code !== 'FORG0001' && $code !== 'XPTY0004' && $error->errorCode === 'FORG0001') {
                throw new DynamicError($code, $error->getMessage());
            }
            throw $error;
        }
    }

    /**
     * Saxon's AtomicSequenceConverter: the value converted to the type by the cast rules (an error keeps the
     * converter's code unless the role gives another).
     */
    private function convertItem(mixed $value, string $type, string $code): mixed
    {
        if ($value instanceof DOMNode) {
            $value = new Untyped(Value::nodeString($value));
        }
        if ($type === 'A' || str_starts_with(self::typeCode($value), $type)) {
            return $value;
        }
        try {
            return $this->castTo($value, $type);
        } catch (DynamicError $error) {
            throw $code === 'XPTY0004' ? $error : new DynamicError($code, $error->getMessage());
        }
    }

    /**
     * Arithmetic (Saxon's ArithmeticExpression): the left operand first, empty gives empty without evaluating the
     * right one; the calculator Saxon chose statically (i integer, c decimal, d double, a by the values).
     *
     * @param list<mixed> $e ['arith', op, a, b, calculator]
     * @param array<int, list<mixed>|Lazy> $frame
     */
    private function arithmetic(array $e, mixed $item, int $pos, int $size, array $frame): mixed
    {
        $a = $this->one($e[2], $item, $pos, $size, $frame);
        if ($a === null) {
            return null;
        }
        $b = $this->one($e[3], $item, $pos, $size, $frame);
        if ($b === null) {
            return null;
        }

        return self::calculate($e[1], $a, $b, $e[4]);
    }

    /**
     * @param string $calculator the operand types Saxon chose: "ii", "cc", "dd", "aa" (dynamic), ...
     */
    public static function calculate(string $operator, mixed $a, mixed $b, string $calculator = 'aa'): mixed
    {
        if ((! is_int($a) && ! is_float($a) && ! ($a instanceof Decimal)) || (! is_int($b) && ! is_float($b) && ! ($b instanceof Decimal))) {
            throw new DynamicError('XPTY0004', 'Unsuitable types for ' . $operator . ' operation (' . Value::typeName($a) . ', ' . Value::typeName($b) . ').');
        }
        $kind = match ($calculator) {
            'ii' => 'integer',
            'cc', 'ci', 'ic' => 'decimal',
            'dd', 'di', 'id', 'dc', 'cd' => 'double',
            default => is_float($a) || is_float($b) ? 'double' : (self::isInteger($a) && self::isInteger($b) ? 'integer' : 'decimal'),
        };

        if ($kind === 'double') {
            $x = Value::toFloat($a);
            $y = Value::toFloat($b);
            switch ($operator) {
                case '+':
                    return $x + $y;
                case '-':
                    return $x - $y;
                case '*':
                    return $x * $y;
                case 'div':
                    return fdiv($x, $y);
                case 'mod':
                    return fmod($x, $y);
                default:
                    if ($y == 0.0) {
                        throw new DynamicError('FOAR0001', 'Integer division by zero.');
                    }
                    $quotient = fdiv($x, $y);
                    if (is_nan($quotient) || is_infinite($quotient)) {
                        throw new DynamicError('FOAR0002', 'Integer division of NaN or infinity.');
                    }

                    return Decimal::fromFloat($quotient < 0 ? ceil($quotient) : floor($quotient))->toInteger();
            }
        }

        if (is_float($a) || is_float($b)) {
            // the plan's conversions make both operands of an integer or decimal calculator integers or decimals
            throw new DynamicError('XPTY0004', "A double where Saxon calculates with {$kind}s.");
        }

        if ($kind === 'integer' && is_int($a) && is_int($b)) {
            // results beyond the int range go on as xs:integer of any size (Saxon's BigIntegerValue)
            switch ($operator) {
                case '+':
                    return ($b > 0 ? $a > PHP_INT_MAX - $b : $a < PHP_INT_MIN - $b)
                        ? Decimal::fromInt($a)->add(Decimal::fromInt($b))->toInteger()
                        : $a + $b;
                case '-':
                    return ($b < 0 ? $a > PHP_INT_MAX + $b : $a < PHP_INT_MIN + $b)
                        ? Decimal::fromInt($a)->subtract(Decimal::fromInt($b))->toInteger()
                        : $a - $b;
                case '*':
                    // below 3037000499 (the square root of 2^63) both, the product fits
                    return abs($a) < 3037000499 && abs($b) < 3037000499
                        ? $a * $b
                        : Decimal::fromInt($a)->multiply(Decimal::fromInt($b))->toInteger();
                case 'idiv':
                    if ($b === 0) {
                        throw new DynamicError('FOAR0001', 'Integer division by zero.');
                    }

                    return $a === PHP_INT_MIN && $b === -1 ? Decimal::fromInt($a)->negate()->toInteger() : intdiv($a, $b);
                case 'mod':
                    if ($b === 0) {
                        throw new DynamicError('FOAR0001', 'Modulus by zero.');
                    }

                    return $b === -1 ? 0 : $a % $b;
                default:
                    // Saxon's Int64Value.div: an exact quotient stays xs:integer; operands outside 0 .. 2^31 - 1
                    // (negative ones, too) are divided as xs:decimal with trailing zeros stripped
                    if ($b === 0) {
                        throw new DynamicError('FOAR0001', 'Integer division by zero.');
                    }
                    if (($a >> 31) !== 0 || ($b >> 31) !== 0) {
                        return Decimal::fromInt($a)->asDecimal()->divide(Decimal::fromInt($b)->asDecimal());
                    }
                    if ($a % $b === 0) {
                        return intdiv($a, $b);
                    }

                    return Decimal::fromInt($a)->divide(Decimal::fromInt($b));
            }
        }

        // decimal (and integers beyond the int range): Saxon's DecimalXDecimal keeps integer operands of +, -, *,
        // mod and idiv as integers; div is always a decimal division
        $integers = self::isInteger($a) && self::isInteger($b);
        $x = Value::toDecimalValue($a);
        $y = Value::toDecimalValue($b);
        if ($operator === 'idiv') {
            // always an xs:integer
            return $x->integerDivide($y);
        }
        if ($integers && $operator === 'div' && $kind === 'integer') {
            // Saxon's BigIntegerValue.div: stripped decimals
            $x = $x->asDecimal();
            $y = $y->asDecimal();
        }
        $result = match ($operator) {
            '+' => $x->add($y),
            '-' => $x->subtract($y),
            '*' => $x->multiply($y),
            'div' => $x->divide($y),
            default => $x->modulo($y),
        };

        return $integers && $operator !== 'div' ? $result->toInteger() : $result;
    }

    private static function isInteger(mixed $value): bool
    {
        return is_int($value) || ($value instanceof Decimal && $value->integer);
    }

    // ------------------------------------------------------------------ nodes

    /**
     * The tree of a document (built on first use).
     */
    public function tree(DOMDocument $document): Tree
    {
        $id = spl_object_id($document);
        if (! isset($this->trees[$id])) {
            $tree = new Tree($document, $this->nextBase);
            $this->nextBase += 1 << 32;
            $this->trees[$id] = $tree;
            $this->order += $tree->order;
        }

        return $this->trees[$id];
    }

    /**
     * Document order of a node.
     */
    public function orderOf(DOMNode $node): int
    {
        $id = spl_object_id($node);
        if (! isset($this->order[$id])) {
            $this->tree($node instanceof DOMDocument ? $node : $node->ownerDocument ?? throw new DynamicError('XPDY0050', 'Node without document.'));
        }

        return $this->order[$id] ?? throw new DynamicError('XPDY0050', 'Node outside the document tree.');
    }

    /**
     * Sorts nodes into document order and removes duplicates.
     *
     * @param list<DOMNode> $nodes
     *
     * @return list<DOMNode>
     */
    public function sortNodes(array $nodes): array
    {
        if (count($nodes) < 2) {
            return $nodes;
        }
        $keyed = [];
        foreach ($nodes as $node) {
            $keyed[$this->orderOf($node)] = $node;
        }
        ksort($keyed, SORT_NUMERIC);

        return array_values($keyed);
    }

    /**
     * @param list<mixed> $values
     *
     * @return list<DOMNode>
     */
    private static function nodesOf(array $values, string $code): array
    {
        foreach ($values as $value) {
            if (! $value instanceof DOMNode) {
                throw new DynamicError($code, 'Expected nodes, found ' . Value::typeName($value) . '.');
            }
        }
        /** @var list<DOMNode> $values */
        return $values;
    }

    /**
     * @param list<mixed> $left
     * @param list<mixed> $right
     *
     * @return list<DOMNode>
     */
    private function venn(string $operator, array $left, array $right): array
    {
        $left = self::nodesOf($left, 'XPTY0004');
        $right = self::nodesOf($right, 'XPTY0004');
        if ($operator === 'union') {
            return $this->sortNodes([...$left, ...$right]);
        }
        $other = [];
        foreach ($right as $node) {
            $other[spl_object_id($node)] = true;
        }
        $result = [];
        foreach ($left as $node) {
            if (isset($other[spl_object_id($node)]) === ($operator === 'intersect')) {
                $result[] = $node;
            }
        }

        return $this->sortNodes($result);
    }

    private function parentOf(DOMNode $node): ?DOMNode
    {
        return $this->treeOf($node)->parent[spl_object_id($node)] ?? null;
    }

    /**
     * The nodes of an axis that pass the node test, in axis order (reverse axes nearest first).
     *
     * @param list<mixed> $test
     *
     * @return list<DOMNode>
     */
    private function axis(DOMNode $node, string $axis, array $test): array
    {
        $tree = $this->treeOf($node);
        $id = spl_object_id($node);
        $isAttribute = $node->nodeType === XML_ATTRIBUTE_NODE;

        switch ($axis) {
            case 'child':
                if ($test[0] === self::KIND_ELEMENT && $test[2] !== null && $test[1] !== null) {
                    $result = [];
                    foreach ($tree->children[$id] ?? [] as $child) {
                        if ($child->nodeType === XML_ELEMENT_NODE && $child->localName === $test[2] && ($child->namespaceURI ?? '') === $test[1]) {
                            $result[] = $child;
                        }
                    }

                    return $result;
                }
                $candidates = $tree->children[$id] ?? [];
                break;
            case 'attribute':
                $candidates = $tree->attributes[$id] ?? [];
                break;
            case 'self':
                $candidates = [$node];
                break;
            case 'descendant':
            case 'descendant-or-self':
                if ($isAttribute) {
                    $candidates = $axis === 'descendant' ? [] : [$node];
                    break;
                }
                $first = $tree->index[$id] + ($axis === 'descendant' ? 1 : 0);
                $last = $tree->last[$id];
                if ($test[0] === self::KIND_ELEMENT && $test[2] !== null && $test[1] !== null) {
                    return $this->elementsBetween($tree, '{' . $test[1] . '}' . $test[2], $first, $last);
                }
                $candidates = array_slice($tree->nodes, $first, $last - $first + 1);
                break;
            case 'parent':
                $parent = $tree->parent[$id] ?? null;
                $candidates = $parent === null ? [] : [$parent];
                break;
            case 'ancestor':
            case 'ancestor-or-self':
                $candidates = $axis === 'ancestor-or-self' ? [$node] : [];
                for ($parent = $tree->parent[$id] ?? null; $parent !== null; $parent = $tree->parent[spl_object_id($parent)] ?? null) {
                    $candidates[] = $parent;
                }
                break;
            case 'following-sibling':
            case 'preceding-sibling':
                $candidates = [];
                $parent = $tree->parent[$id] ?? null;
                if ($parent === null || $isAttribute) {
                    break;
                }
                $siblings = $tree->children[spl_object_id($parent)];
                $index = array_search($node, $siblings, true);
                if ($axis === 'following-sibling') {
                    $candidates = array_slice($siblings, (int) $index + 1);
                } else {
                    $candidates = array_reverse(array_slice($siblings, 0, (int) $index));
                }
                break;
            case 'following':
                $start = $isAttribute ? $tree->index[spl_object_id($tree->parent[$id])] + 1 : $tree->last[$id] + 1;
                $candidates = array_slice($tree->nodes, $start);
                break;
            case 'preceding':
                $element = $isAttribute ? $tree->parent[$id] : $node;
                $ancestors = [];
                for ($parent = $tree->parent[spl_object_id($element)] ?? null; $parent !== null; $parent = $tree->parent[spl_object_id($parent)] ?? null) {
                    $ancestors[spl_object_id($parent)] = true;
                }
                if ($isAttribute) {
                    $ancestors[spl_object_id($element)] = true;
                }
                $candidates = [];
                for ($i = $tree->index[spl_object_id($element)] - 1; $i >= 0; $i--) {
                    $candidate = $tree->nodes[$i];
                    if (! isset($ancestors[spl_object_id($candidate)])) {
                        $candidates[] = $candidate;
                    }
                }
                break;
            default:
                throw new DynamicError('XPST0010', "Unsupported axis $axis.");
        }

        $result = [];
        foreach ($candidates as $candidate) {
            if (self::passes($candidate, $test)) {
                $result[] = $candidate;
            }
        }

        return $result;
    }

    /**
     * The elements of one name whose index in the tree lies between $first and $last - a descendant axis with a name
     * test, found by binary search in the name index.
     *
     * @return list<DOMElement>
     */
    private function elementsBetween(Tree $tree, string $name, int $first, int $last): array
    {
        $elements = $tree->elements[$name] ?? [];
        $low = 0;
        $high = count($elements);
        while ($low < $high) {
            $middle = ($low + $high) >> 1;
            if ($tree->index[spl_object_id($elements[$middle])] < $first) {
                $low = $middle + 1;
            } else {
                $high = $middle;
            }
        }
        $result = [];
        for ($i = $low, $count = count($elements); $i < $count; $i++) {
            if ($tree->index[spl_object_id($elements[$i])] > $last) {
                break;
            }
            $result[] = $elements[$i];
        }

        return $result;
    }

    /**
     * Whether a node passes a node test: [kinds, uri, local] (null = any) or ['u', [tests]].
     *
     * @param list<mixed> $test
     */
    public static function passes(DOMNode $node, array $test): bool
    {
        if ($test[0] === 'u') {
            foreach ($test[1] as $member) {
                if (self::passes($node, $member)) {
                    return true;
                }
            }

            return false;
        }
        $kind = match ($node->nodeType) {
            XML_ELEMENT_NODE => self::KIND_ELEMENT,
            XML_ATTRIBUTE_NODE => self::KIND_ATTRIBUTE,
            XML_TEXT_NODE, XML_CDATA_SECTION_NODE => self::KIND_TEXT,
            XML_COMMENT_NODE => self::KIND_COMMENT,
            XML_PI_NODE => self::KIND_PI,
            XML_DOCUMENT_NODE => self::KIND_DOCUMENT,
            default => 0,
        };
        if (($test[0] & $kind) === 0) {
            return false;
        }
        if ($test[2] !== null && ($node instanceof DOMProcessingInstruction ? $node->target : $node->localName) !== $test[2]) {
            return false;
        }

        return $test[1] === null || ($node->namespaceURI ?? '') === $test[1];
    }

    private function treeOf(DOMNode $node): Tree
    {
        $document = $node instanceof DOMDocument ? $node : $node->ownerDocument;
        if ($document === null) {
            throw new DynamicError('XPDY0050', 'Node without document.');
        }

        return $this->trees[spl_object_id($document)] ?? $this->tree($document);
    }

    private function root(mixed $item): DOMDocument
    {
        if ($item === null) {
            throw new DynamicError('XPDY0002', 'The context item is absent.');
        }
        if (! $item instanceof DOMNode) {
            throw new DynamicError('XPTY0020', 'The context item is not a node.');
        }

        return $item instanceof DOMDocument ? $item : $item->ownerDocument ?? throw new DynamicError('XPDY0050', 'Node without document.');
    }

    // ------------------------------------------------------------------ variables and functions

    /**
     * @return list<mixed>
     */
    private function global(string $name): array
    {
        if (isset($this->globalValues[$name])) {
            return $this->globalValues[$name];
        }
        $expression = $this->globals[$name] ?? throw new DynamicError('XPST0008', "Unknown global variable $name.");
        if (isset($this->computing[$name])) {
            throw new DynamicError('XTDE0640', "Circular definition of the global variable $name.");
        }
        $this->computing[$name] = true;
        try {
            // XSLT evaluates a global variable completely when it is first used
            $value = self::all($this->ev($expression, $this->document, 1, 1, []));
        } finally {
            unset($this->computing[$name]);
        }

        return $this->globalValues[$name] = $value;
    }

    /**
     * A function of the rules: a new frame with the arguments in slots 0 .. (computed when first read, like Saxon's
     * closures; an argument that calls a function is computed at once), no context item.
     *
     * @param list<list<mixed>> $arguments
     * @param array<int, list<mixed>|Lazy> $frame
     *
     * @return list<mixed>
     */
    private function callUserFunction(string $name, array $arguments, mixed $item, int $pos, int $size, array $frame): array
    {
        $body = $this->functions[$name] ?? throw new DynamicError('XPST0017', "Unknown function $name.");
        $locals = [];
        foreach ($arguments as $slot => $argument) {
            $locals[$slot] = self::callsFunction($argument)
                ? $this->ev($argument, $item, $pos, $size, $frame)
                : $this->bindVariable($argument, 'lazy', $item, $pos, $size, $frame);
        }

        return $this->ev($body, null, 1, 1, $locals);
    }

    /**
     * Whether an expression - or any part of it - calls a user function.
     *
     * @param array<mixed> $expression
     */
    private static function callsFunction(array $expression): bool
    {
        if (($expression[0] ?? null) === 'ufCall') {
            return true;
        }
        foreach ($expression as $part) {
            if (is_array($part) && self::callsFunction($part)) {
                return true;
            }
        }

        return false;
    }

    /**
     * A built-in function; its arguments were already converted by the plan (atomized, cast, checked).
     *
     * @param list<list<mixed>> $arguments
     * @param array<int, list<mixed>|Lazy> $frame
     *
     * @return list<mixed>
     */
    private function call(string $name, array $arguments, mixed $item, int $pos, int $size, array $frame): array
    {
        switch ($name) {
            case 'not':
            case 'boolean':
            case 'exists':
            case 'empty':
                return [$this->bool(['fn', $name, $arguments], $item, $pos, $size, $frame)];
            case 'true':
                return [true];
            case 'false':
                return [false];
            case 'position':
                return [$pos];
            case 'last':
                return [$size];
            case 'count':
                return [count(self::all($this->ev($arguments[0], $item, $pos, $size, $frame)))];
            case 'reverse':
                return array_reverse(self::all($this->ev($arguments[0], $item, $pos, $size, $frame)));
            case 'data':
                return $this->mapItems($this->ev($arguments[0], $item, $pos, $size, $frame), fn(mixed $value): mixed => Value::atomizeItem($value));
            case 'distinct-values':
                return self::distinctValues(self::all($this->ev($arguments[0], $item, $pos, $size, $frame)));
            case 'sum':
                $zero = isset($arguments[1]) ? $this->ev($arguments[1], $item, $pos, $size, $frame) : [0];
                $values = self::all($this->ev($arguments[0], $item, $pos, $size, $frame));
                if ($values === []) {
                    return self::all($zero);
                }

                return [self::sum($values)];
            case 'string-join':
                $separator = isset($arguments[1]) ? (string) $this->stringArgument($arguments[1], $item, $pos, $size, $frame) : '';
                $parts = [];
                foreach (self::all($this->ev($arguments[0], $item, $pos, $size, $frame)) as $part) {
                    $parts[] = Value::string($part);
                }

                return [implode($separator, $parts)];
            case 'concat':
                $text = '';
                foreach ($arguments as $argument) {
                    $value = $this->one($argument, $item, $pos, $size, $frame);
                    $text .= $value === null ? '' : Value::string($value);
                }

                return [$text];
            case 'string':
                $value = $arguments === [] ? $this->contextItem($item) : $this->one($arguments[0], $item, $pos, $size, $frame);

                return [$value === null ? '' : Value::string($value)];
            case 'string-length':
                $text = $arguments === [] ? Value::string($this->contextItem($item)) : (string) $this->stringArgument($arguments[0], $item, $pos, $size, $frame);

                return [mb_strlen($text, 'UTF-8')];
            case 'normalize-space':
                $text = $arguments === [] ? Value::string($this->contextItem($item)) : (string) $this->stringArgument($arguments[0], $item, $pos, $size, $frame);

                return [preg_replace('/[ \t\r\n]+/', ' ', trim($text, " \t\r\n")) ?? $text];
            case 'upper-case':
                return [mb_strtoupper((string) $this->stringArgument($arguments[0], $item, $pos, $size, $frame), 'UTF-8')];
            case 'lower-case':
                return [mb_strtolower((string) $this->stringArgument($arguments[0], $item, $pos, $size, $frame), 'UTF-8')];
            case 'substring':
                $text = (string) $this->stringArgument($arguments[0], $item, $pos, $size, $frame);
                $start = $this->doubleArgument($arguments[1], $item, $pos, $size, $frame);
                $length = isset($arguments[2]) ? $this->doubleArgument($arguments[2], $item, $pos, $size, $frame) : null;

                return [self::substring($text, $start, $length)];
            case 'substring-before':
            case 'substring-after':
            case 'contains':
            case 'starts-with':
            case 'ends-with':
                $text = (string) $this->stringArgument($arguments[0], $item, $pos, $size, $frame);
                $search = (string) $this->stringArgument($arguments[1], $item, $pos, $size, $frame);

                return [self::stringSearch($name, $text, $search)];
            case 'translate':
                return [self::translate(
                    (string) $this->stringArgument($arguments[0], $item, $pos, $size, $frame),
                    (string) $this->stringArgument($arguments[1], $item, $pos, $size, $frame),
                    (string) $this->stringArgument($arguments[2], $item, $pos, $size, $frame),
                )];
            case 'string-to-codepoints':
                $text = (string) $this->stringArgument($arguments[0], $item, $pos, $size, $frame);

                return $text === '' ? [] : array_map(mb_ord(...), mb_str_split($text, 1, 'UTF-8'));
            case 'codepoints-to-string':
                $text = '';
                foreach (self::all($this->ev($arguments[0], $item, $pos, $size, $frame)) as $codepoint) {
                    if (! is_int($codepoint) || $codepoint < 1 || $codepoint > 0x10FFFF || ($codepoint >= 0xD800 && $codepoint <= 0xDFFF)) {
                        throw new DynamicError('FOCH0001', 'Invalid codepoint ' . Value::string($codepoint) . '.');
                    }
                    $text .= mb_chr($codepoint, 'UTF-8');
                }

                return [$text];
            case 'matches':
                return [Regex::matches(
                    (string) $this->stringArgument($arguments[0], $item, $pos, $size, $frame),
                    (string) $this->stringArgument($arguments[1], $item, $pos, $size, $frame),
                    isset($arguments[2]) ? (string) $this->stringArgument($arguments[2], $item, $pos, $size, $frame) : '',
                )];
            case 'replace':
                return [Regex::replace(
                    (string) $this->stringArgument($arguments[0], $item, $pos, $size, $frame),
                    (string) $this->stringArgument($arguments[1], $item, $pos, $size, $frame),
                    (string) $this->stringArgument($arguments[2], $item, $pos, $size, $frame),
                    isset($arguments[3]) ? (string) $this->stringArgument($arguments[3], $item, $pos, $size, $frame) : '',
                )];
            case 'tokenize':
                $text = (string) $this->stringArgument($arguments[0], $item, $pos, $size, $frame);
                if (count($arguments) === 1) {
                    $text = preg_replace('/[ \t\r\n]+/', ' ', trim($text, " \t\r\n")) ?? '';

                    return $text === '' ? [] : explode(' ', $text);
                }

                return Regex::tokenize(
                    $text,
                    (string) $this->stringArgument($arguments[1], $item, $pos, $size, $frame),
                    isset($arguments[2]) ? (string) $this->stringArgument($arguments[2], $item, $pos, $size, $frame) : '',
                );
            case 'number':
                $value = $arguments === [] ? $this->contextItem($item) : $this->one($arguments[0], $item, $pos, $size, $frame);

                return [$value === null ? NAN : Value::number($value)];
            case 'abs':
            case 'round':
            case 'floor':
            case 'ceiling':
                $value = $this->one($arguments[0], $item, $pos, $size, $frame);

                return $value === null ? [] : [self::rounding($name, $value)];
            case 'name':
            case 'local-name':
            case 'namespace-uri':
                $node = $arguments === [] ? $this->contextNode($item) : $this->one($arguments[0], $item, $pos, $size, $frame);
                if ($node !== null && ! $node instanceof DOMNode) {
                    throw new DynamicError('XPTY0004', "The argument of $name() must be a node.");
                }

                return [$node === null ? '' : self::nodeName($name, $node)];
            case 'root':
                $node = $arguments === [] ? $this->contextNode($item) : $this->one($arguments[0], $item, $pos, $size, $frame);
                if ($node === null) {
                    return [];
                }
                if (! $node instanceof DOMNode) {
                    throw new DynamicError('XPTY0004', 'The argument of root() must be a node.');
                }

                return [$this->root($node)];
            case 'document':
                return $this->document(self::all($this->ev($arguments[0], $item, $pos, $size, $frame)));
            case 'subsequence':
                return $this->subsequenceOf($arguments, $item, $pos, $size, $frame, true);
        }

        throw new DynamicError('XPST0017', "Unknown function $name.");
    }

    /**
     * fn:subsequence. Saxon's iterator reads the items up to the first position when it is made, and afterwards,
     * whenever an item is taken, the next one ahead: an error there comes before the item in front of it
     * ($lookahead), unless the reader only asks whether an item is there (exists, empty).
     *
     * @param list<list<mixed>> $arguments
     * @param array<int, list<mixed>|Lazy> $frame
     *
     * @return list<mixed>
     */
    private function subsequenceOf(array $arguments, mixed $item, int $pos, int $size, array $frame, bool $lookahead): array
    {
        $values = $this->ev($arguments[0], $item, $pos, $size, $frame);
        $start = $this->one($arguments[1], $item, $pos, $size, $frame);
        $length = isset($arguments[2]) ? $this->one($arguments[2], $item, $pos, $size, $frame) : null;

        return self::subsequence($values, self::subsequenceRange($start, $length), $lookahead);
    }

    /**
     * The positions fn:subsequence takes, as Saxon computes them (Subsequence_2, Subsequence_3): integers directly,
     * other numbers rounded; the last position is cut to a Java int as Saxon does. Null when nothing is taken, the last
     * position JAVA_INT_MAX for "up to the end".
     *
     * @return array{int, int}|null
     */
    private static function subsequenceRange(mixed $start, mixed $length): ?array
    {
        if (! Value::isNumeric($start) || ($length !== null && ! Value::isNumeric($length))) {
            throw new DynamicError('XPTY0004', 'The positions of subsequence() must be numbers.');
        }
        /** @var int|float|Decimal $start */
        /** @var int|float|Decimal|null $length */
        if (is_int($start) && ($length === null || is_int($length))) {
            if ($start > self::JAVA_INT_MAX) {
                return null;
            }
            if ($length === null) {
                return [max(1, $start), self::JAVA_INT_MAX];
            }
            $length = min($length, self::JAVA_INT_MAX);
            if ($length < 1 || $start + $length - 1 < 1) {
                return null;
            }

            return [max(1, $start), self::javaInt($start + $length - 1)];
        }

        if ((is_float($start) && is_nan($start)) || Value::compareNumbers($start, PHP_INT_MAX) === 1) {
            return null;
        }
        $start = self::rounding('round', $start);
        $first = Value::compareNumbers($start, 1) === 1 ? self::toLong($start) : 1;
        if ($first > self::JAVA_INT_MAX) {
            return null;
        }
        if ($length === null) {
            return [$first, self::JAVA_INT_MAX];
        }
        if (is_float($length) && is_nan($length)) {
            return null;
        }
        $length = self::rounding('round', $length);
        if (Value::compareNumbers($length, 0) !== 1) {
            return null;
        }
        $end = self::calculate('+', $start, $length);
        if (is_float($end) && is_nan($end)) {
            return null;
        }
        $end = self::calculate('-', $end, 1);
        if (Value::compareNumbers($end, 0) !== 1) {
            return null;
        }

        return [$first, Value::compareNumbers($end, PHP_INT_MAX) === -1 ? self::javaInt(self::toLong($end)) : self::JAVA_INT_MAX];
    }

    /**
     * An integral number as Java's long: a double cut off at the limits.
     *
     * @param int|float|Decimal $value
     */
    private static function toLong(mixed $value): int
    {
        return match (true) {
            is_int($value) => $value,
            $value instanceof Decimal => (int) $value->toString(),
            $value >= 9.2233720368547758E18 => PHP_INT_MAX,
            $value <= -9.2233720368547758E18 => PHP_INT_MIN,
            default => (int) $value,
        };
    }

    /**
     * The items at the positions of subsequenceRange(), read as subsequenceOf() describes.
     *
     * @param list<mixed> $values
     * @param array{int, int}|null $range
     *
     * @return list<mixed>
     */
    private static function subsequence(array $values, ?array $range, bool $lookahead): array
    {
        if ($range === null) {
            return [];
        }
        [$first, $last] = $range;
        if ($last === self::JAVA_INT_MAX) {
            // TailIterator: the skipped items are read, the rest as it comes
            for ($i = 0; $i < $first - 1 && isset($values[$i]); $i++) {
                if ($values[$i] instanceof Failure) {
                    return [$values[$i]];
                }
            }

            return array_slice($values, $first - 1);
        }
        $result = [];
        for ($position = 1; $position <= $last && isset($values[$position - 1]); $position++) {
            $value = $values[$position - 1];
            if ($value instanceof Failure) {
                if ($position <= $first) {
                    return [$value];
                }
                if ($lookahead) {
                    array_pop($result);
                }
                $result[] = $value;

                return $result;
            }
            if ($position >= $first) {
                $result[] = $value;
            }
        }

        return $result;
    }

    /**
     * A long cut to a Java int (Saxon's "(int) lend").
     */
    private static function javaInt(int $value): int
    {
        $value &= 0xFFFFFFFF;

        return $value > self::JAVA_INT_MAX ? $value - 0x100000000 : $value;
    }

    /**
     * A string argument (the plan converted it already): null for an empty sequence.
     *
     * @param list<mixed> $argument
     * @param array<int, list<mixed>|Lazy> $frame
     */
    private function stringArgument(array $argument, mixed $item, int $pos, int $size, array $frame): ?string
    {
        $value = $this->one($argument, $item, $pos, $size, $frame);

        return $value === null ? null : Value::string($value);
    }

    /**
     * @param list<mixed> $argument
     * @param array<int, list<mixed>|Lazy> $frame
     */
    private function doubleArgument(array $argument, mixed $item, int $pos, int $size, array $frame): float
    {
        $value = $this->one($argument, $item, $pos, $size, $frame);
        if ($value === null || ! Value::isNumeric($value)) {
            throw new DynamicError('XPTY0004', 'Expected a number.');
        }
        /** @var int|float|Decimal $value */
        return Value::toFloat($value);
    }

    private function contextItem(mixed $item): mixed
    {
        if ($item === null) {
            throw new DynamicError('XPDY0002', 'The context item is absent.');
        }

        return $item;
    }

    private function contextNode(mixed $item): DOMNode
    {
        $item = $this->contextItem($item);
        if (! $item instanceof DOMNode) {
            throw new DynamicError('XPTY0004', 'The context item is not a node.');
        }

        return $item;
    }

    private static function nodeName(string $function, DOMNode $node): string
    {
        $named = $node instanceof DOMElement || $node instanceof DOMAttr;

        return match ($function) {
            'name' => $named ? $node->nodeName : ($node instanceof DOMProcessingInstruction ? $node->target : ''),
            'local-name' => $named ? (string) $node->localName : ($node instanceof DOMProcessingInstruction ? $node->target : ''),
            default => $named ? ($node->namespaceURI ?? '') : '',
        };
    }

    private static function stringSearch(string $function, string $text, string $search): string|bool
    {
        switch ($function) {
            case 'substring-before':
                $at = $search === '' ? false : strpos($text, $search);

                return $at === false ? '' : substr($text, 0, $at);
            case 'substring-after':
                if ($search === '') {
                    return $text;
                }
                $at = strpos($text, $search);

                return $at === false ? '' : substr($text, $at + strlen($search));
            case 'contains':
                return str_contains($text, $search);
            case 'starts-with':
                return str_starts_with($text, $search);
            default:
                return str_ends_with($text, $search);
        }
    }

    /**
     * fn:substring: the characters at positions p with round(start) <= p < round(start) + round(length).
     */
    private static function substring(string $text, float $start, ?float $length): string
    {
        $first = self::roundDouble($start);
        $end = $length === null ? INF : $first + self::roundDouble($length);
        if (is_nan($first) || is_nan($end)) {
            return '';
        }
        $from = max(1.0, $first);
        $characters = mb_strlen($text, 'UTF-8');
        $to = min((float) $characters + 1, $end);
        if ($to <= $from) {
            return '';
        }

        return mb_substr($text, (int) $from - 1, (int) ($to - $from), 'UTF-8');
    }

    /**
     * fn:round on a double: to the nearest integer, a half toward positive infinity; -0.5 <= x < 0 gives -0.
     */
    private static function roundDouble(float $value): float
    {
        if (is_nan($value) || is_infinite($value) || $value == 0.0) {
            return $value;
        }
        if ($value < 0 && $value >= -0.5) {
            return -0.0;
        }
        $floor = floor($value);

        return $value - $floor >= 0.5 ? $floor + 1 : $floor;
    }

    private static function translate(string $text, string $map, string $translation): string
    {
        $from = mb_str_split($map, 1, 'UTF-8');
        $to = mb_str_split($translation, 1, 'UTF-8');
        $replacements = [];
        foreach ($from as $index => $character) {
            if (! isset($replacements[$character])) {
                $replacements[$character] = $to[$index] ?? '';
            }
        }
        $result = '';
        foreach (mb_str_split($text, 1, 'UTF-8') as $character) {
            $result .= $replacements[$character] ?? $character;
        }

        return $result;
    }

    /**
     * @param list<mixed> $values
     */
    private static function sum(array $values): mixed
    {
        $total = null;
        foreach ($values as $value) {
            if ($value instanceof DOMNode) {
                $value = new Untyped(Value::nodeString($value));
            }
            if ($value instanceof Untyped) {
                $value = Value::cast($value, 'double');
            }
            if (! Value::isNumeric($value)) {
                throw new DynamicError('FORG0006', 'fn:sum() can only add numbers, found ' . Value::typeName($value) . '.');
            }
            $total = $total === null ? $value : self::calculate('+', $total, $value);
        }

        return $total;
    }

    private static function rounding(string $function, mixed $value): mixed
    {
        if ($value instanceof Untyped) {
            $value = Value::cast($value, 'double');
        }
        if (! Value::isNumeric($value)) {
            throw new DynamicError('XPTY0004', "The argument of $function() must be a number.");
        }
        if (is_int($value)) {
            return $function === 'abs' ? ($value === PHP_INT_MIN ? Decimal::fromInt($value)->negate()->toInteger() : abs($value)) : $value;
        }
        if ($value instanceof Decimal) {
            $result = match ($function) {
                'abs' => $value->abs(),
                'round' => $value->round(),
                'floor' => $value->floor(),
                default => $value->ceiling(),
            };

            return $value->integer ? $result->toInteger() : $result;
        }
        /** @var float $value */
        return match ($function) {
            'abs' => abs($value),
            'round' => self::roundDouble($value),
            'floor' => floor($value),
            default => ceil($value),
        };
    }

    /**
     * @param list<mixed> $values
     *
     * @return list<mixed>
     */
    private static function distinctValues(array $values): array
    {
        // The first of equal values is kept, as it is (an xs:untypedAtomic stays one); untyped compares as string.
        $result = [];
        $keys = [];
        foreach ($values as $item) {
            $item = Value::atomizeItem($item);
            $key = $item instanceof Untyped ? $item->value : $item;
            foreach ($keys as $seen) {
                try {
                    $comparison = Value::compare($key, $seen);
                } catch (DynamicError) {
                    continue;
                }
                $bothNaN = is_float($key) && is_nan($key) && is_float($seen) && is_nan($seen);
                if ($comparison === 0 || $bothNaN) {
                    continue 2;
                }
            }
            $result[] = $item;
            $keys[] = $key;
        }

        return $result;
    }

    /**
     * @param list<mixed> $values
     *
     * @return list<DOMDocument>
     */
    private function document(array $values): array
    {
        $documents = [];
        foreach ($values as $item) {
            $name = Value::string($item);
            $path = $this->documents[$name] ?? throw new DynamicError('FODC0002', "Document \"$name\" is not available.");
            if (! isset($this->loadedDocuments[$name])) {
                // read as text: a file of the package, not loaded through the entity loader an application may have
                // set for its documents
                $xml = @file_get_contents($path);
                $document = new DOMDocument();
                [$loaded] = Xml::collect(static fn(): bool => is_string($xml) && $xml !== '' && $document->loadXML($xml, LIBXML_NONET | LIBXML_NOCDATA));
                if (! $loaded) {
                    throw new DynamicError('FODC0002', "Document \"$name\" cannot be read.");
                }
                $this->loadedDocuments[$name] = $document;
                $this->tree($document);
            }
            $documents[] = $this->loadedDocuments[$name];
        }

        return $documents;
    }
}
