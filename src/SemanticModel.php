<?php

declare(strict_types=1);

namespace Dealerweb\EInvoice;

/**
 * The EN 16931 semantic model as published by KoSIT (resources/compiled/model.php):
 * element names, BT/BG ids, cardinality and value types.
 *
 * @internal used by Document::toArray() and Generation\Fields
 *
 * @phpstan-type Element array{id: string, type: string, min: int, multiple: bool, description: string}
 */
final class SemanticModel
{
    /**
     * KoSIT creates one PAYMENT_INSTRUCTIONS per payment means code, although the model allows
     * only one - these elements are therefore always treated as lists.
     */
    private const ALWAYS_MULTIPLE = ['PAYMENT_INSTRUCTIONS'];

    /** @var array{root: string, types: array<string, array<string, Element>>}|null */
    private static ?array $model = null;

    /** @var array<string, list<string>>|null the groups above each business term and group, by its id */
    private static ?array $groups = null;

    /** @var array<string, bool> whether a business term or group repeats in the model, by its id */
    private static array $repeating = [];

    /**
     * Child elements of a group type, by element name.
     *
     * @return array<string, Element>
     */
    public static function children(string $type): array
    {
        return self::load()['types'][$type] ?? [];
    }

    public static function rootType(): string
    {
        return self::load()['root'];
    }

    public static function isGroup(string $type): bool
    {
        return isset(self::load()['types'][$type]);
    }

    /**
     * @param Element|null $spec element spec from children()
     */
    public static function isMultiple(string $name, ?array $spec): bool
    {
        return $spec === null || $spec['multiple'] || in_array($name, self::ALWAYS_MULTIPLE, true);
    }

    /**
     * The groups a business term or group lies in, e.g. BG-16 for the remittance information BT-83 - all of them
     * where it occurs in several places (the lines of an XRechnung also as sub invoice lines, BG-DEX-01).
     *
     * @return list<string>
     */
    public static function groupsOf(string $id): array
    {
        self::index();

        return self::$groups[$id] ?? [];
    }

    /**
     * Whether a business term or group may occur more than once where it occurs (the credit transfers BG-17 of the
     * payment instructions) - false for an id the model does not know.
     */
    public static function repeats(string $id): bool
    {
        self::index();

        return self::$repeating[$id] ?? false;
    }

    private static function index(): void
    {
        if (self::$groups === null) {
            self::$groups = [];
            self::$repeating = [];
            self::collectGroups(self::rootType(), []);
        }
    }

    /**
     * @param list<string> $above the ids of the groups above the type
     */
    private static function collectGroups(string $type, array $above): void
    {
        foreach (self::children($type) as $name => $element) {
            self::$groups[$element['id']] = array_values(array_unique([...self::$groups[$element['id']] ?? [], ...$above]));
            self::$repeating[$element['id']] = (self::$repeating[$element['id']] ?? false) || self::isMultiple((string) $name, $element);
            // A group that contains itself (sub invoice lines) is read once.
            if (self::isGroup($element['type']) && ! in_array($element['id'], $above, true)) {
                self::collectGroups($element['type'], [...$above, $element['id']]);
            }
        }
    }

    /**
     * @return array{root: string, types: array<string, array<string, Element>>}
     */
    private static function load(): array
    {
        return self::$model ??= require dirname(__DIR__) . '/resources/compiled/model.php';
    }
}
