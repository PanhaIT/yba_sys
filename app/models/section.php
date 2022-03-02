<?php

class Section extends AppModel {
    var $name = 'Section';
    var $hasMany = array(
        'Service' => array(
            'className' => 'Service',
            'foreignKey' => 'section_id',
            'conditions' => '',
            'fields' => '',
            'order' => ''
        )
    );
}
?>