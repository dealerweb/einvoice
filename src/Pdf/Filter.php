<?php

declare(strict_types=1);

namespace Dealerweb\EInvoice\Pdf;

use Closure;
use Dealerweb\EInvoice\Exception\InvalidPdf;

/**
 * Decodes the data of a stream as far as reading the structure of a PDF and its attachments needs it: FlateDecode,
 * with or without the PNG predictors of cross-reference streams, one filter or a chain.
 *
 * The data decoded is limited: a few kilobytes of compressed data can hold gigabytes (a "zip bomb"), and a limit
 * refuses them before the memory runs out.
 *
 * @internal
 */
final class Filter
{
    /** How much a stream may decode to where the caller sets no other limit (16 MiB). */
    public const LIMIT = 16777216;

    /** Compressed data is inflated in pieces of this size - one piece holds at most about 1,000 times as much. */
    private const CHUNK = 4096;

    /**
     * @param int $limit the most bytes the data may decode to
     * @param (Closure(mixed): mixed)|null $resolve the object a reference refers to - a filter and its parameters may
     *                                              be indirect objects of the document
     * @throws InvalidPdf an unsupported filter, damaged data or more data than the limit
     */
    public static function decode(Stream $stream, int $limit = self::LIMIT, ?Closure $resolve = null): string
    {
        $resolve ??= static fn(mixed $value): mixed => $value;
        $filters = $resolve($stream->dictionary->get('Filter'));
        $parameters = $resolve($stream->dictionary->get('DecodeParms'));
        $filters = $filters === null ? [] : (is_array($filters) ? $filters : [$filters]);
        $parameters = is_array($parameters) ? $parameters : [$parameters];

        $data = $stream->data;
        foreach ($filters as $index => $filter) {
            $filter = $resolve($filter);
            $name = $filter instanceof Name ? $filter->value : '';
            if ($name !== 'FlateDecode' && $name !== 'Fl') {
                throw new InvalidPdf("The PDF uses the filter $name, which cannot be read.");
            }
            $data = self::unpredict(self::inflate($data, $limit), $resolve($parameters[$index] ?? null), $resolve);
        }

        return $data;
    }

    /**
     * Inflates zlib data (FlateDecode), also where writers used raw deflate data or gzip. Data cut off before its end
     * gives what it holds, as readers take it.
     */
    private static function inflate(string $data, int $limit): string
    {
        $encoding = match (true) {
            str_starts_with($data, "\x1F\x8B") => ZLIB_ENCODING_GZIP,
            strlen($data) >= 2 && (ord($data[0]) & 0x0F) === 8 && (ord($data[0]) * 256 + ord($data[1])) % 31 === 0 => ZLIB_ENCODING_DEFLATE,
            default => ZLIB_ENCODING_RAW,
        };
        $context = inflate_init($encoding);
        if ($context === false) {
            throw new InvalidPdf('A compressed stream of the PDF cannot be read.');
        }

        $output = '';
        $length = strlen($data);
        for ($offset = 0; $offset < $length; $offset += self::CHUNK) {
            $last = $offset + self::CHUNK >= $length;
            $part = @inflate_add($context, substr($data, $offset, self::CHUNK), $last ? ZLIB_FINISH : ZLIB_SYNC_FLUSH);
            if ($part === false) {
                throw new InvalidPdf('A compressed stream of the PDF is damaged.');
            }
            $output .= $part;
            if (strlen($output) > $limit) {
                throw new InvalidPdf('A compressed stream of the PDF holds more data than can be read.');
            }
            if (inflate_get_status($context) === ZLIB_STREAM_END) {
                break;
            }
        }
        if ($output === '' && $length > 0 && inflate_get_status($context) !== ZLIB_STREAM_END) {
            throw new InvalidPdf('A compressed stream of the PDF is damaged.');
        }

        return $output;
    }

    /**
     * Reverses a predictor (DecodeParms /Predictor): none (1), or one of the PNG predictors (10 to 15) per row.
     */
    /**
     * @param Closure(mixed): mixed $resolve
     */
    private static function unpredict(string $data, mixed $parameters, Closure $resolve): string
    {
        if (! $parameters instanceof Dictionary) {
            return $data;
        }
        $predictor = $resolve($parameters->get('Predictor'));
        if (! is_int($predictor) || $predictor <= 1) {
            return $data;
        }
        if ($predictor < 10) {
            throw new InvalidPdf('The PDF uses the TIFF predictor, which cannot be read.');
        }

        $colors = $resolve($parameters->get('Colors')) ?? 1;
        $bits = $resolve($parameters->get('BitsPerComponent')) ?? 8;
        $columns = $resolve($parameters->get('Columns')) ?? 1;
        $length = strlen($data);
        // A row is never longer than the data - more columns are a damaged or hostile file.
        if (! is_int($colors) || $colors < 1 || $colors > 32 || ! in_array($bits, [1, 2, 4, 8, 16], true) || ! is_int($columns) || $columns < 1 || $columns > intdiv(8 * $length, $colors * $bits) + 1) {
            throw new InvalidPdf('The predictor of a stream of the PDF is damaged.');
        }
        $pixel = max(1, intdiv($colors * $bits + 7, 8));
        $row = intdiv($colors * $bits * $columns + 7, 8);

        $result = '';
        $previous = str_repeat("\0", $row);
        for ($offset = 0; $offset + 1 <= $length; $offset += $row + 1) {
            $type = ord($data[$offset]);
            $line = str_pad(substr($data, $offset + 1, $row), $row, "\0");
            $current = '';
            for ($i = 0; $i < $row; $i++) {
                $left = $i >= $pixel ? ord($current[$i - $pixel]) : 0;
                $up = ord($previous[$i]);
                $upLeft = $i >= $pixel ? ord($previous[$i - $pixel]) : 0;
                $value = ord($line[$i]);
                $value += match ($type) {
                    0 => 0,
                    1 => $left,
                    2 => $up,
                    3 => intdiv($left + $up, 2),
                    4 => self::paeth($left, $up, $upLeft),
                    default => throw new InvalidPdf('A compressed stream of the PDF is damaged.'),
                };
                $current .= chr($value & 0xFF);
            }
            $result .= $current;
            $previous = $current;
        }

        return $result;
    }

    private static function paeth(int $left, int $up, int $upLeft): int
    {
        $estimate = $left + $up - $upLeft;
        $distanceLeft = abs($estimate - $left);
        $distanceUp = abs($estimate - $up);
        $distanceUpLeft = abs($estimate - $upLeft);
        if ($distanceLeft <= $distanceUp && $distanceLeft <= $distanceUpLeft) {
            return $left;
        }

        return $distanceUp <= $distanceUpLeft ? $up : $upLeft;
    }
}
