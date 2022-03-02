<?php

class ExchangeRatesController extends AppController {

    var $name = 'ExchangeRates';
    var $components = array('Helper');

    function index() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        $this->Helper->saveUserActivity($user['User']['id'], 'Exchange Rate', 'Dashboard');
    }

    function ajax($branchId = '') {
        $this->layout = 'ajax';
        $branch = ClassRegistry::init('Branch')->read(null, $branchId);
        $this->set(compact('branchId', 'branch'));
    }

    function add($id) {
        $this->layout = 'ajax';
        if(!empty($id)){
            $r = 0;
            $restCode = array();
            $dateNow  = date("Y-m-d H:i:s");
            $user = $this->getCurrentUser();
            $comCurrency = ClassRegistry::init('BranchCurrency')->read(null, $id);
            $rateSell    = $_POST['rate_sell']!=''?$_POST['rate_sell']:0;
            $rateChange  = $_POST['rate_change']!=''?$_POST['rate_change']:0;
            $ratePurchase  = $_POST['rate_purchase']!=''?$_POST['rate_purchase']:0;
            // Insert Exchange Rate
            $this->ExchangeRate->create();
            $this->data['ExchangeRate']['sys_code']  = md5(rand().strtotime(date("Y-m-d H:i:s")).$user['User']['id']);
            $this->data['ExchangeRate']['branch_id'] = $comCurrency['BranchCurrency']['branch_id'];
            $this->data['ExchangeRate']['currency_id'] = $comCurrency['BranchCurrency']['currency_id'];
            $this->data['ExchangeRate']['rate_to_sell']   = $rateSell;
            $this->data['ExchangeRate']['rate_to_change'] = $rateChange;
            $this->data['ExchangeRate']['rate_purchase']  = $ratePurchase;
            $this->data['ExchangeRate']['created']    = $dateNow;
            $this->data['ExchangeRate']['created_by'] = $user['User']['id'];
            $this->data['ExchangeRate']['is_active']  = 1;
            $this->ExchangeRate->save($this->data);
            $exchangeRateId = $this->ExchangeRate->id;
            // Convert to REST
            $restCode[$r] = $this->Helper->convertToDataSync($this->data['ExchangeRate'], 'exchange_rates');
            $restCode[$r]['modified'] = $dateNow;
            $restCode[$r]['dbtodo']   = 'exchange_rates';
            $restCode[$r]['actodo']   = 'is';
            $r++;
            // Update Company Currency
            mysql_query("UPDATE `branch_currencies` SET `exchange_rate_id` = '".$exchangeRateId."', `rate_to_sell`='".$rateSell."', `rate_to_change`='".$rateChange."', `rate_purchase`='".$ratePurchase."', `modified`='".$dateNow."', `modified_by`=".$user['User']['id']." WHERE `id`=".$id.";");
            // Convert to REST
            $restCode[$r]['exchange_rate_id'] = $this->data['ExchangeRate']['sys_code'];
            $restCode[$r]['rate_to_sell']   = $rateSell;
            $restCode[$r]['rate_to_change'] = $rateChange;
            $restCode[$r]['rate_purchase']  = $ratePurchase;
            $restCode[$r]['modified']       = $dateNow;
            $restCode[$r]['modified_by']    = $this->Helper->getSQLSysCode("users", $user['User']['id']);
            $restCode[$r]['dbtodo']  = 'branch_currencies';
            $restCode[$r]['actodo']  = 'ut';
            $restCode[$r]['con']     = "sys_code = '".$comCurrency['BranchCurrency']['sys_code']."'";
            // Save File Send
            $this->Helper->sendFileToSync($restCode, 0, 0);
            // Save User Activity
            $this->Helper->saveUserActivity($user['User']['id'], 'Exchange Rate', 'Save Add New', $exchangeRateId);
        }
        exit;
    }
    function view($branchId = null, $currencyCenterId = null){   
        $this->layout = 'ajax';
        if (!$branchId && !$currencyCenterId) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $user = $this->getCurrentUser();
        // User Activity
        $this->Helper->saveUserActivity($user['User']['id'], 'Exchange Rate', 'View', $branchId);
        $this->data = ClassRegistry::init('ExchangeRate')->find('all', array('fields' => array(
                                'ExchangeRate.*',
                                'branches.name',
                                'branches.currency_id',
                                'currencies.name',
                                'currencies.symbol'),
                            'joins' => array(
                                array('table' => 'branches', 'type' => 'left', 'conditions' => array('ExchangeRate.branch_id=branches.id')),
                                array('table' => 'currencies', 'type' => 'left', 'conditions' => array('currencies.id=ExchangeRate.currency_id'))
                            ),
                            'conditions' => array('ExchangeRate.is_active = 1', 'ExchangeRate.branch_id' => $branchId, 'ExchangeRate.currency_id' => $currencyCenterId)
                        ));        
    }
}

?>