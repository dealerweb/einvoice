<?php

declare(strict_types=1);

namespace Dealerweb\EInvoice;

use Dealerweb\EInvoice\Exception\UnsupportedDocument;
use DOMDocument;

/**
 * The concrete XML syntaxes of EN 16931 (CEN/TS 16931-3-2 and 16931-3-3).
 */
enum Syntax: string
{
    case UblInvoice = 'ubl-invoice';
    case UblCreditNote = 'ubl-creditnote';
    case Cii = 'cii';

    private const ROOTS = [
        'urn:oasis:names:specification:ubl:schema:xsd:Invoice-2|Invoice' => self::UblInvoice,
        'urn:oasis:names:specification:ubl:schema:xsd:CreditNote-2|CreditNote' => self::UblCreditNote,
        'urn:un:unece:uncefact:data:standard:CrossIndustryInvoice:100|CrossIndustryInvoice' => self::Cii,
    ];

    /** Legacy formats that predate EN 16931 and cannot be mapped to it. */
    private const LEGACY = [
        'urn:ferd:CrossIndustryDocument:invoice:1p0' => 'ZUGFeRD 1.0',
    ];

    public static function detect(DOMDocument $document): self
    {
        $root = $document->documentElement;
        if ($root === null) {
            throw new UnsupportedDocument('The document is empty.');
        }

        $syntax = self::ROOTS[$root->namespaceURI . '|' . $root->localName] ?? null;
        if ($syntax !== null) {
            return $syntax;
        }

        // a root without namespace has none (null) - never an array offset (deprecated since PHP 8.5)
        $legacy = self::LEGACY[$root->namespaceURI ?? ''] ?? null;
        if ($legacy !== null) {
            throw new UnsupportedDocument($legacy . ' predates EN 16931 and is not supported.');
        }

        throw new UnsupportedDocument("Unknown root element {{$root->namespaceURI}}{$root->localName}.");
    }

    /**
     * The compiled KoSIT mapping for this syntax (resources/compiled/*.php).
     *
     * @internal used by Invoice::fromXml()
     *
     * @return array<string, mixed>
     */
    public function program(): array
    {
        static $programs = [];

        return $programs[$this->value] ??= require dirname(__DIR__) . '/resources/compiled/' . $this->value . '.php';
    }
}
