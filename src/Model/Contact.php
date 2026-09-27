<?php

declare(strict_types=1);

namespace Dealerweb\EInvoice\Model;

/**
 * A contact of a party.
 *
 * Used as:
 *  - invoice.seller.contact (BG-6)
 *  - invoice.seller.additionalContacts (BG-6)
 *  - invoice.buyer.contact (BG-9)
 *  - invoice.buyer.additionalContacts (BG-9)
 *  - invoice.payee.contact (BG-X-39)
 *  - invoice.payee.additionalContacts (BG-X-39)
 *  - invoice.sellerTaxRepresentative.contact (BG-X-17)
 *  - invoice.sellerTaxRepresentative.additionalContacts (BG-X-17)
 *  - and 32 more (MODEL.md)
 *
 * Generated from resources/model/mapping.php - do not edit.
 */
final class Contact extends Element
{
    public function __construct(
        /** Name of the contact person */
        public ?string $name = null,
        /** Department */
        public ?string $department = null,
        /** Telephone number */
        public ?string $phone = null,
        /** Fax number (EXTENDED) */
        public ?string $fax = null,
        /** E-mail address */
        public ?string $email = null,
        /** Type of the contact (code, EXTENDED) */
        public ?string $typeCode = null,
    ) {}
}
