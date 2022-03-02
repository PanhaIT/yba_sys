<?php

class SalesInvoiceService extends AppModel {

    var $name = 'SalesInvoiceService';

    var $belongsTo = array(
        'SalesInvoice' => array(
            'className' => 'SalesInvoice',
            'foreignKey' => 'sales_invoice_id',
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
        'Service' => array(
            'className' => 'Service',
            'foreignKey' => 'service_id',
            'conditions' => '',
            'fields' => '',
            'order' => ''
        ),
    );
}

?>