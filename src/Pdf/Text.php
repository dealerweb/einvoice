<?php

declare(strict_types=1);

namespace Dealerweb\EInvoice\Pdf;

/**
 * A string object - its bytes, whether written literal "(...)" or hexadecimal "<...>".
 *
 * @internal
 */
final class Text
{
    /**
     * The bytes where PDFDocEncoding differs from Latin-1 and their Unicode code points (PDF 1.7, annex D.2).
     */
    private const PDF_DOC_ENCODING = [
        0x18 => 0x02D8, 0x19 => 0x02C7, 0x1A => 0x02C6, 0x1B => 0x02D9, 0x1C => 0x02DD, 0x1D => 0x02DB, 0x1E => 0x02DA,
        0x1F => 0x02DC, 0x80 => 0x2022, 0x81 => 0x2020, 0x82 => 0x2021, 0x83 => 0x2026, 0x84 => 0x2014, 0x85 => 0x2013,
        0x86 => 0x0192, 0x87 => 0x2044, 0x88 => 0x2039, 0x89 => 0x203A, 0x8A => 0x2212, 0x8B => 0x2030, 0x8C => 0x201E,
        0x8D => 0x201C, 0x8E => 0x201D, 0x8F => 0x2018, 0x90 => 0x2019, 0x91 => 0x201A, 0x92 => 0x2122, 0x93 => 0xFB01,
        0x94 => 0xFB02, 0x95 => 0x0141, 0x96 => 0x0152, 0x97 => 0x0160, 0x98 => 0x0178, 0x99 => 0x017D, 0x9A => 0x0131,
        0x9B => 0x0142, 0x9C => 0x0153, 0x9D => 0x0161, 0x9E => 0x017E, 0xA0 => 0x20AC,
    ];

    /**
     * The language escape of a Unicode text string (7.9.2.2): ESC, an ISO 639 language code, perhaps an ISO 3166
     * country code, ESC - it names the language of what follows (for reading aloud, or the glyphs of CJK text) and is
     * not shown. Bytes alone: in UTF-8 no other character holds 0x1B.
     */
    private const LANGUAGE_ESCAPE = '/\x1B[A-Za-z]{2,3}(?:[A-Za-z]{2})?\x1B/';

    public function __construct(public readonly string $bytes) {}

    /**
     * A text string (PDF 1.7, 7.9.2.2) as UTF-8: UTF-16BE with byte order mark, UTF-8 with byte order mark (PDF 2.0)
     * or PDFDocEncoding - and UTF-16LE with byte order mark, which the standard does not know but writers use and
     * readers (PDF.js, PDFium) read. A last odd byte of UTF-16 is dropped, as they do; the language escapes of a
     * Unicode text are left out, as readers do not show them.
     */
    public function utf8(): string
    {
        foreach (["\xFE\xFF" => 'UTF-16BE', "\xFF\xFE" => 'UTF-16LE'] as $mark => $encoding) {
            if (str_starts_with($this->bytes, $mark)) {
                $data = substr($this->bytes, 2);

                return self::withoutLanguageEscapes(mb_convert_encoding(substr($data, 0, strlen($data) - strlen($data) % 2), 'UTF-8', $encoding));
            }
        }
        if (str_starts_with($this->bytes, "\xEF\xBB\xBF")) {
            return self::withoutLanguageEscapes(mb_scrub(substr($this->bytes, 3), 'UTF-8'));
        }

        $text = '';
        foreach (str_split($this->bytes) as $byte) {
            $code = ord($byte);
            $text .= mb_chr(self::PDF_DOC_ENCODING[$code] ?? $code, 'UTF-8');
        }

        return $text;
    }

    /**
     * A text string for a UTF-8 value: printable ASCII as it is, anything else as UTF-16BE with byte order mark.
     */
    public static function fromUtf8(string $value): self
    {
        return new self(preg_match('/^[\x20-\x7E]*$/', $value) === 1 ? $value : "\xFE\xFF" . mb_convert_encoding($value, 'UTF-16BE', 'UTF-8'));
    }

    private static function withoutLanguageEscapes(string $text): string
    {
        return preg_replace(self::LANGUAGE_ESCAPE, '', $text) ?? $text;
    }
}
