<?php

declare(strict_types=1);

namespace Dealerweb\EInvoice\Generation;

use DateTimeImmutable;
use Dealerweb\EInvoice\CodeList;
use Dealerweb\EInvoice\CodeLists;
use Dealerweb\EInvoice\Document;
use Dealerweb\EInvoice\Exception\InvalidInvoice;
use Dealerweb\EInvoice\Exception\InvalidPdf;
use Dealerweb\EInvoice\Exception\InvalidXml;
use Dealerweb\EInvoice\Exception\UnsupportedDocument;
use Dealerweb\EInvoice\Invoice;
use Dealerweb\EInvoice\Labels;
use Dealerweb\EInvoice\Model\FieldWriter;
use Dealerweb\EInvoice\Profile;
use Dealerweb\EInvoice\Render\PdfRenderer;
use Dealerweb\EInvoice\Rules;
use Dealerweb\EInvoice\Syntax;
use Dealerweb\EInvoice\Validation\Validator;
use InvalidArgumentException;

/**
 * Generates an electronic invoice: in CII ZUGFeRD / Factur-X in the profiles MINIMUM to EXTENDED, XRechnung or Peppol
 * BIS Billing 3.0, in UBL EN 16931, XRechnung with its extension or Peppol BIS Billing 3.0 - as XML, and in CII also as
 * ZUGFeRD / Factur-X PDF (PDF/A-3 with the XML embedded). Invoice::toXml() and Invoice::toPdf() use it; here the
 * invoice may also be given by the ids of the fields, and the validator may be left out.
 *
 * The invoice is given as model (Invoice, or an array with the names of the model) or by the ids of the fields - the
 * business terms and groups of EN 16931 and the extensions of Factur-X, nested in their groups or not (Fields). The
 * document is written in the order of the schema, with what the specification fixes (ChargeIndicator, schemeID,
 * TypeCode, the format of dates, BT-24) and checked by the validator - an invoice that does not meet its profile is not
 * returned (InvalidInvoice).
 */
final class Generator
{
    /** The part of the CII tree each profile may use - XRechnung and Peppol have the fields of EN 16931 in the CII schema D16B. */
    private const CII = [
        'MINIMUM' => 'MINIMUM',
        'BASIC WL' => 'BASIC WL',
        'BASIC' => 'BASIC',
        'EN16931' => 'EN16931',
        'EXTENDED' => 'EXTENDED',
        'XRECHNUNG' => 'XRECHNUNG',
        'PEPPOL' => 'PEPPOL',
    ];

    /** The part of the UBL trees each profile may use - the extension of XRechnung belongs to XRechnung only. */
    private const UBL = [
        'CORE' => 'CORE',
        'XRECHNUNG' => 'XRECHNUNG',
        'PEPPOL' => 'PEPPOL',
    ];

    /** The specification identifier of an XRechnung that uses the extension (sub lines, third party payments). */
    private const XRECHNUNG_EXTENSION = 'urn:cen.eu:en16931:2017#compliant#urn:xeinkauf.de:kosit:xrechnung_3.0#conformant#urn:xeinkauf.de:kosit:extension:xrechnung_3.0';

    /** The business process (BT-23) of Peppol BIS Billing 3.0 unless the invoice gives one: billing, profile 01. */
    private const PEPPOL_PROCESS = 'urn:fdc:peppol.eu:2017:poacc:billing:01:1.0';

    /** The profile the invoice is written and checked in. */
    private readonly Profile $profile;

    /** The profile of the tree the invoice is written in. */
    private readonly string $tree;

    /** Whether the invoice is written as UBL - as Invoice or CreditNote by its type. */
    private readonly bool $ubl;

    /** The XML the validator accepted last - pdf() does not check it again. */
    private ?string $checked = null;

    /**
     * @param bool $validate check the invoice with the validator before returning it
     * @param Syntax|null $syntax the syntax to write: CII for the profiles of ZUGFeRD / Factur-X and for XRechnung
     *                            unless given, UBL for Profile::Core and Profile::Peppol (both syntaxes). Either UBL
     *                            syntax means UBL - a credit note (type code 381 ...) is written as UBL CreditNote, any
     *                            other invoice as UBL Invoice. EN 16931 itself is the same in both: Profile::En16931 in
     *                            UBL is Profile::Core, Profile::Core in CII is Profile::En16931 (the profile of
     *                            ZUGFeRD / Factur-X of that name).
     * @throws InvalidArgumentException a profile of ZUGFeRD / Factur-X other than EN 16931 in UBL
     */
    public function __construct(Profile $profile, private readonly bool $validate = true, ?Syntax $syntax = null)
    {
        $this->ubl = $syntax === null ? in_array($profile, [Profile::Core, Profile::Peppol], true) : $syntax !== Syntax::Cii;
        $this->profile = match (true) {
            $this->ubl && $profile === Profile::En16931 => Profile::Core,
            ! $this->ubl && $profile === Profile::Core => Profile::En16931,
            default => $profile,
        };
        $this->tree = match (true) {
            $this->ubl => self::UBL[$this->profile->value] ?? throw new InvalidArgumentException("{$profile->label()} is written in CII only."),
            default => self::CII[$this->profile->value] ?? throw new InvalidArgumentException("{$profile->label()} has no CII."),
        };
    }

    /**
     * The invoice written to be shown (Render\HtmlRenderer, Render\PdfRenderer) as the model holds it: in CII of
     * ZUGFeRD / Factur-X EXTENDED - where it uses the XRechnung extension (sub lines, the type of a third party payment)
     * in UBL of XRechnung -, not checked, with no specification identifier (BT-24) other than its own.
     *
     * @internal used by Document::of() for an invoice not read from a document
     * @throws InvalidArgumentException the invoice holds content no syntax has a place for together, or a value of the
     *                                  wrong kind
     */
    public static function preview(Invoice $invoice): string
    {
        try {
            return (new self(Profile::Extended, false))->modelXml($invoice, false);
        } catch (InvalidArgumentException $cii) {
            try {
                return (new self(Profile::XRechnung, false, Syntax::UblInvoice))->modelXml($invoice, false);
            } catch (InvalidArgumentException) {
                throw $cii;
            }
        }
    }

    /**
     * The invoice as XML (UTF-8). From the model the totals and the VAT breakdown are completed where they are not
     * given (Invoice::calculate()), and a message names the path of a value ("lines.2.unit").
     *
     * BT-24 is set from the profile unless the invoice gives it - an XRechnung may name an extension of XRechnung, no
     * other specification; an XRechnung in UBL that uses the extension (sub lines, third party payments) names it. The
     * identifier of another profile (of a read invoice) is replaced by the one of this.
     *
     * @param Invoice|array<string, mixed> $invoice the model, an array with its names or the fields by their ids
     * @throws InvalidArgumentException a field is unknown, not part of the profile or in the wrong place, occurs too
     *                                  often or has a value of the wrong kind
     * @throws InvalidInvoice the validator rejects the invoice (report())
     */
    public function xml(Invoice|array $invoice): string
    {
        if (is_array($invoice) && ! self::isFieldList($invoice)) {
            $invoice = Invoice::fromArray($invoice);
        }
        if (! $invoice instanceof Invoice) {
            $typeCode = self::given($invoice, 'BT-3');

            return $this->fieldsXml($invoice, $this->treeFor(is_scalar($typeCode) ? (string) $typeCode : null));
        }

        return $this->modelXml($invoice, true);
    }

    /**
     * Writes a read document - UBL or CII, any specification - as CII in this generator's profile: its content after
     * EN 16931 (Document::toArray()). What lies beyond EN 16931 (Document::unmapped()) is not carried over. The
     * specification identifier (BT-24) becomes the one of the profile - for XRechnung one that specifies it further
     * (XRechnung CVD) is kept.
     *
     * @internal the proof of writing by the ids of the fields against the model of KoSIT (GeneratorRoundTripTest)
     * @throws InvalidArgumentException the document holds a value CII or the profile has no place for
     * @throws InvalidInvoice the validator rejects the result (report())
     */
    public function xmlFromDocument(Document $document): string
    {
        if ($this->ubl) {
            throw new InvalidArgumentException('xmlFromDocument() writes CII - write the model of the invoice to write it as UBL.');
        }
        $fields = Fields::ofDocument($document);
        $identifier = $fields['BG-2']['BT-24'] ?? null;
        if ($this->profile !== Profile::XRechnung || ! is_string($identifier) || ! str_starts_with($identifier, $this->profile->identifier() . '#')) {
            unset($fields['BG-2']['BT-24']);
        }

        return $this->fieldsXml($fields, Tree::cii());
    }

    /**
     * The invoice as ZUGFeRD / Factur-X PDF (PDF/A-3): the XML embedded in the PDF given or, without one, in the
     * invoice rendered by PdfRenderer in the language given. The invoice may be given as its XML or as model (written
     * by xml() first). The PDF given has to meet PDF/A-3 itself - its fonts embedded, no JavaScript -, the rest PDF/A
     * asks of the file is done here (HybridPdf). The XML is checked as by xml() - one this generator has just returned
     * is not checked again. A ZUGFeRD / Factur-X PDF holds CII of ZUGFeRD / Factur-X or XRechnung: a generator of UBL
     * or of Peppol writes none.
     *
     * @param string|Invoice $xml the XML of the invoice, or the invoice as model
     * @param string $language de, en or fr - of the rendered invoice and of the document title where the PDF has none
     * @throws InvalidInvoice the validator rejects the XML (report())
     * @throws InvalidPdf the PDF cannot be read, is encrypted or signed
     * @throws InvalidXml|UnsupportedDocument the XML cannot be read as an invoice
     * @throws InvalidArgumentException an unknown language, a value of the model that has no place, UBL, Peppol
     */
    public function pdf(string|Invoice $xml, ?string $pdf = null, string $language = 'en'): string
    {
        if (! in_array($language, Labels::LANGUAGES, true)) {
            throw new InvalidArgumentException("Unsupported language \"$language\", use one of " . implode(', ', Labels::LANGUAGES) . '.');
        }
        if ($this->ubl) {
            throw new InvalidArgumentException('A ZUGFeRD / Factur-X PDF holds CII - this generator writes UBL.');
        }
        if ($this->profile === Profile::Peppol) {
            throw new InvalidArgumentException('A ZUGFeRD / Factur-X PDF holds ZUGFeRD / Factur-X or XRechnung - Peppol BIS Billing 3.0 is XML only.');
        }
        if ($xml instanceof Invoice) {
            $xml = $this->xml($xml);
        }
        if ($this->validate && $xml !== $this->checked) {
            $report = (new Validator())->validate($xml, $this->profile);
            if (! $report->isValid()) {
                throw new InvalidInvoice($report);
            }
        }

        $document = Document::fromXml($xml);
        if ($document->syntax() !== Syntax::Cii) {
            throw new InvalidArgumentException('A ZUGFeRD / Factur-X PDF holds CII - the XML given is UBL.');
        }
        $type = CodeLists::name(CodeList::DocumentType, (string) $document->value('BT-3'), $language) ?? '';
        $title = trim($type . ' ' . $document->value('BT-1'));

        return $pdf === null
            // The rendered invoice maps every character to Unicode: PDF/A-3u.
            ? HybridPdf::create((new PdfRenderer($language))->renderDocument($document), $xml, $this->profile, $title, new DateTimeImmutable(), 'U')
            : HybridPdf::create($pdf, $xml, $this->profile, $title, new DateTimeImmutable());
    }

    /**
     * The invoice of the model as XML - with $identify, BT-24 as xml() describes it; without, the invoice keeps the
     * one it gives and gets none else (preview()).
     *
     * @throws InvalidArgumentException a value that has no place, with its path
     * @throws InvalidInvoice the validator rejects the invoice (report())
     */
    private function modelXml(Invoice $invoice, bool $identify): string
    {
        if ($identify && in_array($invoice->specification, array_map(static fn(Profile $profile): string => $profile->identifier(), Profile::cases()), true)) {
            $invoice = clone $invoice;
            $invoice->specification = null;
        }
        $tree = $this->treeFor(is_scalar($invoice->typeCode) ? (string) $invoice->typeCode : null);
        $writer = new FieldWriter($tree, $this->tree);
        $fields = $writer->write($invoice);
        try {
            return $this->fieldsXml($fields, $tree, $identify);
        } catch (InvalidArgumentException $e) {
            throw new InvalidArgumentException($writer->explain($e->getMessage()), 0, $e);
        }
    }

    /**
     * The tree the invoice is written in: CII, or in UBL the CreditNote for a credit note (by its type code), the Invoice
     * for any other.
     */
    private function treeFor(?string $typeCode): Tree
    {
        if (! $this->ubl) {
            return Tree::cii();
        }

        return Tree::of(in_array(trim((string) $typeCode), Rules::CREDIT_NOTE_CODES, true) ? Syntax::UblCreditNote : Syntax::UblInvoice);
    }

    /**
     * The invoice given by the ids of the fields as XML of the tree - with $identify, BT-24 of the profile where the
     * fields give none (and BT-23 of Peppol).
     *
     * @param array<string, mixed> $fields
     */
    private function fieldsXml(array $fields, Tree $tree, bool $identify = true): string
    {
        if ($identify) {
            $identifier = self::given($fields, 'BT-24');
            $own = $this->profile->identifier();
            if ($identifier === null) {
                $fields['BT-24'] = $this->ubl && $this->profile === Profile::XRechnung && self::usesExtension($fields) ? self::XRECHNUNG_EXTENSION : $own;
            } elseif (in_array($this->profile, [Profile::XRechnung, Profile::Peppol], true) && (! is_string($identifier) || ($identifier !== $own && ! str_starts_with($identifier, $own . '#')))) {
                // The rules of FeRD check the identifier of their profiles, those of XRechnung only warn (BR-DE-21).
                throw new InvalidArgumentException(($this->profile === Profile::XRechnung
                    ? "BT-24: an XRechnung names XRechnung ($own) or an extension of it, not "
                    : "BT-24: a Peppol invoice names Peppol BIS Billing 3.0 ($own) or a specification of it, not ") . var_export(is_string($identifier) ? Rules::excerpt($identifier) : $identifier, true) . '.');
            }
            if ($this->profile === Profile::Peppol && self::given($fields, 'BT-23') === null) {
                $fields['BT-23'] = self::PEPPOL_PROCESS;
            }
        }

        $document = (new TreeWriter($tree, $this->tree))->write(Fields::nodes($tree, $fields));
        $xml = (string) $document->saveXML();

        if ($this->validate) {
            $report = (new Validator())->validate($xml, $this->profile);
            if (! $report->isValid()) {
                throw new InvalidInvoice($report);
            }
            $this->checked = $xml;
        }

        return $xml;
    }

    /**
     * Whether the fields use the extension of XRechnung: the sub lines of a line or third party payments (UBL).
     *
     * @param array<mixed> $fields
     */
    private static function usesExtension(array $fields): bool
    {
        foreach ($fields as $key => $value) {
            if (is_string($key) && (str_starts_with($key, 'BG-DEX-') || str_starts_with($key, 'BT-DEX-'))) {
                return true;
            }
            if (is_array($value) && self::usesExtension($value)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Whether an array gives the fields by the ids of the field list (BT-1, BG-4) - not by the names of the model.
     *
     * @param array<mixed> $invoice
     */
    private static function isFieldList(array $invoice): bool
    {
        if ($invoice === []) {
            return true;
        }
        foreach (array_keys($invoice) as $key) {
            if (is_string($key) && preg_match('/^B[GT]-/', $key) === 1) {
                return true;
            }
        }

        return false;
    }

    /**
     * The value the fields give a field anywhere, in whatever group - null if they give it nowhere or give no value
     * (null, '', []).
     *
     * @param array<mixed> $fields
     */
    private static function given(array $fields, string $id): mixed
    {
        foreach ($fields as $key => $value) {
            if ($key === $id && $value !== null && $value !== '' && $value !== []) {
                return $value;
            }
            if (is_array($value) && ($found = self::given($value, $id)) !== null) {
                return $found;
            }
        }

        return null;
    }
}
