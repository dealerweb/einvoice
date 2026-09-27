<?php

declare(strict_types=1);

namespace Dealerweb\EInvoice\Pdf;

use Dealerweb\EInvoice\Exception\InvalidPdf;

/**
 * Reads the objects of the PDF syntax (ISO 32000-1, 7.3) from a buffer - the file or a decoded object stream: names,
 * strings, numbers, references, arrays and dictionaries. Positions are byte offsets, passed by reference.
 *
 * The bytes read are counted: reading a damaged file over and over - strings or arrays open to its end, found once for
 * every object that starts inside them - is refused after a few passes over the data instead of taking minutes.
 *
 * @internal
 */
final class Lexer
{
    private const WHITESPACE = "\0\t\n\f\r ";

    private const DELIMITERS = '()<>[]{}/%';

    /** Nesting deeper than this is no document but an attack on the stack. */
    private const MAX_DEPTH = 100;

    /** How often the data may be read over: a whole file needs about one pass, a damaged one a few. */
    private const PASSES = 16;

    /** The bytes a small buffer may be read over in any case. */
    private const MINIMUM_BUDGET = 1048576;

    /**
     * Integers with more digits are no numbers of a file (the offsets of 10 digits included) but damage - and beyond
     * the integers of PHP. integer() reads at most as many.
     */
    private const INTEGER_DIGITS = 18;

    private readonly int $length;

    private int $budget;

    public function __construct(public readonly string $data)
    {
        $this->length = strlen($data);
        $this->budget = max(self::MINIMUM_BUDGET, self::PASSES * $this->length);
    }

    /**
     * Counts bytes read - by the lexer and by searches of the parser in the data.
     *
     * @throws InvalidPdf the data has been read over too often
     */
    public function spend(int $bytes): void
    {
        $this->budget -= $bytes;
        if ($this->budget < 0) {
            throw new InvalidPdf('The PDF is damaged beyond repair.');
        }
    }

    /**
     * Whether the data has been read over too often - reading it again in another way cannot help then.
     */
    public function exhausted(): bool
    {
        return $this->budget < 0;
    }

    /**
     * Skips white space and comments.
     *
     * @throws InvalidPdf the position is outside the data
     */
    public function skipSpace(int &$position): void
    {
        if ($position < 0 || $position > $this->length) {
            throw new InvalidPdf('The PDF refers to a place outside the file.');
        }
        $start = $position;
        $position += strspn($this->data, self::WHITESPACE, $position);
        while ($position < $this->length && $this->data[$position] === '%') {
            $position += strcspn($this->data, "\r\n", $position);
            $position += strspn($this->data, self::WHITESPACE, $position);
        }
        // Counted here and not by spend(): this runs for every token.
        $this->budget -= $position - $start;
        if ($this->budget < 0) {
            throw new InvalidPdf('The PDF is damaged beyond repair.');
        }
    }

    /**
     * The next token of regular characters (a keyword or a number), empty at a delimiter or at the end.
     *
     * @phpstan-impure it moves the position
     */
    public function token(int &$position): string
    {
        $this->skipSpace($position);
        $length = strcspn($this->data, self::WHITESPACE . self::DELIMITERS, $position);
        $this->budget -= $length;
        if ($this->budget < 0) {
            throw new InvalidPdf('The PDF is damaged beyond repair.');
        }
        $token = substr($this->data, $position, $length);
        $position += $length;

        return $token;
    }

    /**
     * Whether the next token is the keyword - the position moves past it only then.
     *
     * @phpstan-impure it moves the position
     */
    public function keyword(int &$position, string $keyword): bool
    {
        $next = $position;
        if ($this->token($next) !== $keyword) {
            return false;
        }
        $position = $next;

        return true;
    }

    /**
     * The next token as a non-negative integer, null if it is none (the position stays then).
     *
     * @phpstan-impure it moves the position
     */
    public function integer(int &$position): ?int
    {
        $next = $position;
        $token = $this->token($next);
        if (preg_match('/^\d{1,18}$/', $token) !== 1) {
            return null;
        }
        $position = $next;

        return (int) $token;
    }

    /**
     * The object at the position.
     *
     * @phpstan-impure it moves the position
     * @throws InvalidPdf no object at the position
     */
    public function value(int &$position, int $depth = 0): mixed
    {
        if ($depth > self::MAX_DEPTH) {
            throw new InvalidPdf('The objects of the PDF are nested too deeply.');
        }
        $this->skipSpace($position);
        if ($position >= $this->length) {
            throw new InvalidPdf('The PDF ends in the middle of an object.');
        }

        $character = $this->data[$position];
        if ($character === '/') {
            return $this->name($position);
        }
        if ($character === '(') {
            return $this->literal($position);
        }
        if ($character === '<') {
            if ($this->at($position + 1) === '<') {
                return $this->dictionary($position, $depth);
            }

            return $this->hexadecimal($position);
        }
        if ($character === '[') {
            $position++;
            $this->spend(1);
            $items = [];
            while (true) {
                $this->skipSpace($position);
                if ($position >= $this->length) {
                    throw new InvalidPdf('The PDF ends in the middle of an array.');
                }
                if ($this->data[$position] === ']') {
                    $position++;
                    $this->spend(1);

                    return $items;
                }
                $items[] = $this->value($position, $depth + 1);
            }
        }

        $start = $position;
        $token = $this->token($position);
        if ($token === '') {
            throw new InvalidPdf("Unexpected character \"$character\" in the PDF at offset $start.");
        }
        if (preg_match('/^[+-]?\d+$/', $token) === 1) {
            // Beyond the integers of a file: kept as written, as a number no structure takes.
            if (strlen(ltrim($token, '+-0')) > self::INTEGER_DIGITS) {
                return new Real($token);
            }
            // An integer, or the object number of a reference "12 0 R".
            if ($token[0] !== '-' && $token[0] !== '+') {
                $next = $position;
                $generation = $this->integer($next);
                if ($generation !== null && $this->keyword($next, 'R')) {
                    $position = $next;

                    return new Reference((int) $token, $generation);
                }
            }

            return (int) $token;
        }
        if (preg_match('/^[+-]?(\d+\.\d*|\.\d+)$/', $token) === 1) {
            return new Real($token);
        }

        return match ($token) {
            'true' => true,
            'false' => false,
            'null' => null,
            default => throw new InvalidPdf("Unexpected \"$token\" in the PDF at offset $start."),
        };
    }

    /**
     * The byte at a position, empty beyond the end.
     */
    private function at(int $position): string
    {
        return $position < $this->length ? $this->data[$position] : '';
    }

    private function name(int &$position): Name
    {
        $position++;
        $length = strcspn($this->data, self::WHITESPACE . self::DELIMITERS, $position);
        $this->spend($length + 1);
        $raw = substr($this->data, $position, $length);
        $position += $length;

        return new Name(preg_replace_callback('/#([0-9A-Fa-f]{2})/', static fn(array $match): string => chr((int) hexdec($match[1])), $raw) ?? $raw);
    }

    private function literal(int &$position): Text
    {
        $start = $position;
        $position++;
        $bytes = '';
        $depth = 1;
        while ($position < $this->length) {
            // The bytes up to the next one that means something in a string, at once.
            $run = strcspn($this->data, "\\()\r", $position);
            $bytes .= substr($this->data, $position, $run);
            $position += $run;
            if ($position >= $this->length) {
                break;
            }
            $character = $this->data[$position++];
            if ($character === '\\') {
                $next = $this->at($position++);
                if ($next !== '' && str_contains('01234567', $next)) {
                    $octal = $next;
                    while (strlen($octal) < 3 && $this->at($position) !== '' && str_contains('01234567', $this->at($position))) {
                        $octal .= $this->data[$position++];
                    }
                    $bytes .= chr(octdec($octal) & 0xFF);
                } elseif ($next === "\r") {
                    // A backslash at the end of a line continues the string on the next one.
                    if ($this->at($position) === "\n") {
                        $position++;
                    }
                } elseif ($next !== "\n") {
                    $bytes .= match ($next) {
                        'n' => "\n",
                        'r' => "\r",
                        't' => "\t",
                        'b' => "\x08",
                        'f' => "\f",
                        default => $next,
                    };
                }
            } elseif ($character === '(') {
                $depth++;
                $bytes .= $character;
            } elseif ($character === ')') {
                if (--$depth === 0) {
                    $this->spend($position - $start);

                    return new Text($bytes);
                }
                $bytes .= $character;
            } else {
                // An end of line in a string (CR, CR LF) is a line feed.
                if ($this->at($position) === "\n") {
                    $position++;
                }
                $bytes .= "\n";
            }
        }

        $this->spend(min($position, $this->length) - $start);

        throw new InvalidPdf('The PDF ends in the middle of a string.');
    }

    private function hexadecimal(int &$position): Text
    {
        $end = strpos($this->data, '>', $position);
        if ($end === false) {
            $this->spend($this->length - $position);

            throw new InvalidPdf('The PDF ends in the middle of a string.');
        }
        $this->spend($end + 1 - $position);
        $digits = preg_replace('/[^0-9A-Fa-f]/', '', substr($this->data, $position + 1, $end - $position - 1)) ?? '';
        $position = $end + 1;
        if (strlen($digits) % 2 === 1) {
            $digits .= '0';
        }

        return new Text((string) hex2bin($digits));
    }

    private function dictionary(int &$position, int $depth): Dictionary
    {
        $position += 2;
        $this->spend(2);
        $dictionary = new Dictionary();
        while (true) {
            $this->skipSpace($position);
            if ($position >= $this->length) {
                throw new InvalidPdf('The PDF ends in the middle of a dictionary.');
            }
            if ($this->data[$position] === '>' && $this->at($position + 1) === '>') {
                $position += 2;
                $this->spend(2);

                return $dictionary;
            }
            $key = $this->value($position, $depth + 1);
            if (! $key instanceof Name) {
                throw new InvalidPdf('A dictionary of the PDF has a key that is no name.');
            }
            $dictionary->set($key->value, $this->value($position, $depth + 1));
        }
    }
}
