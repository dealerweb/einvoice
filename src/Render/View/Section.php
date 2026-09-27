<?php

declare(strict_types=1);

namespace Dealerweb\EInvoice\Render\View;

use Dealerweb\EInvoice\CodeList;
use Dealerweb\EInvoice\CodeLists;
use Dealerweb\EInvoice\Document;
use Dealerweb\EInvoice\Labels;
use Dealerweb\EInvoice\ModelValues;
use Dealerweb\EInvoice\Render\Decimal;
use Dealerweb\EInvoice\Render\Format;
use Dealerweb\EInvoice\Render\Texts;
use Dealerweb\EInvoice\Rules;
use Dealerweb\EInvoice\UnmappedValue;

/**
 * A section of the view (head, parties, lines, totals, payment ...) with what every section needs: the data of
 * the invoice, and values written for the reader - dates and numbers for the language, codes by their name,
 * identifiers with their scheme, unmapped values as "label: value" rows.
 *
 * @internal
 */
abstract class Section
{
    /** Longest name of a scheme that still serves as label of a row; longer ones follow the value. */
    private const MAX_LABEL = 24;

    /** Data types of fields (Labels::dataType()) whose values are numbers. */
    private const NUMBER_TYPES = ['Amount', 'Unit Price Amount', 'Quantity', 'Percentage', 'Rate'];

    protected readonly Document $invoice;

    protected readonly string $language;

    protected readonly Texts $texts;

    protected readonly Format $format;

    /** @var array<string, mixed> the invoice with the element names of the model, groups with their source path */
    protected readonly array $data;

    protected readonly ?string $currency;

    /** @var array{delivery: string|null, start: string|null, end: string|null} */
    protected readonly array $dates;

    protected readonly Extras $extras;

    public function __construct(Context $context)
    {
        $this->invoice = $context->invoice;
        $this->language = $context->language;
        $this->texts = $context->texts;
        $this->format = $context->format;
        $this->data = $context->data;
        $this->currency = $context->currency;
        $this->dates = $context->dates;
        $this->extras = $context->extras;
    }

    // ---------------------------------------------------------------- values of the model (see ModelValues)

    /**
     * @return list<array<string, mixed>>
     */
    protected function groups(mixed $node): array
    {
        return ModelValues::groups($node);
    }

    /**
     * @return list<mixed>
     */
    protected function leaves(mixed $node): array
    {
        return ModelValues::leaves($node);
    }

    protected function value(mixed $node): ?string
    {
        return ModelValues::value($node);
    }

    protected function text(mixed $node): ?string
    {
        return ModelValues::text($node);
    }

    protected function attribute(mixed $node, string $name): ?string
    {
        return ModelValues::attribute($node, $name);
    }

    protected function source(mixed $group): string
    {
        return ModelValues::source($group);
    }

    /**
     * @return list<array{value: string, scheme: string|null}>
     */
    protected function schemedValues(mixed $node): array
    {
        return ModelValues::schemedValues($node);
    }

    /**
     * @return list<string>
     */
    protected function distinctValues(mixed $node): array
    {
        return ModelValues::distinctValues($node);
    }

    /**
     * @param list<array<string, mixed>> $groups
     */
    protected function across(array $groups, string $field): ?string
    {
        return ModelValues::across($groups, $field);
    }

    protected function clean(string $text): string
    {
        return ModelValues::clean($text);
    }

    // ---------------------------------------------------------------- texts

    /**
     * The given lines without empty ones - null or blank.
     *
     * @param array<mixed> $lines
     * @return list<string>
     */
    protected function filled(array $lines): array
    {
        return array_values(array_filter($lines, static fn(mixed $line): bool => is_string($line) && trim($line) !== ''));
    }

    /**
     * @param array<mixed> $lines
     */
    protected function joinLines(array $lines): ?string
    {
        $lines = $this->filled($lines);

        return $lines === [] ? null : implode("\n", $lines);
    }

    protected function nonEmpty(?string $value): ?string
    {
        return $value === null || trim($value) === '' ? null : trim($value);
    }

    /**
     * The trading name where it differs from the name.
     */
    protected function differentName(?string $tradingName, ?string $name): ?string
    {
        return $tradingName !== null && mb_strtolower($tradingName) !== mb_strtolower((string) $name) ? $tradingName : null;
    }

    /**
     * Rows without repetitions - the same identifier often comes twice, as party identifier and as
     * electronic address.
     *
     * @param list<array{0: string, 1: string}> $rows
     * @return list<array{0: string, 1: string}>
     */
    protected function unique(array $rows): array
    {
        $unique = [];
        foreach ($rows as $row) {
            $unique[$row[0] . "\0" . $row[1]] = $row;
        }

        return array_values($unique);
    }

    /**
     * Whether two texts say the same, regardless of case and blanks.
     */
    protected function sameText(?string $a, ?string $b): bool
    {
        return Rules::sameText($a, $b);
    }

    /**
     * A coded free text in one line: the code of its content (BT-X-5, BT-X-9) in brackets, unless the
     * text already names it ("Ihr Zeichen=8000172161"); its subject (BT-X-10) only without such a code.
     */
    protected function codedText(string $text, ?string $contentCode, ?string $subjectCode): string
    {
        $label = match (true) {
            $contentCode !== null && $contentCode !== '' => $this->mentions($text, $contentCode) ? null : $contentCode,
            $subjectCode !== null && $subjectCode !== '' => $this->codeName(CodeList::TextSubject, $subjectCode),
            default => null,
        };

        return $label === null ? $text : $text . ' (' . $label . ')';
    }

    /**
     * Whether a text contains a term, regardless of case, blanks and punctuation.
     */
    private function mentions(string $text, string $term): bool
    {
        $normalize = static function (string $value): string {
            $value = mb_strtolower($value);

            return preg_replace('/[^\p{L}\p{N}]+/u', '', $value) ?? $value;
        };
        $term = $normalize($term);

        return $term !== '' && str_contains($normalize($text), $term);
    }

    // ---------------------------------------------------------------- dates

    /**
     * A date of the model; KoSIT joins several dates with ";" (several due dates of ZUGFeRD EXTENDED).
     */
    protected function date(mixed $node): ?string
    {
        $value = $this->value($node);
        if ($value === null) {
            return null;
        }

        $dates = array_filter(array_map('trim', explode(';', $value)), static fn(string $date): bool => $date !== '');

        return implode(', ', array_map(fn(string $date): string => $this->format->date($date), $dates));
    }

    /**
     * Dates of the extension come as written in CII (format 102: YYYYMMDD).
     */
    protected function extensionDate(string $value): string
    {
        $value = trim($value);

        return preg_match('/^(\d{4})(\d{2})(\d{2})$/', $value, $match)
            ? $this->format->date("$match[1]-$match[2]-$match[3]")
            : $value;
    }

    /**
     * A date of the extension (CII format 102: YYYYMMDD) as ISO date, comparable with the dates of the model.
     */
    protected function isoDate(string $value): string
    {
        $value = trim($value);

        return preg_match('/^(\d{4})(\d{2})(\d{2})$/', $value, $match) ? "$match[1]-$match[2]-$match[3]" : $value;
    }

    /**
     * A period as label and value: a period of one day is a date ("Leistungsdatum 02.09.2026"), and
     * none at all where it is the delivery date - "02.09.2026 bis 02.09.2026" says nothing.
     *
     * @return array{0: string, 1: string|null}
     */
    protected function periodOrDate(?string $start, ?string $end, ?string $delivery, string $periodKey): array
    {
        if ($start !== null && $start === $end) {
            return $start === $delivery ? [$periodKey, null] : ['field.service_date', $this->date($start)];
        }

        return [$periodKey, $this->period($start, $end)];
    }

    private function period(mixed $from, mixed $to): ?string
    {
        $from = $this->date($from);
        $to = $this->date($to);

        return match (true) {
            $from !== null && $to !== null => $this->texts->get('value.period', ['from' => $from, 'to' => $to]),
            $from !== null => $this->texts->get('value.period_from', ['from' => $from]),
            $to !== null => $this->texts->get('value.period_to', ['to' => $to]),
            default => null,
        };
    }

    // ---------------------------------------------------------------- codes and identifiers

    protected function codeName(CodeList $list, string $code): string
    {
        return CodeLists::name($list, $code, $this->language) ?? $code;
    }

    protected function unitName(?string $code): string
    {
        return $code === null ? '' : $this->codeName(CodeList::Unit, $code);
    }

    protected function currencyName(?string $code): ?string
    {
        if ($code === null) {
            return null;
        }

        $name = CodeLists::name(CodeList::Currency, $code, $this->language);

        return $name === null || $name === $code ? $code : "$name ($code)";
    }

    /**
     * Identifier with the short name of its scheme: "A456123 (Kfz-Kennzeichen)".
     */
    protected function identifier(mixed $node, CodeList $schemes): ?string
    {
        // Each value with its own scheme - an identifier the model expects once may still repeat.
        $identifiers = [];
        foreach ($this->leaves($node) as $leaf) {
            $value = $this->value($leaf);
            if ($value !== null) {
                $scheme = $this->attribute($leaf, 'scheme_identifier');
                $identifiers[] = $scheme === null ? $value : $value . ' (' . $this->shortName($this->codeName($schemes, $scheme)) . ')';
            }
        }

        return $identifiers === [] ? null : implode(', ', array_unique($identifiers));
    }

    /**
     * Identifier rows labelled with their scheme where the scheme is known ("GLN: 4000001123452").
     *
     * @param list<array{0: string, 1: string}> $rows
     */
    protected function addIdentifier(array &$rows, string $key, mixed $node): void
    {
        foreach ($this->leaves($node) as $leaf) {
            $value = $this->value($leaf);
            if ($value !== null) {
                $this->addSchemed($rows, $key, $value, $this->attribute($leaf, 'scheme_identifier'), CodeList::IdentifierScheme, CodeList::ElectronicAddressScheme);
            }
        }
    }

    /**
     * @param list<array{0: string, 1: string}> $rows
     */
    protected function addSchemed(array &$rows, string $key, string $value, ?string $scheme, CodeList ...$lists): void
    {
        [$label, $text] = $this->schemed($key, $value, $scheme, ...$lists);
        $this->addLabel($rows, $label, $text);
    }

    /**
     * The address for electronic invoices (BT-34, BT-49), labelled with its kind: "E-Mail" for an email
     * address, otherwise the scheme - a Leitweg-ID, a Peppol participant identifier (GLN, VAT number) ...
     *
     * @param list<array{0: string, 1: string}> $rows
     * @param array{value: string, scheme: string|null} $address
     */
    protected function addElectronicAddress(array &$rows, array $address): void
    {
        if ($address['scheme'] === null || strtoupper($address['scheme']) === 'EM') {
            $this->add($rows, 'party.email', $address['value']);

            return;
        }

        $this->addSchemed($rows, 'party.electronic_address', $address['value'], $address['scheme'], CodeList::ElectronicAddressScheme);
    }

    /**
     * Label and value of a value with a scheme: the short name of the scheme as label where it
     * is short enough ("GLN", "Leitweg-ID"), otherwise the generic label and the scheme after the value.
     *
     * @return array{0: string, 1: string}
     */
    protected function schemed(string $key, string $value, ?string $scheme, CodeList ...$lists): array
    {
        [$label, $text] = $this->schemeLabel($value, $scheme, ...$lists);

        return [$label ?? $this->texts->get($key), $text];
    }

    /**
     * Short form of a scheme name: "GTIN - Globale Artikelnummer" becomes "GTIN", "EAN-Lokationsnummer
     * (GLN)" becomes "GLN", "D-U-N-S-Nummer (Data Universal Numbering System)" becomes "D-U-N-S-Nummer".
     */
    protected function shortName(string $name): string
    {
        $dash = strpos($name, ' - ');
        if ($dash !== false) {
            return trim(substr($name, 0, $dash));
        }

        if (preg_match('/\(([A-Z0-9][A-Z0-9-]{1,11})\)$/', $name, $match)) {
            return $match[1];
        }

        $before = trim((string) strstr($name, ' (', true));

        return $before !== '' && mb_strlen($before) <= self::MAX_LABEL ? $before : $name;
    }

    /**
     * VAT of a line or an allowance: the rate, with the category code where it is not the standard rate.
     */
    protected function vatText(?string $category, ?string $rate): ?string
    {
        $text = $rate === null ? null : $this->format->percent($rate);
        if ($category !== null && strtoupper($category) !== 'S') {
            $text = trim($text . ' ' . $category);
        }

        return $text;
    }

    /**
     * The reason of an allowance or charge: its text, otherwise the name of its code.
     *
     * @param array<string, mixed> $group
     */
    protected function reason(array $group, string $prefix, bool $allowance): ?string
    {
        $text = $this->text($group[$prefix . '_reason'] ?? null);
        if ($text !== null) {
            return $text;
        }

        $code = $this->value($group[$prefix . '_reason_code'] ?? null);

        return $code === null ? null : $this->codeName($allowance ? CodeList::AllowanceReason : CodeList::ChargeReason, $code);
    }

    /**
     * The short name of the scheme of a value where it can serve as label (null: the value needs the
     * label of its field), and the value - with the scheme after it where the name is too long.
     *
     * @return array{0: string|null, 1: string}
     */
    private function schemeLabel(string $value, ?string $scheme, CodeList ...$lists): array
    {
        if ($scheme === null) {
            return [null, $value];
        }

        $name = null;
        foreach ($lists as $list) {
            $name ??= CodeLists::name($list, $scheme, $this->language);
        }

        $short = $this->shortName($name ?? $scheme);
        if ($name !== null && mb_strlen($short) <= self::MAX_LABEL) {
            return [$short, $value];
        }

        return [null, $value . ' (' . $short . ')'];
    }

    private function schemeName(string $scheme): string
    {
        return $this->shortName(CodeLists::name(CodeList::IdentifierScheme, $scheme, $this->language)
            ?? CodeLists::name(CodeList::ElectronicAddressScheme, $scheme, $this->language)
            ?? $scheme);
    }

    // ---------------------------------------------------------------- rows

    /**
     * @param list<array{0: string, 1: string}> $rows
     */
    protected function add(array &$rows, string $key, ?string $value): void
    {
        if ($value !== null && trim($value) !== '') {
            $rows[] = [$this->texts->get($key), $value];
        }
    }

    /**
     * @param list<array{0: string, 1: string}> $rows
     */
    protected function addLabel(array &$rows, string $label, ?string $value): void
    {
        if ($value !== null && trim($value) !== '') {
            $rows[] = [$label, $value];
        }
    }

    /**
     * "label value" parts of one line of details.
     *
     * @param list<string> $parts
     */
    protected function addInline(array &$parts, string $label, ?string $value): void
    {
        if ($value !== null && trim($value) !== '') {
            $parts[] = $label === '' ? $value : $label . ' ' . $value;
        }
    }

    // ---------------------------------------------------------------- unmapped values (see Extras)

    /**
     * Rows for the unmapped values of a block, labelled relative to the block: in the seller's block
     * "Postal Address › ID" instead of "Accounting Supplier Party › Postal Address › ID".
     *
     * @return list<array{0: string, 1: string}>
     */
    protected function attachedRows(string $key): array
    {
        return $this->extraRows($this->extras->attached($key), $this->extras->depth($key));
    }

    /**
     * "label: value" rows for unmapped values: the name of the field without what the block says (UnmappedValue::label()),
     * the value formatted by its qualifiers.
     *
     * @param list<UnmappedValue> $values
     * @param int $skip element names the readable label leaves out (see UnmappedValue::technicalLabel())
     * @return list<array{0: string, 1: string}>
     */
    protected function extraRows(array $values, int $skip = 0): array
    {
        $rows = [];
        foreach ($values as $value) {
            $rows[] = [$value->label($this->language, $skip), $this->extraValue($value)];
        }

        return $rows;
    }

    /**
     * Values in groups of the element that contains them, in document order.
     *
     * @param list<UnmappedValue> $values
     * @return list<list<UnmappedValue>>
     */
    protected function byParent(array $values): array
    {
        $groups = [];
        foreach ($values as $value) {
            $groups[$this->parentPath($value)][] = $value;
        }

        return array_values($groups);
    }

    /**
     * Values in groups of the enclosing element with the given local name, e.g. one group per
     * discount term - also for values in its child elements.
     *
     * @param list<UnmappedValue> $values
     * @return list<list<UnmappedValue>>
     */
    protected function byElement(array $values, string $element): array
    {
        $pattern = '#^(.*?/(?:[^/:]+:)?' . preg_quote($element, '#') . '(?:\[\d+\])?)(?:/|$)#';

        $groups = [];
        foreach ($values as $value) {
            $groups[preg_match($pattern, $value->path, $match) ? $match[1] : $value->path][] = $value;
        }

        return array_values($groups);
    }

    /**
     * The path of the element that contains a value (for an attribute: the element that contains its element).
     */
    protected function parentPath(UnmappedValue $value): string
    {
        $element = preg_replace('#/@[^/]+$#', '', $value->path) ?? $value->path;

        return preg_replace('#/[^/]+$#', '', $element) ?? $element;
    }

    /**
     * Whether an unmapped value is a number: by the data type of its field where the field list knows it (CII),
     * otherwise by its text - an identifier like "12.50" of a batch stays as written.
     */
    private function isNumber(UnmappedValue $value): bool
    {
        $type = $value->id === null ? null : Labels::dataType($value->id);

        return $type === null || in_array($type, self::NUMBER_TYPES, true);
    }

    private function extraValue(UnmappedValue $value): string
    {
        $text = $this->clean($value->value);
        $attributes = $value->attributes;
        $last = $value->names === [] ? null : $value->names[array_key_last($value->names)];

        if (isset($attributes['unitCode'])) {
            $text = (Decimal::isDecimal($text) ? $this->format->quantity($text) : $text) . ' ' . $this->unitName($attributes['unitCode']);
            unset($attributes['unitCode']);
        } elseif (isset($attributes['currencyID'])) {
            $text = $this->format->money($text, $attributes['currencyID']);
            unset($attributes['currencyID']);
        } elseif (in_array($last, ['DateTimeString', 'DateString'], true)) {
            $text = $this->extensionDate($text);
        } elseif (Decimal::isDecimal($text) && $this->isNumber($value)) {
            $text = $this->format->number($text, Decimal::places($text));
        }

        if (isset($attributes['schemeID'])) {
            $text .= ' (' . $this->schemeName($attributes['schemeID']) . ')';
            unset($attributes['schemeID']);
        }

        foreach ($attributes as $name => $attribute) {
            $text .= ' (' . $name . ': ' . $attribute . ')';
        }

        return $text;
    }
}
