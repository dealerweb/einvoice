<?php

declare(strict_types=1);

namespace Dealerweb\EInvoice\Model\Behavior;

use Dealerweb\EInvoice\CodeList;
use Dealerweb\EInvoice\CodeLists;
use Dealerweb\EInvoice\Exception\InvalidAttachment;
use RuntimeException;

/**
 * What an attachment does besides holding its values: the file from a path (fromFile()), its content decoded
 * (content()), a name that is safe to write (safeFilename()) and writing it (saveTo()). The package never opens the
 * address of an external document (url) - it only passes it on.
 *
 * @internal part of Model\Attachment
 */
trait AttachmentBehavior
{
    /**
     * File extension of each MIME type EN 16931 allows for an attached document (code list CodeList::MimeType,
     * BT-125-1 - AttachmentTest checks that every code of the list has one). A file of another type keeps its
     * delivered name.
     */
    public const EXTENSIONS = [
        'application/pdf' => 'pdf',
        'image/png' => 'png',
        'image/jpeg' => 'jpg',
        'text/csv' => 'csv',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'xlsx',
        'application/vnd.oasis.opendocument.spreadsheet' => 'ods',
    ];

    /** Other spellings of an extension that fit the MIME type as well. */
    private const ALIASES = ['jpeg' => 'jpg'];

    /** Device names Windows reserves - a file may not start with them, whatever extensions follow. */
    private const RESERVED_NAMES = '/^(con|prn|aux|nul|com[0-9¹²³]|lpt[0-9¹²³]|conin\$|conout\$)(\.|$)/iu';

    /** Longest file name in bytes: file systems allow 255, with room for a counter added when saving. */
    private const MAX_NAME_BYTES = 200;

    /**
     * An attachment of a file: its name, its type (by its extension - PDF, PNG, JPEG, CSV, XLSX or ODS, the types
     * EN 16931 allows) and its content; the name is also the reference of the document (id, which EN 16931 asks for) -
     * change it where the document has another. A path or a file:// URL, no stream wrapper: reading does not reach the
     * network.
     *
     * @throws InvalidAttachment the file cannot be read, or its type is none EN 16931 allows for an attachment
     */
    public static function fromFile(string $path, ?string $description = null): static
    {
        $shown = addcslashes($path, "\0..\37");
        if ((preg_match('~^[a-z0-9+.-]{2,}://~i', $path) === 1 && stripos($path, 'file://') !== 0) || str_starts_with($path, 'data:')) {
            throw new InvalidAttachment("Cannot read $shown - only files are read, no stream wrapper.");
        }
        $filename = basename(str_replace('\\', '/', $path));
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        $extension = self::ALIASES[$extension] ?? $extension;
        $mimeCode = array_search($extension, self::EXTENSIONS, true);
        if (! is_string($mimeCode) || ! CodeLists::contains(CodeList::MimeType, $mimeCode)) {
            throw new InvalidAttachment("$shown: EN 16931 allows PDF, PNG, JPEG, CSV, XLSX and ODS files as attachments, not \"$extension\".");
        }
        $content = $path === '' || str_contains($path, "\0") || is_dir($path) ? false : @file_get_contents($path);
        if ($content === false) {
            throw new InvalidAttachment("Cannot read $shown.");
        }

        return new static(id: $filename, description: $description, base64: base64_encode($content), mimeCode: $mimeCode, filename: $filename);
    }

    /**
     * Whether the invoice carries the file itself, not only the address of an external document.
     */
    public function hasContent(): bool
    {
        return $this->compact() !== '';
    }

    /**
     * The attached file, decoded - null where the attachment only names an external document.
     *
     * @throws InvalidAttachment the content is no valid base64
     */
    public function content(): ?string
    {
        $base64 = $this->compact();
        if ($base64 === '') {
            return null;
        }

        $content = base64_decode($base64, true);
        if ($content === false) {
            throw new InvalidAttachment("The attachment {$this->safeFilename()} is no valid base64.");
        }

        return $content;
    }

    /**
     * Size of the attached file in bytes, without decoding it - null without content.
     */
    public function size(): ?int
    {
        $content = $this->compact();
        if ($content === '') {
            return null;
        }

        return max(0, intdiv(strlen($content) * 3, 4) - substr_count(substr($content, -2), '='));
    }

    /**
     * A file name that is safe to write: the delivered name without directories, without control and invisible
     * format characters (a right-to-left override would disguise the extension) and without the characters file
     * systems refuse, ending in the extension of its MIME type - a PDF delivered as "invoice.exe" becomes
     * "invoice.exe.pdf". Without a usable name: "attachment" with that extension.
     */
    public function safeFilename(): string
    {
        $name = preg_replace('~^.*[/\\\\]~s', '', $this->filename ?? '') ?? '';
        $name = preg_replace('/[\p{Cc}\p{Cf}<>:"|?*]/u', '', $name) ?? '';
        $name = trim($name, " .\t");

        if (preg_match(self::RESERVED_NAMES, $name)) {
            $name = '_' . $name;
        }
        if ($name === '') {
            $name = 'attachment';
        }

        $extension = self::EXTENSIONS[strtolower(trim((string) $this->mimeCode))] ?? null;
        $current = strtolower(pathinfo($name, PATHINFO_EXTENSION));
        if ($extension !== null && $current !== $extension && (self::ALIASES[$current] ?? null) !== $extension) {
            $name .= '.' . $extension;
        }

        if (strlen($name) > self::MAX_NAME_BYTES) {
            $suffix = pathinfo($name, PATHINFO_EXTENSION);
            $suffix = $suffix === '' ? '' : '.' . $suffix;
            $name = mb_strcut($name, 0, self::MAX_NAME_BYTES - strlen($suffix), 'UTF-8') . $suffix;
        }

        return $name;
    }

    /**
     * Writes the attached file into a directory under safeFilename(). An existing file is never overwritten: the
     * name then gets a counter, "invoice (2).pdf". Returns the path of the written file.
     *
     * @throws InvalidAttachment the attachment has no content (it only names an external document), or its content is
     *                           no valid base64
     * @throws RuntimeException the directory does not exist or the file cannot be written
     */
    public function saveTo(string $directory): string
    {
        $content = $this->content() ?? throw new InvalidAttachment("The attachment {$this->safeFilename()} only names an external document, it has no content to save.");
        if (! is_dir($directory)) {
            throw new RuntimeException("Directory $directory does not exist.");
        }

        $name = $this->safeFilename();
        $extension = pathinfo($name, PATHINFO_EXTENSION);
        $base = $extension === '' ? $name : substr($name, 0, -strlen($extension) - 1);

        for ($counter = 1; $counter < 1000; $counter++) {
            $candidate = $counter === 1 ? $name : "$base ($counter)" . ($extension === '' ? '' : ".$extension");
            $path = rtrim($directory, '/\\') . DIRECTORY_SEPARATOR . $candidate;

            // "x" creates the file only if it does not exist yet - no race between checking and writing.
            $handle = @fopen($path, 'x');
            if ($handle === false) {
                if (file_exists($path)) {
                    continue;
                }
                throw new RuntimeException("Cannot write $path.");
            }

            $written = fwrite($handle, $content);
            fclose($handle);
            if ($written !== strlen($content)) {
                @unlink($path);
                throw new RuntimeException("Cannot write $path.");
            }

            return $path;
        }

        throw new RuntimeException("No free file name for $name in $directory.");
    }

    /**
     * The base64 content without the line breaks and blanks it may carry.
     */
    private function compact(): string
    {
        return is_string($this->base64) ? (preg_replace('/\s+/', '', $this->base64) ?? $this->base64) : '';
    }
}
