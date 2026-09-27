<?php

declare(strict_types=1);

namespace Dealerweb\EInvoice\Render\View;

use Dealerweb\EInvoice\ModelValues;
use Dealerweb\EInvoice\UnmappedValue;

/**
 * The information outside of EN 16931 (Document::unmapped()) placed where it belongs: in the block whose source
 * it lies in (a line, note, party, payment instruction or VAT row - the most specific one wins), at a field or
 * group with a place of its own (ExtendedFields), or in the rest that the view shows under "Further
 * information". The values of an allowance or charge of zero go with it - it is left out.
 *
 * Blocks are named by a key: "seller", "buyer", "payee", "tax_representative", "delivery", "note:0",
 * "payment:1", "allowance:0", "charge:0", "vat:0", "attachment:0", "line:2" and "line:2.0" for a sub-line.
 *
 * @internal
 */
final class Extras
{
    /** @var array<string, list<UnmappedValue>> unmapped values by the key of the block they belong to */
    private array $attached = [];

    /** @var array<string, string> source path of each block that takes unmapped values */
    private array $sources = [];

    /** @var array<string, list<UnmappedValue>> unmapped header values with a place of their own, by field or group id */
    private array $special = [];

    /**
     * Unmapped values without a place in the document, with the key of the block they belong to
     * (null: none) - values without a field id and so without a name (UBL) are shown here, not in their block.
     *
     * @var list<array{0: string|null, 1: UnmappedValue}>
     */
    private array $rest = [];

    /** @var array<string, string> line identifier (BT-126) by the key of the line's block */
    private array $linePositions = [];

    /**
     * @param list<UnmappedValue> $unmapped
     * @param array<string, mixed> $data the invoice with meta (Document::toArray(true))
     */
    public function __construct(array $unmapped, array $data)
    {
        $anchors = $this->sources = $this->anchors($data);
        uasort($anchors, static fn(string $a, string $b): int => strlen($b) <=> strlen($a));
        $zero = Allowances::zeroSources($data);

        foreach ($unmapped as $value) {
            foreach ($zero as $source) {
                if (str_starts_with($value->path, $source . '/')) {
                    continue 2;
                }
            }

            if ($value->id !== null && in_array($value->id, ExtendedFields::HEADER_FIELDS, true)) {
                $this->special[$value->id][] = $value;
                continue;
            }

            $group = array_values(array_intersect($value->groups, ExtendedFields::HEADER_GROUPS))[0] ?? null;
            if ($group !== null) {
                $this->special[$group][] = $value;
                continue;
            }

            foreach ($anchors as $key => $source) {
                if (str_starts_with($value->path, $source . '/')) {
                    // Without a field id (UBL) the value would stand in its block under a technical English
                    // name - it goes to the further information, under the title of its block.
                    if ($value->id === null) {
                        $this->rest[] = [$key, $value];
                    } else {
                        $this->attached[$key][] = $value;
                    }
                    continue 2;
                }
            }

            $this->rest[] = [null, $value];
        }
    }

    /**
     * The values of a field or group of the extension with a place of its own (ExtendedFields).
     *
     * @return list<UnmappedValue>
     */
    public function special(string $id): array
    {
        return $this->special[$id] ?? [];
    }

    /**
     * The values that belong to a block.
     *
     * @return list<UnmappedValue>
     */
    public function attached(string $key): array
    {
        return $this->attached[$key] ?? [];
    }

    /**
     * The values of a block, taken out of it - the caller decides where each one goes.
     *
     * @return list<UnmappedValue>
     */
    public function take(string $key): array
    {
        $values = $this->attached($key);
        unset($this->attached[$key]);

        return $values;
    }

    /**
     * Puts a value into the further information, under the title of its block (null: of its group).
     */
    public function addToRest(?string $key, UnmappedValue $value): void
    {
        $this->rest[] = [$key, $value];
    }

    /**
     * @return list<array{0: string|null, 1: UnmappedValue}>
     */
    public function rest(): array
    {
        return $this->rest;
    }

    /**
     * Number of element names below the root down to the element of a block.
     */
    public function depth(string $key): int
    {
        return max(0, substr_count($this->sources[$key] ?? '', '/') - 1);
    }

    /**
     * The identifier of the line of a block key ("line:2" gives its BT-126, or its number).
     */
    public function linePosition(string $key): ?string
    {
        return $this->linePositions[$key] ?? null;
    }

    /**
     * Source paths of the blocks that can take unmapped values, by block key. A block whose source contains a
     * block of another kind is left out - its path is too general.
     *
     * @param array<string, mixed> $data
     * @return array<string, string>
     */
    private function anchors(array $data): array
    {
        $anchors = [];

        foreach (['SELLER' => 'seller', 'BUYER' => 'buyer', 'PAYEE' => 'payee', 'SELLER_TAX_REPRESENTATIVE_PARTY' => 'tax_representative'] as $name => $key) {
            foreach (ModelValues::groups($data[$name] ?? null) as $group) {
                $anchors[$key] = ModelValues::source($group);
            }
        }

        // KoSIT gives the whole transaction as source of the delivery information - the party
        // receiving the goods is the element around the delivery address, name or location.
        $delivery = ModelValues::group($data['DELIVERY_INFORMATION'] ?? null) ?? [];
        foreach (['DELIVER_TO_ADDRESS', 'Deliver_to_party_name', 'Deliver_to_location_identifier'] as $name) {
            if (preg_match('#^(.*?/[^/]*(?:ShipToTradeParty|cac:Delivery)(?:\[\d+\])?)(?:/|$)#', ModelValues::source($delivery[$name] ?? null), $match)) {
                $anchors['delivery'] = $match[1];
                break;
            }
        }

        $lists = ['INVOICE_NOTE' => 'note', 'PAYMENT_INSTRUCTIONS' => 'payment', 'DOCUMENT_LEVEL_ALLOWANCES' => 'allowance',
            'DOCUMENT_LEVEL_CHARGES' => 'charge', 'VAT_BREAKDOWN' => 'vat', 'ADDITIONAL_SUPPORTING_DOCUMENTS' => 'attachment'];
        foreach ($lists as $name => $key) {
            foreach (ModelValues::groups($data[$name] ?? null) as $index => $group) {
                $anchors[$key . ':' . $index] = ModelValues::source($group);
            }
        }

        $this->lineAnchors(ModelValues::groups($data['INVOICE_LINE'] ?? null), 'line:', $anchors);

        $anchors = array_filter($anchors, static fn(string $source): bool => $source !== '');
        $kind = static fn(string $key): string => explode(':', $key)[0];

        return array_filter($anchors, static function (string $source, string $key) use ($anchors, $kind): bool {
            foreach ($anchors as $other => $otherSource) {
                if ($kind($other) !== $kind($key) && str_starts_with($otherSource, $source . '/')) {
                    return false;
                }
            }

            return true;
        }, ARRAY_FILTER_USE_BOTH);
    }

    /**
     * @param list<array<string, mixed>> $lines
     * @param array<string, string> $anchors
     */
    private function lineAnchors(array $lines, string $prefix, array &$anchors): void
    {
        foreach ($lines as $index => $line) {
            $anchors[$prefix . $index] = ModelValues::source($line);
            $this->linePositions[$prefix . $index] = ModelValues::value($line['Invoice_line_identifier'] ?? null) ?? (string) ($index + 1);
            $this->lineAnchors(ModelValues::groups($line['SUB_INVOICE_LINE'] ?? null), $prefix . $index . '.', $anchors);
        }
    }
}
