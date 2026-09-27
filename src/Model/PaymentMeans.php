<?php

declare(strict_types=1);

namespace Dealerweb\EInvoice\Model;

/**
 * How the invoice is to be paid: credit transfer, card, direct debit.
 *
 * Used as:
 *  - invoice.paymentMeans (BG-16)
 *
 * Generated from resources/model/mapping.php - do not edit.
 */
final class PaymentMeans extends Element
{
    public function __construct(
        /** Payment means (BT-81) */
        public ?string $typeCode = null,
        /** Text (BT-82) */
        public ?string $text = null,
        /** Account (BT-84) */
        public ?string $accountId = null,
        /** Account name (BT-85) */
        public ?string $accountName = null,
        /** BIC (BT-86) */
        public ?string $bic = null,
        /** Card number (BT-87) */
        public ?string $cardNumber = null,
        /** Card holder (BT-88) */
        public ?string $cardHolder = null,
        /** Debited account (BT-91) */
        public ?string $debitedAccountId = null,
        /** Debited account name (BT-216) */
        public ?string $debitedAccountName = null,
        /** Debited account BIC (BT-215) */
        public ?string $debitedAccountBic = null,
    ) {}
}
