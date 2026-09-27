<?php

declare(strict_types=1);

namespace Dealerweb\EInvoice\Pdf;

/**
 * Writes a document as a new PDF 1.7 file in one revision: the header with the binary comment PDF/A asks for, the
 * objects the trailer reaches (numbered anew, without the leftovers of earlier revisions, object streams dissolved),
 * one cross-reference table. Streams keep their data as stored; their Length is the length of that data.
 *
 * @internal
 */
final class Writer
{
    /**
     * @param Dictionary $trailer Root, Info, ID - Size is set here
     * @param bool $utf16 text strings in UTF-8 (with its byte order mark, as PDF 2.0 allows them) are written in
     *                    UTF-16BE, which PDF 1.7 knows - for a document read from a PDF 2.0 file
     */
    public static function write(Document $document, Dictionary $trailer, bool $utf16 = false): string
    {
        // Numbers in the order the objects are reached, the catalog first.
        $numbers = [];
        $order = [];
        $pending = array_reverse(array_values($trailer->entries));
        while ($pending !== []) {
            $value = array_pop($pending);
            if ($value instanceof Reference) {
                if (isset($numbers[$value->number]) || $document->object($value->number) === null) {
                    continue;
                }
                $numbers[$value->number] = count($order) + 1;
                $order[] = $value->number;
                $value = $document->object($value->number);
            }
            if ($value instanceof Stream) {
                $value = $value->dictionary;
            }
            if ($value instanceof Dictionary) {
                $value = $value->entries;
            }
            if (is_array($value)) {
                foreach (array_reverse($value) as $item) {
                    $pending[] = $item;
                }
            }
        }

        $output = "%PDF-1.7\n%\xE2\xE3\xCF\xD3\n";
        $offsets = [];
        foreach ($order as $index => $number) {
            $offsets[] = strlen($output);
            $output .= ($index + 1) . " 0 obj\n" . self::object($document->object($number), $numbers, $utf16) . "\nendobj\n";
        }

        $crossReferences = strlen($output);
        $output .= "xref\n0 " . (count($order) + 1) . "\n0000000000 65535 f\r\n";
        foreach ($offsets as $offset) {
            $output .= sprintf("%010d 00000 n\r\n", $offset);
        }
        $trailer = new Dictionary(['Size' => count($order) + 1] + $trailer->entries);

        return $output . "trailer\n" . self::value($trailer, $numbers, false) . "\nstartxref\n$crossReferences\n%%EOF\n";
    }

    /**
     * @param array<int, int> $numbers
     */
    private static function object(mixed $value, array $numbers, bool $utf16): string
    {
        if (! $value instanceof Stream) {
            return self::value($value, $numbers, $utf16);
        }
        $dictionary = new Dictionary($value->dictionary->entries);
        $dictionary->set('Length', strlen($value->data));

        return self::value($dictionary, $numbers, $utf16) . "\nstream\n" . $value->data . "\nendstream";
    }

    /**
     * @param array<int, int> $numbers the new number of each object reached, by its old one
     */
    private static function value(mixed $value, array $numbers, bool $utf16): string
    {
        return match (true) {
            $value === null => 'null',
            is_bool($value) => $value ? 'true' : 'false',
            is_int($value) => (string) $value,
            is_float($value) => self::real($value),
            $value instanceof Real => $value->text,
            $value instanceof Name => self::name($value->value),
            $value instanceof Text => self::text($utf16 && str_starts_with($value->bytes, "\xEF\xBB\xBF") ? Text::fromUtf8(mb_scrub(substr($value->bytes, 3), 'UTF-8'))->bytes : $value->bytes),
            // A reference to an object that does not exist is the null object.
            $value instanceof Reference => isset($numbers[$value->number]) ? $numbers[$value->number] . ' 0 R' : 'null',
            is_array($value) => '[' . implode(' ', array_map(static fn(mixed $item): string => self::value($item, $numbers, $utf16), $value)) . ']',
            $value instanceof Dictionary => '<<' . implode('', array_map(
                static fn(string $key, mixed $item): string => self::name($key) . ' ' . self::value($item, $numbers, $utf16),
                array_map('strval', array_keys($value->entries)),
                array_values($value->entries)
            )) . '>>',
            default => 'null',
        };
    }

    private static function name(string $name): string
    {
        return '/' . (preg_replace_callback('/[^\x21-\x7E]|[#()<>\[\]{}\/%]/', static fn(array $match): string => sprintf('#%02X', ord($match[0])), $name) ?? $name);
    }

    /**
     * Printable ASCII as literal string, anything else hexadecimal.
     */
    private static function text(string $bytes): string
    {
        if (preg_match('/^[\x20-\x7E]*$/', $bytes) === 1) {
            return '(' . strtr($bytes, ['\\' => '\\\\', '(' => '\\(', ')' => '\\)']) . ')';
        }

        return '<' . strtoupper(bin2hex($bytes)) . '>';
    }

    /**
     * A real number without exponent (PDF does not know it), at most six decimals.
     */
    private static function real(float $value): string
    {
        $text = rtrim(rtrim(sprintf('%.6F', $value), '0'), '.');

        return $text === '-0' ? '0' : $text;
    }
}
