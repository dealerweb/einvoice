<?php

declare(strict_types=1);

// ZUGFeRD / Factur-X without a PDF of your own: the package renders the invoice - in German, English or French - and
// embeds the XML. The layout is a reading aid; where your system prints its invoices, embed the XML into that PDF
// instead (zugferd-en16931.php).
//
//     php examples/create/zugferd-en16931-rendered.php   writes output/zugferd-en16931-rendered.pdf

use Dealerweb\EInvoice\Invoice;
use Dealerweb\EInvoice\Profile;

require __DIR__ . '/../bootstrap.php';

/** @var Invoice $invoice */
$invoice = require __DIR__ . '/../sample-invoice.php';

save('zugferd-en16931-rendered.pdf', $invoice->toPdf(Profile::En16931, 'de'));
