<?php

declare(strict_types=1);

namespace Dealerweb\EInvoice\Model;

use DateTimeInterface;

/**
 * A period of time.
 *
 * Used as:
 *  - invoice.invoicingPeriod (BG-14)
 *  - invoice.lines.period (BG-26)
 *
 * Generated from resources/model/mapping.php - do not edit.
 */
final class Period extends Element
{
    public function __construct(
        /** First day */
        public string|DateTimeInterface|null $startDate = null,
        /** Last day */
        public string|DateTimeInterface|null $endDate = null,
        /** Description (EXTENDED) */
        public ?string $description = null,
    ) {}
}
