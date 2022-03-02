<?php

class PurchaseBill extends AppModel {
    var $name = 'PurchaseBill';
    var $belongsTo = array(
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
        'Location' => array(
            'className' => 'Location',
            'foreignKey' => 'location_id',
            'conditions' => '',
            'fields' => '',
            'order' => ''
        ),
        'LocationGroup' => array(
            'className' => 'LocationGroup',
            'foreignKey' => 'location_group_id',
            'conditions' => '',
            'fields' => '',
            'order' => ''
        ),
        'Vendor' => array(
            'className' => 'Vendor',
            'foreignKey' => 'vendor_id',
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
        'PurchaseOrder' => array(
            'className' => 'PurchaseOrder',
            'foreignKey' => 'purchase_order_id',
            'conditions' => '',
            'fields' => '',
            'order' => ''
        ),
        'VendorConsignment' => array(
            'className' => 'VendorConsignment',
            'foreignKey' => 'vendor_consignment_id',
            'conditions' => '',
            'fields' => '',
            'order' => ''
        ),
        'Shipment' => array(
            'className' => 'Shipment',
            'foreignKey' => 'shipment_id',
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
        )
    );
}
?>