<?php

declare(strict_types=1);

namespace Dealerweb\EInvoice\Model;

/**
 * A class of the model in one of its uses, with the fields of its properties there (Mapping).
 *
 * @internal
 */
final class MappedObject
{
    /** @var array<string, MappedProperty> by name */
    public array $properties = [];

    public function __construct(public readonly string $class) {}
}
