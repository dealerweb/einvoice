<?php

// KoSIT cii-xr.xsl compiled into an instruction tree for Dealerweb\EInvoice\Kosit\Transformer.
// Generated - do not edit.

return [
    'source' => 'cii-xr.xsl',
    'sha1' => 'd26c32cc6846e7527b875c6a4bd0a50aa5650d72',
    'namespaces' => [
        'qdt' => 'urn:un:unece:uncefact:data:standard:QualifiedDataType:100',
        'ram' => 'urn:un:unece:uncefact:data:standard:ReusableAggregateBusinessInformationEntity:100',
        'rsm' => 'urn:un:unece:uncefact:data:standard:CrossIndustryInvoice:100',
        'udt' => 'urn:un:unece:uncefact:data:standard:UnqualifiedDataType:100',
        'xr' => 'urn:ce.eu:en16931:2017:xoev-de:kosit:standard:xrechnung-1',
    ],
    'indicators' => [
        'udt:Indicator',
    ],
    'root' => [
        'match' => '/rsm:CrossIndustryInvoice',
        'priority' => 0.5,
        'body' => [
            [
                'op' => 'element',
                'name' => 'invoice',
                'body' => [
                    [
                        'op' => 'variable',
                        'name' => 'current-bg',
                        'select' => [
                            'xpath' => '.',
                        ],
                    ],
                    [
                        'op' => 'apply',
                        'mode' => 'BT-1',
                        'select' => [
                            'xpath' => './rsm:ExchangedDocument/ram:ID',
                        ],
                    ],
                    [
                        'op' => 'apply',
                        'mode' => 'BT-2',
                        'select' => [
                            'xpath' => './rsm:ExchangedDocument/ram:IssueDateTime/udt:DateTimeString[@format = \'102\']',
                        ],
                    ],
                    [
                        'op' => 'apply',
                        'mode' => 'BT-3',
                        'select' => [
                            'xpath' => './rsm:ExchangedDocument/ram:TypeCode',
                        ],
                    ],
                    [
                        'op' => 'apply',
                        'mode' => 'BT-5',
                        'select' => [
                            'xpath' => './rsm:SupplyChainTradeTransaction/ram:ApplicableHeaderTradeSettlement/ram:InvoiceCurrencyCode',
                        ],
                    ],
                    [
                        'op' => 'apply',
                        'mode' => 'BT-6',
                        'select' => [
                            'xpath' => './rsm:SupplyChainTradeTransaction/ram:ApplicableHeaderTradeSettlement/ram:TaxCurrencyCode',
                        ],
                    ],
                    [
                        'op' => 'if',
                        'test' => [
                            'xpath' => 'count(./rsm:SupplyChainTradeTransaction/ram:ApplicableHeaderTradeSettlement/ram:ApplicableTradeTax/ram:TaxPointDate/udt:DateString[@format = \'102\']) > 0',
                        ],
                        'body' => [
                            [
                                'op' => 'element',
                                'name' => 'Value_added_tax_point_date',
                                'body' => [
                                    [
                                        'op' => 'attribute',
                                        'name' => 'xr:id',
                                        'value' => [
                                            'literal' => 'BT-7',
                                        ],
                                    ],
                                    [
                                        'op' => 'attribute',
                                        'name' => 'xr:src',
                                        'value' => [
                                            'literal' => '/rsm:CrossIndustryInvoice/rsm:SupplyChainTradeTransaction/ram:ApplicableHeaderTradeSettlement/ram:ApplicableTradeTax/ram:TaxPointDate/udt:DateString',
                                        ],
                                    ],
                                    [
                                        'op' => 'special',
                                        'key' => 'cii:root:BT-7',
                                    ],
                                ],
                            ],
                        ],
                    ],
                    [
                        'op' => 'apply',
                        'mode' => 'BT-8',
                        'select' => [
                            'xpath' => './rsm:SupplyChainTradeTransaction/ram:ApplicableHeaderTradeSettlement/ram:ApplicableTradeTax/ram:DueDateTypeCode',
                        ],
                    ],
                    [
                        'op' => 'if',
                        'test' => [
                            'xpath' => 'count(./rsm:SupplyChainTradeTransaction/ram:ApplicableHeaderTradeSettlement/ram:SpecifiedTradePaymentTerms/ram:DueDateDateTime/udt:DateTimeString[@format = \'102\']) > 0',
                        ],
                        'body' => [
                            [
                                'op' => 'element',
                                'name' => 'Payment_due_date',
                                'body' => [
                                    [
                                        'op' => 'attribute',
                                        'name' => 'xr:id',
                                        'value' => [
                                            'literal' => 'BT-9',
                                        ],
                                    ],
                                    [
                                        'op' => 'attribute',
                                        'name' => 'xr:src',
                                        'value' => [
                                            'literal' => '/rsm:CrossIndustryInvoice/rsm:SupplyChainTradeTransaction/ram:ApplicableHeaderTradeSettlement/ram:SpecifiedTradePaymentTerms/ram:DueDateDateTime',
                                        ],
                                    ],
                                    [
                                        'op' => 'apply',
                                        'mode' => 'BT-9',
                                        'select' => [
                                            'xpath' => './rsm:SupplyChainTradeTransaction/ram:ApplicableHeaderTradeSettlement/ram:SpecifiedTradePaymentTerms/ram:DueDateDateTime/udt:DateTimeString[@format = \'102\']',
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                    [
                        'op' => 'apply',
                        'mode' => 'BT-10',
                        'select' => [
                            'xpath' => './rsm:SupplyChainTradeTransaction/ram:ApplicableHeaderTradeAgreement/ram:BuyerReference',
                        ],
                    ],
                    [
                        'op' => 'apply',
                        'mode' => 'BT-11',
                        'select' => [
                            'xpath' => './rsm:SupplyChainTradeTransaction/ram:ApplicableHeaderTradeAgreement/ram:SpecifiedProcuringProject/ram:ID',
                        ],
                    ],
                    [
                        'op' => 'apply',
                        'mode' => 'BT-12',
                        'select' => [
                            'xpath' => './rsm:SupplyChainTradeTransaction/ram:ApplicableHeaderTradeAgreement/ram:ContractReferencedDocument/ram:IssuerAssignedID',
                        ],
                    ],
                    [
                        'op' => 'apply',
                        'mode' => 'BT-13',
                        'select' => [
                            'xpath' => './rsm:SupplyChainTradeTransaction/ram:ApplicableHeaderTradeAgreement/ram:BuyerOrderReferencedDocument/ram:IssuerAssignedID',
                        ],
                    ],
                    [
                        'op' => 'apply',
                        'mode' => 'BT-14',
                        'select' => [
                            'xpath' => './rsm:SupplyChainTradeTransaction/ram:ApplicableHeaderTradeAgreement/ram:SellerOrderReferencedDocument/ram:IssuerAssignedID',
                        ],
                    ],
                    [
                        'op' => 'apply',
                        'mode' => 'BT-15',
                        'select' => [
                            'xpath' => './rsm:SupplyChainTradeTransaction/ram:ApplicableHeaderTradeDelivery/ram:ReceivingAdviceReferencedDocument/ram:IssuerAssignedID',
                        ],
                    ],
                    [
                        'op' => 'apply',
                        'mode' => 'BT-16',
                        'select' => [
                            'xpath' => './rsm:SupplyChainTradeTransaction/ram:ApplicableHeaderTradeDelivery/ram:DespatchAdviceReferencedDocument/ram:IssuerAssignedID',
                        ],
                    ],
                    [
                        'op' => 'apply',
                        'mode' => 'BT-17',
                        'select' => [
                            'xpath' => './rsm:SupplyChainTradeTransaction/ram:ApplicableHeaderTradeAgreement/ram:AdditionalReferencedDocument/ram:IssuerAssignedID[following-sibling::ram:TypeCode=\'50\']',
                        ],
                    ],
                    [
                        'op' => 'apply',
                        'mode' => 'BT-18',
                        'select' => [
                            'xpath' => './rsm:SupplyChainTradeTransaction/ram:ApplicableHeaderTradeAgreement/ram:AdditionalReferencedDocument/ram:IssuerAssignedID[following-sibling::ram:TypeCode=\'130\']',
                        ],
                    ],
                    [
                        'op' => 'apply',
                        'mode' => 'BT-19',
                        'select' => [
                            'xpath' => './rsm:SupplyChainTradeTransaction/ram:ApplicableHeaderTradeSettlement/ram:ReceivableSpecifiedTradeAccountingAccount/ram:ID',
                        ],
                    ],
                    [
                        'op' => 'if',
                        'test' => [
                            'xpath' => 'count(./rsm:SupplyChainTradeTransaction/ram:ApplicableHeaderTradeSettlement/ram:SpecifiedTradePaymentTerms/ram:Description) > 0',
                        ],
                        'body' => [
                            [
                                'op' => 'element',
                                'name' => 'Payment_terms',
                                'body' => [
                                    [
                                        'op' => 'attribute',
                                        'name' => 'xr:id',
                                        'value' => [
                                            'literal' => 'BT-20',
                                        ],
                                    ],
                                    [
                                        'op' => 'attribute',
                                        'name' => 'xr:src',
                                        'value' => [
                                            'literal' => '/rsm:CrossIndustryInvoice/rsm:SupplyChainTradeTransaction/ram:ApplicableHeaderTradeSettlement/ram:SpecifiedTradePaymentTerms/ram:Description',
                                        ],
                                    ],
                                    [
                                        'op' => 'apply',
                                        'mode' => 'BT-20',
                                        'select' => [
                                            'xpath' => './rsm:SupplyChainTradeTransaction/ram:ApplicableHeaderTradeSettlement/ram:SpecifiedTradePaymentTerms/ram:Description',
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                    [
                        'op' => 'apply',
                        'mode' => 'BG-1',
                        'select' => [
                            'xpath' => './rsm:ExchangedDocument/ram:IncludedNote',
                        ],
                    ],
                    [
                        'op' => 'apply',
                        'mode' => 'BG-2',
                        'select' => [
                            'xpath' => './rsm:ExchangedDocumentContext',
                        ],
                    ],
                    [
                        'op' => 'apply',
                        'mode' => 'BG-3',
                        'select' => [
                            'xpath' => './rsm:SupplyChainTradeTransaction/ram:ApplicableHeaderTradeSettlement/ram:InvoiceReferencedDocument',
                        ],
                    ],
                    [
                        'op' => 'apply',
                        'mode' => 'BG-4',
                        'select' => [
                            'xpath' => './rsm:SupplyChainTradeTransaction/ram:ApplicableHeaderTradeAgreement/ram:SellerTradeParty',
                        ],
                    ],
                    [
                        'op' => 'apply',
                        'mode' => 'BG-7',
                        'select' => [
                            'xpath' => './rsm:SupplyChainTradeTransaction/ram:ApplicableHeaderTradeAgreement/ram:BuyerTradeParty',
                        ],
                    ],
                    [
                        'op' => 'apply',
                        'mode' => 'BG-10',
                        'select' => [
                            'xpath' => './rsm:SupplyChainTradeTransaction/ram:ApplicableHeaderTradeSettlement/ram:PayeeTradeParty',
                        ],
                    ],
                    [
                        'op' => 'apply',
                        'mode' => 'BG-11',
                        'select' => [
                            'xpath' => './rsm:SupplyChainTradeTransaction/ram:ApplicableHeaderTradeAgreement/ram:SellerTaxRepresentativeTradeParty',
                        ],
                    ],
                    [
                        'op' => 'apply',
                        'mode' => 'BG-13',
                        'select' => [
                            'xpath' => './rsm:SupplyChainTradeTransaction [ boolean( ram:ApplicableHeaderTradeDelivery/ram:ShipToTradeParty) or boolean( ram:ApplicableHeaderTradeDelivery/ram:ActualDeliverySupplyChainEvent/ram:OccurrenceDateTime/udt:DateTimeString) or boolean( ram:ApplicableHeaderTradeSettlement/ram:BillingSpecifiedPeriod) ]',
                        ],
                    ],
                    [
                        'op' => 'apply',
                        'mode' => 'BG-14',
                        'select' => [
                            'xpath' => '/rsm:CrossIndustryInvoice/rsm:SupplyChainTradeTransaction/ram:ApplicableHeaderTradeSettlement/ram:BillingSpecifiedPeriod',
                        ],
                    ],
                    [
                        'op' => 'special',
                        'key' => 'cii:root:BG-16',
                    ],
                    [
                        'op' => 'apply',
                        'mode' => 'BG-20',
                        'select' => [
                            'xpath' => './rsm:SupplyChainTradeTransaction/ram:ApplicableHeaderTradeSettlement/ram:SpecifiedTradeAllowanceCharge[ram:ChargeIndicator/udt:Indicator=\'false\']',
                        ],
                    ],
                    [
                        'op' => 'apply',
                        'mode' => 'BG-21',
                        'select' => [
                            'xpath' => './rsm:SupplyChainTradeTransaction/ram:ApplicableHeaderTradeSettlement/ram:SpecifiedTradeAllowanceCharge[ram:ChargeIndicator/udt:Indicator=\'true\']',
                        ],
                    ],
                    [
                        'op' => 'apply',
                        'mode' => 'BG-22',
                        'select' => [
                            'xpath' => './rsm:SupplyChainTradeTransaction/ram:ApplicableHeaderTradeSettlement/ram:SpecifiedTradeSettlementHeaderMonetarySummation',
                        ],
                    ],
                    [
                        'op' => 'apply',
                        'mode' => 'BG-23',
                        'select' => [
                            'xpath' => './rsm:SupplyChainTradeTransaction/ram:ApplicableHeaderTradeSettlement/ram:ApplicableTradeTax',
                        ],
                    ],
                    [
                        'op' => 'apply',
                        'mode' => 'BG-24',
                        'select' => [
                            'xpath' => './rsm:SupplyChainTradeTransaction/ram:ApplicableHeaderTradeAgreement/ram:AdditionalReferencedDocument',
                        ],
                    ],
                    [
                        'op' => 'apply',
                        'mode' => 'BG-25',
                        'select' => [
                            'xpath' => './rsm:SupplyChainTradeTransaction/ram:IncludedSupplyChainTradeLineItem',
                        ],
                    ],
                ],
            ],
        ],
    ],
    'modes' => [
        'BT-1' => [
            [
                'match' => '/rsm:CrossIndustryInvoice/rsm:ExchangedDocument/ram:ID',
                'priority' => 0.5,
                'body' => [
                    [
                        'op' => 'element',
                        'name' => 'Invoice_number',
                        'body' => [
                            [
                                'op' => 'attribute',
                                'name' => 'xr:id',
                                'value' => [
                                    'literal' => 'BT-1',
                                ],
                            ],
                            [
                                'op' => 'attribute',
                                'name' => 'xr:src',
                                'value' => [
                                    'srcpath' => [
                                        'xpath' => '.',
                                    ],
                                ],
                            ],
                            [
                                'op' => 'call',
                                'name' => 'identifier',
                                'params' => [],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        'BT-2' => [
            [
                'match' => '/rsm:CrossIndustryInvoice/rsm:ExchangedDocument/ram:IssueDateTime/udt:DateTimeString[@format = \'102\']',
                'priority' => 0.5,
                'body' => [
                    [
                        'op' => 'element',
                        'name' => 'Invoice_issue_date',
                        'body' => [
                            [
                                'op' => 'attribute',
                                'name' => 'xr:id',
                                'value' => [
                                    'literal' => 'BT-2',
                                ],
                            ],
                            [
                                'op' => 'attribute',
                                'name' => 'xr:src',
                                'value' => [
                                    'srcpath' => [
                                        'xpath' => '.',
                                    ],
                                ],
                            ],
                            [
                                'op' => 'call',
                                'name' => 'date',
                                'params' => [],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        'BT-3' => [
            [
                'match' => '/rsm:CrossIndustryInvoice/rsm:ExchangedDocument/ram:TypeCode',
                'priority' => 0.5,
                'body' => [
                    [
                        'op' => 'element',
                        'name' => 'Invoice_type_code',
                        'body' => [
                            [
                                'op' => 'attribute',
                                'name' => 'xr:id',
                                'value' => [
                                    'literal' => 'BT-3',
                                ],
                            ],
                            [
                                'op' => 'attribute',
                                'name' => 'xr:src',
                                'value' => [
                                    'srcpath' => [
                                        'xpath' => '.',
                                    ],
                                ],
                            ],
                            [
                                'op' => 'call',
                                'name' => 'code',
                                'params' => [],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        'BT-5' => [
            [
                'match' => '/rsm:CrossIndustryInvoice/rsm:SupplyChainTradeTransaction/ram:ApplicableHeaderTradeSettlement/ram:InvoiceCurrencyCode',
                'priority' => 0.5,
                'body' => [
                    [
                        'op' => 'element',
                        'name' => 'Invoice_currency_code',
                        'body' => [
                            [
                                'op' => 'attribute',
                                'name' => 'xr:id',
                                'value' => [
                                    'literal' => 'BT-5',
                                ],
                            ],
                            [
                                'op' => 'attribute',
                                'name' => 'xr:src',
                                'value' => [
                                    'srcpath' => [
                                        'xpath' => '.',
                                    ],
                                ],
                            ],
                            [
                                'op' => 'call',
                                'name' => 'code',
                                'params' => [],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        'BT-6' => [
            [
                'match' => '/rsm:CrossIndustryInvoice/rsm:SupplyChainTradeTransaction/ram:ApplicableHeaderTradeSettlement/ram:TaxCurrencyCode',
                'priority' => 0.5,
                'body' => [
                    [
                        'op' => 'element',
                        'name' => 'VAT_accounting_currency_code',
                        'body' => [
                            [
                                'op' => 'attribute',
                                'name' => 'xr:id',
                                'value' => [
                                    'literal' => 'BT-6',
                                ],
                            ],
                            [
                                'op' => 'attribute',
                                'name' => 'xr:src',
                                'value' => [
                                    'srcpath' => [
                                        'xpath' => '.',
                                    ],
                                ],
                            ],
                            [
                                'op' => 'call',
                                'name' => 'code',
                                'params' => [],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        'BT-8' => [
            [
                'match' => '/rsm:CrossIndustryInvoice/rsm:SupplyChainTradeTransaction/ram:ApplicableHeaderTradeSettlement/ram:ApplicableTradeTax/ram:DueDateTypeCode',
                'priority' => 0.5,
                'body' => [
                    [
                        'op' => 'element',
                        'name' => 'Value_added_tax_point_date_code',
                        'body' => [
                            [
                                'op' => 'attribute',
                                'name' => 'xr:id',
                                'value' => [
                                    'literal' => 'BT-8',
                                ],
                            ],
                            [
                                'op' => 'attribute',
                                'name' => 'xr:src',
                                'value' => [
                                    'srcpath' => [
                                        'xpath' => '.',
                                    ],
                                ],
                            ],
                            [
                                'op' => 'call',
                                'name' => 'code',
                                'params' => [],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        'BT-9' => [
            [
                'match' => '/rsm:CrossIndustryInvoice/rsm:SupplyChainTradeTransaction/ram:ApplicableHeaderTradeSettlement/ram:SpecifiedTradePaymentTerms/ram:DueDateDateTime/udt:DateTimeString[@format = \'102\']',
                'priority' => 0.5,
                'body' => [
                    [
                        'op' => 'call',
                        'name' => 'date',
                        'params' => [],
                    ],
                    [
                        'op' => 'if',
                        'test' => [
                            'xpath' => 'position() != last()',
                            'positional' => true,
                        ],
                        'body' => [
                            [
                                'op' => 'text',
                                'value' => ';',
                            ],
                        ],
                    ],
                ],
            ],
        ],
        'BT-10' => [
            [
                'match' => '/rsm:CrossIndustryInvoice/rsm:SupplyChainTradeTransaction/ram:ApplicableHeaderTradeAgreement/ram:BuyerReference',
                'priority' => 0.5,
                'body' => [
                    [
                        'op' => 'element',
                        'name' => 'Buyer_reference',
                        'body' => [
                            [
                                'op' => 'attribute',
                                'name' => 'xr:id',
                                'value' => [
                                    'literal' => 'BT-10',
                                ],
                            ],
                            [
                                'op' => 'attribute',
                                'name' => 'xr:src',
                                'value' => [
                                    'srcpath' => [
                                        'xpath' => '.',
                                    ],
                                ],
                            ],
                            [
                                'op' => 'call',
                                'name' => 'text',
                                'params' => [],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        'BT-11' => [
            [
                'match' => '/rsm:CrossIndustryInvoice/rsm:SupplyChainTradeTransaction/ram:ApplicableHeaderTradeAgreement/ram:SpecifiedProcuringProject/ram:ID',
                'priority' => 0.5,
                'body' => [
                    [
                        'op' => 'element',
                        'name' => 'Project_reference',
                        'body' => [
                            [
                                'op' => 'attribute',
                                'name' => 'xr:id',
                                'value' => [
                                    'literal' => 'BT-11',
                                ],
                            ],
                            [
                                'op' => 'attribute',
                                'name' => 'xr:src',
                                'value' => [
                                    'srcpath' => [
                                        'xpath' => '.',
                                    ],
                                ],
                            ],
                            [
                                'op' => 'call',
                                'name' => 'document_reference',
                                'params' => [],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        'BT-12' => [
            [
                'match' => '/rsm:CrossIndustryInvoice/rsm:SupplyChainTradeTransaction/ram:ApplicableHeaderTradeAgreement/ram:ContractReferencedDocument/ram:IssuerAssignedID',
                'priority' => 0.5,
                'body' => [
                    [
                        'op' => 'element',
                        'name' => 'Contract_reference',
                        'body' => [
                            [
                                'op' => 'attribute',
                                'name' => 'xr:id',
                                'value' => [
                                    'literal' => 'BT-12',
                                ],
                            ],
                            [
                                'op' => 'attribute',
                                'name' => 'xr:src',
                                'value' => [
                                    'srcpath' => [
                                        'xpath' => '.',
                                    ],
                                ],
                            ],
                            [
                                'op' => 'call',
                                'name' => 'document_reference',
                                'params' => [],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        'BT-13' => [
            [
                'match' => '/rsm:CrossIndustryInvoice/rsm:SupplyChainTradeTransaction/ram:ApplicableHeaderTradeAgreement/ram:BuyerOrderReferencedDocument/ram:IssuerAssignedID',
                'priority' => 0.5,
                'body' => [
                    [
                        'op' => 'element',
                        'name' => 'Purchase_order_reference',
                        'body' => [
                            [
                                'op' => 'attribute',
                                'name' => 'xr:id',
                                'value' => [
                                    'literal' => 'BT-13',
                                ],
                            ],
                            [
                                'op' => 'attribute',
                                'name' => 'xr:src',
                                'value' => [
                                    'srcpath' => [
                                        'xpath' => '.',
                                    ],
                                ],
                            ],
                            [
                                'op' => 'call',
                                'name' => 'document_reference',
                                'params' => [],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        'BT-14' => [
            [
                'match' => '/rsm:CrossIndustryInvoice/rsm:SupplyChainTradeTransaction/ram:ApplicableHeaderTradeAgreement/ram:SellerOrderReferencedDocument/ram:IssuerAssignedID',
                'priority' => 0.5,
                'body' => [
                    [
                        'op' => 'element',
                        'name' => 'Sales_order_reference',
                        'body' => [
                            [
                                'op' => 'attribute',
                                'name' => 'xr:id',
                                'value' => [
                                    'literal' => 'BT-14',
                                ],
                            ],
                            [
                                'op' => 'attribute',
                                'name' => 'xr:src',
                                'value' => [
                                    'srcpath' => [
                                        'xpath' => '.',
                                    ],
                                ],
                            ],
                            [
                                'op' => 'call',
                                'name' => 'document_reference',
                                'params' => [],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        'BT-15' => [
            [
                'match' => '/rsm:CrossIndustryInvoice/rsm:SupplyChainTradeTransaction/ram:ApplicableHeaderTradeDelivery/ram:ReceivingAdviceReferencedDocument/ram:IssuerAssignedID',
                'priority' => 0.5,
                'body' => [
                    [
                        'op' => 'element',
                        'name' => 'Receiving_advice_reference',
                        'body' => [
                            [
                                'op' => 'attribute',
                                'name' => 'xr:id',
                                'value' => [
                                    'literal' => 'BT-15',
                                ],
                            ],
                            [
                                'op' => 'attribute',
                                'name' => 'xr:src',
                                'value' => [
                                    'srcpath' => [
                                        'xpath' => '.',
                                    ],
                                ],
                            ],
                            [
                                'op' => 'call',
                                'name' => 'document_reference',
                                'params' => [],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        'BT-16' => [
            [
                'match' => '/rsm:CrossIndustryInvoice/rsm:SupplyChainTradeTransaction/ram:ApplicableHeaderTradeDelivery/ram:DespatchAdviceReferencedDocument/ram:IssuerAssignedID',
                'priority' => 0.5,
                'body' => [
                    [
                        'op' => 'element',
                        'name' => 'Despatch_advice_reference',
                        'body' => [
                            [
                                'op' => 'attribute',
                                'name' => 'xr:id',
                                'value' => [
                                    'literal' => 'BT-16',
                                ],
                            ],
                            [
                                'op' => 'attribute',
                                'name' => 'xr:src',
                                'value' => [
                                    'srcpath' => [
                                        'xpath' => '.',
                                    ],
                                ],
                            ],
                            [
                                'op' => 'call',
                                'name' => 'document_reference',
                                'params' => [],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        'BT-17' => [
            [
                'match' => '/rsm:CrossIndustryInvoice/rsm:SupplyChainTradeTransaction/ram:ApplicableHeaderTradeAgreement/ram:AdditionalReferencedDocument/ram:IssuerAssignedID[following-sibling::ram:TypeCode=\'50\']',
                'priority' => 0.5,
                'body' => [
                    [
                        'op' => 'element',
                        'name' => 'Tender_or_lot_reference',
                        'body' => [
                            [
                                'op' => 'attribute',
                                'name' => 'xr:id',
                                'value' => [
                                    'literal' => 'BT-17',
                                ],
                            ],
                            [
                                'op' => 'attribute',
                                'name' => 'xr:src',
                                'value' => [
                                    'srcpath' => [
                                        'xpath' => '.',
                                    ],
                                ],
                            ],
                            [
                                'op' => 'call',
                                'name' => 'document_reference',
                                'params' => [],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        'BT-18' => [
            [
                'match' => '/rsm:CrossIndustryInvoice/rsm:SupplyChainTradeTransaction/ram:ApplicableHeaderTradeAgreement/ram:AdditionalReferencedDocument/ram:IssuerAssignedID[following-sibling::ram:TypeCode=\'130\']',
                'priority' => 0.5,
                'body' => [
                    [
                        'op' => 'element',
                        'name' => 'Invoiced_object_identifier',
                        'body' => [
                            [
                                'op' => 'attribute',
                                'name' => 'xr:id',
                                'value' => [
                                    'literal' => 'BT-18',
                                ],
                            ],
                            [
                                'op' => 'attribute',
                                'name' => 'xr:src',
                                'value' => [
                                    'srcpath' => [
                                        'xpath' => '.',
                                    ],
                                ],
                            ],
                            [
                                'op' => 'call',
                                'name' => 'identifier-with-scheme',
                                'params' => [
                                    'schemeID' => [
                                        'xpath' => 'following-sibling::ram:ReferenceTypeCode',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        'BT-19' => [
            [
                'match' => '/rsm:CrossIndustryInvoice/rsm:SupplyChainTradeTransaction/ram:ApplicableHeaderTradeSettlement/ram:ReceivableSpecifiedTradeAccountingAccount/ram:ID',
                'priority' => 0.5,
                'body' => [
                    [
                        'op' => 'element',
                        'name' => 'Buyer_accounting_reference',
                        'body' => [
                            [
                                'op' => 'attribute',
                                'name' => 'xr:id',
                                'value' => [
                                    'literal' => 'BT-19',
                                ],
                            ],
                            [
                                'op' => 'attribute',
                                'name' => 'xr:src',
                                'value' => [
                                    'srcpath' => [
                                        'xpath' => '.',
                                    ],
                                ],
                            ],
                            [
                                'op' => 'call',
                                'name' => 'text',
                                'params' => [],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        'BT-20' => [
            [
                'match' => '/rsm:CrossIndustryInvoice/rsm:SupplyChainTradeTransaction/ram:ApplicableHeaderTradeSettlement/ram:SpecifiedTradePaymentTerms/ram:Description',
                'priority' => 0.5,
                'body' => [
                    [
                        'op' => 'call',
                        'name' => 'text',
                        'params' => [],
                    ],
                    [
                        'op' => 'if',
                        'test' => [
                            'xpath' => 'position() != last()',
                            'positional' => true,
                        ],
                        'body' => [
                            [
                                'op' => 'text',
                                'value' => ';',
                            ],
                        ],
                    ],
                ],
            ],
        ],
        'BG-1' => [
            [
                'match' => '/rsm:CrossIndustryInvoice/rsm:ExchangedDocument/ram:IncludedNote',
                'priority' => 0.5,
                'body' => [
                    [
                        'op' => 'variable',
                        'name' => 'bg-contents',
                        'body' => [
                            [
                                'op' => 'apply',
                                'mode' => 'BT-21',
                                'select' => [
                                    'xpath' => './ram:SubjectCode',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BT-22',
                                'select' => [
                                    'xpath' => './ram:Content',
                                ],
                            ],
                        ],
                    ],
                    [
                        'op' => 'if',
                        'test' => [
                            'var' => 'bg-contents',
                        ],
                        'body' => [
                            [
                                'op' => 'element',
                                'name' => 'INVOICE_NOTE',
                                'body' => [
                                    [
                                        'op' => 'attribute',
                                        'name' => 'xr:id',
                                        'value' => [
                                            'literal' => 'BG-1',
                                        ],
                                    ],
                                    [
                                        'op' => 'attribute',
                                        'name' => 'xr:src',
                                        'value' => [
                                            'srcpath' => [
                                                'xpath' => '.',
                                            ],
                                        ],
                                    ],
                                    [
                                        'op' => 'sequence',
                                        'var' => 'bg-contents',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        'BT-21' => [
            [
                'match' => '/rsm:CrossIndustryInvoice/rsm:ExchangedDocument/ram:IncludedNote/ram:SubjectCode',
                'priority' => 0.5,
                'body' => [
                    [
                        'op' => 'element',
                        'name' => 'Invoice_note_subject_code',
                        'body' => [
                            [
                                'op' => 'attribute',
                                'name' => 'xr:id',
                                'value' => [
                                    'literal' => 'BT-21',
                                ],
                            ],
                            [
                                'op' => 'attribute',
                                'name' => 'xr:src',
                                'value' => [
                                    'srcpath' => [
                                        'xpath' => '.',
                                    ],
                                ],
                            ],
                            [
                                'op' => 'call',
                                'name' => 'code',
                                'params' => [],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        'BT-22' => [
            [
                'match' => '/rsm:CrossIndustryInvoice/rsm:ExchangedDocument/ram:IncludedNote/ram:Content',
                'priority' => 0.5,
                'body' => [
                    [
                        'op' => 'element',
                        'name' => 'Invoice_note',
                        'body' => [
                            [
                                'op' => 'attribute',
                                'name' => 'xr:id',
                                'value' => [
                                    'literal' => 'BT-22',
                                ],
                            ],
                            [
                                'op' => 'attribute',
                                'name' => 'xr:src',
                                'value' => [
                                    'srcpath' => [
                                        'xpath' => '.',
                                    ],
                                ],
                            ],
                            [
                                'op' => 'call',
                                'name' => 'text',
                                'params' => [],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        'BG-2' => [
            [
                'match' => '/rsm:CrossIndustryInvoice/rsm:ExchangedDocumentContext',
                'priority' => 0.5,
                'body' => [
                    [
                        'op' => 'variable',
                        'name' => 'bg-contents',
                        'body' => [
                            [
                                'op' => 'apply',
                                'mode' => 'BT-23',
                                'select' => [
                                    'xpath' => './ram:BusinessProcessSpecifiedDocumentContextParameter/ram:ID',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BT-24',
                                'select' => [
                                    'xpath' => './ram:GuidelineSpecifiedDocumentContextParameter/ram:ID',
                                ],
                            ],
                        ],
                    ],
                    [
                        'op' => 'if',
                        'test' => [
                            'var' => 'bg-contents',
                        ],
                        'body' => [
                            [
                                'op' => 'element',
                                'name' => 'PROCESS_CONTROL',
                                'body' => [
                                    [
                                        'op' => 'attribute',
                                        'name' => 'xr:id',
                                        'value' => [
                                            'literal' => 'BG-2',
                                        ],
                                    ],
                                    [
                                        'op' => 'attribute',
                                        'name' => 'xr:src',
                                        'value' => [
                                            'srcpath' => [
                                                'xpath' => '.',
                                            ],
                                        ],
                                    ],
                                    [
                                        'op' => 'sequence',
                                        'var' => 'bg-contents',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        'BT-23' => [
            [
                'match' => '/rsm:CrossIndustryInvoice/rsm:ExchangedDocumentContext/ram:BusinessProcessSpecifiedDocumentContextParameter/ram:ID',
                'priority' => 0.5,
                'body' => [
                    [
                        'op' => 'element',
                        'name' => 'Business_process_type',
                        'body' => [
                            [
                                'op' => 'attribute',
                                'name' => 'xr:id',
                                'value' => [
                                    'literal' => 'BT-23',
                                ],
                            ],
                            [
                                'op' => 'attribute',
                                'name' => 'xr:src',
                                'value' => [
                                    'srcpath' => [
                                        'xpath' => '.',
                                    ],
                                ],
                            ],
                            [
                                'op' => 'call',
                                'name' => 'text',
                                'params' => [],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        'BT-24' => [
            [
                'match' => '/rsm:CrossIndustryInvoice/rsm:ExchangedDocumentContext/ram:GuidelineSpecifiedDocumentContextParameter/ram:ID',
                'priority' => 0.5,
                'body' => [
                    [
                        'op' => 'element',
                        'name' => 'Specification_identifier',
                        'body' => [
                            [
                                'op' => 'attribute',
                                'name' => 'xr:id',
                                'value' => [
                                    'literal' => 'BT-24',
                                ],
                            ],
                            [
                                'op' => 'attribute',
                                'name' => 'xr:src',
                                'value' => [
                                    'srcpath' => [
                                        'xpath' => '.',
                                    ],
                                ],
                            ],
                            [
                                'op' => 'call',
                                'name' => 'identifier',
                                'params' => [],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        'BG-3' => [
            [
                'match' => '/rsm:CrossIndustryInvoice/rsm:SupplyChainTradeTransaction/ram:ApplicableHeaderTradeSettlement/ram:InvoiceReferencedDocument',
                'priority' => 0.5,
                'body' => [
                    [
                        'op' => 'variable',
                        'name' => 'bg-contents',
                        'body' => [
                            [
                                'op' => 'apply',
                                'mode' => 'BT-25',
                                'select' => [
                                    'xpath' => './ram:IssuerAssignedID',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BT-26',
                                'select' => [
                                    'xpath' => './ram:FormattedIssueDateTime/qdt:DateTimeString[@format = \'102\']',
                                ],
                            ],
                        ],
                    ],
                    [
                        'op' => 'if',
                        'test' => [
                            'var' => 'bg-contents',
                        ],
                        'body' => [
                            [
                                'op' => 'element',
                                'name' => 'PRECEDING_INVOICE_REFERENCE',
                                'body' => [
                                    [
                                        'op' => 'attribute',
                                        'name' => 'xr:id',
                                        'value' => [
                                            'literal' => 'BG-3',
                                        ],
                                    ],
                                    [
                                        'op' => 'attribute',
                                        'name' => 'xr:src',
                                        'value' => [
                                            'srcpath' => [
                                                'xpath' => '.',
                                            ],
                                        ],
                                    ],
                                    [
                                        'op' => 'sequence',
                                        'var' => 'bg-contents',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        'BT-25' => [
            [
                'match' => '/rsm:CrossIndustryInvoice/rsm:SupplyChainTradeTransaction/ram:ApplicableHeaderTradeSettlement/ram:InvoiceReferencedDocument/ram:IssuerAssignedID',
                'priority' => 0.5,
                'body' => [
                    [
                        'op' => 'element',
                        'name' => 'Preceding_Invoice_reference',
                        'body' => [
                            [
                                'op' => 'attribute',
                                'name' => 'xr:id',
                                'value' => [
                                    'literal' => 'BT-25',
                                ],
                            ],
                            [
                                'op' => 'attribute',
                                'name' => 'xr:src',
                                'value' => [
                                    'srcpath' => [
                                        'xpath' => '.',
                                    ],
                                ],
                            ],
                            [
                                'op' => 'call',
                                'name' => 'document_reference',
                                'params' => [],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        'BT-26' => [
            [
                'match' => '/rsm:CrossIndustryInvoice/rsm:SupplyChainTradeTransaction/ram:ApplicableHeaderTradeSettlement/ram:InvoiceReferencedDocument/ram:FormattedIssueDateTime/qdt:DateTimeString[@format = \'102\']',
                'priority' => 0.5,
                'body' => [
                    [
                        'op' => 'element',
                        'name' => 'Preceding_Invoice_issue_date',
                        'body' => [
                            [
                                'op' => 'attribute',
                                'name' => 'xr:id',
                                'value' => [
                                    'literal' => 'BT-26',
                                ],
                            ],
                            [
                                'op' => 'attribute',
                                'name' => 'xr:src',
                                'value' => [
                                    'srcpath' => [
                                        'xpath' => '.',
                                    ],
                                ],
                            ],
                            [
                                'op' => 'call',
                                'name' => 'date',
                                'params' => [],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        'BG-4' => [
            [
                'match' => '/rsm:CrossIndustryInvoice/rsm:SupplyChainTradeTransaction/ram:ApplicableHeaderTradeAgreement/ram:SellerTradeParty',
                'priority' => 0.5,
                'body' => [
                    [
                        'op' => 'variable',
                        'name' => 'bg-contents',
                        'body' => [
                            [
                                'op' => 'apply',
                                'mode' => 'BT-27',
                                'select' => [
                                    'xpath' => './ram:Name',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BT-28',
                                'select' => [
                                    'xpath' => './ram:SpecifiedLegalOrganization/ram:TradingBusinessName',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BT-29',
                                'select' => [
                                    'xpath' => './ram:ID',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BT-29',
                                'select' => [
                                    'xpath' => './ram:GlobalID[boolean(@schemeID)]',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BT-30',
                                'select' => [
                                    'xpath' => './ram:SpecifiedLegalOrganization/ram:ID',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BT-31',
                                'select' => [
                                    'xpath' => './ram:SpecifiedTaxRegistration/ram:ID[@schemeID=\'VA\']',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BT-32',
                                'select' => [
                                    'xpath' => './ram:SpecifiedTaxRegistration/ram:ID[@schemeID=\'FC\']',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BT-33',
                                'select' => [
                                    'xpath' => './ram:Description',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BT-34',
                                'select' => [
                                    'xpath' => './ram:URIUniversalCommunication/ram:URIID',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BG-5',
                                'select' => [
                                    'xpath' => './ram:PostalTradeAddress',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BG-6',
                                'select' => [
                                    'xpath' => './ram:DefinedTradeContact',
                                ],
                            ],
                        ],
                    ],
                    [
                        'op' => 'if',
                        'test' => [
                            'var' => 'bg-contents',
                        ],
                        'body' => [
                            [
                                'op' => 'element',
                                'name' => 'SELLER',
                                'body' => [
                                    [
                                        'op' => 'attribute',
                                        'name' => 'xr:id',
                                        'value' => [
                                            'literal' => 'BG-4',
                                        ],
                                    ],
                                    [
                                        'op' => 'attribute',
                                        'name' => 'xr:src',
                                        'value' => [
                                            'srcpath' => [
                                                'xpath' => '.',
                                            ],
                                        ],
                                    ],
                                    [
                                        'op' => 'sequence',
                                        'var' => 'bg-contents',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        'BT-27' => [
            [
                'match' => '/rsm:CrossIndustryInvoice/rsm:SupplyChainTradeTransaction/ram:ApplicableHeaderTradeAgreement/ram:SellerTradeParty/ram:Name',
                'priority' => 0.5,
                'body' => [
                    [
                        'op' => 'element',
                        'name' => 'Seller_name',
                        'body' => [
                            [
                                'op' => 'attribute',
                                'name' => 'xr:id',
                                'value' => [
                                    'literal' => 'BT-27',
                                ],
                            ],
                            [
                                'op' => 'attribute',
                                'name' => 'xr:src',
                                'value' => [
                                    'srcpath' => [
                                        'xpath' => '.',
                                    ],
                                ],
                            ],
                            [
                                'op' => 'call',
                                'name' => 'text',
                                'params' => [],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        'BT-28' => [
            [
                'match' => '/rsm:CrossIndustryInvoice/rsm:SupplyChainTradeTransaction/ram:ApplicableHeaderTradeAgreement/ram:SellerTradeParty/ram:SpecifiedLegalOrganization/ram:TradingBusinessName',
                'priority' => 0.5,
                'body' => [
                    [
                        'op' => 'element',
                        'name' => 'Seller_trading_name',
                        'body' => [
                            [
                                'op' => 'attribute',
                                'name' => 'xr:id',
                                'value' => [
                                    'literal' => 'BT-28',
                                ],
                            ],
                            [
                                'op' => 'attribute',
                                'name' => 'xr:src',
                                'value' => [
                                    'srcpath' => [
                                        'xpath' => '.',
                                    ],
                                ],
                            ],
                            [
                                'op' => 'call',
                                'name' => 'text',
                                'params' => [],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        'BT-29' => [
            [
                'match' => '/rsm:CrossIndustryInvoice/rsm:SupplyChainTradeTransaction/ram:ApplicableHeaderTradeAgreement/ram:SellerTradeParty/ram:ID',
                'priority' => 0.5,
                'body' => [
                    [
                        'op' => 'element',
                        'name' => 'Seller_identifier',
                        'body' => [
                            [
                                'op' => 'attribute',
                                'name' => 'xr:id',
                                'value' => [
                                    'literal' => 'BT-29',
                                ],
                            ],
                            [
                                'op' => 'attribute',
                                'name' => 'xr:src',
                                'value' => [
                                    'srcpath' => [
                                        'xpath' => '.',
                                    ],
                                ],
                            ],
                            [
                                'op' => 'call',
                                'name' => 'identifier',
                                'params' => [],
                            ],
                        ],
                    ],
                ],
            ],
            [
                'match' => '/rsm:CrossIndustryInvoice/rsm:SupplyChainTradeTransaction/ram:ApplicableHeaderTradeAgreement/ram:SellerTradeParty/ram:GlobalID[boolean(@schemeID)]',
                'priority' => 0.5,
                'body' => [
                    [
                        'op' => 'element',
                        'name' => 'Seller_identifier',
                        'body' => [
                            [
                                'op' => 'attribute',
                                'name' => 'xr:id',
                                'value' => [
                                    'literal' => 'BT-29',
                                ],
                            ],
                            [
                                'op' => 'attribute',
                                'name' => 'xr:src',
                                'value' => [
                                    'srcpath' => [
                                        'xpath' => '.',
                                    ],
                                ],
                            ],
                            [
                                'op' => 'call',
                                'name' => 'identifier',
                                'params' => [],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        'BT-30' => [
            [
                'match' => '/rsm:CrossIndustryInvoice/rsm:SupplyChainTradeTransaction/ram:ApplicableHeaderTradeAgreement/ram:SellerTradeParty/ram:SpecifiedLegalOrganization/ram:ID',
                'priority' => 0.5,
                'body' => [
                    [
                        'op' => 'element',
                        'name' => 'Seller_legal_registration_identifier',
                        'body' => [
                            [
                                'op' => 'attribute',
                                'name' => 'xr:id',
                                'value' => [
                                    'literal' => 'BT-30',
                                ],
                            ],
                            [
                                'op' => 'attribute',
                                'name' => 'xr:src',
                                'value' => [
                                    'srcpath' => [
                                        'xpath' => '.',
                                    ],
                                ],
                            ],
                            [
                                'op' => 'call',
                                'name' => 'identifier',
                                'params' => [],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        'BT-31' => [
            [
                'match' => '/rsm:CrossIndustryInvoice/rsm:SupplyChainTradeTransaction/ram:ApplicableHeaderTradeAgreement/ram:SellerTradeParty/ram:SpecifiedTaxRegistration/ram:ID[@schemeID=\'VA\']',
                'priority' => 0.5,
                'body' => [
                    [
                        'op' => 'element',
                        'name' => 'Seller_VAT_identifier',
                        'body' => [
                            [
                                'op' => 'attribute',
                                'name' => 'xr:id',
                                'value' => [
                                    'literal' => 'BT-31',
                                ],
                            ],
                            [
                                'op' => 'attribute',
                                'name' => 'xr:src',
                                'value' => [
                                    'srcpath' => [
                                        'xpath' => '.',
                                    ],
                                ],
                            ],
                            [
                                'op' => 'call',
                                'name' => 'identifier',
                                'params' => [],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        'BT-32' => [
            [
                'match' => '/rsm:CrossIndustryInvoice/rsm:SupplyChainTradeTransaction/ram:ApplicableHeaderTradeAgreement/ram:SellerTradeParty/ram:SpecifiedTaxRegistration/ram:ID[@schemeID=\'FC\']',
                'priority' => 0.5,
                'body' => [
                    [
                        'op' => 'element',
                        'name' => 'Seller_tax_registration_identifier',
                        'body' => [
                            [
                                'op' => 'attribute',
                                'name' => 'xr:id',
                                'value' => [
                                    'literal' => 'BT-32',
                                ],
                            ],
                            [
                                'op' => 'attribute',
                                'name' => 'xr:src',
                                'value' => [
                                    'srcpath' => [
                                        'xpath' => '.',
                                    ],
                                ],
                            ],
                            [
                                'op' => 'call',
                                'name' => 'identifier',
                                'params' => [],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        'BT-33' => [
            [
                'match' => '/rsm:CrossIndustryInvoice/rsm:SupplyChainTradeTransaction/ram:ApplicableHeaderTradeAgreement/ram:SellerTradeParty/ram:Description',
                'priority' => 0.5,
                'body' => [
                    [
                        'op' => 'element',
                        'name' => 'Seller_additional_legal_information',
                        'body' => [
                            [
                                'op' => 'attribute',
                                'name' => 'xr:id',
                                'value' => [
                                    'literal' => 'BT-33',
                                ],
                            ],
                            [
                                'op' => 'attribute',
                                'name' => 'xr:src',
                                'value' => [
                                    'srcpath' => [
                                        'xpath' => '.',
                                    ],
                                ],
                            ],
                            [
                                'op' => 'call',
                                'name' => 'text',
                                'params' => [],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        'BT-34' => [
            [
                'match' => '/rsm:CrossIndustryInvoice/rsm:SupplyChainTradeTransaction/ram:ApplicableHeaderTradeAgreement/ram:SellerTradeParty/ram:URIUniversalCommunication/ram:URIID',
                'priority' => 0.5,
                'body' => [
                    [
                        'op' => 'element',
                        'name' => 'Seller_electronic_address',
                        'body' => [
                            [
                                'op' => 'attribute',
                                'name' => 'xr:id',
                                'value' => [
                                    'literal' => 'BT-34',
                                ],
                            ],
                            [
                                'op' => 'attribute',
                                'name' => 'xr:src',
                                'value' => [
                                    'srcpath' => [
                                        'xpath' => '.',
                                    ],
                                ],
                            ],
                            [
                                'op' => 'call',
                                'name' => 'identifier',
                                'params' => [],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        'BG-5' => [
            [
                'match' => '/rsm:CrossIndustryInvoice/rsm:SupplyChainTradeTransaction/ram:ApplicableHeaderTradeAgreement/ram:SellerTradeParty/ram:PostalTradeAddress',
                'priority' => 0.5,
                'body' => [
                    [
                        'op' => 'variable',
                        'name' => 'bg-contents',
                        'body' => [
                            [
                                'op' => 'apply',
                                'mode' => 'BT-35',
                                'select' => [
                                    'xpath' => './ram:LineOne',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BT-36',
                                'select' => [
                                    'xpath' => './ram:LineTwo',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BT-162',
                                'select' => [
                                    'xpath' => './ram:LineThree',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BT-37',
                                'select' => [
                                    'xpath' => './ram:CityName',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BT-38',
                                'select' => [
                                    'xpath' => './ram:PostcodeCode',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BT-39',
                                'select' => [
                                    'xpath' => './ram:CountrySubDivisionName',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BT-40',
                                'select' => [
                                    'xpath' => './ram:CountryID',
                                ],
                            ],
                        ],
                    ],
                    [
                        'op' => 'if',
                        'test' => [
                            'var' => 'bg-contents',
                        ],
                        'body' => [
                            [
                                'op' => 'element',
                                'name' => 'SELLER_POSTAL_ADDRESS',
                                'body' => [
                                    [
                                        'op' => 'attribute',
                                        'name' => 'xr:id',
                                        'value' => [
                                            'literal' => 'BG-5',
                                        ],
                                    ],
                                    [
                                        'op' => 'attribute',
                                        'name' => 'xr:src',
                                        'value' => [
                                            'srcpath' => [
                                                'xpath' => '.',
                                            ],
                                        ],
                                    ],
                                    [
                                        'op' => 'sequence',
                                        'var' => 'bg-contents',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        'BT-35' => [
            [
                'match' => '/rsm:CrossIndustryInvoice/rsm:SupplyChainTradeTransaction/ram:ApplicableHeaderTradeAgreement/ram:SellerTradeParty/ram:PostalTradeAddress/ram:LineOne',
                'priority' => 0.5,
                'body' => [
                    [
                        'op' => 'element',
                        'name' => 'Seller_address_line_1',
                        'body' => [
                            [
                                'op' => 'attribute',
                                'name' => 'xr:id',
                                'value' => [
                                    'literal' => 'BT-35',
                                ],
                            ],
                            [
                                'op' => 'attribute',
                                'name' => 'xr:src',
                                'value' => [
                                    'srcpath' => [
                                        'xpath' => '.',
                                    ],
                                ],
                            ],
                            [
                                'op' => 'call',
                                'name' => 'text',
                                'params' => [],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        'BT-36' => [
            [
                'match' => '/rsm:CrossIndustryInvoice/rsm:SupplyChainTradeTransaction/ram:ApplicableHeaderTradeAgreement/ram:SellerTradeParty/ram:PostalTradeAddress/ram:LineTwo',
                'priority' => 0.5,
                'body' => [
                    [
                        'op' => 'element',
                        'name' => 'Seller_address_line_2',
                        'body' => [
                            [
                                'op' => 'attribute',
                                'name' => 'xr:id',
                                'value' => [
                                    'literal' => 'BT-36',
                                ],
                            ],
                            [
                                'op' => 'attribute',
                                'name' => 'xr:src',
                                'value' => [
                                    'srcpath' => [
                                        'xpath' => '.',
                                    ],
                                ],
                            ],
                            [
                                'op' => 'call',
                                'name' => 'text',
                                'params' => [],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        'BT-162' => [
            [
                'match' => '/rsm:CrossIndustryInvoice/rsm:SupplyChainTradeTransaction/ram:ApplicableHeaderTradeAgreement/ram:SellerTradeParty/ram:PostalTradeAddress/ram:LineThree',
                'priority' => 0.5,
                'body' => [
                    [
                        'op' => 'element',
                        'name' => 'Seller_address_line_3',
                        'body' => [
                            [
                                'op' => 'attribute',
                                'name' => 'xr:id',
                                'value' => [
                                    'literal' => 'BT-162',
                                ],
                            ],
                            [
                                'op' => 'attribute',
                                'name' => 'xr:src',
                                'value' => [
                                    'srcpath' => [
                                        'xpath' => '.',
                                    ],
                                ],
                            ],
                            [
                                'op' => 'call',
                                'name' => 'text',
                                'params' => [],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        'BT-37' => [
            [
                'match' => '/rsm:CrossIndustryInvoice/rsm:SupplyChainTradeTransaction/ram:ApplicableHeaderTradeAgreement/ram:SellerTradeParty/ram:PostalTradeAddress/ram:CityName',
                'priority' => 0.5,
                'body' => [
                    [
                        'op' => 'element',
                        'name' => 'Seller_city',
                        'body' => [
                            [
                                'op' => 'attribute',
                                'name' => 'xr:id',
                                'value' => [
                                    'literal' => 'BT-37',
                                ],
                            ],
                            [
                                'op' => 'attribute',
                                'name' => 'xr:src',
                                'value' => [
                                    'srcpath' => [
                                        'xpath' => '.',
                                    ],
                                ],
                            ],
                            [
                                'op' => 'call',
                                'name' => 'text',
                                'params' => [],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        'BT-38' => [
            [
                'match' => '/rsm:CrossIndustryInvoice/rsm:SupplyChainTradeTransaction/ram:ApplicableHeaderTradeAgreement/ram:SellerTradeParty/ram:PostalTradeAddress/ram:PostcodeCode',
                'priority' => 0.5,
                'body' => [
                    [
                        'op' => 'element',
                        'name' => 'Seller_post_code',
                        'body' => [
                            [
                                'op' => 'attribute',
                                'name' => 'xr:id',
                                'value' => [
                                    'literal' => 'BT-38',
                                ],
                            ],
                            [
                                'op' => 'attribute',
                                'name' => 'xr:src',
                                'value' => [
                                    'srcpath' => [
                                        'xpath' => '.',
                                    ],
                                ],
                            ],
                            [
                                'op' => 'call',
                                'name' => 'text',
                                'params' => [],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        'BT-39' => [
            [
                'match' => '/rsm:CrossIndustryInvoice/rsm:SupplyChainTradeTransaction/ram:ApplicableHeaderTradeAgreement/ram:SellerTradeParty/ram:PostalTradeAddress/ram:CountrySubDivisionName',
                'priority' => 0.5,
                'body' => [
                    [
                        'op' => 'element',
                        'name' => 'Seller_country_subdivision',
                        'body' => [
                            [
                                'op' => 'attribute',
                                'name' => 'xr:id',
                                'value' => [
                                    'literal' => 'BT-39',
                                ],
                            ],
                            [
                                'op' => 'attribute',
                                'name' => 'xr:src',
                                'value' => [
                                    'srcpath' => [
                                        'xpath' => '.',
                                    ],
                                ],
                            ],
                            [
                                'op' => 'call',
                                'name' => 'text',
                                'params' => [],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        'BT-40' => [
            [
                'match' => '/rsm:CrossIndustryInvoice/rsm:SupplyChainTradeTransaction/ram:ApplicableHeaderTradeAgreement/ram:SellerTradeParty/ram:PostalTradeAddress/ram:CountryID',
                'priority' => 0.5,
                'body' => [
                    [
                        'op' => 'element',
                        'name' => 'Seller_country_code',
                        'body' => [
                            [
                                'op' => 'attribute',
                                'name' => 'xr:id',
                                'value' => [
                                    'literal' => 'BT-40',
                                ],
                            ],
                            [
                                'op' => 'attribute',
                                'name' => 'xr:src',
                                'value' => [
                                    'srcpath' => [
                                        'xpath' => '.',
                                    ],
                                ],
                            ],
                            [
                                'op' => 'call',
                                'name' => 'code',
                                'params' => [],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        'BG-6' => [
            [
                'match' => '/rsm:CrossIndustryInvoice/rsm:SupplyChainTradeTransaction/ram:ApplicableHeaderTradeAgreement/ram:SellerTradeParty/ram:DefinedTradeContact',
                'priority' => 0.5,
                'body' => [
                    [
                        'op' => 'variable',
                        'name' => 'bg-contents',
                        'body' => [
                            [
                                'op' => 'apply',
                                'mode' => 'BT-41',
                                'select' => [
                                    'xpath' => './ram:DepartmentName',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BT-41',
                                'select' => [
                                    'xpath' => './ram:PersonName',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BT-42',
                                'select' => [
                                    'xpath' => './ram:TelephoneUniversalCommunication/ram:CompleteNumber',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BT-43',
                                'select' => [
                                    'xpath' => './ram:EmailURIUniversalCommunication/ram:URIID',
                                ],
                            ],
                        ],
                    ],
                    [
                        'op' => 'if',
                        'test' => [
                            'var' => 'bg-contents',
                        ],
                        'body' => [
                            [
                                'op' => 'element',
                                'name' => 'SELLER_CONTACT',
                                'body' => [
                                    [
                                        'op' => 'attribute',
                                        'name' => 'xr:id',
                                        'value' => [
                                            'literal' => 'BG-6',
                                        ],
                                    ],
                                    [
                                        'op' => 'attribute',
                                        'name' => 'xr:src',
                                        'value' => [
                                            'srcpath' => [
                                                'xpath' => '.',
                                            ],
                                        ],
                                    ],
                                    [
                                        'op' => 'sequence',
                                        'var' => 'bg-contents',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        'BT-41' => [
            [
                'match' => '/rsm:CrossIndustryInvoice/rsm:SupplyChainTradeTransaction/ram:ApplicableHeaderTradeAgreement/ram:SellerTradeParty/ram:DefinedTradeContact/ram:DepartmentName',
                'priority' => 0.5,
                'body' => [
                    [
                        'op' => 'element',
                        'name' => 'Seller_contact_point',
                        'body' => [
                            [
                                'op' => 'attribute',
                                'name' => 'xr:id',
                                'value' => [
                                    'literal' => 'BT-41',
                                ],
                            ],
                            [
                                'op' => 'attribute',
                                'name' => 'xr:src',
                                'value' => [
                                    'srcpath' => [
                                        'xpath' => '.',
                                    ],
                                ],
                            ],
                            [
                                'op' => 'call',
                                'name' => 'text',
                                'params' => [],
                            ],
                        ],
                    ],
                ],
            ],
            [
                'match' => '/rsm:CrossIndustryInvoice/rsm:SupplyChainTradeTransaction/ram:ApplicableHeaderTradeAgreement/ram:SellerTradeParty/ram:DefinedTradeContact/ram:PersonName',
                'priority' => 0.5,
                'body' => [
                    [
                        'op' => 'element',
                        'name' => 'Seller_contact_point',
                        'body' => [
                            [
                                'op' => 'attribute',
                                'name' => 'xr:id',
                                'value' => [
                                    'literal' => 'BT-41',
                                ],
                            ],
                            [
                                'op' => 'attribute',
                                'name' => 'xr:src',
                                'value' => [
                                    'srcpath' => [
                                        'xpath' => '.',
                                    ],
                                ],
                            ],
                            [
                                'op' => 'call',
                                'name' => 'text',
                                'params' => [],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        'BT-42' => [
            [
                'match' => '/rsm:CrossIndustryInvoice/rsm:SupplyChainTradeTransaction/ram:ApplicableHeaderTradeAgreement/ram:SellerTradeParty/ram:DefinedTradeContact/ram:TelephoneUniversalCommunication/ram:CompleteNumber',
                'priority' => 0.5,
                'body' => [
                    [
                        'op' => 'element',
                        'name' => 'Seller_contact_telephone_number',
                        'body' => [
                            [
                                'op' => 'attribute',
                                'name' => 'xr:id',
                                'value' => [
                                    'literal' => 'BT-42',
                                ],
                            ],
                            [
                                'op' => 'attribute',
                                'name' => 'xr:src',
                                'value' => [
                                    'srcpath' => [
                                        'xpath' => '.',
                                    ],
                                ],
                            ],
                            [
                                'op' => 'call',
                                'name' => 'text',
                                'params' => [],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        'BT-43' => [
            [
                'match' => '/rsm:CrossIndustryInvoice/rsm:SupplyChainTradeTransaction/ram:ApplicableHeaderTradeAgreement/ram:SellerTradeParty/ram:DefinedTradeContact/ram:EmailURIUniversalCommunication/ram:URIID',
                'priority' => 0.5,
                'body' => [
                    [
                        'op' => 'element',
                        'name' => 'Seller_contact_email_address',
                        'body' => [
                            [
                                'op' => 'attribute',
                                'name' => 'xr:id',
                                'value' => [
                                    'literal' => 'BT-43',
                                ],
                            ],
                            [
                                'op' => 'attribute',
                                'name' => 'xr:src',
                                'value' => [
                                    'srcpath' => [
                                        'xpath' => '.',
                                    ],
                                ],
                            ],
                            [
                                'op' => 'call',
                                'name' => 'text',
                                'params' => [],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        'BG-7' => [
            [
                'match' => '/rsm:CrossIndustryInvoice/rsm:SupplyChainTradeTransaction/ram:ApplicableHeaderTradeAgreement/ram:BuyerTradeParty',
                'priority' => 0.5,
                'body' => [
                    [
                        'op' => 'variable',
                        'name' => 'bg-contents',
                        'body' => [
                            [
                                'op' => 'apply',
                                'mode' => 'BT-44',
                                'select' => [
                                    'xpath' => './ram:Name',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BT-45',
                                'select' => [
                                    'xpath' => './ram:SpecifiedLegalOrganization/ram:TradingBusinessName',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BT-46',
                                'select' => [
                                    'xpath' => './ram:ID[not(following-sibling::ram:GlobalID/@schemeID)]',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BT-46',
                                'select' => [
                                    'xpath' => './ram:GlobalID[boolean(@schemeID)]',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BT-47',
                                'select' => [
                                    'xpath' => './ram:SpecifiedLegalOrganization/ram:ID',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BT-48',
                                'select' => [
                                    'xpath' => './ram:SpecifiedTaxRegistration/ram:ID[(@schemeID=\'VA\' or @schemeID=\'VAT\')]',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BT-49',
                                'select' => [
                                    'xpath' => './ram:URIUniversalCommunication/ram:URIID',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BG-8',
                                'select' => [
                                    'xpath' => './ram:PostalTradeAddress',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BG-9',
                                'select' => [
                                    'xpath' => './ram:DefinedTradeContact',
                                ],
                            ],
                        ],
                    ],
                    [
                        'op' => 'if',
                        'test' => [
                            'var' => 'bg-contents',
                        ],
                        'body' => [
                            [
                                'op' => 'element',
                                'name' => 'BUYER',
                                'body' => [
                                    [
                                        'op' => 'attribute',
                                        'name' => 'xr:id',
                                        'value' => [
                                            'literal' => 'BG-7',
                                        ],
                                    ],
                                    [
                                        'op' => 'attribute',
                                        'name' => 'xr:src',
                                        'value' => [
                                            'srcpath' => [
                                                'xpath' => '.',
                                            ],
                                        ],
                                    ],
                                    [
                                        'op' => 'sequence',
                                        'var' => 'bg-contents',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        'BT-44' => [
            [
                'match' => '/rsm:CrossIndustryInvoice/rsm:SupplyChainTradeTransaction/ram:ApplicableHeaderTradeAgreement/ram:BuyerTradeParty/ram:Name',
                'priority' => 0.5,
                'body' => [
                    [
                        'op' => 'element',
                        'name' => 'Buyer_name',
                        'body' => [
                            [
                                'op' => 'attribute',
                                'name' => 'xr:id',
                                'value' => [
                                    'literal' => 'BT-44',
                                ],
                            ],
                            [
                                'op' => 'attribute',
                                'name' => 'xr:src',
                                'value' => [
                                    'srcpath' => [
                                        'xpath' => '.',
                                    ],
                                ],
                            ],
                            [
                                'op' => 'call',
                                'name' => 'text',
                                'params' => [],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        'BT-45' => [
            [
                'match' => '/rsm:CrossIndustryInvoice/rsm:SupplyChainTradeTransaction/ram:ApplicableHeaderTradeAgreement/ram:BuyerTradeParty/ram:SpecifiedLegalOrganization/ram:TradingBusinessName',
                'priority' => 0.5,
                'body' => [
                    [
                        'op' => 'element',
                        'name' => 'Buyer_trading_name',
                        'body' => [
                            [
                                'op' => 'attribute',
                                'name' => 'xr:id',
                                'value' => [
                                    'literal' => 'BT-45',
                                ],
                            ],
                            [
                                'op' => 'attribute',
                                'name' => 'xr:src',
                                'value' => [
                                    'srcpath' => [
                                        'xpath' => '.',
                                    ],
                                ],
                            ],
                            [
                                'op' => 'call',
                                'name' => 'text',
                                'params' => [],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        'BT-46' => [
            [
                'match' => '/rsm:CrossIndustryInvoice/rsm:SupplyChainTradeTransaction/ram:ApplicableHeaderTradeAgreement/ram:BuyerTradeParty/ram:ID[not(following-sibling::ram:GlobalID/@schemeID)]',
                'priority' => 0.5,
                'body' => [
                    [
                        'op' => 'element',
                        'name' => 'Buyer_identifier',
                        'body' => [
                            [
                                'op' => 'attribute',
                                'name' => 'xr:id',
                                'value' => [
                                    'literal' => 'BT-46',
                                ],
                            ],
                            [
                                'op' => 'attribute',
                                'name' => 'xr:src',
                                'value' => [
                                    'srcpath' => [
                                        'xpath' => '.',
                                    ],
                                ],
                            ],
                            [
                                'op' => 'call',
                                'name' => 'identifier',
                                'params' => [],
                            ],
                        ],
                    ],
                ],
            ],
            [
                'match' => '/rsm:CrossIndustryInvoice/rsm:SupplyChainTradeTransaction/ram:ApplicableHeaderTradeAgreement/ram:BuyerTradeParty/ram:GlobalID[boolean(@schemeID)]',
                'priority' => 0.5,
                'body' => [
                    [
                        'op' => 'element',
                        'name' => 'Buyer_identifier',
                        'body' => [
                            [
                                'op' => 'attribute',
                                'name' => 'xr:id',
                                'value' => [
                                    'literal' => 'BT-46',
                                ],
                            ],
                            [
                                'op' => 'attribute',
                                'name' => 'xr:src',
                                'value' => [
                                    'srcpath' => [
                                        'xpath' => '.',
                                    ],
                                ],
                            ],
                            [
                                'op' => 'call',
                                'name' => 'identifier',
                                'params' => [],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        'BT-47' => [
            [
                'match' => '/rsm:CrossIndustryInvoice/rsm:SupplyChainTradeTransaction/ram:ApplicableHeaderTradeAgreement/ram:BuyerTradeParty/ram:SpecifiedLegalOrganization/ram:ID',
                'priority' => 0.5,
                'body' => [
                    [
                        'op' => 'element',
                        'name' => 'Buyer_legal_registration_identifier',
                        'body' => [
                            [
                                'op' => 'attribute',
                                'name' => 'xr:id',
                                'value' => [
                                    'literal' => 'BT-47',
                                ],
                            ],
                            [
                                'op' => 'attribute',
                                'name' => 'xr:src',
                                'value' => [
                                    'srcpath' => [
                                        'xpath' => '.',
                                    ],
                                ],
                            ],
                            [
                                'op' => 'call',
                                'name' => 'identifier',
                                'params' => [],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        'BT-48' => [
            [
                'match' => '/rsm:CrossIndustryInvoice/rsm:SupplyChainTradeTransaction/ram:ApplicableHeaderTradeAgreement/ram:BuyerTradeParty/ram:SpecifiedTaxRegistration/ram:ID[(@schemeID=\'VA\' or @schemeID=\'VAT\')]',
                'priority' => 0.5,
                'body' => [
                    [
                        'op' => 'element',
                        'name' => 'Buyer_VAT_identifier',
                        'body' => [
                            [
                                'op' => 'attribute',
                                'name' => 'xr:id',
                                'value' => [
                                    'literal' => 'BT-48',
                                ],
                            ],
                            [
                                'op' => 'attribute',
                                'name' => 'xr:src',
                                'value' => [
                                    'srcpath' => [
                                        'xpath' => '.',
                                    ],
                                ],
                            ],
                            [
                                'op' => 'call',
                                'name' => 'identifier',
                                'params' => [],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        'BT-49' => [
            [
                'match' => '/rsm:CrossIndustryInvoice/rsm:SupplyChainTradeTransaction/ram:ApplicableHeaderTradeAgreement/ram:BuyerTradeParty/ram:URIUniversalCommunication/ram:URIID',
                'priority' => 0.5,
                'body' => [
                    [
                        'op' => 'element',
                        'name' => 'Buyer_electronic_address',
                        'body' => [
                            [
                                'op' => 'attribute',
                                'name' => 'xr:id',
                                'value' => [
                                    'literal' => 'BT-49',
                                ],
                            ],
                            [
                                'op' => 'attribute',
                                'name' => 'xr:src',
                                'value' => [
                                    'srcpath' => [
                                        'xpath' => '.',
                                    ],
                                ],
                            ],
                            [
                                'op' => 'call',
                                'name' => 'identifier',
                                'params' => [],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        'BG-8' => [
            [
                'match' => '/rsm:CrossIndustryInvoice/rsm:SupplyChainTradeTransaction/ram:ApplicableHeaderTradeAgreement/ram:BuyerTradeParty/ram:PostalTradeAddress',
                'priority' => 0.5,
                'body' => [
                    [
                        'op' => 'variable',
                        'name' => 'bg-contents',
                        'body' => [
                            [
                                'op' => 'apply',
                                'mode' => 'BT-50',
                                'select' => [
                                    'xpath' => './ram:LineOne',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BT-51',
                                'select' => [
                                    'xpath' => './ram:LineTwo',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BT-163',
                                'select' => [
                                    'xpath' => './ram:LineThree',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BT-52',
                                'select' => [
                                    'xpath' => './ram:CityName',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BT-53',
                                'select' => [
                                    'xpath' => './ram:PostcodeCode',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BT-54',
                                'select' => [
                                    'xpath' => './ram:CountrySubDivisionName',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BT-55',
                                'select' => [
                                    'xpath' => './ram:CountryID',
                                ],
                            ],
                        ],
                    ],
                    [
                        'op' => 'if',
                        'test' => [
                            'var' => 'bg-contents',
                        ],
                        'body' => [
                            [
                                'op' => 'element',
                                'name' => 'BUYER_POSTAL_ADDRESS',
                                'body' => [
                                    [
                                        'op' => 'attribute',
                                        'name' => 'xr:id',
                                        'value' => [
                                            'literal' => 'BG-8',
                                        ],
                                    ],
                                    [
                                        'op' => 'attribute',
                                        'name' => 'xr:src',
                                        'value' => [
                                            'srcpath' => [
                                                'xpath' => '.',
                                            ],
                                        ],
                                    ],
                                    [
                                        'op' => 'sequence',
                                        'var' => 'bg-contents',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        'BT-50' => [
            [
                'match' => '/rsm:CrossIndustryInvoice/rsm:SupplyChainTradeTransaction/ram:ApplicableHeaderTradeAgreement/ram:BuyerTradeParty/ram:PostalTradeAddress/ram:LineOne',
                'priority' => 0.5,
                'body' => [
                    [
                        'op' => 'element',
                        'name' => 'Buyer_address_line_1',
                        'body' => [
                            [
                                'op' => 'attribute',
                                'name' => 'xr:id',
                                'value' => [
                                    'literal' => 'BT-50',
                                ],
                            ],
                            [
                                'op' => 'attribute',
                                'name' => 'xr:src',
                                'value' => [
                                    'srcpath' => [
                                        'xpath' => '.',
                                    ],
                                ],
                            ],
                            [
                                'op' => 'call',
                                'name' => 'text',
                                'params' => [],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        'BT-51' => [
            [
                'match' => '/rsm:CrossIndustryInvoice/rsm:SupplyChainTradeTransaction/ram:ApplicableHeaderTradeAgreement/ram:BuyerTradeParty/ram:PostalTradeAddress/ram:LineTwo',
                'priority' => 0.5,
                'body' => [
                    [
                        'op' => 'element',
                        'name' => 'Buyer_address_line_2',
                        'body' => [
                            [
                                'op' => 'attribute',
                                'name' => 'xr:id',
                                'value' => [
                                    'literal' => 'BT-51',
                                ],
                            ],
                            [
                                'op' => 'attribute',
                                'name' => 'xr:src',
                                'value' => [
                                    'srcpath' => [
                                        'xpath' => '.',
                                    ],
                                ],
                            ],
                            [
                                'op' => 'call',
                                'name' => 'text',
                                'params' => [],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        'BT-163' => [
            [
                'match' => '/rsm:CrossIndustryInvoice/rsm:SupplyChainTradeTransaction/ram:ApplicableHeaderTradeAgreement/ram:BuyerTradeParty/ram:PostalTradeAddress/ram:LineThree',
                'priority' => 0.5,
                'body' => [
                    [
                        'op' => 'element',
                        'name' => 'Buyer_address_line_3',
                        'body' => [
                            [
                                'op' => 'attribute',
                                'name' => 'xr:id',
                                'value' => [
                                    'literal' => 'BT-163',
                                ],
                            ],
                            [
                                'op' => 'attribute',
                                'name' => 'xr:src',
                                'value' => [
                                    'srcpath' => [
                                        'xpath' => '.',
                                    ],
                                ],
                            ],
                            [
                                'op' => 'call',
                                'name' => 'text',
                                'params' => [],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        'BT-52' => [
            [
                'match' => '/rsm:CrossIndustryInvoice/rsm:SupplyChainTradeTransaction/ram:ApplicableHeaderTradeAgreement/ram:BuyerTradeParty/ram:PostalTradeAddress/ram:CityName',
                'priority' => 0.5,
                'body' => [
                    [
                        'op' => 'element',
                        'name' => 'Buyer_city',
                        'body' => [
                            [
                                'op' => 'attribute',
                                'name' => 'xr:id',
                                'value' => [
                                    'literal' => 'BT-52',
                                ],
                            ],
                            [
                                'op' => 'attribute',
                                'name' => 'xr:src',
                                'value' => [
                                    'srcpath' => [
                                        'xpath' => '.',
                                    ],
                                ],
                            ],
                            [
                                'op' => 'call',
                                'name' => 'text',
                                'params' => [],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        'BT-53' => [
            [
                'match' => '/rsm:CrossIndustryInvoice/rsm:SupplyChainTradeTransaction/ram:ApplicableHeaderTradeAgreement/ram:BuyerTradeParty/ram:PostalTradeAddress/ram:PostcodeCode',
                'priority' => 0.5,
                'body' => [
                    [
                        'op' => 'element',
                        'name' => 'Buyer_post_code',
                        'body' => [
                            [
                                'op' => 'attribute',
                                'name' => 'xr:id',
                                'value' => [
                                    'literal' => 'BT-53',
                                ],
                            ],
                            [
                                'op' => 'attribute',
                                'name' => 'xr:src',
                                'value' => [
                                    'srcpath' => [
                                        'xpath' => '.',
                                    ],
                                ],
                            ],
                            [
                                'op' => 'call',
                                'name' => 'text',
                                'params' => [],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        'BT-54' => [
            [
                'match' => '/rsm:CrossIndustryInvoice/rsm:SupplyChainTradeTransaction/ram:ApplicableHeaderTradeAgreement/ram:BuyerTradeParty/ram:PostalTradeAddress/ram:CountrySubDivisionName',
                'priority' => 0.5,
                'body' => [
                    [
                        'op' => 'element',
                        'name' => 'Buyer_country_subdivision',
                        'body' => [
                            [
                                'op' => 'attribute',
                                'name' => 'xr:id',
                                'value' => [
                                    'literal' => 'BT-54',
                                ],
                            ],
                            [
                                'op' => 'attribute',
                                'name' => 'xr:src',
                                'value' => [
                                    'srcpath' => [
                                        'xpath' => '.',
                                    ],
                                ],
                            ],
                            [
                                'op' => 'call',
                                'name' => 'text',
                                'params' => [],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        'BT-55' => [
            [
                'match' => '/rsm:CrossIndustryInvoice/rsm:SupplyChainTradeTransaction/ram:ApplicableHeaderTradeAgreement/ram:BuyerTradeParty/ram:PostalTradeAddress/ram:CountryID',
                'priority' => 0.5,
                'body' => [
                    [
                        'op' => 'element',
                        'name' => 'Buyer_country_code',
                        'body' => [
                            [
                                'op' => 'attribute',
                                'name' => 'xr:id',
                                'value' => [
                                    'literal' => 'BT-55',
                                ],
                            ],
                            [
                                'op' => 'attribute',
                                'name' => 'xr:src',
                                'value' => [
                                    'srcpath' => [
                                        'xpath' => '.',
                                    ],
                                ],
                            ],
                            [
                                'op' => 'call',
                                'name' => 'code',
                                'params' => [],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        'BG-9' => [
            [
                'match' => '/rsm:CrossIndustryInvoice/rsm:SupplyChainTradeTransaction/ram:ApplicableHeaderTradeAgreement/ram:BuyerTradeParty/ram:DefinedTradeContact',
                'priority' => 0.5,
                'body' => [
                    [
                        'op' => 'variable',
                        'name' => 'bg-contents',
                        'body' => [
                            [
                                'op' => 'apply',
                                'mode' => 'BT-56',
                                'select' => [
                                    'xpath' => './ram:DepartmentName',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BT-56',
                                'select' => [
                                    'xpath' => './ram:PersonName',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BT-57',
                                'select' => [
                                    'xpath' => './ram:TelephoneUniversalCommunication/ram:CompleteNumber',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BT-58',
                                'select' => [
                                    'xpath' => './ram:EmailURIUniversalCommunication/ram:URIID',
                                ],
                            ],
                        ],
                    ],
                    [
                        'op' => 'if',
                        'test' => [
                            'var' => 'bg-contents',
                        ],
                        'body' => [
                            [
                                'op' => 'element',
                                'name' => 'BUYER_CONTACT',
                                'body' => [
                                    [
                                        'op' => 'attribute',
                                        'name' => 'xr:id',
                                        'value' => [
                                            'literal' => 'BG-9',
                                        ],
                                    ],
                                    [
                                        'op' => 'attribute',
                                        'name' => 'xr:src',
                                        'value' => [
                                            'srcpath' => [
                                                'xpath' => '.',
                                            ],
                                        ],
                                    ],
                                    [
                                        'op' => 'sequence',
                                        'var' => 'bg-contents',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        'BT-56' => [
            [
                'match' => '/rsm:CrossIndustryInvoice/rsm:SupplyChainTradeTransaction/ram:ApplicableHeaderTradeAgreement/ram:BuyerTradeParty/ram:DefinedTradeContact/ram:DepartmentName',
                'priority' => 0.5,
                'body' => [
                    [
                        'op' => 'element',
                        'name' => 'Buyer_contact_point',
                        'body' => [
                            [
                                'op' => 'attribute',
                                'name' => 'xr:id',
                                'value' => [
                                    'literal' => 'BT-56',
                                ],
                            ],
                            [
                                'op' => 'attribute',
                                'name' => 'xr:src',
                                'value' => [
                                    'srcpath' => [
                                        'xpath' => '.',
                                    ],
                                ],
                            ],
                            [
                                'op' => 'call',
                                'name' => 'text',
                                'params' => [],
                            ],
                        ],
                    ],
                ],
            ],
            [
                'match' => '/rsm:CrossIndustryInvoice/rsm:SupplyChainTradeTransaction/ram:ApplicableHeaderTradeAgreement/ram:BuyerTradeParty/ram:DefinedTradeContact/ram:PersonName',
                'priority' => 0.5,
                'body' => [
                    [
                        'op' => 'element',
                        'name' => 'Buyer_contact_point',
                        'body' => [
                            [
                                'op' => 'attribute',
                                'name' => 'xr:id',
                                'value' => [
                                    'literal' => 'BT-56',
                                ],
                            ],
                            [
                                'op' => 'attribute',
                                'name' => 'xr:src',
                                'value' => [
                                    'srcpath' => [
                                        'xpath' => '.',
                                    ],
                                ],
                            ],
                            [
                                'op' => 'call',
                                'name' => 'text',
                                'params' => [],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        'BT-57' => [
            [
                'match' => '/rsm:CrossIndustryInvoice/rsm:SupplyChainTradeTransaction/ram:ApplicableHeaderTradeAgreement/ram:BuyerTradeParty/ram:DefinedTradeContact/ram:TelephoneUniversalCommunication/ram:CompleteNumber',
                'priority' => 0.5,
                'body' => [
                    [
                        'op' => 'element',
                        'name' => 'Buyer_contact_telephone_number',
                        'body' => [
                            [
                                'op' => 'attribute',
                                'name' => 'xr:id',
                                'value' => [
                                    'literal' => 'BT-57',
                                ],
                            ],
                            [
                                'op' => 'attribute',
                                'name' => 'xr:src',
                                'value' => [
                                    'srcpath' => [
                                        'xpath' => '.',
                                    ],
                                ],
                            ],
                            [
                                'op' => 'call',
                                'name' => 'text',
                                'params' => [],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        'BT-58' => [
            [
                'match' => '/rsm:CrossIndustryInvoice/rsm:SupplyChainTradeTransaction/ram:ApplicableHeaderTradeAgreement/ram:BuyerTradeParty/ram:DefinedTradeContact/ram:EmailURIUniversalCommunication/ram:URIID',
                'priority' => 0.5,
                'body' => [
                    [
                        'op' => 'element',
                        'name' => 'Buyer_contact_email_address',
                        'body' => [
                            [
                                'op' => 'attribute',
                                'name' => 'xr:id',
                                'value' => [
                                    'literal' => 'BT-58',
                                ],
                            ],
                            [
                                'op' => 'attribute',
                                'name' => 'xr:src',
                                'value' => [
                                    'srcpath' => [
                                        'xpath' => '.',
                                    ],
                                ],
                            ],
                            [
                                'op' => 'call',
                                'name' => 'text',
                                'params' => [],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        'BG-10' => [
            [
                'match' => '/rsm:CrossIndustryInvoice/rsm:SupplyChainTradeTransaction/ram:ApplicableHeaderTradeSettlement/ram:PayeeTradeParty',
                'priority' => 0.5,
                'body' => [
                    [
                        'op' => 'variable',
                        'name' => 'bg-contents',
                        'body' => [
                            [
                                'op' => 'apply',
                                'mode' => 'BT-59',
                                'select' => [
                                    'xpath' => './ram:Name',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BT-60',
                                'select' => [
                                    'xpath' => './ram:GlobalID[boolean(@schemeID)]',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BT-60',
                                'select' => [
                                    'xpath' => './ram:ID[not(following-sibling::ram:GlobalID/@schemeID)]',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BT-61',
                                'select' => [
                                    'xpath' => './ram:SpecifiedLegalOrganization/ram:ID',
                                ],
                            ],
                        ],
                    ],
                    [
                        'op' => 'if',
                        'test' => [
                            'var' => 'bg-contents',
                        ],
                        'body' => [
                            [
                                'op' => 'element',
                                'name' => 'PAYEE',
                                'body' => [
                                    [
                                        'op' => 'attribute',
                                        'name' => 'xr:id',
                                        'value' => [
                                            'literal' => 'BG-10',
                                        ],
                                    ],
                                    [
                                        'op' => 'attribute',
                                        'name' => 'xr:src',
                                        'value' => [
                                            'srcpath' => [
                                                'xpath' => '.',
                                            ],
                                        ],
                                    ],
                                    [
                                        'op' => 'sequence',
                                        'var' => 'bg-contents',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        'BT-59' => [
            [
                'match' => '/rsm:CrossIndustryInvoice/rsm:SupplyChainTradeTransaction/ram:ApplicableHeaderTradeSettlement/ram:PayeeTradeParty/ram:Name',
                'priority' => 0.5,
                'body' => [
                    [
                        'op' => 'element',
                        'name' => 'Payee_name',
                        'body' => [
                            [
                                'op' => 'attribute',
                                'name' => 'xr:id',
                                'value' => [
                                    'literal' => 'BT-59',
                                ],
                            ],
                            [
                                'op' => 'attribute',
                                'name' => 'xr:src',
                                'value' => [
                                    'srcpath' => [
                                        'xpath' => '.',
                                    ],
                                ],
                            ],
                            [
                                'op' => 'call',
                                'name' => 'text',
                                'params' => [],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        'BT-60' => [
            [
                'match' => '/rsm:CrossIndustryInvoice/rsm:SupplyChainTradeTransaction/ram:ApplicableHeaderTradeSettlement/ram:PayeeTradeParty/ram:GlobalID[boolean(@schemeID)]',
                'priority' => 0.5,
                'body' => [
                    [
                        'op' => 'element',
                        'name' => 'Payee_identifier',
                        'body' => [
                            [
                                'op' => 'attribute',
                                'name' => 'xr:id',
                                'value' => [
                                    'literal' => 'BT-60',
                                ],
                            ],
                            [
                                'op' => 'attribute',
                                'name' => 'xr:src',
                                'value' => [
                                    'srcpath' => [
                                        'xpath' => '.',
                                    ],
                                ],
                            ],
                            [
                                'op' => 'call',
                                'name' => 'identifier',
                                'params' => [],
                            ],
                        ],
                    ],
                ],
            ],
            [
                'match' => '/rsm:CrossIndustryInvoice/rsm:SupplyChainTradeTransaction/ram:ApplicableHeaderTradeSettlement/ram:PayeeTradeParty/ram:ID[not(following-sibling::ram:GlobalID/@schemeID)]',
                'priority' => 0.5,
                'body' => [
                    [
                        'op' => 'element',
                        'name' => 'Payee_identifier',
                        'body' => [
                            [
                                'op' => 'attribute',
                                'name' => 'xr:id',
                                'value' => [
                                    'literal' => 'BT-60',
                                ],
                            ],
                            [
                                'op' => 'attribute',
                                'name' => 'xr:src',
                                'value' => [
                                    'srcpath' => [
                                        'xpath' => '.',
                                    ],
                                ],
                            ],
                            [
                                'op' => 'call',
                                'name' => 'identifier',
                                'params' => [],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        'BT-61' => [
            [
                'match' => '/rsm:CrossIndustryInvoice/rsm:SupplyChainTradeTransaction/ram:ApplicableHeaderTradeSettlement/ram:PayeeTradeParty/ram:SpecifiedLegalOrganization/ram:ID',
                'priority' => 0.5,
                'body' => [
                    [
                        'op' => 'element',
                        'name' => 'Payee_legal_registration_identifier',
                        'body' => [
                            [
                                'op' => 'attribute',
                                'name' => 'xr:id',
                                'value' => [
                                    'literal' => 'BT-61',
                                ],
                            ],
                            [
                                'op' => 'attribute',
                                'name' => 'xr:src',
                                'value' => [
                                    'srcpath' => [
                                        'xpath' => '.',
                                    ],
                                ],
                            ],
                            [
                                'op' => 'call',
                                'name' => 'identifier',
                                'params' => [],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        'BG-11' => [
            [
                'match' => '/rsm:CrossIndustryInvoice/rsm:SupplyChainTradeTransaction/ram:ApplicableHeaderTradeAgreement/ram:SellerTaxRepresentativeTradeParty',
                'priority' => 0.5,
                'body' => [
                    [
                        'op' => 'variable',
                        'name' => 'bg-contents',
                        'body' => [
                            [
                                'op' => 'apply',
                                'mode' => 'BT-62',
                                'select' => [
                                    'xpath' => './ram:Name',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BT-63',
                                'select' => [
                                    'xpath' => './ram:SpecifiedTaxRegistration/ram:ID',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BG-12',
                                'select' => [
                                    'xpath' => './ram:PostalTradeAddress',
                                ],
                            ],
                        ],
                    ],
                    [
                        'op' => 'if',
                        'test' => [
                            'var' => 'bg-contents',
                        ],
                        'body' => [
                            [
                                'op' => 'element',
                                'name' => 'SELLER_TAX_REPRESENTATIVE_PARTY',
                                'body' => [
                                    [
                                        'op' => 'attribute',
                                        'name' => 'xr:id',
                                        'value' => [
                                            'literal' => 'BG-11',
                                        ],
                                    ],
                                    [
                                        'op' => 'attribute',
                                        'name' => 'xr:src',
                                        'value' => [
                                            'srcpath' => [
                                                'xpath' => '.',
                                            ],
                                        ],
                                    ],
                                    [
                                        'op' => 'sequence',
                                        'var' => 'bg-contents',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        'BT-62' => [
            [
                'match' => '/rsm:CrossIndustryInvoice/rsm:SupplyChainTradeTransaction/ram:ApplicableHeaderTradeAgreement/ram:SellerTaxRepresentativeTradeParty/ram:Name',
                'priority' => 0.5,
                'body' => [
                    [
                        'op' => 'element',
                        'name' => 'Seller_tax_representative_name',
                        'body' => [
                            [
                                'op' => 'attribute',
                                'name' => 'xr:id',
                                'value' => [
                                    'literal' => 'BT-62',
                                ],
                            ],
                            [
                                'op' => 'attribute',
                                'name' => 'xr:src',
                                'value' => [
                                    'srcpath' => [
                                        'xpath' => '.',
                                    ],
                                ],
                            ],
                            [
                                'op' => 'call',
                                'name' => 'text',
                                'params' => [],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        'BT-63' => [
            [
                'match' => '/rsm:CrossIndustryInvoice/rsm:SupplyChainTradeTransaction/ram:ApplicableHeaderTradeAgreement/ram:SellerTaxRepresentativeTradeParty/ram:SpecifiedTaxRegistration/ram:ID',
                'priority' => 0.5,
                'body' => [
                    [
                        'op' => 'element',
                        'name' => 'Seller_tax_representative_VAT_identifier',
                        'body' => [
                            [
                                'op' => 'attribute',
                                'name' => 'xr:id',
                                'value' => [
                                    'literal' => 'BT-63',
                                ],
                            ],
                            [
                                'op' => 'attribute',
                                'name' => 'xr:src',
                                'value' => [
                                    'srcpath' => [
                                        'xpath' => '.',
                                    ],
                                ],
                            ],
                            [
                                'op' => 'call',
                                'name' => 'identifier',
                                'params' => [],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        'BG-12' => [
            [
                'match' => '/rsm:CrossIndustryInvoice/rsm:SupplyChainTradeTransaction/ram:ApplicableHeaderTradeAgreement/ram:SellerTaxRepresentativeTradeParty/ram:PostalTradeAddress',
                'priority' => 0.5,
                'body' => [
                    [
                        'op' => 'variable',
                        'name' => 'bg-contents',
                        'body' => [
                            [
                                'op' => 'apply',
                                'mode' => 'BT-64',
                                'select' => [
                                    'xpath' => './ram:LineOne',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BT-65',
                                'select' => [
                                    'xpath' => './ram:LineTwo',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BT-164',
                                'select' => [
                                    'xpath' => './ram:LineThree',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BT-66',
                                'select' => [
                                    'xpath' => './ram:CityName',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BT-67',
                                'select' => [
                                    'xpath' => './ram:PostcodeCode',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BT-68',
                                'select' => [
                                    'xpath' => './ram:CountrySubDivisionName',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BT-69',
                                'select' => [
                                    'xpath' => './ram:CountryID',
                                ],
                            ],
                        ],
                    ],
                    [
                        'op' => 'if',
                        'test' => [
                            'var' => 'bg-contents',
                        ],
                        'body' => [
                            [
                                'op' => 'element',
                                'name' => 'SELLER_TAX_REPRESENTATIVE_POSTAL_ADDRESS',
                                'body' => [
                                    [
                                        'op' => 'attribute',
                                        'name' => 'xr:id',
                                        'value' => [
                                            'literal' => 'BG-12',
                                        ],
                                    ],
                                    [
                                        'op' => 'attribute',
                                        'name' => 'xr:src',
                                        'value' => [
                                            'srcpath' => [
                                                'xpath' => '.',
                                            ],
                                        ],
                                    ],
                                    [
                                        'op' => 'sequence',
                                        'var' => 'bg-contents',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        'BT-64' => [
            [
                'match' => '/rsm:CrossIndustryInvoice/rsm:SupplyChainTradeTransaction/ram:ApplicableHeaderTradeAgreement/ram:SellerTaxRepresentativeTradeParty/ram:PostalTradeAddress/ram:LineOne',
                'priority' => 0.5,
                'body' => [
                    [
                        'op' => 'element',
                        'name' => 'Tax_representative_address_line_1',
                        'body' => [
                            [
                                'op' => 'attribute',
                                'name' => 'xr:id',
                                'value' => [
                                    'literal' => 'BT-64',
                                ],
                            ],
                            [
                                'op' => 'attribute',
                                'name' => 'xr:src',
                                'value' => [
                                    'srcpath' => [
                                        'xpath' => '.',
                                    ],
                                ],
                            ],
                            [
                                'op' => 'call',
                                'name' => 'text',
                                'params' => [],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        'BT-65' => [
            [
                'match' => '/rsm:CrossIndustryInvoice/rsm:SupplyChainTradeTransaction/ram:ApplicableHeaderTradeAgreement/ram:SellerTaxRepresentativeTradeParty/ram:PostalTradeAddress/ram:LineTwo',
                'priority' => 0.5,
                'body' => [
                    [
                        'op' => 'element',
                        'name' => 'Tax_representative_address_line_2',
                        'body' => [
                            [
                                'op' => 'attribute',
                                'name' => 'xr:id',
                                'value' => [
                                    'literal' => 'BT-65',
                                ],
                            ],
                            [
                                'op' => 'attribute',
                                'name' => 'xr:src',
                                'value' => [
                                    'srcpath' => [
                                        'xpath' => '.',
                                    ],
                                ],
                            ],
                            [
                                'op' => 'call',
                                'name' => 'text',
                                'params' => [],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        'BT-164' => [
            [
                'match' => '/rsm:CrossIndustryInvoice/rsm:SupplyChainTradeTransaction/ram:ApplicableHeaderTradeAgreement/ram:SellerTaxRepresentativeTradeParty/ram:PostalTradeAddress/ram:LineThree',
                'priority' => 0.5,
                'body' => [
                    [
                        'op' => 'element',
                        'name' => 'Tax_representative_address_line_3',
                        'body' => [
                            [
                                'op' => 'attribute',
                                'name' => 'xr:id',
                                'value' => [
                                    'literal' => 'BT-164',
                                ],
                            ],
                            [
                                'op' => 'attribute',
                                'name' => 'xr:src',
                                'value' => [
                                    'srcpath' => [
                                        'xpath' => '.',
                                    ],
                                ],
                            ],
                            [
                                'op' => 'call',
                                'name' => 'text',
                                'params' => [],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        'BT-66' => [
            [
                'match' => '/rsm:CrossIndustryInvoice/rsm:SupplyChainTradeTransaction/ram:ApplicableHeaderTradeAgreement/ram:SellerTaxRepresentativeTradeParty/ram:PostalTradeAddress/ram:CityName',
                'priority' => 0.5,
                'body' => [
                    [
                        'op' => 'element',
                        'name' => 'Tax_representative_city',
                        'body' => [
                            [
                                'op' => 'attribute',
                                'name' => 'xr:id',
                                'value' => [
                                    'literal' => 'BT-66',
                                ],
                            ],
                            [
                                'op' => 'attribute',
                                'name' => 'xr:src',
                                'value' => [
                                    'srcpath' => [
                                        'xpath' => '.',
                                    ],
                                ],
                            ],
                            [
                                'op' => 'call',
                                'name' => 'text',
                                'params' => [],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        'BT-67' => [
            [
                'match' => '/rsm:CrossIndustryInvoice/rsm:SupplyChainTradeTransaction/ram:ApplicableHeaderTradeAgreement/ram:SellerTaxRepresentativeTradeParty/ram:PostalTradeAddress/ram:PostcodeCode',
                'priority' => 0.5,
                'body' => [
                    [
                        'op' => 'element',
                        'name' => 'Tax_representative_post_code',
                        'body' => [
                            [
                                'op' => 'attribute',
                                'name' => 'xr:id',
                                'value' => [
                                    'literal' => 'BT-67',
                                ],
                            ],
                            [
                                'op' => 'attribute',
                                'name' => 'xr:src',
                                'value' => [
                                    'srcpath' => [
                                        'xpath' => '.',
                                    ],
                                ],
                            ],
                            [
                                'op' => 'call',
                                'name' => 'text',
                                'params' => [],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        'BT-68' => [
            [
                'match' => '/rsm:CrossIndustryInvoice/rsm:SupplyChainTradeTransaction/ram:ApplicableHeaderTradeAgreement/ram:SellerTaxRepresentativeTradeParty/ram:PostalTradeAddress/ram:CountrySubDivisionName',
                'priority' => 0.5,
                'body' => [
                    [
                        'op' => 'element',
                        'name' => 'Tax_representative_country_subdivision',
                        'body' => [
                            [
                                'op' => 'attribute',
                                'name' => 'xr:id',
                                'value' => [
                                    'literal' => 'BT-68',
                                ],
                            ],
                            [
                                'op' => 'attribute',
                                'name' => 'xr:src',
                                'value' => [
                                    'srcpath' => [
                                        'xpath' => '.',
                                    ],
                                ],
                            ],
                            [
                                'op' => 'call',
                                'name' => 'text',
                                'params' => [],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        'BT-69' => [
            [
                'match' => '/rsm:CrossIndustryInvoice/rsm:SupplyChainTradeTransaction/ram:ApplicableHeaderTradeAgreement/ram:SellerTaxRepresentativeTradeParty/ram:PostalTradeAddress/ram:CountryID',
                'priority' => 0.5,
                'body' => [
                    [
                        'op' => 'element',
                        'name' => 'Tax_representative_country_code',
                        'body' => [
                            [
                                'op' => 'attribute',
                                'name' => 'xr:id',
                                'value' => [
                                    'literal' => 'BT-69',
                                ],
                            ],
                            [
                                'op' => 'attribute',
                                'name' => 'xr:src',
                                'value' => [
                                    'srcpath' => [
                                        'xpath' => '.',
                                    ],
                                ],
                            ],
                            [
                                'op' => 'call',
                                'name' => 'code',
                                'params' => [],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        'BG-13' => [
            [
                'match' => '/rsm:CrossIndustryInvoice/rsm:SupplyChainTradeTransaction [ boolean( ram:ApplicableHeaderTradeDelivery/ram:ShipToTradeParty) or boolean( ram:ApplicableHeaderTradeDelivery/ram:ActualDeliverySupplyChainEvent/ram:OccurrenceDateTime/udt:DateTimeString) or boolean( ram:ApplicableHeaderTradeSettlement/ram:BillingSpecifiedPeriod) ]',
                'priority' => 0.5,
                'body' => [
                    [
                        'op' => 'variable',
                        'name' => 'bg-contents',
                        'body' => [
                            [
                                'op' => 'apply',
                                'mode' => 'BT-70',
                                'select' => [
                                    'xpath' => './ram:ApplicableHeaderTradeDelivery/ram:ShipToTradeParty/ram:Name',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BT-71',
                                'select' => [
                                    'xpath' => './ram:ApplicableHeaderTradeDelivery/ram:ShipToTradeParty/ram:ID[not(following-sibling::ram:GlobalID/@schemeID)]',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BT-71',
                                'select' => [
                                    'xpath' => './ram:ApplicableHeaderTradeDelivery/ram:ShipToTradeParty/ram:GlobalID[boolean(@schemeID)]',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BT-72',
                                'select' => [
                                    'xpath' => './ram:ApplicableHeaderTradeDelivery/ram:ActualDeliverySupplyChainEvent/ram:OccurrenceDateTime/udt:DateTimeString[@format = \'102\']',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BG-15',
                                'select' => [
                                    'xpath' => './ram:ApplicableHeaderTradeDelivery/ram:ShipToTradeParty/ram:PostalTradeAddress',
                                ],
                            ],
                        ],
                    ],
                    [
                        'op' => 'if',
                        'test' => [
                            'var' => 'bg-contents',
                        ],
                        'body' => [
                            [
                                'op' => 'element',
                                'name' => 'DELIVERY_INFORMATION',
                                'body' => [
                                    [
                                        'op' => 'attribute',
                                        'name' => 'xr:id',
                                        'value' => [
                                            'literal' => 'BG-13',
                                        ],
                                    ],
                                    [
                                        'op' => 'attribute',
                                        'name' => 'xr:src',
                                        'value' => [
                                            'srcpath' => [
                                                'xpath' => '.',
                                            ],
                                        ],
                                    ],
                                    [
                                        'op' => 'sequence',
                                        'var' => 'bg-contents',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        'BT-70' => [
            [
                'match' => '/rsm:CrossIndustryInvoice/rsm:SupplyChainTradeTransaction/ram:ApplicableHeaderTradeDelivery/ram:ShipToTradeParty/ram:Name',
                'priority' => 0.5,
                'body' => [
                    [
                        'op' => 'element',
                        'name' => 'Deliver_to_party_name',
                        'body' => [
                            [
                                'op' => 'attribute',
                                'name' => 'xr:id',
                                'value' => [
                                    'literal' => 'BT-70',
                                ],
                            ],
                            [
                                'op' => 'attribute',
                                'name' => 'xr:src',
                                'value' => [
                                    'srcpath' => [
                                        'xpath' => '.',
                                    ],
                                ],
                            ],
                            [
                                'op' => 'call',
                                'name' => 'text',
                                'params' => [],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        'BT-71' => [
            [
                'match' => '/rsm:CrossIndustryInvoice/rsm:SupplyChainTradeTransaction/ram:ApplicableHeaderTradeDelivery/ram:ShipToTradeParty/ram:ID[not(following-sibling::ram:GlobalID/@schemeID)]',
                'priority' => 0.5,
                'body' => [
                    [
                        'op' => 'element',
                        'name' => 'Deliver_to_location_identifier',
                        'body' => [
                            [
                                'op' => 'attribute',
                                'name' => 'xr:id',
                                'value' => [
                                    'literal' => 'BT-71',
                                ],
                            ],
                            [
                                'op' => 'attribute',
                                'name' => 'xr:src',
                                'value' => [
                                    'srcpath' => [
                                        'xpath' => '.',
                                    ],
                                ],
                            ],
                            [
                                'op' => 'call',
                                'name' => 'identifier',
                                'params' => [],
                            ],
                        ],
                    ],
                ],
            ],
            [
                'match' => '/rsm:CrossIndustryInvoice/rsm:SupplyChainTradeTransaction/ram:ApplicableHeaderTradeDelivery/ram:ShipToTradeParty/ram:GlobalID[boolean(@schemeID)]',
                'priority' => 0.5,
                'body' => [
                    [
                        'op' => 'element',
                        'name' => 'Deliver_to_location_identifier',
                        'body' => [
                            [
                                'op' => 'attribute',
                                'name' => 'xr:id',
                                'value' => [
                                    'literal' => 'BT-71',
                                ],
                            ],
                            [
                                'op' => 'attribute',
                                'name' => 'xr:src',
                                'value' => [
                                    'srcpath' => [
                                        'xpath' => '.',
                                    ],
                                ],
                            ],
                            [
                                'op' => 'call',
                                'name' => 'identifier',
                                'params' => [],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        'BT-72' => [
            [
                'match' => '/rsm:CrossIndustryInvoice/rsm:SupplyChainTradeTransaction/ram:ApplicableHeaderTradeDelivery/ram:ActualDeliverySupplyChainEvent/ram:OccurrenceDateTime/udt:DateTimeString[@format = \'102\']',
                'priority' => 0.5,
                'body' => [
                    [
                        'op' => 'element',
                        'name' => 'Actual_delivery_date',
                        'body' => [
                            [
                                'op' => 'attribute',
                                'name' => 'xr:id',
                                'value' => [
                                    'literal' => 'BT-72',
                                ],
                            ],
                            [
                                'op' => 'attribute',
                                'name' => 'xr:src',
                                'value' => [
                                    'srcpath' => [
                                        'xpath' => '.',
                                    ],
                                ],
                            ],
                            [
                                'op' => 'call',
                                'name' => 'date',
                                'params' => [],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        'BG-14' => [
            [
                'match' => '/rsm:CrossIndustryInvoice/rsm:SupplyChainTradeTransaction/ram:ApplicableHeaderTradeSettlement/ram:BillingSpecifiedPeriod',
                'priority' => 0.5,
                'body' => [
                    [
                        'op' => 'variable',
                        'name' => 'bg-contents',
                        'body' => [
                            [
                                'op' => 'apply',
                                'mode' => 'BT-73',
                                'select' => [
                                    'xpath' => './ram:StartDateTime/udt:DateTimeString[@format = \'102\']',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BT-74',
                                'select' => [
                                    'xpath' => './ram:EndDateTime/udt:DateTimeString[@format = \'102\']',
                                ],
                            ],
                        ],
                    ],
                    [
                        'op' => 'if',
                        'test' => [
                            'var' => 'bg-contents',
                        ],
                        'body' => [
                            [
                                'op' => 'element',
                                'name' => 'INVOICING_PERIOD',
                                'body' => [
                                    [
                                        'op' => 'attribute',
                                        'name' => 'xr:id',
                                        'value' => [
                                            'literal' => 'BG-14',
                                        ],
                                    ],
                                    [
                                        'op' => 'attribute',
                                        'name' => 'xr:src',
                                        'value' => [
                                            'srcpath' => [
                                                'xpath' => '.',
                                            ],
                                        ],
                                    ],
                                    [
                                        'op' => 'sequence',
                                        'var' => 'bg-contents',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        'BT-73' => [
            [
                'match' => '/rsm:CrossIndustryInvoice/rsm:SupplyChainTradeTransaction/ram:ApplicableHeaderTradeSettlement/ram:BillingSpecifiedPeriod/ram:StartDateTime/udt:DateTimeString[@format = \'102\']',
                'priority' => 0.5,
                'body' => [
                    [
                        'op' => 'element',
                        'name' => 'Invoicing_period_start_date',
                        'body' => [
                            [
                                'op' => 'attribute',
                                'name' => 'xr:id',
                                'value' => [
                                    'literal' => 'BT-73',
                                ],
                            ],
                            [
                                'op' => 'attribute',
                                'name' => 'xr:src',
                                'value' => [
                                    'srcpath' => [
                                        'xpath' => '.',
                                    ],
                                ],
                            ],
                            [
                                'op' => 'call',
                                'name' => 'date',
                                'params' => [],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        'BT-74' => [
            [
                'match' => '/rsm:CrossIndustryInvoice/rsm:SupplyChainTradeTransaction/ram:ApplicableHeaderTradeSettlement/ram:BillingSpecifiedPeriod/ram:EndDateTime/udt:DateTimeString[@format = \'102\']',
                'priority' => 0.5,
                'body' => [
                    [
                        'op' => 'element',
                        'name' => 'Invoicing_period_end_date',
                        'body' => [
                            [
                                'op' => 'attribute',
                                'name' => 'xr:id',
                                'value' => [
                                    'literal' => 'BT-74',
                                ],
                            ],
                            [
                                'op' => 'attribute',
                                'name' => 'xr:src',
                                'value' => [
                                    'srcpath' => [
                                        'xpath' => '.',
                                    ],
                                ],
                            ],
                            [
                                'op' => 'call',
                                'name' => 'date',
                                'params' => [],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        'BG-15' => [
            [
                'match' => '/rsm:CrossIndustryInvoice/rsm:SupplyChainTradeTransaction/ram:ApplicableHeaderTradeDelivery/ram:ShipToTradeParty/ram:PostalTradeAddress',
                'priority' => 0.5,
                'body' => [
                    [
                        'op' => 'variable',
                        'name' => 'bg-contents',
                        'body' => [
                            [
                                'op' => 'apply',
                                'mode' => 'BT-75',
                                'select' => [
                                    'xpath' => './ram:LineOne',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BT-76',
                                'select' => [
                                    'xpath' => './ram:LineTwo',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BT-165',
                                'select' => [
                                    'xpath' => './ram:LineThree',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BT-77',
                                'select' => [
                                    'xpath' => './ram:CityName',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BT-78',
                                'select' => [
                                    'xpath' => './ram:PostcodeCode',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BT-79',
                                'select' => [
                                    'xpath' => './ram:CountrySubDivisionName',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BT-80',
                                'select' => [
                                    'xpath' => './ram:CountryID',
                                ],
                            ],
                        ],
                    ],
                    [
                        'op' => 'if',
                        'test' => [
                            'var' => 'bg-contents',
                        ],
                        'body' => [
                            [
                                'op' => 'element',
                                'name' => 'DELIVER_TO_ADDRESS',
                                'body' => [
                                    [
                                        'op' => 'attribute',
                                        'name' => 'xr:id',
                                        'value' => [
                                            'literal' => 'BG-15',
                                        ],
                                    ],
                                    [
                                        'op' => 'attribute',
                                        'name' => 'xr:src',
                                        'value' => [
                                            'srcpath' => [
                                                'xpath' => '.',
                                            ],
                                        ],
                                    ],
                                    [
                                        'op' => 'sequence',
                                        'var' => 'bg-contents',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        'BT-75' => [
            [
                'match' => '/rsm:CrossIndustryInvoice/rsm:SupplyChainTradeTransaction/ram:ApplicableHeaderTradeDelivery/ram:ShipToTradeParty/ram:PostalTradeAddress/ram:LineOne',
                'priority' => 0.5,
                'body' => [
                    [
                        'op' => 'element',
                        'name' => 'Deliver_to_address_line_1',
                        'body' => [
                            [
                                'op' => 'attribute',
                                'name' => 'xr:id',
                                'value' => [
                                    'literal' => 'BT-75',
                                ],
                            ],
                            [
                                'op' => 'attribute',
                                'name' => 'xr:src',
                                'value' => [
                                    'srcpath' => [
                                        'xpath' => '.',
                                    ],
                                ],
                            ],
                            [
                                'op' => 'call',
                                'name' => 'text',
                                'params' => [],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        'BT-76' => [
            [
                'match' => '/rsm:CrossIndustryInvoice/rsm:SupplyChainTradeTransaction/ram:ApplicableHeaderTradeDelivery/ram:ShipToTradeParty/ram:PostalTradeAddress/ram:LineTwo',
                'priority' => 0.5,
                'body' => [
                    [
                        'op' => 'element',
                        'name' => 'Deliver_to_address_line_2',
                        'body' => [
                            [
                                'op' => 'attribute',
                                'name' => 'xr:id',
                                'value' => [
                                    'literal' => 'BT-76',
                                ],
                            ],
                            [
                                'op' => 'attribute',
                                'name' => 'xr:src',
                                'value' => [
                                    'srcpath' => [
                                        'xpath' => '.',
                                    ],
                                ],
                            ],
                            [
                                'op' => 'call',
                                'name' => 'text',
                                'params' => [],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        'BT-165' => [
            [
                'match' => '/rsm:CrossIndustryInvoice/rsm:SupplyChainTradeTransaction/ram:ApplicableHeaderTradeDelivery/ram:ShipToTradeParty/ram:PostalTradeAddress/ram:LineThree',
                'priority' => 0.5,
                'body' => [
                    [
                        'op' => 'element',
                        'name' => 'Deliver_to_address_line_3',
                        'body' => [
                            [
                                'op' => 'attribute',
                                'name' => 'xr:id',
                                'value' => [
                                    'literal' => 'BT-165',
                                ],
                            ],
                            [
                                'op' => 'attribute',
                                'name' => 'xr:src',
                                'value' => [
                                    'srcpath' => [
                                        'xpath' => '.',
                                    ],
                                ],
                            ],
                            [
                                'op' => 'call',
                                'name' => 'text',
                                'params' => [],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        'BT-77' => [
            [
                'match' => '/rsm:CrossIndustryInvoice/rsm:SupplyChainTradeTransaction/ram:ApplicableHeaderTradeDelivery/ram:ShipToTradeParty/ram:PostalTradeAddress/ram:CityName',
                'priority' => 0.5,
                'body' => [
                    [
                        'op' => 'element',
                        'name' => 'Deliver_to_city',
                        'body' => [
                            [
                                'op' => 'attribute',
                                'name' => 'xr:id',
                                'value' => [
                                    'literal' => 'BT-77',
                                ],
                            ],
                            [
                                'op' => 'attribute',
                                'name' => 'xr:src',
                                'value' => [
                                    'srcpath' => [
                                        'xpath' => '.',
                                    ],
                                ],
                            ],
                            [
                                'op' => 'call',
                                'name' => 'text',
                                'params' => [],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        'BT-78' => [
            [
                'match' => '/rsm:CrossIndustryInvoice/rsm:SupplyChainTradeTransaction/ram:ApplicableHeaderTradeDelivery/ram:ShipToTradeParty/ram:PostalTradeAddress/ram:PostcodeCode',
                'priority' => 0.5,
                'body' => [
                    [
                        'op' => 'element',
                        'name' => 'Deliver_to_post_code',
                        'body' => [
                            [
                                'op' => 'attribute',
                                'name' => 'xr:id',
                                'value' => [
                                    'literal' => 'BT-78',
                                ],
                            ],
                            [
                                'op' => 'attribute',
                                'name' => 'xr:src',
                                'value' => [
                                    'srcpath' => [
                                        'xpath' => '.',
                                    ],
                                ],
                            ],
                            [
                                'op' => 'call',
                                'name' => 'text',
                                'params' => [],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        'BT-79' => [
            [
                'match' => '/rsm:CrossIndustryInvoice/rsm:SupplyChainTradeTransaction/ram:ApplicableHeaderTradeDelivery/ram:ShipToTradeParty/ram:PostalTradeAddress/ram:CountrySubDivisionName',
                'priority' => 0.5,
                'body' => [
                    [
                        'op' => 'element',
                        'name' => 'Deliver_to_country_subdivision',
                        'body' => [
                            [
                                'op' => 'attribute',
                                'name' => 'xr:id',
                                'value' => [
                                    'literal' => 'BT-79',
                                ],
                            ],
                            [
                                'op' => 'attribute',
                                'name' => 'xr:src',
                                'value' => [
                                    'srcpath' => [
                                        'xpath' => '.',
                                    ],
                                ],
                            ],
                            [
                                'op' => 'call',
                                'name' => 'text',
                                'params' => [],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        'BT-80' => [
            [
                'match' => '/rsm:CrossIndustryInvoice/rsm:SupplyChainTradeTransaction/ram:ApplicableHeaderTradeDelivery/ram:ShipToTradeParty/ram:PostalTradeAddress/ram:CountryID',
                'priority' => 0.5,
                'body' => [
                    [
                        'op' => 'element',
                        'name' => 'Deliver_to_country_code',
                        'body' => [
                            [
                                'op' => 'attribute',
                                'name' => 'xr:id',
                                'value' => [
                                    'literal' => 'BT-80',
                                ],
                            ],
                            [
                                'op' => 'attribute',
                                'name' => 'xr:src',
                                'value' => [
                                    'srcpath' => [
                                        'xpath' => '.',
                                    ],
                                ],
                            ],
                            [
                                'op' => 'call',
                                'name' => 'code',
                                'params' => [],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        'BG-16' => [
            [
                'match' => '/rsm:CrossIndustryInvoice/rsm:SupplyChainTradeTransaction/ram:ApplicableHeaderTradeSettlement/ram:SpecifiedTradeSettlementPaymentMeans',
                'priority' => 0.5,
                'body' => [
                    [
                        'op' => 'variable',
                        'name' => 'bg-contents',
                        'body' => [
                            [
                                'op' => 'apply',
                                'mode' => 'BT-81',
                                'select' => [
                                    'xpath' => './ram:TypeCode',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BT-82',
                                'select' => [
                                    'xpath' => './ram:Information',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BT-83',
                                'select' => [
                                    'xpath' => '/rsm:CrossIndustryInvoice/rsm:SupplyChainTradeTransaction/ram:ApplicableHeaderTradeSettlement/ram:PaymentReference',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BG-17',
                                'select' => [
                                    'xpath' => './ram:PayeePartyCreditorFinancialAccount',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BG-18',
                                'select' => [
                                    'xpath' => './ram:ApplicableTradeSettlementFinancialCard',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BG-19',
                                'select' => [
                                    'xpath' => '/rsm:CrossIndustryInvoice/rsm:SupplyChainTradeTransaction/ram:ApplicableHeaderTradeSettlement',
                                ],
                            ],
                        ],
                    ],
                    [
                        'op' => 'if',
                        'test' => [
                            'var' => 'bg-contents',
                        ],
                        'body' => [
                            [
                                'op' => 'element',
                                'name' => 'PAYMENT_INSTRUCTIONS',
                                'body' => [
                                    [
                                        'op' => 'attribute',
                                        'name' => 'xr:id',
                                        'value' => [
                                            'literal' => 'BG-16',
                                        ],
                                    ],
                                    [
                                        'op' => 'attribute',
                                        'name' => 'xr:src',
                                        'value' => [
                                            'srcpath' => [
                                                'xpath' => '.',
                                            ],
                                        ],
                                    ],
                                    [
                                        'op' => 'sequence',
                                        'var' => 'bg-contents',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        'BT-81' => [
            [
                'match' => '/rsm:CrossIndustryInvoice/rsm:SupplyChainTradeTransaction/ram:ApplicableHeaderTradeSettlement/ram:SpecifiedTradeSettlementPaymentMeans/ram:TypeCode',
                'priority' => 0.5,
                'body' => [
                    [
                        'op' => 'element',
                        'name' => 'Payment_means_type_code',
                        'body' => [
                            [
                                'op' => 'attribute',
                                'name' => 'xr:id',
                                'value' => [
                                    'literal' => 'BT-81',
                                ],
                            ],
                            [
                                'op' => 'attribute',
                                'name' => 'xr:src',
                                'value' => [
                                    'srcpath' => [
                                        'xpath' => '.',
                                    ],
                                ],
                            ],
                            [
                                'op' => 'call',
                                'name' => 'code',
                                'params' => [],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        'BT-82' => [
            [
                'match' => '/rsm:CrossIndustryInvoice/rsm:SupplyChainTradeTransaction/ram:ApplicableHeaderTradeSettlement/ram:SpecifiedTradeSettlementPaymentMeans/ram:Information',
                'priority' => 0.5,
                'body' => [
                    [
                        'op' => 'element',
                        'name' => 'Payment_means_text',
                        'body' => [
                            [
                                'op' => 'attribute',
                                'name' => 'xr:id',
                                'value' => [
                                    'literal' => 'BT-82',
                                ],
                            ],
                            [
                                'op' => 'attribute',
                                'name' => 'xr:src',
                                'value' => [
                                    'srcpath' => [
                                        'xpath' => '.',
                                    ],
                                ],
                            ],
                            [
                                'op' => 'call',
                                'name' => 'text',
                                'params' => [],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        'BT-83' => [
            [
                'match' => '/rsm:CrossIndustryInvoice/rsm:SupplyChainTradeTransaction/ram:ApplicableHeaderTradeSettlement/ram:PaymentReference',
                'priority' => 0.5,
                'body' => [
                    [
                        'op' => 'element',
                        'name' => 'Remittance_information',
                        'body' => [
                            [
                                'op' => 'attribute',
                                'name' => 'xr:id',
                                'value' => [
                                    'literal' => 'BT-83',
                                ],
                            ],
                            [
                                'op' => 'attribute',
                                'name' => 'xr:src',
                                'value' => [
                                    'srcpath' => [
                                        'xpath' => '.',
                                    ],
                                ],
                            ],
                            [
                                'op' => 'call',
                                'name' => 'text',
                                'params' => [],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        'BG-17' => [
            [
                'match' => '/rsm:CrossIndustryInvoice/rsm:SupplyChainTradeTransaction/ram:ApplicableHeaderTradeSettlement/ram:SpecifiedTradeSettlementPaymentMeans/ram:PayeePartyCreditorFinancialAccount',
                'priority' => 0.5,
                'body' => [
                    [
                        'op' => 'variable',
                        'name' => 'bg-contents',
                        'body' => [
                            [
                                'op' => 'apply',
                                'mode' => 'BT-84',
                                'select' => [
                                    'xpath' => './ram:ProprietaryID',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BT-84',
                                'select' => [
                                    'xpath' => './ram:IBANID',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BT-85',
                                'select' => [
                                    'xpath' => './ram:AccountName',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BT-86',
                                'select' => [
                                    'xpath' => './../ram:PayeeSpecifiedCreditorFinancialInstitution/ram:BICID',
                                ],
                            ],
                        ],
                    ],
                    [
                        'op' => 'if',
                        'test' => [
                            'var' => 'bg-contents',
                        ],
                        'body' => [
                            [
                                'op' => 'element',
                                'name' => 'CREDIT_TRANSFER',
                                'body' => [
                                    [
                                        'op' => 'attribute',
                                        'name' => 'xr:id',
                                        'value' => [
                                            'literal' => 'BG-17',
                                        ],
                                    ],
                                    [
                                        'op' => 'attribute',
                                        'name' => 'xr:src',
                                        'value' => [
                                            'srcpath' => [
                                                'xpath' => '.',
                                            ],
                                        ],
                                    ],
                                    [
                                        'op' => 'sequence',
                                        'var' => 'bg-contents',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        'BT-84' => [
            [
                'match' => '/rsm:CrossIndustryInvoice/rsm:SupplyChainTradeTransaction/ram:ApplicableHeaderTradeSettlement/ram:SpecifiedTradeSettlementPaymentMeans/ram:PayeePartyCreditorFinancialAccount/ram:ProprietaryID',
                'priority' => 0.5,
                'body' => [
                    [
                        'op' => 'element',
                        'name' => 'Payment_account_identifier',
                        'body' => [
                            [
                                'op' => 'attribute',
                                'name' => 'xr:id',
                                'value' => [
                                    'literal' => 'BT-84',
                                ],
                            ],
                            [
                                'op' => 'attribute',
                                'name' => 'xr:src',
                                'value' => [
                                    'srcpath' => [
                                        'xpath' => '.',
                                    ],
                                ],
                            ],
                            [
                                'op' => 'call',
                                'name' => 'identifier',
                                'params' => [],
                            ],
                        ],
                    ],
                ],
            ],
            [
                'match' => '/rsm:CrossIndustryInvoice/rsm:SupplyChainTradeTransaction/ram:ApplicableHeaderTradeSettlement/ram:SpecifiedTradeSettlementPaymentMeans/ram:PayeePartyCreditorFinancialAccount/ram:IBANID',
                'priority' => 0.5,
                'body' => [
                    [
                        'op' => 'element',
                        'name' => 'Payment_account_identifier',
                        'body' => [
                            [
                                'op' => 'attribute',
                                'name' => 'xr:id',
                                'value' => [
                                    'literal' => 'BT-84',
                                ],
                            ],
                            [
                                'op' => 'attribute',
                                'name' => 'xr:src',
                                'value' => [
                                    'srcpath' => [
                                        'xpath' => '.',
                                    ],
                                ],
                            ],
                            [
                                'op' => 'call',
                                'name' => 'identifier',
                                'params' => [],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        'BT-85' => [
            [
                'match' => '/rsm:CrossIndustryInvoice/rsm:SupplyChainTradeTransaction/ram:ApplicableHeaderTradeSettlement/ram:SpecifiedTradeSettlementPaymentMeans/ram:PayeePartyCreditorFinancialAccount/ram:AccountName',
                'priority' => 0.5,
                'body' => [
                    [
                        'op' => 'element',
                        'name' => 'Payment_account_name',
                        'body' => [
                            [
                                'op' => 'attribute',
                                'name' => 'xr:id',
                                'value' => [
                                    'literal' => 'BT-85',
                                ],
                            ],
                            [
                                'op' => 'attribute',
                                'name' => 'xr:src',
                                'value' => [
                                    'srcpath' => [
                                        'xpath' => '.',
                                    ],
                                ],
                            ],
                            [
                                'op' => 'call',
                                'name' => 'text',
                                'params' => [],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        'BT-86' => [
            [
                'match' => '/rsm:CrossIndustryInvoice/rsm:SupplyChainTradeTransaction/ram:ApplicableHeaderTradeSettlement/ram:SpecifiedTradeSettlementPaymentMeans/ram:PayeeSpecifiedCreditorFinancialInstitution/ram:BICID',
                'priority' => 0.5,
                'body' => [
                    [
                        'op' => 'element',
                        'name' => 'Payment_service_provider_identifier',
                        'body' => [
                            [
                                'op' => 'attribute',
                                'name' => 'xr:id',
                                'value' => [
                                    'literal' => 'BT-86',
                                ],
                            ],
                            [
                                'op' => 'attribute',
                                'name' => 'xr:src',
                                'value' => [
                                    'srcpath' => [
                                        'xpath' => '.',
                                    ],
                                ],
                            ],
                            [
                                'op' => 'call',
                                'name' => 'text',
                                'params' => [],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        'BG-18' => [
            [
                'match' => '/rsm:CrossIndustryInvoice/rsm:SupplyChainTradeTransaction/ram:ApplicableHeaderTradeSettlement/ram:SpecifiedTradeSettlementPaymentMeans/ram:ApplicableTradeSettlementFinancialCard',
                'priority' => 0.5,
                'body' => [
                    [
                        'op' => 'variable',
                        'name' => 'bg-contents',
                        'body' => [
                            [
                                'op' => 'apply',
                                'mode' => 'BT-87',
                                'select' => [
                                    'xpath' => './ram:ID',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BT-88',
                                'select' => [
                                    'xpath' => './ram:CardholderName',
                                ],
                            ],
                        ],
                    ],
                    [
                        'op' => 'if',
                        'test' => [
                            'var' => 'bg-contents',
                        ],
                        'body' => [
                            [
                                'op' => 'element',
                                'name' => 'PAYMENT_CARD_INFORMATION',
                                'body' => [
                                    [
                                        'op' => 'attribute',
                                        'name' => 'xr:id',
                                        'value' => [
                                            'literal' => 'BG-18',
                                        ],
                                    ],
                                    [
                                        'op' => 'attribute',
                                        'name' => 'xr:src',
                                        'value' => [
                                            'srcpath' => [
                                                'xpath' => '.',
                                            ],
                                        ],
                                    ],
                                    [
                                        'op' => 'sequence',
                                        'var' => 'bg-contents',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        'BT-87' => [
            [
                'match' => '/rsm:CrossIndustryInvoice/rsm:SupplyChainTradeTransaction/ram:ApplicableHeaderTradeSettlement/ram:SpecifiedTradeSettlementPaymentMeans/ram:ApplicableTradeSettlementFinancialCard/ram:ID',
                'priority' => 0.5,
                'body' => [
                    [
                        'op' => 'element',
                        'name' => 'Payment_card_primary_account_number',
                        'body' => [
                            [
                                'op' => 'attribute',
                                'name' => 'xr:id',
                                'value' => [
                                    'literal' => 'BT-87',
                                ],
                            ],
                            [
                                'op' => 'attribute',
                                'name' => 'xr:src',
                                'value' => [
                                    'srcpath' => [
                                        'xpath' => '.',
                                    ],
                                ],
                            ],
                            [
                                'op' => 'call',
                                'name' => 'text',
                                'params' => [],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        'BT-88' => [
            [
                'match' => '/rsm:CrossIndustryInvoice/rsm:SupplyChainTradeTransaction/ram:ApplicableHeaderTradeSettlement/ram:SpecifiedTradeSettlementPaymentMeans/ram:ApplicableTradeSettlementFinancialCard/ram:CardholderName',
                'priority' => 0.5,
                'body' => [
                    [
                        'op' => 'element',
                        'name' => 'Payment_card_holder_name',
                        'body' => [
                            [
                                'op' => 'attribute',
                                'name' => 'xr:id',
                                'value' => [
                                    'literal' => 'BT-88',
                                ],
                            ],
                            [
                                'op' => 'attribute',
                                'name' => 'xr:src',
                                'value' => [
                                    'srcpath' => [
                                        'xpath' => '.',
                                    ],
                                ],
                            ],
                            [
                                'op' => 'call',
                                'name' => 'text',
                                'params' => [],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        'BG-19' => [
            [
                'match' => '/rsm:CrossIndustryInvoice/rsm:SupplyChainTradeTransaction/ram:ApplicableHeaderTradeSettlement',
                'priority' => 0.5,
                'body' => [
                    [
                        'op' => 'variable',
                        'name' => 'bg-contents',
                        'body' => [
                            [
                                'op' => 'apply',
                                'mode' => 'BT-89',
                                'select' => [
                                    'xpath' => './ram:SpecifiedTradePaymentTerms/ram:DirectDebitMandateID',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BT-90',
                                'select' => [
                                    'xpath' => './ram:CreditorReferenceID',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BT-91',
                                'select' => [
                                    'xpath' => './ram:SpecifiedTradeSettlementPaymentMeans/ram:PayerPartyDebtorFinancialAccount/ram:IBANID',
                                ],
                            ],
                        ],
                    ],
                    [
                        'op' => 'if',
                        'test' => [
                            'var' => 'bg-contents',
                        ],
                        'body' => [
                            [
                                'op' => 'element',
                                'name' => 'DIRECT_DEBIT',
                                'body' => [
                                    [
                                        'op' => 'attribute',
                                        'name' => 'xr:id',
                                        'value' => [
                                            'literal' => 'BG-19',
                                        ],
                                    ],
                                    [
                                        'op' => 'attribute',
                                        'name' => 'xr:src',
                                        'value' => [
                                            'srcpath' => [
                                                'xpath' => '.',
                                            ],
                                        ],
                                    ],
                                    [
                                        'op' => 'sequence',
                                        'var' => 'bg-contents',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        'BT-89' => [
            [
                'match' => '/rsm:CrossIndustryInvoice/rsm:SupplyChainTradeTransaction/ram:ApplicableHeaderTradeSettlement/ram:SpecifiedTradePaymentTerms/ram:DirectDebitMandateID',
                'priority' => 0.5,
                'body' => [
                    [
                        'op' => 'element',
                        'name' => 'Mandate_reference_identifier',
                        'body' => [
                            [
                                'op' => 'attribute',
                                'name' => 'xr:id',
                                'value' => [
                                    'literal' => 'BT-89',
                                ],
                            ],
                            [
                                'op' => 'attribute',
                                'name' => 'xr:src',
                                'value' => [
                                    'srcpath' => [
                                        'xpath' => '.',
                                    ],
                                ],
                            ],
                            [
                                'op' => 'call',
                                'name' => 'identifier',
                                'params' => [],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        'BT-90' => [
            [
                'match' => '/rsm:CrossIndustryInvoice/rsm:SupplyChainTradeTransaction/ram:ApplicableHeaderTradeSettlement/ram:CreditorReferenceID',
                'priority' => 0.5,
                'body' => [
                    [
                        'op' => 'element',
                        'name' => 'Bank_assigned_creditor_identifier',
                        'body' => [
                            [
                                'op' => 'attribute',
                                'name' => 'xr:id',
                                'value' => [
                                    'literal' => 'BT-90',
                                ],
                            ],
                            [
                                'op' => 'attribute',
                                'name' => 'xr:src',
                                'value' => [
                                    'srcpath' => [
                                        'xpath' => '.',
                                    ],
                                ],
                            ],
                            [
                                'op' => 'call',
                                'name' => 'identifier',
                                'params' => [],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        'BT-91' => [
            [
                'match' => '/rsm:CrossIndustryInvoice/rsm:SupplyChainTradeTransaction/ram:ApplicableHeaderTradeSettlement/ram:SpecifiedTradeSettlementPaymentMeans/ram:PayerPartyDebtorFinancialAccount/ram:IBANID',
                'priority' => 0.5,
                'body' => [
                    [
                        'op' => 'element',
                        'name' => 'Debited_account_identifier',
                        'body' => [
                            [
                                'op' => 'attribute',
                                'name' => 'xr:id',
                                'value' => [
                                    'literal' => 'BT-91',
                                ],
                            ],
                            [
                                'op' => 'attribute',
                                'name' => 'xr:src',
                                'value' => [
                                    'srcpath' => [
                                        'xpath' => '.',
                                    ],
                                ],
                            ],
                            [
                                'op' => 'call',
                                'name' => 'identifier',
                                'params' => [],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        'BG-20' => [
            [
                'match' => '/rsm:CrossIndustryInvoice/rsm:SupplyChainTradeTransaction/ram:ApplicableHeaderTradeSettlement/ram:SpecifiedTradeAllowanceCharge[ram:ChargeIndicator/udt:Indicator=\'false\']',
                'priority' => 0.5,
                'body' => [
                    [
                        'op' => 'variable',
                        'name' => 'bg-contents',
                        'body' => [
                            [
                                'op' => 'apply',
                                'mode' => 'BT-92',
                                'select' => [
                                    'xpath' => './ram:ActualAmount',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BT-93',
                                'select' => [
                                    'xpath' => './ram:BasisAmount',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BT-94',
                                'select' => [
                                    'xpath' => './ram:CalculationPercent',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BT-95',
                                'select' => [
                                    'xpath' => './ram:CategoryTradeTax/ram:CategoryCode',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BT-96',
                                'select' => [
                                    'xpath' => './ram:CategoryTradeTax/ram:RateApplicablePercent',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BT-97',
                                'select' => [
                                    'xpath' => './ram:Reason',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BT-98',
                                'select' => [
                                    'xpath' => './ram:ReasonCode',
                                ],
                            ],
                        ],
                    ],
                    [
                        'op' => 'if',
                        'test' => [
                            'var' => 'bg-contents',
                        ],
                        'body' => [
                            [
                                'op' => 'element',
                                'name' => 'DOCUMENT_LEVEL_ALLOWANCES',
                                'body' => [
                                    [
                                        'op' => 'attribute',
                                        'name' => 'xr:id',
                                        'value' => [
                                            'literal' => 'BG-20',
                                        ],
                                    ],
                                    [
                                        'op' => 'attribute',
                                        'name' => 'xr:src',
                                        'value' => [
                                            'srcpath' => [
                                                'xpath' => '.',
                                            ],
                                        ],
                                    ],
                                    [
                                        'op' => 'sequence',
                                        'var' => 'bg-contents',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        'BT-92' => [
            [
                'match' => '/rsm:CrossIndustryInvoice/rsm:SupplyChainTradeTransaction/ram:ApplicableHeaderTradeSettlement/ram:SpecifiedTradeAllowanceCharge/ram:ActualAmount',
                'priority' => 0.5,
                'body' => [
                    [
                        'op' => 'element',
                        'name' => 'Document_level_allowance_amount',
                        'body' => [
                            [
                                'op' => 'attribute',
                                'name' => 'xr:id',
                                'value' => [
                                    'literal' => 'BT-92',
                                ],
                            ],
                            [
                                'op' => 'attribute',
                                'name' => 'xr:src',
                                'value' => [
                                    'srcpath' => [
                                        'xpath' => '.',
                                    ],
                                ],
                            ],
                            [
                                'op' => 'call',
                                'name' => 'amount',
                                'params' => [],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        'BT-93' => [
            [
                'match' => '/rsm:CrossIndustryInvoice/rsm:SupplyChainTradeTransaction/ram:ApplicableHeaderTradeSettlement/ram:SpecifiedTradeAllowanceCharge/ram:BasisAmount',
                'priority' => 0.5,
                'body' => [
                    [
                        'op' => 'element',
                        'name' => 'Document_level_allowance_base_amount',
                        'body' => [
                            [
                                'op' => 'attribute',
                                'name' => 'xr:id',
                                'value' => [
                                    'literal' => 'BT-93',
                                ],
                            ],
                            [
                                'op' => 'attribute',
                                'name' => 'xr:src',
                                'value' => [
                                    'srcpath' => [
                                        'xpath' => '.',
                                    ],
                                ],
                            ],
                            [
                                'op' => 'call',
                                'name' => 'amount',
                                'params' => [],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        'BT-94' => [
            [
                'match' => '/rsm:CrossIndustryInvoice/rsm:SupplyChainTradeTransaction/ram:ApplicableHeaderTradeSettlement/ram:SpecifiedTradeAllowanceCharge/ram:CalculationPercent',
                'priority' => 0.5,
                'body' => [
                    [
                        'op' => 'element',
                        'name' => 'Document_level_allowance_percentage',
                        'body' => [
                            [
                                'op' => 'attribute',
                                'name' => 'xr:id',
                                'value' => [
                                    'literal' => 'BT-94',
                                ],
                            ],
                            [
                                'op' => 'attribute',
                                'name' => 'xr:src',
                                'value' => [
                                    'srcpath' => [
                                        'xpath' => '.',
                                    ],
                                ],
                            ],
                            [
                                'op' => 'call',
                                'name' => 'percentage',
                                'params' => [],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        'BT-95' => [
            [
                'match' => '/rsm:CrossIndustryInvoice/rsm:SupplyChainTradeTransaction/ram:ApplicableHeaderTradeSettlement/ram:SpecifiedTradeAllowanceCharge/ram:CategoryTradeTax/ram:CategoryCode',
                'priority' => 0.5,
                'body' => [
                    [
                        'op' => 'element',
                        'name' => 'Document_level_allowance_VAT_category_code',
                        'body' => [
                            [
                                'op' => 'attribute',
                                'name' => 'xr:id',
                                'value' => [
                                    'literal' => 'BT-95',
                                ],
                            ],
                            [
                                'op' => 'attribute',
                                'name' => 'xr:src',
                                'value' => [
                                    'srcpath' => [
                                        'xpath' => '.',
                                    ],
                                ],
                            ],
                            [
                                'op' => 'call',
                                'name' => 'code',
                                'params' => [],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        'BT-96' => [
            [
                'match' => '/rsm:CrossIndustryInvoice/rsm:SupplyChainTradeTransaction/ram:ApplicableHeaderTradeSettlement/ram:SpecifiedTradeAllowanceCharge/ram:CategoryTradeTax/ram:RateApplicablePercent',
                'priority' => 0.5,
                'body' => [
                    [
                        'op' => 'element',
                        'name' => 'Document_level_allowance_VAT_rate',
                        'body' => [
                            [
                                'op' => 'attribute',
                                'name' => 'xr:id',
                                'value' => [
                                    'literal' => 'BT-96',
                                ],
                            ],
                            [
                                'op' => 'attribute',
                                'name' => 'xr:src',
                                'value' => [
                                    'srcpath' => [
                                        'xpath' => '.',
                                    ],
                                ],
                            ],
                            [
                                'op' => 'call',
                                'name' => 'percentage',
                                'params' => [],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        'BT-97' => [
            [
                'match' => '/rsm:CrossIndustryInvoice/rsm:SupplyChainTradeTransaction/ram:ApplicableHeaderTradeSettlement/ram:SpecifiedTradeAllowanceCharge/ram:Reason',
                'priority' => 0.5,
                'body' => [
                    [
                        'op' => 'element',
                        'name' => 'Document_level_allowance_reason',
                        'body' => [
                            [
                                'op' => 'attribute',
                                'name' => 'xr:id',
                                'value' => [
                                    'literal' => 'BT-97',
                                ],
                            ],
                            [
                                'op' => 'attribute',
                                'name' => 'xr:src',
                                'value' => [
                                    'srcpath' => [
                                        'xpath' => '.',
                                    ],
                                ],
                            ],
                            [
                                'op' => 'call',
                                'name' => 'text',
                                'params' => [],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        'BT-98' => [
            [
                'match' => '/rsm:CrossIndustryInvoice/rsm:SupplyChainTradeTransaction/ram:ApplicableHeaderTradeSettlement/ram:SpecifiedTradeAllowanceCharge/ram:ReasonCode',
                'priority' => 0.5,
                'body' => [
                    [
                        'op' => 'element',
                        'name' => 'Document_level_allowance_reason_code',
                        'body' => [
                            [
                                'op' => 'attribute',
                                'name' => 'xr:id',
                                'value' => [
                                    'literal' => 'BT-98',
                                ],
                            ],
                            [
                                'op' => 'attribute',
                                'name' => 'xr:src',
                                'value' => [
                                    'srcpath' => [
                                        'xpath' => '.',
                                    ],
                                ],
                            ],
                            [
                                'op' => 'call',
                                'name' => 'code',
                                'params' => [],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        'BG-21' => [
            [
                'match' => '/rsm:CrossIndustryInvoice/rsm:SupplyChainTradeTransaction/ram:ApplicableHeaderTradeSettlement/ram:SpecifiedTradeAllowanceCharge[ram:ChargeIndicator/udt:Indicator=\'true\']',
                'priority' => 0.5,
                'body' => [
                    [
                        'op' => 'variable',
                        'name' => 'bg-contents',
                        'body' => [
                            [
                                'op' => 'apply',
                                'mode' => 'BT-99',
                                'select' => [
                                    'xpath' => './ram:ActualAmount',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BT-100',
                                'select' => [
                                    'xpath' => './ram:BasisAmount',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BT-101',
                                'select' => [
                                    'xpath' => './ram:CalculationPercent',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BT-102',
                                'select' => [
                                    'xpath' => './ram:CategoryTradeTax/ram:CategoryCode',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BT-103',
                                'select' => [
                                    'xpath' => './ram:CategoryTradeTax/ram:RateApplicablePercent',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BT-104',
                                'select' => [
                                    'xpath' => './ram:Reason',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BT-105',
                                'select' => [
                                    'xpath' => './ram:ReasonCode',
                                ],
                            ],
                        ],
                    ],
                    [
                        'op' => 'if',
                        'test' => [
                            'var' => 'bg-contents',
                        ],
                        'body' => [
                            [
                                'op' => 'element',
                                'name' => 'DOCUMENT_LEVEL_CHARGES',
                                'body' => [
                                    [
                                        'op' => 'attribute',
                                        'name' => 'xr:id',
                                        'value' => [
                                            'literal' => 'BG-21',
                                        ],
                                    ],
                                    [
                                        'op' => 'attribute',
                                        'name' => 'xr:src',
                                        'value' => [
                                            'srcpath' => [
                                                'xpath' => '.',
                                            ],
                                        ],
                                    ],
                                    [
                                        'op' => 'sequence',
                                        'var' => 'bg-contents',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        'BT-99' => [
            [
                'match' => '/rsm:CrossIndustryInvoice/rsm:SupplyChainTradeTransaction/ram:ApplicableHeaderTradeSettlement/ram:SpecifiedTradeAllowanceCharge/ram:ActualAmount',
                'priority' => 0.5,
                'body' => [
                    [
                        'op' => 'element',
                        'name' => 'Document_level_charge_amount',
                        'body' => [
                            [
                                'op' => 'attribute',
                                'name' => 'xr:id',
                                'value' => [
                                    'literal' => 'BT-99',
                                ],
                            ],
                            [
                                'op' => 'attribute',
                                'name' => 'xr:src',
                                'value' => [
                                    'srcpath' => [
                                        'xpath' => '.',
                                    ],
                                ],
                            ],
                            [
                                'op' => 'call',
                                'name' => 'amount',
                                'params' => [],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        'BT-100' => [
            [
                'match' => '/rsm:CrossIndustryInvoice/rsm:SupplyChainTradeTransaction/ram:ApplicableHeaderTradeSettlement/ram:SpecifiedTradeAllowanceCharge/ram:BasisAmount',
                'priority' => 0.5,
                'body' => [
                    [
                        'op' => 'element',
                        'name' => 'Document_level_charge_base_amount',
                        'body' => [
                            [
                                'op' => 'attribute',
                                'name' => 'xr:id',
                                'value' => [
                                    'literal' => 'BT-100',
                                ],
                            ],
                            [
                                'op' => 'attribute',
                                'name' => 'xr:src',
                                'value' => [
                                    'srcpath' => [
                                        'xpath' => '.',
                                    ],
                                ],
                            ],
                            [
                                'op' => 'call',
                                'name' => 'amount',
                                'params' => [],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        'BT-101' => [
            [
                'match' => '/rsm:CrossIndustryInvoice/rsm:SupplyChainTradeTransaction/ram:ApplicableHeaderTradeSettlement/ram:SpecifiedTradeAllowanceCharge/ram:CalculationPercent',
                'priority' => 0.5,
                'body' => [
                    [
                        'op' => 'element',
                        'name' => 'Document_level_charge_percentage',
                        'body' => [
                            [
                                'op' => 'attribute',
                                'name' => 'xr:id',
                                'value' => [
                                    'literal' => 'BT-101',
                                ],
                            ],
                            [
                                'op' => 'attribute',
                                'name' => 'xr:src',
                                'value' => [
                                    'srcpath' => [
                                        'xpath' => '.',
                                    ],
                                ],
                            ],
                            [
                                'op' => 'call',
                                'name' => 'percentage',
                                'params' => [],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        'BT-102' => [
            [
                'match' => '/rsm:CrossIndustryInvoice/rsm:SupplyChainTradeTransaction/ram:ApplicableHeaderTradeSettlement/ram:SpecifiedTradeAllowanceCharge/ram:CategoryTradeTax/ram:CategoryCode',
                'priority' => 0.5,
                'body' => [
                    [
                        'op' => 'element',
                        'name' => 'Document_level_charge_VAT_category_code',
                        'body' => [
                            [
                                'op' => 'attribute',
                                'name' => 'xr:id',
                                'value' => [
                                    'literal' => 'BT-102',
                                ],
                            ],
                            [
                                'op' => 'attribute',
                                'name' => 'xr:src',
                                'value' => [
                                    'srcpath' => [
                                        'xpath' => '.',
                                    ],
                                ],
                            ],
                            [
                                'op' => 'call',
                                'name' => 'code',
                                'params' => [],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        'BT-103' => [
            [
                'match' => '/rsm:CrossIndustryInvoice/rsm:SupplyChainTradeTransaction/ram:ApplicableHeaderTradeSettlement/ram:SpecifiedTradeAllowanceCharge/ram:CategoryTradeTax/ram:RateApplicablePercent',
                'priority' => 0.5,
                'body' => [
                    [
                        'op' => 'element',
                        'name' => 'Document_level_charge_VAT_rate',
                        'body' => [
                            [
                                'op' => 'attribute',
                                'name' => 'xr:id',
                                'value' => [
                                    'literal' => 'BT-103',
                                ],
                            ],
                            [
                                'op' => 'attribute',
                                'name' => 'xr:src',
                                'value' => [
                                    'srcpath' => [
                                        'xpath' => '.',
                                    ],
                                ],
                            ],
                            [
                                'op' => 'call',
                                'name' => 'percentage',
                                'params' => [],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        'BT-104' => [
            [
                'match' => '/rsm:CrossIndustryInvoice/rsm:SupplyChainTradeTransaction/ram:ApplicableHeaderTradeSettlement/ram:SpecifiedTradeAllowanceCharge/ram:Reason',
                'priority' => 0.5,
                'body' => [
                    [
                        'op' => 'element',
                        'name' => 'Document_level_charge_reason',
                        'body' => [
                            [
                                'op' => 'attribute',
                                'name' => 'xr:id',
                                'value' => [
                                    'literal' => 'BT-104',
                                ],
                            ],
                            [
                                'op' => 'attribute',
                                'name' => 'xr:src',
                                'value' => [
                                    'srcpath' => [
                                        'xpath' => '.',
                                    ],
                                ],
                            ],
                            [
                                'op' => 'call',
                                'name' => 'text',
                                'params' => [],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        'BT-105' => [
            [
                'match' => '/rsm:CrossIndustryInvoice/rsm:SupplyChainTradeTransaction/ram:ApplicableHeaderTradeSettlement/ram:SpecifiedTradeAllowanceCharge/ram:ReasonCode',
                'priority' => 0.5,
                'body' => [
                    [
                        'op' => 'element',
                        'name' => 'Document_level_charge_reason_code',
                        'body' => [
                            [
                                'op' => 'attribute',
                                'name' => 'xr:id',
                                'value' => [
                                    'literal' => 'BT-105',
                                ],
                            ],
                            [
                                'op' => 'attribute',
                                'name' => 'xr:src',
                                'value' => [
                                    'srcpath' => [
                                        'xpath' => '.',
                                    ],
                                ],
                            ],
                            [
                                'op' => 'call',
                                'name' => 'code',
                                'params' => [],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        'BG-22' => [
            [
                'match' => '/rsm:CrossIndustryInvoice/rsm:SupplyChainTradeTransaction/ram:ApplicableHeaderTradeSettlement/ram:SpecifiedTradeSettlementHeaderMonetarySummation',
                'priority' => 0.5,
                'body' => [
                    [
                        'op' => 'variable',
                        'name' => 'bg-contents',
                        'body' => [
                            [
                                'op' => 'apply',
                                'mode' => 'BT-106',
                                'select' => [
                                    'xpath' => './ram:LineTotalAmount',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BT-107',
                                'select' => [
                                    'xpath' => './ram:AllowanceTotalAmount',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BT-108',
                                'select' => [
                                    'xpath' => './ram:ChargeTotalAmount',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BT-109',
                                'select' => [
                                    'xpath' => './ram:TaxBasisTotalAmount',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BT-110',
                                'select' => [
                                    'xpath' => './ram:TaxTotalAmount[@currencyID = parent::ram:SpecifiedTradeSettlementHeaderMonetarySummation/preceding-sibling::ram:InvoiceCurrencyCode]',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BT-111',
                                'select' => [
                                    'xpath' => './ram:TaxTotalAmount[@currencyID = parent::ram:SpecifiedTradeSettlementHeaderMonetarySummation/preceding-sibling::ram:TaxCurrencyCode]',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BT-112',
                                'select' => [
                                    'xpath' => './ram:GrandTotalAmount',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BT-113',
                                'select' => [
                                    'xpath' => './ram:TotalPrepaidAmount',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BT-114',
                                'select' => [
                                    'xpath' => './ram:RoundingAmount',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BT-115',
                                'select' => [
                                    'xpath' => './ram:DuePayableAmount',
                                ],
                            ],
                        ],
                    ],
                    [
                        'op' => 'if',
                        'test' => [
                            'var' => 'bg-contents',
                        ],
                        'body' => [
                            [
                                'op' => 'element',
                                'name' => 'DOCUMENT_TOTALS',
                                'body' => [
                                    [
                                        'op' => 'attribute',
                                        'name' => 'xr:id',
                                        'value' => [
                                            'literal' => 'BG-22',
                                        ],
                                    ],
                                    [
                                        'op' => 'attribute',
                                        'name' => 'xr:src',
                                        'value' => [
                                            'srcpath' => [
                                                'xpath' => '.',
                                            ],
                                        ],
                                    ],
                                    [
                                        'op' => 'sequence',
                                        'var' => 'bg-contents',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        'BT-106' => [
            [
                'match' => '/rsm:CrossIndustryInvoice/rsm:SupplyChainTradeTransaction/ram:ApplicableHeaderTradeSettlement/ram:SpecifiedTradeSettlementHeaderMonetarySummation/ram:LineTotalAmount',
                'priority' => 0.5,
                'body' => [
                    [
                        'op' => 'element',
                        'name' => 'Sum_of_Invoice_line_net_amount',
                        'body' => [
                            [
                                'op' => 'attribute',
                                'name' => 'xr:id',
                                'value' => [
                                    'literal' => 'BT-106',
                                ],
                            ],
                            [
                                'op' => 'attribute',
                                'name' => 'xr:src',
                                'value' => [
                                    'srcpath' => [
                                        'xpath' => '.',
                                    ],
                                ],
                            ],
                            [
                                'op' => 'call',
                                'name' => 'amount',
                                'params' => [],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        'BT-107' => [
            [
                'match' => '/rsm:CrossIndustryInvoice/rsm:SupplyChainTradeTransaction/ram:ApplicableHeaderTradeSettlement/ram:SpecifiedTradeSettlementHeaderMonetarySummation/ram:AllowanceTotalAmount',
                'priority' => 0.5,
                'body' => [
                    [
                        'op' => 'element',
                        'name' => 'Sum_of_allowances_on_document_level',
                        'body' => [
                            [
                                'op' => 'attribute',
                                'name' => 'xr:id',
                                'value' => [
                                    'literal' => 'BT-107',
                                ],
                            ],
                            [
                                'op' => 'attribute',
                                'name' => 'xr:src',
                                'value' => [
                                    'srcpath' => [
                                        'xpath' => '.',
                                    ],
                                ],
                            ],
                            [
                                'op' => 'call',
                                'name' => 'amount',
                                'params' => [],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        'BT-108' => [
            [
                'match' => '/rsm:CrossIndustryInvoice/rsm:SupplyChainTradeTransaction/ram:ApplicableHeaderTradeSettlement/ram:SpecifiedTradeSettlementHeaderMonetarySummation/ram:ChargeTotalAmount',
                'priority' => 0.5,
                'body' => [
                    [
                        'op' => 'element',
                        'name' => 'Sum_of_charges_on_document_level',
                        'body' => [
                            [
                                'op' => 'attribute',
                                'name' => 'xr:id',
                                'value' => [
                                    'literal' => 'BT-108',
                                ],
                            ],
                            [
                                'op' => 'attribute',
                                'name' => 'xr:src',
                                'value' => [
                                    'srcpath' => [
                                        'xpath' => '.',
                                    ],
                                ],
                            ],
                            [
                                'op' => 'call',
                                'name' => 'amount',
                                'params' => [],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        'BT-109' => [
            [
                'match' => '/rsm:CrossIndustryInvoice/rsm:SupplyChainTradeTransaction/ram:ApplicableHeaderTradeSettlement/ram:SpecifiedTradeSettlementHeaderMonetarySummation/ram:TaxBasisTotalAmount',
                'priority' => 0.5,
                'body' => [
                    [
                        'op' => 'element',
                        'name' => 'Invoice_total_amount_without_VAT',
                        'body' => [
                            [
                                'op' => 'attribute',
                                'name' => 'xr:id',
                                'value' => [
                                    'literal' => 'BT-109',
                                ],
                            ],
                            [
                                'op' => 'attribute',
                                'name' => 'xr:src',
                                'value' => [
                                    'srcpath' => [
                                        'xpath' => '.',
                                    ],
                                ],
                            ],
                            [
                                'op' => 'call',
                                'name' => 'amount',
                                'params' => [],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        'BT-110' => [
            [
                'match' => '/rsm:CrossIndustryInvoice/rsm:SupplyChainTradeTransaction/ram:ApplicableHeaderTradeSettlement/ram:SpecifiedTradeSettlementHeaderMonetarySummation/ram:TaxTotalAmount[@currencyID = parent::ram:SpecifiedTradeSettlementHeaderMonetarySummation/preceding-sibling::ram:InvoiceCurrencyCode]',
                'priority' => 0.5,
                'body' => [
                    [
                        'op' => 'element',
                        'name' => 'Invoice_total_VAT_amount',
                        'body' => [
                            [
                                'op' => 'attribute',
                                'name' => 'xr:id',
                                'value' => [
                                    'literal' => 'BT-110',
                                ],
                            ],
                            [
                                'op' => 'attribute',
                                'name' => 'xr:src',
                                'value' => [
                                    'srcpath' => [
                                        'xpath' => '.',
                                    ],
                                ],
                            ],
                            [
                                'op' => 'call',
                                'name' => 'amount',
                                'params' => [],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        'BT-111' => [
            [
                'match' => '/rsm:CrossIndustryInvoice/rsm:SupplyChainTradeTransaction/ram:ApplicableHeaderTradeSettlement/ram:SpecifiedTradeSettlementHeaderMonetarySummation/ram:TaxTotalAmount[@currencyID = parent::ram:SpecifiedTradeSettlementHeaderMonetarySummation/preceding-sibling::ram:TaxCurrencyCode]',
                'priority' => 0.5,
                'body' => [
                    [
                        'op' => 'element',
                        'name' => 'Invoice_total_VAT_amount_in_accounting_currency',
                        'body' => [
                            [
                                'op' => 'attribute',
                                'name' => 'xr:id',
                                'value' => [
                                    'literal' => 'BT-111',
                                ],
                            ],
                            [
                                'op' => 'attribute',
                                'name' => 'xr:src',
                                'value' => [
                                    'srcpath' => [
                                        'xpath' => '.',
                                    ],
                                ],
                            ],
                            [
                                'op' => 'call',
                                'name' => 'amount',
                                'params' => [],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        'BT-112' => [
            [
                'match' => '/rsm:CrossIndustryInvoice/rsm:SupplyChainTradeTransaction/ram:ApplicableHeaderTradeSettlement/ram:SpecifiedTradeSettlementHeaderMonetarySummation/ram:GrandTotalAmount',
                'priority' => 0.5,
                'body' => [
                    [
                        'op' => 'element',
                        'name' => 'Invoice_total_amount_with_VAT',
                        'body' => [
                            [
                                'op' => 'attribute',
                                'name' => 'xr:id',
                                'value' => [
                                    'literal' => 'BT-112',
                                ],
                            ],
                            [
                                'op' => 'attribute',
                                'name' => 'xr:src',
                                'value' => [
                                    'srcpath' => [
                                        'xpath' => '.',
                                    ],
                                ],
                            ],
                            [
                                'op' => 'call',
                                'name' => 'amount',
                                'params' => [],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        'BT-113' => [
            [
                'match' => '/rsm:CrossIndustryInvoice/rsm:SupplyChainTradeTransaction/ram:ApplicableHeaderTradeSettlement/ram:SpecifiedTradeSettlementHeaderMonetarySummation/ram:TotalPrepaidAmount',
                'priority' => 0.5,
                'body' => [
                    [
                        'op' => 'element',
                        'name' => 'Paid_amount',
                        'body' => [
                            [
                                'op' => 'attribute',
                                'name' => 'xr:id',
                                'value' => [
                                    'literal' => 'BT-113',
                                ],
                            ],
                            [
                                'op' => 'attribute',
                                'name' => 'xr:src',
                                'value' => [
                                    'srcpath' => [
                                        'xpath' => '.',
                                    ],
                                ],
                            ],
                            [
                                'op' => 'call',
                                'name' => 'amount',
                                'params' => [],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        'BT-114' => [
            [
                'match' => '/rsm:CrossIndustryInvoice/rsm:SupplyChainTradeTransaction/ram:ApplicableHeaderTradeSettlement/ram:SpecifiedTradeSettlementHeaderMonetarySummation/ram:RoundingAmount',
                'priority' => 0.5,
                'body' => [
                    [
                        'op' => 'element',
                        'name' => 'Rounding_amount',
                        'body' => [
                            [
                                'op' => 'attribute',
                                'name' => 'xr:id',
                                'value' => [
                                    'literal' => 'BT-114',
                                ],
                            ],
                            [
                                'op' => 'attribute',
                                'name' => 'xr:src',
                                'value' => [
                                    'srcpath' => [
                                        'xpath' => '.',
                                    ],
                                ],
                            ],
                            [
                                'op' => 'call',
                                'name' => 'amount',
                                'params' => [],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        'BT-115' => [
            [
                'match' => '/rsm:CrossIndustryInvoice/rsm:SupplyChainTradeTransaction/ram:ApplicableHeaderTradeSettlement/ram:SpecifiedTradeSettlementHeaderMonetarySummation/ram:DuePayableAmount',
                'priority' => 0.5,
                'body' => [
                    [
                        'op' => 'element',
                        'name' => 'Amount_due_for_payment',
                        'body' => [
                            [
                                'op' => 'attribute',
                                'name' => 'xr:id',
                                'value' => [
                                    'literal' => 'BT-115',
                                ],
                            ],
                            [
                                'op' => 'attribute',
                                'name' => 'xr:src',
                                'value' => [
                                    'srcpath' => [
                                        'xpath' => '.',
                                    ],
                                ],
                            ],
                            [
                                'op' => 'call',
                                'name' => 'amount',
                                'params' => [],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        'BG-23' => [
            [
                'match' => '/rsm:CrossIndustryInvoice/rsm:SupplyChainTradeTransaction/ram:ApplicableHeaderTradeSettlement/ram:ApplicableTradeTax',
                'priority' => 0.5,
                'body' => [
                    [
                        'op' => 'variable',
                        'name' => 'bg-contents',
                        'body' => [
                            [
                                'op' => 'apply',
                                'mode' => 'BT-116',
                                'select' => [
                                    'xpath' => './ram:BasisAmount',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BT-117',
                                'select' => [
                                    'xpath' => './ram:CalculatedAmount',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BT-118',
                                'select' => [
                                    'xpath' => './ram:CategoryCode',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BT-119',
                                'select' => [
                                    'xpath' => './ram:RateApplicablePercent',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BT-120',
                                'select' => [
                                    'xpath' => './ram:ExemptionReason',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BT-121',
                                'select' => [
                                    'xpath' => './ram:ExemptionReasonCode',
                                ],
                            ],
                        ],
                    ],
                    [
                        'op' => 'if',
                        'test' => [
                            'var' => 'bg-contents',
                        ],
                        'body' => [
                            [
                                'op' => 'element',
                                'name' => 'VAT_BREAKDOWN',
                                'body' => [
                                    [
                                        'op' => 'attribute',
                                        'name' => 'xr:id',
                                        'value' => [
                                            'literal' => 'BG-23',
                                        ],
                                    ],
                                    [
                                        'op' => 'attribute',
                                        'name' => 'xr:src',
                                        'value' => [
                                            'srcpath' => [
                                                'xpath' => '.',
                                            ],
                                        ],
                                    ],
                                    [
                                        'op' => 'sequence',
                                        'var' => 'bg-contents',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        'BT-116' => [
            [
                'match' => '/rsm:CrossIndustryInvoice/rsm:SupplyChainTradeTransaction/ram:ApplicableHeaderTradeSettlement/ram:ApplicableTradeTax/ram:BasisAmount',
                'priority' => 0.5,
                'body' => [
                    [
                        'op' => 'element',
                        'name' => 'VAT_category_taxable_amount',
                        'body' => [
                            [
                                'op' => 'attribute',
                                'name' => 'xr:id',
                                'value' => [
                                    'literal' => 'BT-116',
                                ],
                            ],
                            [
                                'op' => 'attribute',
                                'name' => 'xr:src',
                                'value' => [
                                    'srcpath' => [
                                        'xpath' => '.',
                                    ],
                                ],
                            ],
                            [
                                'op' => 'call',
                                'name' => 'amount',
                                'params' => [],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        'BT-117' => [
            [
                'match' => '/rsm:CrossIndustryInvoice/rsm:SupplyChainTradeTransaction/ram:ApplicableHeaderTradeSettlement/ram:ApplicableTradeTax/ram:CalculatedAmount',
                'priority' => 0.5,
                'body' => [
                    [
                        'op' => 'element',
                        'name' => 'VAT_category_tax_amount',
                        'body' => [
                            [
                                'op' => 'attribute',
                                'name' => 'xr:id',
                                'value' => [
                                    'literal' => 'BT-117',
                                ],
                            ],
                            [
                                'op' => 'attribute',
                                'name' => 'xr:src',
                                'value' => [
                                    'srcpath' => [
                                        'xpath' => '.',
                                    ],
                                ],
                            ],
                            [
                                'op' => 'call',
                                'name' => 'amount',
                                'params' => [],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        'BT-118' => [
            [
                'match' => '/rsm:CrossIndustryInvoice/rsm:SupplyChainTradeTransaction/ram:ApplicableHeaderTradeSettlement/ram:ApplicableTradeTax/ram:CategoryCode',
                'priority' => 0.5,
                'body' => [
                    [
                        'op' => 'element',
                        'name' => 'VAT_category_code',
                        'body' => [
                            [
                                'op' => 'attribute',
                                'name' => 'xr:id',
                                'value' => [
                                    'literal' => 'BT-118',
                                ],
                            ],
                            [
                                'op' => 'attribute',
                                'name' => 'xr:src',
                                'value' => [
                                    'srcpath' => [
                                        'xpath' => '.',
                                    ],
                                ],
                            ],
                            [
                                'op' => 'call',
                                'name' => 'code',
                                'params' => [],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        'BT-119' => [
            [
                'match' => '/rsm:CrossIndustryInvoice/rsm:SupplyChainTradeTransaction/ram:ApplicableHeaderTradeSettlement/ram:ApplicableTradeTax/ram:RateApplicablePercent',
                'priority' => 0.5,
                'body' => [
                    [
                        'op' => 'element',
                        'name' => 'VAT_category_rate',
                        'body' => [
                            [
                                'op' => 'attribute',
                                'name' => 'xr:id',
                                'value' => [
                                    'literal' => 'BT-119',
                                ],
                            ],
                            [
                                'op' => 'attribute',
                                'name' => 'xr:src',
                                'value' => [
                                    'srcpath' => [
                                        'xpath' => '.',
                                    ],
                                ],
                            ],
                            [
                                'op' => 'call',
                                'name' => 'percentage',
                                'params' => [],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        'BT-120' => [
            [
                'match' => '/rsm:CrossIndustryInvoice/rsm:SupplyChainTradeTransaction/ram:ApplicableHeaderTradeSettlement/ram:ApplicableTradeTax/ram:ExemptionReason',
                'priority' => 0.5,
                'body' => [
                    [
                        'op' => 'element',
                        'name' => 'VAT_exemption_reason_text',
                        'body' => [
                            [
                                'op' => 'attribute',
                                'name' => 'xr:id',
                                'value' => [
                                    'literal' => 'BT-120',
                                ],
                            ],
                            [
                                'op' => 'attribute',
                                'name' => 'xr:src',
                                'value' => [
                                    'srcpath' => [
                                        'xpath' => '.',
                                    ],
                                ],
                            ],
                            [
                                'op' => 'call',
                                'name' => 'text',
                                'params' => [],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        'BT-121' => [
            [
                'match' => '/rsm:CrossIndustryInvoice/rsm:SupplyChainTradeTransaction/ram:ApplicableHeaderTradeSettlement/ram:ApplicableTradeTax/ram:ExemptionReasonCode',
                'priority' => 0.5,
                'body' => [
                    [
                        'op' => 'element',
                        'name' => 'VAT_exemption_reason_code',
                        'body' => [
                            [
                                'op' => 'attribute',
                                'name' => 'xr:id',
                                'value' => [
                                    'literal' => 'BT-121',
                                ],
                            ],
                            [
                                'op' => 'attribute',
                                'name' => 'xr:src',
                                'value' => [
                                    'srcpath' => [
                                        'xpath' => '.',
                                    ],
                                ],
                            ],
                            [
                                'op' => 'call',
                                'name' => 'code',
                                'params' => [],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        'BG-24' => [
            [
                'match' => '/rsm:CrossIndustryInvoice/rsm:SupplyChainTradeTransaction/ram:ApplicableHeaderTradeAgreement/ram:AdditionalReferencedDocument',
                'priority' => 0.5,
                'body' => [
                    [
                        'op' => 'variable',
                        'name' => 'bg-contents',
                        'body' => [
                            [
                                'op' => 'apply',
                                'mode' => 'BT-122',
                                'select' => [
                                    'xpath' => './ram:IssuerAssignedID[following-sibling::ram:TypeCode=\'916\']',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BT-123',
                                'select' => [
                                    'xpath' => './ram:Name',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BT-124',
                                'select' => [
                                    'xpath' => './ram:URIID',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BT-125',
                                'select' => [
                                    'xpath' => './ram:AttachmentBinaryObject',
                                ],
                            ],
                        ],
                    ],
                    [
                        'op' => 'if',
                        'test' => [
                            'var' => 'bg-contents',
                        ],
                        'body' => [
                            [
                                'op' => 'element',
                                'name' => 'ADDITIONAL_SUPPORTING_DOCUMENTS',
                                'body' => [
                                    [
                                        'op' => 'attribute',
                                        'name' => 'xr:id',
                                        'value' => [
                                            'literal' => 'BG-24',
                                        ],
                                    ],
                                    [
                                        'op' => 'attribute',
                                        'name' => 'xr:src',
                                        'value' => [
                                            'srcpath' => [
                                                'xpath' => '.',
                                            ],
                                        ],
                                    ],
                                    [
                                        'op' => 'sequence',
                                        'var' => 'bg-contents',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        'BT-122' => [
            [
                'match' => '/rsm:CrossIndustryInvoice/rsm:SupplyChainTradeTransaction/ram:ApplicableHeaderTradeAgreement/ram:AdditionalReferencedDocument/ram:IssuerAssignedID[following-sibling::ram:TypeCode=\'916\']',
                'priority' => 0.5,
                'body' => [
                    [
                        'op' => 'element',
                        'name' => 'Supporting_document_reference',
                        'body' => [
                            [
                                'op' => 'attribute',
                                'name' => 'xr:id',
                                'value' => [
                                    'literal' => 'BT-122',
                                ],
                            ],
                            [
                                'op' => 'attribute',
                                'name' => 'xr:src',
                                'value' => [
                                    'srcpath' => [
                                        'xpath' => '.',
                                    ],
                                ],
                            ],
                            [
                                'op' => 'call',
                                'name' => 'document_reference',
                                'params' => [],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        'BT-123' => [
            [
                'match' => '/rsm:CrossIndustryInvoice/rsm:SupplyChainTradeTransaction/ram:ApplicableHeaderTradeAgreement/ram:AdditionalReferencedDocument/ram:Name',
                'priority' => 0.5,
                'body' => [
                    [
                        'op' => 'element',
                        'name' => 'Supporting_document_description',
                        'body' => [
                            [
                                'op' => 'attribute',
                                'name' => 'xr:id',
                                'value' => [
                                    'literal' => 'BT-123',
                                ],
                            ],
                            [
                                'op' => 'attribute',
                                'name' => 'xr:src',
                                'value' => [
                                    'srcpath' => [
                                        'xpath' => '.',
                                    ],
                                ],
                            ],
                            [
                                'op' => 'call',
                                'name' => 'text',
                                'params' => [],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        'BT-124' => [
            [
                'match' => '/rsm:CrossIndustryInvoice/rsm:SupplyChainTradeTransaction/ram:ApplicableHeaderTradeAgreement/ram:AdditionalReferencedDocument/ram:URIID',
                'priority' => 0.5,
                'body' => [
                    [
                        'op' => 'element',
                        'name' => 'External_document_location',
                        'body' => [
                            [
                                'op' => 'attribute',
                                'name' => 'xr:id',
                                'value' => [
                                    'literal' => 'BT-124',
                                ],
                            ],
                            [
                                'op' => 'attribute',
                                'name' => 'xr:src',
                                'value' => [
                                    'srcpath' => [
                                        'xpath' => '.',
                                    ],
                                ],
                            ],
                            [
                                'op' => 'call',
                                'name' => 'text',
                                'params' => [],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        'BT-125' => [
            [
                'match' => '/rsm:CrossIndustryInvoice/rsm:SupplyChainTradeTransaction/ram:ApplicableHeaderTradeAgreement/ram:AdditionalReferencedDocument/ram:AttachmentBinaryObject',
                'priority' => 0.5,
                'body' => [
                    [
                        'op' => 'element',
                        'name' => 'Attached_document',
                        'body' => [
                            [
                                'op' => 'attribute',
                                'name' => 'xr:id',
                                'value' => [
                                    'literal' => 'BT-125',
                                ],
                            ],
                            [
                                'op' => 'attribute',
                                'name' => 'xr:src',
                                'value' => [
                                    'srcpath' => [
                                        'xpath' => '.',
                                    ],
                                ],
                            ],
                            [
                                'op' => 'call',
                                'name' => 'binary_object',
                                'params' => [],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        'BG-25' => [
            [
                'match' => '/rsm:CrossIndustryInvoice/rsm:SupplyChainTradeTransaction/ram:IncludedSupplyChainTradeLineItem',
                'priority' => 0.5,
                'body' => [
                    [
                        'op' => 'variable',
                        'name' => 'bg-contents',
                        'body' => [
                            [
                                'op' => 'apply',
                                'mode' => 'BT-126',
                                'select' => [
                                    'xpath' => './ram:AssociatedDocumentLineDocument/ram:LineID',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BT-127',
                                'select' => [
                                    'xpath' => './ram:AssociatedDocumentLineDocument/ram:IncludedNote/ram:Content',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BT-128',
                                'select' => [
                                    'xpath' => './ram:SpecifiedLineTradeSettlement/ram:AdditionalReferencedDocument/ram:IssuerAssignedID[following-sibling::ram:TypeCode=\'130\']',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BT-129',
                                'select' => [
                                    'xpath' => './ram:SpecifiedLineTradeDelivery/ram:BilledQuantity',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BT-130',
                                'select' => [
                                    'xpath' => './ram:SpecifiedLineTradeDelivery/ram:BilledQuantity/@unitCode',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BT-131',
                                'select' => [
                                    'xpath' => './ram:SpecifiedLineTradeSettlement/ram:SpecifiedTradeSettlementLineMonetarySummation/ram:LineTotalAmount',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BT-132',
                                'select' => [
                                    'xpath' => './ram:SpecifiedLineTradeAgreement/ram:BuyerOrderReferencedDocument/ram:LineID',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BT-133',
                                'select' => [
                                    'xpath' => './ram:SpecifiedLineTradeSettlement/ram:ReceivableSpecifiedTradeAccountingAccount/ram:ID',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BG-26',
                                'select' => [
                                    'xpath' => './ram:SpecifiedLineTradeSettlement/ram:BillingSpecifiedPeriod',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BG-27',
                                'select' => [
                                    'xpath' => './ram:SpecifiedLineTradeSettlement/ram:SpecifiedTradeAllowanceCharge[ram:ChargeIndicator/udt:Indicator=\'false\']',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BG-28',
                                'select' => [
                                    'xpath' => './ram:SpecifiedLineTradeSettlement/ram:SpecifiedTradeAllowanceCharge[ram:ChargeIndicator/udt:Indicator=\'true\']',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BG-29',
                                'select' => [
                                    'xpath' => './ram:SpecifiedLineTradeAgreement',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BG-30',
                                'select' => [
                                    'xpath' => './ram:SpecifiedLineTradeSettlement/ram:ApplicableTradeTax',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BG-31',
                                'select' => [
                                    'xpath' => './ram:SpecifiedTradeProduct',
                                ],
                            ],
                        ],
                    ],
                    [
                        'op' => 'if',
                        'test' => [
                            'var' => 'bg-contents',
                        ],
                        'body' => [
                            [
                                'op' => 'element',
                                'name' => 'INVOICE_LINE',
                                'body' => [
                                    [
                                        'op' => 'attribute',
                                        'name' => 'xr:id',
                                        'value' => [
                                            'literal' => 'BG-25',
                                        ],
                                    ],
                                    [
                                        'op' => 'attribute',
                                        'name' => 'xr:src',
                                        'value' => [
                                            'srcpath' => [
                                                'xpath' => '.',
                                            ],
                                        ],
                                    ],
                                    [
                                        'op' => 'sequence',
                                        'var' => 'bg-contents',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        'BT-126' => [
            [
                'match' => '/rsm:CrossIndustryInvoice/rsm:SupplyChainTradeTransaction/ram:IncludedSupplyChainTradeLineItem/ram:AssociatedDocumentLineDocument/ram:LineID',
                'priority' => 0.5,
                'body' => [
                    [
                        'op' => 'element',
                        'name' => 'Invoice_line_identifier',
                        'body' => [
                            [
                                'op' => 'attribute',
                                'name' => 'xr:id',
                                'value' => [
                                    'literal' => 'BT-126',
                                ],
                            ],
                            [
                                'op' => 'attribute',
                                'name' => 'xr:src',
                                'value' => [
                                    'srcpath' => [
                                        'xpath' => '.',
                                    ],
                                ],
                            ],
                            [
                                'op' => 'call',
                                'name' => 'identifier',
                                'params' => [],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        'BT-127' => [
            [
                'match' => '/rsm:CrossIndustryInvoice/rsm:SupplyChainTradeTransaction/ram:IncludedSupplyChainTradeLineItem/ram:AssociatedDocumentLineDocument/ram:IncludedNote/ram:Content',
                'priority' => 0.5,
                'body' => [
                    [
                        'op' => 'element',
                        'name' => 'Invoice_line_note',
                        'body' => [
                            [
                                'op' => 'attribute',
                                'name' => 'xr:id',
                                'value' => [
                                    'literal' => 'BT-127',
                                ],
                            ],
                            [
                                'op' => 'attribute',
                                'name' => 'xr:src',
                                'value' => [
                                    'srcpath' => [
                                        'xpath' => '.',
                                    ],
                                ],
                            ],
                            [
                                'op' => 'call',
                                'name' => 'text',
                                'params' => [],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        'BT-128' => [
            [
                'match' => '/rsm:CrossIndustryInvoice/rsm:SupplyChainTradeTransaction/ram:IncludedSupplyChainTradeLineItem/ram:SpecifiedLineTradeSettlement/ram:AdditionalReferencedDocument/ram:IssuerAssignedID[following-sibling::ram:TypeCode=\'130\']',
                'priority' => 0.5,
                'body' => [
                    [
                        'op' => 'element',
                        'name' => 'Invoice_line_object_identifier',
                        'body' => [
                            [
                                'op' => 'attribute',
                                'name' => 'xr:id',
                                'value' => [
                                    'literal' => 'BT-128',
                                ],
                            ],
                            [
                                'op' => 'attribute',
                                'name' => 'xr:src',
                                'value' => [
                                    'srcpath' => [
                                        'xpath' => '.',
                                    ],
                                ],
                            ],
                            [
                                'op' => 'call',
                                'name' => 'identifier-with-scheme',
                                'params' => [
                                    'schemeID' => [
                                        'xpath' => 'following-sibling::ram:ReferenceTypeCode',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        'BT-129' => [
            [
                'match' => '/rsm:CrossIndustryInvoice/rsm:SupplyChainTradeTransaction/ram:IncludedSupplyChainTradeLineItem/ram:SpecifiedLineTradeDelivery/ram:BilledQuantity',
                'priority' => 0.5,
                'body' => [
                    [
                        'op' => 'element',
                        'name' => 'Invoiced_quantity',
                        'body' => [
                            [
                                'op' => 'attribute',
                                'name' => 'xr:id',
                                'value' => [
                                    'literal' => 'BT-129',
                                ],
                            ],
                            [
                                'op' => 'attribute',
                                'name' => 'xr:src',
                                'value' => [
                                    'srcpath' => [
                                        'xpath' => '.',
                                    ],
                                ],
                            ],
                            [
                                'op' => 'call',
                                'name' => 'quantity',
                                'params' => [],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        'BT-130' => [
            [
                'match' => '/rsm:CrossIndustryInvoice/rsm:SupplyChainTradeTransaction/ram:IncludedSupplyChainTradeLineItem/ram:SpecifiedLineTradeDelivery/ram:BilledQuantity/@unitCode',
                'priority' => 0.5,
                'body' => [
                    [
                        'op' => 'element',
                        'name' => 'Invoiced_quantity_unit_of_measure_code',
                        'body' => [
                            [
                                'op' => 'attribute',
                                'name' => 'xr:id',
                                'value' => [
                                    'literal' => 'BT-130',
                                ],
                            ],
                            [
                                'op' => 'attribute',
                                'name' => 'xr:src',
                                'value' => [
                                    'srcpath' => [
                                        'xpath' => '.',
                                    ],
                                ],
                            ],
                            [
                                'op' => 'call',
                                'name' => 'code',
                                'params' => [],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        'BT-131' => [
            [
                'match' => '/rsm:CrossIndustryInvoice/rsm:SupplyChainTradeTransaction/ram:IncludedSupplyChainTradeLineItem/ram:SpecifiedLineTradeSettlement/ram:SpecifiedTradeSettlementLineMonetarySummation/ram:LineTotalAmount',
                'priority' => 0.5,
                'body' => [
                    [
                        'op' => 'element',
                        'name' => 'Invoice_line_net_amount',
                        'body' => [
                            [
                                'op' => 'attribute',
                                'name' => 'xr:id',
                                'value' => [
                                    'literal' => 'BT-131',
                                ],
                            ],
                            [
                                'op' => 'attribute',
                                'name' => 'xr:src',
                                'value' => [
                                    'srcpath' => [
                                        'xpath' => '.',
                                    ],
                                ],
                            ],
                            [
                                'op' => 'call',
                                'name' => 'amount',
                                'params' => [],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        'BT-132' => [
            [
                'match' => '/rsm:CrossIndustryInvoice/rsm:SupplyChainTradeTransaction/ram:IncludedSupplyChainTradeLineItem/ram:SpecifiedLineTradeAgreement/ram:BuyerOrderReferencedDocument/ram:LineID',
                'priority' => 0.5,
                'body' => [
                    [
                        'op' => 'element',
                        'name' => 'Referenced_purchase_order_line_reference',
                        'body' => [
                            [
                                'op' => 'attribute',
                                'name' => 'xr:id',
                                'value' => [
                                    'literal' => 'BT-132',
                                ],
                            ],
                            [
                                'op' => 'attribute',
                                'name' => 'xr:src',
                                'value' => [
                                    'srcpath' => [
                                        'xpath' => '.',
                                    ],
                                ],
                            ],
                            [
                                'op' => 'call',
                                'name' => 'document_reference',
                                'params' => [],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        'BT-133' => [
            [
                'match' => '/rsm:CrossIndustryInvoice/rsm:SupplyChainTradeTransaction/ram:IncludedSupplyChainTradeLineItem/ram:SpecifiedLineTradeSettlement/ram:ReceivableSpecifiedTradeAccountingAccount/ram:ID',
                'priority' => 0.5,
                'body' => [
                    [
                        'op' => 'element',
                        'name' => 'Invoice_line_Buyer_accounting_reference',
                        'body' => [
                            [
                                'op' => 'attribute',
                                'name' => 'xr:id',
                                'value' => [
                                    'literal' => 'BT-133',
                                ],
                            ],
                            [
                                'op' => 'attribute',
                                'name' => 'xr:src',
                                'value' => [
                                    'srcpath' => [
                                        'xpath' => '.',
                                    ],
                                ],
                            ],
                            [
                                'op' => 'call',
                                'name' => 'text',
                                'params' => [],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        'BG-26' => [
            [
                'match' => '/rsm:CrossIndustryInvoice/rsm:SupplyChainTradeTransaction/ram:IncludedSupplyChainTradeLineItem/ram:SpecifiedLineTradeSettlement/ram:BillingSpecifiedPeriod',
                'priority' => 0.5,
                'body' => [
                    [
                        'op' => 'variable',
                        'name' => 'bg-contents',
                        'body' => [
                            [
                                'op' => 'apply',
                                'mode' => 'BT-134',
                                'select' => [
                                    'xpath' => './ram:StartDateTime/udt:DateTimeString[@format=\'102\']',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BT-135',
                                'select' => [
                                    'xpath' => './ram:EndDateTime/udt:DateTimeString[@format=\'102\']',
                                ],
                            ],
                        ],
                    ],
                    [
                        'op' => 'if',
                        'test' => [
                            'var' => 'bg-contents',
                        ],
                        'body' => [
                            [
                                'op' => 'element',
                                'name' => 'INVOICE_LINE_PERIOD',
                                'body' => [
                                    [
                                        'op' => 'attribute',
                                        'name' => 'xr:id',
                                        'value' => [
                                            'literal' => 'BG-26',
                                        ],
                                    ],
                                    [
                                        'op' => 'attribute',
                                        'name' => 'xr:src',
                                        'value' => [
                                            'srcpath' => [
                                                'xpath' => '.',
                                            ],
                                        ],
                                    ],
                                    [
                                        'op' => 'sequence',
                                        'var' => 'bg-contents',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        'BT-134' => [
            [
                'match' => '/rsm:CrossIndustryInvoice/rsm:SupplyChainTradeTransaction/ram:IncludedSupplyChainTradeLineItem/ram:SpecifiedLineTradeSettlement/ram:BillingSpecifiedPeriod/ram:StartDateTime/udt:DateTimeString[@format=\'102\']',
                'priority' => 0.5,
                'body' => [
                    [
                        'op' => 'element',
                        'name' => 'Invoice_line_period_start_date',
                        'body' => [
                            [
                                'op' => 'attribute',
                                'name' => 'xr:id',
                                'value' => [
                                    'literal' => 'BT-134',
                                ],
                            ],
                            [
                                'op' => 'attribute',
                                'name' => 'xr:src',
                                'value' => [
                                    'srcpath' => [
                                        'xpath' => '.',
                                    ],
                                ],
                            ],
                            [
                                'op' => 'call',
                                'name' => 'date',
                                'params' => [],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        'BT-135' => [
            [
                'match' => '/rsm:CrossIndustryInvoice/rsm:SupplyChainTradeTransaction/ram:IncludedSupplyChainTradeLineItem/ram:SpecifiedLineTradeSettlement/ram:BillingSpecifiedPeriod/ram:EndDateTime/udt:DateTimeString[@format=\'102\']',
                'priority' => 0.5,
                'body' => [
                    [
                        'op' => 'element',
                        'name' => 'Invoice_line_period_end_date',
                        'body' => [
                            [
                                'op' => 'attribute',
                                'name' => 'xr:id',
                                'value' => [
                                    'literal' => 'BT-135',
                                ],
                            ],
                            [
                                'op' => 'attribute',
                                'name' => 'xr:src',
                                'value' => [
                                    'srcpath' => [
                                        'xpath' => '.',
                                    ],
                                ],
                            ],
                            [
                                'op' => 'call',
                                'name' => 'date',
                                'params' => [],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        'BG-27' => [
            [
                'match' => '/rsm:CrossIndustryInvoice/rsm:SupplyChainTradeTransaction/ram:IncludedSupplyChainTradeLineItem/ram:SpecifiedLineTradeSettlement/ram:SpecifiedTradeAllowanceCharge[ram:ChargeIndicator/udt:Indicator=\'false\']',
                'priority' => 0.5,
                'body' => [
                    [
                        'op' => 'variable',
                        'name' => 'bg-contents',
                        'body' => [
                            [
                                'op' => 'apply',
                                'mode' => 'BT-136',
                                'select' => [
                                    'xpath' => './ram:ActualAmount',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BT-137',
                                'select' => [
                                    'xpath' => './ram:BasisAmount',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BT-138',
                                'select' => [
                                    'xpath' => './ram:CalculationPercent',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BT-139',
                                'select' => [
                                    'xpath' => './ram:Reason',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BT-140',
                                'select' => [
                                    'xpath' => './ram:ReasonCode',
                                ],
                            ],
                        ],
                    ],
                    [
                        'op' => 'if',
                        'test' => [
                            'var' => 'bg-contents',
                        ],
                        'body' => [
                            [
                                'op' => 'element',
                                'name' => 'INVOICE_LINE_ALLOWANCES',
                                'body' => [
                                    [
                                        'op' => 'attribute',
                                        'name' => 'xr:id',
                                        'value' => [
                                            'literal' => 'BG-27',
                                        ],
                                    ],
                                    [
                                        'op' => 'attribute',
                                        'name' => 'xr:src',
                                        'value' => [
                                            'srcpath' => [
                                                'xpath' => '.',
                                            ],
                                        ],
                                    ],
                                    [
                                        'op' => 'sequence',
                                        'var' => 'bg-contents',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        'BT-136' => [
            [
                'match' => '/rsm:CrossIndustryInvoice/rsm:SupplyChainTradeTransaction/ram:IncludedSupplyChainTradeLineItem/ram:SpecifiedLineTradeSettlement/ram:SpecifiedTradeAllowanceCharge/ram:ActualAmount',
                'priority' => 0.5,
                'body' => [
                    [
                        'op' => 'element',
                        'name' => 'Invoice_line_allowance_amount',
                        'body' => [
                            [
                                'op' => 'attribute',
                                'name' => 'xr:id',
                                'value' => [
                                    'literal' => 'BT-136',
                                ],
                            ],
                            [
                                'op' => 'attribute',
                                'name' => 'xr:src',
                                'value' => [
                                    'srcpath' => [
                                        'xpath' => '.',
                                    ],
                                ],
                            ],
                            [
                                'op' => 'call',
                                'name' => 'amount',
                                'params' => [],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        'BT-137' => [
            [
                'match' => '/rsm:CrossIndustryInvoice/rsm:SupplyChainTradeTransaction/ram:IncludedSupplyChainTradeLineItem/ram:SpecifiedLineTradeSettlement/ram:SpecifiedTradeAllowanceCharge/ram:BasisAmount',
                'priority' => 0.5,
                'body' => [
                    [
                        'op' => 'element',
                        'name' => 'Invoice_line_allowance_base_amount',
                        'body' => [
                            [
                                'op' => 'attribute',
                                'name' => 'xr:id',
                                'value' => [
                                    'literal' => 'BT-137',
                                ],
                            ],
                            [
                                'op' => 'attribute',
                                'name' => 'xr:src',
                                'value' => [
                                    'srcpath' => [
                                        'xpath' => '.',
                                    ],
                                ],
                            ],
                            [
                                'op' => 'call',
                                'name' => 'amount',
                                'params' => [],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        'BT-138' => [
            [
                'match' => '/rsm:CrossIndustryInvoice/rsm:SupplyChainTradeTransaction/ram:IncludedSupplyChainTradeLineItem/ram:SpecifiedLineTradeSettlement/ram:SpecifiedTradeAllowanceCharge/ram:CalculationPercent',
                'priority' => 0.5,
                'body' => [
                    [
                        'op' => 'element',
                        'name' => 'Invoice_line_allowance_percentage',
                        'body' => [
                            [
                                'op' => 'attribute',
                                'name' => 'xr:id',
                                'value' => [
                                    'literal' => 'BT-138',
                                ],
                            ],
                            [
                                'op' => 'attribute',
                                'name' => 'xr:src',
                                'value' => [
                                    'srcpath' => [
                                        'xpath' => '.',
                                    ],
                                ],
                            ],
                            [
                                'op' => 'call',
                                'name' => 'percentage',
                                'params' => [],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        'BT-139' => [
            [
                'match' => '/rsm:CrossIndustryInvoice/rsm:SupplyChainTradeTransaction/ram:IncludedSupplyChainTradeLineItem/ram:SpecifiedLineTradeSettlement/ram:SpecifiedTradeAllowanceCharge/ram:Reason',
                'priority' => 0.5,
                'body' => [
                    [
                        'op' => 'element',
                        'name' => 'Invoice_line_allowance_reason',
                        'body' => [
                            [
                                'op' => 'attribute',
                                'name' => 'xr:id',
                                'value' => [
                                    'literal' => 'BT-139',
                                ],
                            ],
                            [
                                'op' => 'attribute',
                                'name' => 'xr:src',
                                'value' => [
                                    'srcpath' => [
                                        'xpath' => '.',
                                    ],
                                ],
                            ],
                            [
                                'op' => 'call',
                                'name' => 'text',
                                'params' => [],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        'BT-140' => [
            [
                'match' => '/rsm:CrossIndustryInvoice/rsm:SupplyChainTradeTransaction/ram:IncludedSupplyChainTradeLineItem/ram:SpecifiedLineTradeSettlement/ram:SpecifiedTradeAllowanceCharge/ram:ReasonCode',
                'priority' => 0.5,
                'body' => [
                    [
                        'op' => 'element',
                        'name' => 'Invoice_line_allowance_reason_code',
                        'body' => [
                            [
                                'op' => 'attribute',
                                'name' => 'xr:id',
                                'value' => [
                                    'literal' => 'BT-140',
                                ],
                            ],
                            [
                                'op' => 'attribute',
                                'name' => 'xr:src',
                                'value' => [
                                    'srcpath' => [
                                        'xpath' => '.',
                                    ],
                                ],
                            ],
                            [
                                'op' => 'call',
                                'name' => 'code',
                                'params' => [],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        'BG-28' => [
            [
                'match' => '/rsm:CrossIndustryInvoice/rsm:SupplyChainTradeTransaction/ram:IncludedSupplyChainTradeLineItem/ram:SpecifiedLineTradeSettlement/ram:SpecifiedTradeAllowanceCharge[ram:ChargeIndicator/udt:Indicator=\'true\']',
                'priority' => 0.5,
                'body' => [
                    [
                        'op' => 'variable',
                        'name' => 'bg-contents',
                        'body' => [
                            [
                                'op' => 'apply',
                                'mode' => 'BT-141',
                                'select' => [
                                    'xpath' => './ram:ActualAmount',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BT-142',
                                'select' => [
                                    'xpath' => './ram:BasisAmount',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BT-143',
                                'select' => [
                                    'xpath' => './ram:CalculationPercent',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BT-144',
                                'select' => [
                                    'xpath' => './ram:Reason',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BT-145',
                                'select' => [
                                    'xpath' => './ram:ReasonCode',
                                ],
                            ],
                        ],
                    ],
                    [
                        'op' => 'if',
                        'test' => [
                            'var' => 'bg-contents',
                        ],
                        'body' => [
                            [
                                'op' => 'element',
                                'name' => 'INVOICE_LINE_CHARGES',
                                'body' => [
                                    [
                                        'op' => 'attribute',
                                        'name' => 'xr:id',
                                        'value' => [
                                            'literal' => 'BG-28',
                                        ],
                                    ],
                                    [
                                        'op' => 'attribute',
                                        'name' => 'xr:src',
                                        'value' => [
                                            'srcpath' => [
                                                'xpath' => '.',
                                            ],
                                        ],
                                    ],
                                    [
                                        'op' => 'sequence',
                                        'var' => 'bg-contents',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        'BT-141' => [
            [
                'match' => '/rsm:CrossIndustryInvoice/rsm:SupplyChainTradeTransaction/ram:IncludedSupplyChainTradeLineItem/ram:SpecifiedLineTradeSettlement/ram:SpecifiedTradeAllowanceCharge/ram:ActualAmount',
                'priority' => 0.5,
                'body' => [
                    [
                        'op' => 'element',
                        'name' => 'Invoice_line_charge_amount',
                        'body' => [
                            [
                                'op' => 'attribute',
                                'name' => 'xr:id',
                                'value' => [
                                    'literal' => 'BT-141',
                                ],
                            ],
                            [
                                'op' => 'attribute',
                                'name' => 'xr:src',
                                'value' => [
                                    'srcpath' => [
                                        'xpath' => '.',
                                    ],
                                ],
                            ],
                            [
                                'op' => 'call',
                                'name' => 'amount',
                                'params' => [],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        'BT-142' => [
            [
                'match' => '/rsm:CrossIndustryInvoice/rsm:SupplyChainTradeTransaction/ram:IncludedSupplyChainTradeLineItem/ram:SpecifiedLineTradeSettlement/ram:SpecifiedTradeAllowanceCharge/ram:BasisAmount',
                'priority' => 0.5,
                'body' => [
                    [
                        'op' => 'element',
                        'name' => 'Invoice_line_charge_base_amount',
                        'body' => [
                            [
                                'op' => 'attribute',
                                'name' => 'xr:id',
                                'value' => [
                                    'literal' => 'BT-142',
                                ],
                            ],
                            [
                                'op' => 'attribute',
                                'name' => 'xr:src',
                                'value' => [
                                    'srcpath' => [
                                        'xpath' => '.',
                                    ],
                                ],
                            ],
                            [
                                'op' => 'call',
                                'name' => 'amount',
                                'params' => [],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        'BT-143' => [
            [
                'match' => '/rsm:CrossIndustryInvoice/rsm:SupplyChainTradeTransaction/ram:IncludedSupplyChainTradeLineItem/ram:SpecifiedLineTradeSettlement/ram:SpecifiedTradeAllowanceCharge/ram:CalculationPercent',
                'priority' => 0.5,
                'body' => [
                    [
                        'op' => 'element',
                        'name' => 'Invoice_line_charge_percentage',
                        'body' => [
                            [
                                'op' => 'attribute',
                                'name' => 'xr:id',
                                'value' => [
                                    'literal' => 'BT-143',
                                ],
                            ],
                            [
                                'op' => 'attribute',
                                'name' => 'xr:src',
                                'value' => [
                                    'srcpath' => [
                                        'xpath' => '.',
                                    ],
                                ],
                            ],
                            [
                                'op' => 'call',
                                'name' => 'percentage',
                                'params' => [],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        'BT-144' => [
            [
                'match' => '/rsm:CrossIndustryInvoice/rsm:SupplyChainTradeTransaction/ram:IncludedSupplyChainTradeLineItem/ram:SpecifiedLineTradeSettlement/ram:SpecifiedTradeAllowanceCharge/ram:Reason',
                'priority' => 0.5,
                'body' => [
                    [
                        'op' => 'element',
                        'name' => 'Invoice_line_charge_reason',
                        'body' => [
                            [
                                'op' => 'attribute',
                                'name' => 'xr:id',
                                'value' => [
                                    'literal' => 'BT-144',
                                ],
                            ],
                            [
                                'op' => 'attribute',
                                'name' => 'xr:src',
                                'value' => [
                                    'srcpath' => [
                                        'xpath' => '.',
                                    ],
                                ],
                            ],
                            [
                                'op' => 'call',
                                'name' => 'text',
                                'params' => [],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        'BT-145' => [
            [
                'match' => '/rsm:CrossIndustryInvoice/rsm:SupplyChainTradeTransaction/ram:IncludedSupplyChainTradeLineItem/ram:SpecifiedLineTradeSettlement/ram:SpecifiedTradeAllowanceCharge/ram:ReasonCode',
                'priority' => 0.5,
                'body' => [
                    [
                        'op' => 'element',
                        'name' => 'Invoice_line_charge_reason_code',
                        'body' => [
                            [
                                'op' => 'attribute',
                                'name' => 'xr:id',
                                'value' => [
                                    'literal' => 'BT-145',
                                ],
                            ],
                            [
                                'op' => 'attribute',
                                'name' => 'xr:src',
                                'value' => [
                                    'srcpath' => [
                                        'xpath' => '.',
                                    ],
                                ],
                            ],
                            [
                                'op' => 'call',
                                'name' => 'code',
                                'params' => [],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        'BG-29' => [
            [
                'match' => '/rsm:CrossIndustryInvoice/rsm:SupplyChainTradeTransaction/ram:IncludedSupplyChainTradeLineItem/ram:SpecifiedLineTradeAgreement',
                'priority' => 0.5,
                'body' => [
                    [
                        'op' => 'variable',
                        'name' => 'bg-contents',
                        'body' => [
                            [
                                'op' => 'apply',
                                'mode' => 'BT-146',
                                'select' => [
                                    'xpath' => './ram:NetPriceProductTradePrice/ram:ChargeAmount',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BT-147',
                                'select' => [
                                    'xpath' => './ram:GrossPriceProductTradePrice/ram:AppliedTradeAllowanceCharge/ram:ActualAmount',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BT-148',
                                'select' => [
                                    'xpath' => './ram:GrossPriceProductTradePrice/ram:ChargeAmount',
                                ],
                            ],
                            [
                                'op' => 'if',
                                'test' => [
                                    'xpath' => './ram:NetPriceProductTradePrice/ram:BasisQuantity | ./ram:GrossPriceProductTradePrice/ram:BasisQuantity',
                                ],
                                'body' => [
                                    [
                                        'op' => 'element',
                                        'name' => 'Item_price_base_quantity',
                                        'body' => [
                                            [
                                                'op' => 'attribute',
                                                'name' => 'xr:id',
                                                'value' => [
                                                    'literal' => 'BT-149',
                                                ],
                                            ],
                                            [
                                                'op' => 'attribute',
                                                'name' => 'xr:src',
                                                'value' => [
                                                    'literal' => '/rsm:CrossIndustryInvoice/rsm:SupplyChainTradeTransaction/ram:IncludedSupplyChainTradeLineItem/ram:SpecifiedLineTradeAgreement/ram:GrossPriceProductTradePrice/ram:BasisQuantity | /rsm:CrossIndustryInvoice/rsm:SupplyChainTradeTransaction/ram:IncludedSupplyChainTradeLineItem/ram:SpecifiedLineTradeAgreement/ram:NetPriceProductTradePrice/ram:BasisQuantity',
                                                ],
                                            ],
                                            [
                                                'op' => 'choose',
                                                'when' => [
                                                    [
                                                        'test' => [
                                                            'xpath' => './ram:NetPriceProductTradePrice/ram:BasisQuantity and ./ram:GrossPriceProductTradePrice/ram:BasisQuantity',
                                                        ],
                                                        'body' => [
                                                            [
                                                                'op' => 'apply',
                                                                'mode' => 'BT-149',
                                                                'select' => [
                                                                    'xpath' => './ram:GrossPriceProductTradePrice/ram:BasisQuantity',
                                                                ],
                                                            ],
                                                        ],
                                                    ],
                                                ],
                                                'otherwise' => [
                                                    [
                                                        'op' => 'if',
                                                        'test' => [
                                                            'xpath' => './ram:NetPriceProductTradePrice/ram:BasisQuantity',
                                                        ],
                                                        'body' => [
                                                            [
                                                                'op' => 'apply',
                                                                'mode' => 'BT-149',
                                                                'select' => [
                                                                    'xpath' => './ram:NetPriceProductTradePrice/ram:BasisQuantity',
                                                                ],
                                                            ],
                                                        ],
                                                    ],
                                                    [
                                                        'op' => 'if',
                                                        'test' => [
                                                            'xpath' => './ram:GrossPriceProductTradePrice/ram:BasisQuantity',
                                                        ],
                                                        'body' => [
                                                            [
                                                                'op' => 'apply',
                                                                'mode' => 'BT-149',
                                                                'select' => [
                                                                    'xpath' => './ram:GrossPriceProductTradePrice/ram:BasisQuantity',
                                                                ],
                                                            ],
                                                        ],
                                                    ],
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                            [
                                'op' => 'if',
                                'test' => [
                                    'xpath' => './ram:NetPriceProductTradePrice/ram:BasisQuantity/@unitCode | ./ram:GrossPriceProductTradePrice/ram:BasisQuantity/@unitCode',
                                ],
                                'body' => [
                                    [
                                        'op' => 'element',
                                        'name' => 'Item_price_base_quantity_unit_of_measure',
                                        'body' => [
                                            [
                                                'op' => 'attribute',
                                                'name' => 'xr:id',
                                                'value' => [
                                                    'literal' => 'BT-150',
                                                ],
                                            ],
                                            [
                                                'op' => 'attribute',
                                                'name' => 'xr:src',
                                                'value' => [
                                                    'literal' => '/rsm:CrossIndustryInvoice/rsm:SupplyChainTradeTransaction/ram:IncludedSupplyChainTradeLineItem/ram:SpecifiedLineTradeAgreement/ram:GrossPriceProductTradePrice/ram:BasisQuantity/@unitCode | /rsm:CrossIndustryInvoice/rsm:SupplyChainTradeTransaction/ram:IncludedSupplyChainTradeLineItem/ram:SpecifiedLineTradeAgreement/ram:NetPriceProductTradePrice/ram:BasisQuantity/@unitCode',
                                                ],
                                            ],
                                            [
                                                'op' => 'choose',
                                                'when' => [
                                                    [
                                                        'test' => [
                                                            'xpath' => './ram:NetPriceProductTradePrice/ram:BasisQuantity/@unitCode and ./ram:GrossPriceProductTradePrice/ram:BasisQuantity/@unitCode',
                                                        ],
                                                        'body' => [
                                                            [
                                                                'op' => 'apply',
                                                                'mode' => 'BT-150',
                                                                'select' => [
                                                                    'xpath' => './ram:GrossPriceProductTradePrice/ram:BasisQuantity/@unitCode',
                                                                ],
                                                            ],
                                                        ],
                                                    ],
                                                ],
                                                'otherwise' => [
                                                    [
                                                        'op' => 'if',
                                                        'test' => [
                                                            'xpath' => './ram:NetPriceProductTradePrice/ram:BasisQuantity/@unitCode',
                                                        ],
                                                        'body' => [
                                                            [
                                                                'op' => 'apply',
                                                                'mode' => 'BT-150',
                                                                'select' => [
                                                                    'xpath' => './ram:NetPriceProductTradePrice/ram:BasisQuantity/@unitCode',
                                                                ],
                                                            ],
                                                        ],
                                                    ],
                                                    [
                                                        'op' => 'if',
                                                        'test' => [
                                                            'xpath' => './ram:GrossPriceProductTradePrice/ram:BasisQuantity/@unitCode',
                                                        ],
                                                        'body' => [
                                                            [
                                                                'op' => 'apply',
                                                                'mode' => 'BT-150',
                                                                'select' => [
                                                                    'xpath' => './ram:GrossPriceProductTradePrice/ram:BasisQuantity/@unitCode',
                                                                ],
                                                            ],
                                                        ],
                                                    ],
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                    [
                        'op' => 'if',
                        'test' => [
                            'var' => 'bg-contents',
                        ],
                        'body' => [
                            [
                                'op' => 'element',
                                'name' => 'PRICE_DETAILS',
                                'body' => [
                                    [
                                        'op' => 'attribute',
                                        'name' => 'xr:id',
                                        'value' => [
                                            'literal' => 'BG-29',
                                        ],
                                    ],
                                    [
                                        'op' => 'attribute',
                                        'name' => 'xr:src',
                                        'value' => [
                                            'srcpath' => [
                                                'xpath' => '.',
                                            ],
                                        ],
                                    ],
                                    [
                                        'op' => 'sequence',
                                        'var' => 'bg-contents',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        'BT-146' => [
            [
                'match' => '/rsm:CrossIndustryInvoice/rsm:SupplyChainTradeTransaction/ram:IncludedSupplyChainTradeLineItem/ram:SpecifiedLineTradeAgreement/ram:NetPriceProductTradePrice/ram:ChargeAmount',
                'priority' => 0.5,
                'body' => [
                    [
                        'op' => 'element',
                        'name' => 'Item_net_price',
                        'body' => [
                            [
                                'op' => 'attribute',
                                'name' => 'xr:id',
                                'value' => [
                                    'literal' => 'BT-146',
                                ],
                            ],
                            [
                                'op' => 'attribute',
                                'name' => 'xr:src',
                                'value' => [
                                    'srcpath' => [
                                        'xpath' => '.',
                                    ],
                                ],
                            ],
                            [
                                'op' => 'call',
                                'name' => 'unit_price_amount',
                                'params' => [],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        'BT-147' => [
            [
                'match' => '/rsm:CrossIndustryInvoice/rsm:SupplyChainTradeTransaction/ram:IncludedSupplyChainTradeLineItem/ram:SpecifiedLineTradeAgreement/ram:GrossPriceProductTradePrice/ram:AppliedTradeAllowanceCharge/ram:ActualAmount',
                'priority' => 0.5,
                'body' => [
                    [
                        'op' => 'element',
                        'name' => 'Item_price_discount',
                        'body' => [
                            [
                                'op' => 'attribute',
                                'name' => 'xr:id',
                                'value' => [
                                    'literal' => 'BT-147',
                                ],
                            ],
                            [
                                'op' => 'attribute',
                                'name' => 'xr:src',
                                'value' => [
                                    'srcpath' => [
                                        'xpath' => '.',
                                    ],
                                ],
                            ],
                            [
                                'op' => 'call',
                                'name' => 'unit_price_amount',
                                'params' => [],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        'BT-148' => [
            [
                'match' => '/rsm:CrossIndustryInvoice/rsm:SupplyChainTradeTransaction/ram:IncludedSupplyChainTradeLineItem/ram:SpecifiedLineTradeAgreement/ram:GrossPriceProductTradePrice/ram:ChargeAmount',
                'priority' => 0.5,
                'body' => [
                    [
                        'op' => 'element',
                        'name' => 'Item_gross_price',
                        'body' => [
                            [
                                'op' => 'attribute',
                                'name' => 'xr:id',
                                'value' => [
                                    'literal' => 'BT-148',
                                ],
                            ],
                            [
                                'op' => 'attribute',
                                'name' => 'xr:src',
                                'value' => [
                                    'srcpath' => [
                                        'xpath' => '.',
                                    ],
                                ],
                            ],
                            [
                                'op' => 'call',
                                'name' => 'unit_price_amount',
                                'params' => [],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        'BG-30' => [
            [
                'match' => '/rsm:CrossIndustryInvoice/rsm:SupplyChainTradeTransaction/ram:IncludedSupplyChainTradeLineItem/ram:SpecifiedLineTradeSettlement/ram:ApplicableTradeTax',
                'priority' => 0.5,
                'body' => [
                    [
                        'op' => 'variable',
                        'name' => 'bg-contents',
                        'body' => [
                            [
                                'op' => 'apply',
                                'mode' => 'BT-151',
                                'select' => [
                                    'xpath' => './ram:CategoryCode',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BT-152',
                                'select' => [
                                    'xpath' => './ram:RateApplicablePercent',
                                ],
                            ],
                        ],
                    ],
                    [
                        'op' => 'if',
                        'test' => [
                            'var' => 'bg-contents',
                        ],
                        'body' => [
                            [
                                'op' => 'element',
                                'name' => 'LINE_VAT_INFORMATION',
                                'body' => [
                                    [
                                        'op' => 'attribute',
                                        'name' => 'xr:id',
                                        'value' => [
                                            'literal' => 'BG-30',
                                        ],
                                    ],
                                    [
                                        'op' => 'attribute',
                                        'name' => 'xr:src',
                                        'value' => [
                                            'srcpath' => [
                                                'xpath' => '.',
                                            ],
                                        ],
                                    ],
                                    [
                                        'op' => 'sequence',
                                        'var' => 'bg-contents',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        'BT-151' => [
            [
                'match' => '/rsm:CrossIndustryInvoice/rsm:SupplyChainTradeTransaction/ram:IncludedSupplyChainTradeLineItem/ram:SpecifiedLineTradeSettlement/ram:ApplicableTradeTax/ram:CategoryCode',
                'priority' => 0.5,
                'body' => [
                    [
                        'op' => 'element',
                        'name' => 'Invoiced_item_VAT_category_code',
                        'body' => [
                            [
                                'op' => 'attribute',
                                'name' => 'xr:id',
                                'value' => [
                                    'literal' => 'BT-151',
                                ],
                            ],
                            [
                                'op' => 'attribute',
                                'name' => 'xr:src',
                                'value' => [
                                    'srcpath' => [
                                        'xpath' => '.',
                                    ],
                                ],
                            ],
                            [
                                'op' => 'call',
                                'name' => 'code',
                                'params' => [],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        'BT-152' => [
            [
                'match' => '/rsm:CrossIndustryInvoice/rsm:SupplyChainTradeTransaction/ram:IncludedSupplyChainTradeLineItem/ram:SpecifiedLineTradeSettlement/ram:ApplicableTradeTax/ram:RateApplicablePercent',
                'priority' => 0.5,
                'body' => [
                    [
                        'op' => 'element',
                        'name' => 'Invoiced_item_VAT_rate',
                        'body' => [
                            [
                                'op' => 'attribute',
                                'name' => 'xr:id',
                                'value' => [
                                    'literal' => 'BT-152',
                                ],
                            ],
                            [
                                'op' => 'attribute',
                                'name' => 'xr:src',
                                'value' => [
                                    'srcpath' => [
                                        'xpath' => '.',
                                    ],
                                ],
                            ],
                            [
                                'op' => 'call',
                                'name' => 'percentage',
                                'params' => [],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        'BG-31' => [
            [
                'match' => '/rsm:CrossIndustryInvoice/rsm:SupplyChainTradeTransaction/ram:IncludedSupplyChainTradeLineItem/ram:SpecifiedTradeProduct',
                'priority' => 0.5,
                'body' => [
                    [
                        'op' => 'variable',
                        'name' => 'bg-contents',
                        'body' => [
                            [
                                'op' => 'apply',
                                'mode' => 'BT-153',
                                'select' => [
                                    'xpath' => './ram:Name',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BT-154',
                                'select' => [
                                    'xpath' => './ram:Description',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BT-155',
                                'select' => [
                                    'xpath' => './ram:SellerAssignedID',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BT-156',
                                'select' => [
                                    'xpath' => './ram:BuyerAssignedID',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BT-157',
                                'select' => [
                                    'xpath' => './ram:GlobalID',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BT-158',
                                'select' => [
                                    'xpath' => './ram:DesignatedProductClassification/ram:ClassCode',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BT-159',
                                'select' => [
                                    'xpath' => './ram:OriginTradeCountry/ram:ID',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BG-32',
                                'select' => [
                                    'xpath' => './ram:ApplicableProductCharacteristic',
                                ],
                            ],
                        ],
                    ],
                    [
                        'op' => 'if',
                        'test' => [
                            'var' => 'bg-contents',
                        ],
                        'body' => [
                            [
                                'op' => 'element',
                                'name' => 'ITEM_INFORMATION',
                                'body' => [
                                    [
                                        'op' => 'attribute',
                                        'name' => 'xr:id',
                                        'value' => [
                                            'literal' => 'BG-31',
                                        ],
                                    ],
                                    [
                                        'op' => 'attribute',
                                        'name' => 'xr:src',
                                        'value' => [
                                            'srcpath' => [
                                                'xpath' => '.',
                                            ],
                                        ],
                                    ],
                                    [
                                        'op' => 'sequence',
                                        'var' => 'bg-contents',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        'BT-153' => [
            [
                'match' => '/rsm:CrossIndustryInvoice/rsm:SupplyChainTradeTransaction/ram:IncludedSupplyChainTradeLineItem/ram:SpecifiedTradeProduct/ram:Name',
                'priority' => 0.5,
                'body' => [
                    [
                        'op' => 'element',
                        'name' => 'Item_name',
                        'body' => [
                            [
                                'op' => 'attribute',
                                'name' => 'xr:id',
                                'value' => [
                                    'literal' => 'BT-153',
                                ],
                            ],
                            [
                                'op' => 'attribute',
                                'name' => 'xr:src',
                                'value' => [
                                    'srcpath' => [
                                        'xpath' => '.',
                                    ],
                                ],
                            ],
                            [
                                'op' => 'call',
                                'name' => 'text',
                                'params' => [],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        'BT-154' => [
            [
                'match' => '/rsm:CrossIndustryInvoice/rsm:SupplyChainTradeTransaction/ram:IncludedSupplyChainTradeLineItem/ram:SpecifiedTradeProduct/ram:Description',
                'priority' => 0.5,
                'body' => [
                    [
                        'op' => 'element',
                        'name' => 'Item_description',
                        'body' => [
                            [
                                'op' => 'attribute',
                                'name' => 'xr:id',
                                'value' => [
                                    'literal' => 'BT-154',
                                ],
                            ],
                            [
                                'op' => 'attribute',
                                'name' => 'xr:src',
                                'value' => [
                                    'srcpath' => [
                                        'xpath' => '.',
                                    ],
                                ],
                            ],
                            [
                                'op' => 'call',
                                'name' => 'text',
                                'params' => [],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        'BT-155' => [
            [
                'match' => '/rsm:CrossIndustryInvoice/rsm:SupplyChainTradeTransaction/ram:IncludedSupplyChainTradeLineItem/ram:SpecifiedTradeProduct/ram:SellerAssignedID',
                'priority' => 0.5,
                'body' => [
                    [
                        'op' => 'element',
                        'name' => 'Item_Sellers_identifier',
                        'body' => [
                            [
                                'op' => 'attribute',
                                'name' => 'xr:id',
                                'value' => [
                                    'literal' => 'BT-155',
                                ],
                            ],
                            [
                                'op' => 'attribute',
                                'name' => 'xr:src',
                                'value' => [
                                    'srcpath' => [
                                        'xpath' => '.',
                                    ],
                                ],
                            ],
                            [
                                'op' => 'call',
                                'name' => 'identifier',
                                'params' => [],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        'BT-156' => [
            [
                'match' => '/rsm:CrossIndustryInvoice/rsm:SupplyChainTradeTransaction/ram:IncludedSupplyChainTradeLineItem/ram:SpecifiedTradeProduct/ram:BuyerAssignedID',
                'priority' => 0.5,
                'body' => [
                    [
                        'op' => 'element',
                        'name' => 'Item_Buyers_identifier',
                        'body' => [
                            [
                                'op' => 'attribute',
                                'name' => 'xr:id',
                                'value' => [
                                    'literal' => 'BT-156',
                                ],
                            ],
                            [
                                'op' => 'attribute',
                                'name' => 'xr:src',
                                'value' => [
                                    'srcpath' => [
                                        'xpath' => '.',
                                    ],
                                ],
                            ],
                            [
                                'op' => 'call',
                                'name' => 'identifier',
                                'params' => [],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        'BT-157' => [
            [
                'match' => '/rsm:CrossIndustryInvoice/rsm:SupplyChainTradeTransaction/ram:IncludedSupplyChainTradeLineItem/ram:SpecifiedTradeProduct/ram:GlobalID',
                'priority' => 0.5,
                'body' => [
                    [
                        'op' => 'element',
                        'name' => 'Item_standard_identifier',
                        'body' => [
                            [
                                'op' => 'attribute',
                                'name' => 'xr:id',
                                'value' => [
                                    'literal' => 'BT-157',
                                ],
                            ],
                            [
                                'op' => 'attribute',
                                'name' => 'xr:src',
                                'value' => [
                                    'srcpath' => [
                                        'xpath' => '.',
                                    ],
                                ],
                            ],
                            [
                                'op' => 'call',
                                'name' => 'identifier',
                                'params' => [],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        'BT-158' => [
            [
                'match' => '/rsm:CrossIndustryInvoice/rsm:SupplyChainTradeTransaction/ram:IncludedSupplyChainTradeLineItem/ram:SpecifiedTradeProduct/ram:DesignatedProductClassification/ram:ClassCode',
                'priority' => 0.5,
                'body' => [
                    [
                        'op' => 'element',
                        'name' => 'Item_classification_identifier',
                        'body' => [
                            [
                                'op' => 'attribute',
                                'name' => 'xr:id',
                                'value' => [
                                    'literal' => 'BT-158',
                                ],
                            ],
                            [
                                'op' => 'attribute',
                                'name' => 'xr:src',
                                'value' => [
                                    'srcpath' => [
                                        'xpath' => '.',
                                    ],
                                ],
                            ],
                            [
                                'op' => 'call',
                                'name' => 'identifier',
                                'params' => [],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        'BT-159' => [
            [
                'match' => '/rsm:CrossIndustryInvoice/rsm:SupplyChainTradeTransaction/ram:IncludedSupplyChainTradeLineItem/ram:SpecifiedTradeProduct/ram:OriginTradeCountry/ram:ID',
                'priority' => 0.5,
                'body' => [
                    [
                        'op' => 'element',
                        'name' => 'Item_country_of_origin',
                        'body' => [
                            [
                                'op' => 'attribute',
                                'name' => 'xr:id',
                                'value' => [
                                    'literal' => 'BT-159',
                                ],
                            ],
                            [
                                'op' => 'attribute',
                                'name' => 'xr:src',
                                'value' => [
                                    'srcpath' => [
                                        'xpath' => '.',
                                    ],
                                ],
                            ],
                            [
                                'op' => 'call',
                                'name' => 'code',
                                'params' => [],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        'BG-32' => [
            [
                'match' => '/rsm:CrossIndustryInvoice/rsm:SupplyChainTradeTransaction/ram:IncludedSupplyChainTradeLineItem/ram:SpecifiedTradeProduct/ram:ApplicableProductCharacteristic',
                'priority' => 0.5,
                'body' => [
                    [
                        'op' => 'variable',
                        'name' => 'bg-contents',
                        'body' => [
                            [
                                'op' => 'apply',
                                'mode' => 'BT-160',
                                'select' => [
                                    'xpath' => './ram:Description',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BT-161',
                                'select' => [
                                    'xpath' => './ram:Value',
                                ],
                            ],
                        ],
                    ],
                    [
                        'op' => 'if',
                        'test' => [
                            'var' => 'bg-contents',
                        ],
                        'body' => [
                            [
                                'op' => 'element',
                                'name' => 'ITEM_ATTRIBUTES',
                                'body' => [
                                    [
                                        'op' => 'attribute',
                                        'name' => 'xr:id',
                                        'value' => [
                                            'literal' => 'BG-32',
                                        ],
                                    ],
                                    [
                                        'op' => 'attribute',
                                        'name' => 'xr:src',
                                        'value' => [
                                            'srcpath' => [
                                                'xpath' => '.',
                                            ],
                                        ],
                                    ],
                                    [
                                        'op' => 'sequence',
                                        'var' => 'bg-contents',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        'BT-160' => [
            [
                'match' => '/rsm:CrossIndustryInvoice/rsm:SupplyChainTradeTransaction/ram:IncludedSupplyChainTradeLineItem/ram:SpecifiedTradeProduct/ram:ApplicableProductCharacteristic/ram:Description',
                'priority' => 0.5,
                'body' => [
                    [
                        'op' => 'element',
                        'name' => 'Item_attribute_name',
                        'body' => [
                            [
                                'op' => 'attribute',
                                'name' => 'xr:id',
                                'value' => [
                                    'literal' => 'BT-160',
                                ],
                            ],
                            [
                                'op' => 'attribute',
                                'name' => 'xr:src',
                                'value' => [
                                    'srcpath' => [
                                        'xpath' => '.',
                                    ],
                                ],
                            ],
                            [
                                'op' => 'call',
                                'name' => 'text',
                                'params' => [],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        'BT-161' => [
            [
                'match' => '/rsm:CrossIndustryInvoice/rsm:SupplyChainTradeTransaction/ram:IncludedSupplyChainTradeLineItem/ram:SpecifiedTradeProduct/ram:ApplicableProductCharacteristic/ram:Value',
                'priority' => 0.5,
                'body' => [
                    [
                        'op' => 'element',
                        'name' => 'Item_attribute_value',
                        'body' => [
                            [
                                'op' => 'attribute',
                                'name' => 'xr:id',
                                'value' => [
                                    'literal' => 'BT-161',
                                ],
                            ],
                            [
                                'op' => 'attribute',
                                'name' => 'xr:src',
                                'value' => [
                                    'srcpath' => [
                                        'xpath' => '.',
                                    ],
                                ],
                            ],
                            [
                                'op' => 'call',
                                'name' => 'text',
                                'params' => [],
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
    'named' => [
        'distinct-bt-7' => [
            'unsupported' => true,
        ],
        'distinct-bt-86' => [
            'unsupported' => true,
        ],
    ],
];
