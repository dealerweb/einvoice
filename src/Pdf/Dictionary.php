<?php

declare(strict_types=1);

namespace Dealerweb\EInvoice\Pdf;

/**
 * A dictionary object: entries by the names of their keys, in the order read or set. Values are PHP ints, bools and
 * null, lists for arrays, and the classes of this namespace.
 *
 * @internal
 */
final class Dictionary
{
    /**
     * @param array<string, mixed> $entries
     */
    public function __construct(public array $entries = []) {}

    public function get(string $key): mixed
    {
        return $this->entries[$key] ?? null;
    }

    public function set(string $key, mixed $value): void
    {
        $this->entries[$key] = $value;
    }

    public function remove(string $key): void
    {
        unset($this->entries[$key]);
    }

    /**
     * The value of a key if it is a name (/Type /Catalog: "Catalog"), otherwise null.
     */
    public function name(string $key): ?string
    {
        $value = $this->get($key);

        return $value instanceof Name ? $value->value : null;
    }
}
