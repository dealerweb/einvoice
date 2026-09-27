# KoSIT validator configuration (XRechnung 3.0.2 and EN 16931)

Taken unchanged (line endings LF) from the release **v2026-08-31** of
https://github.com/itplr-kosit/validator-configuration-xrechnung, file
`xrechnung-3.0.2-validator-configuration-2026-08-31.zip` (sha1 `d3bad4af565dc0baad6527393637f03117e3cebd`) - the
configuration of the KoSIT validator (https://github.com/itplr-kosit/validator) for XRechnung 3.0.2 and EN 16931. Only
the files the validator of this package uses; paths as in the release.

| Files | Content | Rights | In the package |
|---|---|---|---|
| `scenarios.xml` | the scenarios: which schema and rules apply to which document, custom message levels | KoSIT, Apache License 2.0 (`LICENSE`) | compiled into `resources/compiled/validation/scenarios.php`, with the scenarios the package adds |
| `resources/ubl/2.1/xsd/` | XML schemas of OASIS UBL 2.1 | Copyright (c) OASIS Open 2013, unchanged | unchanged |
| `resources/cii/16b/xsd/` | XML schemas of UN/CEFACT Cross Industry Invoice D16B | Copyright (C) UN/CEFACT 2016 - may be copied and distributed unchanged with its notice | unchanged |
| `resources/ubl/2.1/xsl/`, `resources/cii/16b/xsl/` | Schematron rules of EN 16931 (CEN/TC 434, release validation-1.3.16 of https://github.com/ConnectingEurope/eInvoicing-EN16931), compiled to XSLT by SchXslt | European Union Public Licence 1.2 (`LICENSE-EUPL-1.2`) | compiled into `resources/compiled/validation/en16931-*.php` |
| `resources/xrechnung/3.0.2/xsl/` | Schematron rules of XRechnung 3.0.2 (release 2.6.0 of https://github.com/itplr-kosit/xrechnung-schematron), compiled to XSLT by SchXslt | KoSIT, Apache License 2.0 (`LICENSE`) | compiled into `resources/compiled/validation/xrechnung-*.php` |

The compiled rule sets keep the licence of their source - the European Union Public Licence 1.2 those of CEN, the
Apache License 2.0 those of XRechnung - and name source and licence in their first line.
