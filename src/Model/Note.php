<?php

declare(strict_types=1);

namespace Dealerweb\EInvoice\Model;

/**
 * A note in free text, with its subject.
 *
 * Used as:
 *  - invoice.notes (BG-1)
 *  - invoice.lines.additionalNotes (BT-127-00)
 *
 * Generated from resources/model/mapping.php - do not edit.
 */
final class Note extends Element
{
    protected const PRIMARY = 'text';

    public function __construct(
        /** Text */
        public ?string $text = null,
        /** Subject of the text (code, UNTDID 4451) */
        public ?string $subjectCode = null,
        /** Content of the text (code, EXTENDED) */
        public ?string $contentCode = null,
    ) {}
}
