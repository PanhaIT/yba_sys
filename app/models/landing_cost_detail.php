<?php

class LandingCostDetail extends AppModel {

    var $name = 'LandingCostDetail';
    var $belongsTo = array(
        'LandingCost' => array(
            'className' => 'LandingCost',
            'foreignKey' => 'landing_cost_id',
            'conditions' => '',
            'fields' => '',
            'order' => ''
        ),
        'PurchaseOrderDetail' => array(
            'className' => 'PurchaseOrderDetail',
            'foreignKey' => 'purchase_order_detail_id',
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