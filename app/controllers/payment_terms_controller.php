<?php

class PaymentTermsController extends AppController {

    var $name = 'PaymentTerms';
    var $components = array('Helper');

    function index() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        $this->Helper->saveUserActivity($user['User']['id'], 'Payment Term', 'Dashboard');
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
        $this->Helper->saveUserActivity($user['User']['id'], 'Payment Term', 'View', $id);
        $this->set('paymentTerm', $this->PaymentTerm->read(null, $id));
    }

    function add() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        if (!empty($this->data)) {
            if ($this->Helper->checkDouplicate('name', 'payment_terms', $this->data['PaymentTerm']['name'])) {
                $this->Helper->saveUserActivity($user['User']['id'], 'Payment Term', 'Save Add New (Name ready existed)');
                echo MESSAGE_DATA_ALREADY_EXISTS_IN_THE_SYSTEM;
                exit;
            } else {
//                $r = 0;
//                $restCode = array();
                $dateNow  = date("Y-m-d H:i:s");
                $this->PaymentTerm->create();
                $this->data['PaymentTerm']['sys_code']   = md5(rand().strtotime(date("Y-m-d H:i:s")).$user['User']['id']);
                $this->data['PaymentTerm']['created']    = $dateNow;
                $this->data['PaymentTerm']['created_by'] = $user['User']['id'];
                $this->data['PaymentTerm']['is_active'] = 1;
                if ($this->PaymentTerm->save($this->data)) {
                    // Convert to REST
//                    $restCode[$r] = $this->Helper->convertToDataSync($this->data['PaymentTerm'], 'payment_terms');
//                    $restCode[$r]['modified']   = $dateNow;
//                    $restCode[$r]['dbtodo']     = 'payment_terms';
//                    $restCode[$r]['actodo']     = 'is';
                    // Save File Send
//                    $this->Helper->sendFileToSync($restCode, 0, 0);
                    // Save User Activity
                    $this->Helper->saveUserActivity($user['User']['id'], 'Payment Term', 'Save Add New', $this->PaymentTerm->id);
                    echo MESSAGE_DATA_HAS_BEEN_SAVED;
                    exit;
                } else {
                    $this->Helper->saveUserActivity($user['User']['id'], 'Payment Term', 'Save Add New (Error)');
                    echo MESSAGE_DATA_COULD_NOT_BE_SAVED;
                    exit;
                }
            }
        }
        $this->Helper->saveUserActivity($user['User']['id'], 'Payment Term', 'Add New');
    }

    function edit($id = null) {
        $this->layout = 'ajax';
        if (!$id && empty($this->data)) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $user = $this->getCurrentUser();
        if (!empty($this->data)) {
            if ($this->Helper->checkDouplicateEdit('name', 'payment_terms', $id, $this->data['PaymentTerm']['name'])) {
                $this->Helper->saveUserActivity($user['User']['id'], 'Payment Term', 'Save Edit (Name ready existed)', $id);
                echo MESSAGE_DATA_ALREADY_EXISTS_IN_THE_SYSTEM;
                exit;
            } else {
//                $r = 0;
//                $restCode  = array();
                $dateNow   = date("Y-m-d H:i:s");
                $this->data['PaymentTerm']['modified']    = $dateNow;
                $this->data['PaymentTerm']['modified_by'] = $user['User']['id'];
                if ($this->PaymentTerm->save($this->data)) {
                    // Convert to REST
//                    $restCode[$r] = $this->Helper->convertToDataSync($this->data['PaymentTerm'], 'payment_terms');
//                    $restCode[$r]['dbtodo'] = 'payment_terms';
//                    $restCode[$r]['actodo'] = 'ut';
//                    $restCode[$r]['con']    = "sys_code = '".$this->data['PaymentTerm']['sys_code']."'";
                    // Save File Send
//                    $this->Helper->sendFileToSync($restCode, 0, 0);
                    // Save User Activity
                    $this->Helper->saveUserActivity($user['User']['id'], 'Payment Term', 'Save Edit', $id);
                    echo MESSAGE_DATA_HAS_BEEN_SAVED;
                    exit;
                } else {
                    $this->Helper->saveUserActivity($user['User']['id'], 'Payment Term', 'Save Edit (Error)', $id);
                    echo MESSAGE_DATA_COULD_NOT_BE_SAVED;
                    exit;
                }
            }
        }
        if (empty($this->data)) {
            $this->Helper->saveUserActivity($user['User']['id'], 'Payment Term', 'Edit', $id);
            $this->data = $this->PaymentTerm->read(null, $id);
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
        $this->data = $this->PaymentTerm->read(null, $id);
        mysql_query("UPDATE `payment_terms` SET `is_active`=2, `modified`='".date("Y-m-d H:i:s")."', `modified_by`=".$user['User']['id']." WHERE `id`=".$id.";");
        // Convert to REST
//        $restCode[$r]['is_active']   = 2;
//        $restCode[$r]['modified']    = $dateNow;
//        $restCode[$r]['modified_by'] = $this->Helper->getSQLSysCode("users", $user['User']['id']);
//        $restCode[$r]['dbtodo'] = 'payment_terms';
//        $restCode[$r]['actodo'] = 'ut';
//        $restCode[$r]['con']    = "sys_code = '".$this->data['PaymentTerm']['sys_code']."'";
        // Save File Send
        $this->Helper->saveUserActivity($user['User']['id'], 'Payment Term', 'Delete', $id);
//        $this->Helper->sendFileToSync($restCode, 0, 0);
        // Save User Activity
        echo MESSAGE_DATA_HAS_BEEN_DELETED;
        exit;
    }
    
    function exportExcel(){
        $this->layout = 'ajax';
        if (isset($_POST['action']) && $_POST['action'] == 'export') {
            $user = $this->getCurrentUser();
            $this->Helper->saveUserActivity($user['User']['id'], 'Payment Term', 'Export to Excel');
            $filename = "public/report/payment_term_export.csv";
            $fp = fopen($filename, "wb");
            $excelContent = 'Payment Terms' . "\n\n";
            $excelContent .= TABLE_NO . "\t" . TABLE_NAME. "\t" . TABLE_NET_DAYS;
            $query = mysql_query('SELECT id, name, net_days FROM payment_terms WHERE is_active=1 ORDER BY name');
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