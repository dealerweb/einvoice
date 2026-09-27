<?php

declare(strict_types=1);

// Peppol BIS Billing 3.0 in UBL - the invoice of the Peppol network (used for instance in Belgium, the Netherlands,
// Norway, Sweden, Denmark, Australia and Singapore), sent through a Peppol access point. Peppol takes electronic
// addresses with a scheme of Peppol (EAS) - no e-mail address: here the German VAT identifier (9930) and the Leitweg-ID
// (0204). The business process of Peppol (BT-23) is set where the invoice gives none.
//
//     php examples/create/peppol-ubl.php               writes output/peppol-ubl.xml

use Dealerweb\EInvoice\Invoice;
use Dealerweb\EInvoice\Model\Identifier;
use Dealerweb\EInvoice\Profile;

require __DIR__ . '/../bootstrap.php';

/** @var Invoice $invoice */
$invoice = require __DIR__ . '/../sample-invoice.php';
$invoice->seller->electronicAddress = new Identifier('DE123456789', '9930');
$invoice->buyer->electronicAddress = new Identifier('04011000-12345-34', '0204');

save('peppol-ubl.xml', $invoice->toXml(Profile::Peppol));
