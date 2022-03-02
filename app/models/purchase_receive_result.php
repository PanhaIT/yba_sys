<?php

class PurchaseReceiveResult extends AppModel {
    var $name = 'PurchaseReceiveResult';
    var $belongsTo = array(
        'PurchaseBill' => array(
            'className' => 'PurchaseBill',
            'foreignKey' => 'purchase_bill_id',
            'conditions' => '',
            'fields' => '',
            'order' => ''
        )
    );
}
?>