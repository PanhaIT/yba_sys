<?php

class TermConditionApply extends AppModel {
    var $name = 'TermConditionApply';
    var $belongsTo = array(
        'ModuleType' => array(
            'className' => 'ModuleType',
            'foreignKey' => 'module_type_id',
            'conditions' => '',
            'fields' => '',
            'order' => ''
        ),
        'TermConditionType' => array(
            'className' => 'TermConditionType',
            'foreignKey' => 'term_condition_type_id',
            'conditions' => '',
            'fields' => '',
            'order' => ''
        ),
        'TermCondition' => array(
            'className' => 'TermCondition',
            'foreignKey' => 'term_condition_default_id',
            'conditions' => '',
            'fields' => '',
            'order' => ''
        )
    );
}
?>