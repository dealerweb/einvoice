<?php

declare(strict_types=1);

namespace Dealerweb\EInvoice\Exception;

use Dealerweb\EInvoice\Validation\Message;
use Dealerweb\EInvoice\Validation\Report;

/**
 * A generated invoice does not meet its profile - the validator's report says why (report()).
 */
final class InvalidInvoice extends EInvoiceException
{
    public function __construct(private readonly Report $report)
    {
        $errors = $report->errors();
        $shown = array_map(
            static fn(Message $message): string => $message->code . ': ' . $message->text,
            array_slice($errors, 0, 3)
        );
        $more = count($errors) > 3 ? ' (and ' . (count($errors) - 3) . ' more)' : '';

        parent::__construct(
            'The invoice does not meet the profile ' . ($report->profile()?->label() ?? '-') . ': ' . implode(' | ', $shown) . $more
        );
    }

    public function report(): Report
    {
        return $this->report;
    }
}
