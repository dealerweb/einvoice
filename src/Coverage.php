<?php

declare(strict_types=1);

namespace Dealerweb\EInvoice;

use Dealerweb\EInvoice\Kosit\NodeIndex;
use Dealerweb\EInvoice\Kosit\Transformer;
use DOMAttr;
use DOMDocument;
use DOMElement;
use DOMNode;
use DOMXPath;

/**
 * Finds the information of an invoice that the EN 16931 model does not carry - so that a
 * visualization can show it instead of silently dropping it ("further information").
 *
 * A value counts as covered when the KoSIT mapping wrote it to the output. On top of that,
 * a few kinds of nodes carry no information of their own and are not reported:
 *
 *  - currencyID on amounts when it equals the invoice currency (BT-5) or the VAT accounting
 *    currency (BT-6) - that is what the model states once for the whole invoice;
 *  - format="102" on dates - the model holds the normalized date;
 *  - provenance of codes and identifiers (listAgencyID, schemeName, languageID, ...), and on a
 *    mapped value also its list or scheme id - the model holds the value itself;
 *  - the allowance/charge indicator - the model distinguishes allowances and charges by group.
 *    Not so on the price of a line: KoSIT maps every allowance or charge there as a discount
 *    (BT-147), so the indicator of a charge is reported - it is the only trace of the charge;
 *  - the tax scheme "VAT" - EN 16931 knows no other - and the tax scheme of a party tax
 *    registration whose number was mapped (it only decides between BT-31 and BT-32);
 *  - document type codes 50, 130 and 916, which only decide where a reference goes
 *    (tender, invoiced object, supporting document);
 *  - the UBL version;
 *  - a CII net price base quantity equal to the gross one KoSIT took for BT-149/BT-150;
 *  - within payment means, which KoSIT groups by their code: repetitions of a value that is
 *    covered at the same path.
 *
 * Attributes of an element that is reported themselves (e.g. the scheme of an unmapped
 * identifier) are attached to that element's entry instead of being reported separately.
 *
 * @internal used by Invoice::fromXml()
 */
final class Coverage
{
    /** Document type codes that only decide where a reference goes: tender (50), invoiced object (130), supporting document (916). */
    private const SELECTOR_CODES = ['50', '130', '916'];

    /** Elements of a payment means - the only structure KoSIT merges when it repeats (by payment means code). */
    private const PAYMENT_MEANS = ['PaymentMeans', 'SpecifiedTradeSettlementPaymentMeans'];

    /** Elements of the price of a line (UBL, CII). */
    private const PRICES = ['Price', 'GrossPriceProductTradePrice', 'NetPriceProductTradePrice'];

    /**
     * Group ids the field list gives an element that holds much more than that group: the settlement
     * of the document carries BG-19 (direct debit) only because the creditor reference (BT-90) sits
     * directly in it - as heading, every other value of the settlement would read as direct debit.
     */
    private const MISLEADING_GROUPS = ['ApplicableHeaderTradeSettlement' => 'BG-19'];

    /**
     * @param array<string, DOMNode> $consumed source nodes written to the output, by node key
     * @param list<string> $currencies invoice and VAT accounting currency
     */
    private function __construct(
        private readonly NodeIndex $index,
        private readonly array $consumed,
        private readonly array $currencies,
    ) {}

    /**
     * @param array<string, DOMNode> $consumed source nodes written to the output, by their key in $index
     * @return list<UnmappedValue>
     */
    public static function unmapped(DOMDocument $source, DOMDocument $intermediate, array $consumed, NodeIndex $index): array
    {
        return (new self($index, $consumed, self::currencies($intermediate)))->find($source);
    }

    /**
     * @return list<UnmappedValue>
     */
    private function find(DOMDocument $source): array
    {
        $xpath = new DOMXPath($source);
        // The official field list (and so the field ids) exists for CII only.
        $cii = $source->documentElement?->localName === 'CrossIndustryInvoice';

        $covered = [];
        foreach ($this->consumed as $node) {
            $covered[$this->genericPath($node) . '=' . self::value($node)] = true;
        }

        $unmapped = [];
        // Unmapped values that were either reported (with their attributes) or dropped as structural
        // or repeated - their attributes are dealt with by that decision.
        $decided = [];

        foreach ($xpath->query('//*[not(*)][normalize-space()]') ?: [] as $element) {
            if (! $element instanceof DOMElement || $this->isConsumed($element)) {
                continue;
            }

            $decided[$this->index->key($element)] = true;

            if ($this->isStructural($element) || $this->isRepetition($element, self::value($element), $covered)) {
                continue;
            }

            $attributes = [];
            foreach ($element->attributes as $attribute) {
                if (! $this->isNoise($attribute)) {
                    $attributes[$attribute->nodeName] = $attribute->value;
                }
            }

            $unmapped[] = new UnmappedValue(
                $this->index->path($element),
                self::value($element),
                self::localPath($element),
                $attributes,
                $cii ? Labels::ciiId($element) : null,
                $cii ? self::groups($element) : [],
            );
        }

        foreach ($xpath->query('//@*') ?: [] as $attribute) {
            if (! $attribute instanceof DOMAttr
                || $attribute->ownerElement === null
                || $this->isConsumed($attribute)
                || isset($decided[$this->index->key($attribute->ownerElement)])
                || $this->isNoise($attribute)
                || $this->describesMappedValue($attribute)
                || $this->isRepetition($attribute, $attribute->value, $covered)) {
                continue;
            }

            $unmapped[] = new UnmappedValue(
                $this->index->path($attribute),
                $attribute->value,
                self::localPath($attribute),
                [],
                $cii ? Labels::ciiId($attribute) : null,
                $cii ? self::groups($attribute->ownerElement) : [],
            );
        }

        return $unmapped;
    }

    private function isConsumed(DOMNode $node): bool
    {
        return isset($this->consumed[$this->index->key($node)]);
    }

    /**
     * A value of a payment means that repeats one KoSIT wrote to the output at the same path - KoSIT
     * merges payment means with the same code (their code then stands once).
     *
     * @param array<string, true> $covered generic paths and values of the covered nodes
     */
    private function isRepetition(DOMNode $node, string $value, array $covered): bool
    {
        return isset($covered[$this->genericPath($node) . '=' . $value]) && self::within($node, self::PAYMENT_MEANS);
    }

    /**
     * Attributes without information of their own, wherever they occur (Rules::isNoise).
     */
    private function isNoise(DOMAttr $attribute): bool
    {
        return Rules::isNoise($attribute, $this->currencies);
    }

    /**
     * The list or scheme id of a value that was mapped: if it mattered to the model, the
     * identifier value type would have taken it along.
     */
    private function describesMappedValue(DOMAttr $attribute): bool
    {
        return in_array($attribute->localName, ['listID', 'schemeID'], true)
            && $attribute->ownerElement !== null
            && $this->isConsumed($attribute->ownerElement);
    }

    private function isStructural(DOMElement $element): bool
    {
        $value = self::value($element);
        $parent = $element->parentNode;
        $name = $element->localName;
        $parentName = $parent instanceof DOMElement ? ($parent->localName ?? '') : '';

        return match (true) {
            $name === 'ChargeIndicator', $name === 'Indicator' && $parentName === 'ChargeIndicator'
                => ! (Rules::indicator($value) === true && self::within($element, self::PRICES)),
            $name === 'ID' && $parentName === 'TaxScheme' => $value === 'VAT' || $this->registrationMapped($parent),
            $name === 'TypeCode' && str_ends_with($parentName, 'TradeTax') => $value === 'VAT',
            $name === 'DocumentTypeCode', $name === 'TypeCode' && $parentName === 'AdditionalReferencedDocument'
                => in_array($value, self::SELECTOR_CODES, true),
            Rules::isSyntaxVersion($element) => true,
            $name === 'BasisQuantity' && $parentName === 'NetPriceProductTradePrice' => $this->sameAsGrossBasisQuantity($element),
            default => false,
        };
    }

    /**
     * UBL PartyTaxScheme: was the CompanyID next to this TaxScheme mapped?
     */
    private function registrationMapped(DOMNode $taxScheme): bool
    {
        $registration = $taxScheme->parentNode;
        if (! $registration instanceof DOMElement || $registration->localName !== 'PartyTaxScheme') {
            return false;
        }

        foreach ($registration->childNodes as $child) {
            if ($child instanceof DOMElement && $child->localName === 'CompanyID' && $this->isConsumed($child)) {
                return true;
            }
        }

        return false;
    }

    /**
     * CII: with a net and a gross base quantity KoSIT maps the gross one (BG-29). The net one
     * only adds information when it differs in amount or unit.
     */
    private function sameAsGrossBasisQuantity(DOMElement $net): bool
    {
        $agreement = $net->parentNode?->parentNode;
        if (! $agreement instanceof DOMElement) {
            return false;
        }

        foreach ($agreement->childNodes as $price) {
            if (! $price instanceof DOMElement || $price->localName !== 'GrossPriceProductTradePrice') {
                continue;
            }

            foreach ($price->childNodes as $gross) {
                if ($gross instanceof DOMElement && $gross->localName === 'BasisQuantity' && $this->isConsumed($gross)) {
                    return is_numeric(self::value($gross)) && is_numeric(self::value($net))
                        && (float) self::value($gross) === (float) self::value($net)
                        && $gross->getAttribute('unitCode') === $net->getAttribute('unitCode');
                }
            }
        }

        return false;
    }

    /**
     * Path with qualified names and without positions - identical for repeated structures.
     */
    private function genericPath(DOMNode $node): string
    {
        $path = $this->index->path($node);

        return preg_replace('/\[\d+\]/', '', $path) ?? $path;
    }

    /**
     * Field ids of the enclosing CII elements that the field list knows, outermost first.
     *
     * @return list<string>
     */
    private static function groups(?DOMNode $node): array
    {
        $groups = [];
        for ($element = $node?->parentNode; $element instanceof DOMElement && $element->parentNode instanceof DOMElement; $element = $element->parentNode) {
            $id = Labels::ciiId($element);
            if ($id !== null && (self::MISLEADING_GROUPS[$element->localName ?? ''] ?? null) !== $id) {
                array_unshift($groups, $id);
            }
        }

        return $groups;
    }

    /**
     * Whether an element with one of the local names encloses the node.
     *
     * @param list<string> $names
     */
    private static function within(DOMNode $node, array $names): bool
    {
        for ($element = $node instanceof DOMAttr ? $node->ownerElement : $node->parentNode; $element instanceof DOMElement; $element = $element->parentNode) {
            if (in_array($element->localName, $names, true)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return list<string>
     */
    private static function currencies(DOMDocument $intermediate): array
    {
        $xpath = new DOMXPath($intermediate);
        $xpath->registerNamespace('xr', Transformer::XR_NAMESPACE);

        $currencies = [];
        foreach ($xpath->query('/xr:invoice/xr:Invoice_currency_code | /xr:invoice/xr:VAT_accounting_currency_code') ?: [] as $code) {
            if ($code instanceof DOMElement) {
                $currencies[] = trim($code->textContent);
            }
        }

        return $currencies;
    }

    private static function value(DOMNode $node): string
    {
        return $node instanceof DOMAttr ? $node->value : trim((string) $node->textContent);
    }

    /**
     * Local names from below the root element - the basis for a readable label.
     *
     * @return list<string>
     */
    private static function localPath(DOMNode $node): array
    {
        $names = $node instanceof DOMAttr ? [$node->localName ?? $node->nodeName] : [];
        $element = $node instanceof DOMAttr ? $node->ownerElement : $node;

        while ($element instanceof DOMElement && $element->parentNode instanceof DOMElement) {
            array_unshift($names, $element->localName ?? $element->nodeName);
            $element = $element->parentNode;
        }

        return $names;
    }
}
