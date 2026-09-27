<?php

declare(strict_types=1);

namespace Dealerweb\EInvoice\Model;

/**
 * An attribute of the item of a line.
 *
 * Used as:
 *  - invoice.lines.attributes (BG-32)
 *
 * Generated from resources/model/mapping.php - do not edit.
 */
final class ItemAttribute extends Element
{
    public function __construct(
        /** Name (BT-160) */
        public ?string $name = null,
        /** Value (BT-161) */
        public ?string $value = null,
        /** Numeric value (BT-X-12) */
        public string|int|float|null $numericValue = null,
        /** Unit (BT-X-12-0) */
        public ?string $unit = null,
        /** Type (BT-X-11) */
        public ?string $typeCode = null,
    ) {}
}
