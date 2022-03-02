<?php

class TransferOrderReceipt extends AppModel {

    var $name = 'TransferOrderReceipt';

    var $belongsTo = array(
        'TransferOrder' => array(
            'className' => 'TransferOrder',
            'foreignKey' => 'transfer_order_id',
            'conditions' => '',
            'fields' => '',
            'order' => ''
        ),
        'ExchangeRate' => array(
            'className' => 'ExchangeRate',
            'foreignKey' => 'exchange_rate_id',
            'conditions' => '',
            'fields' => '',
            'order' => ''
        ),
        'User' => array(
            'className' => 'User',
            'foreignKey' => 'created_by',
            'conditions' => '',
            'fields' => '',
            'order' => ''
        )
    );
}

?>