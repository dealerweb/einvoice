<?php

declare(strict_types=1);

// Showing an invoice as HTML page - one self-contained file (no scripts, no external resources), in German, English
// or French, laid out like a German business invoice. A read invoice is shown as delivered, with everything its
// document holds - also what the model has no place for. Only the XML is the invoice: a rendering is a reading aid.
//
//     php examples/show/show-as-html.php                  shows input/zugferd-extended.pdf
//     php examples/show/show-as-html.php invoice.xml      shows your file

use Dealerweb\EInvoice\Invoice;
use Dealerweb\EInvoice\Render\HtmlRenderer;

require __DIR__ . '/../bootstrap.php';

$invoice = Invoice::fromFile(fileArgument('zugferd-extended.pdf'));

foreach (['de', 'en', 'fr'] as $language) {
    save("shown-$language.html", (new HtmlRenderer($language))->render($invoice));
}
