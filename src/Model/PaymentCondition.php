<?php

declare(strict_types=1);

namespace Dealerweb\EInvoice\Model;

use DateTimeInterface;

/**
 * The terms of an early payment discount or of a late payment penalty.
 *
 * Used as:
 *  - invoice.earlyPaymentDiscount (BG-X-44)
 *  - invoice.latePaymentPenalty (BG-X-43)
 *  - invoice.additionalPaymentTerms.earlyPaymentDiscount (BG-X-44)
 *  - invoice.additionalPaymentTerms.latePaymentPenalty (BG-X-43)
 *
 * Generated from resources/model/mapping.php - do not edit.
 */
final class PaymentCondition extends Element
{
    public function __construct(
        /** Date the period starts from */
        public string|DateTimeInterface|null $referenceDate = null,
        /** Length of the period */
        public string|int|float|null $period = null,
        /** Unit of the period (code, e.g. DAY) */
        public ?string $periodUnit = null,
        /** Amount the percentage applies to */
        public string|int|float|null $baseAmount = null,
        /** Percentage */
        public string|int|float|null $percentage = null,
        /** Amount */
        public string|int|float|null $amount = null,
    ) {}
}
