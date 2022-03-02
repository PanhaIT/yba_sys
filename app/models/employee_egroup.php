<?php
class EmployeeEgroup extends AppModel {
    var $name = 'EmployeeEgroup';
    
    var $belongsTo = array(
        'Employee' => array(
            'className' => 'Employee',
            'foreignKey' => 'employee_id',
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