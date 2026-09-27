<?php

declare(strict_types=1);

namespace Dealerweb\EInvoice\Model;

/**
 * An allowance or a charge: of the document, of a line or of a price.
 *
 * Used as:
 *  - invoice.allowances (BG-20)
 *  - invoice.charges (BG-21)
 *  - invoice.lines.allowances (BG-27)
 *  - invoice.lines.charges (BG-28)
 *  - invoice.lines.additionalPriceDiscounts (BT-147-00)
 *  - invoice.lines.priceCharges (BT-X-302-00)
 *
 * Generated from resources/model/mapping.php - do not edit.
 */
final class AllowanceCharge extends Element
{
    public function __construct(
        /** Amount, without VAT */
        public string|int|float|null $amount = null,
        /** Amount the percentage applies to */
        public string|int|float|null $baseAmount = null,
        /** Percentage */
        public string|int|float|null $percentage = null,
        /** Reason (text) */
        public ?string $reason = null,
        /** Reason (code: UNTDID 5189 for an allowance, UNTDID 7161 for a charge) */
        public ?string $reasonCode = null,
        /** VAT category code */
        public ?string $vatCategory = null,
        /** VAT rate in percent */
        public string|int|float|null $vatRate = null,
        /** Reason for the VAT exemption (text) */
        public ?string $vatExemptionReason = null,
        /** Reason for the VAT exemption (code, VATEX) */
        public ?string $vatExemptionReasonCode = null,
        /** Order of the calculation (EXTENDED) */
        public ?string $sequence = null,
        /** Quantity the amount applies to (EXTENDED) */
        public string|int|float|null $baseQuantity = null,
        /** Unit of that quantity (EXTENDED) */
        public ?string $baseUnit = null,
        /** Type of a tax other than VAT (code) */
        public ?string $taxTypeCode = null,
    ) {}
}
