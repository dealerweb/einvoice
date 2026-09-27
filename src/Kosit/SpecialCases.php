<?php

declare(strict_types=1);

namespace Dealerweb\EInvoice\Kosit;

use DOMDocumentFragment;
use DOMNode;
use RuntimeException;

/**
 * Hand-written counterparts of the few KoSIT XSLT snippets that XPath 1.0 cannot express
 * (xsl:for-each-group, distinct-values). Each one is guarded by a fingerprint of its snippet - when
 * KoSIT changes the snippet, the mapping is not compiled again until the code below has been
 * reviewed.
 *
 * @internal
 */
final class SpecialCases
{
    /**
     * @param array<string, array{nodes?: list<DOMNode>, fragment?: DOMDocumentFragment}> $vars
     */
    public static function run(string $key, Transformer $transformer, DOMNode $context, array $vars, DOMNode $parent): void
    {
        match ($key) {
            'ubl-invoice:root:BG-1', 'ubl-creditnote:root:BG-1' => self::ublNotes($transformer, $context, $vars, $parent),
            'ubl-invoice:root:BG-16', 'ubl-creditnote:root:BG-16' => self::ublPaymentInstructions($transformer, $context, $vars, $parent),
            'cii:root:BT-7' => self::ciiTaxPointDates($transformer, $context, $parent),
            'cii:root:BG-16' => self::ciiPaymentInstructions($transformer, $context, $parent),
            default => throw new RuntimeException("No special case implemented for $key"),
        };
    }

    /**
     * UBL notes (BG-1): a note consisting of exactly three capital letters followed by another
     * note is that note's subject code (BT-21).
     *
     *   <xsl:for-each-group select="./cbc:Note" group-by="
     *       if (following-sibling::cbc:Note and matches(., '^[A-Z]{3}$'))
     *       then generate-id(following-sibling::cbc:Note[1]) else generate-id(.)">
     *     <xr:INVOICE_NOTE> xr:id BG-1, xr:src = src-path($current-bg)
     *       if count(current-group()) gt 1: Invoice_note_subject_code (BT-21) = current-group()[1]
     *       Invoice_note (BT-22) = current-group()[last()]
     *
     * @param array<string, array{nodes?: list<DOMNode>, fragment?: DOMDocumentFragment}> $vars
     */
    private static function ublNotes(Transformer $transformer, DOMNode $context, array $vars, DOMNode $parent): void
    {
        $groups = [];
        foreach ($transformer->select('./cbc:Note', $context) as $note) {
            $next = $transformer->select('following-sibling::cbc:Note[1]', $note)[0] ?? null;
            $target = $next !== null && preg_match('/^[A-Z]{3}\z/', $transformer->stringValue($note)) ? $next : $note;
            $groups[$transformer->key($target)][] = $note;
        }

        $src = $transformer->srcPath($vars['current-bg']['nodes'][0] ?? $context);

        foreach ($groups as $group) {
            $element = $transformer->element($parent, 'INVOICE_NOTE', 'BG-1', $src);

            if (count($group) > 1) {
                self::value($transformer, $element, 'Invoice_note_subject_code', 'BT-21', $group[0]);
            }

            self::value($transformer, $element, 'Invoice_note', 'BT-22', $group[count($group) - 1]);
        }
    }

    /**
     * UBL payment instructions (BG-16), grouped by payment means code.
     *
     *   <xsl:for-each-group select="cac:PaymentMeans" group-by="cbc:PaymentMeansCode">
     *     <xr:PAYMENT_INSTRUCTIONS> xr:id BG-16, xr:src = src-path($current-bg)
     *       apply BT-81 current-group()[1]/cbc:PaymentMeansCode
     *       apply BT-82 current-group()[1]/cbc:PaymentMeansCode/@name
     *       for-each-group current-group()/cbc:PaymentID group-by text(): apply BT-83 current-group()[1]
     *       apply BG-17 current-group()/cac:PayeeFinancialAccount
     *       apply BG-18 current-group()/cac:CardAccount
     *       apply BG-19 current-group()/cac:PaymentMandate
     *
     * @param array<string, array{nodes?: list<DOMNode>, fragment?: DOMDocumentFragment}> $vars
     */
    private static function ublPaymentInstructions(Transformer $transformer, DOMNode $context, array $vars, DOMNode $parent): void
    {
        $src = $transformer->srcPath($vars['current-bg']['nodes'][0] ?? $context);

        foreach (self::groupBy($transformer, $transformer->select('cac:PaymentMeans', $context), 'cbc:PaymentMeansCode') as $group) {
            $element = $transformer->element($parent, 'PAYMENT_INSTRUCTIONS', 'BG-16', $src);

            $transformer->applyTemplates('BT-81', $transformer->select('cbc:PaymentMeansCode', $group[0]), $element);
            $transformer->applyTemplates('BT-82', $transformer->select('cbc:PaymentMeansCode/@name', $group[0]), $element);

            foreach (self::groupBy($transformer, $transformer->selectEach('cbc:PaymentID', $group), 'text()') as $ids) {
                $transformer->applyTemplates('BT-83', [$ids[0]], $element);
            }

            $transformer->applyTemplates('BG-17', $transformer->selectEach('cac:PayeeFinancialAccount', $group), $element);
            $transformer->applyTemplates('BG-18', $transformer->selectEach('cac:CardAccount', $group), $element);
            $transformer->applyTemplates('BG-19', $transformer->selectEach('cac:PaymentMandate', $group), $element);
        }
    }

    /**
     * CII value added tax point date (BT-7): the distinct dates of all tax breakdowns, joined by ';'.
     *
     *   <xsl:call-template name="distinct-bt-7">
     *     <xsl:with-param name="date-values" select="distinct-values(./rsm:SupplyChainTradeTransaction/
     *         ram:ApplicableHeaderTradeSettlement/ram:ApplicableTradeTax/ram:TaxPointDate/udt:DateString[@format = '102'])"/>
     *   distinct-bt-7: for-each $date-values: call date, ';' between
     */
    private static function ciiTaxPointDates(Transformer $transformer, DOMNode $context, DOMNode $parent): void
    {
        $values = [];
        $nodes = $transformer->select(
            "./rsm:SupplyChainTradeTransaction/ram:ApplicableHeaderTradeSettlement/ram:ApplicableTradeTax/ram:TaxPointDate/udt:DateString[@format = '102']",
            $context,
        );

        foreach ($nodes as $node) {
            $transformer->consume($node);
            $values[$transformer->stringValue($node)] = true;
        }

        $transformer->appendText($parent, implode(';', array_map(
            static fn(string $value): string => $transformer->date($value),
            array_map('strval', array_keys($values)),
        )));
    }

    /**
     * CII payment instructions (BG-16), grouped by payment means type code.
     *
     *   <xsl:for-each-group select="./rsm:SupplyChainTradeTransaction/ram:ApplicableHeaderTradeSettlement/
     *       ram:SpecifiedTradeSettlementPaymentMeans" group-by="ram:TypeCode">
     *     <xr:PAYMENT_INSTRUCTIONS> xr:id BG-16, xr:src = src-path(.)  (first item of the group)
     *       apply BT-81 current-group()[1]/ram:TypeCode
     *       apply BT-82 ./ram:Information
     *       apply BT-83 current-group()/../ram:PaymentReference
     *       apply BG-17 current-group()/ram:PayeePartyCreditorFinancialAccount
     *       apply BG-18 current-group()/ram:ApplicableTradeSettlementFinancialCard
     *       apply BG-19 current-group()/../../ram:ApplicableHeaderTradeSettlement
     */
    private static function ciiPaymentInstructions(Transformer $transformer, DOMNode $context, DOMNode $parent): void
    {
        $means = $transformer->select(
            './rsm:SupplyChainTradeTransaction/ram:ApplicableHeaderTradeSettlement/ram:SpecifiedTradeSettlementPaymentMeans',
            $context,
        );

        foreach (self::groupBy($transformer, $means, 'ram:TypeCode') as $group) {
            $element = $transformer->element($parent, 'PAYMENT_INSTRUCTIONS', 'BG-16', $transformer->srcPath($group[0]));

            $transformer->applyTemplates('BT-81', $transformer->select('ram:TypeCode', $group[0]), $element);
            $transformer->applyTemplates('BT-82', $transformer->select('./ram:Information', $group[0]), $element);
            $transformer->applyTemplates('BT-83', $transformer->selectEach('../ram:PaymentReference', $group), $element);
            $transformer->applyTemplates('BG-17', $transformer->selectEach('ram:PayeePartyCreditorFinancialAccount', $group), $element);
            $transformer->applyTemplates('BG-18', $transformer->selectEach('ram:ApplicableTradeSettlementFinancialCard', $group), $element);
            $transformer->applyTemplates('BG-19', $transformer->selectEach('../../ram:ApplicableHeaderTradeSettlement', $group), $element);
        }
    }

    /**
     * xsl:for-each-group with group-by: a node joins one group per value of the key expression
     * (none if the key is empty); groups keep the order of their first member.
     *
     * @param list<DOMNode> $nodes
     * @return list<list<DOMNode>>
     */
    private static function groupBy(Transformer $transformer, array $nodes, string $keyExpression): array
    {
        $groups = [];
        foreach ($nodes as $node) {
            foreach ($transformer->select($keyExpression, $node) as $key) {
                $groups['k:' . $transformer->stringValue($key)][] = $node;
            }
        }

        return array_values($groups);
    }

    private static function value(Transformer $transformer, DOMNode $parent, string $name, string $id, DOMNode $source): void
    {
        $element = $transformer->element($parent, $name, $id, $transformer->srcPath($source));
        $transformer->appendText($element, $transformer->stringValue($source));
        $transformer->consume($source);
    }
}
