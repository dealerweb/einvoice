<?php

declare(strict_types=1);

// Supporting documents (BG-24) travel in the XML of the invoice: files of the types EN 16931 allows (PDF, PNG, JPEG,
// CSV, XLSX, ODS) with their content, or a document named only by its address (never opened by the package).
//
//     php examples/attachments/attach-files.php        writes output/xrechnung-with-attachments.xml

use Dealerweb\EInvoice\Invoice;
use Dealerweb\EInvoice\Model\Attachment;
use Dealerweb\EInvoice\Profile;

require __DIR__ . '/../bootstrap.php';

/** @var Invoice $invoice */
$invoice = require __DIR__ . '/../sample-invoice.php';

// fromFile() takes name, type and content from the file - and the name as the reference EN 16931 asks for.
$invoice->attachments[] = Attachment::fromFile(input('time-sheet.csv'), 'Time sheet of the installation');
$invoice->attachments[] = new Attachment(
    id: 'Offer A-17',
    description: 'Our offer',
    url: 'https://muster.example/offers/A-17',
);

save('xrechnung-with-attachments.xml', $invoice->toXml(Profile::XRechnung));
