<?php

declare(strict_types=1);

namespace Dealerweb\EInvoice\Generation;

use Composer\InstalledVersions;
use DateTimeImmutable;
use DateTimeZone;
use Dealerweb\EInvoice\Exception\InvalidPdf;
use Dealerweb\EInvoice\Exception\UnsupportedDocument;
use Dealerweb\EInvoice\Pdf\Dictionary;
use Dealerweb\EInvoice\Pdf\Document;
use Dealerweb\EInvoice\Pdf\Filter;
use Dealerweb\EInvoice\Pdf\Name;
use Dealerweb\EInvoice\Pdf\Parser;
use Dealerweb\EInvoice\Pdf\Reference;
use Dealerweb\EInvoice\Pdf\Stream;
use Dealerweb\EInvoice\Pdf\Text;
use Dealerweb\EInvoice\Pdf\Writer;
use Dealerweb\EInvoice\Profile;
use Exception;

/**
 * Writes the XML of an invoice into a PDF as ZUGFeRD / Factur-X (specification Factur-X 1.09.2 / ZUGFeRD 2.5.2,
 * chapter 6): a PDF/A-3 with the XML embedded as factur-x.xml (xrechnung.xml in the profile XRECHNUNG), associated
 * with the document (AF) as Alternative - Data for MINIMUM and BASIC WL, whose XML holds less than the PDF shows -, and
 * the XMP metadata of PDF/A-3 with the extension schema of Factur-X.
 *
 * The PDF is written anew in one revision. What PDF/A asks of the file is done here: the binary comment after the
 * header, an output intent with an sRGB profile where the PDF has none, the resources of each page in its own
 * dictionary, the other embedded files described as PDF/A-3 asks (name, MIME type, date, relationship, associated
 * with the document), the document information in agreement with the XMP metadata, a file identifier. What it asks of
 * the content - fonts embedded, no encryption, no JavaScript, transparency only with its colour space - the PDF given
 * has to meet; the invoice rendered by PdfRenderer does. An invoice file the PDF carries already is replaced. The XMP
 * metadata is written anew; it keeps of the old one the PDF/A conformance level, the PDF/UA identification and the
 * languages.
 *
 * It also reads the XML of the invoice out of such a PDF (invoiceXml()) - for Invoice::fromPdf() and the validator.
 *
 * @internal
 */
final class HybridPdf
{
    /** The names of the invoice file of ZUGFeRD 2.0, ZUGFeRD / Factur-X and XRechnung, in lower case. */
    private const INVOICE_FILES = ['factur-x.xml', 'xrechnung.xml', 'zugferd-invoice.xml'];

    /** The conformance level of each profile in the XMP metadata (fx:ConformanceLevel). */
    private const CONFORMANCE_LEVELS = [
        'MINIMUM' => 'MINIMUM',
        'BASIC WL' => 'BASIC WL',
        'BASIC' => 'BASIC',
        'EN16931' => 'EN 16931',
        'EXTENDED' => 'EXTENDED',
        'XRECHNUNG' => 'XRECHNUNG',
    ];

    /**
     * The keys of the document information that the XMP metadata repeats - written here. Trapped is left out: its
     * property pdf:Trapped is none of those PDF/A-3 predefines.
     */
    private const INFORMATION_KEYS = ['Title', 'Author', 'Subject', 'Keywords', 'Creator', 'Producer', 'CreationDate', 'ModDate', 'Trapped'];

    /**
     * The characters of a text of the document information at most: a string of PDF/A holds 32,767 bytes (ISO
     * 19005-3, 6.1.13), in UTF-16 four bytes each at most.
     */
    private const TEXT_LENGTH = 8000;

    /** A ToUnicode map as dompdf writes it is a few kilobytes - one larger is no such map. */
    private const UNICODE_MAP_LIMIT = 1048576;

    /** The MIME types of embedded files that name none, by the extension of their names (PDF/A-3, 6.8). */
    private const MIME_TYPES = [
        'pdf' => 'application/pdf', 'xml' => 'text/xml', 'txt' => 'text/plain', 'csv' => 'text/csv',
        'htm' => 'text/html', 'html' => 'text/html', 'json' => 'application/json', 'zip' => 'application/zip',
        'jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png', 'gif' => 'image/gif',
        'tif' => 'image/tiff', 'tiff' => 'image/tiff', 'odt' => 'application/vnd.oasis.opendocument.text',
        'ods' => 'application/vnd.oasis.opendocument.spreadsheet',
        'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    ];

    /** The name this package gives itself as creator and producer of a PDF. */
    private const TOOL = 'DealerWeb E-Invoice';

    /**
     * @param string $title the title of the document if the PDF names none
     * @param string|null $conformance the PDF/A conformance level to declare (A, B or U) - by default the one the PDF
     *                                 declares already, B otherwise
     * @throws InvalidPdf the PDF cannot be read, is encrypted or signed, or has no pages
     */
    public static function create(string $pdf, string $xml, Profile $profile, string $title, DateTimeImmutable $now, ?string $conformance = null): string
    {
        $level = self::CONFORMANCE_LEVELS[$profile->value] ?? throw new InvalidPdf("The profile {$profile->value} has no ZUGFeRD / Factur-X PDF.");
        $fileName = $profile === Profile::XRechnung ? 'xrechnung.xml' : 'factur-x.xml';

        $document = Parser::parse($pdf);
        $root = $document->trailer->get('Root');
        $catalog = $document->resolve($root);
        if (! $root instanceof Reference || ! $catalog instanceof Dictionary) {
            throw new InvalidPdf('The PDF has no document catalog.');
        }
        $pages = self::pages($document, $catalog);
        self::refuseSignature($document, $catalog, $pages);
        // PDF 2.0 writes text strings in UTF-8, which PDF 1.7 - the base of PDF/A-3 - does not know. The version is
        // the one of the header, or the one of the catalog if later (ISO 32000-1, 7.7.2).
        $version = preg_match('/%PDF-(\d+\.\d+)/', substr($pdf, 0, 1024), $header) === 1 ? $header[1] : '1.7';
        if (preg_match('/^\d+\.\d+$/', $catalog->name('Version') ?? '') === 1 && version_compare((string) $catalog->name('Version'), $version, '>')) {
            $version = (string) $catalog->name('Version');
        }
        $metadata = self::metadata($document, $catalog);
        $conformance ??= $metadata['conformance'] ?? 'B';

        self::repairUnicodeMaps($document, $pages);
        self::outputIntent($document, $catalog);
        $relationship = in_array($profile, [Profile::Minimum, Profile::BasicWl], true) ? 'Data' : 'Alternative';
        $specification = self::fileSpecification($document, $xml, $fileName, $relationship, $now);
        self::attach($document, $catalog, $fileName, $specification, $now);
        self::linkPageFiles($document, $catalog, $pages, $specification, $now);

        $information = self::information($document, $title, $now);
        $catalog->set('Metadata', $document->add(new Stream(
            new Dictionary(['Type' => new Name('Metadata'), 'Subtype' => new Name('XML')]),
            self::xmp($information, $conformance, $fileName, $level, $metadata)
        )));
        // The header says 1.7 - PDF/A-3 is based on it.
        $catalog->remove('Version');

        $identifier = $document->resolve($document->trailer->get('ID'));
        $first = is_array($identifier) ? $document->resolve($identifier[0] ?? null) : null;
        $second = new Text(md5($xml . $now->format(DATE_ATOM) . strlen($pdf), true));

        return Writer::write($document, new Dictionary([
            'Root' => $root,
            'Info' => $document->add(self::informationDictionary($document, $information)),
            'ID' => [$first instanceof Text ? $first : $second, $second],
        ]), version_compare($version, '2.0', '>='));
    }

    /**
     * Whether a content is a PDF: its header in the first 1,024 bytes, where readers look for it.
     */
    public static function isPdf(string $content): bool
    {
        return str_contains(substr($content, 0, 1024), '%PDF-');
    }

    /**
     * The XML of the invoice a ZUGFeRD / Factur-X PDF carries: the embedded file factur-x.xml, xrechnung.xml or
     * zugferd-invoice.xml (ZUGFeRD 2.0; ZUGFeRD-invoice.xml of ZUGFeRD 1.0 has the same name), found by its name in the
     * embedded files of the document or among the files associated with it - decoded.
     *
     * @throws InvalidPdf the content is no PDF, or the PDF is damaged beyond repair or encrypted
     * @throws UnsupportedDocument the PDF carries no invoice file
     */
    public static function invoiceXml(string $pdf): string
    {
        if (! self::isPdf($pdf)) {
            throw new InvalidPdf('The content is no PDF.');
        }
        $document = Parser::parse($pdf);
        $catalog = $document->resolve($document->trailer->get('Root'));
        if (! $catalog instanceof Dictionary) {
            throw new InvalidPdf('The PDF has no document catalog.');
        }

        $found = [];
        $names = $document->resolve($catalog->get('Names'));
        $associated = $document->resolve($catalog->get('AF'));
        $files = [
            ...array_map(static fn(array $pair): array => [$pair[0]->utf8(), $pair[1]], $names instanceof Dictionary ? self::namesOf($document, $names->get('EmbeddedFiles')) : []),
            ...array_map(static fn(mixed $specification): array => ['', $specification], is_array($associated) ? $associated : []),
        ];
        foreach ($files as [$name, $specification]) {
            $invoiceFile = self::invoiceFile($document, $name, $specification);
            if ($invoiceFile !== null) {
                $found[$invoiceFile] ??= $specification;
            }
        }
        foreach (self::INVOICE_FILES as $invoiceFile) {
            $specification = $document->resolve($found[$invoiceFile] ?? null);
            $embedded = $specification instanceof Dictionary ? $document->resolve($specification->get('EF')) : null;
            $stream = $embedded instanceof Dictionary ? $document->resolve($embedded->get('F') ?? $embedded->get('UF')) : null;
            if ($stream instanceof Stream) {
                return Filter::decode($stream, Filter::LIMIT, $document->resolve(...));
            }
        }

        throw new UnsupportedDocument('The PDF carries no invoice - no embedded ' . implode(', ', self::INVOICE_FILES) . '.');
    }

    /**
     * The pages of the document, each given the resources it inherits from the page tree: PDF/A asks for resources
     * associated with the content stream itself (ISO 19005-3, 6.2.2). Resources a node of the tree holds directly
     * become one object that the pages share.
     *
     * @return list<Dictionary>
     * @throws InvalidPdf no pages, or a page tree that is no tree
     */
    private static function pages(Document $document, Dictionary $catalog): array
    {
        $pages = [];
        $seen = [];
        $pending = [[$catalog->get('Pages'), null]];
        while ($pending !== []) {
            [$node, $inherited] = array_pop($pending);
            $node = $document->resolve($node);
            if (! $node instanceof Dictionary) {
                continue;
            }
            // A node reached twice - a cycle, or a page listed in two places - is no page tree.
            if (isset($seen[spl_object_id($node)])) {
                throw new InvalidPdf('The page tree of the PDF is damaged.');
            }
            $seen[spl_object_id($node)] = true;

            $own = $node->get('Resources');
            $kids = $document->resolve($node->get('Kids'));
            $type = $node->name('Type');
            if ($type === 'Pages' || ($type !== 'Page' && is_array($kids))) {
                if ($own instanceof Dictionary) {
                    $own = $document->add($own);
                    $node->set('Resources', $own);
                }
                foreach (is_array($kids) ? array_reverse($kids) : [] as $kid) {
                    $pending[] = [$kid, $own ?? $inherited];
                }

                continue;
            }
            if ($own === null && $inherited !== null) {
                $node->set('Resources', $inherited);
            }
            $pages[] = $node;
        }
        if ($pages === []) {
            throw new InvalidPdf('The PDF has no pages.');
        }

        return $pages;
    }

    /**
     * A signed PDF cannot take the invoice: writing it anew breaks every signature. Signed is a signature field with
     * a value (the signature), in the form or on a page, or the permissions of a certification.
     *
     * @param list<Dictionary> $pages
     */
    private static function refuseSignature(Document $document, Dictionary $catalog, array $pages): void
    {
        $signed = $catalog->get('Perms') !== null;
        $form = $document->resolve($catalog->get('AcroForm'));
        $fields = $form instanceof Dictionary ? $document->resolve($form->get('Fields')) : null;
        $pending = [];
        foreach (is_array($fields) ? $fields : [] as $field) {
            $pending[] = [$field, null];
        }
        foreach ($pages as $page) {
            $annotations = $document->resolve($page->get('Annots'));
            foreach (is_array($annotations) ? $annotations : [] as $annotation) {
                $pending[] = [$annotation, null];
            }
        }

        $seen = [];
        while (! $signed && $pending !== []) {
            [$field, $inheritedType] = array_pop($pending);
            $field = $document->resolve($field);
            if (! $field instanceof Dictionary || isset($seen[spl_object_id($field)])) {
                continue;
            }
            $seen[spl_object_id($field)] = true;
            // The field type is inherited from the parent field (ISO 32000-1, 12.7.3.1).
            $type = $field->name('FT') ?? $inheritedType;
            $signed = $type === 'Sig' && $document->resolve($field->get('V')) instanceof Dictionary;
            $kids = $document->resolve($field->get('Kids'));
            foreach (is_array($kids) ? $kids : [] as $kid) {
                $pending[] = [$kid, $type];
            }
        }
        if ($signed) {
            throw new InvalidPdf('The PDF is signed - writing the invoice into it would break the signature.');
        }
    }

    /**
     * What the XMP metadata of the PDF says that the new metadata keeps: the PDF/A conformance level
     * (pdfaid:conformance), the part of PDF/UA 1 (pdfuaid:part) and the languages (dc:language).
     *
     * @return array{conformance: ?string, accessible: bool, languages: list<string>}
     */
    private static function metadata(Document $document, Dictionary $catalog): array
    {
        $found = ['conformance' => null, 'accessible' => false, 'languages' => []];
        $metadata = $document->resolve($catalog->get('Metadata'));
        if (! $metadata instanceof Stream) {
            return $found;
        }
        try {
            $xmp = Filter::decode($metadata, Filter::LIMIT, $document->resolve(...));
        } catch (InvalidPdf) {
            return $found;
        }

        if (preg_match('/pdfaid:conformance\s*(?:>|=\s*["\'])\s*([ABUabu])\b/', $xmp, $match) === 1) {
            $found['conformance'] = strtoupper($match[1]);
        }
        // PDF/UA 2 is based on PDF 2.0 - written as PDF 1.7 the file cannot claim it.
        $found['accessible'] = preg_match('/pdfuaid:part\s*(?:>|=\s*["\'])\s*1\s*[<"\']/', $xmp) === 1;
        if (preg_match('#<dc:language>(.*?)</dc:language>#s', $xmp, $match) === 1 && preg_match_all('#<rdf:li(?:\s[^>]*)?>\s*([A-Za-z]{1,8}(?:-[A-Za-z0-9]{1,8})*)\s*</rdf:li>#', $match[1], $languages) > 0) {
            $found['languages'] = array_values(array_unique($languages[1]));
        }

        return $found;
    }

    /**
     * Writes the identity mapping of character codes to Unicode that dompdf gives its fonts - one range
     * "<0000> <FFFF> <0000>" - as the CMap syntax has it: a range may vary in its last byte only (ISO 32000-1,
     * 9.10.3), so readers such as veraPDF map the first 256 codes and no character above U+00FF. Written as one range
     * for each first byte (the surrogates left out), every character of the page maps to Unicode, as PDF/A-3u asks.
     *
     * @param list<Dictionary> $pages
     */
    private static function repairUnicodeMaps(Document $document, array $pages): void
    {
        $seen = [];
        foreach ($pages as $page) {
            $resources = $document->resolve($page->get('Resources'));
            $fonts = $resources instanceof Dictionary ? $document->resolve($resources->get('Font')) : null;
            foreach ($fonts instanceof Dictionary ? $fonts->entries : [] as $font) {
                $font = $document->resolve($font);
                $map = $font instanceof Dictionary ? $document->resolve($font->get('ToUnicode')) : null;
                if (! $map instanceof Stream || isset($seen[spl_object_id($map)])) {
                    continue;
                }
                $seen[spl_object_id($map)] = true;
                try {
                    $cmap = Filter::decode($map, self::UNICODE_MAP_LIMIT, $document->resolve(...));
                } catch (InvalidPdf) {
                    continue;
                }
                if (preg_match('/\b1\s+beginbfrange\s*<0000>\s*<FFFF>\s*<0000>\s*endbfrange/i', $cmap) !== 1) {
                    continue;
                }

                $ranges = [];
                for ($byte = 0; $byte <= 0xFF; $byte++) {
                    if ($byte < 0xD8 || $byte > 0xDF) {
                        $ranges[] = sprintf('<%1$02X00> <%1$02XFF> <%1$02X00>', $byte);
                    }
                }
                $blocks = implode("\n", array_map(
                    static fn(array $block): string => count($block) . " beginbfrange\n" . implode("\n", $block) . "\nendbfrange",
                    array_chunk($ranges, 100)
                ));
                $map->data = self::compress(preg_replace('/\b1\s+beginbfrange\s*<0000>\s*<FFFF>\s*<0000>\s*endbfrange/i', $blocks, $cmap) ?? $cmap);
                $map->dictionary->set('Filter', new Name('FlateDecode'));
                $map->dictionary->remove('DecodeParms');
            }
        }
    }

    /**
     * The output intent of PDF/A with an sRGB profile, unless the PDF has one - the colours of the page contents are
     * DeviceRGB and DeviceGray, which PDF/A allows only with it. Where the PDF has another output intent with a
     * profile (PDF/X), the one of PDF/A takes the same: all of them have to refer to the same profile (ISO 19005-3,
     * 6.2.3).
     */
    private static function outputIntent(Document $document, Dictionary $catalog): void
    {
        $intents = $document->resolve($catalog->get('OutputIntents'));
        $intents = is_array($intents) ? $intents : [];
        $other = null;
        foreach ($intents as $intent) {
            $intent = $document->resolve($intent);
            if ($intent instanceof Dictionary && $intent->name('S') === 'GTS_PDFA1') {
                return;
            }
            if ($intent instanceof Dictionary && $intent->get('DestOutputProfile') !== null) {
                $other ??= $intent;
            }
        }
        if ($other !== null) {
            $intents[] = $document->add(new Dictionary(['S' => new Name('GTS_PDFA1')] + $other->entries));
            $catalog->set('OutputIntents', $intents);

            return;
        }

        $icc = file_get_contents(dirname(__DIR__, 2) . '/resources/icc/sRGB-v2-micro.icc');
        if ($icc === false) {
            throw new InvalidPdf('The sRGB profile of the package is missing (resources/icc).');
        }
        $profile = $document->add(new Stream(new Dictionary(['N' => 3, 'Filter' => new Name('FlateDecode')]), self::compress($icc)));
        $intents[] = $document->add(new Dictionary([
            'Type' => new Name('OutputIntent'),
            'S' => new Name('GTS_PDFA1'),
            'OutputConditionIdentifier' => new Text('sRGB IEC61966-2.1'),
            'RegistryName' => new Text('http://www.color.org'),
            'Info' => new Text('sRGB IEC61966-2.1'),
            'DestOutputProfile' => $profile,
        ]));
        $catalog->set('OutputIntents', $intents);
    }

    /**
     * The file specification of the invoice with the embedded file (text/xml, compressed, with its date and size).
     */
    private static function fileSpecification(Document $document, string $xml, string $fileName, string $relationship, DateTimeImmutable $now): Reference
    {
        $file = $document->add(new Stream(new Dictionary([
            'Type' => new Name('EmbeddedFile'),
            'Subtype' => new Name('text/xml'),
            'Filter' => new Name('FlateDecode'),
            'Params' => new Dictionary([
                'ModDate' => new Text(self::pdfDate($now)),
                'Size' => strlen($xml),
                'CheckSum' => new Text(md5($xml, true)),
            ]),
        ]), self::compress($xml)));

        return $document->add(new Dictionary([
            'Type' => new Name('Filespec'),
            'F' => new Text($fileName),
            'UF' => new Text($fileName),
            'Desc' => new Text($fileName === 'xrechnung.xml' ? 'XRechnung' : 'Factur-X / ZUGFeRD invoice'),
            'AFRelationship' => new Name($relationship),
            'EF' => new Dictionary(['F' => $file, 'UF' => $file]),
        ]));
    }

    /**
     * Adds the invoice to the embedded files of the document (the name tree, written flat in the order of the names)
     * and to its associated files (AF); an invoice file the PDF carries already is taken out of both. The other
     * embedded files stay - also two of the same name - and get what PDF/A-3 asks of them.
     */
    private static function attach(Document $document, Dictionary $catalog, string $fileName, Reference $specification, DateTimeImmutable $now): void
    {
        $names = $document->resolve($catalog->get('Names'));
        if (! $names instanceof Dictionary) {
            $names = new Dictionary();
            $catalog->set('Names', $names);
        }

        $files = [];
        foreach (self::namesOf($document, $names->get('EmbeddedFiles')) as [$name, $value]) {
            if (! self::isInvoice($document, $name->utf8(), $value)) {
                self::describeFile($document, $name, $value, $now);
                $files[] = [$name, $value];
            }
        }
        $files[] = [new Text($fileName), $specification];
        usort($files, static fn(array $a, array $b): int => strcmp($a[0]->bytes, $b[0]->bytes));
        $flat = [];
        foreach ($files as [$name, $value]) {
            $flat[] = $name;
            $flat[] = $value;
        }
        $names->set('EmbeddedFiles', new Dictionary(['Names' => $flat]));

        // Associated with the document: what was, without an old invoice file, every embedded file, the invoice.
        $associated = $document->resolve($catalog->get('AF'));
        $list = [];
        $listed = [];
        foreach (is_array($associated) ? $associated : [] as $item) {
            if (! self::isInvoice($document, '', $item)) {
                $list[] = $item;
                $file = $document->resolve($item);
                if ($file instanceof Dictionary) {
                    $listed[spl_object_id($file)] = true;
                }
            }
        }
        foreach ($files as [, $value]) {
            $file = $document->resolve($value);
            if ($file instanceof Dictionary && ! isset($listed[spl_object_id($file)])) {
                $list[] = $value;
                $listed[spl_object_id($file)] = true;
            }
        }
        $catalog->set('AF', $list);
    }

    /**
     * The files the pages link - associated with a page or an annotation (AF), attached by an annotation
     * (FileAttachment): an invoice file becomes the new one, so that no old invoice stays in the document, and the
     * annotation that showed it stays; every other file gets what PDF/A-3 asks of it, and an attached one that nothing
     * associates is associated with its page.
     *
     * @param list<Dictionary> $pages
     */
    private static function linkPageFiles(Document $document, Dictionary $catalog, array $pages, Reference $specification, DateTimeImmutable $now): void
    {
        $associated = self::relinkAssociated($document, $catalog, $specification, $now);
        foreach ($pages as $page) {
            $associated += self::relinkAssociated($document, $page, $specification, $now);
            $annotations = $document->resolve($page->get('Annots'));
            $attached = [];
            foreach (is_array($annotations) ? $annotations : [] as $item) {
                $annotation = $document->resolve($item);
                if (! $annotation instanceof Dictionary) {
                    continue;
                }
                $associated += self::relinkAssociated($document, $annotation, $specification, $now);
                $file = $annotation->get('FS');
                if ($annotation->name('Subtype') !== 'FileAttachment' || ! $document->resolve($file) instanceof Dictionary) {
                    continue;
                }
                if (self::isInvoice($document, '', $file)) {
                    $annotation->set('FS', $specification);

                    continue;
                }
                // An association names the file specification itself, so it has to be an object of its own.
                if (! $file instanceof Reference) {
                    $file = $document->add($file);
                    $annotation->set('FS', $file);
                }
                self::describeFile($document, new Text('attachment'), $file, $now);
                $attached[] = $file;
            }
            $list = $document->resolve($page->get('AF'));
            $list = is_array($list) ? $list : [];
            foreach ($attached as $file) {
                $id = spl_object_id($document->resolve($file));
                if (! isset($associated[$id])) {
                    $list[] = $file;
                    $associated[$id] = true;
                }
            }
            if ($list !== []) {
                $page->set('AF', $list);
            }
        }
    }

    /**
     * The associated files (AF) of the document, a page or an annotation with an invoice file replaced by the new one
     * - listed once - and the others described as PDF/A-3 asks.
     *
     * @return array<int, true> the file specifications associated, by the ids of their dictionaries
     */
    private static function relinkAssociated(Document $document, Dictionary $owner, Reference $specification, DateTimeImmutable $now): array
    {
        $associated = $document->resolve($owner->get('AF'));
        if (! is_array($associated)) {
            return [];
        }
        $list = [];
        $ids = [];
        foreach ($associated as $item) {
            $file = $document->resolve($item);
            if (! $file instanceof Dictionary) {
                continue;
            }
            if (self::isInvoice($document, '', $file)) {
                $item = $specification;
                $file = $document->resolve($specification);
            } else {
                self::describeFile($document, new Text('attachment'), $item, $now);
            }
            if ($file instanceof Dictionary && ! isset($ids[spl_object_id($file)])) {
                $ids[spl_object_id($file)] = true;
                $list[] = $item;
            }
        }
        $owner->set('AF', $list);

        return $ids;
    }

    /**
     * Gives an embedded file what PDF/A-3 asks where it is missing (ISO 19005-3, 6.8): the file name in F and UF, the
     * relationship to the document (Unspecified), the MIME type of the file (by its extension, application/octet-stream
     * otherwise) and the date of its last change (now).
     */
    private static function describeFile(Document $document, Text $name, mixed $value, DateTimeImmutable $now): void
    {
        $specification = $document->resolve($value);
        if (! $specification instanceof Dictionary) {
            return;
        }
        $file = $document->resolve($specification->get('F'));
        $unicode = $document->resolve($specification->get('UF'));
        $fileName = $unicode instanceof Text ? $unicode->utf8() : ($file instanceof Text ? $file->utf8() : $name->utf8());
        if (! $file instanceof Text) {
            $specification->set('F', Text::fromUtf8($fileName));
        }
        if (! $unicode instanceof Text) {
            $specification->set('UF', Text::fromUtf8($fileName));
        }
        if ($specification->get('AFRelationship') === null) {
            $specification->set('AFRelationship', new Name('Unspecified'));
        }

        $embedded = $document->resolve($specification->get('EF'));
        $stream = $embedded instanceof Dictionary ? $document->resolve($embedded->get('F') ?? $embedded->get('UF')) : null;
        if (! $stream instanceof Stream) {
            return;
        }
        if ($stream->dictionary->get('Subtype') === null) {
            $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            $stream->dictionary->set('Subtype', new Name(self::MIME_TYPES[$extension] ?? 'application/octet-stream'));
        }
        $parameters = $document->resolve($stream->dictionary->get('Params'));
        if (! $parameters instanceof Dictionary) {
            $parameters = new Dictionary();
            $stream->dictionary->set('Params', $parameters);
        }
        if ($parameters->get('ModDate') === null) {
            $parameters->set('ModDate', new Text(self::pdfDate($now)));
        }
    }

    /**
     * The entries of a name tree, its kids included - each node once.
     *
     * @return list<array{Text, mixed}>
     */
    private static function namesOf(Document $document, mixed $root): array
    {
        $pairs = [];
        $seen = [];
        $pending = [$root];
        while ($pending !== []) {
            $node = $document->resolve(array_pop($pending));
            if (! $node instanceof Dictionary || isset($seen[spl_object_id($node)])) {
                continue;
            }
            $seen[spl_object_id($node)] = true;
            $names = $document->resolve($node->get('Names'));
            if (is_array($names)) {
                for ($i = 0; $i + 1 < count($names); $i += 2) {
                    $name = $document->resolve($names[$i]);
                    if ($name instanceof Text) {
                        $pairs[] = [$name, $names[$i + 1]];
                    }
                }
            }
            $kids = $document->resolve($node->get('Kids'));
            foreach (is_array($kids) ? array_reverse($kids) : [] as $kid) {
                $pending[] = $kid;
            }
        }

        return $pairs;
    }

    /**
     * Whether an embedded file is an invoice file of ZUGFeRD / Factur-X - by its name in the tree or in its file
     * specification.
     */
    private static function isInvoice(Document $document, string $name, mixed $specification): bool
    {
        return self::invoiceFile($document, $name, $specification) !== null;
    }

    /**
     * The name of the invoice file an embedded file is (INVOICE_FILES, in lower case) - by its name in the tree or in
     * its file specification; null for another file.
     */
    private static function invoiceFile(Document $document, string $name, mixed $specification): ?string
    {
        $names = [$name];
        $specification = $document->resolve($specification);
        if ($specification instanceof Dictionary) {
            foreach (['UF', 'F'] as $key) {
                $value = $document->resolve($specification->get($key));
                if ($value instanceof Text) {
                    $names[] = $value->utf8();
                }
            }
        }
        foreach ($names as $candidate) {
            $candidate = strtolower(trim($candidate));
            if (in_array($candidate, self::INVOICE_FILES, true)) {
                return $candidate;
            }
        }

        return null;
    }

    /**
     * The name and version of this package as creator and producer of a PDF, e.g. "DealerWeb E-Invoice 1.1.0" - the
     * version Composer installed, without it where Composer does not know the package.
     */
    public static function tool(): string
    {
        static $tool = null;
        if ($tool === null) {
            $version = null;
            if (class_exists(InstalledVersions::class) && InstalledVersions::isInstalled('dealerweb/einvoice')) {
                $version = InstalledVersions::getPrettyVersion('dealerweb/einvoice');
            }
            $tool = self::TOOL . (is_string($version) && $version !== '' ? ' ' . ltrim($version, 'vV') : '');
        }

        return $tool;
    }

    /**
     * The document information in UTF-8, from the information dictionary of the PDF: title (the one given where the
     * PDF has none), author, subject, keywords, the creation date and now as date of the change. Creator and producer
     * are always this package - it writes the final file, whatever made the PDF before. What XML does not allow is left out - control characters,
     * noncharacters -, line breaks are line feeds (as XML reads them) and a text keeps at most 8,000 characters.
     *
     * @return array{title: string, author: string, subject: string, keywords: string, creator: string, producer: string, created: DateTimeImmutable, modified: DateTimeImmutable}
     */
    private static function information(Document $document, string $title, DateTimeImmutable $now): array
    {
        $info = $document->resolve($document->trailer->get('Info'));
        $text = static function (string $key) use ($document, $info): string {
            $value = $info instanceof Dictionary ? $document->resolve($info->get($key)) : null;

            return $value instanceof Text ? self::clean($value->utf8()) : '';
        };
        $created = $info instanceof Dictionary ? $document->resolve($info->get('CreationDate')) : null;

        return [
            'title' => $text('Title') !== '' ? $text('Title') : self::clean($title),
            'author' => $text('Author'),
            'subject' => $text('Subject'),
            'keywords' => $text('Keywords'),
            'creator' => self::tool(),
            'producer' => self::tool(),
            'created' => ($created instanceof Text ? self::parseDate($created->bytes) : null) ?? $now,
            'modified' => $now,
        ];
    }

    /**
     * A text for the document information and the XMP metadata alike.
     */
    private static function clean(string $value): string
    {
        $value = mb_scrub($value, 'UTF-8');
        $value = preg_replace('/\r\n?/', "\n", $value) ?? $value;
        $value = preg_replace('/[^\x{9}\x{A}\x{20}-\x{7E}\x{A0}-\x{D7FF}\x{E000}-\x{FFFD}\x{10000}-\x{10FFFF}]/u', '', $value) ?? $value;

        return trim(mb_substr($value, 0, self::TEXT_LENGTH, 'UTF-8'));
    }

    /**
     * The information dictionary: the entries of the XMP metadata as they are there, the other entries of the PDF
     * as they were.
     *
     * @param array{title: string, author: string, subject: string, keywords: string, creator: string, producer: string, created: DateTimeImmutable, modified: DateTimeImmutable} $information
     */
    private static function informationDictionary(Document $document, array $information): Dictionary
    {
        $dictionary = new Dictionary();
        $info = $document->resolve($document->trailer->get('Info'));
        foreach ($info instanceof Dictionary ? $info->entries : [] as $key => $value) {
            if (! in_array((string) $key, self::INFORMATION_KEYS, true)) {
                $dictionary->set((string) $key, $value);
            }
        }
        foreach (['Title' => 'title', 'Author' => 'author', 'Subject' => 'subject', 'Keywords' => 'keywords', 'Creator' => 'creator', 'Producer' => 'producer'] as $key => $field) {
            if ($information[$field] !== '') {
                $dictionary->set($key, Text::fromUtf8($information[$field]));
            }
        }
        $dictionary->set('CreationDate', new Text(self::pdfDate($information['created'])));
        $dictionary->set('ModDate', new Text(self::pdfDate($information['modified'])));

        return $dictionary;
    }

    /**
     * The XMP metadata: PDF/A-3 identification, the document information (in agreement with the information
     * dictionary, as PDF/A asks), the extension schema of Factur-X and its properties - and what the PDF declared of
     * PDF/UA 1 and its languages.
     *
     * @param array{title: string, author: string, subject: string, keywords: string, creator: string, producer: string, created: DateTimeImmutable, modified: DateTimeImmutable} $information
     * @param array{conformance: ?string, accessible: bool, languages: list<string>} $metadata
     */
    private static function xmp(array $information, string $conformance, string $fileName, string $level, array $metadata): string
    {
        $x = static fn(string $value): string => htmlspecialchars($value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
        $alternative = static fn(string $value): string => '<rdf:Alt><rdf:li xml:lang="x-default">' . $x($value) . '</rdf:li></rdf:Alt>';

        $dublinCore = '      <dc:format>application/pdf</dc:format>' . "\n"
            . '      <dc:title>' . $alternative($information['title']) . '</dc:title>' . "\n";
        if ($information['author'] !== '') {
            $dublinCore .= '      <dc:creator><rdf:Seq><rdf:li>' . $x($information['author']) . '</rdf:li></rdf:Seq></dc:creator>' . "\n";
        }
        if ($information['subject'] !== '') {
            $dublinCore .= '      <dc:description>' . $alternative($information['subject']) . '</dc:description>' . "\n";
        }
        if ($metadata['languages'] !== []) {
            $dublinCore .= '      <dc:language><rdf:Bag>' . implode('', array_map(static fn(string $language): string => '<rdf:li>' . $x($language) . '</rdf:li>', $metadata['languages'])) . '</rdf:Bag></dc:language>' . "\n";
        }
        $keywords = $information['keywords'] !== '' ? '      <pdf:Keywords>' . $x($information['keywords']) . '</pdf:Keywords>' . "\n" : '';

        $schemas = self::extensionSchema('Factur-X PDFA Extension Schema', 'urn:factur-x:pdfa:CrossIndustryDocument:invoice:1p0#', 'fx', [
            ['DocumentFileName', 'Text', 'external', 'The name of the embedded XML document'],
            ['DocumentType', 'Text', 'external', 'The type of the hybrid document in capital letters, e.g. INVOICE or ORDER'],
            ['Version', 'Text', 'external', 'The actual version of the standard applying to the embedded XML document'],
            ['ConformanceLevel', 'Text', 'external', 'The conformance level of the embedded XML document'],
        ]);
        $accessibility = '';
        if ($metadata['accessible']) {
            // The identification schema of PDF/UA is none of the schemas PDF/A-3 predefines.
            $schemas .= self::extensionSchema('PDF/UA Universal Accessibility Schema', 'http://www.aiim.org/pdfua/ns/id/', 'pdfuaid', [
                ['part', 'Integer', 'internal', 'Indicates, which part of ISO 14289 standard is followed'],
            ]);
            $accessibility = <<<XML
                    <rdf:Description rdf:about="" xmlns:pdfuaid="http://www.aiim.org/pdfua/ns/id/">
                      <pdfuaid:part>1</pdfuaid:part>
                    </rdf:Description>

                XML;
        }

        return "<?xpacket begin=\"\u{FEFF}\" id=\"W5M0MpCehiHzreSzNTczkc9d\"?>\n" . <<<XML
            <x:xmpmeta xmlns:x="adobe:ns:meta/">
              <rdf:RDF xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#">
                <rdf:Description rdf:about="" xmlns:pdfaid="http://www.aiim.org/pdfa/ns/id/">
                  <pdfaid:part>3</pdfaid:part>
                  <pdfaid:conformance>$conformance</pdfaid:conformance>
                </rdf:Description>
            $accessibility    <rdf:Description rdf:about="" xmlns:dc="http://purl.org/dc/elements/1.1/">
            $dublinCore    </rdf:Description>
                <rdf:Description rdf:about="" xmlns:pdf="http://ns.adobe.com/pdf/1.3/">
                  <pdf:Producer>{$x($information['producer'])}</pdf:Producer>
            $keywords    </rdf:Description>
                <rdf:Description rdf:about="" xmlns:xmp="http://ns.adobe.com/xap/1.0/">
                  <xmp:CreatorTool>{$x($information['creator'])}</xmp:CreatorTool>
                  <xmp:CreateDate>{$information['created']->format(DATE_ATOM)}</xmp:CreateDate>
                  <xmp:ModifyDate>{$information['modified']->format(DATE_ATOM)}</xmp:ModifyDate>
                  <xmp:MetadataDate>{$information['modified']->format(DATE_ATOM)}</xmp:MetadataDate>
                </rdf:Description>
                <rdf:Description rdf:about="" xmlns:pdfaExtension="http://www.aiim.org/pdfa/ns/extension/" xmlns:pdfaSchema="http://www.aiim.org/pdfa/ns/schema#" xmlns:pdfaProperty="http://www.aiim.org/pdfa/ns/property#">
                  <pdfaExtension:schemas>
                    <rdf:Bag>
            $schemas          </rdf:Bag>
                  </pdfaExtension:schemas>
                </rdf:Description>
                <rdf:Description rdf:about="" xmlns:fx="urn:factur-x:pdfa:CrossIndustryDocument:invoice:1p0#">
                  <fx:DocumentType>INVOICE</fx:DocumentType>
                  <fx:DocumentFileName>$fileName</fx:DocumentFileName>
                  <fx:Version>1.0</fx:Version>
                  <fx:ConformanceLevel>$level</fx:ConformanceLevel>
                </rdf:Description>
              </rdf:RDF>
            </x:xmpmeta>
            <?xpacket end="w"?>
            XML;
    }

    /**
     * An entry of pdfaExtension:schemas: a schema that PDF/A does not predefine, with its properties.
     *
     * @param list<array{string, string, string, string}> $properties name, value type, category, description
     */
    private static function extensionSchema(string $schema, string $namespace, string $prefix, array $properties): string
    {
        $items = '';
        foreach ($properties as [$name, $type, $category, $description]) {
            $items .= <<<XML
                                <rdf:li rdf:parseType="Resource">
                                  <pdfaProperty:name>$name</pdfaProperty:name>
                                  <pdfaProperty:valueType>$type</pdfaProperty:valueType>
                                  <pdfaProperty:category>$category</pdfaProperty:category>
                                  <pdfaProperty:description>$description</pdfaProperty:description>
                                </rdf:li>

                XML;
        }

        return <<<XML
                      <rdf:li rdf:parseType="Resource">
                        <pdfaSchema:schema>$schema</pdfaSchema:schema>
                        <pdfaSchema:namespaceURI>$namespace</pdfaSchema:namespaceURI>
                        <pdfaSchema:prefix>$prefix</pdfaSchema:prefix>
                        <pdfaSchema:property>
                          <rdf:Seq>
            $items              </rdf:Seq>
                        </pdfaSchema:property>
                      </rdf:li>

            XML;
    }

    /**
     * A date of the PDF (D:YYYYMMDDHHmmSSOHH'mm') - parts left out default as the specification says, a date
     * without time zone counts as UTC. Null if it is none.
     */
    private static function parseDate(string $value): ?DateTimeImmutable
    {
        if (preg_match("/^D?:?(\\d{4})(\\d{2})?(\\d{2})?(\\d{2})?(\\d{2})?(\\d{2})?(?:([Zz+\\-])(\\d{2})?'?(\\d{2})?'?)?$/", trim($value), $match) !== 1) {
            return null;
        }
        $zone = 'UTC';
        if (($match[7] ?? '') === '+' || ($match[7] ?? '') === '-') {
            $zone = $match[7] . str_pad($match[8] ?? '0', 2, '0', STR_PAD_LEFT) . ':' . str_pad($match[9] ?? '0', 2, '0', STR_PAD_LEFT);
        }
        try {
            $date = new DateTimeImmutable(sprintf(
                '%s-%s-%s %s:%s:%s',
                $match[1],
                ($match[2] ?? '') !== '' ? $match[2] : '01',
                ($match[3] ?? '') !== '' ? $match[3] : '01',
                ($match[4] ?? '') !== '' ? $match[4] : '00',
                ($match[5] ?? '') !== '' ? $match[5] : '00',
                ($match[6] ?? '') !== '' ? $match[6] : '00',
            ), new DateTimeZone($zone));
        } catch (Exception) {
            return null;
        }

        return $date->format('YmdHis') === $match[1] . (($match[2] ?? '') ?: '01') . (($match[3] ?? '') ?: '01') . (($match[4] ?? '') ?: '00') . (($match[5] ?? '') ?: '00') . (($match[6] ?? '') ?: '00') ? $date : null;
    }

    private static function pdfDate(DateTimeImmutable $date): string
    {
        return 'D:' . $date->format('YmdHis') . str_replace(':', "'", $date->format('P')) . "'";
    }

    private static function compress(string $data): string
    {
        $compressed = gzcompress($data, 9);
        if ($compressed === false) {
            throw new InvalidPdf('Compressing the data failed.');
        }

        return $compressed;
    }
}
