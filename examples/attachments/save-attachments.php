<?php

declare(strict_types=1);

// The attachments of an invoice read and saved. saveTo() writes under a name made safe for the file system (no
// folders, no control characters, the extension of the type) and never overwrites a file - it counts up:
// "time-sheet (2).csv".
//
//     php examples/attachments/save-attachments.php               saves those of input/xrechnung-with-attachments.xml
//     php examples/attachments/save-attachments.php invoice.xml   saves those of your file

use Dealerweb\EInvoice\Invoice;

require __DIR__ . '/../bootstrap.php';

$invoice = Invoice::fromFile(fileArgument('xrechnung-with-attachments.xml'));
$folder = output('attachments');
if (! is_dir($folder) && ! mkdir($folder) && ! is_dir($folder)) {
    throw new RuntimeException("Cannot create the folder $folder.");
}

foreach ($invoice->attachments as $attachment) {
    if (! $attachment->hasContent()) {
        echo "{$attachment->id}: {$attachment->description}, at {$attachment->url}\n";   // only named by its address
        continue;
    }
    $saved = $attachment->saveTo($folder);
    echo "{$attachment->filename} ({$attachment->mimeCode}, {$attachment->size()} bytes): {$attachment->description}\n";
    echo '  saved as output/attachments/' . basename($saved) . "\n";
}
