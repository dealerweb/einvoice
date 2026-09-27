<?php

declare(strict_types=1);

// An invalid invoice and what the report says about it: the rule (its identifier and text) and the place in the
// document (an XPath). input/invalid-xrechnung.xml is an XRechnung without the name of the seller.
//
//     php examples/check/check-an-invalid-invoice.php

use Dealerweb\EInvoice\Validation\Validator;

require __DIR__ . '/../bootstrap.php';

$report = (new Validator())->validateFile(input('invalid-xrechnung.xml'));

echo ($report->isValid() ? 'Valid' : 'Invalid') . ', checked by ' . $report->scenario() . "\n";
foreach ($report->errors() as $message) {
    echo "{$message->code}: {$message->text}\n  at {$message->location}\n";
}

// Everything as nested arrays, e.g. to store or send the report as JSON.
$json = json_encode($report->toArray(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
save('invalid-xrechnung-report.json', $json);
