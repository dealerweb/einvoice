<?php

declare(strict_types=1);

// ZUGFeRD / Factur-X BASIC - the smallest profile with lines: quantity, price and VAT of each line, the parties
// without contact persons, no supporting documents. Written is only what the profile holds: a value it has no place
// for stops the call with its path and the profiles that have it ("seller.contact.name (BT-41) is not a part of the
// profile BASIC. It is in EN16931, EXTENDED, XRECHNUNG and PEPPOL.").
//
//     php examples/create/zugferd-basic.php                writes output/zugferd-basic.xml and .pdf
//     php examples/create/zugferd-basic.php my-invoice.pdf  with your own PDF

use Dealerweb\EInvoice\Invoice;
use Dealerweb\EInvoice\Model\Contact;
use Dealerweb\EInvoice\Profile;

require __DIR__ . '/../bootstrap.php';

/** @var Invoice $invoice */
$invoice = require __DIR__ . '/../sample-invoice.php';
$invoice->seller->contact = new Contact();   // BASIC has no contact person

save('zugferd-basic.xml', $invoice->toXml(Profile::Basic));
save('zugferd-basic.pdf', $invoice->toPdf(Profile::Basic, pdf: contentOf(fileArgument('invoice.pdf'))));
