# The rules of Peppol BIS Billing 3.0 (own implementation)

`peppol-ubl.sch` (UBL Invoice and CreditNote) and `peppol-cii.sch` (UN/CEFACT CII D16B) hold the rules of Peppol BIS
Billing 3.0 of OpenPeppol as this package implements them: written for the package (MIT License, like the rest of it),
not taken from the validation artefacts of OpenPeppol. Every rule carries the identifier the specification gives it
(`PEPPOL-EN16931-R001`, `DE-R-003`, ...) and comes to the same verdict with the same flag (fatal or warning) at the
same node as the official rules of release 3.0.20 of https://github.com/OpenPEPPOL/peppol-bis-invoice-3 - in all unit
tests and examples of the release (587 documents in UBL, 320 in CII). The arrangement of the rules, their helper
functions and their messages are the package's own.

| File | Content |
|---|---|
| `peppol-ubl.sch`, `peppol-cii.sch` | the rules as Schematron - the source |
| `peppol-ubl.xsl`, `peppol-cii.xsl` | compiled from them by SchXslt 1.10.1, the compiler of the stylesheets of CEN and KoSIT |

The package contains what is compiled from them: `resources/compiled/validation/peppol-ubl.php` and `peppol-cii.php`,
the rule sets the validator applies after the rules of EN 16931 to a document with the specification identifier of
Peppol BIS Billing 3.0 (or with `Profile::Peppol`).

The rules of EN 16931 are those of the package (`resources/kosit-validator`, release 1.3.16 of CEN); release 3.0.20 of
Peppol BIS Billing brings release 1.3.15 with it. Where a rule of the release does not do what its text says, the
package's rule does what the release's does - the verdicts are the point: in CII the rule of the type code (P0100)
is written for `ram:ExchangedDocument`, which CII does not have, and never applies; DK-R-007 looks for the mandate
reference below the payment means.
