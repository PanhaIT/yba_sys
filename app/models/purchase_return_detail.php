<?php

class PurchaseReturnDetail extends AppModel {

    var $name = 'PurchaseReturnDetail';
    var $belongsTo = array(
        'PurchaseReturn' => array(
            'className' => 'PurchaseReturn',
            'foreignKey' => 'purchase_return_id',
            'conditions' => '',
            'fields' => '',
            'order' => ''
        ),
        'Product' => array(
            'className' => 'Product',
            'foreignKey' => 'product_id',
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
        )
    );
}
?>