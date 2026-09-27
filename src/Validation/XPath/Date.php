<?php

declare(strict_types=1);

namespace Dealerweb\EInvoice\Validation\XPath;

/**
 * xs:date: year, month, day and an optional timezone. A date without timezone is compared in the implicit timezone -
 * Saxon takes the offset of the default timezone of the JVM at the time of the run, the evaluator the one of PHP
 * (date.timezone): the verdict on an invoice that compares a date with a timezone to one without depends on the server
 * in both.
 *
 * @internal
 */
final class Date
{
    private const LEXICAL = '/^[ \t\r\n]*(-?)(\d{4,})-(\d{2})-(\d{2})(Z|[+-]\d{2}:\d{2})?[ \t\r\n]*$/D';

    /** The implicit timezone of XPath's dynamic context, offset from UTC in minutes. */
    private static int $implicitTimezone = 0;

    private function __construct(
        public readonly int $year,
        public readonly int $month,
        public readonly int $day,
        /** Offset from UTC in minutes, null without timezone. */
        public readonly ?int $timezone,
    ) {}

    /**
     * From the lexical form of xs:date, e.g. "2026-09-25" or "2026-09-25+02:00"; null where the text is no valid date.
     */
    public static function parse(string $text): ?self
    {
        if (! preg_match(self::LEXICAL, $text, $match)) {
            return null;
        }
        if (strlen($match[2]) > 4 && $match[2][0] === '0') {
            return null;
        }
        if (strlen($match[2]) > 10 || (strlen($match[2]) === 10 && strcmp($match[2], '2147483647') > 0)) {
            // Saxon keeps years in an int (Date::yearOutOfRange)
            return null;
        }
        $year = (int) ($match[1] . $match[2]);
        $month = (int) $match[3];
        $day = (int) $match[4];
        // Year 0 exists (XML Schema 1.1, as Saxon 12): 1 BC, a leap year.
        if ($month < 1 || $month > 12 || $day < 1 || $day > self::daysInMonth($year, $month)) {
            return null;
        }

        $timezone = null;
        if (($match[5] ?? '') === 'Z') {
            $timezone = 0;
        } elseif (($match[5] ?? '') !== '') {
            $hours = (int) substr($match[5], 1, 2);
            $minutes = (int) substr($match[5], 4, 2);
            if ($minutes > 59 || $hours * 60 + $minutes > 14 * 60) {
                return null;
            }
            $timezone = ($match[5][0] === '-' ? -1 : 1) * ($hours * 60 + $minutes);
        }

        return new self($year, $month, $day, $timezone);
    }

    /**
     * Whether Saxon rejects the text as a year it cannot handle (FODT0001) rather than as an invalid date: the year
     * is the text up to the first of "-:+TZ" (after a leading minus); Saxon reads its digits into an int and gives
     * up as soon as the number exceeds 2^31 - 1, before it checks the rest (GDateValue.setLexicalValue).
     */
    public static function yearOutOfRange(string $text): bool
    {
        $text = trim($text, " \t\r\n");
        if (str_starts_with($text, '-')) {
            $text = substr($text, 1);
        }
        $year = substr($text, 0, strcspn($text, '-:+TZ'));
        if (strlen($year) < 4 || (strlen($year) > 4 && $year[0] === '0')) {
            return false;
        }
        // the digits Saxon reads before it meets anything else: their value only grows, so it exceeds 2^31 - 1 at
        // some digit exactly when the value of all of them does
        $digits = ltrim(substr($year, 0, strspn($year, '0123456789')), '0');

        return strlen($digits) > 10 || (strlen($digits) === 10 && strcmp($digits, '2147483647') > 0);
    }

    /**
     * The timezone a date without one is taken to be in when it is compared.
     */
    public static function setImplicitTimezone(int $minutes): void
    {
        self::$implicitTimezone = $minutes;
    }

    public function compare(self $other): int
    {
        return $this->instant() <=> $other->instant();
    }

    /**
     * Canonical form: "2026-09-25", with "Z" for UTC.
     */
    public function toString(): string
    {
        $text = ($this->year < 0 ? '-' : '') . str_pad((string) abs($this->year), 4, '0', STR_PAD_LEFT)
            . sprintf('-%02d-%02d', $this->month, $this->day);
        if ($this->timezone === null) {
            return $text;
        }
        if ($this->timezone === 0) {
            return $text . 'Z';
        }

        return $text . ($this->timezone < 0 ? '-' : '+') . sprintf('%02d:%02d', intdiv(abs($this->timezone), 60), abs($this->timezone) % 60);
    }

    /**
     * Minutes since 0001-01-01T00:00Z of the start of the day.
     */
    private function instant(): int
    {
        // Days from civil (proleptic Gregorian), H. Hinnant's algorithm.
        $year = $this->year - ($this->month <= 2 ? 1 : 0);
        $era = intdiv($year >= 0 ? $year : $year - 399, 400);
        $yearOfEra = $year - $era * 400;
        $dayOfYear = intdiv(153 * ($this->month + ($this->month > 2 ? -3 : 9)) + 2, 5) + $this->day - 1;
        $dayOfEra = $yearOfEra * 365 + intdiv($yearOfEra, 4) - intdiv($yearOfEra, 100) + $dayOfYear;
        $days = $era * 146097 + $dayOfEra;

        return $days * 1440 - ($this->timezone ?? self::$implicitTimezone);
    }

    private static function daysInMonth(int $year, int $month): int
    {
        if ($month === 2) {
            $leap = ($year % 4 === 0 && $year % 100 !== 0) || $year % 400 === 0;

            return $leap ? 29 : 28;
        }

        return in_array($month, [4, 6, 9, 11], true) ? 30 : 31;
    }
}
