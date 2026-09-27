<?php

declare(strict_types=1);

namespace Dealerweb\EInvoice\Exception;

use RuntimeException;

/**
 * The document cannot be read as an invoice, a part of it is broken or a generated invoice does not meet its profile -
 * catch this to handle every reason at once.
 */
abstract class EInvoiceException extends RuntimeException {}
