<?php

declare(strict_types=1);

// The invoice the examples share, built with the model: every property has a readable name (MODEL.md lists them all),
// every object is there from the start ($invoice->seller->address), every list starts empty. Amounts are strings or
// numbers, dates YYYY-MM-DD; classes of constants name the common codes. What follows from the lines - their net
// amounts, the VAT breakdown, the totals - is calculated when the invoice is written.

use Dealerweb\EInvoice\Invoice;
use Dealerweb\EInvoice\Model\Identifier;
use Dealerweb\EInvoice\Model\InvoiceTypeCode;
use Dealerweb\EInvoice\Model\Line;
use Dealerweb\EInvoice\Model\PaymentMeans;
use Dealerweb\EInvoice\Model\PaymentMeansCode;
use Dealerweb\EInvoice\Model\UnitCode;
use Dealerweb\EInvoice\Model\VatCategoryCode;

$invoice = new Invoice();
$invoice->number = 'R-2026-0001';
$invoice->typeCode = InvoiceTypeCode::COMMERCIAL_INVOICE;           // 380
$invoice->issueDate = '2026-09-26';
$invoice->dueDate = '2026-10-10';
$invoice->currency = 'EUR';
$invoice->buyerReference = '04011000-12345-34';                     // the Leitweg-ID of a German public buyer
$invoice->businessProcess = 'urn:fdc:peppol.eu:2017:poacc:billing:01:1.0';
$invoice->paymentTerms = 'Payable within 14 days without deduction.';
$invoice->delivery->date = '2026-09-25';                           // the date of the supply, which German invoices name

$invoice->seller->name = 'Muster GmbH';
$invoice->seller->vatId = 'DE123456789';
$invoice->seller->electronicAddress = new Identifier('invoice@muster.example', 'EM');   // EM: an e-mail address
$invoice->seller->address->line1 = 'Hauptstrasse 1';
$invoice->seller->address->postcode = '08523';
$invoice->seller->address->city = 'Plauen';
$invoice->seller->address->country = 'DE';
$invoice->seller->contact->name = 'Max Muster';
$invoice->seller->contact->phone = '+49 3741 123456';
$invoice->seller->contact->email = 'max@muster.example';

$invoice->buyer->name = 'Stadt Beispielhausen';
$invoice->buyer->electronicAddress = new Identifier('inbox@beispielhausen.example', 'EM');
$invoice->buyer->address->line1 = 'Rathausplatz 1';
$invoice->buyer->address->postcode = '12345';
$invoice->buyer->address->city = 'Beispielhausen';
$invoice->buyer->address->country = 'DE';

$invoice->paymentMeans[] = new PaymentMeans(
    typeCode: PaymentMeansCode::SEPA_CREDIT_TRANSFER,
    accountId: 'DE02120300000000202051',
);

$invoice->lines[] = new Line(
    id: '1',
    name: 'Screw set',
    quantity: 2,
    unit: UnitCode::PIECE,
    netPrice: '50.00',
    vatCategory: VatCategoryCode::STANDARD_RATE,
    vatRate: 19
);
$invoice->lines[] = new Line(
    id: '2',
    name: 'Installation',
    quantity: '1.5',
    unit: UnitCode::HOUR,
    netPrice: '80.00',
    vatCategory: VatCategoryCode::STANDARD_RATE,
    vatRate: 19
);

return $invoice;
