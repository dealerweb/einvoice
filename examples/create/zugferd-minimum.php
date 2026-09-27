<?php

declare(strict_types=1);

// ZUGFeRD / Factur-X MINIMUM. ZUGFeRD (Germany) and Factur-X (France) are one format: a PDF of the invoice with the
// invoice as CII XML embedded. Its profiles differ in how much the XML holds - MINIMUM, BASIC WL, BASIC, EN 16931,
// EXTENDED (and XRECHNUNG). MINIMUM holds the key facts for booking only - number, date, parties, totals, no lines:
// a booking aid, in Germany no e-invoice (FeRD). The PDF is the invoice here, the XML travels with it as data.
//
//     php examples/create/zugferd-minimum.php                writes output/zugferd-minimum.xml and .pdf
//     php examples/create/zugferd-minimum.php my-invoice.pdf  with your own PDF

use Dealerweb\EInvoice\Invoice;
use Dealerweb\EInvoice\Model\InvoiceTypeCode;
use Dealerweb\EInvoice\Profile;

require __DIR__ . '/../bootstrap.php';

// A new invoice with what MINIMUM holds - with no lines to calculate from, the totals are given.
$invoice = new Invoice();
$invoice->number = 'R-2026-0001';
$invoice->typeCode = InvoiceTypeCode::COMMERCIAL_INVOICE;
$invoice->issueDate = '2026-09-26';
$invoice->currency = 'EUR';
$invoice->buyerReference = '04011000-12345-34';
$invoice->seller->name = 'Muster GmbH';
$invoice->seller->vatId = 'DE123456789';
$invoice->seller->address->country = 'DE';
$invoice->buyer->name = 'Stadt Beispielhausen';
$invoice->totals->netAmount = '220.00';
$invoice->totals->vatAmount = '41.80';
$invoice->totals->grossAmount = '261.80';
$invoice->totals->dueAmount = '261.80';

save('zugferd-minimum.xml', $invoice->toXml(Profile::Minimum));
save('zugferd-minimum.pdf', $invoice->toPdf(Profile::Minimum, pdf: contentOf(fileArgument('invoice.pdf'))));
