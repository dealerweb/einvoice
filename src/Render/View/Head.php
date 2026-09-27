<?php

declare(strict_types=1);

namespace Dealerweb\EInvoice\Render\View;

use Dealerweb\EInvoice\CodeList;
use Dealerweb\EInvoice\CodeLists;
use Dealerweb\EInvoice\Rules;
use Dealerweb\EInvoice\Syntax;
use Dealerweb\EInvoice\UnmappedValue;

/**
 * The head of the document: title, number and date, banners and marks, and the dates and references of the
 * invoice - the key ones for the information block, the others for below the address.
 *
 * @internal
 */
final class Head extends Section
{
    /** References that stand in the information block; the others follow below the address. */
    private const KEY_REFERENCES = ['field.delivery_date', 'field.period', 'field.service_date', 'field.buyer_reference', 'field.order'];

    /**
     * The name of the document type (BT-3), e.g. "Rechnung", "Gutschrift (vom Kunden ausgestellt)".
     */
    public function title(): string
    {
        $code = $this->invoice->typeCode();
        if ($code === '380') {
            return $this->texts->get('title.invoice');
        }

        $name = $code === null ? null : CodeLists::name(CodeList::DocumentType, $code, $this->language);

        return $name ?? $this->texts->get($this->invoice->isCreditNote() ? 'title.credit_note' : 'title.invoice');
    }

    /**
     * The invoice number (BT-1).
     */
    public function number(): ?string
    {
        return $this->value($this->data['Invoice_number'] ?? null);
    }

    /**
     * The issue date (BT-2), written for the reader.
     */
    public function issueDate(): ?string
    {
        return $this->date($this->data['Invoice_issue_date'] ?? null);
    }

    /**
     * The seller's own name of the document (BT-X-2, e.g. "WARENRECHNUNG") where it adds something.
     */
    public function subtitle(string $title): ?string
    {
        $names = array_map(fn(UnmappedValue $value): string => $this->clean($value->value), $this->extras->special(ExtendedFields::DOCUMENT_NAME));
        $names = array_filter($names, static fn(string $name): bool => $name !== '' && mb_strtolower($name) !== mb_strtolower($title));

        return $names === [] ? null : implode(', ', array_unique($names));
    }

    /**
     * @return list<array{kind: string, text: string}>
     */
    public function banners(): array
    {
        $banners = [];

        foreach ($this->extras->special(ExtendedFields::TEST_INDICATOR) as $value) {
            if (Rules::indicator($value->value) === true) {
                $banners[] = ['kind' => 'test', 'text' => $this->texts->get('banner.test')];
                break;
            }
        }

        $specification = $this->invoice->specification();
        if ($specification->isBookingAid()) {
            $banners[] = ['kind' => 'warning', 'text' => $this->texts->get('banner.booking_aid', ['profile' => (string) $specification->profile])];
        }

        return $banners;
    }

    /**
     * @return list<string>
     */
    public function badges(): array
    {
        // A UBL CreditNote whose type code is not a credit note code: the title does not say it.
        $code = $this->invoice->typeCode();
        if ($this->invoice->syntax() === Syntax::UblCreditNote && $code !== null && ! in_array($code, Rules::CREDIT_NOTE_CODES, true)) {
            return [$this->texts->get('badge.credit_note')];
        }

        return [];
    }

    /**
     * The preceding invoices (BG-3) as text, and a hint where a corrected invoice names none.
     *
     * @return array{text: string|null, hint: string|null}
     */
    public function preceding(): array
    {
        $items = [];
        foreach ($this->groups($this->data['PRECEDING_INVOICE_REFERENCE'] ?? null) as $reference) {
            $number = $this->value($reference['Preceding_Invoice_reference'] ?? null);
            $date = $this->date($reference['Preceding_Invoice_issue_date'] ?? null);
            if ($number !== null || $date !== null) {
                $items[] = $date === null ? (string) $number : $this->texts->get('value.dated', ['value' => (string) $number, 'date' => $date]);
            }
        }

        // A corrected invoice has to refer to the invoice it corrects - in EN 16931 the reference is optional.
        $missing = $items === [] && $this->invoice->typeCode() === '384';

        return ['text' => $items === [] ? null : implode(', ', $items), 'hint' => $missing ? $this->texts->get('hint.no_preceding') : null];
    }

    /**
     * Dates and references of the invoice: the key ones (delivery date or invoicing period, buyer
     * reference, purchase order) for the information block, the others for below the address.
     *
     * @return array{0: list<array{0: string, 1: string}>, 1: list<array{0: string, 1: string}>}
     */
    public function references(?string $preceding): array
    {
        [$periodKey, $period] = $this->periodOrDate($this->dates['start'], $this->dates['end'], $this->dates['delivery'], 'field.period');

        $values = [
            'field.preceding' => $preceding,
            'field.delivery_date' => $this->date($this->dates['delivery']),
            $periodKey => $period,
            'field.tax_point' => $this->taxPoint(),
            'field.buyer_reference' => $this->value($this->data['Buyer_reference'] ?? null),
            'field.order' => $this->value($this->data['Purchase_order_reference'] ?? null),
            'field.sales_order' => $this->value($this->data['Sales_order_reference'] ?? null),
            'field.contract' => $this->value($this->data['Contract_reference'] ?? null),
            'field.project' => $this->project(),
            'field.tender' => $this->value($this->data['Tender_or_lot_reference'] ?? null),
            'field.receiving_advice' => $this->value($this->data['Receiving_advice_reference'] ?? null),
            'field.despatch_advice' => $this->value($this->data['Despatch_advice_reference'] ?? null),
            'field.delivery_note' => $this->deliveryNote(),
            'field.object' => $this->identifier($this->data['Invoiced_object_identifier'] ?? null, CodeList::ReferenceQualifier),
            'field.accounting' => $this->value($this->data['Buyer_accounting_reference'] ?? null),
            'field.vat_currency' => $this->currencyName($this->value($this->data['VAT_accounting_currency_code'] ?? null)),
        ];

        $key = [];
        $other = [];
        foreach ($values as $field => $value) {
            if (in_array($field, self::KEY_REFERENCES, true)) {
                $this->add($key, $field, $value);
            } else {
                $this->add($other, $field, $value);
            }
        }

        return [$key, $other];
    }

    private function taxPoint(): ?string
    {
        $dates = array_filter([$this->date($this->data['Value_added_tax_point_date'] ?? null)]);

        // A code per VAT breakdown - each interpreted on its own, the same one named once.
        foreach ($this->leaves($this->data['Value_added_tax_point_date_code'] ?? null) as $code) {
            $code = $this->value($code);
            if ($code !== null) {
                $dates[] = $this->codeName(CodeList::EventTime, $code);
            }
        }

        return $dates === [] ? null : implode(', ', array_unique($dates));
    }

    private function project(): ?string
    {
        $reference = $this->value($this->data['Project_reference'] ?? null);
        $names = array_map(fn(UnmappedValue $value): string => $this->clean($value->value), $this->extras->special(ExtendedFields::PROJECT_NAME));
        $name = implode(', ', array_filter($names, static fn(string $name): bool => $name !== '' && $name !== $reference));

        return match (true) {
            $reference === null => $name === '' ? null : $name,
            $name === '' => $reference,
            default => "$reference ($name)",
        };
    }

    private function deliveryNote(): ?string
    {
        $numbers = array_map(fn(UnmappedValue $value): string => $this->clean($value->value), $this->extras->special(ExtendedFields::DELIVERY_NOTE));
        $dates = array_map(fn(UnmappedValue $value): string => $this->extensionDate($value->value), $this->extras->special(ExtendedFields::DELIVERY_NOTE_DATE));

        $notes = [];
        foreach ($numbers as $index => $number) {
            $notes[] = isset($dates[$index]) ? $this->texts->get('value.dated', ['value' => $number, 'date' => $dates[$index]]) : $number;
        }

        return $notes === [] ? null : implode(', ', $notes);
    }
}
