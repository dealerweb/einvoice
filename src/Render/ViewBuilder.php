<?php

declare(strict_types=1);

namespace Dealerweb\EInvoice\Render;

use Dealerweb\EInvoice\Document;
use Dealerweb\EInvoice\Render\View\Allowances;
use Dealerweb\EInvoice\Render\View\Attachments;
use Dealerweb\EInvoice\Render\View\Context;
use Dealerweb\EInvoice\Render\View\Footer;
use Dealerweb\EInvoice\Render\View\Further;
use Dealerweb\EInvoice\Render\View\Head;
use Dealerweb\EInvoice\Render\View\Lines;
use Dealerweb\EInvoice\Render\View\Notes;
use Dealerweb\EInvoice\Render\View\Parties;
use Dealerweb\EInvoice\Render\View\Payment;
use Dealerweb\EInvoice\Render\View\Totals;

/**
 * Turns an invoice into the view of the rendered document (resources/templates/invoice.php), laid out like a
 * German business letter: the issuer in the letterhead and with its legal data in the footer of every page,
 * the addressee below the letterhead, date, customer number, VAT identifiers and contact person in the
 * information block beside it.
 *
 * Every value is formatted for the language, every code replaced by its name, and the information outside of
 * EN 16931 (Document::unmapped()) placed where it belongs - in its line, note, party, payment instruction or VAT
 * row (View\Extras). What has no place of its own ends up under "Further information".
 *
 * The issuer is the seller; a self-billed invoice (Document::isSelfBilled()) is issued by the buyer and
 * addressed to the seller. An invoicee of ZUGFeRD EXTENDED (BG-X-36) receives the invoice in place of the buyer.
 *
 * Each section of the document is built by a class of its own in View\ - this class only puts them together.
 *
 * @internal
 */
final class ViewBuilder
{
    private const SYNTAX_NAMES = [
        'ubl-invoice' => 'UBL Invoice',
        'ubl-creditnote' => 'UBL CreditNote',
        'cii' => 'UN/CEFACT CII',
    ];

    public function __construct(private readonly Document $invoice, private readonly string $language) {}

    /**
     * @return array<string, mixed>
     */
    public function build(): array
    {
        $context = new Context($this->invoice, $this->language);
        $texts = $context->texts;
        $head = new Head($context);
        $parties = new Parties($context);
        $footer = new Footer($context);
        $totals = new Totals($context);
        $payment = new Payment($context);

        // The seller and buyer come first: they take their unmapped identifiers out of the further information.
        $seller = $parties->seller();
        $buyer = $parties->buyer();
        $invoicee = $parties->invoicee();
        $selfBilled = $this->invoice->isSelfBilled();

        [$issuer, $recipient] = $selfBilled ? [$buyer, $seller] : [$seller, $buyer];
        $addressee = $invoicee !== null && ! $selfBilled ? $invoicee : $recipient;
        $foreign = $parties->isForeign($addressee, $issuer);
        $partyRows = $parties->rows($buyer, $invoicee, $addressee, $issuer, $selfBilled);

        // "Gutschrift (vom Kunden ausgestellt)": the addition in brackets becomes a mark below the heading.
        $title = $head->title();
        [$heading, $addition] = preg_match('/^(.+?)\s*\(([^()]+)\)$/u', $title, $match) ? [$match[1], $match[2]] : [$title, null];
        $number = $head->number();
        $headline = $number === null ? $heading : $texts->get('title.number', ['title' => $heading, 'number' => $number]);
        $issueDate = $head->issueDate();

        $legalFooter = $footer->build($issuer);
        $vat = $totals->vat();
        $preceding = $head->preceding();
        [$keyReferences, $references] = $head->references($preceding['text']);

        return [
            'language' => $this->language,
            'documentTitle' => $headline . ($issuer !== null && $issuer['name'] !== null ? ' - ' . $issuer['name'] : ''),
            'pageTitle' => $issueDate === null ? $headline : $texts->get('value.dated', ['value' => $headline, 'date' => $issueDate]),
            'page' => $footer->page($legalFooter['fixed'] ? $legalFooter['lines'] : 0),
            'banners' => $head->banners(),
            'letterhead' => $parties->letterhead($issuer, $foreign),
            'returnLine' => $parties->returnLine($issuer, $foreign),
            'recipient' => $parties->recipient($addressee, $foreign),
            'title' => $heading,
            'marks' => array_values(array_filter([$addition, ...$head->badges()])),
            'subtitle' => $head->subtitle($title),
            'info' => $parties->info($issueDate, $number, $keyReferences, $seller, $buyer, $addressee),
            'references' => $references,
            'precedingHint' => $preceding['hint'],
            'parties' => $partyRows,
            'lines' => (new Lines($context))->build(),
            'allowances' => (new Allowances($context))->build(),
            'totals' => $totals->rows($vat),
            'vatNotes' => $totals->notes($vat),
            'payment' => $payment->build(),
            'notes' => (new Notes($context))->build($legalFooter['legal'], $heading),
            'attachments' => (new Attachments($context))->build(),
            'thirdParty' => $payment->thirdParty(),
            'further' => (new Further($context))->build(),
            'footer' => [
                'columns' => $legalFooter['columns'],
                'fixed' => $legalFooter['fixed'],
                'page' => $texts->get('footer.page'),
            ],
            'end' => [
                'readingAid' => $texts->get('footer.reading_aid'),
                'format' => $texts->get('footer.format', [
                    'specification' => $this->invoice->specification()->name() ?: '-',
                    'syntax' => self::SYNTAX_NAMES[$this->invoice->syntax()->value],
                ]),
                'standard' => $texts->get('footer.standard'),
            ],
        ];
    }
}
