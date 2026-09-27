<?php

declare(strict_types=1);

// Every example file in examples/input read, one line each: reading takes every format the same way, and it does not
// validate - invalid-xrechnung.xml is read as well (check/check-an-invalid-invoice.php shows what is wrong with it).
// The key facts come from summary(), e.g. to capture an invoice as a document.
//
//     php examples/read/read-every-input-file.php

use Dealerweb\EInvoice\Exception\UnsupportedDocument;
use Dealerweb\EInvoice\Invoice;

require __DIR__ . '/../bootstrap.php';

foreach (glob(__DIR__ . '/../input/*') ?: [] as $file) {
    if (! preg_match('/\.(xml|pdf)$/', $file)) {
        continue;
    }
    try {
        $invoice = Invoice::fromFile($file);
    } catch (UnsupportedDocument $e) {
        printf("%-31s %s\n", basename($file), $e->getMessage());   // invoice.pdf: a PDF without an invoice in it
        continue;
    }
    $facts = $invoice->summary();
    printf(
        "%-31s %-11s %-12s %-10s %-22s %8s %s, %d lines\n",
        basename($file),
        $facts->isCreditNote ? 'credit note' : 'invoice',
        $facts->number,
        $facts->issueDate,
        $facts->seller->name ?? '-',
        $facts->grossAmount,
        $facts->currency,
        count($invoice->lines),
    );
}
