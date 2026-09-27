<?php

declare(strict_types=1);

// ZUGFeRD / Factur-X EXTENDED - EN 16931 and more, for what trade and industry need beyond it: here the delivery note
// (BT-X-202), the terms of a cash discount (BG-X-44) and a document name (BT-X-2); further sub lines, logistics
// charges, more parties and references. The recipient has to accept EXTENDED. MODEL.md shows the profiles of every
// field (X for EXTENDED).
//
//     php examples/create/zugferd-extended.php                writes output/zugferd-extended.xml and .pdf
//     php examples/create/zugferd-extended.php my-invoice.pdf  with your own PDF

use Dealerweb\EInvoice\Invoice;
use Dealerweb\EInvoice\Model\DocumentReference;
use Dealerweb\EInvoice\Model\PaymentCondition;
use Dealerweb\EInvoice\Profile;

require __DIR__ . '/../bootstrap.php';

/** @var Invoice $invoice */
$invoice = require __DIR__ . '/../sample-invoice.php';

$invoice->documentName = 'RECHNUNG';
$invoice->delivery->deliveryNote = new DocumentReference(number: 'LS-2026-0815', date: '2026-09-25');
$invoice->earlyPaymentDiscount = new PaymentCondition(   // 2 % cash discount within 7 days
    referenceDate: '2026-09-26',
    period: 7,
    periodUnit: 'DAY',
    baseAmount: '261.80',
    percentage: 2,
);

save('zugferd-extended.xml', $invoice->toXml(Profile::Extended));
save('zugferd-extended.pdf', $invoice->toPdf(Profile::Extended, pdf: contentOf(fileArgument('invoice.pdf'))));
