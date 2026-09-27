<?php

declare(strict_types=1);

namespace Dealerweb\EInvoice\Validation\Schematron;

use Dealerweb\EInvoice\Validation\XPath\DynamicError;
use Dealerweb\EInvoice\Validation\XPath\Evaluator;
use Dealerweb\EInvoice\Validation\XPath\Invariants;
use Dealerweb\EInvoice\Validation\XPath\Lazy;
use Dealerweb\EInvoice\Validation\XPath\Tree;
use DOMAttr;
use DOMDocument;
use DOMElement;
use DOMNode;
use DOMProcessingInstruction;

/**
 * Runs a compiled Schematron rule set (resources/compiled/validation) on a document and returns what the official
 * stylesheet reports: the failed asserts and successful reports, in its order.
 *
 * The document is walked like the stylesheet walks it - the document node, each element, its attributes, its
 * children (SchXslt visits text nodes, too). At each node the rules are tried in Saxon's order (highest priority
 * first); a rule whose pattern cannot be evaluated for the node does not match it (XSLT's rule for errors in
 * patterns). The first matching rule of each Schematron pattern fires: its variables, conditions and findings run as
 * compiled from Saxon's plan. SchXslt keeps all patterns in one mode, so a node passes the rules of all patterns
 * before the walk goes on; the skeleton walks the document once per pattern. Findings are reported grouped by
 * pattern in the order of the patterns, within a pattern in the order they were made. An expression that fails ends
 * the run with that error, as it ends Saxon's transformation.
 *
 * @internal
 *
 * @phpstan-type Check array{0: string, 1: string|null, 2: string|null, 3: string|null, 4: string, 5: list<list<mixed>>}
 * @phpstan-type Rule array{mode: int, pattern: int, priority: float|int, match: list<mixed>, keys: list<string>, action: list<mixed>, checks: list<Check>}
 * @phpstan-type RuleSet array{source: string, sha1: string, saxon: string, compiler: string, title: string, documents: array<string, string>, globals: array<string, list<mixed>>, functions: array<string, list<mixed>>, patterns: list<array{id: string|null, name: string|null}>, modes: list<array{node: bool}>, rules: list<Rule>}
 */
final class Engine
{
    /** @var array<int, list<Rule>> the rules of each mode, in precedence order */
    private array $rules = [];

    /**
     * @var array<int, array<string, list<int>>> per mode: key of a node => the rules to try - only for the keys the
     *                                           rules name and the wildcards, so that it does not grow with the names
     *                                           of the documents
     */
    private array $candidates = [];

    /** @var array<int, array<string, true>> per mode: the keys its rules name */
    private array $named = [];

    /**
     * @var array<int, list<string>> the modes whose rules all match elements of a name: those names ("{uri}local") -
     *                               their elements are visited in document order instead of walking the document (the
     *                               skeleton walks it once per pattern)
     */
    private array $direct = [];

    /**
     * @var array<int, array{int, DOMNode}> the nodes whose position among their siblings the run has counted, with
     *                                      the node - held, so that its id is not handed out again
     */
    private array $positions = [];

    /** @var array<string, list<mixed>> the functions of the rule set, their invariant paths marked */
    private readonly array $functions;

    /** @var array<string, list<mixed>> the global variables of the rule set, their invariant paths marked */
    private readonly array $globals;

    /**
     * @param RuleSet $ruleSet compiled rule set (resources/compiled/validation)
     * @param string $directory folder the paths of the documents document() may load are relative to
     */
    public function __construct(private readonly array $ruleSet, private readonly string $directory = '')
    {
        // paths that depend on the document alone are computed once per document (Invariants)
        $invariants = new Invariants();
        $this->functions = $invariants->mark($ruleSet['functions']);
        $this->globals = $invariants->mark($ruleSet['globals']);
        foreach (array_keys($ruleSet['modes']) as $mode) {
            $this->rules[$mode] = [];
            $this->named[$mode] = [];
        }
        foreach ($invariants->mark($ruleSet['rules']) as $rule) {
            $this->rules[$rule['mode']][] = $rule;
            foreach ($rule['keys'] as $key) {
                $this->named[$rule['mode']][$key] = true;
            }
        }
        foreach ($this->named as $mode => $keys) {
            $names = [];
            foreach (array_keys($keys) as $key) {
                if (! str_starts_with($key, 'E{')) {
                    continue 2;
                }
                $names[] = substr($key, 1);
            }
            $this->direct[$mode] = $names;
        }
    }

    /**
     * @return list<Finding>
     *
     * @throws DynamicError the rules cannot be applied to this document (Saxon would stop as well)
     */
    public function run(DOMDocument $document): array
    {
        $documents = [];
        foreach ($this->ruleSet['documents'] as $name => $file) {
            $documents[$name] = $this->directory . '/' . $file;
        }
        $evaluator = new Evaluator($this->functions, $this->globals, $documents);
        $evaluator->bind($document);
        $skeleton = $this->ruleSet['compiler'] === 'skeleton';

        // findings per pattern
        $buckets = array_fill(0, count($this->ruleSet['patterns']), []);
        $this->positions = [];
        $tree = $evaluator->tree($document);
        try {
            foreach ($this->rules as $mode => $rules) {
                if (! isset($this->direct[$mode])) {
                    $this->walk($document, $evaluator, $mode, $rules, ! $skeleton, $skeleton, $buckets);

                    continue;
                }
                foreach (self::elementsNamed($tree, $this->direct[$mode]) as $element) {
                    $this->visit($element, $evaluator, $mode, $rules, $skeleton, $buckets);
                }
            }
        } finally {
            // the ids of the nodes are handed out anew once they are gone
            $this->positions = [];
        }

        return array_merge(...$buckets);
    }

    /**
     * The elements of the names, in document order - the order a walk meets them in.
     *
     * @param list<string> $names "{uri}local"
     *
     * @return list<DOMElement>
     */
    private static function elementsNamed(Tree $tree, array $names): array
    {
        if (count($names) === 1) {
            return $tree->elements[$names[0]] ?? [];
        }
        $elements = [];
        foreach ($names as $name) {
            foreach ($tree->elements[$name] ?? [] as $element) {
                $elements[$tree->index[spl_object_id($element)]] = $element;
            }
        }
        ksort($elements);

        return array_values($elements);
    }

    /**
     * @param list<Rule> $rules
     * @param array<int, list<Finding>> $buckets findings per pattern
     */
    private function walk(DOMNode $node, Evaluator $evaluator, int $mode, array $rules, bool $texts, bool $skeleton, array &$buckets): void
    {
        $this->visit($node, $evaluator, $mode, $rules, $skeleton, $buckets);
        if ($node instanceof DOMElement) {
            foreach ($node->attributes ?? [] as $attribute) {
                $this->visit($attribute, $evaluator, $mode, $rules, $skeleton, $buckets);
            }
        }
        if ($node instanceof DOMElement || $node instanceof DOMDocument) {
            foreach ($node->childNodes as $child) {
                if ($child instanceof DOMElement) {
                    $this->walk($child, $evaluator, $mode, $rules, $texts, $skeleton, $buckets);
                } elseif ($texts && in_array($child->nodeType, [XML_TEXT_NODE, XML_CDATA_SECTION_NODE, XML_COMMENT_NODE, XML_PI_NODE], true)) {
                    $this->visit($child, $evaluator, $mode, $rules, $skeleton, $buckets);
                }
            }
        }
    }

    /**
     * The rules of the mode at one node: the first matching rule of each pattern fires.
     *
     * @param list<Rule> $rules
     * @param array<int, list<Finding>> $buckets findings per pattern
     */
    private function visit(DOMNode $node, Evaluator $evaluator, int $mode, array $rules, bool $skeleton, array &$buckets): void
    {
        $fired = [];
        foreach ($this->candidates($mode, $rules, $node) as $index) {
            $rule = $rules[$index];
            if (isset($fired[$rule['pattern']])) {
                continue;
            }
            try {
                if (! $evaluator->matches($rule['match'], $node)) {
                    continue;
                }
            } catch (DynamicError) {
                continue;
            }
            $fired[$rule['pattern']] = true;
            $this->execute($rule['action'], $rule['checks'], $node, [], $evaluator, $skeleton, $buckets[$rule['pattern']]);
        }
    }

    /**
     * The rules that can match a node, in the order they are tried.
     *
     * @param list<Rule> $rules
     *
     * @return list<int>
     */
    private function candidates(int $mode, array $rules, DOMNode $node): array
    {
        $key = match ($node->nodeType) {
            XML_ELEMENT_NODE => 'E{' . ($node->namespaceURI ?? '') . '}' . $node->localName,
            XML_ATTRIBUTE_NODE => 'A{' . ($node->namespaceURI ?? '') . '}' . $node->localName,
            XML_DOCUMENT_NODE => 'D',
            XML_TEXT_NODE, XML_CDATA_SECTION_NODE => 'T',
            default => '*',
        };
        $wildcard = match ($node->nodeType) {
            XML_ELEMENT_NODE => 'E*',
            XML_ATTRIBUTE_NODE => 'A*',
            default => $key,
        };
        // a name no rule names: the rules of any node of its kind
        if (! isset($this->named[$mode][$key])) {
            $key = $wildcard;
        }
        if (isset($this->candidates[$mode][$key])) {
            return $this->candidates[$mode][$key];
        }
        $found = [];
        foreach ($rules as $index => $rule) {
            foreach ($rule['keys'] as $ruleKey) {
                if ($ruleKey === $key || $ruleKey === $wildcard || $ruleKey === '*') {
                    $found[] = $index;
                    break;
                }
            }
        }

        return $this->candidates[$mode][$key] = $found;
    }

    /**
     * @param list<mixed> $action
     * @param list<Check> $checks
     * @param array<int, list<mixed>|Lazy> $frame
     * @param list<Finding> $findings
     */
    private function execute(array $action, array $checks, DOMNode $node, array $frame, Evaluator $evaluator, bool $skeleton, array &$findings): void
    {
        switch ($action[0]) {
            case 'seq':
                foreach ($action[1] as $part) {
                    $this->execute($part, $checks, $node, $frame, $evaluator, $skeleton, $findings);
                }

                return;
            case 'let':
                $frame[$action[1]] = $evaluator->bindVariable($action[2], $action[4], $node, 1, 1, $frame);
                $this->execute($action[3], $checks, $node, $frame, $evaluator, $skeleton, $findings);

                return;
            case 'choose':
                $count = count($action[1]);
                for ($i = 0; $i < $count; $i += 2) {
                    if ($evaluator->test($action[1][$i], $node, $frame)) {
                        $this->execute($action[1][$i + 1], $checks, $node, $frame, $evaluator, $skeleton, $findings);

                        return;
                    }
                }

                return;
            case 'finding':
                [$kind, $id, $flag, $role, $test, $message] = $checks[$action[1]];
                $text = '';
                foreach ($message as $part) {
                    $text .= $part[0] === 'str' ? $part[1] : $evaluator->text($part, $node, $frame);
                }
                $findings[] = new Finding($kind, $id, $flag, $role, $test, $text, $skeleton ? $this->skeletonLocation($node) : $this->schxsltLocation($node), $node);

                return;
            case 'nop':
                return;
        }

        throw new DynamicError('XTSE0010', "Unknown action {$action[0]}.");
    }

    /**
     * Location as the ISO skeleton writes it (mode schematron-select-full-path):
     * /*:Invoice[namespace-uri()='urn:...'][1]/*:ID[namespace-uri()='urn:...'][1], attributes as /@name.
     */
    private function skeletonLocation(DOMNode $node): string
    {
        if ($node instanceof DOMAttr) {
            $parent = $node->ownerElement;
            $path = $parent === null ? '' : $this->skeletonLocation($parent);
            if ($node->namespaceURI === null || $node->namespaceURI === '') {
                return $path . '/@' . $node->nodeName;
            }

            return $path . "/@*[local-name()='{$node->localName}' and namespace-uri()='{$node->namespaceURI}']";
        }
        if (! $node instanceof DOMElement) {
            return '';
        }
        $parent = $node->parentNode;
        $path = $parent instanceof DOMElement ? $this->skeletonLocation($parent) : '';
        $namespace = $node->namespaceURI ?? '';
        $step = $namespace === '' ? $node->nodeName : "*:{$node->localName}[namespace-uri()='$namespace']";

        return "$path/$step" . '[' . $this->position($node) . ']';
    }

    /**
     * Location as SchXslt writes it (schxslt:location): /Q{urn:...}Invoice[1]/Q{urn:...}ID[1], attributes as
     * /@Q{}name.
     */
    private function schxsltLocation(DOMNode $node): string
    {
        $segments = [];
        for ($current = $node; $current !== null && ! $current instanceof DOMDocument; $current = $current instanceof DOMAttr ? $current->ownerElement : $current->parentNode) {
            $segments[] = $current instanceof DOMAttr
                ? '@Q{' . ($current->namespaceURI ?? '') . '}' . $current->localName
                : match (true) {
                    $current instanceof DOMElement => 'Q{' . ($current->namespaceURI ?? '') . '}' . $current->localName . '[' . $this->position($current) . ']',
                    $current instanceof DOMProcessingInstruction => 'processing-instruction("' . $current->target . '")[' . $this->position($current) . ']',
                    $current->nodeType === XML_COMMENT_NODE => 'comment()[' . $this->position($current) . ']',
                    default => 'text()[' . $this->position($current) . ']',
                };
        }

        return '/' . implode('/', array_reverse($segments));
    }

    /**
     * The number xsl:number level="single" gives a node: the siblings of the same kind before it - elements of the
     * same name, processing instructions of the same target - plus one. Counted once for all children of a parent:
     * findings at many siblings would otherwise count the siblings before each of them.
     */
    private function position(DOMNode $node): int
    {
        $id = spl_object_id($node);
        if (! isset($this->positions[$id])) {
            $counts = [];
            for ($sibling = $node->parentNode->firstChild ?? $node; $sibling !== null; $sibling = $sibling->nextSibling) {
                $kind = match (true) {
                    $sibling instanceof DOMElement => '{' . ($sibling->namespaceURI ?? '') . '}' . $sibling->localName,
                    $sibling instanceof DOMProcessingInstruction => '?' . $sibling->target,
                    default => (string) $sibling->nodeType,
                };
                $counts[$kind] = ($counts[$kind] ?? 0) + 1;
                $this->positions[spl_object_id($sibling)] = [$counts[$kind], $sibling];
            }
        }

        return $this->positions[$id][0];
    }
}
