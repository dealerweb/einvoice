<?php

declare(strict_types=1);

namespace Dealerweb\EInvoice\Model;

/**
 * A property of a class of the model in one of its uses (Mapping): a value with the fields it stands for, or an object;
 * a list with the element of the syntax an item is an instance of.
 *
 * @internal
 */
final class MappedProperty
{
    /**
     * @param list<string> $ids the fields of a value: one, or the same value in several syntaxes, or the ID and the
     *                          GlobalID of an identifier
     * @param string|null $node for a list: the element of the syntax an item is an instance of - null where each value
     *                          is one (each identifier of a party in CII)
     */
    public function __construct(
        public readonly string $name,
        public readonly bool $list,
        public readonly ?MappedObject $object,
        public readonly array $ids,
        public readonly ?string $node,
        /** 1 for a list whose first item is given by single properties of its object (the contact of a party in CII) */
        public int $offset = 0,
    ) {}
}
