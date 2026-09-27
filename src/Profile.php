<?php

declare(strict_types=1);

namespace Dealerweb\EInvoice;

/**
 * The rules an invoice is checked against: the profiles of ZUGFeRD / Factur-X (rules of FeRD, CII only), XRechnung
 * (rules of CEN and KoSIT), EN 16931 itself (rules of CEN) and Peppol BIS Billing 3.0 (rules of CEN and the rules of
 * Peppol as this package implements them).
 */
enum Profile: string
{
    /** ZUGFeRD / Factur-X MINIMUM: header data only - a booking aid, not an invoice in the sense of EN 16931. */
    case Minimum = 'MINIMUM';

    /** ZUGFeRD / Factur-X BASIC WL: without invoice lines - a booking aid, not an invoice in the sense of EN 16931. */
    case BasicWl = 'BASIC WL';

    /** ZUGFeRD / Factur-X BASIC: a subset of EN 16931 with invoice lines. */
    case Basic = 'BASIC';

    /** ZUGFeRD / Factur-X EN 16931 (formerly COMFORT): EN 16931 in CII, checked with the rules of FeRD. */
    case En16931 = 'EN16931';

    /** ZUGFeRD / Factur-X EXTENDED: EN 16931 with the extensions of FeRD. */
    case Extended = 'EXTENDED';

    /** XRechnung 3.0, UBL or CII: the German specification (CIUS) of EN 16931 with its extension and CVD. */
    case XRechnung = 'XRECHNUNG';

    /** EN 16931 itself, UBL or CII: the rules of CEN only (KoSIT's scenario "EN16931"). */
    case Core = 'CORE';

    /**
     * Peppol BIS Billing 3.0, UBL or CII: the specification of OpenPeppol based on EN 16931, checked against EN 16931
     * and the rules of Peppol - the package's own implementation (resources/peppol) with the identifiers and verdicts
     * of the official rules, which are not part of the package.
     */
    case Peppol = 'PEPPOL';

    /**
     * Readable name, e.g. "ZUGFeRD / Factur-X EXTENDED", "XRechnung", "EN 16931".
     */
    public function label(): string
    {
        return match ($this) {
            self::XRechnung => 'XRechnung',
            self::Core => 'EN 16931',
            self::Peppol => 'Peppol BIS Billing 3.0',
            self::En16931 => 'ZUGFeRD / Factur-X EN 16931',
            default => 'ZUGFeRD / Factur-X ' . $this->value,
        };
    }

    /**
     * The specification identifier (BT-24) a document of this profile carries - for XRechnung the one of version 3.0.
     */
    public function identifier(): string
    {
        return match ($this) {
            self::Minimum => 'urn:factur-x.eu:1p0:minimum',
            self::BasicWl => 'urn:factur-x.eu:1p0:basicwl',
            self::Basic => 'urn:cen.eu:en16931:2017#compliant#urn:factur-x.eu:1p0:basic',
            self::En16931, self::Core => 'urn:cen.eu:en16931:2017',
            self::Extended => 'urn:cen.eu:en16931:2017#conformant#urn:factur-x.eu:1p0:extended',
            self::XRechnung => 'urn:cen.eu:en16931:2017#compliant#urn:xeinkauf.de:kosit:xrechnung_3.0',
            self::Peppol => 'urn:cen.eu:en16931:2017#compliant#urn:fdc:peppol.eu:2017:poacc:billing:3.0',
        };
    }

    /**
     * A profile of ZUGFeRD / Factur-X: its documents are CII, its rules come from FeRD.
     */
    public function isFacturX(): bool
    {
        return ! in_array($this, [self::XRechnung, self::Core, self::Peppol], true);
    }

    /**
     * Documents of this profile are invoices in the sense of EN 16931 - in Germany the requirement for an electronic
     * invoice (§ 14 UStG). MINIMUM and BASIC WL are not: they carry too little.
     */
    public function isEInvoice(): bool
    {
        return ! in_array($this, [self::Minimum, self::BasicWl], true);
    }
}
