<?php

declare(strict_types=1);

namespace Dealerweb\EInvoice\Render;

use RuntimeException;
use Throwable;

/**
 * Fills the template (resources/templates/invoice.php) with a prepared view.
 *
 * @internal
 */
final class Template
{
    /**
     * @param array<string, mixed> $view from ViewBuilder::build()
     * @param bool $pdf the document is meant for dompdf: page footer with page numbers, no screen frame
     */
    public static function render(array $view, Texts $texts, bool $pdf): string
    {
        $directory = dirname(__DIR__, 2) . '/resources/templates';
        $css = file_get_contents($directory . '/invoice.css');
        if ($css === false) {
            throw new RuntimeException("Cannot read $directory/invoice.css - the package is incomplete");
        }

        ob_start();
        try {
            (static function (array $view, Texts $t, string $css, bool $pdf, string $template): void {
                require $template;
            })($view, $texts, $css, $pdf, $directory . '/invoice.php');
        } catch (Throwable $exception) {
            ob_end_clean();

            throw $exception;
        }

        return (string) ob_get_clean();
    }
}
