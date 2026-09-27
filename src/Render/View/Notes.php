<?php

declare(strict_types=1);

namespace Dealerweb\EInvoice\Render\View;

use Dealerweb\EInvoice\CodeList;
use Dealerweb\EInvoice\UnmappedValue;

/**
 * The notes of the invoice with their subject, as rows of a table - a note of a page does not fit one row.
 *
 * @internal
 */
final class Notes extends Section
{
    /** Subject of a note that carries the title of the document (UNTDID 4451 "Title"). */
    private const TITLE_NOTE = 'AFM';

    /**
     * Notes with their subject; the XRechnung convention "#ADU#text" becomes subject and text.
     * Additional legal information of the issuer too long for the footer is the last note.
     *
     * @return list<array{rows: list<array{class: string, key: string|null, text: string|null, extras: list<array{0: string, 1: string}>}>}>
     */
    public function build(?string $legal, string $heading): array
    {
        $notes = [];
        foreach ($this->groups($this->data['INVOICE_NOTE'] ?? null) as $index => $note) {
            $code = $this->value($note['Invoice_note_subject_code'] ?? null);
            $text = (string) $this->text($note['Invoice_note'] ?? null);

            if ($code === null && preg_match('/^#([A-Za-z]{3})#(.*)$/s', $text, $match)) {
                $code = strtoupper($match[1]);
                $text = $this->clean($match[2]);
            }

            // A note "Title: Rechnung" only repeats the heading of the document.
            if ($code === self::TITLE_NOTE && $this->sameText($text, $heading)) {
                continue;
            }

            // The code of the content (BT-X-5) joins the text: "2351249 (Auftragsnummer)".
            $contentCode = null;
            $values = array_values(array_filter($this->extras->attached('note:' . $index), function (UnmappedValue $value) use (&$contentCode): bool {
                if ($value->id === ExtendedFields::NOTE_CONTENT_CODE && $contentCode === null) {
                    $contentCode = $this->clean($value->value);

                    return false;
                }

                return true;
            }));
            $text = $text === '' ? (string) $contentCode : $this->codedText($text, $contentCode, null);

            $extras = $this->extraRows($values, $this->extras->depth('note:' . $index));
            if ($text === '' && $extras === []) {
                continue;
            }

            $notes[] = ['subject' => $code === null ? null : $this->codeName(CodeList::TextSubject, $code), 'text' => $text, 'extras' => $extras];
        }

        if ($legal !== null) {
            $notes[] = ['subject' => $this->texts->get('party.legal'), 'text' => $legal, 'extras' => []];
        }

        return array_map(fn(array $note): array => ['rows' => $this->rows($note['subject'], $note['text'], $note['extras'])], $notes);
    }

    /**
     * A note as rows of a table, each with a few lines of its text: dompdf never splits a row, and a
     * note of a page (a sender puts the whole letter into it) would run over the footer. A paragraph of
     * pages without line breaks is split at blanks as well. The subject stands in the first row, the
     * additional values in the last one.
     *
     * @param list<array{0: string, 1: string}> $extras
     * @return list<array{class: string, key: string|null, text: string|null, extras: list<array{0: string, 1: string}>}>
     */
    private function rows(?string $subject, string $text, array $extras): array
    {
        $rows = [];
        $lines = $text === '' ? [] : array_map(static fn(string $line): string => $line === '' ? "\u{00A0}" : $line, explode("\n", $text));
        foreach (array_chunk($lines, TextLayout::NOTE_LINES_PER_ROW) as $chunk) {
            $chunk = implode("\n", $chunk);
            $pieces = TextLayout::lineCount($chunk, TextLayout::NOTE_WIDTH) > TextLayout::SPLIT_LINES
                ? TextLayout::pieces($chunk, TextLayout::NOTE_WIDTH, TextLayout::NOTE_LINES_PER_ROW) : [$chunk];
            foreach ($pieces as $piece) {
                $rows[] = ['class' => '', 'key' => $subject === null ? null : '', 'text' => $piece, 'extras' => []];
            }
        }
        if ($extras !== []) {
            $rows[] = ['class' => '', 'key' => $subject === null ? null : '', 'text' => null, 'extras' => $extras];
        }
        if ($rows === []) {
            return [];
        }

        $rows[0]['key'] = $subject;
        $rows[0]['class'] = 'first';
        $last = array_key_last($rows);
        $rows[$last]['class'] = trim($rows[$last]['class'] . ' last');

        return $rows;
    }
}
