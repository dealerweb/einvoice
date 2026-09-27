<?php

declare(strict_types=1);

namespace Dealerweb\EInvoice\Render\View;

use Dealerweb\EInvoice\CodeList;
use Dealerweb\EInvoice\UnmappedValue;

/**
 * The parties of the invoice as a letter shows them: the issuer in the letterhead, the addressee in the address
 * field with the information block beside it, the other parties (delivery, buyer or invoicee, contact person,
 * payee, tax representative) as rows below the references.
 *
 * A party is an array with the keys of PARTY; the issuer is the seller, the addressee the buyer - or the
 * invoicee of ZUGFeRD EXTENDED (BG-X-36), and the roles swap for a self-billed invoice.
 *
 * @internal
 */
final class Parties extends Section
{
    /** Role of each field of the invoicee (BG-X-36). */
    private const INVOICEE_FIELDS = [
        'BT-X-226' => 'name', 'BT-X-228' => 'trading_name',
        'BT-X-235' => 'line1', 'BT-X-236' => 'line2', 'BT-X-237' => 'line3', 'BT-X-234' => 'postcode',
        'BT-X-238' => 'city', 'BT-X-240' => 'subdivision', 'BT-X-239' => 'country',
        'BT-X-224' => 'identifier', 'BT-X-225' => 'identifier', 'BT-X-227' => 'registration', 'BT-X-242' => 'vat_id',
        'BT-X-241' => 'electronic_address', 'BT-X-229' => 'contact', 'BT-X-230' => 'contact', 'BT-X-231' => 'phone',
        'BT-X-233' => 'email',
    ];

    /** Elements of a CII postal address (PostalTradeAddress) and their role in the address. */
    private const ADDRESS_ELEMENTS = [
        'LineOne' => 'line1', 'LineTwo' => 'line2', 'LineThree' => 'line3', 'PostcodeCode' => 'postcode',
        'CityName' => 'city', 'CountrySubDivisionName' => 'subdivision', 'CountryID' => 'country',
    ];

    /** Title of the block of a party by its role. */
    private const ROLE_TITLES = ['seller' => 'section.seller', 'buyer' => 'section.buyer', 'invoicee' => 'section.invoicee'];

    /** A party (seller, buyer, invoicee): its keys with their defaults. */
    private const PARTY = [
        'role' => '', 'name' => null, 'tradingName' => null, 'contact' => null, 'phone' => null, 'email' => null,
        'address' => [], 'city' => null, 'countryCode' => null, 'country' => null, 'vatId' => null, 'taxNumber' => null,
        'registration' => [], 'identifiers' => [], 'electronic' => [], 'legal' => null, 'extras' => [],
    ];

    /**
     * @return array<string, mixed>|null
     */
    public function seller(): ?array
    {
        $seller = $this->groups($this->data['SELLER'] ?? null)[0] ?? null;
        if ($seller === null) {
            return null;
        }

        $contacts = $this->groups($seller['SELLER_CONTACT'] ?? null);

        return $this->partyData('seller', $seller['Seller_name'] ?? null, $seller['Seller_trading_name'] ?? null, $seller['SELLER_POSTAL_ADDRESS'] ?? null, 'Seller', [
            'contact' => $this->across($contacts, 'Seller_contact_point'),
            'phone' => $this->across($contacts, 'Seller_contact_telephone_number'),
            'email' => $this->across($contacts, 'Seller_contact_email_address'),
            'vatId' => $this->value($seller['Seller_VAT_identifier'] ?? null),
            'taxNumber' => $this->value($seller['Seller_tax_registration_identifier'] ?? null),
            'registration' => $this->schemedValues($seller['Seller_legal_registration_identifier'] ?? null),
            'identifiers' => [...$this->schemedValues($seller['Seller_identifier'] ?? null), ...$this->claimIdentifiers('seller', 'BT-29')],
            'electronic' => $this->schemedValues($seller['Seller_electronic_address'] ?? null),
            'legal' => $this->text($seller['Seller_additional_legal_information'] ?? null),
        ]);
    }

    /**
     * @return array<string, mixed>|null
     */
    public function buyer(): ?array
    {
        $buyer = $this->groups($this->data['BUYER'] ?? null)[0] ?? null;
        if ($buyer === null) {
            return null;
        }

        $contacts = $this->groups($buyer['BUYER_CONTACT'] ?? null);

        return $this->partyData('buyer', $buyer['Buyer_name'] ?? null, $buyer['Buyer_trading_name'] ?? null, $buyer['BUYER_POSTAL_ADDRESS'] ?? null, 'Buyer', [
            'contact' => $this->across($contacts, 'Buyer_contact_point'),
            'phone' => $this->across($contacts, 'Buyer_contact_telephone_number'),
            'email' => $this->across($contacts, 'Buyer_contact_email_address'),
            'vatId' => $this->value($buyer['Buyer_VAT_identifier'] ?? null),
            'registration' => $this->schemedValues($buyer['Buyer_legal_registration_identifier'] ?? null),
            'identifiers' => [...$this->schemedValues($buyer['Buyer_identifier'] ?? null), ...$this->claimIdentifiers('buyer', 'BT-46')],
            'electronic' => $this->schemedValues($buyer['Buyer_electronic_address'] ?? null),
        ]);
    }

    /**
     * The invoicee of the extension (BG-X-36) - the party that receives the invoice in place of the buyer.
     *
     * @return array<string, mixed>|null
     */
    public function invoicee(): ?array
    {
        $fields = [];
        $other = [];
        foreach ($this->extras->special(ExtendedFields::INVOICEE) as $value) {
            $role = self::INVOICEE_FIELDS[$value->id ?? ''] ?? null;
            if ($role === null) {
                $other[] = $value;
            } else {
                $fields[$role][] = $value;
            }
        }

        if ($fields === [] && $other === []) {
            return null;
        }

        $first = fn(string $role): ?string => isset($fields[$role][0]) ? ($this->clean($fields[$role][0]->value) ?: null) : null;
        $schemed = fn(string $role): array => array_values(array_filter(array_map(
            fn(UnmappedValue $value): array => ['value' => $this->clean($value->value), 'scheme' => $this->nonEmpty($value->attributes['schemeID'] ?? null)],
            $fields[$role] ?? [],
        ), static fn(array $identifier): bool => $identifier['value'] !== ''));

        $name = $first('name');
        $tradingName = $first('trading_name');
        $country = $first('country');
        $contacts = array_filter(array_map(
            fn(UnmappedValue $value): ?string => $this->differentName($this->differentName($this->clean($value->value) ?: null, $name), $tradingName),
            $fields['contact'] ?? [],
        ));

        return [
            ...self::PARTY,
            'role' => 'invoicee',
            'name' => $name,
            'tradingName' => $this->differentName($tradingName, $name),
            'contact' => $contacts === [] ? null : implode(', ', $contacts),
            'phone' => $first('phone'),
            'email' => $first('email'),
            'address' => $this->filled([$first('line1'), $first('line2'), $first('line3'), trim($first('postcode') . ' ' . $first('city')), $first('subdivision')]),
            'city' => $first('city'),
            'countryCode' => $country,
            'country' => $country === null ? null : $this->codeName(CodeList::Country, $country),
            'vatId' => $first('vat_id'),
            'registration' => $schemed('registration'),
            'identifiers' => $schemed('identifier'),
            'electronic' => $schemed('electronic_address'),
            'extras' => $this->extraRows($other),
        ];
    }

    /**
     * Whether the addressee lives in another country than the issuer: then the country is written in
     * the addresses of the letter.
     *
     * @param array<string, mixed>|null $party
     * @param array<string, mixed>|null $issuer
     */
    public function isForeign(?array $party, ?array $issuer): bool
    {
        $country = $party['countryCode'] ?? null;

        return $country !== null && strtoupper($country) !== strtoupper((string) ($issuer['countryCode'] ?? ''));
    }

    /**
     * @param array<string, mixed>|null $issuer
     * @return array{name: string|null, tradingName: string|null, address: string|null}|null
     */
    public function letterhead(?array $issuer, bool $foreign): ?array
    {
        if ($issuer === null) {
            return null;
        }

        $address = $this->postal($issuer, $foreign);

        return [
            'name' => $issuer['name'],
            'tradingName' => $issuer['tradingName'],
            'address' => $address === [] ? null : implode(' · ', $address),
        ];
    }

    /**
     * The return address in small print above the addressee.
     *
     * @param array<string, mixed>|null $issuer
     */
    public function returnLine(?array $issuer, bool $foreign): ?string
    {
        if ($issuer === null) {
            return null;
        }

        $parts = $this->filled([$issuer['name'], ...$this->postal($issuer, $foreign)]);

        return $parts === [] ? null : implode(' · ', $parts);
    }

    /**
     * The address of the addressee: name, contact point, street lines, postcode and city, the country
     * in capitals where the letter goes abroad.
     *
     * @param array<string, mixed>|null $party
     * @return list<string>|null
     */
    public function recipient(?array $party, bool $foreign): ?array
    {
        if ($party === null) {
            return null;
        }

        $lines = $this->filled([$party['name'], $party['tradingName'], $party['contact'], ...$party['address'],
            $foreign && $party['country'] !== null ? mb_strtoupper($party['country']) : null]);

        return $lines === [] ? null : $lines;
    }

    /**
     * The information block beside the addressee, below the title: date and number of the invoice,
     * its key references, customer and supplier number, VAT identifier and tax number of the
     * addressee. Everything else about the parties stands below the address.
     *
     * @param list<array{0: string, 1: string}> $references
     * @param array<string, mixed>|null $seller
     * @param array<string, mixed>|null $buyer
     * @param array<string, mixed>|null $addressee
     * @return list<array{0: string, 1: string}>
     */
    public function info(?string $date, ?string $number, array $references, ?array $seller, ?array $buyer, ?array $addressee): array
    {
        $rows = [];
        $this->add($rows, 'info.date', $date);
        $this->add($rows, 'info.number', $number);
        array_push($rows, ...$references);
        // Identifiers without a scheme are the numbers the partners give each other.
        $this->add($rows, 'info.customer_number', $this->plainIdentifiers($buyer));
        $this->add($rows, 'info.supplier_number', $this->plainIdentifiers($seller));
        $this->add($rows, 'info.vat_id', $addressee['vatId'] ?? null);
        $this->add($rows, 'info.tax_number', $addressee['taxNumber'] ?? null);

        return $rows;
    }

    /**
     * The parties as rows below the references: the recipient of the delivery, the buyer where an invoicee
     * receives the invoice (the invoicee of a self-billed invoice), what the address does not show of the
     * addressee, the contact person of the issuer, the payee and the tax representative.
     *
     * @param array<string, mixed>|null $buyer
     * @param array<string, mixed>|null $invoicee
     * @param array<string, mixed>|null $addressee
     * @param array<string, mixed>|null $issuer
     * @return list<array{title: string, name: string|null, text: string|null, details: string|null}>
     */
    public function rows(?array $buyer, ?array $invoicee, ?array $addressee, ?array $issuer, bool $selfBilled): array
    {
        $blocks = array_values(array_filter([
            $this->delivery(),
            match (true) {
                $invoicee !== null && ! $selfBilled && $buyer !== null => $this->side('section.buyer', $buyer),
                $invoicee !== null && $selfBilled => $this->side('section.invoicee', $invoicee),
                default => null,
            },
            $addressee === null ? null : $this->side(self::ROLE_TITLES[$addressee['role']], $addressee, true),
            $this->contact($issuer),
            $this->payee(),
            $this->taxRepresentative(),
        ]));

        return array_values(array_filter(array_map(fn(array $block): ?array => $this->partyRow($block), $blocks)));
    }

    /**
     * @param array<string, mixed> $details
     * @return array<string, mixed>
     */
    private function partyData(string $role, mixed $name, mixed $tradingName, mixed $address, string $prefix, array $details): array
    {
        $name = $this->value($name);
        $tradingName = $this->value($tradingName);
        $group = $this->groups($address)[0] ?? [];
        $country = $this->value($group[$prefix . '_country_code'] ?? null);

        return [
            ...self::PARTY,
            ...$details,
            'role' => $role,
            'name' => $name,
            'tradingName' => $this->differentName($tradingName, $name),
            // Senders often give the name of the company as contact point - it would stand twice.
            'contact' => $this->differentName($this->differentName($details['contact'] ?? null, $name), $tradingName),
            'address' => $this->address($address, $prefix, false),
            'city' => $this->value($group[$prefix . '_city'] ?? null),
            'countryCode' => $country,
            'country' => $country === null ? null : $this->codeName(CodeList::Country, $country),
        ];
    }

    /**
     * Takes the unmapped values of the seller or buyer: further identifiers of the party (CII allows
     * several, EN 16931 one) become identifiers, every other value goes to the further information.
     *
     * @return list<array{value: string, scheme: string|null}>
     */
    private function claimIdentifiers(string $anchor, string $id): array
    {
        $identifiers = [];
        foreach ($this->extras->take($anchor) as $value) {
            $text = $this->clean($value->value);
            if ($value->id === $id && $text !== '') {
                $identifiers[] = ['value' => $text, 'scheme' => $this->nonEmpty($value->attributes['schemeID'] ?? null)];
            } else {
                $this->extras->addToRest($anchor, $value);
            }
        }

        return $identifiers;
    }

    /**
     * Address lines of the issuer, the country only where the letter goes abroad.
     *
     * @param array<string, mixed> $party
     * @return list<string>
     */
    private function postal(array $party, bool $withCountry): array
    {
        return $this->filled([...$party['address'], $withCountry ? $party['country'] : null]);
    }

    /**
     * Identifiers of a party without a scheme, joined.
     *
     * @param array<string, mixed>|null $party
     */
    private function plainIdentifiers(?array $party): ?string
    {
        $values = [];
        foreach ($party['identifiers'] ?? [] as $identifier) {
            if ($identifier['scheme'] === null) {
                $values[] = $identifier['value'];
            }
        }

        return $values === [] ? null : implode(', ', array_unique($values));
    }

    /**
     * A party beside issuer and addressee: the buyer where an invoicee receives the invoice, the
     * invoicee of a self-billed invoice. As $details the addressee itself: only what stands neither
     * in its address nor in the information block - identifiers, electronic address, telephone and
     * email of its contact, legal information. Customer and supplier numbers stand in the
     * information block.
     *
     * @param array<string, mixed> $party
     * @return array<string, mixed>|null
     */
    private function side(string $titleKey, array $party, bool $details = false): ?array
    {
        $rows = [];
        if (! $details) {
            $this->add($rows, 'party.vat_id', $party['vatId']);
            $this->add($rows, 'party.tax_number', $party['taxNumber']);
        }
        foreach ($party['registration'] as $registration) {
            $this->addSchemed($rows, 'party.registration', $registration['value'], $registration['scheme'], CodeList::IdentifierScheme, CodeList::ElectronicAddressScheme);
        }
        foreach ($party['identifiers'] as $identifier) {
            if ($identifier['scheme'] !== null || $party['role'] === 'invoicee') {
                $this->addSchemed($rows, 'party.identifier', $identifier['value'], $identifier['scheme'], CodeList::IdentifierScheme, CodeList::ElectronicAddressScheme);
            }
        }
        foreach ($party['electronic'] as $address) {
            $this->addElectronicAddress($rows, $address);
        }
        if (! $details) {
            $this->addContact($rows, $party['contact'], $party['phone'], $party['email']);
        }
        $this->add($rows, 'party.legal', $party['legal']);

        // The contact point of the addressee is part of its address; its telephone and email address
        // stand as lines of their own, like the contact person of the issuer.
        $lines = $details ? $this->filled([$party['phone'], $party['email']]) : $this->filled([...$party['address'], $party['country']]);
        if ($details && $lines === [] && $rows === [] && $party['extras'] === []) {
            return null;
        }

        return [
            'title' => $this->texts->get($titleKey),
            'name' => $details ? null : $party['name'],
            'tradingName' => $details ? null : $party['tradingName'],
            'address' => $lines,
            'rows' => $this->unique($rows),
            'extras' => $party['extras'],
        ];
    }

    /**
     * The contact person of the issuer: name, telephone and email address.
     *
     * @param array<string, mixed>|null $issuer
     * @return array<string, mixed>|null
     */
    private function contact(?array $issuer): ?array
    {
        if ($issuer === null || ($issuer['contact'] === null && $issuer['phone'] === null && $issuer['email'] === null)) {
            return null;
        }

        return [
            'title' => $this->texts->get('section.contact'),
            'name' => $issuer['contact'],
            'tradingName' => null,
            'address' => $this->filled([$issuer['phone'], $issuer['email']]),
            'rows' => [],
            'extras' => [],
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    private function payee(): ?array
    {
        $payee = $this->groups($this->data['PAYEE'] ?? null)[0] ?? null;
        if ($payee === null) {
            return null;
        }

        $rows = [];
        $this->addIdentifier($rows, 'party.identifier', $payee['Payee_identifier'] ?? null);
        $this->addIdentifier($rows, 'party.registration', $payee['Payee_legal_registration_identifier'] ?? null);

        return $this->block('section.payee', $payee['Payee_name'] ?? null, [], $rows, 'payee');
    }

    /**
     * @return array<string, mixed>|null
     */
    private function taxRepresentative(): ?array
    {
        $representative = $this->groups($this->data['SELLER_TAX_REPRESENTATIVE_PARTY'] ?? null)[0] ?? null;
        if ($representative === null) {
            return null;
        }

        $rows = [];
        $this->add($rows, 'party.vat_id', $this->value($representative['Seller_tax_representative_VAT_identifier'] ?? null));

        return $this->block(
            'section.tax_representative',
            $representative['Seller_tax_representative_name'] ?? null,
            $this->address($representative['SELLER_TAX_REPRESENTATIVE_POSTAL_ADDRESS'] ?? null, 'Tax_representative'),
            $rows,
            'tax_representative',
        );
    }

    /**
     * Recipient and place of the delivery; the delivery date is one of the references of the invoice.
     *
     * @return array<string, mixed>|null
     */
    private function delivery(): ?array
    {
        $delivery = $this->groups($this->data['DELIVERY_INFORMATION'] ?? null)[0] ?? null;
        if ($delivery === null) {
            return null;
        }

        $rows = [];
        $this->addIdentifier($rows, 'party.location', $delivery['Deliver_to_location_identifier'] ?? null);
        $address = $this->address($delivery['DELIVER_TO_ADDRESS'] ?? null, 'Deliver_to');
        $name = $delivery['Deliver_to_party_name'] ?? null;

        if ($rows === [] && $address === [] && $this->value($name) === null && $this->extras->attached('delivery') === []) {
            return null;
        }

        return $this->block('section.delivery', $name, $address, $rows, 'delivery');
    }

    /**
     * @param list<string> $address
     * @param list<array{0: string, 1: string}> $rows
     * @return array<string, mixed>
     */
    private function block(string $titleKey, mixed $name, array $address, array $rows, string $anchor): array
    {
        // A party without an address in EN 16931 (the payee) may bring one in the extension.
        $extras = $this->extras->attached($anchor);
        $skip = $this->extras->depth($anchor);
        if ($address === [] && $extras !== []) {
            [$address, $extras] = $this->extensionAddress($extras, $skip);
        }

        return [
            'title' => $this->texts->get($titleKey),
            'name' => $this->value($name),
            'tradingName' => null,
            'address' => $address,
            'rows' => $this->unique($rows),
            'extras' => $this->extraRows($extras, $skip),
        ];
    }

    /**
     * The postal address among the extension values of a party (its PostalTradeAddress) as address
     * lines, and the values that remain.
     *
     * @param list<UnmappedValue> $values
     * @return array{0: list<string>, 1: list<UnmappedValue>}
     */
    private function extensionAddress(array $values, int $skip): array
    {
        $fields = [];
        $rest = [];
        foreach ($values as $value) {
            $names = array_slice($value->names, $skip);
            $role = count($names) === 2 && $names[0] === 'PostalTradeAddress' ? (self::ADDRESS_ELEMENTS[$names[1]] ?? null) : null;
            if ($role !== null && ! isset($fields[$role])) {
                $fields[$role] = $this->clean($value->value);
            } else {
                $rest[] = $value;
            }
        }

        if ($fields === []) {
            return [[], $values];
        }

        $country = $fields['country'] ?? null;

        return [$this->filled([$fields['line1'] ?? null, $fields['line2'] ?? null, $fields['line3'] ?? null,
            trim(($fields['postcode'] ?? '') . ' ' . ($fields['city'] ?? '')), $fields['subdivision'] ?? null,
            $country === null ? null : $this->codeName(CodeList::Country, $country)]), $rest];
    }

    /**
     * A party as one row below the references: its role, name and address in one line, identifiers
     * and further details below it - or in that line where name and address are missing.
     *
     * @param array<string, mixed> $block
     * @return array{title: string, name: string|null, text: string|null, details: string|null}|null
     */
    private function partyRow(array $block): ?array
    {
        $text = implode(', ', $this->filled([$block['tradingName'], ...$block['address']]));
        $details = [];
        foreach ([...$block['rows'], ...$block['extras']] as [$label, $value]) {
            $details[] = $label . ' ' . str_replace("\n", ', ', $value);
        }
        $details = implode(' · ', $details);

        if ($block['name'] === null && $text === '') {
            return $details === '' ? null : ['title' => $block['title'], 'name' => null, 'text' => $details, 'details' => null];
        }

        return ['title' => $block['title'], 'name' => $block['name'], 'text' => $text === '' ? null : $text, 'details' => $details === '' ? null : $details];
    }

    /**
     * Address lines: street lines, postcode and city, subdivision and - unless left out - the country.
     *
     * @return list<string>
     */
    private function address(mixed $group, string $prefix, bool $withCountry = true): array
    {
        $address = $this->groups($group)[0] ?? null;
        if ($address === null) {
            return [];
        }

        $lines = [];
        foreach (['_address_line_1', '_address_line_2', '_address_line_3'] as $field) {
            $lines[] = $this->value($address[$prefix . $field] ?? null);
        }
        $lines[] = trim($this->value($address[$prefix . '_post_code'] ?? null) . ' ' . $this->value($address[$prefix . '_city'] ?? null));
        $lines[] = $this->value($address[$prefix . '_country_subdivision'] ?? null);
        $country = $this->value($address[$prefix . '_country_code'] ?? null);
        if ($withCountry && $country !== null) {
            $lines[] = $this->codeName(CodeList::Country, $country);
        }

        return $this->filled($lines);
    }

    /**
     * The contact as one row: name, telephone and email address below each other.
     *
     * @param list<array{0: string, 1: string}> $rows
     */
    private function addContact(array &$rows, ?string $point, ?string $phone, ?string $email): void
    {
        $this->add($rows, 'party.contact', $this->joinLines([$point, $phone, $email]));
    }
}
