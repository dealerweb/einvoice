<?php

declare(strict_types=1);

// ZUGFeRD / Factur-X EN 16931 (called COMFORT before) - the XML holds the complete core invoice of EN 16931: the usual
// profile between companies. The usual way: the XML embedded into the PDF your system prints of the invoice - the
// pages stay as they are. Written here also as XML alone.
//
//     php examples/create/zugferd-en16931.php                writes output/zugferd-en16931.xml and .pdf
//     php examples/create/zugferd-en16931.php my-invoice.pdf  with your own PDF
//
// Your PDF has to meet PDF/A-3 in its content (fonts embedded, no JavaScript); the rest PDF/A asks of the file is done
// here. Encrypted and signed PDFs are refused - writing into a signed PDF would break its signature.

use Dealerweb\EInvoice\Invoice;
use Dealerweb\EInvoice\Profile;

require __DIR__ . '/../bootstrap.php';

/** @var Invoice $invoice */
$invoice = require __DIR__ . '/../sample-invoice.php';
$yourPdf = contentOf(fileArgument('invoice.pdf'));   // the invoice as your system prints it

save('zugferd-en16931.xml', $invoice->toXml(Profile::En16931));
save('zugferd-en16931.pdf', $invoice->toPdf(Profile::En16931, pdf: $yourPdf));
