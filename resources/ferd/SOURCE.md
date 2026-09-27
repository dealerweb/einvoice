# FeRD sources (ZUGFeRD 2.5.2 / Factur-X 1.09.2)

From the release package **ZUGFeRD 2.5.2** (German edition, file `ZUGFeRD_2.5.2_DE.zip`,
sha1 `ed0df56344f610b5d475c57975f90b155415082b`) of the Forum elektronische Rechnung Deutschland (FeRD),
published 2026-08-04, valid from 2026-09-01. Download: https://www.ferd-net.de/download-zugferd
(registration and acceptance of the terms of use). Rights: see `NOTICE`.

| File | Folder of the release package | Content | In the package |
|---|---|---|---|
| `1_FACTUR-X 1.09.2 - EN FR.xlsx` | `Dokumentation` | the field list: sheet "Factur-X CII D22B EXTENDED" with id, XPath, data type and profiles of every element | compiled into `resources/compiled/facturx-fields.php` (ids, paths, data types and profiles - the names of the fields are the package's own) and `resources/compiled/cii-tree.php` (the element tree the generator writes along) |
| `2_EN16931 code lists values v17b - used from 2026-05-15 - Fx 1.09.2.xlsx` | `Dokumentation` | the EN 16931 code lists (European Commission, v17b): every code with its English name, per list version, changes and the business terms using it | compiled into `resources/compiled/codelists.php` |
| `7_ZUGFeRD_2.5.2_Technischer_Anhang_Profil_EXTENDED.pdf` | `Dokumentation` | the technical appendix of the EXTENDED profile | - |
| `Factur-X_1.09.2_<PROFILE>*.xsd` | `Schema`, `<n>_Factur-X_1.09.2_<PROFILE>` | the four XML schemas of each profile | `schema/<PROFILE>/`, unchanged |
| `FACTUR-X_<PROFILE>_codedb.xml` | `Schema`, `_XSLT_<PROFILE>` | the code lists the rules of the profile load | `schema/<PROFILE>/`, unchanged |
| `FACTUR-X_<PROFILE>.xslt` | `Schema`, `_XSLT_<PROFILE>` | the Schematron rules of the profile, compiled to XSLT | compiled into `resources/compiled/validation/facturx-*.php` |

`<PROFILE>` stands for MINIMUM, BASICWL, BASIC, EN16931 and EXTENDED. All files are taken unchanged.

| File | sha1 |
|---|---|
| `1_FACTUR-X 1.09.2 - EN FR.xlsx` | `19a20a5ef469c817aa254e3f3ce10580c37a4dcb` |
| `2_EN16931 code lists values v17b - used from 2026-05-15 - Fx 1.09.2.xlsx` | `3a762c016d1d00b252e6a895f0e5bbf1252cd462` |
| `7_ZUGFeRD_2.5.2_Technischer_Anhang_Profil_EXTENDED.pdf` | `3dd6b052de47fb391caaf758e0b2fbf0cd55d157` |

`resources/compiled/facturx-fields.php` records the sha1 of the workbook it was built from (key `source`).
