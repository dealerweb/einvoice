<?php

declare(strict_types=1);

// Reading an invoice - XML (XRechnung, ZUGFeRD / Factur-X, Peppol BIS, EN 16931; UBL or CII) or a ZUGFeRD / Factur-X
// PDF, the file tells which - into the same model an invoice is built with: every field with its readable name.
//
//     php examples/read/read-an-invoice.php                reads input/xrechnung-cii.xml
//     php examples/read/read-an-invoice.php invoice.xml    reads your file

use Dealerweb\EInvoice\CodeList;
use Dealerweb\EInvoice\CodeLists;
use Dealerweb\EInvoice\Invoice;

require __DIR__ . '/../bootstrap.php';

$invoice = Invoice::fromFile(fileArgument('xrechnung-cii.xml'));   // from a string: fromXml($xml), fromPdf($pdf)
$facts = $invoice->summary();   // the key facts, dates and amounts as text as the document gives them

$kind = $invoice->isCreditNote() ? 'Credit note' : 'Invoice';
echo "$kind {$facts->number} of {$facts->issueDate}, due {$facts->dueDate}\n";
echo "Specification: {$invoice->specification}\n";

$seller = $invoice->seller;
echo "Seller: {$seller->name}, {$seller->address->postcode} {$seller->address->city}, VAT ID {$seller->vatId}\n";
$buyer = $invoice->buyer;
echo "Buyer:  {$buyer->name}, {$buyer->address->postcode} {$buyer->address->city}, reference {$invoice->buyerReference}\n";

foreach ($invoice->lines as $line) {
    echo "  {$line->id}  {$line->name}: {$line->quantity} {$line->unit} x {$line->netPrice} = {$line->netAmount}";
    echo " (VAT {$line->vatCategory} {$line->vatRate} %)\n";
}
foreach ($invoice->vatBreakdown as $vat) {
    echo "VAT {$vat->category} {$vat->rate} % of {$vat->taxableAmount}: {$vat->taxAmount}\n";
}
$totals = $invoice->totals;
echo "Total {$totals->netAmount} + VAT {$totals->vatAmount} = {$totals->grossAmount} {$invoice->currency}";
echo ", due {$totals->dueAmount}\n";

foreach ($invoice->paymentMeans as $means) {
    // Codes are kept as the document gives them; CodeLists names them in German, English or French.
    $name = CodeLists::name(CodeList::PaymentMeans, (string) $means->typeCode, 'en');
    echo "Payment: $name to {$means->accountId}\n";
}
echo "Terms: {$invoice->paymentTerms}\n";

// What the document holds beyond the model - an element outside its syntax, a second value of a field the model has
// once - is named here instead of being dropped.
foreach ($invoice->unread() as $unread) {
    echo "Not read: $unread\n";
}
