<?php

declare(strict_types=1);

namespace Dealerweb\EInvoice\Kosit;

use DOMAttr;
use DOMDocument;
use DOMElement;
use DOMNode;

/**
 * Key, source path and document position of every node of a source document, computed in one pass.
 *
 * Asking libxml for the path of a node (getNodePath) or counting its siblings costs time in
 * proportion to the number of siblings - done for every node, reading an invoice with thousands of
 * lines took minutes. The index answers each of these questions at once.
 *
 * The index keeps every node it has seen: as long as a PHP object of a DOM node lives, DOM hands out
 * that same object for the node, so its object id identifies the node.
 *
 * @internal
 */
final class NodeIndex
{
    /** @var array<int, array{key: string, path: string, order: int}> by object id */
    private array $entries = [];

    /** @var list<DOMNode> the indexed nodes, kept alive so that their object ids stay unique */
    private array $nodes = [];

    public function __construct(DOMDocument $document)
    {
        $root = $document->documentElement;
        if ($root !== null) {
            $this->addElement($root, '/' . $root->nodeName);
        }
    }

    /**
     * A key that identifies the node within the document.
     */
    public function key(DOMNode $node): string
    {
        return $this->entries[spl_object_id($node)]['key'] ?? ($node->getNodePath() ?? spl_object_hash($node));
    }

    /**
     * The path of the node with qualified names as written in the document and a position predicate
     * wherever siblings share the name - xr:src-path() of KoSIT's common-xr.xsl:
     * "/rsm:CrossIndustryInvoice/rsm:SupplyChainTradeTransaction/ram:IncludedSupplyChainTradeLineItem[2]".
     * Attributes end in "/@name".
     */
    public function path(DOMNode $node): string
    {
        return $this->entries[spl_object_id($node)]['path'] ?? self::computePath($node);
    }

    /**
     * The nodes in document order: an element, then its attributes, then its content.
     *
     * @param list<DOMNode> $nodes
     * @return list<DOMNode>
     */
    public function documentOrder(array $nodes): array
    {
        usort($nodes, fn(DOMNode $a, DOMNode $b): int => $this->order($a) <=> $this->order($b));

        return $nodes;
    }

    private function order(DOMNode $node): int
    {
        return $this->entries[spl_object_id($node)]['order'] ?? PHP_INT_MAX;
    }

    private function addElement(DOMElement $element, string $path): void
    {
        $this->add($element, $path);

        foreach ($element->attributes as $attribute) {
            $this->add($attribute, $path . '/@' . $attribute->nodeName);
        }

        // How often each name occurs among the child elements: only repeated names get a position.
        $counts = [];
        foreach ($element->childNodes as $child) {
            if ($child instanceof DOMElement) {
                $counts[$child->nodeName] = ($counts[$child->nodeName] ?? 0) + 1;
            }
        }

        $positions = [];
        foreach ($element->childNodes as $child) {
            if ($child instanceof DOMElement) {
                $name = $child->nodeName;
                $positions[$name] = ($positions[$name] ?? 0) + 1;
                $this->addElement($child, $path . '/' . $name . ($counts[$name] > 1 ? '[' . $positions[$name] . ']' : ''));
            } else {
                // Text, comments and processing instructions share the path of their element.
                $this->add($child, $path);
            }
        }
    }

    private function add(DOMNode $node, string $path): void
    {
        $order = count($this->nodes);
        $this->nodes[] = $node;
        $this->entries[spl_object_id($node)] = ['key' => (string) $order, 'path' => $path, 'order' => $order];
    }

    /**
     * The path of a node the index does not know (from another document) - by walking its siblings.
     */
    private static function computePath(DOMNode $node): string
    {
        $element = $node instanceof DOMAttr ? $node->ownerElement : $node;
        $segments = [];

        while ($element instanceof DOMElement) {
            $name = $element->nodeName;
            $index = 1;
            $repeated = false;
            for ($sibling = $element->previousSibling; $sibling !== null; $sibling = $sibling->previousSibling) {
                if ($sibling instanceof DOMElement && $sibling->nodeName === $name) {
                    $index++;
                    $repeated = true;
                }
            }
            for ($sibling = $element->nextSibling; $sibling !== null && ! $repeated; $sibling = $sibling->nextSibling) {
                $repeated = $sibling instanceof DOMElement && $sibling->nodeName === $name;
            }

            array_unshift($segments, '/' . $name . ($repeated ? '[' . $index . ']' : ''));
            $element = $element->parentNode;
        }

        $path = implode('', $segments);

        return $node instanceof DOMAttr ? $path . '/@' . $node->nodeName : $path;
    }
}
