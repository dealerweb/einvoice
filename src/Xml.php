<?php

declare(strict_types=1);

namespace Dealerweb\EInvoice;

use Dealerweb\EInvoice\Exception\InvalidXml;
use Dealerweb\EInvoice\Exception\UnsupportedDocument;
use DOMDocument;
use DOMXPath;
use LibXMLError;
use XMLReader;

/**
 * Parses the XML of an invoice the same way for reading and for validation: no network, no DOCTYPE (refused before
 * the document is parsed), no size limit for the text of embedded attachments, elements nested at most MAX_DEPTH
 * deep.
 *
 * @internal
 */
final class Xml
{
    /**
     * How deep elements may nest, the root element counted: what libxml reads without PARSEHUGE (256 levels below the
     * root), which lifts the limit. Far beyond any invoice - the rules take a time that grows with the square of the
     * depth.
     */
    public const MAX_DEPTH = 257;

    /**
     * @param int $options further libxml options, e.g. LIBXML_NOCDATA
     *
     * @throws InvalidXml the document is empty, not well-formed XML or nested deeper than MAX_DEPTH
     * @throws UnsupportedDocument the document declares a DOCTYPE
     */
    public static function load(string $xml, int $options = 0): DOMDocument
    {
        if (trim($xml) === '') {
            throw new InvalidXml('The document is empty.');
        }

        // An invoice never needs a DTD - refuse it before the document is parsed with PARSEHUGE, which lifts libxml's
        // limits against entity expansion. libxml reads the prolog itself, in the encoding it parses the document in.
        [$doctype] = self::collect(static fn(): ?bool => self::doctype($xml));
        if ($doctype === true) {
            throw new UnsupportedDocument('Documents with a DOCTYPE are not accepted.');
        }

        // PARSEHUGE: embedded attachments can exceed libxml's 10 MB limit for a single text node. Where libxml stopped
        // before the root element - a broken document, or an entity bomb its limits stopped - they stay in force.
        $huge = $doctype === false ? LIBXML_PARSEHUGE : 0;
        $document = new DOMDocument();
        [$loaded, $errors] = self::collect(static fn(): bool => $document->loadXML($xml, LIBXML_NONET | LIBXML_BIGLINES | $huge | $options));

        if (! $loaded) {
            // libxml's limits stop an entity bomb before the root element: it is told from a broken document by a
            // parse that goes on after the error.
            if ($doctype === null && self::recovered($xml)->doctype !== null) {
                throw new UnsupportedDocument('Documents with a DOCTYPE are not accepted.');
            }
            $error = self::reason($errors);

            throw $error === null
                ? new InvalidXml('The document is not well-formed XML.')
                : new InvalidXml('The document is not well-formed XML: ' . trim($error->message) . '.', $error->line > 0 ? $error->line : null);
        }

        if ($document->doctype !== null) {
            throw new UnsupportedDocument('Documents with a DOCTYPE are not accepted.');
        }

        if ($huge !== 0 && (new DOMXPath($document))->evaluate('boolean(/' . str_repeat('*/', self::MAX_DEPTH) . '*)') === true) {
            throw new InvalidXml('The document nests its elements deeper than ' . self::MAX_DEPTH . ' levels.');
        }

        return $document;
    }

    /**
     * The content of a file in the file system - a path or a file:// URL, no stream wrapper: those reach the network
     * (http, ftp, data, php://filter/resource=http://...), and the documents are read without it.
     *
     * @throws InvalidXml the file cannot be read
     */
    public static function readFile(string $path): string
    {
        $shown = addcslashes($path, "\0..\37");
        // PHP takes a scheme of two or more characters before "://", and "data:", as a stream wrapper.
        if ((preg_match('~^[a-z0-9+.-]{2,}://~i', $path) === 1 && stripos($path, 'file://') !== 0) || str_starts_with($path, 'data:')) {
            throw new InvalidXml("Cannot read $shown - only files are read, no stream wrapper.");
        }
        if ($path === '' || str_contains($path, "\0") || is_dir($path)) {
            throw new InvalidXml("Cannot read \"$shown\" - no file.");
        }

        $content = @file_get_contents($path);
        if ($content === false) {
            throw new InvalidXml("Cannot read $shown.");
        }

        return $content;
    }

    /**
     * Runs libxml with its errors collected: returns the result of the call and the errors it raised. Errors the
     * caller had collected before (libxml_use_internal_errors) stay collected - as libxml can only clear them all, the
     * errors of the call are cleared only where there were none before.
     *
     * @template T
     *
     * @param callable(): T $call
     *
     * @return array{T, list<LibXMLError>}
     */
    public static function collect(callable $call): array
    {
        $previous = libxml_use_internal_errors(true);
        $pending = count(libxml_get_errors());
        try {
            $result = $call();
            $errors = array_slice(libxml_get_errors(), $pending);
        } finally {
            if ($pending === 0) {
                libxml_clear_errors();
            }
            libxml_use_internal_errors($previous);
        }

        return [$result, $errors];
    }

    /**
     * Whether the document declares a DOCTYPE - read by libxml up to its root element without PARSEHUGE, so in the
     * encoding it will parse the document in (UTF-16, or UTF-7 its XML declaration names). Null: libxml stops before
     * the root element - the document is not well-formed or exceeds libxml's limits.
     */
    private static function doctype(string $xml): ?bool
    {
        $reader = new XMLReader();
        if (! $reader->XML($xml, null, LIBXML_NONET)) {
            return null;
        }

        try {
            while ($reader->read()) {
                if ($reader->nodeType === XMLReader::DOC_TYPE) {
                    return true;
                }
                if ($reader->nodeType === XMLReader::ELEMENT) {
                    return false;
                }
            }

            return null;
        } finally {
            $reader->close();
        }
    }

    /**
     * The document as far as libxml reads it without PARSEHUGE, going on after errors - its errors discarded.
     */
    private static function recovered(string $xml): DOMDocument
    {
        $document = new DOMDocument();
        $document->recover = true;
        self::collect(static fn(): bool => $document->loadXML($xml, LIBXML_NONET | LIBXML_BIGLINES));

        return $document;
    }

    /**
     * The error that made the parse fail: the first fatal one - a warning may come before it.
     *
     * @param list<LibXMLError> $errors
     */
    private static function reason(array $errors): ?LibXMLError
    {
        foreach ([LIBXML_ERR_FATAL, LIBXML_ERR_ERROR] as $level) {
            foreach ($errors as $error) {
                if ($error->level === $level) {
                    return $error;
                }
            }
        }

        return $errors[0] ?? null;
    }
}
