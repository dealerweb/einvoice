<?php

declare(strict_types=1);

namespace Dealerweb\EInvoice\Model;

/**
 * The project the invoice refers to.
 *
 * Used as:
 *  - invoice.project (BT-11-00)
 *
 * Generated from resources/model/mapping.php - do not edit.
 */
final class Project extends Element
{
    protected const PRIMARY = 'id';

    public function __construct(
        /** Identifier (BT-11) */
        public ?string $id = null,
        /** Name (BT-11-0) */
        public ?string $name = null,
    ) {}
}
