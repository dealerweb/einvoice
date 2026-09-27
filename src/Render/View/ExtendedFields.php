<?php

declare(strict_types=1);

namespace Dealerweb\EInvoice\Render\View;

/**
 * Fields and groups of ZUGFeRD / Factur-X EXTENDED (ids of the FeRD field list) that the view shows at a
 * place of their own instead of under "Further information".
 *
 * @internal
 */
final class ExtendedFields
{
    public const TEST_INDICATOR = 'BT-X-1';
    public const DOCUMENT_NAME = 'BT-X-2';
    public const PROJECT_NAME = 'BT-11-0';
    public const DELIVERY_NOTE = 'BT-X-202';
    public const DELIVERY_NOTE_DATE = 'BT-X-203';
    public const LINE_STATUS = 'BT-X-8';
    public const PARENT_LINE = 'BT-X-304';
    public const INCLUDED_ITEM = 'BG-X-1';
    public const INVOICEE = 'BG-X-36';
    public const DISCOUNT_TERMS = 'BG-X-44';
    public const PENALTY_TERMS = 'BG-X-43';
    public const LOGISTICS_CHARGE = 'BG-X-42';
    public const NOTE_CONTENT_CODE = 'BT-X-5';
    public const LINE_NOTE_CONTENT_CODE = 'BT-X-9';
    public const LINE_NOTE_SUBJECT_CODE = 'BT-X-10';
    public const LINE_DELIVERY_DATE = 'BT-X-85';

    /** Fields shown in the head of the document. */
    public const HEADER_FIELDS = [self::TEST_INDICATOR, self::DOCUMENT_NAME, self::PROJECT_NAME, self::DELIVERY_NOTE, self::DELIVERY_NOTE_DATE];

    /** Groups with a place of their own: invoicee, payment terms, charges. */
    public const HEADER_GROUPS = [self::INVOICEE, self::DISCOUNT_TERMS, self::PENALTY_TERMS, self::LOGISTICS_CHARGE];

    /**
     * Elements of the groups that occur repeatedly - their values belong together even where some of them
     * sit in a child element (the date of a discount term, the VAT of a charge).
     */
    public const GROUP_ELEMENTS = [
        self::DISCOUNT_TERMS => 'ApplicableTradePaymentDiscountTerms',
        self::PENALTY_TERMS => 'ApplicableTradePaymentPenaltyTerms',
        self::LOGISTICS_CHARGE => 'SpecifiedLogisticsServiceCharge',
    ];
}
