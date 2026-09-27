<?php

declare(strict_types=1);

namespace Dealerweb\EInvoice\Model;

/**
 * A tax: a further VAT of a line, the tax included in a price, the VAT of an advance payment or of a logistics
 * charge.
 *
 * Used as:
 *  - invoice.lines.additionalTaxes (BG-30)
 *  - invoice.lines.includedTax (BG-X-4)
 *  - invoice.logisticsServiceCharges.taxes (BT-X-273-00)
 *  - invoice.advancePayments.taxes (BG-X-46)
 *
 * Generated from resources/model/mapping.php - do not edit.
 */
final class Tax extends Element
{
    public function __construct(
        /** VAT category code */
        public ?string $category = null,
        /** VAT rate in percent */
        public string|int|float|null $rate = null,
        /** Tax amount */
        public string|int|float|null $amount = null,
        /** Reason for the VAT exemption (text) */
        public ?string $exemptionReason = null,
        /** Reason for the VAT exemption (code, VATEX) */
        public ?string $exemptionReasonCode = null,
        /** VAT point date code */
        public ?string $taxPointDateCode = null,
    ) {}
}
