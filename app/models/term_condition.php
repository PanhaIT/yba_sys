<?php

class TermCondition extends AppModel {
    var $name = 'TermCondition';
    var $belongsTo = array(
        'TermConditionType' => array(
            'className' => 'TermConditionType',
            'foreignKey' => 'term_condition_type_id',
            'conditions' => '',
            'fields' => '',
            'order' => ''
        )
    );
}
?>