<?php

class PromotionalPoint extends AppModel {
    var $name = 'PromotionalPoint';
    var $belongsTo = array(
        'Branch' => array(
            'className' => 'Branch',
            'foreignKey' => 'branch_id',
            'conditions' => '',
            'fields' => '',
            'order' => ''
        )
    );
}
?>