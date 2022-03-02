<?php

class SalesReturnWithSale extends AppModel {
    var $name = 'SalesReturnWithSale';
    var $belongsTo = array(
        'SalesOrder' => array(
            'className' => 'SalesOrder',
            'foreignKey' => 'sales_invoice_id',
            'conditions' => '',
            'fields' => '',
            'order' => ''
        ),
        'SalesReturn' => array(
            'className' => 'SalesReturn',
            'foreignKey' => 'sales_return_id',
            'conditions' => '',
            'fields' => '',
            'order' => ''
        )
    );
}
?>