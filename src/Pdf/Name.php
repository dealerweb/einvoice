<?php

declare(strict_types=1);

namespace Dealerweb\EInvoice\Pdf;

/**
 * A name object (/Type) - its bytes with the #xx escapes resolved.
 *
 * @internal
 */
final class Name
{
    public function __construct(public readonly string $value) {}
}
