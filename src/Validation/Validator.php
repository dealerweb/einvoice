<?php

declare(strict_types=1);

namespace Dealerweb\EInvoice\Validation;

use Dealerweb\EInvoice\Exception\InvalidPdf;
use Dealerweb\EInvoice\Exception\InvalidXml;
use Dealerweb\EInvoice\Exception\UnsupportedDocument;
use Dealerweb\EInvoice\Generation\HybridPdf;
use Dealerweb\EInvoice\Profile;
use Dealerweb\EInvoice\Specification;
use Dealerweb\EInvoice\Syntax;
use Dealerweb\EInvoice\Validation\Schematron\Engine;
use Dealerweb\EInvoice\Validation\XPath\DynamicError;
use Dealerweb\EInvoice\Xml;
use DOMDocument;
use DOMElement;
use LibXMLError;
use RuntimeException;

/**
 * Checks whether an electronic invoice is valid in its profile - its XML, or the XML a ZUGFeRD / Factur-X PDF carries -
 * with the official schemas and rules, evaluated in PHP as the official tools evaluate them: the KoSIT validator
 * (configuration XRechnung 3.0.2 with the CEN rules of EN 16931) for XRechnung and EN 16931, the rules of FeRD for
 * ZUGFeRD / Factur-X, the rules of Peppol BIS Billing 3.0 in an implementation of the package.
 *
 *  1. The XML: well-formed, no DOCTYPE, a UBL Invoice, UBL CreditNote or CII document.
 *  2. The profile: as given, or from the specification identifier (BT-24) - see Scenario::select().
 *  3. The XML schema of the profile. If it finds errors, the rules are not applied (as in KoSIT's validator).
 *  4. The rule sets (Schematron) of the profile. The level of a finding comes from its flag or role as in KoSIT's
 *     report ("fatal"/"error" error, "warning"/"warn" warning, "information"/"info" information, else error), the
 *     custom levels of the KoSIT scenario override it.
 *
 * The document is valid when no message is an error - KoSIT's "accept".
 */
final class Validator
{
    /** @var array<string, Engine> compiled rule sets, loaded once per process */
    private static array $engines = [];

    /**
     * Validates an invoice: its XML - or a ZUGFeRD / Factur-X PDF, whose XML is checked (see validatePdf()).
     *
     * @param Profile|null $profile the profile to check against; null: the one the document names (BT-24)
     */
    public function validate(string $xml, ?Profile $profile = null): Report
    {
        return HybridPdf::isPdf($xml) ? $this->validatePdf($xml, $profile) : $this->validateXml($xml, $profile);
    }

    /**
     * Validates an invoice file: its XML, or a ZUGFeRD / Factur-X PDF (see validatePdf()). A path or a file:// URL, no
     * stream wrapper (http, data, php://filter): the validator does not reach the network.
     *
     * @throws InvalidXml the file cannot be read
     */
    public function validateFile(string $path, ?Profile $profile = null): Report
    {
        return $this->validate(Xml::readFile($path), $profile);
    }

    /**
     * Validates the invoice a ZUGFeRD / Factur-X PDF carries: the XML embedded in it (factur-x.xml, xrechnung.xml or
     * zugferd-invoice.xml), as validate() checks XML. The PDF itself - PDF/A-3, the embedding - is the job of a PDF
     * validator such as veraPDF; the report says so in its notes. A PDF that cannot be read (INVALID_PDF) or carries no
     * invoice (NO_INVOICE) is reported, not thrown.
     *
     * @param Profile|null $profile the profile to check against; null: the one the XML names (BT-24)
     */
    public function validatePdf(string $pdf, ?Profile $profile = null): Report
    {
        try {
            $xml = HybridPdf::invoiceXml($pdf);
        } catch (InvalidPdf $e) {
            return self::rejected(Message::INVALID_PDF, $e->getMessage());
        } catch (UnsupportedDocument $e) {
            return self::rejected(Message::NO_INVOICE, $e->getMessage());
        }

        return $this->validateXml($xml, $profile)->withNote('The XML the PDF carries was checked, not the PDF itself (PDF/A-3, the embedding) - that is the job of a PDF validator such as veraPDF.');
    }

    /**
     * The checks of the XML (see the class): the file of a PDF is XML here, whatever it holds.
     */
    private function validateXml(string $xml, ?Profile $profile): Report
    {
        try {
            $document = Xml::load($xml, LIBXML_NOCDATA);
        } catch (InvalidXml $e) {
            return self::rejected(Message::NOT_WELL_FORMED, $e->getMessage(), $e->documentLine);
        } catch (UnsupportedDocument $e) {
            return self::rejected(Message::DOCTYPE, $e->getMessage());
        }

        try {
            $syntax = Syntax::detect($document);
        } catch (UnsupportedDocument $e) {
            return self::rejected(Message::NO_INVOICE, $e->getMessage());
        }

        $identifier = self::identifier($document, $syntax);
        $specification = Specification::parse($identifier);
        $scenario = Scenario::select($document, $syntax, $specification, $profile, $identifier);
        if (is_string($scenario)) {
            return new Report(null, null, $syntax, $specification, null, [], [
                new Message(Severity::Error, Message::SOURCE_DOCUMENT, Message::NO_SCENARIO, $scenario),
            ]);
        }

        $messages = self::schemaMessages($document, $scenario->schema);
        $ruleSets = [];
        if (array_filter($messages, static fn(Message $message): bool => $message->severity === Severity::Error) === []) {
            foreach ($scenario->ruleSets as $key) {
                $ruleSets[] = $key;
                array_push($messages, ...self::ruleMessages($document, $key, $scenario->levels));
            }
        }

        return new Report($scenario->profile, $scenario->name, $syntax, $specification, basename($scenario->schema), $ruleSets, $messages, $scenario->notes);
    }

    private static function rejected(string $code, string $text, ?int $line = null): Report
    {
        return new Report(null, null, null, null, null, [], [new Message(Severity::Error, Message::SOURCE_DOCUMENT, $code, $text, null, $line)]);
    }

    /**
     * The specification identifier (BT-24): UBL cbc:CustomizationID, CII the ID of the guideline parameter.
     */
    private static function identifier(DOMDocument $document, Syntax $syntax): string
    {
        $path = $syntax === Syntax::Cii
            ? [
                ['urn:un:unece:uncefact:data:standard:CrossIndustryInvoice:100', 'ExchangedDocumentContext'],
                ['urn:un:unece:uncefact:data:standard:ReusableAggregateBusinessInformationEntity:100', 'GuidelineSpecifiedDocumentContextParameter'],
                ['urn:un:unece:uncefact:data:standard:ReusableAggregateBusinessInformationEntity:100', 'ID'],
            ]
            : [['urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2', 'CustomizationID']];
        $element = $document->documentElement;
        foreach ($path as [$namespace, $name]) {
            if ($element === null) {
                break;
            }
            $next = null;
            foreach ($element->childNodes as $child) {
                if ($child instanceof DOMElement && $child->namespaceURI === $namespace && $child->localName === $name) {
                    $next = $child;
                    break;
                }
            }
            $element = $next;
        }

        return $element === null ? '' : $element->textContent;
    }

    /**
     * @return list<Message>
     *
     * @throws RuntimeException the schema cannot be used - an installation problem, not one of the document
     */
    private static function schemaMessages(DOMDocument $document, string $schema): array
    {
        $path = self::root() . '/' . $schema;
        if (! is_file($path)) {
            throw new RuntimeException("Cannot use the schema $schema: the file is missing.");
        }

        // The schema loads its parts from the package - through libxml's own loader, not one the application set for
        // its documents (one that loads nothing, against XXE). The document is parsed already and loads nothing.
        $loader = libxml_get_external_entity_loader();
        libxml_set_external_entity_loader(null);
        try {
            // @: PHP adds a warning "Invalid Schema" to the errors of libxml, which are handled here.
            [$valid, $errors] = Xml::collect(static fn(): bool => @$document->schemaValidate($path));
        } finally {
            libxml_set_external_entity_loader($loader);
        }

        $messages = [];
        foreach ($errors as $error) {
            if (self::isSchemaFailure($error)) {
                throw new RuntimeException("Cannot use the schema $schema: " . trim($error->message));
            }
            if (self::acceptedByXerces($error)) {
                continue;
            }
            $messages[] = new Message(
                $error->level === LIBXML_ERR_WARNING ? Severity::Warning : Severity::Error,
                Message::SOURCE_SCHEMA,
                Message::SCHEMA,
                self::normalize($error->message),
                null,
                self::line($error),
            );
        }
        if (! $valid && $errors === []) {
            throw new RuntimeException("Cannot use the schema $schema: libxml rejects the document without a reason.");
        }

        return $messages;
    }

    /**
     * Whether Xerces - the parser of KoSIT's validator - accepts a value libxml rejects: a decimal or integer of more
     * than 24 digits (a limit of libxml, Xerces has none) or a date, time or dateTime with whitespace around it (its
     * facet whiteSpace="collapse" removes it, libxml does not for these types). No bundled schema restricts these types
     * further by facets.
     */
    private static function acceptedByXerces(LibXMLError $error): bool
    {
        if ($error->code !== 1824 || ! preg_match("/^Element '[^']*'(?:, attribute '[^']*')?: '(.*)' is not a valid value of the atomic type 'xs:(decimal|integer|date|time|dateTime)'\\.$/sD", trim($error->message), $match)) {
            return false;
        }
        $value = trim($match[1], " \t\r\n");

        return match ($match[2]) {
            'decimal' => preg_match('/^[+-]?(\d+(\.\d*)?|\.\d+)$/D', $value) === 1,
            'integer' => preg_match('/^[+-]?\d+$/D', $value) === 1,
            // libxml itself for the value without the whitespace: its rules for dates and times are those of Xerces
            default => $value !== $match[1] && self::libxmlAccepts($match[2], $value),
        };
    }

    private static function libxmlAccepts(string $type, string $value): bool
    {
        $document = new DOMDocument();
        $document->appendChild($document->createElement('value'))->appendChild($document->createTextNode($value));
        $schema = '<xs:schema xmlns:xs="http://www.w3.org/2001/XMLSchema"><xs:element name="value" type="xs:' . $type . '"/></xs:schema>';
        [$valid] = Xml::collect(static fn(): bool => @$document->schemaValidateSource($schema));

        return $valid;
    }

    /**
     * Whether an error comes from reading the schema, not from validating the document: libxml's codes of input and
     * output (15xx) and of the schema parser (17xx, 30xx) - the document's are those of the schema validator (18xx).
     */
    private static function isSchemaFailure(LibXMLError $error): bool
    {
        return str_ends_with(strtolower($error->file), '.xsd')
            || ($error->code >= 1500 && $error->code < 1600)
            || ($error->code >= 1700 && $error->code < 1800)
            || ($error->code >= 3000 && $error->code < 3100);
    }

    /**
     * @param array<string, string> $levels custom levels of the scenario
     *
     * @return list<Message>
     */
    private static function ruleMessages(DOMDocument $document, string $key, array $levels): array
    {
        try {
            $findings = self::engine($key)->run($document);
        } catch (DynamicError $e) {
            return [new Message(Severity::Error, $key, Message::PROCESSING_ERROR, "The rule set could not be applied to the document ({$e->getMessage()}) - its checks are missing.")];
        }

        $messages = [];
        foreach ($findings as $finding) {
            $code = $finding->id ?? Message::UNSPECIFIC;
            $severity = isset($levels[$code]) ? Severity::from($levels[$code]) : self::level($finding->flag, $finding->role);
            $messages[] = new Message($severity, $key, $code, self::normalize($finding->text), $finding->location, null, $finding->test);
        }

        return $messages;
    }

    /**
     * The level of a finding as KoSIT's report derives it from flag and role.
     */
    private static function level(?string $flag, ?string $role): Severity
    {
        $values = array_values(array_filter([$flag, $role], static fn(?string $value): bool => $value !== null));

        return match (true) {
            array_intersect($values, ['fatal', 'error']) !== [] => Severity::Error,
            array_intersect($values, ['warning', 'warn']) !== [] => Severity::Warning,
            array_intersect($values, ['information', 'info']) !== [] => Severity::Information,
            default => Severity::Error,
        };
    }

    private static function engine(string $key): Engine
    {
        if (! isset(self::$engines[$key])) {
            self::$engines[$key] = new Engine(require self::root() . "/resources/compiled/validation/$key.php", self::root());
        }

        return self::$engines[$key];
    }

    private static function normalize(string $text): string
    {
        return trim(preg_replace('/\s+/u', ' ', $text) ?? $text);
    }

    private static function line(LibXMLError $error): ?int
    {
        return $error->line > 0 ? $error->line : null;
    }

    private static function root(): string
    {
        return dirname(__DIR__, 2);
    }
}
