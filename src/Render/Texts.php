<?php

declare(strict_types=1);

namespace Dealerweb\EInvoice\Render;

/**
 * Texts of the rendered invoice (resources/translations/render-<language>.php).
 *
 * @internal
 */
final class Texts
{
    /** @var array<string, array<string, string>> */
    private static array $texts = [];

    public function __construct(private readonly string $language) {}

    /**
     * The text for a key with its placeholders ({name}) replaced; English if the language lacks it.
     * All placeholders are replaced at once: a value that itself contains "{name}" stays as it is.
     *
     * @param array<string, string|int> $parameters
     */
    public function get(string $key, array $parameters = []): string
    {
        $text = self::load($this->language)[$key] ?? self::load('en')[$key] ?? $key;

        $replacements = [];
        foreach ($parameters as $name => $value) {
            $replacements['{' . $name . '}'] = (string) $value;
        }

        return strtr($text, $replacements);
    }

    /**
     * @return array<string, string>
     */
    private static function load(string $language): array
    {
        return self::$texts[$language] ??= require dirname(__DIR__, 2) . '/resources/translations/render-' . $language . '.php';
    }
}
