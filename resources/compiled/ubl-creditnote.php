<?php

// KoSIT ubl-creditnote-xr.xsl compiled into an instruction tree for Dealerweb\EInvoice\Kosit\Transformer.
// Generated - do not edit.

return [
    'source' => 'ubl-creditnote-xr.xsl',
    'sha1' => 'd4465b26ebe5ef355421e4cc88515cb59a3abbeb',
    'namespaces' => [
        'CreditNote' => 'urn:oasis:names:specification:ubl:schema:xsd:CreditNote-2',
        'cac' => 'urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2',
        'cbc' => 'urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2',
        'xr' => 'urn:ce.eu:en16931:2017:xoev-de:kosit:standard:xrechnung-1',
    ],
    'indicators' => [
        'cbc:ChargeIndicator',
    ],
    'root' => [
        'match' => '/CreditNote:CreditNote',
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
                            'xpath' => './cbc:ID',
                        ],
                    ],
                    [
                        'op' => 'apply',
                        'mode' => 'BT-2',
                        'select' => [
                            'xpath' => './cbc:IssueDate',
                        ],
                    ],
                    [
                        'op' => 'apply',
                        'mode' => 'BT-3',
                        'select' => [
                            'xpath' => './cbc:CreditNoteTypeCode',
                        ],
                    ],
                    [
                        'op' => 'apply',
                        'mode' => 'BT-5',
                        'select' => [
                            'xpath' => './cbc:DocumentCurrencyCode',
                        ],
                    ],
                    [
                        'op' => 'apply',
                        'mode' => 'BT-6',
                        'select' => [
                            'xpath' => './cbc:TaxCurrencyCode',
                        ],
                    ],
                    [
                        'op' => 'apply',
                        'mode' => 'BT-7',
                        'select' => [
                            'xpath' => './cbc:TaxPointDate',
                        ],
                    ],
                    [
                        'op' => 'apply',
                        'mode' => 'BT-8',
                        'select' => [
                            'xpath' => './cac:InvoicePeriod/cbc:DescriptionCode',
                        ],
                    ],
                    [
                        'op' => 'apply',
                        'mode' => 'BT-9',
                        'select' => [
                            'xpath' => './cac:PaymentMeans/cbc:PaymentDueDate',
                        ],
                    ],
                    [
                        'op' => 'apply',
                        'mode' => 'BT-10',
                        'select' => [
                            'xpath' => './cbc:BuyerReference',
                        ],
                    ],
                    [
                        'op' => 'apply',
                        'mode' => 'BT-11',
                        'select' => [
                            'xpath' => './cac:AdditionalDocumentReference/cbc:ID[following-sibling::cbc:DocumentTypeCode = 50]',
                        ],
                    ],
                    [
                        'op' => 'apply',
                        'mode' => 'BT-12',
                        'select' => [
                            'xpath' => './cac:ContractDocumentReference/cbc:ID',
                        ],
                    ],
                    [
                        'op' => 'apply',
                        'mode' => 'BT-13',
                        'select' => [
                            'xpath' => './cac:OrderReference/cbc:ID',
                        ],
                    ],
                    [
                        'op' => 'apply',
                        'mode' => 'BT-14',
                        'select' => [
                            'xpath' => './cac:OrderReference/cbc:SalesOrderID',
                        ],
                    ],
                    [
                        'op' => 'apply',
                        'mode' => 'BT-15',
                        'select' => [
                            'xpath' => './cac:ReceiptDocumentReference/cbc:ID',
                        ],
                    ],
                    [
                        'op' => 'apply',
                        'mode' => 'BT-16',
                        'select' => [
                            'xpath' => './cac:DespatchDocumentReference/cbc:ID',
                        ],
                    ],
                    [
                        'op' => 'apply',
                        'mode' => 'BT-17',
                        'select' => [
                            'xpath' => './cac:OriginatorDocumentReference/cbc:ID',
                        ],
                    ],
                    [
                        'op' => 'apply',
                        'mode' => 'BT-18',
                        'select' => [
                            'xpath' => './cac:AdditionalDocumentReference/cbc:ID[following-sibling::cbc:DocumentTypeCode=\'130\']',
                        ],
                    ],
                    [
                        'op' => 'apply',
                        'mode' => 'BT-19',
                        'select' => [
                            'xpath' => './cbc:AccountingCost',
                        ],
                    ],
                    [
                        'op' => 'apply',
                        'mode' => 'BT-20',
                        'select' => [
                            'xpath' => './cac:PaymentTerms/cbc:Note',
                        ],
                    ],
                    [
                        'op' => 'special',
                        'key' => 'ubl-creditnote:root:BG-1',
                    ],
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
                                'op' => 'if',
                                'test' => [
                                    'xpath' => './cbc:ProfileID',
                                ],
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
                                                'op' => 'value-of',
                                                'select' => [
                                                    'xpath' => './cbc:ProfileID',
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
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
                                        'op' => 'value-of',
                                        'select' => [
                                            'xpath' => './cbc:CustomizationID',
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                    [
                        'op' => 'apply',
                        'mode' => 'BG-3',
                        'select' => [
                            'xpath' => './cac:BillingReference/cac:InvoiceDocumentReference',
                        ],
                    ],
                    [
                        'op' => 'apply',
                        'mode' => 'BG-4',
                        'select' => [
                            'xpath' => './cac:AccountingSupplierParty',
                        ],
                    ],
                    [
                        'op' => 'apply',
                        'mode' => 'BG-7',
                        'select' => [
                            'xpath' => './cac:AccountingCustomerParty',
                        ],
                    ],
                    [
                        'op' => 'apply',
                        'mode' => 'BG-10',
                        'select' => [
                            'xpath' => './cac:PayeeParty',
                        ],
                    ],
                    [
                        'op' => 'apply',
                        'mode' => 'BG-11',
                        'select' => [
                            'xpath' => './cac:TaxRepresentativeParty',
                        ],
                    ],
                    [
                        'op' => 'apply',
                        'mode' => 'BG-13',
                        'select' => [
                            'xpath' => './cac:Delivery',
                        ],
                    ],
                    [
                        'op' => 'apply',
                        'mode' => 'BG-14',
                        'select' => [
                            'xpath' => '/CreditNote:CreditNote/cac:InvoicePeriod',
                        ],
                    ],
                    [
                        'op' => 'special',
                        'key' => 'ubl-creditnote:root:BG-16',
                    ],
                    [
                        'op' => 'apply',
                        'mode' => 'BG-20',
                        'select' => [
                            'xpath' => './cac:AllowanceCharge[cbc:ChargeIndicator = \'false\']',
                        ],
                    ],
                    [
                        'op' => 'apply',
                        'mode' => 'BG-21',
                        'select' => [
                            'xpath' => './cac:AllowanceCharge[cbc:ChargeIndicator = \'true\']',
                        ],
                    ],
                    [
                        'op' => 'apply',
                        'mode' => 'BG-22',
                        'select' => [
                            'xpath' => './cac:LegalMonetaryTotal',
                        ],
                    ],
                    [
                        'op' => 'apply',
                        'mode' => 'BG-23',
                        'select' => [
                            'xpath' => './cac:TaxTotal/cac:TaxSubtotal',
                        ],
                    ],
                    [
                        'op' => 'apply',
                        'mode' => 'BG-24',
                        'select' => [
                            'xpath' => './cac:AdditionalDocumentReference[not(cbc:DocumentTypeCode = 50)]',
                        ],
                    ],
                    [
                        'op' => 'apply',
                        'mode' => 'BG-25',
                        'select' => [
                            'xpath' => './cac:CreditNoteLine',
                        ],
                    ],
                ],
            ],
        ],
    ],
    'modes' => [
        'BT-1' => [
            [
                'match' => '/CreditNote:CreditNote/cbc:ID',
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
                'match' => '/CreditNote:CreditNote/cbc:IssueDate',
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
                'match' => '/CreditNote:CreditNote/cbc:CreditNoteTypeCode',
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
                'match' => '/CreditNote:CreditNote/cbc:DocumentCurrencyCode',
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
                'match' => '/CreditNote:CreditNote/cbc:TaxCurrencyCode',
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
        'BT-7' => [
            [
                'match' => '/CreditNote:CreditNote/cbc:TaxPointDate',
                'priority' => 0.5,
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
        'BT-8' => [
            [
                'match' => '/CreditNote:CreditNote/cac:InvoicePeriod/cbc:DescriptionCode',
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
                'match' => '/CreditNote:CreditNote/cac:PaymentMeans/cbc:PaymentDueDate',
                'priority' => 0.5,
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
        'BT-10' => [
            [
                'match' => '/CreditNote:CreditNote/cbc:BuyerReference',
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
                'match' => '/CreditNote:CreditNote/cac:AdditionalDocumentReference/cbc:ID[following-sibling::cbc:DocumentTypeCode = 50]',
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
                'match' => '/CreditNote:CreditNote/cac:ContractDocumentReference/cbc:ID',
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
                'match' => '/CreditNote:CreditNote/cac:OrderReference/cbc:ID',
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
                'match' => '/CreditNote:CreditNote/cac:OrderReference/cbc:SalesOrderID',
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
                'match' => '/CreditNote:CreditNote/cac:ReceiptDocumentReference/cbc:ID',
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
                'match' => '/CreditNote:CreditNote/cac:DespatchDocumentReference/cbc:ID',
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
                'match' => '/CreditNote:CreditNote/cac:OriginatorDocumentReference/cbc:ID',
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
                'match' => '/CreditNote:CreditNote/cac:AdditionalDocumentReference/cbc:ID[following-sibling::cbc:DocumentTypeCode=\'130\']',
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
                                'name' => 'identifier',
                                'params' => [],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        'BT-19' => [
            [
                'match' => '/CreditNote:CreditNote/cbc:AccountingCost',
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
                'match' => '/CreditNote:CreditNote/cac:PaymentTerms/cbc:Note',
                'priority' => 0.5,
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
        'BG-3' => [
            [
                'match' => '/CreditNote:CreditNote/cac:BillingReference/cac:InvoiceDocumentReference',
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
                                    'xpath' => './cbc:ID',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BT-26',
                                'select' => [
                                    'xpath' => './cbc:IssueDate',
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
                'match' => '/CreditNote:CreditNote/cac:BillingReference/cac:InvoiceDocumentReference/cbc:ID',
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
                'match' => '/CreditNote:CreditNote/cac:BillingReference/cac:InvoiceDocumentReference/cbc:IssueDate',
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
                'match' => '/CreditNote:CreditNote/cac:AccountingSupplierParty',
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
                                    'xpath' => './cac:Party/cac:PartyLegalEntity/cbc:RegistrationName',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BT-28',
                                'select' => [
                                    'xpath' => './cac:Party/cac:PartyName/cbc:Name',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BT-29',
                                'select' => [
                                    'xpath' => './cac:Party/cac:PartyIdentification/cbc:ID[not(@schemeID = \'SEPA\')]',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BT-30',
                                'select' => [
                                    'xpath' => './cac:Party/cac:PartyLegalEntity/cbc:CompanyID',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BT-31',
                                'select' => [
                                    'xpath' => './cac:Party/cac:PartyTaxScheme/cbc:CompanyID[following-sibling::cac:TaxScheme/cbc:ID = \'VAT\']',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BT-32',
                                'select' => [
                                    'xpath' => './cac:Party/cac:PartyTaxScheme/cbc:CompanyID[following-sibling::cac:TaxScheme/cbc:ID != \'VAT\']',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BT-33',
                                'select' => [
                                    'xpath' => './cac:Party/cac:PartyLegalEntity/cbc:CompanyLegalForm',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BT-34',
                                'select' => [
                                    'xpath' => './cac:Party/cbc:EndpointID',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BG-5',
                                'select' => [
                                    'xpath' => './cac:Party/cac:PostalAddress',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BG-6',
                                'select' => [
                                    'xpath' => './cac:Party/cac:Contact',
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
                'match' => '/CreditNote:CreditNote/cac:AccountingSupplierParty/cac:Party/cac:PartyLegalEntity/cbc:RegistrationName',
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
                'match' => '/CreditNote:CreditNote/cac:AccountingSupplierParty/cac:Party/cac:PartyName/cbc:Name',
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
                'match' => '/CreditNote:CreditNote/cac:AccountingSupplierParty/cac:Party/cac:PartyIdentification/cbc:ID[not(@schemeID = \'SEPA\')]',
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
                'match' => '/CreditNote:CreditNote/cac:AccountingSupplierParty/cac:Party/cac:PartyLegalEntity/cbc:CompanyID',
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
                'match' => '/CreditNote:CreditNote/cac:AccountingSupplierParty/cac:Party/cac:PartyTaxScheme/cbc:CompanyID[following-sibling::cac:TaxScheme/cbc:ID = \'VAT\']',
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
                'match' => '/CreditNote:CreditNote/cac:AccountingSupplierParty/cac:Party/cac:PartyTaxScheme/cbc:CompanyID[following-sibling::cac:TaxScheme/cbc:ID != \'VAT\']',
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
                'match' => '/CreditNote:CreditNote/cac:AccountingSupplierParty/cac:Party/cac:PartyLegalEntity/cbc:CompanyLegalForm',
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
                'match' => '/CreditNote:CreditNote/cac:AccountingSupplierParty/cac:Party/cbc:EndpointID',
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
                'match' => '/CreditNote:CreditNote/cac:AccountingSupplierParty/cac:Party/cac:PostalAddress',
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
                                    'xpath' => './cbc:StreetName',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BT-36',
                                'select' => [
                                    'xpath' => './cbc:AdditionalStreetName',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BT-162',
                                'select' => [
                                    'xpath' => './cac:AddressLine/cbc:Line',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BT-37',
                                'select' => [
                                    'xpath' => './cbc:CityName',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BT-38',
                                'select' => [
                                    'xpath' => './cbc:PostalZone',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BT-39',
                                'select' => [
                                    'xpath' => './cbc:CountrySubentity',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BT-40',
                                'select' => [
                                    'xpath' => './cac:Country/cbc:IdentificationCode',
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
                'match' => '/CreditNote:CreditNote/cac:AccountingSupplierParty/cac:Party/cac:PostalAddress/cbc:StreetName',
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
                'match' => '/CreditNote:CreditNote/cac:AccountingSupplierParty/cac:Party/cac:PostalAddress/cbc:AdditionalStreetName',
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
                'match' => '/CreditNote:CreditNote/cac:AccountingSupplierParty/cac:Party/cac:PostalAddress/cac:AddressLine/cbc:Line',
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
                'match' => '/CreditNote:CreditNote/cac:AccountingSupplierParty/cac:Party/cac:PostalAddress/cbc:CityName',
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
                'match' => '/CreditNote:CreditNote/cac:AccountingSupplierParty/cac:Party/cac:PostalAddress/cbc:PostalZone',
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
                'match' => '/CreditNote:CreditNote/cac:AccountingSupplierParty/cac:Party/cac:PostalAddress/cbc:CountrySubentity',
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
                'match' => '/CreditNote:CreditNote/cac:AccountingSupplierParty/cac:Party/cac:PostalAddress/cac:Country/cbc:IdentificationCode',
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
                'match' => '/CreditNote:CreditNote/cac:AccountingSupplierParty/cac:Party/cac:Contact',
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
                                    'xpath' => './cbc:Name',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BT-42',
                                'select' => [
                                    'xpath' => './cbc:Telephone',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BT-43',
                                'select' => [
                                    'xpath' => './cbc:ElectronicMail',
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
                'match' => '/CreditNote:CreditNote/cac:AccountingSupplierParty/cac:Party/cac:Contact/cbc:Name',
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
                'match' => '/CreditNote:CreditNote/cac:AccountingSupplierParty/cac:Party/cac:Contact/cbc:Telephone',
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
                'match' => '/CreditNote:CreditNote/cac:AccountingSupplierParty/cac:Party/cac:Contact/cbc:ElectronicMail',
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
                'match' => '/CreditNote:CreditNote/cac:AccountingCustomerParty',
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
                                    'xpath' => './cac:Party/cac:PartyLegalEntity/cbc:RegistrationName',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BT-45',
                                'select' => [
                                    'xpath' => './cac:Party/cac:PartyName/cbc:Name',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BT-46',
                                'select' => [
                                    'xpath' => './cac:Party/cac:PartyIdentification/cbc:ID',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BT-47',
                                'select' => [
                                    'xpath' => './cac:Party/cac:PartyLegalEntity/cbc:CompanyID',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BT-48',
                                'select' => [
                                    'xpath' => './cac:Party/cac:PartyTaxScheme/cbc:CompanyID[following-sibling::cac:TaxScheme/cbc:ID = \'VAT\']',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BT-49',
                                'select' => [
                                    'xpath' => './cac:Party/cbc:EndpointID',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BG-8',
                                'select' => [
                                    'xpath' => './cac:Party/cac:PostalAddress',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BG-9',
                                'select' => [
                                    'xpath' => './cac:Party/cac:Contact',
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
                'match' => '/CreditNote:CreditNote/cac:AccountingCustomerParty/cac:Party/cac:PartyLegalEntity/cbc:RegistrationName',
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
                'match' => '/CreditNote:CreditNote/cac:AccountingCustomerParty/cac:Party/cac:PartyName/cbc:Name',
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
                'match' => '/CreditNote:CreditNote/cac:AccountingCustomerParty/cac:Party/cac:PartyIdentification/cbc:ID',
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
                'match' => '/CreditNote:CreditNote/cac:AccountingCustomerParty/cac:Party/cac:PartyLegalEntity/cbc:CompanyID',
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
                'match' => '/CreditNote:CreditNote/cac:AccountingCustomerParty/cac:Party/cac:PartyTaxScheme/cbc:CompanyID[following-sibling::cac:TaxScheme/cbc:ID = \'VAT\']',
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
                'match' => '/CreditNote:CreditNote/cac:AccountingCustomerParty/cac:Party/cbc:EndpointID',
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
                'match' => '/CreditNote:CreditNote/cac:AccountingCustomerParty/cac:Party/cac:PostalAddress',
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
                                    'xpath' => './cbc:StreetName',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BT-51',
                                'select' => [
                                    'xpath' => './cbc:AdditionalStreetName',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BT-163',
                                'select' => [
                                    'xpath' => './cac:AddressLine/cbc:Line',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BT-52',
                                'select' => [
                                    'xpath' => './cbc:CityName',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BT-53',
                                'select' => [
                                    'xpath' => './cbc:PostalZone',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BT-54',
                                'select' => [
                                    'xpath' => './cbc:CountrySubentity',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BT-55',
                                'select' => [
                                    'xpath' => './cac:Country/cbc:IdentificationCode',
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
                'match' => '/CreditNote:CreditNote/cac:AccountingCustomerParty/cac:Party/cac:PostalAddress/cbc:StreetName',
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
                'match' => '/CreditNote:CreditNote/cac:AccountingCustomerParty/cac:Party/cac:PostalAddress/cbc:AdditionalStreetName',
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
                'match' => '/CreditNote:CreditNote/cac:AccountingCustomerParty/cac:Party/cac:PostalAddress/cac:AddressLine/cbc:Line',
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
                'match' => '/CreditNote:CreditNote/cac:AccountingCustomerParty/cac:Party/cac:PostalAddress/cbc:CityName',
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
                'match' => '/CreditNote:CreditNote/cac:AccountingCustomerParty/cac:Party/cac:PostalAddress/cbc:PostalZone',
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
                'match' => '/CreditNote:CreditNote/cac:AccountingCustomerParty/cac:Party/cac:PostalAddress/cbc:CountrySubentity',
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
                'match' => '/CreditNote:CreditNote/cac:AccountingCustomerParty/cac:Party/cac:PostalAddress/cac:Country/cbc:IdentificationCode',
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
                'match' => '/CreditNote:CreditNote/cac:AccountingCustomerParty/cac:Party/cac:Contact',
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
                                    'xpath' => './cbc:Name',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BT-57',
                                'select' => [
                                    'xpath' => './cbc:Telephone',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BT-58',
                                'select' => [
                                    'xpath' => './cbc:ElectronicMail',
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
                'match' => '/CreditNote:CreditNote/cac:AccountingCustomerParty/cac:Party/cac:Contact/cbc:Name',
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
                'match' => '/CreditNote:CreditNote/cac:AccountingCustomerParty/cac:Party/cac:Contact/cbc:Telephone',
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
                'match' => '/CreditNote:CreditNote/cac:AccountingCustomerParty/cac:Party/cac:Contact/cbc:ElectronicMail',
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
                'match' => '/CreditNote:CreditNote/cac:PayeeParty',
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
                                    'xpath' => './cac:PartyName/cbc:Name',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BT-60',
                                'select' => [
                                    'xpath' => './cac:PartyIdentification/cbc:ID[not(@schemeID = \'SEPA\')]',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BT-61',
                                'select' => [
                                    'xpath' => './cac:PartyLegalEntity/cbc:CompanyID',
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
                'match' => '/CreditNote:CreditNote/cac:PayeeParty/cac:PartyName/cbc:Name',
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
                'match' => '/CreditNote:CreditNote/cac:PayeeParty/cac:PartyIdentification/cbc:ID[not(@schemeID = \'SEPA\')]',
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
                'match' => '/CreditNote:CreditNote/cac:PayeeParty/cac:PartyLegalEntity/cbc:CompanyID',
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
                'match' => '/CreditNote:CreditNote/cac:TaxRepresentativeParty',
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
                                    'xpath' => './cac:PartyName/cbc:Name',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BT-63',
                                'select' => [
                                    'xpath' => './cac:PartyTaxScheme/cbc:CompanyID[following-sibling::cac:TaxScheme/cbc:ID = \'VAT\']',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BG-12',
                                'select' => [
                                    'xpath' => './cac:PostalAddress',
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
                'match' => '/CreditNote:CreditNote/cac:TaxRepresentativeParty/cac:PartyName/cbc:Name',
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
                'match' => '/CreditNote:CreditNote/cac:TaxRepresentativeParty/cac:PartyTaxScheme/cbc:CompanyID[following-sibling::cac:TaxScheme/cbc:ID = \'VAT\']',
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
                'match' => '/CreditNote:CreditNote/cac:TaxRepresentativeParty/cac:PostalAddress',
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
                                    'xpath' => './cbc:StreetName',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BT-65',
                                'select' => [
                                    'xpath' => './cbc:AdditionalStreetName',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BT-164',
                                'select' => [
                                    'xpath' => './cac:AddressLine/cbc:Line',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BT-66',
                                'select' => [
                                    'xpath' => './cbc:CityName',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BT-67',
                                'select' => [
                                    'xpath' => './cbc:PostalZone',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BT-68',
                                'select' => [
                                    'xpath' => './cbc:CountrySubentity',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BT-69',
                                'select' => [
                                    'xpath' => './cac:Country/cbc:IdentificationCode',
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
                'match' => '/CreditNote:CreditNote/cac:TaxRepresentativeParty/cac:PostalAddress/cbc:StreetName',
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
                'match' => '/CreditNote:CreditNote/cac:TaxRepresentativeParty/cac:PostalAddress/cbc:AdditionalStreetName',
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
                'match' => '/CreditNote:CreditNote/cac:TaxRepresentativeParty/cac:PostalAddress/cac:AddressLine/cbc:Line',
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
                'match' => '/CreditNote:CreditNote/cac:TaxRepresentativeParty/cac:PostalAddress/cbc:CityName',
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
                'match' => '/CreditNote:CreditNote/cac:TaxRepresentativeParty/cac:PostalAddress/cbc:PostalZone',
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
                'match' => '/CreditNote:CreditNote/cac:TaxRepresentativeParty/cac:PostalAddress/cbc:CountrySubentity',
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
                'match' => '/CreditNote:CreditNote/cac:TaxRepresentativeParty/cac:PostalAddress/cac:Country/cbc:IdentificationCode',
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
                'match' => '/CreditNote:CreditNote/cac:Delivery',
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
                                    'xpath' => './cac:DeliveryParty/cac:PartyName/cbc:Name',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BT-71',
                                'select' => [
                                    'xpath' => './cac:DeliveryLocation/cbc:ID',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BT-72',
                                'select' => [
                                    'xpath' => './cbc:ActualDeliveryDate',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BG-15',
                                'select' => [
                                    'xpath' => './cac:DeliveryLocation/cac:Address',
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
                'match' => '/CreditNote:CreditNote/cac:Delivery/cac:DeliveryParty/cac:PartyName/cbc:Name',
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
                'match' => '/CreditNote:CreditNote/cac:Delivery/cac:DeliveryLocation/cbc:ID',
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
                'match' => '/CreditNote:CreditNote/cac:Delivery/cbc:ActualDeliveryDate',
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
                'match' => '/CreditNote:CreditNote/cac:InvoicePeriod',
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
                                    'xpath' => './cbc:StartDate',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BT-74',
                                'select' => [
                                    'xpath' => './cbc:EndDate',
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
                'match' => '/CreditNote:CreditNote/cac:InvoicePeriod/cbc:StartDate',
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
                'match' => '/CreditNote:CreditNote/cac:InvoicePeriod/cbc:EndDate',
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
                'match' => '/CreditNote:CreditNote/cac:Delivery/cac:DeliveryLocation/cac:Address',
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
                                    'xpath' => './cbc:StreetName',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BT-76',
                                'select' => [
                                    'xpath' => './cbc:AdditionalStreetName',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BT-165',
                                'select' => [
                                    'xpath' => './cac:AddressLine/cbc:Line',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BT-77',
                                'select' => [
                                    'xpath' => './cbc:CityName',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BT-78',
                                'select' => [
                                    'xpath' => './cbc:PostalZone',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BT-79',
                                'select' => [
                                    'xpath' => './cbc:CountrySubentity',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BT-80',
                                'select' => [
                                    'xpath' => './cac:Country/cbc:IdentificationCode',
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
                'match' => '/CreditNote:CreditNote/cac:Delivery/cac:DeliveryLocation/cac:Address/cbc:StreetName',
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
                'match' => '/CreditNote:CreditNote/cac:Delivery/cac:DeliveryLocation/cac:Address/cbc:AdditionalStreetName',
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
                'match' => '/CreditNote:CreditNote/cac:Delivery/cac:DeliveryLocation/cac:Address/cac:AddressLine/cbc:Line',
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
                'match' => '/CreditNote:CreditNote/cac:Delivery/cac:DeliveryLocation/cac:Address/cbc:CityName',
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
                'match' => '/CreditNote:CreditNote/cac:Delivery/cac:DeliveryLocation/cac:Address/cbc:PostalZone',
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
                'match' => '/CreditNote:CreditNote/cac:Delivery/cac:DeliveryLocation/cac:Address/cbc:CountrySubentity',
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
                'match' => '/CreditNote:CreditNote/cac:Delivery/cac:DeliveryLocation/cac:Address/cac:Country/cbc:IdentificationCode',
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
        'BT-81' => [
            [
                'match' => '/CreditNote:CreditNote/cac:PaymentMeans/cbc:PaymentMeansCode',
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
                'match' => '/CreditNote:CreditNote/cac:PaymentMeans/cbc:PaymentMeansCode/@name',
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
                'match' => '/CreditNote:CreditNote/cac:PaymentMeans/cbc:PaymentID',
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
                'match' => '/CreditNote:CreditNote/cac:PaymentMeans/cac:PayeeFinancialAccount',
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
                                    'xpath' => './cbc:ID',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BT-85',
                                'select' => [
                                    'xpath' => './cbc:Name',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BT-86',
                                'select' => [
                                    'xpath' => './cac:FinancialInstitutionBranch/cbc:ID',
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
                'match' => '/CreditNote:CreditNote/cac:PaymentMeans/cac:PayeeFinancialAccount/cbc:ID',
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
                'match' => '/CreditNote:CreditNote/cac:PaymentMeans/cac:PayeeFinancialAccount/cbc:Name',
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
                'match' => '/CreditNote:CreditNote/cac:PaymentMeans/cac:PayeeFinancialAccount/cac:FinancialInstitutionBranch/cbc:ID',
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
                                'name' => 'identifier',
                                'params' => [],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        'BG-18' => [
            [
                'match' => '/CreditNote:CreditNote/cac:PaymentMeans/cac:CardAccount',
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
                                    'xpath' => './cbc:PrimaryAccountNumberID',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BT-88',
                                'select' => [
                                    'xpath' => './cbc:HolderName',
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
                'match' => '/CreditNote:CreditNote/cac:PaymentMeans/cac:CardAccount/cbc:PrimaryAccountNumberID',
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
                'match' => '/CreditNote:CreditNote/cac:PaymentMeans/cac:CardAccount/cbc:HolderName',
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
                'match' => '/CreditNote:CreditNote/cac:PaymentMeans/cac:PaymentMandate',
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
                                    'xpath' => './cbc:ID',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BT-90',
                                'select' => [
                                    'xpath' => '/CreditNote:CreditNote/cac:PayeeParty/cac:PartyIdentification/cbc:ID[@schemeID = \'SEPA\']',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BT-90',
                                'select' => [
                                    'xpath' => '/CreditNote:CreditNote/cac:AccountingSupplierParty/cac:Party/cac:PartyIdentification/cbc:ID[@schemeID = \'SEPA\']',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BT-91',
                                'select' => [
                                    'xpath' => './cac:PayerFinancialAccount/cbc:ID',
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
                'match' => '/CreditNote:CreditNote/cac:PaymentMeans/cac:PaymentMandate/cbc:ID',
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
                'match' => '/CreditNote:CreditNote/cac:PayeeParty/cac:PartyIdentification/cbc:ID[@schemeID = \'SEPA\']',
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
            [
                'match' => '/CreditNote:CreditNote/cac:AccountingSupplierParty/cac:Party/cac:PartyIdentification/cbc:ID[@schemeID = \'SEPA\']',
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
                'match' => '/CreditNote:CreditNote/cac:PaymentMeans/cac:PaymentMandate/cac:PayerFinancialAccount/cbc:ID',
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
                'match' => '/CreditNote:CreditNote/cac:AllowanceCharge[cbc:ChargeIndicator = \'false\']',
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
                                    'xpath' => './cbc:Amount[preceding-sibling::cbc:ChargeIndicator = \'false\']',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BT-93',
                                'select' => [
                                    'xpath' => './cbc:BaseAmount[preceding-sibling::cbc:ChargeIndicator = \'false\']',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BT-94',
                                'select' => [
                                    'xpath' => './cbc:MultiplierFactorNumeric[preceding-sibling::cbc:ChargeIndicator = \'false\']',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BT-95',
                                'select' => [
                                    'xpath' => './cac:TaxCategory/cbc:ID[ancestor::cac:AllowanceCharge/cbc:ChargeIndicator = \'false\' and following-sibling::cac:TaxScheme/cbc:ID = \'VAT\']',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BT-96',
                                'select' => [
                                    'xpath' => './cac:TaxCategory/cbc:Percent[ancestor::cac:AllowanceCharge/cbc:ChargeIndicator = \'false\']',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BT-97',
                                'select' => [
                                    'xpath' => './cbc:AllowanceChargeReason[preceding-sibling::cbc:ChargeIndicator = \'false\']',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BT-98',
                                'select' => [
                                    'xpath' => './cbc:AllowanceChargeReasonCode[preceding-sibling::cbc:ChargeIndicator = \'false\']',
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
                'match' => '/CreditNote:CreditNote/cac:AllowanceCharge/cbc:Amount[preceding-sibling::cbc:ChargeIndicator = \'false\']',
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
                'match' => '/CreditNote:CreditNote/cac:AllowanceCharge/cbc:BaseAmount[preceding-sibling::cbc:ChargeIndicator = \'false\']',
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
                'match' => '/CreditNote:CreditNote/cac:AllowanceCharge/cbc:MultiplierFactorNumeric[preceding-sibling::cbc:ChargeIndicator = \'false\']',
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
                'match' => '/CreditNote:CreditNote/cac:AllowanceCharge/cac:TaxCategory/cbc:ID[ancestor::cac:AllowanceCharge/cbc:ChargeIndicator = \'false\' and following-sibling::cac:TaxScheme/cbc:ID = \'VAT\']',
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
                'match' => '/CreditNote:CreditNote/cac:AllowanceCharge/cac:TaxCategory/cbc:Percent[ancestor::cac:AllowanceCharge/cbc:ChargeIndicator = \'false\']',
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
                'match' => '/CreditNote:CreditNote/cac:AllowanceCharge/cbc:AllowanceChargeReason[preceding-sibling::cbc:ChargeIndicator = \'false\']',
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
                'match' => '/CreditNote:CreditNote/cac:AllowanceCharge/cbc:AllowanceChargeReasonCode[preceding-sibling::cbc:ChargeIndicator = \'false\']',
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
                'match' => '/CreditNote:CreditNote/cac:AllowanceCharge[cbc:ChargeIndicator = \'true\']',
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
                                    'xpath' => './cbc:Amount[preceding-sibling::cbc:ChargeIndicator = \'true\']',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BT-100',
                                'select' => [
                                    'xpath' => './cbc:BaseAmount[preceding-sibling::cbc:ChargeIndicator = \'true\']',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BT-101',
                                'select' => [
                                    'xpath' => './cbc:MultiplierFactorNumeric[preceding-sibling::cbc:ChargeIndicator = \'true\']',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BT-102',
                                'select' => [
                                    'xpath' => './cac:TaxCategory/cbc:ID[ancestor::cac:AllowanceCharge/cbc:ChargeIndicator = \'true\']',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BT-103',
                                'select' => [
                                    'xpath' => './cac:TaxCategory/cbc:Percent[ancestor::cac:AllowanceCharge/cbc:ChargeIndicator = \'true\']',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BT-104',
                                'select' => [
                                    'xpath' => './cbc:AllowanceChargeReason[preceding-sibling::cbc:ChargeIndicator = \'true\']',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BT-105',
                                'select' => [
                                    'xpath' => './cbc:AllowanceChargeReasonCode[preceding-sibling::cbc:ChargeIndicator = \'true\']',
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
                'match' => '/CreditNote:CreditNote/cac:AllowanceCharge/cbc:Amount[preceding-sibling::cbc:ChargeIndicator = \'true\']',
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
                'match' => '/CreditNote:CreditNote/cac:AllowanceCharge/cbc:BaseAmount[preceding-sibling::cbc:ChargeIndicator = \'true\']',
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
                'match' => '/CreditNote:CreditNote/cac:AllowanceCharge/cbc:MultiplierFactorNumeric[preceding-sibling::cbc:ChargeIndicator = \'true\']',
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
                'match' => '/CreditNote:CreditNote/cac:AllowanceCharge/cac:TaxCategory/cbc:ID[ancestor::cac:AllowanceCharge/cbc:ChargeIndicator = \'true\']',
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
                'match' => '/CreditNote:CreditNote/cac:AllowanceCharge/cac:TaxCategory/cbc:Percent[ancestor::cac:AllowanceCharge/cbc:ChargeIndicator = \'true\']',
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
                'match' => '/CreditNote:CreditNote/cac:AllowanceCharge/cbc:AllowanceChargeReason[preceding-sibling::cbc:ChargeIndicator = \'true\']',
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
                'match' => '/CreditNote:CreditNote/cac:AllowanceCharge/cbc:AllowanceChargeReasonCode[preceding-sibling::cbc:ChargeIndicator = \'true\']',
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
                'match' => '/CreditNote:CreditNote/cac:LegalMonetaryTotal',
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
                                    'xpath' => './cbc:LineExtensionAmount',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BT-107',
                                'select' => [
                                    'xpath' => './cbc:AllowanceTotalAmount',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BT-108',
                                'select' => [
                                    'xpath' => './cbc:ChargeTotalAmount',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BT-109',
                                'select' => [
                                    'xpath' => './cbc:TaxExclusiveAmount',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BT-110',
                                'select' => [
                                    'xpath' => '/CreditNote:CreditNote/cac:TaxTotal/cbc:TaxAmount[/CreditNote:CreditNote/cbc:DocumentCurrencyCode = @currencyID]',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BT-111',
                                'select' => [
                                    'xpath' => '/CreditNote:CreditNote/cac:TaxTotal/cbc:TaxAmount[/CreditNote:CreditNote/cbc:TaxCurrencyCode = @currencyID]',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BT-112',
                                'select' => [
                                    'xpath' => './cbc:TaxInclusiveAmount',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BT-113',
                                'select' => [
                                    'xpath' => './cbc:PrepaidAmount',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BT-114',
                                'select' => [
                                    'xpath' => './cbc:PayableRoundingAmount',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BT-115',
                                'select' => [
                                    'xpath' => './cbc:PayableAmount',
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
                'match' => '/CreditNote:CreditNote/cac:LegalMonetaryTotal/cbc:LineExtensionAmount',
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
                'match' => '/CreditNote:CreditNote/cac:LegalMonetaryTotal/cbc:AllowanceTotalAmount',
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
                'match' => '/CreditNote:CreditNote/cac:LegalMonetaryTotal/cbc:ChargeTotalAmount',
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
                'match' => '/CreditNote:CreditNote/cac:LegalMonetaryTotal/cbc:TaxExclusiveAmount',
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
                'match' => '/CreditNote:CreditNote/cac:TaxTotal/cbc:TaxAmount',
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
                'match' => '/CreditNote:CreditNote/cac:TaxTotal/cbc:TaxAmount',
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
                'match' => '/CreditNote:CreditNote/cac:LegalMonetaryTotal/cbc:TaxInclusiveAmount',
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
                'match' => '/CreditNote:CreditNote/cac:LegalMonetaryTotal/cbc:PrepaidAmount',
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
                'match' => '/CreditNote:CreditNote/cac:LegalMonetaryTotal/cbc:PayableRoundingAmount',
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
                'match' => '/CreditNote:CreditNote/cac:LegalMonetaryTotal/cbc:PayableAmount',
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
                'match' => '/CreditNote:CreditNote/cac:TaxTotal/cac:TaxSubtotal',
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
                                    'xpath' => './cbc:TaxableAmount',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BT-117',
                                'select' => [
                                    'xpath' => './cbc:TaxAmount',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BT-118',
                                'select' => [
                                    'xpath' => './cac:TaxCategory/cbc:ID[following-sibling::cac:TaxScheme/cbc:ID = \'VAT\']',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BT-119',
                                'select' => [
                                    'xpath' => './cac:TaxCategory/cbc:Percent',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BT-120',
                                'select' => [
                                    'xpath' => './cac:TaxCategory/cbc:TaxExemptionReason',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BT-121',
                                'select' => [
                                    'xpath' => './cac:TaxCategory/cbc:TaxExemptionReasonCode',
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
                'match' => '/CreditNote:CreditNote/cac:TaxTotal/cac:TaxSubtotal/cbc:TaxableAmount',
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
                'match' => '/CreditNote:CreditNote/cac:TaxTotal/cac:TaxSubtotal/cbc:TaxAmount',
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
                'match' => '/CreditNote:CreditNote/cac:TaxTotal/cac:TaxSubtotal/cac:TaxCategory/cbc:ID[following-sibling::cac:TaxScheme/cbc:ID = \'VAT\']',
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
                'match' => '/CreditNote:CreditNote/cac:TaxTotal/cac:TaxSubtotal/cac:TaxCategory/cbc:Percent',
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
                'match' => '/CreditNote:CreditNote/cac:TaxTotal/cac:TaxSubtotal/cac:TaxCategory/cbc:TaxExemptionReason',
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
                'match' => '/CreditNote:CreditNote/cac:TaxTotal/cac:TaxSubtotal/cac:TaxCategory/cbc:TaxExemptionReasonCode',
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
                'match' => '/CreditNote:CreditNote/cac:AdditionalDocumentReference[not(cbc:DocumentTypeCode = 50)]',
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
                                    'xpath' => './cbc:ID',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BT-123',
                                'select' => [
                                    'xpath' => './cbc:DocumentDescription',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BT-124',
                                'select' => [
                                    'xpath' => './cac:Attachment/cac:ExternalReference/cbc:URI',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BT-125',
                                'select' => [
                                    'xpath' => './cac:Attachment/cbc:EmbeddedDocumentBinaryObject',
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
                'match' => '/CreditNote:CreditNote/cac:AdditionalDocumentReference/cbc:ID',
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
                'match' => '/CreditNote:CreditNote/cac:AdditionalDocumentReference/cbc:DocumentDescription',
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
                'match' => '/CreditNote:CreditNote/cac:AdditionalDocumentReference/cac:Attachment/cac:ExternalReference/cbc:URI',
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
                'match' => '/CreditNote:CreditNote/cac:AdditionalDocumentReference/cac:Attachment/cbc:EmbeddedDocumentBinaryObject',
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
                'match' => '/CreditNote:CreditNote/cac:CreditNoteLine',
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
                                    'xpath' => './cbc:ID',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BT-127',
                                'select' => [
                                    'xpath' => './cbc:Note',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BT-128',
                                'select' => [
                                    'xpath' => './cac:DocumentReference/cbc:ID[following-sibling::cbc:DocumentTypeCode = \'130\']',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BT-129',
                                'select' => [
                                    'xpath' => './cbc:CreditedQuantity',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BT-130',
                                'select' => [
                                    'xpath' => './cbc:CreditedQuantity/@unitCode',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BT-131',
                                'select' => [
                                    'xpath' => './cbc:LineExtensionAmount',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BT-132',
                                'select' => [
                                    'xpath' => './cac:OrderLineReference/cbc:LineID',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BT-133',
                                'select' => [
                                    'xpath' => './cbc:AccountingCost',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BG-26',
                                'select' => [
                                    'xpath' => './cac:InvoicePeriod',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BG-27',
                                'select' => [
                                    'xpath' => './cac:AllowanceCharge[cbc:ChargeIndicator = \'false\']',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BG-28',
                                'select' => [
                                    'xpath' => './cac:AllowanceCharge[cbc:ChargeIndicator = \'true\']',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BG-29',
                                'select' => [
                                    'xpath' => './cac:Price',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BG-30',
                                'select' => [
                                    'xpath' => './cac:Item/cac:ClassifiedTaxCategory',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BG-31',
                                'select' => [
                                    'xpath' => './cac:Item',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BG-DEX-01',
                                'select' => [
                                    'xpath' => 'cac:SubCreditNoteLine',
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
                'match' => '/CreditNote:CreditNote/cac:CreditNoteLine/cbc:ID',
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
                'match' => '/CreditNote:CreditNote/cac:CreditNoteLine/cbc:Note',
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
                'match' => '/CreditNote:CreditNote/cac:CreditNoteLine/cac:DocumentReference/cbc:ID[following-sibling::cbc:DocumentTypeCode = \'130\']',
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
                                'name' => 'identifier',
                                'params' => [],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        'BT-129' => [
            [
                'match' => '/CreditNote:CreditNote/cac:CreditNoteLine/cbc:CreditedQuantity',
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
                'match' => '/CreditNote:CreditNote/cac:CreditNoteLine/cbc:CreditedQuantity/@unitCode',
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
                'match' => '/CreditNote:CreditNote/cac:CreditNoteLine/cbc:LineExtensionAmount',
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
                'match' => '/CreditNote:CreditNote/cac:CreditNoteLine/cac:OrderLineReference/cbc:LineID',
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
                'match' => '/CreditNote:CreditNote/cac:CreditNoteLine/cbc:AccountingCost',
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
                'match' => '/CreditNote:CreditNote/cac:CreditNoteLine/cac:InvoicePeriod',
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
                                    'xpath' => './cbc:StartDate',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BT-135',
                                'select' => [
                                    'xpath' => './cbc:EndDate',
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
                'match' => '/CreditNote:CreditNote/cac:CreditNoteLine/cac:InvoicePeriod/cbc:StartDate',
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
                'match' => '/CreditNote:CreditNote/cac:CreditNoteLine/cac:InvoicePeriod/cbc:EndDate',
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
                'match' => '/CreditNote:CreditNote/cac:CreditNoteLine/cac:AllowanceCharge[cbc:ChargeIndicator = \'false\']',
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
                                    'xpath' => './cbc:Amount[preceding-sibling::cbc:ChargeIndicator = \'false\']',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BT-137',
                                'select' => [
                                    'xpath' => './cbc:BaseAmount[preceding-sibling::cbc:ChargeIndicator = \'false\']',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BT-138',
                                'select' => [
                                    'xpath' => './cbc:MultiplierFactorNumeric[preceding-sibling::cbc:ChargeIndicator = \'false\']',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BT-139',
                                'select' => [
                                    'xpath' => './cbc:AllowanceChargeReason[preceding-sibling::cbc:ChargeIndicator = \'false\']',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BT-140',
                                'select' => [
                                    'xpath' => './cbc:AllowanceChargeReasonCode[preceding-sibling::cbc:ChargeIndicator = \'false\']',
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
                'match' => '/CreditNote:CreditNote/cac:CreditNoteLine/cac:AllowanceCharge/cbc:Amount[preceding-sibling::cbc:ChargeIndicator = \'false\']',
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
                'match' => '/CreditNote:CreditNote/cac:CreditNoteLine/cac:AllowanceCharge/cbc:BaseAmount[preceding-sibling::cbc:ChargeIndicator = \'false\']',
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
                'match' => '/CreditNote:CreditNote/cac:CreditNoteLine/cac:AllowanceCharge/cbc:MultiplierFactorNumeric[preceding-sibling::cbc:ChargeIndicator = \'false\']',
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
                'match' => '/CreditNote:CreditNote/cac:CreditNoteLine/cac:AllowanceCharge/cbc:AllowanceChargeReason[preceding-sibling::cbc:ChargeIndicator = \'false\']',
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
                'match' => '/CreditNote:CreditNote/cac:CreditNoteLine/cac:AllowanceCharge/cbc:AllowanceChargeReasonCode[preceding-sibling::cbc:ChargeIndicator = \'false\']',
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
                'match' => '/CreditNote:CreditNote/cac:CreditNoteLine/cac:AllowanceCharge[cbc:ChargeIndicator = \'true\']',
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
                                    'xpath' => './cbc:Amount[preceding-sibling::cbc:ChargeIndicator = \'true\']',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BT-142',
                                'select' => [
                                    'xpath' => './cbc:BaseAmount[preceding-sibling::cbc:ChargeIndicator = \'true\']',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BT-143',
                                'select' => [
                                    'xpath' => './cbc:MultiplierFactorNumeric[preceding-sibling::cbc:ChargeIndicator = \'true\']',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BT-144',
                                'select' => [
                                    'xpath' => './cbc:AllowanceChargeReason[preceding-sibling::cbc:ChargeIndicator = \'true\']',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BT-145',
                                'select' => [
                                    'xpath' => './cbc:AllowanceChargeReasonCode[preceding-sibling::cbc:ChargeIndicator = \'true\']',
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
                'match' => '/CreditNote:CreditNote/cac:CreditNoteLine/cac:AllowanceCharge/cbc:Amount[preceding-sibling::cbc:ChargeIndicator = \'true\']',
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
                'match' => '/CreditNote:CreditNote/cac:CreditNoteLine/cac:AllowanceCharge/cbc:BaseAmount[preceding-sibling::cbc:ChargeIndicator = \'true\']',
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
                'match' => '/CreditNote:CreditNote/cac:CreditNoteLine/cac:AllowanceCharge/cbc:MultiplierFactorNumeric[preceding-sibling::cbc:ChargeIndicator = \'true\']',
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
                'match' => '/CreditNote:CreditNote/cac:CreditNoteLine/cac:AllowanceCharge/cbc:AllowanceChargeReason[preceding-sibling::cbc:ChargeIndicator = \'true\']',
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
                'match' => '/CreditNote:CreditNote/cac:CreditNoteLine/cac:AllowanceCharge/cbc:AllowanceChargeReasonCode[preceding-sibling::cbc:ChargeIndicator = \'true\']',
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
                'match' => '/CreditNote:CreditNote/cac:CreditNoteLine/cac:Price',
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
                                    'xpath' => './cbc:PriceAmount',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BT-147',
                                'select' => [
                                    'xpath' => './cac:AllowanceCharge/cbc:Amount[preceding-sibling::cbc:ChargeIndicator = \'false\']',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BT-148',
                                'select' => [
                                    'xpath' => './cac:AllowanceCharge/cbc:BaseAmount[preceding-sibling::cbc:ChargeIndicator = \'false\']',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BT-149',
                                'select' => [
                                    'xpath' => './cbc:BaseQuantity',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BT-150',
                                'select' => [
                                    'xpath' => './cbc:BaseQuantity/@unitCode',
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
                'match' => '/CreditNote:CreditNote/cac:CreditNoteLine/cac:Price/cbc:PriceAmount',
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
                'match' => '/CreditNote:CreditNote/cac:CreditNoteLine/cac:Price/cac:AllowanceCharge/cbc:Amount[preceding-sibling::cbc:ChargeIndicator = \'false\']',
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
                'match' => '/CreditNote:CreditNote/cac:CreditNoteLine/cac:Price/cac:AllowanceCharge/cbc:BaseAmount[preceding-sibling::cbc:ChargeIndicator = \'false\']',
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
        'BT-149' => [
            [
                'match' => '/CreditNote:CreditNote/cac:CreditNoteLine/cac:Price/cbc:BaseQuantity',
                'priority' => 0.5,
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
        'BT-150' => [
            [
                'match' => '/CreditNote:CreditNote/cac:CreditNoteLine/cac:Price/cbc:BaseQuantity/@unitCode',
                'priority' => 0.5,
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
        'BG-30' => [
            [
                'match' => '/CreditNote:CreditNote/cac:CreditNoteLine/cac:Item/cac:ClassifiedTaxCategory',
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
                                    'xpath' => './cbc:ID[following-sibling::cac:TaxScheme/cbc:ID = \'VAT\']',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BT-152',
                                'select' => [
                                    'xpath' => './cbc:Percent',
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
                'match' => '/CreditNote:CreditNote/cac:CreditNoteLine/cac:Item/cac:ClassifiedTaxCategory/cbc:ID[following-sibling::cac:TaxScheme/cbc:ID = \'VAT\']',
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
                'match' => '/CreditNote:CreditNote/cac:CreditNoteLine/cac:Item/cac:ClassifiedTaxCategory/cbc:Percent',
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
                'match' => '/CreditNote:CreditNote/cac:CreditNoteLine/cac:Item',
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
                                    'xpath' => './cbc:Name',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BT-154',
                                'select' => [
                                    'xpath' => './cbc:Description',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BT-155',
                                'select' => [
                                    'xpath' => './cac:SellersItemIdentification/cbc:ID',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BT-156',
                                'select' => [
                                    'xpath' => './cac:BuyersItemIdentification/cbc:ID',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BT-157',
                                'select' => [
                                    'xpath' => './cac:StandardItemIdentification/cbc:ID',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BT-158',
                                'select' => [
                                    'xpath' => './cac:CommodityClassification/cbc:ItemClassificationCode',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BT-159',
                                'select' => [
                                    'xpath' => './cac:OriginCountry/cbc:IdentificationCode',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BG-32',
                                'select' => [
                                    'xpath' => './cac:AdditionalItemProperty',
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
                'match' => '/CreditNote:CreditNote/cac:CreditNoteLine/cac:Item/cbc:Name',
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
                'match' => '/CreditNote:CreditNote/cac:CreditNoteLine/cac:Item/cbc:Description',
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
                'match' => '/CreditNote:CreditNote/cac:CreditNoteLine/cac:Item/cac:SellersItemIdentification/cbc:ID',
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
                'match' => '/CreditNote:CreditNote/cac:CreditNoteLine/cac:Item/cac:BuyersItemIdentification/cbc:ID',
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
                'match' => '/CreditNote:CreditNote/cac:CreditNoteLine/cac:Item/cac:StandardItemIdentification/cbc:ID',
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
                'match' => '/CreditNote:CreditNote/cac:CreditNoteLine/cac:Item/cac:CommodityClassification/cbc:ItemClassificationCode',
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
                'match' => '/CreditNote:CreditNote/cac:CreditNoteLine/cac:Item/cac:OriginCountry/cbc:IdentificationCode',
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
                'match' => '/CreditNote:CreditNote/cac:CreditNoteLine/cac:Item/cac:AdditionalItemProperty',
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
                                    'xpath' => './cbc:Name',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BT-161',
                                'select' => [
                                    'xpath' => './cbc:Value',
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
                'match' => '/CreditNote:CreditNote/cac:CreditNoteLine/cac:Item/cac:AdditionalItemProperty/cbc:Name',
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
                'match' => '/CreditNote:CreditNote/cac:CreditNoteLine/cac:Item/cac:AdditionalItemProperty/cbc:Value',
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
        'BG-DEX-01' => [
            [
                'match' => '//cac:SubCreditNoteLine',
                'priority' => 0.0,
                'body' => [
                    [
                        'op' => 'variable',
                        'name' => 'bg-contents',
                        'body' => [
                            [
                                'op' => 'apply',
                                'mode' => 'BT-126',
                                'select' => [
                                    'xpath' => '/CreditNote:CreditNote/cac:CreditNoteLine/cbc:ID',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BT-127',
                                'select' => [
                                    'xpath' => '/CreditNote:CreditNote/cac:CreditNoteLine/cbc:Note',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BT-128',
                                'select' => [
                                    'xpath' => '/CreditNote:CreditNote/cac:CreditNoteLine/cac:DocumentReference/cbc:ID[following-sibling::cbc:DocumentTypeCode = \'130\']',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BT-129',
                                'select' => [
                                    'xpath' => '/CreditNote:CreditNote/cac:CreditNoteLine/cbc:CreditedQuantity',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BT-130',
                                'select' => [
                                    'xpath' => '/CreditNote:CreditNote/cac:CreditNoteLine/cbc:CreditedQuantity/@unitCode',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BT-131',
                                'select' => [
                                    'xpath' => '/CreditNote:CreditNote/cac:CreditNoteLine/cbc:LineExtensionAmount',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BT-132',
                                'select' => [
                                    'xpath' => '/CreditNote:CreditNote/cac:CreditNoteLine/cac:OrderLineReference/cbc:LineID',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BT-133',
                                'select' => [
                                    'xpath' => '/CreditNote:CreditNote/cac:CreditNoteLine/cbc:AccountingCost',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BG-DEX-02',
                                'select' => [
                                    'xpath' => './cac:Item',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BG-DEX-03',
                                'select' => [
                                    'xpath' => './cac:AllowanceCharge[cbc:ChargeIndicator = \'false\']',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BG-DEX-04',
                                'select' => [
                                    'xpath' => './cac:AllowanceCharge[cbc:ChargeIndicator = \'true\']',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BG-DEX-05',
                                'select' => [
                                    'xpath' => './cac:InvoicePeriod',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BG-DEX-06',
                                'select' => [
                                    'xpath' => './cac:ClassifiedTaxCategory',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BG-DEX-07',
                                'select' => [
                                    'xpath' => './cac:Price',
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
                                'name' => 'SUB_INVOICE_LINE',
                                'body' => [
                                    [
                                        'op' => 'attribute',
                                        'name' => 'xr:id',
                                        'value' => [
                                            'literal' => 'BG-DEX-01',
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
        'BG-DEX-02' => [
            [
                'match' => '//cac:SubCreditNoteLine/cac:Item',
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
                                    'xpath' => '/CreditNote:CreditNote/cac:CreditNoteLine/cac:Item/cbc:Name',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BT-154',
                                'select' => [
                                    'xpath' => '/CreditNote:CreditNote/cac:CreditNoteLine/cac:Item/cbc:Description',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BT-155',
                                'select' => [
                                    'xpath' => '/CreditNote:CreditNote/cac:CreditNoteLine/cac:Item/cac:SellersItemIdentification/cbc:ID',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BT-156',
                                'select' => [
                                    'xpath' => '/CreditNote:CreditNote/cac:CreditNoteLine/cac:Item/cac:BuyersItemIdentification/cbc:ID',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BT-157',
                                'select' => [
                                    'xpath' => '/CreditNote:CreditNote/cac:CreditNoteLine/cac:Item/cac:StandardItemIdentification/cbc:ID',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BT-158',
                                'select' => [
                                    'xpath' => '/CreditNote:CreditNote/cac:CreditNoteLine/cac:Item/cac:CommodityClassification/cbc:ItemClassificationCode',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BT-159',
                                'select' => [
                                    'xpath' => '/CreditNote:CreditNote/cac:CreditNoteLine/cac:Item/cac:OriginCountry/cbc:IdentificationCode',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BG-DEX-08',
                                'select' => [
                                    'xpath' => './cac:AdditionalItemProperty',
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
                                'name' => 'SUB_INVOICE_ITEM_INFORMATION',
                                'body' => [
                                    [
                                        'op' => 'attribute',
                                        'name' => 'xr:id',
                                        'value' => [
                                            'literal' => 'BG-DEX-02',
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
        'BG-DEX-08' => [
            [
                'match' => '//cac:SubCreditNoteLine/cac:Item/cac:AdditionalItemProperty',
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
                                    'xpath' => '/CreditNote:CreditNote/cac:CreditNoteLine/cac:Item/cac:AdditionalItemProperty/cbc:Name',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BT-161',
                                'select' => [
                                    'xpath' => '/CreditNote:CreditNote/cac:CreditNoteLine/cac:Item/cac:AdditionalItemProperty/cbc:Value',
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
                                'name' => 'SUB_INVOICE_ITEM_ATTRIBUTES',
                                'body' => [
                                    [
                                        'op' => 'attribute',
                                        'name' => 'xr:id',
                                        'value' => [
                                            'literal' => 'BG-DEX-08',
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
        'BG-DEX-03' => [
            [
                'match' => '//cac:SubCreditNoteLine/cac:AllowanceCharge[cbc:ChargeIndicator = \'false\']',
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
                                    'xpath' => '/CreditNote:CreditNote/cac:CreditNoteLine/cac:AllowanceCharge/cbc:Amount[preceding-sibling::cbc:ChargeIndicator = \'false\']',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BT-137',
                                'select' => [
                                    'xpath' => '/CreditNote:CreditNote/cac:CreditNoteLine/cac:AllowanceCharge/cbc:BaseAmount[preceding-sibling::cbc:ChargeIndicator = \'false\']',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BT-138',
                                'select' => [
                                    'xpath' => '/CreditNote:CreditNote/cac:CreditNoteLine/cac:AllowanceCharge/cbc:MultiplierFactorNumeric[preceding-sibling::cbc:ChargeIndicator = \'false\']',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BT-139',
                                'select' => [
                                    'xpath' => '/CreditNote:CreditNote/cac:CreditNoteLine/cac:AllowanceCharge/cbc:AllowanceChargeReason[preceding-sibling::cbc:ChargeIndicator = \'false\']',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BT-140',
                                'select' => [
                                    'xpath' => '/CreditNote:CreditNote/cac:CreditNoteLine/cac:AllowanceCharge/cbc:AllowanceChargeReasonCode[preceding-sibling::cbc:ChargeIndicator = \'false\']',
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
                                'name' => 'SUB_INVOICE_LINE_ALLOWANCES',
                                'body' => [
                                    [
                                        'op' => 'attribute',
                                        'name' => 'xr:id',
                                        'value' => [
                                            'literal' => 'BG-DEX-03',
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
        'BG-DEX-04' => [
            [
                'match' => '//cac:SubCreditNoteLine/cac:AllowanceCharge[cbc:ChargeIndicator = \'true\']',
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
                                    'xpath' => '/CreditNote:CreditNote/cac:CreditNoteLine/cac:AllowanceCharge/cbc:Amount[preceding-sibling::cbc:ChargeIndicator = \'true\']',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BT-142',
                                'select' => [
                                    'xpath' => '/CreditNote:CreditNote/cac:CreditNoteLine/cac:AllowanceCharge/cbc:BaseAmount[preceding-sibling::cbc:ChargeIndicator = \'true\']',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BT-143',
                                'select' => [
                                    'xpath' => '/CreditNote:CreditNote/cac:CreditNoteLine/cac:AllowanceCharge/cbc:MultiplierFactorNumeric[preceding-sibling::cbc:ChargeIndicator = \'true\']',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BT-144',
                                'select' => [
                                    'xpath' => '/CreditNote:CreditNote/cac:CreditNoteLine/cac:AllowanceCharge/cbc:AllowanceChargeReason[preceding-sibling::cbc:ChargeIndicator = \'true\']',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BT-145',
                                'select' => [
                                    'xpath' => '/CreditNote:CreditNote/cac:CreditNoteLine/cac:AllowanceCharge/cbc:AllowanceChargeReasonCode[preceding-sibling::cbc:ChargeIndicator = \'true\']',
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
                                'name' => 'SUB_INVOICE_LINE_CHARGES',
                                'body' => [
                                    [
                                        'op' => 'attribute',
                                        'name' => 'xr:id',
                                        'value' => [
                                            'literal' => 'BG-DEX-04',
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
        'BG-DEX-05' => [
            [
                'match' => '//cac:SubCreditNoteLine/cac:InvoicePeriod',
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
                                    'xpath' => '/CreditNote:CreditNote/cac:CreditNoteLine/cac:InvoicePeriod/cbc:StartDate',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BT-135',
                                'select' => [
                                    'xpath' => '/CreditNote:CreditNote/cac:CreditNoteLine/cac:InvoicePeriod/cbc:EndDate',
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
                                'name' => 'SUB_INVOICE_LINE_PERIOD',
                                'body' => [
                                    [
                                        'op' => 'attribute',
                                        'name' => 'xr:id',
                                        'value' => [
                                            'literal' => 'BG-DEX-05',
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
        'BG-DEX-06' => [
            [
                'match' => '//cac:SubCreditNoteLine/cac:ClassifiedTaxCategory',
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
                                    'xpath' => '/CreditNote:CreditNote/cac:CreditNoteLine/cac:Item/cac:ClassifiedTaxCategory/cbc:ID[following-sibling::cac:TaxScheme/cbc:ID = \'VAT\']',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BT-152',
                                'select' => [
                                    'xpath' => '/CreditNote:CreditNote/cac:CreditNoteLine/cac:Item/cac:ClassifiedTaxCategory/cbc:Percent',
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
                                'name' => 'SUB_INVOICE_LINE_VAT_INFORMATION',
                                'body' => [
                                    [
                                        'op' => 'attribute',
                                        'name' => 'xr:id',
                                        'value' => [
                                            'literal' => 'BG-DEX-06',
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
        'BG-DEX-07' => [
            [
                'match' => '//cac:SubCreditNoteLine/cac:Price',
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
                                    'xpath' => '/CreditNote:CreditNote/cac:CreditNoteLine/cac:Price/cbc:PriceAmount',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BT-147',
                                'select' => [
                                    'xpath' => '/CreditNote:CreditNote/cac:CreditNoteLine/cac:Price/cac:AllowanceCharge/cbc:Amount[preceding-sibling::cbc:ChargeIndicator = \'false\']',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BT-148',
                                'select' => [
                                    'xpath' => '/CreditNote:CreditNote/cac:CreditNoteLine/cac:Price/cac:AllowanceCharge/cbc:BaseAmount[preceding-sibling::cbc:ChargeIndicator = \'false\']',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BT-149',
                                'select' => [
                                    'xpath' => '/CreditNote:CreditNote/cac:CreditNoteLine/cac:Price/cbc:BaseQuantity',
                                ],
                            ],
                            [
                                'op' => 'apply',
                                'mode' => 'BT-150',
                                'select' => [
                                    'xpath' => '/CreditNote:CreditNote/cac:CreditNoteLine/cac:Price/cbc:BaseQuantity/@unitCode',
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
                                'name' => 'SUB_INVOICE_PRICE_DETAILS',
                                'body' => [
                                    [
                                        'op' => 'attribute',
                                        'name' => 'xr:id',
                                        'value' => [
                                            'literal' => 'BG-DEX-07',
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
    ],
    'named' => [],
];
