<?php

declare(strict_types=1);

namespace Dealerweb\EInvoice\Model;

use DateTimeInterface;

/**
 * Terms of payment (an instalment): its due date, its amount, its conditions.
 *
 * Used as:
 *  - invoice.additionalPaymentTerms (BT-20-00)
 *
 * Generated from resources/model/mapping.php - do not edit.
 */
final class PaymentTerms extends Element
{
    protected const OBJECTS = [
        'earlyPaymentDiscount' => PaymentCondition::class,
        'latePaymentPenalty' => PaymentCondition::class,
        'payee' => Party::class,
    ];

    public function __construct(
        /** Terms of payment (text) (BT-20) */
        public ?string $description = null,
        /** Due date (BT-9) */
        public string|DateTimeInterface|null $dueDate = null,
        /** Mandate reference of a direct debit (BT-89) */
        public ?string $mandateReference = null,
        /** Amount of this payment (BT-X-275) */
        public string|int|float|null $partialPaymentAmount = null,
        /** Early payment discount */
        public PaymentCondition $earlyPaymentDiscount = new PaymentCondition(),
        /** Late payment penalty */
        public PaymentCondition $latePaymentPenalty = new PaymentCondition(),
        /** Payee of this payment, where it is not the payee of the invoice */
        public Party $payee = new Party(),
    ) {}
}
