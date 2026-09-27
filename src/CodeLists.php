<?php

declare(strict_types=1);

namespace Dealerweb\EInvoice;

/**
 * Names of code values in German, English and French, e.g. document type 380, payment means 58,
 * unit H87 or country DE, and the code lists a business term uses.
 *
 * Source: resources/compiled/codelists.php, compiled from the official EN 16931 code lists (all codes,
 * English names), CLDR (country, currency and language names) and the package's own translations for the
 * remaining names.
 *
 * @phpstan-type ListData array{title: string, version: string, usage: string, changes: string, remark: string, fields: list<string>, codes: array<array-key, array<string, string>>}
 */
final class CodeLists
{
    /** The languages of the names, the same as Labels::LANGUAGES. */
    public const LANGUAGES = Labels::LANGUAGES;

    /** @var array{source: array<string, string>, notes: list<string>, lists: array<string, ListData>, fields: array<string, list<string>>}|null */
    private static ?array $data = null;

    /** @var array<string, array<string, string>> list => lower-case code => code */
    private static array $lowerCase = [];

    /**
     * Name of a code, e.g. name(CodeList::DocumentType, '380', 'de') = "Handelsrechnung". Falls back to
     * English where no translation exists - unless $fallback is false, then only a name in $language
     * counts. Null for an unknown code or a list without names (the hybrid PDF lists, where the code
     * itself is the display value).
     */
    public static function name(CodeList $list, string $code, string $language = 'en', bool $fallback = true): ?string
    {
        $entry = self::entry($list, $code);
        if ($entry === null) {
            return null;
        }

        foreach ($fallback ? [$language, 'en'] : [$language] as $candidate) {
            if (in_array($candidate, self::LANGUAGES, true) && ($entry[$candidate] ?? '') !== '') {
                return $entry[$candidate];
            }
        }

        return null;
    }

    /**
     * Whether the list contains the code. Surrounding whitespace and letter case do not matter.
     */
    public static function contains(CodeList $list, string $code): bool
    {
        return self::entry($list, $code) !== null;
    }

    /**
     * All codes of a list in the order of the official publication.
     *
     * @return list<string>
     */
    public static function codes(CodeList $list): array
    {
        return array_map('strval', array_keys(self::list($list)['codes']));
    }

    /**
     * A further column of the official list, e.g. property(CodeList::DocumentType, '381', 'interpretation')
     * = "Credit Note", property(CodeList::VatExemptionReason, 'VATEX-EU-AE', 'remark'),
     * property(CodeList::PaymentMeans, '58', 'usage') = "SEPA", property(CodeList::EventTime, '5', 'syntax') = "cii".
     */
    public static function property(CodeList $list, string $code, string $property): ?string
    {
        if (in_array($property, self::LANGUAGES, true)) {
            return null;
        }

        return self::entry($list, $code)[$property] ?? null;
    }

    /**
     * The code lists a business term uses, e.g. forField('BT-81') = [CodeList::PaymentMeans]. BT-151 uses two:
     * the tax scheme (VAT) and the VAT category (S, Z, E, ...).
     *
     * @return list<CodeList>
     */
    public static function forField(string $id): array
    {
        return array_map(CodeList::from(...), self::data()['fields'][$id] ?? []);
    }

    /**
     * Name of the value of a business term, e.g. nameForField('BT-81', '58', 'de').
     */
    public static function nameForField(string $id, string $code, string $language = 'en'): ?string
    {
        foreach (self::forField($id) as $list) {
            if (self::contains($list, $code)) {
                return self::name($list, $code, $language);
            }
        }

        return null;
    }

    /**
     * What the Index of the official code lists says about a list.
     *
     * @return array{title: string, version: string, usage: string, changes: string, remark: string, fields: list<string>}
     */
    public static function info(CodeList $list): array
    {
        $info = self::list($list);
        unset($info['codes']);

        return $info;
    }

    /**
     * The publication the codes are taken from, e.g. "EN 16931 code lists v17b (valid from 2026-05-15), CLDR 48.2.2".
     */
    public static function source(): string
    {
        $source = self::data()['source'];

        return sprintf('%s (valid from %s), CLDR %s', $source['specification'], $source['effective'], $source['cldr']);
    }

    /**
     * @return array<string, string>|null
     */
    private static function entry(CodeList $list, string $code): ?array
    {
        $codes = self::list($list)['codes'];
        $code = trim($code);

        if (isset($codes[$code])) {
            return $codes[$code];
        }

        // The compiler guarantees that no two codes of a list differ only in case.
        if (! isset(self::$lowerCase[$list->value])) {
            self::$lowerCase[$list->value] = [];
            foreach (array_keys($codes) as $known) {
                self::$lowerCase[$list->value][strtolower((string) $known)] = (string) $known;
            }
        }

        $known = self::$lowerCase[$list->value][strtolower($code)] ?? null;

        return $known === null ? null : $codes[$known];
    }

    /**
     * @return ListData
     */
    private static function list(CodeList $list): array
    {
        return self::data()['lists'][$list->value];
    }

    /**
     * @return array{source: array<string, string>, notes: list<string>, lists: array<string, ListData>, fields: array<string, list<string>>}
     */
    private static function data(): array
    {
        return self::$data ??= require dirname(__DIR__) . '/resources/compiled/codelists.php';
    }
}
