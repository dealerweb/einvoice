<?php

declare(strict_types=1);

namespace Dealerweb\EInvoice\Render\View;

use Dealerweb\EInvoice\Document;
use Dealerweb\EInvoice\ModelValues;
use Dealerweb\EInvoice\Render\Format;
use Dealerweb\EInvoice\Render\Texts;

/**
 * What every section of the view works with: the invoice and its data, the language, and the unmapped
 * values placed at the blocks they belong to.
 *
 * @internal
 */
final readonly class Context
{
    public Texts $texts;

    public Format $format;

    /** @var array<string, mixed> the invoice with the element names of the model, groups with their source path */
    public array $data;

    /** Invoice currency (BT-5). */
    public ?string $currency;

    /**
     * Delivery date and invoicing period of the document (ISO) - the lines leave out what only repeats them.
     *
     * @var array{delivery: string|null, start: string|null, end: string|null}
     */
    public array $dates;

    public Extras $extras;

    public function __construct(public Document $invoice, public string $language)
    {
        $this->texts = new Texts($language);
        $this->format = new Format($language);
        $this->data = $invoice->toArray(true);
        $this->currency = ModelValues::value($this->data['Invoice_currency_code'] ?? null);

        $delivery = ModelValues::group($this->data['DELIVERY_INFORMATION'] ?? null) ?? [];
        $period = ModelValues::group($this->data['INVOICING_PERIOD'] ?? null) ?? [];
        $this->dates = [
            'delivery' => ModelValues::value($delivery['Actual_delivery_date'] ?? null),
            'start' => ModelValues::value($period['Invoicing_period_start_date'] ?? null),
            'end' => ModelValues::value($period['Invoicing_period_end_date'] ?? null),
        ];

        $this->extras = new Extras($invoice->unmapped(), $this->data);
    }
}
