<?php

declare(strict_types=1);

namespace Dealerweb\EInvoice\Generation;

use Dealerweb\EInvoice\Rules;
use DOMAttr;
use DOMDocument;
use DOMElement;
use DOMXPath;
use InvalidArgumentException;

/**
 * Reads a document into the nodes of its tree (Tree) - every element and attribute with its id, in document order,
 * values as written. What the tree does not know (elements outside the syntax the tree covers, attributes of other
 * namespaces) is left out and listed by unknown() - unless it carries no information, by the rules of
 * Document::unmapped(): the currency of an amount that is the invoice or VAT accounting currency, the provenance of a
 * code or identifier, the list or scheme id of an element that is read, xsi:schemaLocation, the version of UBL.
 *
 * Elements with the same name in the same place are told apart like the workbook does: by the fixed values in their
 * subtree (ChargeIndicator of allowances and charges, schemeID of tax registrations, TypeCode of additional documents,
 * listID of a non-VAT tax code) or by the currency of an amount (VAT total in invoice or in accounting currency - in UBL
 * the currency of the amount in the VAT total).
 *
 * @internal
 */
final class TreeReader
{
    /** @var list<string> */
    private array $unknown = [];

    /** @var array<string, string> values of the currency nodes (BT-5 invoice currency, BT-6 accounting currency) */
    private array $currencies = [];

    public function __construct(private readonly Tree $tree) {}

    /**
     * @return list<Node> the children of the root element
     */
    public function read(DOMDocument $document): array
    {
        $root = $document->documentElement;
        $expected = $this->tree->root();
        $local = str_contains($expected['name'], ':') ? substr($expected['name'], strpos($expected['name'], ':') + 1) : $expected['name'];
        if ($root === null || $root->namespaceURI !== $expected['namespace'] || $root->localName !== $local) {
            throw new InvalidArgumentException("The document is no {$this->tree->label()} invoice ({$expected['name']}).");
        }

        $this->unknown = [];
        $this->currencies = [];
        $xpath = new DOMXPath($document);
        foreach ($this->tree->declared() as $prefix) {
            $xpath->registerNamespace($prefix, $this->tree->namespace($prefix));
        }
        foreach (['BT-5', 'BT-6'] as $id) {
            $value = $xpath->evaluate('string(/*/' . $this->tree->node($id)['path'] . ')');
            if (is_string($value) && trim($value) !== '') {
                $this->currencies[$id] = trim($value);
            }
        }

        return $this->children($root, null, '/' . $root->nodeName);
    }

    /**
     * The paths of the elements and attributes the last read() left out.
     *
     * @return list<string>
     */
    public function unknown(): array
    {
        return $this->unknown;
    }

    /**
     * The attributes and child elements of an element as nodes.
     *
     * @return list<Node>
     */
    private function children(DOMElement $element, ?string $id, string $path): array
    {
        $nodes = [];
        foreach ($element->attributes as $attribute) {
            $child = $attribute->namespaceURI === null ? $this->candidate($id, '@' . $attribute->localName) : null;
            if ($child === null) {
                if (! $this->carriesNothing($attribute)) {
                    $this->unknown[] = "$path/@{$attribute->nodeName}";
                }
                continue;
            }
            $nodes[] = new Node($child, $attribute->value);
        }

        $position = [];
        foreach ($element->childNodes as $childElement) {
            if (! $childElement instanceof DOMElement) {
                continue;
            }
            $prefix = $this->tree->prefix((string) $childElement->namespaceURI);
            $name = $prefix === null ? null : $prefix . ':' . $childElement->localName;
            $position[$childElement->nodeName] = ($position[$childElement->nodeName] ?? 0) + 1;
            $childPath = "$path/{$childElement->nodeName}[{$position[$childElement->nodeName]}]";

            $child = $name === null ? null : $this->choose($childElement, $this->candidates($id, $name));
            if ($child === null) {
                if (! Rules::isSyntaxVersion($childElement)) {
                    $this->unknown[] = $childPath;
                }
                continue;
            }

            $node = new Node($child);
            $node->children = $this->children($childElement, $child, $childPath);
            if (! $this->hasElementChildren($childElement)) {
                $codec = $this->tree->node($child)['codec'] ?? null;
                if ($codec === null) {
                    $node->value = $childElement->textContent;
                } else {
                    // A text of several fields: each part as the virtual child of its name.
                    foreach (Codec::decode($codec, $childElement->textContent) as $part => $value) {
                        $node->children[] = new Node($this->candidate($child, $part) ?? throw new InvalidArgumentException("$child has no part $part."), $value);
                    }
                }
            }
            $nodes[] = $node;
        }

        return $nodes;
    }

    /**
     * An attribute the tree does not know that holds nothing the model could lose (Rules::isNoise), or the list or
     * scheme id of an element that is read - its value is what the model holds. Attributes are read only of the
     * elements the tree knows.
     */
    private function carriesNothing(DOMAttr $attribute): bool
    {
        return Rules::isNoise($attribute, array_values($this->currencies)) || in_array($attribute->localName, ['listID', 'schemeID'], true);
    }

    /**
     * The one candidate for an attribute (attributes of an element cannot repeat).
     */
    private function candidate(?string $id, string $name): ?string
    {
        return $this->candidates($id, $name)[0] ?? null;
    }

    /**
     * @return list<string> the child ids of a node with the given name
     */
    private function candidates(?string $id, string $name): array
    {
        return array_values(array_filter(
            $this->tree->children($id),
            fn(string $child): bool => $this->tree->node($child)['name'] === $name
        ));
    }

    /**
     * Which of the nodes with the element's name the element is: the one whose fixed values or currency it has,
     * otherwise the one without fixed values.
     *
     * @param list<string> $candidates
     */
    private function choose(DOMElement $element, array $candidates): ?string
    {
        if (count($candidates) < 2) {
            return $candidates[0] ?? null;
        }

        $default = null;
        $distinguishing = $this->tree->distinguishing($candidates);
        foreach ($candidates as $candidate) {
            $node = $this->tree->node($candidate);
            if (isset($node['currency'])) {
                if (($this->currencies[$node['currency']] ?? null) === self::currency($element)) {
                    return $candidate;
                }
                $default ??= $candidate;
                continue;
            }

            $fixed = $distinguishing[$candidate];
            if ($fixed === []) {
                $default ??= $candidate;
            } elseif ($this->hasFixed($element, $fixed)) {
                return $candidate;
            }
        }

        return $default;
    }

    /**
     * The currency of an element: its own for an amount, the one of the amount in it for a total (cac:TaxTotal).
     */
    private static function currency(DOMElement $element): string
    {
        if ($element->hasAttribute('currencyID')) {
            return trim($element->getAttribute('currencyID'));
        }
        foreach ($element->childNodes as $child) {
            if ($child instanceof DOMElement && $child->hasAttribute('currencyID')) {
                return trim($child->getAttribute('currencyID'));
            }
        }

        return '';
    }

    /**
     * Whether the subtree of an element holds the given fixed values (paths like "/ram:TypeCode" or
     * "/ram:ID/@schemeID"; indicators compared as xs:boolean).
     *
     * @param array<string, string> $fixed
     */
    private function hasFixed(DOMElement $element, array $fixed): bool
    {
        foreach ($fixed as $path => $expected) {
            $current = $element;
            $actual = null;
            foreach (array_slice(explode('/', $path), 1) as $step) {
                if (str_starts_with($step, '@')) {
                    $actual = $current->hasAttribute(substr($step, 1)) ? $current->getAttribute(substr($step, 1)) : null;
                    break;
                }
                [$prefix, $local] = explode(':', $step);
                $next = null;
                foreach ($current->childNodes as $child) {
                    if ($child instanceof DOMElement && $child->localName === $local && $child->namespaceURI === $this->tree->namespace($prefix)) {
                        $next = $child;
                        break;
                    }
                }
                if ($next === null) {
                    return false;
                }
                $current = $next;
                $actual = $current->textContent;
            }
            if ($actual === null || self::normalize($actual) !== self::normalize($expected)) {
                return false;
            }
        }

        return true;
    }

    private static function normalize(string $value): string
    {
        $value = trim($value);

        return match (strtolower($value)) {
            'true', '1' => 'true',
            'false', '0' => 'false',
            default => $value,
        };
    }

    private function hasElementChildren(DOMElement $element): bool
    {
        foreach ($element->childNodes as $child) {
            if ($child instanceof DOMElement) {
                return true;
            }
        }

        return false;
    }
}
