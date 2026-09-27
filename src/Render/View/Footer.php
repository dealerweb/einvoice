<?php

declare(strict_types=1);

namespace Dealerweb\EInvoice\Render\View;

use Dealerweb\EInvoice\CodeList;

/**
 * The legal data of the issuer in the footer, and the geometry of the page around it: margins, position of the
 * footer and of the line with the page number.
 *
 * @internal
 */
final class Footer extends Section
{
    /** The page (A4) in millimetres: height, margins; the text is 174 mm wide. */
    private const PAGE_HEIGHT = 297.0;
    private const MARGIN_TOP = 12.0;
    private const MARGIN_RIGHT = 18.0;
    private const MARGIN_LEFT = 18.0;
    private const TEXT_WIDTH = 174.0;

    /**
     * The footer: free space below it, height of one of its lines (6.5 pt at line-height 1.35 -
     * measured in the PDF of dompdf: 3.96 mm, not the 3.1 mm the stylesheet suggests), space below
     * its rule.
     */
    private const FOOTER_BOTTOM = 10.0;
    private const FOOTER_LINE = 3.96;
    private const FOOTER_PADDING = 2.0;

    /** The line with document and page number above the rule of the footer, and the space above it. */
    private const PAGE_LINE = 4.5;
    private const CONTENT_GAP = 4.0;

    /** Characters of a footer line per millimetre of column width (6.5 pt DejaVu Sans). */
    private const FOOTER_CHARS_PER_MM = 0.78;

    /**
     * Lines of the footer on every page - also the height the additional legal information (BT-33)
     * may make it grow to. A higher footer stands once, at the end of the document.
     */
    private const MAX_FOOTER_LINES = 8;

    /** Additional legal information (BT-33) longer than this stands in the notes, not in the footer. */
    private const MAX_FOOTER_LEGAL = 180;

    /**
     * The legal data of the issuer for the footer of every page, in columns: name and address;
     * register and additional legal information; tax numbers, identifiers and electronic address.
     * Customer and supplier number stand in the information block, the bank account with the payment.
     *
     * @param array<string, mixed>|null $issuer
     * @return array{columns: list<list<array{label: string|null, text: string, strong: bool}>>, lines: int, fixed: bool, legal: string|null}
     */
    public function build(?array $issuer): array
    {
        if ($issuer === null) {
            return ['columns' => [], 'lines' => 0, 'fixed' => true, 'legal' => null];
        }

        $row = static fn(?string $label, string $text, bool $strong = false): array => ['label' => $label, 'text' => $text, 'strong' => $strong];

        $address = $issuer['name'] === null ? [] : [$row(null, $issuer['name'], true)];
        foreach ($this->filled([$issuer['tradingName'], ...$issuer['address'], $issuer['country']]) as $line) {
            $address[] = $row(null, $line);
        }

        $register = [];
        foreach ($issuer['registration'] as $registration) {
            [$label, $text] = $this->schemed('party.registration', $registration['value'], $registration['scheme'], CodeList::IdentifierScheme, CodeList::ElectronicAddressScheme);
            $register[] = $row($label, $text);
        }

        $tax = [];
        foreach ([['party.vat_id', $issuer['vatId']], ['party.tax_number', $issuer['taxNumber']]] as [$key, $value]) {
            if ($value !== null) {
                $tax[] = $row($this->texts->get($key), $value);
            }
        }
        foreach ($issuer['identifiers'] as $identifier) {
            if ($identifier['scheme'] !== null) {
                [$label, $text] = $this->schemed('party.identifier', $identifier['value'], $identifier['scheme'], CodeList::IdentifierScheme, CodeList::ElectronicAddressScheme);
                $tax[] = $row($label, $text);
            }
        }
        $electronic = [];
        foreach ($issuer['electronic'] as $electronicAddress) {
            $this->addElectronicAddress($electronic, $electronicAddress);
        }
        foreach ($electronic as [$label, $text]) {
            $tax[] = $row($label, $text);
        }
        $tax = array_values(array_unique($tax, SORT_REGULAR));

        // Short additional legal information (managing director, register court) belongs to the
        // register; long text would make the footer take much of every page - it becomes a note.
        $legal = $issuer['legal'];
        if ($legal !== null && mb_strlen($legal) <= self::MAX_FOOTER_LEGAL) {
            $withLegal = [...$register, $row(null, $legal)];
            $columns = array_values(array_filter([$address, $withLegal, $tax]));
            $lines = $this->lines($columns);
            if ($lines <= self::MAX_FOOTER_LINES) {
                return ['columns' => $columns, 'lines' => $lines, 'fixed' => true, 'legal' => null];
            }
        }

        $columns = array_values(array_filter([$address, $register, $tax]));
        $lines = $this->lines($columns);

        // Higher than that (dozens of identifiers), it would take much of every page - or more than a
        // page, and dompdf would break pages without end.
        return ['columns' => $columns, 'lines' => $lines, 'fixed' => $lines <= self::MAX_FOOTER_LINES, 'legal' => $legal];
    }

    /**
     * Margins of the page and position of the footer in millimetres: the footer is as high as its
     * longest column, the text of every page ends above the line with the page number.
     *
     * @return array{marginTop: float, marginRight: float, marginBottom: float, marginLeft: float, footerTop: float, pageLineTop: float}
     */
    public function page(int $footerLines): array
    {
        $footerTop = self::PAGE_HEIGHT - self::FOOTER_BOTTOM - $footerLines * self::FOOTER_LINE - self::FOOTER_PADDING;
        $pageLineTop = $footerTop - self::PAGE_LINE;

        return [
            'marginTop' => self::MARGIN_TOP,
            'marginRight' => self::MARGIN_RIGHT,
            'marginBottom' => round(self::PAGE_HEIGHT - $pageLineTop + self::CONTENT_GAP, 1),
            'marginLeft' => self::MARGIN_LEFT,
            // relative to the top of the text area, where dompdf places a fixed element
            'footerTop' => round($footerTop - self::MARGIN_TOP, 1),
            // from the top edge of the sheet, for the page numbers drawn by PdfRenderer
            'pageLineTop' => round($pageLineTop, 1),
        ];
    }

    /**
     * Estimated height of the footer in lines: its longest column, long values wrapped.
     *
     * @param list<list<array{label: string|null, text: string, strong: bool}>> $columns
     */
    private function lines(array $columns): int
    {
        if ($columns === []) {
            return 0;
        }

        $characters = max(12, (int) floor((self::TEXT_WIDTH / count($columns) - 3) * self::FOOTER_CHARS_PER_MM));
        $height = 0;
        foreach ($columns as $column) {
            $lines = 0;
            foreach ($column as $row) {
                foreach (explode("\n", ($row['label'] !== null ? $row['label'] . ' ' : '') . $row['text']) as $line) {
                    $lines += max(1, (int) ceil(mb_strlen($line) * ($row['strong'] ? 1.15 : 1) / $characters));
                }
            }
            $height = max($height, $lines);
        }

        return $height;
    }
}
