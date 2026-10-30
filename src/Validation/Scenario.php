<?php

declare(strict_types=1);

namespace Dealerweb\EInvoice\Validation;

use Dealerweb\EInvoice\Profile;
use Dealerweb\EInvoice\Specification;
use Dealerweb\EInvoice\Standard;
use Dealerweb\EInvoice\Syntax;
use DOMDocument;
use DOMElement;
use LogicException;

/**
 * What a document is checked against: the XML schema, the rule sets and the levels of their messages - one of the
 * scenarios of the KoSIT configuration, one of Peppol BIS Billing 3.0 or a ZUGFeRD / Factur-X profile
 * (resources/compiled/validation/scenarios.php), for an invoice of the French e-invoicing reform with the French rules
 * BR-FR added.
 *
 * @internal
 */
final readonly class Scenario
{
    private const RSM = 'urn:un:unece:uncefact:data:standard:CrossIndustryInvoice:100';

    private const RAM = 'urn:un:unece:uncefact:data:standard:ReusableAggregateBusinessInformationEntity:100';

    private const CBC = 'urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2';

    /**
     * @param string $schema path of the XML schema, relative to the package
     * @param list<string> $ruleSets the rule sets, in the order they are applied
     * @param array<string, string> $levels rule id => level that overrides the level of its messages
     * @param list<string> $notes what the caller should know about the choice
     */
    public function __construct(
        public string $name,
        public Profile $profile,
        public string $schema,
        public array $ruleSets,
        public array $levels = [],
        public array $notes = [],
    ) {}

    /**
     * The scenario of a document: for a given profile the one of that profile, otherwise the one its specification
     * identifier (BT-24) names - XRechnung by KoSIT's scenarios, which compare the identifier exactly, Peppol BIS
     * Billing 3.0 by its own for every identifier that begins with the one of Peppol (its rule PEPPOL-EN16931-R004
     * judges the rest: a version appended is an error, not a way past the rules of Peppol), ZUGFeRD / Factur-X by the
     * profile in the identifier, the plain EN 16931 identifier as EN 16931 (KoSIT's scenario) - but in the XML of a
     * ZUGFeRD / Factur-X PDF as the profile ZUGFeRD / Factur-X EN 16931, which has that identifier: Factur-X is the
     * PDF with its XML (BR-HYBRID-01 of the specification), an XML of its own that names EN 16931 is an invoice of
     * EN 16931. Other identifiers based on EN 16931 get EN 16931 itself, with a note - not one with whitespace around
     * it: the scenarios do not match it, and an identifier of XRechnung or Peppol it hides is not checked as EN 16931
     * alone. An invoice of the French e-invoicing reform - it names the profile EXTENDED-CTC-FR or gives its
     * processing in a note with the subject code BAR (BR-FR-20) - gets the French rules BR-FR of FNFE-MPE as well,
     * after those of its scenario, as the French validation applies them; the profile EXTENDED-CTC-FR itself is
     * checked with the rules of FNFE-MPE for it, in place of those of EN 16931 or Factur-X EXTENDED.
     *
     * @param string $identifier the specification identifier as the document writes it, whitespace included
     * @param bool $hybrid the XML comes from a ZUGFeRD / Factur-X PDF
     * @param bool $frenchRules the French rules BR-FR apply whatever the document declares (Validator's option)
     *
     * @return self|string the scenario, or why there is none
     */
    public static function select(DOMDocument $document, Syntax $syntax, Specification $specification, ?Profile $profile, string $identifier, bool $hybrid, bool $frenchRules): self|string
    {
        $french = $frenchRules || self::isFrench($document, $syntax, $specification);
        $scenario = self::base($document, $syntax, $specification, $profile, $identifier, $hybrid, $french);

        return $scenario instanceof self && $french ? $scenario->withFrance($syntax) : $scenario;
    }

    /**
     * The scenario of select() without the French rules - its note on another specification names them if they follow.
     *
     * @return self|string
     */
    private static function base(DOMDocument $document, Syntax $syntax, Specification $specification, ?Profile $profile, string $identifier, bool $hybrid, bool $french): self|string
    {
        if ($profile !== null) {
            return self::forProfile($document, $syntax, $specification, $profile);
        }
        // in CII whatever surrounds the identifier, as FeRD's rules, in UBL only without whitespace, as KoSIT compares
        if ($specification->profile === 'EXTENDED-CTC-FR' && ($syntax === Syntax::Cii || $identifier === $specification->identifier)) {
            return self::extendedCtcFr($syntax);
        }

        foreach (self::listed() as $scenario) {
            // in a ZUGFeRD / Factur-X PDF the plain EN 16931 identifier is the profile EN 16931 (FeRD's rules)
            if ($hybrid && $scenario['profile'] === Profile::Core->value && $syntax === Syntax::Cii) {
                continue;
            }
            if (self::matches($document, $scenario)) {
                return self::fromTable($scenario);
            }
        }

        if ($syntax === Syntax::Cii) {
            $ferd = self::ferdProfile($specification, $hybrid);
            if ($ferd !== null) {
                return self::ferd($ferd);
            }
        }

        if ($identifier === $specification->identifier && str_starts_with($identifier, Specification::EN16931)) {
            $scenario = self::core($syntax);

            return new self($scenario->name, $scenario->profile, $scenario->schema, $scenario->ruleSets, $scenario->levels, [self::partly($specification, 'EN 16931', $french)]);
        }

        if ($specification->identifier === '') {
            return 'The document has no specification identifier (BT-24) - no rules apply.';
        }
        // a long identifier is not repeated in full
        $shown = strlen($identifier) > 300 ? mb_strcut($identifier, 0, 300, 'UTF-8') . '...' : $identifier;

        return $identifier === $specification->identifier
            ? "No rules for the specification identifier \"$shown\" (BT-24)."
            : "No rules for the specification identifier \"$shown\" (BT-24) - it has whitespace around it, which the scenarios do not match.";
    }

    /**
     * @return self|string
     */
    private static function forProfile(DOMDocument $document, Syntax $syntax, Specification $specification, Profile $profile): self|string
    {
        if ($profile->isFacturX()) {
            return $syntax === Syntax::Cii ? self::ferd($profile) : "The profile {$profile->label()} needs a CII document, this one is UBL.";
        }
        if ($profile === Profile::Core) {
            return self::core($syntax);
        }

        // XRechnung and Peppol: the scenario of the profile its identifier names, else the first for the root element
        $first = null;
        foreach (self::listed() as $scenario) {
            if ($scenario['profile'] !== $profile->value || $scenario['root'] !== self::name($document->documentElement)) {
                continue;
            }
            if (self::matches($document, $scenario)) {
                return self::fromTable($scenario);
            }
            $first ??= $scenario;
        }
        if ($first === null) {
            return "No {$profile->label()} rules for {$syntax->value}.";
        }
        $scenario = self::fromTable($first);
        $version = $profile === Profile::XRechnung ? 'XRechnung 3.0' : $profile->label();

        return new self($scenario->name, $scenario->profile, $scenario->schema, $scenario->ruleSets, $scenario->levels, [
            "The specification identifier (BT-24) is not the one of $version - checked with the rules of {$profile->label()} as requested.",
        ]);
    }

    /**
     * The KoSIT scenario "EN16931" of the syntax.
     */
    private static function core(Syntax $syntax): self
    {
        $root = match ($syntax) {
            Syntax::UblInvoice => '{urn:oasis:names:specification:ubl:schema:xsd:Invoice-2}Invoice',
            Syntax::UblCreditNote => '{urn:oasis:names:specification:ubl:schema:xsd:CreditNote-2}CreditNote',
            Syntax::Cii => '{urn:un:unece:uncefact:data:standard:CrossIndustryInvoice:100}CrossIndustryInvoice',
        };
        foreach (self::table()['kosit'] as $scenario) {
            if ($scenario['profile'] === Profile::Core->value && $scenario['root'] === $root) {
                return self::fromTable($scenario);
            }
        }

        throw new LogicException("No EN 16931 scenario for {$syntax->value} in scenarios.php.");
    }

    /**
     * The ZUGFeRD / Factur-X profile named by the identifier - the plain EN 16931 identifier only in the XML of a PDF.
     */
    private static function ferdProfile(Specification $specification, bool $hybrid): ?Profile
    {
        if ($specification->standard === Standard::En16931) {
            return $hybrid ? Profile::En16931 : null;
        }
        if ($specification->standard !== Standard::FacturX) {
            return null;
        }

        return Profile::tryFrom((string) $specification->profile);
    }

    private static function ferd(Profile $profile): self
    {
        $ferd = self::table()['ferd'][$profile->value] ?? throw new LogicException("No FeRD scenario for {$profile->value} in scenarios.php.");

        return new self("{$profile->label()} (Factur-X {$ferd['version']})", $profile, $ferd['schema'], $ferd['rules']);
    }

    /**
     * The profile EXTENDED-CTC-FR of the French e-invoicing reform: the XML schema of UBL 2.1, in CII that of ZUGFeRD /
     * Factur-X EXTENDED (FNFE-MPE names it for CII, EXTENDED-CTC-FR being a subset of it), and the rules of FNFE-MPE
     * for the profile in place of those of EN 16931 or Factur-X EXTENDED.
     */
    private static function extendedCtcFr(Syntax $syntax): self
    {
        $base = $syntax === Syntax::Cii ? self::ferd(Profile::Extended) : self::core($syntax);
        $label = match ($syntax) {
            Syntax::UblInvoice => 'UBL Invoice',
            Syntax::UblCreditNote => 'UBL CreditNote',
            Syntax::Cii => 'CII',
        };

        return new self("EXTENDED-CTC-FR ($label)", $base->profile, $base->schema, [self::table()['france']['extended'][$syntax === Syntax::Cii ? 'cii' : 'ubl']]);
    }

    /**
     * This scenario with the French rules BR-FR of the syntax after its own rules - the third step of the French
     * validation, after the schema and the rules of the profile (FNFE-MPE).
     */
    private function withFrance(Syntax $syntax): self
    {
        $france = self::table()['france'];

        return new self("{$this->name} + French rules BR-FR (FNFE-MPE {$france['version']})", $this->profile, $this->schema, [...$this->ruleSets, $france[$syntax === Syntax::Cii ? 'cii' : 'ubl']], $this->levels, $this->notes);
    }

    /**
     * Whether the document is an invoice of the French e-invoicing reform: it names the profile EXTENDED-CTC-FR (BT-24)
     * or gives its processing in a note with the subject code BAR (BT-21, BR-FR-20). The note is found as the rules BR-FR
     * find it: in CII a note of the document with that subject code, in UBL "#BAR#" in the notes of the invoice joined
     * by "#".
     */
    private static function isFrench(DOMDocument $document, Syntax $syntax, Specification $specification): bool
    {
        $root = $document->documentElement;
        if ($specification->profile === 'EXTENDED-CTC-FR') {
            return true;
        }
        if ($root === null) {
            return false;
        }
        if ($syntax !== Syntax::Cii) {
            $notes = array_map(static fn(DOMElement $note): string => $note->textContent, self::children($root, self::CBC, 'Note'));

            return str_contains(implode('#', $notes), '#BAR#');
        }
        foreach (self::children($root, self::RSM, 'ExchangedDocument') as $exchanged) {
            foreach (self::children($exchanged, self::RAM, 'IncludedNote') as $note) {
                foreach (self::children($note, self::RAM, 'SubjectCode') as $code) {
                    if ($code->textContent === 'BAR') {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    /**
     * @return list<DOMElement>
     */
    private static function children(DOMElement $element, string $namespace, string $name): array
    {
        $children = [];
        foreach ($element->childNodes as $child) {
            if ($child instanceof DOMElement && $child->namespaceURI === $namespace && $child->localName === $name) {
                $children[] = $child;
            }
        }

        return $children;
    }

    /**
     * @param array<string, mixed> $scenario
     */
    private static function fromTable(array $scenario): self
    {
        return new self($scenario['name'], Profile::from($scenario['profile']), $scenario['schema'], $scenario['rules'], $scenario['levels']);
    }

    private static function partly(Specification $specification, string $checked, bool $french): string
    {
        $checked .= $french ? ' and the French rules BR-FR' : '';

        return "The document follows {$specification->name()} - checked against $checked, the additional rules of that specification are not included.";
    }

    /**
     * The match of KoSIT's scenarios, the same for those of Peppol: the root element, the element path below it and
     * the identifier compared with the string value of an element or with one of its text nodes - for Peppol with the
     * beginning of the string value, which has no whitespace around it.
     *
     * @param array<string, mixed> $scenario
     */
    private static function matches(DOMDocument $document, array $scenario): bool
    {
        $root = $document->documentElement;
        if ($root === null || self::name($root) !== $scenario['root']) {
            return false;
        }
        $elements = [$root];
        foreach ($scenario['path'] as $step) {
            $next = [];
            foreach ($elements as $element) {
                foreach ($element->childNodes as $child) {
                    if ($child instanceof DOMElement && self::name($child) === $step) {
                        $next[] = $child;
                    }
                }
            }
            $elements = $next;
        }
        foreach ($elements as $element) {
            if ($scenario['compare'] === 'prefix') {
                $value = $element->textContent;
                if ($value === trim($value) && str_starts_with($value, $scenario['identifier'])) {
                    return true;
                }
                continue;
            }
            if ($scenario['compare'] === 'string') {
                if ($element->textContent === $scenario['identifier']) {
                    return true;
                }
                continue;
            }
            foreach ($element->childNodes as $child) {
                if (($child->nodeType === XML_TEXT_NODE || $child->nodeType === XML_CDATA_SECTION_NODE) && $child->nodeValue === $scenario['identifier']) {
                    return true;
                }
            }
        }

        return false;
    }

    private static function name(?DOMElement $element): string
    {
        return $element === null ? '' : '{' . ($element->namespaceURI ?? '') . '}' . $element->localName;
    }

    /**
     * The scenarios selected by the specification identifier: KoSIT's in their order, then those of Peppol.
     *
     * @return list<array<string, mixed>>
     */
    private static function listed(): array
    {
        return [...self::table()['kosit'], ...self::table()['peppol']];
    }

    /**
     * @return array{kosit: list<array<string, mixed>>, peppol: list<array<string, mixed>>, ferd: array<string, array{version: string, schema: string, rules: list<string>}>, france: array{version: string, ubl: string, cii: string, extended: array{ubl: string, cii: string}}}
     */
    private static function table(): array
    {
        static $table = null;

        return $table ??= require dirname(__DIR__, 2) . '/resources/compiled/validation/scenarios.php';
    }
}
