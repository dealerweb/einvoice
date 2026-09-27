<?php

declare(strict_types=1);

namespace Dealerweb\EInvoice\Exception;

/**
 * The XML is not an EN 16931 invoice in a supported syntax (UBL Invoice, UBL CreditNote, CII) - e.g. an order
 * or a ZUGFeRD 1.0 invoice - or it declares a DOCTYPE, which an invoice never needs.
 */
final class UnsupportedDocument extends EInvoiceException {}
