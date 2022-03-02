<?php
class CgroupPriceType extends AppModel {
    var $name = 'CgroupPriceType';
    
    var $belongsTo = array(
        'PriceType' => array(
            'className' => 'PriceType',
            'foreignKey' => 'price_type_id',
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