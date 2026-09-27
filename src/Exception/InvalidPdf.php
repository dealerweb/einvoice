<?php

declare(strict_types=1);

namespace Dealerweb\EInvoice\Exception;

/**
 * A PDF cannot be read or cannot carry an invoice: it is no PDF, damaged beyond repair, encrypted or signed (writing
 * the invoice into it would break the signature).
 */
final class InvalidPdf extends EInvoiceException {}
