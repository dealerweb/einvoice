<?php

declare(strict_types=1);

namespace Dealerweb\EInvoice\Validation;

/**
 * One finding of a validation: a problem of the XML, a schema error, a rule that failed or reported, or a rule set
 * that could not be applied.
 */
final readonly class Message
{
    /** The XML could not be read, it is no invoice or no rules apply to it (source of the message). */
    public const SOURCE_DOCUMENT = 'document';

    /** The XML schema of the profile (source of the message). */
    public const SOURCE_SCHEMA = 'schema';

    /** The document is not well-formed XML (code). */
    public const NOT_WELL_FORMED = 'NOT_WELL_FORMED';

    /** The document declares a DOCTYPE, which invoices never need (code). */
    public const DOCTYPE = 'DOCTYPE';

    /** The document is no invoice in UBL or CII, or a PDF that carries none (code). */
    public const NO_INVOICE = 'NO_INVOICE';

    /** The PDF cannot be read: damaged beyond repair or encrypted (code). */
    public const INVALID_PDF = 'INVALID_PDF';

    /** No rules apply: unknown specification identifier (BT-24), or a profile that needs the other syntax (code). */
    public const NO_SCENARIO = 'NO_SCENARIO';

    /** The document violates its XML schema (code). */
    public const SCHEMA = 'SCHEMA';

    /** A rule set could not be applied to the document - its checks are missing (code, as in KoSIT's report). */
    public const PROCESSING_ERROR = 'PROCESSING_ERROR';

    /** A rule without id (code, as in KoSIT's report). */
    public const UNSPECIFIC = 'UNSPECIFIC';

    public function __construct(
        public Severity $severity,
        /** SOURCE_DOCUMENT, SOURCE_SCHEMA or the rule set, e.g. "en16931-ubl", "xrechnung-cii", "facturx-extended". */
        public string $source,
        /** The id of the rule (e.g. "BR-CO-10"), otherwise one of the constants above. */
        public string $code,
        /** The text of the rule or the error, whitespace normalized. */
        public string $text,
        /** XPath of the node a rule fired on (rules only). */
        public ?string $location = null,
        /** Line of the schema error, where libxml knows it. */
        public ?int $line = null,
        /** The test of the rule, as written in the rule set (rules only). */
        public ?string $test = null,
    ) {}

    /**
     * @return array{severity: string, source: string, code: string, text: string, location: string|null, line: int|null, test: string|null}
     */
    public function toArray(): array
    {
        return [
            'severity' => $this->severity->value,
            'source' => $this->source,
            'code' => $this->code,
            'text' => $this->text,
            'location' => $this->location,
            'line' => $this->line,
            'test' => $this->test,
        ];
    }
}
