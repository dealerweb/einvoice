# FNFE-MPE sources (France CTC rules BR-FR, XP Z12-012 1.4.0)

From the repository **France_RFE** of the Forum National de la Facture Électronique et des Marchés Publics
Électroniques (FNFE-MPE), release **v1.4.0.04** (2026-09-04, commit `97ba0f3d4ef2abed5780e9490d5c3a2be832ab1d`), the
source archive of the tag (sha1 `927fa1c6a06d3014afae1d37b0457e07b80db694`). Repository:
https://github.com/fnfempe/France_RFE. License: Apache License 2.0 (`LICENSE`, from the repository); the header of the
Schematron files says "Schematron Licensed under European Union Public Licence (EUPL) version 1.4.0".

The rules are the additional controls of the French e-invoicing reform (standard AFNOR XP Z12-012, version 1.4.0 of
2026-06-30), applied after the schema and the rules of EN 16931 or of the Factur-X profile, and the rules of the
profile EXTENDED-CTC-FR, which take the place of those of EN 16931 for it. Taken is the standard
version (flag "fatal"), which applies from 2026-10-01 at the latest (header of the files); the repository's "warning"
versions were meant for the time before.

| File | Folder of the repository (`FNFE_RFE_INVOICE/`) | Content | In the package |
|---|---|---|---|
| `BR-FR-Flux2-Schematron-UBL.xslt` | `UBL/EN16931/2xslt` (the same file in `UBL/EXTENDED-CTC-FR/2xslt`) | the rules BR-FR for UBL Invoice and CreditNote, the Schematron `UBL/EN16931/schematron/BR-FR-Flux2-Schematron-UBL.sch` compiled to XSLT | compiled into `resources/compiled/validation/france-ubl.php` |
| `BR-FR-Flux2-Schematron-CII.xslt` | `CII/EN16931/2xslt` (the same file in `CII/EXTENDED-CTC-FR/2xslt` and for each Factur-X profile) | the rules BR-FR for UN/CEFACT CII, also the XML of a Factur-X, compiled from `CII/EN16931/schematron/BR-FR-Flux2-Schematron-CII.sch` | compiled into `resources/compiled/validation/france-cii.php` |
| `EXTENDED-CTC-FR-UBL.xslt` | `UBL/EXTENDED-CTC-FR/2xslt` | the rules of the profile EXTENDED-CTC-FR for UBL, derived from those of EN 16931 1.3.16, compiled from `UBL/EXTENDED-CTC-FR/schematron/EXTENDED-CTC-FR-UBL.sch` | compiled into `resources/compiled/validation/extended-ctc-fr-ubl.php` |
| `EXTENDED-CTC-FR-CII.xslt` | `CII/EXTENDED-CTC-FR/2xslt` | the rules of the profile EXTENDED-CTC-FR for CII, compiled from `CII/EXTENDED-CTC-FR/schematron/EXTENDED-CTC-FR-CII.sch` | compiled into `resources/compiled/validation/extended-ctc-fr-cii.php` |

All files are taken unchanged.

| File | sha1 |
|---|---|
| `BR-FR-Flux2-Schematron-UBL.xslt` | `9ac3697377245e1a1445d09e5ca42f54f8029ff6` |
| `BR-FR-Flux2-Schematron-CII.xslt` | `a8b79b153c782a2df5c9e31a521c877c8545c53f` |
| `EXTENDED-CTC-FR-UBL.xslt` | `f1c69d1b6bdb1f508a16a54c421d472d1428dd75` |
| `EXTENDED-CTC-FR-CII.xslt` | `b0ff86e7373553ff386a4ddd5e2348fb354a5a87` |