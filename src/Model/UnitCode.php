<?php

declare(strict_types=1);

namespace Dealerweb\EInvoice\Model;

/**
 * Common unit codes (BT-130 and the other units, UN/ECE Recommendation 20 and 21) - any other code of the lists is
 * given as it is.
 */
final class UnitCode
{
    public const PIECE = 'H87';

    public const ONE = 'C62';

    public const SET = 'SET';

    public const PAIR = 'PR';

    public const PACKAGE = 'XPK';

    public const BOX = 'XBX';

    public const LUMP_SUM = 'LS';

    public const PERCENT = 'P1';

    public const MINUTE = 'MIN';

    public const HOUR = 'HUR';

    public const DAY = 'DAY';

    public const WEEK = 'WEE';

    public const MONTH = 'MON';

    public const YEAR = 'ANN';

    public const GRAM = 'GRM';

    public const KILOGRAM = 'KGM';

    public const TONNE = 'TNE';

    public const MILLIMETRE = 'MMT';

    public const CENTIMETRE = 'CMT';

    public const METRE = 'MTR';

    public const KILOMETRE = 'KMT';

    public const SQUARE_METRE = 'MTK';

    public const CUBIC_METRE = 'MTQ';

    public const MILLILITRE = 'MLT';

    public const LITRE = 'LTR';

    public const KILOWATT_HOUR = 'KWH';
}
