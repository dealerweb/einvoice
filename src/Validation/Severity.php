<?php

declare(strict_types=1);

namespace Dealerweb\EInvoice\Validation;

/**
 * The level of a validation message, as KoSIT's report assigns it: an error rejects the invoice, a warning and an
 * information do not.
 */
enum Severity: string
{
    case Error = 'error';
    case Warning = 'warning';
    case Information = 'information';
}
