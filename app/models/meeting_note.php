<?php

class MeetingNote extends AppModel {
    var $name = 'MeetingNote';
    var $belongsTo = array(
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