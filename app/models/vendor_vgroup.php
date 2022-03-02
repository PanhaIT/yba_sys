<?php
class VendorVgroup extends AppModel {
    var $name = 'VendorVgroup';
    
    var $belongsTo = array(
        'Vendor' => array(
            'className' => 'Vendor',
            'foreignKey' => 'vendor_id',
            'conditions' => '',
            'fields' => '',
            'order' => ''
        ),
        'Vgroup' => array(
            'className' => 'Vgroup',
            'foreignKey' => 'vgroup_id',
            'conditions' => '',
            'fields' => '',
            'order' => ''
        )
    );
}
?>