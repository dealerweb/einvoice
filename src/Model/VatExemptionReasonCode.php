<?php

declare(strict_types=1);

namespace Dealerweb\EInvoice\Model;

/**
 * Common reasons of a VAT exemption (BT-121, VATEX) - any other code of the list is given as it is.
 */
final class VatExemptionReasonCode
{
    public const REVERSE_CHARGE = 'VATEX-EU-AE';

    public const INTRA_COMMUNITY_SUPPLY = 'VATEX-EU-IC';

    public const EXPORT_OUTSIDE_EU = 'VATEX-EU-G';

    public const NOT_SUBJECT_TO_VAT = 'VATEX-EU-O';

    public const ARTICLE_79_C = 'VATEX-EU-79-C';

    public const ARTICLE_132 = 'VATEX-EU-132';

    public const ARTICLE_143 = 'VATEX-EU-143';

    public const ARTICLE_148 = 'VATEX-EU-148';

    public const ARTICLE_151 = 'VATEX-EU-151';

    public const ARTICLE_309 = 'VATEX-EU-309';

    public const TRAVEL_AGENTS = 'VATEX-EU-D';

    public const SECOND_HAND_GOODS = 'VATEX-EU-F';

    public const WORKS_OF_ART = 'VATEX-EU-I';

    public const COLLECTORS_ITEMS_AND_ANTIQUES = 'VATEX-EU-J';
}
