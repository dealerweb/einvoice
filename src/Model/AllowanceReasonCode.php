<?php

declare(strict_types=1);

namespace Dealerweb\EInvoice\Model;

/**
 * Common reasons of an allowance (BT-98, BT-140, UNTDID 5189) - any other code of the list is given as it is.
 */
final class AllowanceReasonCode
{
    public const BONUS_FOR_WORKS_AHEAD_OF_SCHEDULE = '41';

    public const OTHER_BONUS = '42';

    public const MANUFACTURERS_CONSUMER_DISCOUNT = '60';

    public const SPECIAL_AGREEMENT = '64';

    public const PRODUCTION_ERROR_DISCOUNT = '65';

    public const NEW_OUTLET_DISCOUNT = '66';

    public const SAMPLE_DISCOUNT = '67';

    public const END_OF_RANGE_DISCOUNT = '68';

    public const INCOTERM_DISCOUNT = '70';

    public const POINT_OF_SALES_THRESHOLD_ALLOWANCE = '71';

    public const MATERIAL_SURCHARGE_DEDUCTION = '88';

    public const DISCOUNT = '95';

    public const SPECIAL_REBATE = '100';

    public const FIXED_LONG_TERM = '102';

    public const TEMPORARY = '103';

    public const STANDARD = '104';

    public const YEARLY_TURNOVER = '105';
}
