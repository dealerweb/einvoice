<?php

declare(strict_types=1);

namespace Dealerweb\EInvoice\Render\View;

use Dealerweb\EInvoice\CodeList;
use Dealerweb\EInvoice\Render\Decimal;
use Dealerweb\EInvoice\UnmappedValue;

/**
 * Payment: the due date, payment terms with cash discount and late payment written out, the payment
 * instructions with their accounts - and the payments of third parties of the XRechnung extension.
 *
 * @internal
 */
final class Payment extends Section
{
    /** Payment means "not defined" and "mutually defined" (BT-81) - the text of the sender says more. */
    private const UNDEFINED_PAYMENT_MEANS = ['1', 'ZZZ'];

    /**
     * @return array<string, mixed>
     */
    public function build(): array
    {
        $terms = $this->paymentTerms($this->text($this->data['Payment_terms'] ?? null));

        // A cash discount of 0 % is no discount - XRechnung software writes "net within 14 days" so. It
        // joins the terms, unless their text already names its days.
        $termsText = $terms['text'];
        $discounts = [];
        foreach ([...$terms['discounts'], ...$this->extensionTerms()] as $term) {
            if (! $term['net']) {
                $discounts[] = $term['text'];
            } elseif ($termsText === null || $term['days'] === null || ! preg_match('/(?<!\d)' . preg_quote($term['days'], '/') . '(?!\d)/', $termsText)) {
                $termsText = $termsText === null ? $term['text'] : $termsText . "\n" . $term['text'];
            }
        }

        $instructions = [];
        foreach ($this->groups($this->data['PAYMENT_INSTRUCTIONS'] ?? null) as $index => $instruction) {
            $code = $this->value($instruction['Payment_means_type_code'] ?? null);
            $means = $code === null ? null : $this->codeName(CodeList::PaymentMeans, $code);
            $text = $this->text($instruction['Payment_means_text'] ?? null);

            // "Zahlungsmittel nicht definiert" (1) or mutually defined (ZZZ) says less than the text of the sender.
            if ($text !== null && in_array($code, self::UNDEFINED_PAYMENT_MEANS, true)) {
                $means = null;
            }

            $rows = [];
            foreach ($this->groups($instruction['CREDIT_TRANSFER'] ?? null) as $transfer) {
                // An account can come with IBAN and account number - each is recognized on its own.
                foreach ($this->distinctValues($transfer['Payment_account_identifier'] ?? null) as $account) {
                    $this->add($rows, $this->format->isIban($account) ? 'payment.iban' : 'payment.account', $this->format->account($account));
                }
                $this->add($rows, 'payment.account_name', $this->value($transfer['Payment_account_name'] ?? null));
                $this->add($rows, 'payment.bic', $this->value($transfer['Payment_service_provider_identifier'] ?? null));
            }

            $card = $this->groups($instruction['PAYMENT_CARD_INFORMATION'] ?? null)[0] ?? [];
            $this->add($rows, 'payment.card', $this->value($card['Payment_card_primary_account_number'] ?? null));
            $this->add($rows, 'payment.card_holder', $this->value($card['Payment_card_holder_name'] ?? null));

            $debit = $this->groups($instruction['DIRECT_DEBIT'] ?? null)[0] ?? [];
            $this->add($rows, 'payment.mandate', $this->value($debit['Mandate_reference_identifier'] ?? null));
            $this->add($rows, 'payment.creditor_id', $this->value($debit['Bank_assigned_creditor_identifier'] ?? null));
            $debited = array_map(fn(string $account): string => $this->format->account($account), $this->distinctValues($debit['Debited_account_identifier'] ?? null));
            $this->add($rows, 'payment.debited_account', $debited === [] ? null : implode(', ', $debited));

            $this->add($rows, 'payment.remittance', $this->value($instruction['Remittance_information'] ?? null));

            $instructions[] = [
                'title' => $means ?? $text,
                'text' => $means !== null && $text !== null && ! $this->sameText($text, $means) ? $text : null,
                'rows' => $rows,
                'extras' => $this->attachedRows('payment:' . $index),
            ];
        }

        // Payment means and terms stand side by side in one row, which dompdf never splits - where they
        // are long, they stand one below the other.
        $texts = [$termsText, ...$discounts];
        foreach ($instructions as $instruction) {
            array_push($texts, $instruction['title'], $instruction['text'], ...array_column([...$instruction['rows'], ...$instruction['extras']], 1));
        }
        $texts = array_filter($texts, static fn(?string $text): bool => $text !== null);

        return [
            'dueDate' => $this->date($this->data['Payment_due_date'] ?? null),
            'terms' => $termsText,
            'discounts' => $discounts,
            'instructions' => $instructions,
            'long' => TextLayout::isLong(implode("\n", $texts)),
        ];
    }

    /**
     * Third party payments of the XRechnung extension (BG-DEX-09).
     *
     * @return list<array{type: string|null, description: string|null, more: list<string>, amount: string|null}>
     */
    public function thirdParty(): array
    {
        $payments = [];
        foreach ($this->groups($this->data['THIRD_PARTY_PAYMENT'] ?? null) as $payment) {
            $amount = $this->value($payment['Third_party_payment_amount'] ?? null);
            [$description, $more] = TextLayout::cellParts($this->text($payment['Third_party_payment_description'] ?? null));
            $payments[] = [
                'type' => $this->value($payment['Third_party_payment_type'] ?? null),
                'description' => $description,
                'more' => $more,
                'amount' => $amount === null ? null : $this->format->amount($amount),
            ];
        }

        return $payments;
    }

    /**
     * Payment terms (BT-20) with the XRechnung convention for cash discounts and late payment
     * ("#SKONTO#TAGE=14#PROZENT=2.00#BASISBETRAG=100.00#") written out; other text stays as it is.
     *
     * @return array{text: string|null, discounts: list<array{text: string, net: bool, days: string|null}>}
     */
    private function paymentTerms(?string $terms): array
    {
        $discounts = [];
        $text = preg_replace_callback('/#(SKONTO|VERZUG)#((?:[A-Za-z]+=[^#]*#)+)/i', function (array $match) use (&$discounts): string {
            $fields = [];
            foreach (explode('#', $match[2]) as $part) {
                if (str_contains($part, '=')) {
                    [$key, $value] = explode('=', $part, 2);
                    $fields[strtoupper(trim($key))] = trim($value);
                }
            }

            $discounts[] = $this->term(
                strtoupper($match[1]) === 'VERZUG',
                $fields['TAGE'] ?? null,
                null,
                $fields['PROZENT'] ?? null,
                $fields['BASISBETRAG'] ?? null,
                null,
                [],
            );

            return '';
        }, (string) $terms);

        return ['text' => $this->clean((string) $text) ?: null, 'discounts' => $discounts];
    }

    /**
     * Cash discount (BG-X-44) and late payment terms (BG-X-43) of ZUGFeRD EXTENDED.
     *
     * @return list<array{text: string, net: bool, days: string|null}>
     */
    private function extensionTerms(): array
    {
        $fields = [
            ExtendedFields::DISCOUNT_TERMS => ['period' => 'BT-X-283', 'percent' => 'BT-X-286', 'base' => 'BT-X-285', 'amount' => 'BT-X-287'],
            ExtendedFields::PENALTY_TERMS => ['period' => 'BT-X-277', 'percent' => 'BT-X-280', 'base' => 'BT-X-279', 'amount' => 'BT-X-281'],
        ];

        $texts = [];
        foreach ($fields as $group => $ids) {
            foreach ($this->byElement($this->extras->special($group), ExtendedFields::GROUP_ELEMENTS[$group]) as $values) {
                $byId = [];
                $other = [];
                foreach ($values as $value) {
                    if (in_array($value->id, $ids, true)) {
                        $byId[(string) $value->id] = $value;
                    } else {
                        $other[] = $value;
                    }
                }

                $period = $byId[$ids['period']] ?? null;
                $texts[] = $this->term(
                    $group === ExtendedFields::PENALTY_TERMS,
                    $period?->value,
                    $period?->attributes['unitCode'] ?? null,
                    ($byId[$ids['percent']] ?? null)?->value,
                    ($byId[$ids['base']] ?? null)?->value,
                    ($byId[$ids['amount']] ?? null)?->value,
                    $other,
                );
            }
        }

        return $texts;
    }

    /**
     * A term with what the payment block needs to know of it: whether it is a cash discount of 0 %
     * ("net within 14 days") and its number of days.
     *
     * @param list<UnmappedValue> $other
     * @return array{text: string, net: bool, days: string|null}
     */
    private function term(bool $late, ?string $count, ?string $unit, ?string $percent, ?string $base, ?string $amount, array $other): array
    {
        $days = $count === null || trim($count) === '' ? null : $this->format->quantity($count);

        return [
            'text' => $this->termText($late, $count, $unit, $percent, $base, $amount, $other),
            'net' => ! $late && $days !== null && $percent !== null && Decimal::isZero($percent),
            'days' => $days,
        ];
    }

    /**
     * @param list<UnmappedValue> $other further values of the term, e.g. a reference date
     */
    private function termText(bool $late, ?string $count, ?string $unit, ?string $percent, ?string $base, ?string $amount, array $other): string
    {
        $period = null;
        if ($count !== null && trim($count) !== '') {
            $number = $this->format->quantity($count);
            $period = $unit === null || strtoupper($unit) === 'DAY'
                ? $this->texts->get(Decimal::isOne($count) ? 'payment.day' : 'payment.days', ['count' => $number])
                : $number . ' ' . $this->unitName($unit);
        }

        $percentText = $percent === null || trim($percent) === '' ? null : $this->format->percent($percent);

        $text = match (true) {
            $late => $this->texts->get('payment.late_text', ['percent' => (string) $percentText, 'period' => (string) $period]),
            $percentText !== null && Decimal::isZero((string) $percent) && $period !== null => $this->texts->get('payment.net_text', ['period' => $period]),
            $period !== null => $this->texts->get('payment.discount_text', ['percent' => (string) $percentText, 'period' => $period]),
            default => $this->texts->get('payment.discount_only', ['percent' => (string) $percentText]),
        };

        $additions = [];
        if ($base !== null && trim($base) !== '') {
            $additions[] = $this->texts->get('payment.discount_base', ['amount' => $this->format->money($base, $this->currency)]);
        }
        if ($amount !== null && trim($amount) !== '') {
            $additions[] = $this->texts->get('payment.discount_amount', ['amount' => $this->format->money($amount, $this->currency)]);
        }
        foreach ($this->extraRows($other) as [$label, $value]) {
            $additions[] = $label . ' ' . $value;
        }

        return trim($text) . ($additions === [] ? '' : ' (' . implode(', ', $additions) . ')');
    }
}
