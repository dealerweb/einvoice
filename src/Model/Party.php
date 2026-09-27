<?php

declare(strict_types=1);

namespace Dealerweb\EInvoice\Model;

/**
 * A party: the seller, the buyer, the payee and the other parties of the invoice or of a line.
 *
 * Used as:
 *  - invoice.seller (BG-4)
 *  - invoice.buyer (BG-7)
 *  - invoice.payee (BG-10)
 *  - invoice.sellerTaxRepresentative (BG-11)
 *  - invoice.delivery.shipTo (BG-13)
 *  - invoice.delivery.ultimateShipTo (BG-X-27)
 *  - invoice.delivery.shipFrom (BG-X-30)
 *  - invoice.lines.manufacturer (BG-X-93)
 *  - and 12 more (MODEL.md)
 *
 * Generated from resources/model/mapping.php - do not edit.
 */
final class Party extends Element
{
    protected const OBJECTS = [
        'legalRegistrationId' => Identifier::class,
        'electronicAddress' => Identifier::class,
        'address' => Address::class,
        'contact' => Contact::class,
        'legalAddress' => Address::class,
    ];

    protected const LISTS = [
        'identifiers' => Identifier::class,
        'additionalContacts' => Contact::class,
    ];

    /**
     * @param list<Identifier> $identifiers Identifiers of the party; with a scheme (e.g. 0088 for a GLN) a global identifier
     * @param list<Contact> $additionalContacts Further contacts (EXTENDED) - the first is contact
     */
    public function __construct(
        /** Name */
        public ?string $name = null,
        /** Trading name: a name by which the party is known, other than its legal name */
        public ?string $tradingName = null,
        public array $identifiers = [],
        /** Legal registration identifier (e.g. the number in the commercial register) with its scheme */
        public Identifier $legalRegistrationId = new Identifier(),
        /** VAT identifier (e.g. DE123456789) */
        public ?string $vatId = null,
        /** Local tax registration identifier (in Germany the Steuernummer) */
        public ?string $taxNumber = null,
        /** Additional legal information */
        public ?string $legalInformation = null,
        /** Electronic address with its scheme (EAS: EM for an e-mail address, 0204 for a Leitweg-ID) */
        public Identifier $electronicAddress = new Identifier(),
        /** Postal address */
        public Address $address = new Address(),
        /** Contact */
        public Contact $contact = new Contact(),
        public array $additionalContacts = [],
        /** Role of the party (code, EXTENDED) */
        public ?string $roleCode = null,
        /** Postal address of the legal organization (EXTENDED) */
        public Address $legalAddress = new Address(),
    ) {}
}
