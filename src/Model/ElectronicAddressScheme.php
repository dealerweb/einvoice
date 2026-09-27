<?php

declare(strict_types=1);

namespace Dealerweb\EInvoice\Model;

/**
 * Common schemes of an electronic address (BT-34-1, BT-49-1, EAS) - any other code of the list is given as it is.
 */
final class ElectronicAddressScheme
{
    public const EMAIL = 'EM';

    public const LEITWEG_ID = '0204';

    public const GLN = '0088';

    public const DUNS = '0060';

    public const GERMAN_VAT_NUMBER = '9930';
}
