<?php

declare(strict_types=1);

namespace Dealerweb\EInvoice\Generation;

use Dealerweb\EInvoice\Syntax;
use InvalidArgumentException;

/**
 * The element tree of a syntax, keyed by the ids of the fields: every node with its parent, qualified name, position in
 * the schema, cardinality, profiles, data type and fixed value.
 *
 *  - CII (resources/compiled/cii-tree.php, compiled from the field list of the FeRD release package, the schemas of
 *    the profiles and the CII schema D16B of XRechnung): ZUGFeRD / Factur-X EXTENDED and XRechnung.
 *  - UBL Invoice and CreditNote (resources/compiled/ubl-invoice-tree.php, ubl-creditnote-tree.php, compiled from the
 *    binding of the fields to UBL and the schemas of UBL 2.1): EN 16931, XRechnung with its extension and Peppol BIS
 *    Billing 3.0.
 *
 * A field has the same id in each tree - BT-27 is the seller's name in CII and in UBL -, the elements around it have
 * ids of their own tree.
 *
 * @internal
 *
 * @phpstan-type TreeNode array{parent: string|null, name: string, path: string, type?: string, max: int, maxima?: array<string, int>, occurs?: array<string, int>, min?: int, profiles: list<string>, fixed?: string, default?: string, currency?: string, global?: string, proprietary?: string, order: int, required?: array<string, list<string>>, field?: string, codes?: array<string, string>, codec?: string, virtual?: bool, takes?: list<string>}
 * @phpstan-type TreeData array{source: array<string, string>, namespaces: array<string, string>, nodes: array<string, TreeNode>, children: array<string, list<string>>, root?: array{name: string, namespace: string}, declare?: list<string>, recursion?: array{node: string, depth: int}}
 */
final class Tree
{
    /** The compiled tree of each syntax. */
    private const FILES = [
        'cii' => 'cii-tree.php',
        'ubl-invoice' => 'ubl-invoice-tree.php',
        'ubl-creditnote' => 'ubl-creditnote-tree.php',
    ];

    /** @var array<string, self> */
    private static array $trees = [];

    /** @var array<string, array<string, string>> fixed values in the subtree of a node, by the path below it */
    private array $fixedBelow = [];

    /** @var array<string, array<string, array<string, string>>> */
    private array $distinguishing = [];

    /** @var array<string, string>|null the node that takes each field of another syntax */
    private ?array $takers = null;

    /**
     * @param TreeData $data
     */
    private function __construct(public readonly Syntax $syntax, private readonly array $data) {}

    public static function of(Syntax $syntax): self
    {
        if (! isset(self::$trees[$syntax->value])) {
            /** @var TreeData $data */
            $data = require dirname(__DIR__, 2) . '/resources/compiled/' . self::FILES[$syntax->value];
            self::$trees[$syntax->value] = new self($syntax, $data);
        }

        return self::$trees[$syntax->value];
    }

    public static function cii(): self
    {
        return self::of(Syntax::Cii);
    }

    /**
     * @return TreeNode
     */
    public function node(string $id): array
    {
        return $this->data['nodes'][$id] ?? throw new InvalidArgumentException("Unknown field $id.");
    }

    public function has(string $id): bool
    {
        return isset($this->data['nodes'][$id]);
    }

    /**
     * The profiles a node is part of, for a message that refuses it in another: "It is in EN16931, EXTENDED and
     * XRECHNUNG."
     */
    public function inProfiles(string $id): string
    {
        $profiles = $this->node($id)['profiles'];
        $last = array_pop($profiles);

        return $last === null ? 'It is in no profile.' : 'It is in ' . ($profiles === [] ? '' : implode(', ', $profiles) . ' and ') . "$last.";
    }

    /**
     * A node that holds a value: an attribute or an element without child elements (a note of UBL holds its subject
     * code and text as parts of its value, not as elements).
     */
    public function isLeaf(string $id): bool
    {
        foreach ($this->children($id) as $child) {
            $node = $this->node($child);
            if (! str_starts_with($node['name'], '@') && ! ($node['virtual'] ?? false)) {
                return false;
            }
        }

        return true;
    }

    /**
     * The child ids of a node in document order, those of the root element for null.
     *
     * @return list<string>
     */
    public function children(?string $id): array
    {
        return $this->data['children'][$id ?? ''] ?? [];
    }

    /**
     * The field a node stands for: its own id, or for a place the syntax gives a field besides its own (the creditor
     * identifier BT-90 with the payee instead of the seller in UBL) the id of that field.
     */
    public function field(string $id): string
    {
        return $this->node($id)['field'] ?? $id;
    }

    /**
     * The node that holds a field this tree has no node of, where its own field is not given: in UBL the name of a
     * contact holds its department (a field of CII, BT-41-0) - null for a field no node takes.
     */
    public function taker(string $id): ?string
    {
        if ($this->takers === null) {
            $this->takers = [];
            foreach ($this->data['nodes'] as $node => $row) {
                foreach ($row['takes'] ?? [] as $field) {
                    $this->takers[$field] = $node;
                }
            }
        }

        return $this->takers[$id] ?? null;
    }

    /**
     * The namespace URI of a prefix of the tree (rsm, ram, qdt, udt; cac, cbc).
     */
    public function namespace(string $prefix): string
    {
        return $this->data['namespaces'][$prefix] ?? throw new InvalidArgumentException("Unknown prefix $prefix.");
    }

    /**
     * The prefix of the tree for a namespace URI, null for a namespace the syntax does not use.
     */
    public function prefix(string $namespace): ?string
    {
        $prefix = array_search($namespace, $this->data['namespaces'], true);

        return is_string($prefix) ? $prefix : null;
    }

    /**
     * The root element: its qualified name and namespace.
     *
     * @return array{name: string, namespace: string}
     */
    public function root(): array
    {
        return $this->data['root'] ?? ['name' => 'rsm:CrossIndustryInvoice', 'namespace' => $this->namespace('rsm')];
    }

    /**
     * The prefixes the root element declares, in this order.
     *
     * @return list<string>
     */
    public function declared(): array
    {
        return $this->data['declare'] ?? ['rsm', 'qdt', 'ram', 'udt'];
    }

    /**
     * The name of the syntax in a message: CII or UBL.
     */
    public function label(): string
    {
        return $this->syntax === Syntax::Cii ? 'CII' : 'UBL';
    }

    /**
     * How the syntax writes a date: YYYYMMDD in CII (format 102), YYYY-MM-DD in UBL.
     */
    public function dateFormat(): string
    {
        return $this->syntax === Syntax::Cii ? 'Ymd' : 'Y-m-d';
    }

    /**
     * The element that repeats itself below itself - the sub invoice line of the XRechnung extension in UBL - and how
     * deep the tree has it: below that element the nodes of each level carry the level as suffix ("BT-126#2" the
     * identifier of a sub line of a sub line). Null for a tree without it.
     *
     * @return array{node: string, depth: int}|null
     */
    public function recursion(): ?array
    {
        return $this->data['recursion'] ?? null;
    }

    /**
     * The fixed values in the subtree of a node, by the path below it ("/ram:TypeCode", "/ram:ID/@schemeID") - what
     * tells nodes with the same name apart. A child that shares its name with a sibling is left out with its subtree:
     * its fixed values tell it from that sibling (the listID of a non-VAT tax code among the reason codes of a charge),
     * not its parent from others.
     *
     * @return array<string, string>
     */
    public function fixedBelow(string $id): array
    {
        if (! isset($this->fixedBelow[$id])) {
            $names = array_count_values(array_map(fn(string $child): string => $this->node($child)['name'], $this->children($id)));
            $fixed = [];
            foreach ($this->children($id) as $child) {
                $node = $this->node($child);
                if ($names[$node['name']] > 1 || ($node['virtual'] ?? false)) {
                    continue;
                }
                if (isset($node['fixed'])) {
                    $fixed['/' . $node['name']] = $node['fixed'];
                }
                foreach ($this->fixedBelow($child) as $path => $value) {
                    $fixed['/' . $node['name'] . $path] = $value;
                }
            }
            $this->fixedBelow[$id] = $fixed;
        }

        return $this->fixedBelow[$id];
    }

    /**
     * What tells nodes with the same name and parent apart: for each of them the fixed values in its subtree that the
     * others do not share - a value all of them fix (TypeCode "VAT" of an allowance and of a charge) or that sits in an
     * optional part tells nothing and must not be required of a document.
     *
     * @param list<string> $ids
     * @return array<string, array<string, string>>
     */
    public function distinguishing(array $ids): array
    {
        $key = implode(',', $ids);
        if (! isset($this->distinguishing[$key])) {
            $fixed = [];
            foreach ($ids as $id) {
                $fixed[$id] = $this->fixedBelow($id);
            }
            $result = [];
            foreach ($fixed as $id => $values) {
                $result[$id] = array_filter(
                    $values,
                    static function (string $value, string $path) use ($fixed): bool {
                        foreach ($fixed as $other) {
                            if (($other[$path] ?? null) !== $value) {
                                return true;
                            }
                        }

                        return false;
                    },
                    ARRAY_FILTER_USE_BOTH
                );
            }
            $this->distinguishing[$key] = $result;
        }

        return $this->distinguishing[$key];
    }
}
