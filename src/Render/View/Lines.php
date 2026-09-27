<?php

declare(strict_types=1);

namespace Dealerweb\EInvoice\Render\View;

use Dealerweb\EInvoice\CodeList;
use Dealerweb\EInvoice\Labels;
use Dealerweb\EInvoice\Render\Decimal;
use Dealerweb\EInvoice\Rules;
use Dealerweb\EInvoice\UnmappedValue;

/**
 * The lines of the invoice as rows of the table of lines: position, article, the texts of the item with their
 * details, quantity, price, VAT and amount - sub-lines below their line, long texts over rows of their own.
 *
 * @internal
 */
final class Lines extends Section
{
    /** Longest line identifier shown in the position column - six digits ("000010") fit. */
    private const MAX_POSITION = 6;

    /** Rows of the lines up to which they stay one table, and the rows of each table beyond (tables()). */
    private const MAX_TABLE_ROWS = 100;
    private const TABLE_ROWS = 50;

    /** Role of each field of an included product (BG-X-1). */
    private const INCLUDED_ITEM_FIELDS = [
        'BT-X-18' => 'name', 'BT-X-19' => 'description', 'BT-X-20' => 'quantity',
        'BT-X-16' => 'seller_id', 'BT-X-17' => 'buyer_id', 'BT-X-15' => 'global_id', 'BT-X-309' => 'industry_id', 'BT-X-308' => 'id',
    ];

    /**
     * Lines in document order. Sub-lines come from the UBL extension of XRechnung (nested in the
     * model) or from the parent line id of ZUGFeRD EXTENDED (BT-X-304) - a group line is shown
     * before its sub-lines even where the document lists it after them.
     *
     * @return array{tables: list<list<array<string, mixed>>>, hasSubLines: bool, hasArticle: bool, none: string}
     */
    public function build(): array
    {
        $nodes = [];
        foreach ($this->groups($this->data['INVOICE_LINE'] ?? null) as $index => $line) {
            $nodes[] = $this->lineNode($line, 'line:' . $index);
        }

        $rows = [];
        foreach ($this->nest($nodes) as $node) {
            $this->flatten($node, 0, $rows);
        }

        $hasSubLines = false;
        $hasArticle = false;
        foreach ($rows as $row) {
            $hasSubLines = $hasSubLines || $row['depth'] > 0;
            $hasArticle = $hasArticle || $row['article'] !== null;
        }

        return [
            'tables' => $this->tables($this->tableRows($rows)),
            'hasSubLines' => $hasSubLines,
            'hasArticle' => $hasArticle,
            'none' => $this->texts->get('lines.none'),
        ];
    }

    /**
     * The rows of the lines in tables of their own, each with its head: at every page break dompdf
     * lays out the rest of a table anew - a thousand lines in one table took 24 s and 462 MB. Up to
     * MAX_TABLE_ROWS rows stay one table; a longer one is split every TABLE_ROWS rows, never within
     * the rows of one line.
     *
     * @param list<array<string, mixed>> $rows
     * @return list<list<array<string, mixed>>>
     */
    private function tables(array $rows): array
    {
        if (count($rows) <= self::MAX_TABLE_ROWS) {
            return $rows === [] ? [] : [$rows];
        }

        $tables = [];
        $table = [];
        foreach ($rows as $row) {
            if (count($table) >= self::TABLE_ROWS && ! str_contains($row['class'], 'continuation')) {
                $tables[] = $table;
                $table = [];
            }
            $table[] = $row;
        }

        return [...$tables, $table];
    }

    /**
     * The rows of the table: a line whose texts need more than one part (TextLayout::itemParts()) goes on in
     * rows that only carry its further texts - one block, without the rule between its rows.
     *
     * @param list<array<string, mixed>> $lines
     * @return list<array<string, mixed>>
     */
    private function tableRows(array $lines): array
    {
        $rows = [];
        foreach ($lines as $line) {
            $parts = $line['parts'] === [] ? [[]] : $line['parts'];
            $last = array_key_last($parts);
            foreach ($parts as $index => $blocks) {
                $class = 'row-' . $line['kind'] . ' depth-' . min(4, $line['depth'])
                    . ($index > 0 ? ' continuation' : '') . ($index < $last ? ' continued' : '');
                unset($line['parts']);
                $rows[] = $index === 0
                    ? ['class' => $class, 'blocks' => $blocks, ...$line]
                    : ['class' => $class, 'blocks' => $blocks, 'position' => null, 'article' => null, 'quantity' => null,
                        'price' => null, 'priceBasis' => null, 'vat' => null, 'amount' => null];
            }
        }

        return $rows;
    }

    /**
     * @param array<string, mixed> $line
     * @return array{line: array<string, mixed>, extras: list<UnmappedValue>, skip: int, status: string|null, parent: string|null, id: string|null, children: list<array<string, mixed>>}
     */
    private function lineNode(array $line, string $key): array
    {
        $extras = [];
        $status = null;
        $parent = null;
        foreach ($this->extras->attached($key) as $value) {
            if ($value->id === ExtendedFields::LINE_STATUS) {
                $status = strtoupper(trim($value->value));
            } elseif ($value->id === ExtendedFields::PARENT_LINE) {
                $parent = trim($value->value);
            } else {
                $extras[] = $value;
            }
        }

        $children = [];
        foreach ($this->groups($line['SUB_INVOICE_LINE'] ?? null) as $index => $sub) {
            $children[] = $this->lineNode($sub, $key . '.' . $index);
        }

        return [
            'line' => $line,
            'extras' => $extras,
            'skip' => $this->extras->depth($key),
            'status' => $status,
            'parent' => $parent === '' ? null : $parent,
            'id' => $this->value($line['Invoice_line_identifier'] ?? null),
            'children' => $children,
        ];
    }

    /**
     * Puts lines with a parent line id (BT-X-304) under their parent; cycles are broken.
     *
     * @param list<array<string, mixed>> $nodes
     * @return list<array<string, mixed>>
     */
    private function nest(array $nodes): array
    {
        $byId = [];
        foreach ($nodes as $index => $node) {
            if ($node['id'] !== null && ! isset($byId[$node['id']])) {
                $byId[$node['id']] = $index;
            }
        }

        $parentOf = [];
        foreach ($nodes as $index => $node) {
            $parent = $node['parent'] !== null ? ($byId[$node['parent']] ?? null) : null;
            if ($parent !== null && $parent !== $index) {
                $parentOf[$index] = $parent;
            }
        }

        if ($parentOf === []) {
            return $nodes;
        }

        foreach (array_keys($parentOf) as $index) {
            $seen = [$index => true];
            for ($parent = $parentOf[$index]; $parent !== null; $parent = $parentOf[$parent] ?? null) {
                if (isset($seen[$parent])) {
                    unset($parentOf[$index]);
                    break;
                }
                $seen[$parent] = true;
            }
        }

        $childrenOf = [];
        foreach ($parentOf as $index => $parent) {
            $childrenOf[$parent][] = $index;
        }

        $build = static function (int $index) use (&$build, $nodes, $childrenOf): array {
            $node = $nodes[$index];
            foreach ($childrenOf[$index] ?? [] as $child) {
                $node['children'][] = $build($child);
            }

            return $node;
        };

        $roots = [];
        $emitted = [];
        foreach (array_keys($nodes) as $index) {
            $root = $index;
            while (isset($parentOf[$root])) {
                $root = $parentOf[$root];
            }

            if (! isset($emitted[$root])) {
                $emitted[$root] = true;
                $roots[] = $build($root);
            }
        }

        return $roots;
    }

    /**
     * @param array<string, mixed> $node
     * @param list<array<string, mixed>> $rows
     */
    private function flatten(array $node, int $depth, array &$rows): void
    {
        $kind = match (true) {
            $node['status'] === 'INFORMATION' => 'information',
            $node['status'] === 'GROUP' || $node['children'] !== [] => 'group',
            $depth > 0 => 'sub',
            default => 'line',
        };

        $rows[] = $this->lineRow($node['line'], $node['extras'], $node['skip'], $depth, $kind);

        foreach ($node['children'] as $child) {
            $this->flatten($child, $depth + 1, $rows);
        }
    }

    /**
     * @param array<string, mixed> $line
     * @param list<UnmappedValue> $extras
     * @param int $skip element names of the line's own path, left out of the labels of its extras
     * @return array<string, mixed>
     */
    private function lineRow(array $line, array $extras, int $skip, int $depth, string $kind): array
    {
        $item = $this->groups($line['ITEM_INFORMATION'] ?? null)[0] ?? [];
        $price = $this->groups($line['PRICE_DETAILS'] ?? null)[0] ?? [];
        // Expected once, but a line can name more than one tax - none of them is dropped.
        $vats = [];
        foreach ($this->groups($line['LINE_VAT_INFORMATION'] ?? null) as $vat) {
            $vats[] = $this->vatText($this->value($vat['Invoiced_item_VAT_category_code'] ?? null), $this->value($vat['Invoiced_item_VAT_rate'] ?? null));
        }
        $vats = array_values(array_unique(array_filter($vats, static fn(?string $text): bool => $text !== null)));

        // The line identifier is chosen by the seller - mostly 1, 2, 3 or 0101, sometimes a text.
        // A long one moves from the narrow column to the details of the line.
        $position = $this->value($line['Invoice_line_identifier'] ?? null);
        $identifiers = [];
        if ($position !== null && mb_strlen($position) > self::MAX_POSITION) {
            $this->addInline($identifiers, $this->texts->get('lines.pos'), $position);
            $position = null;
        }
        // The buyer's item identifier only where it is not the one of the seller in the article column.
        $buyerItem = $this->value($item['Item_Buyers_identifier'] ?? null);
        if (! $this->sameText($buyerItem, $this->value($item['Item_Sellers_identifier'] ?? null))) {
            $this->addInline($identifiers, $this->texts->get('lines.item_buyer_id'), $buyerItem);
        }
        foreach ($this->leaves($item['Item_standard_identifier'] ?? null) as $identifier) {
            [$label, $value] = $this->schemed('lines.item_standard_id', (string) $this->value($identifier), $this->attribute($identifier, 'scheme_identifier'), CodeList::IdentifierScheme);
            $this->addInline($identifiers, $label, $value);
        }
        $this->addInline($identifiers, $this->texts->get('lines.order_line'), $this->value($line['Referenced_purchase_order_line_reference'] ?? null));
        $this->addInline($identifiers, $this->texts->get('lines.object'), $this->identifier($line['Invoice_line_object_identifier'] ?? null, CodeList::ReferenceQualifier));
        $this->addInline($identifiers, $this->texts->get('lines.accounting'), $this->value($line['Invoice_line_Buyer_accounting_reference'] ?? null));

        // The delivery date of the line (EXTENDED) and its period only where they add something to the
        // head of the document: a period of one day is a date, and none if it is the delivery date.
        $delivery = null;
        $extras = array_values(array_filter($extras, function (UnmappedValue $value) use (&$delivery): bool {
            if ($value->id === ExtendedFields::LINE_DELIVERY_DATE && $delivery === null) {
                $delivery = $this->isoDate($value->value);

                return false;
            }

            return true;
        }));

        $details = [];
        $period = $this->groups($line['INVOICE_LINE_PERIOD'] ?? null)[0] ?? [];
        $start = $this->value($period['Invoice_line_period_start_date'] ?? null);
        $end = $this->value($period['Invoice_line_period_end_date'] ?? null);
        if ($start !== $this->dates['start'] || $end !== $this->dates['end']) {
            [$periodKey, $periodText] = $this->periodOrDate($start, $end, $delivery ?? $this->dates['delivery'], 'lines.period');
            $this->addInline($details, $this->texts->get($periodKey), $periodText);
        }
        if ($delivery !== null && $delivery !== $this->dates['delivery']) {
            $this->addInline($details, $this->texts->get('field.delivery_date'), $this->date($delivery));
        }
        foreach ($this->groups($item['ITEM_ATTRIBUTES'] ?? null) as $attribute) {
            $this->addInline($details, (string) $this->value($attribute['Item_attribute_name'] ?? null), $this->value($attribute['Item_attribute_value'] ?? null));
        }
        foreach ($this->leaves($item['Item_classification_identifier'] ?? null) as $classification) {
            $this->addInline($details, $this->texts->get('lines.classification'), $this->classification($classification));
        }
        $origin = $this->value($item['Item_country_of_origin'] ?? null);
        $this->addInline($details, $this->texts->get('lines.origin'), $origin === null ? null : $this->codeName(CodeList::Country, $origin));

        // Discounts and charges on the gross price, each with its reason - matched by their element, a
        // discount without reason next to one with a reason must not take its reason.
        ['charges' => $charges, 'reasons' => $reasons, 'rest' => $extras] = $this->priceAllowanceCharges($extras);
        $withReason = function (string $amount, ?string $key) use (&$reasons): string {
            $texts = array_filter(array_map(fn(UnmappedValue $value): string => $this->clean($value->value), $reasons[$key ?? ''] ?? []));
            unset($reasons[$key ?? '']);

            return $this->format->amount($amount) . ($texts === [] ? '' : ' (' . implode(', ', $texts) . ')');
        };

        $grossPrice = $this->value($price['Item_gross_price'] ?? null);
        $discounts = [];
        $surcharges = [];
        foreach ($this->leaves($price['Item_price_discount'] ?? null) as $discount) {
            $amount = $this->value($discount);
            if ($amount !== null) {
                $key = $this->priceAllowanceCharge($this->source($discount));
                if ($key !== null && isset($charges[$key])) {
                    $surcharges[] = $withReason($amount, $key);
                } else {
                    $discounts[] = $withReason($amount, $key);
                }
            }
        }
        // A list price equal to the net price says nothing.
        $netPrice = $this->value($price['Item_net_price'] ?? null);
        if ($discounts === [] && $surcharges === [] && Decimal::equals($grossPrice, $netPrice)) {
            $grossPrice = null;
        }
        if ($grossPrice !== null || $discounts !== [] || $surcharges !== []) {
            $parts = [];
            $this->addInline($parts, $this->texts->get('lines.gross_price'), $grossPrice === null ? null : $this->format->amount($grossPrice));
            $this->addInline($parts, $this->texts->get('lines.price_discount'), $discounts === [] ? null : implode(', ', $discounts));
            $this->addInline($parts, $this->texts->get('lines.charge'), $surcharges === [] ? null : implode(', ', $surcharges));
            $details[] = implode(' · ', $parts);
        }
        foreach ($reasons as $unused) {
            array_push($extras, ...$unused);
        }

        foreach ($this->groups($line['INVOICE_LINE_ALLOWANCES'] ?? null) as $allowance) {
            if (! Allowances::isZero($allowance, 'Invoice_line_allowance')) {
                $details[] = $this->allowanceText('lines.allowance', $allowance, 'Invoice_line_allowance', true);
            }
        }
        foreach ($this->groups($line['INVOICE_LINE_CHARGES'] ?? null) as $charge) {
            if (! Allowances::isZero($charge, 'Invoice_line_charge')) {
                $details[] = $this->allowanceText('lines.charge', $charge, 'Invoice_line_charge', false);
            }
        }

        [$note, $extras] = $this->lineNote($line['Invoice_line_note'] ?? null, $extras);
        array_push($details, ...$this->lineExtras($extras, $skip));

        // Name and description as a person reads them (the same rule as the subject of the summary);
        // the note often only repeats them.
        [$name, $description] = Rules::itemName(
            $this->text($item['Item_name'] ?? null),
            $this->text($item['Item_description'] ?? null),
            $this->value($item['Item_Sellers_identifier'] ?? null),
        );
        if ($this->sameText($note, $name) || $this->sameText($note, $description)) {
            $note = null;
        }

        $quantity = $this->value($line['Invoiced_quantity'] ?? null);
        $amount = $this->value($line['Invoice_line_net_amount'] ?? null);
        $unit = $this->unitName($this->value($line['Invoiced_quantity_unit_of_measure_code'] ?? null));

        $blocks = [];
        foreach ([['item-name', $name], ['item-text', $description], ['item-text muted', $note],
            ['item-details', $identifiers === [] ? null : implode(' · ', $identifiers)]] as [$class, $text]) {
            if ($text !== null) {
                $blocks[] = ['class' => $class, 'text' => $text];
            }
        }
        foreach ($details as $detail) {
            $blocks[] = ['class' => 'item-details', 'text' => $detail];
        }
        if ($kind === 'information') {
            $blocks[] = ['class' => 'tag', 'text' => $this->texts->get('lines.information')];
        }

        return [
            'depth' => $depth,
            'kind' => $kind,
            'position' => $position,
            'article' => $this->value($item['Item_Sellers_identifier'] ?? null),
            'parts' => TextLayout::itemParts($blocks),
            'quantity' => $quantity === null ? null : trim($this->format->quantity($quantity) . ' ' . $unit),
            'price' => $netPrice === null ? null : $this->format->amount($netPrice),
            'priceBasis' => $this->priceBasis($price),
            'vat' => $vats === [] ? null : implode(', ', $vats),
            'amount' => $amount === null ? null : $this->format->amount($amount),
        ];
    }

    /**
     * The extra information of the discounts and charges on the gross price of a line (CII), by the
     * path of their element: which of them are charges - KoSIT takes every one as discount (BT-147),
     * the indicator of a charge comes as extra information - and their reasons (EXTENDED). The rest
     * stays extra information of the line. (UBL values carry no field id and go to the further
     * information; a UBL charge on the price is left out by KoSIT and shows up there.)
     *
     * @param list<UnmappedValue> $extras
     * @return array{charges: array<string, true>, reasons: array<string, list<UnmappedValue>>, rest: list<UnmappedValue>}
     */
    private function priceAllowanceCharges(array $extras): array
    {
        $charges = [];
        $reasons = [];
        $rest = [];
        foreach ($extras as $value) {
            $key = $this->priceAllowanceCharge($value->path);
            $name = $value->names[count($value->names) - 1];
            if ($key !== null && $name === 'Indicator' && Rules::indicator($value->value) === true) {
                $charges[$key] = true;
            } elseif ($key !== null && $name === 'Reason') {
                $reasons[$key][] = $value;
            } else {
                $rest[] = $value;
            }
        }

        return ['charges' => $charges, 'reasons' => $reasons, 'rest' => $rest];
    }

    /**
     * The path of the discount or charge on the gross price of a line that a path lies in, or null.
     */
    private function priceAllowanceCharge(string $path): ?string
    {
        return preg_match('#^(.*/(?:[^/:]+:)?GrossPriceProductTradePrice/(?:[^/:]+:)?AppliedTradeAllowanceCharge(?:\[\d+\])?)(?:/|$)#', $path, $match)
            ? $match[1]
            : null;
    }

    /**
     * "je 10 Stück" where the net price refers to more than one unit (BT-149, BT-150).
     *
     * @param array<string, mixed> $price
     */
    private function priceBasis(array $price): ?string
    {
        $basis = $this->value($price['Item_price_base_quantity'] ?? null);
        if ($basis === null || Decimal::isOne($basis)) {
            return null;
        }

        $unit = $this->unitName($this->value($price['Item_price_base_quantity_unit_of_measure'] ?? null));

        return $this->texts->get('lines.per', ['quantity' => trim($this->format->quantity($basis) . ' ' . $unit)]);
    }

    private function classification(mixed $node): ?string
    {
        $value = $this->value($node);
        if ($value === null) {
            return null;
        }

        $scheme = $this->attribute($node, 'scheme_identifier');
        $version = $this->attribute($node, 'scheme_version_identifier');
        $schemeName = $scheme === null ? null : $this->shortName($this->codeName(CodeList::ItemType, $scheme));

        return $value . ($schemeName !== null ? ' (' . $schemeName . ($version !== null ? ' ' . $version : '') . ')' : '');
    }

    /**
     * "Nachlass (Mengenrabatt): 5 % von 40,00 = -2,00"
     *
     * @param array<string, mixed> $group
     */
    private function allowanceText(string $labelKey, array $group, string $prefix, bool $allowance): string
    {
        $reason = $this->reason($group, $prefix, $allowance);
        $base = $this->value($group[$prefix . '_base_amount'] ?? null);
        $percent = $this->value($group[$prefix . '_percentage'] ?? null);
        $amount = $this->value($group[$prefix . '_amount'] ?? null);

        $calculation = $percent === null ? null : ($base === null
            ? $this->format->percent($percent)
            : $this->texts->get('lines.percent_of', ['percent' => $this->format->percent($percent), 'base' => $this->format->amount($base)]));
        $value = $amount === null ? null : $this->format->amount($allowance ? Decimal::negate($amount) : $amount);

        return $this->texts->get($labelKey) . ($reason !== null ? ' (' . $reason . ')' : '') . ': '
            . implode(' = ', array_filter([$calculation, $value], static fn(?string $part): bool => $part !== null));
    }

    /**
     * Extension values of a line: included products as one line each, the rest as "label: value".
     *
     * @param list<UnmappedValue> $values
     * @return list<string>
     */
    private function lineExtras(array $values, int $skip): array
    {
        $details = [];
        foreach ($this->byParent($values) as $group) {
            if (in_array(ExtendedFields::INCLUDED_ITEM, $group[0]->groups, true)) {
                $details[] = $this->includedItem($group);
                continue;
            }

            foreach ($this->extraRows($group, $skip) as [$label, $value]) {
                $details[] = $label . ': ' . $value;
            }
        }

        return $details;
    }

    /**
     * The notes of a line, each on a line of its own and with the codes the extension gives it (BT-X-9,
     * BT-X-10) - "Zolltarifnr=87089997 (Zolltarifnummer)" instead of three rows with their names.
     *
     * @param list<UnmappedValue> $extras
     * @return array{0: string|null, 1: list<UnmappedValue>} the notes and the extras not used by them
     */
    private function lineNote(mixed $node, array $extras): array
    {
        $notes = [];
        foreach ($this->leaves($node) as $leaf) {
            $text = $this->text($leaf);
            if ($text === null) {
                continue;
            }

            $source = $this->source($leaf);
            $element = $source === '' ? '' : substr($source, 0, (int) strrpos($source, '/'));
            $codes = [];
            foreach ($extras as $index => $value) {
                if ($element !== '' && in_array($value->id, [ExtendedFields::LINE_NOTE_CONTENT_CODE, ExtendedFields::LINE_NOTE_SUBJECT_CODE], true)
                    && ! isset($codes[$value->id]) && str_starts_with($value->path, $element . '/')) {
                    $codes[$value->id] = $this->clean($value->value);
                    unset($extras[$index]);
                }
            }

            $notes[] = $this->codedText($text, $codes[ExtendedFields::LINE_NOTE_CONTENT_CODE] ?? null, $codes[ExtendedFields::LINE_NOTE_SUBJECT_CODE] ?? null);
        }

        return [$notes === [] ? null : implode("\n", $notes), array_values($extras)];
    }

    /**
     * An included product (BG-X-1): "Enthält: 20 Stück Erdbeer 20 x 150g Becher (Art.-Nr. JOG103 · GTIN 4123...)".
     *
     * @param list<UnmappedValue> $values
     */
    private function includedItem(array $values): string
    {
        $fields = [];
        foreach ($values as $value) {
            $fields[self::INCLUDED_ITEM_FIELDS[$value->id ?? ''] ?? $value->label($this->language)][] = $value;
        }

        $quantity = null;
        if (isset($fields['quantity'][0])) {
            $unit = $fields['quantity'][0]->attributes['unitCode'] ?? null;
            $quantity = trim($this->format->quantity($fields['quantity'][0]->value) . ' ' . $this->unitName($unit));
        }

        $name = isset($fields['name'][0]) ? $this->clean($fields['name'][0]->value) : null;

        $identifiers = [];
        foreach (['seller_id' => 'lines.item_seller_id', 'buyer_id' => 'lines.item_buyer_id'] as $role => $key) {
            foreach ($fields[$role] ?? [] as $value) {
                $this->addInline($identifiers, $this->texts->get($key), $this->clean($value->value));
            }
        }
        foreach (['global_id', 'industry_id', 'id'] as $role) {
            foreach ($fields[$role] ?? [] as $value) {
                [$label, $text] = $this->schemed('lines.item_standard_id', $this->clean($value->value), $value->attributes['schemeID'] ?? null, CodeList::IdentifierScheme);
                $this->addInline($identifiers, $label, $text);
            }
        }
        foreach ($fields as $role => $list) {
            if (! in_array($role, self::INCLUDED_ITEM_FIELDS, true)) {
                foreach ($this->extraRows($list, Labels::depth(ExtendedFields::INCLUDED_ITEM) ?? 0) as [$label, $value]) {
                    $this->addInline($identifiers, $label, $value);
                }
            }
        }

        $description = isset($fields['description'][0]) ? $this->clean($fields['description'][0]->value) : null;
        $text = trim(implode(' ', array_filter([$quantity, $name])));
        $additions = array_filter([$description, $identifiers === [] ? null : implode(' · ', $identifiers)]);

        return $this->texts->get('lines.included') . ': ' . $text . ($additions === [] ? '' : ' (' . implode('; ', $additions) . ')');
    }
}
