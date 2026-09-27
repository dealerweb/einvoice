<?php

/**
 * The invoice model with readable names: every field of EN 16931, of the XRechnung extension and of ZUGFeRD /
 * Factur-X EXTENDED by the path a user gives it ($invoice->buyer->address->city) and the id of the Factur-X field
 * list it stands for. The classes in src/Model and the documentation MODEL.md are generated from it; at run time
 * Model\Mapping reads it (writing and reading).
 *
 * Notation: a key is a property; "name[]" is a list; a value is the field id, a list of ids where one property covers
 * several fields, or an array for an object. In an object, "@type" names its class, "@group" the group id(s) of the
 * field list, "@note" a remark for the documentation in English and German (['en' => ..., 'de' => ...]). The
 * properties of EN 16931 (and the XRechnung extension) come first, those EXTENDED adds after them.
 *
 * One property, several ids: the fields of one value in the syntaxes (the third party payment of the XRechnung
 * extension, BT-DEX-002 in UBL, is BT-179 in CII), or the identifier and the global identifier of a party (an ID and
 * a GlobalID in CII: the one with the scheme given, the other without).
 *
 * Where EN 16931 has a value once and EXTENDED repeats its element (the contact of a party, the payment terms, the
 * VAT of a line), the property holds the first and a list "additional..." the others.
 *
 * @internal
 */

declare(strict_types=1);

/**
 * A postal address (EN 16931: BG-5, BG-8, BG-12, BG-15) - the ids in the order of the field list: post code, line 1,
 * line 2, line 3, city, country, subdivision.
 *
 * @param list<string> $ids
 * @return array<string, mixed>
 */
$address = static fn(string $group, array $ids): array => [
    '@type' => 'Address',
    '@group' => $group,
    'line1' => $ids[1],
    'line2' => $ids[2],
    'line3' => $ids[3],
    'postcode' => $ids[0],
    'city' => $ids[4],
    'subdivision' => $ids[6],
    'country' => $ids[5],
];

/** Seven consecutive ids of the EXTENDED field list, in the order of $address. */
$addressX = static fn(string $group, int $first): array => $address($group, array_map(static fn(int $n): string => "BT-X-$n", range($first, $first + 6)));

/**
 * A contact of a party (EN 16931: BG-6, BG-9) - the ids: name, department, type code (EXTENDED), phone, fax (EXTENDED),
 * email.
 *
 * @param list<string> $ids
 * @return array<string, mixed>
 */
$contact = static fn(string $group, array $ids): array => [
    '@type' => 'Contact',
    '@group' => $group,
    'name' => $ids[0],
    'department' => $ids[1],
    'phone' => $ids[3],
    'fax' => $ids[4],
    'email' => $ids[5],
    'typeCode' => $ids[2],
];

/**
 * An identifier with its scheme: an ICD, the scheme of an electronic address, the type of an object identifier. The
 * value may name two fields, the identifier and the global identifier of a party (ID and GlobalID in CII): with a
 * scheme the one the scheme belongs to, without the other.
 *
 * @param string|list<string> $value
 * @return array<string, mixed>
 */
$identifier = static fn(string|array $value, ?string $scheme = null): array => array_filter([
    '@type' => 'Identifier',
    'value' => $value,
    'scheme' => $scheme,
], static fn(mixed $item): bool => $item !== null);

/**
 * A reference to another document (an order, a contract, a despatch advice): its number, the date the document was
 * issued (EXTENDED), the line of it referred to, a type code, the scheme of the number.
 *
 * @param string|list<string>|null $group
 * @return array<string, mixed>
 */
$reference = static fn(string|array|null $group, string $number, ?string $date = null, ?string $lineId = null, ?string $typeCode = null, ?string $scheme = null): array => array_filter([
    '@type' => 'DocumentReference',
    '@group' => $group,
    'number' => $number,
    'date' => $date,
    'lineId' => $lineId,
    'typeCode' => $typeCode,
    'scheme' => $scheme,
], static fn(mixed $item): bool => $item !== null);

/**
 * A party: the seller and the buyer of EN 16931 and the parties EXTENDED adds, all of the same shape. A contact comes
 * with the list of the further contacts EXTENDED allows.
 *
 * @param string|list<string> $group
 * @param array<string, mixed> $fields the ids by property; identifiers as [value id(s), scheme id]
 * @return array<string, mixed>
 */
$party = static function (string|array $group, array $fields) use ($identifier): array {
    $result = ['@type' => 'Party', '@group' => $group];
    foreach ($fields as $property => $ids) {
        $result[$property] = match ($property) {
            'identifiers[]', 'legalRegistrationId', 'electronicAddress' => $identifier($ids[0], $ids[1] ?? null),
            default => $ids,
        };
        if ($property === 'contact') {
            if (in_array($ids['@group'] ?? null, ['BG-6', 'BG-9'], true)) {
                $result[$property] = ['@note' => [
                    'en' => 'The contact point is a person (`name`) or a department (`department`), not both. UBL has one element for the two: a department is written as the name and read back as the name, a department next to a name is refused. In CII the rules refuse the two together, even spread over several contacts (CII-SR-465 for the seller, CII-SR-466 for the buyer; Peppol only warns).',
                    'de' => 'Die Kontaktstelle ist eine Person (`name`) oder eine Abteilung (`department`), nicht beides. UBL hat für beides ein Element: eine Abteilung wird als Name geschrieben und als Name zurückgelesen, eine Abteilung neben einem Namen wird abgelehnt. In CII lehnen die Regeln beides zusammen ab, auch verteilt auf mehrere Kontakte (CII-SR-465 beim Verkäufer, CII-SR-466 beim Käufer; Peppol warnt nur).',
                ]] + $ids;
            }
            $result['additionalContacts[]'] = ['@note' => ['en' => 'More contacts (EXTENDED) - the first is `contact`.', 'de' => 'Weitere Kontakte (EXTENDED) - der erste ist `contact`.']] + $ids;
        }
    }

    return $result;
};

/**
 * An allowance or a charge, of the document, of a line or of a price.
 *
 * @param array<string, string> $ids
 * @return array<string, mixed>
 */
$allowanceCharge = static fn(string $group, array $ids): array => ['@type' => 'AllowanceCharge', '@group' => $group] + $ids;

/**
 * The terms of an early payment discount or a late payment penalty (EXTENDED) - the ids: reference date, period, its
 * unit, base amount, percentage, amount.
 *
 * @param list<string> $ids
 * @return array<string, mixed>
 */
$paymentCondition = static fn(string $group, array $ids): array => [
    '@type' => 'PaymentCondition',
    '@group' => $group,
    'referenceDate' => $ids[0],
    'period' => $ids[1],
    'periodUnit' => $ids[2],
    'baseAmount' => $ids[3],
    'percentage' => $ids[4],
    'amount' => $ids[5],
];

/**
 * A tax (EXTENDED: a further VAT of a line, the tax included in a price, the VAT of an advance payment or of a
 * logistics charge) - the type code is fixed to VAT, not a field.
 *
 * @param array<string, string> $ids
 * @return array<string, mixed>
 */
$tax = static fn(string $group, array $ids): array => ['@type' => 'Tax', '@group' => $group] + $ids;

/**
 * An EXTENDED party of the usual shape, by the numbers of its fields BT-X-n: id and global id (its scheme n-0), name,
 * role, registration (its scheme n-0), trading name, legal address, contact, address, electronic address (its scheme
 * n-0), VAT identifier.
 *
 * @param list<int> $contactIds name, department, type code, phone, fax, email
 * @return array<string, mixed>
 */
$partyX = static fn(string $group, int $id, int $globalId, int $name, int $role, int $registration, int $tradingName, string $legalAddressGroup, int $legalAddress, string $contactGroup, array $contactIds, string $addressGroup, int $address, int $electronicAddress, int $vatId): array => $party($group, [
    'name' => "BT-X-$name",
    'tradingName' => "BT-X-$tradingName",
    'identifiers[]' => [["BT-X-$id", "BT-X-$globalId"], "BT-X-$globalId-0"],
    'legalRegistrationId' => ["BT-X-$registration", "BT-X-$registration-0"],
    'vatId' => "BT-X-$vatId",
    'electronicAddress' => ["BT-X-$electronicAddress", "BT-X-$electronicAddress-0"],
    'address' => $addressX($addressGroup, $address),
    'contact' => $contact($contactGroup, array_map(static fn(int $n): string => "BT-X-$n", $contactIds)),
    'roleCode' => "BT-X-$role",
    'legalAddress' => $addressX($legalAddressGroup, $legalAddress),
]);

// ---------------------------------------------------------------- the parties of the document

$seller = $party('BG-4', [
    'name' => 'BT-27',
    'tradingName' => 'BT-28',
    'identifiers[]' => ['BT-29', 'BT-29-1'],
    'legalRegistrationId' => ['BT-30', 'BT-30-1'],
    'vatId' => 'BT-31',
    'taxNumber' => 'BT-32',
    'legalInformation' => 'BT-33',
    'electronicAddress' => ['BT-34', 'BT-34-1'],
    'address' => $address('BG-5', ['BT-38', 'BT-35', 'BT-36', 'BT-162', 'BT-37', 'BT-40', 'BT-39']),
    'contact' => $contact('BG-6', ['BT-41', 'BT-41-0', 'BT-X-317', 'BT-42', 'BT-X-107', 'BT-43']),
    'roleCode' => 'BT-X-543',
    'legalAddress' => $addressX('BG-X-14', 100),
]);

$buyer = $party('BG-7', [
    'name' => 'BT-44',
    'tradingName' => 'BT-45',
    'identifiers[]' => ['BT-46', 'BT-46-1'],
    'legalRegistrationId' => ['BT-47', 'BT-47-1'],
    'vatId' => 'BT-48',
    'legalInformation' => 'BT-X-334',
    'electronicAddress' => ['BT-49', 'BT-49-1'],
    'address' => $address('BG-8', ['BT-53', 'BT-50', 'BT-51', 'BT-163', 'BT-52', 'BT-55', 'BT-54']),
    'contact' => $contact('BG-9', ['BT-56', 'BT-56-0', 'BT-X-318', 'BT-57', 'BT-X-115', 'BT-58']),
    'roleCode' => 'BT-X-544',
    'legalAddress' => $addressX('BG-X-15', 108),
]);

$payee = $party('BG-10', [
    'name' => 'BT-59',
    'tradingName' => 'BT-X-243',
    'identifiers[]' => ['BT-60', 'BT-60-1'],
    'legalRegistrationId' => ['BT-61', 'BT-61-1'],
    'vatId' => 'BT-X-257',
    'electronicAddress' => ['BT-X-256', 'BT-X-256-0'],
    'address' => $addressX('BG-X-40', 249),
    'contact' => $contact('BG-X-39', ['BT-X-244', 'BT-X-245', 'BT-X-326', 'BT-X-246', 'BT-X-247', 'BT-X-248']),
    'roleCode' => 'BT-X-468',
    'legalAddress' => $addressX('BG-X-72', 469),
]);

$sellerTaxRepresentative = $party('BG-11', [
    'name' => 'BT-62',
    'tradingName' => 'BT-X-119',
    'identifiers[]' => [['BT-X-116', 'BT-X-117'], 'BT-X-117-1'],
    'legalRegistrationId' => ['BT-X-118', 'BT-X-118-0'],
    'vatId' => 'BT-63',
    'electronicAddress' => ['BT-X-125', 'BT-X-125-0'],
    'address' => $address('BG-12', ['BT-67', 'BT-64', 'BT-65', 'BT-164', 'BT-66', 'BT-69', 'BT-68']),
    'contact' => $contact('BG-X-17', ['BT-X-120', 'BT-X-121', 'BT-X-319', 'BT-X-122', 'BT-X-123', 'BT-X-124']),
    'roleCode' => 'BT-X-547',
    'legalAddress' => $addressX('BG-X-59', 389),
]);

/**
 * The deliver to party: its identifier is the location identifier of EN 16931 (BT-71).
 */
$shipTo = $party('BG-13', [
    'name' => 'BT-70',
    'tradingName' => 'BT-X-154',
    'identifiers[]' => ['BT-71', 'BT-71-1'],
    'legalRegistrationId' => ['BT-X-153', 'BT-X-153-0'],
    'vatId' => 'BT-X-161',
    'electronicAddress' => ['BT-X-160', 'BT-X-160-0'],
    'address' => $address('BG-15', ['BT-78', 'BT-75', 'BT-76', 'BT-165', 'BT-77', 'BT-80', 'BT-79']),
    'contact' => $contact('BG-X-26', ['BT-X-155', 'BT-X-156', 'BT-X-321', 'BT-X-157', 'BT-X-158', 'BT-X-159']),
    'roleCode' => 'BT-X-550',
    'legalAddress' => $addressX('BG-X-67', 433),
]);

/**
 * The payee of payment terms (EXTENDED: an instalment paid to another party than the payee of the invoice).
 */
$paymentTermsPayee = $partyX('BG-X-77', 506, 507, 504, 511, 508, 505, 'BG-X-80', 525, 'BG-X-78', [512, 513, 514, 515, 516, 517], 'BG-X-79', 518, 510, 509);

// ---------------------------------------------------------------- the parties of a line (EXTENDED)

$lineSeller = $party('BG-X-90', [
    'name' => 'BT-X-569',
    'tradingName' => 'BT-X-573',
    'identifiers[]' => [['BT-X-567', 'BT-X-568'], 'BT-X-568-0'],
    'legalRegistrationId' => ['BT-X-572', 'BT-X-572-0'],
    'vatId' => 'BT-X-587',
    'taxNumber' => 'BT-X-588',
    'legalInformation' => 'BT-X-571',
    'electronicAddress' => ['BT-X-586', 'BT-X-586-0'],
    'address' => $addressX('BG-X-92', 579),
    'contact' => $contact('BG-X-91', ['BT-X-574', 'BT-X-574-1', 'BT-X-575', 'BT-X-576', 'BT-X-577', 'BT-X-578']),
    'roleCode' => 'BT-X-570',
]);

$lineShipTo = $party('BG-X-7', [
    'name' => 'BT-X-50',
    'tradingName' => 'BT-X-52',
    'identifiers[]' => [['BT-186-00', 'BT-186'], 'BT-186-1'],
    'legalRegistrationId' => ['BT-X-51', 'BT-X-51-0'],
    'vatId' => 'BT-X-66',
    'electronicAddress' => ['BT-X-65', 'BT-X-65-0'],
    'address' => $addressX('BG-X-9', 58),
    'contact' => $contact('BG-X-8', ['BT-X-54', 'BT-X-54-1', 'BT-X-315', 'BT-X-55', 'BT-X-56', 'BT-X-57']),
    'roleCode' => 'BT-X-541',
]);

$lineUltimateShipTo = $party('BG-X-10', [
    'name' => 'BT-X-69',
    'tradingName' => 'BT-X-71',
    'identifiers[]' => [['BT-X-67', 'BT-X-68'], 'BT-X-68-0'],
    'legalRegistrationId' => ['BT-X-70', 'BT-X-70-0'],
    'vatId' => 'BT-X-84',
    'electronicAddress' => ['BT-X-83', 'BT-X-83-0'],
    'address' => $addressX('BG-X-12', 76),
    'contact' => $contact('BG-X-11', ['BT-X-72', 'BT-X-72-1', 'BT-X-316', 'BT-X-73', 'BT-X-74', 'BT-X-75']),
    'roleCode' => 'BT-X-542',
]);

$manufacturer = $party('BG-X-93', [
    'name' => 'BT-X-595',
    'tradingName' => 'BT-X-599',
    'identifiers[]' => [['BT-X-593', 'BT-X-594'], 'BT-X-594-0'],
    'legalRegistrationId' => ['BT-X-598', 'BT-X-598-0'],
    'vatId' => 'BT-X-614',
    'taxNumber' => 'BT-X-615',
    'legalInformation' => 'BT-X-597',
    'electronicAddress' => ['BT-X-613', 'BT-X-613-0'],
    'address' => $addressX('BG-X-95', 606),
    'contact' => $contact('BG-X-94', ['BT-X-600', 'BT-X-601', 'BT-X-602', 'BT-X-603', 'BT-X-604', 'BT-X-605']),
    'roleCode' => 'BT-X-596',
]);

// ---------------------------------------------------------------- a line (BG-25), also a sub line of XRechnung (BG-DEX-01)

$line = [
    '@type' => 'Line',
    '@group' => 'BG-25',
    // EN 16931 and the XRechnung extension
    'id' => 'BT-126',
    'note' => 'BT-127',
    'quantity' => 'BT-129',
    'unit' => 'BT-130',
    'netAmount' => 'BT-131',
    'name' => 'BT-153',
    'description' => 'BT-154',
    'netPrice' => 'BT-146',
    'grossPrice' => 'BT-148',
    'priceBaseQuantity' => 'BT-149',
    'priceBaseUnit' => 'BT-150',
    'priceDiscount' => 'BT-147',
    'vatCategory' => 'BT-151',
    'vatRate' => 'BT-152',
    'sellerItemId' => 'BT-155',
    'buyerItemId' => 'BT-156',
    'standardItemId' => $identifier('BT-157', 'BT-157-1'),
    'classifications[]' => ['@type' => 'Classification', '@group' => 'BT-158-00', 'value' => 'BT-158', 'scheme' => 'BT-158-1', 'schemeVersion' => 'BT-158-2', 'name' => 'BT-X-13'],
    'originCountry' => 'BT-159',
    'attributes[]' => ['@type' => 'ItemAttribute', '@group' => 'BG-32', 'name' => 'BT-160', 'value' => 'BT-161', 'numericValue' => 'BT-X-12', 'unit' => 'BT-X-12-0', 'typeCode' => 'BT-X-11'],
    'objectIdentifier' => $identifier('BT-128', 'BT-128-1'),
    'buyerAccountingReference' => 'BT-133',
    'period' => ['@type' => 'Period', '@group' => 'BG-26', 'startDate' => 'BT-134', 'endDate' => 'BT-135'],
    'allowances[]' => $allowanceCharge('BG-27', ['amount' => 'BT-136', 'baseAmount' => 'BT-137', 'percentage' => 'BT-138', 'reason' => 'BT-139', 'reasonCode' => 'BT-140']),
    'charges[]' => $allowanceCharge('BG-28', ['amount' => 'BT-141', 'baseAmount' => 'BT-142', 'percentage' => 'BT-143', 'reason' => 'BT-144', 'reasonCode' => 'BT-145', 'taxTypeCode' => 'BT-193']),
    'purchaseOrder' => $reference(null, 'BT-X-21', 'BT-X-22', 'BT-132'),
    'subLines[]' => ['@type' => 'Line', '@group' => 'BG-DEX-01', '@note' => ['en' => 'XRechnung extension (UBL): sub lines, built like a line.', 'de' => 'XRechnung-Erweiterung (UBL): Unterpositionen, aufgebaut wie eine Position.']],
    // ZUGFeRD / Factur-X EXTENDED
    'noteSubjectCode' => 'BT-X-10',
    'noteContentCode' => 'BT-X-9',
    'additionalNotes[]' => ['@type' => 'Note', '@group' => 'BT-127-00', '@note' => ['en' => 'More notes - the first are `note`, `noteSubjectCode` and `noteContentCode`.', 'de' => 'Weitere Notizen - die erste sind `note`, `noteSubjectCode` und `noteContentCode`.'], 'text' => 'BT-127', 'subjectCode' => 'BT-X-10', 'contentCode' => 'BT-X-9'],
    'parentLineId' => 'BT-X-304',
    'typeCode' => 'BT-X-7',
    'subtypeCode' => 'BT-X-8',
    'grossPriceBaseQuantity' => 'BT-149-1',
    'grossPriceBaseUnit' => 'BT-150-1',
    'priceDiscountPercentage' => 'BT-X-34',
    'priceDiscountBaseAmount' => 'BT-X-35',
    'priceDiscountReason' => 'BT-X-36',
    'priceDiscountReasonCode' => 'BT-X-313',
    'additionalPriceDiscounts[]' => $allowanceCharge('BT-147-00', ['@note' => ['en' => 'More discounts on the gross price - the first are `priceDiscount` and `priceDiscount...`.', 'de' => 'Weitere Nachlässe auf den Bruttopreis - der erste sind `priceDiscount` und `priceDiscount...`.'], 'amount' => 'BT-147', 'baseAmount' => 'BT-X-35', 'percentage' => 'BT-X-34', 'reason' => 'BT-X-36', 'reasonCode' => 'BT-X-313']),
    'priceCharges[]' => $allowanceCharge('BT-X-302-00', ['amount' => 'BT-X-302', 'baseAmount' => 'BT-X-301', 'percentage' => 'BT-X-300', 'reason' => 'BT-X-303', 'reasonCode' => 'BT-X-314', 'taxTypeCode' => 'BT-X-616']),
    'vatAmount' => 'BT-X-95',
    'vatExemptionReason' => 'BT-X-96',
    'vatExemptionReasonCode' => 'BT-X-97',
    'taxPointDateCode' => 'BT-X-589',
    'additionalTaxes[]' => $tax('BG-30', ['@note' => ['en' => 'More VAT of the line - the first are `vatCategory`, `vatRate` and `vat...`.', 'de' => 'Weitere Umsatzsteuer der Position - die erste sind `vatCategory`, `vatRate` und `vat...`.'], 'category' => 'BT-151', 'rate' => 'BT-152', 'amount' => 'BT-X-95', 'exemptionReason' => 'BT-X-96', 'exemptionReasonCode' => 'BT-X-97', 'taxPointDateCode' => 'BT-X-589']),
    'chargeFreeQuantity' => 'BT-X-46',
    'chargeFreeQuantityUnit' => 'BT-X-46-0',
    'packageQuantity' => 'BT-X-47',
    'packageQuantityUnit' => 'BT-X-47-0',
    'quantityPerPackage' => 'BT-X-561',
    'quantityPerPackageUnit' => 'BT-X-561-0',
    'chargeTotal' => 'BT-X-327',
    'allowanceTotal' => 'BT-X-328',
    'allowanceChargeTotal' => 'BT-X-98',
    'taxTotal' => 'BT-X-329',
    'taxTotalInVatCurrency' => 'BT-X-590',
    'grossAmount' => 'BT-X-330',
    'itemId' => 'BT-X-305',
    'industryItemId' => 'BT-X-532',
    'modelId' => 'BT-X-533',
    'batchIds[]' => 'BT-X-534',
    'brand' => 'BT-X-535',
    'model' => 'BT-X-536',
    'instances[]' => ['@type' => 'ItemInstance', '@group' => 'BG-X-84', 'batchId' => 'BT-X-306', 'serialId' => 'BT-X-307'],
    'manufacturer' => $manufacturer,
    'includedItems[]' => [
        '@type' => 'IncludedItem',
        '@group' => 'BG-X-1',
        'id' => 'BT-X-308',
        'identifiers[]' => $identifier('BT-X-15', 'BT-X-15-1'),
        'sellerItemId' => 'BT-X-16',
        'buyerItemId' => 'BT-X-17',
        'industryItemId' => 'BT-X-309',
        'name' => 'BT-X-18',
        'description' => 'BT-X-19',
        'quantity' => 'BT-X-20',
        'unit' => 'BT-X-20-1',
    ],
    'additionalObjectIdentifiers[]' => ['@note' => ['en' => 'More object identifiers - the first is `objectIdentifier`.', 'de' => 'Weitere Objektkennungen - die erste ist `objectIdentifier`.']] + $identifier('BT-128', 'BT-128-1'),
    'buyerAccountingReferenceTypeCode' => 'BT-X-99',
    'additionalBuyerAccountingReferences[]' => ['@type' => 'AccountingReference', '@group' => 'BT-133-00', '@note' => ['en' => 'More buyer accounting references - the first are `buyerAccountingReference` and `buyerAccountingReferenceTypeCode`.', 'de' => 'Weitere Buchungsreferenzen - die erste sind `buyerAccountingReference` und `buyerAccountingReferenceTypeCode`.'], 'id' => 'BT-133', 'typeCode' => 'BT-X-99'],
    'salesOrder' => $reference('BG-X-81', 'BT-X-537', 'BT-X-539', 'BT-X-538'),
    'quotation' => $reference('BG-X-47', 'BT-X-310', 'BT-X-312', 'BT-X-311'),
    'contract' => $reference('BG-X-2', 'BT-X-24', 'BT-X-26', 'BT-X-25'),
    'ultimateCustomerOrders[]' => $reference('BG-X-5', 'BT-X-43', 'BT-X-45', 'BT-X-44'),
    'despatchAdvice' => $reference('BG-X-13', 'BT-X-86', 'BT-X-88', 'BT-X-87'),
    'receivingAdvice' => $reference('BG-X-82', 'BT-X-89', 'BT-X-91', 'BT-X-90'),
    'deliveryNote' => $reference('BG-X-83', 'BT-X-92', 'BT-X-94', 'BT-X-93'),
    'precedingInvoice' => $reference('BG-X-48', 'BT-X-331', 'BT-X-333', 'BT-X-540', 'BT-X-332'),
    'additionalDocuments[]' => [
        '@type' => 'Attachment',
        '@group' => 'BG-X-3',
        'id' => 'BT-X-27',
        'description' => 'BT-X-299',
        'url' => 'BT-X-28',
        'base64' => 'BT-X-31',
        'mimeCode' => 'BT-X-31-1',
        'filename' => 'BT-X-31-2',
        'date' => 'BT-X-33',
        'lineId' => 'BT-X-29',
        'typeCode' => 'BT-X-30',
        'referenceTypeCode' => 'BT-X-32',
    ],
    'deliveryTerms' => ['@type' => 'DeliveryTerms', '@group' => 'BG-X-87', 'code' => 'BT-X-562', 'locationCountry' => 'BT-X-565', 'locationName' => 'BT-X-566'],
    'includedTax' => $tax('BG-X-4', ['category' => 'BT-X-40', 'rate' => 'BT-X-42', 'amount' => 'BT-X-37', 'exemptionReason' => 'BT-X-39', 'exemptionReasonCode' => 'BT-X-41']),
    'seller' => $lineSeller,
    'shipTo' => $lineShipTo,
    'ultimateShipTo' => $lineUltimateShipTo,
    'deliveryDate' => 'BT-X-85',
];

// ---------------------------------------------------------------- the invoice

$earlyPaymentDiscount = $paymentCondition('BG-X-44', ['BT-X-282', 'BT-X-283', 'BT-X-284', 'BT-X-285', 'BT-X-286', 'BT-X-287']);
$latePaymentPenalty = $paymentCondition('BG-X-43', ['BT-X-276', 'BT-X-277', 'BT-X-278', 'BT-X-279', 'BT-X-280', 'BT-X-281']);

return [
    '@type' => 'Invoice',
    // EN 16931 and the XRechnung extension
    'number' => 'BT-1',
    'typeCode' => 'BT-3',
    'issueDate' => 'BT-2',
    'currency' => 'BT-5',
    'vatCurrency' => 'BT-6',
    'taxPointDate' => 'BT-7',
    'taxPointDateCode' => 'BT-8',
    'dueDate' => 'BT-9',
    'buyerReference' => 'BT-10',
    'buyerAccountingReference' => 'BT-19',
    'paymentTerms' => 'BT-20',
    'paymentReference' => 'BT-83',
    'businessProcess' => 'BT-23',
    'specification' => 'BT-24',
    'notes[]' => ['@type' => 'Note', '@group' => 'BG-1', 'text' => 'BT-22', 'subjectCode' => 'BT-21', 'contentCode' => 'BT-X-5'],
    'precedingInvoices[]' => $reference('BG-3', 'BT-25', 'BT-26', null, 'BT-X-555'),
    'seller' => $seller,
    'buyer' => $buyer,
    'payee' => $payee,
    'sellerTaxRepresentative' => $sellerTaxRepresentative,
    'purchaseOrder' => $reference(null, 'BT-13', 'BT-X-147'),
    'salesOrder' => $reference(null, 'BT-14', 'BT-X-146'),
    'contract' => $reference(null, 'BT-12', 'BT-X-148', null, 'BT-X-405'),
    'project' => ['@type' => 'Project', '@group' => 'BT-11-00', 'id' => 'BT-11', 'name' => 'BT-11-0'],
    'tender' => $reference(null, 'BT-17', 'BT-X-556'),
    'invoicedObject' => $reference(null, 'BT-18', 'BT-X-557', null, null, 'BT-18-1'),
    'delivery' => [
        '@type' => 'Delivery',
        'date' => 'BT-72',
        'shipTo' => $shipTo,
        'despatchAdvice' => $reference(null, 'BT-16', 'BT-X-200'),
        'receivingAdvice' => $reference(null, 'BT-15', 'BT-X-201'),
        'deliveryNote' => $reference(null, 'BT-X-202', 'BT-X-203'),
        'transportModeCodes[]' => 'BT-X-152',
        'ultimateShipTo' => $partyX('BG-X-27', 162, 163, 164, 551, 165, 166, 'BG-X-68', 440, 'BG-X-28', [167, 168, 322, 169, 170, 171], 'BG-X-29', 172, 179, 180),
        'shipFrom' => $partyX('BG-X-30', 181, 182, 183, 552, 184, 185, 'BG-X-69', 447, 'BG-X-31', [186, 187, 323, 188, 189, 190], 'BG-X-32', 191, 198, 199),
    ],
    'invoicingPeriod' => ['@type' => 'Period', '@group' => 'BG-14', 'startDate' => 'BT-73', 'endDate' => 'BT-74', 'description' => 'BT-X-264'],
    'paymentMeans[]' => [
        '@type' => 'PaymentMeans',
        '@group' => 'BG-16',
        'typeCode' => 'BT-81',
        'text' => 'BT-82',
        'accountId' => 'BT-84',
        'accountName' => 'BT-85',
        'bic' => 'BT-86',
        'cardNumber' => 'BT-87',
        'cardHolder' => 'BT-88',
        'debitedAccountId' => 'BT-91',
        'debitedAccountName' => 'BT-216',
        'debitedAccountBic' => 'BT-215',
    ],
    'directDebit' => ['@type' => 'DirectDebit', '@group' => 'BG-19', 'mandateReference' => 'BT-89', 'creditorId' => 'BT-90'],
    'allowances[]' => $allowanceCharge('BG-20', [
        'amount' => 'BT-92', 'baseAmount' => 'BT-93', 'percentage' => 'BT-94', 'reason' => 'BT-97', 'reasonCode' => 'BT-98',
        'vatCategory' => 'BT-95', 'vatRate' => 'BT-96', 'vatExemptionReason' => 'BT-173', 'vatExemptionReasonCode' => 'BT-174',
        'sequence' => 'BT-X-265', 'baseQuantity' => 'BT-X-266', 'baseUnit' => 'BT-X-267',
    ]),
    'charges[]' => $allowanceCharge('BG-21', [
        'amount' => 'BT-99', 'baseAmount' => 'BT-100', 'percentage' => 'BT-101', 'reason' => 'BT-104', 'reasonCode' => 'BT-105',
        'vatCategory' => 'BT-102', 'vatRate' => 'BT-103', 'vatExemptionReason' => 'BT-175', 'vatExemptionReasonCode' => 'BT-176',
        'taxTypeCode' => 'BT-177',
        'sequence' => 'BT-X-268', 'baseQuantity' => 'BT-X-269', 'baseUnit' => 'BT-X-270',
    ]),
    'totals' => [
        '@type' => 'Totals',
        '@group' => 'BG-22',
        'lineNetAmount' => 'BT-106',
        'allowanceAmount' => 'BT-107',
        'chargeAmount' => 'BT-108',
        'netAmount' => 'BT-109',
        'vatAmount' => 'BT-110',
        'vatAmountInVatCurrency' => 'BT-111',
        'grossAmount' => 'BT-112',
        'paidAmount' => 'BT-113',
        'roundingAmount' => 'BT-114',
        'dueAmount' => 'BT-115',
    ],
    'vatBreakdown[]' => [
        '@type' => 'VatBreakdown',
        '@group' => 'BG-23',
        'category' => 'BT-118',
        'rate' => 'BT-119',
        'taxableAmount' => 'BT-116',
        'taxAmount' => 'BT-117',
        'exemptionReason' => 'BT-120',
        'exemptionReasonCode' => 'BT-121',
        'lineTotalBasisAmount' => 'BT-X-262',
        'allowanceChargeBasisAmount' => 'BT-X-263',
    ],
    'attachments[]' => [
        '@type' => 'Attachment',
        '@group' => 'BG-24',
        'id' => 'BT-122',
        'description' => 'BT-123',
        'url' => 'BT-124',
        'base64' => 'BT-125',
        'mimeCode' => 'BT-125-1',
        'filename' => 'BT-125-2',
        'date' => 'BT-X-149',
    ],
    'lines[]' => $line,
    'thirdPartyPayments[]' => [
        '@type' => 'ThirdPartyPayment',
        '@group' => ['BG-DEX-09', 'BG-34'],
        '@note' => ['en' => 'XRechnung extension (UBL, with a type) and EXTENDED (CII); the amount counts towards the amount due.', 'de' => 'XRechnung-Erweiterung (UBL, mit Art) und EXTENDED (CII); der Betrag zählt zum Zahlbetrag.'],
        'type' => 'BT-DEX-001',
        'amount' => ['BT-DEX-002', 'BT-179'],
        'description' => ['BT-DEX-003', 'BT-180'],
    ],
    // ZUGFeRD / Factur-X EXTENDED
    'testIndicator' => 'BT-X-1',
    'copyIndicator' => 'BT-X-3',
    'documentName' => 'BT-X-2',
    'languageCode' => 'BT-X-4',
    'contractualDueDate' => 'BT-X-6',
    'sellerReference' => 'BT-X-204',
    'additionalTenders[]' => ['@note' => ['en' => 'More tender or lot references (EXTENDED) - the first is `tender`.', 'de' => 'Weitere Vergabe- oder Losnummern (EXTENDED) - die erste ist `tender`.']] + $reference('BT-17-00', 'BT-17', 'BT-X-556'),
    'additionalInvoicedObjects[]' => ['@note' => ['en' => 'More invoiced object identifiers (EXTENDED) - the first is `invoicedObject`.', 'de' => 'Weitere Objektkennungen (EXTENDED) - die erste ist `invoicedObject`.']] + $reference('BT-18-00', 'BT-18', 'BT-X-557', null, null, 'BT-18-1'),
    'buyerAccountingReferenceTypeCode' => 'BT-X-290',
    'additionalBuyerAccountingReferences[]' => ['@type' => 'AccountingReference', '@group' => 'BT-19-00', '@note' => ['en' => 'More buyer accounting references - the first are `buyerAccountingReference` and `buyerAccountingReferenceTypeCode`.', 'de' => 'Weitere Buchungsreferenzen - die erste sind `buyerAccountingReference` und `buyerAccountingReferenceTypeCode`.'], 'id' => 'BT-19', 'typeCode' => 'BT-X-290'],
    'partialPaymentAmount' => 'BT-X-275',
    'earlyPaymentDiscount' => $earlyPaymentDiscount,
    'latePaymentPenalty' => $latePaymentPenalty,
    'paymentTermsPayee' => $paymentTermsPayee,
    'additionalPaymentTerms[]' => [
        '@type' => 'PaymentTerms',
        '@group' => 'BT-20-00',
        '@note' => [
            'en' => 'More payment terms (instalments) - the first are `dueDate`, `paymentTerms`, `directDebit.mandateReference`, `partialPaymentAmount`, `earlyPaymentDiscount`, `latePaymentPenalty` and `paymentTermsPayee` of the invoice.',
            'de' => 'Weitere Zahlungsbedingungen (Raten) - die erste sind `dueDate`, `paymentTerms`, `directDebit.mandateReference`, `partialPaymentAmount`, `earlyPaymentDiscount`, `latePaymentPenalty` und `paymentTermsPayee` der Rechnung.',
        ],
        'description' => 'BT-20',
        'dueDate' => 'BT-9',
        'mandateReference' => 'BT-89',
        'partialPaymentAmount' => 'BT-X-275',
        'earlyPaymentDiscount' => $earlyPaymentDiscount,
        'latePaymentPenalty' => $latePaymentPenalty,
        'payee' => $paymentTermsPayee,
    ],
    'buyerTaxRepresentative' => $partyX('BG-X-54', 364, 365, 362, 546, 366, 363, 'BG-X-57', 382, 'BG-X-55', [369, 370, 371, 372, 373, 374], 'BG-X-56', 375, 368, 367),
    'salesAgent' => $partyX('BG-X-49', 337, 338, 335, 545, 339, 336, 'BG-X-53', 355, 'BG-X-51', [342, 343, 347, 344, 345, 346], 'BG-X-52', 348, 341, 340),
    'buyerAgent' => $partyX('BG-X-62', 408, 409, 406, 549, 410, 407, 'BG-X-66', 426, 'BG-X-64', [413, 414, 415, 416, 417, 418], 'BG-X-65', 419, 412, 411),
    'productEndUser' => $partyX('BG-X-18', 126, 127, 128, 548, 129, 130, 'BG-X-60', 396, 'BG-X-20', [131, 132, 320, 133, 134, 135], 'BG-X-21', 136, 143, 144),
    'invoicer' => $partyX('BG-X-33', 205, 206, 207, 553, 208, 209, 'BG-X-70', 454, 'BG-X-34', [210, 211, 324, 212, 213, 214], 'BG-X-35', 215, 222, 223),
    'invoicee' => $partyX('BG-X-36', 224, 225, 226, 554, 227, 228, 'BG-X-71', 461, 'BG-X-37', [229, 230, 325, 231, 232, 233], 'BG-X-38', 234, 241, 242),
    'payer' => $partyX('BG-X-73', 478, 479, 476, 483, 480, 477, 'BG-X-76', 497, 'BG-X-74', [484, 485, 486, 487, 488, 489], 'BG-X-75', 490, 482, 481),
    'quotation' => $reference('BG-X-61', 'BT-X-403', 'BT-X-404'),
    'ultimateCustomerOrders[]' => $reference('BG-X-23', 'BT-X-150', 'BT-X-151'),
    'deliveryTerms' => ['@type' => 'DeliveryTerms', '@group' => 'BG-X-22', 'code' => 'BT-X-145', 'locationCountry' => 'BT-X-563', 'locationName' => 'BT-X-564'],
    'logisticsServiceCharges[]' => [
        '@type' => 'LogisticsServiceCharge',
        '@group' => 'BG-X-42',
        'description' => 'BT-X-271',
        'amount' => 'BT-X-272',
        'taxes[]' => $tax('BT-X-273-00', ['category' => 'BT-X-273', 'rate' => 'BT-X-274', 'exemptionReason' => 'BT-X-591', 'exemptionReasonCode' => 'BT-X-592']),
    ],
    'currencyExchange' => ['@type' => 'CurrencyExchange', '@group' => 'BG-X-41', 'sourceCurrency' => 'BT-X-258', 'targetCurrency' => 'BT-X-259', 'rate' => 'BT-X-260', 'date' => 'BT-X-261'],
    'advancePayments[]' => [
        '@type' => 'AdvancePayment',
        '@group' => 'BG-X-45',
        'amount' => 'BT-X-291',
        'date' => 'BT-X-292',
        'taxes[]' => $tax('BG-X-46', ['category' => 'BT-X-296', 'rate' => 'BT-X-298', 'amount' => 'BT-X-293', 'exemptionReason' => 'BT-X-295', 'exemptionReasonCode' => 'BT-X-297']),
        'precedingInvoice' => $reference('BG-X-85', 'BT-X-558', 'BT-X-560', null, 'BT-X-559'),
    ],
];
