<?php

/**
 * Template of the rendered invoice (HtmlRenderer, PdfRenderer), laid out like a German business
 * letter. Every value comes prepared from ViewBuilder - the template only escapes and arranges.
 * No scripts, no external resources.
 *
 * In the PDF the legal footer of the issuer is a fixed element, repeated on every page, and dompdf
 * places it only on the pages that follow it in the document - it therefore comes first. On screen,
 * and in the PDF where it is too high for every page, it stands at the end of the sheet.
 *
 * @var array<string, mixed> $view
 * @var \Dealerweb\EInvoice\Render\Texts $t
 * @var string $css
 * @var bool $pdf
 */

$e = static fn(mixed $value): string => htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, 'UTF-8');
$nl = static fn(mixed $value): string => nl2br($e($value), false);

$kv = static function (array $rows, string $class = 'kv') use ($e, $nl): string {
    if ($rows === []) {
        return '';
    }

    $html = '<table class="' . $class . '">';
    foreach ($rows as [$key, $value]) {
        $html .= '<tr><td class="key">' . $e($key) . '</td><td class="value">' . $nl($value) . '</td></tr>';
    }

    return $html . '</table>';
};

// Additional values in one line: "label value · label value".
$inline = static fn(array $rows): string => implode(' · ', array_map(
    static fn(array $row): string => '<span class="muted">' . $e($row[0]) . '</span> ' . $e($row[1]),
    $rows
));

// A party in one row: the name in bold, the address after it, details in small print below.
$party = static function (array $party) use ($e): string {
    $html = $party['name'] !== null ? '<strong>' . $e($party['name']) . '</strong>' . ($party['text'] !== null ? ', ' : '') : '';
    $html .= $e($party['text']);

    return $html . ($party['details'] !== null ? '<div class="party-details">' . $e($party['details']) . '</div>' : '');
};

// The columns side by side - or one below the other, where the footer has to break across pages
// (dompdf never splits a table row).
$footer = static function (array $columns, bool $stacked = false) use ($e, $nl): string {
    if ($columns === []) {
        return '';
    }

    $html = $stacked ? '' : '<table class="footer-columns columns-' . count($columns) . '"><tr>';
    foreach ($columns as $column) {
        $html .= $stacked ? '<div class="footer-column">' : '<td>';
        foreach ($column as $row) {
            $html .= '<div' . ($row['strong'] ? ' class="strong"' : '') . '>'
                . ($row['label'] !== null ? '<span class="label">' . $e($row['label']) . '</span> ' : '')
                . $nl($row['text']) . '</div>';
        }
        $html .= $stacked ? '</div>' : '</td>';
    }

    return $stacked ? $html : $html . '</tr></table>';
};

$page = $view['page'];
$lines = $view['lines'];
$payment = $view['payment'];

$means = '';
foreach ($payment['instructions'] as $instruction) {
    $means .= '<div class="instruction">';
    if ($instruction['title'] !== null) {
        $means .= '<div class="instruction-title">' . $e($instruction['title']) . '</div>';
    }
    if ($instruction['text'] !== null) {
        $means .= '<div class="small muted">' . $nl($instruction['text']) . '</div>';
    }
    $means .= $kv([...$instruction['rows'], ...$instruction['extras']]) . '</div>';
}

$terms = '';
if ($payment['dueDate'] !== null) {
    $terms .= $kv([[$t->get('payment.due_date'), $payment['dueDate']]]);
}
if ($payment['discounts'] !== []) {
    $terms .= '<div class="caption">' . $e($t->get('payment.discount')) . '</div><ul class="terms-list">';
    foreach ($payment['discounts'] as $discount) {
        $terms .= '<li>' . $e($discount) . '</li>';
    }
    $terms .= '</ul>';
}
if ($payment['terms'] !== null) {
    $terms .= '<div class="caption">' . $e($t->get('payment.terms')) . '</div><p>' . $nl($payment['terms']) . '</p>';
}

?><!DOCTYPE html>
<html lang="<?= $e($view['language']) ?>">
<head>
<meta charset="utf-8">
<meta name="generator" content="dealerweb/einvoice">
<title><?= $e($view['documentTitle']) ?></title>
<style>
<?= $css ?>

@page {
    margin: <?= $page['marginTop'] ?>mm <?= $page['marginRight'] ?>mm <?= $page['marginBottom'] ?>mm <?= $page['marginLeft'] ?>mm;
}

.page-footer.is-fixed {
    top: <?= $page['footerTop'] ?>mm;
}
</style>
</head>
<body class="<?= $pdf ? 'pdf' : 'screen' ?>">
<?php if ($pdf && $view['footer']['fixed']): ?>
<div class="page-footer is-fixed"><?= $footer($view['footer']['columns']) ?></div>
<?php endif ?>
<div class="sheet">

    <table class="letterhead">
        <tr>
            <td>
<?php if ($view['letterhead'] !== null): ?>
<?php if ($view['letterhead']['name'] !== null): ?>
                <div class="issuer-name"><?= $nl($view['letterhead']['name']) ?></div>
<?php endif ?>
<?php if ($view['letterhead']['tradingName'] !== null): ?>
                <div class="issuer-trading"><?= $nl($view['letterhead']['tradingName']) ?></div>
<?php endif ?>
<?php if ($view['letterhead']['address'] !== null): ?>
                <div class="issuer-address"><?= $e($view['letterhead']['address']) ?></div>
<?php endif ?>
<?php endif ?>
            </td>
        </tr>
    </table>

    <table class="address-row">
        <tr>
            <td class="addressee">
<?php if ($view['returnLine'] !== null): ?>
                <div class="return-line"><?= $e($view['returnLine']) ?></div>
<?php endif ?>
<?php if ($view['recipient'] !== null): ?>
                <div class="recipient"><?= implode('<br>', array_map($nl, $view['recipient'])) ?></div>
<?php endif ?>
            </td>
            <td class="gutter"></td>
            <td class="info">
                <h1><?= $e($view['title']) ?></h1>
<?php if ($view['marks'] !== [] || $view['subtitle'] !== null): ?>
                <div class="marks">
<?php foreach ($view['marks'] as $mark): ?>
                    <span class="badge"><?= $e($mark) ?></span>
<?php endforeach ?>
<?php if ($view['subtitle'] !== null): ?>
                    <span class="subtitle"><?= $e($view['subtitle']) ?></span>
<?php endif ?>
                </div>
<?php endif ?>
                <?= $kv($view['info'], 'info') ?>

            </td>
        </tr>
    </table>

<?php foreach ($view['banners'] as $banner): ?>
    <div class="banner banner-<?= $e($banner['kind']) ?>"><?= $e($banner['text']) ?></div>
<?php endforeach ?>

<?php if ($view['references'] !== [] || $view['parties'] !== []): ?>
    <table class="references">
<?php foreach (array_chunk($view['references'], 2) as $pair): ?>
        <tr>
            <td class="key"><?= $e($pair[0][0]) ?></td>
            <td class="value"><?= $nl($pair[0][1]) ?></td>
            <td class="key"><?= $e($pair[1][0] ?? '') ?></td>
            <td class="value"><?= $nl($pair[1][1] ?? '') ?></td>
        </tr>
<?php endforeach ?>
<?php foreach ($view['parties'] as $row): ?>
        <tr class="party">
            <td class="key"><?= $e($row['title']) ?></td>
            <td class="value" colspan="3"><?= $party($row) ?></td>
        </tr>
<?php endforeach ?>
    </table>
<?php endif ?>
<?php if ($view['precedingHint'] !== null): ?>
    <p class="hint"><?= $e($view['precedingHint']) ?></p>
<?php endif ?>

<?php if ($lines['tables'] === []): ?>
    <p class="no-lines muted"><?= $e($lines['none']) ?></p>
<?php else: ?>
<?php foreach ($lines['tables'] as $rows): ?>
    <table class="lines">
        <thead>
            <tr>
                <th class="col-pos"><?= $e($t->get('lines.pos')) ?></th>
<?php if ($lines['hasArticle']): ?>
                <th class="col-article"><?= $e($t->get('lines.article')) ?></th>
<?php endif ?>
                <th class="col-item"><?= $e($t->get('lines.description')) ?></th>
                <th class="col-quantity num"><?= $e($t->get('lines.quantity')) ?></th>
                <th class="col-price num"><?= $e($t->get('lines.unit_price')) ?></th>
                <th class="col-vat num"><?= $e($t->get('lines.vat')) ?></th>
                <th class="col-amount num"><?= $e($t->get('lines.amount')) ?></th>
            </tr>
        </thead>
        <tbody>
<?php foreach ($rows as $row): ?>
            <tr class="<?= $e($row['class']) ?>">
                <td><?= $e($row['position']) ?></td>
<?php if ($lines['hasArticle']): ?>
                <td class="article"><?= $e($row['article']) ?></td>
<?php endif ?>
                <td>
                    <div class="item">
<?php foreach ($row['blocks'] as $block): ?>
                        <div class="<?= $e($block['class']) ?>"><?= $nl($block['text']) ?></div>
<?php endforeach ?>
                    </div>
                </td>
                <td class="num quantity"><?= $e($row['quantity']) ?></td>
                <td class="num"><?= $e($row['price']) ?><?php if ($row['priceBasis'] !== null): ?><div class="price-basis"><?= $e($row['priceBasis']) ?></div><?php endif ?></td>
                <td class="num vat"><?= $e($row['vat']) ?></td>
                <td class="num"><?= $e($row['amount']) ?></td>
            </tr>
<?php endforeach ?>
        </tbody>
    </table>
<?php endforeach ?>
<?php if ($lines['hasSubLines']): ?>
    <p class="lines-hint"><?= $e($t->get('lines.sub_hint')) ?></p>
<?php endif ?>
<?php endif ?>

<?php if ($view['allowances'] !== []): ?>
    <h2><?= $e($t->get('section.document_allowances')) ?></h2>
    <table class="list">
        <thead>
            <tr>
                <th><?= $e($t->get('ac.reason')) ?></th>
                <th class="num"><?= $e($t->get('ac.base')) ?></th>
                <th class="num"><?= $e($t->get('ac.percent')) ?></th>
                <th class="num"><?= $e($t->get('ac.vat')) ?></th>
                <th class="num"><?= $e($t->get('ac.amount')) ?></th>
            </tr>
        </thead>
        <tbody>
<?php foreach ($view['allowances'] as $allowance): ?>
            <tr<?= $allowance['more'] !== [] ? ' class="continued"' : ($allowance['extras'] !== [] ? ' class="has-extra"' : '') ?>>
                <td><strong><?= $e($allowance['type']) ?></strong><?= $allowance['reason'] !== null ? ' ' . $nl($allowance['reason']) : '' ?></td>
                <td class="num"><?= $e($allowance['base']) ?></td>
                <td class="num"><?= $e($allowance['percent']) ?></td>
                <td class="num vat"><?= $e($allowance['vat']) ?></td>
                <td class="num"><?= $e($allowance['amount']) ?></td>
            </tr>
<?php foreach ($allowance['more'] as $index => $piece): ?>
            <tr class="continuation<?= $index < count($allowance['more']) - 1 ? ' continued' : ($allowance['extras'] !== [] ? ' has-extra' : '') ?>"><td><?= $nl($piece) ?></td><td colspan="4"></td></tr>
<?php endforeach ?>
<?php if ($allowance['extras'] !== []): ?>
            <tr class="extra"><td colspan="5" class="small"><?= $inline($allowance['extras']) ?></td></tr>
<?php endif ?>
<?php endforeach ?>
        </tbody>
    </table>
<?php endif ?>

<?php if ($view['totals'] !== [] || $view['vatNotes'] !== []): ?>
    <div class="summary">
<?php if ($view['totals'] !== []): ?>
        <table class="summary-row">
            <tr>
                <td class="summary-space"></td>
                <td>
                    <table class="totals">
<?php foreach ($view['totals'] as $total): ?>
                        <tr<?= $total['kind'] !== '' ? ' class="' . $e($total['kind']) . '"' : '' ?>><td><?= $e($total['label']) ?></td><td class="num"><?= $e($total['value']) ?></td></tr>
<?php endforeach ?>
                    </table>
                </td>
            </tr>
        </table>
<?php endif ?>
<?php foreach ($view['vatNotes'] as $note): ?>
        <p class="vat-note"><?= $nl($note) ?></p>
<?php endforeach ?>
    </div>
<?php endif ?>

<?php if ($means !== '' || $terms !== ''): ?>
    <div<?= $payment['long'] ? '' : ' class="keep"' ?>>
    <h2><?= $e($t->get('section.payment')) ?></h2>
<?php if ($means !== '' && $terms !== '' && ! $payment['long']): ?>
    <table class="columns">
        <tr>
            <td class="left"><?= $means ?></td>
            <td class="right"><?= $terms ?></td>
        </tr>
    </table>
<?php else: ?>
    <?= $means . $terms ?>
<?php endif ?>
    </div>
<?php endif ?>

<?php if ($view['notes'] !== []): ?>
    <h2><?= $e($t->get('section.notes')) ?></h2>
    <table class="notes">
<?php foreach ($view['notes'] as $note): ?>
<?php foreach ($note['rows'] as $row): ?>
        <tr class="<?= $e($row['class']) ?>">
<?php if ($row['key'] !== null): ?>
            <td class="key"><?= $e($row['key']) ?></td>
            <td class="value">
<?php else: ?>
            <td class="value" colspan="2">
<?php endif ?>
<?php if ($row['text'] !== null): ?>
                <?= $nl($row['text']) ?>
<?php else: ?>
                <div class="small"><?= $inline($row['extras']) ?></div>
<?php endif ?>
            </td>
        </tr>
<?php endforeach ?>
<?php endforeach ?>
    </table>
<?php endif ?>

<?php if ($view['attachments'] !== []): ?>
    <h2><?= $e($t->get('section.attachments')) ?></h2>
    <table class="list">
        <thead>
            <tr>
                <th><?= $e($t->get('attachments.reference')) ?></th>
                <th><?= $e($t->get('attachments.description')) ?></th>
                <th><?= $e($t->get('attachments.file')) ?></th>
            </tr>
        </thead>
        <tbody>
<?php foreach ($view['attachments'] as $attachment): ?>
            <tr<?= $attachment['more'] !== [] ? ' class="continued"' : '' ?>>
                <td><?= $e($attachment['reference']) ?></td>
                <td>
                    <?= $nl($attachment['description']) ?>
<?php if ($attachment['extras'] !== []): ?>
                    <div class="small"><?= $inline($attachment['extras']) ?></div>
<?php endif ?>
                </td>
                <td>
                    <?= $e($attachment['file']) ?>
<?php if ($attachment['location'] !== null): ?>
                    <div class="small"><span class="muted"><?= $e($t->get('attachments.location')) ?>:</span> <?= $e($attachment['location']) ?></div>
<?php endif ?>
                </td>
            </tr>
<?php foreach ($attachment['more'] as $index => $piece): ?>
            <tr class="continuation<?= $index < count($attachment['more']) - 1 ? ' continued' : '' ?>"><td></td><td><?= $nl($piece) ?></td><td></td></tr>
<?php endforeach ?>
<?php endforeach ?>
        </tbody>
    </table>
<?php endif ?>

<?php if ($view['thirdParty'] !== []): ?>
    <h2><?= $e($t->get('section.third_party')) ?></h2>
    <table class="list">
        <thead>
            <tr>
                <th><?= $e($t->get('third_party.type')) ?></th>
                <th><?= $e($t->get('third_party.description')) ?></th>
                <th class="num"><?= $e($t->get('third_party.amount')) ?></th>
            </tr>
        </thead>
        <tbody>
<?php foreach ($view['thirdParty'] as $thirdParty): ?>
            <tr<?= $thirdParty['more'] !== [] ? ' class="continued"' : '' ?>>
                <td><?= $e($thirdParty['type']) ?></td>
                <td><?= $nl($thirdParty['description']) ?></td>
                <td class="num"><?= $e($thirdParty['amount']) ?></td>
            </tr>
<?php foreach ($thirdParty['more'] as $index => $piece): ?>
            <tr class="continuation<?= $index < count($thirdParty['more']) - 1 ? ' continued' : '' ?>"><td></td><td><?= $nl($piece) ?></td><td></td></tr>
<?php endforeach ?>
<?php endforeach ?>
        </tbody>
    </table>
<?php endif ?>

<?php if ($view['further'] !== []): ?>
    <h2><?= $e($t->get('section.further')) ?></h2>
<?php foreach ($view['further'] as $section): ?>
<?php if ($section['title'] !== null): ?>
    <div class="further-title"><?= $e($section['title']) ?></div>
<?php endif ?>
    <?= $kv($section['rows'], 'kv small') ?>
<?php endforeach ?>
<?php endif ?>

    <div class="document-end">
        <p class="reading-aid"><?= $e($view['end']['readingAid']) ?></p>
        <p><?= $e($view['end']['format']) ?></p>
        <p><?= $e($view['end']['standard']) ?></p>
    </div>

<?php if (! $pdf || ! $view['footer']['fixed']): ?>
    <div class="page-footer is-end"><?= $footer($view['footer']['columns'], $pdf) ?></div>
<?php endif ?>
</div>
</body>
</html>
