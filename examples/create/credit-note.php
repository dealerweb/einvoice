<?php

declare(strict_types=1);

// A credit note is the same model as an invoice, with a credit note type code (381) and the invoice it refers to. In
// UBL it becomes a UBL CreditNote, in CII the type code tells. A corrected invoice has the type code 384
// (InvoiceTypeCode::CORRECTED_INVOICE), a self-billed invoice - a "Gutschrift" in German tax law, the buyer bills
// itself - 389 (InvoiceTypeCode::SELF_BILLED_INVOICE).
//
//     php examples/create/credit-note.php              writes output/credit-note-ubl.xml and credit-note-cii.xml

use Dealerweb\EInvoice\Invoice;
use Dealerweb\EInvoice\Model\DocumentReference;
use Dealerweb\EInvoice\Model\InvoiceTypeCode;
use Dealerweb\EInvoice\Profile;

require __DIR__ . '/../bootstrap.php';

/** @var Invoice $invoice */
$invoice = require __DIR__ . '/../sample-invoice.php';

$invoice->number = 'G-2026-0001';
$invoice->typeCode = InvoiceTypeCode::CREDIT_NOTE;
$invoice->precedingInvoices[] = new DocumentReference(number: 'R-2026-0001', date: '2026-09-26');

save('credit-note-ubl.xml', $invoice->toXml(Profile::Core));        // a UBL CreditNote after EN 16931
save('credit-note-cii.xml', $invoice->toXml(Profile::XRechnung));   // an XRechnung in CII
