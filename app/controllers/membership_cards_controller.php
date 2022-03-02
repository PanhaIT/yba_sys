<?php

class MembershipCardsController extends AppController {

    var $name = 'MembershipCards';
    var $components = array('Helper', 'Address');

    function index() {
        $this->layout = 'ajax';
    }

    function ajax() {
        $this->layout = 'ajax';
    }

    function view($id = null) {
        $this->layout = 'ajax';
        if (!$id) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $this->set('membershipCard', $this->MembershipCard->read(null, $id));
    }

    function add() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        if (!empty($this->data)) {
            $this->loadModel('Customer');
            if ($this->Helper->checkDouplicate('card_id', 'membership_cards', $this->data['MembershipCard']['card_id'])) {
                echo MESSAGE_DATA_ALREADY_EXISTS_IN_THE_SYSTEM;
                exit;
            } 
            if($this->data['MembershipCard']['customer_id']!=""){
                $customer['Customer']['id'] = $this->data['MembershipCard']['customer_id'];
                $customer['Customer']['sex'] = $this->data['MembershipCard']['sex'];
                $customer['Customer']['main_number'] = $this->data['MembershipCard']['main_number'];
                $customer['Customer']['email'] = $this->data['MembershipCard']['email'];
                $customer['Customer']['dob'] = $this->data['MembershipCard']['dob'];
                $customer['Customer']['address'] = $this->data['MembershipCard']['address'];
                $this->Customer->save($customer['Customer']);
            }else{
                $this->Customer->create();
                $customer['Customer']['customer_code'] = $this->Helper->getAutoGenerateCustomerCode();
                $customer['Customer']['name'] = $this->data['MembershipCard']['name'];
                $customer['Customer']['sex'] = $this->data['MembershipCard']['sex'];
                $customer['Customer']['main_number'] = $this->data['MembershipCard']['main_number'];
                $customer['Customer']['email'] = $this->data['MembershipCard']['email'];
                $customer['Customer']['dob'] = $this->data['MembershipCard']['dob'];
                $customer['Customer']['address'] = $this->data['MembershipCard']['address'];
                if($this->Customer->save($customer['Customer'])){
                    $this->data['MembershipCard']['customer_id'] = $this->Customer->getLastInsertId();
                    $this->loadModel('CustomerCompany');
                    $this->loadModel('CustomerCgroup');
                    // insert customer company
                    $this->CustomerCompany->create();
                    $customerCompany['CustomerCompany']['customer_id'] = $this->Customer->getLastInsertId();
                    $customerCompany['CustomerCompany']['company_id'] = $this->data['MembershipCard']['company_id'];
                    $this->CustomerCompany->save($customerCompany['CustomerCompany']);
                    
                    // Insert customer group
                    if($this->data['MembershipCard']['cgroup_id']!=""){
                        $this->CustomerCgroup->create();
                        $customerCgroup['CustomerCgroup']['customer_id'] = $this->Customer->getLastInsertId();
                        $customerCgroup['CustomerCgroup']['cgroup_id'] = $this->data['MembershipCard']['cgroup_id'];
                        $this->CustomerCgroup->save($customerCgroup['CustomerCgroup']);
                    }
                }                                
            }
            
            $this->MembershipCard->create();
            if($this->data['MembershipCard']['discount_percent1'] != "" && $this->data['MembershipCard']['type_of_membership_card_id'] == 1){
                $this->data['MembershipCard']['discount_percent'] = $this->data['MembershipCard']['discount_percent1'];
                $this->data['MembershipCard']['account_id']       = $this->data['MembershipCard']['account_id1'];

                $this->data['MembershipCard']['total_point']      = $this->data['MembershipCard']['total_point'];
                $this->data['MembershipCard']['exchange_point']   = $this->data['MembershipCard']['exchange_point'];
                $this->data['MembershipCard']['point_in_dollar']  = $this->data['MembershipCard']['point_in_dollar'];
            }else{
                $this->data['MembershipCard']['discount_percent'] = $this->data['MembershipCard']['discount_percent2'];
                $this->data['MembershipCard']['account_id']       = $this->data['MembershipCard']['account_id2'];

                $this->data['MembershipCard']['total_point']      = $this->data['MembershipCard']['total_point'];
                $this->data['MembershipCard']['exchange_point']   = $this->data['MembershipCard']['exchange_point'];
                $this->data['MembershipCard']['point_in_dollar']  = $this->data['MembershipCard']['point_in_dollar'];
            }
            $this->data['MembershipCard']['created_by'] = $user['User']['id'];
            $this->data['MembershipCard']['is_active']  = 1;
            if ($this->MembershipCard->save($this->data)) {
                echo MESSAGE_DATA_HAS_BEEN_SAVED;
                exit;
            } else {
                echo MESSAGE_DATA_COULD_NOT_BE_SAVED;
                exit;
            }
        }
        if($user['User']['id'] == 1){
            $conditionUser = "";
        }else{
            $conditionUser = "id IN (SELECT cgroup_id FROM cgroup_companies WHERE company_id IN (SELECT company_id FROM user_companies WHERE user_id = ".$user['User']['id']."))";
        }        
        $sexes = array('Male' => 'Male', 'Female' => 'Female');
        $companies = ClassRegistry::init('Company')->find('list', array('order' => 'id', 'conditions' => array('is_active' => 1, 'id IN (SELECT company_id FROM user_companies WHERE user_id = '.$user['User']['id'].')')));
        $cgroups = ClassRegistry::init('Cgroup')->find('all', array('order' => 'id', 'conditions' => array('is_active' => 1, $conditionUser)));
        $customers = ClassRegistry::init('Customer')->find('all', array('conditions' => array('Customer.is_active = 1')));                        
        $typeOfMembershipCards = ClassRegistry::init('TypeOfMembershipCard')->find('list', array('conditions' => array('is_active = 1'))); 
        $arAccount = ClassRegistry::init('AccountType')->findById(11);
        $arAccountId = $arAccount['AccountType']['chart_account_id'];        
        $arAccountTop = ClassRegistry::init('AccountType')->findById(8);                
        $arAccountTopId = 39;
        $this->set(compact('sexes', 'companies', 'cgroups', 'customers', 'typeOfMembershipCards',"arAccountId", "arAccountTopId"));
    }

    function edit($id = null) {
        $this->layout = 'ajax';
        if (!$id && empty($this->data)) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $user = $this->getCurrentUser();
        if (!empty($this->data)) {
            if ($this->Helper->checkDouplicateEdit('card_id', 'membership_cards', $id, $this->data['MembershipCard']['card_id'])) {
                echo MESSAGE_DATA_ALREADY_EXISTS_IN_THE_SYSTEM;
                exit;
            }
            $this->loadModel('Customer');
            if($this->data['MembershipCard']['customer_id']!=""){
                $customer['Customer']['id'] = $this->data['MembershipCard']['customer_id'];
                $customer['Customer']['sex'] = $this->data['MembershipCard']['sex'];
                $customer['Customer']['main_number'] = $this->data['MembershipCard']['main_number'];
                $customer['Customer']['email'] = $this->data['MembershipCard']['email'];
                $customer['Customer']['dob'] = $this->data['MembershipCard']['dob'];
                $customer['Customer']['address'] = $this->data['MembershipCard']['address'];
                $this->Customer->save($customer['Customer']);
            }else{
                $this->Customer->create();
                $customer['Customer']['name'] = $this->data['MembershipCard']['name'];
                $customer['Customer']['sex'] = $this->data['MembershipCard']['sex'];
                $customer['Customer']['main_number'] = $this->data['MembershipCard']['main_number'];
                $customer['Customer']['email'] = $this->data['MembershipCard']['email'];
                $customer['Customer']['dob'] = $this->data['MembershipCard']['dob'];
                $customer['Customer']['address'] = $this->data['MembershipCard']['address'];
                if($this->Customer->save($customer['Customer'])){
                    // get customer id insert into membership card
                    $this->data['MembershipCard']['customer_id'] = $this->Customer->getLastInsertId();
                    $this->loadModel('CustomerCompany');
                    $this->loadModel('CustomerCgroup');
                    // insert customer company
                    $this->CustomerCompany->create();
                    $customerCompany['CustomerCompany']['customer_id'] = $this->Customer->getLastInsertId();
                    $customerCompany['CustomerCompany']['company_id'] = $this->data['MembershipCard']['company_id'];
                    $this->CustomerCompany->save($customerCompany['CustomerCompany']);
                    
                    // Insert customer group
                    if($this->data['MembershipCard']['cgroup_id']!=""){
                        $this->CustomerCgroup->create();
                        $customerCgroup['CustomerCgroup']['customer_id'] = $this->Customer->getLastInsertId();
                        $customerCgroup['CustomerCgroup']['cgroup_id'] = $this->data['MembershipCard']['cgroup_id'];
                        $this->CustomerCgroup->save($customerCgroup['CustomerCgroup']);
                    }
                }                                
            }
            
            if($this->data['MembershipCard']['discount_percent1'] != "" && $this->data['MembershipCard']['type_of_membership_card_id'] == 1){
                $this->data['MembershipCard']['discount_percent'] = $this->data['MembershipCard']['discount_percent1'];
                $this->data['MembershipCard']['account_id'] = $this->data['MembershipCard']['account_id1'];
            }else{
                $this->data['MembershipCard']['discount_percent'] = $this->data['MembershipCard']['discount_percent2'];
                $this->data['MembershipCard']['account_id'] = $this->data['MembershipCard']['account_id2'];
            }
            
            $this->data['MembershipCard']['modified_by'] = $user['User']['id'];
            $this->data['MembershipCard']['is_active']   = 1;
            if ($this->MembershipCard->save($this->data)) {                
                
                echo MESSAGE_DATA_HAS_BEEN_SAVED;
                exit;
            } else {
                echo MESSAGE_DATA_COULD_NOT_BE_SAVED;
                exit;
            }
        }
        if (empty($this->data)) {
            $this->data = $this->MembershipCard->read(null, $id);
        }
        if($user['User']['id'] == 1){
            $conditionUser = "";
        }else{
            $conditionUser = "id IN (SELECT cgroup_id FROM cgroup_companies WHERE company_id IN (SELECT company_id FROM user_companies WHERE user_id = ".$user['User']['id']."))";
        }   
        $sexes = array('Male' => 'Male', 'Female' => 'Female');
        $companies = ClassRegistry::init('Company')->find('list', array('order' => 'id', 'conditions' => array('is_active' => 1, 'id IN (SELECT company_id FROM user_companies WHERE user_id = '.$user['User']['id'].')')));
        $cgroups = ClassRegistry::init('Cgroup')->find('all', array('order' => 'id', 'conditions' => array('is_active' => 1, $conditionUser)));
        $customers = ClassRegistry::init('Customer')->find('all', array('conditions' => array('Customer.is_active = 1')));                        
        $typeOfMembershipCards = ClassRegistry::init('TypeOfMembershipCard')->find('list', array('conditions' => array('is_active = 1'))); 
        $arAccount = ClassRegistry::init('AccountType')->findById(11);
         
                
        $arAccountTop = ClassRegistry::init('AccountType')->findById(8);
        if(!empty($this->data)){
            $arAccountId = $this->data['MembershipCard']['account_id'];
            $arAccountTopId = $this->data['MembershipCard']['account_id'];
        }else{
            $arAccountId = $arAccount['AccountType']['chart_account_id'];
            $arAccountTopId = 39;
        }        
        $this->set(compact('sexes', 'companies', 'cgroups', 'customers', 'typeOfMembershipCards',"arAccountId", "arAccountTopId"));
    }

    function delete($id = null) {
        if (!$id) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $this->MembershipCard->updateAll(
                array('MembershipCard.is_active' => "2"),
                array('MembershipCard.id' => $id)
        );
        echo MESSAGE_DATA_HAS_BEEN_DELETED;
        exit;
    }

    function seacrhCustomer($id = null) {
        $this->layout = 'ajax';
        $this->loadModel('Customer');
        $user = $this->getCurrentUser();        
        $result = array();
        if($user['User']['id'] == 1){
            $userPermission = "";
        }else{
            $userPermission = 'Customer.id IN (SELECT customer_id FROM customer_companies WHERE company_id IN (SELECT company_id FROM user_companies WHERE user_id ='.$user['User']['id'].'))';
        }
        $customers = $this->Customer->find('first', array(
                    'conditions' => array('OR' => array(
                            'Customer.id ' => $id,                            
                        ), 'Customer.is_active' => 1, $userPermission
                    ),
                ));
        $result['email'] = $customers['Customer']['email'];
        $result['address'] = $customers['Customer']['address'];
        $result['sex'] = $customers['Customer']['sex'];
        $result['main_number'] = $customers['Customer']['main_number'];
        $result['dob'] = $customers['Customer']['dob'];
        echo json_encode($result);
        exit;
    }
    
    function exportExcel(){
        $this->layout = 'ajax';
        if (isset($_POST['action']) && $_POST['action'] == 'export') {
            $user = $this->getCurrentUser();
            $filename = "public/report/customer_export.csv";
            $fp = fopen($filename, "wb");
            $excelContent = 'MembershipCards' . "\n\n";
            $excelContent .= TABLE_NO . "\t" . TABLE_CUSTOMER_GROUP. "\t" . TABLE_CODE. "\t" . TABLE_NAME. "\t" . TABLE_NAME_IN_KHMER. "\t" . TABLE_SEX;
            if($user['User']['id'] == 1 || $user['User']['id'] == 57){
                $conditionUser = "";
            }else{
                $conditionUser = " AND id IN (SELECT customer_id FROM customer_companies WHERE company_id IN (SELECT company_id FROM user_companies WHERE user_id = ".$user['User']['id']."))";
            }
            $query = mysql_query('SELECT id, (SELECT GROUP_CONCAT(name) FROM cgroups WHERE id IN (SELECT cgroup_id FROM customer_cgroups WHERE customer_id = customers.id)), customer_code, name, name_kh, sex '
                    . '           FROM customers WHERE is_active=1'.$conditionUser.' ORDER BY customer_code');
            $index = 1;
            while ($data = mysql_fetch_array($query)) {
                $excelContent .= "\n" . $index++ . "\t" . $data[1]. "\t" . $data[2]. "\t" . $data[3]. "\t" . $data[4]. "\t" . $data[5];
            }
            $excelContent = chr(255) . chr(254) . @mb_convert_encoding($excelContent, 'UTF-16LE', 'UTF-8');
            fwrite($fp, $excelContent);
            fclose($fp);
            exit();
        }
    }        
    
    function vendor() {
        $this->layout = "ajax";
    }

    function vendorAjax() {
        $this->layout = "ajax";
    }

    function customer($companyId = null , $customerGroupId = null, $saleId = null) {
        $this->layout = 'ajax';
        if(!empty($companyId)){
            $this->set("saleId", $saleId);
            $this->set("customerGroupId", $customerGroupId);
            $this->set('companyId', $companyId);
        }else{
            exit;
        }
    }

    function customerAjax($companyId, $group = null) {
        $this->layout = 'ajax';
        if(!empty($companyId)){
            $saleId = $_GET['sale_id'];
            $this->set("saleId", $saleId);
            $this->set('companyId', $companyId);
            $this->set('group', $group);
        }else{
            exit;
        }
    }
}

?>