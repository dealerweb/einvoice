<?php

declare(strict_types=1);

namespace Dealerweb\EInvoice;

use JsonSerializable;

/**
 * A value of the invoice that the EN 16931 model does not carry (Document::unmapped()) - for the rendering, which
 * shows it where it belongs, with its name.
 *
 * @internal
 */
final readonly class UnmappedValue implements JsonSerializable
{
    /**
     * @param string $path where the value stands in the source document, with the prefixes as written there and a
     *                     position where elements repeat: "/rsm:CrossIndustryInvoice/.../ram:IncludedNote[2]/ram:Content"
     * @param string $value the text of the element (without surrounding blanks) or the value of the attribute
     * @param list<string> $names local element names below the root, the attribute name last
     * @param array<string, string> $attributes qualifiers of the value, e.g. ['schemeID' => '0088'] or ['unitCode' => 'DAY']
     * @param string|null $id field id of the Factur-X / ZUGFeRD field list (CII only), e.g. BT-X-92
     * @param list<string> $groups field ids of the enclosing groups, outermost first (CII only) - they tell
     *                             whose value it is, e.g. BG-X-36 (invoicee) for a postcode
     */
    public function __construct(
        public string $path,
        public string $value,
        public array $names,
        public array $attributes = [],
        public ?string $id = null,
        public array $groups = [],
    ) {}

    /**
     * Name of the field (de, en, fr) where it has an id - e.g. "Lieferung › Lieferschein › Nummer" (Labels::name()) -,
     * otherwise a readable form of the element names ("Contact › Telefax").
     *
     * @param int $skip element names down to the element the value is shown in, e.g. 3 for the seller's block of a CII
     *                  document (1 for "AccountingSupplierParty" in UBL): the name leaves out what that element already
     *                  says, "Anschrift › Ort" instead of "Verkäufer › Anschrift › Ort"
     */
    public function label(string $language = 'en', int $skip = 0): string
    {
        $parts = $this->id !== null ? Labels::parts($this->id, $language, $skip) : [];

        return $parts !== [] ? implode(Labels::SEPARATOR, $parts) : $this->technicalLabel($skip);
    }

    /**
     * Readable label from the element names: "SpecifiedProcuringProject/Name" becomes
     * "Specified Procuring Project › Name". Syntax wrappers without meaning are left out, and the
     * first $skip names where they are known from the context.
     */
    public function technicalLabel(int $skip = 0): string
    {
        $wrappers = ['SupplyChainTradeTransaction', 'ApplicableHeaderTradeAgreement', 'ApplicableHeaderTradeDelivery',
            'ApplicableHeaderTradeSettlement', 'IncludedSupplyChainTradeLineItem', 'SpecifiedLineTradeAgreement',
            'SpecifiedLineTradeDelivery', 'SpecifiedLineTradeSettlement', 'Party', 'DateTimeString', 'DateString'];

        $all = array_slice($this->names, $skip) ?: $this->names;
        $names = array_values(array_filter($all, static fn(string $name): bool => ! in_array($name, $wrappers, true)));
        $names = array_slice($names === [] ? $all : $names, -3);

        return implode(' › ', array_map(
            static fn(string $name): string => ucfirst(trim(preg_replace('/(?<=[a-z])(?=[A-Z])|(?<=[A-Z])(?=[A-Z][a-z])/', ' ', $name) ?? $name)),
            $names,
        ));
    }

    /**
     * @return array{path: string, id: string|null, groups: list<string>, label: string, value: string, attributes: array<string, string>}
     */
    public function jsonSerialize(): array
    {
        return [
            'path' => $this->path,
            'id' => $this->id,
            'groups' => $this->groups,
            'label' => $this->label(),
            'value' => $this->value,
            'attributes' => $this->attributes,
        ];
    }
}
