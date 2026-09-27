<?php

declare(strict_types=1);

namespace Dealerweb\EInvoice\Validation\XPath;

use RuntimeException;

/**
 * An expression failed while it was evaluated, with the error code of XPath 2.0 (e.g. FORG0001 invalid value for a
 * cast, XPTY0004 wrong type, FOAR0001 division by zero). A processor like Saxon stops the whole transformation at such
 * an error - the validator reports that the rules could not be applied, like the KoSIT validator does.
 *
 * @internal
 */
final class DynamicError extends RuntimeException
{
    public function __construct(public readonly string $errorCode, string $message)
    {
        parent::__construct("$errorCode: $message");
    }
}
