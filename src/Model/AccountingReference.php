<?php

declare(strict_types=1);

namespace Dealerweb\EInvoice\Model;

/**
 * Where the buyer books the invoice: an account and its type.
 *
 * Used as:
 *  - invoice.lines.additionalBuyerAccountingReferences (BT-133-00)
 *  - invoice.additionalBuyerAccountingReferences (BT-19-00)
 *
 * Generated from resources/model/mapping.php - do not edit.
 */
final class AccountingReference extends Element
{
    protected const PRIMARY = 'id';

    public function __construct(
        /** Account */
        public ?string $id = null,
        /** Type of the account (code, EXTENDED) */
        public ?string $typeCode = null,
    ) {}
}
