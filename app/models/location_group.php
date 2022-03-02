<?php
class LocationGroup extends AppModel {
    var $name = 'LocationGroup';
    var $belongsTo = array(
        'LocationGroupType' => array(
            'className' => 'LocationGroupType',
            'foreignKey' => 'location_group_type_id',
            'conditions' => '',
            'fields' => '',
            'order' => ''
        )
    );
}
?>