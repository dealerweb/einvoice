<?php

declare(strict_types=1);

// ZUGFeRD / Factur-X BASIC WL ("without lines") - the XML holds the header, the parties, the payment, the VAT
// breakdown and the totals, no lines: like MINIMUM a booking aid, in Germany no e-invoice (FeRD). The PDF is the
// invoice, the XML travels with it as data.
//
//     php examples/create/zugferd-basic-wl.php                writes output/zugferd-basic-wl.xml and .pdf
//     php examples/create/zugferd-basic-wl.php my-invoice.pdf  with your own PDF

use Dealerweb\EInvoice\Invoice;
use Dealerweb\EInvoice\Model\Contact;
use Dealerweb\EInvoice\Model\VatBreakdown;
use Dealerweb\EInvoice\Model\VatCategoryCode;
use Dealerweb\EInvoice\Profile;

require __DIR__ . '/../bootstrap.php';

/** @var Invoice $invoice */
$invoice = require __DIR__ . '/../sample-invoice.php';

// No lines - and no contact person, which BASIC WL has no place for. Without lines to calculate from, the VAT
// breakdown and the totals are given.
$invoice->lines = [];
$invoice->seller->contact = new Contact();
$invoice->vatBreakdown[] = new VatBreakdown(
    category: VatCategoryCode::STANDARD_RATE,
    rate: 19,
    taxableAmount: '220.00',
    taxAmount: '41.80',
);
$invoice->totals->lineNetAmount = '220.00';
$invoice->totals->netAmount = '220.00';
$invoice->totals->vatAmount = '41.80';
$invoice->totals->grossAmount = '261.80';
$invoice->totals->dueAmount = '261.80';

save('zugferd-basic-wl.xml', $invoice->toXml(Profile::BasicWl));
save('zugferd-basic-wl.pdf', $invoice->toPdf(Profile::BasicWl, pdf: contentOf(fileArgument('invoice.pdf'))));
