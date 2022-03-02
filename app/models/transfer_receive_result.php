<?php

class TransferReceiveResult extends AppModel {
    var $name = 'TransferReceiveResult';
    var $belongsTo = array(
        'TransferOrder' => array(
            'className' => 'TransferOrder',
            'foreignKey' => 'transfer_order_id',
            'conditions' => '',
            'fields' => '',
            'order' => ''
        )
    );
}
?>