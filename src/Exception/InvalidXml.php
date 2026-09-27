<?php

declare(strict_types=1);

namespace Dealerweb\EInvoice\Exception;

/**
 * The document is empty, cannot be read or is not well-formed XML.
 */
final class InvalidXml extends EInvoiceException
{
    /**
     * @param int|null $documentLine the line of the document the parser stopped at, where known
     */
    public function __construct(string $message = '', public readonly ?int $documentLine = null)
    {
        parent::__construct($message);
    }
}
