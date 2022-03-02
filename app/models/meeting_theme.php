<?php

class MeetingTheme extends AppModel {
    var $name = 'MeetingTheme';
    var $belongsTo = array(
        'Employee' => array(
            'className' => 'Employee',
            'foreignKey' => 'employee_id',
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
        'Egroup' => array(
            'className' => 'Egroup',
            'foreignKey' => 'egroup_id',
            'conditions' => '',
            'fields' => '',
            'order' => ''
        )
    );
}
?>