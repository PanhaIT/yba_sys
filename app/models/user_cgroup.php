<?php
class UserCgroup extends AppModel {
    var $name = 'UserCgroup';
    
    var $belongsTo = array(
        'User' => array(
            'className' => 'User',
            'foreignKey' => 'user_id',
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