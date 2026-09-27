<?php

declare(strict_types=1);

namespace Dealerweb\EInvoice\Validation\XPath;

/**
 * Regular expressions of XPath (fn:matches, fn:replace, fn:tokenize) as PCRE. XPath has its own dialect (XML Schema
 * plus anchors, back-references and reluctant quantifiers); where it differs from PCRE, the pattern is rewritten:
 *  - "\s" is only space, tab, CR and LF; "\d" is a decimal digit of Unicode 6.2 - Saxon's table, not the newer one of
 *    PCRE; "\w" is everything but punctuation, separators and other characters (PCRE's table, as the categories \p{..});
 *  - "." does not match CR or LF unless the flag s is set (XPath 3.1, as Saxon 12);
 *  - "$" matches only at the very end, not before a final line break (PCRE modifier D);
 *  - flag x removes whitespace from the pattern, except within brackets.
 * Unsupported parts (character class subtraction, block names like \p{IsBasicLatin}, \S \D \w \W within brackets,
 * \i \c) raise FORX0002. Where PCRE gives up on a text - its JIT out of stack is tried again without it, other limits
 * of backtracking - the expression fails (FOER0000) instead of not matching: XPath knows no such limits.
 *
 * @internal
 */
final class Regex
{
    /** The decimal digits (Nd) of Unicode 6.2.0, as Saxon 12 reads them from its categories.xml. */
    private const DIGITS = '\x{30}-\x{39}\x{660}-\x{669}\x{6F0}-\x{6F9}\x{7C0}-\x{7C9}\x{966}-\x{96F}\x{9E6}-\x{9EF}'
        . '\x{A66}-\x{A6F}\x{AE6}-\x{AEF}\x{B66}-\x{B6F}\x{BE6}-\x{BEF}\x{C66}-\x{C6F}\x{CE6}-\x{CEF}\x{D66}-\x{D6F}'
        . '\x{E50}-\x{E59}\x{ED0}-\x{ED9}\x{F20}-\x{F29}\x{1040}-\x{1049}\x{1090}-\x{1099}\x{17E0}-\x{17E9}'
        . '\x{1810}-\x{1819}\x{1946}-\x{194F}\x{19D0}-\x{19D9}\x{1A80}-\x{1A89}\x{1A90}-\x{1A99}\x{1B50}-\x{1B59}'
        . '\x{1BB0}-\x{1BB9}\x{1C40}-\x{1C49}\x{1C50}-\x{1C59}\x{A620}-\x{A629}\x{A8D0}-\x{A8D9}\x{A900}-\x{A909}'
        . '\x{A9D0}-\x{A9D9}\x{AA50}-\x{AA59}\x{ABF0}-\x{ABF9}\x{FF10}-\x{FF19}\x{104A0}-\x{104A9}\x{11066}-\x{1106F}'
        . '\x{110F0}-\x{110F9}\x{11136}-\x{1113F}\x{111D0}-\x{111D9}\x{116C0}-\x{116C9}\x{1D7CE}-\x{1D7FF}';

    /** @var array<string, array{0: string, 1: int}> cache: flags + pattern => PCRE and its number of groups */
    private static array $compiled = [];

    /**
     * @return array{0: string, 1: int} the PCRE pattern and its number of capturing groups
     *
     * @throws DynamicError FORX0001 invalid flags, FORX0002 invalid or unsupported pattern
     */
    public static function compile(string $pattern, string $flags = ''): array
    {
        $key = $flags . "\0" . $pattern;
        if (isset(self::$compiled[$key])) {
            return self::$compiled[$key];
        }
        if (preg_match('/[^smixq]/', $flags)) {
            throw new DynamicError('FORX0001', "Invalid regular expression flags \"$flags\".");
        }

        // Flag q (XPath 3.0): the pattern is plain text.
        $body = str_contains($flags, 'q')
            ? preg_quote($pattern, '~')
            : self::translate(str_contains($flags, 'x') ? self::removeWhitespace($pattern) : $pattern, str_contains($flags, 's'), $pattern);
        $modifiers = 'uD' . (str_contains($flags, 'i') ? 'i' : '') . (str_contains($flags, 'm') ? 'm' : '');
        $compiled = '~(*LF)' . $body . '~' . $modifiers;

        // An always matching alternative reports every group of the pattern (as null) - their number.
        if (@preg_match('~(*LF)(?:' . $body . ')|~' . $modifiers, '', $groups, PREG_UNMATCHED_AS_NULL) === false) {
            throw new DynamicError('FORX0002', "Invalid regular expression \"$pattern\".");
        }

        return self::$compiled[$key] = [$compiled, count($groups) - 1];
    }

    /**
     * @throws DynamicError FOER0000 PCRE gives up on the text
     */
    public static function matches(string $input, string $pattern, string $flags = ''): bool
    {
        return self::run(self::compile($pattern, $flags)[0], static fn(string $compiled): int|false => preg_match($compiled, $input)) === 1;
    }

    /**
     * @throws DynamicError FORX0003 the pattern matches an empty string, FORX0004 invalid replacement, FOER0000 PCRE
     *                      gives up on the text
     */
    public static function replace(string $input, string $pattern, string $replacement, string $flags = ''): string
    {
        [$compiled, $groups] = self::compile($pattern, $flags);
        if (preg_match($compiled, '') === 1) {
            throw new DynamicError('FORX0003', "The pattern \"$pattern\" matches a zero-length string.");
        }
        $replacement = str_contains($flags, 'q') ? addcslashes($replacement, '\\$') : self::replacement($replacement, $groups);

        return self::run($compiled, static fn(string $compiled): ?string => preg_replace($compiled, $replacement, $input));
    }

    /**
     * @return list<string>
     *
     * @throws DynamicError FORX0003 the pattern matches an empty string, FOER0000 PCRE gives up on the text
     */
    public static function tokenize(string $input, string $pattern, string $flags = ''): array
    {
        if ($input === '') {
            return [];
        }
        [$compiled] = self::compile($pattern, $flags);
        if (preg_match($compiled, '') === 1) {
            throw new DynamicError('FORX0003', "The pattern \"$pattern\" matches a zero-length string.");
        }

        return self::run($compiled, static fn(string $compiled): array|false => preg_split($compiled, $input));
    }

    /**
     * Runs a PCRE function with the pattern: where its JIT runs out of stack - a long text in a repeated group -
     * once more without JIT, which keeps its matching on the heap. Any other failure, a limit of backtracking or
     * recursion included, fails the expression: a silent "no match" would decide the rule.
     *
     * @template T
     *
     * @param callable(string): (T|false|null) $call
     *
     * @return T
     *
     * @throws DynamicError FOER0000
     */
    private static function run(string $compiled, callable $call): mixed
    {
        $result = $call($compiled);
        if ($result === false || $result === null) {
            if (preg_last_error() === PREG_JIT_STACKLIMIT_ERROR) {
                // (*NO_JIT) opens the pattern, right after its delimiter
                $result = $call(substr_replace($compiled, '(*NO_JIT)', 1, 0));
            }
            if ($result === false || $result === null) {
                throw new DynamicError('FOER0000', 'The regular expression cannot be applied to the text (' . preg_last_error_msg() . ').');
            }
        }

        return $result;
    }

    private static function translate(string $source, bool $dotAll, string $pattern): string
    {
        $pcre = '';
        $inClass = false;
        $length = strlen($source);
        for ($i = 0; $i < $length; $i++) {
            $char = $source[$i];

            if ($char === '\\') {
                if ($i + 1 >= $length) {
                    throw new DynamicError('FORX0002', "Invalid regular expression \"$pattern\": trailing backslash.");
                }
                $pcre .= self::escape($source, $i, $inClass, $pattern);

                continue;
            }

            if ($inClass) {
                if ($char === '[') {
                    throw new DynamicError('FORX0002', "Unsupported character class subtraction in \"$pattern\".");
                }
                if ($char === ']') {
                    $inClass = false;
                }
                $pcre .= $char === '~' ? '\~' : $char;

                continue;
            }

            if ($char === '[') {
                $inClass = true;
                $pcre .= '[';
                // "^" right after the bracket negates, it is no literal
                if (($source[$i + 1] ?? '') === '^') {
                    $pcre .= '^';
                    $i++;
                }

                continue;
            }

            $pcre .= match ($char) {
                '.' => $dotAll ? '(?s:.)' : '[^\n\r]',
                '~' => '\~',
                default => $char,
            };
        }
        if ($inClass) {
            throw new DynamicError('FORX0002', "Invalid regular expression \"$pattern\": unclosed bracket.");
        }

        return $pcre;
    }

    /**
     * The escape at $i (the backslash) in PCRE; moves $i to its last character.
     */
    private static function escape(string $source, int &$i, bool $inClass, string $pattern): string
    {
        $next = $source[++$i];
        $unsupported = static fn(): DynamicError => new DynamicError('FORX0002', "Unsupported escape \\$next within brackets in \"$pattern\".");

        return match ($next) {
            's' => $inClass ? '\x20\t\n\r' : '[\x20\t\n\r]',
            'S' => $inClass ? throw $unsupported() : '[^\x20\t\n\r]',
            'd' => $inClass ? self::DIGITS : '[' . self::DIGITS . ']',
            'D' => $inClass ? throw $unsupported() : '[^' . self::DIGITS . ']',
            'w' => $inClass ? throw $unsupported() : '[^\p{P}\p{Z}\p{C}]',
            'W' => $inClass ? throw $unsupported() : '[\p{P}\p{Z}\p{C}]',
            'n' => '\n',
            'r' => '\r',
            't' => '\t',
            'p', 'P' => self::category($source, $i, $next, $pattern),
            '\\', '|', '.', '-', '^', '?', '*', '+', '{', '}', '(', ')', '[', ']', '$' => '\\' . $next,
            default => ctype_digit($next) && ! $inClass
                ? '\\' . $next
                : throw new DynamicError('FORX0002', "Invalid escape \\$next in regular expression \"$pattern\"."),
        };
    }

    /**
     * \p{Lu}, \P{L} - general categories only.
     */
    private static function category(string $source, int &$i, string $escape, string $pattern): string
    {
        if (! preg_match('/\G\{([A-Za-z]{1,2})\}/', $source, $match, 0, $i + 1)) {
            throw new DynamicError('FORX0002', "Unsupported category escape in \"$pattern\".");
        }
        $i += strlen($match[0]);

        return '\\' . $escape . '{' . $match[1] . '}';
    }

    /**
     * XPath replacement string -> PCRE: $n refers to a group (as many digits as groups exist), \$ and \\ are literal.
     */
    private static function replacement(string $replacement, int $groups): string
    {
        $result = '';
        $length = strlen($replacement);
        for ($i = 0; $i < $length; $i++) {
            $char = $replacement[$i];
            if ($char === '\\') {
                $next = $replacement[$i + 1] ?? '';
                if ($next !== '\\' && $next !== '$') {
                    throw new DynamicError('FORX0004', "Invalid replacement string \"$replacement\".");
                }
                $result .= '\\' . $next;
                $i++;
            } elseif ($char === '$') {
                if (! preg_match('/\G\d+/', $replacement, $match, 0, $i + 1)) {
                    throw new DynamicError('FORX0004', "Invalid replacement string \"$replacement\".");
                }
                $digits = $match[0];
                // as many digits as there are groups: "$10" with 9 groups is group 1 followed by "0"
                while (strlen($digits) > 1 && (int) $digits > $groups) {
                    $digits = substr($digits, 0, -1);
                }
                $result .= (int) $digits > $groups ? '' : '${' . $digits . '}';
                $i += strlen($digits);
            } else {
                $result .= $char;
            }
        }

        return $result;
    }

    private static function removeWhitespace(string $pattern): string
    {
        $result = '';
        $inClass = false;
        $length = strlen($pattern);
        for ($i = 0; $i < $length; $i++) {
            $char = $pattern[$i];
            if ($char === '\\' && $i + 1 < $length) {
                $result .= $char . $pattern[++$i];

                continue;
            }
            if ($char === '[') {
                $inClass = true;
            } elseif ($char === ']') {
                $inClass = false;
            }
            if (! $inClass && in_array($char, [' ', "\t", "\r", "\n"], true)) {
                continue;
            }
            $result .= $char;
        }

        return $result;
    }
}
