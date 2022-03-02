<?php

class EPgroupSharesController extends AppController {

    var $name = 'EPgroupShares';
    var $components = array('Helper');

    function index() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        $this->Helper->saveUserActivity($user['User']['id'], 'E-Commmerce', 'Share');
        $categories = ClassRegistry::init('EProductCategory')->find("all", array('conditions' => array('EProductCategory.is_active' => 1)));
        $this->set(compact('categories'));
    }
    
    function saveShopShare($id = null, $status){
        $this->layout = 'ajax';
        if(!$id){
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        // Update Shop Share
        $user = $this->getCurrentUser();
        $shop = ClassRegistry::init('EStoreShare')->read(null, $id);
        mysql_query("UPDATE e_store_shares SET is_share = ".$status." WHERE id = ".$id);
        $e = 0;
        $syncEco = array();
        $dateNow = date("Y-m-d H:i:s");
        // Convert to REST
        $syncEco[$e]['modified'] = $dateNow;
        $syncEco[$e]['status']   = 1;
        $syncEco[$e]['dbtodo']   = 'shops';
        $syncEco[$e]['actodo']   = 'ut';
        $syncEco[$e]['con']      = "sys_code = '".$shop['EStoreShare']['sys_code']."'";
        // Save File Send to E-Commerce
        $this->Helper->sendFileToSyncPublic($syncEco);
        // Save User Activity
        $this->Helper->saveUserActivity($user['User']['id'], 'E-Commmerce', 'Save Shop Share');
        echo MESSAGE_DATA_HAS_BEEN_SAVED;
        exit;
    }
    
    function editShopShare(){
        $this->layout = 'ajax';
        if(!empty($this->data)){
            $e = 0;
            $syncEco = array();
            $dateNow = date("Y-m-d H:i:s");
            $user = $this->getCurrentUser();
            $this->loadModel('EStoreShare');
            $this->data['EStoreShare']['modified']    = $dateNow;
            $this->data['EStoreShare']['modified_by'] = $user['User']['id'];
            $this->EStoreShare->save($this->data);
            // Convert to REST
            $syncEco[$e] = $this->Helper->convertToDataSync($this->data['EStoreShare'], 'e_store_shares');
            $syncEco[$e]['dbtodo']   = 'shops';
            $syncEco[$e]['actodo']   = 'ut';
            $syncEco[$e]['con']      = "sys_code = '".$this->data['EStoreShare']['sys_code']."'";
            // Save File Send to E-Commerce
            $this->Helper->sendFileToSyncPublic($syncEco);
            // Save User Activity
            $this->Helper->saveUserActivity($user['User']['id'], 'E-Commmerce', 'Edit Shop Share');
            echo MESSAGE_DATA_HAS_BEEN_SAVED;
        } else {
            echo MESSAGE_DATA_INVALID;
        }
        exit;
    }
    
    function savePgroupShare(){
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        if(!empty($_POST)){
            $e = 0;
            $syncEco = array();
            $dateNow = date("Y-m-d H:i:s");
            $this->loadModel('EPgroupShare');
            // Delete Pgroup Share
            mysql_query("DELETE FROM e_pgroup_shares WHERE company_id = ".$this->data['company_id']);
            for ($i = 0; $i < sizeof($_POST['id']); $i++) {
                $pgroupCategoryId = 0;
                if($_POST['share'][$i] == 1){
                    $pgroupCategoryId = $_POST['category'][$i];
                    $status = 1;
                    $pgroupShare = array();
                    $this->EPgroupShare->create();
                    $pgroupShare['EPgroupShare']['company_id'] = $this->data['company_id'];
                    $pgroupShare['EPgroupShare']['e_product_category_id'] = $_POST['category'][$i];
                    $pgroupShare['EPgroupShare']['pgroup_id']  = $_POST['id'][$i];
                    $pgroupShare['EPgroupShare']['created']    = $dateNow;
                    $pgroupShare['EPgroupShare']['created_by'] = $user['User']['id'];
                    $this->EPgroupShare->save($pgroupShare);
                    // Insert Product to Share
                    $sqlProduct = mysql_query("SELECT * FROM products WHERE id IN (SELECT product_id FROM product_pgroups WHERE pgroup_id = ".$_POST['id'][$i].")");
                    while($rowProduct = mysql_fetch_array($sqlProduct)){
                        $checkShare = mysql_query("SELECT id FROM e_product_shares WHERE product_id = ".$rowProduct['id']);
                        if(mysql_fetch_array($checkShare)){
                            mysql_query("UPDATE `e_product_shares` SET is_active = 1 WHERE id = ".$rowProduct['id'].";");
                        } else {
                            mysql_query("INSERT INTO `e_product_shares` (`company_id`, `product_id`, `created`, `created_by`) VALUES (".$this->data['company_id'].", ".$rowProduct['id'].", '".$dateNow."', ".$user['User']['id'].");");
                        }
                    }
                } else {
                    $status = 2;
                    // Update Product Share
                    $sqlProduct = mysql_query("SELECT * FROM products WHERE id IN (SELECT product_id FROM product_pgroups WHERE pgroup_id = ".$_POST['id'][$i].")");
                    while($rowProduct = mysql_fetch_array($sqlProduct)){
                        mysql_query("UPDATE `e_product_shares` SET is_active = 2 WHERE id = ".$rowProduct['id'].";");
                    }
                }
                // Convert to REST
                if($pgroupCategoryId > 0){
                    $ePgroupCategorySys = $this->Helper->getSQLSysCode("e_product_categories", $pgroupCategoryId);
                    $syncEco[$e]['product_category_id'] = $ePgroupCategorySys;
                } else {
                    $syncEco[$e]['product_category_id'] = 0;
                }
                $syncEco[$e]['status']   = $status;
                $syncEco[$e]['dbtodo']   = 'pgroups';
                $syncEco[$e]['actodo']   = 'ut';
                $syncEco[$e]['con']      = "sys_code = '".$this->Helper->getSQLSysCode("pgroups", $_POST['id'][$i])."'";
                $e++;
                // Convert to REST
                $syncEco[$e]['status']   = $status;
                $syncEco[$e]['dbtodo']   = 'products';
                $syncEco[$e]['actodo']   = 'ut';
                $syncEco[$e]['con']      = "id IN (SELECT product_id FROM product_pgroups WHERE pgroup_id =".$this->Helper->getSQLSyncCode("pgroups", $_POST['id'][$i]).")";
                $e++;
            } 
            // Save File Send to E-Commerce
            $this->Helper->sendFileToSyncPublic($syncEco);
            // Save User Activity
            $this->Helper->saveUserActivity($user['User']['id'], 'E-Commmerce', 'Save Product Group Share');
            echo MESSAGE_DATA_HAS_BEEN_SAVED;
        } else {
            echo MESSAGE_DATA_INVALID;
        }
        exit;
    }

}

?>