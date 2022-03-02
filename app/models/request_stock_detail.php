<?php

class RequestStockDetail extends AppModel {

    var $name = 'RequestStockDetail';
    var $belongsTo = array(
        'RequestStock' => array(
            'className' => 'RequestStock',
            'foreignKey' => 'request_stock_id',
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