<?php

class ConsignmentReturnDetail extends AppModel {

    var $name = 'ConsignmentReturnDetail';
    var $belongsTo = array(
        'ConsignmentReturn' => array(
            'className' => 'ConsignmentReturn',
            'foreignKey' => 'consignment_return_id',
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