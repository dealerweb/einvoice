# The invoice model - names and fields

Generated from `resources/model/mapping.php` - do not edit.

Every field of EN 16931, of the XRechnung extension and of ZUGFeRD / Factur-X EXTENDED has a place in the model:
`$invoice->buyer->address->city` stands for BT-52. For each object the tables give the property, its value, the id
of the Factur-X field list, its name, how often it may occur, the profiles in CII (M MINIMUM, W BASIC WL,
B BASIC, E EN 16931, X EXTENDED, R XRechnung, P Peppol BIS) and in UBL (C EN 16931, R XRechnung, P Peppol BIS;
empty: no place in UBL). `name[]` is a list. The invoice is `Dealerweb\EInvoice\Invoice`, its parts are classes of
`Dealerweb\EInvoice\Model`; how to read, build and write it: README.md, section "Usage".

Coverage: 866 fields and 160 groups, 0 missing, 0 unknown, 0 misplaced.

## Rules

- **Names** in English, camelCase, without abbreviations except the common ones (`vatId`, `bic`, `url`); the same
  thing has the same name everywhere (`typeCode`, `reasonCode`, `startDate`/`endDate`, `vatCategory`/`vatRate`).
- **Reused objects:** `Party` (seller, buyer and every other party), `Address`, `Contact`, `Identifier` (value +
  scheme), `DocumentReference` (number, date, line, type), `AllowanceCharge`, `Period`, `Tax`, `Attachment` - one
  table per use, because the ids differ.
- **Values:** amounts, quantities and percentages as string or number (`'19.00'`, `19`), dates as `YYYY-MM-DD` or
  `DateTimeInterface`, codes as string (constants for the common lists), yes/no as `bool`, a file as Base64 text.
  Objects are always there (`$invoice->buyer->address->city` needs no check for null), lists start empty.
- **Calculated** by the generator where not given: the totals (`totals`), the VAT breakdown (`vatBreakdown`) and the
  price discount or gross price of a line; given values stay and are checked.
- **Profiles and syntaxes:** a field outside the profile, or one the syntax has no place for, is refused by the
  generator with its path (`buyer.roleCode (BT-X-544) is not a part of the profile XRECHNUNG.`,
  `lines.0.typeCode has no place in UBL.`).
- **Reading:** the model comes from every invoice read, CII or UBL; what a format does not have stays empty, what
  the model has no place for is named by `Model\Reader::unread()`.
- **Not a field of the model** is what the generator sets itself: the currency of an amount, the format of a date,
  the GlobalID of an identifier with a scheme (else the ID), the ProprietaryID of an account that is no IBAN, the
  fixed values of the field list; in UBL also the tax scheme, the document type codes and `NA` where UBL requires a
  value EN 16931 has no field for.

## Decisions

- Address lines are `line1`, `line2`, `line3` (EN 16931: address line 1 to 3), not `street`: line 1 is the main
  line, mostly street and house number, but also a post box. The post code is `postcode`.
- `country` holds the country code (`DE`), `currency` the currency code, `unit` the unit of measure code (`H87`).
- The line is flat: `name`, `description`, `quantity`, `unit`, `netPrice`, `vatRate` directly at the line, the
  item information too (`sellerItemId`, `originCountry`) - no sub group `item`.
- Once in EN 16931, repeatable in EXTENDED: the property holds the first, a list `additional...[]` the others -
  the contacts of a party (`contact`), the payment terms (`dueDate`, `paymentTerms` ... of the invoice), the buyer
  accounting references, the notes of a line, the discounts on the gross price, the VAT and the object identifier
  of a line. Only in EXTENDED and repeatable: a list right away (`priceCharges[]`, `transportModeCodes[]`).
- Payment account: `accountId` (IBAN or another account identifier), `accountName`, `bic` - not `iban`, because
  the field holds other identifiers too. The debited account of a direct debit (`debitedAccountId`) belongs to the
  payment means as in CII and UBL, the mandate and the creditor id (`directDebit`) to the invoice.
- `paymentReference` for the remittance information.
- `delivery->shipTo` is the deliver-to address as a party (name, address; EXTENDED also contact, VAT id); its
  identifier (`identifiers[]`) is the identifier of the deliver-to location (BT-71).
- Document references are objects (`purchaseOrder->number`, `->date`), so that EXTENDED can add the date - the
  preceding invoices too (`precedingInvoices[]`).
- `priceBaseQuantity` applies to the net and the gross price; `grossPriceBaseQuantity` only where the gross price
  has another one (EXTENDED - UBL has one base quantity for both).
- `earlyPaymentDiscount` and `latePaymentPenalty` instead of the EXTENDED names "payment discount terms" and
  "payment penalty terms".
- `subLines[]` (XRechnung extension, UBL only) and `parentLineId` (EXTENDED) stay apart: two mechanisms.
- `thirdPartyPayments[]` are the third party payments of the XRechnung extension (UBL) and of EXTENDED (CII): both
  count towards the amount due (BR-DEX-09, BR-FXEXT-CO-16); the type exists in UBL only.
- The subject code of a note of the invoice (`notes[]->subjectCode`) is a property of its own; UBL writes it into
  the note (`#AAI#text`).
- `taxPointDateCode` holds the code of EN 16931 (UNTDID 2005: 3, 35, 432) - CII writes and reads its own (5, 29, 72).
- A contact has a name (`name`) and a department (`department`); UBL has one name for both: the department is
  written there where no person is named.

## Overview

- `invoice` (Invoice)
  - `invoice.notes` (Note) - BG-1
  - `invoice.precedingInvoices` (DocumentReference) - BG-3
  - `invoice.seller` (Party) - BG-4
    - `invoice.seller.address` (Address) - BG-5
    - `invoice.seller.contact` (Contact) - BG-6
    - `invoice.seller.additionalContacts` (Contact) - BG-6
    - `invoice.seller.legalAddress` (Address) - BG-X-14
  - `invoice.buyer` (Party) - BG-7
    - `invoice.buyer.address` (Address) - BG-8
    - `invoice.buyer.contact` (Contact) - BG-9
    - `invoice.buyer.additionalContacts` (Contact) - BG-9
    - `invoice.buyer.legalAddress` (Address) - BG-X-15
  - `invoice.payee` (Party) - BG-10
    - `invoice.payee.address` (Address) - BG-X-40
    - `invoice.payee.contact` (Contact) - BG-X-39
    - `invoice.payee.additionalContacts` (Contact) - BG-X-39
    - `invoice.payee.legalAddress` (Address) - BG-X-72
  - `invoice.sellerTaxRepresentative` (Party) - BG-11
    - `invoice.sellerTaxRepresentative.address` (Address) - BG-12
    - `invoice.sellerTaxRepresentative.contact` (Contact) - BG-X-17
    - `invoice.sellerTaxRepresentative.additionalContacts` (Contact) - BG-X-17
    - `invoice.sellerTaxRepresentative.legalAddress` (Address) - BG-X-59
  - `invoice.purchaseOrder` (DocumentReference)
  - `invoice.salesOrder` (DocumentReference)
  - `invoice.contract` (DocumentReference)
  - `invoice.project` (Project) - BT-11-00
  - `invoice.tender` (DocumentReference)
  - `invoice.invoicedObject` (DocumentReference)
  - `invoice.delivery` (Delivery)
    - `invoice.delivery.shipTo` (Party) - BG-13
      - `invoice.delivery.shipTo.address` (Address) - BG-15
      - `invoice.delivery.shipTo.contact` (Contact) - BG-X-26
      - `invoice.delivery.shipTo.additionalContacts` (Contact) - BG-X-26
      - `invoice.delivery.shipTo.legalAddress` (Address) - BG-X-67
    - `invoice.delivery.despatchAdvice` (DocumentReference)
    - `invoice.delivery.receivingAdvice` (DocumentReference)
    - `invoice.delivery.deliveryNote` (DocumentReference)
    - `invoice.delivery.ultimateShipTo` (Party) - BG-X-27
      - `invoice.delivery.ultimateShipTo.address` (Address) - BG-X-29
      - `invoice.delivery.ultimateShipTo.contact` (Contact) - BG-X-28
      - `invoice.delivery.ultimateShipTo.additionalContacts` (Contact) - BG-X-28
      - `invoice.delivery.ultimateShipTo.legalAddress` (Address) - BG-X-68
    - `invoice.delivery.shipFrom` (Party) - BG-X-30
      - `invoice.delivery.shipFrom.address` (Address) - BG-X-32
      - `invoice.delivery.shipFrom.contact` (Contact) - BG-X-31
      - `invoice.delivery.shipFrom.additionalContacts` (Contact) - BG-X-31
      - `invoice.delivery.shipFrom.legalAddress` (Address) - BG-X-69
  - `invoice.invoicingPeriod` (Period) - BG-14
  - `invoice.paymentMeans` (PaymentMeans) - BG-16
  - `invoice.directDebit` (DirectDebit) - BG-19
  - `invoice.allowances` (AllowanceCharge) - BG-20
  - `invoice.charges` (AllowanceCharge) - BG-21
  - `invoice.totals` (Totals) - BG-22
  - `invoice.vatBreakdown` (VatBreakdown) - BG-23
  - `invoice.attachments` (Attachment) - BG-24
  - `invoice.lines` (Line) - BG-25
    - `invoice.lines.classifications` (Classification) - BT-158-00
    - `invoice.lines.attributes` (ItemAttribute) - BG-32
    - `invoice.lines.period` (Period) - BG-26
    - `invoice.lines.allowances` (AllowanceCharge) - BG-27
    - `invoice.lines.charges` (AllowanceCharge) - BG-28
    - `invoice.lines.purchaseOrder` (DocumentReference)
    - `invoice.lines.subLines` (Line) - BG-DEX-01
    - `invoice.lines.additionalNotes` (Note) - BT-127-00
    - `invoice.lines.additionalPriceDiscounts` (AllowanceCharge) - BT-147-00
    - `invoice.lines.priceCharges` (AllowanceCharge) - BT-X-302-00
    - `invoice.lines.additionalTaxes` (Tax) - BG-30
    - `invoice.lines.instances` (ItemInstance) - BG-X-84
    - `invoice.lines.manufacturer` (Party) - BG-X-93
      - `invoice.lines.manufacturer.address` (Address) - BG-X-95
      - `invoice.lines.manufacturer.contact` (Contact) - BG-X-94
      - `invoice.lines.manufacturer.additionalContacts` (Contact) - BG-X-94
    - `invoice.lines.includedItems` (IncludedItem) - BG-X-1
    - `invoice.lines.additionalBuyerAccountingReferences` (AccountingReference) - BT-133-00
    - `invoice.lines.salesOrder` (DocumentReference) - BG-X-81
    - `invoice.lines.quotation` (DocumentReference) - BG-X-47
    - `invoice.lines.contract` (DocumentReference) - BG-X-2
    - `invoice.lines.ultimateCustomerOrders` (DocumentReference) - BG-X-5
    - `invoice.lines.despatchAdvice` (DocumentReference) - BG-X-13
    - `invoice.lines.receivingAdvice` (DocumentReference) - BG-X-82
    - `invoice.lines.deliveryNote` (DocumentReference) - BG-X-83
    - `invoice.lines.precedingInvoice` (DocumentReference) - BG-X-48
    - `invoice.lines.additionalDocuments` (Attachment) - BG-X-3
    - `invoice.lines.deliveryTerms` (DeliveryTerms) - BG-X-87
    - `invoice.lines.includedTax` (Tax) - BG-X-4
    - `invoice.lines.seller` (Party) - BG-X-90
      - `invoice.lines.seller.address` (Address) - BG-X-92
      - `invoice.lines.seller.contact` (Contact) - BG-X-91
      - `invoice.lines.seller.additionalContacts` (Contact) - BG-X-91
    - `invoice.lines.shipTo` (Party) - BG-X-7
      - `invoice.lines.shipTo.address` (Address) - BG-X-9
      - `invoice.lines.shipTo.contact` (Contact) - BG-X-8
      - `invoice.lines.shipTo.additionalContacts` (Contact) - BG-X-8
    - `invoice.lines.ultimateShipTo` (Party) - BG-X-10
      - `invoice.lines.ultimateShipTo.address` (Address) - BG-X-12
      - `invoice.lines.ultimateShipTo.contact` (Contact) - BG-X-11
      - `invoice.lines.ultimateShipTo.additionalContacts` (Contact) - BG-X-11
  - `invoice.thirdPartyPayments` (ThirdPartyPayment) - BG-DEX-09, BG-34
  - `invoice.additionalTenders` (DocumentReference) - BT-17-00
  - `invoice.additionalInvoicedObjects` (DocumentReference) - BT-18-00
  - `invoice.additionalBuyerAccountingReferences` (AccountingReference) - BT-19-00
  - `invoice.earlyPaymentDiscount` (PaymentCondition) - BG-X-44
  - `invoice.latePaymentPenalty` (PaymentCondition) - BG-X-43
  - `invoice.paymentTermsPayee` (Party) - BG-X-77
    - `invoice.paymentTermsPayee.address` (Address) - BG-X-79
    - `invoice.paymentTermsPayee.contact` (Contact) - BG-X-78
    - `invoice.paymentTermsPayee.additionalContacts` (Contact) - BG-X-78
    - `invoice.paymentTermsPayee.legalAddress` (Address) - BG-X-80
  - `invoice.additionalPaymentTerms` (PaymentTerms) - BT-20-00
    - `invoice.additionalPaymentTerms.earlyPaymentDiscount` (PaymentCondition) - BG-X-44
    - `invoice.additionalPaymentTerms.latePaymentPenalty` (PaymentCondition) - BG-X-43
    - `invoice.additionalPaymentTerms.payee` (Party) - BG-X-77
      - `invoice.additionalPaymentTerms.payee.address` (Address) - BG-X-79
      - `invoice.additionalPaymentTerms.payee.contact` (Contact) - BG-X-78
      - `invoice.additionalPaymentTerms.payee.additionalContacts` (Contact) - BG-X-78
      - `invoice.additionalPaymentTerms.payee.legalAddress` (Address) - BG-X-80
  - `invoice.buyerTaxRepresentative` (Party) - BG-X-54
    - `invoice.buyerTaxRepresentative.address` (Address) - BG-X-56
    - `invoice.buyerTaxRepresentative.contact` (Contact) - BG-X-55
    - `invoice.buyerTaxRepresentative.additionalContacts` (Contact) - BG-X-55
    - `invoice.buyerTaxRepresentative.legalAddress` (Address) - BG-X-57
  - `invoice.salesAgent` (Party) - BG-X-49
    - `invoice.salesAgent.address` (Address) - BG-X-52
    - `invoice.salesAgent.contact` (Contact) - BG-X-51
    - `invoice.salesAgent.additionalContacts` (Contact) - BG-X-51
    - `invoice.salesAgent.legalAddress` (Address) - BG-X-53
  - `invoice.buyerAgent` (Party) - BG-X-62
    - `invoice.buyerAgent.address` (Address) - BG-X-65
    - `invoice.buyerAgent.contact` (Contact) - BG-X-64
    - `invoice.buyerAgent.additionalContacts` (Contact) - BG-X-64
    - `invoice.buyerAgent.legalAddress` (Address) - BG-X-66
  - `invoice.productEndUser` (Party) - BG-X-18
    - `invoice.productEndUser.address` (Address) - BG-X-21
    - `invoice.productEndUser.contact` (Contact) - BG-X-20
    - `invoice.productEndUser.additionalContacts` (Contact) - BG-X-20
    - `invoice.productEndUser.legalAddress` (Address) - BG-X-60
  - `invoice.invoicer` (Party) - BG-X-33
    - `invoice.invoicer.address` (Address) - BG-X-35
    - `invoice.invoicer.contact` (Contact) - BG-X-34
    - `invoice.invoicer.additionalContacts` (Contact) - BG-X-34
    - `invoice.invoicer.legalAddress` (Address) - BG-X-70
  - `invoice.invoicee` (Party) - BG-X-36
    - `invoice.invoicee.address` (Address) - BG-X-38
    - `invoice.invoicee.contact` (Contact) - BG-X-37
    - `invoice.invoicee.additionalContacts` (Contact) - BG-X-37
    - `invoice.invoicee.legalAddress` (Address) - BG-X-71
  - `invoice.payer` (Party) - BG-X-73
    - `invoice.payer.address` (Address) - BG-X-75
    - `invoice.payer.contact` (Contact) - BG-X-74
    - `invoice.payer.additionalContacts` (Contact) - BG-X-74
    - `invoice.payer.legalAddress` (Address) - BG-X-76
  - `invoice.quotation` (DocumentReference) - BG-X-61
  - `invoice.ultimateCustomerOrders` (DocumentReference) - BG-X-23
  - `invoice.deliveryTerms` (DeliveryTerms) - BG-X-22
  - `invoice.logisticsServiceCharges` (LogisticsServiceCharge) - BG-X-42
    - `invoice.logisticsServiceCharges.taxes` (Tax) - BT-X-273-00
  - `invoice.currencyExchange` (CurrencyExchange) - BG-X-41
  - `invoice.advancePayments` (AdvancePayment) - BG-X-45
    - `invoice.advancePayments.taxes` (Tax) - BG-X-46
    - `invoice.advancePayments.precedingInvoice` (DocumentReference) - BG-X-85

## `invoice` - Invoice

| Property | Value | ID | Name | Occurs | Profiles | UBL |
|---|---|---|---|---|---|---|
| `number` | Identifier | BT-1 | Invoice number | 1..1 | MWBEXRP | CRP |
| `typeCode` | Code | BT-3 | Invoice type | 1..1 | MWBEXRP | CRP |
| `issueDate` | Date | BT-2 | Invoice date | 1..1 | MWBEXRP | CRP |
| `currency` | Code | BT-5 | Invoice currency | 1..1 | MWBEXRP | CRP |
| `vatCurrency` | Code | BT-6 | VAT currency | 0..1 | WBEXRP | CRP |
| `taxPointDate` | Date | BT-7 | Tax point date | 1..1 | EXRP | CRP |
| `taxPointDateCode` | Code | BT-8 | Tax point date code | 0..1 | WBEXRP | CRP |
| `dueDate` | Date | BT-9 | Due date | 1..1 | WBEXRP | CRP |
| `buyerReference` | Text | BT-10 | Buyer reference | 0..1 | MWBEXRP | CRP |
| `buyerAccountingReference` | Text | BT-19 | Buyer accounting reference | 1..1 | WBEXRP | CRP |
| `paymentTerms` | Text | BT-20 | Payment terms | 0..1 | WBEXRP | CRP |
| `paymentReference` | Text | BT-83 | Payment reference | 0..1 | WBEXRP | CRP |
| `businessProcess` | Text | BT-23 | Business process | 1..1 | MWBEXRP | CRP |
| `specification` | Identifier | BT-24 | Specification identifier | 1..1 | MWBEXRP | CRP |
| `notes[]` | Note[] | BG-1 | Note | 0..n | WBEXRP | CRP |
| `precedingInvoices[]` | DocumentReference[] | BG-3 | Preceding invoice | 0..n | WBEXRP | CRP |
| `seller` | Party | BG-4 | Seller | 1..1 | MWBEXRP | CRP |
| `buyer` | Party | BG-7 | Buyer | 1..1 | MWBEXRP | CRP |
| `payee` | Party | BG-10 | Payee | 0..1 | WBEXRP | CRP |
| `sellerTaxRepresentative` | Party | BG-11 | Seller tax representative | 0..1 | WBEXRP | CRP |
| `purchaseOrder` | DocumentReference |  |  |  |  | CRP |
| `salesOrder` | DocumentReference |  |  |  |  | CRP |
| `contract` | DocumentReference |  |  |  |  | CRP |
| `project` | Project | BT-11-00 | Project | 0..1 | EXRP | CRP |
| `tender` | DocumentReference |  |  |  |  | CRP |
| `invoicedObject` | DocumentReference |  |  |  |  | CRP |
| `delivery` | Delivery |  |  |  |  | CRP |
| `invoicingPeriod` | Period | BG-14 | Invoicing period | 0..1 | WBEXRP | CRP |
| `paymentMeans[]` | PaymentMeans[] | BG-16 | Payment instructions | 0..n | WBEXRP | CRP |
| `directDebit` | DirectDebit | BG-19 | Direct debit | 1..1 | MWBEXRP | CRP |
| `allowances[]` | AllowanceCharge[] | BG-20 | Allowance | 0..n | WBEXRP | CRP |
| `charges[]` | AllowanceCharge[] | BG-21 | Charge | 0..n | WBEXRP | CRP |
| `totals` | Totals | BG-22 | Totals | 1..1 | MWBEXRP | CRP |
| `vatBreakdown[]` | VatBreakdown[] | BG-23 | VAT breakdown | 1..n | WBEXRP | CRP |
| `attachments[]` | Attachment[] | BG-24 | Attachment | 0..n | EXRP | CRP |
| `lines[]` | Line[] | BG-25 | Line | 1..n | BEXRP | CRP |
| `thirdPartyPayments[]` | ThirdPartyPayment[] | BG-DEX-09, BG-34 | Third party payment / Third party payment | 0..n | X | R |
| `testIndicator` | Yes/no | BT-X-1 | Test indicator | 1..1 | X |  |
| `copyIndicator` | Yes/no | BT-X-3 | Copy indicator | 1..1 | X |  |
| `documentName` | Text | BT-X-2 | Document name | 0..1 | X |  |
| `languageCode` | Code | BT-X-4 | Language | 0..1 | X |  |
| `contractualDueDate` | Date | BT-X-6 | Contractual due date | 1..1 | X |  |
| `sellerReference` | Text | BT-X-204 | Seller reference | 0..1 | X |  |
| `additionalTenders[]` | DocumentReference[] | BT-17-00 | Tender or lot | 0..n | EXRP |  |
| `additionalInvoicedObjects[]` | DocumentReference[] | BT-18-00 | Invoiced object | 0..n | EXRP |  |
| `buyerAccountingReferenceTypeCode` | Code | BT-X-290 | Accounting reference type | 0..1 | X |  |
| `additionalBuyerAccountingReferences[]` | AccountingReference[] | BT-19-00 | Buyer accounting reference | 0..n | X |  |
| `partialPaymentAmount` | Amount | BT-X-275 | Partial payment amount | 0..1 | X |  |
| `earlyPaymentDiscount` | PaymentCondition | BG-X-44 | Early payment discount | 0..1 | X |  |
| `latePaymentPenalty` | PaymentCondition | BG-X-43 | Late payment penalty | 0..1 | X |  |
| `paymentTermsPayee` | Party | BG-X-77 | Payee of the payment terms | 0..1 | X |  |
| `additionalPaymentTerms[]` | PaymentTerms[] | BT-20-00 | Payment terms | 0..n | X |  |
| `buyerTaxRepresentative` | Party | BG-X-54 | Buyer tax representative | 0..1 | X |  |
| `salesAgent` | Party | BG-X-49 | Sales agent | 0..1 | X |  |
| `buyerAgent` | Party | BG-X-62 | Buyer agent | 0..1 | X |  |
| `productEndUser` | Party | BG-X-18 | Product end user | 0..1 | X |  |
| `invoicer` | Party | BG-X-33 | Invoicer | 0..1 | X |  |
| `invoicee` | Party | BG-X-36 | Invoicee | 0..1 | X |  |
| `payer` | Party | BG-X-73 | Payer | 0..1 | X |  |
| `quotation` | DocumentReference | BG-X-61 | Quotation | 0..1 | X |  |
| `ultimateCustomerOrders[]` | DocumentReference[] | BG-X-23 | Ultimate customer order | 0..n | X |  |
| `deliveryTerms` | DeliveryTerms | BG-X-22 | Delivery terms | 0..1 | X |  |
| `logisticsServiceCharges[]` | LogisticsServiceCharge[] | BG-X-42 | Logistics service charge | 0..n | X |  |
| `currencyExchange` | CurrencyExchange | BG-X-41 | Currency exchange | 0..1 | X |  |
| `advancePayments[]` | AdvancePayment[] | BG-X-45 | Advance payment | 0..n | X |  |

## `invoice.notes` - Note (BG-1)

| Property | Value | ID | Name | Occurs | Profiles | UBL |
|---|---|---|---|---|---|---|
| `text` | Text | BT-22 | Note › Text | 0..1 | WBEXRP | CRP |
| `subjectCode` | Code | BT-21 | Note › Subject code | 0..1 | WBEXRP | CRP |
| `contentCode` | Code | BT-X-5 | Note › Content code | 0..1 | X |  |

## `invoice.precedingInvoices` - DocumentReference (BG-3)

| Property | Value | ID | Name | Occurs | Profiles | UBL |
|---|---|---|---|---|---|---|
| `number` | Document number | BT-25 | Preceding invoice › Number | 0..1 | WBEXRP | CRP |
| `date` | Date | BT-26 | Preceding invoice › Date | 1..1 | WBEXRP | CRP |
| `typeCode` | Code | BT-X-555 | Preceding invoice › Type | 0..1 | X |  |

## `invoice.seller` - Party (BG-4)

| Property | Value | ID | Name | Occurs | Profiles | UBL |
|---|---|---|---|---|---|---|
| `name` | Text | BT-27 | Seller › Name | 0..1 | MWBEXRP | CRP |
| `tradingName` | Text | BT-28 | Seller › Trading name | 0..1 | WBEXRP | CRP |
| `identifiers[].value` | Identifier | BT-29 | Seller › Identifier | 0..n | WBEXRP | CRP |
| `identifiers[].scheme` | Text | BT-29-1 | Seller › Identifier › Scheme | 0..1 | WBEXRP | CRP |
| `legalRegistrationId.value` | Identifier | BT-30 | Seller › Legal registration identifier | 0..1 | MWBEXRP | CRP |
| `legalRegistrationId.scheme` | Text | BT-30-1 | Seller › Legal registration identifier › Scheme | 0..1 | MWBEXRP | CRP |
| `vatId` | Identifier | BT-31 | Seller › VAT identifier | 1..1 | MWBEXRP | CRP |
| `taxNumber` | Identifier | BT-32 | Seller › Tax number | 1..1 | MWBEXRP | CRP |
| `legalInformation` | Text | BT-33 | Seller › Legal information | 0..1 | EXRP | CRP |
| `electronicAddress.value` | Identifier | BT-34 | Seller › Electronic address | 0..1 | WBEXRP | CRP |
| `electronicAddress.scheme` | Text | BT-34-1 | Seller › Electronic address › Scheme | 0..1 | WBEXRP | CRP |
| `address` | Address | BG-5 | Seller › Address | 0..1 | MWBEXRP | CRP |
| `contact` | Contact | BG-6 | Seller › Contact | 0..n | EXRP | CRP |
| `additionalContacts[]` | Contact[] | BG-6 | Seller › Contact | 0..n | X |  |
| `roleCode` | Code | BT-X-543 | Seller › Role | 0..1 | X |  |
| `legalAddress` | Address | BG-X-14 | Seller › Registered address | 0..1 | X |  |

## `invoice.seller.address` - Address (BG-5)

| Property | Value | ID | Name | Occurs | Profiles | UBL |
|---|---|---|---|---|---|---|
| `line1` | Text | BT-35 | Seller › Address › Address line 1 | 0..1 | WBEXRP | CRP |
| `line2` | Text | BT-36 | Seller › Address › Address line 2 | 0..1 | WBEXRP | CRP |
| `line3` | Text | BT-162 | Seller › Address › Address line 3 | 0..1 | WBEXRP | CRP |
| `postcode` | Text | BT-38 | Seller › Address › Post code | 0..1 | WBEXRP | CRP |
| `city` | Text | BT-37 | Seller › Address › City | 0..1 | WBEXRP | CRP |
| `subdivision` | Text | BT-39 | Seller › Address › Country subdivision | 0..1 | WBEXRP | CRP |
| `country` | Code | BT-40 | Seller › Address › Country | 1..1 | MWBEXRP | CRP |

## `invoice.seller.contact` - Contact (BG-6)

The contact point is a person (`name`) or a department (`department`), not both. UBL has one element for the two: a department is written as the name and read back as the name, a department next to a name is refused. In CII the rules refuse the two together, even spread over several contacts (CII-SR-465 for the seller, CII-SR-466 for the buyer; Peppol only warns).

| Property | Value | ID | Name | Occurs | Profiles | UBL |
|---|---|---|---|---|---|---|
| `name` | Text | BT-41 | Seller › Contact › Name | 0..1 | EXRP | CRP |
| `department` | Text | BT-41-0 | Seller › Contact › Department | 0..1 | EXRP | CRP (as BT-41) |
| `phone` | Text | BT-42 | Seller › Contact › Phone | 0..1 | EXRP | CRP |
| `fax` | Text | BT-X-107 | Seller › Contact › Fax | 0..1 | X |  |
| `email` | Text | BT-43 | Seller › Contact › Email | 0..1 | EXRP | CRP |
| `typeCode` | Code | BT-X-317 | Seller › Contact › Type | 0..1 | X |  |

## `invoice.seller.additionalContacts` - Contact (BG-6)

More contacts (EXTENDED) - the first is `contact`.

| Property | Value | ID | Name | Occurs | Profiles | UBL |
|---|---|---|---|---|---|---|
| `name` | Text | BT-41 | Seller › Contact › Name | 0..1 | EXRP | CRP |
| `department` | Text | BT-41-0 | Seller › Contact › Department | 0..1 | EXRP | CRP (as BT-41) |
| `phone` | Text | BT-42 | Seller › Contact › Phone | 0..1 | EXRP | CRP |
| `fax` | Text | BT-X-107 | Seller › Contact › Fax | 0..1 | X |  |
| `email` | Text | BT-43 | Seller › Contact › Email | 0..1 | EXRP | CRP |
| `typeCode` | Code | BT-X-317 | Seller › Contact › Type | 0..1 | X |  |

## `invoice.seller.legalAddress` - Address (BG-X-14)

| Property | Value | ID | Name | Occurs | Profiles | UBL |
|---|---|---|---|---|---|---|
| `line1` | Text | BT-X-101 | Seller › Registered address › Address line 1 | 0..1 | X |  |
| `line2` | Text | BT-X-102 | Seller › Registered address › Address line 2 | 0..1 | X |  |
| `line3` | Text | BT-X-103 | Seller › Registered address › Address line 3 | 0..1 | X |  |
| `postcode` | Text | BT-X-100 | Seller › Registered address › Post code | 0..1 | X |  |
| `city` | Text | BT-X-104 | Seller › Registered address › City | 0..1 | X |  |
| `subdivision` | Text | BT-X-106 | Seller › Registered address › Country subdivision | 0..1 | X |  |
| `country` | Code | BT-X-105 | Seller › Registered address › Country | 1..1 | X |  |

## `invoice.buyer` - Party (BG-7)

| Property | Value | ID | Name | Occurs | Profiles | UBL |
|---|---|---|---|---|---|---|
| `name` | Text | BT-44 | Buyer › Name | 0..1 | MWBEXRP | CRP |
| `tradingName` | Text | BT-45 | Buyer › Trading name | 0..1 | WBEXRP | CRP |
| `identifiers[].value` | Identifier | BT-46 | Buyer › Identifier | 0..1 | WBEXRP | CRP |
| `identifiers[].scheme` | Text | BT-46-1 | Buyer › Identifier › Scheme | 0..1 | WBEXRP | CRP |
| `legalRegistrationId.value` | Identifier | BT-47 | Buyer › Legal registration identifier | 0..1 | MWBEXRP | CRP |
| `legalRegistrationId.scheme` | Text | BT-47-1 | Buyer › Legal registration identifier › Scheme | 0..1 | MWBEXRP | CRP |
| `vatId` | Identifier | BT-48 | Buyer › VAT identifier | 1..1 | MWBEXRP | CRP |
| `legalInformation` | Text | BT-X-334 | Buyer › Legal information | 0..1 | X |  |
| `electronicAddress.value` | Identifier | BT-49 | Buyer › Electronic address | 0..1 | WBEXRP | CRP |
| `electronicAddress.scheme` | Text | BT-49-1 | Buyer › Electronic address › Scheme | 0..1 | WBEXRP | CRP |
| `address` | Address | BG-8 | Buyer › Address | 0..1 | MWBEXRP | CRP |
| `contact` | Contact | BG-9 | Buyer › Contact | 0..n | EXRP | CRP |
| `additionalContacts[]` | Contact[] | BG-9 | Buyer › Contact | 0..n | X |  |
| `roleCode` | Code | BT-X-544 | Buyer › Role | 0..1 | X |  |
| `legalAddress` | Address | BG-X-15 | Buyer › Registered address | 0..1 | X |  |

## `invoice.buyer.address` - Address (BG-8)

| Property | Value | ID | Name | Occurs | Profiles | UBL |
|---|---|---|---|---|---|---|
| `line1` | Text | BT-50 | Buyer › Address › Address line 1 | 0..1 | WBEXRP | CRP |
| `line2` | Text | BT-51 | Buyer › Address › Address line 2 | 0..1 | WBEXRP | CRP |
| `line3` | Text | BT-163 | Buyer › Address › Address line 3 | 0..1 | WBEXRP | CRP |
| `postcode` | Text | BT-53 | Buyer › Address › Post code | 0..1 | WBEXRP | CRP |
| `city` | Text | BT-52 | Buyer › Address › City | 0..1 | WBEXRP | CRP |
| `subdivision` | Text | BT-54 | Buyer › Address › Country subdivision | 0..1 | WBEXRP | CRP |
| `country` | Code | BT-55 | Buyer › Address › Country | 1..1 | MWBEXRP | CRP |

## `invoice.buyer.contact` - Contact (BG-9)

The contact point is a person (`name`) or a department (`department`), not both. UBL has one element for the two: a department is written as the name and read back as the name, a department next to a name is refused. In CII the rules refuse the two together, even spread over several contacts (CII-SR-465 for the seller, CII-SR-466 for the buyer; Peppol only warns).

| Property | Value | ID | Name | Occurs | Profiles | UBL |
|---|---|---|---|---|---|---|
| `name` | Text | BT-56 | Buyer › Contact › Name | 0..1 | EXRP | CRP |
| `department` | Text | BT-56-0 | Buyer › Contact › Department | 0..1 | EXRP | CRP (as BT-56) |
| `phone` | Text | BT-57 | Buyer › Contact › Phone | 0..1 | EXRP | CRP |
| `fax` | Text | BT-X-115 | Buyer › Contact › Fax | 0..1 | X |  |
| `email` | Text | BT-58 | Buyer › Contact › Email | 0..1 | EXRP | CRP |
| `typeCode` | Code | BT-X-318 | Buyer › Contact › Type | 0..1 | X |  |

## `invoice.buyer.additionalContacts` - Contact (BG-9)

More contacts (EXTENDED) - the first is `contact`.

| Property | Value | ID | Name | Occurs | Profiles | UBL |
|---|---|---|---|---|---|---|
| `name` | Text | BT-56 | Buyer › Contact › Name | 0..1 | EXRP | CRP |
| `department` | Text | BT-56-0 | Buyer › Contact › Department | 0..1 | EXRP | CRP (as BT-56) |
| `phone` | Text | BT-57 | Buyer › Contact › Phone | 0..1 | EXRP | CRP |
| `fax` | Text | BT-X-115 | Buyer › Contact › Fax | 0..1 | X |  |
| `email` | Text | BT-58 | Buyer › Contact › Email | 0..1 | EXRP | CRP |
| `typeCode` | Code | BT-X-318 | Buyer › Contact › Type | 0..1 | X |  |

## `invoice.buyer.legalAddress` - Address (BG-X-15)

| Property | Value | ID | Name | Occurs | Profiles | UBL |
|---|---|---|---|---|---|---|
| `line1` | Text | BT-X-109 | Buyer › Registered address › Address line 1 | 0..1 | X |  |
| `line2` | Text | BT-X-110 | Buyer › Registered address › Address line 2 | 0..1 | X |  |
| `line3` | Text | BT-X-111 | Buyer › Registered address › Address line 3 | 0..1 | X |  |
| `postcode` | Text | BT-X-108 | Buyer › Registered address › Post code | 0..1 | X |  |
| `city` | Text | BT-X-112 | Buyer › Registered address › City | 0..1 | X |  |
| `subdivision` | Text | BT-X-114 | Buyer › Registered address › Country subdivision | 0..1 | X |  |
| `country` | Code | BT-X-113 | Buyer › Registered address › Country | 1..1 | X |  |

## `invoice.payee` - Party (BG-10)

| Property | Value | ID | Name | Occurs | Profiles | UBL |
|---|---|---|---|---|---|---|
| `name` | Text | BT-59 | Payee › Name | 0..1 | WBEXRP | CRP |
| `tradingName` | Text | BT-X-243 | Payee › Trading name | 0..1 | X |  |
| `identifiers[].value` | Identifier | BT-60 | Payee › Identifier | 0..1 | WBEXRP | CRP |
| `identifiers[].scheme` | Text | BT-60-1 | Payee › Identifier › Scheme | 0..1 | WBEXRP | CRP |
| `legalRegistrationId.value` | Identifier | BT-61 | Payee › Legal registration identifier | 0..1 | WBEXRP | CRP |
| `legalRegistrationId.scheme` | Text | BT-61-1 | Payee › Legal registration identifier › Scheme | 0..1 | WBEXRP | CRP |
| `vatId` | Identifier | BT-X-257 | Payee › VAT identifier | 1..1 | X |  |
| `electronicAddress.value` | Identifier | BT-X-256 | Payee › Electronic address | 0..1 | X |  |
| `electronicAddress.scheme` | Text | BT-X-256-0 | Payee › Electronic address › Scheme | 0..1 | X |  |
| `address` | Address | BG-X-40 | Payee › Address | 0..1 | X |  |
| `contact` | Contact | BG-X-39 | Payee › Contact | 0..n | X |  |
| `additionalContacts[]` | Contact[] | BG-X-39 | Payee › Contact | 0..n | X |  |
| `roleCode` | Code | BT-X-468 | Payee › Role | 0..1 | X |  |
| `legalAddress` | Address | BG-X-72 | Payee › Registered address | 0..1 | X |  |

## `invoice.payee.address` - Address (BG-X-40)

| Property | Value | ID | Name | Occurs | Profiles | UBL |
|---|---|---|---|---|---|---|
| `line1` | Text | BT-X-250 | Payee › Address › Address line 1 | 0..1 | X |  |
| `line2` | Text | BT-X-251 | Payee › Address › Address line 2 | 0..1 | X |  |
| `line3` | Text | BT-X-252 | Payee › Address › Address line 3 | 0..1 | X |  |
| `postcode` | Text | BT-X-249 | Payee › Address › Post code | 0..1 | X |  |
| `city` | Text | BT-X-253 | Payee › Address › City | 0..1 | X |  |
| `subdivision` | Text | BT-X-255 | Payee › Address › Country subdivision | 0..1 | X |  |
| `country` | Code | BT-X-254 | Payee › Address › Country | 1..1 | X |  |

## `invoice.payee.contact` - Contact (BG-X-39)

| Property | Value | ID | Name | Occurs | Profiles | UBL |
|---|---|---|---|---|---|---|
| `name` | Text | BT-X-244 | Payee › Contact › Name | 0..1 | X |  |
| `department` | Text | BT-X-245 | Payee › Contact › Department | 0..1 | X |  |
| `phone` | Text | BT-X-246 | Payee › Contact › Phone | 0..1 | X |  |
| `fax` | Text | BT-X-247 | Payee › Contact › Fax | 0..1 | X |  |
| `email` | Text | BT-X-248 | Payee › Contact › Email | 0..1 | X |  |
| `typeCode` | Code | BT-X-326 | Payee › Contact › Type | 0..1 | X |  |

## `invoice.payee.additionalContacts` - Contact (BG-X-39)

More contacts (EXTENDED) - the first is `contact`.

| Property | Value | ID | Name | Occurs | Profiles | UBL |
|---|---|---|---|---|---|---|
| `name` | Text | BT-X-244 | Payee › Contact › Name | 0..1 | X |  |
| `department` | Text | BT-X-245 | Payee › Contact › Department | 0..1 | X |  |
| `phone` | Text | BT-X-246 | Payee › Contact › Phone | 0..1 | X |  |
| `fax` | Text | BT-X-247 | Payee › Contact › Fax | 0..1 | X |  |
| `email` | Text | BT-X-248 | Payee › Contact › Email | 0..1 | X |  |
| `typeCode` | Code | BT-X-326 | Payee › Contact › Type | 0..1 | X |  |

## `invoice.payee.legalAddress` - Address (BG-X-72)

| Property | Value | ID | Name | Occurs | Profiles | UBL |
|---|---|---|---|---|---|---|
| `line1` | Text | BT-X-470 | Payee › Registered address › Address line 1 | 0..1 | X |  |
| `line2` | Text | BT-X-471 | Payee › Registered address › Address line 2 | 0..1 | X |  |
| `line3` | Text | BT-X-472 | Payee › Registered address › Address line 3 | 0..1 | X |  |
| `postcode` | Text | BT-X-469 | Payee › Registered address › Post code | 0..1 | X |  |
| `city` | Text | BT-X-473 | Payee › Registered address › City | 0..1 | X |  |
| `subdivision` | Text | BT-X-475 | Payee › Registered address › Country subdivision | 0..1 | X |  |
| `country` | Code | BT-X-474 | Payee › Registered address › Country | 1..1 | X |  |

## `invoice.sellerTaxRepresentative` - Party (BG-11)

| Property | Value | ID | Name | Occurs | Profiles | UBL |
|---|---|---|---|---|---|---|
| `name` | Text | BT-62 | Seller tax representative › Name | 0..1 | WBEXRP | CRP |
| `tradingName` | Text | BT-X-119 | Seller tax representative › Trading name | 0..1 | X |  |
| `identifiers[].value` | Identifier | BT-X-116, BT-X-117 | Seller tax representative › Identifier | 0..1 / 0..n | X |  |
| `identifiers[].scheme` | Text | BT-X-117-1 | Seller tax representative › Identifier › Scheme | 0..1 | X |  |
| `legalRegistrationId.value` | Identifier | BT-X-118 | Seller tax representative › Legal registration identifier | 0..1 | X |  |
| `legalRegistrationId.scheme` | Text | BT-X-118-0 | Seller tax representative › Legal registration identifier › Scheme | 0..1 | X |  |
| `vatId` | Identifier | BT-63 | Seller tax representative › VAT identifier | 1..1 | WBEXRP | CRP |
| `electronicAddress.value` | Identifier | BT-X-125 | Seller tax representative › Electronic address | 0..1 | X |  |
| `electronicAddress.scheme` | Text | BT-X-125-0 | Seller tax representative › Electronic address › Scheme | 0..1 | X |  |
| `address` | Address | BG-12 | Seller tax representative › Address | 0..1 | WBEXRP | CRP |
| `contact` | Contact | BG-X-17 | Seller tax representative › Contact | 0..n | X |  |
| `additionalContacts[]` | Contact[] | BG-X-17 | Seller tax representative › Contact | 0..n | X |  |
| `roleCode` | Code | BT-X-547 | Seller tax representative › Role | 0..1 | X |  |
| `legalAddress` | Address | BG-X-59 | Seller tax representative › Registered address | 0..1 | X |  |

## `invoice.sellerTaxRepresentative.address` - Address (BG-12)

| Property | Value | ID | Name | Occurs | Profiles | UBL |
|---|---|---|---|---|---|---|
| `line1` | Text | BT-64 | Seller tax representative › Address › Address line 1 | 0..1 | WBEXRP | CRP |
| `line2` | Text | BT-65 | Seller tax representative › Address › Address line 2 | 0..1 | WBEXRP | CRP |
| `line3` | Text | BT-164 | Seller tax representative › Address › Address line 3 | 0..1 | WBEXRP | CRP |
| `postcode` | Text | BT-67 | Seller tax representative › Address › Post code | 0..1 | WBEXRP | CRP |
| `city` | Text | BT-66 | Seller tax representative › Address › City | 0..1 | WBEXRP | CRP |
| `subdivision` | Text | BT-68 | Seller tax representative › Address › Country subdivision | 0..1 | WBEXRP | CRP |
| `country` | Code | BT-69 | Seller tax representative › Address › Country | 1..1 | WBEXRP | CRP |

## `invoice.sellerTaxRepresentative.contact` - Contact (BG-X-17)

| Property | Value | ID | Name | Occurs | Profiles | UBL |
|---|---|---|---|---|---|---|
| `name` | Text | BT-X-120 | Seller tax representative › Contact › Name | 0..1 | X |  |
| `department` | Text | BT-X-121 | Seller tax representative › Contact › Department | 0..1 | X |  |
| `phone` | Text | BT-X-122 | Seller tax representative › Contact › Phone | 0..1 | X |  |
| `fax` | Text | BT-X-123 | Seller tax representative › Contact › Fax | 0..1 | X |  |
| `email` | Text | BT-X-124 | Seller tax representative › Contact › Email | 0..1 | X |  |
| `typeCode` | Code | BT-X-319 | Seller tax representative › Contact › Type | 0..1 | X |  |

## `invoice.sellerTaxRepresentative.additionalContacts` - Contact (BG-X-17)

More contacts (EXTENDED) - the first is `contact`.

| Property | Value | ID | Name | Occurs | Profiles | UBL |
|---|---|---|---|---|---|---|
| `name` | Text | BT-X-120 | Seller tax representative › Contact › Name | 0..1 | X |  |
| `department` | Text | BT-X-121 | Seller tax representative › Contact › Department | 0..1 | X |  |
| `phone` | Text | BT-X-122 | Seller tax representative › Contact › Phone | 0..1 | X |  |
| `fax` | Text | BT-X-123 | Seller tax representative › Contact › Fax | 0..1 | X |  |
| `email` | Text | BT-X-124 | Seller tax representative › Contact › Email | 0..1 | X |  |
| `typeCode` | Code | BT-X-319 | Seller tax representative › Contact › Type | 0..1 | X |  |

## `invoice.sellerTaxRepresentative.legalAddress` - Address (BG-X-59)

| Property | Value | ID | Name | Occurs | Profiles | UBL |
|---|---|---|---|---|---|---|
| `line1` | Text | BT-X-390 | Seller tax representative › Registered address › Address line 1 | 0..1 | X |  |
| `line2` | Text | BT-X-391 | Seller tax representative › Registered address › Address line 2 | 0..1 | X |  |
| `line3` | Text | BT-X-392 | Seller tax representative › Registered address › Address line 3 | 0..1 | X |  |
| `postcode` | Text | BT-X-389 | Seller tax representative › Registered address › Post code | 0..1 | X |  |
| `city` | Text | BT-X-393 | Seller tax representative › Registered address › City | 0..1 | X |  |
| `subdivision` | Text | BT-X-395 | Seller tax representative › Registered address › Country subdivision | 0..1 | X |  |
| `country` | Code | BT-X-394 | Seller tax representative › Registered address › Country | 1..1 | X |  |

## `invoice.purchaseOrder` - DocumentReference

| Property | Value | ID | Name | Occurs | Profiles | UBL |
|---|---|---|---|---|---|---|
| `number` | Document number | BT-13 | Purchase order › Number | 0..1 | MWBEXRP | CRP |
| `date` | Date | BT-X-147 | Purchase order › Date | 1..1 | X |  |

## `invoice.salesOrder` - DocumentReference

| Property | Value | ID | Name | Occurs | Profiles | UBL |
|---|---|---|---|---|---|---|
| `number` | Document number | BT-14 | Sales order › Number | 0..1 | EXRP | CRP |
| `date` | Date | BT-X-146 | Sales order › Date | 1..1 | X |  |

## `invoice.contract` - DocumentReference

| Property | Value | ID | Name | Occurs | Profiles | UBL |
|---|---|---|---|---|---|---|
| `number` | Document number | BT-12 | Contract › Number | 0..1 | WBEXRP | CRP |
| `date` | Date | BT-X-148 | Contract › Date | 1..1 | X |  |
| `typeCode` | Code | BT-X-405 | Contract › Type | 0..1 | X |  |

## `invoice.project` - Project (BT-11-00)

| Property | Value | ID | Name | Occurs | Profiles | UBL |
|---|---|---|---|---|---|---|
| `id` | Document number | BT-11 | Project › Identifier | 1..1 | EXRP | CRP |
| `name` | Text | BT-11-0 | Project › Name | 1..1 | EXRP |  |

## `invoice.tender` - DocumentReference

| Property | Value | ID | Name | Occurs | Profiles | UBL |
|---|---|---|---|---|---|---|
| `number` | Document number | BT-17 | Tender or lot › Number | 0..1 | EXRP | CRP |
| `date` | Date | BT-X-556 | Tender or lot › Date | 1..1 | X |  |

## `invoice.invoicedObject` - DocumentReference

| Property | Value | ID | Name | Occurs | Profiles | UBL |
|---|---|---|---|---|---|---|
| `number` | Identifier | BT-18 | Invoiced object › Number | 0..1 | EXRP | CRP |
| `date` | Date | BT-X-557 | Invoiced object › Date | 1..1 | X |  |
| `scheme` | Text | BT-18-1 | Invoiced object › Scheme | 0..1 | EXRP | CRP |

## `invoice.delivery` - Delivery

| Property | Value | ID | Name | Occurs | Profiles | UBL |
|---|---|---|---|---|---|---|
| `date` | Date | BT-72 | Delivery › Date | 1..1 | WBEXRP | CRP |
| `shipTo` | Party | BG-13 | Delivery › Ship-to party | 0..1 | WBEXRP | CRP |
| `despatchAdvice` | DocumentReference |  |  |  |  | CRP |
| `receivingAdvice` | DocumentReference |  |  |  |  | CRP |
| `deliveryNote` | DocumentReference |  |  |  |  |  |
| `transportModeCodes[]` | Text | BT-X-152 | Delivery › Transport mode | 1..1 | X |  |
| `ultimateShipTo` | Party | BG-X-27 | Delivery › Ultimate ship-to party | 0..1 | X |  |
| `shipFrom` | Party | BG-X-30 | Delivery › Ship-from party | 0..1 | X |  |

## `invoice.delivery.shipTo` - Party (BG-13)

| Property | Value | ID | Name | Occurs | Profiles | UBL |
|---|---|---|---|---|---|---|
| `name` | Text | BT-70 | Delivery › Ship-to party › Name | 0..1 | WBEXRP | CRP |
| `tradingName` | Text | BT-X-154 | Delivery › Ship-to party › Trading name | 0..1 | X |  |
| `identifiers[].value` | Identifier | BT-71 | Delivery › Ship-to party › Identifier | 0..1 | WBEXRP | CRP |
| `identifiers[].scheme` | Text | BT-71-1 | Delivery › Ship-to party › Identifier › Scheme | 0..1 | WBEXRP | CRP |
| `legalRegistrationId.value` | Identifier | BT-X-153 | Delivery › Ship-to party › Legal registration identifier | 0..1 | X |  |
| `legalRegistrationId.scheme` | Text | BT-X-153-0 | Delivery › Ship-to party › Legal registration identifier › Scheme | 0..1 | X |  |
| `vatId` | Identifier | BT-X-161 | Delivery › Ship-to party › VAT identifier | 1..1 | X |  |
| `electronicAddress.value` | Identifier | BT-X-160 | Delivery › Ship-to party › Electronic address | 0..1 | X |  |
| `electronicAddress.scheme` | Text | BT-X-160-0 | Delivery › Ship-to party › Electronic address › Scheme | 0..1 | X |  |
| `address` | Address | BG-15 | Delivery › Ship-to party › Address | 0..1 | WBEXRP | CRP |
| `contact` | Contact | BG-X-26 | Delivery › Ship-to party › Contact | 0..n | X |  |
| `additionalContacts[]` | Contact[] | BG-X-26 | Delivery › Ship-to party › Contact | 0..n | X |  |
| `roleCode` | Code | BT-X-550 | Delivery › Ship-to party › Role | 0..1 | X |  |
| `legalAddress` | Address | BG-X-67 | Delivery › Ship-to party › Registered address | 0..1 | X |  |

## `invoice.delivery.shipTo.address` - Address (BG-15)

| Property | Value | ID | Name | Occurs | Profiles | UBL |
|---|---|---|---|---|---|---|
| `line1` | Text | BT-75 | Delivery › Ship-to party › Address › Address line 1 | 0..1 | WBEXRP | CRP |
| `line2` | Text | BT-76 | Delivery › Ship-to party › Address › Address line 2 | 0..1 | WBEXRP | CRP |
| `line3` | Text | BT-165 | Delivery › Ship-to party › Address › Address line 3 | 0..1 | WBEXRP | CRP |
| `postcode` | Text | BT-78 | Delivery › Ship-to party › Address › Post code | 0..1 | WBEXRP | CRP |
| `city` | Text | BT-77 | Delivery › Ship-to party › Address › City | 0..1 | WBEXRP | CRP |
| `subdivision` | Text | BT-79 | Delivery › Ship-to party › Address › Country subdivision | 0..1 | WBEXRP | CRP |
| `country` | Code | BT-80 | Delivery › Ship-to party › Address › Country | 1..1 | WBEXRP | CRP |

## `invoice.delivery.shipTo.contact` - Contact (BG-X-26)

| Property | Value | ID | Name | Occurs | Profiles | UBL |
|---|---|---|---|---|---|---|
| `name` | Text | BT-X-155 | Delivery › Ship-to party › Contact › Name | 0..1 | X |  |
| `department` | Text | BT-X-156 | Delivery › Ship-to party › Contact › Department | 0..1 | X |  |
| `phone` | Text | BT-X-157 | Delivery › Ship-to party › Contact › Phone | 0..1 | X |  |
| `fax` | Text | BT-X-158 | Delivery › Ship-to party › Contact › Fax | 0..1 | X |  |
| `email` | Text | BT-X-159 | Delivery › Ship-to party › Contact › Email | 0..1 | X |  |
| `typeCode` | Code | BT-X-321 | Delivery › Ship-to party › Contact › Type | 0..1 | X |  |

## `invoice.delivery.shipTo.additionalContacts` - Contact (BG-X-26)

More contacts (EXTENDED) - the first is `contact`.

| Property | Value | ID | Name | Occurs | Profiles | UBL |
|---|---|---|---|---|---|---|
| `name` | Text | BT-X-155 | Delivery › Ship-to party › Contact › Name | 0..1 | X |  |
| `department` | Text | BT-X-156 | Delivery › Ship-to party › Contact › Department | 0..1 | X |  |
| `phone` | Text | BT-X-157 | Delivery › Ship-to party › Contact › Phone | 0..1 | X |  |
| `fax` | Text | BT-X-158 | Delivery › Ship-to party › Contact › Fax | 0..1 | X |  |
| `email` | Text | BT-X-159 | Delivery › Ship-to party › Contact › Email | 0..1 | X |  |
| `typeCode` | Code | BT-X-321 | Delivery › Ship-to party › Contact › Type | 0..1 | X |  |

## `invoice.delivery.shipTo.legalAddress` - Address (BG-X-67)

| Property | Value | ID | Name | Occurs | Profiles | UBL |
|---|---|---|---|---|---|---|
| `line1` | Text | BT-X-434 | Delivery › Ship-to party › Registered address › Address line 1 | 0..1 | X |  |
| `line2` | Text | BT-X-435 | Delivery › Ship-to party › Registered address › Address line 2 | 0..1 | X |  |
| `line3` | Text | BT-X-436 | Delivery › Ship-to party › Registered address › Address line 3 | 0..1 | X |  |
| `postcode` | Text | BT-X-433 | Delivery › Ship-to party › Registered address › Post code | 0..1 | X |  |
| `city` | Text | BT-X-437 | Delivery › Ship-to party › Registered address › City | 0..1 | X |  |
| `subdivision` | Text | BT-X-439 | Delivery › Ship-to party › Registered address › Country subdivision | 0..1 | X |  |
| `country` | Code | BT-X-438 | Delivery › Ship-to party › Registered address › Country | 1..1 | X |  |

## `invoice.delivery.despatchAdvice` - DocumentReference

| Property | Value | ID | Name | Occurs | Profiles | UBL |
|---|---|---|---|---|---|---|
| `number` | Document number | BT-16 | Delivery › Despatch advice › Number | 0..1 | WBEXRP | CRP |
| `date` | Date | BT-X-200 | Delivery › Despatch advice › Date | 1..1 | X |  |

## `invoice.delivery.receivingAdvice` - DocumentReference

| Property | Value | ID | Name | Occurs | Profiles | UBL |
|---|---|---|---|---|---|---|
| `number` | Document number | BT-15 | Delivery › Receiving advice › Number | 0..1 | EXRP | CRP |
| `date` | Date | BT-X-201 | Delivery › Receiving advice › Date | 1..1 | X |  |

## `invoice.delivery.deliveryNote` - DocumentReference

| Property | Value | ID | Name | Occurs | Profiles | UBL |
|---|---|---|---|---|---|---|
| `number` | Document number | BT-X-202 | Delivery › Delivery note › Number | 0..1 | X |  |
| `date` | Date | BT-X-203 | Delivery › Delivery note › Date | 1..1 | X |  |

## `invoice.delivery.ultimateShipTo` - Party (BG-X-27)

| Property | Value | ID | Name | Occurs | Profiles | UBL |
|---|---|---|---|---|---|---|
| `name` | Text | BT-X-164 | Delivery › Ultimate ship-to party › Name | 0..1 | X |  |
| `tradingName` | Text | BT-X-166 | Delivery › Ultimate ship-to party › Trading name | 0..1 | X |  |
| `identifiers[].value` | Identifier | BT-X-162, BT-X-163 | Delivery › Ultimate ship-to party › Identifier | 0..1 / 0..n | X |  |
| `identifiers[].scheme` | Text | BT-X-163-0 | Delivery › Ultimate ship-to party › Identifier › Scheme | 0..1 | X |  |
| `legalRegistrationId.value` | Identifier | BT-X-165 | Delivery › Ultimate ship-to party › Legal registration identifier | 0..1 | X |  |
| `legalRegistrationId.scheme` | Text | BT-X-165-0 | Delivery › Ultimate ship-to party › Legal registration identifier › Scheme | 0..1 | X |  |
| `vatId` | Identifier | BT-X-180 | Delivery › Ultimate ship-to party › VAT identifier | 1..1 | X |  |
| `electronicAddress.value` | Identifier | BT-X-179 | Delivery › Ultimate ship-to party › Electronic address | 0..1 | X |  |
| `electronicAddress.scheme` | Text | BT-X-179-0 | Delivery › Ultimate ship-to party › Electronic address › Scheme | 0..1 | X |  |
| `address` | Address | BG-X-29 | Delivery › Ultimate ship-to party › Address | 0..1 | X |  |
| `contact` | Contact | BG-X-28 | Delivery › Ultimate ship-to party › Contact | 0..n | X |  |
| `additionalContacts[]` | Contact[] | BG-X-28 | Delivery › Ultimate ship-to party › Contact | 0..n | X |  |
| `roleCode` | Code | BT-X-551 | Delivery › Ultimate ship-to party › Role | 0..1 | X |  |
| `legalAddress` | Address | BG-X-68 | Delivery › Ultimate ship-to party › Registered address | 0..1 | X |  |

## `invoice.delivery.ultimateShipTo.address` - Address (BG-X-29)

| Property | Value | ID | Name | Occurs | Profiles | UBL |
|---|---|---|---|---|---|---|
| `line1` | Text | BT-X-173 | Delivery › Ultimate ship-to party › Address › Address line 1 | 0..1 | X |  |
| `line2` | Text | BT-X-174 | Delivery › Ultimate ship-to party › Address › Address line 2 | 0..1 | X |  |
| `line3` | Text | BT-X-175 | Delivery › Ultimate ship-to party › Address › Address line 3 | 0..1 | X |  |
| `postcode` | Text | BT-X-172 | Delivery › Ultimate ship-to party › Address › Post code | 0..1 | X |  |
| `city` | Text | BT-X-176 | Delivery › Ultimate ship-to party › Address › City | 0..1 | X |  |
| `subdivision` | Text | BT-X-178 | Delivery › Ultimate ship-to party › Address › Country subdivision | 0..1 | X |  |
| `country` | Code | BT-X-177 | Delivery › Ultimate ship-to party › Address › Country | 1..1 | X |  |

## `invoice.delivery.ultimateShipTo.contact` - Contact (BG-X-28)

| Property | Value | ID | Name | Occurs | Profiles | UBL |
|---|---|---|---|---|---|---|
| `name` | Text | BT-X-167 | Delivery › Ultimate ship-to party › Contact › Name | 0..1 | X |  |
| `department` | Text | BT-X-168 | Delivery › Ultimate ship-to party › Contact › Department | 0..1 | X |  |
| `phone` | Text | BT-X-169 | Delivery › Ultimate ship-to party › Contact › Phone | 0..1 | X |  |
| `fax` | Text | BT-X-170 | Delivery › Ultimate ship-to party › Contact › Fax | 0..1 | X |  |
| `email` | Text | BT-X-171 | Delivery › Ultimate ship-to party › Contact › Email | 0..1 | X |  |
| `typeCode` | Code | BT-X-322 | Delivery › Ultimate ship-to party › Contact › Type | 0..1 | X |  |

## `invoice.delivery.ultimateShipTo.additionalContacts` - Contact (BG-X-28)

More contacts (EXTENDED) - the first is `contact`.

| Property | Value | ID | Name | Occurs | Profiles | UBL |
|---|---|---|---|---|---|---|
| `name` | Text | BT-X-167 | Delivery › Ultimate ship-to party › Contact › Name | 0..1 | X |  |
| `department` | Text | BT-X-168 | Delivery › Ultimate ship-to party › Contact › Department | 0..1 | X |  |
| `phone` | Text | BT-X-169 | Delivery › Ultimate ship-to party › Contact › Phone | 0..1 | X |  |
| `fax` | Text | BT-X-170 | Delivery › Ultimate ship-to party › Contact › Fax | 0..1 | X |  |
| `email` | Text | BT-X-171 | Delivery › Ultimate ship-to party › Contact › Email | 0..1 | X |  |
| `typeCode` | Code | BT-X-322 | Delivery › Ultimate ship-to party › Contact › Type | 0..1 | X |  |

## `invoice.delivery.ultimateShipTo.legalAddress` - Address (BG-X-68)

| Property | Value | ID | Name | Occurs | Profiles | UBL |
|---|---|---|---|---|---|---|
| `line1` | Text | BT-X-441 | Delivery › Ultimate ship-to party › Registered address › Address line 1 | 0..1 | X |  |
| `line2` | Text | BT-X-442 | Delivery › Ultimate ship-to party › Registered address › Address line 2 | 0..1 | X |  |
| `line3` | Text | BT-X-443 | Delivery › Ultimate ship-to party › Registered address › Address line 3 | 0..1 | X |  |
| `postcode` | Text | BT-X-440 | Delivery › Ultimate ship-to party › Registered address › Post code | 0..1 | X |  |
| `city` | Text | BT-X-444 | Delivery › Ultimate ship-to party › Registered address › City | 0..1 | X |  |
| `subdivision` | Text | BT-X-446 | Delivery › Ultimate ship-to party › Registered address › Country subdivision | 0..1 | X |  |
| `country` | Code | BT-X-445 | Delivery › Ultimate ship-to party › Registered address › Country | 1..1 | X |  |

## `invoice.delivery.shipFrom` - Party (BG-X-30)

| Property | Value | ID | Name | Occurs | Profiles | UBL |
|---|---|---|---|---|---|---|
| `name` | Text | BT-X-183 | Delivery › Ship-from party › Name | 0..1 | X |  |
| `tradingName` | Text | BT-X-185 | Delivery › Ship-from party › Trading name | 0..1 | X |  |
| `identifiers[].value` | Identifier | BT-X-181, BT-X-182 | Delivery › Ship-from party › Identifier | 0..1 / 0..n | X |  |
| `identifiers[].scheme` | Text | BT-X-182-0 | Delivery › Ship-from party › Identifier › Scheme | 0..1 | X |  |
| `legalRegistrationId.value` | Identifier | BT-X-184 | Delivery › Ship-from party › Legal registration identifier | 0..1 | X |  |
| `legalRegistrationId.scheme` | Text | BT-X-184-0 | Delivery › Ship-from party › Legal registration identifier › Scheme | 0..1 | X |  |
| `vatId` | Identifier | BT-X-199 | Delivery › Ship-from party › VAT identifier | 1..1 | X |  |
| `electronicAddress.value` | Identifier | BT-X-198 | Delivery › Ship-from party › Electronic address | 0..1 | X |  |
| `electronicAddress.scheme` | Text | BT-X-198-0 | Delivery › Ship-from party › Electronic address › Scheme | 0..1 | X |  |
| `address` | Address | BG-X-32 | Delivery › Ship-from party › Address | 0..1 | X |  |
| `contact` | Contact | BG-X-31 | Delivery › Ship-from party › Contact | 0..n | X |  |
| `additionalContacts[]` | Contact[] | BG-X-31 | Delivery › Ship-from party › Contact | 0..n | X |  |
| `roleCode` | Code | BT-X-552 | Delivery › Ship-from party › Role | 0..1 | X |  |
| `legalAddress` | Address | BG-X-69 | Delivery › Ship-from party › Registered address | 0..1 | X |  |

## `invoice.delivery.shipFrom.address` - Address (BG-X-32)

| Property | Value | ID | Name | Occurs | Profiles | UBL |
|---|---|---|---|---|---|---|
| `line1` | Text | BT-X-192 | Delivery › Ship-from party › Address › Address line 1 | 0..1 | X |  |
| `line2` | Text | BT-X-193 | Delivery › Ship-from party › Address › Address line 2 | 0..1 | X |  |
| `line3` | Text | BT-X-194 | Delivery › Ship-from party › Address › Address line 3 | 0..1 | X |  |
| `postcode` | Text | BT-X-191 | Delivery › Ship-from party › Address › Post code | 0..1 | X |  |
| `city` | Text | BT-X-195 | Delivery › Ship-from party › Address › City | 0..1 | X |  |
| `subdivision` | Text | BT-X-197 | Delivery › Ship-from party › Address › Country subdivision | 0..1 | X |  |
| `country` | Code | BT-X-196 | Delivery › Ship-from party › Address › Country | 1..1 | X |  |

## `invoice.delivery.shipFrom.contact` - Contact (BG-X-31)

| Property | Value | ID | Name | Occurs | Profiles | UBL |
|---|---|---|---|---|---|---|
| `name` | Text | BT-X-186 | Delivery › Ship-from party › Contact › Name | 0..1 | X |  |
| `department` | Text | BT-X-187 | Delivery › Ship-from party › Contact › Department | 0..1 | X |  |
| `phone` | Text | BT-X-188 | Delivery › Ship-from party › Contact › Phone | 0..1 | X |  |
| `fax` | Text | BT-X-189 | Delivery › Ship-from party › Contact › Fax | 0..1 | X |  |
| `email` | Text | BT-X-190 | Delivery › Ship-from party › Contact › Email | 0..1 | X |  |
| `typeCode` | Code | BT-X-323 | Delivery › Ship-from party › Contact › Type | 0..1 | X |  |

## `invoice.delivery.shipFrom.additionalContacts` - Contact (BG-X-31)

More contacts (EXTENDED) - the first is `contact`.

| Property | Value | ID | Name | Occurs | Profiles | UBL |
|---|---|---|---|---|---|---|
| `name` | Text | BT-X-186 | Delivery › Ship-from party › Contact › Name | 0..1 | X |  |
| `department` | Text | BT-X-187 | Delivery › Ship-from party › Contact › Department | 0..1 | X |  |
| `phone` | Text | BT-X-188 | Delivery › Ship-from party › Contact › Phone | 0..1 | X |  |
| `fax` | Text | BT-X-189 | Delivery › Ship-from party › Contact › Fax | 0..1 | X |  |
| `email` | Text | BT-X-190 | Delivery › Ship-from party › Contact › Email | 0..1 | X |  |
| `typeCode` | Code | BT-X-323 | Delivery › Ship-from party › Contact › Type | 0..1 | X |  |

## `invoice.delivery.shipFrom.legalAddress` - Address (BG-X-69)

| Property | Value | ID | Name | Occurs | Profiles | UBL |
|---|---|---|---|---|---|---|
| `line1` | Text | BT-X-448 | Delivery › Ship-from party › Registered address › Address line 1 | 0..1 | X |  |
| `line2` | Text | BT-X-449 | Delivery › Ship-from party › Registered address › Address line 2 | 0..1 | X |  |
| `line3` | Text | BT-X-450 | Delivery › Ship-from party › Registered address › Address line 3 | 0..1 | X |  |
| `postcode` | Text | BT-X-447 | Delivery › Ship-from party › Registered address › Post code | 0..1 | X |  |
| `city` | Text | BT-X-451 | Delivery › Ship-from party › Registered address › City | 0..1 | X |  |
| `subdivision` | Text | BT-X-453 | Delivery › Ship-from party › Registered address › Country subdivision | 0..1 | X |  |
| `country` | Code | BT-X-452 | Delivery › Ship-from party › Registered address › Country | 1..1 | X |  |

## `invoice.invoicingPeriod` - Period (BG-14)

| Property | Value | ID | Name | Occurs | Profiles | UBL |
|---|---|---|---|---|---|---|
| `startDate` | Date | BT-73 | Invoicing period › Start date | 1..1 | WBEXRP | CRP |
| `endDate` | Date | BT-74 | Invoicing period › End date | 1..1 | WBEXRP | CRP |
| `description` | Text | BT-X-264 | Invoicing period › Description | 0..1 | X |  |

## `invoice.paymentMeans` - PaymentMeans (BG-16)

| Property | Value | ID | Name | Occurs | Profiles | UBL |
|---|---|---|---|---|---|---|
| `typeCode` | Code | BT-81 | Payment instructions › Payment means | 1..1 | WBEXRP | CRP |
| `text` | Text | BT-82 | Payment instructions › Text | 0..1 | EXRP | CRP |
| `accountId` | Identifier | BT-84 | Payment instructions › Account | 0..1 | WBEXRP | CRP |
| `accountName` | Text | BT-85 | Payment instructions › Account name | 0..1 | EXRP | CRP |
| `bic` | Identifier | BT-86 | Payment instructions › BIC | 1..1 | EXRP | CRP |
| `cardNumber` | Text | BT-87 | Payment instructions › Card number | 1..1 | EXRP | CRP |
| `cardHolder` | Text | BT-88 | Payment instructions › Card holder | 0..1 | EXRP | CRP |
| `debitedAccountId` | Identifier | BT-91 | Payment instructions › Debited account | 1..1 | WBEXRP | CRP |
| `debitedAccountName` | Text | BT-216 | Payment instructions › Debited account name | 0..1 | X |  |
| `debitedAccountBic` | Identifier | BT-215 | Payment instructions › Debited account BIC | 0..1 | X |  |

## `invoice.directDebit` - DirectDebit (BG-19)

| Property | Value | ID | Name | Occurs | Profiles | UBL |
|---|---|---|---|---|---|---|
| `mandateReference` | Identifier | BT-89 | Direct debit › Mandate reference | 0..1 | WBEXRP | CRP |
| `creditorId` | Identifier | BT-90 | Direct debit › Creditor identifier | 0..1 | WBEXRP | CRP |

## `invoice.allowances` - AllowanceCharge (BG-20)

| Property | Value | ID | Name | Occurs | Profiles | UBL |
|---|---|---|---|---|---|---|
| `amount` | Amount | BT-92 | Allowance › Amount | 1..1 | WBEXRP | CRP |
| `baseAmount` | Amount | BT-93 | Allowance › Base amount | 0..1 | WBEXRP | CRP |
| `percentage` | Percentage | BT-94 | Allowance › Percentage | 0..1 | WBEXRP | CRP |
| `reason` | Text | BT-97 | Allowance › Reason | 0..1 | WBEXRP | CRP |
| `reasonCode` | Code | BT-98 | Allowance › Reason code | 0..1 | WBEXRP | CRP |
| `vatCategory` | Code | BT-95 | Allowance › VAT category | 0..1 | WBEXRP | CRP |
| `vatRate` | Percentage | BT-96 | Allowance › VAT rate | 0..1 | WBEXRP | CRP |
| `vatExemptionReason` | Text | BT-173 | Allowance › VAT exemption reason | 0..1 | X |  |
| `vatExemptionReasonCode` | Code | BT-174 | Allowance › VAT exemption reason code | 0..1 | X |  |
| `sequence` | Identifier | BT-X-265 | Allowance › Calculation sequence | 0..1 | X |  |
| `baseQuantity` | Quantity | BT-X-266 | Allowance › Base quantity | 0..1 | X |  |
| `baseUnit` | Code | BT-X-267 | Allowance › Base quantity unit | 0..1 | X |  |

## `invoice.charges` - AllowanceCharge (BG-21)

| Property | Value | ID | Name | Occurs | Profiles | UBL |
|---|---|---|---|---|---|---|
| `amount` | Amount | BT-99 | Charge › Amount | 1..1 | WBEXRP | CRP |
| `baseAmount` | Amount | BT-100 | Charge › Base amount | 0..1 | WBEXRP | CRP |
| `percentage` | Percentage | BT-101 | Charge › Percentage | 0..1 | WBEXRP | CRP |
| `reason` | Text | BT-104 | Charge › Reason | 0..1 | WBEXRP | CRP |
| `reasonCode` | Code | BT-105 | Charge › Reason code | 0..1 | WBEXRP | CRP |
| `vatCategory` | Code | BT-102 | Charge › VAT category | 0..1 | WBEXRP | CRP |
| `vatRate` | Percentage | BT-103 | Charge › VAT rate | 0..1 | WBEXRP | CRP |
| `vatExemptionReason` | Text | BT-175 | Charge › VAT exemption reason | 0..1 | X |  |
| `vatExemptionReasonCode` | Code | BT-176 | Charge › VAT exemption reason code | 0..1 | X |  |
| `taxTypeCode` | Code | BT-177 | Charge › Tax type | 0..1 | X |  |
| `sequence` | Identifier | BT-X-268 | Charge › Calculation sequence | 0..1 | X |  |
| `baseQuantity` | Quantity | BT-X-269 | Charge › Base quantity | 0..1 | X |  |
| `baseUnit` | Code | BT-X-270 | Charge › Base quantity unit | 0..1 | X |  |

## `invoice.totals` - Totals (BG-22)

| Property | Value | ID | Name | Occurs | Profiles | UBL |
|---|---|---|---|---|---|---|
| `lineNetAmount` | Amount | BT-106 | Totals › Sum of line net amounts | 1..1 | WBEXRP | CRP |
| `allowanceAmount` | Amount | BT-107 | Totals › Sum of allowances | 0..1 | WBEXRP | CRP |
| `chargeAmount` | Amount | BT-108 | Totals › Sum of charges | 0..1 | WBEXRP | CRP |
| `netAmount` | Amount | BT-109 | Totals › Net amount | 1..1 | MWBEXRP | CRP |
| `vatAmount` | Amount | BT-110 | Totals › VAT amount | 0..2 | MWBEXRP | CRP |
| `vatAmountInVatCurrency` | Amount | BT-111 | Totals › VAT amount in VAT currency | 0..2 | MWBEXRP | CRP |
| `grossAmount` | Amount | BT-112 | Totals › Gross amount | 1..1 | MWBEXRP | CRP |
| `paidAmount` | Amount | BT-113 | Totals › Paid amount | 0..1 | WBEXRP | CRP |
| `roundingAmount` | Amount | BT-114 | Totals › Rounding amount | 0..1 | EXRP | CRP |
| `dueAmount` | Amount | BT-115 | Totals › Amount due | 1..1 | MWBEXRP | CRP |

## `invoice.vatBreakdown` - VatBreakdown (BG-23)

| Property | Value | ID | Name | Occurs | Profiles | UBL |
|---|---|---|---|---|---|---|
| `category` | Code | BT-118 | VAT breakdown › Category | 0..1 | WBEXRP | CRP |
| `rate` | Percentage | BT-119 | VAT breakdown › Rate | 0..1 | WBEXRP | CRP |
| `taxableAmount` | Amount | BT-116 | VAT breakdown › Taxable amount | 0..1 | WBEXRP | CRP |
| `taxAmount` | Amount | BT-117 | VAT breakdown › Tax amount | 0..1 | WBEXRP | CRP |
| `exemptionReason` | Text | BT-120 | VAT breakdown › Exemption reason | 0..1 | WBEXRP | CRP |
| `exemptionReasonCode` | Code | BT-121 | VAT breakdown › Exemption reason code | 0..1 | WBEXRP | CRP |
| `lineTotalBasisAmount` | Amount | BT-X-262 | VAT breakdown › Sum of line amounts | 0..1 | X |  |
| `allowanceChargeBasisAmount` | Amount | BT-X-263 | VAT breakdown › Allowances and charges | 0..1 | X |  |

## `invoice.attachments` - Attachment (BG-24)

| Property | Value | ID | Name | Occurs | Profiles | UBL |
|---|---|---|---|---|---|---|
| `id` | Document number | BT-122 | Attachment › Identifier | 0..1 | EXRP | CRP |
| `description` | Text | BT-123 | Attachment › Description | 0..1 | EXRP | CRP |
| `url` | Text | BT-124 | Attachment › URL | 0..1 | EXRP | CRP |
| `base64` | File (Base64) | BT-125 | Attachment › Content | 0..1 | EXRP | CRP |
| `mimeCode` | Text | BT-125-1 | Attachment › MIME type | 1..1 | EXRP | CRP |
| `filename` | Text | BT-125-2 | Attachment › File name | 1..1 | EXRP | CRP |
| `date` | Date | BT-X-149 | Attachment › Date | 1..1 | X |  |

## `invoice.lines` - Line (BG-25)

| Property | Value | ID | Name | Occurs | Profiles | UBL |
|---|---|---|---|---|---|---|
| `id` | Identifier | BT-126 | Line › Identifier | 1..1 | BEXRP | CRP |
| `note` | Text | BT-127 | Line › Note | 0..1 | BEXRP | CRP |
| `quantity` | Quantity | BT-129 | Line › Quantity | 0..1 | BEXRP | CRP |
| `unit` | Code | BT-130 | Line › Unit | 0..1 | BEXRP | CRP |
| `netAmount` | Amount | BT-131 | Line › Net amount | 0..1 | BEXRP | CRP |
| `name` | Text | BT-153 | Line › Name | 1..1 | BEXRP | CRP |
| `description` | Text | BT-154 | Line › Description | 0..1 | EXRP | CRP |
| `netPrice` | Price | BT-146 | Line › Net price | 1..1 | BEXRP | CRP |
| `grossPrice` | Price | BT-148 | Line › Gross price | 1..1 | BEXRP | CRP |
| `priceBaseQuantity` | Quantity | BT-149 | Line › Price base quantity | 0..1 | BEXRP | CRP |
| `priceBaseUnit` | Code | BT-150 | Line › Price base quantity unit | 0..1 | BEXRP | CRP |
| `priceDiscount` | Price | BT-147 | Line › Price discount | 1..1 | BEXRP | CRP |
| `vatCategory` | Code | BT-151 | Line › VAT category | 0..1 | BEXRP | CRP |
| `vatRate` | Percentage | BT-152 | Line › VAT rate | 0..1 | BEXRP | CRP |
| `sellerItemId` | Identifier | BT-155 | Line › Seller item number | 0..1 | EXRP | CRP |
| `buyerItemId` | Identifier | BT-156 | Line › Buyer item number | 0..1 | EXRP | CRP |
| `standardItemId.value` | Identifier | BT-157 | Line › Standard item identifier | 0..1 | BEXRP | CRP |
| `standardItemId.scheme` | Text | BT-157-1 | Line › Standard item identifier › Scheme | 0..1 | BEXRP | CRP |
| `classifications[]` | Classification[] | BT-158-00 | Line › Classification | 0..n | EXRP | CRP |
| `originCountry` | Code | BT-159 | Line › Country of origin | 1..1 | EXRP | CRP |
| `attributes[]` | ItemAttribute[] | BG-32 | Line › Attribute | 0..n | EXRP | CRP |
| `objectIdentifier.value` | Identifier | BT-128 | Line › Object identifier | 0..1 | EXRP | CRP |
| `objectIdentifier.scheme` | Text | BT-128-1 | Line › Object identifier › Scheme | 0..1 | EXRP | CRP |
| `buyerAccountingReference` | Text | BT-133 | Line › Buyer accounting reference | 1..1 | EXRP | CRP |
| `period` | Period | BG-26 | Line › Period | 0..1 | BEXRP | CRP |
| `allowances[]` | AllowanceCharge[] | BG-27 | Line › Allowance | 0..n | BEXRP | CRP |
| `charges[]` | AllowanceCharge[] | BG-28 | Line › Charge | 0..n | BEXRP | CRP |
| `purchaseOrder` | DocumentReference |  |  |  |  | CRP |
| `subLines[]` | Line[] | BG-DEX-01 | Sub invoice line | 0..n |  | R |
| `noteSubjectCode` | Code | BT-X-10 | Line › Note subject code | 0..1 | X |  |
| `noteContentCode` | Code | BT-X-9 | Line › Note content code | 0..1 | X |  |
| `additionalNotes[]` | Note[] | BT-127-00 | Line › Note | 0..n | X |  |
| `parentLineId` | Identifier | BT-X-304 | Line › Parent line | 0..1 | X |  |
| `typeCode` | Code | BT-X-7 | Line › Type | 0..1 | X |  |
| `subtypeCode` | Code | BT-X-8 | Line › Line subtype | 0..1 | X |  |
| `grossPriceBaseQuantity` | Quantity | BT-149-1 | Line › Gross price base quantity | 0..1 | BEXRP |  |
| `grossPriceBaseUnit` | Code | BT-150-1 | Line › Gross price base quantity unit | 0..1 | BEXRP |  |
| `priceDiscountPercentage` | Percentage | BT-X-34 | Line › Price discount percentage | 0..1 | X |  |
| `priceDiscountBaseAmount` | Price | BT-X-35 | Line › Price discount base amount | 0..1 | X |  |
| `priceDiscountReason` | Text | BT-X-36 | Line › Price discount reason | 0..1 | X |  |
| `priceDiscountReasonCode` | Code | BT-X-313 | Line › Price discount reason code | 0..1 | X |  |
| `additionalPriceDiscounts[]` | AllowanceCharge[] | BT-147-00 | Line › Price discount | 0..n | X |  |
| `priceCharges[]` | AllowanceCharge[] | BT-X-302-00 | Line › Price charge | 0..n | X |  |
| `vatAmount` | Amount | BT-X-95 | Line › VAT amount | 0..1 | X |  |
| `vatExemptionReason` | Text | BT-X-96 | Line › VAT exemption reason | 0..1 | X |  |
| `vatExemptionReasonCode` | Code | BT-X-97 | Line › VAT exemption reason code | 0..1 | X |  |
| `taxPointDateCode` | Code | BT-X-589 | Line › Tax point date code | 0..1 | X |  |
| `additionalTaxes[]` | Tax[] | BG-30 | Line › VAT | 0..n | X |  |
| `chargeFreeQuantity` | Quantity | BT-X-46 | Line › Charge-free quantity | 0..1 | X |  |
| `chargeFreeQuantityUnit` | Code | BT-X-46-0 | Line › Charge-free quantity unit | 0..1 | X |  |
| `packageQuantity` | Quantity | BT-X-47 | Line › Number of packages | 0..1 | X |  |
| `packageQuantityUnit` | Code | BT-X-47-0 | Line › Package unit | 0..1 | X |  |
| `quantityPerPackage` | Quantity | BT-X-561 | Line › Quantity per package | 0..1 | X |  |
| `quantityPerPackageUnit` | Code | BT-X-561-0 | Line › Quantity per package unit | 0..1 | X |  |
| `chargeTotal` | Amount | BT-X-327 | Line › Total of charges | 0..1 | X |  |
| `allowanceTotal` | Amount | BT-X-328 | Line › Total of allowances | 0..1 | X |  |
| `allowanceChargeTotal` | Amount | BT-X-98 | Line › Total of allowances and charges | 0..1 | X |  |
| `taxTotal` | Amount | BT-X-329 | Line › Tax total | 0..2 | X |  |
| `taxTotalInVatCurrency` | Amount | BT-X-590 | Line › Tax total in VAT currency | 0..2 | X |  |
| `grossAmount` | Amount | BT-X-330 | Line › Gross amount | 0..1 | X |  |
| `itemId` | Identifier | BT-X-305 | Line › Item identifier | 0..1 | X |  |
| `industryItemId` | Identifier | BT-X-532 | Line › Industry item identifier | 0..1 | X |  |
| `modelId` | Identifier | BT-X-533 | Line › Model identifier | 0..1 | X |  |
| `batchIds[]` | Text | BT-X-534 | Line › Batch number | 0..n | X |  |
| `brand` | Text | BT-X-535 | Line › Brand | 0..1 | X |  |
| `model` | Text | BT-X-536 | Line › Model | 0..1 | X |  |
| `instances[]` | ItemInstance[] | BG-X-84 | Line › Item instance | 0..n | X |  |
| `manufacturer` | Party | BG-X-93 | Line › Manufacturer | 0..1 | X |  |
| `includedItems[]` | IncludedItem[] | BG-X-1 | Line › Included item | 0..n | X |  |
| `additionalObjectIdentifiers[].value` | Identifier | BT-128 | Line › Object identifier | 0..1 | EXRP | CRP |
| `additionalObjectIdentifiers[].scheme` | Text | BT-128-1 | Line › Object identifier › Scheme | 0..1 | EXRP | CRP |
| `buyerAccountingReferenceTypeCode` | Code | BT-X-99 | Line › Accounting reference type | 0..1 | X |  |
| `additionalBuyerAccountingReferences[]` | AccountingReference[] | BT-133-00 | Line › Buyer accounting reference | 0..n | X |  |
| `salesOrder` | DocumentReference | BG-X-81 | Line › Sales order | 0..1 | X |  |
| `quotation` | DocumentReference | BG-X-47 | Line › Quotation | 0..1 | X |  |
| `contract` | DocumentReference | BG-X-2 | Line › Contract | 0..1 | X |  |
| `ultimateCustomerOrders[]` | DocumentReference[] | BG-X-5 | Line › Ultimate customer order | 0..n | X |  |
| `despatchAdvice` | DocumentReference | BG-X-13 | Line › Despatch advice | 0..1 | X |  |
| `receivingAdvice` | DocumentReference | BG-X-82 | Line › Receiving advice | 0..1 | X |  |
| `deliveryNote` | DocumentReference | BG-X-83 | Line › Delivery note | 0..1 | X |  |
| `precedingInvoice` | DocumentReference | BG-X-48 | Line › Preceding invoice | 0..1 | X |  |
| `additionalDocuments[]` | Attachment[] | BG-X-3 | Line › Referenced document | 0..n | X |  |
| `deliveryTerms` | DeliveryTerms | BG-X-87 | Line › Delivery terms | 0..1 | X |  |
| `includedTax` | Tax | BG-X-4 | Line › Included tax | 0..1 | X |  |
| `seller` | Party | BG-X-90 | Line › Seller | 0..1 | X |  |
| `shipTo` | Party | BG-X-7 | Line › Ship-to party | 0..1 | X |  |
| `ultimateShipTo` | Party | BG-X-10 | Line › Ultimate ship-to party | 0..1 | X |  |
| `deliveryDate` | Date | BT-X-85 | Line › Delivery date | 1..1 | X |  |

## `invoice.lines.classifications` - Classification (BT-158-00)

| Property | Value | ID | Name | Occurs | Profiles | UBL |
|---|---|---|---|---|---|---|
| `value` | Identifier | BT-158 | Line › Classification | 0..1 | EXRP | CRP |
| `scheme` | Text | BT-158-1 | Line › Classification › Scheme | 0..1 | EXRP | CRP |
| `schemeVersion` | Text | BT-158-2 | Line › Classification › Scheme version | 0..1 | EXRP | CRP |
| `name` | Text | BT-X-13 | Line › Classification › Name | 0..1 | X |  |

## `invoice.lines.attributes` - ItemAttribute (BG-32)

| Property | Value | ID | Name | Occurs | Profiles | UBL |
|---|---|---|---|---|---|---|
| `name` | Text | BT-160 | Line › Attribute › Name | 0..1 | EXRP | CRP |
| `value` | Text | BT-161 | Line › Attribute › Value | 0..1 | EXRP | CRP |
| `numericValue` | Quantity | BT-X-12 | Line › Attribute › Numeric value | 0..1 | X |  |
| `unit` | Code | BT-X-12-0 | Line › Attribute › Unit | 0..1 | X |  |
| `typeCode` | Code | BT-X-11 | Line › Attribute › Type | 0..1 | X |  |

## `invoice.lines.period` - Period (BG-26)

| Property | Value | ID | Name | Occurs | Profiles | UBL |
|---|---|---|---|---|---|---|
| `startDate` | Date | BT-134 | Line › Period › Start date | 1..1 | BEXRP | CRP |
| `endDate` | Date | BT-135 | Line › Period › End date | 1..1 | BEXRP | CRP |

## `invoice.lines.allowances` - AllowanceCharge (BG-27)

| Property | Value | ID | Name | Occurs | Profiles | UBL |
|---|---|---|---|---|---|---|
| `amount` | Amount | BT-136 | Line › Allowance › Amount | 1..1 | BEXRP | CRP |
| `baseAmount` | Amount | BT-137 | Line › Allowance › Base amount | 0..1 | BEXRP | CRP |
| `percentage` | Percentage | BT-138 | Line › Allowance › Percentage | 0..1 | BEXRP | CRP |
| `reason` | Text | BT-139 | Line › Allowance › Reason | 0..1 | BEXRP | CRP |
| `reasonCode` | Code | BT-140 | Line › Allowance › Reason code | 0..1 | BEXRP | CRP |

## `invoice.lines.charges` - AllowanceCharge (BG-28)

| Property | Value | ID | Name | Occurs | Profiles | UBL |
|---|---|---|---|---|---|---|
| `amount` | Amount | BT-141 | Line › Charge › Amount | 1..1 | BEXRP | CRP |
| `baseAmount` | Amount | BT-142 | Line › Charge › Base amount | 0..1 | BEXRP | CRP |
| `percentage` | Percentage | BT-143 | Line › Charge › Percentage | 0..1 | BEXRP | CRP |
| `reason` | Text | BT-144 | Line › Charge › Reason | 0..1 | BEXRP | CRP |
| `reasonCode` | Code | BT-145 | Line › Charge › Reason code | 0..1 | BEXRP | CRP |
| `taxTypeCode` | Code | BT-193 | Line › Charge › Tax type | 0..1 | X |  |

## `invoice.lines.purchaseOrder` - DocumentReference

| Property | Value | ID | Name | Occurs | Profiles | UBL |
|---|---|---|---|---|---|---|
| `number` | Document number | BT-X-21 | Line › Purchase order › Number | 0..1 | X |  |
| `date` | Date | BT-X-22 | Line › Purchase order › Date | 1..1 | X |  |
| `lineId` | Document number | BT-132 | Line › Purchase order › Line number | 0..1 | EXRP | CRP |

## `invoice.lines.subLines` - Line (BG-DEX-01)

XRechnung extension (UBL): sub lines, built like a line.

## `invoice.lines.additionalNotes` - Note (BT-127-00)

More notes - the first are `note`, `noteSubjectCode` and `noteContentCode`.

| Property | Value | ID | Name | Occurs | Profiles | UBL |
|---|---|---|---|---|---|---|
| `text` | Text | BT-127 | Line › Note | 0..1 | BEXRP | CRP |
| `subjectCode` | Code | BT-X-10 | Line › Note subject code | 0..1 | X |  |
| `contentCode` | Code | BT-X-9 | Line › Note content code | 0..1 | X |  |

## `invoice.lines.additionalPriceDiscounts` - AllowanceCharge (BT-147-00)

More discounts on the gross price - the first are `priceDiscount` and `priceDiscount...`.

| Property | Value | ID | Name | Occurs | Profiles | UBL |
|---|---|---|---|---|---|---|
| `amount` | Price | BT-147 | Line › Price discount | 1..1 | BEXRP | CRP |
| `baseAmount` | Price | BT-X-35 | Line › Price discount base amount | 0..1 | X |  |
| `percentage` | Percentage | BT-X-34 | Line › Price discount percentage | 0..1 | X |  |
| `reason` | Text | BT-X-36 | Line › Price discount reason | 0..1 | X |  |
| `reasonCode` | Code | BT-X-313 | Line › Price discount reason code | 0..1 | X |  |

## `invoice.lines.priceCharges` - AllowanceCharge (BT-X-302-00)

| Property | Value | ID | Name | Occurs | Profiles | UBL |
|---|---|---|---|---|---|---|
| `amount` | Price | BT-X-302 | Line › Price charge › Amount | 1..1 | X |  |
| `baseAmount` | Price | BT-X-301 | Line › Price charge › Base amount | 0..1 | X |  |
| `percentage` | Percentage | BT-X-300 | Line › Price charge › Percentage | 0..1 | X |  |
| `reason` | Text | BT-X-303 | Line › Price charge › Reason | 0..1 | X |  |
| `reasonCode` | Code | BT-X-314 | Line › Price charge › Reason code | 0..1 | X |  |
| `taxTypeCode` | Code | BT-X-616 | Line › Price charge › Tax type | 0..1 | X |  |

## `invoice.lines.additionalTaxes` - Tax (BG-30)

More VAT of the line - the first are `vatCategory`, `vatRate` and `vat...`.

| Property | Value | ID | Name | Occurs | Profiles | UBL |
|---|---|---|---|---|---|---|
| `category` | Code | BT-151 | Line › VAT category | 0..1 | BEXRP | CRP |
| `rate` | Percentage | BT-152 | Line › VAT rate | 0..1 | BEXRP | CRP |
| `amount` | Amount | BT-X-95 | Line › VAT amount | 0..1 | X |  |
| `exemptionReason` | Text | BT-X-96 | Line › VAT exemption reason | 0..1 | X |  |
| `exemptionReasonCode` | Code | BT-X-97 | Line › VAT exemption reason code | 0..1 | X |  |
| `taxPointDateCode` | Code | BT-X-589 | Line › Tax point date code | 0..1 | X |  |

## `invoice.lines.instances` - ItemInstance (BG-X-84)

| Property | Value | ID | Name | Occurs | Profiles | UBL |
|---|---|---|---|---|---|---|
| `batchId` | Identifier | BT-X-306 | Line › Item instance › Batch number | 0..1 | X |  |
| `serialId` | Identifier | BT-X-307 | Line › Item instance › Serial number | 0..1 | X |  |

## `invoice.lines.manufacturer` - Party (BG-X-93)

| Property | Value | ID | Name | Occurs | Profiles | UBL |
|---|---|---|---|---|---|---|
| `name` | Text | BT-X-595 | Line › Manufacturer › Name | 0..1 | X |  |
| `tradingName` | Text | BT-X-599 | Line › Manufacturer › Trading name | 0..1 | X |  |
| `identifiers[].value` | Identifier | BT-X-593, BT-X-594 | Line › Manufacturer › Identifier | 0..n | X |  |
| `identifiers[].scheme` | Text | BT-X-594-0 | Line › Manufacturer › Identifier › Scheme | 0..1 | X |  |
| `legalRegistrationId.value` | Identifier | BT-X-598 | Line › Manufacturer › Legal registration identifier | 0..1 | X |  |
| `legalRegistrationId.scheme` | Text | BT-X-598-0 | Line › Manufacturer › Legal registration identifier › Scheme | 0..1 | X |  |
| `vatId` | Identifier | BT-X-614 | Line › Manufacturer › VAT identifier | 1..1 | X |  |
| `taxNumber` | Identifier | BT-X-615 | Line › Manufacturer › Tax number | 1..1 | X |  |
| `legalInformation` | Code | BT-X-597 | Line › Manufacturer › Legal information | 0..1 | X |  |
| `electronicAddress.value` | Identifier | BT-X-613 | Line › Manufacturer › Electronic address | 0..1 | X |  |
| `electronicAddress.scheme` | Text | BT-X-613-0 | Line › Manufacturer › Electronic address › Scheme | 0..1 | X |  |
| `address` | Address | BG-X-95 | Line › Manufacturer › Address | 0..1 | X |  |
| `contact` | Contact | BG-X-94 | Line › Manufacturer › Contact | 0..n | X |  |
| `additionalContacts[]` | Contact[] | BG-X-94 | Line › Manufacturer › Contact | 0..n | X |  |
| `roleCode` | Code | BT-X-596 | Line › Manufacturer › Role | 0..1 | X |  |

## `invoice.lines.manufacturer.address` - Address (BG-X-95)

| Property | Value | ID | Name | Occurs | Profiles | UBL |
|---|---|---|---|---|---|---|
| `line1` | Text | BT-X-607 | Line › Manufacturer › Address › Address line 1 | 0..1 | X |  |
| `line2` | Text | BT-X-608 | Line › Manufacturer › Address › Address line 2 | 0..1 | X |  |
| `line3` | Text | BT-X-609 | Line › Manufacturer › Address › Address line 3 | 0..1 | X |  |
| `postcode` | Text | BT-X-606 | Line › Manufacturer › Address › Post code | 0..1 | X |  |
| `city` | Text | BT-X-610 | Line › Manufacturer › Address › City | 0..1 | X |  |
| `subdivision` | Text | BT-X-612 | Line › Manufacturer › Address › Country subdivision | 0..1 | X |  |
| `country` | Code | BT-X-611 | Line › Manufacturer › Address › Country | 1..1 | X |  |

## `invoice.lines.manufacturer.contact` - Contact (BG-X-94)

| Property | Value | ID | Name | Occurs | Profiles | UBL |
|---|---|---|---|---|---|---|
| `name` | Text | BT-X-600 | Line › Manufacturer › Contact › Name | 0..1 | X |  |
| `department` | Text | BT-X-601 | Line › Manufacturer › Contact › Department | 0..1 | X |  |
| `phone` | Text | BT-X-603 | Line › Manufacturer › Contact › Phone | 0..1 | X |  |
| `fax` | Text | BT-X-604 | Line › Manufacturer › Contact › Fax | 0..1 | X |  |
| `email` | Text | BT-X-605 | Line › Manufacturer › Contact › Email | 0..1 | X |  |
| `typeCode` | Code | BT-X-602 | Line › Manufacturer › Contact › Type | 0..1 | X |  |

## `invoice.lines.manufacturer.additionalContacts` - Contact (BG-X-94)

More contacts (EXTENDED) - the first is `contact`.

| Property | Value | ID | Name | Occurs | Profiles | UBL |
|---|---|---|---|---|---|---|
| `name` | Text | BT-X-600 | Line › Manufacturer › Contact › Name | 0..1 | X |  |
| `department` | Text | BT-X-601 | Line › Manufacturer › Contact › Department | 0..1 | X |  |
| `phone` | Text | BT-X-603 | Line › Manufacturer › Contact › Phone | 0..1 | X |  |
| `fax` | Text | BT-X-604 | Line › Manufacturer › Contact › Fax | 0..1 | X |  |
| `email` | Text | BT-X-605 | Line › Manufacturer › Contact › Email | 0..1 | X |  |
| `typeCode` | Code | BT-X-602 | Line › Manufacturer › Contact › Type | 0..1 | X |  |

## `invoice.lines.includedItems` - IncludedItem (BG-X-1)

| Property | Value | ID | Name | Occurs | Profiles | UBL |
|---|---|---|---|---|---|---|
| `id` | Identifier | BT-X-308 | Line › Included item › Identifier | 0..1 | X |  |
| `identifiers[].value` | Identifier | BT-X-15 | Line › Included item › Global identifier | 0..n | X |  |
| `identifiers[].scheme` | Text | BT-X-15-1 | Line › Included item › Global identifier › Scheme | 0..1 | X |  |
| `sellerItemId` | Identifier | BT-X-16 | Line › Included item › Seller item number | 0..1 | X |  |
| `buyerItemId` | Identifier | BT-X-17 | Line › Included item › Buyer item number | 0..1 | X |  |
| `industryItemId` | Identifier | BT-X-309 | Line › Included item › Industry item identifier | 0..1 | X |  |
| `name` | Text | BT-X-18 | Line › Included item › Name | 1..1 | X |  |
| `description` | Text | BT-X-19 | Line › Included item › Description | 0..1 | X |  |
| `quantity` | Quantity | BT-X-20 | Line › Included item › Quantity | 0..1 | X |  |
| `unit` | Code | BT-X-20-1 | Line › Included item › Unit | 0..1 | X |  |

## `invoice.lines.additionalBuyerAccountingReferences` - AccountingReference (BT-133-00)

More buyer accounting references - the first are `buyerAccountingReference` and `buyerAccountingReferenceTypeCode`.

| Property | Value | ID | Name | Occurs | Profiles | UBL |
|---|---|---|---|---|---|---|
| `id` | Text | BT-133 | Line › Buyer accounting reference | 1..1 | EXRP | CRP |
| `typeCode` | Code | BT-X-99 | Line › Accounting reference type | 0..1 | X |  |

## `invoice.lines.salesOrder` - DocumentReference (BG-X-81)

| Property | Value | ID | Name | Occurs | Profiles | UBL |
|---|---|---|---|---|---|---|
| `number` | Document number | BT-X-537 | Line › Sales order › Number | 0..1 | X |  |
| `date` | Date | BT-X-539 | Line › Sales order › Date | 1..1 | X |  |
| `lineId` | Document number | BT-X-538 | Line › Sales order › Line number | 0..1 | X |  |

## `invoice.lines.quotation` - DocumentReference (BG-X-47)

| Property | Value | ID | Name | Occurs | Profiles | UBL |
|---|---|---|---|---|---|---|
| `number` | Document number | BT-X-310 | Line › Quotation › Number | 0..1 | X |  |
| `date` | Date | BT-X-312 | Line › Quotation › Date | 1..1 | X |  |
| `lineId` | Document number | BT-X-311 | Line › Quotation › Line number | 0..1 | X |  |

## `invoice.lines.contract` - DocumentReference (BG-X-2)

| Property | Value | ID | Name | Occurs | Profiles | UBL |
|---|---|---|---|---|---|---|
| `number` | Document number | BT-X-24 | Line › Contract › Number | 0..1 | X |  |
| `date` | Date | BT-X-26 | Line › Contract › Date | 1..1 | X |  |
| `lineId` | Document number | BT-X-25 | Line › Contract › Line number | 0..1 | X |  |

## `invoice.lines.ultimateCustomerOrders` - DocumentReference (BG-X-5)

| Property | Value | ID | Name | Occurs | Profiles | UBL |
|---|---|---|---|---|---|---|
| `number` | Document number | BT-X-43 | Line › Ultimate customer order › Number | 0..1 | X |  |
| `date` | Date | BT-X-45 | Line › Ultimate customer order › Date | 1..1 | X |  |
| `lineId` | Document number | BT-X-44 | Line › Ultimate customer order › Line number | 0..1 | X |  |

## `invoice.lines.despatchAdvice` - DocumentReference (BG-X-13)

| Property | Value | ID | Name | Occurs | Profiles | UBL |
|---|---|---|---|---|---|---|
| `number` | Document number | BT-X-86 | Line › Despatch advice › Number | 0..1 | X |  |
| `date` | Date | BT-X-88 | Line › Despatch advice › Date | 1..1 | X |  |
| `lineId` | Document number | BT-X-87 | Line › Despatch advice › Line number | 0..1 | X |  |

## `invoice.lines.receivingAdvice` - DocumentReference (BG-X-82)

| Property | Value | ID | Name | Occurs | Profiles | UBL |
|---|---|---|---|---|---|---|
| `number` | Document number | BT-X-89 | Line › Receiving advice › Number | 0..1 | X |  |
| `date` | Date | BT-X-91 | Line › Receiving advice › Date | 1..1 | X |  |
| `lineId` | Document number | BT-X-90 | Line › Receiving advice › Line number | 0..1 | X |  |

## `invoice.lines.deliveryNote` - DocumentReference (BG-X-83)

| Property | Value | ID | Name | Occurs | Profiles | UBL |
|---|---|---|---|---|---|---|
| `number` | Document number | BT-X-92 | Line › Delivery note › Number | 0..1 | X |  |
| `date` | Date | BT-X-94 | Line › Delivery note › Date | 1..1 | X |  |
| `lineId` | Document number | BT-X-93 | Line › Delivery note › Line number | 0..1 | X |  |

## `invoice.lines.precedingInvoice` - DocumentReference (BG-X-48)

| Property | Value | ID | Name | Occurs | Profiles | UBL |
|---|---|---|---|---|---|---|
| `number` | Document number | BT-X-331 | Line › Preceding invoice › Number | 0..1 | X |  |
| `date` | Date | BT-X-333 | Line › Preceding invoice › Date | 1..1 | X |  |
| `lineId` | Document number | BT-X-540 | Line › Preceding invoice › Line number | 0..1 | X |  |
| `typeCode` | Code | BT-X-332 | Line › Preceding invoice › Type | 0..1 | X |  |

## `invoice.lines.additionalDocuments` - Attachment (BG-X-3)

| Property | Value | ID | Name | Occurs | Profiles | UBL |
|---|---|---|---|---|---|---|
| `id` | Document number | BT-X-27 | Line › Referenced document › Identifier | 0..1 | X |  |
| `description` | Text | BT-X-299 | Line › Referenced document › Description | 0..1 | X |  |
| `url` | Text | BT-X-28 | Line › Referenced document › URL | 0..1 | X |  |
| `base64` | File (Base64) | BT-X-31 | Line › Referenced document › Content | 0..1 | X |  |
| `mimeCode` | Text | BT-X-31-1 | Line › Referenced document › MIME type | 1..1 | X |  |
| `filename` | Text | BT-X-31-2 | Line › Referenced document › File name | 1..1 | X |  |
| `date` | Date | BT-X-33 | Line › Referenced document › Date | 1..1 | X |  |
| `lineId` | Document number | BT-X-29 | Line › Referenced document › Line number | 0..1 | X |  |
| `typeCode` | Text | BT-X-30 | Line › Referenced document › Type | 0..1 | X |  |
| `referenceTypeCode` | Text | BT-X-32 | Line › Referenced document › Reference type | 0..1 | X |  |

## `invoice.lines.deliveryTerms` - DeliveryTerms (BG-X-87)

| Property | Value | ID | Name | Occurs | Profiles | UBL |
|---|---|---|---|---|---|---|
| `code` | Code | BT-X-562 | Line › Delivery terms › Code | 1..1 | X |  |
| `locationCountry` | Code | BT-X-565 | Line › Delivery terms › Location country | 0..1 | X |  |
| `locationName` | Text | BT-X-566 | Line › Delivery terms › Location name | 0..1 | X |  |

## `invoice.lines.includedTax` - Tax (BG-X-4)

| Property | Value | ID | Name | Occurs | Profiles | UBL |
|---|---|---|---|---|---|---|
| `category` | Code | BT-X-40 | Line › Included tax › Category | 0..1 | X |  |
| `rate` | Percentage | BT-X-42 | Line › Included tax › Rate | 0..1 | X |  |
| `amount` | Amount | BT-X-37 | Line › Included tax › Amount | 0..1 | X |  |
| `exemptionReason` | Text | BT-X-39 | Line › Included tax › Exemption reason | 0..1 | X |  |
| `exemptionReasonCode` | Code | BT-X-41 | Line › Included tax › Exemption reason code | 0..1 | X |  |

## `invoice.lines.seller` - Party (BG-X-90)

| Property | Value | ID | Name | Occurs | Profiles | UBL |
|---|---|---|---|---|---|---|
| `name` | Text | BT-X-569 | Line › Seller › Name | 0..1 | X |  |
| `tradingName` | Text | BT-X-573 | Line › Seller › Trading name | 0..1 | X |  |
| `identifiers[].value` | Identifier | BT-X-567, BT-X-568 | Line › Seller › Identifier | 0..n | X |  |
| `identifiers[].scheme` | Text | BT-X-568-0 | Line › Seller › Identifier › Scheme | 0..1 | X |  |
| `legalRegistrationId.value` | Identifier | BT-X-572 | Line › Seller › Legal registration identifier | 0..1 | X |  |
| `legalRegistrationId.scheme` | Text | BT-X-572-0 | Line › Seller › Legal registration identifier › Scheme | 0..1 | X |  |
| `vatId` | Identifier | BT-X-587 | Line › Seller › VAT identifier | 1..1 | X |  |
| `taxNumber` | Identifier | BT-X-588 | Line › Seller › Tax number | 1..1 | X |  |
| `legalInformation` | Code | BT-X-571 | Line › Seller › Legal information | 0..1 | X |  |
| `electronicAddress.value` | Identifier | BT-X-586 | Line › Seller › Electronic address | 0..1 | X |  |
| `electronicAddress.scheme` | Text | BT-X-586-0 | Line › Seller › Electronic address › Scheme | 0..1 | X |  |
| `address` | Address | BG-X-92 | Line › Seller › Address | 0..1 | X |  |
| `contact` | Contact | BG-X-91 | Line › Seller › Contact | 0..n | X |  |
| `additionalContacts[]` | Contact[] | BG-X-91 | Line › Seller › Contact | 0..n | X |  |
| `roleCode` | Code | BT-X-570 | Line › Seller › Role | 0..1 | X |  |

## `invoice.lines.seller.address` - Address (BG-X-92)

| Property | Value | ID | Name | Occurs | Profiles | UBL |
|---|---|---|---|---|---|---|
| `line1` | Text | BT-X-580 | Line › Seller › Address › Address line 1 | 0..1 | X |  |
| `line2` | Text | BT-X-581 | Line › Seller › Address › Address line 2 | 0..1 | X |  |
| `line3` | Text | BT-X-582 | Line › Seller › Address › Address line 3 | 0..1 | X |  |
| `postcode` | Text | BT-X-579 | Line › Seller › Address › Post code | 0..1 | X |  |
| `city` | Text | BT-X-583 | Line › Seller › Address › City | 0..1 | X |  |
| `subdivision` | Text | BT-X-585 | Line › Seller › Address › Country subdivision | 0..1 | X |  |
| `country` | Code | BT-X-584 | Line › Seller › Address › Country | 1..1 | X |  |

## `invoice.lines.seller.contact` - Contact (BG-X-91)

| Property | Value | ID | Name | Occurs | Profiles | UBL |
|---|---|---|---|---|---|---|
| `name` | Text | BT-X-574 | Line › Seller › Contact › Name | 0..1 | X |  |
| `department` | Text | BT-X-574-1 | Line › Seller › Contact › Department | 0..1 | X |  |
| `phone` | Text | BT-X-576 | Line › Seller › Contact › Phone | 0..1 | X |  |
| `fax` | Text | BT-X-577 | Line › Seller › Contact › Fax | 0..1 | X |  |
| `email` | Text | BT-X-578 | Line › Seller › Contact › Email | 0..1 | X |  |
| `typeCode` | Code | BT-X-575 | Line › Seller › Contact › Type | 0..1 | X |  |

## `invoice.lines.seller.additionalContacts` - Contact (BG-X-91)

More contacts (EXTENDED) - the first is `contact`.

| Property | Value | ID | Name | Occurs | Profiles | UBL |
|---|---|---|---|---|---|---|
| `name` | Text | BT-X-574 | Line › Seller › Contact › Name | 0..1 | X |  |
| `department` | Text | BT-X-574-1 | Line › Seller › Contact › Department | 0..1 | X |  |
| `phone` | Text | BT-X-576 | Line › Seller › Contact › Phone | 0..1 | X |  |
| `fax` | Text | BT-X-577 | Line › Seller › Contact › Fax | 0..1 | X |  |
| `email` | Text | BT-X-578 | Line › Seller › Contact › Email | 0..1 | X |  |
| `typeCode` | Code | BT-X-575 | Line › Seller › Contact › Type | 0..1 | X |  |

## `invoice.lines.shipTo` - Party (BG-X-7)

| Property | Value | ID | Name | Occurs | Profiles | UBL |
|---|---|---|---|---|---|---|
| `name` | Text | BT-X-50 | Line › Ship-to party › Name | 0..1 | X |  |
| `tradingName` | Text | BT-X-52 | Line › Ship-to party › Trading name | 0..1 | X |  |
| `identifiers[].value` | Identifier | BT-186-00, BT-186 | Line › Ship-to party › Identifier | 0..1 / 0..n | X |  |
| `identifiers[].scheme` | Text | BT-186-1 | Line › Ship-to party › Identifier › Scheme | 0..1 | X |  |
| `legalRegistrationId.value` | Identifier | BT-X-51 | Line › Ship-to party › Legal registration identifier | 0..1 | X |  |
| `legalRegistrationId.scheme` | Text | BT-X-51-0 | Line › Ship-to party › Legal registration identifier › Scheme | 0..1 | X |  |
| `vatId` | Identifier | BT-X-66 | Line › Ship-to party › VAT identifier | 1..1 | X |  |
| `electronicAddress.value` | Identifier | BT-X-65 | Line › Ship-to party › Electronic address | 0..1 | X |  |
| `electronicAddress.scheme` | Text | BT-X-65-0 | Line › Ship-to party › Electronic address › Scheme | 0..1 | X |  |
| `address` | Address | BG-X-9 | Line › Ship-to party › Address | 0..1 | X |  |
| `contact` | Contact | BG-X-8 | Line › Ship-to party › Contact | 0..n | X |  |
| `additionalContacts[]` | Contact[] | BG-X-8 | Line › Ship-to party › Contact | 0..n | X |  |
| `roleCode` | Code | BT-X-541 | Line › Ship-to party › Role | 0..1 | X |  |

## `invoice.lines.shipTo.address` - Address (BG-X-9)

| Property | Value | ID | Name | Occurs | Profiles | UBL |
|---|---|---|---|---|---|---|
| `line1` | Text | BT-X-59 | Line › Ship-to party › Address › Address line 1 | 0..1 | X |  |
| `line2` | Text | BT-X-60 | Line › Ship-to party › Address › Address line 2 | 0..1 | X |  |
| `line3` | Text | BT-X-61 | Line › Ship-to party › Address › Address line 3 | 0..1 | X |  |
| `postcode` | Text | BT-X-58 | Line › Ship-to party › Address › Post code | 0..1 | X |  |
| `city` | Text | BT-X-62 | Line › Ship-to party › Address › City | 0..1 | X |  |
| `subdivision` | Text | BT-X-64 | Line › Ship-to party › Address › Country subdivision | 0..1 | X |  |
| `country` | Code | BT-X-63 | Line › Ship-to party › Address › Country | 1..1 | X |  |

## `invoice.lines.shipTo.contact` - Contact (BG-X-8)

| Property | Value | ID | Name | Occurs | Profiles | UBL |
|---|---|---|---|---|---|---|
| `name` | Text | BT-X-54 | Line › Ship-to party › Contact › Name | 0..1 | X |  |
| `department` | Text | BT-X-54-1 | Line › Ship-to party › Contact › Department | 0..1 | X |  |
| `phone` | Text | BT-X-55 | Line › Ship-to party › Contact › Phone | 0..1 | X |  |
| `fax` | Text | BT-X-56 | Line › Ship-to party › Contact › Fax | 0..1 | X |  |
| `email` | Text | BT-X-57 | Line › Ship-to party › Contact › Email | 0..1 | X |  |
| `typeCode` | Code | BT-X-315 | Line › Ship-to party › Contact › Type | 0..1 | X |  |

## `invoice.lines.shipTo.additionalContacts` - Contact (BG-X-8)

More contacts (EXTENDED) - the first is `contact`.

| Property | Value | ID | Name | Occurs | Profiles | UBL |
|---|---|---|---|---|---|---|
| `name` | Text | BT-X-54 | Line › Ship-to party › Contact › Name | 0..1 | X |  |
| `department` | Text | BT-X-54-1 | Line › Ship-to party › Contact › Department | 0..1 | X |  |
| `phone` | Text | BT-X-55 | Line › Ship-to party › Contact › Phone | 0..1 | X |  |
| `fax` | Text | BT-X-56 | Line › Ship-to party › Contact › Fax | 0..1 | X |  |
| `email` | Text | BT-X-57 | Line › Ship-to party › Contact › Email | 0..1 | X |  |
| `typeCode` | Code | BT-X-315 | Line › Ship-to party › Contact › Type | 0..1 | X |  |

## `invoice.lines.ultimateShipTo` - Party (BG-X-10)

| Property | Value | ID | Name | Occurs | Profiles | UBL |
|---|---|---|---|---|---|---|
| `name` | Text | BT-X-69 | Line › Ultimate ship-to party › Name | 0..1 | X |  |
| `tradingName` | Text | BT-X-71 | Line › Ultimate ship-to party › Trading name | 0..1 | X |  |
| `identifiers[].value` | Identifier | BT-X-67, BT-X-68 | Line › Ultimate ship-to party › Identifier | 0..1 / 0..n | X |  |
| `identifiers[].scheme` | Text | BT-X-68-0 | Line › Ultimate ship-to party › Identifier › Scheme | 0..1 | X |  |
| `legalRegistrationId.value` | Identifier | BT-X-70 | Line › Ultimate ship-to party › Legal registration identifier | 0..1 | X |  |
| `legalRegistrationId.scheme` | Text | BT-X-70-0 | Line › Ultimate ship-to party › Legal registration identifier › Scheme | 0..1 | X |  |
| `vatId` | Identifier | BT-X-84 | Line › Ultimate ship-to party › VAT identifier | 1..1 | X |  |
| `electronicAddress.value` | Identifier | BT-X-83 | Line › Ultimate ship-to party › Electronic address | 0..1 | X |  |
| `electronicAddress.scheme` | Text | BT-X-83-0 | Line › Ultimate ship-to party › Electronic address › Scheme | 0..1 | X |  |
| `address` | Address | BG-X-12 | Line › Ultimate ship-to party › Address | 0..1 | X |  |
| `contact` | Contact | BG-X-11 | Line › Ultimate ship-to party › Contact | 0..n | X |  |
| `additionalContacts[]` | Contact[] | BG-X-11 | Line › Ultimate ship-to party › Contact | 0..n | X |  |
| `roleCode` | Code | BT-X-542 | Line › Ultimate ship-to party › Role | 0..1 | X |  |

## `invoice.lines.ultimateShipTo.address` - Address (BG-X-12)

| Property | Value | ID | Name | Occurs | Profiles | UBL |
|---|---|---|---|---|---|---|
| `line1` | Text | BT-X-77 | Line › Ultimate ship-to party › Address › Address line 1 | 0..1 | X |  |
| `line2` | Text | BT-X-78 | Line › Ultimate ship-to party › Address › Address line 2 | 0..1 | X |  |
| `line3` | Text | BT-X-79 | Line › Ultimate ship-to party › Address › Address line 3 | 0..1 | X |  |
| `postcode` | Text | BT-X-76 | Line › Ultimate ship-to party › Address › Post code | 0..1 | X |  |
| `city` | Text | BT-X-80 | Line › Ultimate ship-to party › Address › City | 0..1 | X |  |
| `subdivision` | Text | BT-X-82 | Line › Ultimate ship-to party › Address › Country subdivision | 0..1 | X |  |
| `country` | Code | BT-X-81 | Line › Ultimate ship-to party › Address › Country | 1..1 | X |  |

## `invoice.lines.ultimateShipTo.contact` - Contact (BG-X-11)

| Property | Value | ID | Name | Occurs | Profiles | UBL |
|---|---|---|---|---|---|---|
| `name` | Text | BT-X-72 | Line › Ultimate ship-to party › Contact › Name | 0..1 | X |  |
| `department` | Text | BT-X-72-1 | Line › Ultimate ship-to party › Contact › Department | 0..1 | X |  |
| `phone` | Text | BT-X-73 | Line › Ultimate ship-to party › Contact › Phone | 0..1 | X |  |
| `fax` | Text | BT-X-74 | Line › Ultimate ship-to party › Contact › Fax | 0..1 | X |  |
| `email` | Text | BT-X-75 | Line › Ultimate ship-to party › Contact › Email | 0..1 | X |  |
| `typeCode` | Code | BT-X-316 | Line › Ultimate ship-to party › Contact › Type | 0..1 | X |  |

## `invoice.lines.ultimateShipTo.additionalContacts` - Contact (BG-X-11)

More contacts (EXTENDED) - the first is `contact`.

| Property | Value | ID | Name | Occurs | Profiles | UBL |
|---|---|---|---|---|---|---|
| `name` | Text | BT-X-72 | Line › Ultimate ship-to party › Contact › Name | 0..1 | X |  |
| `department` | Text | BT-X-72-1 | Line › Ultimate ship-to party › Contact › Department | 0..1 | X |  |
| `phone` | Text | BT-X-73 | Line › Ultimate ship-to party › Contact › Phone | 0..1 | X |  |
| `fax` | Text | BT-X-74 | Line › Ultimate ship-to party › Contact › Fax | 0..1 | X |  |
| `email` | Text | BT-X-75 | Line › Ultimate ship-to party › Contact › Email | 0..1 | X |  |
| `typeCode` | Code | BT-X-316 | Line › Ultimate ship-to party › Contact › Type | 0..1 | X |  |

## `invoice.thirdPartyPayments` - ThirdPartyPayment (BG-DEX-09, BG-34)

XRechnung extension (UBL, with a type) and EXTENDED (CII); the amount counts towards the amount due.

| Property | Value | ID | Name | Occurs | Profiles | UBL |
|---|---|---|---|---|---|---|
| `type` | Text | BT-DEX-001 | Third party payment type | 0..1 | R | R (Invoice only) |
| `amount` | Amount | BT-DEX-002, BT-179 | Third party payment amount / Third party payment › Amount | 0..1 / 1..1 | R / X | R (Invoice only) |
| `description` | Text | BT-DEX-003, BT-180 | Third party payment description / Third party payment › Description | 0..1 / 1..1 | R / X | R (Invoice only) |

## `invoice.additionalTenders` - DocumentReference (BT-17-00)

More tender or lot references (EXTENDED) - the first is `tender`.

| Property | Value | ID | Name | Occurs | Profiles | UBL |
|---|---|---|---|---|---|---|
| `number` | Document number | BT-17 | Tender or lot › Number | 0..1 | EXRP | CRP |
| `date` | Date | BT-X-556 | Tender or lot › Date | 1..1 | X |  |

## `invoice.additionalInvoicedObjects` - DocumentReference (BT-18-00)

More invoiced object identifiers (EXTENDED) - the first is `invoicedObject`.

| Property | Value | ID | Name | Occurs | Profiles | UBL |
|---|---|---|---|---|---|---|
| `number` | Identifier | BT-18 | Invoiced object › Number | 0..1 | EXRP | CRP |
| `date` | Date | BT-X-557 | Invoiced object › Date | 1..1 | X |  |
| `scheme` | Text | BT-18-1 | Invoiced object › Scheme | 0..1 | EXRP | CRP |

## `invoice.additionalBuyerAccountingReferences` - AccountingReference (BT-19-00)

More buyer accounting references - the first are `buyerAccountingReference` and `buyerAccountingReferenceTypeCode`.

| Property | Value | ID | Name | Occurs | Profiles | UBL |
|---|---|---|---|---|---|---|
| `id` | Text | BT-19 | Buyer accounting reference | 1..1 | WBEXRP | CRP |
| `typeCode` | Code | BT-X-290 | Accounting reference type | 0..1 | X |  |

## `invoice.earlyPaymentDiscount` - PaymentCondition (BG-X-44)

| Property | Value | ID | Name | Occurs | Profiles | UBL |
|---|---|---|---|---|---|---|
| `referenceDate` | Date | BT-X-282 | Early payment discount › Reference date | 1..1 | X |  |
| `period` | Quantity | BT-X-283 | Early payment discount › Period | 0..1 | X |  |
| `periodUnit` | Code | BT-X-284 | Early payment discount › Period unit | 0..1 | X |  |
| `baseAmount` | Amount | BT-X-285 | Early payment discount › Base amount | 0..1 | X |  |
| `percentage` | Percentage | BT-X-286 | Early payment discount › Percentage | 0..1 | X |  |
| `amount` | Amount | BT-X-287 | Early payment discount › Amount | 0..1 | X |  |

## `invoice.latePaymentPenalty` - PaymentCondition (BG-X-43)

| Property | Value | ID | Name | Occurs | Profiles | UBL |
|---|---|---|---|---|---|---|
| `referenceDate` | Date | BT-X-276 | Late payment penalty › Reference date | 1..1 | X |  |
| `period` | Quantity | BT-X-277 | Late payment penalty › Period | 0..1 | X |  |
| `periodUnit` | Code | BT-X-278 | Late payment penalty › Period unit | 0..1 | X |  |
| `baseAmount` | Amount | BT-X-279 | Late payment penalty › Base amount | 0..1 | X |  |
| `percentage` | Percentage | BT-X-280 | Late payment penalty › Percentage | 0..1 | X |  |
| `amount` | Amount | BT-X-281 | Late payment penalty › Amount | 0..1 | X |  |

## `invoice.paymentTermsPayee` - Party (BG-X-77)

| Property | Value | ID | Name | Occurs | Profiles | UBL |
|---|---|---|---|---|---|---|
| `name` | Text | BT-X-504 | Payee of the payment terms › Name | 0..1 | X |  |
| `tradingName` | Text | BT-X-505 | Payee of the payment terms › Trading name | 0..1 | X |  |
| `identifiers[].value` | Identifier | BT-X-506, BT-X-507 | Payee of the payment terms › Identifier | 0..1 | X |  |
| `identifiers[].scheme` | Text | BT-X-507-0 | Payee of the payment terms › Identifier › Scheme | 0..1 | X |  |
| `legalRegistrationId.value` | Identifier | BT-X-508 | Payee of the payment terms › Legal registration identifier | 0..1 | X |  |
| `legalRegistrationId.scheme` | Text | BT-X-508-0 | Payee of the payment terms › Legal registration identifier › Scheme | 0..1 | X |  |
| `vatId` | Identifier | BT-X-509 | Payee of the payment terms › VAT identifier | 1..1 | X |  |
| `electronicAddress.value` | Identifier | BT-X-510 | Payee of the payment terms › Electronic address | 0..1 | X |  |
| `electronicAddress.scheme` | Text | BT-X-510-0 | Payee of the payment terms › Electronic address › Scheme | 0..1 | X |  |
| `address` | Address | BG-X-79 | Payee of the payment terms › Address | 0..1 | X |  |
| `contact` | Contact | BG-X-78 | Payee of the payment terms › Contact | 0..n | X |  |
| `additionalContacts[]` | Contact[] | BG-X-78 | Payee of the payment terms › Contact | 0..n | X |  |
| `roleCode` | Code | BT-X-511 | Payee of the payment terms › Role | 0..1 | X |  |
| `legalAddress` | Address | BG-X-80 | Payee of the payment terms › Registered address | 0..1 | X |  |

## `invoice.paymentTermsPayee.address` - Address (BG-X-79)

| Property | Value | ID | Name | Occurs | Profiles | UBL |
|---|---|---|---|---|---|---|
| `line1` | Text | BT-X-519 | Payee of the payment terms › Address › Address line 1 | 0..1 | X |  |
| `line2` | Text | BT-X-520 | Payee of the payment terms › Address › Address line 2 | 0..1 | X |  |
| `line3` | Text | BT-X-521 | Payee of the payment terms › Address › Address line 3 | 0..1 | X |  |
| `postcode` | Text | BT-X-518 | Payee of the payment terms › Address › Post code | 0..1 | X |  |
| `city` | Text | BT-X-522 | Payee of the payment terms › Address › City | 0..1 | X |  |
| `subdivision` | Text | BT-X-524 | Payee of the payment terms › Address › Country subdivision | 0..1 | X |  |
| `country` | Code | BT-X-523 | Payee of the payment terms › Address › Country | 1..1 | X |  |

## `invoice.paymentTermsPayee.contact` - Contact (BG-X-78)

| Property | Value | ID | Name | Occurs | Profiles | UBL |
|---|---|---|---|---|---|---|
| `name` | Text | BT-X-512 | Payee of the payment terms › Contact › Name | 0..1 | X |  |
| `department` | Text | BT-X-513 | Payee of the payment terms › Contact › Department | 0..1 | X |  |
| `phone` | Text | BT-X-515 | Payee of the payment terms › Contact › Phone | 0..1 | X |  |
| `fax` | Text | BT-X-516 | Payee of the payment terms › Contact › Fax | 0..1 | X |  |
| `email` | Text | BT-X-517 | Payee of the payment terms › Contact › Email | 0..1 | X |  |
| `typeCode` | Code | BT-X-514 | Payee of the payment terms › Contact › Type | 0..1 | X |  |

## `invoice.paymentTermsPayee.additionalContacts` - Contact (BG-X-78)

More contacts (EXTENDED) - the first is `contact`.

| Property | Value | ID | Name | Occurs | Profiles | UBL |
|---|---|---|---|---|---|---|
| `name` | Text | BT-X-512 | Payee of the payment terms › Contact › Name | 0..1 | X |  |
| `department` | Text | BT-X-513 | Payee of the payment terms › Contact › Department | 0..1 | X |  |
| `phone` | Text | BT-X-515 | Payee of the payment terms › Contact › Phone | 0..1 | X |  |
| `fax` | Text | BT-X-516 | Payee of the payment terms › Contact › Fax | 0..1 | X |  |
| `email` | Text | BT-X-517 | Payee of the payment terms › Contact › Email | 0..1 | X |  |
| `typeCode` | Code | BT-X-514 | Payee of the payment terms › Contact › Type | 0..1 | X |  |

## `invoice.paymentTermsPayee.legalAddress` - Address (BG-X-80)

| Property | Value | ID | Name | Occurs | Profiles | UBL |
|---|---|---|---|---|---|---|
| `line1` | Text | BT-X-526 | Payee of the payment terms › Registered address › Address line 1 | 0..1 | X |  |
| `line2` | Text | BT-X-527 | Payee of the payment terms › Registered address › Address line 2 | 0..1 | X |  |
| `line3` | Text | BT-X-528 | Payee of the payment terms › Registered address › Address line 3 | 0..1 | X |  |
| `postcode` | Text | BT-X-525 | Payee of the payment terms › Registered address › Post code | 0..1 | X |  |
| `city` | Text | BT-X-529 | Payee of the payment terms › Registered address › City | 0..1 | X |  |
| `subdivision` | Text | BT-X-531 | Payee of the payment terms › Registered address › Country subdivision | 0..1 | X |  |
| `country` | Code | BT-X-530 | Payee of the payment terms › Registered address › Country | 1..1 | X |  |

## `invoice.additionalPaymentTerms` - PaymentTerms (BT-20-00)

More payment terms (instalments) - the first are `dueDate`, `paymentTerms`, `directDebit.mandateReference`, `partialPaymentAmount`, `earlyPaymentDiscount`, `latePaymentPenalty` and `paymentTermsPayee` of the invoice.

| Property | Value | ID | Name | Occurs | Profiles | UBL |
|---|---|---|---|---|---|---|
| `description` | Text | BT-20 | Payment terms | 0..1 | WBEXRP | CRP |
| `dueDate` | Date | BT-9 | Due date | 1..1 | WBEXRP | CRP |
| `mandateReference` | Identifier | BT-89 | Direct debit › Mandate reference | 0..1 | WBEXRP | CRP |
| `partialPaymentAmount` | Amount | BT-X-275 | Partial payment amount | 0..1 | X |  |
| `earlyPaymentDiscount` | PaymentCondition | BG-X-44 | Early payment discount | 0..1 | X |  |
| `latePaymentPenalty` | PaymentCondition | BG-X-43 | Late payment penalty | 0..1 | X |  |
| `payee` | Party | BG-X-77 | Payee of the payment terms | 0..1 | X |  |

## `invoice.additionalPaymentTerms.earlyPaymentDiscount` - PaymentCondition (BG-X-44)

| Property | Value | ID | Name | Occurs | Profiles | UBL |
|---|---|---|---|---|---|---|
| `referenceDate` | Date | BT-X-282 | Early payment discount › Reference date | 1..1 | X |  |
| `period` | Quantity | BT-X-283 | Early payment discount › Period | 0..1 | X |  |
| `periodUnit` | Code | BT-X-284 | Early payment discount › Period unit | 0..1 | X |  |
| `baseAmount` | Amount | BT-X-285 | Early payment discount › Base amount | 0..1 | X |  |
| `percentage` | Percentage | BT-X-286 | Early payment discount › Percentage | 0..1 | X |  |
| `amount` | Amount | BT-X-287 | Early payment discount › Amount | 0..1 | X |  |

## `invoice.additionalPaymentTerms.latePaymentPenalty` - PaymentCondition (BG-X-43)

| Property | Value | ID | Name | Occurs | Profiles | UBL |
|---|---|---|---|---|---|---|
| `referenceDate` | Date | BT-X-276 | Late payment penalty › Reference date | 1..1 | X |  |
| `period` | Quantity | BT-X-277 | Late payment penalty › Period | 0..1 | X |  |
| `periodUnit` | Code | BT-X-278 | Late payment penalty › Period unit | 0..1 | X |  |
| `baseAmount` | Amount | BT-X-279 | Late payment penalty › Base amount | 0..1 | X |  |
| `percentage` | Percentage | BT-X-280 | Late payment penalty › Percentage | 0..1 | X |  |
| `amount` | Amount | BT-X-281 | Late payment penalty › Amount | 0..1 | X |  |

## `invoice.additionalPaymentTerms.payee` - Party (BG-X-77)

| Property | Value | ID | Name | Occurs | Profiles | UBL |
|---|---|---|---|---|---|---|
| `name` | Text | BT-X-504 | Payee of the payment terms › Name | 0..1 | X |  |
| `tradingName` | Text | BT-X-505 | Payee of the payment terms › Trading name | 0..1 | X |  |
| `identifiers[].value` | Identifier | BT-X-506, BT-X-507 | Payee of the payment terms › Identifier | 0..1 | X |  |
| `identifiers[].scheme` | Text | BT-X-507-0 | Payee of the payment terms › Identifier › Scheme | 0..1 | X |  |
| `legalRegistrationId.value` | Identifier | BT-X-508 | Payee of the payment terms › Legal registration identifier | 0..1 | X |  |
| `legalRegistrationId.scheme` | Text | BT-X-508-0 | Payee of the payment terms › Legal registration identifier › Scheme | 0..1 | X |  |
| `vatId` | Identifier | BT-X-509 | Payee of the payment terms › VAT identifier | 1..1 | X |  |
| `electronicAddress.value` | Identifier | BT-X-510 | Payee of the payment terms › Electronic address | 0..1 | X |  |
| `electronicAddress.scheme` | Text | BT-X-510-0 | Payee of the payment terms › Electronic address › Scheme | 0..1 | X |  |
| `address` | Address | BG-X-79 | Payee of the payment terms › Address | 0..1 | X |  |
| `contact` | Contact | BG-X-78 | Payee of the payment terms › Contact | 0..n | X |  |
| `additionalContacts[]` | Contact[] | BG-X-78 | Payee of the payment terms › Contact | 0..n | X |  |
| `roleCode` | Code | BT-X-511 | Payee of the payment terms › Role | 0..1 | X |  |
| `legalAddress` | Address | BG-X-80 | Payee of the payment terms › Registered address | 0..1 | X |  |

## `invoice.additionalPaymentTerms.payee.address` - Address (BG-X-79)

| Property | Value | ID | Name | Occurs | Profiles | UBL |
|---|---|---|---|---|---|---|
| `line1` | Text | BT-X-519 | Payee of the payment terms › Address › Address line 1 | 0..1 | X |  |
| `line2` | Text | BT-X-520 | Payee of the payment terms › Address › Address line 2 | 0..1 | X |  |
| `line3` | Text | BT-X-521 | Payee of the payment terms › Address › Address line 3 | 0..1 | X |  |
| `postcode` | Text | BT-X-518 | Payee of the payment terms › Address › Post code | 0..1 | X |  |
| `city` | Text | BT-X-522 | Payee of the payment terms › Address › City | 0..1 | X |  |
| `subdivision` | Text | BT-X-524 | Payee of the payment terms › Address › Country subdivision | 0..1 | X |  |
| `country` | Code | BT-X-523 | Payee of the payment terms › Address › Country | 1..1 | X |  |

## `invoice.additionalPaymentTerms.payee.contact` - Contact (BG-X-78)

| Property | Value | ID | Name | Occurs | Profiles | UBL |
|---|---|---|---|---|---|---|
| `name` | Text | BT-X-512 | Payee of the payment terms › Contact › Name | 0..1 | X |  |
| `department` | Text | BT-X-513 | Payee of the payment terms › Contact › Department | 0..1 | X |  |
| `phone` | Text | BT-X-515 | Payee of the payment terms › Contact › Phone | 0..1 | X |  |
| `fax` | Text | BT-X-516 | Payee of the payment terms › Contact › Fax | 0..1 | X |  |
| `email` | Text | BT-X-517 | Payee of the payment terms › Contact › Email | 0..1 | X |  |
| `typeCode` | Code | BT-X-514 | Payee of the payment terms › Contact › Type | 0..1 | X |  |

## `invoice.additionalPaymentTerms.payee.additionalContacts` - Contact (BG-X-78)

More contacts (EXTENDED) - the first is `contact`.

| Property | Value | ID | Name | Occurs | Profiles | UBL |
|---|---|---|---|---|---|---|
| `name` | Text | BT-X-512 | Payee of the payment terms › Contact › Name | 0..1 | X |  |
| `department` | Text | BT-X-513 | Payee of the payment terms › Contact › Department | 0..1 | X |  |
| `phone` | Text | BT-X-515 | Payee of the payment terms › Contact › Phone | 0..1 | X |  |
| `fax` | Text | BT-X-516 | Payee of the payment terms › Contact › Fax | 0..1 | X |  |
| `email` | Text | BT-X-517 | Payee of the payment terms › Contact › Email | 0..1 | X |  |
| `typeCode` | Code | BT-X-514 | Payee of the payment terms › Contact › Type | 0..1 | X |  |

## `invoice.additionalPaymentTerms.payee.legalAddress` - Address (BG-X-80)

| Property | Value | ID | Name | Occurs | Profiles | UBL |
|---|---|---|---|---|---|---|
| `line1` | Text | BT-X-526 | Payee of the payment terms › Registered address › Address line 1 | 0..1 | X |  |
| `line2` | Text | BT-X-527 | Payee of the payment terms › Registered address › Address line 2 | 0..1 | X |  |
| `line3` | Text | BT-X-528 | Payee of the payment terms › Registered address › Address line 3 | 0..1 | X |  |
| `postcode` | Text | BT-X-525 | Payee of the payment terms › Registered address › Post code | 0..1 | X |  |
| `city` | Text | BT-X-529 | Payee of the payment terms › Registered address › City | 0..1 | X |  |
| `subdivision` | Text | BT-X-531 | Payee of the payment terms › Registered address › Country subdivision | 0..1 | X |  |
| `country` | Code | BT-X-530 | Payee of the payment terms › Registered address › Country | 1..1 | X |  |

## `invoice.buyerTaxRepresentative` - Party (BG-X-54)

| Property | Value | ID | Name | Occurs | Profiles | UBL |
|---|---|---|---|---|---|---|
| `name` | Text | BT-X-362 | Buyer tax representative › Name | 0..1 | X |  |
| `tradingName` | Text | BT-X-363 | Buyer tax representative › Trading name | 0..1 | X |  |
| `identifiers[].value` | Identifier | BT-X-364, BT-X-365 | Buyer tax representative › Identifier | 0..1 / 0..n | X |  |
| `identifiers[].scheme` | Text | BT-X-365-0 | Buyer tax representative › Identifier › Scheme | 0..1 | X |  |
| `legalRegistrationId.value` | Identifier | BT-X-366 | Buyer tax representative › Legal registration identifier | 0..1 | X |  |
| `legalRegistrationId.scheme` | Text | BT-X-366-0 | Buyer tax representative › Legal registration identifier › Scheme | 0..1 | X |  |
| `vatId` | Identifier | BT-X-367 | Buyer tax representative › VAT identifier | 1..1 | X |  |
| `electronicAddress.value` | Identifier | BT-X-368 | Buyer tax representative › Electronic address | 0..1 | X |  |
| `electronicAddress.scheme` | Text | BT-X-368-0 | Buyer tax representative › Electronic address › Scheme | 0..1 | X |  |
| `address` | Address | BG-X-56 | Buyer tax representative › Address | 0..1 | X |  |
| `contact` | Contact | BG-X-55 | Buyer tax representative › Contact | 0..n | X |  |
| `additionalContacts[]` | Contact[] | BG-X-55 | Buyer tax representative › Contact | 0..n | X |  |
| `roleCode` | Code | BT-X-546 | Buyer tax representative › Role | 0..1 | X |  |
| `legalAddress` | Address | BG-X-57 | Buyer tax representative › Registered address | 0..1 | X |  |

## `invoice.buyerTaxRepresentative.address` - Address (BG-X-56)

| Property | Value | ID | Name | Occurs | Profiles | UBL |
|---|---|---|---|---|---|---|
| `line1` | Text | BT-X-376 | Buyer tax representative › Address › Address line 1 | 0..1 | X |  |
| `line2` | Text | BT-X-377 | Buyer tax representative › Address › Address line 2 | 0..1 | X |  |
| `line3` | Text | BT-X-378 | Buyer tax representative › Address › Address line 3 | 0..1 | X |  |
| `postcode` | Text | BT-X-375 | Buyer tax representative › Address › Post code | 0..1 | X |  |
| `city` | Text | BT-X-379 | Buyer tax representative › Address › City | 0..1 | X |  |
| `subdivision` | Text | BT-X-381 | Buyer tax representative › Address › Country subdivision | 0..1 | X |  |
| `country` | Code | BT-X-380 | Buyer tax representative › Address › Country | 1..1 | X |  |

## `invoice.buyerTaxRepresentative.contact` - Contact (BG-X-55)

| Property | Value | ID | Name | Occurs | Profiles | UBL |
|---|---|---|---|---|---|---|
| `name` | Text | BT-X-369 | Buyer tax representative › Contact › Name | 0..1 | X |  |
| `department` | Text | BT-X-370 | Buyer tax representative › Contact › Department | 0..1 | X |  |
| `phone` | Text | BT-X-372 | Buyer tax representative › Contact › Phone | 0..1 | X |  |
| `fax` | Text | BT-X-373 | Buyer tax representative › Contact › Fax | 0..1 | X |  |
| `email` | Text | BT-X-374 | Buyer tax representative › Contact › Email | 0..1 | X |  |
| `typeCode` | Code | BT-X-371 | Buyer tax representative › Contact › Type | 0..1 | X |  |

## `invoice.buyerTaxRepresentative.additionalContacts` - Contact (BG-X-55)

More contacts (EXTENDED) - the first is `contact`.

| Property | Value | ID | Name | Occurs | Profiles | UBL |
|---|---|---|---|---|---|---|
| `name` | Text | BT-X-369 | Buyer tax representative › Contact › Name | 0..1 | X |  |
| `department` | Text | BT-X-370 | Buyer tax representative › Contact › Department | 0..1 | X |  |
| `phone` | Text | BT-X-372 | Buyer tax representative › Contact › Phone | 0..1 | X |  |
| `fax` | Text | BT-X-373 | Buyer tax representative › Contact › Fax | 0..1 | X |  |
| `email` | Text | BT-X-374 | Buyer tax representative › Contact › Email | 0..1 | X |  |
| `typeCode` | Code | BT-X-371 | Buyer tax representative › Contact › Type | 0..1 | X |  |

## `invoice.buyerTaxRepresentative.legalAddress` - Address (BG-X-57)

| Property | Value | ID | Name | Occurs | Profiles | UBL |
|---|---|---|---|---|---|---|
| `line1` | Text | BT-X-383 | Buyer tax representative › Registered address › Address line 1 | 0..1 | X |  |
| `line2` | Text | BT-X-384 | Buyer tax representative › Registered address › Address line 2 | 0..1 | X |  |
| `line3` | Text | BT-X-385 | Buyer tax representative › Registered address › Address line 3 | 0..1 | X |  |
| `postcode` | Text | BT-X-382 | Buyer tax representative › Registered address › Post code | 0..1 | X |  |
| `city` | Text | BT-X-386 | Buyer tax representative › Registered address › City | 0..1 | X |  |
| `subdivision` | Text | BT-X-388 | Buyer tax representative › Registered address › Country subdivision | 0..1 | X |  |
| `country` | Code | BT-X-387 | Buyer tax representative › Registered address › Country | 1..1 | X |  |

## `invoice.salesAgent` - Party (BG-X-49)

| Property | Value | ID | Name | Occurs | Profiles | UBL |
|---|---|---|---|---|---|---|
| `name` | Text | BT-X-335 | Sales agent › Name | 0..1 | X |  |
| `tradingName` | Text | BT-X-336 | Sales agent › Trading name | 0..1 | X |  |
| `identifiers[].value` | Identifier | BT-X-337, BT-X-338 | Sales agent › Identifier | 0..1 / 0..n | X |  |
| `identifiers[].scheme` | Text | BT-X-338-0 | Sales agent › Identifier › Scheme | 0..1 | X |  |
| `legalRegistrationId.value` | Identifier | BT-X-339 | Sales agent › Legal registration identifier | 0..1 | X |  |
| `legalRegistrationId.scheme` | Text | BT-X-339-0 | Sales agent › Legal registration identifier › Scheme | 0..1 | X |  |
| `vatId` | Identifier | BT-X-340 | Sales agent › VAT identifier | 1..1 | X |  |
| `electronicAddress.value` | Identifier | BT-X-341 | Sales agent › Electronic address | 0..1 | X |  |
| `electronicAddress.scheme` | Text | BT-X-341-0 | Sales agent › Electronic address › Scheme | 0..1 | X |  |
| `address` | Address | BG-X-52 | Sales agent › Address | 0..1 | X |  |
| `contact` | Contact | BG-X-51 | Sales agent › Contact | 0..n | X |  |
| `additionalContacts[]` | Contact[] | BG-X-51 | Sales agent › Contact | 0..n | X |  |
| `roleCode` | Code | BT-X-545 | Sales agent › Role | 0..1 | X |  |
| `legalAddress` | Address | BG-X-53 | Sales agent › Registered address | 0..1 | X |  |

## `invoice.salesAgent.address` - Address (BG-X-52)

| Property | Value | ID | Name | Occurs | Profiles | UBL |
|---|---|---|---|---|---|---|
| `line1` | Text | BT-X-349 | Sales agent › Address › Address line 1 | 0..1 | X |  |
| `line2` | Text | BT-X-350 | Sales agent › Address › Address line 2 | 0..1 | X |  |
| `line3` | Text | BT-X-351 | Sales agent › Address › Address line 3 | 0..1 | X |  |
| `postcode` | Text | BT-X-348 | Sales agent › Address › Post code | 0..1 | X |  |
| `city` | Text | BT-X-352 | Sales agent › Address › City | 0..1 | X |  |
| `subdivision` | Text | BT-X-354 | Sales agent › Address › Country subdivision | 0..1 | X |  |
| `country` | Code | BT-X-353 | Sales agent › Address › Country | 1..1 | X |  |

## `invoice.salesAgent.contact` - Contact (BG-X-51)

| Property | Value | ID | Name | Occurs | Profiles | UBL |
|---|---|---|---|---|---|---|
| `name` | Text | BT-X-342 | Sales agent › Contact › Name | 0..1 | X |  |
| `department` | Text | BT-X-343 | Sales agent › Contact › Department | 0..1 | X |  |
| `phone` | Text | BT-X-344 | Sales agent › Contact › Phone | 0..1 | X |  |
| `fax` | Text | BT-X-345 | Sales agent › Contact › Fax | 0..1 | X |  |
| `email` | Text | BT-X-346 | Sales agent › Contact › Email | 0..1 | X |  |
| `typeCode` | Code | BT-X-347 | Sales agent › Contact › Type | 0..1 | X |  |

## `invoice.salesAgent.additionalContacts` - Contact (BG-X-51)

More contacts (EXTENDED) - the first is `contact`.

| Property | Value | ID | Name | Occurs | Profiles | UBL |
|---|---|---|---|---|---|---|
| `name` | Text | BT-X-342 | Sales agent › Contact › Name | 0..1 | X |  |
| `department` | Text | BT-X-343 | Sales agent › Contact › Department | 0..1 | X |  |
| `phone` | Text | BT-X-344 | Sales agent › Contact › Phone | 0..1 | X |  |
| `fax` | Text | BT-X-345 | Sales agent › Contact › Fax | 0..1 | X |  |
| `email` | Text | BT-X-346 | Sales agent › Contact › Email | 0..1 | X |  |
| `typeCode` | Code | BT-X-347 | Sales agent › Contact › Type | 0..1 | X |  |

## `invoice.salesAgent.legalAddress` - Address (BG-X-53)

| Property | Value | ID | Name | Occurs | Profiles | UBL |
|---|---|---|---|---|---|---|
| `line1` | Text | BT-X-356 | Sales agent › Registered address › Address line 1 | 0..1 | X |  |
| `line2` | Text | BT-X-357 | Sales agent › Registered address › Address line 2 | 0..1 | X |  |
| `line3` | Text | BT-X-358 | Sales agent › Registered address › Address line 3 | 0..1 | X |  |
| `postcode` | Text | BT-X-355 | Sales agent › Registered address › Post code | 0..1 | X |  |
| `city` | Text | BT-X-359 | Sales agent › Registered address › City | 0..1 | X |  |
| `subdivision` | Text | BT-X-361 | Sales agent › Registered address › Country subdivision | 0..1 | X |  |
| `country` | Code | BT-X-360 | Sales agent › Registered address › Country | 1..1 | X |  |

## `invoice.buyerAgent` - Party (BG-X-62)

| Property | Value | ID | Name | Occurs | Profiles | UBL |
|---|---|---|---|---|---|---|
| `name` | Text | BT-X-406 | Buyer agent › Name | 0..1 | X |  |
| `tradingName` | Text | BT-X-407 | Buyer agent › Trading name | 0..1 | X |  |
| `identifiers[].value` | Identifier | BT-X-408, BT-X-409 | Buyer agent › Identifier | 0..1 / 0..n | X |  |
| `identifiers[].scheme` | Text | BT-X-409-0 | Buyer agent › Identifier › Scheme | 0..1 | X |  |
| `legalRegistrationId.value` | Identifier | BT-X-410 | Buyer agent › Legal registration identifier | 0..1 | X |  |
| `legalRegistrationId.scheme` | Text | BT-X-410-0 | Buyer agent › Legal registration identifier › Scheme | 0..1 | X |  |
| `vatId` | Identifier | BT-X-411 | Buyer agent › VAT identifier | 1..1 | X |  |
| `electronicAddress.value` | Identifier | BT-X-412 | Buyer agent › Electronic address | 0..1 | X |  |
| `electronicAddress.scheme` | Text | BT-X-412-0 | Buyer agent › Electronic address › Scheme | 0..1 | X |  |
| `address` | Address | BG-X-65 | Buyer agent › Address | 0..1 | X |  |
| `contact` | Contact | BG-X-64 | Buyer agent › Contact | 0..n | X |  |
| `additionalContacts[]` | Contact[] | BG-X-64 | Buyer agent › Contact | 0..n | X |  |
| `roleCode` | Code | BT-X-549 | Buyer agent › Role | 0..1 | X |  |
| `legalAddress` | Address | BG-X-66 | Buyer agent › Registered address | 0..1 | X |  |

## `invoice.buyerAgent.address` - Address (BG-X-65)

| Property | Value | ID | Name | Occurs | Profiles | UBL |
|---|---|---|---|---|---|---|
| `line1` | Text | BT-X-420 | Buyer agent › Address › Address line 1 | 0..1 | X |  |
| `line2` | Text | BT-X-421 | Buyer agent › Address › Address line 2 | 0..1 | X |  |
| `line3` | Text | BT-X-422 | Buyer agent › Address › Address line 3 | 0..1 | X |  |
| `postcode` | Text | BT-X-419 | Buyer agent › Address › Post code | 0..1 | X |  |
| `city` | Text | BT-X-423 | Buyer agent › Address › City | 0..1 | X |  |
| `subdivision` | Text | BT-X-425 | Buyer agent › Address › Country subdivision | 0..1 | X |  |
| `country` | Code | BT-X-424 | Buyer agent › Address › Country | 1..1 | X |  |

## `invoice.buyerAgent.contact` - Contact (BG-X-64)

| Property | Value | ID | Name | Occurs | Profiles | UBL |
|---|---|---|---|---|---|---|
| `name` | Text | BT-X-413 | Buyer agent › Contact › Name | 0..1 | X |  |
| `department` | Text | BT-X-414 | Buyer agent › Contact › Department | 0..1 | X |  |
| `phone` | Text | BT-X-416 | Buyer agent › Contact › Phone | 0..1 | X |  |
| `fax` | Text | BT-X-417 | Buyer agent › Contact › Fax | 0..1 | X |  |
| `email` | Text | BT-X-418 | Buyer agent › Contact › Email | 0..1 | X |  |
| `typeCode` | Code | BT-X-415 | Buyer agent › Contact › Type | 0..1 | X |  |

## `invoice.buyerAgent.additionalContacts` - Contact (BG-X-64)

More contacts (EXTENDED) - the first is `contact`.

| Property | Value | ID | Name | Occurs | Profiles | UBL |
|---|---|---|---|---|---|---|
| `name` | Text | BT-X-413 | Buyer agent › Contact › Name | 0..1 | X |  |
| `department` | Text | BT-X-414 | Buyer agent › Contact › Department | 0..1 | X |  |
| `phone` | Text | BT-X-416 | Buyer agent › Contact › Phone | 0..1 | X |  |
| `fax` | Text | BT-X-417 | Buyer agent › Contact › Fax | 0..1 | X |  |
| `email` | Text | BT-X-418 | Buyer agent › Contact › Email | 0..1 | X |  |
| `typeCode` | Code | BT-X-415 | Buyer agent › Contact › Type | 0..1 | X |  |

## `invoice.buyerAgent.legalAddress` - Address (BG-X-66)

| Property | Value | ID | Name | Occurs | Profiles | UBL |
|---|---|---|---|---|---|---|
| `line1` | Text | BT-X-427 | Buyer agent › Registered address › Address line 1 | 0..1 | X |  |
| `line2` | Text | BT-X-428 | Buyer agent › Registered address › Address line 2 | 0..1 | X |  |
| `line3` | Text | BT-X-429 | Buyer agent › Registered address › Address line 3 | 0..1 | X |  |
| `postcode` | Text | BT-X-426 | Buyer agent › Registered address › Post code | 0..1 | X |  |
| `city` | Text | BT-X-430 | Buyer agent › Registered address › City | 0..1 | X |  |
| `subdivision` | Text | BT-X-432 | Buyer agent › Registered address › Country subdivision | 0..1 | X |  |
| `country` | Code | BT-X-431 | Buyer agent › Registered address › Country | 1..1 | X |  |

## `invoice.productEndUser` - Party (BG-X-18)

| Property | Value | ID | Name | Occurs | Profiles | UBL |
|---|---|---|---|---|---|---|
| `name` | Text | BT-X-128 | Product end user › Name | 0..1 | X |  |
| `tradingName` | Text | BT-X-130 | Product end user › Trading name | 0..1 | X |  |
| `identifiers[].value` | Identifier | BT-X-126, BT-X-127 | Product end user › Identifier | 0..1 / 0..n | X |  |
| `identifiers[].scheme` | Text | BT-X-127-0 | Product end user › Identifier › Scheme | 0..1 | X |  |
| `legalRegistrationId.value` | Identifier | BT-X-129 | Product end user › Legal registration identifier | 0..1 | X |  |
| `legalRegistrationId.scheme` | Text | BT-X-129-0 | Product end user › Legal registration identifier › Scheme | 0..1 | X |  |
| `vatId` | Identifier | BT-X-144 | Product end user › VAT identifier | 1..1 | X |  |
| `electronicAddress.value` | Identifier | BT-X-143 | Product end user › Electronic address | 0..1 | X |  |
| `electronicAddress.scheme` | Text | BT-X-143-0 | Product end user › Electronic address › Scheme | 0..1 | X |  |
| `address` | Address | BG-X-21 | Product end user › Address | 0..1 | X |  |
| `contact` | Contact | BG-X-20 | Product end user › Contact | 0..n | X |  |
| `additionalContacts[]` | Contact[] | BG-X-20 | Product end user › Contact | 0..n | X |  |
| `roleCode` | Code | BT-X-548 | Product end user › Role | 0..1 | X |  |
| `legalAddress` | Address | BG-X-60 | Product end user › Registered address | 0..1 | X |  |

## `invoice.productEndUser.address` - Address (BG-X-21)

| Property | Value | ID | Name | Occurs | Profiles | UBL |
|---|---|---|---|---|---|---|
| `line1` | Text | BT-X-137 | Product end user › Address › Address line 1 | 0..1 | X |  |
| `line2` | Text | BT-X-138 | Product end user › Address › Address line 2 | 0..1 | X |  |
| `line3` | Text | BT-X-139 | Product end user › Address › Address line 3 | 0..1 | X |  |
| `postcode` | Text | BT-X-136 | Product end user › Address › Post code | 0..1 | X |  |
| `city` | Text | BT-X-140 | Product end user › Address › City | 0..1 | X |  |
| `subdivision` | Text | BT-X-142 | Product end user › Address › Country subdivision | 0..1 | X |  |
| `country` | Code | BT-X-141 | Product end user › Address › Country | 1..1 | X |  |

## `invoice.productEndUser.contact` - Contact (BG-X-20)

| Property | Value | ID | Name | Occurs | Profiles | UBL |
|---|---|---|---|---|---|---|
| `name` | Text | BT-X-131 | Product end user › Contact › Name | 0..1 | X |  |
| `department` | Text | BT-X-132 | Product end user › Contact › Department | 0..1 | X |  |
| `phone` | Text | BT-X-133 | Product end user › Contact › Phone | 0..1 | X |  |
| `fax` | Text | BT-X-134 | Product end user › Contact › Fax | 0..1 | X |  |
| `email` | Text | BT-X-135 | Product end user › Contact › Email | 0..1 | X |  |
| `typeCode` | Code | BT-X-320 | Product end user › Contact › Type | 0..1 | X |  |

## `invoice.productEndUser.additionalContacts` - Contact (BG-X-20)

More contacts (EXTENDED) - the first is `contact`.

| Property | Value | ID | Name | Occurs | Profiles | UBL |
|---|---|---|---|---|---|---|
| `name` | Text | BT-X-131 | Product end user › Contact › Name | 0..1 | X |  |
| `department` | Text | BT-X-132 | Product end user › Contact › Department | 0..1 | X |  |
| `phone` | Text | BT-X-133 | Product end user › Contact › Phone | 0..1 | X |  |
| `fax` | Text | BT-X-134 | Product end user › Contact › Fax | 0..1 | X |  |
| `email` | Text | BT-X-135 | Product end user › Contact › Email | 0..1 | X |  |
| `typeCode` | Code | BT-X-320 | Product end user › Contact › Type | 0..1 | X |  |

## `invoice.productEndUser.legalAddress` - Address (BG-X-60)

| Property | Value | ID | Name | Occurs | Profiles | UBL |
|---|---|---|---|---|---|---|
| `line1` | Text | BT-X-397 | Product end user › Registered address › Address line 1 | 0..1 | X |  |
| `line2` | Text | BT-X-398 | Product end user › Registered address › Address line 2 | 0..1 | X |  |
| `line3` | Text | BT-X-399 | Product end user › Registered address › Address line 3 | 0..1 | X |  |
| `postcode` | Text | BT-X-396 | Product end user › Registered address › Post code | 0..1 | X |  |
| `city` | Text | BT-X-400 | Product end user › Registered address › City | 0..1 | X |  |
| `subdivision` | Text | BT-X-402 | Product end user › Registered address › Country subdivision | 0..1 | X |  |
| `country` | Code | BT-X-401 | Product end user › Registered address › Country | 1..1 | X |  |

## `invoice.invoicer` - Party (BG-X-33)

| Property | Value | ID | Name | Occurs | Profiles | UBL |
|---|---|---|---|---|---|---|
| `name` | Text | BT-X-207 | Invoicer › Name | 0..1 | X |  |
| `tradingName` | Text | BT-X-209 | Invoicer › Trading name | 0..1 | X |  |
| `identifiers[].value` | Identifier | BT-X-205, BT-X-206 | Invoicer › Identifier | 0..1 / 0..n | X |  |
| `identifiers[].scheme` | Text | BT-X-206-0 | Invoicer › Identifier › Scheme | 0..1 | X |  |
| `legalRegistrationId.value` | Identifier | BT-X-208 | Invoicer › Legal registration identifier | 0..1 | X |  |
| `legalRegistrationId.scheme` | Text | BT-X-208-0 | Invoicer › Legal registration identifier › Scheme | 0..1 | X |  |
| `vatId` | Identifier | BT-X-223 | Invoicer › VAT identifier | 1..1 | X |  |
| `electronicAddress.value` | Identifier | BT-X-222 | Invoicer › Electronic address | 0..1 | X |  |
| `electronicAddress.scheme` | Text | BT-X-222-0 | Invoicer › Electronic address › Scheme | 0..1 | X |  |
| `address` | Address | BG-X-35 | Invoicer › Address | 0..1 | X |  |
| `contact` | Contact | BG-X-34 | Invoicer › Contact | 0..n | X |  |
| `additionalContacts[]` | Contact[] | BG-X-34 | Invoicer › Contact | 0..n | X |  |
| `roleCode` | Code | BT-X-553 | Invoicer › Role | 0..1 | X |  |
| `legalAddress` | Address | BG-X-70 | Invoicer › Registered address | 0..1 | X |  |

## `invoice.invoicer.address` - Address (BG-X-35)

| Property | Value | ID | Name | Occurs | Profiles | UBL |
|---|---|---|---|---|---|---|
| `line1` | Text | BT-X-216 | Invoicer › Address › Address line 1 | 0..1 | X |  |
| `line2` | Text | BT-X-217 | Invoicer › Address › Address line 2 | 0..1 | X |  |
| `line3` | Text | BT-X-218 | Invoicer › Address › Address line 3 | 0..1 | X |  |
| `postcode` | Text | BT-X-215 | Invoicer › Address › Post code | 0..1 | X |  |
| `city` | Text | BT-X-219 | Invoicer › Address › City | 0..1 | X |  |
| `subdivision` | Text | BT-X-221 | Invoicer › Address › Country subdivision | 0..1 | X |  |
| `country` | Code | BT-X-220 | Invoicer › Address › Country | 1..1 | X |  |

## `invoice.invoicer.contact` - Contact (BG-X-34)

| Property | Value | ID | Name | Occurs | Profiles | UBL |
|---|---|---|---|---|---|---|
| `name` | Text | BT-X-210 | Invoicer › Contact › Name | 0..1 | X |  |
| `department` | Text | BT-X-211 | Invoicer › Contact › Department | 0..1 | X |  |
| `phone` | Text | BT-X-212 | Invoicer › Contact › Phone | 0..1 | X |  |
| `fax` | Text | BT-X-213 | Invoicer › Contact › Fax | 0..1 | X |  |
| `email` | Text | BT-X-214 | Invoicer › Contact › Email | 0..1 | X |  |
| `typeCode` | Code | BT-X-324 | Invoicer › Contact › Type | 0..1 | X |  |

## `invoice.invoicer.additionalContacts` - Contact (BG-X-34)

More contacts (EXTENDED) - the first is `contact`.

| Property | Value | ID | Name | Occurs | Profiles | UBL |
|---|---|---|---|---|---|---|
| `name` | Text | BT-X-210 | Invoicer › Contact › Name | 0..1 | X |  |
| `department` | Text | BT-X-211 | Invoicer › Contact › Department | 0..1 | X |  |
| `phone` | Text | BT-X-212 | Invoicer › Contact › Phone | 0..1 | X |  |
| `fax` | Text | BT-X-213 | Invoicer › Contact › Fax | 0..1 | X |  |
| `email` | Text | BT-X-214 | Invoicer › Contact › Email | 0..1 | X |  |
| `typeCode` | Code | BT-X-324 | Invoicer › Contact › Type | 0..1 | X |  |

## `invoice.invoicer.legalAddress` - Address (BG-X-70)

| Property | Value | ID | Name | Occurs | Profiles | UBL |
|---|---|---|---|---|---|---|
| `line1` | Text | BT-X-455 | Invoicer › Registered address › Address line 1 | 0..1 | X |  |
| `line2` | Text | BT-X-456 | Invoicer › Registered address › Address line 2 | 0..1 | X |  |
| `line3` | Text | BT-X-457 | Invoicer › Registered address › Address line 3 | 0..1 | X |  |
| `postcode` | Text | BT-X-454 | Invoicer › Registered address › Post code | 0..1 | X |  |
| `city` | Text | BT-X-458 | Invoicer › Registered address › City | 0..1 | X |  |
| `subdivision` | Text | BT-X-460 | Invoicer › Registered address › Country subdivision | 0..1 | X |  |
| `country` | Code | BT-X-459 | Invoicer › Registered address › Country | 1..1 | X |  |

## `invoice.invoicee` - Party (BG-X-36)

| Property | Value | ID | Name | Occurs | Profiles | UBL |
|---|---|---|---|---|---|---|
| `name` | Text | BT-X-226 | Invoicee › Name | 0..1 | X |  |
| `tradingName` | Text | BT-X-228 | Invoicee › Trading name | 0..1 | X |  |
| `identifiers[].value` | Identifier | BT-X-224, BT-X-225 | Invoicee › Identifier | 0..1 / 0..n | X |  |
| `identifiers[].scheme` | Text | BT-X-225-0 | Invoicee › Identifier › Scheme | 0..1 | X |  |
| `legalRegistrationId.value` | Identifier | BT-X-227 | Invoicee › Legal registration identifier | 0..1 | X |  |
| `legalRegistrationId.scheme` | Text | BT-X-227-0 | Invoicee › Legal registration identifier › Scheme | 0..1 | X |  |
| `vatId` | Identifier | BT-X-242 | Invoicee › VAT identifier | 1..1 | X |  |
| `electronicAddress.value` | Identifier | BT-X-241 | Invoicee › Electronic address | 0..1 | X |  |
| `electronicAddress.scheme` | Text | BT-X-241-0 | Invoicee › Electronic address › Scheme | 0..1 | X |  |
| `address` | Address | BG-X-38 | Invoicee › Address | 0..1 | X |  |
| `contact` | Contact | BG-X-37 | Invoicee › Contact | 0..n | X |  |
| `additionalContacts[]` | Contact[] | BG-X-37 | Invoicee › Contact | 0..n | X |  |
| `roleCode` | Code | BT-X-554 | Invoicee › Role | 0..1 | X |  |
| `legalAddress` | Address | BG-X-71 | Invoicee › Registered address | 0..1 | X |  |

## `invoice.invoicee.address` - Address (BG-X-38)

| Property | Value | ID | Name | Occurs | Profiles | UBL |
|---|---|---|---|---|---|---|
| `line1` | Text | BT-X-235 | Invoicee › Address › Address line 1 | 0..1 | X |  |
| `line2` | Text | BT-X-236 | Invoicee › Address › Address line 2 | 0..1 | X |  |
| `line3` | Text | BT-X-237 | Invoicee › Address › Address line 3 | 0..1 | X |  |
| `postcode` | Text | BT-X-234 | Invoicee › Address › Post code | 0..1 | X |  |
| `city` | Text | BT-X-238 | Invoicee › Address › City | 0..1 | X |  |
| `subdivision` | Text | BT-X-240 | Invoicee › Address › Country subdivision | 0..1 | X |  |
| `country` | Code | BT-X-239 | Invoicee › Address › Country | 1..1 | X |  |

## `invoice.invoicee.contact` - Contact (BG-X-37)

| Property | Value | ID | Name | Occurs | Profiles | UBL |
|---|---|---|---|---|---|---|
| `name` | Text | BT-X-229 | Invoicee › Contact › Name | 0..1 | X |  |
| `department` | Text | BT-X-230 | Invoicee › Contact › Department | 0..1 | X |  |
| `phone` | Text | BT-X-231 | Invoicee › Contact › Phone | 0..1 | X |  |
| `fax` | Text | BT-X-232 | Invoicee › Contact › Fax | 0..1 | X |  |
| `email` | Text | BT-X-233 | Invoicee › Contact › Email | 0..1 | X |  |
| `typeCode` | Code | BT-X-325 | Invoicee › Contact › Type | 0..1 | X |  |

## `invoice.invoicee.additionalContacts` - Contact (BG-X-37)

More contacts (EXTENDED) - the first is `contact`.

| Property | Value | ID | Name | Occurs | Profiles | UBL |
|---|---|---|---|---|---|---|
| `name` | Text | BT-X-229 | Invoicee › Contact › Name | 0..1 | X |  |
| `department` | Text | BT-X-230 | Invoicee › Contact › Department | 0..1 | X |  |
| `phone` | Text | BT-X-231 | Invoicee › Contact › Phone | 0..1 | X |  |
| `fax` | Text | BT-X-232 | Invoicee › Contact › Fax | 0..1 | X |  |
| `email` | Text | BT-X-233 | Invoicee › Contact › Email | 0..1 | X |  |
| `typeCode` | Code | BT-X-325 | Invoicee › Contact › Type | 0..1 | X |  |

## `invoice.invoicee.legalAddress` - Address (BG-X-71)

| Property | Value | ID | Name | Occurs | Profiles | UBL |
|---|---|---|---|---|---|---|
| `line1` | Text | BT-X-462 | Invoicee › Registered address › Address line 1 | 0..1 | X |  |
| `line2` | Text | BT-X-463 | Invoicee › Registered address › Address line 2 | 0..1 | X |  |
| `line3` | Text | BT-X-464 | Invoicee › Registered address › Address line 3 | 0..1 | X |  |
| `postcode` | Text | BT-X-461 | Invoicee › Registered address › Post code | 0..1 | X |  |
| `city` | Text | BT-X-465 | Invoicee › Registered address › City | 0..1 | X |  |
| `subdivision` | Text | BT-X-467 | Invoicee › Registered address › Country subdivision | 0..1 | X |  |
| `country` | Code | BT-X-466 | Invoicee › Registered address › Country | 1..1 | X |  |

## `invoice.payer` - Party (BG-X-73)

| Property | Value | ID | Name | Occurs | Profiles | UBL |
|---|---|---|---|---|---|---|
| `name` | Text | BT-X-476 | Payer › Name | 0..1 | X |  |
| `tradingName` | Text | BT-X-477 | Payer › Trading name | 0..1 | X |  |
| `identifiers[].value` | Identifier | BT-X-478, BT-X-479 | Payer › Identifier | 0..1 | X |  |
| `identifiers[].scheme` | Text | BT-X-479-0 | Payer › Identifier › Scheme | 0..1 | X |  |
| `legalRegistrationId.value` | Identifier | BT-X-480 | Payer › Legal registration identifier | 0..1 | X |  |
| `legalRegistrationId.scheme` | Text | BT-X-480-0 | Payer › Legal registration identifier › Scheme | 0..1 | X |  |
| `vatId` | Identifier | BT-X-481 | Payer › VAT identifier | 1..1 | X |  |
| `electronicAddress.value` | Identifier | BT-X-482 | Payer › Electronic address | 0..1 | X |  |
| `electronicAddress.scheme` | Text | BT-X-482-0 | Payer › Electronic address › Scheme | 0..1 | X |  |
| `address` | Address | BG-X-75 | Payer › Address | 0..1 | X |  |
| `contact` | Contact | BG-X-74 | Payer › Contact | 0..n | X |  |
| `additionalContacts[]` | Contact[] | BG-X-74 | Payer › Contact | 0..n | X |  |
| `roleCode` | Code | BT-X-483 | Payer › Role | 0..1 | X |  |
| `legalAddress` | Address | BG-X-76 | Payer › Registered address | 0..1 | X |  |

## `invoice.payer.address` - Address (BG-X-75)

| Property | Value | ID | Name | Occurs | Profiles | UBL |
|---|---|---|---|---|---|---|
| `line1` | Text | BT-X-491 | Payer › Address › Address line 1 | 0..1 | X |  |
| `line2` | Text | BT-X-492 | Payer › Address › Address line 2 | 0..1 | X |  |
| `line3` | Text | BT-X-493 | Payer › Address › Address line 3 | 0..1 | X |  |
| `postcode` | Text | BT-X-490 | Payer › Address › Post code | 0..1 | X |  |
| `city` | Text | BT-X-494 | Payer › Address › City | 0..1 | X |  |
| `subdivision` | Text | BT-X-496 | Payer › Address › Country subdivision | 0..1 | X |  |
| `country` | Code | BT-X-495 | Payer › Address › Country | 1..1 | X |  |

## `invoice.payer.contact` - Contact (BG-X-74)

| Property | Value | ID | Name | Occurs | Profiles | UBL |
|---|---|---|---|---|---|---|
| `name` | Text | BT-X-484 | Payer › Contact › Name | 0..1 | X |  |
| `department` | Text | BT-X-485 | Payer › Contact › Department | 0..1 | X |  |
| `phone` | Text | BT-X-487 | Payer › Contact › Phone | 0..1 | X |  |
| `fax` | Text | BT-X-488 | Payer › Contact › Fax | 0..1 | X |  |
| `email` | Text | BT-X-489 | Payer › Contact › Email | 0..1 | X |  |
| `typeCode` | Code | BT-X-486 | Payer › Contact › Type | 0..1 | X |  |

## `invoice.payer.additionalContacts` - Contact (BG-X-74)

More contacts (EXTENDED) - the first is `contact`.

| Property | Value | ID | Name | Occurs | Profiles | UBL |
|---|---|---|---|---|---|---|
| `name` | Text | BT-X-484 | Payer › Contact › Name | 0..1 | X |  |
| `department` | Text | BT-X-485 | Payer › Contact › Department | 0..1 | X |  |
| `phone` | Text | BT-X-487 | Payer › Contact › Phone | 0..1 | X |  |
| `fax` | Text | BT-X-488 | Payer › Contact › Fax | 0..1 | X |  |
| `email` | Text | BT-X-489 | Payer › Contact › Email | 0..1 | X |  |
| `typeCode` | Code | BT-X-486 | Payer › Contact › Type | 0..1 | X |  |

## `invoice.payer.legalAddress` - Address (BG-X-76)

| Property | Value | ID | Name | Occurs | Profiles | UBL |
|---|---|---|---|---|---|---|
| `line1` | Text | BT-X-498 | Payer › Registered address › Address line 1 | 0..1 | X |  |
| `line2` | Text | BT-X-499 | Payer › Registered address › Address line 2 | 0..1 | X |  |
| `line3` | Text | BT-X-500 | Payer › Registered address › Address line 3 | 0..1 | X |  |
| `postcode` | Text | BT-X-497 | Payer › Registered address › Post code | 0..1 | X |  |
| `city` | Text | BT-X-501 | Payer › Registered address › City | 0..1 | X |  |
| `subdivision` | Text | BT-X-503 | Payer › Registered address › Country subdivision | 0..1 | X |  |
| `country` | Code | BT-X-502 | Payer › Registered address › Country | 1..1 | X |  |

## `invoice.quotation` - DocumentReference (BG-X-61)

| Property | Value | ID | Name | Occurs | Profiles | UBL |
|---|---|---|---|---|---|---|
| `number` | Document number | BT-X-403 | Quotation › Number | 0..1 | X |  |
| `date` | Date | BT-X-404 | Quotation › Date | 1..1 | X |  |

## `invoice.ultimateCustomerOrders` - DocumentReference (BG-X-23)

| Property | Value | ID | Name | Occurs | Profiles | UBL |
|---|---|---|---|---|---|---|
| `number` | Document number | BT-X-150 | Ultimate customer order › Number | 0..1 | X |  |
| `date` | Date | BT-X-151 | Ultimate customer order › Date | 1..1 | X |  |

## `invoice.deliveryTerms` - DeliveryTerms (BG-X-22)

| Property | Value | ID | Name | Occurs | Profiles | UBL |
|---|---|---|---|---|---|---|
| `code` | Code | BT-X-145 | Delivery terms › Code | 1..1 | X |  |
| `locationCountry` | Code | BT-X-563 | Delivery terms › Location country | 0..1 | X |  |
| `locationName` | Text | BT-X-564 | Delivery terms › Location name | 0..1 | X |  |

## `invoice.logisticsServiceCharges` - LogisticsServiceCharge (BG-X-42)

| Property | Value | ID | Name | Occurs | Profiles | UBL |
|---|---|---|---|---|---|---|
| `description` | Text | BT-X-271 | Logistics service charge › Description | 1..1 | X |  |
| `amount` | Amount | BT-X-272 | Logistics service charge › Amount | 1..1 | X |  |
| `taxes[]` | Tax[] | BT-X-273-00 | Logistics service charge › Tax | 1..n | X |  |

## `invoice.logisticsServiceCharges.taxes` - Tax (BT-X-273-00)

| Property | Value | ID | Name | Occurs | Profiles | UBL |
|---|---|---|---|---|---|---|
| `category` | Code | BT-X-273 | Logistics service charge › Tax › Category | 0..1 | X |  |
| `rate` | Percentage | BT-X-274 | Logistics service charge › Tax › Rate | 0..1 | X |  |
| `exemptionReason` | Text | BT-X-591 | Logistics service charge › Tax › Exemption reason | 0..1 | X |  |
| `exemptionReasonCode` | Code | BT-X-592 | Logistics service charge › Tax › Exemption reason code | 0..1 | X |  |

## `invoice.currencyExchange` - CurrencyExchange (BG-X-41)

| Property | Value | ID | Name | Occurs | Profiles | UBL |
|---|---|---|---|---|---|---|
| `sourceCurrency` | Code | BT-X-258 | Currency exchange › Source currency | 1..1 | X |  |
| `targetCurrency` | Code | BT-X-259 | Currency exchange › Target currency | 1..1 | X |  |
| `rate` | Rate | BT-X-260 | Currency exchange › Exchange rate | 1..1 | X |  |
| `date` | Date | BT-X-261 | Currency exchange › Date | 1..1 | X |  |

## `invoice.advancePayments` - AdvancePayment (BG-X-45)

| Property | Value | ID | Name | Occurs | Profiles | UBL |
|---|---|---|---|---|---|---|
| `amount` | Amount | BT-X-291 | Advance payment › Amount | 1..1 | X |  |
| `date` | Date | BT-X-292 | Advance payment › Date | 1..1 | X |  |
| `taxes[]` | Tax[] | BG-X-46 | Advance payment › Tax | 1..n | X |  |
| `precedingInvoice` | DocumentReference | BG-X-85 | Advance payment › Preceding invoice | 0..1 | X |  |

## `invoice.advancePayments.taxes` - Tax (BG-X-46)

| Property | Value | ID | Name | Occurs | Profiles | UBL |
|---|---|---|---|---|---|---|
| `category` | Code | BT-X-296 | Advance payment › Tax › Category | 0..1 | X |  |
| `rate` | Percentage | BT-X-298 | Advance payment › Tax › Rate | 0..1 | X |  |
| `amount` | Amount | BT-X-293 | Advance payment › Tax › Amount | 0..1 | X |  |
| `exemptionReason` | Text | BT-X-295 | Advance payment › Tax › Exemption reason | 0..1 | X |  |
| `exemptionReasonCode` | Code | BT-X-297 | Advance payment › Tax › Exemption reason code | 0..1 | X |  |

## `invoice.advancePayments.precedingInvoice` - DocumentReference (BG-X-85)

| Property | Value | ID | Name | Occurs | Profiles | UBL |
|---|---|---|---|---|---|---|
| `number` | Document number | BT-X-558 | Advance payment › Preceding invoice › Number | 0..1 | X |  |
| `date` | Date | BT-X-560 | Advance payment › Preceding invoice › Date | 1..1 | X |  |
| `typeCode` | Code | BT-X-559 | Advance payment › Preceding invoice › Type | 0..1 | X |  |

