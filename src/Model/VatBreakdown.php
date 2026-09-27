<?php

declare(strict_types=1);

namespace Dealerweb\EInvoice\Model;

/**
 * The VAT of one VAT category and rate.
 *
 * Used as:
 *  - invoice.vatBreakdown (BG-23)
 *
 * Generated from resources/model/mapping.php - do not edit.
 */
final class VatBreakdown extends Element
{
    public function __construct(
        /** Category (BT-118) */
        public ?string $category = null,
        /** Rate (BT-119) */
        public string|int|float|null $rate = null,
        /** Taxable amount (BT-116) */
        public string|int|float|null $taxableAmount = null,
        /** Tax amount (BT-117) */
        public string|int|float|null $taxAmount = null,
        /** Exemption reason (BT-120) */
        public ?string $exemptionReason = null,
        /** Exemption reason code (BT-121) */
        public ?string $exemptionReasonCode = null,
        /** Sum of line amounts (BT-X-262) */
        public string|int|float|null $lineTotalBasisAmount = null,
        /** Allowances and charges (BT-X-263) */
        public string|int|float|null $allowanceChargeBasisAmount = null,
    ) {}
}
