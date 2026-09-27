<?php

declare(strict_types=1);

// Converting: an invoice read in one format and written in another - the model is the same. What the target asks
// beyond the source has to be in the invoice or be added (XRechnung: the Leitweg-ID, a contact of the seller, ...);
// a value the target has no place for stops the call with its path.
//
//     php examples/convert/zugferd-pdf-to-xrechnung.php              converts input/zugferd-en16931.pdf
//     php examples/convert/zugferd-pdf-to-xrechnung.php invoice.pdf  converts your ZUGFeRD / Factur-X PDF

use Dealerweb\EInvoice\Invoice;
use Dealerweb\EInvoice\Profile;
use Dealerweb\EInvoice\Syntax;

require __DIR__ . '/../bootstrap.php';

$invoice = Invoice::fromFile(fileArgument('zugferd-en16931.pdf'));

save('converted-xrechnung-cii.xml', $invoice->toXml(Profile::XRechnung));
save('converted-xrechnung-ubl.xml', $invoice->toXml(Profile::XRechnung, Syntax::UblInvoice));
