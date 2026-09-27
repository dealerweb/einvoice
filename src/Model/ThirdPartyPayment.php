<?php

declare(strict_types=1);

namespace Dealerweb\EInvoice\Model;

/**
 * An amount paid to or collected for a third party - it counts to the amount due.
 *
 * Used as:
 *  - invoice.thirdPartyPayments (BG-DEX-09, BG-34)
 *
 * Generated from resources/model/mapping.php - do not edit.
 */
final class ThirdPartyPayment extends Element
{
    public function __construct(
        /** Type of the payment (XRechnung extension, UBL only) (BT-DEX-001) */
        public ?string $type = null,
        /** Amount (BT-DEX-002, BT-179) */
        public string|int|float|null $amount = null,
        /** Description (BT-DEX-003, BT-180) */
        public ?string $description = null,
    ) {}
}
