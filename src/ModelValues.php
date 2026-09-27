<?php

declare(strict_types=1);

namespace Dealerweb\EInvoice;

/**
 * Reads values from the nested arrays of Document::toArray(): a group or field that the model expects once
 * may still repeat, a value may carry attributes (['value' => ..., 'scheme_identifier' => ...]) and, with
 * meta, its id and source path. Used by the supporting documents of Document and by the rendering.
 *
 * @internal
 */
final class ModelValues
{
    /**
     * Groups of the model as a list, whether the element occurs once or repeatedly.
     *
     * @return list<array<string, mixed>>
     */
    public static function groups(mixed $node): array
    {
        if (! is_array($node) || $node === []) {
            return [];
        }

        return array_is_list($node) ? array_values(array_filter($node, 'is_array')) : [$node];
    }

    /**
     * The first group of a node that may repeat.
     *
     * @return array<string, mixed>|null
     */
    public static function group(mixed $node): ?array
    {
        return self::groups($node)[0] ?? null;
    }

    /**
     * Values of a field that may occur repeatedly (e.g. BT-29 seller identifiers).
     *
     * @return list<mixed>
     */
    public static function leaves(mixed $node): array
    {
        if ($node === null) {
            return [];
        }

        return is_array($node) && array_is_list($node) ? $node : [$node];
    }

    /**
     * Text of a field without surrounding blanks; repeated values joined with a comma.
     */
    public static function value(mixed $node): ?string
    {
        if (is_string($node)) {
            $node = trim($node);

            return $node === '' ? null : $node;
        }

        if (! is_array($node)) {
            return null;
        }

        if (array_key_exists('value', $node)) {
            return self::value($node['value']);
        }

        if (array_is_list($node)) {
            $values = array_filter(array_map(static fn(mixed $item): ?string => self::value($item), $node), static fn(?string $item): bool => $item !== null);

            return $values === [] ? null : implode(', ', $values);
        }

        return null;
    }

    /**
     * Multi-line text of a field, repeated values on lines of their own.
     */
    public static function text(mixed $node): ?string
    {
        $values = is_array($node) && array_is_list($node) ? $node : [$node];
        $texts = [];
        foreach ($values as $value) {
            $text = is_array($value) ? ($value['value'] ?? null) : $value;
            if (is_string($text) && ($text = self::clean($text)) !== '') {
                $texts[] = $text;
            }
        }

        return $texts === [] ? null : implode("\n", $texts);
    }

    /**
     * An attribute of a value (scheme_identifier, mime_code, filename ...), null where it is missing or blank.
     */
    public static function attribute(mixed $node, string $name): ?string
    {
        $value = is_array($node) ? ($node[$name] ?? null) : null;

        return is_string($value) && trim($value) !== '' ? trim($value) : null;
    }

    /**
     * The source path of a group or value (with meta), '' where it has none.
     */
    public static function source(mixed $group): string
    {
        return is_array($group) && is_string($group['_src'] ?? null) ? $group['_src'] : '';
    }

    /**
     * Identifiers of a field that may occur repeatedly, each with its scheme.
     *
     * @return list<array{value: string, scheme: string|null}>
     */
    public static function schemedValues(mixed $node): array
    {
        $values = [];
        foreach (self::leaves($node) as $leaf) {
            $value = self::value($leaf);
            if ($value !== null) {
                $values[] = ['value' => $value, 'scheme' => self::attribute($leaf, 'scheme_identifier')];
            }
        }

        return $values;
    }

    /**
     * The values of a field one by one, each once - for values that are interpreted on their own
     * (an IBAN next to an account number).
     *
     * @return list<string>
     */
    public static function distinctValues(mixed $node): array
    {
        $values = [];
        foreach (self::leaves($node) as $leaf) {
            $value = self::value($leaf);
            if ($value !== null && ! in_array($value, $values, true)) {
                $values[] = $value;
            }
        }

        return $values;
    }

    /**
     * A field over every occurrence of a group the model expects once: a second contact is
     * information as well.
     *
     * @param list<array<string, mixed>> $groups
     */
    public static function across(array $groups, string $field): ?string
    {
        $values = [];
        foreach ($groups as $group) {
            array_push($values, ...self::distinctValues($group[$field] ?? null));
        }

        return $values === [] ? null : implode(', ', array_unique($values));
    }

    /**
     * Text without surrounding blanks per line and without runs of empty lines (tabs from
     * senders that indent their notes, CR LF).
     */
    public static function clean(string $text): string
    {
        $lines = array_map(static fn(string $line): string => trim(preg_replace('/[ \t\x{00A0}]+/u', ' ', $line) ?? $line), preg_split('/\R/u', $text) ?: []);
        $text = implode("\n", $lines);

        return trim(preg_replace("/\n{3,}/", "\n\n", $text) ?? $text);
    }
}
