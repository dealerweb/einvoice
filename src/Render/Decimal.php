<?php

declare(strict_types=1);

namespace Dealerweb\EInvoice\Render;

/**
 * Amounts, quantities and rates of the invoice as the decimal text they are written in ("529.87") - never as
 * float, so that nothing is rounded.
 *
 * @internal
 */
final class Decimal
{
    /**
     * A plain decimal number split into its parts: the integer part without leading zeros ('0' at least), the
     * fraction without trailing zeros. Null for anything else (a unit, a letter, an exponent).
     *
     * @return array{negative: bool, integer: string, fraction: string}|null
     */
    public static function parse(string $value): ?array
    {
        if (! preg_match('/^([+-]?)(\d*)(?:\.(\d*))?$/', trim($value), $match) || ($match[2] === '' && ($match[3] ?? '') === '')) {
            return null;
        }

        $integer = ltrim($match[2], '0');

        return ['negative' => $match[1] === '-', 'integer' => $integer === '' ? '0' : $integer, 'fraction' => rtrim($match[3] ?? '', '0')];
    }

    /**
     * Whether two amounts of the invoice are the same number ("100", "100.00").
     */
    public static function equals(?string $a, ?string $b): bool
    {
        if ($a === null || $b === null) {
            return false;
        }

        $normalized = self::normalize($a);

        return $normalized !== null && $normalized === self::normalize($b);
    }

    public static function isZero(string $value): bool
    {
        return trim($value) !== '' && (bool) preg_match('/^[+-]?0*(\.0*)?$/', trim($value));
    }

    public static function isOne(string $value): bool
    {
        return (bool) preg_match('/^\+?0*1(\.0*)?$/', trim($value));
    }

    /**
     * Whether a text is a number with decimals ("12.50", "0.5") - not a whole number, and not an identifier
     * with a leading zero ("01.01", "007.1"), which is shown as written.
     */
    public static function isDecimal(string $value): bool
    {
        return (bool) preg_match('/^-?(?:0|[1-9]\d*)\.\d+$/', $value);
    }

    /**
     * The number of decimal places as written: "12.50" has two, "12" none.
     */
    public static function places(string $value): int
    {
        $point = strpos($value, '.');

        return $point === false ? 0 : strlen(rtrim($value)) - $point - 1;
    }

    /**
     * The amount with the opposite sign: "12.50" becomes "-12.50", zero stays as it is.
     */
    public static function negate(string $amount): string
    {
        $amount = trim($amount);
        if (str_starts_with($amount, '-')) {
            return substr($amount, 1);
        }

        return self::isZero($amount) ? $amount : '-' . ltrim($amount, '+');
    }

    private static function normalize(string $value): ?string
    {
        $number = self::parse($value);
        if ($number === null) {
            return null;
        }

        $zero = $number['integer'] === '0' && $number['fraction'] === '';

        return ($number['negative'] && ! $zero ? '-' : '') . $number['integer'] . ($number['fraction'] === '' ? '' : '.' . $number['fraction']);
    }
}
