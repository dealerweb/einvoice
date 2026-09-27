<?php

declare(strict_types=1);

namespace Dealerweb\EInvoice\Render\View;

use Dealerweb\EInvoice\CodeList;

/**
 * The additional supporting documents (BG-24): reference, description, the attached file with its type and
 * size, or its external location.
 *
 * @internal
 */
final class Attachments extends Section
{
    /**
     * @return list<array<string, mixed>>
     */
    public function build(): array
    {
        $files = $this->invoice->attachments();
        $attachments = [];
        foreach ($this->groups($this->data['ADDITIONAL_SUPPORTING_DOCUMENTS'] ?? null) as $index => $document) {
            $file = null;
            $attached = $files[$index] ?? null;
            if ($attached !== null) {
                $bytes = $attached->size();
                $parts = array_filter([
                    $attached->filename,
                    $attached->mimeCode === null ? null : $this->codeName(CodeList::MimeType, $attached->mimeCode),
                    $bytes === null ? null : $this->format->size($bytes),
                ]);
                $file = $parts === [] ? null : implode(', ', $parts);
            }

            [$description, $more] = TextLayout::cellParts($this->text($document['Supporting_document_description'] ?? null));
            $attachments[] = [
                'reference' => $this->value($document['Supporting_document_reference'] ?? null),
                'description' => $description,
                'more' => $more,
                'file' => $file,
                'location' => $this->value($document['External_document_location'] ?? null),
                'extras' => $this->attachedRows('attachment:' . $index),
            ];
        }

        return $attachments;
    }
}
