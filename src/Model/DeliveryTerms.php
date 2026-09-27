<?php

declare(strict_types=1);

namespace Dealerweb\EInvoice\Model;

/**
 * The terms of delivery (Incoterms).
 *
 * Used as:
 *  - invoice.lines.deliveryTerms (BG-X-87)
 *  - invoice.deliveryTerms (BG-X-22)
 *
 * Generated from resources/model/mapping.php - do not edit.
 */
final class DeliveryTerms extends Element
{
    public function __construct(
        /** Terms of delivery (code, e.g. an Incoterm) */
        public ?string $code = null,
        /** Country of the location of the terms */
        public ?string $locationCountry = null,
        /** Name of the location of the terms */
        public ?string $locationName = null,
    ) {}
}
