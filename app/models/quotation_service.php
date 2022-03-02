<?php

class QuotationService extends AppModel {

    var $name = 'QuotationService';

    var $belongsTo = array(
        'Quotation' => array(
            'className' => 'Quotation',
            'foreignKey' => 'quotation_id',
            'conditions' => '',
            'fields' => '',
            'order' => ''
        ),
        'Discount' => array(
            'className' => 'Discount',
            'foreignKey' => 'discount_id',
            'conditions' => '',
            'fields' => '',
            'order' => ''
        ),
        'Service' => array(
            'className' => 'Service',
            'foreignKey' => 'service_id',
            'conditions' => '',
            'fields' => '',
            'order' => ''
        ),
    );
}

