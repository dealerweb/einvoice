# Changelog

All notable changes to this package are documented in this file. The format follows
[Keep a Changelog](https://keepachangelog.com/en/1.1.0/), the versions follow
[Semantic Versioning](https://semver.org/spec/v2.0.0.html). Classes and methods marked `@internal` are not
part of the public API and may change in any release.

## [Unreleased]

## [1.2.1] - 2026-10-25

### Changed

- The names of countries, currencies and languages come from Unicode CLDR 49.0.0 (was 48.2.2). Three English
  country names change: HM "Heard Island & McDonald Islands", SH "St. Helena, Ascension & Tristan da Cunha", TF
  "French Southern and Antarctic Lands".

## [1.2.0] - 2026-10-13

### Added

- The rules of the French e-invoicing reform (standard AFNOR XP Z12-012), published by FNFE-MPE (release 1.4.0.04):
  the French rules BR-FR for UBL and CII, checked after the rules of the profile for an invoice that declares the
  reform - a note with the subject code BAR, the profile EXTENDED-CTC-FR - and for every invoice with
  `new Validator(frenchRules: true)`. The scenario names them ("EN16931 (UBL Invoice) + French rules BR-FR (FNFE-MPE
  1.4.0.04)"), `Report::ruleSets()` lists `france-ubl` or `france-cii`. Proven against Saxon like the other rule sets;
  the examples of FNFE-MPE pass.

### Changed

- An invoice of the profile EXTENDED-CTC-FR is checked with the rules of FNFE-MPE for that profile (scenario
  "EXTENDED-CTC-FR (UBL Invoice)", "... (CII)") in place of those of EN 16931 or ZUGFeRD / Factur-X EXTENDED, which
  rejected the examples of FNFE-MPE; the note that it was checked only in part is gone.
- The generator writes ZUGFeRD / Factur-X EN 16931 only when it also passes the scenario EN16931 (CII) of the KoSIT
  validator - the CII schema D16B and the rules of CEN -, with which a recipient may check the XML on its own;
  otherwise `InvalidInvoice`, its report noting why. A second preceding invoice reference (BG-3) is therefore refused
  in EN 16931 as in XRechnung (`precedingInvoices: at most 1 in the profile EN16931, 2 given.`): the schema of FeRD
  holds any number, D16B one.

## [1.1.2] - 2026-10-13

### Fixed

- A CII invoice as XML of its own with the plain EN 16931 identifier (`urn:cen.eu:en16931:2017`) is checked as
  EN 16931, like the KoSIT validator's scenario "EN16931 (CII)": `Profile::Core`, the rules of CEN, the schema of
  CII D16B. It was checked with the narrower rules of ZUGFeRD / Factur-X EN 16931, which rejected, for example, the
  VAT category B (split payment) that EN 16931 allows. Factur-X is the PDF with its XML: the XML of a ZUGFeRD /
  Factur-X PDF keeps the rules of FeRD, as does `Profile::En16931` given to the validator. With whitespace around the
  identifier such an XML matches no scenario (`NO_SCENARIO`), as in UBL and in the KoSIT validator.

## [1.1.1] - 2026-10-01

### Changed

- A written ZUGFeRD / Factur-X PDF names this package with its version as creator and producer (document
  information and XMP, e.g. "DealerWeb E-Invoice 1.1.1"), also where the given PDF names another program - the
  package writes the final file. The rendered reading PDF names it as creator, too.

## [1.1.0] - 2026-09-27

### Added

- `Invoice::xmlFromPdf()`: the XML a ZUGFeRD / Factur-X PDF carries, byte for byte as the sender embedded it - the
  original of an invoice received as PDF, to store next to it.

## [1.0.0] - 2026-09-27

First public release.

### Added

- One class for the invoice, `Invoice`: every field of EN 16931, of the XRechnung extension and of ZUGFeRD / Factur-X
  EXTENDED as a property with a readable name, the parts of the invoice as classes in `Model` - as objects or as
  arrays with the same names (`fromArray()`, `toArray()`), with constants for the common codes. `MODEL.md` lists every
  name with its field and its profiles in CII and in UBL.
- Reading of EN 16931 invoices and credit notes - XRechnung 1.2 to 3.0 (including extension and CVD), ZUGFeRD 2.0,
  ZUGFeRD / Factur-X MINIMUM to EXTENDED and EXTENDED-CTC-FR, Peppol BIS - as XML in UBL and UN/CEFACT CII or from a
  ZUGFeRD / Factur-X PDF (`Invoice::fromFile()`, `fromXml()`, `fromPdf()`): every example of FeRD and KoSIT is read
  completely and written back with the same content. What a document holds and the model has no place for is named
  instead of dropped (`Invoice::unread()`). A value is the same in both syntaxes (the VAT point date code of EN 16931,
  the subject code of a note as a property of its own).
- Credit notes and self-billing recognized (`Invoice::isCreditNote()`, `isSelfBilled()`), the key facts for capturing
  an invoice as a document (`Invoice::summary()`) and the declared specification with standard, profile and version
  (`Specification`).
- The supporting documents (BG-24, `Model\Attachment`): the attached file decoded (`content()`), a safe file name and
  saving that never overwrites a file (`saveTo()`), a file attached by its path (`Attachment::fromFile()`); addresses
  of external documents are passed on, never opened.
- Names of all business terms and of every ZUGFeRD / Factur-X EXTENDED element in German, English and French - the
  package's own, the chain of the properties of the model that lead to a field (`Labels`); a rendered invoice leaves
  out what its block already says.
- The 31 EN 16931 code lists with the names of their codes in German, English and French (`CodeLists`).
- Rendering as PDF (dompdf) or HTML page in German, English and French (`PdfRenderer`, `HtmlRenderer`): a read invoice
  as delivered, with every value beyond EN 16931 at its place, one built or changed as the model holds it. Its base is
  the official KoSIT mapping, compiled from its XSLT stylesheets and executed in PHP: for 227 test invoices the output
  is identical to the one of the original stylesheets. Two deliberate deviations: an impossible date is reported
  instead of stopping the run, and indicators written as `1`/`0` count like `true`/`false`.
- Validation with the verdict of the official tools (`Validator`, `Report`, `Profile`): the XML schemas and the
  Schematron rules of EN 16931 (CEN), XRechnung (KoSIT) and the ZUGFeRD / Factur-X profiles MINIMUM to EXTENDED
  (FeRD), chosen by the specification identifier or given as profile and applied like the KoSIT validator does
  (scenarios, message levels, rules only after a valid schema). The official Schematron stylesheets are compiled from
  Saxon's execution plan and executed in PHP: for 2,802 documents every finding equals Saxon's, every schema verdict
  Xerces' and every report the one of the KoSIT validator 1.6.3. The time grows linearly with the lines of an invoice.
  Peppol BIS Billing 3.0 in UBL and CII (also as `Profile::Peppol`) is checked against EN 16931 and the rules of
  Peppol - the package's own implementation with the rule identifiers, flags and verdicts of the official rules of
  OpenPeppol (`resources/peppol/SOURCE.md`): in every unit test and example of release 3.0.20 and in every document of
  the tests both find the same. Of a ZUGFeRD / Factur-X PDF the XML it carries is checked (`validatePdf()`, and
  `validate()` and `validateFile()`, which see that it is a PDF).
- Writing (`Invoice::toXml()`): CII of ZUGFeRD / Factur-X MINIMUM to EXTENDED, XRechnung and Peppol BIS Billing 3.0, UBL
  invoices and credit notes of EN 16931, XRechnung with its extension (sub lines, third party payments) and Peppol BIS
  Billing 3.0 - in the order of the schema with what the specification fixes, checked by the validator before it is
  returned (`InvalidInvoice`). Totals and the VAT breakdown are calculated where they are not given
  (`Invoice::calculate()`), and a message names the path of a value that has no place and the profiles that have it. EN
  16931 itself is written in either syntax, whether named `Profile::En16931` or `Profile::Core`; a credit note code
  gives a UBL CreditNote. What UBL requires beyond EN 16931 is added (tax scheme, currency of every amount, `NA` where
  UBL asks for a value EN 16931 has none for). Every CII example of FeRD and KoSIT read and written again is the same
  document; every CII example of EN 16931 content written as UBL, and every UBL example written as CII, has the same
  content, and a valid one stays valid. `Profile::identifier()` gives the specification identifier of a profile.
- ZUGFeRD / Factur-X PDF (`Invoice::toPdf()`): the XML embedded in a PDF/A-3 as the specification asks - in a PDF of
  your own, the invoice as your system prints it, which is written anew as PDF/A asks of the file, or, without one,
  in the invoice rendered by the package (PDF/A-3u).
  Checked with veraPDF and Mustang for every profile and the PDFs of the FeRD examples. `InvalidPdf` for a PDF that
  cannot take the invoice (damaged, encrypted, signed).
- Writing by the ids of the Factur-X field list and without the validator (`Generation\Generator`).
- Runnable examples (`examples/`): a script for every format - XRechnung in CII, UBL and with its extension,
  ZUGFeRD / Factur-X MINIMUM to EXTENDED as XML and in your own PDF, EN 16931 in UBL, credit notes, Peppol BIS - and
  for reading, checking, converting, showing, attachments, names and errors, with example files of every format.

[Unreleased]: https://github.com/dealerweb/einvoice/compare/v1.2.1...HEAD
[1.2.1]: https://github.com/dealerweb/einvoice/compare/v1.2.0...v1.2.1
[1.2.0]: https://github.com/dealerweb/einvoice/compare/v1.1.2...v1.2.0
[1.1.2]: https://github.com/dealerweb/einvoice/compare/v1.1.1...v1.1.2
[1.1.1]: https://github.com/dealerweb/einvoice/compare/v1.1.0...v1.1.1
[1.1.0]: https://github.com/dealerweb/einvoice/compare/v1.0.0...v1.1.0
[1.0.0]: https://github.com/dealerweb/einvoice/releases/tag/v1.0.0
