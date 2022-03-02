<?php

class ReceivePaymentDetail extends AppModel {
    var $name = 'ReceivePaymentDetail';
    var $belongsTo = array(
        'SalesInvoice' => array(
            'className' => 'SalesInvoice',
            'foreignKey' => 'sales_invoice_id',
            'conditions' => '',
            'fields' => '',
            'order' => ''
        ),
        'ReceivePayment' => array(
            'className' => 'ReceivePayment',
            'foreignKey' => 'receive_payment_id',
            'conditions' => '',
            'fields' => '',
            'order' => ''
        )
    );
}
?>