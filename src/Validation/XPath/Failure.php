<?php

declare(strict_types=1);

namespace Dealerweb\EInvoice\Validation\XPath;

/**
 * The end of a sequence whose next item could not be computed. Saxon computes sequences item by item as they are
 * read: the error surfaces only if a reader asks for that item - exists() of a sequence whose first item is fine does
 * not see the error of the second. The evaluator computes sequences at once, stops at the first error and keeps it here
 * as the last entry; reading past the items before it raises it.
 *
 * @internal
 */
final class Failure
{
    public function __construct(public readonly DynamicError $error) {}
}
