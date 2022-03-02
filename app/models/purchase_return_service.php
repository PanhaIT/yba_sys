<?php

class PurchaseReturnService extends AppModel {

    var $name = 'PurchaseReturnService';

    var $belongsTo = array(
        'PurchaseReturn' => array(
            'className' => 'PurchaseReturn',
            'foreignKey' => 'purchase_return_id',
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

?>