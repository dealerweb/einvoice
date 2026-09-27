<?php

declare(strict_types=1);

// XRechnung in UBL - the same content as in CII (xrechnung-cii.php), in the other syntax of EN 16931. Which one to
// send is the recipient's choice; public portals take both.
//
//     php examples/create/xrechnung-ubl.php            writes output/xrechnung-ubl.xml

use Dealerweb\EInvoice\Invoice;
use Dealerweb\EInvoice\Profile;
use Dealerweb\EInvoice\Syntax;

require __DIR__ . '/../bootstrap.php';

/** @var Invoice $invoice */
$invoice = require __DIR__ . '/../sample-invoice.php';

save('xrechnung-ubl.xml', $invoice->toXml(Profile::XRechnung, Syntax::UblInvoice));
