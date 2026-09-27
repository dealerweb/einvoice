<?php

declare(strict_types=1);

// Writing by the ids of the fields - the business terms and groups of EN 16931 and the extensions of Factur-X (BT-1,
// BG-4, BT-X-202 ...) as the Factur-X field list names them - instead of the names of the model, with
// Generation\Generator. Nothing is calculated here: the totals and the VAT breakdown are given. The generator can also
// leave out the validator, and write the PDF of XML written before.
//
//     php examples/create/by-field-ids.php             writes output/by-field-ids.xml, -unchecked.xml and .pdf

use Dealerweb\EInvoice\Generation\Generator;
use Dealerweb\EInvoice\Invoice;
use Dealerweb\EInvoice\Profile;

require __DIR__ . '/../bootstrap.php';

$xml = (new Generator(Profile::XRechnung))->xml([
    'BT-1' => 'R-2026-0003',
    'BT-2' => '2026-09-27',                    // dates as YYYY-MM-DD, YYYYMMDD or DateTimeInterface
    'BT-3' => '380',
    'BT-5' => 'EUR',
    'BT-10' => '04011000-12345-34',
    'BT-20' => 'Payable within 14 days.',
    'BT-23' => 'urn:fdc:peppol.eu:2017:poacc:billing:01:1.0',
    'BG-4' => [                                // seller
        'BT-27' => 'Muster GmbH',
        'BT-31' => 'DE123456789',              // its scheme VA is set by the generator
        'BT-34' => ['value' => 'invoice@muster.example', 'BT-34-1' => 'EM'],   // a value with an attribute
        'BG-5' => ['BT-35' => 'Hauptstrasse 1', 'BT-37' => 'Plauen', 'BT-38' => '08523', 'BT-40' => 'DE'],
        'BG-6' => ['BT-41' => 'Max Muster', 'BT-42' => '+49 3741 123456', 'BT-43' => 'max@muster.example'],
    ],
    'BG-7' => [                                // buyer
        'BT-44' => 'Stadt Beispielhausen',
        'BT-49' => ['value' => 'inbox@beispielhausen.example', 'BT-49-1' => 'EM'],
        'BG-8' => ['BT-50' => 'Rathausplatz 1', 'BT-52' => 'Beispielhausen', 'BT-53' => '12345', 'BT-55' => 'DE'],
    ],
    'BG-16' => ['BT-81' => '58', 'BG-17' => ['BT-84' => 'DE02120300000000202051']],
    'BG-22' => ['BT-106' => '100.00', 'BT-109' => '100.00', 'BT-110' => '19.00', 'BT-112' => '119.00', 'BT-115' => '119.00'],
    'BG-23' => [['BT-116' => '100.00', 'BT-117' => '19.00', 'BT-118' => 'S', 'BT-119' => '19']],
    'BG-25' => [                               // one array per line
        ['BT-126' => '1', 'BT-129' => '2', 'BT-130' => 'H87', 'BT-131' => '100.00',
            'BG-29' => ['BT-146' => '50.00'], 'BG-30' => ['BT-151' => 'S', 'BT-152' => '19'], 'BG-31' => ['BT-153' => 'Screw set']],
    ],
]);
save('by-field-ids.xml', $xml);

// The model can go this way, too - here without the validator, which runs otherwise.
/** @var Invoice $invoice */
$invoice = require __DIR__ . '/../sample-invoice.php';
save('by-field-ids-unchecked.xml', (new Generator(Profile::Extended, validate: false))->xml($invoice));

// The PDF of XML written before: without a PDF given, the rendered invoice with the XML in it.
save('by-field-ids.pdf', (new Generator(Profile::XRechnung))->pdf($xml, null, 'de'));
