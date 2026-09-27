# CLDR sources (48.2.2)

Display names of countries, currencies and languages in German, English and French, taken unchanged from
release **48.2.2** of cldr-json (https://github.com/unicode-org/cldr-json, tag `48.2.2`, commit
`13e9fe520d5efe4298b1f988adea284a3396d8f5`, published 2026-09-21), the JSON edition of the Unicode Common
Locale Data Repository (CLDR). License: Unicode License V3 (`LICENSE`, see `NOTICE`).

Why CLDR: the EN 16931 code lists name codes in English only, and for countries with the ISO short name
("United States of America (the)", "Korea (the Republic of)"). CLDR is the maintained source of display names
that operating systems and browsers use, in all three languages ("United States", "Vereinigte Staaten",
"États-Unis"). The code lists `country`, `currency` and `language` take these names; codes CLDR does not know keep
the official English name (country 1A Kosovo, XI Northern Ireland, 66 collective language codes such as `afa`, a few
currencies) or have names of the package's own.

The names are compiled into `resources/compiled/codelists.php`; the files are not part of the package.

| File | Path in cldr-json | Used for | sha1 |
|---|---|---|---|
| `de/territories.json` | `cldr-json/cldr-localenames-full/main/de/territories.json` | country names | `3699c00e36b6def1e607e37cb4e01497b8a5079e` |
| `en/territories.json` | `cldr-json/cldr-localenames-full/main/en/territories.json` | country names | `3002e7d47aa8b21c47666abb4b535c9d9aba2734` |
| `fr/territories.json` | `cldr-json/cldr-localenames-full/main/fr/territories.json` | country names | `df68f56bd874facce46b7f32156d05db54536a8d` |
| `de/currencies.json` | `cldr-json/cldr-numbers-full/main/de/currencies.json` | currency names | `ba31868c0a4bb2017f11bac7c94992164660191c` |
| `en/currencies.json` | `cldr-json/cldr-numbers-full/main/en/currencies.json` | currency names | `a5fc6241ca2c61683082247f600dbea1c2924ab0` |
| `fr/currencies.json` | `cldr-json/cldr-numbers-full/main/fr/currencies.json` | currency names | `67e19dcf0f699b450d44dc4ec491123cd2200af4` |
| `de/languages.json` | `cldr-json/cldr-localenames-full/main/de/languages.json` | language names | `2c00303d66e5c681545accc3a93363dba078c770` |
| `en/languages.json` | `cldr-json/cldr-localenames-full/main/en/languages.json` | language names | `da8d1c05b0c14d9705ac9b84061ee07bcfc9de9e` |
| `fr/languages.json` | `cldr-json/cldr-localenames-full/main/fr/languages.json` | language names | `eb72af69d4861403dc9f0eb4346dedde45715127` |
| `supplemental/aliases.json` | `cldr-json/cldr-core/supplemental/aliases.json` | ISO 639-2 codes CLDR does not use directly (`ger`, `deu` -> `de`) | `2f78b43063f5dc0d159d9d294cac540a461b1942` |
| `LICENSE` | `LICENSE` | Unicode License V3 | `aeeef93ed46b57d858d6eefb5b9881c97a42de75` |
| `VERSION` | - | the release number, recorded in `resources/compiled/codelists.php` | - |
