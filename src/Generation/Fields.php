<?php

declare(strict_types=1);

namespace Dealerweb\EInvoice\Generation;

use DateTimeInterface;
use Dealerweb\EInvoice\Document;
use Dealerweb\EInvoice\Kosit\Transformer;
use Dealerweb\EInvoice\Rules;
use Dealerweb\EInvoice\SemanticModel;
use Dealerweb\EInvoice\Syntax;
use DOMElement;
use InvalidArgumentException;

/**
 * Turns the fields of an invoice, as a user gives them, into nodes of the tree of a syntax (Tree):
 *
 *  - Keys are ids of the fields: the business terms and groups of EN 16931 (BT-1, BG-4), the extensions of Factur-X
 *    (BT-X-202, BG-X-36) and, where several instances of an element have to be grouped, the ids of the tree for
 *    elements without a business term of their own (BT-17-00: one AdditionalReferencedDocument in CII).
 *  - A field may be given in any node above it: the nodes in between are created, and a second field for the same
 *    node goes into the same instance (BT-27 and BG-5 into one seller, even given at the top level). It may also be
 *    given in a group of EN 16931 that has it although the syntax puts its element elsewhere (the remittance
 *    information BT-83 in the payment instructions BG-16, the direct debit BG-19 in the payment instructions it belongs
 *    to, the invoicing period BG-14 in the delivery information BG-13 as EN 16931-1 has it) - in no other group. Where
 *    the elements of several given groups contain it, it goes into the innermost element.
 *  - A list gives several instances: of the field itself if it repeats (BT-29), otherwise of the nearest node above it
 *    that repeats (BT-17 in one AdditionalReferencedDocument each). A group given as an array is a new instance if it
 *    repeats (an invoice line), the one instance otherwise. A list of one value is that value; null, '' and [] are no
 *    value, also in a list.
 *  - An element with attributes is given as ['value' => ..., '<attribute id>' => ...] - or with the attributes next
 *    to it (BT-34 and BT-34-1; BT-29 and BT-29-1).
 *  - Values: dates as YYYY-MM-DD, DateTimeInterface or YYYYMMDD of a day that exists (written as the syntax writes a
 *    date), indicators as bool, numbers as string (1234.56 - with a point, without thousands separators) or int (a
 *    float is written with at most 10 decimals, as short as it reads back), everything else as string - as the
 *    document shall show it, in UTF-8 with the characters XML allows. What the specification fixes (the scheme VA of
 *    a VAT identifier, the type code 916 of a supporting document) cannot be changed; a code the syntax writes from
 *    another list is translated (the VAT point date code of EN 16931, UNTDID 2005, is one of UNTDID 2475 in CII).
 *  - The rules of the field list of CII: an identifier with a scheme is a GlobalID (BT-29 with BT-29-1), without one an
 *    ID; a payment account (BT-84) that is no IBAN is a ProprietaryID.
 *
 * @internal
 */
final class Fields
{
    private const IBAN = '/^[A-Z]{2}\d{2}[A-Z0-9]{11,30}$/D';

    /**
     * The groups of EN 16931-1 the model of KoSIT (SemanticModel) moved a business term or group out of: the invoicing period
     * BG-14 with its dates lies in the delivery information BG-13 there, at the invoice level in the model - as in CII
     * and UBL.
     */
    private const EN16931_GROUPS = ['BG-14' => ['BG-13'], 'BT-73' => ['BG-13'], 'BT-74' => ['BG-13']];

    /** A character XML does not allow (XML 1.0, 2.2). */
    private const NO_XML_CHARACTER = '/[^\x{9}\x{A}\x{D}\x{20}-\x{D7FF}\x{E000}-\x{FFFD}\x{10000}-\x{10FFFF}]/u';

    /** The data types of the numbers of the trees - of the field list (CII) and of the schema (UBL): xs:decimal. */
    private const NUMBERS = ['Amount', 'Unit Price Amount', 'Quantity', 'Percentage', 'Rate', 'Numeric', 'Percent'];

    /** A decimal as XML Schema writes it (xs:decimal) - blanks around it are allowed there as well. */
    private const DECIMAL = '/^\s*[+-]?(\d+(\.\d*)?|\.\d+)\s*$/D';

    /**
     * The attributes of a value in the KoSIT model and the suffix of the field that holds them: the id of an attribute
     * of the field list is the id of its element plus -1 for the scheme or the MIME code, -2 for the scheme version or
     * the file name.
     */
    private const ATTRIBUTES = [
        'scheme_identifier' => '-1',
        'scheme_version_identifier' => '-2',
        'mime_code' => '-1',
        'filename' => '-2',
    ];

    /**
     * The VAT point date codes of UBL (UNTDID 2005) and their counterparts in CII (UNTDID 2475): invoice date, delivery
     * date, date of payment (CEN/TS 16931-3-2 and -3-3).
     */
    private const VAT_POINT_DATE_CODES = ['3' => '5', '35' => '29', '432' => '72'];

    /**
     * Attribute values of the KoSIT model that CII says by its element: a UBL invoice marks the bank assigned creditor
     * identifier (BT-90) with the scheme SEPA, CII has the element ram:CreditorReferenceID for it.
     */
    private const IMPLIED = [
        'BT-90' => ['scheme_identifier' => ['SEPA']],
    ];

    /** The object identifiers (invoiced object, line object) whose scheme is an element of its own in CII. */
    private const OBJECT_IDENTIFIERS = ['BT-18', 'BT-128'];

    /** @var array<string, int> the depth of each node in the tree, by its id */
    private array $depths = [];

    /**
     * Values placed when all others are: a single value whose element lies in a repeating element goes into its first
     * instance, which a field given later may make (the payment means BG-16 given after the direct debit BG-19).
     *
     * @var list<array{0: non-empty-list<array{0: Node, 1: string|null, 2: Node|null}>, 1: int, 2: string, 3: mixed, 4: bool}>
     */
    private array $deferred = [];

    private function __construct(private readonly Tree $tree) {}

    /**
     * @param array<mixed> $fields
     * @return list<Node> the children of the root element
     * @throws InvalidArgumentException an unknown field, a field in the wrong place, a field given twice, a wrong value
     */
    public static function nodes(Tree $tree, array $fields): array
    {
        $root = new Node('');
        $placement = new self($tree);
        $placement->place([[$root, null, null]], $fields);
        // A group placed late may defer values of its own again.
        while ($placement->deferred !== []) {
            $jobs = $placement->deferred;
            $placement->deferred = [];
            foreach ($jobs as $job) {
                $placement->placeItem(...$job);
            }
        }
        $placement->prune($root);

        return $root->children;
    }

    /**
     * The fields of a read document for CII, from its KoSIT model (Document::intermediate()): business terms by their
     * id, groups as arrays, several of one id as a list; the attributes of a value (scheme, version, MIME code, file
     * name) as the fields of the field list that hold them.
     *
     * @return array<string, mixed>
     * @throws InvalidArgumentException the document holds a value CII has no place for
     */
    public static function ofDocument(Document $document): array
    {
        $root = $document->intermediate()->documentElement ?? throw new InvalidArgumentException('The invoice has no content.');
        $cii = $document->syntax() === Syntax::Cii;
        $reader = new self(Tree::cii());

        // The scheme of an object identifier, an element next to it in CII: KoSIT reads it from UBL only - from CII it
        // is among the values the model does not carry, found by the element it belongs to.
        $schemes = [];
        foreach ($cii ? $document->unmapped() : [] as $value) {
            if (in_array((string) $value->id, ['BT-18-1', 'BT-128-1'], true)) {
                $schemes[self::parentPath($value->path)] = $value->value;
            }
        }
        // The invoiced object of UBL (a document reference with the type code 130) is in KoSIT's model an additional
        // supporting document as well - written as CII it would become one more document.
        $objects = [];
        if (! $cii) {
            foreach ($root->getElementsByTagNameNS(Transformer::XR_NAMESPACE, '*') as $element) {
                if ($element->getAttributeNS(Transformer::XR_NAMESPACE, 'id') === 'BT-18') {
                    $objects[] = self::parentPath($element->getAttributeNS(Transformer::XR_NAMESPACE, 'src'));
                }
            }
        }
        $fields = $reader->ofGroup($root, $schemes, $objects);

        // The VAT point date code (BT-8), one for the invoice: CII has it in each VAT breakdown - KoSIT reads it once
        // for each -, and as a code of UNTDID 2475, where UBL has one of UNTDID 2005.
        if (isset($fields['BT-8'])) {
            $codes = array_values(array_unique(is_array($fields['BT-8']) ? $fields['BT-8'] : [$fields['BT-8']]));
            if (count($codes) > 1) {
                throw new InvalidArgumentException('BT-8: the invoice names several VAT point date codes (' . implode(', ', $codes) . ') - EN 16931 has one.');
            }
            $fields['BT-8'] = $cii ? $codes[0] : (self::VAT_POINT_DATE_CODES[(string) $codes[0]] ?? throw new InvalidArgumentException("BT-8: the code {$codes[0]} has no counterpart in CII."));
        }
        // KoSIT joins the different tax point dates (BT-7) of the VAT breakdowns of a CII invoice with ";".
        if (isset($fields['BT-7']) && is_string($fields['BT-7']) && str_contains($fields['BT-7'], ';')) {
            throw new InvalidArgumentException("BT-7: the invoice names several VAT point dates ({$fields['BT-7']}) - EN 16931 has one.");
        }

        // The instalments of ZUGFeRD / Factur-X EXTENDED - payment terms, each with its due date and, for a direct
        // debit, its mandate reference: KoSIT joins the dates with ";" and gives the mandates in the same order. Each
        // date gets payment terms of its own again, with the mandate of the same place where there are as many; the
        // descriptions, joined with ";" as well, stay one text in the first.
        if ($cii && isset($fields['BT-9']) && is_string($fields['BT-9']) && str_contains($fields['BT-9'], ';')) {
            $dates = explode(';', $fields['BT-9']);
            $mandates = self::takeMandates($fields, count($dates));
            if ($mandates === null) {
                $fields['BT-9'] = $dates;
            } else {
                unset($fields['BT-9']);
                $fields['BT-20-00'] = array_map(static fn(string $date, string $mandate): array => ['BT-9' => $date, 'BT-89' => $mandate], $dates, $mandates);
            }
        }

        return $fields;
    }

    /**
     * Takes the mandate references (BT-89) out of the direct debit of the payment instructions if they are as many as
     * the due dates of the instalments - null if no or more than one direct debit has as many.
     *
     * @param array<string, mixed> $fields
     * @return list<string>|null
     */
    private static function takeMandates(array &$fields, int $count): ?array
    {
        $instructions = $fields['BG-16'] ?? null;
        if (! is_array($instructions)) {
            return null;
        }
        $list = array_is_list($instructions) ? $instructions : [$instructions];
        $found = null;
        foreach ($list as $index => $instruction) {
            $debit = is_array($instruction) ? ($instruction['BG-19'] ?? null) : null;
            $mandates = is_array($debit) ? ($debit['BT-89'] ?? null) : null;
            if (is_array($mandates) && array_is_list($mandates) && count($mandates) === $count && array_filter($mandates, 'is_string') === $mandates) {
                if ($found !== null) {
                    return null;
                }
                $found = $index;
            }
        }
        if ($found === null || ! is_array($list[$found]) || ! is_array($list[$found]['BG-19'])) {
            return null;
        }
        /** @var list<string> $mandates */
        $mandates = $list[$found]['BG-19']['BT-89'];
        unset($list[$found]['BG-19']['BT-89']);
        $fields['BG-16'] = array_is_list($instructions) ? $list : $list[0];

        return $mandates;
    }

    /**
     * @param array<string, string> $schemes the schemes of object identifiers by the path of their element (CII)
     * @param list<string> $objects the paths of the invoiced objects (UBL)
     * @return array<string, mixed>
     */
    private function ofGroup(DOMElement $group, array $schemes, array $objects): array
    {
        $fields = [];
        foreach ($group->childNodes as $element) {
            if (! $element instanceof DOMElement) {
                continue;
            }
            $id = $element->getAttributeNS(Transformer::XR_NAMESPACE, 'id');
            if (! $this->tree->has($id)) {
                throw new InvalidArgumentException("{$element->localName} ($id) has no place in CII.");
            }
            $source = $element->getAttributeNS(Transformer::XR_NAMESPACE, 'src');

            $hasElements = false;
            $children = [];
            foreach ($element->childNodes as $child) {
                if ($child instanceof DOMElement) {
                    $hasElements = true;
                    $children[] = $child->getAttributeNS(Transformer::XR_NAMESPACE, 'id');
                }
            }
            if ($hasElements) {
                // The invoiced object as supporting document: it holds its identifier only.
                if ($id === 'BG-24' && in_array($source, $objects, true) && array_diff($children, ['BT-122']) === []) {
                    continue;
                }
                $fields[$id][] = $this->ofGroup($element, $schemes, $objects);
                continue;
            }

            $attributes = [];
            foreach ($element->attributes as $attribute) {
                if ($attribute->namespaceURI === Transformer::XR_NAMESPACE || in_array($attribute->value, self::IMPLIED[$id][$attribute->localName] ?? [], true)) {
                    continue;
                }
                $suffix = self::ATTRIBUTES[$attribute->localName]
                    ?? throw new InvalidArgumentException("$id: unknown attribute {$attribute->localName}.");
                if ($this->tree->has($id . $suffix)) {
                    $attributes[$id . $suffix] = $attribute->value;
                } elseif (! $this->fixesScheme($id)) {
                    throw new InvalidArgumentException("$id: the value {$attribute->value} of {$attribute->localName} has no place in CII.");
                }
            }

            // Attributes of the element itself go with its value, the others (the scheme of an invoiced object
            // identifier is an element of its own) next to it.
            $target = $this->sourceNode($id, $source);
            if (in_array($target, self::OBJECT_IDENTIFIERS, true) && isset($schemes[self::parentPath($source)])) {
                $attributes[$target . '-1'] ??= $schemes[self::parentPath($source)];
            }
            $own = array_filter($attributes, fn(string $key): bool => $this->isAttributeOf($target, $key), ARRAY_FILTER_USE_KEY);
            $fields[$target][] = $own === [] ? $element->textContent : ['value' => $element->textContent] + $own;
            foreach (array_diff_key($attributes, $own) as $key => $value) {
                $fields[$key][] = $value;
            }
        }

        return array_map(static fn(array $values): mixed => count($values) === 1 ? $values[0] : $values, $fields);
    }

    /**
     * A path without its last step: the element a value of it belongs to.
     */
    private static function parentPath(string $path): string
    {
        $end = strrpos($path, '/');

        return $end === false ? '' : substr($path, 0, $end);
    }

    /**
     * The node of the field list a value of a CII source comes from: the KoSIT model gives the department of a contact
     * (ram:DepartmentName) as the contact point BT-41 like the person, the GlobalID of a party as its identifier BT-29
     * and a ProprietaryID as the payment account BT-84 - the field list has the other element as <id>-0.
     */
    private function sourceNode(string $id, string $source): string
    {
        if (! preg_match('#/(\w+:\w+)(?:\[\d+\])?$#', $source, $match) || $this->tree->node($id)['name'] === $match[1]) {
            return $id;
        }
        $alternative = $id . '-0';
        if ($this->tree->has($alternative)
            && $this->tree->node($alternative)['name'] === $match[1]
            && $this->tree->node($alternative)['parent'] === $this->tree->node($id)['parent']) {
            return $alternative;
        }

        return $id;
    }

    /**
     * Whether a field (or its GlobalID) has the attribute.
     */
    private function isAttributeOf(string $id, string $attribute): bool
    {
        $node = $this->tree->node($id);

        return in_array($attribute, $this->tree->children($id), true)
            || (isset($node['global']) && in_array($attribute, $this->tree->children($node['global']), true));
    }

    /**
     * Whether the field list fixes the scheme of a field (VA of a VAT identifier, FC of a tax number) - the syntax says
     * it then, whatever scheme UBL may have given the value.
     */
    private function fixesScheme(string $id): bool
    {
        foreach ($this->tree->children($id) as $child) {
            if ($this->tree->node($child)['name'] === '@schemeID' && isset($this->tree->node($child)['fixed'])) {
                return true;
            }
        }

        return false;
    }

    /**
     * Places the fields given in a group:
     *
     *  - A field goes into the innermost element among the given groups that contains it in the syntax: the groups of
     *    EN 16931 and the elements of CII do not always nest alike - the remittance information (BT-83) of the payment
     *    instructions (BG-16) is an element of the settlement, not of the payment means.
     *  - A single value whose element lies in a repeating element (the VAT point date BT-7 in ram:ApplicableTradeTax)
     *    goes into its first instance - placed after all other fields, so that the instances given (BG-23) exist.
     *  - A list for a field that repeats in EN 16931 but occurs once below this group in the syntax (the credit transfer
     *    accounts BG-17 of the payment instructions BG-16) gives an instance of this group per item, each with the
     *    fields EN 16931 has in the group itself (the payment means code).
     *
     * @param non-empty-list<array{0: Node, 1: string|null, 2: Node|null}> $contexts the given groups, from the document to the current
     * @param array<mixed> $fields
     */
    private function place(array $contexts, array $fields): void
    {
        $fields = $this->attachAttributes(array_map(self::normalize(...), $fields));
        $fields = $this->multiply($contexts, $fields);

        foreach ($fields as $key => $value) {
            if (! is_string($key) || ! $this->tree->has($key)) {
                if (is_string($key) && $this->tree->syntax !== Syntax::Cii && Tree::cii()->has($key)) {
                    throw new InvalidArgumentException("$key has no place in {$this->tree->label()}.");
                }
                throw new InvalidArgumentException('Unknown field ' . var_export($key, true) . ' in ' . self::where(end($contexts)[1]) . '.');
            }
            if (self::isEmpty($value)) {
                continue;
            }
            $many = is_array($value) && array_is_list($value);
            // A list of one is the value - unless it is one instance of a field that repeats.
            if ($many && count($value) === 1 && ! $this->repeats($key)) {
                $value = $value[0];
                $many = false;
            }
            foreach ($many ? $value : [$value] as $item) {
                $id = $this->resolve($key, $item);
                $target = $this->target($contexts, $id);
                $job = [$contexts, $target, $id, $item, $many];
                if (! $many && $this->attaches($contexts[$target][1], $id)) {
                    $this->deferred[] = $job;
                } else {
                    $this->placeItem(...$job);
                }
            }
        }
    }

    /**
     * A value as the fields take it: a list - an array with integer keys only, also one filtered with gaps - without
     * the items that are no value.
     */
    private static function normalize(mixed $value): mixed
    {
        if (! is_array($value) || $value === [] || array_filter(array_keys($value), 'is_string') !== []) {
            return $value;
        }

        return array_values(array_filter($value, static fn(mixed $item): bool => ! self::isEmpty($item)));
    }

    /**
     * null, '' and [] are no value - nor an element whose value is none of its attributes, nor a group or list of
     * nothing else.
     */
    private static function isEmpty(mixed $value): bool
    {
        if ($value === null || $value === '' || $value === []) {
            return true;
        }
        if (! is_array($value)) {
            return false;
        }
        if (array_key_exists('value', $value)) {
            return $value['value'] === null || $value['value'] === '';
        }
        foreach ($value as $item) {
            if (! self::isEmpty($item)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Attributes given next to their element go with its value: BT-34-1 with BT-34, the scheme BT-29-1 of the GlobalID
     * with the identifier BT-29.
     *
     * @param array<mixed> $fields
     * @return array<mixed>
     */
    private function attachAttributes(array $fields): array
    {
        foreach ($fields as $key => $attribute) {
            if (! is_string($key) || ! $this->tree->has($key) || ! str_starts_with($this->tree->node($key)['name'], '@')) {
                continue;
            }
            $element = $this->tree->node($key)['parent'];
            $owner = null;
            foreach (array_keys($fields) as $candidate) {
                if (is_string($candidate) && $this->tree->has($candidate) && ($candidate === $element || ($this->tree->node($candidate)['global'] ?? null) === $element)) {
                    $owner = $candidate;
                }
            }
            if ($owner === null || self::isEmpty($fields[$owner])) {
                continue;
            }
            unset($fields[$key]);
            if (self::isEmpty($attribute)) {
                continue;
            }
            $value = $fields[$owner];
            if (is_array($value) && array_is_list($value)) {
                // A list of one is the value; which of several values the attribute goes with nothing tells - a list
                // for a field that occurs once is refused when it is placed.
                if (count($value) > 1) {
                    if ($this->repeats($owner)) {
                        throw new InvalidArgumentException("$key goes with a value of $owner, which is a list: give each value as ['value' => ..., '$key' => ...].");
                    }
                    $fields[$key] = $attribute;

                    continue;
                }
                $value = $value[0];
            }
            if (is_array($value) && array_key_exists($key, $value)) {
                throw new InvalidArgumentException("$key is given twice.");
            }
            $fields[$owner] = (is_array($value) ? $value : ['value' => $value]) + [$key => $attribute];
        }

        return $fields;
    }

    /**
     * The given group a field goes into: of those whose element contains the field in the syntax the one whose element
     * lies deepest. Every group given inside it whose element does not contain the field has to have it in EN 16931.
     *
     * @param non-empty-list<array{0: Node, 1: string|null, 2: Node|null}> $contexts
     */
    private function target(array $contexts, string $id): int
    {
        $target = 0;
        foreach ($contexts as $index => [, $contextId]) {
            if ($this->contains($contextId, $id) && $this->depth($contextId) >= $this->depth($contexts[$target][1])) {
                $target = $index;
            }
        }
        for ($index = count($contexts) - 1; $index > $target; $index--) {
            $contextId = $contexts[$index][1];
            if (! $this->contains($contextId, $id) && ! self::inGroup($id, $contextId)) {
                throw new InvalidArgumentException("$id does not belong into " . self::where($contextId) . '.');
            }
        }

        return $target;
    }

    /**
     * Splits the fields of a repeating group when a list gives a field that repeats in EN 16931 but occurs once below
     * the group in the syntax (place()): the first item stays, each further one goes with the fields EN 16931 has in
     * the group itself into a new instance of it.
     *
     * @param non-empty-list<array{0: Node, 1: string|null, 2: Node|null}> $contexts
     * @param array<mixed> $fields
     * @return array<mixed> the fields for the current instance
     */
    private function multiply(array $contexts, array $fields): array
    {
        $contextId = end($contexts)[1];
        if ($contextId === null || count($contexts) < 2 || ! $this->repeats($contextId)) {
            return $fields;
        }
        foreach ($fields as $key => $value) {
            if (! is_string($key) || ! $this->tree->has($key) || ! is_array($value) || count($value) < 2 || ! array_is_list($value)
                || ! SemanticModel::repeats(self::term($key)) || ! $this->contains($contextId, $key) || $this->repeatsBetween($contextId, $key)) {
                continue;
            }
            // The plain values of the group itself go into every instance (the payment means code), its groups stay in
            // the first (one payment card, one direct debit); a value of the list's group given next to the list would
            // belong to one item of it only.
            $others = [];
            foreach ($fields as $otherKey => $other) {
                if ($otherKey === $key || ! is_string($otherKey) || ! $this->tree->has($otherKey)) {
                    continue;
                }
                if (in_array($key, SemanticModel::groupsOf(self::term($otherKey)), true)) {
                    throw new InvalidArgumentException("$otherKey belongs to one $key of the list: give it in that $key.");
                }
                if ($this->tree->isLeaf($otherKey) && $this->contains($contextId, $otherKey)) {
                    $others[$otherKey] = $other;
                }
            }
            $parent = end($contexts)[2] ?? throw new InvalidArgumentException("$contextId has no parent element.");
            foreach (array_slice($value, 1) as $item) {
                $instance = self::append($parent, $contextId, $contextId);
                $this->place([...array_slice($contexts, 0, -1), [$instance, $contextId, $parent]], $others + [$key => $item]);
            }
            $fields[$key] = $value[0];
        }

        return $fields;
    }

    /**
     * Whether the element of a field lies in a repeating element below the context (not the field itself).
     */
    private function attaches(?string $contextId, string $id): bool
    {
        $chain = $this->chain($contextId, $id);
        array_pop($chain);
        foreach ($chain as $node) {
            if ($this->repeats($node)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Whether a field or a node between it and the context repeats.
     */
    private function repeatsBetween(string $contextId, string $id): bool
    {
        foreach ($this->chain($contextId, $id) as $node) {
            if ($this->repeats($node)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Places one value of a field below the given group it goes into: shared instances above, new instances where the
     * field or a node above it repeats.
     *
     * @param non-empty-list<array{0: Node, 1: string|null, 2: Node|null}> $contexts
     */
    private function placeItem(array $contexts, int $target, string $id, mixed $item, bool $many): void
    {
        [$context, $contextId] = $contexts[$target];
        $chain = $this->chain($contextId, $id);
        $last = count($chain) - 1;
        $split = $last;
        if ($many && ! $this->repeats($id)) {
            $split = null;
            for ($level = $last - 1; $level >= 0; $level--) {
                if ($this->repeats($chain[$level])) {
                    $split = $level;
                    break;
                }
            }
            if ($split === null) {
                throw new InvalidArgumentException("$id occurs only once in " . self::where($contextId) . ', a list was given.');
            }
        }

        $parent = $context;
        $holder = $context;
        foreach ($chain as $level => $nodeId) {
            $new = $level >= $split && ($many || ($level === $last && $this->repeats($nodeId)));
            $holder = $parent;
            $parent = $new ? self::append($parent, $nodeId, $id) : self::shared($parent, $nodeId, $id);
        }

        // An element whose text holds several fields (the note of UBL) takes them like a group.
        if ($this->tree->isLeaf($id) && ! isset($this->tree->node($id)['codec'])) {
            $this->setValue($parent, $id, $item);

            return;
        }
        if (! is_array($item) || array_is_list($item)) {
            throw new InvalidArgumentException("$id is a group: give its fields as an array.");
        }
        // The groups given around it stay: a field of it may belong into one of them (the debited account BT-91 of a
        // direct debit into the payment means it was given in).
        $this->place([...$contexts, [$parent, $id, $holder]], $item);
    }

    /**
     * Whether EN 16931 has a field in a group although the syntax puts its element elsewhere (the remittance
     * information BT-83 of the payment instructions BG-16, the invoicing period BG-14 of the delivery information BG-13
     * in EN 16931-1) - by the business terms of both; an extension of Factur-X or an element without a business term
     * of its own lies only where the syntax has it.
     */
    private static function inGroup(string $id, ?string $group): bool
    {
        if ($group === null || ! preg_match('/^B[GT]-\d+/', $group, $groupTerm) || ! preg_match('/^B[GT]-\d+/', $id)) {
            return false;
        }
        $term = self::term($id);

        return in_array($groupTerm[0], [...SemanticModel::groupsOf($term), ...(self::EN16931_GROUPS[$term] ?? [])], true);
    }

    /**
     * The business term or group of EN 16931 a field belongs to: BT-20 for its payment terms BT-20-00.
     */
    private static function term(string $id): string
    {
        return preg_match('/^B[GT]-\d+/', $id, $match) === 1 ? $match[0] : $id;
    }

    /**
     * Whether a node lies below another in the tree (the document contains every node).
     */
    private function contains(?string $ancestor, string $id): bool
    {
        for ($current = $this->tree->node($id)['parent']; $current !== null; $current = $this->tree->node($current)['parent']) {
            if ($current === $ancestor) {
                return true;
            }
        }

        return $ancestor === null;
    }

    /**
     * How deep the element of a node lies in the tree - the document 0, the elements below the root 1.
     */
    private function depth(?string $id): int
    {
        if ($id === null) {
            return 0;
        }

        return $this->depths[$id] ??= 1 + $this->depth($this->tree->node($id)['parent']);
    }

    /**
     * The value of an element or attribute, with the attributes of an element given as ['value' => ..., id => ...].
     */
    private function setValue(Node $node, string $id, mixed $item): void
    {
        $format = null;
        if (is_array($item)) {
            if (! array_key_exists('value', $item)) {
                throw new InvalidArgumentException("$id: give the value as 'value' next to its attributes.");
            }
            foreach ($item as $key => $attribute) {
                if ($key === 'value' || self::isEmpty($attribute)) {
                    continue;
                }
                if (! is_string($key) || ! in_array($key, $this->tree->children($id), true)) {
                    throw new InvalidArgumentException("$id has no attribute " . var_export($key, true) . '.');
                }
                $this->setValue(self::shared($node, $key, $id), $key, $attribute);
                if ($this->tree->node($key)['name'] === '@format') {
                    $format = is_scalar($attribute) ? (string) $attribute : '';
                }
            }
            $item = $item['value'];
        }
        if ($node->value !== null) {
            throw new InvalidArgumentException("$id is given twice.");
        }
        $row = $this->tree->node($id);
        $value = self::text($this->tree, $id, $item, $format);
        $value = $row['codes'][$value] ?? $value;
        $fixed = $row['fixed'] ?? null;
        if ($fixed !== null && $value !== $fixed) {
            throw new InvalidArgumentException("$id is always \"$fixed\", \"" . Rules::excerpt($value) . '" was given.');
        }
        $node->value = $value;
    }

    /**
     * A value as the document writes it, by the data type of the field. A date is YYYY-MM-DD, YYYYMMDD or a
     * DateTimeInterface, written as the syntax writes a date - with a format other than 102 given (CII), any text of
     * that format. Also the check of a value of the model before its fields are placed (Model\FieldWriter).
     *
     * @throws InvalidArgumentException a value of the wrong kind
     */
    public static function text(Tree $tree, string $id, mixed $value, ?string $format = null): string
    {
        $type = $tree->node($id)['type'] ?? null;
        if ($type === 'Date' && ($format === null || $format === '102')) {
            if ($value instanceof DateTimeInterface) {
                return $value->format($tree->dateFormat());
            }
            if (is_string($value) && (preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $value, $match) === 1 || preg_match('/^(\d{4})(\d{2})(\d{2})$/', $value, $match) === 1)
                && checkdate((int) $match[2], (int) $match[3], (int) $match[1])) {
                return $tree->dateFormat() === 'Ymd' ? $match[1] . $match[2] . $match[3] : "$match[1]-$match[2]-$match[3]";
            }

            throw new InvalidArgumentException("$id: " . (is_string($value) ? '"' . Rules::excerpt($value) . '"' : get_debug_type($value)) . ' is no date (YYYY-MM-DD).');
        }
        if ($value instanceof DateTimeInterface) {
            throw new InvalidArgumentException("$id is no date.");
        }
        if ($type === 'Indicator') {
            return match ($value) {
                true, 'true' => 'true',
                false, 'false' => 'false',
                default => throw new InvalidArgumentException("$id is an indicator: give true or false."),
            };
        }
        if (is_bool($value)) {
            throw new InvalidArgumentException("$id is no indicator (true/false).");
        }
        if (is_int($value)) {
            return (string) $value;
        }
        if (is_float($value)) {
            return self::decimal($id, $value);
        }
        if (! is_string($value)) {
            throw new InvalidArgumentException("$id: expected a string, got " . get_debug_type($value) . '.');
        }
        if (! mb_check_encoding($value, 'UTF-8')) {
            throw new InvalidArgumentException("$id: the value is no UTF-8.");
        }
        if (preg_match(self::NO_XML_CHARACTER, $value, $match) === 1) {
            throw new InvalidArgumentException(sprintf('%s: the value holds the character U+%04X, which XML does not allow.', $id, mb_ord($match[0], 'UTF-8')));
        }
        if (in_array($type, self::NUMBERS, true) && preg_match(self::DECIMAL, $value) !== 1) {
            throw new InvalidArgumentException("$id: \"" . Rules::excerpt($value) . '" is no decimal number - write it like 1234.56, with a point and without thousands separators.');
        }

        return $value;
    }

    /**
     * A float as a decimal: rounded to 10 decimals and written with as few of them as read back the same number -
     * 1234567.89 as "1234567.89", not with the error of its binary form.
     */
    private static function decimal(string $id, float $value): string
    {
        if (! is_finite($value)) {
            throw new InvalidArgumentException("$id: no finite number.");
        }
        $rounded = round($value, 10);
        $text = sprintf('%.10F', $rounded);
        for ($decimals = 0; $decimals < 10; $decimals++) {
            $shorter = sprintf('%.' . $decimals . 'F', $rounded);
            if ((float) $shorter === $rounded) {
                $text = $shorter;
                break;
            }
        }
        if (str_contains($text, '.')) {
            $text = rtrim(rtrim($text, '0'), '.');
        }

        return (float) $text === 0.0 ? '0' : $text;
    }

    /**
     * The node a value goes into: a GlobalID if the identifier comes with its scheme, a ProprietaryID for an account
     * number that is no IBAN (CII).
     */
    private function resolve(string $id, mixed $item): string
    {
        $node = $this->tree->node($id);
        if (isset($node['global']) && is_array($item)
            && array_intersect(array_keys(array_filter($item, static fn(mixed $value): bool => ! self::isEmpty($value))), $this->tree->children($node['global'])) !== []) {
            return $node['global'];
        }
        $text = is_array($item) ? ($item['value'] ?? null) : $item;
        if (isset($node['proprietary']) && is_string($text) && ! preg_match(self::IBAN, strtoupper(str_replace(' ', '', $text)))) {
            return $node['proprietary'];
        }

        return $id;
    }

    /**
     * The nodes from below the context down to the field.
     *
     * @return non-empty-list<string>
     */
    private function chain(?string $contextId, string $id): array
    {
        $chain = [];
        $current = $id;
        while ($current !== $contextId) {
            if ($current === null) {
                throw new InvalidArgumentException("$id does not belong into " . self::where($contextId) . '.');
            }
            array_unshift($chain, $current);
            $current = $this->tree->node($current)['parent'];
        }
        if ($chain === []) {
            throw new InvalidArgumentException("$id is given in itself.");
        }

        return $chain;
    }

    private function repeats(string $id): bool
    {
        return $this->tree->node($id)['max'] !== 1;
    }

    private static function shared(Node $parent, string $id, string $given): Node
    {
        foreach ($parent->children as $child) {
            if ($child->id === $id) {
                return $child;
            }
        }

        return self::append($parent, $id, $given);
    }

    private static function append(Node $parent, string $id, string $given): Node
    {
        $node = new Node($id, given: $given);
        $parent->children[] = $node;

        return $node;
    }

    /**
     * Removes containers left without content - a group whose fields all belong elsewhere in the syntax (BG-13 with its
     * actual delivery date only in CII) would otherwise remain as an empty element. An element that has attributes but
     * no value is an error: the attribute was given without the value it belongs to.
     */
    private function prune(Node $node): void
    {
        $children = [];
        foreach ($node->children as $child) {
            $this->prune($child);
            if ($child->value === null && $child->children !== [] && $this->tree->isLeaf($child->id) && ! str_starts_with($this->tree->node($child->id)['name'], '@')
                && ! isset($this->tree->node($child->id)['codec'])) {
                throw new InvalidArgumentException(implode(', ', array_map(static fn(Node $attribute): string => $attribute->id, $child->children)) . " goes with a value of {$child->id}, which is not given.");
            }
            if ($child->value !== null || $child->children !== []) {
                $children[] = $child;
            }
        }
        $node->children = $children;
    }

    private static function where(?string $contextId): string
    {
        return $contextId === null ? 'the invoice' : $contextId;
    }
}
