<?php
class CustomerCgroup extends AppModel {
    var $name = 'CustomerCgroup';
    
    var $belongsTo = array(
        'Customer' => array(
            'className' => 'Customer',
            'foreignKey' => 'customer_id',
            'conditions' => '',
            'fields' => '',
            'order' => ''
        ),
        'Cgroup' => array(
            'className' => 'Cgroup',
            'foreignKey' => 'cgroup_id',
            'conditions' => '',
            'fields' => '',
            'order' => ''
        )
    );
}
?>