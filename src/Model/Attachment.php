<?php

declare(strict_types=1);

namespace Dealerweb\EInvoice\Model;

use DateTimeInterface;
use Dealerweb\EInvoice\Model\Behavior\AttachmentBehavior;

/**
 * A supporting document: attached, or where it is found.
 *
 * Used as:
 *  - invoice.attachments (BG-24)
 *  - invoice.lines.additionalDocuments (BG-X-3)
 *
 * Generated from resources/model/mapping.php - do not edit.
 */
final class Attachment extends Element
{
    use AttachmentBehavior;

    public function __construct(
        /** Identifier of the document */
        public ?string $id = null,
        /** Description of the document */
        public ?string $description = null,
        /** Where an external document is found (URL) */
        public ?string $url = null,
        /** The document itself, base64 encoded - content() decodes it, fromFile() sets it */
        public ?string $base64 = null,
        /** MIME type of the document (e.g. application/pdf) */
        public ?string $mimeCode = null,
        /** File name of the document */
        public ?string $filename = null,
        /** Issue date of the document (EXTENDED) */
        public string|DateTimeInterface|null $date = null,
        /** Referenced line of the document (EXTENDED) */
        public ?string $lineId = null,
        /** Type of the document (code, EXTENDED) */
        public ?string $typeCode = null,
        /** Type of the reference (code, EXTENDED) */
        public ?string $referenceTypeCode = null,
    ) {}
}
