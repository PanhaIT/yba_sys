<?php

class TransferOrderDetail extends AppModel {

    var $name = 'TransferOrderDetail';
    var $belongsTo = array(
        'Product' => array(
            'className' => 'Product',
            'foreignKey' => 'product_id',
            'conditions' => '',
            'fields' => '',
            'order' => ''
        )
    );
}
?>