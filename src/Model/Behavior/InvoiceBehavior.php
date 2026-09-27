<?php

declare(strict_types=1);

namespace Dealerweb\EInvoice\Model\Behavior;

use Dealerweb\EInvoice\Exception\InvalidInvoice;
use Dealerweb\EInvoice\Exception\InvalidPdf;
use Dealerweb\EInvoice\Exception\InvalidXml;
use Dealerweb\EInvoice\Exception\UnsupportedDocument;
use Dealerweb\EInvoice\Generation\Generator;
use Dealerweb\EInvoice\Generation\HybridPdf;
use Dealerweb\EInvoice\Invoice;
use Dealerweb\EInvoice\Model\Calculator;
use Dealerweb\EInvoice\Model\Reader;
use Dealerweb\EInvoice\Profile;
use Dealerweb\EInvoice\Rules;
use Dealerweb\EInvoice\Summary;
use Dealerweb\EInvoice\Syntax;
use Dealerweb\EInvoice\Xml;
use InvalidArgumentException;

/**
 * What the invoice does besides holding its values: reading it - from XML or from a ZUGFeRD / Factur-X PDF -, writing
 * it - as XML or as ZUGFeRD / Factur-X PDF -, completing it (calculate()) and what can be said about it (isCreditNote(),
 * summary()).
 *
 * @internal part of Invoice
 */
trait InvoiceBehavior
{
    /** The XML the invoice was read from - null for an invoice built. */
    private ?string $readXml = null;

    /** The syntax of the XML it was read from. */
    private ?Syntax $readSyntax = null;

    /** @var list<string> what the model had no place for when the invoice was read */
    private array $readUnread = [];

    /**
     * An invoice read from its XML: UBL Invoice, UBL CreditNote or CII - XRechnung, ZUGFeRD / Factur-X, Peppol BIS,
     * EN 16931. What the model has no place for is named by unread(). A ZUGFeRD / Factur-X PDF given here is read as
     * fromPdf() reads it.
     *
     * @throws InvalidXml the document is empty or not well-formed XML
     * @throws UnsupportedDocument the XML is no invoice in a supported syntax (an order, ZUGFeRD 1.0) or declares a
     *                             DOCTYPE
     * @throws InvalidPdf a PDF that is damaged beyond repair or encrypted
     */
    public static function fromXml(string $xml): Invoice
    {
        return HybridPdf::isPdf($xml) ? self::fromPdf($xml) : self::read($xml);
    }

    /**
     * An invoice read from a ZUGFeRD / Factur-X PDF (also an XRechnung in a PDF): from the XML the PDF carries -
     * factur-x.xml, zugferd-invoice.xml or xrechnung.xml. Only the XML is the invoice, the pages of the PDF are not
     * read.
     *
     * @throws InvalidPdf the content is no PDF, or the PDF is damaged beyond repair or encrypted
     * @throws UnsupportedDocument the PDF carries no invoice, or its XML is no invoice in a supported syntax
     * @throws InvalidXml the XML the PDF carries is not well-formed
     */
    public static function fromPdf(string $pdf): Invoice
    {
        return self::read(HybridPdf::invoiceXml($pdf));
    }

    /**
     * The XML of the invoice a ZUGFeRD / Factur-X PDF carries - factur-x.xml, zugferd-invoice.xml or xrechnung.xml -
     * exactly as the sender embedded it: byte for byte, neither read nor written anew. The original of an invoice
     * received as PDF, to store next to it; read it with fromXml(), check it with Validator.
     *
     * @throws InvalidPdf the content is no PDF, or the PDF is damaged beyond repair or encrypted
     * @throws UnsupportedDocument the PDF carries no invoice file
     */
    public static function xmlFromPdf(string $pdf): string
    {
        return HybridPdf::invoiceXml($pdf);
    }

    /**
     * An invoice read from a file: its XML, or a ZUGFeRD / Factur-X PDF (see fromPdf()) - which one, the file tells. A
     * path or a file:// URL, no stream wrapper (http, data, php://filter): reading does not reach the network.
     *
     * @throws InvalidXml the file cannot be read, or its XML is not well-formed
     * @throws InvalidPdf the file is a PDF that is damaged beyond repair or encrypted
     * @throws UnsupportedDocument the file is no invoice in a supported syntax, or a PDF that carries none
     */
    public static function fromFile(string $path): Invoice
    {
        return self::fromXml(Xml::readFile($path));
    }

    /**
     * What the document the invoice was read from holds and the model has no place for, by its path in the document
     * ("/rsm:…/ram:Postbox = 12 34 - not an element of ZUGFeRD / Factur-X EXTENDED") - nothing is dropped silently.
     * Empty for an invoice read completely, and for one built.
     *
     * @return list<string>
     */
    public function unread(): array
    {
        return $this->readUnread;
    }

    /**
     * Completes what follows from the lines and the allowances and charges and is not given: the net amount of each
     * line, the VAT breakdown, the totals. Given values stay - the validator checks them. toXml() and toPdf() do the
     * same with a copy of the invoice.
     *
     * @throws InvalidArgumentException a list holds an item of the wrong kind (an array instead of a Line), with its path
     */
    public function calculate(): static
    {
        $this->checkItems();
        Calculator::complete($this);

        return $this;
    }

    /**
     * The invoice as XML of a profile: ZUGFeRD / Factur-X (MINIMUM to EXTENDED), XRechnung, EN 16931 or Peppol BIS
     * Billing 3.0 - completed (calculate(), with a copy) and checked by the validator: an invoice that does not meet
     * its profile is not returned. For more options (the fields by their ids, no validation) see Generator.
     *
     * @param Syntax|null $syntax CII or UBL (Syntax::UblInvoice - a credit note is written as UBL CreditNote by its
     *                            type) where the profile has both; by default CII for ZUGFeRD / Factur-X and
     *                            XRechnung, UBL for Peppol
     * @throws InvalidArgumentException a value has no place in the profile or the syntax, or is of the wrong kind - the
     *                                  message names its path
     * @throws InvalidInvoice the validator rejects the invoice (report())
     */
    public function toXml(Profile $profile, ?Syntax $syntax = null): string
    {
        return (new Generator($profile, syntax: $syntax))->xml($this);
    }

    /**
     * The invoice as ZUGFeRD / Factur-X PDF - a PDF/A-3 with the XML embedded, which a person reads and a program
     * processes: the invoice rendered in the language given, or your own PDF of it. Written and checked as by toXml(),
     * in CII - the syntax such a PDF holds; a profile of ZUGFeRD / Factur-X, EN 16931 or XRechnung (Peppol BIS has no
     * PDF).
     *
     * @param string $language de, en or fr - of the rendered invoice
     * @param string|null $pdf your own PDF of the invoice to carry the XML instead of the rendered one; it has to meet
     *                         PDF/A-3 itself (fonts embedded, no JavaScript)
     * @throws InvalidArgumentException a value has no place in the profile, a profile without PDF, an unknown language
     * @throws InvalidInvoice the validator rejects the invoice (report())
     * @throws InvalidPdf your PDF cannot be read, is encrypted or signed
     */
    public function toPdf(Profile $profile, string $language = 'en', ?string $pdf = null): string
    {
        return (new Generator($profile, syntax: Syntax::Cii))->pdf($this, $pdf, $language);
    }

    /**
     * Whether it is a credit note: the type code is one of a credit note (381, 396, ...), or it was read from a UBL
     * CreditNote.
     */
    public function isCreditNote(): bool
    {
        return $this->readSyntax === Syntax::UblCreditNote || in_array(trim((string) $this->typeCode), Rules::CREDIT_NOTE_CODES, true);
    }

    /**
     * Whether the buyer issued it on behalf of the seller (self-billing, in German tax law a "Gutschrift"): the type
     * code is one of self-billing (389, 261, ...).
     */
    public function isSelfBilled(): bool
    {
        return in_array(trim((string) $this->typeCode), Rules::SELF_BILLED_CODES, true);
    }

    /**
     * The key facts for capturing the invoice as a document: number, type, dates, parties, amounts, VAT rates and a
     * subject - see Summary.
     */
    public function summary(): Summary
    {
        return Summary::of($this);
    }

    /**
     * The XML the invoice was read from, as long as the invoice is unchanged - null for an invoice built or changed.
     *
     * @internal used by the rendering: a read invoice is shown as delivered
     */
    public function sourceXml(): ?string
    {
        if ($this->readXml === null) {
            return null;
        }

        return (new Reader())->read($this->readXml)->toArray() === $this->toArray() ? $this->readXml : null;
    }

    /**
     * The invoice remembers the document it was read from: its XML and syntax, and what the model had no place for.
     *
     * @internal used by the reading
     * @param list<string> $unread
     */
    public function rememberSource(string $xml, ?Syntax $syntax, array $unread): static
    {
        $this->readXml = $xml;
        $this->readSyntax = $syntax;
        $this->readUnread = $unread;

        return $this;
    }

    /**
     * The invoice read from XML - what a PDF carries is XML here, whatever it holds.
     *
     * @throws InvalidXml the document is empty or not well-formed XML
     * @throws UnsupportedDocument the XML is no invoice in a supported syntax, or declares a DOCTYPE
     */
    private static function read(string $xml): Invoice
    {
        $reader = new Reader();

        return $reader->read($xml)->rememberSource($xml, $reader->syntax(), $reader->unread());
    }
}
