<?php

declare(strict_types=1);

namespace Dealerweb\EInvoice\Validation\XPath;

/**
 * xs:untypedAtomic - the typed value of a node of a document without schema types, e.g. the content of an element.
 * Compared with a number it is read as xs:double, with a string as string (XPath 2.0, 3.5.2).
 *
 * @internal
 */
final class Untyped
{
    public function __construct(public readonly string $value) {}
}
