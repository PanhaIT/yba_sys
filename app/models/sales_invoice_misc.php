<?php

class SalesInvoiceMisc extends AppModel {

    var $name = 'SalesInvoiceMisc';

    var $belongsTo = array(
        'SalesInvoice' => array(
            'className' => 'SalesInvoice',
            'foreignKey' => 'sales_invoice_id',
            'conditions' => '',
            'fields' => '',
            'order' => ''
        ),
        'Uom' => array(
            'className' => 'Uom',
            'foreignKey' => 'qty_uom_id',
            'conditions' => '',
            'fields' => '',
            'order' => ''
        ),
        'Discount' => array(
            'className' => 'Discount',
            'foreignKey' => 'discount_id',
            'conditions' => '',
            'fields' => '',
            'order' => ''
        ),
    );
}

?>