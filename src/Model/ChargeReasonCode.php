<?php

declare(strict_types=1);

namespace Dealerweb\EInvoice\Model;

/**
 * Common reasons of a charge (BT-105, BT-145, UNTDID 7161) - any other code of the list is given as it is.
 */
final class ChargeReasonCode
{
    public const ADVERTISING = 'AA';

    public const MISCELLANEOUS = 'ABK';

    public const ADDITIONAL_PACKAGING = 'ABL';

    public const OTHER_SERVICES = 'ADR';

    public const PICK_UP = 'ADT';

    public const COLLECTION_AND_RECYCLING = 'AEO';

    public const CLEANING = 'CG';

    public const DELIVERY = 'DL';

    public const FREIGHT_SERVICE = 'FC';

    public const FINANCING = 'FI';

    public const LABELLING = 'LA';

    public const PACKING = 'PC';

    public const SHIPPING_AND_HANDLING = 'SAA';

    public const MUTUALLY_DEFINED = 'ZZZ';
}
