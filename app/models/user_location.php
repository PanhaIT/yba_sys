<?php
class UserLocation extends AppModel {
    var $name = 'UserLocation';
    var $belongsTo = array(
        'Location' => array(
            'className' => 'Location',
            'foreignKey' => 'location_id',
            'conditions' => '',
            'fields' => '',
            'order' => ''
        )
    );
}
?>
