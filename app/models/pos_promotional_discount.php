<?php
class PosPromotionalDiscount extends AppModel {
    var $name = 'PosPromotionalDiscount';
    
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