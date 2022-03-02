<?php
class VendorContact extends AppModel {
    var $name = 'VendorContact';
    
    var $belongsTo = array(
        'Vendor' => array(
            'className' => 'Vendor',
            'foreignKey' => 'vendor_id',
            'conditions' => '',
            'fields' => '',
            'order' => ''
        )
    );
}
?>