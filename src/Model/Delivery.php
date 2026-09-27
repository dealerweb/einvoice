<?php

declare(strict_types=1);

namespace Dealerweb\EInvoice\Model;

use DateTimeInterface;

/**
 * Where and when the goods and services were delivered.
 *
 * Used as:
 *  - invoice.delivery
 *
 * Generated from resources/model/mapping.php - do not edit.
 */
final class Delivery extends Element
{
    protected const OBJECTS = [
        'shipTo' => Party::class,
        'despatchAdvice' => DocumentReference::class,
        'receivingAdvice' => DocumentReference::class,
        'deliveryNote' => DocumentReference::class,
        'ultimateShipTo' => Party::class,
        'shipFrom' => Party::class,
    ];

    protected const LISTS = [
        'transportModeCodes' => null,
    ];

    /**
     * @param list<string> $transportModeCodes Transport mode (BT-X-152)
     */
    public function __construct(
        /** Date (BT-72) */
        public string|DateTimeInterface|null $date = null,
        /** Ship-to party (BG-13) */
        public Party $shipTo = new Party(),
        /** Despatch advice (BT-16) */
        public DocumentReference $despatchAdvice = new DocumentReference(),
        /** Receiving advice (BT-15) */
        public DocumentReference $receivingAdvice = new DocumentReference(),
        /** Delivery note (BT-X-202) */
        public DocumentReference $deliveryNote = new DocumentReference(),
        public array $transportModeCodes = [],
        /** Ultimate ship-to party (BG-X-27) */
        public Party $ultimateShipTo = new Party(),
        /** Ship-from party (BG-X-30) */
        public Party $shipFrom = new Party(),
    ) {}
}
