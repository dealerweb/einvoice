<?php

declare(strict_types=1);

namespace Dealerweb\EInvoice\Pdf;

/**
 * A stream object: its dictionary and its data as stored, still encoded by the filters of the dictionary.
 *
 * @internal
 */
final class Stream
{
    public function __construct(public Dictionary $dictionary, public string $data) {}
}
