<?php

declare(strict_types=1);

namespace Dealerweb\EInvoice\Render;

use Dealerweb\EInvoice\Document;
use Dealerweb\EInvoice\Invoice;
use Dealerweb\EInvoice\Labels;
use InvalidArgumentException;

/**
 * Renders an invoice as one self-contained HTML document - no scripts, no external resources,
 * styles inline - in German, English or French.
 *
 * A read invoice is shown as delivered, with everything its document holds; an invoice built or changed as the model
 * holds it. The rendering is a reading aid: only the XML is the original invoice.
 */
final class HtmlRenderer
{
    /**
     * @param string $language de, en or fr (Labels::LANGUAGES)
     * @throws InvalidArgumentException for any other language
     */
    public function __construct(private readonly string $language = 'en')
    {
        if (! in_array($language, Labels::LANGUAGES, true)) {
            throw new InvalidArgumentException("Unsupported language \"$language\", use one of " . implode(', ', Labels::LANGUAGES) . '.');
        }
    }

    /**
     * @throws InvalidArgumentException an invoice built with content no syntax holds together (the sub lines of the
     *                                  XRechnung extension beside fields of ZUGFeRD / Factur-X EXTENDED)
     */
    public function render(Invoice $invoice): string
    {
        return $this->renderDocument(Document::of($invoice));
    }

    /**
     * @internal
     */
    public function renderDocument(Document $document): string
    {
        return Template::render((new ViewBuilder($document, $this->language))->build(), new Texts($this->language), false);
    }
}
