<?php

class ClassesController extends AppController {

    var $name = 'Classes';
    var $components = array('Helper');

    function index() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        $this->Helper->saveUserActivity($user['User']['id'], 'Class', 'Dashborad');
        // update ordering
        $query[0] = mysql_query("SELECT id FROM classes WHERE ISNULL(parent_id) AND is_active=1 ORDER BY name");
        $index[0] = 1;
        while ($data[0] = mysql_fetch_array($query[0])) {
            mysql_query("UPDATE classes SET ordering='" . str_pad($index[0]++, 3, "0", STR_PAD_LEFT) . "' WHERE id=" . $data[0]['id']);
            $query[1] = mysql_query("SELECT id,(SELECT ordering FROM classes WHERE id=c.parent_id) AS parent_ordering FROM classes c WHERE parent_id=" . $data[0]['id'] . " AND is_active=1 ORDER BY name");
            $index[1] = 1;
            while ($data[1] = mysql_fetch_array($query[1])) {
                mysql_query("UPDATE classes SET ordering='" . $data[1]['parent_ordering'] . str_pad($index[1]++, 3, "0", STR_PAD_LEFT) . "' WHERE id=" . $data[1]['id']);
                $query[2] = mysql_query("SELECT id,(SELECT ordering FROM classes WHERE id=c.parent_id) AS parent_ordering FROM classes c WHERE parent_id=" . $data[1]['id'] . " AND is_active=1 ORDER BY name");
                $index[2] = 1;
                while ($data[2] = mysql_fetch_array($query[2])) {
                    mysql_query("UPDATE classes SET ordering='" . $data[2]['parent_ordering'] . str_pad($index[2]++, 3, "0", STR_PAD_LEFT) . "' WHERE id=" . $data[2]['id']);
                    $query[3] = mysql_query("SELECT id,(SELECT ordering FROM classes WHERE id=c.parent_id) AS parent_ordering FROM classes c WHERE parent_id=" . $data[2]['id'] . " AND is_active=1 ORDER BY name");
                    $index[3] = 1;
                    while ($data[3] = mysql_fetch_array($query[3])) {
                        mysql_query("UPDATE classes SET ordering='" . $data[3]['parent_ordering'] . str_pad($index[3]++, 3, "0", STR_PAD_LEFT) . "' WHERE id=" . $data[3]['id']);
                        $query[4] = mysql_query("SELECT id,(SELECT ordering FROM classes WHERE id=c.parent_id) AS parent_ordering FROM classes c WHERE parent_id=" . $data[3]['id'] . " AND is_active=1 ORDER BY name");
                        $index[4] = 1;
                        while ($data[4] = mysql_fetch_array($query[4])) {
                            mysql_query("UPDATE classes SET ordering='" . $data[4]['parent_ordering'] . str_pad($index[4]++, 3, "0", STR_PAD_LEFT) . "' WHERE id=" . $data[4]['id']);
                            $query[5] = mysql_query("SELECT id,(SELECT ordering FROM classes WHERE id=c.parent_id) AS parent_ordering FROM classes c WHERE parent_id=" . $data[4]['id'] . " AND is_active=1 ORDER BY name");
                            $index[5] = 1;
                            while ($data[5] = mysql_fetch_array($query[5])) {
                                mysql_query("UPDATE classes SET ordering='" . $data[5]['parent_ordering'] . str_pad($index[5]++, 3, "0", STR_PAD_LEFT) . "' WHERE id=" . $data[5]['id']);
                            }
                        }
                    }
                }
            }
        }
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
        $this->Helper->saveUserActivity($user['User']['id'], 'Class', 'View', $id);
        $this->set('class', $this->Class->read(null, $id));
    }

    function add() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        if (!empty($this->data)) {
            $comCheck = 0;
            if(!empty($this->data['company_id'])){
                $comCheck = implode(",", $this->data['company_id']);
            }
            if ($this->Helper->checkDouplicate('name', 'classes', $this->data['Class']['name'], 'is_active = 1 AND id IN (SELECT class_id FROM class_companies WHERE company_id IN ('.$comCheck.'))')) {
                $this->Helper->saveUserActivity($user['User']['id'], 'Class', 'Save Add New (Name ready existed)');
                echo MESSAGE_DATA_ALREADY_EXISTS_IN_THE_SYSTEM;
                exit;
            } else {
                $r = 0;
                $restCode  = array();
                $dateNow   = date("Y-m-d H:i:s");
                $this->Class->create();
                $this->data['Class']['sys_code']   = md5(rand().strtotime(date("Y-m-d H:i:s")).$user['User']['id']);
                $this->data['Class']['created']    = $dateNow;
                $this->data['Class']['created_by'] = $user['User']['id'];
                $this->data['Class']['is_active']  = 1;
                if ($this->Class->save($this->data)) {
                    $lastInsertId = $this->Class->getLastInsertId();
                    // Convert to REST
                    $restCode[$r] = $this->Helper->convertToDataSync($this->data['Class'], 'classes');
                    $restCode[$r]['modified']   = $dateNow;
                    $restCode[$r]['dbtodo']     = 'classes';
                    $restCode[$r]['actodo']     = 'is';
                    $r++;
                    // Class company
                    if (isset($this->data['company_id'])) {
                        for ($i = 0; $i < sizeof($this->data['company_id']); $i++) {
                            mysql_query("INSERT INTO class_companies (class_id, company_id) VALUES ('" . $lastInsertId . "','" . $this->data['company_id'][$i] . "')");
                            // Convert to REST
                            $restCode[$r]['class_id']   = $this->data['Class']['sys_code'];
                            $restCode[$r]['company_id'] = $this->Helper->getSQLSysCode("companies", $this->data['company_id'][$i]);
                            $restCode[$r]['dbtodo']     = 'class_companies';
                            $restCode[$r]['actodo']     = 'is';
                            $r++;
                        }
                    }
                    // Save File Send
                    $this->Helper->sendFileToSync($restCode, 0, 0);
                    // Save User Activity
                    $this->Helper->saveUserActivity($user['User']['id'], 'Class', 'Save Add New', $lastInsertId);
                    echo MESSAGE_DATA_HAS_BEEN_SAVED;
                    exit;
                } else {
                    $this->Helper->saveUserActivity($user['User']['id'], 'Class', 'Save Add New (Error)');
                    echo MESSAGE_DATA_COULD_NOT_BE_SAVED;
                    exit;
                }
            }
        }
        $this->Helper->saveUserActivity($user['User']['id'], 'Class', 'Add New');
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
            if(!empty($this->data['company_id'])){
                $comCheck = implode(",", $this->data['company_id']);
            }
            if ($this->Helper->checkDouplicateEdit('name', 'classes', $id, $this->data['Class']['name'], 'is_active = 1 AND id IN (SELECT class_id FROM class_companies WHERE company_id IN ('.$comCheck.'))')) {
                $this->Helper->saveUserActivity($user['User']['id'], 'Class', 'Save Edit (Name ready existed)', $id);
                echo MESSAGE_DATA_ALREADY_EXISTS_IN_THE_SYSTEM;
                exit;
            } else {
                $r = 0;
                $restCode = array();
                $dateNow  = date("Y-m-d H:i:s");
                $this->data['Class']['modified']    = $dateNow;
                $this->data['Class']['modified_by'] = $user['User']['id'];
                if ($this->Class->save($this->data)) {
                    // Convert to REST
                    $restCode[$r] = $this->Helper->convertToDataSync($this->data['Class'], 'classes');
                    $restCode[$r]['dbtodo'] = 'classes';
                    $restCode[$r]['actodo'] = 'ut';
                    $restCode[$r]['con']    = "sys_code = '".$this->data['Class']['sys_code']."'";
                    $r++;
                    // Class company
                    mysql_query("DELETE FROM class_companies WHERE class_id=" . $id);
                    // Convert to REST
                    $restCode[$r]['dbtodo'] = 'class_companies';
                    $restCode[$r]['actodo'] = 'dt';
                    $restCode[$r]['con']    = "class_id = ".$this->data['Class']['sys_code'];
                    $r++;
                    if (isset($this->data['company_id'])) {
                        for ($i = 0; $i < sizeof($this->data['company_id']); $i++) {
                            mysql_query("INSERT INTO class_companies (class_id, company_id) VALUES ('" . $id . "','" . $this->data['company_id'][$i] . "')");
                            // Convert to REST
                            $restCode[$r]['class_id']   = $this->data['Class']['sys_code'];
                            $restCode[$r]['company_id'] = $this->Helper->getSQLSysCode("companies", $this->data['company_id'][$i]);
                            $restCode[$r]['dbtodo']     = 'class_companies';
                            $restCode[$r]['actodo']     = 'is';
                            $r++;
                        }
                    }
                    // Save File Send
                    $this->Helper->sendFileToSync($restCode, 0, 0);
                    // Save User Activity
                    $this->Helper->saveUserActivity($user['User']['id'], 'Class', 'Save Edit', $id);
                    echo MESSAGE_DATA_HAS_BEEN_SAVED;
                    exit;
                } else {
                    $this->Helper->saveUserActivity($user['User']['id'], 'Class', 'Save Edit (Error)', $id);
                    echo MESSAGE_DATA_COULD_NOT_BE_SAVED;
                    exit;
                }
            }
        }
        if (empty($this->data)) {
            $this->Helper->saveUserActivity($user['User']['id'], 'Class', 'Edit', $id);
            $this->data = $this->Class->read(null, $id);
        }
    }

    function delete($id = null) {
        if (!$id) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }

        // check if class and it's child already in used
        $classList=array();
        $classList[]=$id;
        $queryChild[0]=mysql_query("SELECT id FROM classes WHERE is_active=1 AND parent_id=".$id);
        if(mysql_num_rows($queryChild[0])){
            while($dataChild[0]=mysql_fetch_array($queryChild[0])){
                $classList[]=$dataChild[0]['id'];
                $queryChild[1]=mysql_query("SELECT id FROM classes WHERE is_active=1 AND parent_id=".$dataChild[0]['id']);
                if(mysql_num_rows($queryChild[1])){
                    while($dataChild[1]=mysql_fetch_array($queryChild[1])){
                        $classList[]=$dataChild[1]['id'];
                        $queryChild[2]=mysql_query("SELECT id FROM classes WHERE is_active=1 AND parent_id=".$dataChild[1]['id']);
                        if(mysql_num_rows($queryChild[2])){
                            while($dataChild[2]=mysql_fetch_array($queryChild[2])){
                                $classList[]=$dataChild[2]['id'];
                                $queryChild[3]=mysql_query("SELECT id FROM classes WHERE is_active=1 AND parent_id=".$dataChild[2]['id']);
                                if(mysql_num_rows($queryChild[3])){
                                    while($dataChild[3]=mysql_fetch_array($queryChild[3])){
                                        $classList[]=$dataChild[3]['id'];
                                        $queryChild[4]=mysql_query("SELECT id FROM classes WHERE is_active=1 AND parent_id=".$dataChild[3]['id']);
                                        if(mysql_num_rows($queryChild[4])){
                                            while($dataChild[4]=mysql_fetch_array($queryChild[4])){
                                                $classList[]=$dataChild[4]['id'];
                                                $queryChild[5]=mysql_query("SELECT id FROM classes WHERE is_active=1 AND parent_id=".$dataChild[4]['id']);
                                                if(mysql_num_rows($queryChild[5])){
                                                    while($dataChild[5]=mysql_fetch_array($queryChild[5])){
                                                        $classList[]=$dataChild[5]['id'];
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
        $queryGl=mysql_query("SELECT gl.id FROM general_ledgers gl INNER JOIN general_ledger_details gld ON gl.id=gld.general_ledger_id WHERE is_active=1 AND class_id IN (" . implode(",", $classList) . ")");
        $notAllowDelete=mysql_num_rows($queryGl);
        $user = $this->getCurrentUser();
        if(!$notAllowDelete){
            $r = 0;
            $restCode = array();
            $dateNow  = date("Y-m-d H:i:s");
            $this->data = $this->Class->read(null, $id);
            mysql_query("UPDATE `classes` SET `is_active`=2, `modified`='".$dateNow."', `modified_by`=".$user['User']['id']." WHERE `id`=".$id.";");
            // Convert to REST
            $restCode[$r]['is_active']   = 2;
            $restCode[$r]['modified']    = $dateNow;
            $restCode[$r]['modified_by'] = $this->Helper->getSQLSysCode("users", $user['User']['id']);
            $restCode[$r]['dbtodo'] = 'classes';
            $restCode[$r]['actodo'] = 'ut';
            $restCode[$r]['con']    = "sys_code = '".$this->data['Class']['sys_code']."'";
            // Save File Send
            $this->Helper->sendFileToSync($restCode, 0, 0);
            // Save User Activity
            $this->Helper->saveUserActivity($user['User']['id'], 'Class', 'Delete', $id);
            echo MESSAGE_DATA_HAS_BEEN_DELETED;
            exit;
        }else{
            // Save User Activity
            $this->Helper->saveUserActivity($user['User']['id'], 'Class', 'Error Delete', $id);
            echo MESSAGE_CLASS_ALREADY_IN_USED;
            exit;
        }
    }
    
    function exportExcel(){
        $this->layout = 'ajax';
        if (isset($_POST['action']) && $_POST['action'] == 'export') {
            $user = $this->getCurrentUser();
            $this->Helper->saveUserActivity($user['User']['id'], 'Class', 'Export to Excel');
            $filename = "public/report/class_export.csv";
            $fp = fopen($filename, "wb");
            $excelContent = 'Classes' . "\n\n";
            $excelContent .= TABLE_NO . "\t" . TABLE_NAME. "\t" . TABLE_COMPANY;
            if($user['User']['id'] == 1 || $user['User']['id'] == 57){
                $conditionUser = "";
            }else{
                $conditionUser = " AND c.id IN (SELECT class_id FROM class_companies WHERE company_id IN (SELECT company_id FROM user_companies WHERE user_id = ".$user['User']['id']."))";
            }
            $query = mysql_query('SELECT id, CONCAT(
                                    IF(parent_id IS NOT NULL,
                                        IF((SELECT parent_id FROM classes WHERE id=c.parent_id) IS NOT NULL,
                                            IF((SELECT parent_id FROM classes WHERE id=(SELECT parent_id FROM classes WHERE id=c.parent_id)) IS NOT NULL,
                                                IF((SELECT parent_id FROM classes WHERE id=(SELECT parent_id FROM classes WHERE id=(SELECT parent_id FROM classes WHERE id=c.parent_id))) IS NOT NULL,
                                                    IF((SELECT parent_id FROM classes WHERE id=(SELECT parent_id FROM classes WHERE id=(SELECT parent_id FROM classes WHERE id=(SELECT parent_id FROM classes WHERE id=c.parent_id)))) IS NOT NULL,
                                                        "                    ",
                                                    "                "),
                                                "            "),
                                            "        "),
                                        "    "),
                                    ""),
                                    name
                                ), (SELECT GROUP_CONCAT(name) FROM companies WHERE id IN (SELECT company_id FROM class_companies WHERE class_id = c.id)) '
                    . '           FROM classes c WHERE c.is_active=1'.$conditionUser.' ORDER BY ordering');
            $index = 1;
            while ($data = mysql_fetch_array($query)) {
                $excelContent .= "\n" . $index++ . "\t" . $data[1]. "\t" . $data[2];
            }
            $excelContent = chr(255) . chr(254) . @mb_convert_encoding($excelContent, 'UTF-16LE', 'UTF-8');
            fwrite($fp, $excelContent);
            fclose($fp);
            exit();
        }
    }

}

?>