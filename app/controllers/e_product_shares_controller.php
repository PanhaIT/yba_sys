<?php

class EProductSharesController extends AppController {

    var $name = 'EProductShares';
    var $components = array('Helper', 'ProductCom');

    function index() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        // User Activity
        $this->Helper->saveUserActivity($user['User']['id'], 'E-Commerce Product Share', 'Dashboard');
    }

    function ajax($companyId, $pgroupId) {
        $this->layout = 'ajax';
        $this->set(compact('companyId', 'pgroupId'));
    }
    
    function productPrice($id = null) {
        $this->layout = 'ajax';
        if (empty($id)) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $user = $this->getCurrentUser();
        $this->Helper->saveUserActivity($user['User']['id'], 'E-Commerce Product Share', 'View Price', $id);
        $products = ClassRegistry::init('Product')->read(null, $id);
        $this->set(compact('products'));
    }
    
    function productPriceDetail($id){
        $this->layout = 'ajax';
        if(empty($id)){
            exit;
        }
        $products = ClassRegistry::init('Product')->read(null, $id);
        $branch   = ClassRegistry::init('Branch')->find('first',array('fields' => array('Branch.id', 'Branch.name'),'conditions' => array('Branch.is_active = 1', 'Branch.company_id' => $products['Product']['company_id'], 'Branch.is_head' => 1)));
        $branchId = $branch['Branch']['id'];
        $currency = mysql_query("SELECT symbol FROM currency_centers WHERE id = (SELECT currency_center_id FROM branches WHERE id = ".$branchId." LIMIT 1)");
        $rowCurr  = mysql_fetch_array($currency);
        $symbol   = $rowCurr[0];
        $this->set(compact('products', 'branchId', 'symbol'));
    }
    
}

?>