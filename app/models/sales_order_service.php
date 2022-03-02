<?php

class SalesOrderService extends AppModel {

    var $name = 'SalesOrderService';

    var $belongsTo = array(
        'SalesOrder' => array(
            'className' => 'SalesOrder',
            'foreignKey' => 'sales_order_id',
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
        'Service' => array(
            'className' => 'Service',
            'foreignKey' => 'service_id',
            'conditions' => '',
            'fields' => '',
            'order' => ''
        ),
    );
}

