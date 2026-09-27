<?php

declare(strict_types=1);

namespace Dealerweb\EInvoice\Model;

/**
 * The VAT category codes of EN 16931 (UNTDID 5305): of a line, an allowance or charge, the VAT breakdown.
 */
final class VatCategoryCode
{
    public const STANDARD_RATE = 'S';

    public const ZERO_RATED = 'Z';

    public const EXEMPT = 'E';

    public const REVERSE_CHARGE = 'AE';

    public const INTRA_COMMUNITY_SUPPLY = 'K';

    public const EXPORT_OUTSIDE_EU = 'G';

    public const NOT_SUBJECT_TO_VAT = 'O';

    public const CANARY_ISLANDS = 'L';

    public const CEUTA_AND_MELILLA = 'M';
}
