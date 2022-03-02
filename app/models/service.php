<?php

class Service extends AppModel {
    var $name = 'Service';
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
        'ServiceGroup' => array(
            'className' => 'ServiceGroup',
            'foreignKey' => 'service_group_id',
            'conditions' => '',
            'fields' => '',
            'order' => ''
        )
    );
}
?>