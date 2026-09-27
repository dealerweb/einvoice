<?php

declare(strict_types=1);

namespace Dealerweb\EInvoice\Model;

use DateTimeInterface;

/**
 * A payment received in advance.
 *
 * Used as:
 *  - invoice.advancePayments (BG-X-45)
 *
 * Generated from resources/model/mapping.php - do not edit.
 */
final class AdvancePayment extends Element
{
    protected const OBJECTS = [
        'precedingInvoice' => DocumentReference::class,
    ];

    protected const LISTS = [
        'taxes' => Tax::class,
    ];

    /**
     * @param list<Tax> $taxes Tax (BG-X-46)
     */
    public function __construct(
        /** Amount (BT-X-291) */
        public string|int|float|null $amount = null,
        /** Date (BT-X-292) */
        public string|DateTimeInterface|null $date = null,
        public array $taxes = [],
        /** Preceding invoice (BG-X-85) */
        public DocumentReference $precedingInvoice = new DocumentReference(),
    ) {}
}
