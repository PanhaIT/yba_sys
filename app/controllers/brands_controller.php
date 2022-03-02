<?php

class BrandsController extends AppController {

    var $name = 'Brands';
    var $components = array('Helper');
    
    function index() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        $this->Helper->saveUserActivity($user['User']['id'], 'Brand', 'Dashboard');
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
        $this->Helper->saveUserActivity($user['User']['id'], 'Brand', 'View', $id);
        $this->data = $this->Brand->read(null, $id);
    }

    function add() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        if (!empty($this->data)) {
            if ($this->Helper->checkDouplicate('name', 'brands', $this->data['Brand']['name'], 'is_active = 1')) {
                $this->Helper->saveUserActivity($user['User']['id'], 'Brand', 'Save Add New (Name ready existed)');
                echo MESSAGE_DATA_ALREADY_EXISTS_IN_THE_SYSTEM;
                exit;
            } else {
                $dateNow  = date("Y-m-d H:i:s");
                $this->Brand->create();
                $this->data['Brand']['sys_code']   = md5(rand().strtotime(date("Y-m-d H:i:s")).$user['User']['id']);
                $this->data['Brand']['name']       = $this->data['Brand']['name'];
                $this->data['Brand']['created']    = $dateNow;
                $this->data['Brand']['created_by'] = $user['User']['id'];
                $this->data['Brand']['is_active']  = 1;
                if ($this->Brand->save($this->data)) {
                    $lastInsertId = $this->Brand->id;
                    // Save User Activity
                    $this->Helper->saveUserActivity($user['User']['id'], 'Brand', 'Save Add New', $lastInsertId);
                    echo MESSAGE_DATA_HAS_BEEN_SAVED;
                    exit;
                } else {
                    $this->Helper->saveUserActivity($user['User']['id'], 'Brand', 'Save Add New (Error)');
                    echo MESSAGE_DATA_COULD_NOT_BE_SAVED;
                    exit;
                }
            }
        }
        $this->Helper->saveUserActivity($user['User']['id'], 'Brand', 'Add New');
    }

    function edit($id = null) {
        $this->layout = 'ajax';
        if (!$id && empty($this->data)) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $user = $this->getCurrentUser();
        if (!empty($this->data)) {
            if ($this->Helper->checkDouplicateEdit('name', 'brands', $id, $this->data['Brand']['name'], 'is_active = 1')) {
                $this->Helper->saveUserActivity($user['User']['id'], 'Brand', 'Save Edit (Name ready existed)', $id);
                echo MESSAGE_DATA_ALREADY_EXISTS_IN_THE_SYSTEM;
                exit;
            } else {
                $dateNow   = date("Y-m-d H:i:s");
                $this->data['Brand']['name']        = $this->data['Brand']['name'];
                $this->data['Brand']['modified']    = $dateNow;
                $this->data['Brand']['modified_by'] = $user['User']['id'];
                if ($this->Brand->save($this->data)) {
                    // Save User Activity
                    $this->Helper->saveUserActivity($user['User']['id'], 'Brand', 'Save Edit', $id);
                    echo MESSAGE_DATA_HAS_BEEN_SAVED;
                    exit;
                } else {
                    $this->Helper->saveUserActivity($user['User']['id'], 'Brand', 'Save Edit (Error)', $id);
                    echo MESSAGE_DATA_COULD_NOT_BE_SAVED;
                    exit;
                }
            }
        }
        if (empty($this->data)) {
            $this->Helper->saveUserActivity($user['User']['id'], 'Brand', 'Edit', $id);
            $this->data = $this->Brand->read(null, $id);
        }
    }

    function delete($id = null) {
        if (!$id) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $user = $this->getCurrentUser();
        $dateNow  = date("Y-m-d H:i:s");
        $this->data = $this->Brand->read(null, $id);
        mysql_query("UPDATE `brands` SET `is_active`=2, `modified`='".$dateNow."', `modified_by`=".$user['User']['id']." WHERE `id`=".$id.";");
        // Save User Activity
        $this->Helper->saveUserActivity($user['User']['id'], 'Brand', 'Delete', $id);
        echo MESSAGE_DATA_HAS_BEEN_DELETED;
        exit;
    }
    
    function exportExcel(){
        $this->layout = 'ajax';
        if (isset($_POST['action']) && $_POST['action'] == 'export') {
            $user = $this->getCurrentUser();
            $this->Helper->saveUserActivity($user['User']['id'], 'Brand', 'Export to Excel');
            $filename = "public/report/brand_export.csv";
            $fp = fopen($filename, "wb");
            $excelContent = 'Brands' . "\n\n";
            $excelContent .= TABLE_NO . "\t" . TABLE_NAME;
            $query = mysql_query('SELECT id,  name FROM brands WHERE is_active=1 ORDER BY name');
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