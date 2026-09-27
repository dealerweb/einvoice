<?php

declare(strict_types=1);

// An invoice from an array - with the names of the model, e.g. from JSON, a form or a database row: the same as built
// object by object (sample-invoice.php). An object with one main value may be given as that value ('purchaseOrder'
// => 'PO-4711' is its number). toArray() and json_encode() give it back with the same names.
//
//     php examples/create/from-an-array.php            writes output/from-an-array.xml and .json

use Dealerweb\EInvoice\Invoice;
use Dealerweb\EInvoice\Profile;

require __DIR__ . '/../bootstrap.php';

$invoice = Invoice::fromArray([
    'number' => 'R-2026-0002',
    'typeCode' => '380',
    'issueDate' => '2026-09-27',
    'currency' => 'EUR',
    'purchaseOrder' => 'PO-4711',
    'paymentTerms' => 'Payable within 14 days without deduction.',
    'delivery' => ['date' => '2026-09-25'],
    'seller' => [
        'name' => 'Muster GmbH',
        'vatId' => 'DE123456789',
        'address' => [
            'line1' => 'Hauptstrasse 1',
            'postcode' => '08523',
            'city' => 'Plauen',
            'country' => 'DE',
        ],
    ],
    'buyer' => [
        'name' => 'Beispiel AG',
        'address' => [
            'line1' => 'Marktplatz 5',
            'postcode' => '95028',
            'city' => 'Hof',
            'country' => 'DE',
        ],
    ],
    'paymentMeans' => [
        ['typeCode' => '58', 'accountId' => 'DE02120300000000202051'],
    ],
    'lines' => [
        [
            'id' => '1',
            'name' => 'Screw set',
            'quantity' => 2,
            'unit' => 'H87',
            'netPrice' => '50.00',
            'vatCategory' => 'S',
            'vatRate' => 19,
        ],
    ],
]);

save('from-an-array.xml', $invoice->toXml(Profile::En16931));

$json = json_encode($invoice, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
save('from-an-array.json', $json);
