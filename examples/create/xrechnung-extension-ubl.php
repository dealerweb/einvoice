<?php

declare(strict_types=1);

// XRechnung with its extension, in UBL - for what EN 16931 cannot hold: sub lines (a line made of lines, BG-DEX-01;
// here a workstation of a monitor and a keyboard) and third party payments. The specification identifier names the
// extension, the recipient has to accept it. The sub lines of the extension exist in UBL only.
//
//     php examples/create/xrechnung-extension-ubl.php  writes output/xrechnung-extension-ubl.xml

use Dealerweb\EInvoice\Invoice;
use Dealerweb\EInvoice\Model\Line;
use Dealerweb\EInvoice\Model\UnitCode;
use Dealerweb\EInvoice\Model\VatCategoryCode;
use Dealerweb\EInvoice\Profile;
use Dealerweb\EInvoice\Syntax;

require __DIR__ . '/../bootstrap.php';

/** @var Invoice $invoice */
$invoice = require __DIR__ . '/../sample-invoice.php';

// A line of its own and, as its sub lines, what it is made of - each built like a line.
$workstation = new Line(id: '3', name: 'Workstation', quantity: 1, unit: UnitCode::PIECE, netPrice: '150.00');
$workstation->subLines[] = new Line(id: '3.1', name: 'Monitor', quantity: 1, unit: UnitCode::PIECE, netPrice: '100.00');
$workstation->subLines[] = new Line(id: '3.2', name: 'Keyboard', quantity: 1, unit: UnitCode::PIECE, netPrice: '50.00');
foreach ([$workstation, ...$workstation->subLines] as $line) {
    $line->vatCategory = VatCategoryCode::STANDARD_RATE;
    $line->vatRate = 19;
}
$invoice->lines[] = $workstation;

// With sub lines the specification identifier of the extension is set by itself.
save('xrechnung-extension-ubl.xml', $invoice->toXml(Profile::XRechnung, Syntax::UblInvoice));
