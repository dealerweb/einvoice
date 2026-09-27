<?php

declare(strict_types=1);

namespace Dealerweb\EInvoice\Render;

/**
 * Numbers and dates of the invoice written the way a reader of the language expects them.
 *
 * Works on the decimal strings of the invoice, never on floats: 1234.5 stays exact and keeps
 * every significant decimal ("0.1234" per unit remains "0,1234").
 *
 * @internal
 */
final class Format
{
    /** Decimal and thousands separator per language (French groups with a no-break space). */
    private const SEPARATORS = [
        'de' => [',', '.'],
        'en' => ['.', ','],
        'fr' => [',', "\u{00A0}"],
    ];

    private const MONTHS_EN = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

    public function __construct(private readonly string $language) {}

    /**
     * A decimal number with at least $decimals decimals; further decimals are kept where they are
     * not zero. Anything that is not a plain decimal number is returned as written.
     */
    public function number(string $value, int $decimals = 0): string
    {
        $value = trim($value);
        $number = Decimal::parse($value);
        if ($number === null) {
            return $value;
        }

        $integer = $number['integer'];
        $fraction = str_pad($number['fraction'], $decimals, '0');
        [$decimalSeparator, $thousandsSeparator] = self::SEPARATORS[$this->language] ?? self::SEPARATORS['en'];

        // Group the digits from the right; the separator is inserted afterwards - it may be a multibyte
        // character (French no-break space) that must never pass through strrev().
        $groups = array_map('strrev', array_reverse(str_split(strrev($integer), 3)));
        $grouped = implode($thousandsSeparator, $groups);
        $negative = $number['negative'] && trim($integer . $fraction, '0') !== '';

        return ($negative ? '-' : '') . $grouped . ($fraction !== '' ? $decimalSeparator . $fraction : '');
    }

    /**
     * Money: at least two decimals ("1.234,50").
     */
    public function amount(string $value): string
    {
        return $this->number($value, 2);
    }

    /**
     * Amount with its currency code: "1.234,50 EUR".
     */
    public function money(string $value, ?string $currency): string
    {
        return $this->amount($value) . ($currency !== null && $currency !== '' ? "\u{00A0}" . $currency : '');
    }

    /**
     * Quantities without trailing zeros ("20.0000" becomes "20", "2.500" becomes "2,5").
     */
    public function quantity(string $value): string
    {
        return $this->number($value);
    }

    /**
     * Percentages without trailing zeros: "19 %", "7,5 %" (English "19%").
     */
    public function percent(string $value): string
    {
        return $this->number($value) . ($this->language === 'en' ? '%' : "\u{00A0}%");
    }

    /**
     * ISO dates (the model normalizes every date to YYYY-MM-DD): 15.11.2024, 15/11/2024, 15 Nov 2024.
     * Anything else - e.g. the marker of an impossible date - is returned as written.
     */
    public function date(string $value): string
    {
        if (! preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', trim($value), $match)) {
            return $value;
        }

        [, $year, $month, $day] = $match;

        return match ($this->language) {
            'de' => "$day.$month.$year",
            'fr' => "$day/$month/$year",
            default => ltrim($day, '0') . ' ' . (self::MONTHS_EN[(int) $month - 1] ?? $month) . ' ' . $year,
        };
    }

    /**
     * IBAN in groups of four ("DE02 1203 0000 0000 2020 51"); other account identifiers as written.
     */
    public function account(string $value): string
    {
        return $this->isIban($value) ? trim(chunk_split(self::compact($value), 4, ' ')) : trim($value);
    }

    /**
     * Whether an account identifier is an IBAN.
     */
    public function isIban(string $value): bool
    {
        return (bool) preg_match('/^[A-Z]{2}\d{2}[A-Z0-9]{8,30}$/', self::compact($value));
    }

    /**
     * Size of a file in bytes as "812 B", "45 KB", "1,2 MB".
     */
    public function size(int $bytes): string
    {
        if ($bytes < 1024) {
            return $bytes . "\u{00A0}B";
        }

        if ($bytes < 1024 * 1024) {
            return $this->number((string) (int) round($bytes / 1024)) . "\u{00A0}KB";
        }

        return $this->number(number_format($bytes / 1024 / 1024, 1, '.', '')) . "\u{00A0}MB";
    }

    /**
     * An account identifier without blanks, in capitals.
     */
    private static function compact(string $value): string
    {
        return strtoupper(preg_replace('/\s+/', '', $value) ?? $value);
    }
}
