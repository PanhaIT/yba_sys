<?php

class Employee extends AppModel {
    var $name = 'Employee';
    var $belongsTo = array(
        'Position' => array(
            'className' => 'Position',
            'foreignKey' => 'position_id',
            'conditions' => '',
            'fields' => '',
            'order' => ''
        ),
        'EmployeeType' => array(
            'className' => 'EmployeeType',
            'foreignKey' => 'employee_type_id',
            'conditions' => '',
            'fields' => '',
            'order' => ''
        ),
        'Vendor' => array(
            'className' => 'Vendor',
            'foreignKey' => 'work_for_vendor_id',
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
        )
    );
}
?>
