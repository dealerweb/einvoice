<?php

declare(strict_types=1);

namespace Dealerweb\EInvoice\Validation\XPath;

use LogicException;

/**
 * xs:double as string, digit for digit as Saxon writes it (net.sf.saxon.value.FloatingPointConverter): the free-format
 * algorithm (FPP)² of Steele and White - the shortest digits that identify the double - in plain notation from 1.0E-6
 * up to (not including) 1.0E6, else "1.5E-7" with at least one digit after the point. Subnormal doubles follow Java's
 * Double.toString (shortest digits, two where one would do, the closer of them).
 *
 * @internal
 */
final class DoubleFormat
{
    private const MAX = 1.7976931348623157E308;

    private const MIN = 4.9E-324;

    public static function format(float $value): string
    {
        if (is_nan($value)) {
            return 'NaN';
        }
        if (is_infinite($value)) {
            return $value > 0 ? 'INF' : '-INF';
        }
        if ($value == 0.0) {
            return fdiv(1, $value) < 0 ? '-0' : '0';
        }
        if (abs($value) === self::MAX) {
            return ($value < 0 ? '-' : '') . '1.7976931348623157E308';
        }
        if (abs($value) === self::MIN) {
            return ($value < 0 ? '-' : '') . '4.9E-324';
        }

        $sign = $value < 0 ? '-' : '';
        $value = abs($value);
        $bits = self::bits($value);
        $rawExponent = ($bits >> 52) & 0x7FF;
        if ($rawExponent === 0) {
            return $sign . self::subnormal($value);
        }
        $fraction = (1 << 52) | ($bits & 0xFFFFFFFFFFFFF);
        $exponent = $rawExponent - 1023;
        $exponential = $value >= 1000000 || $value < 0.000001;

        return $sign . self::steeleWhite($exponent, $fraction, $exponential);
    }

    /**
     * (FPP)² for value = fraction * 2^(exponent - 52).
     */
    private static function steeleWhite(int $exponent, int $fraction, bool $exponential): string
    {
        $p = 52;
        $ten = Decimal::fromInt(10);
        $r = self::shift(Decimal::fromInt($fraction), max($exponent - $p, 0));
        $s = self::shift(Decimal::fromInt(1), max(0, -($exponent - $p)));
        $mMinus = self::shift(Decimal::fromInt(1), max($exponent - $p, 0));
        $mPlus = $mMinus;

        // simple fixup
        if ($fraction === 1 << ($p - 1)) {
            $mPlus = self::shift($mPlus, 1);
            $r = self::shift($r, 1);
            $s = self::shift($s, 1);
        }
        $k = 0;
        // (S + 9) / 10 = ceiling(S / 10)
        while ($r->compare(self::quotient($s->add(Decimal::fromInt(9)), $ten)) < 0) {
            $k--;
            $r = $r->multiply($ten);
            $mMinus = $mMinus->multiply($ten);
            $mPlus = $mPlus->multiply($ten);
        }
        while (self::shift($r, 1)->add($mPlus)->compare(self::shift($s, 1)) >= 0) {
            $s = $s->multiply($ten);
            $k++;
        }

        $digits = [];
        $firstExponent = $k - 1;
        while (true) {
            $k--;
            [$digit, $r] = self::digit($r->multiply($ten), $s);
            $mMinus = $mMinus->multiply($ten);
            $mPlus = $mPlus->multiply($ten);
            $twiceR = self::shift($r, 1);
            $low = $twiceR->compare($mMinus) < 0;
            $high = $twiceR->compare(self::shift($s, 1)->subtract($mPlus)) > 0;
            if ($low || $high) {
                break;
            }
            $digits[] = $digit;
        }
        if ($high && (! $low || self::shift($r, 1)->compare($s) > 0)) {
            $digit++;
        }
        $digits[] = $digit;

        if ($exponential) {
            $text = $digits[0] . '.' . (count($digits) > 1 ? implode('', array_slice($digits, 1)) : '0');

            return $text . 'E' . $firstExponent;
        }

        // Plain: the digits are d1 d2 ... with d1 at 10^firstExponent.
        $digitText = implode('', $digits);
        $point = $firstExponent + 1;
        if ($point <= 0) {
            return '0.' . str_repeat('0', -$point) . $digitText;
        }
        if ($point >= strlen($digitText)) {
            return $digitText . str_repeat('0', $point - strlen($digitText));
        }

        return substr($digitText, 0, $point) . '.' . substr($digitText, $point);
    }

    /**
     * The next digit: floor(value / divisor), known to be 0 to 9, and the remainder.
     *
     * @return array{0: int, 1: Decimal}
     */
    private static function digit(Decimal $value, Decimal $divisor): array
    {
        $digit = 0;
        while ($value->compare($divisor) >= 0) {
            $value = $value->subtract($divisor);
            $digit++;
        }

        return [$digit, $value];
    }

    private static function quotient(Decimal $dividend, Decimal $divisor): Decimal
    {
        $quotient = $dividend->integerDivide($divisor);

        return is_int($quotient) ? Decimal::fromInt($quotient) : $quotient;
    }

    private static function shift(Decimal $value, int $bits): Decimal
    {
        while ($bits > 0) {
            $step = min($bits, 30);
            $value = $value->multiply(Decimal::fromInt(1 << $step));
            $bits -= $step;
        }

        return $value;
    }

    /**
     * Java's Double.toString for a subnormal double: the shortest digits that read back as the value; where one digit
     * would do, the closer of the one- and two-digit forms. Always "d.dE-n".
     */
    private static function subnormal(float $value): string
    {
        $text = '';
        for ($precision = 1; $precision <= 17; $precision++) {
            $text = sprintf('%.' . ($precision - 1) . 'e', $value);
            if ((float) $text === $value) {
                break;
            }
        }
        if ($precision === 1) {
            $two = sprintf('%.1e', $value);
            $exact = Decimal::fromFloat($value);
            $distanceOne = $exact->subtract(Decimal::fromFloat((float) $text))->abs();
            $distanceTwo = $exact->subtract(Decimal::fromFloat((float) $two))->abs();
            if ($distanceTwo->compare($distanceOne) < 0) {
                $text = $two;
            }
        }
        if (preg_match('/^(\d)(?:\.(\d+))?e([+-]\d+)$/', $text, $match) !== 1) {
            throw new LogicException("Unexpected form of a double: $text");
        }
        $fraction = rtrim($match[2], '0');

        return $match[1] . '.' . ($fraction === '' ? '0' : $fraction) . 'E' . (int) $match[3];
    }

    /**
     * The 64 bits of the double.
     */
    private static function bits(float $value): int
    {
        $bits = unpack('J', pack('E', $value));
        if ($bits === false || ! is_int($bits[1] ?? null)) {
            throw new LogicException('Cannot read the bits of a double.');
        }

        return $bits[1];
    }
}
