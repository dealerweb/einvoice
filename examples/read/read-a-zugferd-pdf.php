<?php

declare(strict_types=1);

// Reading a ZUGFeRD / Factur-X PDF: the invoice is the XML file embedded in it (factur-x.xml, zugferd-invoice.xml or
// xrechnung.xml), the pages show it to a person. Reading gives the same model as from the XML alone - here with the
// fields only EXTENDED has -, and xmlFromPdf() gives that XML itself, as it is embedded.
//
//     php examples/read/read-a-zugferd-pdf.php                reads input/zugferd-extended.pdf
//     php examples/read/read-a-zugferd-pdf.php invoice.pdf    reads your PDF

use Dealerweb\EInvoice\Invoice;

require __DIR__ . '/../bootstrap.php';

$pdf = contentOf(fileArgument('zugferd-extended.pdf'));
$invoice = Invoice::fromPdf($pdf);   // Invoice::fromFile() and fromXml() see a PDF by themselves

echo "Invoice {$invoice->number} ({$invoice->documentName}) from {$invoice->seller->name}\n";
echo "Delivery note: {$invoice->delivery->deliveryNote->number}\n";
$discount = $invoice->earlyPaymentDiscount;
echo "Cash discount: {$discount->percentage} % of {$discount->baseAmount} within {$discount->period} days\n";
echo 'Lines: ' . count($invoice->lines) . ", gross {$invoice->totals->grossAmount} {$invoice->currency}\n";

// The original of the invoice is its XML: the file embedded in the PDF, byte for byte as the sender wrote it - to
// store next to the PDF.
$original = Invoice::xmlFromPdf($pdf);
$same = Invoice::fromXml($original)->number === $invoice->number;
echo 'Embedded XML: ' . strlen($original) . ' bytes, ' . ($same ? 'the same invoice' : 'another invoice') . "\n";
