<?php
class MembershipCard extends AppModel {
    var $name = 'MembershipCard';
    
    var $belongsTo = array(
        'Company' => array(
            'className' => 'Company',
            'foreignKey' => 'company_id',
            'conditions' => '',
            'fields' => '',
            'order' => ''
        ),
        'Customer' => array(
            'className' => 'Customer',
            'foreignKey' => 'customer_id',
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
        ),
        'ChartAccount' => array(
            'className' => 'ChartAccount',
            'foreignKey' => 'account_id',
            'conditions' => '',
            'fields' => '',
            'order' => ''
        ),
        'TypeOfMembershipCard' => array(
            'className' => 'TypeOfMembershipCard',
            'foreignKey' => 'type_of_membership_card_id',
            'conditions' => '',
            'fields' => '',
            'order' => ''
        )
    );

}
?>