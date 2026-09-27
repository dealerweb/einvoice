<?php

declare(strict_types=1);

namespace Dealerweb\EInvoice\Validation\XPath;

use DOMNode;

/**
 * The value model of the evaluator and the rules of XPath 2.0 for it: atomization, effective boolean value, casting,
 * comparison. A sequence is a PHP list; its items are
 *  - DOMNode (document, element, attribute, text, comment, processing instruction),
 *  - string = xs:string, Untyped = xs:untypedAtomic, bool = xs:boolean, Date = xs:date,
 *  - int = xs:integer, Decimal = xs:decimal (or an xs:integer beyond the int range), float = xs:double.
 *
 * @internal
 */
final class Value
{
    private const WHITESPACE = " \t\r\n";

    private const DOUBLE = '/^([+-]?)(?:[0-9]+(?:\.[0-9]*)?|\.[0-9]+)(?:[eE][+-]?[0-9]+)?$/D';

    /**
     * Name of the dynamic type, as XPath writes it: "xs:integer", "element()", ...
     */
    public static function typeName(mixed $item): string
    {
        return match (true) {
            $item instanceof DOMNode => match ($item->nodeType) {
                XML_DOCUMENT_NODE => 'document-node()',
                XML_ELEMENT_NODE => 'element()',
                XML_ATTRIBUTE_NODE => 'attribute()',
                XML_COMMENT_NODE => 'comment()',
                XML_PI_NODE => 'processing-instruction()',
                default => 'text()',
            },
            is_string($item) => 'xs:string',
            $item instanceof Untyped => 'xs:untypedAtomic',
            is_bool($item) => 'xs:boolean',
            is_int($item) => 'xs:integer',
            $item instanceof Decimal => $item->integer ? 'xs:integer' : 'xs:decimal',
            is_float($item) => 'xs:double',
            $item instanceof Date => 'xs:date',
            default => get_debug_type($item),
        };
    }

    public static function isNumeric(mixed $item): bool
    {
        return is_int($item) || is_float($item) || $item instanceof Decimal;
    }

    /**
     * The typed value of an item: a node becomes xs:untypedAtomic with its string value.
     */
    public static function atomizeItem(mixed $item): mixed
    {
        return $item instanceof DOMNode ? new Untyped(self::nodeString($item)) : $item;
    }

    /**
     * @param list<mixed> $sequence
     *
     * @return list<mixed>
     */
    public static function atomize(array $sequence): array
    {
        $atomized = [];
        foreach ($sequence as $item) {
            $atomized[] = $item instanceof DOMNode ? new Untyped(self::nodeString($item)) : $item;
        }

        return $atomized;
    }

    public static function nodeString(DOMNode $node): string
    {
        return $node->textContent ?? '';
    }

    /**
     * The string value of an item (fn:string): a node's text, an atomic value cast to xs:string.
     */
    public static function string(mixed $item): string
    {
        return match (true) {
            $item instanceof DOMNode => self::nodeString($item),
            is_string($item) => $item,
            $item instanceof Untyped => $item->value,
            is_int($item) => (string) $item,
            is_bool($item) => $item ? 'true' : 'false',
            $item instanceof Decimal => $item->toString(),
            is_float($item) => self::doubleToString($item),
            $item instanceof Date => $item->toString(),
            default => throw new DynamicError('XPTY0004', 'Cannot convert ' . self::typeName($item) . ' to xs:string.'),
        };
    }

    /**
     * Effective boolean value (XPath 2.0, 2.4.3).
     *
     * @param list<mixed> $sequence
     */
    public static function ebv(array $sequence): bool
    {
        if ($sequence === []) {
            return false;
        }
        $first = $sequence[0];
        if ($first instanceof DOMNode) {
            return true;
        }
        if (count($sequence) > 1) {
            throw new DynamicError('FORG0006', 'Effective boolean value is not defined for a sequence of two or more items starting with ' . self::typeName($first) . '.');
        }

        return match (true) {
            is_bool($first) => $first,
            is_string($first) => $first !== '',
            $first instanceof Untyped => $first->value !== '',
            is_int($first) => $first !== 0,
            is_float($first) => $first != 0.0 && ! is_nan($first),
            $first instanceof Decimal => ! $first->isZero(),
            default => throw new DynamicError('FORG0006', 'Effective boolean value is not defined for ' . self::typeName($first) . '.'),
        };
    }

    /**
     * Casts an atomic value to xs:<type> (XPath 2.0, 17.1).
     *
     * @throws DynamicError FORG0001 the value has no valid form of the type, XPTY0004 the types cannot be cast
     */
    public static function cast(mixed $item, string $type): mixed
    {
        if ($item instanceof DOMNode) {
            $item = new Untyped(self::nodeString($item));
        }

        switch ($type) {
            case 'string':
                return self::string($item);
            case 'untypedAtomic':
                return new Untyped(self::string($item));
            case 'double':
                return self::toDouble($item);
            case 'decimal':
                return self::toDecimal($item);
            case 'integer':
                return self::toInteger($item);
            case 'boolean':
                return self::toBoolean($item);
            case 'date':
                if ($item instanceof Date) {
                    return $item;
                }
                if (is_string($item) || $item instanceof Untyped) {
                    $text = is_string($item) ? $item : $item->value;

                    return Date::parse($text) ?? throw new DynamicError(Date::yearOutOfRange($text) ? 'FODT0001' : 'FORG0001', "Invalid date \"$text\".");
                }
                break;
        }

        throw new DynamicError('XPTY0004', 'Cannot cast ' . self::typeName($item) . " to xs:$type.");
    }

    public static function castable(mixed $item, string $type): bool
    {
        try {
            self::cast($item, $type);

            return true;
        } catch (DynamicError) {
            return false;
        }
    }

    /**
     * The value read as xs:double, NaN where it is not a number (fn:number).
     */
    public static function number(mixed $item): float
    {
        try {
            return self::toDouble($item instanceof DOMNode ? new Untyped(self::nodeString($item)) : $item);
        } catch (DynamicError) {
            return NAN;
        }
    }

    /**
     * Lexical form of xs:double: "1.5", "-1.0E-7", "INF", "NaN"; null where the text is none.
     */
    public static function parseDouble(string $text): ?float
    {
        $text = trim($text, self::WHITESPACE);

        return match ($text) {
            'INF', '+INF' => INF,
            '-INF' => -INF,
            'NaN' => NAN,
            default => preg_match(self::DOUBLE, $text) ? (float) $text : null,
        };
    }

    /**
     * Canonical form of xs:double as fn:string writes it: "0.1", "100", "1.0E6", "1.5E-7", "INF", "NaN", "-0".
     */
    public static function doubleToString(float $value): string
    {
        return DoubleFormat::format($value);
    }

    /**
     * Value comparison of two atomic values (eq, ne, lt, le, gt, ge after the operands were prepared): -1, 0, 1, or
     * null where a NaN makes them unordered.
     *
     * @throws DynamicError XPTY0004 the types cannot be compared
     */
    public static function compare(mixed $a, mixed $b): ?int
    {
        if (self::isNumeric($a) && self::isNumeric($b)) {
            return self::compareNumbers($a, $b);
        }
        if (is_string($a) && is_string($b)) {
            return strcmp($a, $b) <=> 0;
        }
        if (is_bool($a) && is_bool($b)) {
            return $a <=> $b;
        }
        if ($a instanceof Date && $b instanceof Date) {
            return $a->compare($b);
        }

        throw new DynamicError('XPTY0004', 'Cannot compare ' . self::typeName($a) . ' with ' . self::typeName($b) . '.');
    }

    /**
     * @param int|float|Decimal $a
     * @param int|float|Decimal $b
     */
    public static function compareNumbers(mixed $a, mixed $b): ?int
    {
        if (is_int($a) && is_int($b)) {
            return $a <=> $b;
        }
        if (is_float($a) || is_float($b)) {
            $x = self::toFloat($a);
            $y = self::toFloat($b);
            if (is_nan($x) || is_nan($y)) {
                return null;
            }

            return $x <=> $y;
        }

        return self::toDecimalValue($a)->compare(self::toDecimalValue($b));
    }

    /**
     * @param int|float|Decimal $value
     */
    public static function toFloat(mixed $value): float
    {
        return match (true) {
            is_float($value) => $value,
            is_int($value) => (float) $value,
            default => $value->toFloat(),
        };
    }

    /**
     * @param int|Decimal $value
     */
    public static function toDecimalValue(mixed $value): Decimal
    {
        return is_int($value) ? Decimal::fromInt($value) : $value;
    }

    private static function toDouble(mixed $item): float
    {
        return match (true) {
            is_float($item) => $item,
            is_int($item) => (float) $item,
            $item instanceof Decimal => $item->toFloat(),
            is_bool($item) => $item ? 1.0 : 0.0,
            is_string($item), $item instanceof Untyped => self::parseDouble(is_string($item) ? $item : $item->value)
                ?? throw new DynamicError('FORG0001', 'Cannot convert "' . (is_string($item) ? $item : $item->value) . '" to xs:double.'),
            default => throw new DynamicError('XPTY0004', 'Cannot cast ' . self::typeName($item) . ' to xs:double.'),
        };
    }

    private static function toDecimal(mixed $item): Decimal
    {
        return match (true) {
            $item instanceof Decimal => $item->asDecimal(),
            is_int($item) => Decimal::fromInt($item)->asDecimal(),
            is_float($item) => Decimal::fromFloat($item),
            is_bool($item) => Decimal::fromInt($item ? 1 : 0)->asDecimal(),
            is_string($item), $item instanceof Untyped => Decimal::parse(is_string($item) ? $item : $item->value)
                ?? throw new DynamicError('FORG0001', 'Cannot convert "' . (is_string($item) ? $item : $item->value) . '" to xs:decimal.'),
            default => throw new DynamicError('XPTY0004', 'Cannot cast ' . self::typeName($item) . ' to xs:decimal.'),
        };
    }

    private static function toInteger(mixed $item): int|Decimal
    {
        if (is_int($item)) {
            return $item;
        }
        if ($item instanceof Decimal) {
            return $item->toInteger();
        }
        if (is_float($item)) {
            if (! is_finite($item)) {
                throw new DynamicError('FOCA0002', 'Cannot convert ' . self::doubleToString($item) . ' to xs:integer.');
            }

            return Decimal::fromFloat($item)->toInteger();
        }
        if (is_bool($item)) {
            return $item ? 1 : 0;
        }
        if (is_string($item) || $item instanceof Untyped) {
            $text = trim(is_string($item) ? $item : $item->value, self::WHITESPACE);
            if (! preg_match('/^([+-]?)([0-9]+)$/D', $text, $match)) {
                throw new DynamicError('FORG0001', "Cannot convert \"$text\" to xs:integer.");
            }

            return Decimal::integer($match[1] === '-', $match[2]);
        }

        throw new DynamicError('XPTY0004', 'Cannot cast ' . self::typeName($item) . ' to xs:integer.');
    }

    private static function toBoolean(mixed $item): bool
    {
        if (is_bool($item)) {
            return $item;
        }
        if (is_string($item) || $item instanceof Untyped) {
            $text = trim(is_string($item) ? $item : $item->value, self::WHITESPACE);

            return match ($text) {
                'true', '1' => true,
                'false', '0' => false,
                default => throw new DynamicError('FORG0001', "Cannot convert \"$text\" to xs:boolean."),
            };
        }
        if (self::isNumeric($item)) {
            return match (true) {
                is_int($item) => $item !== 0,
                is_float($item) => $item != 0.0 && ! is_nan($item),
                default => ! $item->isZero(),
            };
        }

        throw new DynamicError('XPTY0004', 'Cannot cast ' . self::typeName($item) . ' to xs:boolean.');
    }
}
