<?php

declare(strict_types=1);

namespace Dealerweb\EInvoice\Pdf;

use Dealerweb\EInvoice\Exception\InvalidPdf;

/**
 * Reads the structure of a PDF (ISO 32000-1, 7.5): the cross-reference data - tables, cross-reference streams, both
 * in one file (hybrid) and the chain of incremental updates - and the objects, also those in object streams.
 *
 * Damaged files are read as PDF readers read them. Where an entry of the cross-reference data points to the wrong
 * place (a file changed by a tool that did not update the offsets), the file is searched for that object; the other
 * entries stay, since they know the newest revision of each object. Where the cross-reference data cannot be read at
 * all, the file is searched for all its objects. An encrypted PDF is refused.
 *
 * Reading is bounded: the lexer counts the bytes it reads, the data decoded from streams is limited, and a search of
 * the file takes one pass. A hostile file is refused instead of taking the process down.
 *
 * @internal
 */
final class Parser
{
    /** An object header "12 0 obj" (the digits no part of a longer number), or the keyword "trailer". */
    private const HEADER_OR_TRAILER = '/(?<![0-9])(\d{1,10})[\0\t\n\f\r ]{1,32}(\d{1,5})[\0\t\n\f\r ]{1,32}obj(?![A-Za-z0-9])|(?<![A-Za-z])trailer(?![A-Za-z0-9])/';

    /** The end of an object: "endobj", "stream" (the data follows) or the header of the next one if "endobj" is missing. */
    private const OBJECT_END = '/endobj|(?<![A-Za-z])stream(?![A-Za-z])|(?<![0-9])\d{1,10}[\0\t\n\f\r ]{1,32}\d{1,5}[\0\t\n\f\r ]{1,32}obj(?![A-Za-z0-9])/';

    /** The data all streams of a file may decode to together: 8 times the file, at least 32 MiB. */
    private const DECODE_FACTOR = 8;

    private const DECODE_MINIMUM = 33554432;

    /** How many of the last trailers of a damaged file are tried - one for each update it had. */
    private const TRAILERS_TRIED = 8;

    private readonly Lexer $lexer;

    private readonly int $length;

    private int $decodeBudget;

    /**
     * Where each object is: [1, offset, generation] in the file, [2, number of the object stream, index] in an object
     * stream, [0, 0, 0] deleted.
     *
     * @var array<int, array{int, int, int}>
     */
    private array $entries = [];

    /** @var array<int, array{lexer: Lexer, first: int, offsets: array<int, int>}> read object streams by number */
    private array $objectStreams = [];

    /** @var array<int, true> the object streams being decoded - a parameter of one cannot lie in one of them */
    private array $decoding = [];

    private ?Document $document = null;

    private function __construct(string $data)
    {
        $this->lexer = new Lexer($data);
        $this->length = strlen($data);
        $this->decodeBudget = max(self::DECODE_MINIMUM, self::DECODE_FACTOR * $this->length);
    }

    /**
     * @throws InvalidPdf no PDF, damaged beyond repair or encrypted
     */
    public static function parse(string $data): Document
    {
        // Readers accept bytes before the header - the offsets then count from the header.
        $start = strpos(substr($data, 0, 1024), '%PDF-');
        if ($start === false) {
            throw new InvalidPdf('The file is no PDF.');
        }
        $parser = new self(substr($data, $start));

        try {
            $trailer = $parser->readCrossReferences();
            $parser->repairEntries();
        } catch (InvalidPdf $exception) {
            if ($parser->lexer->exhausted()) {
                throw $exception;
            }
            $trailer = $parser->reconstruct();
        }

        if ($trailer->get('Encrypt') !== null) {
            throw new InvalidPdf('The PDF is encrypted - PDF/A does not allow that.');
        }

        $document = new Document($trailer, $parser->read(...));
        $parser->document = $document;

        if (! $document->resolve($trailer->get('Root')) instanceof Dictionary) {
            throw new InvalidPdf('The PDF has no document catalog.');
        }

        return $document;
    }

    /**
     * Reads the cross-reference sections from the last one back, newer entries winning over older ones. The trailer
     * is the newest one, completed from older ones.
     */
    private function readCrossReferences(): Dictionary
    {
        $position = strrpos($this->lexer->data, 'startxref');
        if ($position === false) {
            throw new InvalidPdf('The PDF has no cross-reference data.');
        }
        $position += 9;
        $offset = $this->lexer->integer($position);

        $trailer = null;
        $visited = [];
        while ($offset !== null) {
            if (isset($visited[$offset]) || $offset < 0 || $offset >= $this->length) {
                throw new InvalidPdf('The cross-reference data of the PDF is damaged.');
            }
            $visited[$offset] = true;

            $position = $offset;
            $section = $this->lexer->keyword($position, 'xref') ? $this->readTable($position) : $this->readStream($offset);
            if ($trailer === null) {
                $trailer = $section;
            } else {
                foreach (['Root', 'Info', 'ID', 'Encrypt'] as $key) {
                    if ($trailer->get($key) === null && $section->get($key) !== null) {
                        $trailer->set($key, $section->get($key));
                    }
                }
            }
            // An offset that is no number is damage, not the end of the chain: the older sections would be lost.
            $previous = $section->get('Prev');
            if ($previous !== null && ! is_int($previous)) {
                throw new InvalidPdf('The cross-reference data of the PDF is damaged.');
            }
            $offset = $previous;
        }
        if ($trailer === null || $trailer->get('Root') === null) {
            throw new InvalidPdf('The PDF has no trailer.');
        }

        return $trailer;
    }

    /**
     * A cross-reference table and its trailer; in a hybrid file the cross-reference stream of the same section
     * (XRefStm) comes first - the table marks the objects it holds as free for older readers.
     */
    private function readTable(int $position): Dictionary
    {
        $entries = [];
        while (true) {
            if ($this->lexer->keyword($position, 'trailer')) {
                break;
            }
            $first = $this->lexer->integer($position);
            $count = $this->lexer->integer($position);
            if ($first === null || $count === null) {
                throw new InvalidPdf('The cross-reference table of the PDF is damaged.');
            }
            for ($i = 0; $i < $count; $i++) {
                $offset = $this->lexer->integer($position);
                $generation = $this->lexer->integer($position);
                $type = $this->lexer->token($position);
                if ($offset === null || $generation === null || ($type !== 'n' && $type !== 'f')) {
                    throw new InvalidPdf('The cross-reference table of the PDF is damaged.');
                }
                $entries[$first + $i] = $type === 'n' ? [1, $offset, $generation] : [0, 0, 0];
            }
        }

        $trailer = $this->lexer->value($position);
        if (! $trailer instanceof Dictionary) {
            throw new InvalidPdf('The trailer of the PDF is damaged.');
        }
        $stream = $trailer->get('XRefStm');
        if ($stream !== null) {
            $this->readStream(is_int($stream) ? $stream : throw new InvalidPdf('The cross-reference data of the PDF is damaged.'));
        }
        foreach ($entries as $number => $entry) {
            $this->entries[$number] ??= $entry;
        }

        return $trailer;
    }

    /**
     * A cross-reference stream (PDF 1.5): its dictionary is the trailer of the section.
     */
    private function readStream(int $offset): Dictionary
    {
        $stream = $this->indirect($offset);
        if (! $stream instanceof Stream || $stream->dictionary->name('Type') !== 'XRef') {
            throw new InvalidPdf('The cross-reference data of the PDF is damaged.');
        }
        $dictionary = $stream->dictionary;
        // It is read before any object can be: its entries are direct objects (ISO 32000-1, 7.5.8.2) - a reference
        // there is damage, and the file is searched for its objects.
        foreach (['Size', 'Index', 'Prev', 'W', 'Filter', 'DecodeParms'] as $key) {
            if (self::refers($dictionary->get($key))) {
                throw new InvalidPdf('The cross-reference stream of the PDF is damaged.');
            }
        }
        $widths = $dictionary->get('W');
        $size = $dictionary->get('Size');
        if (! is_array($widths) || count($widths) < 3 || ! is_int($size)) {
            throw new InvalidPdf('The cross-reference stream of the PDF is damaged.');
        }
        $widths = array_map(static fn(mixed $width): int => is_int($width) && $width >= 0 && $width <= 8 ? $width : throw new InvalidPdf('The cross-reference stream of the PDF is damaged.'), array_slice($widths, 0, 3));
        $entryLength = array_sum($widths);
        // Entries without bytes would be as many as Size says, from no data at all.
        if ($entryLength === 0) {
            throw new InvalidPdf('The cross-reference stream of the PDF is damaged.');
        }
        $index = $dictionary->get('Index') ?? [0, $size];
        if (! is_array($index)) {
            throw new InvalidPdf('The cross-reference stream of the PDF is damaged.');
        }
        $data = $this->decode($stream);

        $position = 0;
        for ($i = 0; $i + 1 < count($index); $i += 2) {
            [$first, $count] = [$index[$i], $index[$i + 1]];
            if (! is_int($first) || ! is_int($count) || $first < 0 || $count < 0) {
                throw new InvalidPdf('The cross-reference stream of the PDF is damaged.');
            }
            for ($j = 0; $j < $count; $j++, $position += $entryLength) {
                if ($position + $entryLength > strlen($data)) {
                    throw new InvalidPdf('The cross-reference stream of the PDF is damaged.');
                }
                $fields = [];
                $field = $position;
                foreach ($widths as $width) {
                    $fields[] = $width === 0 ? null : self::bigEndian(substr($data, $field, $width));
                    $field += $width;
                }
                $type = $fields[0] ?? 1;
                $entry = match ($type) {
                    1 => [1, $fields[1] ?? 0, $fields[2] ?? 0],
                    2 => [2, $fields[1] ?? 0, $fields[2] ?? 0],
                    // Free, or an unknown type - a reference to the null object (7.5.8.3).
                    default => [0, 0, 0],
                };
                $this->entries[$first + $j] ??= $entry;
            }
        }

        return $dictionary;
    }

    /**
     * Every object the cross-reference data places in the file has to start there. Where one does not, the file is
     * searched for it; an object found nowhere is taken as deleted.
     */
    private function repairEntries(): void
    {
        $wrong = [];
        foreach ($this->entries as $number => $entry) {
            if ($entry[0] === 1 && ! $this->startsObject($entry[1], $number)) {
                $wrong[] = $number;
            }
        }
        if ($wrong === []) {
            return;
        }

        $found = $this->scan()['entries'];
        foreach ($wrong as $number) {
            $this->entries[$number] = $found[$number] ?? [0, 0, 0];
        }
    }

    /**
     * Whether the object of the number starts at the offset ("12 0 obj").
     */
    private function startsObject(int $offset, int $number): bool
    {
        if ($offset < 0 || $offset >= $this->length) {
            return false;
        }
        $position = $offset;

        return $this->lexer->integer($position) === $number && $this->lexer->integer($position) !== null && $this->lexer->keyword($position, 'obj');
    }

    /**
     * Reads a file whose cross-reference data cannot be read: its objects as the search finds them, the newest trailer
     * that names a catalog (of a table or of a cross-reference stream, the later in the file) - or, without one, the
     * last catalog of the file.
     */
    private function reconstruct(): Dictionary
    {
        $this->entries = [];
        $this->objectStreams = [];
        $scan = $this->scan();
        $this->entries = $scan['entries'];
        $catalogs = $this->readObjectStreams($scan['streams'], $scan['places'], $scan['catalogs']);

        $candidates = $scan['streamTrailers'];
        foreach (array_slice($scan['trailers'], -self::TRAILERS_TRIED) as $position) {
            $start = $position;
            try {
                $candidate = $this->lexer->value($start);
            } catch (InvalidPdf) {
                continue;
            }
            if ($candidate instanceof Dictionary && $candidate->get('Root') !== null) {
                $candidates[$position] = $candidate;
            }
        }
        if ($candidates !== []) {
            return $candidates[max(array_keys($candidates))];
        }

        arsort($catalogs);
        foreach (array_keys($catalogs) as $number) {
            try {
                $object = $this->read($number);
            } catch (InvalidPdf) {
                continue;
            }
            if ($object instanceof Dictionary && $object->name('Type') === 'Catalog') {
                return new Dictionary(['Root' => new Reference($number)]);
            }
        }

        throw new InvalidPdf($this->lexer->exhausted() ? 'The PDF is damaged beyond repair.' : 'The PDF has no document catalog.');
    }

    /**
     * Searches the file for its objects ("12 0 obj") in one pass from the start, as readers do where the
     * cross-reference data is of no use. The data of a stream is skipped, so that an uncompressed PDF embedded in the
     * file does not lend it its objects; a later definition of a number wins over an earlier one, as in incremental
     * updates. Noted on the way: the object streams, the trailers and the objects that may be the catalog.
     *
     * @return array{entries: array<int, array{int, int, int}>, places: array<int, int>, streams: array<int, int>, trailers: list<int>, streamTrailers: array<int, Dictionary>, catalogs: array<int, int>}
     *     places: the offset of each object; streams: the offsets of the object streams by their numbers; trailers:
     *     positions after the keywords "trailer"; streamTrailers: the dictionaries of cross-reference streams that
     *     name a catalog, by their offsets; catalogs: the offsets of the objects that may be the catalog by number
     */
    private function scan(): array
    {
        $data = $this->lexer->data;
        $entries = [];
        $places = [];
        $streams = [];
        $trailers = [];
        $streamTrailers = [];
        $catalogs = [];
        $endstream = null;
        $position = 0;
        while (preg_match(self::HEADER_OR_TRAILER, $data, $match, PREG_OFFSET_CAPTURE, $position) === 1) {
            $start = $match[0][1];
            $position = $start + strlen($match[0][0]);
            if (! isset($match[1]) || $match[1][1] < 0) {
                $trailers[] = $position;

                continue;
            }
            $number = (int) $match[1][0];
            $entries[$number] = [1, $start, (int) $match[2][0]];
            $places[$number] = $start;
            unset($streams[$number], $catalogs[$number]);

            $end = preg_match(self::OBJECT_END, $data, $found, PREG_OFFSET_CAPTURE, $position) === 1 ? $found[0] : ['', $this->length];
            if (substr_count($data, 'Catalog', $position, $end[1] - $position) > 0) {
                $catalogs[$number] = $start;
            }
            if ($end[0] === 'endobj') {
                $position = $end[1] + 6;
            } elseif ($end[0] === 'stream') {
                $dictionary = $this->dictionaryBetween($position, $end[1]);
                $position = $this->skipStream($end[1] + 6, $dictionary, $entries, $endstream);
                if ($dictionary?->name('Type') === 'ObjStm') {
                    $streams[$number] = $start;
                } elseif ($dictionary?->name('Type') === 'XRef' && $dictionary->get('Root') !== null) {
                    $streamTrailers[$start] = $dictionary;
                }
            } else {
                // The next object, or the end of the file: this one misses its "endobj".
                $position = $end[1];
            }
        }

        return ['entries' => $entries, 'places' => $places, 'streams' => $streams, 'trailers' => $trailers, 'streamTrailers' => $streamTrailers, 'catalogs' => $catalogs];
    }

    /**
     * Adds the objects of the object streams a search found to the entries - an object counts at the place of its
     * stream, so that a later definition wins, whether in the file or in a later object stream.
     *
     * @param array<int, int> $streams the offsets of the object streams by their numbers
     * @param array<int, int> $places the offset of each object found in the file
     * @param array<int, int> $catalogs the offsets of the objects that may be the catalog by number
     * @return array<int, int> the objects that may be the catalog, with those of the object streams
     */
    private function readObjectStreams(array $streams, array $places, array $catalogs): array
    {
        asort($streams);
        foreach ($streams as $number => $offset) {
            try {
                $stream = $this->objectStreamAt($number, $offset);
            } catch (InvalidPdf) {
                continue;
            }
            $data = $stream['lexer']->data;
            $offsets = $stream['offsets'];
            asort($offsets);
            $starts = array_values($offsets);
            foreach (array_keys($offsets) as $index => $contained) {
                if (($places[$contained] ?? -1) > $offset) {
                    continue;
                }
                $this->entries[$contained] = [2, $number, $index];
                $places[$contained] = $offset;
                unset($catalogs[$contained]);
                $from = $stream['first'] + $starts[$index];
                $to = isset($starts[$index + 1]) ? $stream['first'] + $starts[$index + 1] : strlen($data);
                if ($to > $from && substr_count($data, 'Catalog', $from, $to - $from) > 0) {
                    $catalogs[$contained] = $offset;
                }
            }
        }

        return $catalogs;
    }

    /**
     * The dictionary of a stream the search found, between its header and the keyword "stream" - null if it cannot be
     * read.
     */
    private function dictionaryBetween(int $from, int $to): ?Dictionary
    {
        $lexer = new Lexer(substr($this->lexer->data, $from, $to - $from));
        $position = 0;
        try {
            $value = $lexer->value($position);
        } catch (InvalidPdf) {
            return null;
        }

        return $value instanceof Dictionary ? $value : null;
    }

    /**
     * Where the search goes on after the data of a stream: past its "endstream" - found by its Length where that is
     * right, by searching otherwise -, or at the start of the data if the stream has no end.
     *
     * @param array<int, array{int, int, int}> $entries the objects found so far
     * @param array{int, int|false}|null $endstream the last search for "endstream": where it started, what it found -
     *                                               a search from a place up to that finds the same, and the whole
     *                                               search stays one pass over the file
     */
    private function skipStream(int $position, ?Dictionary $dictionary, array $entries, ?array &$endstream): int
    {
        $data = $this->lexer->data;
        if (($data[$position] ?? '') === "\r") {
            $position++;
        }
        if (($data[$position] ?? '') === "\n") {
            $position++;
        }

        $length = $dictionary?->get('Length');
        if ($length instanceof Reference) {
            $entry = $entries[$length->number] ?? $this->entries[$length->number] ?? null;
            $length = $entry !== null && $entry[0] === 1 ? $this->integerObject($entry[1], $length->number) : null;
        }
        if (is_int($length) && $length >= 0 && $length <= $this->length - $position
            && preg_match('/\G[\0\t\n\f\r ]*endstream/', $data, $match, 0, $position + $length) === 1) {
            return $position + $length + strlen($match[0]);
        }

        if ($endstream === null || $position < $endstream[0] || ($endstream[1] !== false && $position > $endstream[1])) {
            $endstream = [$position, strpos($data, 'endstream', $position)];
        }

        return $endstream[1] === false ? $position : $endstream[1] + 9;
    }

    /**
     * The integer of the object at an offset ("5 0 obj 1234 endobj"), null if it is none.
     */
    private function integerObject(int $offset, int $number): ?int
    {
        if (! $this->startsObject($offset, $number)) {
            return null;
        }
        $position = $offset;
        $this->lexer->integer($position);
        $this->lexer->integer($position);
        $this->lexer->keyword($position, 'obj');

        return $this->lexer->integer($position);
    }

    /**
     * Reads an object by its number (Document).
     */
    private function read(int $number): mixed
    {
        $entry = $this->entries[$number] ?? [0, 0, 0];
        if ($entry[0] === 1) {
            return $this->indirect($entry[1]);
        }
        if ($entry[0] === 2) {
            $stream = $this->objectStream($entry[1]);
            $offset = $stream['offsets'][$number] ?? null;
            if ($offset === null) {
                return null;
            }
            $position = $stream['first'] + $offset;

            return $stream['lexer']->value($position);
        }

        return null;
    }

    /**
     * The indirect object at an offset of the file ("12 0 obj ... endobj"), a stream with its data as stored.
     */
    private function indirect(int $offset): mixed
    {
        if ($offset < 0 || $offset >= $this->length) {
            throw new InvalidPdf("No object of the PDF at offset $offset.");
        }
        $position = $offset;
        if ($this->lexer->integer($position) === null || $this->lexer->integer($position) === null || ! $this->lexer->keyword($position, 'obj')) {
            throw new InvalidPdf("No object of the PDF at offset $offset.");
        }
        $value = $this->lexer->value($position);
        if (! $this->lexer->keyword($position, 'stream')) {
            return $value;
        }
        if (! $value instanceof Dictionary) {
            throw new InvalidPdf("The stream of the PDF at offset $offset has no dictionary.");
        }

        return new Stream($value, $this->streamData($position, $value));
    }

    /**
     * The data of a stream: Length bytes after the end of line that follows "stream" - or, where Length is wrong or
     * cannot be read, up to "endstream".
     */
    private function streamData(int $position, Dictionary $dictionary): string
    {
        $data = $this->lexer->data;
        if (($data[$position] ?? '') === "\r") {
            $position++;
        }
        if (($data[$position] ?? '') === "\n") {
            $position++;
        }

        $length = $dictionary->get('Length');
        if ($length instanceof Reference) {
            // Before the document exists (a damaged file searched), the entries found so far know the object.
            $entry = $this->entries[$length->number] ?? [0, 0, 0];
            $length = match (true) {
                $this->document !== null => $this->document->object($length->number),
                $entry[0] === 1 => $this->integerObject($entry[1], $length->number),
                default => null,
            };
        }
        if (is_int($length) && $length >= 0 && $length <= $this->length - $position
            && preg_match('/\G[\0\t\n\f\r ]*endstream/', $data, $match, 0, $position + $length) === 1) {
            $this->lexer->spend($length);

            return substr($data, $position, $length);
        }

        $end = strpos($data, 'endstream', $position);
        $this->lexer->spend(($end === false ? $this->length : $end) - $position);
        if ($end === false) {
            throw new InvalidPdf('A stream of the PDF has no end.');
        }
        $stream = substr($data, $position, $end - $position);
        if (str_ends_with($stream, "\r\n")) {
            return substr($stream, 0, -2);
        }

        return str_ends_with($stream, "\n") || str_ends_with($stream, "\r") ? substr($stream, 0, -1) : $stream;
    }

    /**
     * An object stream (PDF 1.5) by its number.
     *
     * @return array{lexer: Lexer, first: int, offsets: array<int, int>}
     */
    private function objectStream(int $number): array
    {
        if (isset($this->objectStreams[$number])) {
            return $this->objectStreams[$number];
        }
        $entry = $this->entries[$number] ?? [0, 0, 0];
        if ($entry[0] !== 1) {
            throw new InvalidPdf("Object $number of the PDF is no object stream.");
        }

        return $this->objectStreamAt($number, $entry[1]);
    }

    /**
     * An object stream at an offset of the file: its decoded data and where each object starts in it.
     *
     * @return array{lexer: Lexer, first: int, offsets: array<int, int>}
     */
    private function objectStreamAt(int $number, int $offset): array
    {
        if (isset($this->objectStreams[$number])) {
            return $this->objectStreams[$number];
        }
        // Its parameters lead back into it by way of other objects: it cannot be decoded to read them.
        if (isset($this->decoding[$number])) {
            throw new InvalidPdf("The object stream $number of the PDF is damaged.");
        }
        $stream = $this->indirect($offset);
        if (! $stream instanceof Stream || $stream->dictionary->name('Type') !== 'ObjStm') {
            throw new InvalidPdf("Object $number of the PDF is no object stream.");
        }
        $count = $stream->dictionary->get('N');
        $first = $stream->dictionary->get('First');
        if (! is_int($count) || ! is_int($first) || $count < 0 || $first < 0) {
            throw new InvalidPdf("The object stream $number of the PDF is damaged.");
        }

        $this->decoding[$number] = true;
        try {
            $lexer = new Lexer($this->decode($stream));
        } finally {
            unset($this->decoding[$number]);
        }
        $length = strlen($lexer->data);
        if ($first > $length) {
            throw new InvalidPdf("The object stream $number of the PDF is damaged.");
        }
        $offsets = [];
        $position = 0;
        for ($i = 0; $i < $count; $i++) {
            $object = $lexer->integer($position);
            $relative = $lexer->integer($position);
            if ($object === null || $relative === null) {
                throw new InvalidPdf("The object stream $number of the PDF is damaged.");
            }
            if ($relative < $length - $first) {
                $offsets[$object] ??= $relative;
            }
        }

        return $this->objectStreams[$number] = ['lexer' => $lexer, 'first' => $first, 'offsets' => $offsets];
    }

    /**
     * The decoded data of a stream of the file, within what the file may decode to together.
     */
    private function decode(Stream $stream): string
    {
        $data = Filter::decode($stream, $this->decodeBudget, $this->parameter(...));
        $this->decodeBudget -= strlen($data);

        return $data;
    }

    /**
     * The object a filter or its parameters refer to (Filter, DecodeParms): read from the file, or from an object
     * stream that is not being decoded - a stream never needs itself to be decoded.
     */
    private function parameter(mixed $value): mixed
    {
        if (! $value instanceof Reference) {
            return $value;
        }
        $entry = $this->entries[$value->number] ?? [0, 0, 0];
        if ($entry[0] === 2 && isset($this->decoding[$entry[1]])) {
            return null;
        }
        // A parameter that cannot be read is none, as a Length that refers back to its stream.
        try {
            return $this->document !== null ? $this->document->resolve($value) : $this->read($value->number);
        } catch (InvalidPdf) {
            return null;
        }
    }

    /**
     * Whether a value is a reference or holds one.
     */
    private static function refers(mixed $value): bool
    {
        if ($value instanceof Reference) {
            return true;
        }
        foreach (match (true) {
            is_array($value) => $value,
            $value instanceof Dictionary => $value->entries,
            default => [],
        } as $item) {
            if (self::refers($item)) {
                return true;
            }
        }

        return false;
    }

    private static function bigEndian(string $bytes): int
    {
        // Eight bytes with the highest bit set are beyond every offset - and beyond the integers of PHP.
        if (strlen($bytes) === 8 && ord($bytes[0]) >= 0x80) {
            throw new InvalidPdf('The cross-reference stream of the PDF is damaged.');
        }
        $value = 0;
        foreach (str_split($bytes) as $byte) {
            $value = ($value << 8) | ord($byte);
        }

        return $value;
    }
}
