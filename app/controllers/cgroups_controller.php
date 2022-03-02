<?php

class CgroupsController extends AppController {

    var $name = 'Cgroups';
    var $components = array('Helper');

    function index() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        $this->Helper->saveUserActivity($user['User']['id'], 'Customer Group', 'Dashborad');
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
        $user = $this->getCurrentUser();
        $this->Helper->saveUserActivity($user['User']['id'], 'Customer Group', 'View', $id);
        $customers = ClassRegistry::init('CustomerCgroup')->find('all', array(
                    'fields' => array('Customer.*'),
                    'conditions' => array('CustomerCgroup.cgroup_id' => $id, 'Customer.is_active' => 1),
                    'order' => array('Customer.name DESC'))
        );
        $users = ClassRegistry::init('UserCgroup')->find('all', array(
                    'fields' => array('User.*'),
                    'conditions' => array('UserCgroup.cgroup_id' => $id, 'User.is_active' => 1),
                    'order' => array('User.first_name DESC'))
        );
        // $priceTypes = ClassRegistry::init('CgroupPriceType')->find('all', array(
        //             'fields' => array('PriceType.*'),
        //             'conditions' => array('CgroupPriceType.cgroup_id' => $id, 'PriceType.is_active' => 1),
        //             'order' => array('PriceType.name DESC'))
        // );
        $this->data = $this->Cgroup->read(null, $id);
        $this->set(compact('customers', 'users'));
    }

    function add() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        if (!empty($this->data)) {
            $comCheck = 0;
            if(!empty($this->data['Cgroup']['company_id'])){
                $comCheck = implode(",", $this->data['Cgroup']['company_id']);
            }
            if ($this->Helper->checkDouplicate('name', 'cgroups', $this->data['Cgroup']['name'], 'is_active = 1 AND id IN (SELECT cgroup_id FROM cgroup_companies WHERE company_id IN ('.$comCheck.'))')) {
                $this->Helper->saveUserActivity($user['User']['id'], 'Customer Group', 'Save Add New (Name ready existed)');
                echo MESSAGE_DATA_ALREADY_EXISTS_IN_THE_SYSTEM;
                exit;
            } else {
                $dateNow   = date("Y-m-d H:i:s");
                $this->Cgroup->create();
                $user = $this->getCurrentUser();
                $this->data['Cgroup']['sys_code']   = md5(rand().strtotime(date("Y-m-d H:i:s")).$user['User']['id']);
                $this->data['Cgroup']['created']    = $dateNow;
                $this->data['Cgroup']['created_by'] = $user['User']['id'];
                $this->data['Cgroup']['is_active']  = 1;
                if ($this->Cgroup->save($this->data)) {
                    $lastInsertId = $this->Cgroup->getLastInsertId();
                    // Customer group
                    // if (!empty($this->data['Cgroup']['customer_id'])) {
                    //     for ($i = 0; $i < sizeof($this->data['Cgroup']['customer_id']); $i++) {
                    //         mysql_query("INSERT INTO customer_cgroups (customer_id, cgroup_id) VALUES ('" . $this->data['Cgroup']['customer_id'][$i] . "','" . $lastInsertId . "')");
                    //     }
                    // }
                    // Cgroup company
                    if (!empty($this->data['Cgroup']['company_id'])) {
                        for ($i = 0; $i < sizeof($this->data['Cgroup']['company_id']); $i++) {
                            mysql_query("INSERT INTO cgroup_companies (cgroup_id, company_id) VALUES ('" . $lastInsertId . "','" . $this->data['Cgroup']['company_id'][$i] . "')");
                        }
                    }
                    // Cgroup Price Type
                    // if (!empty($this->data['Cgroup']['price_type'])) {
                    //     for ($i = 0; $i < sizeof($this->data['Cgroup']['price_type']); $i++) {
                    //         if($this->data['Cgroup']['price_type'][$i] != ''){
                    //             mysql_query("INSERT INTO cgroup_price_types (cgroup_id, price_type_id) VALUES ('" . $lastInsertId . "','" . $this->data['Cgroup']['price_type'][$i] . "')");
                    //         }
                    //     }
                    // }
                    // Cgroup User
                    if (!empty($this->data['Cgroup']['user_id'])) {
                        for ($i = 0; $i < sizeof($this->data['Cgroup']['user_id']); $i++) {
                            mysql_query("INSERT INTO user_cgroups (cgroup_id, user_id) VALUES ('" . $lastInsertId . "','" . $this->data['Cgroup']['user_id'][$i] . "')");
                        }
                    }
                    // Save User Activity
                    $this->Helper->saveUserActivity($user['User']['id'], 'Customer Group', 'Save Add New', $lastInsertId);
                    echo MESSAGE_DATA_HAS_BEEN_SAVED;
                    exit;
                } else {
                    $this->Helper->saveUserActivity($user['User']['id'], 'Customer Group', 'Save Add New (Error)');
                    echo MESSAGE_DATA_COULD_NOT_BE_SAVED;
                    exit;
                }
            }
        }
        $this->Helper->saveUserActivity($user['User']['id'], 'Customer Group', 'Add New');
        $companies = ClassRegistry::init('Company')->find('list', array('order' => 'id', 'conditions' => array('is_active' => 1, 'id IN (SELECT company_id FROM user_companies WHERE user_id = '.$user['User']['id'].')')));
        $priceTypes = ClassRegistry::init('PriceType')->find('list', array('order' => 'ordering', 'conditions' => array('is_active' => 1, 'is_ecommerce' => 0)));
        $this->set(compact("companies", "priceTypes"));
    }

    function edit($id = null) {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        if (!$id && empty($this->data)) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        if (!empty($this->data)) {
            $comCheck = 0;
            if(!empty($this->data['Cgroup']['company_id'])){
                $comCheck = implode(",", $this->data['Cgroup']['company_id']);
            }
            if ($this->Helper->checkDouplicateEdit('name', 'cgroups', $id, $this->data['Cgroup']['name'], 'is_active = 1 AND id IN (SELECT cgroup_id FROM cgroup_companies WHERE company_id IN ('.$comCheck.'))')) {
                $this->Helper->saveUserActivity($user['User']['id'], 'Customer Group', 'Save Edit (Name ready existed)', $id);
                echo MESSAGE_DATA_ALREADY_EXISTS_IN_THE_SYSTEM;
                exit;
            } else {
                $dateNow   = date("Y-m-d H:i:s");
                $this->data['Cgroup']['modified']    = $dateNow;
                $this->data['Cgroup']['modified_by'] = $user['User']['id'];
                if ($this->Cgroup->save($this->data)) {
                    // mysql_query("DELETE FROM customer_cgroups WHERE cgroup_id=" . $id);
                    // if (!empty($this->data['Cgroup']['customer_id'])) {
                    //     for ($i = 0; $i < sizeof($this->data['Cgroup']['customer_id']); $i++) {
                    //         mysql_query("INSERT INTO customer_cgroups (customer_id,cgroup_id) VALUES ('" . $this->data['Cgroup']['customer_id'][$i] . "','" . $id . "')");
                    //     }
                    // }
                    // Cgroup company
                    mysql_query("DELETE FROM cgroup_companies WHERE cgroup_id=" . $id);
                    if (!empty($this->data['Cgroup']['company_id'])) {
                        for ($i = 0; $i < sizeof($this->data['Cgroup']['company_id']); $i++) {
                            mysql_query("INSERT INTO cgroup_companies (cgroup_id, company_id) VALUES ('" . $id . "','" . $this->data['Cgroup']['company_id'][$i] . "')");
                        }
                    }
                    // Cgroup Price Type
                    // mysql_query("DELETE FROM cgroup_price_types WHERE cgroup_id=" . $id);
                    // if (!empty($this->data['Cgroup']['price_type'])) {
                    //     for ($i = 0; $i < sizeof($this->data['Cgroup']['price_type']); $i++) {
                    //         if($this->data['Cgroup']['price_type'][$i] != ''){
                    //             mysql_query("INSERT INTO cgroup_price_types (cgroup_id, price_type_id) VALUES ('" . $id . "','" . $this->data['Cgroup']['price_type'][$i] . "')");
                    //         }
                    //     }
                    // }
                    // Cgroup User
                    mysql_query("DELETE FROM user_cgroups WHERE cgroup_id=" . $id);
                    if (!empty($this->data['Cgroup']['user_id'])) {
                        for ($i = 0; $i < sizeof($this->data['Cgroup']['user_id']); $i++) {
                            mysql_query("INSERT INTO user_cgroups (cgroup_id, user_id) VALUES ('" . $id . "','" . $this->data['Cgroup']['user_id'][$i] . "')");
                        }
                    }
                    // Save User Activity
                    $this->Helper->saveUserActivity($user['User']['id'], 'Customer Group', 'Save Edit', $id);
                    echo MESSAGE_DATA_HAS_BEEN_SAVED;
                    exit;
                } else {
                    $this->Helper->saveUserActivity($user['User']['id'], 'Customer Group', 'Save Edit (Error)', $id);
                    echo MESSAGE_DATA_COULD_NOT_BE_SAVED;
                    exit;
                }
            }
        }
        if (empty($this->data)) {
            $this->Helper->saveUserActivity($user['User']['id'], 'Customer Group', 'Edit', $id);
            $this->data = $this->Cgroup->read(null, $id);
            $companySellecteds = ClassRegistry::init('CgroupCompany')->find('list', array('fields' => array('id', 'company_id'), 'order' => 'id', 'conditions' => array('cgroup_id' => $id)));
            $companySellected = array();
            foreach ($companySellecteds as $cs) {
                array_push($companySellected, $cs);
            }
            $companies = ClassRegistry::init('Company')->find('list', array('order' => 'id', 'conditions' => array('is_active' => 1, 'id IN (SELECT company_id FROM user_companies WHERE user_id = '.$user['User']['id'].')')));
            $priceTypeSellecteds = ClassRegistry::init('CgroupPriceType')->find('list', array('fields' => array('id', 'price_type_id'), 'order' => 'id', 'conditions' => array('cgroup_id' => $id)));
            $priceTypeSellected = array();
            foreach ($priceTypeSellecteds as $cs) {
                array_push($priceTypeSellected, $cs);
            }
            $priceTypes = ClassRegistry::init('PriceType')->find('list', array('order' => 'ordering', 'conditions' => array('is_active' => 1, 'is_ecommerce' => 0)));
            $this->set(compact('companies', 'companySellected', 'priceTypes', 'priceTypeSellected'));
        }
    }

    function delete($id = null) {
        if (!$id) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $dateNow  = date("Y-m-d H:i:s");
        $user = $this->getCurrentUser();
        $this->data = $this->Cgroup->read(null, $id);
        mysql_query("UPDATE `cgroups` SET `is_active`=2, `modified`='".$dateNow."', `modified_by`=".$user['User']['id']." WHERE `id`=".$id.";");
        // Save User Activity
        $this->Helper->saveUserActivity($user['User']['id'], 'Customer Group', 'Delete', $id);
        echo MESSAGE_DATA_HAS_BEEN_DELETED;
        exit;
    }
    
    function searchCustomer() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        $userPermission = 'Customer.id IN (SELECT customer_id FROM customer_companies WHERE company_id IN (SELECT company_id FROM user_companies WHERE user_id ='.$user['User']['id'].'))';
        $customers = ClassRegistry::init('Customer')->find('all', array(
                    'conditions' => array('OR' => array(
                            'Customer.name LIKE' => '%' . $this->params['url']['q'] . '%',
                            'Customer.name_kh LIKE' => '%' . $this->params['url']['q'] . '%',
                            'Customer.customer_code LIKE' => '%' . $this->params['url']['q'] . '%',
                            'Customer.main_number LIKE' => '%' . $this->params['url']['q'] . '%',
                            'Customer.mobile_number LIKE' => '%' . $this->params['url']['q'] . '%',
                            'Customer.other_number LIKE' => '%' . $this->params['url']['q'] . '%',
                            'Customer.email LIKE' => '%' . $this->params['url']['q'] . '%',
                            'Customer.fax LIKE' => '%' . $this->params['url']['q'] . '%',
                        ), 'Customer.is_active' => 1, $userPermission
                    ),
                ));

        $this->set(compact('customers'));
    }

    function customer($companyId = null) {
        $this->layout = 'ajax';
        $this->set(compact('companyId'));
    }

    function customer_ajax($companyId = null) {
        $this->layout = 'ajax';
        $this->set(compact('companyId'));
    }
    
    function exportExcel(){
        $this->layout = 'ajax';
        if (isset($_POST['action']) && $_POST['action'] == 'export') {
            $user = $this->getCurrentUser();
            $this->Helper->saveUserActivity($user['User']['id'], 'Customer Group', 'Export to Excel');
            $filename = "public/report/customer_group_export.csv";
            $fp = fopen($filename, "wb");
            $excelContent = 'Customer Group' . "\n\n";
            $excelContent .= TABLE_NO . "\t" . TABLE_NAME;
            if($user['User']['id'] == 1 || $user['User']['id'] == 57){
                $conditionUser = "";
            }else{
                $conditionUser = " AND id IN (SELECT cgroup_id FROM cgroup_companies WHERE company_id IN (SELECT company_id FROM user_companies WHERE user_id = ".$user['User']['id']."))";
            }
            $query = mysql_query('SELECT id,  name '
                    . '           FROM cgroups WHERE is_active=1'.$conditionUser.' ORDER BY name');
            $index = 1;
            while ($data = mysql_fetch_array($query)) {
                $excelContent .= "\n" . $index++ . "\t" . $data[1];
            }
            $excelContent = chr(255) . chr(254) . @mb_convert_encoding($excelContent, 'UTF-16LE', 'UTF-8');
            fwrite($fp, $excelContent);
            fclose($fp);
            exit();
        }
    }

}

?>