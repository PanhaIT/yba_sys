<?php

class TransferReceive extends AppModel {
    var $name = 'TransferReceive';
    var $belongsTo = array(
        'TransferOrder' => array(
            'className' => 'TransferOrder',
            'foreignKey' => 'transfer_order_id',
            'conditions' => '',
            'fields' => '',
            'order' => ''
        ),
        'TransferOrderDetail' => array(
            'className' => 'TransferOrderDetail',
            'foreignKey' => 'transfer_order_detail_id',
            'conditions' => '',
            'fields' => '',
            'order' => ''
        ),
        'TransferReceiveResult' => array(
            'className' => 'TransferReceiveResult',
            'foreignKey' => 'transfer_receive_result_id',
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