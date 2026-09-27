<?php

declare(strict_types=1);

// Peppol BIS Billing 3.0 in CII - the same as peppol-ubl.php in the other syntax of EN 16931. Most participants of
// the Peppol network expect UBL; CII only where the recipient asks for it.
//
//     php examples/create/peppol-cii.php               writes output/peppol-cii.xml

use Dealerweb\EInvoice\Invoice;
use Dealerweb\EInvoice\Model\Identifier;
use Dealerweb\EInvoice\Profile;
use Dealerweb\EInvoice\Syntax;

require __DIR__ . '/../bootstrap.php';

/** @var Invoice $invoice */
$invoice = require __DIR__ . '/../sample-invoice.php';
$invoice->seller->electronicAddress = new Identifier('DE123456789', '9930');
$invoice->buyer->electronicAddress = new Identifier('04011000-12345-34', '0204');

save('peppol-cii.xml', $invoice->toXml(Profile::Peppol, Syntax::Cii));
