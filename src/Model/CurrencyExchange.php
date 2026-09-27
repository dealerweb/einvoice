<?php

declare(strict_types=1);

namespace Dealerweb\EInvoice\Model;

use DateTimeInterface;

/**
 * The exchange rate between the invoice currency and another.
 *
 * Used as:
 *  - invoice.currencyExchange (BG-X-41)
 *
 * Generated from resources/model/mapping.php - do not edit.
 */
final class CurrencyExchange extends Element
{
    public function __construct(
        /** Source currency (BT-X-258) */
        public ?string $sourceCurrency = null,
        /** Target currency (BT-X-259) */
        public ?string $targetCurrency = null,
        /** Exchange rate (BT-X-260) */
        public string|int|float|null $rate = null,
        /** Date (BT-X-261) */
        public string|DateTimeInterface|null $date = null,
    ) {}
}
