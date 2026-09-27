<?php

declare(strict_types=1);

namespace Dealerweb\EInvoice\Generation;

use DOMDocument;
use DOMElement;
use InvalidArgumentException;

/**
 * Writes nodes of a tree (Tree) as a document of a profile: every element in the order of the schema, values as given.
 * What the specification fixes is added where it is missing - the ChargeIndicator of an allowance or charge, the
 * schemeID of a tax registration, the TypeCode of an additional document, the format of a date, the currency of an
 * amount -, and so are the containers the profile's schema requires although they may be empty
 * (ram:ApplicableHeaderTradeDelivery). A value of several fields (the note of UBL with its subject code) is written as
 * the one text of its element.
 *
 * A node has to be a child of the node it is given in and belong to the profile; a node that may occur once must not be
 * given twice, nor an element that several nodes share more often than its schema allows (the reason code of a charge
 * as BT-105 and BT-177).
 *
 * @internal
 */
final class TreeWriter
{
    private DOMDocument $document;

    /** @var array<string, string> values of the currency nodes (BT-5, BT-6) */
    private array $currencies = [];

    /**
     * @param string $profile the profile of the tree: MINIMUM, BASIC WL, BASIC, EN16931, EXTENDED or XRECHNUNG in CII;
     *                        CORE, XRECHNUNG or PEPPOL in UBL
     */
    public function __construct(private readonly Tree $tree, private readonly string $profile)
    {
        $this->document = new DOMDocument('1.0', 'UTF-8');
    }

    /**
     * @param list<Node> $nodes the children of the root element
     * @throws InvalidArgumentException a node is not allowed where it is given
     */
    public function write(array $nodes): DOMDocument
    {
        $this->document = new DOMDocument('1.0', 'UTF-8');
        $this->document->formatOutput = true;
        $root = $this->tree->root();
        $element = $this->document->createElementNS($root['namespace'], $root['name']);
        foreach ($this->tree->declared() as $prefix) {
            $element->setAttributeNS('http://www.w3.org/2000/xmlns/', "xmlns:$prefix", $this->tree->namespace($prefix));
        }
        $this->document->appendChild($element);

        $this->currencies = [];
        $this->collectCurrencies($nodes);
        $this->writeChildren($element, null, $nodes);

        return $this->document;
    }

    /**
     * Writes the given children of an element, adds the implied ones and puts them in the order of the schema.
     *
     * @param list<Node> $nodes
     */
    private function writeChildren(DOMElement $element, ?string $parent, array $nodes): void
    {
        $counts = [];
        $shared = [];
        foreach ($nodes as $node) {
            $row = $this->tree->node($node->id);
            if ($row['parent'] !== $parent) {
                throw new InvalidArgumentException($this->label($node) . ' is not a part of ' . ($parent ?? 'the document') . '.');
            }
            if (! in_array($this->profile, $row['profiles'], true)) {
                throw new InvalidArgumentException($this->label($node) . " is not a part of the profile {$this->profile}. " . $this->tree->inProfiles($node->id));
            }
            $counts[$node->id] = ($counts[$node->id] ?? 0) + 1;
            $max = $row['maxima'][$this->profile] ?? $row['max'];
            if ($max > 0 && $counts[$node->id] > $max) {
                throw new InvalidArgumentException($this->label($node) . " may occur at most $max time(s) in the profile {$this->profile}.");
            }
            if (isset($row['occurs'][$this->profile])) {
                $shared[$row['name']][] = $node->given ?? $node->id;
                $occurs = $row['occurs'][$this->profile];
                if ($occurs > 0 && count($shared[$row['name']]) > $occurs) {
                    throw new InvalidArgumentException("{$row['name']} may occur at most $occurs time(s) in the profile {$this->profile}, given for " . implode(', ', array_unique($shared[$row['name']])) . '.');
                }
            }
            if ($node->value !== null && $node->children !== [] && ! $this->onlyAttributes($node->children)) {
                throw new InvalidArgumentException("{$node->id} has a value and child elements.");
            }
        }

        $nodes = [...$nodes, ...$this->implied($parent, $nodes)];

        // Attributes first, then the elements in schema order; nodes at the same position keep their order.
        $positions = [];
        foreach ($nodes as $index => $node) {
            $row = $this->tree->node($node->id);
            $positions[$index] = str_starts_with($row['name'], '@') ? -1 : $row['order'];
        }
        $indexes = array_keys($nodes);
        usort($indexes, static fn(int $a, int $b): int => [$positions[$a], $a] <=> [$positions[$b], $b]);

        $parts = [];
        foreach ($indexes as $index) {
            $node = $nodes[$index];
            $row = $this->tree->node($node->id);
            $name = $row['name'];
            if ($row['virtual'] ?? false) {
                $parts[$name] = $node->value ?? '';
                continue;
            }
            if (str_starts_with($name, '@')) {
                $element->setAttribute(substr($name, 1), $node->value ?? '');
                continue;
            }

            $child = $this->document->createElementNS($this->tree->namespace(strstr($name, ':', true) ?: ''), $name);
            $element->appendChild($child);
            $this->writeChildren($child, $node->id, $node->children);
            if ($node->value !== null) {
                $child->appendChild($this->document->createTextNode($node->value));
            }
        }
        $codec = $parent === null ? null : ($this->tree->node($parent)['codec'] ?? null);
        if ($codec !== null && $parts !== []) {
            $element->appendChild($this->document->createTextNode(Codec::encode($codec, $parts)));
        }
    }

    /**
     * The children the writer adds to an element when they are not given: fixed values, defaults, the currency of an
     * amount, required containers whose content is implied (ChargeIndicator), required containers that may be empty.
     *
     * @param list<Node> $given
     * @return list<Node>
     */
    private function implied(?string $parent, array $given): array
    {
        $present = [];
        foreach ($given as $node) {
            $present[$node->id] = true;
            $present[$this->tree->node($node->id)['name']] = true;
        }

        $implied = [];
        $parentRow = $parent === null ? null : $this->tree->node($parent);
        foreach ($this->tree->children($parent) as $id) {
            if (isset($present[$id])) {
                continue;
            }
            $row = $this->tree->node($id);
            if (! in_array($this->profile, $row['profiles'], true) || ($row['virtual'] ?? false)) {
                continue;
            }

            if (isset($row['fixed']) || isset($row['default'])) {
                $implied[] = new Node($id, $row['fixed'] ?? $row['default'] ?? null);
            } elseif ($row['name'] === '@currencyID' && isset($parentRow['currency'], $this->currencies[$parentRow['currency']])) {
                $implied[] = new Node($id, $this->currencies[$parentRow['currency']]);
            } elseif (($row['min'] ?? 0) > 0 && ! isset($row['type']) && $this->structural($id)) {
                $implied[] = new Node($id);
            } else {
                continue;
            }
            $present[$row['name']] = true;
        }

        foreach ($parentRow['required'][$this->profile] ?? [] as $name) {
            if (isset($present[$name])) {
                continue;
            }
            foreach ($this->tree->children($parent) as $id) {
                if ($this->tree->node($id)['name'] === $name) {
                    $implied[] = new Node($id);
                    break;
                }
            }
        }

        return $implied;
    }

    /**
     * A container whose content the writer adds by itself: its subtree holds fixed values or defaults reached through
     * containers only - the ChargeIndicator of an allowance or charge, the tax scheme of a tax registration in UBL, not
     * the tax registration whose identifier the user gives.
     */
    private function structural(string $id): bool
    {
        foreach ($this->tree->children($id) as $child) {
            $row = $this->tree->node($child);
            if ((isset($row['fixed']) || isset($row['default'])) && ($row['min'] ?? 0) > 0) {
                return true;
            }
            if (! isset($row['type']) && ($row['min'] ?? 0) > 0 && $this->structural($child)) {
                return true;
            }
        }

        return false;
    }

    /**
     * A node as a message names it: by the field given for it where that is another one - "BT-9 (BT-20-00,
     * ram:SpecifiedTradePaymentTerms)" for the payment terms a due date needs.
     */
    private function label(Node $node): string
    {
        return $node->given === null || $node->given === $node->id
            ? $node->id
            : "{$node->given} ({$node->id}, " . $this->tree->node($node->id)['name'] . ')';
    }

    /**
     * Whether the children of a node are attributes or parts of its value only.
     *
     * @param list<Node> $nodes
     */
    private function onlyAttributes(array $nodes): bool
    {
        foreach ($nodes as $node) {
            $row = $this->tree->node($node->id);
            if (! str_starts_with($row['name'], '@') && ! ($row['virtual'] ?? false)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param list<Node> $nodes
     */
    private function collectCurrencies(array $nodes): void
    {
        foreach ($nodes as $node) {
            if (($node->id === 'BT-5' || $node->id === 'BT-6') && $node->value !== null) {
                $this->currencies[$node->id] ??= trim($node->value);
            }
            $this->collectCurrencies($node->children);
        }
    }
}
