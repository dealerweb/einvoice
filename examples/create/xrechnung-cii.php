<?php

declare(strict_types=1);

// XRechnung in CII - the German standard for e-invoices (a specification of EN 16931, maintained by KoSIT). Public
// buyers in Germany take invoices in XRechnung; between companies it is one of the formats of the German e-invoice
// obligation. Beyond EN 16931 it asks for the Leitweg-ID of a public buyer (buyerReference; between companies any
// reference of the buyer), a contact of the seller with name, phone and e-mail, the electronic addresses of seller and
// buyer and the payment means - sample-invoice.php has them all. It is sent as it is, an XML file.
//
//     php examples/create/xrechnung-cii.php            writes output/xrechnung-cii.xml

use Dealerweb\EInvoice\Invoice;
use Dealerweb\EInvoice\Profile;

require __DIR__ . '/../bootstrap.php';

/** @var Invoice $invoice */
$invoice = require __DIR__ . '/../sample-invoice.php';

// The totals and the VAT breakdown are calculated, the result is checked with the official rules of XRechnung.
save('xrechnung-cii.xml', $invoice->toXml(Profile::XRechnung));
