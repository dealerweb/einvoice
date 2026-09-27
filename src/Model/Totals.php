<?php

declare(strict_types=1);

namespace Dealerweb\EInvoice\Model;

/**
 * The totals of the invoice.
 *
 * Used as:
 *  - invoice.totals (BG-22)
 *
 * Generated from resources/model/mapping.php - do not edit.
 */
final class Totals extends Element
{
    public function __construct(
        /** Sum of line net amounts (BT-106) */
        public string|int|float|null $lineNetAmount = null,
        /** Sum of allowances (BT-107) */
        public string|int|float|null $allowanceAmount = null,
        /** Sum of charges (BT-108) */
        public string|int|float|null $chargeAmount = null,
        /** Net amount (BT-109) */
        public string|int|float|null $netAmount = null,
        /** VAT amount (BT-110) */
        public string|int|float|null $vatAmount = null,
        /** VAT amount in VAT currency (BT-111) */
        public string|int|float|null $vatAmountInVatCurrency = null,
        /** Gross amount (BT-112) */
        public string|int|float|null $grossAmount = null,
        /** Paid amount (BT-113) */
        public string|int|float|null $paidAmount = null,
        /** Rounding amount (BT-114) */
        public string|int|float|null $roundingAmount = null,
        /** Amount due (BT-115) */
        public string|int|float|null $dueAmount = null,
    ) {}
}
