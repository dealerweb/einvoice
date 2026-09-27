<?php

declare(strict_types=1);

// An XML you already have - from another system, or written before - embedded into the PDF of the invoice: a
// ZUGFeRD / Factur-X PDF with exactly that XML. Generation\Generator::pdf() takes the XML as it is and checks it.
//
//     php examples/convert/xml-into-your-pdf.php      writes output/xml-into-your-pdf.pdf from input/xrechnung-cii.xml
//                                                     and input/invoice.pdf

use Dealerweb\EInvoice\Generation\Generator;
use Dealerweb\EInvoice\Invoice;
use Dealerweb\EInvoice\Profile;

require __DIR__ . '/../bootstrap.php';

$xml = contentOf(input('xrechnung-cii.xml'));
$yourPdf = contentOf(input('invoice.pdf'));

$pdf = save('xml-into-your-pdf.pdf', (new Generator(Profile::XRechnung))->pdf($xml, $yourPdf));

$same = Invoice::fromFile($pdf)->toArray() === Invoice::fromXml($xml)->toArray();
echo 'Read back, the same invoice as the XML: ' . ($same ? 'yes' : 'no') . "\n";
