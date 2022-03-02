<?php
class Vendor extends AppModel {
    var $name = 'Vendor';
    
    var $belongsTo = array(
        'PaymentTerm' => array(
            'className' => 'PaymentTerm',
            'foreignKey' => 'payment_term_id',
            'conditions' => '',
            'fields' => '',
            'order' => ''
        ),
        'Country' => array(
            'className' => 'Country',
            'foreignKey' => 'country_id',
            'conditions' => '',
            'fields' => '',
            'order' => ''
        ),
        'User' => array(
            'className' => 'User',
            'foreignKey' => 'created_by',
            'conditions' => '',
            'fields' => '',
            'order' => ''
        )
    );

}
?>