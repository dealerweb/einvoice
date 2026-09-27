<?php

declare(strict_types=1);

// XRechnung as PDF: the XML embedded in your PDF of the invoice under the ZUGFeRD / Factur-X profile XRECHNUNG (as
// xrechnung.xml) - a person reads the pages, a program the XML. Public buyers usually want the XML alone
// (xrechnung-cii.php); for a recipient who takes a PDF, this one carries both.
//
//     php examples/create/xrechnung-pdf.php                writes output/xrechnung.pdf from input/invoice.pdf
//     php examples/create/xrechnung-pdf.php my-invoice.pdf with your own PDF
//
// Your PDF has to meet PDF/A-3 in its content (fonts embedded, no JavaScript); the rest PDF/A asks of the file is done
// here. Encrypted and signed PDFs are refused - writing into a signed PDF would break its signature.

use Dealerweb\EInvoice\Invoice;
use Dealerweb\EInvoice\Profile;

require __DIR__ . '/../bootstrap.php';

/** @var Invoice $invoice */
$invoice = require __DIR__ . '/../sample-invoice.php';
$yourPdf = contentOf(fileArgument('invoice.pdf'));   // the invoice as your system prints it

save('xrechnung.pdf', $invoice->toPdf(Profile::XRechnung, pdf: $yourPdf));
