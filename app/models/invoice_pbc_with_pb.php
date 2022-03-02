<?php

class InvoicePbcWithPb extends AppModel {
    var $name = 'InvoicePbcWithPb';
    var $belongsTo = array(
        'PurchaseOrder' => array(
            'className' => 'PurchaseOrder',
            'foreignKey' => 'purchase_order_id',
            'conditions' => '',
            'fields' => '',
            'order' => ''
        ),
        'PurchaseReturn' => array(
            'className' => 'PurchaseReturn',
            'foreignKey' => 'purchase_return_id',
            'conditions' => '',
            'fields' => '',
            'order' => ''
        )
    );
}
?>