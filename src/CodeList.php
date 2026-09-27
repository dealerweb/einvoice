<?php

declare(strict_types=1);

namespace Dealerweb\EInvoice;

/**
 * The code lists of EN 16931 and of the Factur-X / ZUGFeRD EXTENDED profile, as published in the
 * EN 16931 code lists (in brackets: the tab of the official workbook).
 */
enum CodeList: string
{
    /** ISO 3166-1 alpha-2 country codes, extended by 1A (Kosovo) and XI (Northern Ireland) [Country] */
    case Country = 'country';

    /** ISO 4217 currency codes [Currency] */
    case Currency = 'currency';

    /** ISO/IEC 6523 identifier scheme codes, ICD [ICD] */
    case IdentifierScheme = 'icd';

    /** UNTDID 1001 document type, the subset allowed for invoices and credit notes [1001] */
    case DocumentType = 'document-type';

    /** UNTDID 1153 reference code qualifier [1153] */
    case ReferenceQualifier = 'reference-qualifier';

    /** Scheme of the VAT identifier: VAT in UBL, VA in CII [VAT ID] */
    case VatIdentifier = 'vat-identifier';

    /** Scheme of the tax registration identifier: FC [FISCAL ID] */
    case TaxRegistration = 'tax-registration';

    /** Tax scheme of a VAT category, always VAT (subset of UNTDID 5153) [VAT CAT] */
    case TaxScheme = 'tax-scheme';

    /** Event time code of the VAT point date: UNTDID 2005 in UBL, UNTDID 2475 in CII [Time] */
    case EventTime = 'event-time';

    /** UNTDID 4451 text subject qualifier [Text] */
    case TextSubject = 'text-subject';

    /** UNTDID 4461 payment means [Payment] */
    case PaymentMeans = 'payment-means';

    /** UNTDID 5305 duty or tax or fee category, the subset for VAT [5305] */
    case VatCategory = 'vat-category';

    /** UNTDID 5189 allowance reason, subset [Allowance] */
    case AllowanceReason = 'allowance-reason';

    /** UNTDID 7143 item type identification: the scheme of an item classification [Item] */
    case ItemType = 'item-type';

    /** UNTDID 7161 charge reason (special service description) [Charge] */
    case ChargeReason = 'charge-reason';

    /** MIME types of attached documents [MIME] */
    case MimeType = 'mime-type';

    /** CEF electronic address scheme, EAS [EAS] */
    case ElectronicAddressScheme = 'eas';

    /** CEF VAT exemption reason, VATEX [VATEX] */
    case VatExemptionReason = 'vatex';

    /** UN/ECE Recommendation 20 and 21 unit codes [Unit] */
    case Unit = 'unit';

    /** UNTDID 5153 duty or tax or fee type other than VAT [Non-VAT Tax Code] */
    case TaxType = 'tax-type';

    /** UNTDID 1229 action code: line status (EXTENDED) [Line Status] */
    case LineStatus = 'line-status';

    /** ISO 639-2 alpha-3 language codes (EXTENDED) [Language] */
    case Language = 'language';

    /** UNTDID 6313 measured attribute code with Factur-X additions (EXTENDED) [Characteristic] */
    case Characteristic = 'characteristic';

    /** Line status reason: regular line, group or information only (EXTENDED) [Line Reason] */
    case LineReason = 'line-reason';

    /** Incoterms (EXTENDED) [INCOTERMS] */
    case Incoterms = 'incoterms';

    /** UN/ECE Recommendation 19 transport mode (EXTENDED) [Transport] */
    case TransportMode = 'transport-mode';

    /** UNTDID 2379 date format [Date] */
    case DateFormat = 'date-format';

    /** Document type in the XMP metadata of a hybrid PDF (Factur-X / ZUGFeRD) [HybridDocument] */
    case HybridDocument = 'hybrid-document';

    /** Conformance level in the XMP metadata of a hybrid PDF [HybridConformance] */
    case HybridConformance = 'hybrid-conformance';

    /** File name of the XML embedded in a hybrid PDF [Filename] */
    case HybridFilename = 'hybrid-filename';

    /** Version in the XMP metadata of a hybrid PDF [HybridVersion] */
    case HybridVersion = 'hybrid-version';
}
