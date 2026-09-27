<?php

declare(strict_types=1);

namespace Dealerweb\EInvoice\Model;

use Dealerweb\EInvoice\Invoice;
use Dealerweb\EInvoice\Validation\XPath\Decimal;

/**
 * Completes an invoice by what follows from its lines and its allowances and charges (EN 16931, BR-CO-10 to BR-CO-17
 * and the rules of the lines): the amount of an allowance or charge from its percentage, the net amount of a line, the
 * VAT breakdown, the totals. What is given stays - the validator checks it -, what cannot be calculated because a value
 * it needs is missing stays empty. Amounts are rounded to two decimals, half away from zero; exact decimals throughout.
 *
 * @internal used by Invoice::calculate() and the generator
 */
final class Calculator
{
    public static function complete(Invoice $invoice): void
    {
        foreach ($invoice->lines as $line) {
            self::line($line);
        }
        foreach ([...$invoice->allowances, ...$invoice->charges] as $allowanceCharge) {
            self::allowanceCharge($allowanceCharge);
        }
        self::vatBreakdown($invoice);
        self::totals($invoice);
    }

    /**
     * The difference of two values of the model, exact and with the decimals of the more precise one ("55.00" - "50"
     * is "5.00") - null where one of them is no number.
     */
    public static function difference(mixed $minuend, mixed $subtrahend): ?string
    {
        return self::combine($minuend, $subtrahend, true);
    }

    /**
     * The sum of two values of the model, exact and with the decimals of the more precise one ("50" + "5.00" is
     * "55.00") - null where one of them is no number.
     */
    public static function total(mixed $first, mixed $second): ?string
    {
        return self::combine($first, $second, false);
    }

    private static function combine(mixed $a, mixed $b, bool $subtract): ?string
    {
        $first = self::number($a);
        $second = self::number($b);
        if ($first === null || $second === null) {
            return null;
        }
        $places = max(self::places($a), self::places($b));
        $text = ($subtract ? $first->subtract($second) : $first->add($second))->toString();
        $fraction = str_contains($text, '.') ? strlen($text) - strpos($text, '.') - 1 : 0;
        if ($fraction < $places) {
            $text .= ($fraction === 0 ? '.' : '') . str_repeat('0', $places - $fraction);
        }

        return $text;
    }

    /**
     * The decimals a value of the model is written with ("55.00": 2).
     */
    private static function places(mixed $value): int
    {
        $text = is_string($value) ? trim($value) : (is_float($value) ? (string) $value : '');
        $point = strpos($text, '.');

        return $point === false ? 0 : strlen($text) - $point - 1;
    }

    /**
     * Whether two values of the model are the same number ("1", 1 and "1.00").
     */
    public static function same(mixed $first, mixed $second): bool
    {
        $a = self::number($first);
        $b = self::number($second);

        return $a !== null && $b !== null && $a->compare($b) === 0;
    }

    /**
     * The net amount of a line: quantity x (net price / base quantity) + charges - allowances. And its prices: the net
     * price is the gross price less the price discount - the gross price or the discount follows from the others where
     * no further discount or charge of the price is given (EXTENDED).
     */
    private static function line(Line $line): void
    {
        foreach ([...$line->allowances, ...$line->charges] as $allowanceCharge) {
            self::allowanceCharge($allowanceCharge);
        }
        foreach ($line->subLines as $subLine) {
            self::line($subLine);
        }
        if (self::given($line->netPrice) && $line->additionalPriceDiscounts === [] && $line->priceCharges === []
            && ! self::given($line->priceDiscountPercentage) && ! self::given($line->priceDiscountBaseAmount)) {
            if (self::given($line->grossPrice) && ! self::given($line->priceDiscount)) {
                $line->priceDiscount = self::difference($line->grossPrice, $line->netPrice);
            } elseif (! self::given($line->grossPrice) && self::given($line->priceDiscount)) {
                $line->grossPrice = self::total($line->netPrice, $line->priceDiscount);
            }
        }
        if (self::given($line->netAmount)) {
            return;
        }
        $quantity = self::number($line->quantity);
        $price = self::number($line->netPrice);
        $allowances = self::sum(array_map(static fn(AllowanceCharge $item): mixed => $item->amount, $line->allowances));
        $charges = self::sum(array_map(static fn(AllowanceCharge $item): mixed => $item->amount, $line->charges));
        if ($quantity === null || $price === null || $allowances === null || $charges === null) {
            return;
        }
        $base = self::number($line->priceBaseQuantity);
        $amount = $quantity->multiply($price);
        if ($base !== null && ! $base->isZero()) {
            $amount = $amount->divide($base);
        }
        $line->netAmount = self::amount($amount->add($charges)->subtract($allowances));
    }

    /**
     * The amount of an allowance or charge from its base amount and percentage.
     */
    private static function allowanceCharge(AllowanceCharge $allowanceCharge): void
    {
        if (self::given($allowanceCharge->amount)) {
            return;
        }
        $base = self::number($allowanceCharge->baseAmount);
        $percentage = self::number($allowanceCharge->percentage);
        if ($base !== null && $percentage !== null) {
            $allowanceCharge->amount = self::amount($base->multiply($percentage)->divide(Decimal::of('100')));
        }
    }

    /**
     * The VAT breakdown: per VAT category and rate the net amounts of the lines plus the charges minus the allowances
     * of the document, and the tax on them. A breakdown given keeps its values and gets those it lacks; categories
     * the lines have and the breakdown does not are added, with the exemption reason of a line, allowance or charge
     * of the category where one gives it. Only where every line, allowance and charge names its category and amount.
     */
    private static function vatBreakdown(Invoice $invoice): void
    {
        /** @var array<string, array{category: string, rate: string|int|float|null, taxable: Decimal, reason: string|null, reasonCode: string|null}> $computed */
        $computed = [];
        $complete = true;
        $add = static function (?string $category, mixed $rate, mixed $amount, bool $negate, ?string $reason, ?string $reasonCode) use (&$computed, &$complete): void {
            $number = self::number($amount);
            if (! self::given($category) || $number === null) {
                $complete = false;

                return;
            }
            $key = self::key((string) $category, $rate);
            $computed[$key] ??= ['category' => (string) $category, 'rate' => $rate, 'taxable' => Decimal::of('0'), 'reason' => null, 'reasonCode' => null];
            $computed[$key]['taxable'] = $computed[$key]['taxable']->add($negate ? $number->negate() : $number);
            $computed[$key]['reason'] ??= self::given($reason) ? $reason : null;
            $computed[$key]['reasonCode'] ??= self::given($reasonCode) ? $reasonCode : null;
        };
        foreach ($invoice->lines as $line) {
            $add($line->vatCategory, $line->vatRate, $line->netAmount, false, $line->vatExemptionReason, $line->vatExemptionReasonCode);
        }
        foreach ($invoice->charges as $charge) {
            $add($charge->vatCategory, $charge->vatRate, $charge->amount, false, $charge->vatExemptionReason, $charge->vatExemptionReasonCode);
        }
        foreach ($invoice->allowances as $allowance) {
            $add($allowance->vatCategory, $allowance->vatRate, $allowance->amount, true, $allowance->vatExemptionReason, $allowance->vatExemptionReasonCode);
        }
        if ($invoice->lines === [] || ! $complete) {
            $computed = [];
        }

        foreach ($invoice->vatBreakdown as $breakdown) {
            $key = self::key((string) $breakdown->category, $breakdown->rate);
            if (! self::given($breakdown->taxableAmount) && isset($computed[$key])) {
                $breakdown->taxableAmount = self::amount($computed[$key]['taxable']);
            }
            if (! self::given($breakdown->taxAmount)) {
                $breakdown->taxAmount = self::tax($breakdown->taxableAmount, $breakdown->rate);
            }
            unset($computed[$key]);
        }
        foreach ($computed as $entry) {
            $breakdown = new VatBreakdown(
                category: $entry['category'],
                rate: $entry['rate'],
                taxableAmount: self::amount($entry['taxable']),
                exemptionReason: $entry['reason'],
                exemptionReasonCode: $entry['reasonCode'],
            );
            $breakdown->taxAmount = self::tax($breakdown->taxableAmount, $breakdown->rate);
            $invoice->vatBreakdown[] = $breakdown;
        }
    }

    /**
     * The totals (BR-CO-10 to BR-CO-16): the sums of the lines and of the allowances and charges, the net, VAT and
     * gross amount and the amount due - with the amounts of third parties (BR-DEX-09, BR-FXEXT-CO-16).
     */
    private static function totals(Invoice $invoice): void
    {
        $totals = $invoice->totals;
        if (! self::given($totals->lineNetAmount) && $invoice->lines !== []) {
            $totals->lineNetAmount = self::amount(self::sum(array_map(static fn(Line $line): mixed => $line->netAmount, $invoice->lines)));
        }
        if (! self::given($totals->allowanceAmount) && $invoice->allowances !== []) {
            $totals->allowanceAmount = self::amount(self::sum(array_map(static fn(AllowanceCharge $item): mixed => $item->amount, $invoice->allowances)));
        }
        if (! self::given($totals->chargeAmount) && $invoice->charges !== []) {
            $totals->chargeAmount = self::amount(self::sum(array_map(static fn(AllowanceCharge $item): mixed => $item->amount, $invoice->charges)));
        }
        if (! self::given($totals->netAmount)) {
            $lines = self::number($totals->lineNetAmount);
            $allowances = self::given($totals->allowanceAmount) ? self::number($totals->allowanceAmount) : Decimal::of('0');
            $charges = self::given($totals->chargeAmount) ? self::number($totals->chargeAmount) : Decimal::of('0');
            if ($lines !== null && $allowances !== null && $charges !== null) {
                $totals->netAmount = self::amount($lines->subtract($allowances)->add($charges));
            }
        }
        if (! self::given($totals->vatAmount) && $invoice->vatBreakdown !== []) {
            $totals->vatAmount = self::amount(self::sum(array_map(static fn(VatBreakdown $item): mixed => $item->taxAmount, $invoice->vatBreakdown)));
        }
        if (! self::given($totals->grossAmount)) {
            $net = self::number($totals->netAmount);
            $vat = self::number($totals->vatAmount);
            if ($net !== null && $vat !== null) {
                $totals->grossAmount = self::amount($net->add($vat));
            }
        }
        if (! self::given($totals->dueAmount)) {
            $gross = self::number($totals->grossAmount);
            $paid = self::given($totals->paidAmount) ? self::number($totals->paidAmount) : Decimal::of('0');
            $rounding = self::given($totals->roundingAmount) ? self::number($totals->roundingAmount) : Decimal::of('0');
            $thirdParties = self::sum(array_map(static fn(ThirdPartyPayment $item): mixed => $item->amount, $invoice->thirdPartyPayments));
            if ($gross !== null && $paid !== null && $rounding !== null && $thirdParties !== null) {
                $totals->dueAmount = self::amount($gross->subtract($paid)->add($rounding)->add($thirdParties));
            }
        }
    }

    /**
     * The tax of a taxable amount at a rate - none where no rate is given (a category without VAT).
     */
    private static function tax(mixed $taxable, mixed $rate): ?string
    {
        $amount = self::number($taxable);
        if ($amount === null) {
            return null;
        }
        $percent = self::given($rate) ? self::number($rate) : Decimal::of('0');

        return $percent === null ? null : self::amount($amount->multiply($percent)->divide(Decimal::of('100')));
    }

    /**
     * The key of a VAT category and rate: 19 and "19.00" are the same rate, no rate (a category without VAT) is 0.
     */
    private static function key(string $category, mixed $rate): string
    {
        $number = self::given($rate) ? self::number($rate) : Decimal::of('0');

        return strtoupper(trim($category)) . '|' . ($number?->toString() ?? (is_scalar($rate) ? (string) $rate : ''));
    }

    /**
     * The sum of amounts - null where one of them is missing or no number.
     *
     * @param list<mixed> $values
     */
    private static function sum(array $values): ?Decimal
    {
        $sum = Decimal::of('0');
        foreach ($values as $value) {
            $number = self::number($value);
            if ($number === null) {
                return null;
            }
            $sum = $sum->add($number);
        }

        return $sum;
    }

    /**
     * A value of the model as decimal: a numeric string, an int or a float as it reads ("0.1", not its binary form).
     */
    private static function number(mixed $value): ?Decimal
    {
        if (is_int($value)) {
            return Decimal::of((string) $value);
        }
        if (is_float($value)) {
            if (! is_finite($value)) {
                return null;
            }
            // Rounded to 10 decimals and written with as few of them as read back the same number (as Fields does).
            $rounded = round($value, 10);
            for ($places = 0; $places < 10; $places++) {
                if ((float) sprintf("%.{$places}F", $rounded) === $rounded) {
                    break;
                }
            }

            return Decimal::parse(sprintf("%.{$places}F", $rounded));
        }

        return is_string($value) ? Decimal::parse(trim($value)) : null;
    }

    /**
     * An amount with two decimals, rounded half away from zero ("12.50").
     */
    private static function amount(?Decimal $value): ?string
    {
        if ($value === null) {
            return null;
        }
        $negative = $value->compare(Decimal::of('0')) < 0;
        $rounded = $value->abs()->round(2);
        $text = $rounded->toString();
        [$integer, $fraction] = str_contains($text, '.') ? explode('.', $text) : [$text, ''];
        $text = $integer . '.' . str_pad($fraction, 2, '0');

        return $negative && ! $rounded->isZero() ? "-$text" : $text;
    }

    private static function given(mixed $value): bool
    {
        return $value !== null && $value !== '';
    }
}
