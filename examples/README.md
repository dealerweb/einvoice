# Examples

Runnable scripts for every format and every task. Each one stands alone, reads the example files in
[`input/`](input/) and writes what it makes to `examples/output/`:

```
php examples/create/xrechnung-cii.php
php examples/create/zugferd-en16931.php my-invoice.pdf     # most scripts take a file of your own
```

`sample-invoice.php` is the invoice the scripts share, built with the model - the place to see how an invoice is put
together.

## Creating - one script per format

| Script | Writes |
|---|---|
| `create/xrechnung-cii.php` | XRechnung in CII - the German standard, e.g. for public buyers |
| `create/xrechnung-ubl.php` | XRechnung in UBL |
| `create/xrechnung-extension-ubl.php` | XRechnung with its extension in UBL: a line made of sub lines |
| `create/xrechnung-pdf.php` | XRechnung embedded in your PDF (ZUGFeRD / Factur-X profile XRECHNUNG) |
| `create/zugferd-minimum.php` | ZUGFeRD / Factur-X MINIMUM - a booking aid, header and totals: XML and your PDF with it |
| `create/zugferd-basic-wl.php` | ZUGFeRD / Factur-X BASIC WL - a booking aid without lines: XML and your PDF with it |
| `create/zugferd-basic.php` | ZUGFeRD / Factur-X BASIC - the smallest profile with lines: XML and your PDF with it |
| `create/zugferd-en16931.php` | ZUGFeRD / Factur-X EN 16931 - the usual profile between companies: XML and your PDF with it |
| `create/zugferd-en16931-rendered.php` | ZUGFeRD / Factur-X without a PDF of your own: the invoice rendered by the package |
| `create/zugferd-extended.php` | ZUGFeRD / Factur-X EXTENDED - with a delivery note, a cash discount and a document name |
| `create/en16931-ubl.php` | EN 16931 in UBL without a national specification |
| `create/credit-note.php` | a credit note - a UBL CreditNote, and an XRechnung in CII |
| `create/peppol-ubl.php` | Peppol BIS Billing 3.0 in UBL |
| `create/peppol-cii.php` | Peppol BIS Billing 3.0 in CII |
| `create/from-an-array.php` | an invoice from an array with the names of the model - and as JSON |
| `create/by-field-ids.php` | an invoice by the ids of the fields (BT-1, BG-4 ...), without the validator, and the PDF of its XML |

## Reading, checking, converting, showing

| Script | Shows |
|---|---|
| `read/read-an-invoice.php` | an invoice read: parties, lines, VAT, totals, payment - and what was not read |
| `read/read-a-zugferd-pdf.php` | a ZUGFeRD / Factur-X PDF read, with the fields only EXTENDED has, and its XML as embedded |
| `read/read-every-input-file.php` | every example file read, its key facts on one line |
| `check/check-a-file.php` | the report of the validator for one file: verdict, profile, rules, messages |
| `check/check-every-input-file.php` | every example file checked, one line each |
| `check/check-an-invalid-invoice.php` | an invalid invoice: the rules it breaks, where, and the report as JSON |
| `check/check-against-a-profile.php` | checked against a profile given instead of the one the document names |
| `convert/zugferd-pdf-to-xrechnung.php` | a ZUGFeRD / Factur-X PDF written as XRechnung in CII and UBL |
| `convert/ubl-to-cii.php` | UBL written as CII, and CII as UBL |
| `convert/xml-into-your-pdf.php` | an XML you already have embedded into your PDF, exactly as it is |
| `show/show-as-html.php` | an invoice as HTML page to read, in German, English and French |
| `show/show-as-pdf.php` | an invoice as PDF to read, in German, English and French |
| `attachments/attach-files.php` | a file and the address of a document attached to an invoice |
| `attachments/save-attachments.php` | the attachments of an invoice saved, under safe names |
| `more/names-and-codes.php` | the names of fields and code values in German, English and French |
| `more/errors.php` | what is thrown when reading or writing fails, and what it says |

## Example files

| File | Is |
|---|---|
| `input/invoice.pdf` | the PDF an accounting system prints of the sample invoice - "your PDF" of the ZUGFeRD / Factur-X scripts; no invoice in it |
| `input/time-sheet.csv` | a supporting document to attach |
| `input/xrechnung-cii.xml` | XRechnung in CII |
| `input/xrechnung-ubl.xml` | XRechnung in UBL |
| `input/xrechnung-extension-ubl.xml` | XRechnung with its extension in UBL, with sub lines |
| `input/xrechnung-with-attachments.xml` | XRechnung in CII with an attached file and the address of a document |
| `input/xrechnung.pdf` | XRechnung embedded in `invoice.pdf` |
| `input/zugferd-minimum.pdf` | ZUGFeRD / Factur-X MINIMUM in `invoice.pdf` |
| `input/zugferd-basic-wl.pdf` | ZUGFeRD / Factur-X BASIC WL in `invoice.pdf` |
| `input/zugferd-basic.pdf` | ZUGFeRD / Factur-X BASIC in `invoice.pdf` |
| `input/zugferd-en16931.pdf` | ZUGFeRD / Factur-X EN 16931 in `invoice.pdf` |
| `input/zugferd-en16931.xml` | ZUGFeRD / Factur-X EN 16931, the XML alone |
| `input/zugferd-extended.pdf` | ZUGFeRD / Factur-X EXTENDED in `invoice.pdf` |
| `input/en16931-ubl.xml` | EN 16931 in UBL |
| `input/credit-note-ubl.xml` | a credit note, UBL CreditNote |
| `input/peppol-ubl.xml` | Peppol BIS Billing 3.0 in UBL |
| `input/peppol-cii.xml` | Peppol BIS Billing 3.0 in CII |
| `input/invalid-xrechnung.xml` | an XRechnung without the name of the seller - invalid on purpose |

The invoice files are written by the scripts in `create/` and `attachments/` themselves.
