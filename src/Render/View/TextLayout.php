<?php

declare(strict_types=1);

namespace Dealerweb\EInvoice\Render\View;

/**
 * Estimates how many lines a text takes in a column and splits long texts into parts for rows of their own:
 * dompdf never splits a table row, and a row higher than the space left on a page is cut off at its foot.
 *
 * @internal
 */
final class TextLayout
{
    /** Lines of a note per row of its table. */
    public const NOTE_LINES_PER_ROW = 5;

    /** Characters of a line of a note, on the safe side (the column holds about 80). */
    public const NOTE_WIDTH = 70;

    /**
     * Lines up to which a row of a note or a line stays one row: at most about half a page, which moves to the
     * next page as a whole. Only a longer text is split - a page holds about 69 lines.
     */
    public const SPLIT_LINES = 40;

    /** Lines of the item column per row of a line that is split. */
    public const ITEM_LINES_PER_ROW = 8;

    /** Characters of a line of the item column, on the safe side (it holds about 34, without article column 49). */
    public const ITEM_WIDTH = 30;

    /**
     * Whether a text in the item column takes more than half a page (SPLIT_LINES).
     */
    public static function isLong(string $text): bool
    {
        return self::lineCount($text, self::ITEM_WIDTH) > self::SPLIT_LINES;
    }

    /**
     * The texts of the item column as parts: one as long as they fit into half a page (SPLIT_LINES),
     * otherwise parts of at most ITEM_LINES_PER_ROW lines - the first stands in the row of the line, every
     * further one in a row of its own. A description of pages (a sender lists every copier of a contract in
     * it) would otherwise run over the footer and be cut off at the foot of the page.
     *
     * @param list<array{class: string, text: string}> $blocks
     * @return list<list<array{class: string, text: string}>>
     */
    public static function itemParts(array $blocks): array
    {
        $lines = 0;
        foreach ($blocks as $block) {
            $lines += self::lineCount($block['text'], self::ITEM_WIDTH);
        }
        if ($lines <= self::SPLIT_LINES) {
            return [$blocks];
        }

        $parts = [];
        $part = [];
        $free = self::ITEM_LINES_PER_ROW;
        foreach ($blocks as $block) {
            if ($part !== [] && $free < 1) {
                $parts[] = $part;
                $part = [];
                $free = self::ITEM_LINES_PER_ROW;
            }
            foreach (self::pieces($block['text'], self::ITEM_WIDTH, self::ITEM_LINES_PER_ROW, $free) as $index => $piece) {
                if ($index > 0) {
                    $parts[] = $part;
                    $part = [];
                    $free = self::ITEM_LINES_PER_ROW;
                }
                $part[] = ['class' => $block['class'], 'text' => $piece];
                $free -= self::lineCount($piece, self::ITEM_WIDTH);
            }
        }

        return $part === [] ? $parts : [...$parts, $part];
    }

    /**
     * A text of a table cell as its first part and the parts for rows of their own below it - a text of more
     * than half a page (SPLIT_LINES) would be cut at its foot.
     *
     * @return array{0: string|null, 1: list<string>}
     */
    public static function cellParts(?string $text): array
    {
        if ($text === null || ! self::isLong($text)) {
            return [$text, []];
        }

        $pieces = self::pieces($text, self::ITEM_WIDTH, self::NOTE_LINES_PER_ROW);

        return [$pieces[0] ?? null, array_slice($pieces, 1)];
    }

    /**
     * A text in pieces of at most $lines lines (the first of at most $first), estimated with $width
     * characters a line and cut at blanks and line breaks - together the pieces are the whole text.
     *
     * @return list<string>
     */
    public static function pieces(string $text, int $width, int $lines, ?int $first = null): array
    {
        $limit = max(1, $first ?? $lines);
        $count = 0;
        $offset = 0;
        $cuts = [];
        foreach (explode("\n", $text) as $paragraph) {
            foreach (self::wrap($paragraph, $width) as $start) {
                if ($count >= $limit) {
                    $cuts[] = $offset + $start;
                    $count = 0;
                    $limit = $lines;
                }
                $count++;
            }
            $offset += mb_strlen($paragraph) + 1;
        }

        $pieces = [];
        $from = 0;
        foreach ([...$cuts, mb_strlen($text)] as $cut) {
            $piece = rtrim(mb_substr($text, $from, $cut - $from));
            if ($piece !== '') {
                $pieces[] = $piece;
            }
            $from = $cut;
        }

        return $pieces;
    }

    /**
     * Estimated number of lines of a text in a column of $width characters.
     */
    public static function lineCount(string $text, int $width): int
    {
        $count = 0;
        foreach (explode("\n", $text) as $paragraph) {
            $count += count(self::wrap($paragraph, $width));
        }

        return $count;
    }

    /**
     * Where the lines of a paragraph start when it wraps at blanks to $width characters - an estimate of
     * the layout; a word longer than a line is cut.
     *
     * @return non-empty-list<int>
     */
    private static function wrap(string $paragraph, int $width): array
    {
        $starts = [0];
        $length = mb_strlen($paragraph);
        $position = 0;
        while ($length - $position > $width) {
            $space = mb_strrpos(mb_substr($paragraph, $position, $width + 1), ' ');
            $position += $space === false || $space === 0 ? $width : $space + 1;
            $starts[] = $position;
        }

        return $starts;
    }
}
