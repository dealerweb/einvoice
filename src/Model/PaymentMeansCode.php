<?php

declare(strict_types=1);

namespace Dealerweb\EInvoice\Model;

/**
 * Common payment means codes (BT-81, UNTDID 4461) - any other code of the list is given as it is.
 */
final class PaymentMeansCode
{
    public const NOT_DEFINED = '1';

    public const CASH = '10';

    public const CHEQUE = '20';

    public const CREDIT_TRANSFER = '30';

    public const PAYMENT_TO_BANK_ACCOUNT = '42';

    public const BANK_CARD = '48';

    public const DIRECT_DEBIT = '49';

    public const CREDIT_CARD = '54';

    public const DEBIT_CARD = '55';

    public const STANDING_AGREEMENT = '57';

    public const SEPA_CREDIT_TRANSFER = '58';

    public const SEPA_DIRECT_DEBIT = '59';

    public const ONLINE_PAYMENT_SERVICE = '68';

    public const CLEARING_BETWEEN_PARTNERS = '97';

    public const MUTUALLY_DEFINED = 'ZZZ';
}
