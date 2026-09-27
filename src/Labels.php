<?php

declare(strict_types=1);

namespace Dealerweb\EInvoice;

use DOMAttr;
use DOMElement;
use DOMNode;

/**
 * Names of the fields and groups of an invoice in German, English and French - the package's own names: the chain of
 * the properties of the model that lead to a field, e.g. "Position › Verkäufer › Registernummer" for the legal
 * registration identifier of the seller of a line.
 *
 * The ids and paths are those of the Factur-X / ZUGFeRD field list of the FeRD release package: every EN 16931
 * business term and group (BT / BG) and every element of the EXTENDED profile (BT-X / BG-X)
 * (resources/compiled/facturx-fields.php, compiled from that list).
 */
final class Labels
{
    /** The languages of the package: names of business terms and codes, rendering. */
    public const LANGUAGES = ['de', 'en', 'fr'];

    /** Between the parts of a name. */
    public const SEPARATOR = ' › ';

    /** The properties of the model on the charge side of the elements allowances and charges share in CII. */
    private const CHARGES = ['charges', 'priceCharges'];

    /** @var array{source: array<string, string>, terms: array<string, array{de: string, en: string, fr: string}>, fields: array<string, array{path: string, name: list<string>, depths: list<int|null>, type: string|null, profiles: list<string>}>, paths: array<string, list<string>>}|null */
    private static ?array $data = null;

    /**
     * Name of a field or group, e.g. name('BT-1', 'fr') = "Numéro de facture", name('BT-X-22', 'de') =
     * "Position › Bestellung › Datum". Another language than de, en or fr gives the English name.
     */
    public static function name(string $id, string $language = 'en'): ?string
    {
        $parts = self::parts($id, $language);

        return $parts === [] ? null : implode(self::SEPARATOR, $parts);
    }

    /**
     * The parts of the name of a field or group, without those a view already says: the parts down to the element at
     * $depth (the number of element names below the root down to the element the value is shown in) - in the block of
     * a line "Verkäufer › Registernummer" instead of "Position › Verkäufer › Registernummer". The last part stays.
     *
     * @internal used by UnmappedValue, the rendering and the tools
     * @return list<string> empty for an unknown id
     */
    public static function parts(string $id, string $language = 'en', int $depth = 0): array
    {
        $field = self::data()['fields'][$id] ?? null;
        if ($field === null) {
            return [];
        }

        $language = in_array($language, self::LANGUAGES, true) ? $language : 'en';
        $terms = self::data()['terms'];
        $parts = array_map(static fn(string $key): string => $terms[$key][$language], $field['name']);

        $said = 0;
        foreach ($field['depths'] as $index => $partDepth) {
            if ($partDepth !== null && $partDepth <= $depth) {
                $said = $index + 1;
            }
        }

        return array_slice($parts, min($said, count($parts) - 1));
    }

    /**
     * The number of element names below the root down to the element of a field or group, e.g. 2 for a line (BG-25);
     * null for an unknown id.
     *
     * @internal used by the rendering
     */
    public static function depth(string $id): ?int
    {
        $path = self::data()['fields'][$id]['path'] ?? null;

        return $path === null ? null : substr_count($path, '/') + 1;
    }

    /**
     * The semantic data type of a field as EN 16931 names it - Amount, Unit Price Amount, Quantity, Percentage,
     * Rate, Code, Identifier, Date, Document Reference, Text, String, Binary Object or Indicator; null for a
     * group or an unknown id.
     *
     * @internal used by the rendering
     */
    public static function dataType(string $id): ?string
    {
        return self::data()['fields'][$id]['type'] ?? null;
    }

    /**
     * Field id of a node of a CII document, e.g. BT-X-1 for ExchangedDocumentContext/TestIndicator/Indicator.
     * Allowances and charges share their elements - the indicator of the enclosing allowance/charge decides.
     *
     * @internal used by Coverage
     */
    public static function ciiId(DOMNode $node): ?string
    {
        $candidates = self::data()['paths'][self::ciiPath($node)] ?? [];
        if (count($candidates) < 2) {
            return $candidates[0] ?? null;
        }

        $charge = self::isCharge($node);
        if ($charge === null) {
            return $candidates[0];
        }

        $matching = array_values(array_filter(
            $candidates,
            static fn(string $id): bool => self::isChargeField(self::data()['fields'][$id]['name']) === $charge,
        ));
        // The reason code of a charge doubles as the code of a tax other than VAT (with a list id): the reason is the rule.
        foreach ($matching as $id) {
            $name = self::data()['fields'][$id]['name'];
            if (end($name) !== 'taxTypeCode') {
                return $id;
            }
        }

        return $matching[0] ?? $candidates[0];
    }

    /**
     * The specification of the field list - its ids and paths -, e.g. "ZUGFeRD 2.5.2 / Factur-X 1.09.2".
     */
    public static function source(): string
    {
        return self::data()['source']['specification'] ?? '';
    }

    /**
     * Local names below the root element, attributes with "@" - the format of the field list.
     */
    private static function ciiPath(DOMNode $node): string
    {
        $names = $node instanceof DOMAttr ? ['@' . $node->localName] : [];
        $element = $node instanceof DOMAttr ? $node->ownerElement : $node;

        while ($element instanceof DOMElement && $element->parentNode instanceof DOMElement) {
            array_unshift($names, $element->localName);
            $element = $element->parentNode;
        }

        return implode('/', $names);
    }

    /**
     * Whether a field (by the terms of its name) belongs to a charge, to an allowance - of the document, of a line, of
     * a price (the discount of a line has properties of its own: priceDiscountReason ...) - or to neither.
     *
     * @param list<string> $name
     */
    private static function isChargeField(array $name): ?bool
    {
        foreach ($name as $key) {
            if (in_array($key, self::CHARGES, true)) {
                return true;
            }
            if ($key === 'allowances' || $key === 'additionalPriceDiscounts' || str_starts_with($key, 'priceDiscount')) {
                return false;
            }
        }

        return null;
    }

    /**
     * true inside a charge, false inside an allowance, null outside of both or when the indicator is no
     * xs:boolean (Rules::indicator).
     */
    private static function isCharge(DOMNode $node): ?bool
    {
        $element = $node instanceof DOMAttr ? $node->ownerElement : $node;

        for (; $element instanceof DOMElement; $element = $element->parentNode) {
            if (! str_ends_with($element->localName ?? '', 'AllowanceCharge')) {
                continue;
            }

            foreach ($element->childNodes as $child) {
                if ($child instanceof DOMElement && $child->localName === 'ChargeIndicator') {
                    return Rules::indicator($child->textContent);
                }
            }

            return null;
        }

        return null;
    }

    /**
     * @return array{source: array<string, string>, terms: array<string, array{de: string, en: string, fr: string}>, fields: array<string, array{path: string, name: list<string>, depths: list<int|null>, type: string|null, profiles: list<string>}>, paths: array<string, list<string>>}
     */
    private static function data(): array
    {
        return self::$data ??= require dirname(__DIR__) . '/resources/compiled/facturx-fields.php';
    }
}
