<?php

declare(strict_types=1);

// EN 16931 in UBL without a national specification (Profile::Core) - for recipients that take the plain European
// standard, e.g. in other EU countries. In CII the same is ZUGFeRD / Factur-X EN 16931 (Profile::Core in CII is
// Profile::En16931, and the other way round in UBL).
//
//     php examples/create/en16931-ubl.php              writes output/en16931-ubl.xml

use Dealerweb\EInvoice\Invoice;
use Dealerweb\EInvoice\Profile;

require __DIR__ . '/../bootstrap.php';

/** @var Invoice $invoice */
$invoice = require __DIR__ . '/../sample-invoice.php';

save('en16931-ubl.xml', $invoice->toXml(Profile::Core));
