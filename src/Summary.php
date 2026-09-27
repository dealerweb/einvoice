<?php

declare(strict_types=1);

namespace Dealerweb\EInvoice;

use DateTimeInterface;
use Dealerweb\EInvoice\Model\Line;
use Dealerweb\EInvoice\Model\Party;
use InvalidArgumentException;

/**
 * The key facts of an invoice for capturing it as a document (Invoice::summary()): number, type, dates, parties,
 * amounts, VAT rates and a subject.
 *
 * The values are the invoice's, without blanks around them: amounts and rates as decimal text ("529.87", never
 * float), dates as ISO date (YYYY-MM-DD), seller and buyer as the invoice names them - also for a self-billed invoice,
 * where the buyer issues the document. Two are interpreted: the due date is the earliest the invoice gives (cash
 * discount, instalments), the subject is the name of the first line, or its description where the name only repeats
 * the article number (Rules::itemName) - as the rendering shows it.
 */
final readonly class Summary
{
    /**
     * @param list<string> $vatRates distinct rates of the VAT breakdown (BT-119), the highest first
     */
    public function __construct(
        /** BT-1 */
        public ?string $number,
        /** BT-3, UNTDID 1001 */
        public ?string $typeCode,
        /** Invoice::isCreditNote() */
        public bool $isCreditNote,
        /** Invoice::isSelfBilled() */
        public bool $isSelfBilled,
        /** BT-2 */
        public ?string $issueDate,
        /** BT-9; the earliest where the invoice gives several (cash discount, instalments) */
        public ?string $dueDate,
        /** BT-5 */
        public ?string $currency,
        /** BT-10, in Germany the Leitweg-ID */
        public ?string $buyerReference,
        /** BT-13 */
        public ?string $orderReference,
        /** The seller (BG-4), a copy of the invoice's - null where the invoice names none */
        public ?Party $seller,
        /** The buyer (BG-7), a copy of the invoice's - null where the invoice names none */
        public ?Party $buyer,
        /** BT-109 */
        public ?string $netAmount,
        /** BT-110 */
        public ?string $vatAmount,
        /** BT-112 */
        public ?string $grossAmount,
        /** BT-115 */
        public ?string $dueAmount,
        public array $vatRates,
        /** Name of the first line (BT-153), its description (BT-154) where the name is the article number, on one line */
        public ?string $subject,
    ) {}

    /**
     * @throws InvalidArgumentException a list of the invoice holds an item of the wrong kind, with its path
     */
    public static function of(Invoice $invoice): self
    {
        $invoice->checkItems();
        $dueDates = [self::date($invoice->dueDate)];
        foreach ($invoice->additionalPaymentTerms as $terms) {
            $dueDates[] = self::date($terms->dueDate);
        }
        $dueDates = array_filter($dueDates, static fn(?string $date): bool => $date !== null);

        return new self(
            number: self::text($invoice->number),
            typeCode: self::text($invoice->typeCode),
            isCreditNote: $invoice->isCreditNote(),
            isSelfBilled: $invoice->isSelfBilled(),
            issueDate: self::date($invoice->issueDate),
            dueDate: $dueDates === [] ? null : min($dueDates),
            currency: self::text($invoice->currency),
            buyerReference: self::text($invoice->buyerReference),
            orderReference: self::text($invoice->purchaseOrder->number),
            seller: $invoice->seller->isEmpty() ? null : clone $invoice->seller,
            buyer: $invoice->buyer->isEmpty() ? null : clone $invoice->buyer,
            netAmount: self::text($invoice->totals->netAmount),
            vatAmount: self::text($invoice->totals->vatAmount),
            grossAmount: self::text($invoice->totals->grossAmount),
            dueAmount: self::text($invoice->totals->dueAmount),
            vatRates: self::vatRates($invoice),
            subject: self::subject($invoice->lines[0] ?? null),
        );
    }

    /**
     * The summary as nested arrays, e.g. for JSON - the parties with the names of the model.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            ...get_object_vars($this),
            'seller' => $this->seller?->toArray(),
            'buyer' => $this->buyer?->toArray(),
        ];
    }

    /**
     * @return list<string>
     */
    private static function vatRates(Invoice $invoice): array
    {
        $rates = [];
        foreach ($invoice->vatBreakdown as $vat) {
            $rate = self::text($vat->rate);
            if ($rate !== null) {
                // "19" and "19.00" are the same rate - only zeros after the decimal point say nothing
                $rates[str_contains($rate, '.') ? rtrim(rtrim($rate, '0'), '.') : $rate] ??= $rate;
            }
        }

        $rates = array_values($rates);
        usort($rates, static fn(string $a, string $b): int => (float) $b <=> (float) $a);

        return $rates;
    }

    private static function subject(?Line $line): ?string
    {
        [$name] = Rules::itemName(self::text($line?->name), self::text($line?->description), self::text($line?->sellerItemId));

        return $name === null ? null : trim(preg_replace('/\s+/u', ' ', $name) ?? $name);
    }

    /**
     * A value as text without blanks around it - null for none.
     */
    private static function text(string|int|float|null $value): ?string
    {
        $text = trim((string) $value);

        return $text === '' ? null : $text;
    }

    /**
     * An ISO date, or null - an impossible date is read as delivered ("ILLEGAL DATE FORMAT of ...").
     */
    private static function date(string|DateTimeInterface|null $value): ?string
    {
        if ($value instanceof DateTimeInterface) {
            return $value->format('Y-m-d');
        }
        $value = self::text($value);

        return $value !== null && preg_match('/^\d{4}-\d{2}-\d{2}$/', $value) === 1 ? $value : null;
    }
}
