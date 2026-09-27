<?php

declare(strict_types=1);

namespace Dealerweb\EInvoice\Model;

/**
 * An identifier with its scheme (an ICD such as 0088 for a GLN, the EAS code of an electronic address).
 *
 * Used as:
 *  - invoice.seller.identifiers
 *  - invoice.seller.legalRegistrationId
 *  - invoice.seller.electronicAddress
 *  - invoice.buyer.identifiers
 *  - invoice.buyer.legalRegistrationId
 *  - invoice.buyer.electronicAddress
 *  - invoice.payee.identifiers
 *  - invoice.payee.legalRegistrationId
 *  - and 56 more (MODEL.md)
 *
 * Generated from resources/model/mapping.php - do not edit.
 */
final class Identifier extends Element
{
    protected const PRIMARY = 'value';

    public function __construct(
        /** The identifier */
        public ?string $value = null,
        /** Its scheme, where it has one */
        public ?string $scheme = null,
    ) {}
}
