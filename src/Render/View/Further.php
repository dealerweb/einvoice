<?php

declare(strict_types=1);

namespace Dealerweb\EInvoice\Render\View;

use Dealerweb\EInvoice\Labels;
use Dealerweb\EInvoice\UnmappedValue;

/**
 * "Further information": the unmapped values without a place of their own, under the title of the block they
 * belong to ("Verkäufer", "Pos. 1") or, where they belong to none, under the name of their group.
 *
 * @internal
 */
final class Further extends Section
{
    /**
     * @return list<array{title: string|null, rows: list<array{0: string, 1: string}>}>
     */
    public function build(): array
    {
        $groups = [];
        foreach ($this->extras->rest() as [$key, $value]) {
            $groups[$key . "\0" . $this->parentPath($value)][] = [$key, $value];
        }

        $sections = [];
        foreach ($groups as $group) {
            $key = $group[0][0];
            $values = array_column($group, 1);
            if ($key === null) {
                // Under the name of their group the values leave out what it says: "Rechnungsempfänger" above "Anschrift › Ort".
                $groupId = $this->innermostGroup($values[0]);
                $title = $groupId === null ? null : Labels::name($groupId, $this->language);
                $rows = $this->extraRows($values, $groupId === null ? 0 : Labels::depth($groupId) ?? 0);
            } else {
                $title = $this->blockTitle($key);
                $rows = $this->extraRows($values, $this->extras->depth($key));
            }
            $last = count($sections) - 1;

            if ($last >= 0 && $sections[$last]['title'] === $title) {
                array_push($sections[$last]['rows'], ...$rows);
            } else {
                $sections[] = ['title' => $title, 'rows' => $rows];
            }
        }

        return $sections;
    }

    /**
     * Title of a block: "Verkäufer", "Pos. 3" ...
     */
    private function blockTitle(string $key): string
    {
        $kind = explode(':', $key)[0];
        if ($kind === 'line') {
            return trim($this->texts->get('lines.pos') . ' ' . ($this->extras->linePosition($key) ?? ''));
        }

        return $this->texts->get(match ($kind) {
            'seller' => 'section.seller',
            'buyer' => 'section.buyer',
            'payee' => 'section.payee',
            'tax_representative' => 'section.tax_representative',
            'delivery' => 'section.delivery',
            'note' => 'section.notes',
            'payment' => 'section.payment',
            'allowance', 'charge' => 'section.document_allowances',
            'vat' => 'section.vat',
            default => 'section.attachments',
        });
    }

    /**
     * The innermost group of a value (CII only) that has a name, without the grouping elements ("-00").
     */
    private function innermostGroup(UnmappedValue $value): ?string
    {
        foreach (array_reverse($value->groups) as $group) {
            if (! str_ends_with($group, '-00')) {
                return Labels::name($group) === null ? null : $group;
            }
        }

        return null;
    }
}
