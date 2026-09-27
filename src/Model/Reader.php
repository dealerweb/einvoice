<?php

declare(strict_types=1);

namespace Dealerweb\EInvoice\Model;

use Dealerweb\EInvoice\Exception\InvalidXml;
use Dealerweb\EInvoice\Exception\UnsupportedDocument;
use Dealerweb\EInvoice\Generation\Tree;
use Dealerweb\EInvoice\Generation\TreeReader;
use Dealerweb\EInvoice\Invoice;
use Dealerweb\EInvoice\Syntax;
use Dealerweb\EInvoice\Xml;
use DOMDocument;

/**
 * Reads an electronic invoice into the model: every field of EN 16931, of the XRechnung extension and of ZUGFeRD /
 * Factur-X EXTENDED to its property. Values as delivered: amounts as decimal text ("529.87"), dates as YYYY-MM-DD,
 * indicators as bool.
 *
 * What the model has no place for - an element outside the syntax, a second value of a field the model has once - is
 * named by unread(), not dropped silently.
 *
 * @internal used by Invoice::fromXml()
 */
final class Reader
{
    /** @var list<string> */
    private array $unread = [];

    private ?Syntax $syntax = null;

    /**
     * @throws InvalidXml the document is empty or not well-formed XML
     * @throws UnsupportedDocument the XML is no invoice in a supported syntax, or declares a DOCTYPE
     */
    public function read(string $xml): Invoice
    {
        return $this->readDocument(Xml::load($xml));
    }

    /**
     * @throws InvalidXml the file cannot be read, is empty or is not well-formed XML
     * @throws UnsupportedDocument the XML is no invoice in a supported syntax, or declares a DOCTYPE
     */
    public function readFile(string $path): Invoice
    {
        return $this->read(Xml::readFile($path));
    }

    /**
     * @throws UnsupportedDocument the document is no invoice in a supported syntax
     */
    public function readDocument(DOMDocument $document): Invoice
    {
        $this->unread = [];
        $this->syntax = null;
        $syntax = Syntax::detect($document);
        $tree = Tree::of($syntax);
        $elements = new TreeReader($tree);
        $nodes = $elements->read($document);
        $reader = new NodeReader($tree);
        $invoice = $reader->read($nodes);
        $outside = $syntax === Syntax::Cii ? 'ZUGFeRD / Factur-X EXTENDED' : 'EN 16931 and the XRechnung extension in UBL';
        $this->unread = [
            ...array_map(static fn(string $path): string => "$path - not an element of $outside", $elements->unknown()),
            ...$reader->unread(),
        ];
        $this->syntax = $syntax;

        return $invoice;
    }

    /**
     * What the last read found no place for in the model, by its path in the document - empty for an invoice read
     * completely.
     *
     * @return list<string>
     */
    public function unread(): array
    {
        return $this->unread;
    }

    /**
     * The syntax of the document read last.
     */
    public function syntax(): ?Syntax
    {
        return $this->syntax;
    }
}
