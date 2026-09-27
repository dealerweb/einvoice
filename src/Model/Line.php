<?php

declare(strict_types=1);

namespace Dealerweb\EInvoice\Model;

use DateTimeInterface;

/**
 * An invoice line - also a sub line of the XRechnung extension.
 *
 * Used as:
 *  - invoice.lines (BG-25)
 *
 * Generated from resources/model/mapping.php - do not edit.
 */
final class Line extends Element
{
    protected const OBJECTS = [
        'standardItemId' => Identifier::class,
        'objectIdentifier' => Identifier::class,
        'period' => Period::class,
        'purchaseOrder' => DocumentReference::class,
        'manufacturer' => Party::class,
        'salesOrder' => DocumentReference::class,
        'quotation' => DocumentReference::class,
        'contract' => DocumentReference::class,
        'despatchAdvice' => DocumentReference::class,
        'receivingAdvice' => DocumentReference::class,
        'deliveryNote' => DocumentReference::class,
        'precedingInvoice' => DocumentReference::class,
        'deliveryTerms' => DeliveryTerms::class,
        'includedTax' => Tax::class,
        'seller' => Party::class,
        'shipTo' => Party::class,
        'ultimateShipTo' => Party::class,
    ];

    protected const LISTS = [
        'classifications' => Classification::class,
        'attributes' => ItemAttribute::class,
        'allowances' => AllowanceCharge::class,
        'charges' => AllowanceCharge::class,
        'subLines' => Line::class,
        'additionalNotes' => Note::class,
        'additionalPriceDiscounts' => AllowanceCharge::class,
        'priceCharges' => AllowanceCharge::class,
        'additionalTaxes' => Tax::class,
        'batchIds' => null,
        'instances' => ItemInstance::class,
        'includedItems' => IncludedItem::class,
        'additionalObjectIdentifiers' => Identifier::class,
        'additionalBuyerAccountingReferences' => AccountingReference::class,
        'ultimateCustomerOrders' => DocumentReference::class,
        'additionalDocuments' => Attachment::class,
    ];

    /**
     * @param list<Classification> $classifications Classification (BT-158-00)
     * @param list<ItemAttribute> $attributes Attribute (BG-32)
     * @param list<AllowanceCharge> $allowances Allowance (BG-27)
     * @param list<AllowanceCharge> $charges Charge (BG-28)
     * @param list<Line> $subLines Sub lines (XRechnung extension, UBL only), each built like a line
     * @param list<Note> $additionalNotes Further notes (EXTENDED) - the first is note
     * @param list<AllowanceCharge> $additionalPriceDiscounts Further discounts on the gross price (EXTENDED) - the first is priceDiscount
     * @param list<AllowanceCharge> $priceCharges Charges on the gross price (EXTENDED)
     * @param list<Tax> $additionalTaxes Further VAT of the line (EXTENDED) - the first is vatCategory and vatRate
     * @param list<string> $batchIds Batch number (BT-X-534)
     * @param list<ItemInstance> $instances Item instance (BG-X-84)
     * @param list<IncludedItem> $includedItems Included item (BG-X-1)
     * @param list<Identifier> $additionalObjectIdentifiers Further object identifiers (EXTENDED) - the first is objectIdentifier
     * @param list<AccountingReference> $additionalBuyerAccountingReferences Further accounting references of the buyer (EXTENDED) - the first is buyerAccountingReference
     * @param list<DocumentReference> $ultimateCustomerOrders Ultimate customer order (BG-X-5)
     * @param list<Attachment> $additionalDocuments Referenced document (BG-X-3)
     */
    public function __construct(
        /** Identifier (BT-126) */
        public ?string $id = null,
        /** Note (BT-127) */
        public ?string $note = null,
        /** Quantity (BT-129) */
        public string|int|float|null $quantity = null,
        /** Unit (BT-130) */
        public ?string $unit = null,
        /** Net amount (BT-131) */
        public string|int|float|null $netAmount = null,
        /** Name (BT-153) */
        public ?string $name = null,
        /** Description (BT-154) */
        public ?string $description = null,
        /** Net price (BT-146) */
        public string|int|float|null $netPrice = null,
        /** Gross price (BT-148) */
        public string|int|float|null $grossPrice = null,
        /** Price base quantity (BT-149) */
        public string|int|float|null $priceBaseQuantity = null,
        /** Price base quantity unit (BT-150) */
        public ?string $priceBaseUnit = null,
        /** Price discount (BT-147) */
        public string|int|float|null $priceDiscount = null,
        /** VAT category (BT-151) */
        public ?string $vatCategory = null,
        /** VAT rate (BT-152) */
        public string|int|float|null $vatRate = null,
        /** Seller item number (BT-155) */
        public ?string $sellerItemId = null,
        /** Buyer item number (BT-156) */
        public ?string $buyerItemId = null,
        /** Standard item identifier (BT-157) */
        public Identifier $standardItemId = new Identifier(),
        public array $classifications = [],
        /** Country of origin (BT-159) */
        public ?string $originCountry = null,
        public array $attributes = [],
        /** Object identifier (BT-128) */
        public Identifier $objectIdentifier = new Identifier(),
        /** Buyer accounting reference (BT-133) */
        public ?string $buyerAccountingReference = null,
        /** Period (BG-26) */
        public Period $period = new Period(),
        public array $allowances = [],
        public array $charges = [],
        /** Purchase order (BT-X-21) */
        public DocumentReference $purchaseOrder = new DocumentReference(),
        public array $subLines = [],
        /** Note subject code (BT-X-10) */
        public ?string $noteSubjectCode = null,
        /** Note content code (BT-X-9) */
        public ?string $noteContentCode = null,
        public array $additionalNotes = [],
        /** Parent line (BT-X-304) */
        public ?string $parentLineId = null,
        /** Type (BT-X-7) */
        public ?string $typeCode = null,
        /** Line subtype (BT-X-8) */
        public ?string $subtypeCode = null,
        /** Gross price base quantity (BT-149-1) */
        public string|int|float|null $grossPriceBaseQuantity = null,
        /** Gross price base quantity unit (BT-150-1) */
        public ?string $grossPriceBaseUnit = null,
        /** Price discount percentage (BT-X-34) */
        public string|int|float|null $priceDiscountPercentage = null,
        /** Price discount base amount (BT-X-35) */
        public string|int|float|null $priceDiscountBaseAmount = null,
        /** Price discount reason (BT-X-36) */
        public ?string $priceDiscountReason = null,
        /** Price discount reason code (BT-X-313) */
        public ?string $priceDiscountReasonCode = null,
        public array $additionalPriceDiscounts = [],
        public array $priceCharges = [],
        /** VAT amount (BT-X-95) */
        public string|int|float|null $vatAmount = null,
        /** VAT exemption reason (BT-X-96) */
        public ?string $vatExemptionReason = null,
        /** VAT exemption reason code (BT-X-97) */
        public ?string $vatExemptionReasonCode = null,
        /** Tax point date code (BT-X-589) */
        public ?string $taxPointDateCode = null,
        public array $additionalTaxes = [],
        /** Charge-free quantity (BT-X-46) */
        public string|int|float|null $chargeFreeQuantity = null,
        /** Charge-free quantity unit (BT-X-46-0) */
        public ?string $chargeFreeQuantityUnit = null,
        /** Number of packages (BT-X-47) */
        public string|int|float|null $packageQuantity = null,
        /** Package unit (BT-X-47-0) */
        public ?string $packageQuantityUnit = null,
        /** Quantity per package (BT-X-561) */
        public string|int|float|null $quantityPerPackage = null,
        /** Quantity per package unit (BT-X-561-0) */
        public ?string $quantityPerPackageUnit = null,
        /** Total of charges (BT-X-327) */
        public string|int|float|null $chargeTotal = null,
        /** Total of allowances (BT-X-328) */
        public string|int|float|null $allowanceTotal = null,
        /** Total of allowances and charges (BT-X-98) */
        public string|int|float|null $allowanceChargeTotal = null,
        /** Tax total (BT-X-329) */
        public string|int|float|null $taxTotal = null,
        /** Tax total in VAT currency (BT-X-590) */
        public string|int|float|null $taxTotalInVatCurrency = null,
        /** Gross amount (BT-X-330) */
        public string|int|float|null $grossAmount = null,
        /** Item identifier (BT-X-305) */
        public ?string $itemId = null,
        /** Industry item identifier (BT-X-532) */
        public ?string $industryItemId = null,
        /** Model identifier (BT-X-533) */
        public ?string $modelId = null,
        public array $batchIds = [],
        /** Brand (BT-X-535) */
        public ?string $brand = null,
        /** Model (BT-X-536) */
        public ?string $model = null,
        public array $instances = [],
        /** Manufacturer (BG-X-93) */
        public Party $manufacturer = new Party(),
        public array $includedItems = [],
        public array $additionalObjectIdentifiers = [],
        /** Accounting reference type (BT-X-99) */
        public ?string $buyerAccountingReferenceTypeCode = null,
        public array $additionalBuyerAccountingReferences = [],
        /** Sales order (BG-X-81) */
        public DocumentReference $salesOrder = new DocumentReference(),
        /** Quotation (BG-X-47) */
        public DocumentReference $quotation = new DocumentReference(),
        /** Contract (BG-X-2) */
        public DocumentReference $contract = new DocumentReference(),
        public array $ultimateCustomerOrders = [],
        /** Despatch advice (BG-X-13) */
        public DocumentReference $despatchAdvice = new DocumentReference(),
        /** Receiving advice (BG-X-82) */
        public DocumentReference $receivingAdvice = new DocumentReference(),
        /** Delivery note (BG-X-83) */
        public DocumentReference $deliveryNote = new DocumentReference(),
        /** Preceding invoice (BG-X-48) */
        public DocumentReference $precedingInvoice = new DocumentReference(),
        public array $additionalDocuments = [],
        /** Delivery terms (BG-X-87) */
        public DeliveryTerms $deliveryTerms = new DeliveryTerms(),
        /** Tax included in the price (EXTENDED) */
        public Tax $includedTax = new Tax(),
        /** Seller (BG-X-90) */
        public Party $seller = new Party(),
        /** Ship-to party (BG-X-7) */
        public Party $shipTo = new Party(),
        /** Ultimate ship-to party (BG-X-10) */
        public Party $ultimateShipTo = new Party(),
        /** Delivery date (BT-X-85) */
        public string|DateTimeInterface|null $deliveryDate = null,
    ) {}
}
