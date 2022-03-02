<?php

class ConsignmentDetail extends AppModel {

    var $name = 'ConsignmentDetail';
    var $belongsTo = array(
        'Consignment' => array(
            'className' => 'Consignment',
            'foreignKey' => 'consignment_id',
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