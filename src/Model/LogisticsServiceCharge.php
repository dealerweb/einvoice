<?php

declare(strict_types=1);

namespace Dealerweb\EInvoice\Model;

/**
 * A charge for a logistics service.
 *
 * Used as:
 *  - invoice.logisticsServiceCharges (BG-X-42)
 *
 * Generated from resources/model/mapping.php - do not edit.
 */
final class LogisticsServiceCharge extends Element
{
    protected const LISTS = [
        'taxes' => Tax::class,
    ];

    /**
     * @param list<Tax> $taxes Tax (BT-X-273-00)
     */
    public function __construct(
        /** Description (BT-X-271) */
        public ?string $description = null,
        /** Amount (BT-X-272) */
        public string|int|float|null $amount = null,
        public array $taxes = [],
    ) {}
}
