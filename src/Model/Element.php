<?php

declare(strict_types=1);

namespace Dealerweb\EInvoice\Model;

use DateTime;
use DateTimeInterface;
use Dealerweb\EInvoice\Rules;
use InvalidArgumentException;
use JsonSerializable;
use ReflectionClass;
use ReflectionNamedType;
use ReflectionProperty;
use TypeError;

/**
 * An object of the invoice model: public properties with readable names, as objects or as arrays with the same names.
 *
 *  - Values are plain: a decimal as string or number ('19.00', 19), a date as YYYY-MM-DD or DateTimeInterface, a code
 *    as string, yes/no as bool. What is not given is null.
 *  - Objects are always there - $invoice->buyer->address->city needs no check for null -, lists start empty.
 *  - fromArray() takes the names of the properties at any depth; for an object with one main value (a document
 *    reference, an identifier, a note) that value alone: 'purchaseOrder' => 'PO-4711'. A whole number given for a text
 *    (a line identifier, a postcode) is its digits.
 *
 * @phpstan-consistent-constructor
 */
abstract class Element implements JsonSerializable
{
    /** The properties that hold an object, with its class. */
    protected const OBJECTS = [];

    /** The properties that hold a list, with the class of its items - null for a list of values. */
    protected const LISTS = [];

    /** The property one value given for the whole object goes into. */
    protected const PRIMARY = null;

    /** @var array<string, array<string, int>> the properties of each class of the model - the private ones are none */
    private static array $properties = [];

    /**
     * An object from an array with the names of its properties.
     *
     * @param array<mixed> $values
     * @throws InvalidArgumentException an unknown property, a value of the wrong kind - with its path
     */
    public static function fromArray(array $values): static
    {
        return static::build($values, '');
    }

    /**
     * The object as an array with the names of its properties, without what is not given (null, '', empty lists and
     * objects); a date as YYYY-MM-DD.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $result = [];
        foreach (get_object_vars($this) as $name => $value) {
            $value = self::export($value);
            if ($value !== null) {
                $result[$name] = $value;
            }
        }

        return $result;
    }

    /**
     * The class of the items of a list property - null for a list of values or no list.
     *
     * @internal used by the readers
     * @return class-string<self>|null
     */
    public static function listClass(string $property): ?string
    {
        return static::LISTS[$property] ?? null;
    }

    /**
     * Whether nothing is given: no value, no item in a list, every object empty.
     */
    public function isEmpty(): bool
    {
        return $this->toArray() === [];
    }

    /**
     * Checks the lists of the object and of the objects in it, which an assignment may have filled with anything: an
     * item of a list of objects has to be an object of its class, an item of a list of values a value - as fromArray()
     * makes them.
     *
     * @internal used by calculate() and the generator
     * @throws InvalidArgumentException an item of the wrong kind, with its path
     */
    public function checkItems(string $path = ''): void
    {
        foreach (get_object_vars($this) as $name => $value) {
            $at = self::path($path, $name);
            if ($value instanceof self) {
                $value->checkItems($at);
                continue;
            }
            if (! is_array($value) || ! array_key_exists($name, static::LISTS)) {
                continue;
            }
            $class = static::listClass($name);
            foreach ($value as $index => $item) {
                if ($class === null) {
                    self::value($item, "$at.$index");
                } elseif ($item instanceof $class) {
                    $item->checkItems("$at.$index");
                } else {
                    throw new InvalidArgumentException("$at.$index: " . self::describe($item) . ' is no ' . self::shortName($class)
                        . ' - give an object of the model, or build the invoice with fromArray().');
                }
            }
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    /**
     * A copy with copies of the objects, lists and dates it holds.
     */
    public function __clone()
    {
        $copy = static fn(mixed $item): mixed => $item instanceof self || $item instanceof DateTime ? clone $item : $item;
        foreach (get_object_vars($this) as $name => $value) {
            $this->$name = is_array($value) ? array_map($copy, $value) : $copy($value);
        }
    }

    /**
     * @param array<mixed> $values
     */
    protected static function build(array $values, string $path): static
    {
        $object = new static();
        // The public properties only - not what the invoice keeps for itself (the document it was read from).
        $properties = self::$properties[static::class] ??= array_flip(array_map(
            static fn(ReflectionProperty $property): string => $property->getName(),
            array_filter((new ReflectionClass(static::class))->getProperties(ReflectionProperty::IS_PUBLIC), static fn(ReflectionProperty $property): bool => ! $property->isStatic()),
        ));
        foreach ($values as $name => $value) {
            $at = self::path($path, (string) $name);
            if (! is_string($name) || ! isset($properties[$name])) {
                throw new InvalidArgumentException('Unknown property ' . Rules::excerpt($at) . '.');
            }
            try {
                $object->$name = match (true) {
                    isset(static::OBJECTS[$name]) => self::object(static::OBJECTS[$name], $value, $at),
                    array_key_exists($name, static::LISTS) => self::items(static::LISTS[$name], $value, $at),
                    default => self::value($value, $at),
                };
            } catch (TypeError) {
                $type = (new ReflectionProperty($object, $name))->getType();
                if (is_int($value) && $type instanceof ReflectionNamedType && $type->getName() === 'string') {
                    // A whole number for a text - a line identifier, a postcode - is its digits.
                    $object->$name = (string) $value;
                    continue;
                }
                throw new InvalidArgumentException("$at: " . self::describe($value) . " is no value of this property ($type).");
            }
        }

        return $object;
    }

    /**
     * An object given as object, as array of its properties or as its main value.
     *
     * @param class-string<self> $class
     */
    private static function object(string $class, mixed $value, string $path): self
    {
        if ($value instanceof $class) {
            return $value;
        }
        if (is_array($value) && ! array_is_list($value)) {
            return $class::build($value, $path);
        }
        if ($value === [] || $value === null) {
            return new $class();
        }
        $primary = $class::PRIMARY;
        if (is_string($primary) && ! is_array($value) && ! is_object($value)) {
            return $class::build([$primary => $value], $path);
        }

        throw new InvalidArgumentException("$path: " . self::describe($value) . ' is no ' . self::shortName($class) . '.');
    }

    /**
     * The items of a list: objects (as objects, arrays or main values) or values.
     *
     * @param class-string<self>|null $class
     * @return list<mixed>
     */
    private static function items(?string $class, mixed $value, string $path): array
    {
        if ($value === null) {
            return [];
        }
        if (! is_array($value) || ! array_is_list($value)) {
            throw new InvalidArgumentException("$path is a list: give its items as a list.");
        }
        $items = [];
        foreach ($value as $index => $item) {
            $items[] = $class === null ? self::value($item, "$path.$index") : self::object($class, $item, "$path.$index");
        }

        return $items;
    }

    private static function value(mixed $value, string $path): mixed
    {
        if (is_array($value) || (is_object($value) && ! $value instanceof DateTimeInterface)) {
            throw new InvalidArgumentException("$path: " . self::describe($value) . ' is no value.');
        }

        return $value;
    }

    /**
     * A value of the array form: null for what is not given.
     */
    private static function export(mixed $value): mixed
    {
        if ($value instanceof self) {
            $array = $value->toArray();

            return $array === [] ? null : $array;
        }
        if (is_array($value)) {
            $items = array_values(array_filter(array_map(self::export(...), $value), static fn(mixed $item): bool => $item !== null));

            return $items === [] ? null : $items;
        }
        if ($value instanceof DateTimeInterface) {
            return $value->format('Y-m-d');
        }

        return $value === '' ? null : $value;
    }

    private static function path(string $path, string $name): string
    {
        return $path === '' ? $name : "$path.$name";
    }

    /**
     * A value as a message names it: a text by its beginning, an object by its class.
     */
    private static function describe(mixed $value): string
    {
        return match (true) {
            is_string($value) => var_export(Rules::excerpt($value), true),
            is_scalar($value) => var_export($value, true),
            is_object($value) => self::shortName($value::class),
            default => get_debug_type($value),
        };
    }

    /**
     * The name of a class without its namespace: "Party".
     */
    private static function shortName(string $class): string
    {
        $position = strrpos($class, '\\');

        return $position === false ? $class : substr($class, $position + 1);
    }
}
