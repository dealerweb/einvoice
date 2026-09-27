<?php

declare(strict_types=1);

namespace Dealerweb\EInvoice;

use DateTimeInterface;
use Dealerweb\EInvoice\Model\AccountingReference;
use Dealerweb\EInvoice\Model\AdvancePayment;
use Dealerweb\EInvoice\Model\AllowanceCharge;
use Dealerweb\EInvoice\Model\Attachment;
use Dealerweb\EInvoice\Model\Behavior\InvoiceBehavior;
use Dealerweb\EInvoice\Model\CurrencyExchange;
use Dealerweb\EInvoice\Model\Delivery;
use Dealerweb\EInvoice\Model\DeliveryTerms;
use Dealerweb\EInvoice\Model\DirectDebit;
use Dealerweb\EInvoice\Model\DocumentReference;
use Dealerweb\EInvoice\Model\Element;
use Dealerweb\EInvoice\Model\Line;
use Dealerweb\EInvoice\Model\LogisticsServiceCharge;
use Dealerweb\EInvoice\Model\Note;
use Dealerweb\EInvoice\Model\Party;
use Dealerweb\EInvoice\Model\PaymentCondition;
use Dealerweb\EInvoice\Model\PaymentMeans;
use Dealerweb\EInvoice\Model\PaymentTerms;
use Dealerweb\EInvoice\Model\Period;
use Dealerweb\EInvoice\Model\Project;
use Dealerweb\EInvoice\Model\ThirdPartyPayment;
use Dealerweb\EInvoice\Model\Totals;
use Dealerweb\EInvoice\Model\VatBreakdown;

/**
 * An electronic invoice or credit note: every field of EN 16931, of the XRechnung extension and of ZUGFeRD /
 * Factur-X EXTENDED with a readable name. Read from XML (UBL or CII) or from a ZUGFeRD / Factur-X PDF (fromFile()),
 * or built here - and written as XML (toXml()) or as ZUGFeRD / Factur-X PDF (toPdf()).
 *
 * Used as:
 *  - invoice
 *
 * Generated from resources/model/mapping.php - do not edit.
 */
final class Invoice extends Element
{
    use InvoiceBehavior;

    protected const OBJECTS = [
        'seller' => Party::class,
        'buyer' => Party::class,
        'payee' => Party::class,
        'sellerTaxRepresentative' => Party::class,
        'purchaseOrder' => DocumentReference::class,
        'salesOrder' => DocumentReference::class,
        'contract' => DocumentReference::class,
        'project' => Project::class,
        'tender' => DocumentReference::class,
        'invoicedObject' => DocumentReference::class,
        'delivery' => Delivery::class,
        'invoicingPeriod' => Period::class,
        'directDebit' => DirectDebit::class,
        'totals' => Totals::class,
        'earlyPaymentDiscount' => PaymentCondition::class,
        'latePaymentPenalty' => PaymentCondition::class,
        'paymentTermsPayee' => Party::class,
        'buyerTaxRepresentative' => Party::class,
        'salesAgent' => Party::class,
        'buyerAgent' => Party::class,
        'productEndUser' => Party::class,
        'invoicer' => Party::class,
        'invoicee' => Party::class,
        'payer' => Party::class,
        'quotation' => DocumentReference::class,
        'deliveryTerms' => DeliveryTerms::class,
        'currencyExchange' => CurrencyExchange::class,
    ];

    protected const LISTS = [
        'notes' => Note::class,
        'precedingInvoices' => DocumentReference::class,
        'paymentMeans' => PaymentMeans::class,
        'allowances' => AllowanceCharge::class,
        'charges' => AllowanceCharge::class,
        'vatBreakdown' => VatBreakdown::class,
        'attachments' => Attachment::class,
        'lines' => Line::class,
        'thirdPartyPayments' => ThirdPartyPayment::class,
        'additionalTenders' => DocumentReference::class,
        'additionalInvoicedObjects' => DocumentReference::class,
        'additionalBuyerAccountingReferences' => AccountingReference::class,
        'additionalPaymentTerms' => PaymentTerms::class,
        'ultimateCustomerOrders' => DocumentReference::class,
        'logisticsServiceCharges' => LogisticsServiceCharge::class,
        'advancePayments' => AdvancePayment::class,
    ];

    /**
     * @param list<Note> $notes Note (BG-1)
     * @param list<DocumentReference> $precedingInvoices Preceding invoice (BG-3)
     * @param list<PaymentMeans> $paymentMeans Payment instructions (BG-16)
     * @param list<AllowanceCharge> $allowances Allowance (BG-20)
     * @param list<AllowanceCharge> $charges Charge (BG-21)
     * @param list<VatBreakdown> $vatBreakdown VAT breakdown (BG-23)
     * @param list<Attachment> $attachments Attachment (BG-24)
     * @param list<Line> $lines Line (BG-25)
     * @param list<ThirdPartyPayment> $thirdPartyPayments Payments to third parties (XRechnung extension, UBL) or charges collected on behalf of third parties (EXTENDED) - they count to the amount due
     * @param list<DocumentReference> $additionalTenders Further tender or lot references (EXTENDED) - the first is tender
     * @param list<DocumentReference> $additionalInvoicedObjects Further invoiced object identifiers (EXTENDED) - the first is invoicedObject
     * @param list<AccountingReference> $additionalBuyerAccountingReferences Further accounting references of the buyer (EXTENDED) - the first is buyerAccountingReference
     * @param list<PaymentTerms> $additionalPaymentTerms Further payment terms, instalments (EXTENDED) - the first are dueDate, paymentTerms and the other payment terms of the invoice
     * @param list<DocumentReference> $ultimateCustomerOrders Ultimate customer order (BG-X-23)
     * @param list<LogisticsServiceCharge> $logisticsServiceCharges Logistics service charge (BG-X-42)
     * @param list<AdvancePayment> $advancePayments Advance payment (BG-X-45)
     */
    public function __construct(
        /** Invoice number (BT-1) */
        public ?string $number = null,
        /** Invoice type (BT-3) */
        public ?string $typeCode = null,
        /** Invoice date (BT-2) */
        public string|DateTimeInterface|null $issueDate = null,
        /** Invoice currency (BT-5) */
        public ?string $currency = null,
        /** VAT currency (BT-6) */
        public ?string $vatCurrency = null,
        /** Tax point date (BT-7) */
        public string|DateTimeInterface|null $taxPointDate = null,
        /** Tax point date code (BT-8) */
        public ?string $taxPointDateCode = null,
        /** Due date (BT-9) */
        public string|DateTimeInterface|null $dueDate = null,
        /** Buyer reference (BT-10) */
        public ?string $buyerReference = null,
        /** Buyer accounting reference (BT-19) */
        public ?string $buyerAccountingReference = null,
        /** Payment terms (BT-20) */
        public ?string $paymentTerms = null,
        /** Payment reference (BT-83) */
        public ?string $paymentReference = null,
        /** Business process (BT-23) */
        public ?string $businessProcess = null,
        /** Specification identifier (BT-24) */
        public ?string $specification = null,
        public array $notes = [],
        public array $precedingInvoices = [],
        /** Seller (BG-4) */
        public Party $seller = new Party(),
        /** Buyer (BG-7) */
        public Party $buyer = new Party(),
        /** Payee (BG-10) */
        public Party $payee = new Party(),
        /** Seller tax representative (BG-11) */
        public Party $sellerTaxRepresentative = new Party(),
        /** Purchase order (BT-13) */
        public DocumentReference $purchaseOrder = new DocumentReference(),
        /** Sales order (BT-14) */
        public DocumentReference $salesOrder = new DocumentReference(),
        /** Contract (BT-12) */
        public DocumentReference $contract = new DocumentReference(),
        /** Project (BT-11-00) */
        public Project $project = new Project(),
        /** Tender or lot (BT-17) */
        public DocumentReference $tender = new DocumentReference(),
        /** Invoiced object (BT-18) */
        public DocumentReference $invoicedObject = new DocumentReference(),
        /** Delivery: where and when the goods and services were delivered */
        public Delivery $delivery = new Delivery(),
        /** Invoicing period (BG-14) */
        public Period $invoicingPeriod = new Period(),
        public array $paymentMeans = [],
        /** Direct debit: mandate reference and creditor identifier */
        public DirectDebit $directDebit = new DirectDebit(),
        public array $allowances = [],
        public array $charges = [],
        /** Totals (BG-22) */
        public Totals $totals = new Totals(),
        public array $vatBreakdown = [],
        public array $attachments = [],
        public array $lines = [],
        public array $thirdPartyPayments = [],
        /** Test indicator (BT-X-1) */
        public ?bool $testIndicator = null,
        /** Copy indicator (BT-X-3) */
        public ?bool $copyIndicator = null,
        /** Document name (BT-X-2) */
        public ?string $documentName = null,
        /** Language (BT-X-4) */
        public ?string $languageCode = null,
        /** Contractual due date (BT-X-6) */
        public string|DateTimeInterface|null $contractualDueDate = null,
        /** Seller reference (BT-X-204) */
        public ?string $sellerReference = null,
        public array $additionalTenders = [],
        public array $additionalInvoicedObjects = [],
        /** Accounting reference type (BT-X-290) */
        public ?string $buyerAccountingReferenceTypeCode = null,
        public array $additionalBuyerAccountingReferences = [],
        /** Partial payment amount (BT-X-275) */
        public string|int|float|null $partialPaymentAmount = null,
        /** Early payment discount (EXTENDED) */
        public PaymentCondition $earlyPaymentDiscount = new PaymentCondition(),
        /** Late payment penalty (EXTENDED) */
        public PaymentCondition $latePaymentPenalty = new PaymentCondition(),
        /** Payee of the payment terms, where it is not the payee of the invoice (EXTENDED) */
        public Party $paymentTermsPayee = new Party(),
        public array $additionalPaymentTerms = [],
        /** Buyer tax representative (BG-X-54) */
        public Party $buyerTaxRepresentative = new Party(),
        /** Sales agent (BG-X-49) */
        public Party $salesAgent = new Party(),
        /** Buyer agent (BG-X-62) */
        public Party $buyerAgent = new Party(),
        /** Product end user (BG-X-18) */
        public Party $productEndUser = new Party(),
        /** Invoicer (BG-X-33) */
        public Party $invoicer = new Party(),
        /** Invoicee (BG-X-36) */
        public Party $invoicee = new Party(),
        /** Payer (BG-X-73) */
        public Party $payer = new Party(),
        /** Quotation (BG-X-61) */
        public DocumentReference $quotation = new DocumentReference(),
        public array $ultimateCustomerOrders = [],
        /** Delivery terms (BG-X-22) */
        public DeliveryTerms $deliveryTerms = new DeliveryTerms(),
        public array $logisticsServiceCharges = [],
        /** Currency exchange (BG-X-41) */
        public CurrencyExchange $currencyExchange = new CurrencyExchange(),
        public array $advancePayments = [],
    ) {}
}
