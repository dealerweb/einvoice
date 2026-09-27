<?php

declare(strict_types=1);

namespace Dealerweb\EInvoice\Model;

use DateTimeInterface;
use Dealerweb\EInvoice\Generation\Fields;
use Dealerweb\EInvoice\Generation\Tree;
use Dealerweb\EInvoice\Invoice;
use InvalidArgumentException;

/**
 * Writes an invoice of the model as the fields the generator places in the tree of a syntax (Generation\Fields): each
 * value under the id of its field, the items of a list as instances of the element they repeat, the first item of a
 * list that continues single properties (the contact of a party in CII) made of them, the sub lines of a line as the
 * element that repeats itself below itself - its fields with the level as suffix ("BT-126#2").
 *
 * It checks what it can tell by the path of the model: a value of the wrong kind, a field outside the profile, more
 * items than the profile allows, a field the syntax has no place for; explain() turns the ids in a later message of the
 * generator into those paths.
 *
 * @internal used by Generation\Generator
 */
final class FieldWriter
{
    /** The parts of an ISO date with time and zone - a date of format 205 where the field allows it. */
    private const DATE_TIME = '/^(\d{4})-(\d{2})-(\d{2})T(\d{2}):(\d{2})(?::00)?(Z|[+-]\d{2}:?\d{2})$/D';

    /** @var array<string, string> the path of the model a field was first written from, by its id */
    private array $paths = [];

    /** The suffix of the ids on the level of sub lines being written: "#2" in a sub line of a sub line. */
    private string $suffix = '';

    private readonly Mapping $mapping;

    /**
     * @param string $profile the profile of the tree: MINIMUM, BASIC WL, BASIC, EN16931, EXTENDED or XRECHNUNG in CII;
     *                        CORE, XRECHNUNG or PEPPOL in UBL
     */
    public function __construct(private readonly Tree $tree, private readonly string $profile)
    {
        $this->mapping = Mapping::of($tree);
    }

    /**
     * The fields of an invoice - completed (Calculator) and with the prices as the syntax writes them; the invoice
     * given is not changed.
     *
     * @return array<string, mixed>
     * @throws InvalidArgumentException a value that has no place or is of the wrong kind, with its path
     */
    public function write(Invoice $invoice): array
    {
        $invoice->checkItems();
        $invoice = clone $invoice;
        Calculator::complete($invoice);
        foreach ($invoice->lines as $line) {
            $this->prices($line);
        }
        $this->paths = [];
        $this->suffix = '';

        return $this->object($invoice, $this->mapping->invoice, '');
    }

    /**
     * A message of the generator with the paths of the model next to the ids it names: "lines.0.typeCode (BT-X-7)".
     */
    public function explain(string $message): string
    {
        return (string) preg_replace_callback(
            '/\bB[TG]-(?:X-|DEX-)?\d+(?:-\d+)*(?:#\d+)?/',
            fn(array $match): string => isset($this->paths[$match[0]]) ? "{$this->paths[$match[0]]} ({$match[0]})" : $match[0],
            $message,
        );
    }

    /**
     * The fields of an object, for the element it lies in - flat: Fields places them.
     *
     * @return array<string, mixed>
     */
    private function object(Element $object, MappedObject $mapped, string $path): array
    {
        $fields = [];
        $continued = [];
        foreach ($mapped->properties as $name => $property) {
            $value = $object->$name;
            $at = $path === '' ? $name : "$path.$name";
            if ($property->object === null) {
                if ($property->list) {
                    /** @var list<mixed> $value */
                    $this->values($fields, $property, $value, $at);
                } else {
                    $this->value($fields, $property->ids, $value, $at);
                }
            } elseif (! $property->list) {
                /** @var Element $value */
                if ($property->object->class === 'Identifier') {
                    /** @var Identifier $value */
                    $this->identifier($fields, $property->object, $value, $at, false);
                } else {
                    $fields = $this->merge($fields, $this->object($value, $property->object, $at), $at);
                }
            } elseif ($property->object === $mapped) {
                /** @var list<Element> $value */
                $this->levels($fields, $property, $value, $at);
            } else {
                /** @var list<Element> $value */
                $instances = $this->items($fields, $property, $value, $at);
                if ($instances !== []) {
                    $continued[] = [$property, $instances, $at];
                }
            }
        }
        foreach ($continued as [$property, $instances, $at]) {
            $node = $this->at((string) $property->node);
            if ($property->offset === 1) {
                // The first instance is made of the single properties that lie in the element (the contact of a party).
                $first = [];
                foreach (array_keys($fields) as $key) {
                    if ($this->below($key, $node)) {
                        $first[$key] = $fields[$key];
                        unset($fields[$key]);
                    }
                }
                if ($first !== []) {
                    array_unshift($instances, $first);
                }
            }
            $this->count($node, count($instances), $at);
            $fields[$node] = $instances;
        }

        return $fields;
    }

    /**
     * The items of a list of objects: as instances of their element (returned) or, where each value is an element of
     * its own (the identifiers of a party in CII), into the fields directly.
     *
     * @param array<string, mixed> $fields
     * @param list<Element> $items
     * @return list<array<string, mixed>>
     */
    private function items(array &$fields, MappedProperty $property, array $items, string $path): array
    {
        $mapped = $property->object ?? throw new InvalidArgumentException("$path is no list of objects.");
        if ($property->node === null && $mapped->class === 'Identifier') {
            $this->capacity($mapped, count(array_filter($items, static fn(Element $item): bool => ! $item->isEmpty())), $path);
        }
        $instances = [];
        foreach ($items as $index => $item) {
            if ($item->isEmpty()) {
                continue;
            }
            if ($property->node === null) {
                if ($mapped->class !== 'Identifier' || ! $item instanceof Identifier) {
                    throw new InvalidArgumentException("$path.$index has no place in {$this->tree->label()}.");
                }
                $this->identifier($fields, $mapped, $item, "$path.$index", true);
                continue;
            }
            $this->paths[$this->at($property->node)] ??= $path;
            $instances[] = $this->object($item, $mapped, "$path.$index");
        }

        return $instances;
    }

    /**
     * The items of a list of the object itself - the sub lines of a line: each one level deeper in the element that
     * repeats itself below itself, as deep as the tree has it.
     *
     * @param array<string, mixed> $fields
     * @param list<Element> $items
     */
    private function levels(array &$fields, MappedProperty $property, array $items, string $path): void
    {
        $items = array_values(array_filter($items, static fn(Element $item): bool => ! $item->isEmpty()));
        if ($items === []) {
            return;
        }
        $recursion = $this->tree->recursion();
        if ($property->node === null || $recursion === null) {
            throw new InvalidArgumentException("$path.0 has no place in {$this->tree->label()}.");
        }
        $mapped = $property->object ?? throw new InvalidArgumentException("$path is no list of objects.");
        $level = $this->suffix === '' ? 1 : (int) substr($this->suffix, 1) + 1;
        if ($level > $recursion['depth']) {
            throw new InvalidArgumentException("$path: sub lines nest at most {$recursion['depth']} levels deep.");
        }
        $node = $this->level($property->node, $level);
        $this->check($node, $path);
        $this->paths[$node] ??= $path;
        $outer = $this->suffix;
        $this->suffix = "#$level";
        $instances = [];
        foreach ($items as $index => $item) {
            $instances[] = $this->object($item, $mapped, "$path.$index");
        }
        $this->suffix = $outer;
        $fields[$node] = $instances;
    }

    /**
     * An identifier: with its scheme as attribute of the element it names (a GlobalID in CII where the identifier comes
     * with a scheme and the field has one, else the ID), or with its scheme as element of its own next to it (the type
     * of an object identifier in CII).
     *
     * @param array<string, mixed> $fields
     */
    private function identifier(array &$fields, MappedObject $mapped, Identifier $identifier, string $path, bool $many): void
    {
        if ($identifier->isEmpty()) {
            return;
        }
        $values = array_values(array_filter(array_map($this->at(...), $mapped->properties['value']->ids), $this->tree->has(...)));
        $scheme = isset($mapped->properties['scheme']->ids[0]) ? $this->at($mapped->properties['scheme']->ids[0]) : null;
        if ($values === []) {
            throw new InvalidArgumentException("$path has no place in {$this->tree->label()}.");
        }
        if ($identifier->value === null || $identifier->value === '') {
            throw new InvalidArgumentException("$path.scheme is given without the identifier it belongs to.");
        }
        $hasScheme = $identifier->scheme !== null && $identifier->scheme !== '';
        if ($scheme !== null && ! $this->tree->has($scheme)) {
            $scheme = null;
        }

        // The element the scheme is an attribute of - the GlobalID of the pair, or the one the field list links to the
        // field - and the field it stands for.
        $holder = null;
        $owner = null;
        foreach ($values as $id) {
            foreach ([$id, $this->tree->node($id)['global'] ?? null] as $candidate) {
                if ($candidate !== null && $scheme !== null && in_array($scheme, $this->tree->children($candidate), true)) {
                    $holder = $id;
                    $owner = $candidate;
                }
            }
        }
        $key = $values[0];
        if (count($values) > 1) {
            $key = $hasScheme ? ($holder ?? $values[1]) : ($values[array_search($holder, $values, true) === 0 ? 1 : 0]);
        }
        $this->check($key, "$path.value");
        $value = $this->text($key, $identifier->value, "$path.value");
        $this->paths[$key] ??= "$path.value";
        if (! $hasScheme) {
            $this->put($fields, $key, $value, $many);

            return;
        }

        if ($scheme === null) {
            throw new InvalidArgumentException("$path.scheme has no place in {$this->tree->label()}.");
        }
        $this->check($scheme, "$path.scheme");
        $this->paths[$scheme] ??= "$path.scheme";
        if ($owner === null) {
            // The scheme is an element of its own next to the identifier.
            $this->put($fields, $key, $value, $many);
            $this->put($fields, $scheme, $identifier->scheme, $many);

            return;
        }
        // The element with the scheme: in a list next to IDs of the same field, which are elements of their own.
        $this->check($owner, "$path.value");
        $this->paths[$owner] ??= "$path.value";
        $this->put($fields, $owner, ['value' => $value, $scheme => $identifier->scheme], $many);
    }

    /**
     * A single value, under the first of its fields the syntax has.
     *
     * @param array<string, mixed> $fields
     * @param list<string> $ids
     */
    private function value(array &$fields, array $ids, mixed $value, string $path): void
    {
        if ($value === null || $value === '') {
            return;
        }
        $id = $this->field($ids, $path);
        if (array_key_exists($id, $fields)) {
            throw new InvalidArgumentException(isset($this->paths[$id]) && $this->paths[$id] !== $path
                ? "$path has no place in {$this->tree->label()} next to {$this->paths[$id]} - both are $id there."
                : "$path: the field $id is given twice.");
        }
        $this->paths[$id] ??= $path;
        $fields[$id] = $this->date($id, $value, $path) ?? $this->text($id, $value, $path);
    }

    /**
     * A list of values: Fields makes an instance of the repeating element for each.
     *
     * @param array<string, mixed> $fields
     * @param list<mixed> $values
     */
    private function values(array &$fields, MappedProperty $property, array $values, string $path): void
    {
        $values = array_values(array_filter($values, static fn(mixed $value): bool => $value !== null && $value !== ''));
        if ($values === []) {
            return;
        }
        $id = $this->field($property->ids, $path);
        $this->paths[$id] ??= $path;
        if ($property->node !== null) {
            $this->count($this->at($property->node), count($values), $path);
        }
        $fields[$id] = array_map(fn(mixed $value): string => $this->text($id, $value, $path), $values);
    }

    /**
     * The field of the syntax a value goes into: the first of the ids the syntax has, in the profile.
     *
     * @param list<string> $ids
     */
    private function field(array $ids, string $path): string
    {
        foreach ($ids as $id) {
            $id = $this->at($id);
            if ($this->tree->has($id)) {
                $this->check($id, $path);

                return $id;
            }
        }
        // A field of another syntax that a node of this one holds where its own field is not given.
        foreach ($ids as $id) {
            $taker = $this->tree->taker($this->at($id));
            if ($taker !== null) {
                $this->check($taker, $path);

                return $taker;
            }
        }

        throw new InvalidArgumentException("$path has no place in {$this->tree->label()}.");
    }

    /**
     * A date with time (ISO, with zone) where the field allows the format 205 (CII), as the value with its format -
     * null for any other value.
     *
     * @return array{value: string}|array<string, string>|null
     */
    private function date(string $id, mixed $value, string $path): ?array
    {
        if (! is_string($value) || ! preg_match(self::DATE_TIME, $value, $match) || ($this->tree->node($id)['type'] ?? null) !== 'Date') {
            return null;
        }
        foreach ($this->tree->children($id) as $child) {
            if ($this->tree->node($child)['name'] === '@format' && ! isset($this->tree->node($child)['fixed'])) {
                $zone = $match[6] === 'Z' ? '+0000' : str_replace(':', '', $match[6]);

                return ['value' => $match[1] . $match[2] . $match[3] . $match[4] . $match[5] . $zone, $child => '205'];
            }
        }

        throw new InvalidArgumentException("$path: a date with time has no place here, give the date (YYYY-MM-DD).");
    }

    /**
     * A value as Fields writes it - the check of its kind, with the path.
     */
    private function text(string $id, mixed $value, string $path): string
    {
        if (is_array($value) || (is_object($value) && ! $value instanceof DateTimeInterface)) {
            throw new InvalidArgumentException("$path: " . get_debug_type($value) . ' is no value.');
        }
        try {
            return Fields::text($this->tree, $id, $value);
        } catch (InvalidArgumentException $e) {
            throw new InvalidArgumentException($path . substr($e->getMessage(), strlen($id)), 0, $e);
        }
    }

    /**
     * Whether a field belongs to the profile.
     */
    private function check(string $id, string $path): void
    {
        if (! in_array($this->profile, $this->tree->node($id)['profiles'], true)) {
            throw new InvalidArgumentException("$path ($id) is not a part of the profile {$this->profile}. {$this->tree->inProfiles($id)}");
        }
    }

    /**
     * Whether a list has no more items than its element may occur in the profile.
     */
    private function count(string $node, int $count, string $path): void
    {
        $row = $this->tree->node($node);
        if (! in_array($this->profile, $row['profiles'], true)) {
            throw new InvalidArgumentException("$path ($node) is not a part of the profile {$this->profile}. {$this->tree->inProfiles($node)}");
        }
        $max = $row['maxima'][$this->profile] ?? $row['max'];
        if ($max > 0 && $count > $max) {
            throw new InvalidArgumentException("$path: at most $max in the profile {$this->profile}, $count given.");
        }
    }

    /**
     * Whether identifiers that are elements of their own (of a party) fit: as many as their elements may occur - the ID
     * and the GlobalID in CII, the one identifier of the buyer in UBL.
     */
    private function capacity(MappedObject $mapped, int $count, string $path): void
    {
        $capacity = 0;
        foreach ($mapped->properties['value']->ids as $id) {
            $id = $this->at($id);
            foreach ([$id, $this->tree->has($id) ? $this->tree->node($id)['global'] ?? null : null] as $element) {
                if ($element === null || ! $this->tree->has($element)) {
                    continue;
                }
                $max = $this->tree->node($element)['maxima'][$this->profile] ?? $this->tree->node($element)['max'];
                if ($max === 0) {
                    return;
                }
                $capacity += $max;
            }
        }
        if ($capacity > 0 && $count > $capacity) {
            throw new InvalidArgumentException("$path: at most $capacity in the profile {$this->profile}, $count given.");
        }
    }

    /**
     * @param array<string, mixed> $fields
     */
    private function put(array &$fields, string $id, mixed $value, bool $many): void
    {
        if ($many) {
            $fields[$id][] = $value;

            return;
        }
        if (array_key_exists($id, $fields)) {
            throw new InvalidArgumentException("The field $id is given twice.");
        }
        $fields[$id] = $value;
    }

    /**
     * The fields of a nested object into those of its owner: the syntax shares the elements between them.
     *
     * @param array<string, mixed> $fields
     * @param array<string, mixed> $more
     * @return array<string, mixed>
     */
    private function merge(array $fields, array $more, string $path): array
    {
        foreach ($more as $id => $value) {
            if (array_key_exists($id, $fields)) {
                throw new InvalidArgumentException("$path: the field $id is given twice.");
            }
            $fields[$id] = $value;
        }

        return $fields;
    }

    /**
     * Whether a node of the tree lies below an element.
     */
    private function below(string $id, string $ancestor): bool
    {
        if (! $this->tree->has($id)) {
            return false;
        }
        for ($current = $this->tree->node($id)['parent']; $current !== null; $current = $this->tree->node($current)['parent']) {
            if ($current === $ancestor) {
                return true;
            }
        }

        return false;
    }

    /**
     * The id of a field or element on the level being written: with the suffix of the sub lines.
     */
    private function at(string $id): string
    {
        return $id . $this->suffix;
    }

    /**
     * The id of the element that repeats itself below itself on a level ("BG-DEX-01#2").
     */
    private function level(string $node, int $level): string
    {
        return "$node#$level";
    }

    /**
     * The base quantities of the prices of a line as the syntax writes them. CII has one for each price: the one of
     * the gross price is that of the net price unless given. UBL has one for both prices - a different one for the
     * gross price has no place.
     */
    private function prices(Line $line): void
    {
        $given = static fn(mixed $value): bool => $value !== null && $value !== '';
        if ($this->tree->has('BT-149-1')) {
            if ($given($line->grossPrice) && ! $given($line->grossPriceBaseQuantity) && $given($line->priceBaseQuantity)) {
                $line->grossPriceBaseQuantity = $line->priceBaseQuantity;
            }
            if ($given($line->grossPriceBaseQuantity) && ! $given($line->grossPriceBaseUnit)) {
                $line->grossPriceBaseUnit = $line->priceBaseUnit;
            }
        } else {
            // One base quantity for both prices (as KoSIT reads CII: the one of the gross price where both are given).
            if ($given($line->grossPriceBaseQuantity) && ! $given($line->priceBaseQuantity)) {
                $line->priceBaseQuantity = $line->grossPriceBaseQuantity;
                $line->priceBaseUnit = $given($line->grossPriceBaseUnit) ? $line->grossPriceBaseUnit : $line->priceBaseUnit;
                $line->grossPriceBaseQuantity = null;
                $line->grossPriceBaseUnit = null;
            }
            if ($given($line->grossPriceBaseQuantity) && Calculator::same($line->grossPriceBaseQuantity, $line->priceBaseQuantity)
                && (! $given($line->grossPriceBaseUnit) || ! $given($line->priceBaseUnit) || $line->grossPriceBaseUnit === $line->priceBaseUnit)) {
                $line->priceBaseUnit = $given($line->priceBaseUnit) ? $line->priceBaseUnit : $line->grossPriceBaseUnit;
                $line->grossPriceBaseQuantity = null;
                $line->grossPriceBaseUnit = null;
            }
        }
        foreach ($line->subLines as $subLine) {
            $this->prices($subLine);
        }
    }
}
