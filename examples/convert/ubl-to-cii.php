<?php

declare(strict_types=1);

// UBL and CII are the two syntaxes of EN 16931 - the same invoice in either. Here an XRechnung in UBL written in CII,
// and a ZUGFeRD / Factur-X XML (CII) written as UBL after EN 16931.
//
//     php examples/convert/ubl-to-cii.php               writes output/converted-cii.xml and converted-ubl.xml

use Dealerweb\EInvoice\Invoice;
use Dealerweb\EInvoice\Profile;

require __DIR__ . '/../bootstrap.php';

save('converted-cii.xml', Invoice::fromFile(input('xrechnung-ubl.xml'))->toXml(Profile::XRechnung));
save('converted-ubl.xml', Invoice::fromFile(input('zugferd-en16931.xml'))->toXml(Profile::Core));
