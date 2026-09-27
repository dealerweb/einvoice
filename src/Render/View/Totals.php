<?php

declare(strict_types=1);

namespace Dealerweb\EInvoice\Render\View;

use Dealerweb\EInvoice\CodeList;
use Dealerweb\EInvoice\Render\Decimal;

/**
 * The totals as on a German invoice - net amount, the VAT per rate, gross amount - and the notes on VAT the
 * invoice owes its reader.
 *
 * @internal
 */
final class Totals extends Section
{
    /**
     * The VAT per category and rate as rows of the totals ("USt. 19 % auf 445,27"), with the notes
     * the invoice owes its reader. A category other than the standard rate is marked with its code,
     * as in the lines ("USt. 0 % K auf 2.000,00"), and explained below the totals with the reason
     * of the exemption: "K: Steuerfreie innergemeinschaftliche Lieferung ... - <reason>".
     *
     * @return list<array{label: string, tax: string|null, notes: list<string>}>
     */
    public function vat(): array
    {
        $entries = [];
        foreach ($this->groups($this->data['VAT_BREAKDOWN'] ?? null) as $index => $vat) {
            $code = $this->value($vat['VAT_category_code'] ?? null);
            $rate = $this->value($vat['VAT_category_rate'] ?? null);
            $taxable = $this->value($vat['VAT_category_taxable_amount'] ?? null);

            $label = trim($this->texts->get('totals.vat_label', ['rate' => (string) $this->vatText($code, $rate)]));
            if ($taxable !== null) {
                $label = $this->texts->get('totals.vat_base', ['label' => $label, 'base' => $this->format->amount($taxable)]);
            }

            $exemptionCode = $this->value($vat['VAT_exemption_reason_code'] ?? null);
            $exemption = $this->text($vat['VAT_exemption_reason_text'] ?? null)
                ?? ($exemptionCode === null ? null : $this->codeName(CodeList::VatExemptionReason, $exemptionCode));

            $notes = [];
            if ($code !== null && strtoupper($code) !== 'S') {
                $category = $this->codeName(CodeList::VatCategory, $code);
                $explanation = implode(' - ', array_filter([$category !== $code ? $category : null, $exemption]));
                if ($explanation !== '') {
                    $notes[] = $code . ': ' . $explanation;
                }
            } elseif ($exemption !== null) {
                $notes[] = $exemption;
            }
            foreach ($this->attachedRows('vat:' . $index) as [$extraLabel, $extraValue]) {
                $notes[] = $extraLabel . ': ' . $extraValue;
            }

            $entries[] = ['label' => $label, 'tax' => $this->value($vat['VAT_category_tax_amount'] ?? null), 'notes' => $notes];
        }

        return $entries;
    }

    /**
     * The notes of all VAT rows, each once.
     *
     * @param list<array{label: string, tax: string|null, notes: list<string>}> $vat
     * @return list<string>
     */
    public function notes(array $vat): array
    {
        return array_values(array_unique(array_merge(...array_column($vat, 'notes'))));
    }

    /**
     * Totals as on a German invoice: net amount, the VAT per rate, gross amount. Rows that only repeat
     * another amount are left out - the sum of lines without allowances or charges, the total VAT of a
     * single rate, the amount due where nothing is paid yet.
     *
     * @param list<array{label: string, tax: string|null, notes: list<string>}> $vat
     * @return list<array{label: string, value: string, kind: string}>
     */
    public function rows(array $vat): array
    {
        $totals = $this->groups($this->data['DOCUMENT_TOTALS'] ?? null)[0] ?? [];
        $amount = fn(string $field): ?string => $this->value($totals[$field] ?? null);

        $rows = [];
        $row = function (string $label, ?string $value, string $kind = '', ?string $currency = null) use (&$rows): void {
            if ($value !== null) {
                $rows[] = ['label' => $label, 'value' => $this->format->money($value, $currency ?? $this->currency), 'kind' => $kind];
            }
        };

        $allowances = $amount('Sum_of_allowances_on_document_level');
        $charges = $amount('Sum_of_charges_on_document_level');
        $withAllowances = $allowances !== null && ! Decimal::isZero($allowances);
        $withCharges = $charges !== null && ! Decimal::isZero($charges);
        $lineSum = $amount('Sum_of_Invoice_line_net_amount');
        $net = $amount('Invoice_total_amount_without_VAT');

        if ($withAllowances || $withCharges || ! Decimal::equals($lineSum, $net)) {
            $row($this->texts->get('totals.lines'), $lineSum);
        }
        if ($withAllowances) {
            $row($this->texts->get('totals.allowances'), Decimal::negate((string) $allowances));
        }
        if ($withCharges) {
            $row($this->texts->get('totals.charges'), $charges);
        }
        $row($this->texts->get('totals.net'), $net, 'subtotal');

        foreach ($vat as $entry) {
            $row($entry['label'], $entry['tax']);
        }
        $totalVat = $amount('Invoice_total_VAT_amount');
        if (count($vat) !== 1 || ! Decimal::equals($vat[0]['tax'], $totalVat)) {
            $row($this->texts->get('totals.vat'), $totalVat);
        }
        $vatCurrency = $this->value($this->data['VAT_accounting_currency_code'] ?? null);
        $row($this->texts->get('totals.vat_accounting', ['currency' => (string) $vatCurrency]), $amount('Invoice_total_VAT_amount_in_accounting_currency'), '', $vatCurrency);

        $gross = $amount('Invoice_total_amount_with_VAT');
        $paid = $amount('Paid_amount');
        $rounding = $amount('Rounding_amount');
        $due = $amount('Amount_due_for_payment');
        $withPaid = $paid !== null && ! Decimal::isZero($paid);
        $withRounding = $rounding !== null && ! Decimal::isZero($rounding);
        $withDue = $due !== null && ($withPaid || $withRounding || ! Decimal::equals($due, $gross));

        $row($this->texts->get('totals.gross'), $gross, $withDue ? 'subtotal' : 'due');
        if ($withPaid) {
            $row($this->texts->get('totals.paid'), Decimal::negate((string) $paid));
        }
        if ($withRounding) {
            $row($this->texts->get('totals.rounding'), $rounding);
        }
        if ($withDue) {
            $row($this->texts->get('totals.due'), $due, 'due');
        }

        return $rows;
    }
}
