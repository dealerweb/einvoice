# Security policy

## Supported versions

Security fixes are made for the latest release.

## Reporting a vulnerability

Please do not report a vulnerability in a public issue. Use the private vulnerability reporting of GitHub
instead ("Report a vulnerability" in the "Security" tab of the repository). Describe the problem and, if
possible, attach a document that shows it - without personal or business data of real parties.

You receive an answer as soon as the report has been examined. A fixed release is published together with
a security advisory.

## Scope

Invoices come from outside and are untrusted input. A vulnerability is, for example, a document that makes
the package

- access the network or read or write files,
- execute code or exhaust memory beyond the size of the document,
- produce an HTML page or a PDF that runs scripts or loads external resources.

The package reads the XML with every network access switched off and refuses documents that declare a
DOCTYPE. The HTML output is escaped and self-contained; dompdf runs without remote files, PHP and JavaScript.
