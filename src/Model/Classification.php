<?php

declare(strict_types=1);

namespace Dealerweb\EInvoice\Model;

/**
 * A classification of the item of a line.
 *
 * Used as:
 *  - invoice.lines.classifications (BT-158-00)
 *
 * Generated from resources/model/mapping.php - do not edit.
 */
final class Classification extends Element
{
    public function __construct(
        /** Classification (BT-158) */
        public ?string $value = null,
        /** Scheme (BT-158-1) */
        public ?string $scheme = null,
        /** Scheme version (BT-158-2) */
        public ?string $schemeVersion = null,
        /** Name (BT-X-13) */
        public ?string $name = null,
    ) {}
}
