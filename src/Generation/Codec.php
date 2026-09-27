<?php

declare(strict_types=1);

namespace Dealerweb\EInvoice\Generation;

use InvalidArgumentException;

/**
 * Values a syntax writes as one text although they are several fields: the note of a UBL invoice holds its subject
 * code (BT-21) and its text (BT-22) as "#AAI#text" (the practice of XRechnung, CEN/TS 16931-3-2 has no element for the
 * code). The fields are the virtual children of the element in its tree, named by their part ("#code", "#text").
 *
 * @internal
 */
final class Codec
{
    /**
     * The text of an element from the values of its parts.
     *
     * @param array<string, string> $parts the values by the name of their part
     */
    public static function encode(string $codec, array $parts): string
    {
        return match ($codec) {
            'note' => isset($parts['#code']) && $parts['#code'] !== '' ? '#' . $parts['#code'] . '#' . ($parts['#text'] ?? '') : ($parts['#text'] ?? ''),
            default => throw new InvalidArgumentException("Unknown codec $codec."),
        };
    }

    /**
     * The values of the parts of an element's text, by the name of their part - a note without a code is its text.
     *
     * @return array<string, string>
     */
    public static function decode(string $codec, string $text): array
    {
        return match ($codec) {
            'note' => preg_match('/^#([A-Z]{3})#(.*)$/s', $text, $match) === 1 ? ['#code' => $match[1], '#text' => $match[2]] : ['#text' => $text],
            default => throw new InvalidArgumentException("Unknown codec $codec."),
        };
    }
}
