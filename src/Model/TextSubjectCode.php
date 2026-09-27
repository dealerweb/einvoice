<?php

declare(strict_types=1);

namespace Dealerweb\EInvoice\Model;

/**
 * Common subjects of a note (BT-21, UNTDID 4451) - any other code of the list is given as it is.
 */
final class TextSubjectCode
{
    public const GENERAL_INFORMATION = 'AAI';

    public const PAYMENT_TERM = 'AAB';

    public const ADDITIONAL_CONDITIONS = 'AAJ';

    public const GOVERNMENT_INFORMATION = 'ABL';

    public const NOTE = 'ADU';

    public const PAYMENT_INFORMATION = 'PMT';

    public const PAYMENT_DETAIL = 'PMD';

    public const REGULATORY_INFORMATION = 'REG';

    public const SUPPLIER_REMARKS = 'SUR';

    public const TAX_DECLARATION = 'TXD';

    public const CUSTOMS_DECLARATION = 'CUS';
}
