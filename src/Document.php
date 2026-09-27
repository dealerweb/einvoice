<?php

declare(strict_types=1);

namespace Dealerweb\EInvoice;

use Dealerweb\EInvoice\Exception\InvalidXml;
use Dealerweb\EInvoice\Exception\UnsupportedDocument;
use Dealerweb\EInvoice\Generation\Generator;
use Dealerweb\EInvoice\Kosit\Transformer;
use Dealerweb\EInvoice\Model\Attachment;
use DOMDocument;
use DOMElement;
use DOMXPath;
use InvalidArgumentException;
use JsonException;

/**
 * An invoice or credit note read from UBL or CII (XRechnung, ZUGFeRD 2.x, Factur-X, Peppol BIS) into the semantic
 * model of KoSIT - the business terms of EN 16931, by the official mapping. The base of the rendering, of what
 * EN 16931 does not hold (unmapped()) and of writing a read invoice by its fields; an invoice for the user is the
 * model (Invoice).
 *
 * @internal
 */
final class Document
{
    /**
     * @param list<UnmappedValue> $unmapped
     */
    private function __construct(
        private readonly Syntax $syntax,
        private readonly DOMDocument $intermediate,
        private readonly array $unmapped,
    ) {}

    /**
     * Reads an invoice from its XML (UBL Invoice, UBL CreditNote or CII).
     *
     * @throws InvalidXml the document is empty or not well-formed XML
     * @throws UnsupportedDocument the XML is no invoice in a supported syntax, e.g. ZUGFeRD 1.0, or declares a DOCTYPE
     */
    public static function fromXml(string $xml): self
    {
        $source = Xml::load($xml);
        $syntax = Syntax::detect($source);
        $transformer = new Transformer($syntax->program(), $source);
        $intermediate = $transformer->transform();

        return new self($syntax, $intermediate, Coverage::unmapped($source, $intermediate, $transformer->consumed(), $transformer->index()));
    }

    /**
     * Reads an invoice from an XML file. A path or a file:// URL, no stream wrapper (http, data, php://filter):
     * reading does not reach the network.
     *
     * @throws InvalidXml the file cannot be read, is empty or is not well-formed XML
     * @throws UnsupportedDocument the XML is no invoice in a supported syntax, e.g. ZUGFeRD 1.0, or declares a DOCTYPE
     */
    public static function fromFile(string $path): self
    {
        return self::fromXml(Xml::readFile($path));
    }

    /**
     * The document of an invoice of the model: the one it was read from, as long as the invoice is unchanged -
     * everything it holds, as delivered -, otherwise the invoice written as the model holds it (Generator::preview()).
     *
     * @throws InvalidArgumentException the invoice holds content no syntax has a place for together (the sub lines of
     *                                  the XRechnung extension beside fields of ZUGFeRD / Factur-X EXTENDED)
     */
    public static function of(Invoice $invoice): self
    {
        return self::fromXml($invoice->sourceXml() ?? Generator::preview($invoice));
    }

    public function syntax(): Syntax
    {
        return $this->syntax;
    }

    /**
     * The specification the invoice declares (BT-24): XRechnung 3.0, ZUGFeRD / Factur-X EXTENDED, ...
     */
    public function specification(): Specification
    {
        return Specification::parse($this->value('BT-24'));
    }

    /**
     * Invoice type code (BT-3, UNTDID 1001), e.g. 380 invoice, 381 credit note, 384 corrected invoice.
     */
    public function typeCode(): ?string
    {
        $code = $this->value('BT-3');

        return $code === null ? null : trim($code);
    }

    public function isCreditNote(): bool
    {
        return $this->syntax === Syntax::UblCreditNote || in_array($this->typeCode(), Rules::CREDIT_NOTE_CODES, true);
    }

    /**
     * Issued by the buyer on behalf of the seller (self-billing, in German tax law a "Gutschrift").
     */
    public function isSelfBilled(): bool
    {
        return in_array($this->typeCode(), Rules::SELF_BILLED_CODES, true);
    }

    /**
     * The KoSIT intermediate document (namespace urn:ce.eu:en16931:2017:xoev-de:kosit:standard:xrechnung-1).
     */
    public function intermediate(): DOMDocument
    {
        return clone $this->intermediate;
    }

    /**
     * First value of a business term, e.g. value('BT-1') for the invoice number.
     */
    public function value(string $id): ?string
    {
        return $this->values($id)[0] ?? null;
    }

    /**
     * All values of a business term in document order, e.g. values('BT-153') for all item names.
     *
     * @return list<string>
     */
    public function values(string $id): array
    {
        $xpath = new DOMXPath($this->intermediate);
        $xpath->registerNamespace('xr', Transformer::XR_NAMESPACE);

        $values = [];
        foreach ($xpath->query('//*[@xr:id = "' . str_replace('"', '', $id) . '"]') ?: [] as $element) {
            if ($element instanceof DOMElement) {
                $values[] = $element->textContent;
            }
        }

        return $values;
    }

    /**
     * The invoice as nested arrays with the element names of the semantic model. Values with
     * attributes (scheme identifier, mime code, file name) become ['value' => ..., attribute => ...];
     * with $withMeta every value and group also carries its BT/BG id and source path.
     *
     * @return array<string, mixed>
     */
    public function toArray(bool $withMeta = false): array
    {
        $root = $this->intermediate->documentElement;

        return $root === null ? [] : $this->group($root, SemanticModel::rootType(), $withMeta);
    }

    /**
     * The invoice as JSON, see toArray().
     *
     * @throws JsonException where the invoice nests deeper than 512 levels (sub-lines in sub-lines ...)
     */
    public function toJson(bool $withMeta = false, int $flags = JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES): string
    {
        return json_encode($this->toArray($withMeta), $flags | JSON_THROW_ON_ERROR);
    }

    /**
     * Information of the invoice that the EN 16931 model does not carry.
     *
     * @return list<UnmappedValue>
     */
    public function unmapped(): array
    {
        return $this->unmapped;
    }

    /**
     * The additional supporting documents (BG-24) in the order of the invoice: embedded files (BT-125) and
     * addresses of external documents (BT-124, never opened).
     *
     * @return list<Attachment>
     */
    public function attachments(): array
    {
        $attachments = [];
        foreach (ModelValues::groups($this->toArray()['ADDITIONAL_SUPPORTING_DOCUMENTS'] ?? null) as $document) {
            $attached = $document['Attached_document'] ?? null;
            $content = is_array($attached) ? ($attached['value'] ?? null) : $attached;

            $attachments[] = new Attachment(
                id: ModelValues::value($document['Supporting_document_reference'] ?? null),
                description: ModelValues::text($document['Supporting_document_description'] ?? null),
                url: ModelValues::value($document['External_document_location'] ?? null),
                base64: is_string($content) ? $content : null,
                mimeCode: ModelValues::attribute($attached, 'mime_code'),
                filename: ModelValues::attribute($attached, 'filename'),
            );
        }

        return $attachments;
    }

    /**
     * @return array<string, mixed>
     */
    private function group(DOMElement $element, string $type, bool $withMeta): array
    {
        $result = $withMeta ? $this->meta($element) : [];
        $children = SemanticModel::children($type);
        // Values of the elements that occur more than once, by name - their key in $result keeps the position.
        $lists = [];

        foreach ($element->childNodes as $child) {
            if (! $child instanceof DOMElement) {
                continue;
            }

            $name = $child->localName ?? $child->nodeName;
            $spec = $children[$name] ?? null;
            $value = $spec !== null && SemanticModel::isGroup($spec['type'])
                ? $this->group($child, $spec['type'], $withMeta)
                : $this->leaf($child, $withMeta);

            if (SemanticModel::isMultiple($name, $spec)) {
                $result[$name] ??= [];
                $lists[$name][] = $value;
            } elseif (array_key_exists($name, $result)) {
                // Repeated although the model expects it once - keep every value rather than overwrite one.
                $lists[$name] ??= [$result[$name]];
                $lists[$name][] = $value;
            } else {
                $result[$name] = $value;
            }
        }

        return array_replace($result, $lists);
    }

    /**
     * @return string|array<string, string>
     */
    private function leaf(DOMElement $element, bool $withMeta): string|array
    {
        $attributes = [];
        foreach ($element->attributes as $attribute) {
            if ($attribute->namespaceURI !== Transformer::XR_NAMESPACE) {
                $attributes[$attribute->nodeName] = $attribute->value;
            }
        }

        if (! $withMeta && $attributes === []) {
            return $element->textContent;
        }

        return ($withMeta ? $this->meta($element) : []) + ['value' => $element->textContent] + $attributes;
    }

    /**
     * @return array{_id: string, _src: string}
     */
    private function meta(DOMElement $element): array
    {
        return [
            '_id' => $element->getAttributeNS(Transformer::XR_NAMESPACE, 'id'),
            '_src' => $element->getAttributeNS(Transformer::XR_NAMESPACE, 'src'),
        ];
    }
}
