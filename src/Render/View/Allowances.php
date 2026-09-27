<?php

declare(strict_types=1);

namespace Dealerweb\EInvoice\Render\View;

use Dealerweb\EInvoice\ModelValues;
use Dealerweb\EInvoice\Render\Decimal;

/**
 * Allowances and charges on document level, with the logistics service charges of ZUGFeRD EXTENDED
 * (BG-X-42) - the sum of charges (BT-108) includes them. An allowance or charge of zero is left out,
 * here and in the lines.
 *
 * @internal
 */
final class Allowances extends Section
{
    /** Fields of a logistics service charge (BG-X-42) - part of the charges on document level (BT-108). */
    private const LOGISTICS_FIELDS = ['reason' => 'BT-X-271', 'amount' => 'BT-X-272', 'category' => 'BT-X-273', 'rate' => 'BT-X-274'];

    /**
     * Whether an allowance or charge is zero ("Rabatt 0 % von 13,82 = 0,00") - such a one is left out.
     *
     * @param array<string, mixed> $group
     */
    public static function isZero(array $group, string $prefix): bool
    {
        $amount = ModelValues::value($group[$prefix . '_amount'] ?? null);

        return $amount !== null && Decimal::isZero($amount);
    }

    /**
     * Source paths of the allowances and charges of zero, on document level and in every line - their
     * unmapped values are left out with them.
     *
     * @param array<string, mixed> $data the invoice with meta (Document::toArray(true))
     * @return list<string>
     */
    public static function zeroSources(array $data): array
    {
        $sources = [];
        $collect = static function (mixed $node, string $prefix) use (&$sources): void {
            foreach (ModelValues::groups($node) as $group) {
                if (self::isZero($group, $prefix) && ModelValues::source($group) !== '') {
                    $sources[] = ModelValues::source($group);
                }
            }
        };

        $collect($data['DOCUMENT_LEVEL_ALLOWANCES'] ?? null, 'Document_level_allowance');
        $collect($data['DOCUMENT_LEVEL_CHARGES'] ?? null, 'Document_level_charge');

        $lines = ModelValues::groups($data['INVOICE_LINE'] ?? null);
        while ($lines !== []) {
            $line = array_shift($lines);
            $collect($line['INVOICE_LINE_ALLOWANCES'] ?? null, 'Invoice_line_allowance');
            $collect($line['INVOICE_LINE_CHARGES'] ?? null, 'Invoice_line_charge');
            array_push($lines, ...ModelValues::groups($line['SUB_INVOICE_LINE'] ?? null));
        }

        return $sources;
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function build(): array
    {
        $rows = [];
        foreach ($this->groups($this->data['DOCUMENT_LEVEL_ALLOWANCES'] ?? null) as $index => $allowance) {
            if (! self::isZero($allowance, 'Document_level_allowance')) {
                $rows[] = $this->allowance($allowance, 'Document_level_allowance', true, 'allowance:' . $index);
            }
        }
        foreach ($this->groups($this->data['DOCUMENT_LEVEL_CHARGES'] ?? null) as $index => $charge) {
            if (! self::isZero($charge, 'Document_level_charge')) {
                $rows[] = $this->allowance($charge, 'Document_level_charge', false, 'charge:' . $index);
            }
        }

        $logistics = $this->extras->special(ExtendedFields::LOGISTICS_CHARGE);
        foreach ($this->byElement($logistics, ExtendedFields::GROUP_ELEMENTS[ExtendedFields::LOGISTICS_CHARGE]) as $values) {
            $fields = [];
            $other = [];
            foreach ($values as $value) {
                $role = array_search($value->id, self::LOGISTICS_FIELDS, true);
                if ($role === false) {
                    $other[] = $value;
                } else {
                    $fields[$role] = $this->clean($value->value);
                }
            }
            if (isset($fields['amount']) && Decimal::isZero($fields['amount'])) {
                continue;
            }

            $rows[] = [
                'type' => $this->texts->get('ac.charge'),
                'reason' => $fields['reason'] ?? null,
                'base' => null,
                'percent' => null,
                'vat' => $this->vatText($fields['category'] ?? null, $fields['rate'] ?? null),
                'amount' => isset($fields['amount']) ? $this->format->amount($fields['amount']) : null,
                'extras' => $this->extraRows($other),
            ];
        }

        // A long reason goes on in rows of its own.
        return array_map(static function (array $row): array {
            [$row['reason'], $row['more']] = TextLayout::cellParts($row['reason']);

            return $row;
        }, $rows);
    }

    /**
     * @param array<string, mixed> $group
     * @return array<string, mixed>
     */
    private function allowance(array $group, string $prefix, bool $allowance, string $anchor): array
    {
        $base = $this->value($group[$prefix . '_base_amount'] ?? null);
        $percent = $this->value($group[$prefix . '_percentage'] ?? null);
        $amount = $this->value($group[$prefix . '_amount'] ?? null);

        return [
            'type' => $this->texts->get($allowance ? 'ac.allowance' : 'ac.charge'),
            'reason' => $this->reason($group, $prefix, $allowance),
            'base' => $base === null ? null : $this->format->amount($base),
            'percent' => $percent === null ? null : $this->format->percent($percent),
            'vat' => $this->vatText($this->value($group[$prefix . '_VAT_category_code'] ?? null), $this->value($group[$prefix . '_VAT_rate'] ?? null)),
            'amount' => $amount === null ? null : $this->format->amount($allowance ? Decimal::negate($amount) : $amount),
            'extras' => $this->attachedRows($anchor),
        ];
    }
}
