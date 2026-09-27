<?php

declare(strict_types=1);

namespace Dealerweb\EInvoice\Model;

/**
 * One instance of the item of a line: its batch, its serial number.
 *
 * Used as:
 *  - invoice.lines.instances (BG-X-84)
 *
 * Generated from resources/model/mapping.php - do not edit.
 */
final class ItemInstance extends Element
{
    public function __construct(
        /** Batch number (BT-X-306) */
        public ?string $batchId = null,
        /** Serial number (BT-X-307) */
        public ?string $serialId = null,
    ) {}
}
