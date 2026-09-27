# KoSIT sources

From the XRechnung visualization of the Koordinierungsstelle für IT-Standards (KoSIT), licensed under the Apache
License 2.0 (see `LICENSE`).

| | |
|---|---|
| Repository | https://github.com/itplr-kosit/xrechnung-visualization |
| Release | v2026-08-31 (compatible with XRechnung 3.0) |
| Commit | e158048ec6774cd4837744b21ca34dc95ba14862 |

| File | Folder of the release | Content | In the package |
|---|---|---|---|
| `ubl-invoice-xr.xsl`, `ubl-creditnote-xr.xsl`, `cii-xr.xsl` | `src/xsl` | the mapping of UBL and CII to the EN 16931 model | compiled into `resources/compiled/ubl-invoice.php`, `ubl-creditnote.php`, `cii.php` |
| `common-xr.xsl` | `src/xsl` | the value types and helpers of the mapping | implemented by `src/Kosit/Transformer.php` |
| `functions.xsl` | `src/xsl` | helper functions for Saxon | - |
| `xrechnung-semantic-model.xsd` | `src/xsd` | the EN 16931 model: names, BT/BG ids, cardinality | compiled into `resources/compiled/model.php` |
| `l10n/de.xml`, `l10n/en.xml` | `src/xsl/l10n` | the field labels of the visualization | - |

The files are taken unchanged; the package contains what is compiled from them, not the files themselves.
