<?php

declare(strict_types=1);

namespace Dealerweb\EInvoice;

use DOMAttr;
use DOMElement;

/**
 * Rules for reading an invoice the way a person does - in one place, so that every part of the package (the
 * KoSIT mapping, coverage, labels, rendering, summary and the reader of the model) comes to the same result.
 *
 * @internal
 */
final class Rules
{
    /** UNTDID 1001 codes that the EN 16931 code lists (v17b, 2026-05-15) interpret as credit note. */
    public const CREDIT_NOTE_CODES = ['81', '83', '261', '262', '296', '308', '381', '396', '420', '458', '502', '503', '532'];

    /** Self-billing codes of UNTDID 1001 (EN 16931 code lists v17b): the buyer issues the document on behalf of the seller. */
    public const SELF_BILLED_CODES = ['261', '389', '471', '473', '500', '501', '502', '527'];

    /** Attributes that only tell where a code or identifier comes from. */
    public const PROVENANCE = [
        'listAgencyID', 'listAgencyName', 'listName', 'listURI', 'listSchemeURI', 'listVersionID',
        'schemeAgencyID', 'schemeAgencyName', 'schemeName', 'schemeURI', 'schemeDataURI', 'schemeVersionID',
        'languageID', 'languageLocaleID',
    ];

    /** The characters of a value a message quotes. */
    private const EXCERPT = 60;

    /**
     * An indicator (xs:boolean, e.g. ChargeIndicator, TestIndicator): "true" or "1" is true, "false" or "0" is
     * false - blanks around the value and its case do not matter. Null for anything else.
     */
    public static function indicator(string $value): ?bool
    {
        return match (strtolower(trim($value))) {
            'true', '1' => true,
            'false', '0' => false,
            default => null,
        };
    }

    /**
     * An attribute without information of its own, wherever it occurs: a namespace declaration or an attribute of XML
     * Schema instance (xsi:schemaLocation), the currency of an amount that is the invoice or VAT accounting currency -
     * the invoice states it once -, the date format 102 - the value is the date - and the provenance of a code or
     * identifier (listAgencyID, schemeName, languageID, ...).
     *
     * @param list<string> $currencies the invoice currency and the VAT accounting currency
     */
    public static function isNoise(DOMAttr $attribute, array $currencies): bool
    {
        if ($attribute->prefix === 'xmlns' || $attribute->namespaceURI === 'http://www.w3.org/2001/XMLSchema-instance') {
            return true;
        }

        return match ($attribute->localName) {
            'currencyID' => in_array($attribute->value, $currencies, true),
            'format' => $attribute->value === '102',
            default => in_array($attribute->localName, self::PROVENANCE, true),
        };
    }

    /**
     * An element without information of the invoice: the version of UBL - the syntax tells it.
     */
    public static function isSyntaxVersion(DOMElement $element): bool
    {
        return $element->localName === 'UBLVersionID';
    }

    /**
     * A value as a message quotes it: its first 60 characters - a message does not repeat a whole text or file.
     */
    public static function excerpt(string $value): string
    {
        return mb_strlen($value) > self::EXCERPT ? mb_substr($value, 0, self::EXCERPT) . '…' : $value;
    }

    /**
     * Whether two texts say the same, regardless of case and blanks.
     */
    public static function sameText(?string $a, ?string $b): bool
    {
        $normalize = static fn(string $value): string => mb_strtolower(trim(preg_replace('/\s+/u', ' ', $value) ?? $value));

        return $a !== null && $b !== null && $normalize($a) === $normalize($b);
    }

    /**
     * Name and description of an item: a name that only repeats the article number (BT-153 = BT-155)
     * gives way to the description ("61627208602" / "Wischermotor Heckscheibe"), a description that
     * only repeats the name is left out.
     *
     * @return array{0: string|null, 1: string|null}
     */
    public static function itemName(?string $name, ?string $description, ?string $sellerId): array
    {
        if ($description !== null && self::sameText($name, $sellerId)) {
            return [$description, null];
        }

        return [$name, self::sameText($description, $name) ? null : $description];
    }
}
