<?php

declare(strict_types=1);

// Showing an invoice as PDF (A4, dompdf) - e.g. to read an XRechnung that arrived as XML alone, in German, English or
// French. The PDF is a reading aid without the XML; to send an invoice as PDF, write a ZUGFeRD / Factur-X PDF
// (create/zugferd-en16931.php).
//
//     php examples/show/show-as-pdf.php                   shows input/xrechnung-ubl.xml
//     php examples/show/show-as-pdf.php invoice.xml       shows your file

use Dealerweb\EInvoice\Invoice;
use Dealerweb\EInvoice\Render\PdfRenderer;

require __DIR__ . '/../bootstrap.php';

$invoice = Invoice::fromFile(fileArgument('xrechnung-ubl.xml'));

foreach (['de', 'en', 'fr'] as $language) {
    save("shown-$language.pdf", (new PdfRenderer($language))->render($invoice));
}
