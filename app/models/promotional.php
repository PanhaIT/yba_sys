<?php

class Promotional extends AppModel {
    var $name = 'Promotional';
    var $belongsTo = array(
        'Branch' => array(
            'className' => 'Branch',
            'foreignKey' => 'branch_id',
            'conditions' => '',
            'fields' => '',
            'order' => ''
        ),
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