<?php

declare(strict_types=1);

namespace Dealerweb\EInvoice\Model;

use Dealerweb\EInvoice\Generation\Tree;

/**
 * The mapping of the model (resources/model/mapping.php) as the writer and the reader of a syntax use it: for each
 * class its properties with the fields they stand for, and for each list the element of the syntax an item of it is an
 * instance of - where the model has the first of such elements as a property of its own (the contact of a party in
 * CII) the list takes the others (offset 1). The sub lines of a line, a line again, are the element of the tree that
 * repeats itself below itself (Tree::recursion(), UBL only).
 *
 * @internal
 */
final class Mapping
{
    /** @var array<string, self> the mapping of each syntax */
    private static array $mappings = [];

    public readonly MappedObject $invoice;

    /** @var array<string, string> the id of the field each identifier field is an alternative of (ID - GlobalID) */
    private array $alternatives = [];

    private function __construct(private readonly Tree $tree)
    {
        /** @var array<string, mixed> $mapping */
        $mapping = require dirname(__DIR__, 2) . '/resources/model/mapping.php';
        $types = [];
        $this->invoice = $this->object($mapping, $types);
    }

    public static function of(Tree $tree): self
    {
        return self::$mappings[$tree->syntax->value] ??= new self($tree);
    }

    /**
     * The id of the field an identifier field is an alternative of: the ID for its GlobalID (and for itself).
     */
    public function alternativeOf(string $id): string
    {
        return $this->alternatives[$id] ?? $id;
    }

    /**
     * @param array<string, mixed> $mapping
     * @param array<string, MappedObject> $types the objects built so far by class, for a recursive one (a sub line)
     */
    private function object(array $mapping, array &$types): MappedObject
    {
        $class = (string) $mapping['@type'];
        $properties = array_filter(array_keys($mapping), static fn(string|int $key): bool => ! str_starts_with((string) $key, '@'));
        if ($properties === [] && isset($types[$class])) {
            return $types[$class];
        }
        $object = new MappedObject($class);
        $types[$class] ??= $object;
        // The element of the object in the syntax: what its lists repeat lies below it.
        $holder = $this->commonAncestor($this->treeIdsOf($mapping));

        foreach ($mapping as $key => $value) {
            $key = (string) $key;
            if (str_starts_with($key, '@')) {
                continue;
            }
            $list = str_ends_with($key, '[]');
            $name = $list ? substr($key, 0, -2) : $key;
            if (is_array($value) && isset($value['@type'])) {
                $inner = $this->object($value, $types);
                $node = $list ? $this->instanceNode($value, $holder) : null;
                if ($inner === $object) {
                    // A list of the object itself: the element that repeats itself below itself, where the tree has it.
                    $node = $list ? $this->tree->recursion()['node'] ?? null : null;
                } elseif ($inner->class === 'Identifier') {
                    $this->noteAlternatives($value);
                    // An identifier that is an element of its own (of a party in CII) is its own instance.
                    $values = array_map(fn(string $id): string => $this->alternatives[$id] ?? $id, self::ids($value['value'] ?? []));
                    if ($node !== null && in_array($this->alternatives[$node] ?? $node, $values, true)) {
                        $node = null;
                    }
                }
                $object->properties[$name] = new MappedProperty($name, $list, $inner, [], $node);
            } else {
                $ids = self::ids($value);
                $node = $list ? $this->nearestRepeating([$ids[0]], $holder) : null;
                $object->properties[$name] = new MappedProperty($name, $list, null, $ids, $node);
            }
        }

        // A list that has fields of single properties of its object continues them: its first item is theirs.
        foreach ($object->properties as $property) {
            if (! $property->list || $property->object === null || $property->object === $object || $property->node === null) {
                continue;
            }
            $own = $this->treeIds($property);
            foreach ($object->properties as $other) {
                if (! $other->list && array_intersect($this->treeIds($other), $own) !== []) {
                    $property->offset = 1;
                    break;
                }
            }
        }

        return $object;
    }

    /**
     * The element of the syntax an item of a list of objects is an instance of: the nearest repeating element at or
     * above all its fields, below the element of the object that holds the list - null where there is none (each
     * identifier of a party is an element of its own in CII) or the list has no fields in the syntax.
     *
     * @param array<string, mixed> $mapping
     */
    private function instanceNode(array $mapping, ?string $holder): ?string
    {
        $ids = $this->treeIdsOf($mapping);

        return $ids === [] ? null : $this->nearestRepeating([$this->commonAncestor($ids) ?? $ids[0]], $holder);
    }

    /**
     * The nearest repeating element at or above the first of the ids, below the element of the holder - the instance of
     * an item of a list of values (a batch identifier, a transport mode) or objects.
     *
     * @param list<string> $ids
     */
    private function nearestRepeating(array $ids, ?string $holder): ?string
    {
        if (! $this->tree->has($ids[0])) {
            return null;
        }
        foreach (array_reverse($this->ancestors($ids[0])) as $node) {
            if ($node === $holder) {
                return null;
            }
            if ($this->tree->node($node)['max'] !== 1) {
                return $node;
            }
        }

        return null;
    }

    /**
     * The deepest node at or above all the ids - null for none but the document.
     *
     * @param list<string> $ids
     */
    private function commonAncestor(array $ids): ?string
    {
        if ($ids === []) {
            return null;
        }
        $common = $this->ancestors($ids[0]);
        foreach (array_slice($ids, 1) as $id) {
            $common = array_values(array_intersect($common, $this->ancestors($id)));
        }

        return $common === [] ? null : end($common);
    }

    /**
     * The fields of the syntax a property stands for, of its object and the objects in it.
     *
     * @return list<string>
     */
    private function treeIds(MappedProperty $property): array
    {
        if ($property->object === null) {
            return array_values(array_filter($property->ids, $this->tree->has(...)));
        }
        $ids = [];
        foreach ($property->object->properties as $inner) {
            if ($inner->object !== $property->object) {
                $ids = [...$ids, ...$this->treeIds($inner)];
            }
        }

        return $ids;
    }

    /**
     * The fields of the syntax in an object of the mapping.
     *
     * @param array<string, mixed> $mapping
     * @return list<string>
     */
    private function treeIdsOf(array $mapping): array
    {
        return array_values(array_filter(self::ids($mapping), $this->tree->has(...)));
    }

    /**
     * The ids of the node and its ancestors, from the root down to the node.
     *
     * @return list<string>
     */
    private function ancestors(string $id): array
    {
        $chain = [];
        for ($current = $id; $current !== null; $current = $this->tree->node($current)['parent']) {
            array_unshift($chain, $current);
        }

        return $chain;
    }

    /**
     * Records the fields an identifier of the mapping names as alternatives: the ID and the GlobalID of a party, also
     * where the field list links them (BT-29 with its GlobalID BT-29-0).
     *
     * @param array<string, mixed> $identifier
     */
    private function noteAlternatives(array $identifier): void
    {
        $values = self::ids($identifier['value'] ?? []);
        foreach ($values as $id) {
            $this->alternatives[$id] = $values[0];
            if ($this->tree->has($id) && isset($this->tree->node($id)['global'])) {
                $this->alternatives[(string) $this->tree->node($id)['global']] = $values[0];
            }
        }
    }

    /**
     * The field ids in a value of the mapping.
     *
     * @return list<string>
     */
    private static function ids(mixed $value): array
    {
        if (! is_array($value)) {
            return [(string) $value];
        }
        $ids = [];
        foreach ($value as $key => $item) {
            if (! str_starts_with((string) $key, '@')) {
                $ids = [...$ids, ...self::ids($item)];
            }
        }

        return $ids;
    }
}
