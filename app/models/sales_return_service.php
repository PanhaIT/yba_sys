<?php

class SalesReturnService extends AppModel {

    var $name = 'SalesReturnService';

    var $belongsTo = array(
        'SalesReturn' => array(
            'className' => 'SalesReturn',
            'foreignKey' => 'sales_return_id',
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