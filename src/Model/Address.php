<?php

declare(strict_types=1);

namespace Dealerweb\EInvoice\Model;

/**
 * A postal address.
 *
 * Used as:
 *  - invoice.seller.address (BG-5)
 *  - invoice.seller.legalAddress (BG-X-14)
 *  - invoice.buyer.address (BG-8)
 *  - invoice.buyer.legalAddress (BG-X-15)
 *  - invoice.payee.address (BG-X-40)
 *  - invoice.payee.legalAddress (BG-X-72)
 *  - invoice.sellerTaxRepresentative.address (BG-12)
 *  - invoice.sellerTaxRepresentative.legalAddress (BG-X-59)
 *  - and 28 more (MODEL.md)
 *
 * Generated from resources/model/mapping.php - do not edit.
 */
final class Address extends Element
{
    public function __construct(
        /** Address line 1, usually street and house number - or a post box */
        public ?string $line1 = null,
        /** Address line 2 */
        public ?string $line2 = null,
        /** Address line 3 */
        public ?string $line3 = null,
        /** Post code */
        public ?string $postcode = null,
        /** City */
        public ?string $city = null,
        /** Country subdivision: region, federal state */
        public ?string $subdivision = null,
        /** Country code (ISO 3166-1 alpha-2, e.g. DE) */
        public ?string $country = null,
    ) {}
}
