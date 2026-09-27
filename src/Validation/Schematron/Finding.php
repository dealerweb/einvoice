<?php

declare(strict_types=1);

namespace Dealerweb\EInvoice\Validation\Schematron;

use DOMNode;

/**
 * A failed assert or a successful report of a Schematron rule set - one entry of the SVRL the official stylesheet
 * would produce.
 *
 * @internal
 */
final readonly class Finding
{
    public function __construct(
        /** "assert" (failed-assert) or "report" (successful-report) */
        public string $kind,
        /** Rule id as the rule set gives it, e.g. "BR-CO-16"; null where it has none. */
        public ?string $id,
        /** "fatal", "warning", "information" or null */
        public ?string $flag,
        public ?string $role,
        /** The test as written in the rule set. */
        public string $test,
        /** The message, as the stylesheet writes it (whitespace included). */
        public string $text,
        /** XPath of the node, in the form of the stylesheet's compiler. */
        public string $location,
        /** The node the rule fired on. */
        public DOMNode $node,
    ) {}
}
