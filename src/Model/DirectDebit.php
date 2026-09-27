<?php

declare(strict_types=1);

namespace Dealerweb\EInvoice\Model;

/**
 * The direct debit: mandate and creditor identifier.
 *
 * Used as:
 *  - invoice.directDebit (BG-19)
 *
 * Generated from resources/model/mapping.php - do not edit.
 */
final class DirectDebit extends Element
{
    public function __construct(
        /** Mandate reference (BT-89) */
        public ?string $mandateReference = null,
        /** Creditor identifier (BT-90) */
        public ?string $creditorId = null,
    ) {}
}
