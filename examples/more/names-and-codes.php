<?php

declare(strict_types=1);

// Names of the fields and of the code values in German, English and French - e.g. to label values in a view of your
// own. Fields are named by their id in the Factur-X field list (BT-1, BT-X-202 ...); MODEL.md gives the id of every
// property of the model.
//
//     php examples/more/names-and-codes.php

use Dealerweb\EInvoice\CodeList;
use Dealerweb\EInvoice\CodeLists;
use Dealerweb\EInvoice\Labels;

require __DIR__ . '/../bootstrap.php';

// The name of a field: the chain of the properties of the model that lead to it.
foreach (['de', 'en', 'fr'] as $language) {
    echo "BT-1 ($language): " . Labels::name('BT-1', $language) . "\n";
}
echo 'BT-X-202 (de): ' . Labels::name('BT-X-202', 'de') . "\n";

// The names of code values - payment means, units, countries, VAT categories, document types ...
echo 'Payment means 58 (de): ' . CodeLists::name(CodeList::PaymentMeans, '58', 'de') . "\n";
echo 'Unit HUR (fr): ' . CodeLists::name(CodeList::Unit, 'HUR', 'fr') . "\n";
echo 'Country AT (en): ' . CodeLists::name(CodeList::Country, 'AT', 'en') . "\n";
echo 'Document type 381 (de): ' . CodeLists::name(CodeList::DocumentType, '381', 'de') . "\n";

// The code list of a field, and further columns of a code list.
echo 'Code 58 of BT-81 (en): ' . CodeLists::nameForField('BT-81', '58', 'en') . "\n";
$lists = array_map(static fn(CodeList $list): string => $list->name, CodeLists::forField('BT-151'));
echo 'Code lists of BT-151: ' . implode(', ', $lists) . "\n";
echo 'Document type 381 counts as: ' . CodeLists::property(CodeList::DocumentType, '381', 'interpretation') . "\n";
