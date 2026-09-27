<?php

declare(strict_types=1);

namespace Dealerweb\EInvoice\Pdf;

/**
 * A real number, kept as written (595.280) - integers are PHP ints.
 *
 * @internal
 */
final class Real
{
    public function __construct(public readonly string $text) {}
}
