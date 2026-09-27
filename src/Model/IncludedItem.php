<?php

declare(strict_types=1);

namespace Dealerweb\EInvoice\Model;

/**
 * An item included in the item of a line.
 *
 * Used as:
 *  - invoice.lines.includedItems (BG-X-1)
 *
 * Generated from resources/model/mapping.php - do not edit.
 */
final class IncludedItem extends Element
{
    protected const LISTS = [
        'identifiers' => Identifier::class,
    ];

    /**
     * @param list<Identifier> $identifiers Global identifier (BT-X-15)
     */
    public function __construct(
        /** Identifier (BT-X-308) */
        public ?string $id = null,
        public array $identifiers = [],
        /** Seller item number (BT-X-16) */
        public ?string $sellerItemId = null,
        /** Buyer item number (BT-X-17) */
        public ?string $buyerItemId = null,
        /** Industry item identifier (BT-X-309) */
        public ?string $industryItemId = null,
        /** Name (BT-X-18) */
        public ?string $name = null,
        /** Description (BT-X-19) */
        public ?string $description = null,
        /** Quantity (BT-X-20) */
        public string|int|float|null $quantity = null,
        /** Unit (BT-X-20-1) */
        public ?string $unit = null,
    ) {}
}
