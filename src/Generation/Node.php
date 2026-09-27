<?php

declare(strict_types=1);

namespace Dealerweb\EInvoice\Generation;

/**
 * A node of a document in the terms of its tree (Tree): the id of the field or element, the text of an element or
 * attribute, the children of a container - in document order.
 *
 * @internal
 */
final class Node
{
    /**
     * @param list<Node> $children
     * @param string|null $given the field given for which the node was made - a container of a field (BT-20-00 for
     *                           the due date BT-9) is named by that field in a message
     */
    public function __construct(
        public readonly string $id,
        public ?string $value = null,
        public array $children = [],
        public ?string $given = null,
    ) {}
}
