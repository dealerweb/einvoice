<?php

declare(strict_types=1);

namespace Dealerweb\EInvoice\Validation\XPath;

use DOMAttr;
use DOMDocument;
use DOMElement;
use DOMNode;

/**
 * Everything the axes of XPath need from a document, collected in one walk: document order, children, attributes,
 * parents and the elements of each name. Keys are spl_object_id() of the DOM nodes - PHP hands out the same object for
 * a node as long as one exists, so the tree holds every node it has seen.
 *
 * The data model of XPath without schema: the document node, elements, attributes (namespace declarations are none),
 * text, comments, processing instructions. The document is parsed with LIBXML_NOCDATA, so CDATA sections are text
 * and adjacent text is one node, as in XPath.
 *
 * @internal
 */
final class Tree
{
    /** @var list<DOMNode> the nodes without attributes, in document order, the document node first */
    public array $nodes = [];

    /** @var array<int, int> node => number in document order (attributes included), unique across all trees */
    public array $order = [];

    /** @var array<int, int> node => its index in $nodes */
    public array $index = [];

    /** @var array<int, int> node => index in $nodes of its last descendant (itself without descendants) */
    public array $last = [];

    /** @var array<int, list<DOMNode>> element or document => its children */
    public array $children = [];

    /** @var array<int, list<DOMAttr>> element => its attributes */
    public array $attributes = [];

    /** @var array<int, DOMNode> node => parent; the element for an attribute */
    public array $parent = [];

    /** @var array<string, list<DOMElement>> "{uri}local" => the elements of that name in document order */
    public array $elements = [];

    /**
     * @param int $base first order number, so that the nodes of several documents are ordered, too
     */
    public function __construct(public readonly DOMDocument $document, int $base = 0)
    {
        $counter = $base;
        $this->visit($document, $counter);
    }

    private function visit(DOMNode $node, int &$counter): void
    {
        $id = spl_object_id($node);
        $this->order[$id] = $counter++;
        $this->index[$id] = count($this->nodes);
        $this->nodes[] = $node;

        if ($node instanceof DOMElement) {
            $this->elements['{' . ($node->namespaceURI ?? '') . '}' . $node->localName][] = $node;
            $attributes = [];
            foreach ($node->attributes ?? [] as $attribute) {
                $attributeId = spl_object_id($attribute);
                $this->order[$attributeId] = $counter++;
                $this->parent[$attributeId] = $node;
                $attributes[] = $attribute;
            }
            $this->attributes[$id] = $attributes;
        }

        if ($node instanceof DOMElement || $node instanceof DOMDocument) {
            $children = [];
            foreach ($node->childNodes as $child) {
                if (! in_array($child->nodeType, [XML_ELEMENT_NODE, XML_TEXT_NODE, XML_CDATA_SECTION_NODE, XML_COMMENT_NODE, XML_PI_NODE], true)) {
                    continue;
                }
                $children[] = $child;
                $this->parent[spl_object_id($child)] = $node;
                $this->visit($child, $counter);
            }
            $this->children[$id] = $children;
        }

        $this->last[$id] = count($this->nodes) - 1;
    }
}
