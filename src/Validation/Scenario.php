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
 * (resources/compiled/validation/scenarios.php).
 *
 * @internal
 */
final readonly class Scenario
{
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
     * identifier (BT-24) names - XRechnung by KoSIT's scenarios and Peppol BIS Billing 3.0 by its own (both compare
     * the identifier exactly), ZUGFeRD / Factur-X by the profile in the identifier, the plain EN 16931 identifier in
     * CII as ZUGFeRD / Factur-X EN 16931 and in UBL as EN 16931 (KoSIT's scenario). Other identifiers based on
     * EN 16931 get EN 16931 itself, with a note - not one with whitespace around it: the scenarios do not match it,
     * and an identifier of XRechnung or Peppol it hides is not checked as EN 16931 alone.
     *
     * @param string $identifier the specification identifier as the document writes it, whitespace included
     *
     * @return self|string the scenario, or why there is none
     */
    public static function select(DOMDocument $document, Syntax $syntax, Specification $specification, ?Profile $profile, string $identifier): self|string
    {
        if ($profile !== null) {
            return self::forProfile($document, $syntax, $specification, $profile);
        }

        foreach (self::listed() as $scenario) {
            // a CII document with the plain EN 16931 identifier is a ZUGFeRD / Factur-X EN 16931 invoice (FeRD's rules)
            if ($scenario['profile'] === Profile::Core->value && $syntax === Syntax::Cii) {
                continue;
            }
            if (self::matches($document, $scenario)) {
                return self::fromTable($scenario);
            }
        }

        if ($syntax === Syntax::Cii) {
            $ferd = self::ferdProfile($specification);
            if ($ferd !== null) {
                $scenario = self::ferd($ferd);

                return $specification->profile === 'EXTENDED-CTC-FR'
                    ? new self($scenario->name, $scenario->profile, $scenario->schema, $scenario->ruleSets, $scenario->levels, [self::partly($specification, 'ZUGFeRD / Factur-X EXTENDED')])
                    : $scenario;
            }
        }

        if ($identifier === $specification->identifier && str_starts_with($identifier, Specification::EN16931)) {
            $scenario = self::core($syntax);

            return new self($scenario->name, $scenario->profile, $scenario->schema, $scenario->ruleSets, $scenario->levels, [self::partly($specification, 'EN 16931')]);
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
     * The ZUGFeRD / Factur-X profile named by the identifier, the plain EN 16931 identifier included.
     */
    private static function ferdProfile(Specification $specification): ?Profile
    {
        if ($specification->standard === Standard::En16931) {
            return Profile::En16931;
        }
        if ($specification->standard !== Standard::FacturX) {
            return null;
        }

        return $specification->profile === 'EXTENDED-CTC-FR' ? Profile::Extended : Profile::tryFrom((string) $specification->profile);
    }

    private static function ferd(Profile $profile): self
    {
        $ferd = self::table()['ferd'][$profile->value] ?? throw new LogicException("No FeRD scenario for {$profile->value} in scenarios.php.");

        return new self("{$profile->label()} (Factur-X {$ferd['version']})", $profile, $ferd['schema'], $ferd['rules']);
    }

    /**
     * @param array<string, mixed> $scenario
     */
    private static function fromTable(array $scenario): self
    {
        return new self($scenario['name'], Profile::from($scenario['profile']), $scenario['schema'], $scenario['rules'], $scenario['levels']);
    }

    private static function partly(Specification $specification, string $checked): string
    {
        return "The document follows {$specification->name()} - checked against $checked, the additional rules of that specification are not included.";
    }

    /**
     * The match of KoSIT's scenarios, the same for those of Peppol: the root element, the element path below it and
     * the identifier compared with the string value of an element or with one of its text nodes.
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
     * @return array{kosit: list<array<string, mixed>>, peppol: list<array<string, mixed>>, ferd: array<string, array{version: string, schema: string, rules: list<string>}>}
     */
    private static function table(): array
    {
        static $table = null;

        return $table ??= require dirname(__DIR__, 2) . '/resources/compiled/validation/scenarios.php';
    }
}
