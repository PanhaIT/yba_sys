<?php

class ServicesController extends AppController {

    var $name = 'Services';
    var $components = array('Helper');

    function index() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        // User Activity
        $this->Helper->saveUserActivity($user['User']['id'], 'Service', 'Dashboard');
    }

    function ajax() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        $companies = ClassRegistry::init('Company')->find('list',
                        array(
                            'joins' => array(
                                array('table' => 'user_companies', 'type' => 'inner', 'conditions' => array('user_companies.company_id=Company.id')
                                )
                            ),
                            'conditions' => array('Company.is_active = 1', 'user_companies.user_id=' . $user['User']['id'])
                        )
        );
        $this->set(compact('companies'));
    }

    function view($id = null) {
        $this->layout = 'ajax';
        if (!$id) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $user = $this->getCurrentUser();
        // User Activity
        $this->Helper->saveUserActivity($user['User']['id'], 'Service', 'View', $id);
        $this->set('service', $this->Service->read(null, $id));
    }

    function add() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        if (!empty($this->data)) {
            if ($this->Helper->checkDouplicate('code', 'services', $this->data['Service']['code'], 'is_active = 1 AND company_id = '.$this->data['Service']['company_id'])) {
                // User Activity
                $this->Helper->saveUserActivity($user['User']['id'], 'Service', 'Save Add New (Name ready existed)');
                echo MESSAGE_CODE_ALREADY_EXISTS_IN_THE_SYSTEM;
                exit;
            } else {
                $dateNow   = date("Y-m-d H:i:s");
                $this->Service->create();
                $this->data['Service']['sys_code']   = md5(rand().strtotime(date("Y-m-d H:i:s")).$user['User']['id']);
                $this->data['Service']['created']    = $dateNow;
                $this->data['Service']['created_by'] = $user['User']['id'];
                $this->data['Service']['is_active'] = 1;
                if ($this->Service->save($this->data)) {
                    $lastInsertId = $this->Service->id;
                    // Service Branch
                    if (!empty($this->data['Service']['branch_id'])) {
                        for ($i = 0; $i < sizeof($this->data['Service']['branch_id']); $i++) {
                            mysql_query("INSERT INTO service_branches (service_id,branch_id) VALUES ('" . $lastInsertId . "','" . $this->data['Service']['branch_id'][$i] . "')");
                        }
                    }
                    // Save User Activity
                    $this->Helper->saveUserActivity($user['User']['id'], 'Service', 'Save Add New', $this->Service->id);
                    echo MESSAGE_DATA_HAS_BEEN_SAVED;
                    exit;
                } else {
                    // User Activity
                    $this->Helper->saveUserActivity($user['User']['id'], 'Service', 'Save Add New (Error)');
                    echo MESSAGE_DATA_COULD_NOT_BE_SAVED;
                    exit;
                }
            }
        }
        // User Activity
        $this->Helper->saveUserActivity($user['User']['id'], 'Service', 'Add New');
        $branches = ClassRegistry::init('Branch')->find('list', array('joins' => array(array('table' => 'user_branches', 'type' => 'inner', 'conditions' => array('user_branches.branch_id=Branch.id'))), 'conditions' => array('Branch.is_active = 1', 'user_branches.user_id=' . $user['User']['id'])));
        $sections = ClassRegistry::init('Section')->find("list", array("conditions" => array("Section.is_active != 2", "Section.id IN (SELECT section_id FROM section_companies WHERE company_id IN (SELECT company_id FROM user_companies WHERE user_id = ".$user['User']['id']."))")));
        $uoms = ClassRegistry::init('Uom')->find("list", array("conditions" => array("Uom.is_active = 1")));
        $serviceAccount   = ClassRegistry::init('AccountType')->findById(9);
        $serviceAccountId = $serviceAccount['AccountType']['chart_account_id'];
        $this->set(compact('branches', 'sections', 'serviceAccountId', 'uoms'));
    }

    function edit($id = null) {
        $this->layout = 'ajax';
        if (!$id && empty($this->data)) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $user = $this->getCurrentUser();
        if (!empty($this->data)) {
            if ($this->Helper->checkDouplicateEdit('code', 'services', $id, $this->data['Service']['code'], 'is_active = 1 AND company_id = '.$this->data['Service']['company_id'])) {
                // User Activity
                $this->Helper->saveUserActivity($user['User']['id'], 'Service', 'Save Edit (Name ready existed)', $id);
                echo MESSAGE_CODE_ALREADY_EXISTS_IN_THE_SYSTEM;
                exit;
            } else {
//                $r = 0;
//                $restCode = array();
                $dateNow  = date("Y-m-d H:i:s");
                $this->data['Service']['modified']    = $dateNow;
                $this->data['Service']['modified_by'] = $user['User']['id'];
                if ($this->Service->save($this->data)) {
                    // Convert to REST
//                    $restCode[$r] = $this->Helper->convertToDataSync($this->data['Service'], 'services');
//                    $restCode[$r]['company_id']  = $this->Helper->getSQLSysCode("companies", $this->data['Service']['company_id']);
//                    $restCode[$r]['section_id']  = $this->Helper->getSQLSysCode("sections", $this->data['Service']['section_id']);
//                    $restCode[$r]['modified_by'] = $this->Helper->getSQLSysCode("users", $user['User']['id']);
//                    $restCode[$r]['dbtodo'] = 'services';
//                    $restCode[$r]['actodo'] = 'ut';
//                    $restCode[$r]['con']    = "sys_code = '".$this->data['Service']['sys_code']."'";
//                    $r++;
                    // Service Branch
                    mysql_query("DELETE FROM service_branches WHERE service_id=" . $id);
                    // Convert to REST
//                    $restCode[$r]['dbtodo'] = 'service_branches';
//                    $restCode[$r]['actodo'] = 'dt';
//                    $restCode[$r]['con']    = "service_id = ".$this->data['Service']['sys_code'];
//                    $r++;
                    if (!empty($this->data['Service']['branch_id'])) {
                        for ($i = 0; $i < sizeof($this->data['Service']['branch_id']); $i++) {
                            mysql_query("INSERT INTO service_branches (service_id,branch_id) VALUES ('" . $id . "','" . $this->data['Service']['branch_id'][$i] . "')");
                            // Convert to REST
//                            $restCode[$r]['service_id'] = $this->data['Service']['sys_code'];
//                            $restCode[$r]['branch_id']  = $this->Helper->getSQLSysCode("branches", $this->data['Service']['branch_id'][$i]);
//                            $restCode[$r]['dbtodo']     = 'service_branches';
//                            $restCode[$r]['actodo']     = 'is';
//                            $r++;
                        }
                    }
                    // Save File Send
//                    $this->Helper->sendFileToSync($restCode, 0, 0);
                    // Save User Activity
                    $this->Helper->saveUserActivity($user['User']['id'], 'Service', 'Save Edit', $id);
                    echo MESSAGE_DATA_HAS_BEEN_SAVED;
                    exit;
                } else {
                    // User Activity
                    $this->Helper->saveUserActivity($user['User']['id'], 'Service', 'Save Edit (Error)', $id);
                    echo MESSAGE_DATA_COULD_NOT_BE_SAVED;
                    exit;
                }
            }
        }
        if (empty($this->data)) {
            // User Activity
            $this->Helper->saveUserActivity($user['User']['id'], 'Service', 'Edit', $id);
            $companies = ClassRegistry::init('Company')->find('list',
                            array(
                                'joins' => array(
                                    array('table' => 'user_companies', 'type' => 'inner', 'conditions' => array('user_companies.company_id=Company.id')
                                    )
                                ),
                                'conditions' => array('Company.is_active = 1', 'user_companies.user_id=' . $user['User']['id'])
                            )
            );
            $branches  = ClassRegistry::init('Branch')->find('list', array('joins' => array(array('table' => 'user_branches', 'type' => 'inner', 'conditions' => array('user_branches.branch_id=Branch.id'))), 'conditions' => array('Branch.is_active = 1', 'user_branches.user_id=' . $user['User']['id'])));
            $sections = ClassRegistry::init('Section')->find("list", array("conditions" => array("Section.is_active != 2", "Section.id IN (SELECT section_id FROM section_companies WHERE company_id IN (SELECT company_id FROM user_companies WHERE user_id = ".$user['User']['id']."))")));
            $uoms = ClassRegistry::init('Uom')->find("list", array("conditions" => array("Uom.is_active = 1")));
            $this->set(compact('companies', 'branches', 'sections', 'uoms'));
            $this->data = $this->Service->read(null, $id);
        }
    }

    function delete($id = null) {
        if (!$id) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
//        $r = 0;
//        $restCode = array();
        $dateNow  = date("Y-m-d H:i:s");
        $user = $this->getCurrentUser();
        $this->data = $this->Service->read(null, $id);
        // User Activity
        mysql_query("UPDATE `services` SET `is_active`=2, `modified`='".$dateNow."', `modified_by`=".$user['User']['id']." WHERE `id`=".$id.";");
        // Convert to REST
//        $restCode[$r]['is_active']   = 2;
//        $restCode[$r]['modified']    = $dateNow;
//        $restCode[$r]['modified_by'] = $this->Helper->getSQLSysCode("users", $user['User']['id']);
//        $restCode[$r]['dbtodo'] = 'services';
//        $restCode[$r]['actodo'] = 'ut';
//        $restCode[$r]['con']    = "sys_code = '".$this->data['Service']['sys_code']."'";
        // Save File Send
//        $this->Helper->sendFileToSync($restCode, 0, 0);
        // Save User Activity
        $this->Helper->saveUserActivity($user['User']['id'], 'Service', 'Delete', $id);
        echo MESSAGE_DATA_HAS_BEEN_DELETED;
        exit;
    }
    
    function exportExcel(){
        $this->layout = 'ajax';
        if (isset($_POST['action']) && $_POST['action'] == 'export') {
            $user = $this->getCurrentUser();
            // User Activity
            $this->Helper->saveUserActivity($user['User']['id'], 'Service', 'Export to Excel');
            $filename = "public/report/service_export.csv";
            $fp = fopen($filename, "wb");
            $excelContent = 'Services' . "\n\n";
            $excelContent .= TABLE_NO . "\t" . MENU_BRANCH. "\t" . TABLE_SECTION. "\t" . TABLE_SKU. "\t" . TABLE_NAME. "\t" . SALES_ORDER_UNIT_PRICE . "\t" . TABLE_UOM  . "\t" . GENERAL_DESCRIPTION. "\t" . TABLE_ACCOUNT;
            if($user['User']['id'] == 1 || $user['User']['id'] == 57){
                $conditionUser = "";
            }else{
                $conditionUser = " AND services.company_id IN (SELECT company_id FROM user_companies WHERE user_id = ".$user['User']['id'].")";
            }
            $query = mysql_query('SELECT services.id, (SELECT name FROM branches WHERE id = services.branch_id), sections.name, services.code, services.name, services.unit_price, (SELECT abbr FROM uoms WHERE id = services.uom_id), services.description, (SELECT CONCAT(account_codes,"-",account_description) FROM chart_accounts WHERE id = services.chart_account_id) '
                    . '           FROM services INNER JOIN sections ON sections.id = services.section_id WHERE services.is_active=1'.$conditionUser.' ORDER BY services.name');
            $index = 1;
            while ($data = mysql_fetch_array($query)) {
                $excelContent .= "\n" . $index++ . "\t" . $data[1]. "\t" . $data[2]. "\t" . $data[3]. "\t" . $data[4]. "\t" . $data[5]. "\t" . $data[6]. "\t" . $data[7]. "\t" . $data[8];
            }
            $excelContent = chr(255) . chr(254) . @mb_convert_encoding($excelContent, 'UTF-16LE', 'UTF-8');
            fwrite($fp, $excelContent);
            fclose($fp);
            exit();
        }
    }
    
    function addSection(){
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        if (!empty($this->data)) {
            $this->loadModel('Section');
            $result = array();
            $comCheck = $this->data['company_id'];
            if ($this->Helper->checkDouplicate('name', 'sections', $this->data['Section']['name'], 'is_active = 1 AND id IN (SELECT section_id FROM section_companies WHERE company_id IN ('.$comCheck.'))')) {
                $this->Helper->saveUserActivity($user['User']['id'], 'Section', 'Save Quick Add New (Name ready existed)');
                $result['error'] = 2;
                echo json_encode($result);
                exit;
            } else {
//                $r = 0;
//                $restCode  = array();
                $dateNow   = date("Y-m-d H:i:s");
                $this->Section->create();
                $this->data['Section']['sys_code']   = md5(rand().strtotime(date("Y-m-d H:i:s")).$user['User']['id']);
                $this->data['Section']['created']    = $dateNow;
                $this->data['Section']['created_by'] = $user['User']['id'];
                $this->data['Section']['is_active']  = 1;
                if ($this->Section->save($this->data)) {
                    $sectionId = $this->Section->getLastInsertId();
                    // Convert to REST
//                    $restCode[$r] = $this->Helper->convertToDataSync($this->data['Section'], 'sections');
//                    $restCode[$r]['created_by'] = $this->Helper->getSQLSysCode("users", $user['User']['id']);
//                    $restCode[$r]['modified']   = $dateNow;
//                    $restCode[$r]['dbtodo']     = 'sections';
//                    $restCode[$r]['actodo']     = 'is';
//                    $r++;
                    // Section Company
                    if (isset($this->data['company_id'])) {
                        mysql_query("INSERT INTO section_companies (section_id, company_id) VALUES ('" . $sectionId . "', '".$this->data['company_id']. "')");
                        // Convert to REST
//                        $restCode[$r]['section_id'] = $this->data['Section']['sys_code'];
//                        $restCode[$r]['company_id'] = $this->Helper->getSQLSysCode("companies", $this->data['company_id']);
//                        $restCode[$r]['dbtodo']     = 'section_companies';
//                        $restCode[$r]['actodo']     = 'is';
//                        $r++;
                    }
                    // Save File Send
//                    $this->Helper->sendFileToSync($restCode, 0, 0);
                    // Save User Activity
                    $this->Helper->saveUserActivity($user['User']['id'], 'Section', 'Save Quick Add New', $sectionId);
                    $result['error']  = 0;
                    $result['option'] = '<option value="">'.INPUT_SELECT.'</option>';
                    $sections = ClassRegistry::init('Section')->find('all', array('order' => 'name', 'conditions' => array('is_active' => 1)));
                    foreach($sections AS $section){
                        $selected = '';
                        if($section['Section']['id'] == $sectionId){
                            $selected = 'selected="selected"';
                        }
                        $result['option'] .= '<option value="'.$section['Section']['id'].'" '.$selected.'>'.$section['Section']['name'].'</option>';
                    }
                    echo json_encode($result);
                    exit;
                } else {
                    // User Activity
                    $this->Helper->saveUserActivity($user['User']['id'], 'Section', 'Save Quick Add New (Error)');
                    $result['error'] = 1;
                    echo json_encode($result);
                    exit;
                }
            }
        }
        // User Activity
        $this->Helper->saveUserActivity($user['User']['id'], 'Section', 'Quick Add New');
        $companies = ClassRegistry::init('Company')->find('list', array('joins' => array(array('table' => 'user_companies', 'type' => 'inner', 'conditions' => array('user_companies.company_id=Company.id'))), 'conditions' => array('Company.is_active = 1', 'user_companies.user_id=' . $user['User']['id'])));
        $this->set(compact("companies"));
    }

}

?>