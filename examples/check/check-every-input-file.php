<?php

declare(strict_types=1);

// Every example file in examples/input checked, one line each: the verdict and the rules it was checked with.
// invalid-xrechnung.xml is invalid on purpose; invoice.pdf is a PDF without an invoice (NO_INVOICE).
//
//     php examples/check/check-every-input-file.php

use Dealerweb\EInvoice\Validation\Message;
use Dealerweb\EInvoice\Validation\Validator;

require __DIR__ . '/../bootstrap.php';

$validator = new Validator();
foreach (glob(__DIR__ . '/../input/*') ?: [] as $file) {
    if (! preg_match('/\.(xml|pdf)$/', $file)) {
        continue;
    }
    $report = $validator->validateFile($file);
    $codes = array_map(static fn(Message $message): string => $message->code, $report->errors());
    printf(
        "%-31s %-8s %s%s\n",
        basename($file),
        $report->isValid() ? 'valid' : 'invalid',
        $report->scenario() ?? 'no rules apply',
        $codes === [] ? '' : ' - ' . implode(', ', $codes),
    );
}
