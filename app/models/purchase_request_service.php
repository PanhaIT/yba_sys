<?php

class PurchaseOrderService extends AppModel {

    var $name = 'PurchaseOrderService';
    var $belongsTo = array(
        'Service' => array(
            'className' => 'Service',
            'foreignKey' => 'service_id',
            'conditions' => '',
            'fields' => '',
            'order' => ''
        )
    );
}
?>