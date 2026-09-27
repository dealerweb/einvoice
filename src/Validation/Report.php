<?php

declare(strict_types=1);

namespace Dealerweb\EInvoice\Validation;

use Dealerweb\EInvoice\Profile;
use Dealerweb\EInvoice\Specification;
use Dealerweb\EInvoice\Syntax;

/**
 * The result of Validator::validate(): what the document was checked against and what was found.
 */
final readonly class Report
{
    /**
     * @param list<Message> $messages in the order of the checks: document, schema, rule sets
     * @param list<string> $ruleSets the rule sets applied, e.g. "en16931-ubl", "xrechnung-ubl"
     * @param list<string> $notes remarks of the validator on the choice of the rules (no findings)
     */
    public function __construct(
        private ?Profile $profile,
        private ?string $scenario,
        private ?Syntax $syntax,
        private ?Specification $specification,
        private ?string $schema,
        private array $ruleSets,
        private array $messages,
        private array $notes = [],
    ) {}

    /**
     * No message is an error: the document meets its profile - KoSIT's "accept". Warnings and information do not
     * count.
     */
    public function isValid(): bool
    {
        return $this->profile !== null && $this->errors() === [];
    }

    /**
     * Valid, and the profile makes it an invoice in the sense of EN 16931 - in Germany an electronic invoice
     * (§ 14 UStG). ZUGFeRD / Factur-X MINIMUM and BASIC WL can be valid without being one.
     */
    public function isEInvoice(): bool
    {
        return $this->isValid() && $this->profile?->isEInvoice() === true;
    }

    /**
     * The profile the document was checked against; null when no rules applied (see the messages).
     */
    public function profile(): ?Profile
    {
        return $this->profile;
    }

    /**
     * Name of the scenario, e.g. "EN16931 XRechnung (UBL Invoice)" (KoSIT) or "ZUGFeRD / Factur-X EXTENDED
     * (Factur-X 1.09.2)".
     */
    public function scenario(): ?string
    {
        return $this->scenario;
    }

    public function syntax(): ?Syntax
    {
        return $this->syntax;
    }

    /**
     * The specification identifier (BT-24) as the document gives it.
     */
    public function specification(): ?Specification
    {
        return $this->specification;
    }

    /**
     * File name of the XML schema applied, e.g. "UBL-Invoice-2.1.xsd".
     */
    public function schema(): ?string
    {
        return $this->schema;
    }

    /**
     * The rule sets applied, e.g. ["en16931-cii", "xrechnung-cii"] - empty when the schema already failed.
     *
     * @return list<string>
     */
    public function ruleSets(): array
    {
        return $this->ruleSets;
    }

    /**
     * @return list<Message>
     */
    public function messages(?Severity $severity = null): array
    {
        return $severity === null
            ? $this->messages
            : array_values(array_filter($this->messages, static fn(Message $message): bool => $message->severity === $severity));
    }

    /**
     * @return list<Message>
     */
    public function errors(): array
    {
        return $this->messages(Severity::Error);
    }

    /**
     * @return list<Message>
     */
    public function warnings(): array
    {
        return $this->messages(Severity::Warning);
    }

    /**
     * Remarks of the validator on the rules it chose, e.g. that a specification's own rules are not included - and on
     * what it checked of a PDF.
     *
     * @return list<string>
     */
    public function notes(): array
    {
        return $this->notes;
    }

    /**
     * The report with a remark in front of the others.
     *
     * @internal used by the validator for the XML of a PDF
     */
    public function withNote(string $note): self
    {
        return new self($this->profile, $this->scenario, $this->syntax, $this->specification, $this->schema, $this->ruleSets, $this->messages, [$note, ...$this->notes]);
    }

    /**
     * @return array{valid: bool, eInvoice: bool, profile: string|null, scenario: string|null, syntax: string|null, specification: string|null, schema: string|null, ruleSets: list<string>, messages: list<array<string, mixed>>, notes: list<string>}
     */
    public function toArray(): array
    {
        return [
            'valid' => $this->isValid(),
            'eInvoice' => $this->isEInvoice(),
            'profile' => $this->profile?->value,
            'scenario' => $this->scenario,
            'syntax' => $this->syntax?->value,
            'specification' => $this->specification?->identifier,
            'schema' => $this->schema,
            'ruleSets' => $this->ruleSets,
            'messages' => array_map(static fn(Message $message): array => $message->toArray(), $this->messages),
            'notes' => $this->notes,
        ];
    }
}
