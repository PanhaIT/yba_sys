<?php

class SalesReturnMisc extends AppModel {

    var $name = 'SalesReturnMisc';

    var $belongsTo = array(
        'SalesReturn' => array(
            'className' => 'SalesReturn',
            'foreignKey' => 'sales_return_id',
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
        ),
        'Discount' => array(
            'className' => 'Discount',
            'foreignKey' => 'discount_id',
            'conditions' => '',
            'fields' => '',
            'order' => ''
        )
    );
}

?>