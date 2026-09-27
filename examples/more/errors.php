<?php

declare(strict_types=1);

// What is thrown when reading or writing fails - and what it says. Reading throws a subclass of EInvoiceException;
// writing an InvalidArgumentException for a value that has no place or the wrong form (naming its path in the model),
// and InvalidInvoice when the official rules reject the invoice (with the report).
//
//     php examples/more/errors.php

use Dealerweb\EInvoice\Exception\EInvoiceException;
use Dealerweb\EInvoice\Exception\InvalidInvoice;
use Dealerweb\EInvoice\Invoice;
use Dealerweb\EInvoice\Profile;

require __DIR__ . '/../bootstrap.php';

$try = static function (string $what, callable $action): void {
    try {
        $action();
        echo "$what: no error\n";
    } catch (InvalidInvoice $e) {
        echo "$what: " . $e::class . "\n";
        foreach ($e->report()->errors() as $message) {
            echo "  {$message->code}: {$message->text}\n";
        }
    } catch (EInvoiceException|InvalidArgumentException $e) {
        echo "$what: " . $e::class . "\n  {$e->getMessage()}\n";
    }
};
$sample = static function (): Invoice {
    /** @var Invoice $invoice */
    $invoice = require __DIR__ . '/../sample-invoice.php';

    return $invoice;
};

// Reading
$try('Not XML', static fn() => Invoice::fromXml('an invoice?'));
$order = '<Order xmlns="urn:oasis:names:specification:ubl:schema:xsd:Order-2"/>';
$try('An order, no invoice', static fn() => Invoice::fromXml($order));
$try('A PDF without an invoice', static fn() => Invoice::fromFile(input('invoice.pdf')));
$try('A file that is not there', static fn() => Invoice::fromFile(__DIR__ . '/missing.xml'));

// Writing: a value in the wrong form, a value the profile has no place for, an invoice the rules reject.
$try('A quantity with a comma', static function () use ($sample): void {
    $invoice = $sample();
    $invoice->lines[0]->quantity = '1,5';
    $invoice->toXml(Profile::XRechnung);
});
$try('A contact person in BASIC', static fn() => $sample()->toXml(Profile::Basic));
$try('No seller name', static function () use ($sample): void {
    $invoice = $sample();
    $invoice->seller->name = null;
    $invoice->toXml(Profile::XRechnung);
});
