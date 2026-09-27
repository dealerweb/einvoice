<?php

declare(strict_types=1);

// Checking against a profile given instead of the one the document names - e.g. whether an invoice also meets plain
// EN 16931, or which fields of an EXTENDED invoice EN 16931 has no place for.
//
//     php examples/check/check-against-a-profile.php

use Dealerweb\EInvoice\Profile;
use Dealerweb\EInvoice\Validation\Validator;

require __DIR__ . '/../bootstrap.php';

$validator = new Validator();

// An XRechnung checked by the rules of EN 16931 alone.
$report = $validator->validateFile(input('xrechnung-cii.xml'), Profile::Core);
echo 'xrechnung-cii.xml as plain EN 16931: ' . ($report->isValid() ? 'valid' : 'invalid') . " ({$report->scenario()})\n";

// An EXTENDED invoice checked as ZUGFeRD / Factur-X EN 16931: its EXTENDED fields do not fit the schema.
$report = $validator->validateFile(input('zugferd-extended.pdf'), Profile::En16931);
echo 'zugferd-extended.pdf as EN 16931: ' . ($report->isValid() ? 'valid' : 'invalid') . " ({$report->scenario()})\n";
foreach ($report->errors() as $message) {
    echo "  {$message->code}: {$message->text}\n";
}
