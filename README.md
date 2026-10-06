# dealerweb/einvoice

**E-invoicing in pure PHP** - validator, viewer and generator for XRechnung, ZUGFeRD / Factur-X and Peppol BIS, the
e-invoices of EN 16931 (in Germany: E-Rechnung).

Reads electronic invoices according to EN 16931 - XRechnung, ZUGFeRD 2.x / Factur-X, Peppol BIS - as XML in both
syntaxes (UBL Invoice, UBL CreditNote, UN/CEFACT CII) or as ZUGFeRD / Factur-X PDF, and visualizes them as a readable
PDF or HTML page in German, English or French. It validates them with the verdict of the official tools: the XML schemas
and the Schematron rules of EN 16931, XRechnung and ZUGFeRD / Factur-X, applied like the KoSIT validator does, and the
rules of Peppol BIS Billing 3.0 in an own implementation with the verdicts of the official ones. And it writes them:
CII documents of every ZUGFeRD / Factur-X profile, of XRechnung and of Peppol BIS Billing 3.0, UBL invoices and credit
notes of EN 16931, XRechnung and Peppol BIS Billing 3.0, checked by the validator - as XML, CII also as
ZUGFeRD / Factur-X PDF (PDF/A-3 with the XML embedded). One class stands for the invoice, read or built: `Invoice`,
with every field of EN 16931, of the XRechnung extension and of ZUGFeRD / Factur-X EXTENDED under a readable name -
`$invoice->buyer->address->city`.
Pure PHP (8.2+), no Java, no XSLT processor.

Only the XML is the original invoice - anything rendered from it is a reading aid.

## Online demo

[www.dealerweb.de/einvoice](https://www.dealerweb.de/einvoice) checks an XRechnung,
ZUGFeRD / Factur-X or Peppol BIS invoice - XML or ZUGFeRD PDF - with this library and shows it as a readable page.
The file is only checked, not stored.

## Installation

```
composer require dealerweb/einvoice
```

PHP 8.2 or later with ext-dom, ext-libxml, ext-mbstring, ext-xmlreader and ext-zlib. Composer installs dompdf for the PDF output;
its CSS parser needs ext-iconv.

## Usage

One class stands for the invoice: `Dealerweb\EInvoice\Invoice`. It is read from a file or built in PHP, written as XML
or PDF, checked and shown - every field with a readable name, `$invoice->buyer->address->city`.

```php
use Dealerweb\EInvoice\Invoice;
use Dealerweb\EInvoice\Profile;
use Dealerweb\EInvoice\Render\HtmlRenderer;
use Dealerweb\EInvoice\Validation\Validator;

$invoice = Invoice::fromFile('invoice.pdf');      // XML (XRechnung, ZUGFeRD, Factur-X, Peppol) or a ZUGFeRD PDF
$invoice->number;                                 // "R-2026-0001"
$invoice->seller->name;                           // "Muster GmbH"
$invoice->totals->grossAmount;                    // "119.00"

$report = (new Validator())->validateFile('invoice.pdf');   // with the official rules: $report->isValid()
$html = (new HtmlRenderer('de'))->render($invoice);        // readable, in German, English or French

$invoice = new Invoice();                         // built with the same names - see Creating an invoice
$invoice->number = 'R-2026-0001';
// ...
$xml = $invoice->toXml(Profile::XRechnung);       // XRechnung, ZUGFeRD / Factur-X, EN 16931 or Peppol BIS as XML
$yourPdf = file_get_contents('R-2026-0001.pdf');  // the invoice as your system prints it
$pdf = $invoice->toPdf(Profile::En16931, pdf: $yourPdf);   // ZUGFeRD / Factur-X: your PDF with the XML embedded
```

The sections below go through the four tasks: reading an invoice, creating one, checking one and showing one.
Creating an invoice starts with a table of every format and its call - XRechnung, ZUGFeRD / Factur-X from your own PDF
or without one, EN 16931, Peppol BIS. Runnable scripts for every format and every task, with example files to
read, check and convert, are in [`examples/`](examples/).

### Reading an invoice

```php
use Dealerweb\EInvoice\Invoice;

$invoice = Invoice::fromFile('invoice.xml');      // XML or ZUGFeRD / Factur-X PDF - the file tells which
$invoice = Invoice::fromXml($xml);                // from a string (a PDF given here is read as a PDF)
$invoice = Invoice::fromPdf($pdf);                // from the content of a ZUGFeRD / Factur-X PDF
$xml = Invoice::xmlFromPdf($pdf);                 // the XML such a PDF carries, byte for byte as embedded

$invoice->number;                                 // BT-1: "R-2026-0001" - MODEL.md names every property
$invoice->issueDate;                              // "2026-09-26"
$invoice->buyer->address->city;                   // objects are always there - no check for null
foreach ($invoice->lines as $line) {
    $line->name;                                  // "Screw set"
    $line->quantity;                              // "2"
    $line->netAmount;                             // "100.00"
}
$invoice->isCreditNote();                         // a credit note code (381, 396, ...) or a UBL CreditNote
$invoice->isSelfBilled();                         // issued by the buyer (self-billing, "Gutschrift" in German tax law)
$invoice->unread();                               // what the document holds and the model has no place for, by its path
$invoice->toArray();                              // everything given, with the names of the model (also JSON)

foreach ($invoice->attachments as $attachment) {  // BG-24 supporting documents
    $attachment->filename;                        // as delivered, e.g. "Aufmass.pdf"
    $attachment->content();                       // the file - null where it only names an address (never opened)
    $attachment->saveTo('/tmp');                  // writes it under safeFilename() and never overwrites a file
}

$summary = $invoice->summary();                   // the key facts for capturing it as a document
$summary->dueDate;                                // the earliest due date (cash discount, instalments)
$summary->subject;                                // the first line, its description where the name is an article number
$summary->toArray();                              // nested arrays, e.g. for JSON
```

- **Complete:** every field of EN 16931, of the XRechnung extension and of ZUGFeRD / Factur-X EXTENDED has its
  property ([`MODEL.md`](MODEL.md)). What a document holds beyond them - an element outside the syntax, a second value
  of a field the model has once, an indicator that is none (`yes` for true or false) - is named by `unread()` instead
  of being dropped; what carries no information is left out (the currency of an amount that is the invoice currency,
  the provenance of a code). Every example of FeRD and KoSIT, CII or UBL, is read completely and written back with
  the same content (two CII examples whose issue date is none aside).
- **As delivered:** amounts, quantities and percentages are the decimal text of the document, never float; a date is
  `YYYY-MM-DD` (with time and zone where EXTENDED has one); yes/no is a bool; a file is its base64 text
  (`$attachment->base64`, `content()` decodes it). A value is the same in both syntaxes: the VAT point date code is
  the one of EN 16931 (UNTDID 2005: 3, 35, 432 - CII writes and reads its own list, 5, 29, 72), the subject code of a
  note a property of its own (UBL writes "#AAI#text").
- **From a PDF:** a ZUGFeRD / Factur-X PDF carries the invoice as an embedded XML file - `factur-x.xml`,
  `zugferd-invoice.xml` or `xrechnung.xml`. That file is the invoice and is read; the pages show it to a person.
  `Invoice::xmlFromPdf()` gives the file itself, byte for byte as the sender embedded it - the original to store.
- **Reading does not validate:** an invoice is read as far as its elements can be mapped; missing mandatory fields do
  not stop the reading - check it with `Validator` where that matters.

### Creating an invoice

The invoice is built once, with the names of the model (below), and written in the format its recipient needs - each
format has a script of its own in [`examples/create/`](examples/create/):

| Format | Call |
|---|---|
| XRechnung - the format of German public buyers (their Leitweg-ID in `buyerReference`) | `$invoice->toXml(Profile::XRechnung)` |
| XRechnung in UBL | `$invoice->toXml(Profile::XRechnung, Syntax::UblInvoice)` |
| ZUGFeRD / Factur-X PDF from your own PDF - the invoice as your system prints it, with the XML embedded | `$invoice->toPdf(Profile::En16931, pdf: $yourPdf)` |
| ZUGFeRD / Factur-X PDF without a PDF of your own - the package renders the invoice (`de`, `en` or `fr`) | `$invoice->toPdf(Profile::En16931, 'de')` |
| ZUGFeRD / Factur-X as XML alone | `$invoice->toXml(Profile::En16931)` |
| ZUGFeRD / Factur-X EXTENDED - more fields (profile X in [`MODEL.md`](MODEL.md)) | `Profile::Extended` in the three calls above |
| ZUGFeRD / Factur-X BASIC - fewer fields (profile B in [`MODEL.md`](MODEL.md)) | `Profile::Basic` in the three calls above |
| XRechnung in a PDF (the ZUGFeRD / Factur-X profile XRECHNUNG) | `$invoice->toPdf(Profile::XRechnung, pdf: $yourPdf)` |
| EN 16931 without a national specification, in UBL (in CII it is ZUGFeRD / Factur-X EN 16931) | `$invoice->toXml(Profile::Core)` |
| Peppol BIS Billing 3.0 in UBL - with electronic addresses of Peppol (see the example) | `$invoice->toXml(Profile::Peppol)` |
| Peppol BIS Billing 3.0 in CII | `$invoice->toXml(Profile::Peppol, Syntax::Cii)` |

Each call writes only what its profile holds: nothing is left out silently, a value the profile has no place for stops
it with the path of the value and the profiles that have it
(`seller.contact.name (BT-41) is not a part of the profile BASIC. It is in EN16931, EXTENDED, XRECHNUNG and PEPPOL.`).
And each call checks the invoice by the official rules of its profile and throws `InvalidInvoice` instead of returning
an invalid document (see Errors). A credit note is the same invoice with a credit note type code
(`InvoiceTypeCode::CREDIT_NOTE`, 381), written in UBL as UBL CreditNote. MINIMUM and BASIC WL (`Profile::Minimum`,
`Profile::BasicWl`) hold no invoice lines: booking aids, which in Germany count as no e-invoice since 2025 (FeRD).

```php
use Dealerweb\EInvoice\Invoice;
use Dealerweb\EInvoice\Model\Attachment;
use Dealerweb\EInvoice\Model\Identifier;
use Dealerweb\EInvoice\Model\Line;
use Dealerweb\EInvoice\Model\PaymentMeans;
use Dealerweb\EInvoice\Model\PaymentMeansCode;
use Dealerweb\EInvoice\Model\UnitCode;
use Dealerweb\EInvoice\Model\VatCategoryCode;
use Dealerweb\EInvoice\Profile;
use Dealerweb\EInvoice\Syntax;

$invoice = new Invoice();
$invoice->number = 'R-2026-0001';
$invoice->typeCode = '380';                        // or InvoiceTypeCode::COMMERCIAL_INVOICE
$invoice->issueDate = '2026-09-26';                // or a DateTimeInterface
$invoice->currency = 'EUR';
$invoice->businessProcess = 'urn:fdc:peppol.eu:2017:poacc:billing:01:1.0';
$invoice->buyerReference = '04011000-12345-34';    // the Leitweg-ID of a German public buyer
$invoice->paymentTerms = 'Payable within 14 days.';
$invoice->seller->name = 'Muster GmbH';
$invoice->seller->vatId = 'DE123456789';
$invoice->seller->electronicAddress = new Identifier('invoice@muster.example', 'EM');
$invoice->seller->address->line1 = 'Hauptstrasse 1';
$invoice->seller->address->postcode = '08523';
$invoice->seller->address->city = 'Plauen';
$invoice->seller->address->country = 'DE';
$invoice->seller->contact->name = 'Max Muster';
$invoice->seller->contact->phone = '+49 3741 123456';
$invoice->seller->contact->email = 'max@muster.example';
$invoice->buyer->name = 'Stadt Beispielhausen';
$invoice->buyer->electronicAddress = new Identifier('inbox@beispielhausen.example', 'EM');
$invoice->buyer->address->postcode = '12345';
$invoice->buyer->address->city = 'Beispielhausen';
$invoice->buyer->address->country = 'DE';
$invoice->paymentMeans[] = new PaymentMeans(typeCode: PaymentMeansCode::SEPA_CREDIT_TRANSFER, accountId: 'DE02120300000000202051');
$invoice->lines[] = new Line(id: '1', name: 'Screw set', quantity: 2, unit: UnitCode::PIECE, netPrice: '50.00',
    vatCategory: VatCategoryCode::STANDARD_RATE, vatRate: 19);
$invoice->attachments[] = Attachment::fromFile('Aufmass.pdf');   // a supporting document: name, type, content

$xml = $invoice->toXml(Profile::XRechnung);        // CII; the totals and the VAT breakdown are calculated
$xml = $invoice->toXml(Profile::XRechnung, Syntax::UblInvoice);   // the same invoice in UBL
$pdf = $invoice->toPdf(Profile::En16931, pdf: file_get_contents('R-2026-0001.pdf'));   // your PDF, the XML in it
$pdf = $invoice->toPdf(Profile::En16931, 'de');    // without a PDF of your own: rendered in German, the XML in it

// Peppol takes electronic addresses with a scheme of Peppol - no e-mail address (EM):
$invoice->seller->electronicAddress = new Identifier('DE123456789', '9930');        // the German VAT identifier
$invoice->buyer->electronicAddress = new Identifier('04011000-12345-34', '0204');   // the Leitweg-ID
$xml = $invoice->toXml(Profile::Peppol);           // Peppol BIS Billing 3.0 in UBL

$invoice = Invoice::fromArray([                    // the same as array, with the same names
    'number' => 'R-2026-0001',
    'purchaseOrder' => 'PO-4711',                  // an object with one main value may be given as that value
    'seller' => ['name' => 'Muster GmbH', 'address' => ['city' => 'Plauen', 'country' => 'DE']],
    'lines' => [['id' => '1', 'name' => 'Screw set', 'quantity' => 2, 'unit' => 'H87', 'netPrice' => '50.00']],
    // ...
]);
```

- **Values** are plain: amounts, quantities and percentages as string or number (`'19.00'`, `19` - a text with a
  point and without thousands separators, `'1,5'` is refused), dates as `YYYY-MM-DD` or `DateTimeInterface` (a date
  with time and zone, `2026-09-01T14:30+02:00`, where EXTENDED allows one), yes/no as bool, codes and other texts as
  strings (a whole number given to `fromArray()` is its digits: `'id' => 1`) - classes of constants name the common
  codes (`InvoiceTypeCode`, `VatCategoryCode`, `PaymentMeansCode`, `UnitCode`, `ElectronicAddressScheme`,
  `TextSubjectCode`, `AllowanceReasonCode`, `ChargeReasonCode`, `VatExemptionReasonCode`).
- **Objects and lists:** every object is there from the start (`$invoice->delivery->shipTo->address`), every list
  starts empty. An identifier with its scheme is an `Identifier` (a GlobalID in CII), a reference to another document a
  `DocumentReference` (`$invoice->purchaseOrder->number`, in EXTENDED with its date), a supporting document an
  `Attachment` - `Attachment::fromFile()` takes name, type and content from a file (and the name as its reference,
  which EN 16931 asks for).
- **Once in EN 16931, repeated in EXTENDED:** the property holds the first, a list `additional...` the others - the
  contacts of a party (`contact`, `additionalContacts`), the payment terms (`dueDate`, `paymentTerms` and the others of
  the invoice, then `additionalPaymentTerms`), accounting references, tenders, invoiced objects, and the notes, price
  discounts, VAT and object identifiers of a line. A contact names a person (`name`) or a department (`department`),
  not both: in CII the rules of ZUGFeRD / Factur-X and XRechnung refuse the two together, even in two contacts
  (CII-SR-465, CII-SR-466); UBL writes a department as the name.
- **Calculated** where not given: the amount of an allowance or charge from its percentage, the net amount of a line,
  the price discount of a line from its gross and net price (or the gross price from the discount), the VAT breakdown
  per category and rate, the totals and the amount due (`$invoice->calculate()`; `toXml()` and `toPdf()` do it with a
  copy - the invoice stays as built). Given values stay, the validator checks them. Decimals are exact, amounts
  rounded to two decimals half away from zero.
- **Checked:** the invoice written is checked by the validator in its profile - one that does not meet it is not
  returned (`InvalidInvoice`, see Errors). Before that, an unknown property, a value of the wrong kind (a number that
  is no decimal, an item of a list that is no object of its class - also in a list filled by assignment), a field
  outside the profile, more items than the profile allows, a field the syntax has no place for (the sub lines of the
  XRechnung extension in CII, the extensions of ZUGFeRD / Factur-X in UBL) stop it with the path of the value
  (`seller.roleCode (BT-X-543) is not a part of the profile XRECHNUNG. It is in EXTENDED.`,
  `lines.0.typeCode has no place in UBL.`,
  `lines.0: array is no Line - give an object of the model, or build the invoice with fromArray().`).
  [`MODEL.md`](MODEL.md) lists for each property its profiles in CII and in UBL.
- **Warnings do not stop it:** a warning of the rules leaves the invoice valid - `Validator` lists them
  (`warnings()`). Without any delivery information (the delivery date BT-72, a ship-to party) the delivery element
  that the schema of CII requires stays empty, and the rules of ZUGFeRD / Factur-X BASIC, EN 16931 and EXTENDED warn
  about an empty element (PEPPOL-EN16931-R008) - `$invoice->delivery->date` avoids it.
- **Profiles and syntaxes** (the second argument of `toXml()`, where a profile has both):

  | Profile | CII | UBL |
  |---|---|---|
  | `Minimum`, `BasicWl`, `Basic`, `En16931`, `Extended` | ZUGFeRD / Factur-X, the schema of FeRD for the profile | `En16931` only: EN 16931 in UBL |
  | `XRechnung` | the CII schema D16B of the KoSIT validator (unless `syntax` is given) | UBL 2.1, with the extension (sub lines, third party payments) |
  | `Core` | EN 16931 in CII, as `En16931` | UBL 2.1 (unless `syntax` is given) |
  | `Peppol` | the CII schema D16B of the KoSIT validator (if `syntax` is given) | UBL 2.1 (unless `syntax` is given) |

  EN 16931 itself is the same invoice in both syntaxes: `En16931` is the name of its profile in ZUGFeRD / Factur-X,
  `Core` the name the validator gives the rules of CEN alone - either writes it, in the syntax given. In UBL a credit
  note (type code 381 and the other credit note codes of EN 16931) is a UBL CreditNote, any other invoice a UBL
  Invoice - `Syntax::UblInvoice` and `Syntax::UblCreditNote` both mean UBL. `toPdf()` writes CII, the syntax of a
  ZUGFeRD / Factur-X PDF.
- **Added when written:** the specification identifier (BT-24) of the profile unless the invoice gives one (the
  extension of XRechnung), the business process of Peppol (`urn:fdc:peppol.eu:2017:poacc:billing:01:1.0`) unless
  given, what the specification fixes (the indicator of allowances and charges, the scheme `VA` or `FC` of a tax
  registration, the tax type `VAT`, the date format `102`, the currency of the VAT total), the empty containers the
  schema requires and the order of the schema. An identifier with a scheme becomes a GlobalID, an account number that
  is no IBAN a ProprietaryID. In UBL: the tax scheme `VAT` (`FC` for the tax number), the currency of every amount,
  the document type code `130` of an invoiced object, the order reference `NA` beside a sales order reference and the
  card network `NA` - which UBL requires and EN 16931 has no field for -, the specification identifier of the
  XRechnung extension for an XRechnung with content of the extension (sub lines, third party payments).
- **UBL in its own way:** the subject code of a note is written as `#AAI#text`; a credit note has its due date in its
  payment means (UBL 2.1 CreditNote has no due date of its own) and its project reference as a document reference of
  type `50`; the department of a contact becomes its name where no person is named (a department next to a person
  has no place); the gross price shares the base quantity of the net price (a different one has no place).

Every CII example of FeRD and KoSIT read and written again is the same document; every example, UBL or CII, written
as CII has the same content; the valid ones stay valid, except seven EXTENDED examples whose totals rely on EXTENDED
content (sub invoice lines, logistics charges). Every UBL example of KoSIT and Mustang read and written again as UBL
is the same model, and a valid one stays valid. Every CII example of EN 16931 content written as UBL, and every UBL
example written as CII, gives the semantic model of KoSIT of the original, and a valid one stays valid in the other
syntax - except what the other syntax has no place for, which stops the generator with a message: in UBL a department
next to the person of a contact, a second buyer identifier, the VAT exemption reason of a line; in CII the sub lines
of the XRechnung extension, the type of a third party payment, a second preceding invoice in XRechnung. A small
invoice takes about 1 ms, 20 ms with the validator.

**The ZUGFeRD / Factur-X PDF** (`toPdf()`) is the PDF of the invoice with its XML embedded, a PDF/A-3. The PDF is
yours - the invoice as your system prints it, given with `pdf:` -, or, where you have none, the invoice rendered by
the package in the language given (the layout of `PdfRenderer`):

- The XML is embedded as the specification asks (Factur-X 1.09.2 / ZUGFeRD 2.5.2, chapter 6): as `factur-x.xml`
  (`xrechnung.xml` in the profile XRechnung), associated with the document as `Alternative` (`Data` for MINIMUM and
  BASIC WL, whose XML holds less than the PDF shows), with the XMP metadata of PDF/A-3 and the extension schema of
  Factur-X. An invoice file the PDF carries already is replaced, other attachments stay.
- The PDF is written anew in one revision and gets what PDF/A asks of the file: the binary comment after the header,
  an output intent with an sRGB profile, the resources of each page in its own dictionary, the document information in
  agreement with the XMP metadata, a file identifier. What PDF/A asks of the content - fonts embedded, no JavaScript,
  transparency only with its colour space - your own PDF has to meet; it keeps the conformance level it declares
  (PDF/A-3b otherwise). The rendered invoice is PDF/A-3u as long as its text stays within the characters of the font
  DejaVu Sans (Latin, Greek, Cyrillic): Chinese, Japanese or Korean characters and emoji it cannot show, and the PDF
  then misses PDF/A.
- Encrypted and signed PDFs are refused - writing the invoice into a signed PDF would break the signature. Peppol BIS
  Billing 3.0 is XML only.

Checked with veraPDF (PDF/A) and Mustang (PDF/A and the embedded invoice): the invoice of every profile and the 61
PDFs of the FeRD examples with their XML written into them pass both.

**By the ids of the fields** - the Factur-X field list, in UBL the business terms of EN 16931 and the XRechnung
extension - an invoice is written with `Generation\Generator`, which also writes without the validator:

```php
use Dealerweb\EInvoice\Generation\Generator;
use Dealerweb\EInvoice\Profile;

$xml = (new Generator(Profile::XRechnung))->xml([
    'BT-1' => 'R-2026-0001',                   // keys: the ids of the Factur-X field list
    'BT-2' => '2026-09-26',                    // dates as YYYY-MM-DD, YYYYMMDD or DateTimeInterface
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

$xml = (new Generator(Profile::Extended, validate: false))->xml($invoice);   // the model, without the validator
$pdf = (new Generator(Profile::En16931))->pdf($xml, null, 'de');              // a PDF of XML written before
```

- **Fields** are the ids of the Factur-X field list, whose names `Labels::name()` gives: the business terms and groups
  of EN 16931, the extensions of Factur-X (`BT-X-...`, `BG-X-...`), the attributes of an element (its id with `-1` for
  the scheme or MIME code, `-2` for the scheme version or file name) and, to group several instances, the ids of
  elements without a business term of their own (`BT-17-00`: one additional document). A group may be left out where
  the field says it all (`BT-27` at the top is the seller's name); the invoicing period `BG-14` may stand at the top or
  in the delivery information `BG-13`, as EN 16931-1 has it. A list gives several instances: invoice lines, VAT
  breakdowns, credit transfer accounts (each account gets payment means of its own).
- **Values** are written as given: amounts and quantities as strings (`'19.00'`, with a point and without thousands
  separators), a bool for an indicator, an int as it is, a float with at most ten decimals. `null` and `''` are no
  value. Nothing is calculated: the totals and the VAT breakdown are given.

### Checking an invoice

```php
use Dealerweb\EInvoice\Profile;
use Dealerweb\EInvoice\Validation\Severity;
use Dealerweb\EInvoice\Validation\Validator;

$report = (new Validator())->validateFile('invoice.xml');   // or a ZUGFeRD / Factur-X PDF - the file tells which
$report = (new Validator())->validate($xml);                // XML - or the content of a PDF - as string
$report = (new Validator())->validate($xml, Profile::Core); // against a given profile instead of the one named in BT-24

$report->isValid();        // meets its profile: no message is an error (the KoSIT validator's "accept")
$report->isEInvoice();     // valid and an invoice in the sense of EN 16931 - ZUGFeRD MINIMUM and BASIC WL are not
$report->profile();        // Profile::XRechnung, Profile::Extended, ... - null when no rules apply
$report->scenario();       // e.g. "EN16931 XRechnung (UBL Invoice)", "ZUGFeRD / Factur-X EXTENDED (Factur-X 1.09.2)"
$report->ruleSets();       // e.g. ["en16931-ubl", "xrechnung-ubl"] - empty when the schema already failed

foreach ($report->errors() as $message) {   // also warnings(), messages(Severity::Information), messages()
    $message->code;        // rule id ("BR-CO-10") - or SCHEMA, NOT_WELL_FORMED, NO_SCENARIO, PROCESSING_ERROR, ...
    $message->text;        // the text of the rule or of the error
    $message->source;      // "schema", "document" or the rule set, e.g. "xrechnung-ubl"
    $message->location;    // XPath of the node a rule fired on
    $message->line;        // line of a schema error
}
$report->notes();          // e.g. that the own rules of another CIUS are not included, or what was checked of a PDF
$report->toArray();        // everything as nested arrays, e.g. for JSON
```

Of a ZUGFeRD / Factur-X PDF the XML it carries is checked (`validatePdf()`, or `validate()` and `validateFile()`,
which see that it is a PDF), not the PDF itself - PDF/A-3 and the embedding are the job of a PDF validator such as
veraPDF, and the report says so in its notes. A PDF that cannot be read or carries no invoice is reported
(`INVALID_PDF`, `NO_INVOICE`).

The rules come from the specification identifier (BT-24):

| Document | Profile | Schema | Rules |
|---|---|---|---|
| XRechnung 3.0, its extension and CVD (UBL or CII) | `XRechnung` | UBL 2.1 / CII D16B | EN 16931 (CEN) and XRechnung (KoSIT), with the message levels of KoSIT's scenario |
| ZUGFeRD 2.x / Factur-X MINIMUM, BASIC WL, BASIC, EXTENDED (CII) | `Minimum` ... `Extended` | FeRD schema of the profile | FeRD rules of the profile |
| EN 16931 (`urn:cen.eu:en16931:2017`) in CII - ZUGFeRD / Factur-X EN 16931 | `En16931` | FeRD schema EN16931 | FeRD rules EN16931 |
| EN 16931 in UBL | `Core` | UBL 2.1 | EN 16931 (CEN) |
| Peppol BIS Billing 3.0 (UBL or CII) | `Peppol` | UBL 2.1 / CII D16B | EN 16931 (CEN) and Peppol BIS Billing 3.0 - the package's own implementation of the rules of OpenPeppol (see below) |
| another specification based on EN 16931 (Peppol BIS Self-Billing, another CIUS, XRechnung 2.x) | `Core` | UBL 2.1 / CII D16B | EN 16931 (CEN) - the report notes that the own rules of the specification are not included |
| anything else | - | - | none: rejected (`NO_SCENARIO`), as by the KoSIT validator |

A given profile decides instead - `Profile::Core` checks a CII document like the KoSIT validator's scenario
"EN16931 (CII)", `Profile::XRechnung` and `Profile::Peppol` a document with another identifier with the rules of
the profile and a note. The levels follow KoSIT's report: flag or role `fatal`/`error` is an error, `warning`/`warn`
a warning, `information`/`info` an information, anything else an error. When the schema fails, the rules are not
applied; a rule set that cannot be applied to the document gives a `PROCESSING_ERROR` - both as in the KoSIT
validator.

The verdicts are those of the official tools: the rules are the official Schematron stylesheets (EN 16931 1.3.16 of
CEN and XRechnung 3.0.2 of the KoSIT validator configuration of 2026-08-31, ZUGFeRD 2.5.2 / Factur-X 1.09.2 of FeRD),
compiled from Saxon's execution plan and executed with Saxon's rules of evaluation, down to its errors and its
comparisons of `-0` and `NaN`; libxml checks the schemas, with the verdict of Xerces where libxml is stricter
(decimals of more than 24 digits, dates and times with whitespace around them). This is proven for 2,802 documents
(the official samples, the unit tests of CEN and variants made to fire every rule): every finding equals Saxon's,
every schema verdict Xerces', every report the KoSIT validator's. Two things depend on the server, as
they do with the official tools: a date without timezone is compared in the implicit timezone - Saxon takes the one of
the JVM, the validator the one of PHP (`date.timezone`) -, and where the regular expression library gives up on a text
(hundreds of thousands of characters), the rule set is reported as not applied (`PROCESSING_ERROR`) instead of
deciding the rule.

The rules of Peppol BIS Billing 3.0 are the package's own implementation of the specification, not the validation
artefacts of OpenPeppol - with the rule identifiers, flags and verdicts of its release 3.0.20, in UBL and in CII: in
all unit tests and examples of the release (587 in UBL, 320 in CII) and in every document of the tests they find what
the official rules find, at the same nodes. The rules of
EN 16931 they are applied with are the package's (1.3.16; the release of Peppol brings 1.3.15).

A validation takes from a few to a few hundred milliseconds for usual invoices; large ones take about 3 ms per line
(1,000 lines of XRechnung about 3 s, 1,200 lines of ZUGFeRD EXTENDED about 6 s). The memory needed is about 40 to 60
times the size of the XML.

### Showing an invoice

The viewer of the package: every invoice it reads - XRechnung, ZUGFeRD / Factur-X, Peppol BIS, in UBL or CII - becomes
a readable page in the layout of a German business invoice, in German, English or French.

```php
use Dealerweb\EInvoice\Render\HtmlRenderer;
use Dealerweb\EInvoice\Render\PdfRenderer;

$html = (new HtmlRenderer('de'))->render($invoice);   // one self-contained HTML page - languages de, en, fr
$pdf = (new PdfRenderer('de'))->render($invoice);     // the same layout as A4 PDF (dompdf), to read
```

A read invoice is shown as delivered, with everything its document holds - also what the model has no place for; an
invoice built or changed is shown as the model holds it. Only the XML is the invoice, a rendering is a reading aid:
to send an invoice as PDF, write it with `toPdf()` - a ZUGFeRD / Factur-X PDF carries the XML.

### Names of fields and codes

```php
use Dealerweb\EInvoice\CodeList;
use Dealerweb\EInvoice\CodeLists;
use Dealerweb\EInvoice\Labels;

Labels::name('BT-1', 'fr');       // "Numéro de facture" - names in de, en, fr
Labels::name('BT-X-202', 'de');   // "Lieferung › Lieferschein › Nummer": the properties of the model that lead to it

CodeLists::name(CodeList::PaymentMeans, '58', 'de');   // "SEPA-Überweisung" - names of code values in de, en, fr
CodeLists::name(CodeList::Unit, '2I', 'de', fallback: false);   // null - no German name (English otherwise)
CodeLists::nameForField('BT-81', '58', 'en');         // the code list found through the business term
CodeLists::forField('BT-151');                         // [CodeList::TaxScheme, CodeList::VatCategory]
CodeLists::property(CodeList::DocumentType, '381', 'interpretation');   // "Credit Note"
```

### Errors

Reading - `Invoice::fromFile()`, `fromXml()`, `fromPdf()` and `xmlFromPdf()` - throws a subclass of
`Dealerweb\EInvoice\Exception\EInvoiceException`:

- `InvalidXml` - the file cannot be read, or the document is empty, is not well-formed XML or nests its elements
  deeper than 257 levels (`documentLine` names the line where the parser stopped),
- `UnsupportedDocument` - the document is no EN 16931 invoice in a supported syntax (an order, a ZUGFeRD 1.0
  invoice, ...), declares a DOCTYPE, or is a PDF that carries no invoice,
- `InvalidPdf` - the PDF is damaged beyond repair or encrypted (or what `fromPdf()` got is no PDF).

Reading does not throw for content the model has no place for, it names it in `unread()`.

Writing - `toXml()`, `toPdf()`, `Generator::xml()` and `Generator::pdf()` - throws an `InvalidArgumentException` for
a value that has no place in the profile or is of the wrong kind, and the message names it by its path in the model
(`lines.0.quantity: "1,5" is no decimal number - write it like 1234.56, with a point and without thousands
separators.`, `seller.additionalContacts: at most 1 in the profile EN16931, 2 given.`) or by its field id
(`BT-2: "26.09.2026" is no date (YYYY-MM-DD).`); `InvalidInvoice` (same base class as above) when the validator
rejects the invoice - `report()` holds the report; `InvalidPdf` for your own PDF when it is no PDF, is damaged beyond
repair, encrypted or signed. `Invoice::fromArray()` names an unknown property by its path (`Unknown property
buyer.adress.`).

`Attachment::content()`, `saveTo()` and `fromFile()` throw `InvalidAttachment` (same base class) when an attached
file is no valid base64, there is none to save, or a file cannot be read or is of a type EN 16931 does not allow.

The renderers throw an `InvalidArgumentException` for a language other than `de`, `en` or `fr`.

`Validator::validate()` does not throw for a bad document, it reports it: `NOT_WELL_FORMED`, `DOCTYPE` (refused, as
by reading), `NO_INVOICE`, `INVALID_PDF`. `Validator::validateFile()` throws `InvalidXml` when the file cannot be
read. Files are read from a path or a `file://` URL; stream wrappers (`http://`, `data:`, `php://filter`, ...) are
refused, reading never reaches the network. A schema of the package that cannot be read is a `RuntimeException` - an
installation problem, not an error of the invoice.

### Not included

- **No check of the PDF itself:** of a ZUGFeRD / Factur-X PDF the validator checks the XML it carries, not the PDF
  (PDF/A-3, the embedding) - that is the job of a PDF validator such as veraPDF.
- **No own rules of other specifications:** other CIUS of EN 16931 (Peppol BIS Self-Billing, national ones) are
  checked against EN 16931 itself (see the table above).

## How it works

- **Reading:** the official mapping of the syntaxes to the semantic model of EN 16931, published by KoSIT as XSLT,
  runs compiled to PHP; for 227 invoices - the KoSIT test suite, the examples of every profile of the FeRD release,
  samples of Mustang and horstoeko/zugferd - it gives exactly the output of the stylesheets under Saxon. Two
  deviations are deliberate: an impossible date (30 February) is reported where Saxon aborts, and an indicator written
  as `1` or `0` counts like `true` and `false`, as the EN 16931 validation reads it. The model with readable names is
  read and written along trees of the syntaxes, built from the field list of the FeRD release and the XML schemas of
  UBL 2.1 and CII.
- **Nothing is lost silently:** what a document holds beyond the model is named (`unread()`); left out is only what
  carries no information (the currency of an amount that is the invoice currency, the provenance of a code).
- **Secure parsing:** no network access, documents with a DOCTYPE are rejected before parsing - in the encoding the
  document declares, so a DOCTYPE behind UTF-16 or UTF-7 is found as well -, elements nest at most 257 levels deep,
  files are read from the file system only.
- **Validation:** the official Schematron stylesheets - EN 16931 of CEN, XRechnung of KoSIT, ZUGFeRD / Factur-X of
  FeRD - run compiled to PHP with the rules of evaluation of Saxon; libxml checks the schemas, the scenarios (which
  schema and rules apply to which document, message levels) are those of the KoSIT validator. Where an official rule is
  wrong, the validator is wrong with it: it gives the verdict of the official tools. The rules of Peppol BIS Billing
  3.0 are an own implementation with the verdicts of the official ones.
- **The ZUGFeRD / Factur-X PDF** is written with an own PDF layer: it reads the structure of any PDF -
  cross-reference tables and streams, object streams, hybrid files, incremental updates, offsets that point to the
  wrong places - and writes the document anew in one revision.
- **Names in German, English and French** for every EN 16931 business term and every ZUGFeRD / Factur-X EXTENDED
  element - the package's own: the chain of the properties of the model that lead to the field
  ("Position › Verkäufer › Registernummer"), in a view without what the view already says
  ("Verkäufer › Registernummer" in the block of a line).
- **Code lists:** every code of the 31 EN 16931 code lists (v17b, including the lists of ZUGFeRD / Factur-X EXTENDED)
  with its official English name, further columns (e.g. which document types count as credit note) and the business
  terms using the list. Country, currency and language names in all three languages come from Unicode CLDR. There is
  no official German or French edition: the names of the codes that occur on invoices are own translations (complete
  for the small lists, the common part of the large ones); rarer codes keep their English name.
- **Rendering:** `PdfRenderer` (dompdf) and `HtmlRenderer` lay the invoice out like a German business invoice - the
  issuer in the letterhead, the addressee with an information block beside it (title, date, number, key references,
  customer number, VAT identifier), further references and parties as rows, the lines with their sub-lines, totals
  with the VAT per rate, payment, notes, attachments, and the legal data of the issuer in the footer of every page,
  with page numbers. Self-billing swaps the roles; an invoicee of ZUGFeRD EXTENDED becomes the addressee. Every value
  is shown: information beyond EN 16931 at its place (cash discount terms, delivery note, test indicator, sub-lines,
  logistics charges, ...), the rest under "Further information" - only what merely repeats is left out (a period of
  one day that is the delivery date, a list price equal to the net price, allowances of zero, a contact point that
  is the company name, ...). Each document ends with the notice that only the XML is binding and the CEN/DIN notice.

## Versioning

The package follows [Semantic Versioning](https://semver.org/). Classes and methods marked `@internal` are not
part of the public API. Changes are listed in `CHANGELOG.md`.

## Security

Please report vulnerabilities privately, see `SECURITY.md`.

## License

MIT (see `LICENSE`) - free to use, provided as is, without any warranty or liability.

This software is an application of EN 16931-1:2017 and CEN/TS 16931-2:2017; names and identifiers
of the business terms are reproduced with the permission of CEN and DIN, the owners of the copyright.
Third-party material keeps its own license: what is compiled from the XRechnung visualization of KoSIT
(`resources/compiled`, Apache License 2.0), the XML schemas of the FeRD release package and what is compiled from it
(`resources/ferd`, `resources/compiled`: FeRD rights of use, schemas and rules Apache License 2.0), the validator
configuration of KoSIT (`resources/kosit-validator`: its scenarios and the rules of XRechnung Apache License 2.0; the
rules of EN 16931 of CEN, compiled into `resources/compiled/validation/en16931-*.php`, European Union Public Licence
1.2; the schemas of OASIS UBL 2.1 and UN/CEFACT CII D16B, unchanged with their notices), the names of Unicode CLDR in
the code lists (Unicode License V3) and the sRGB colour profile (`resources/icc`, CC0 1.0) - see `NOTICE`. The PDF
output uses dompdf (LGPL-2.1) and the libraries it needs (LGPL and MIT); Composer installs them as separate packages,
they are not part of this package.
