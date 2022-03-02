<?php

class ChartAccountsController extends AppController {

    var $name = 'ChartAccounts';
    var $components = array('Helper', 'Coa');

    function index() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        if (isset($_POST['action']) && $_POST['action'] == 'export') {
            $this->Helper->saveUserActivity($user['User']['id'], 'Chart Account', 'Export to Excel');
            if($_POST['company_id'] == 'all'){
                $conditionCompany = "SELECT company_id FROM user_companies WHERE user_id = ".$user['User']['id']."";
            }else{
                $conditionCompany = $_POST['company_id'];
            }
            $filename = "public/report/chart_account.csv";
            $fp = fopen($filename, "wb");
            $excelContent = 'Chart of Account' . "\n\n";
            $excelContent .= TABLE_NO . "\t" . TABLE_ACCOUNT_TYPE . "\t" . TABLE_ACCOUNT_CODE_AND_DESCRIPTION . "\t" . TABLE_ACCOUNT_GROUP . "\t" . ACCOUNT_BALANCE . "\t" . TABLE_STATUS;
            $query = mysql_query('  SELECT
                                        c.id,
                                        ct.name,
                                        CONCAT(
                                            IF(parent_id IS NOT NULL,
                                                IF((SELECT parent_id FROM chart_accounts WHERE id=c.parent_id) IS NOT NULL,
                                                    IF((SELECT parent_id FROM chart_accounts WHERE id=(SELECT parent_id FROM chart_accounts WHERE id=c.parent_id)) IS NOT NULL,
                                                        IF((SELECT parent_id FROM chart_accounts WHERE id=(SELECT parent_id FROM chart_accounts WHERE id=(SELECT parent_id FROM chart_accounts WHERE id=c.parent_id))) IS NOT NULL,
                                                            IF((SELECT parent_id FROM chart_accounts WHERE id=(SELECT parent_id FROM chart_accounts WHERE id=(SELECT parent_id FROM chart_accounts WHERE id=(SELECT parent_id FROM chart_accounts WHERE id=c.parent_id)))) IS NOT NULL,
                                                                "                    ",
                                                            "                "),
                                                        "            "),
                                                    "        "),
                                                "    "),
                                            ""),
                                            account_codes,
                                            " · ",
                                            account_description
                                        ),
                                        cg.name,
                                        (SELECT SUM(debit) FROM general_ledgers gl INNER JOIN general_ledger_details gld ON gl.id=gld.general_ledger_id WHERE gld.chart_account_id=c.id AND gl.is_active=1)-(SELECT SUM(credit) FROM general_ledgers gl INNER JOIN general_ledger_details gld ON gl.id=gld.general_ledger_id WHERE gld.chart_account_id=c.id AND gl.is_active=1),
                                        c.is_active
                                    FROM chart_accounts c INNER JOIN chart_account_types ct ON c.chart_account_type_id=ct.id INNER JOIN chart_account_groups cg ON c.chart_account_group_id=cg.id
                                    WHERE c.is_active!=2 AND c.id IN (SELECT chart_account_id FROM chart_account_companies WHERE company_id IN ('.$conditionCompany.'))
                                    ORDER BY account_codes');
            $index = 1;
            while ($data = mysql_fetch_array($query)) {
                $excelContent .= "\n" . $index++ . "\t" . $data[1] . "\t" . $data[2] . "\t" . $data[3] . "\t" . ($data[4] != '' && $data[4] != 0 ? ($data[1] == 'Accounts Payable' || $data[1] == 'Credit Card' || $data[1] == 'Other Current Liability' || $data[1] == 'Long Term Liability' || $data[1] == 'Equity' || $data[1] == 'Income' || $data[1] == 'Other Income' ? number_format($data[4] * -1, 2) : number_format($data[4], 2)) : '') . "\t" . ($data[5] == 1 ? TABLE_ACTIVE : TABLE_INACTIVE);
            }
            $excelContent = chr(255) . chr(254) . @mb_convert_encoding($excelContent, 'UTF-16LE', 'UTF-8');
            fwrite($fp, $excelContent);
            fclose($fp);
            exit();
        }
        $this->Helper->saveUserActivity($user['User']['id'], 'Chart Account', 'Dashborad');
    }

    function ajax($companyId) {
        $this->layout = 'ajax';
        $this->set(compact('companyId'));
    }

    function view($id = null) {
        $this->layout = 'ajax';
        if (!$id) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $user = $this->getCurrentUser();
        $this->Helper->saveUserActivity($user['User']['id'], 'Chart Account', 'View', $id);
        $this->set('chartAccount', $this->ChartAccount->read(null, $id));
        ClassRegistry::init('ChartAccountType')->id = $this->ChartAccount->field('chart_account_type_id');
        $this->set('chartAccountType', ClassRegistry::init('ChartAccountType')->field('name'));
        ClassRegistry::init('ChartAccountGroup')->id = $this->ChartAccount->field('chart_account_group_id');
        $this->set('chartAccountGroup', ClassRegistry::init('ChartAccountGroup')->field('name'));
    }

    function add() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        if (!empty($this->data)) {
            $comCheck = 0;
            if(!empty($this->data['ChartAccount']['company_id'])){
                $comCheck = implode(",", $this->data['ChartAccount']['company_id']);
            }
            if ($this->Helper->checkDouplicate('account_codes', 'chart_accounts', $this->data['ChartAccount']['account_codes'], 'is_active = 1 AND id IN (SELECT chart_account_id FROM chart_account_companies WHERE company_id IN ('.$comCheck.'))')) {
                $this->Helper->saveUserActivity($user['User']['id'], 'Chart Account', 'Save Add New (Name ready existed)');
                echo MESSAGE_DATA_ALREADY_EXISTS_IN_THE_SYSTEM;
                exit;
            } else {
                $dateNow   = date("Y-m-d H:i:s");
                $this->ChartAccount->create();
                $this->data['ChartAccount']['sys_code']   = md5(rand().strtotime(date("Y-m-d H:i:s")).$user['User']['id']);
                $this->data['ChartAccount']['created']    = $dateNow;
                $this->data['ChartAccount']['created_by'] = $user['User']['id'];
                $this->data['ChartAccount']['is_active']  = 1;
                if ($this->ChartAccount->save($this->data)) {
                    $lastInsertId = $this->ChartAccount->getLastInsertId();
                    // coa company
                    if (isset($this->data['ChartAccount']['company_id'])) {
                        for ($i = 0; $i < sizeof($this->data['ChartAccount']['company_id']); $i++) {
                            mysql_query("INSERT INTO chart_account_companies (chart_account_id,company_id) VALUES ('" . $lastInsertId . "','" . $this->data['ChartAccount']['company_id'][$i] . "')");
                        }
                    }
                    // Save User Activity
                    $this->Helper->saveUserActivity($user['User']['id'], 'Chart Account', 'Save Add New', $lastInsertId);
                    echo MESSAGE_DATA_HAS_BEEN_SAVED;
                    exit;
                } else {
                    $this->Helper->saveUserActivity($user['User']['id'], 'Chart Account', 'Save Add New (Error)');
                    echo MESSAGE_DATA_COULD_NOT_BE_SAVED;
                    exit;
                }
            }
        }
        $this->Helper->saveUserActivity($user['User']['id'], 'Chart Account', 'Add New');
        $chartAccountTypes = ClassRegistry::init('ChartAccountType')->find("list");
        $chartAccountGroups = $this->Coa->chartAccountGroupList();
        $this->set(compact("chartAccountTypes", "chartAccountGroups"));
    }

    function edit($id = null) {
        $this->layout = 'ajax';
        if (!$id && empty($this->data)) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $user = $this->getCurrentUser();
        if (!empty($this->data)) {
            $comCheck = 0;
            if(!empty($this->data['ChartAccount']['company_id'])){
                $comCheck = implode(",", $this->data['ChartAccount']['company_id']);
            }
            if ($this->Helper->checkDouplicateEdit('account_codes', 'chart_accounts', $id, $this->data['ChartAccount']['account_codes'], 'is_active = 1 AND id IN (SELECT chart_account_id FROM chart_account_companies WHERE company_id IN ('.$comCheck.'))')) {
                $this->Helper->saveUserActivity($user['User']['id'], 'Chart Account', 'Save Edit (Name ready existed)', $id);
                echo MESSAGE_DATA_ALREADY_EXISTS_IN_THE_SYSTEM;
                exit;
            }
            $dateNow  = date("Y-m-d H:i:s");
            $this->data['ChartAccount']['modified']    = $dateNow;
            $this->data['ChartAccount']['modified_by'] = $user['User']['id'];
            if ($this->ChartAccount->save($this->data)) {
                // coa company
                mysql_query("DELETE FROM chart_account_companies WHERE chart_account_id=" . $id);
                if (isset($this->data['ChartAccount']['company_id'])) {
                    for ($i = 0; $i < sizeof($this->data['ChartAccount']['company_id']); $i++) {
                        mysql_query("INSERT INTO chart_account_companies (chart_account_id,company_id) VALUES ('" . $id . "','" . $this->data['ChartAccount']['company_id'][$i] . "')");
                    }
                }
                // Save User Activity
                $this->Helper->saveUserActivity($user['User']['id'], 'Chart Account', 'Save Edit', $id);
                echo MESSAGE_DATA_HAS_BEEN_SAVED;
                exit;
            } else {
                $this->Helper->saveUserActivity($user['User']['id'], 'Chart Account', 'Save Edit (Error)', $id);
                echo MESSAGE_DATA_COULD_NOT_BE_SAVED;
                exit;
            }
        }
        if (empty($this->data)) {
            $this->Helper->saveUserActivity($user['User']['id'], 'Chart Account', 'Edit', $id);
            $this->data = $this->ChartAccount->read(null, $id);
            $chartAccountTypes = ClassRegistry::init('ChartAccountType')->find("list", array("conditions" => array("ChartAccountType.id IN (13, 14)")));
            $chartAccountGroups = $this->Coa->chartAccountGroupList();
            $this->set(compact("chartAccountTypes", "chartAccountGroups"));
        }
    }

    function delete($id = null) {
        if (!$id) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        
        // check if coa and it's child already in used
        $coaList=array();
        $coaList[]=$id;
        $queryChild[0]=mysql_query("SELECT id FROM chart_accounts WHERE is_active=1 AND parent_id=".$id);
        if(mysql_num_rows($queryChild[0])){
            while($dataChild[0]=mysql_fetch_array($queryChild[0])){
                $coaList[]=$dataChild[0]['id'];
                $queryChild[1]=mysql_query("SELECT id FROM chart_accounts WHERE is_active=1 AND parent_id=".$dataChild[0]['id']);
                if(mysql_num_rows($queryChild[1])){
                    while($dataChild[1]=mysql_fetch_array($queryChild[1])){
                        $coaList[]=$dataChild[1]['id'];
                        $queryChild[2]=mysql_query("SELECT id FROM chart_accounts WHERE is_active=1 AND parent_id=".$dataChild[1]['id']);
                        if(mysql_num_rows($queryChild[2])){
                            while($dataChild[2]=mysql_fetch_array($queryChild[2])){
                                $coaList[]=$dataChild[2]['id'];
                                $queryChild[3]=mysql_query("SELECT id FROM chart_accounts WHERE is_active=1 AND parent_id=".$dataChild[2]['id']);
                                if(mysql_num_rows($queryChild[3])){
                                    while($dataChild[3]=mysql_fetch_array($queryChild[3])){
                                        $coaList[]=$dataChild[3]['id'];
                                        $queryChild[4]=mysql_query("SELECT id FROM chart_accounts WHERE is_active=1 AND parent_id=".$dataChild[3]['id']);
                                        if(mysql_num_rows($queryChild[4])){
                                            while($dataChild[4]=mysql_fetch_array($queryChild[4])){
                                                $coaList[]=$dataChild[4]['id'];
                                                $queryChild[5]=mysql_query("SELECT id FROM chart_accounts WHERE is_active=1 AND parent_id=".$dataChild[4]['id']);
                                                if(mysql_num_rows($queryChild[5])){
                                                    while($dataChild[5]=mysql_fetch_array($queryChild[5])){
                                                        $coaList[]=$dataChild[5]['id'];
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
        $queryGl=mysql_query("SELECT gl.id FROM general_ledgers gl INNER JOIN general_ledger_details gld ON gl.id=gld.general_ledger_id WHERE is_active=1 AND chart_account_id IN (" . implode(",", $coaList) . ")");
        $notAllowDelete=mysql_num_rows($queryGl);
        $user = $this->getCurrentUser();
        if(!$notAllowDelete){
            $dateNow  = date("Y-m-d H:i:s");
            $this->data = $this->ChartAccount->read(null, $id);
            mysql_query("UPDATE `chart_accounts` SET `is_active`=2, `modified`='".$dateNow."', `modified_by`=".$user['User']['id']." WHERE `id`=".$id.";");
            // Save User Activity
            $this->Helper->saveUserActivity($user['User']['id'], 'Chart Account', 'Delete', $id);
            echo MESSAGE_DATA_HAS_BEEN_DELETED;
            exit;
        }else{
            echo MESSAGE_COA_ALREADY_IN_USED;
            exit;
        }
    }

    function status($id = null, $status = 0) {
        if (!$id) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $dateNow  = date("Y-m-d H:i:s");
        $user = $this->getCurrentUser();
        $this->data = $this->ChartAccount->read(null, $id);
        mysql_query("UPDATE `chart_accounts` SET `is_active`=".$status.", `modified`='".$dateNow."', `modified_by`=".$user['User']['id']." WHERE `id`=".$id.";");
        // Save User Activity
        $this->Helper->saveUserActivity($user['User']['id'], 'Chart Account', 'Change Status', $id);
        echo MESSAGE_DATA_HAS_BEEN_SAVED;
        exit;
    }

}

?>