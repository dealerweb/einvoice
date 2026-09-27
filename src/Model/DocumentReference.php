<?php

declare(strict_types=1);

namespace Dealerweb\EInvoice\Model;

use DateTimeInterface;

/**
 * A reference to another document: an order, a contract, a despatch advice, a preceding invoice.
 *
 * Used as:
 *  - invoice.precedingInvoices (BG-3)
 *  - invoice.purchaseOrder
 *  - invoice.salesOrder
 *  - invoice.contract
 *  - invoice.tender
 *  - invoice.invoicedObject
 *  - invoice.delivery.despatchAdvice
 *  - invoice.delivery.receivingAdvice
 *  - and 15 more (MODEL.md)
 *
 * Generated from resources/model/mapping.php - do not edit.
 */
final class DocumentReference extends Element
{
    protected const PRIMARY = 'number';

    public function __construct(
        /** Number of the referenced document */
        public ?string $number = null,
        /** Issue date of the referenced document */
        public string|DateTimeInterface|null $date = null,
        /** Type of the referenced document (code) */
        public ?string $typeCode = null,
        /** Scheme of the number */
        public ?string $scheme = null,
        /** Line of the referenced document */
        public ?string $lineId = null,
    ) {}
}
