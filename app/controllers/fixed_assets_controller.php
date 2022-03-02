<?php

class FixedAssetsController extends AppController {

    var $name = 'FixedAssets';
    var $components = array('Helper');

    function index() {
        $this->layout = "ajax";
        $user = $this->getCurrentUser();
        $this->Helper->saveUserActivity($user['User']['id'], 'Fixed Assest', 'Dashboard');
    }

    function ajax() {
        $this->layout = "ajax";
    }

    function add() {
        $this->layout = "ajax";
        $user = $this->getCurrentUser();
        if (!empty($this->data)) {
            if ($this->Helper->checkDouplicate('name', 'fixed_assets', $this->data['FixedAsset']['name'])) {
                echo MESSAGE_DATA_ALREADY_EXISTS_IN_THE_SYSTEM;
                exit;
            } else {
                $this->data['FixedAsset']['created_by'] = $user['User']['id'];
                if (isset($this->data['FixedAsset']['is_depre'])) {
                    $this->data['FixedAsset']['is_depre'] = 1;
                } else {
                    $this->data['FixedAsset']['is_depre'] = 0;
                }

                $this->data['FixedAsset']['is_active'] = 1;

                if ($this->FixedAsset->saveAll($this->data)) {
                    $this->Helper->saveUserActivity($user['User']['id'], 'Fixed Assest', 'Save Add New', $this->FixedAsset->id);
                    echo MESSAGE_DATA_HAS_BEEN_SAVED;
                    exit;
                } else {
                    $this->Helper->saveUserActivity($user['User']['id'], 'Fixed Assest', 'Save Add New (Error)');
                    echo MESSAGE_DATA_COULD_NOT_BE_SAVED;
                    exit;
                }
            }
        }
        if (empty($this->data)) {
            $this->Helper->saveUserActivity($user['User']['id'], 'Fixed Assest', 'Add New');
            $code = $this->Helper->getAutoGenerateFixedAssetCode();
            $this->set('code', $code);
            $companies = ClassRegistry::init('Company')->find('list',
                        array('joins' => array(array('table' => 'user_companies', 'type' => 'inner', 'conditions' => array('user_companies.company_id=Company.id'))),
                            'fields' => array('Company.id', 'Company.name'),
                            'conditions' => array('Company.is_active = 1', 'user_companies.user_id=' . $user['User']['id'])
                        ));
            $branches = ClassRegistry::init('Branch')->find('all',
                            array('joins' => array(array('table' => 'user_branches', 'type' => 'inner', 'conditions' => array('user_branches.branch_id=Branch.id'))),
                                'fields' => array('Branch.id', 'Branch.name', 'Branch.company_id'),
                                'conditions' => array('Branch.is_active = 1', 'user_branches.user_id=' . $user['User']['id'])
                            ));
            $locations = ClassRegistry::init('Location')->find('list', array('joins' => array(array('table' => 'user_locations', 'type' => 'inner', 'conditions' => array('user_locations.location_id=Location.id'))), 'conditions' => array('user_locations.user_id=' . $user['User']['id'], 'Location.is_active=1'), 'order' => 'Location.name'));
            $vendors = ClassRegistry::init('Vendor')->find("list", array("conditions" => array("Vendor.is_active = 1", "Vendor.id IN (SELECT vendor_id FROM vendor_companies WHERE company_id IN (SELECT company_id FROM user_companies WHERE user_id = ".$user['User']['id']."))")));
            $this->set(compact('companies', 'branches', 'locations', 'vendors'));
        }
    }

    function edit($id=null) {
        $this->layout = "ajax";
        $user = $this->getCurrentUser();
        if (!empty($this->data)) {
            if ($this->Helper->checkDouplicateEdit('name', 'fixed_assets', $id, $this->data['FixedAsset']['name'])) {
                echo MESSAGE_DATA_ALREADY_EXISTS_IN_THE_SYSTEM;
                exit;
            } else {
                $this->data['FixedAsset']['created_by'] = $user['User']['id'];
                $this->data['FixedAsset']['is_active'] = 1;
                if ($this->FixedAsset->saveAll($this->data)) {
                    $this->Helper->saveUserActivity($user['User']['id'], 'Fixed Assest', 'Save Edit', $id);
                    echo MESSAGE_DATA_HAS_BEEN_SAVED;
                    exit;
                } else {
                    $this->Helper->saveUserActivity($user['User']['id'], 'Fixed Assest', 'Save Edit (Error)');
                    echo MESSAGE_DATA_COULD_NOT_BE_SAVED;
                    exit;
                }
            }
        }
        if (empty($this->data)) {
            $this->Helper->saveUserActivity($user['User']['id'], 'Fixed Assest', 'Edit', $id);
            $this->data = $this->FixedAsset->read(null, $id);
            $companies = ClassRegistry::init('Company')->find('list',
                        array('joins' => array(array('table' => 'user_companies', 'type' => 'inner', 'conditions' => array('user_companies.company_id=Company.id'))),
                            'fields' => array('Company.id', 'Company.name'),
                            'conditions' => array('Company.is_active = 1', 'user_companies.user_id=' . $user['User']['id'])
                        ));
            $branches = ClassRegistry::init('Branch')->find('all',
                            array('joins' => array(array('table' => 'user_branches', 'type' => 'inner', 'conditions' => array('user_branches.branch_id=Branch.id'))),
                                'fields' => array('Branch.id', 'Branch.name', 'Branch.company_id'),
                                'conditions' => array('Branch.is_active = 1', 'user_branches.user_id=' . $user['User']['id'])
                            ));
            $locations = ClassRegistry::init('Location')->find('list', array('joins' => array(array('table' => 'user_locations', 'type' => 'inner', 'conditions' => array('user_locations.location_id=Location.id'))), 'conditions' => array('user_locations.user_id=' . $user['User']['id'], 'Location.is_active=1'), 'order' => 'Location.name'));
            $vendors = ClassRegistry::init('Vendor')->find("list", array("conditions" => array("Vendor.is_active = 1", "Vendor.id IN (SELECT vendor_id FROM vendor_companies WHERE company_id IN (SELECT company_id FROM user_companies WHERE user_id = ".$user['User']['id']."))")));
            $this->set(compact('companies', 'branches', 'locations', 'vendors'));
        }
    }

    function view($id = null) {
        $this->layout = 'ajax';
        if (!$id) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $user = $this->getCurrentUser();
        $this->Helper->saveUserActivity($user['User']['id'], 'Fixed Assest', 'View', $id);
        $this->set('fixedAsset', $this->FixedAsset->read(null, $id));
    }

    function delete($id = null) {
        if (!$id) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $user = $this->getCurrentUser();
        $this->Helper->saveUserActivity($user['User']['id'], 'Fixed Assest', 'Delete', $id);
        $this->data['FixedAsset']['modified_by'] = $user['User']['id'];
        $this->data['FixedAsset']['id'] = $id;
        $this->data['FixedAsset']['is_active'] = 2;
        $this->FixedAsset->saveAll($this->data);
        echo MESSAGE_DATA_HAS_BEEN_DELETED;
        exit;
    }

    function post() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        $this->Helper->saveUserActivity($user['User']['id'], 'Fixed Assest', 'Post', '');
        $companies = ClassRegistry::init('Company')->find('list',
                    array('joins' => array(array('table' => 'user_companies', 'type' => 'inner', 'conditions' => array('user_companies.company_id=Company.id'))),
                        'fields' => array('Company.id', 'Company.name'),
                        'conditions' => array('Company.is_active = 1', 'user_companies.user_id=' . $user['User']['id'])
                    ));
        $branches = ClassRegistry::init('Branch')->find('all',
                        array('joins' => array(array('table' => 'user_branches', 'type' => 'inner', 'conditions' => array('user_branches.branch_id=Branch.id'))),
                            'fields' => array('Branch.id', 'Branch.name', 'Branch.company_id'),
                            'conditions' => array('Branch.is_active = 1', 'user_branches.user_id=' . $user['User']['id'])
                        ));
        $this->set(compact('companies', 'branches'));
    }

    function postDetail($companyId = null, $branchId = null, $date = null) {
        $this->layout = 'ajax';
        $this->set('companyId', $companyId);
        $this->set('branchId', $branchId);
        $this->set('date', $date);
    }

    function save() {
        $this->layout = "ajax";
        if ($this->Helper->checkDouplicate('reference', 'general_ledgers', $this->data['FixedAsset']['reference'], 'is_active = 1')) {
            echo 'duplicate';
            exit();
        }
        if (preg_match('/^[0-9]{2}\/[0-9]{2}\/[0-9]{4}$/', $this->data['FixedAsset']['date'])) {
            $this->data['FixedAsset']['date'] = $this->Helper->dateConvert($this->data['FixedAsset']['date']);
        }
        $this->loadModel('GeneralLedger');
        $this->GeneralLedger->create();
        $user = $this->getCurrentUser();
        $this->data['GeneralLedger']['date'] = $this->data['FixedAsset']['date'];
        $this->data['GeneralLedger']['reference'] = $this->data['FixedAsset']['reference'];
        $this->data['GeneralLedger']['note'] = $this->data['FixedAsset']['note'];
        $this->data['GeneralLedger']['created_by'] = $user['User']['id'];
        $this->data['GeneralLedger']['is_approve'] = 1;
        $this->data['GeneralLedger']['is_depreciated'] = 1;
        $this->data['GeneralLedger']['is_active'] = 1;
        if ($this->GeneralLedger->save($this->data)) {
            $generalLedgerId = $this->GeneralLedger->getLastInsertId();
            /**
             * General Ledger Detail
             */
            $this->loadModel('GeneralLedgerDetail');
            for ($i = 0; $i < sizeof($_POST['chart_account_id']); $i++) {
                if ($_POST['chart_account_id'][$i] != '') {
                    $GeneralLedgerDetail = array();
                    $this->GeneralLedgerDetail->create();
                    $GeneralLedgerDetail['GeneralLedgerDetail']['general_ledger_id'] = $generalLedgerId;
                    $GeneralLedgerDetail['GeneralLedgerDetail']['company_id'] = $this->data['FixedAsset']['company_id'];
                    $GeneralLedgerDetail['GeneralLedgerDetail']['branch_id']  = $this->data['FixedAsset']['branch_id'];
                    $GeneralLedgerDetail['GeneralLedgerDetail']['chart_account_id'] = $_POST['chart_account_id'][$i];
                    $GeneralLedgerDetail['GeneralLedgerDetail']['type'] = 'Depreciation';
                    $GeneralLedgerDetail['GeneralLedgerDetail']['debit'] = $_POST['debit'][$i];
                    $GeneralLedgerDetail['GeneralLedgerDetail']['credit'] = $_POST['credit'][$i];
                    $GeneralLedgerDetail['GeneralLedgerDetail']['memo'] = $_POST['memo'][$i];
                    $this->GeneralLedgerDetail->save($GeneralLedgerDetail);
                }
            }
            mysql_query("UPDATE fixed_assets SET is_in_used=1 WHERE is_active=1 AND is_depre=1") or die(mysql_error());
            $this->Helper->saveUserActivity($user['User']['id'], 'Fixed Assest', 'Save Post', $generalLedgerId);
            
            //Table Fixed Asset Amount
            if(!empty($_POST['fixed_asset_id'])){
                $this->loadModel('FixedAssetAmount');
                for ($z = 0; $z < sizeof($_POST['fixed_asset_id']); $z++) {
                    if($_POST['amount_post'][$z] > 0){
                        $this->FixedAssetAmount->create();
                        $this->data['FixedAssetAmount']['fixed_asset_id']   = $_POST['fixed_asset_id'][$z];
                        $this->data['FixedAssetAmount']['general_ledger_id']= $generalLedgerId;
                        if($_POST['depr_method'][$z] == "SLM"){
                            $type = 1;
                        }else if($_POST['depr_method'][$z] == "DBM"){
                            $type = 2;
                        }else{
                            $type = 3;
                        }
                        $this->data['FixedAssetAmount']['type']             = $type;
                        $this->data['FixedAssetAmount']['date_post']        = $this->data['FixedAsset']['date'];
                        $this->data['FixedAssetAmount']['amount_post']      = $_POST['amount_post'][$z];
                        $this->FixedAssetAmount->save($this->data);
                        mysql_query("UPDATE fixed_assets SET cost_remain = (cost_remain+".$_POST['amount_post'][$z].") WHERE id = ".$_POST['fixed_asset_id'][$z]."");
                    }
                }
            }
            echo 'success';
            exit;
        } else {
            $this->Helper->saveUserActivity($user['User']['id'], 'Fixed Assest', 'Save Post (Error)');
            echo 'error';
            exit;
        }
    }

}

?>