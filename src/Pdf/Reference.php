<?php

declare(strict_types=1);

namespace Dealerweb\EInvoice\Pdf;

/**
 * An indirect reference (12 0 R).
 *
 * @internal
 */
final class Reference
{
    public function __construct(public readonly int $number, public readonly int $generation = 0) {}
}
