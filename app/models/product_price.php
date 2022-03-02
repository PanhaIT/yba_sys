<?php

class ProductPrice extends AppModel {
    var $name = 'ProductPrice';
    var $belongsTo = array(
        'Product' => array(
            'className' => 'Product',
            'foreignKey' => 'product_id',
            'conditions' => '',
            'fields' => '',
            'order' => ''
        ),
        'Uom' => array(
            'className' => 'Uom',
            'foreignKey' => 'uom_id',
            'conditions' => '',
            'fields' => '',
            'order' => ''
        ),
    );
}
?>