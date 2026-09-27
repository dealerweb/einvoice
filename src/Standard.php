<?php

declare(strict_types=1);

namespace Dealerweb\EInvoice;

/**
 * The standard an invoice declares in its specification identifier (BT-24).
 */
enum Standard: string
{
    /** EN 16931 itself - also the ZUGFeRD / Factur-X profile "EN 16931" (formerly COMFORT). */
    case En16931 = 'en16931';

    /** XRechnung, the German CIUS of EN 16931 (including its extension and CVD). */
    case XRechnung = 'xrechnung';

    /** ZUGFeRD 2.x / Factur-X profiles MINIMUM, BASIC WL, BASIC, EXTENDED and EXTENDED-CTC-FR. */
    case FacturX = 'factur-x';

    /** Peppol BIS Billing / Self-Billing. */
    case Peppol = 'peppol';

    /** Another CIUS or extension of EN 16931. */
    case Other = 'other';

    /** No specification identifier, or one that is not based on EN 16931. */
    case Unknown = 'unknown';
}
