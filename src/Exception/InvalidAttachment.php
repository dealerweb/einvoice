<?php

declare(strict_types=1);

namespace Dealerweb\EInvoice\Exception;

/**
 * An attachment of the invoice cannot be used: its embedded content is no valid base64, or it has none to save.
 */
final class InvalidAttachment extends EInvoiceException {}
