<?php

class LandingCost extends AppModel {
    var $name = 'LandingCost';
    var $belongsTo = array(
        'Vendor' => array(
            'className' => 'Vendor',
            'foreignKey' => 'vendor_id',
            'conditions' => '',
            'fields' => '',
            'order' => ''
        ),
        'PurchaseOrder' => array(
            'className' => 'PurchaseOrder',
            'foreignKey' => 'purchase_order_id',
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
        ),
        'Company' => array(
            'className' => 'Company',
            'foreignKey' => 'company_id',
            'conditions' => '',
            'fields' => '',
            'order' => ''
        ),
        'Branch' => array(
            'className' => 'Branch',
            'foreignKey' => 'branch_id',
            'conditions' => '',
            'fields' => '',
            'order' => ''
        ),
        'Currency' => array(
            'className' => 'Currency',
            'foreignKey' => 'currency_id',
            'conditions' => '',
            'fields' => '',
            'order' => ''
        ),
        'ChartAccount' => array(
            'className' => 'ChartAccount',
            'foreignKey' => 'ap_id',
            'conditions' => '',
            'fields' => '',
            'order' => ''
        ),
        'LandedCostType' => array(
            'className' => 'LandedCostType',
            'foreignKey' => 'landed_cost_type_id',
            'conditions' => '',
            'fields' => '',
            'order' => ''
        )
    );
}
?>