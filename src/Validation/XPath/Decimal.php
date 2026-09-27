<?php

declare(strict_types=1);

namespace Dealerweb\EInvoice\Validation\XPath;

/**
 * An exact decimal number of any size: xs:decimal, or xs:integer ($integer = true). In a sequence an xs:integer that
 * fits into a PHP int is a plain int; as Decimal it appears beyond that range and inside arithmetic. Value =
 * (-1)^negative * digits * 10^-scale, always normalized: no leading zeros, no trailing zeros after the point, zero is
 * positive.
 *
 * Arithmetic exactly as Saxon 12 does it with Java's BigDecimal: +, -, * exact; idiv truncates; mod takes the sign of
 * the dividend; div rounds HALF_DOWN to max(18, scale(a) - scale(b) + 18) places, where the scale is Java's: an
 * xs:decimal is held with trailing zeros stripped (100 has scale -2), an xs:integer has scale 0 (Calculator.
 * decimalDivide). So 2 div 3 = 0.666666666666666667 and 1.5 div 300.0 has 21 places.
 *
 * @internal
 */
final class Decimal
{
    /** Places a quotient keeps at least (Saxon BigDecimalValue.DIVIDE_PRECISION). */
    public const DIVIDE_PRECISION = 18;

    private const LEXICAL = '/^[ \t\r\n]*([+-]?)(?:([0-9]+)(?:\.([0-9]*))?|\.([0-9]+))[ \t\r\n]*$/D';

    private function __construct(
        public readonly bool $negative,
        /** Digits without leading zeros, "0" for zero. */
        public readonly string $digits,
        /** Digits after the decimal point. */
        public readonly int $scale,
        /** Of type xs:integer (always $scale = 0). */
        public readonly bool $integer = false,
    ) {}

    /**
     * From the lexical form of xs:decimal ("-12.50", ".5", "3."); null where the text is no decimal (an exponent is not
     * allowed).
     */
    public static function parse(string $text): ?self
    {
        if (! preg_match(self::LEXICAL, $text, $match)) {
            return null;
        }
        $whole = ($match[2] ?? '') !== '' ? $match[2] : '';
        $fraction = ($match[4] ?? '') !== '' ? $match[4] : ($match[3] ?? '');

        return self::make($match[1] === '-', $whole . $fraction, strlen($fraction));
    }

    /**
     * From a literal or other text known to be a valid decimal.
     */
    public static function of(string $text): self
    {
        return self::parse($text) ?? throw new DynamicError('FORG0001', "Invalid decimal '$text'.");
    }

    /**
     * An int as xs:integer - asDecimal() gives the same value as xs:decimal.
     */
    public static function fromInt(int $value): self
    {
        if ($value === PHP_INT_MIN) {
            return self::make(true, substr((string) $value, 1), 0, true);
        }

        return self::make($value < 0, (string) abs($value), 0, true);
    }

    /**
     * An xs:integer value from digits; a PHP int where it fits.
     */
    public static function integer(bool $negative, string $digits): int|self
    {
        $digits = ltrim($digits, '0');
        if ($digits === '') {
            return 0;
        }
        if (strlen($digits) < 19 || (strlen($digits) === 19 && strcmp($digits, (string) PHP_INT_MAX) <= 0)) {
            return $negative ? -(int) $digits : (int) $digits;
        }
        if ($negative && $digits === substr((string) PHP_INT_MIN, 1)) {
            return PHP_INT_MIN;
        }

        return new self($negative, $digits, 0, true);
    }

    /**
     * The exact value of a finite double - every double is a binary fraction with a finite decimal expansion.
     */
    public static function fromFloat(float $value): self
    {
        if (! is_finite($value)) {
            throw new DynamicError('FOCA0002', 'Cannot convert ' . Value::doubleToString($value) . ' to xs:decimal.');
        }
        if ($value == 0.0) {
            return self::make(false, '0', 0);
        }

        // value = mantissa / 2^n: doubling is exact, and the integer reached has at most 53 significant bits, so it is
        // a double itself and "%.0f" prints it exactly (as it does any integral double).
        $negative = $value < 0;
        $value = abs($value);
        $exponent = 0;
        while ($value != floor($value)) {
            $value *= 2;
            $exponent++;
        }
        $mantissa = self::make(false, sprintf('%.0f', $value), 0);
        if ($exponent > 0) {
            // m / 2^n = m * 5^n / 10^n
            $scaled = $mantissa->multiply(self::make(false, self::powerOfFive($exponent), 0));
            $mantissa = self::make(false, $scaled->digits, $exponent);
        }

        return $negative ? $mantissa->negate() : $mantissa;
    }

    public function isZero(): bool
    {
        return $this->digits === '0';
    }

    public function isInteger(): bool
    {
        return $this->scale === 0;
    }

    public function negate(): self
    {
        return $this->isZero() ? $this : new self(! $this->negative, $this->digits, $this->scale, $this->integer);
    }

    public function abs(): self
    {
        return $this->negative ? $this->negate() : $this;
    }

    public function add(self $other): self
    {
        $scale = max($this->scale, $other->scale);
        $a = $this->digits . str_repeat('0', $scale - $this->scale);
        $b = $other->digits . str_repeat('0', $scale - $other->scale);

        if ($this->negative === $other->negative) {
            return self::make($this->negative, self::addDigits($a, $b), $scale);
        }
        $comparison = self::compareDigits($a, $b);
        if ($comparison === 0) {
            return self::make(false, '0', 0);
        }

        return $comparison > 0
            ? self::make($this->negative, self::subtractDigits($a, $b), $scale)
            : self::make($other->negative, self::subtractDigits($b, $a), $scale);
    }

    public function subtract(self $other): self
    {
        return $this->add($other->negate());
    }

    public function multiply(self $other): self
    {
        return self::make($this->negative !== $other->negative, self::multiplyDigits($this->digits, $other->digits), $this->scale + $other->scale);
    }

    /**
     * Quotient as xs:decimal: max(18, scale(a) - scale(b) + 18) places with Java's scales (javaScale()), rounded
     * HALF_DOWN - Saxon's Calculator.decimalDivide.
     *
     * @throws DynamicError FOAR0001 division by zero
     */
    public function divide(self $other): self
    {
        if ($other->isZero()) {
            throw new DynamicError('FOAR0001', 'Decimal divide by zero.');
        }
        $places = max(self::DIVIDE_PRECISION, $this->javaScale() - $other->javaScale() + self::DIVIDE_PRECISION);

        // a / b * 10^places = (A * 10^-sa) / (B * 10^-sb) * 10^places = A * 10^(places - sa + sb) / B, with the
        // digits A, B and scales sa, sb of the normalized form; the shift is at least 18 (places >= sa - sb + 18).
        $shift = $places - $this->scale + $other->scale;
        [$quotient, $remainder] = self::divideDigits($this->digits . str_repeat('0', $shift), $other->digits);
        // HALF_DOWN: away from zero only where the rest is more than half the divisor
        if (self::compareDigits(self::addDigits($remainder, $remainder), $other->digits) > 0) {
            $quotient = self::addDigits($quotient, '1');
        }

        return self::make($this->negative !== $other->negative, $quotient, $places);
    }

    /**
     * The scale Java's BigDecimal has for this value in Saxon: 0 for an xs:integer, for an xs:decimal the scale after
     * stripTrailingZeros() - negative for trailing zeros before the point (100 = 1E+2, scale -2).
     */
    public function javaScale(): int
    {
        if ($this->integer || $this->digits === '0') {
            return 0;
        }
        if ($this->scale > 0) {
            return $this->scale;
        }

        return strlen(rtrim($this->digits, '0')) - strlen($this->digits);
    }

    /**
     * Integer division, truncated toward zero.
     *
     * @throws DynamicError FOAR0001 division by zero
     */
    public function integerDivide(self $other): int|self
    {
        if ($other->isZero()) {
            throw new DynamicError('FOAR0001', 'Integer division by zero.');
        }
        $scale = max($this->scale, $other->scale);
        $a = $this->digits . str_repeat('0', $scale - $this->scale);
        $b = $other->digits . str_repeat('0', $scale - $other->scale);
        [$quotient] = self::divideDigits($a, $b);

        return self::integer($this->negative !== $other->negative, $quotient);
    }

    /**
     * Remainder with the sign of the dividend: a - b * (a idiv b).
     *
     * @throws DynamicError FOAR0001 division by zero
     */
    public function modulo(self $other): self
    {
        if ($other->isZero()) {
            throw new DynamicError('FOAR0001', 'Modulus by zero.');
        }
        $scale = max($this->scale, $other->scale);
        $a = $this->digits . str_repeat('0', $scale - $this->scale);
        $b = $other->digits . str_repeat('0', $scale - $other->scale);
        [, $remainder] = self::divideDigits($a, $b);

        return self::make($this->negative, $remainder, $scale, $this->integer && $other->integer);
    }

    public function compare(self $other): int
    {
        if ($this->negative !== $other->negative) {
            return $this->negative ? -1 : 1;
        }
        $scale = max($this->scale, $other->scale);
        $result = self::compareDigits(
            $this->digits . str_repeat('0', $scale - $this->scale),
            $other->digits . str_repeat('0', $scale - $other->scale),
        );

        return $this->negative ? -$result : $result;
    }

    /**
     * Rounded to the given number of places, a half toward positive infinity (fn:round).
     */
    public function round(int $places = 0): self
    {
        if ($this->scale <= $places) {
            return $this;
        }
        $floor = $this->floor($places);
        $half = self::make(false, '5', $places + 1);

        return $this->subtract($floor)->compare($half) >= 0 ? $floor->add(self::make(false, '1', $places)) : $floor;
    }

    /**
     * The largest number with the given places that is not greater than this one.
     */
    public function floor(int $places = 0): self
    {
        if ($this->scale <= $places) {
            return $this;
        }
        $cut = strlen($this->digits) - ($this->scale - $places);
        $kept = $cut > 0 ? substr($this->digits, 0, $cut) : '0';
        $truncated = self::make($this->negative, $kept, $places);
        if (! $this->negative || $truncated->compare($this) === 0) {
            return $truncated;
        }

        return $truncated->subtract(self::make(false, '1', $places));
    }

    public function ceiling(): self
    {
        return $this->negate()->floor()->negate();
    }

    /**
     * The same value as xs:integer (a PHP int where it fits) - only for values without places.
     */
    public function toInteger(): int|self
    {
        if ($this->scale !== 0) {
            $truncated = $this->negative ? $this->ceiling() : $this->floor();

            return self::integer($truncated->negative, $truncated->digits);
        }

        return self::integer($this->negative, $this->digits);
    }

    public function asDecimal(): self
    {
        return $this->integer ? new self($this->negative, $this->digits, $this->scale) : $this;
    }

    public function toFloat(): float
    {
        return (float) $this->toString();
    }

    /**
     * Canonical form of xs:decimal: no exponent, no trailing zeros, "0.5" not ".5", "12" not "12.0".
     */
    public function toString(): string
    {
        if ($this->scale === 0) {
            $text = $this->digits;
        } else {
            $padded = str_pad($this->digits, $this->scale + 1, '0', STR_PAD_LEFT);
            $text = substr($padded, 0, -$this->scale) . '.' . substr($padded, -$this->scale);
        }

        return ($this->negative ? '-' : '') . $text;
    }

    private static function make(bool $negative, string $digits, int $scale, bool $integer = false): self
    {
        $digits = ltrim($digits, '0');
        if ($digits === '') {
            return new self(false, '0', 0, $integer);
        }
        if ($scale > 0) {
            $trailing = strlen($digits) - strlen(rtrim($digits, '0'));
            $strip = min($trailing, $scale);
            if ($strip > 0) {
                $digits = substr($digits, 0, -$strip);
                $scale -= $strip;
            }
        }

        return new self($negative, $digits, $scale, $integer && $scale === 0);
    }

    private static function compareDigits(string $a, string $b): int
    {
        $a = ltrim($a, '0');
        $b = ltrim($b, '0');

        return strlen($a) <=> strlen($b) ?: strcmp($a, $b) <=> 0;
    }

    private static function addDigits(string $a, string $b): string
    {
        $length = max(strlen($a), strlen($b));
        $a = str_pad($a, $length, '0', STR_PAD_LEFT);
        $b = str_pad($b, $length, '0', STR_PAD_LEFT);
        $result = '';
        $carry = 0;
        // 9-digit chunks from the right: a sum stays far below PHP_INT_MAX.
        for ($end = $length; $end > 0; $end -= 9) {
            $start = max(0, $end - 9);
            $sum = (int) substr($a, $start, $end - $start) + (int) substr($b, $start, $end - $start) + $carry;
            $carry = intdiv($sum, 1_000_000_000);
            $result = str_pad((string) ($sum % 1_000_000_000), $end - $start, '0', STR_PAD_LEFT) . $result;
        }

        return ($carry > 0 ? (string) $carry : '') . $result;
    }

    /**
     * $a - $b for $a >= $b.
     */
    private static function subtractDigits(string $a, string $b): string
    {
        $length = max(strlen($a), strlen($b));
        $a = str_pad($a, $length, '0', STR_PAD_LEFT);
        $b = str_pad($b, $length, '0', STR_PAD_LEFT);
        $result = '';
        $borrow = 0;
        for ($end = $length; $end > 0; $end -= 9) {
            $start = max(0, $end - 9);
            $difference = (int) substr($a, $start, $end - $start) - (int) substr($b, $start, $end - $start) - $borrow;
            $borrow = 0;
            if ($difference < 0) {
                $difference += 10 ** ($end - $start);
                $borrow = 1;
            }
            $result = str_pad((string) $difference, $end - $start, '0', STR_PAD_LEFT) . $result;
        }

        return ltrim($result, '0') ?: '0';
    }

    private static function multiplyDigits(string $a, string $b): string
    {
        if ($a === '0' || $b === '0') {
            return '0';
        }
        // Limbs of 7 digits, least significant first: a product of two limbs plus carries stays below 2^63.
        $limbsA = self::limbs($a);
        $limbsB = self::limbs($b);
        $product = array_fill(0, count($limbsA) + count($limbsB), 0);
        foreach ($limbsA as $i => $x) {
            $carry = 0;
            foreach ($limbsB as $j => $y) {
                $value = $product[$i + $j] + $x * $y + $carry;
                $carry = intdiv($value, 10_000_000);
                $product[$i + $j] = $value % 10_000_000;
            }
            $k = $i + count($limbsB);
            while ($carry > 0) {
                $value = $product[$k] + $carry;
                $carry = intdiv($value, 10_000_000);
                $product[$k] = $value % 10_000_000;
                $k++;
            }
        }

        $text = '';
        foreach (array_reverse($product) as $limb) {
            $text .= str_pad((string) $limb, 7, '0', STR_PAD_LEFT);
        }

        return ltrim($text, '0') ?: '0';
    }

    /**
     * @return list<int> 7-digit limbs, least significant first
     */
    private static function limbs(string $digits): array
    {
        $limbs = [];
        for ($end = strlen($digits); $end > 0; $end -= 7) {
            $start = max(0, $end - 7);
            $limbs[] = (int) substr($digits, $start, $end - $start);
        }

        return $limbs;
    }

    /**
     * Long division of non-negative integers.
     *
     * @return array{0: string, 1: string} quotient and remainder
     */
    private static function divideDigits(string $dividend, string $divisor): array
    {
        $divisor = ltrim($divisor, '0');
        if (strlen($divisor) <= 15) {
            // The divisor fits a PHP int: one pass digit by digit, the remainder stays below divisor * 10.
            $small = (int) $divisor;
            $quotient = '';
            $remainder = 0;
            $length = strlen($dividend);
            for ($i = 0; $i < $length; $i++) {
                $remainder = $remainder * 10 + (int) $dividend[$i];
                $quotient .= (string) intdiv($remainder, $small);
                $remainder %= $small;
            }

            return [ltrim($quotient, '0') ?: '0', (string) $remainder];
        }

        $quotient = '';
        $remainder = '0';
        $length = strlen($dividend);
        for ($i = 0; $i < $length; $i++) {
            $remainder = ltrim($remainder . $dividend[$i], '0') ?: '0';
            $digit = 0;
            while (self::compareDigits($remainder, $divisor) >= 0) {
                $remainder = self::subtractDigits($remainder, $divisor);
                $digit++;
            }
            $quotient .= (string) $digit;
        }

        return [ltrim($quotient, '0') ?: '0', $remainder];
    }

    private static function powerOfFive(int $exponent): string
    {
        $result = self::make(false, '1', 0);
        $five = self::make(false, '5', 0);
        for ($i = 0; $i < $exponent; $i++) {
            $result = $result->multiply($five);
        }

        return $result->digits;
    }
}
