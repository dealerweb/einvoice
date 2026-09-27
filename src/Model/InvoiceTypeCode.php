<?php

declare(strict_types=1);

namespace Dealerweb\EInvoice\Model;

/**
 * Common invoice type codes (BT-3, UNTDID 1001) - any other code of the list is given as it is.
 */
final class InvoiceTypeCode
{
    public const COMMERCIAL_INVOICE = '380';

    public const CREDIT_NOTE = '381';

    public const DEBIT_NOTE = '383';

    public const CORRECTED_INVOICE = '384';

    public const PREPAYMENT_INVOICE = '386';

    public const SELF_BILLED_INVOICE = '389';

    public const SELF_BILLED_CREDIT_NOTE = '261';

    public const PARTIAL_INVOICE = '326';

    public const PARTIAL_CONSTRUCTION_INVOICE = '875';

    public const PARTIAL_FINAL_CONSTRUCTION_INVOICE = '876';

    public const FINAL_CONSTRUCTION_INVOICE = '877';

    public const FACTORED_INVOICE = '393';

    public const INVOICE_INFORMATION_FOR_ACCOUNTING = '751';
}
