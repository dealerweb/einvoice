<?php

declare(strict_types=1);

namespace Dealerweb\EInvoice\Model;

use Dealerweb\EInvoice\Generation\Node;
use Dealerweb\EInvoice\Generation\Tree;
use Dealerweb\EInvoice\Invoice;
use Dealerweb\EInvoice\Rules;
use LogicException;

/**
 * Reads the nodes of a document (Generation\TreeReader) into the model: each value to the property of its field, the
 * repeating elements on its way to the index of a list - or, where the model has the element once, to its first
 * instance (the first contact of a party in CII is contact, the others additionalContacts).
 *
 * What the model has no place for is not dropped silently: unread() names it by its path in the document. A value the
 * model has once and the document repeats with the same value (the VAT point date of each VAT breakdown in CII) is no
 * loss, nor is what the writer adds by itself: a value the syntax requires where EN 16931 has none (the card network
 * of UBL), a default that stands for no value (the project name "Project reference" of CII).
 *
 * @internal used by Model\Reader
 *
 * @phpstan-type Step array{property: string, list: bool, element: string|null, offset: int}
 * @phpstan-type Target array{steps: list<Step>, property: string, element: string|null}
 */
final class NodeReader
{
    /** @var array<string, array<string, list<Target>>> the properties each field stands for, by syntax */
    private static array $targets = [];

    /** @var array<string, array<string, true>> the elements the lists of the model bind, by syntax */
    private static array $elements = [];

    /** @var list<string> the values the model has no place for */
    private array $unread = [];

    private readonly Mapping $mapping;

    public function __construct(private readonly Tree $tree)
    {
        $this->mapping = Mapping::of($tree);
    }

    /**
     * @param list<Node> $nodes the children of the root element
     */
    public function read(array $nodes): Invoice
    {
        $this->unread = [];
        $invoice = new Invoice();
        $this->children($invoice, $nodes, [], '');
        self::normalize($invoice);

        return $invoice;
    }

    /**
     * The values the last read() found no place for: "path = value".
     *
     * @return list<string>
     */
    public function unread(): array
    {
        return $this->unread;
    }

    /**
     * @param list<Node> $nodes
     * @param list<array{0: string, 1: int}> $chain the repeating elements above, each with its index
     */
    private function children(Invoice $invoice, array $nodes, array $chain, string $path): void
    {
        $counts = [];
        foreach ($nodes as $node) {
            $row = $this->tree->node($node->id);
            $name = $row['name'];
            $nodeChain = $chain;
            $nodePath = ($row['virtual'] ?? false) ? $path : "$path/$name";
            // A repeating element, or one a list of the model binds (the ID of a party next to its GlobalIDs).
            $key = $this->mapping->alternativeOf($node->id);
            if (! str_starts_with($name, '@') && ! ($row['virtual'] ?? false) && ($row['max'] !== 1 || isset($this->elements()[$key]))) {
                $counts[$key] = ($counts[$key] ?? 0) + 1;
                $nodeChain[] = [$key, $counts[$key] - 1];
                $nodePath .= "[{$counts[$key]}]";
            }
            if ($node->value !== null && $this->tree->isLeaf($node->id) && ! isset($row['fixed']) && ! $this->derived($node->id)) {
                $this->value($invoice, $node, $nodeChain, $nodePath);
            }
            // The attributes of an element lie below it, in its instance.
            $this->children($invoice, $node->children, $nodeChain, $nodePath);
        }
    }

    /**
     * @param list<array{0: string, 1: int}> $chain
     */
    private function value(Invoice $invoice, Node $node, array $chain, string $path): void
    {
        $row = $this->tree->node($node->id);
        $targets = $this->targets()[$this->tree->field($node->id)] ?? [];
        if (isset($row['default']) && ($targets === [] || trim((string) $node->value) === $row['default'])) {
            // What the writer fills in: a value the syntax requires without a field of the model, or the default that
            // stands for no value.
            return;
        }
        $value = $this->text($node, $path);
        if ($value === null) {
            return;
        }
        $duplicate = false;
        foreach ($targets as $target) {
            $indices = self::indices($target, $chain);
            if ($indices !== null) {
                $this->set($invoice, $target, $indices, $value, $path);

                return;
            }
            // Once in the model, repeated in the document: the same value again is no loss.
            $duplicate = $duplicate || self::holds($invoice, $target, self::indices($target, $chain, true), $value);
        }
        if (! $duplicate) {
            $this->unread[] = "$path = " . self::show($value);
        }
    }

    /**
     * The indices of the lists of a property for a node: each list binds a repeating element on the way (its index,
     * less the items the object gives by single properties), a list of values the element of the value; every other
     * repeating element has to be the first - null where the node does not belong to the property.
     *
     * @param Target $target
     * @param list<array{0: string, 1: int}> $chain
     * @param bool $first the indices of the first instances instead (for a value repeated with the same value)
     * @return list<int>|null
     */
    private static function indices(array $target, array $chain, bool $first = false): ?array
    {
        $bound = [];
        $offsets = [];
        foreach ($target['steps'] as $position => $step) {
            if ($step['list'] && $step['element'] !== null) {
                $bound[$step['element']] = $position;
                $offsets[$position] = $step['offset'];
            }
        }
        if ($target['element'] !== null) {
            $bound[$target['element']] = count($target['steps']);
            $offsets[count($target['steps'])] = 0;
        }
        $indices = [];
        foreach ($chain as [$key, $index]) {
            if (isset($bound[$key])) {
                $position = $bound[$key];
                $indices[$position] = $first ? 0 : $index - $offsets[$position];
                if ($indices[$position] < 0) {
                    return null;
                }
            } elseif ($index !== 0 && ! $first) {
                return null;
            }
        }
        if (count($indices) !== count($bound)) {
            return null;
        }
        ksort($indices);

        return array_values($indices);
    }

    /**
     * Sets a value at the path of a property: the objects on the way made, the items of lists added up to the index.
     *
     * @param Target $target
     * @param list<int> $indices
     */
    private function set(Invoice $invoice, array $target, array $indices, mixed $value, string $path): void
    {
        $object = $invoice;
        $next = 0;
        foreach ($target['steps'] as $step) {
            $property = $step['property'];
            if (! $step['list']) {
                $object = $object->$property;
                continue;
            }
            $index = $indices[$next++];
            /** @var list<Element> $items */
            $items = $object->$property;
            $class = $object::listClass($property) ?? throw new LogicException("$property is no list of objects.");
            while (count($items) <= $index) {
                $items[] = new $class();
            }
            $object->$property = $items;
            $object = $items[$index];
        }
        $property = $target['property'];
        if ($target['element'] !== null) {
            /** @var list<mixed> $values */
            $values = $object->$property;
            $values[$indices[$next]] = $value;
            ksort($values);
            $object->$property = array_values($values);

            return;
        }
        if ($object->$property !== null) {
            $this->unread[] = "$path = " . self::show($value) . ' (a second value)';

            return;
        }
        $object->$property = $value;
    }

    /**
     * Whether the model holds the value at the first instances of the path.
     *
     * @param Target $target
     * @param list<int>|null $indices
     */
    private static function holds(Invoice $invoice, array $target, ?array $indices, mixed $value): bool
    {
        if ($indices === null || $target['element'] !== null) {
            return false;
        }
        $object = $invoice;
        $next = 0;
        foreach ($target['steps'] as $step) {
            $object = $object->{$step['property']};
            if ($step['list']) {
                /** @var list<Element> $object */
                $object = $object[$indices[$next++]] ?? null;
            }
            if (! $object instanceof Element) {
                return false;
            }
        }

        return $object->{$target['property']} === $value;
    }

    /**
     * The value of a node as the model holds it: a date as YYYY-MM-DD (CII: format 102) or with time and zone (CII:
     * format 205), an indicator as bool, a code of another list as the code of EN 16931 (the VAT point date code of
     * CII, UNTDID 2475, as one of UNTDID 2005), else the text. Null for an indicator that is none (xs:boolean) - the
     * model holds true or false only, unread() names it.
     */
    private function text(Node $node, string $path): string|bool|null
    {
        $row = $this->tree->node($node->id);
        $value = (string) $node->value;
        if (($row['type'] ?? null) === 'Indicator') {
            $indicator = Rules::indicator($value);
            if ($indicator === null) {
                $this->unread[] = "$path = " . Rules::excerpt($value) . ' - no indicator (true or false), left out';
            }

            return $indicator;
        }
        if (isset($row['codes'])) {
            $code = array_search(trim($value), $row['codes'], true);

            return $code === false ? $value : (string) $code;
        }
        if (($row['type'] ?? null) !== 'Date') {
            return $value;
        }
        $format = null;
        foreach ($node->children as $child) {
            if ($this->tree->node($child->id)['name'] === '@format') {
                $format = trim((string) $child->value);
            }
        }
        $trimmed = trim($value);
        $date = $this->tree->dateFormat() === 'Ymd' ? '/^(\d{4})(\d{2})(\d{2})$/' : '/^(\d{4})-(\d{2})-(\d{2})$/';
        if (($format ?? '102') === '102' && preg_match($date, $trimmed, $match) === 1
            && checkdate((int) $match[2], (int) $match[3], (int) $match[1])) {
            return "$match[1]-$match[2]-$match[3]";
        }
        if ($format === '205' && preg_match('/^(\d{4})(\d{2})(\d{2})(\d{2})(\d{2})([+-]\d{2})(\d{2})$/', $trimmed, $match) === 1
            && checkdate((int) $match[2], (int) $match[3], (int) $match[1])) {
            return "$match[1]-$match[2]-$match[3]T$match[4]:$match[5]$match[6]:$match[7]";
        }
        $this->unread[] = "$path = " . Rules::excerpt($value) . ' - ' . ($this->tree->dateFormat() === 'Ymd' ? 'no date of the format ' . ($format ?? '102') : 'no date (YYYY-MM-DD)') . ', kept as it is';

        return $value;
    }

    private static function show(mixed $value): string
    {
        return is_bool($value) ? var_export($value, true) : (is_scalar($value) ? Rules::excerpt((string) $value) : get_debug_type($value));
    }

    /**
     * An attribute the generator derives: the currency of an amount, the format of a date.
     */
    private function derived(string $id): bool
    {
        $row = $this->tree->node($id);
        $parent = $row['parent'] === null ? null : $this->tree->node($row['parent']);

        return ($row['name'] === '@currencyID' && isset($parent['currency'])) || ($row['name'] === '@format' && ($parent['type'] ?? null) === 'Date');
    }

    /**
     * What the generator adds by itself is no content: the base quantity of the gross price where it is the one of the
     * net price.
     */
    private static function normalize(Invoice $invoice): void
    {
        foreach ($invoice->lines as $line) {
            self::normalizeLine($line);
        }
    }

    private static function normalizeLine(Line $line): void
    {
        if ($line->grossPriceBaseQuantity !== null && $line->grossPriceBaseQuantity === $line->priceBaseQuantity && $line->grossPriceBaseUnit === $line->priceBaseUnit) {
            $line->grossPriceBaseQuantity = null;
            $line->grossPriceBaseUnit = null;
        }
        foreach ($line->subLines as $subLine) {
            self::normalizeLine($subLine);
        }
    }

    /**
     * The properties each field id stands for, with the lists on their way: from the mapping, once per syntax. The
     * fields of a sub line lie in the tree with the level as suffix (Tree::recursion()): "BT-126#2" is the identifier
     * of a sub line of a sub line - lines, subLines, subLines, id.
     *
     * @return array<string, list<Target>>
     */
    private function targets(): array
    {
        $syntax = $this->tree->syntax->value;
        if (! isset(self::$targets[$syntax])) {
            $targets = [];
            $this->collect($this->mapping->invoice, [], [], $targets);
            $recursion = $this->tree->recursion();
            if ($recursion !== null) {
                $targets = [...$targets, ...self::levels($targets, $recursion['node'], $recursion['depth'])];
            }
            self::$targets[$syntax] = $targets;
        }

        return self::$targets[$syntax];
    }

    /**
     * The targets of the fields of the sub lines on each level: those of a line, with the lists of sub lines in
     * between and the elements of the level.
     *
     * @param array<string, list<Target>> $targets
     * @return array<string, list<Target>>
     */
    private static function levels(array $targets, string $node, int $depth): array
    {
        $levels = [];
        foreach ($targets as $id => $list) {
            foreach ($list as $target) {
                if (($target['steps'][0]['property'] ?? null) !== 'lines') {
                    continue;
                }
                for ($level = 1; $level <= $depth; $level++) {
                    $steps = [$target['steps'][0]];
                    for ($inner = 1; $inner <= $level; $inner++) {
                        $steps[] = ['property' => 'subLines', 'list' => true, 'element' => "$node#$inner", 'offset' => 0];
                    }
                    foreach (array_slice($target['steps'], 1) as $step) {
                        $steps[] = ['element' => $step['element'] === null ? null : "{$step['element']}#$level"] + $step;
                    }
                    $levels["$id#$level"][] = [
                        'steps' => $steps,
                        'property' => $target['property'],
                        'element' => $target['element'] === null ? null : "{$target['element']}#$level",
                    ];
                }
            }
        }

        return $levels;
    }

    /**
     * The elements the lists of the model bind, by the key of their alternatives.
     *
     * @return array<string, true>
     */
    private function elements(): array
    {
        $syntax = $this->tree->syntax->value;
        if (! isset(self::$elements[$syntax])) {
            $elements = [];
            foreach ($this->targets() as $targets) {
                foreach ($targets as $target) {
                    foreach ([...array_column($target['steps'], 'element'), $target['element']] as $element) {
                        if ($element !== null) {
                            $elements[$element] = true;
                        }
                    }
                }
            }
            self::$elements[$syntax] = $elements;
        }

        return self::$elements[$syntax];
    }

    /**
     * @param list<Step> $steps
     * @param list<MappedObject> $above the objects on the way, against a recursive one (sub lines)
     * @param array<string, list<Target>> $targets
     */
    private function collect(MappedObject $object, array $steps, array $above, array &$targets): void
    {
        foreach ($object->properties as $property) {
            if ($property->object === null) {
                foreach ($this->alternatives($property->ids) as $id) {
                    $targets[$id][] = ['steps' => $steps, 'property' => $property->name, 'element' => $property->list ? $property->node : null];
                }
                continue;
            }
            // Sub lines are a line again, read by the levels of the tree (targets()); a list without an element in the
            // syntax has no place.
            if (in_array($property->object, [...$above, $object], true) || ($property->list && $property->node === null && $property->object->class !== 'Identifier')) {
                continue;
            }
            $element = $property->node;
            if ($property->list && $element === null) {
                // Each identifier of a party is an element of its own: the ID or its GlobalID (CII).
                $element = $this->mapping->alternativeOf($property->object->properties['value']->ids[0]);
            }
            $this->collect($property->object, [...$steps, ['property' => $property->name, 'list' => $property->list, 'element' => $element, 'offset' => $property->offset]], [...$above, $object], $targets);
        }
    }

    /**
     * The ids of the fields and those the field list links to them: the GlobalID of an identifier, the ProprietaryID of
     * an account (CII).
     *
     * @param list<string> $ids
     * @return list<string>
     */
    private function alternatives(array $ids): array
    {
        $all = [];
        foreach ($ids as $id) {
            if (! $this->tree->has($id)) {
                continue;
            }
            $all[] = $id;
            foreach (['global', 'proprietary'] as $key) {
                if (isset($this->tree->node($id)[$key])) {
                    $all[] = (string) $this->tree->node($id)[$key];
                }
            }
        }

        return $all;
    }
}
