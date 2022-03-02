<?php

class QuotationMisc extends AppModel {

    var $name = 'QuotationMisc';

    var $belongsTo = array(
        'Quotation' => array(
            'className' => 'Quotation',
            'foreignKey' => 'quotation_id',
            'conditions' => '',
            'fields' => '',
            'order' => ''
        ),
        'Uom' => array(
            'className' => 'Uom',
            'foreignKey' => 'qty_uom_id',
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
    );
}