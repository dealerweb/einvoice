<?php

declare(strict_types=1);

// Checking an invoice with the official rules - the XML schemas and the Schematron rules of EN 16931, XRechnung and
// ZUGFeRD / Factur-X, the rules of Peppol BIS -, chosen by the specification identifier of the document (BT-24). Of a
// ZUGFeRD / Factur-X PDF the XML in it is checked, not the PDF itself (PDF/A-3 is the job of a PDF validator).
//
//     php examples/check/check-a-file.php                  checks input/zugferd-en16931.pdf
//     php examples/check/check-a-file.php invoice.xml      checks your file

use Dealerweb\EInvoice\Validation\Validator;

require __DIR__ . '/../bootstrap.php';

$report = (new Validator())->validateFile(fileArgument('zugferd-en16931.pdf'));   // or validate($xml) for a string

echo $report->isValid() ? 'Valid' : 'Invalid';
echo $report->isEInvoice() ? ' - an e-invoice' : ' - no e-invoice';
echo "\nProfile: " . ($report->profile()?->label() ?? '-') . "\n";
echo 'Rules: ' . ($report->scenario() ?? 'none apply') . ' (' . implode(', ', $report->ruleSets()) . ")\n";
foreach ($report->messages() as $message) {   // errors(), warnings() for one level
    echo "{$message->severity->name} {$message->code}: {$message->text}\n";
}
foreach ($report->notes() as $note) {
    echo "Note: $note\n";
}
