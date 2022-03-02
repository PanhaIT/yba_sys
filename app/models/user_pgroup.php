<?php
class UserPgroup extends AppModel {
    var $name = 'UserPgroup';
    
    var $belongsTo = array(
        'User' => array(
            'className' => 'User',
            'foreignKey' => 'user_id',
            'conditions' => '',
            'fields' => '',
            'order' => ''
        ),
        'Pgroup' => array(
            'className' => 'Pgroup',
            'foreignKey' => 'pgroup_id',
            'conditions' => '',
            'fields' => '',
            'order' => ''
        )
    );
}
?>