<?php

class SalesOrderMisc extends AppModel {

    var $name = 'SalesOrderMisc';

    var $belongsTo = array(
        'SalesOrder' => array(
            'className' => 'SalesOrder',
            'foreignKey' => 'sales_order_id',
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
        ),
    );
}