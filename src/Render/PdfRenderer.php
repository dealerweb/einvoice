<?php

declare(strict_types=1);

namespace Dealerweb\EInvoice\Render;

use Dealerweb\EInvoice\Document;
use Dealerweb\EInvoice\Generation\HybridPdf;
use Dealerweb\EInvoice\Invoice;
use Dealerweb\EInvoice\Labels;
use Dompdf\Canvas;
use Dompdf\Dompdf;
use Dompdf\FontMetrics;
use Dompdf\Options;
use InvalidArgumentException;

/**
 * Renders an invoice as PDF (A4) with dompdf - to read it; a PDF that carries the invoice (ZUGFeRD / Factur-X) is
 * Invoice::toPdf().
 *
 * A read invoice is shown as delivered, with everything its document holds; an invoice built or changed as the model
 * holds it. The PDF is a reading aid, not the invoice: only the XML is the original. dompdf runs with every external
 * access switched off (no remote files, no PHP, no JavaScript in the document).
 */
final class PdfRenderer
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
        $view = (new ViewBuilder($document, $this->language))->build();

        $options = new Options();
        $options->setIsRemoteEnabled(false);
        $options->setIsPhpEnabled(false);
        $options->setIsJavascriptEnabled(false);
        $options->setDefaultFont('DejaVu Sans');
        $options->setDefaultMediaType('print');
        $options->setDefaultPaperSize('a4');
        $options->setDefaultPaperOrientation('portrait');

        // U+FEFF (zero width, the byte order mark) shows nothing, but its glyph would map to it - a value PDF/A-3u
        // forbids in the map to Unicode (ISO 19005-3, 6.2.11.7.2).
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml(str_replace("\u{FEFF}", '', Template::render($view, new Texts($this->language), true)), 'UTF-8');
        $dompdf->render();
        $dompdf->addInfo('Title', str_replace("\u{FEFF}", '', $view['documentTitle']));
        $dompdf->addInfo('Creator', HybridPdf::tool());

        $this->pageLine($dompdf->getCanvas(), $view['page'], str_replace("\u{FEFF}", '', $view['pageTitle']), $view['footer']['page']);

        return (string) $dompdf->output();
    }

    /**
     * Document and "page x of y" above the legal footer of every page - drawn after the layout,
     * when the number of pages is known.
     *
     * @param array{marginLeft: float, marginRight: float, pageLineTop: float} $page positions in millimetres
     */
    private function pageLine(Canvas $canvas, array $page, string $title, string $pageText): void
    {
        $title = mb_strimwidth($title, 0, 110, '...');

        $canvas->page_script(static function (int $number, int $count, Canvas $canvas, FontMetrics $metrics) use ($page, $title, $pageText): void {
            // DejaVu Sans comes with dompdf - missing only where its fonts are missing.
            $font = $metrics->getFont('DejaVu Sans');
            if ($font === null) {
                return;
            }

            $size = 6.5;
            $color = [0.373, 0.420, 0.478];
            $millimetre = 72 / 25.4;

            $left = $page['marginLeft'] * $millimetre;
            $right = $canvas->get_width() - $page['marginRight'] * $millimetre;
            $top = $page['pageLineTop'] * $millimetre;
            $text = str_replace(['{page}', '{pages}'], [(string) $number, (string) $count], $pageText);

            $canvas->text($left, $top, $title, $font, $size, $color);
            $canvas->text($right - $metrics->getTextWidth($text, $font, $size), $top, $text, $font, $size, $color);
        });
    }
}
