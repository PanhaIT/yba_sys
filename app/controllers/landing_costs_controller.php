<?php

class LandingCostsController extends AppController {

    var $name = 'LandingCosts';
    var $components = array('Helper');
    
    function viewByUser() {
        $this->layout = 'ajax';
    }

    function index() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        $this->Helper->saveUserActivity($user['User']['id'], 'Landed Cost', 'Dashboard');
    }

    function ajax($vendor = 'all', $filterStatus = 'all', $date = '') {
        $this->layout = 'ajax';
        $this->set(compact('vendor', 'filterStatus', 'date'));
    }

    function view($id = null) {
        $this->layout = 'ajax';
        if (!empty($id)) {
            $user = $this->getCurrentUser();
            $this->data = $this->LandingCost->read(null, $id);
            if (!empty($this->data)) {
                $this->Helper->saveUserActivity($user['User']['id'], 'Landed Cost', 'View', $id);
                $landingCostDetails = ClassRegistry::init('LandingCostDetail')->find("all", array('conditions' => array('LandingCostDetail.landing_cost_id' => $id)));
                $this->set(compact('landingCostDetails'));
            } else {
                exit;
            }
        } else {
            exit;
        }
    }

    function add() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        if (!empty($this->data)) {
            $result = array();
            // Load Model
            $this->loadModel('LandingCostDetail');
            $this->loadModel('GeneralLedger');
            $this->loadModel('GeneralLedgerDetail');
            
            // Landed Cost Type
            $landedCostType = ClassRegistry::init('LandedCostType')->find("first", array('conditions' => array('LandedCostType.id' => $this->data['LandingCost']['landed_cost_type_id'])));
            $purchaseBill   = ClassRegistry::init('PurchaseOrder')->find("first", array('conditions' => array('PurchaseOrder.id' => $this->data['LandingCost']['purchase_order_id'])));
            
            $this->LandingCost->create();
            $landingCost = array();
            $landingCost['LandingCost']['code'] = $this->data['LandingCost']['code'];
            $landingCost['LandingCost']['date'] = $this->data['LandingCost']['date'];
            $landingCost['LandingCost']['note'] = $this->data['LandingCost']['note'];
            $landingCost['LandingCost']['company_id'] = $this->data['LandingCost']['company_id'];
            $landingCost['LandingCost']['branch_id']  = $this->data['LandingCost']['branch_id'];
            $landingCost['LandingCost']['purchase_order_id']     = $this->data['LandingCost']['purchase_order_id'];
            $landingCost['LandingCost']['landed_cost_type_id']   = $this->data['LandingCost']['landed_cost_type_id'];
            $landingCost['LandingCost']['vendor_id']           = $this->data['LandingCost']['vendor_id'];
            $landingCost['LandingCost']['ap_id']               = $this->data['LandingCost']['ap_id'];
            $landingCost['LandingCost']['currency_id']  = $this->data['LandingCost']['currency_id'];
            $landingCost['LandingCost']['total_amount'] = $this->data['LandingCost']['total_amount'];
            $landingCost['LandingCost']['balance'] = $this->data['LandingCost']['total_amount'];
            $landingCost['LandingCost']['created_by'] = $user['User']['id'];
            $landingCost['LandingCost']['status'] = 1;
            if ($this->LandingCost->save($landingCost)) {
                $result['landed_cost_id'] = $landingCostId = $this->LandingCost->id;
                // Get Module Code
                $modCode = $this->Helper->getModuleCode($this->data['LandingCost']['code'], $landingCostId, 'code', 'landing_costs', 'status != -1 AND branch_id = '.$this->data['LandingCost']['branch_id']);
                // Updaet Module Code
                mysql_query("UPDATE landing_costs SET code = '".$modCode."' WHERE id = ".$landingCostId);
                // ACCOUNT Statement GL
                $this->GeneralLedger->create();
                $this->data['GeneralLedger']['landing_cost_id'] = $landingCostId;
                $this->data['GeneralLedger']['date'] = $this->data['LandingCost']['date'];
                $this->data['GeneralLedger']['reference']  = $modCode;
                $this->data['GeneralLedger']['created_by'] = $user['User']['id'];
                $this->data['GeneralLedger']['is_sys'] = 1;
                $this->GeneralLedger->save($this->data);
                $gleaderId = $this->GeneralLedger->getLastInsertId();
                
                /**
                * ACCOUNT Statement GL Detail A/P
                */
                $this->GeneralLedgerDetail->create();
                $this->data['GeneralLedgerDetail']['general_ledger_id'] = $gleaderId;
                $this->data['GeneralLedgerDetail']['type'] = "Landed Cost";
                $this->data['GeneralLedgerDetail']['chart_account_id'] = $this->data['LandingCost']['ap_id'];
                $this->data['GeneralLedgerDetail']['vendor_id']   = $this->data['LandingCost']['vendor_id'];
                $this->data['GeneralLedgerDetail']['company_id']  = $this->data['LandingCost']['company_id'];
                $this->data['GeneralLedgerDetail']['branch_id']   = $this->data['LandingCost']['branch_id'];
                $this->data['GeneralLedgerDetail']['memo']   = "ICS: Landed Cost # " . $modCode ." - Account Payable";
                $this->data['GeneralLedgerDetail']['debit']  = 0;
                $this->data['GeneralLedgerDetail']['credit'] = $this->data['LandingCost']['total_amount'];
                $this->GeneralLedgerDetail->save($this->data);
                
                for ($i = 0; $i < sizeof($_POST['product']); $i++) {
                    if (!empty($_POST['product_id'][$i])) {
                        /* Landed Cost Detail */
                        $landingCostDetail = array();
                        $this->LandingCostDetail->create();
                        $landingCostDetail['LandingCostDetail']['landing_cost_id']   = $landingCostId;
                        $landingCostDetail['LandingCostDetail']['purchase_order_detail_id']  = $_POST['purchase_order_detail'][$i];
                        $landingCostDetail['LandingCostDetail']['product_id']  = $_POST['product_id'][$i];
                        $landingCostDetail['LandingCostDetail']['qty']         = $_POST['qty'][$i];
                        $landingCostDetail['LandingCostDetail']['qty_uom_id']  = $_POST['qty_uom_id'][$i];
                        $landingCostDetail['LandingCostDetail']['conversion']  = $_POST['conversion'][$i];
                        $landingCostDetail['LandingCostDetail']['small_val_uom']  = $_POST['small_val_uom'][$i];
                        $landingCostDetail['LandingCostDetail']['unit_cost']   = $_POST['unit_cost'][$i];
                        $landingCostDetail['LandingCostDetail']['landed_cost'] = $_POST['landed_cost'][$i];
                        $this->LandingCostDetail->save($landingCostDetail);
                        if($_POST['landed_cost'][$i] > 0){
                            // General Ledger Detail (Product Asset)
                            $this->data['GeneralLedgerDetail']['general_ledger_id'] = $gleaderId;
                            $queryInvAccount = mysql_query("SELECT IFNULL((IFNULL((SELECT chart_account_id FROM accounts WHERE product_id = " . $_POST['product_id'][$i] . " AND account_type_id=1),(SELECT chart_account_id FROM pgroup_accounts WHERE pgroup_id = (SELECT pgroup_id FROM product_pgroups WHERE product_id = " . $_POST['product_id'][$i] . " ORDER BY id  DESC LIMIT 1) AND account_type_id=1))),(SELECT chart_account_id FROM account_types WHERE id=1))");
                            $dataInvAccount  = mysql_fetch_array($queryInvAccount);
                            $this->data['GeneralLedgerDetail']['chart_account_id'] = $dataInvAccount[0];
                            $this->data['GeneralLedgerDetail']['type'] = "Landed Cost";
                            $this->data['GeneralLedgerDetail']['vendor_id']   = $this->data['LandingCost']['vendor_id'];
                            $this->data['GeneralLedgerDetail']['company_id']  = $this->data['LandingCost']['company_id'];
                            $this->data['GeneralLedgerDetail']['branch_id']   = $this->data['LandingCost']['branch_id'];
                            $this->data['GeneralLedgerDetail']['product_id']  = $_POST['product_id'][$i];
                            $this->data['GeneralLedgerDetail']['memo']   = "ICS: Landed Cost # " . $modCode . " - ".$landedCostType['LandedCostType']['name'].": " . $_POST['product'][$i];
                            $this->data['GeneralLedgerDetail']['debit']  = $_POST['landed_cost'][$i];
                            $this->data['GeneralLedgerDetail']['credit'] = 0;
                            $this->GeneralLedgerDetail->saveAll($this->data);

                            // Update Inventory Valuation
                            $landedCost = $this->Helper->replaceThousand(number_format(($_POST['landed_cost'][$i] / $_POST['qty'][$i]), 9));
                            mysql_query("UPDATE inventory_valuations SET cost = (cost + ".$landedCost.") WHERE purchase_order_detail_id = ".$_POST['purchase_order_detail'][$i]);
                            // Update Product Cost
                            $sqlCheck = mysql_query("SELECT purchase_order_details.id FROM purchase_order_details INNER JOIN purchase_orders ON purchase_orders.id = purchase_order_details.purchase_order_id WHERE purchase_order_details.product_id = ".$_POST['product_id'][$i]." AND purchase_orders.status > 1 AND purchase_orders.id > ".$landingCost['LandingCost']['purchase_order_id']);
                            if(!mysql_num_rows($sqlCheck)){
                                $unitCost = $this->Helper->replaceThousand(number_format($landedCost * ($_POST['small_val_uom'][$i] / $_POST['conversion'][$i]), 5));
                                mysql_query("UPDATE products SET unit_cost = (unit_cost + ".$unitCost.") WHERE id = ".$_POST['product_id'][$i]);
                            }
                        }
                    }
                }
                // Recalculate Average Cost
                $sqlTrack = mysql_query("SELECT val, is_recalculate FROM tracks WHERE id = 1");
                $track    = mysql_fetch_array($sqlTrack);
                $dateReca = $purchaseBill['PurchaseOrder']['order_date'];
                $dateReca = date("Y-m-d", strtotime(date("Y-m-d", strtotime($dateReca)) . " -1 day"));
                if($track['val'] == "0000-00-00" || (strtotime($track['val']) >= strtotime($dateReca))){
                    mysql_query("UPDATE tracks SET val='".$dateReca."', is_recalculate = 1 WHERE id=1");
                }
                $this->Helper->saveUserActivity($user['User']['id'], 'Landed Cost', 'Save Add New', $landingCostId);
                echo json_encode($result);
                exit;
            } else {
                $this->Helper->saveUserActivity($user['User']['id'], 'Landed Cost', 'Save Add New (Error)');
                $result['code'] = 2;
                echo json_encode($result);
                exit;
            }
        }
        $this->Helper->saveUserActivity($user['User']['id'], 'Landed Cost', 'Add New');
        $companies = ClassRegistry::init('Company')->find('all',
                        array(
                            'joins' => array(
                                array('table' => 'user_companies', 'type' => 'inner', 'conditions' => array('user_companies.company_id=Company.id'))
                            ),
                            'fields' => array('Company.id', 'Company.name', 'Company.vat_calculate'),
                            'conditions' => array('Company.is_active = 1', 'user_companies.user_id=' . $user['User']['id'])
                        ));
        $branches = ClassRegistry::init('Branch')->find('all',
                        array(
                            'joins' => array(
                                array('table' => 'user_branches', 'type' => 'inner', 'conditions' => array('user_branches.branch_id=Branch.id')),
                                array('table' => 'module_code_branches AS ModuleCodeBranch', 'type' => 'left', 'conditions' => array('ModuleCodeBranch.branch_id=Branch.id'))
                            ),
                            'fields' => array('Branch.id', 'Branch.name', 'Branch.company_id', 'ModuleCodeBranch.landed_cost_code', 'Branch.currency_id', 'CurrencyCenter.symbol'),
                            'conditions' => array('Branch.is_active = 1', 'user_branches.user_id=' . $user['User']['id'])
                        ));
        $landedCostTypes = ClassRegistry::init('LandedCostType')->find("list", array('conditions' => array('LandedCostType.is_active' => 1)));
        $apAccountId = '';
        $this->set(compact("companies", "branches", "apAccountId", "landedCostTypes"));
    }

    function orderDetails() {
        $this->layout = 'ajax';
    }

    function vendor($companyId) {
        $this->layout = "ajax";
        if(!empty($companyId)){
            $this->set('companyId', $companyId);
        }else{
            exit;
        }
    }

    function vendorAjax($companyId) {
        $this->layout = "ajax";
        if(!empty($companyId)){
            $this->set('companyId', $companyId);
        }else{
            exit;
        }
    }
    
    function searchVendor() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        $vendors = ClassRegistry::init('Vendor')->find('all', array(
                    'conditions' => array('OR' => array(
                            'Vendor.name LIKE' => '%' . $this->params['url']['q'] . '%',
                            'Vendor.vendor_code LIKE' => '%' . $this->params['url']['q'] . '%',
                        ), 'Vendor.id IN (SELECT vendor_id FROM vendor_companies WHERE company_id IN (SELECT company_id FROM user_companies WHERE user_id = '.$user['User']['id'].'))'
                        , 'Vendor.is_active' => 1
                    ),
                ));
        $this->set(compact('vendors'));
    }

    function void($id = null) {
        if (!$id) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $user = $this->getCurrentUser();
        $sqlCheck = mysql_query("SELECT id FROM landing_cost_receipts WHERE landing_cost_id = ".$id." AND is_void = 0;");
        if(mysql_num_rows($sqlCheck)){
            echo MESSAGE_DATA_COULD_NOT_BE_SAVED;
            exit;
        }
        $landingCost = $this->LandingCost->read(null, $id);
        if($landingCost['LandingCost']['status'] == 1){
            $this->LandingCost->updateAll(
                    array('LandingCost.status' => 0, 'LandingCost.modified_by' => $user['User']['id']),
                    array('LandingCost.id' => $id)
            );
            $this->Helper->saveUserActivity($user['User']['id'], 'Landed Cost', 'Void', $id);
            echo MESSAGE_DATA_HAS_BEEN_DELETED;
            exit;
        } else {
            $this->Helper->saveUserActivity($user['User']['id'], 'Landed Cost', 'Void (Error Status)', $id);
            echo MESSAGE_DATA_COULD_NOT_BE_SAVED;
            exit;
        }
    }

    function edit($id = null) {
        $this->layout = 'ajax';
        if (!$id) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $user = $this->getCurrentUser();
        if (!empty($this->data)) {
            $sqlCheck = mysql_query("SELECT id FROM landing_cost_receipts WHERE landing_cost_id = ".$id." AND is_void = 0;");
            if(mysql_num_rows($sqlCheck)){
                $result['error'] = 2;
                echo json_encode($result);
                exit;
            }
            $landingCost = $this->LandingCost->read(null, $id);
            if ($landingCost['LandingCost']['status'] == 1) {
                $result = array();
                $statuEdit = "-1";
                // Load Model
                $this->loadModel('LandingCostDetail');
                $this->loadModel('GeneralLedger');
                $this->loadModel('GeneralLedgerDetail');
                // Update Status Edit
                $this->LandingCost->updateAll(
                        array('LandingCost.status' => $statuEdit, 'LandingCost.modified_by' => $user['User']['id']),
                        array('LandingCost.id' => $id)
                );
                // Update Cost
                $landingCostDetails = ClassRegistry::init('LandingCostDetail')->find("all", array('conditions' => array('LandingCostDetail.landing_cost_id' => $id)));
                foreach($landingCostDetails AS $landingCostDetail){
                    // Update Inventory Valuation
                    $landedCost = $this->Helper->replaceThousand(number_format(($landingCostDetail['LandingCostDetail']['landed_cost'] / $landingCostDetail['LandingCostDetail']['qty']), 9));
                    mysql_query("UPDATE inventory_valuations SET cost = (cost - ".$landedCost.") WHERE purchase_order_detail_id = ".$landingCostDetail['LandingCostDetail']['purchase_order_detail_id']);
                    // Update Product Cost
                    $sqlCheck = mysql_query("SELECT purchase_order_details.id FROM purchase_order_details INNER JOIN purchase_orders ON purchase_orders.id = purchase_order_details.purchase_order_id WHERE purchase_order_details.product_id = ".$landingCostDetail['LandingCostDetail']['product_id']." AND purchase_orders.status > 1 AND purchase_orders.id > ".$landingCost['LandingCost']['purchase_order_id']);
                    if(!mysql_num_rows($sqlCheck)){
                        $unitCost = $this->Helper->replaceThousand(number_format($landedCost * ($landingCostDetail['LandingCostDetail']['small_val_uom'] / $landingCostDetail['LandingCostDetail']['conversion']), 5));
                        mysql_query("UPDATE products SET unit_cost = (unit_cost - ".$unitCost.") WHERE id = ".$landingCostDetail['LandingCostDetail']['product_id']);
                    }
                }
                // Recalculate Average Cost
                $sqlTrack = mysql_query("SELECT val, is_recalculate FROM tracks WHERE id = 1");
                $track    = mysql_fetch_array($sqlTrack);
                $dateReca = $landingCost['PurchaseOrder']['order_date'];
                $dateReca = date("Y-m-d", strtotime(date("Y-m-d", strtotime($dateReca)) . " -1 day"));
                if($track['val'] == "0000-00-00" || (strtotime($track['val']) >= strtotime($dateReca))){
                    mysql_query("UPDATE tracks SET val='".$dateReca."', is_recalculate = 1 WHERE id=1");
                }
                // Update GL
                $this->GeneralLedger->updateAll(
                        array('GeneralLedger.is_active' => 2, 'GeneralLedger.modified_by' => $user['User']['id']),
                        array('GeneralLedger.landing_cost_id' => $id)
                );

                // Landed Cost Type
                $landedCostType = ClassRegistry::init('LandedCostType')->find("first", array('conditions' => array('LandedCostType.id' => $this->data['LandingCost']['landed_cost_type_id'])));
                $purchaseBill   = ClassRegistry::init('PurchaseOrder')->find("first", array('conditions' => array('PurchaseOrder.id' => $this->data['LandingCost']['purchase_order_id'])));
                $bfBranchId     = $landingCost['LandingCost']['branch_id'];
                $created   = $landingCost['LandingCost']['created'];
                $createdBy = $landingCost['LandingCost']['created_by'];
                $this->LandingCost->create();
                $landingCost = array();
                $landingCost['LandingCost']['code'] = $this->data['LandingCost']['code'];
                $landingCost['LandingCost']['date'] = $this->data['LandingCost']['date'];
                $landingCost['LandingCost']['note'] = $this->data['LandingCost']['note'];
                $landingCost['LandingCost']['company_id'] = $this->data['LandingCost']['company_id'];
                $landingCost['LandingCost']['branch_id']  = $this->data['LandingCost']['branch_id'];
                $landingCost['LandingCost']['purchase_order_id']     = $this->data['LandingCost']['purchase_order_id'];
                $landingCost['LandingCost']['landed_cost_type_id']   = $this->data['LandingCost']['landed_cost_type_id'];
                $landingCost['LandingCost']['vendor_id']           = $this->data['LandingCost']['vendor_id'];
                $landingCost['LandingCost']['ap_id']               = $this->data['LandingCost']['ap_id'];
                $landingCost['LandingCost']['currency_id']  = $this->data['LandingCost']['currency_id'];
                $landingCost['LandingCost']['total_amount'] = $this->data['LandingCost']['total_amount'];
                $landingCost['LandingCost']['balance']    = $this->data['LandingCost']['total_amount'];
                $landingCost['LandingCost']['edited']     = date("Y-m-d H:i:s");
                $landingCost['LandingCost']['edited_by']  = $user['User']['id'];
                $landingCost['LandingCost']['created']    = $created;
                $landingCost['LandingCost']['created_by'] = $createdBy;
                $landingCost['LandingCost']['status'] = 1;
                if ($this->LandingCost->save($landingCost)) {
                    $result['landed_cost_id'] = $landingCostId = $this->LandingCost->id;
                    if($this->data['LandingCost']['branch_id'] != $bfBranchId){
                        // Get Module Code
                        $modCode = $this->Helper->getModuleCode($this->data['LandingCost']['code'], $landingCostId, 'code', 'landing_costs', 'status != -1 AND branch_id = '.$this->data['LandingCost']['branch_id']);
                        // Updaet Module Code
                        mysql_query("UPDATE landing_costs SET code = '".$modCode."' WHERE id = ".$landingCostId);
                    } else {
                        $modCode = $landingCost['LandingCost']['code'];
                    }
                    // ACCOUNT Statement GL
                    $this->GeneralLedger->create();
                    $this->data['GeneralLedger']['landing_cost_id'] = $landingCostId;
                    $this->data['GeneralLedger']['date'] = $this->data['LandingCost']['date'];
                    $this->data['GeneralLedger']['reference']  = $modCode;
                    $this->data['GeneralLedger']['created_by'] = $user['User']['id'];
                    $this->data['GeneralLedger']['is_sys'] = 1;
                    $this->GeneralLedger->save($this->data);
                    $gleaderId = $this->GeneralLedger->getLastInsertId();

                    /**
                    * ACCOUNT Statement GL Detail A/P
                    */
                    $this->GeneralLedgerDetail->create();
                    $this->data['GeneralLedgerDetail']['general_ledger_id'] = $gleaderId;
                    $this->data['GeneralLedgerDetail']['type'] = "Landed Cost";
                    $this->data['GeneralLedgerDetail']['chart_account_id'] = $this->data['LandingCost']['ap_id'];
                    $this->data['GeneralLedgerDetail']['vendor_id']   = $this->data['LandingCost']['vendor_id'];
                    $this->data['GeneralLedgerDetail']['company_id']  = $this->data['LandingCost']['company_id'];
                    $this->data['GeneralLedgerDetail']['branch_id']   = $this->data['LandingCost']['branch_id'];
                    $this->data['GeneralLedgerDetail']['memo']   = "ICS: Landed Cost # " . $modCode ." - Account Payable";
                    $this->data['GeneralLedgerDetail']['debit']  = 0;
                    $this->data['GeneralLedgerDetail']['credit'] = $this->data['LandingCost']['total_amount'];
                    $this->GeneralLedgerDetail->save($this->data);
                    
                    for ($i = 0; $i < sizeof($_POST['product']); $i++) {
                        if (!empty($_POST['product_id'][$i])) {
                            /* Landed Cost Detail */
                            $landingCostDetail = array();
                            $this->LandingCostDetail->create();
                            $landingCostDetail['LandingCostDetail']['landing_cost_id']   = $landingCostId;
                            $landingCostDetail['LandingCostDetail']['purchase_order_detail_id']  = $_POST['purchase_order_detail'][$i];
                            $landingCostDetail['LandingCostDetail']['product_id']  = $_POST['product_id'][$i];
                            $landingCostDetail['LandingCostDetail']['qty']         = $_POST['qty'][$i];
                            $landingCostDetail['LandingCostDetail']['qty_uom_id']  = $_POST['qty_uom_id'][$i];
                            $landingCostDetail['LandingCostDetail']['conversion']  = $_POST['conversion'][$i];
                            $landingCostDetail['LandingCostDetail']['small_val_uom']  = $_POST['small_val_uom'][$i];
                            $landingCostDetail['LandingCostDetail']['unit_cost']   = $_POST['unit_cost'][$i];
                            $landingCostDetail['LandingCostDetail']['landed_cost'] = $_POST['landed_cost'][$i];
                            $this->LandingCostDetail->save($landingCostDetail);
                            if($_POST['landed_cost'][$i] > 0){
                                // General Ledger Detail (Product Asset)
                                $this->data['GeneralLedgerDetail']['general_ledger_id'] = $gleaderId;
                                $queryInvAccount = mysql_query("SELECT IFNULL((IFNULL((SELECT chart_account_id FROM accounts WHERE product_id = " . $_POST['product_id'][$i] . " AND account_type_id=1),(SELECT chart_account_id FROM pgroup_accounts WHERE pgroup_id = (SELECT pgroup_id FROM product_pgroups WHERE product_id = " . $_POST['product_id'][$i] . " ORDER BY id  DESC LIMIT 1) AND account_type_id=1))),(SELECT chart_account_id FROM account_types WHERE id=1))");
                                $dataInvAccount  = mysql_fetch_array($queryInvAccount);
                                $this->data['GeneralLedgerDetail']['chart_account_id'] = $dataInvAccount[0];
                                $this->data['GeneralLedgerDetail']['type'] = "Landed Cost";
                                $this->data['GeneralLedgerDetail']['vendor_id']   = $this->data['LandingCost']['vendor_id'];
                                $this->data['GeneralLedgerDetail']['company_id']  = $this->data['LandingCost']['company_id'];
                                $this->data['GeneralLedgerDetail']['branch_id']   = $this->data['LandingCost']['branch_id'];
                                $this->data['GeneralLedgerDetail']['product_id']  = $_POST['product_id'][$i];
                                $this->data['GeneralLedgerDetail']['memo']   = "ICS: Landed Cost # " . $modCode . " - ".$landedCostType['LandedCostType']['name'].": " . $_POST['product'][$i];
                                $this->data['GeneralLedgerDetail']['debit']  = $_POST['landed_cost'][$i];
                                $this->data['GeneralLedgerDetail']['credit'] = 0;
                                $this->GeneralLedgerDetail->saveAll($this->data);

                                // Update Inventory Valuation
                                $landedCost = $this->Helper->replaceThousand(number_format(($_POST['landed_cost'][$i] / $_POST['qty'][$i]), 9));
                                mysql_query("UPDATE inventory_valuations SET cost = (cost + ".$landedCost.") WHERE purchase_order_detail_id = ".$_POST['purchase_order_detail'][$i]);
                                // Update Product Cost
                                $sqlCheck = mysql_query("SELECT purchase_order_details.id FROM purchase_order_details INNER JOIN purchase_orders ON purchase_orders.id = purchase_order_details.purchase_order_id WHERE purchase_order_details.product_id = ".$_POST['product_id'][$i]." AND purchase_orders.status > 1 AND purchase_orders.id > ".$landingCost['LandingCost']['purchase_order_id']);
                                if(!mysql_num_rows($sqlCheck)){
                                    $unitCost = $this->Helper->replaceThousand(number_format($landedCost * ($_POST['small_val_uom'][$i] / $_POST['conversion'][$i]), 5));
                                    mysql_query("UPDATE products SET unit_cost = (unit_cost + ".$unitCost.") WHERE id = ".$_POST['product_id'][$i]);
                                }
                            }
                        }
                    }
                    // Recalculate Average Cost
                    $sqlTrack = mysql_query("SELECT val, is_recalculate FROM tracks WHERE id = 1");
                    $track    = mysql_fetch_array($sqlTrack);
                    $dateReca = $purchaseBill['PurchaseOrder']['order_date'];
                    $dateReca = date("Y-m-d", strtotime(date("Y-m-d", strtotime($dateReca)) . " -1 day"));
                    if($track['val'] == "0000-00-00" || (strtotime($track['val']) >= strtotime($dateReca))){
                        mysql_query("UPDATE tracks SET val='".$dateReca."', is_recalculate = 1 WHERE id=1");
                    }
                    $this->Helper->saveUserActivity($user['User']['id'], 'Landed Cost', 'Save Edit', $id, $landingCostId);
                    echo json_encode($result);
                    exit;
                } else {
                    $this->Helper->saveUserActivity($user['User']['id'], 'Landed Cost', 'Save Edit (Error)', $id);
                    // Error Saves
                    $result['error'] = 2;
                    echo json_encode($result);
                    exit;
                }
            } else {
                // Error Saves
                $this->Helper->saveUserActivity($user['User']['id'], 'Landed Cost', 'Save Edit (Error Status)', $id);
                $result['error'] = 2;
                echo json_encode($result);
                exit;
            }
            
        }

        if (!empty($id)) {
            $this->Helper->saveUserActivity($user['User']['id'], 'Landed Cost', 'Edit', $id);
            $this->data = $this->LandingCost->read(null, $id);
            $companies = ClassRegistry::init('Company')->find('all',
                            array(
                                'joins' => array(
                                    array('table' => 'user_companies', 'type' => 'inner', 'conditions' => array('user_companies.company_id=Company.id'))
                                ),
                                'fields' => array('Company.id', 'Company.name', 'Company.vat_calculate'),
                                'conditions' => array('Company.is_active = 1', 'user_companies.user_id=' . $user['User']['id'])
                            ));
            $branches = ClassRegistry::init('Branch')->find('all',
                            array(
                                'joins' => array(
                                    array('table' => 'user_branches', 'type' => 'inner', 'conditions' => array('user_branches.branch_id=Branch.id')),
                                    array('table' => 'module_code_branches AS ModuleCodeBranch', 'type' => 'left', 'conditions' => array('ModuleCodeBranch.branch_id=Branch.id'))
                                ),
                                'fields' => array('Branch.id', 'Branch.name', 'Branch.company_id', 'ModuleCodeBranch.landed_cost_code', 'Branch.currency_id', 'CurrencyCenter.symbol'),
                                'conditions' => array('Branch.is_active = 1', 'user_branches.user_id=' . $user['User']['id'])
                            ));
            $landedCostTypes = ClassRegistry::init('LandedCostType')->find("list", array('conditions' => array('LandedCostType.is_active' => 1)));
            $apAccountId = $this->data['LandingCost']['ap_id'];
            $this->set(compact("companies","branches","landedCostTypes","apAccountId"));
        }else{
            exit;
        }
    }
    
    function editDetail($landingCostId = null) {
        $this->layout = 'ajax';
        if ($landingCostId >= 0) {
            $landingCost         = ClassRegistry::init('LandingCost')->find("first", array('conditions' => array('LandingCost.id' => $landingCostId)));
            $landingCostDetails  = ClassRegistry::init('LandingCostDetail')->find("all", array('conditions' => array('LandingCostDetail.landing_cost_id' => $landingCostId)));
            $this->set(compact('landingCost', 'landingCostDetails'));
        } else {
            exit;
        }
    }
    
    function aging($id){
        $this->layout = 'ajax';
        if (!$id) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $user = $this->getCurrentUser();
        if (!empty($this->data)) {
            $this->loadModel('LandingCostReceipt');
            $result = array();
            $ladingCost = $this->LandingCost->find("first", array('conditions' => array('LandingCost.id' => $this->data['LandingCost']['id'])));
            if($ladingCost['LandingCost']['balance'] >= 0 && $ladingCost['LandingCost']['status'] > 0){
                $lastExchangeRate = ClassRegistry::init('ExchangeRate')->find("first", array("conditions" => array(
                                "ExchangeRate.branch_id" => $ladingCost['LandingCost']['branch_id'],
                                "ExchangeRate.currency_id" => $this->data['LandingCost']['currency_id']), "order" => array("ExchangeRate.created desc")));
                if(!empty($lastExchangeRate) && $lastExchangeRate['ExchangeRate']['rate_to_sell'] > 0){
                    $exchangeRateId = $lastExchangeRate['ExchangeRate']['id'];
                    $totalPaidOther = ($this->data['LandingCost']['amount_other'] / $lastExchangeRate['ExchangeRate']['rate_to_sell']);
                } else {
                    $exchangeRateId = 0;
                    $totalPaidOther = 0;
                }
                $totalPaid = $this->data['LandingCost']['amount_us'] + $totalPaidOther;
                if($totalPaid <= $ladingCost['LandingCost']['balance']){
                    $saveLanded = array();
                    $saveLanded['LandingCost']['id'] = $this->data['LandingCost']['id'];
                    $saveLanded['LandingCost']['modified_by'] = $user['User']['id'];
                    $saveLanded['LandingCost']['balance'] = $this->data['LandingCost']['balance_us'];
                    if ($this->LandingCost->save($saveLanded)) {
                        // Sales Order Receipt
                        $ladingCostReceipt = array();
                        $this->LandingCostReceipt->create();
                        $ladingCostReceipt['LandingCostReceipt']['landing_cost_id']    = $this->data['LandingCost']['id'];
                        $ladingCostReceipt['LandingCostReceipt']['exchange_rate_id']   = $exchangeRateId;
                        $ladingCostReceipt['LandingCostReceipt']['currency_id'] = $this->data['LandingCost']['currency_id'];
                        $ladingCostReceipt['LandingCostReceipt']['chart_account_id']   = $this->data['LandingCost']['chart_account_id'];
                        $ladingCostReceipt['LandingCostReceipt']['code']              = '';
                        $ladingCostReceipt['LandingCostReceipt']['amount_us']         = $this->data['LandingCost']['amount_us'];
                        $ladingCostReceipt['LandingCostReceipt']['amount_other']      = $this->data['LandingCost']['amount_other'];
                        $ladingCostReceipt['LandingCostReceipt']['total_amount']      = $this->data['LandingCost']['total_amount'];
                        $ladingCostReceipt['LandingCostReceipt']['balance']           = $this->data['LandingCost']['balance_us'];
                        $ladingCostReceipt['LandingCostReceipt']['balance_other']     = $this->data['LandingCost']['balance_other'];
                        $ladingCostReceipt['LandingCostReceipt']['created_by']        = $user['User']['id'];
                        $ladingCostReceipt['LandingCostReceipt']['pay_date']          = $this->data['LandingCost']['pay_date']!=''?$this->data['LandingCost']['pay_date']:'0000-00-00';
                        if ($this->data['LandingCost']['balance_us'] > 0) {
                            $ladingCostReceipt['LandingCostReceipt']['due_date'] = $this->data['LandingCost']['aging']!=''?$this->data['LandingCost']['aging']:'0000-00-00';
                        }
                        $this->LandingCostReceipt->save($ladingCostReceipt);
                        $result['sr_id'] = $this->LandingCostReceipt->id;
                        // Update Code & Change Receipt Generate Code
                        $modComCode = ClassRegistry::init('ModuleCodeBranch')->find('first', array('conditions' => array("ModuleCodeBranch.branch_id" => $ladingCost['LandingCost']['branch_id'])));
                        $repCode    = date("y").$modComCode['ModuleCodeBranch']['landed_cost_receipt_code'];
                        // Get Module Code
                        $modCode    = $this->Helper->getModuleCode($repCode, $result['sr_id'], 'code', 'landing_cost_receipts', 'is_void = 0');
                        // Updaet Module Code
                        mysql_query("UPDATE landing_cost_receipts SET code = '".$modCode."' WHERE id = ".$result['sr_id']);
                        // Save GL
                        $this->loadModel('GeneralLedger');
                        $this->data['GeneralLedger']['landing_cost_id'] = $this->data['LandingCost']['id'];
                        $this->data['GeneralLedger']['landing_cost_receipt_id'] = $result['sr_id'];
                        $this->data['GeneralLedger']['date'] = $this->data['LandingCost']['pay_date']!=''?$this->data['LandingCost']['pay_date']:'0000-00-00';
                        $this->data['GeneralLedger']['reference'] = $ladingCost['LandingCost']['code'];
                        $this->data['GeneralLedger']['is_sys'] = 1;
                        $this->data['GeneralLedger']['created_by'] = $user['User']['id'];
                        $this->GeneralLedger->saveAll($this->data);
                        $gleaderId = $this->GeneralLedger->getLastInsertId();
                        // Load GL Detail
                        $this->loadModel('GeneralLedgerDetail');
                        // Load Account Type
                        $sql = mysql_query("SELECT * FROM `account_types`");
                        while ($r = mysql_fetch_array($sql)) {
                            if ($r['id'] == 13) {
                                if ($this->data['LandingCost']['chart_account_id'] == '') {
                                    $cash_purchase = $r['chart_account_id'];
                                } else {
                                    $cash_purchase = $this->data['LandingCost']['chart_account_id'];
                                }
                            }
                        }

                        // Save GL Detail
                        $this->data['GeneralLedgerDetail']['general_ledger_id'] = $gleaderId;
                        $this->data['GeneralLedgerDetail']['type'] = "Landed Cost Payment";
                        $this->data['GeneralLedgerDetail']['vendor_id']   = $ladingCost['LandingCost']['vendor_id'];
                        $this->data['GeneralLedgerDetail']['company_id']  = $ladingCost['LandingCost']['company_id'];
                        $this->data['GeneralLedgerDetail']['branch_id']   = $ladingCost['LandingCost']['branch_id'];
                        $this->data['GeneralLedgerDetail']['memo'] = "ICS: Landed Cost # " . $ladingCost['LandingCost']['code'];
                        for ($i = 0; $i < 2; $i++) {
                            if ($i == 0) {
                                $this->data['GeneralLedgerDetail']['chart_account_id'] = $cash_purchase;
                                $this->data['GeneralLedgerDetail']['debit']  = 0;
                                $this->data['GeneralLedgerDetail']['credit'] = $totalPaid;
                                $this->GeneralLedgerDetail->saveAll($this->data);
                            } else {
                                $queryAp = mysql_query("SELECT ap_id FROM landing_costs WHERE id=" . $this->data['LandingCost']['id']);
                                $dataAp = mysql_fetch_array($queryAp);
                                $this->data['GeneralLedgerDetail']['chart_account_id'] = $dataAp[0];
                                $this->data['GeneralLedgerDetail']['debit']  = $totalPaid;
                                $this->data['GeneralLedgerDetail']['credit'] = 0;
                                $this->GeneralLedgerDetail->saveAll($this->data);
                            }
                        }
                        $this->Helper->saveUserActivity($user['User']['id'], 'Landed Cost Payment', 'Save Add New', $result['sr_id']);
                        echo json_encode($result);
                        exit;
                    }
                }else{
                    $this->Helper->saveUserActivity($user['User']['id'], 'Landed Cost Payment', 'Save Add New (Error)');
                    $result['sr_id'] = 0;
                    echo json_encode($result);
                    exit;
                }
            }else{
                $this->Helper->saveUserActivity($user['User']['id'], 'Landed Cost Payment', 'Save Add New (Error PB Status)');
                $result['sr_id'] = 0;
                echo json_encode($result);
                exit;
            }
        }
        $this->Helper->saveUserActivity($user['User']['id'], 'Landed Cost Payment', 'Aging', $id);
        $this->data = $this->LandingCost->read(null, $id);
        if (!empty($this->data)) {
            $landingCostDetails  = ClassRegistry::init('LandingCostDetail')->find("all", array('conditions' => array('LandingCostDetail.landing_cost_id' => $id)));
            $landingCostReceipts = ClassRegistry::init('LandingCostReceipt')->find("all", array('conditions' => array('LandingCostReceipt.landing_cost_id' => $id, 'LandingCostReceipt.is_void' => 0)));
            $cashBankAccount     = ClassRegistry::init('AccountType')->findById(6);
            $cashBankAccountId   = $cashBankAccount['AccountType']['chart_account_id'];
            $this->set(compact('landingCostDetails', 'landingCostReceipts', 'cashBankAccountId'));
        } else {
            exit;
        }
    }
    
    function voidReceipt($id) {
        if (!$id) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $user = $this->getCurrentUser();
        $this->loadModel('GeneralLedger');
        $this->loadModel('LandingCostReceipt');
        $receipt = ClassRegistry::init('LandingCostReceipt')->find("first",
                        array('conditions' => array('LandingCostReceipt.id' => $id)));
        if(!empty($receipt) && $receipt['LandingCostReceipt']['is_void'] == 0){
            $this->LandingCostReceipt->updateAll(
                    array('LandingCostReceipt.is_void' => 1, 'LandingCostReceipt.modified_by' => $user['User']['id']),
                    array('LandingCostReceipt.id' => $id)
            );
            $exchangeRate = ClassRegistry::init('ExchangeRate')->find("first", array("conditions" => array("ExchangeRate.id" => $receipt['LandingCostReceipt']['exchange_rate_id'])));
            if(!empty($exchangeRate) && $exchangeRate['ExchangeRate']['rate_to_sell'] > 0){
                $totalPaidOther = $receipt['LandingCostReceipt']['amount_other'] / $exchangeRate['ExchangeRate']['rate_to_sell'];
            } else {
                $totalPaidOther = 0;
            }
            $total_amount = $receipt['LandingCostReceipt']['amount_us'] + $totalPaidOther;

            mysql_query("UPDATE landing_costs SET balance = balance+" . $total_amount . " WHERE id=" . $receipt['LandingCostReceipt']['landing_cost_id']);

            $this->GeneralLedger->updateAll(
                    array('GeneralLedger.is_active' => 2, 'GeneralLedger.modified_by' => $user['User']['id']),
                    array('GeneralLedger.landing_cost_receipt_id' => $id)
            );
            $this->Helper->saveUserActivity($user['User']['id'], 'Landing Cost Payment', 'Void', $id);
            echo MESSAGE_DATA_HAS_BEEN_DELETED;
            exit;
        }else{
            $this->Helper->saveUserActivity($user['User']['id'], 'Landing Cost Payment', 'Void (Error)', $id);
            echo MESSAGE_DATA_COULD_NOT_BE_SAVED;
            exit;
        }
    }
    
    function purchaseBill($companyId = null, $branchId = null) {
        $this->layout = 'ajax';
        $this->set(compact('companyId', 'branchId'));
    }

    function purchaseBillAjax($companyId = null, $branchId = null) {
        $this->layout = 'ajax';
        $this->set(compact('companyId', 'branchId'));
    }
    
    function getPurchaseLandingCost($id = null){
        $this->layout = 'ajax';
        $result = array();
        if (empty($id)) {
            $result['error'] = 1;
            echo json_encode($result);
            exit;
        }
        // Get Decimal
        $sqlOption = mysql_query("SELECT product_cost_decimal FROM setting_options");
        $rowOption = mysql_fetch_array($sqlOption);
        $rowList = array();
        $rowLbl  = "";
        $index   = '';
        // Get Product
        $sqlSalesDetail  = mysql_query("SELECT products.id AS product_id, products.code AS code, products.barcode AS barcode, products.name AS name, products.small_val_uom AS small_val_uom, sd.id AS id, SUM(sd.qty) AS qty, sd.qty_uom_id AS qty_uom_id, sd.new_unit_cost AS new_unit_cost, sd.conversion AS conversion FROM purchase_order_details AS sd INNER JOIN purchase_orders AS so ON so.id = sd.purchase_order_id INNER JOIN products ON products.id = sd.product_id WHERE sd.purchase_order_id = ".$id." GROUP BY product_id, qty_uom_id, new_unit_cost;");
        while($rowDetail = mysql_fetch_array($sqlSalesDetail)){
            $sqlVal = mysql_query("SELECT cost FROM inventory_valuations WHERE purchase_order_detail_id = ".$rowDetail['id']);
            $rowVal = mysql_fetch_array($sqlVal);
            $index      = rand();
            $productName = str_replace('"', '&quot;', $rowDetail['name']);
            // Open Tr
            $rowLbl .= '<tr class="tblLandingCostList">';
            // Index
            $rowLbl .= '<td class="first" style="width:5%; text-align: center;padding: 0px; height: 30px;">'.++$index.'</td>';
            // UPC
            $rowLbl .= '<td style="width:10%; text-align: left; padding: 5px;"><input type="text" readonly="" class="lblUPC" value="'.$rowDetail['barcode'].'" /></td>';
            // SKU
            $rowLbl .= '<td style="width:10%; text-align: left; padding: 5px;"><input type="text" readonly="" class="lblSKU" value="'.$rowDetail['code'].'" /></td>';
            // Product
            $rowLbl .= '<td style="width:23%; text-align: left; padding: 5px;">';
            $rowLbl .= '<div class="inputContainer" style="width:100%">';
            $rowLbl .= '<input type="hidden" name="purchase_order_detail[]" value="'.$rowDetail['id'].'" />';
            $rowLbl .= '<input type="hidden" name="conversion[]" value="'.$rowDetail['conversion'].'" />';
            $rowLbl .= '<input type="hidden" name="small_val_uom[]" value="'.$rowDetail['small_val_uom'].'" />';
            $rowLbl .= '<input type="hidden" id="product_id_'.$index.'" class="product_id" value="'.$rowDetail['product_id'].'" name="product_id[]" />';
            $rowLbl .= '<input type="text" value="'.$productName.'" name="product[]" class="product validate[required]" style="width: 90%;" />';
            $rowLbl .= '</div>';
            $rowLbl .= '</td>';
            // Qty
            $qty = $rowDetail['qty'];
            $rowLbl .= '<td style="width:8%; text-align: center;padding: 0px;">';
            $rowLbl .= '<div class="inputContainer" style="width:100%">';
            $rowLbl .= '<input type="hidden" name="qty[]" value="'.$qty.'" />';
            $rowLbl .= number_format($qty, 0);
            $rowLbl .= '</div>';
            $rowLbl .= '</td>';
            // UOM
            $uomName = '';
            if(!empty($rowDetail['qty_uom_id'])){
                $sqlUom = mysql_query("SELECT abbr FROM uoms WHERE id=".$rowDetail['qty_uom_id'].";");
                if(mysql_num_rows($sqlUom)){
                    $rowUom = mysql_fetch_array($sqlUom);
                    $uomName = $rowUom[0];
                }
            }
            $rowLbl .= '<td style="width:15%; padding: 0px; text-align: center">';
            $rowLbl .= '<div class="inputContainer" style="width:100%">';
            $rowLbl .= '<input type="hidden" name="qty_uom_id[]" value="'.$rowDetail['qty_uom_id'].'" />';
            $rowLbl .= $uomName;
            $rowLbl .= '</div>';
            $rowLbl .= '</td>';
            // Unit Cost
            $rowLbl .= '<td style="width:11%; padding: 0px; text-align: center">';
            $rowLbl .= '<div class="inputContainer" style="width:100%">';
            $rowLbl .= '<input type="hidden" name="unit_cost[]" value="'.$rowVal[0].'" />';
            $rowLbl .= number_format($rowDetail['new_unit_cost'], $rowOption[0]);
            $rowLbl .= '</div>';
            $rowLbl .= '</td>';
            // Landed Cost
            $rowLbl .= '<td style="width:11%; text-align: center; padding: 0px;">';
            $rowLbl .= '<div class="inputContainer" style="width:100%">';
            $rowLbl .= '<input type="text" value="0" id="landedCost_'.$index.'" name="landed_cost[]" style="width:84%" class="float landedCost" />';
            $rowLbl .= '</div>';
            $rowLbl .= '</td>';
            // Button Remove
            $rowLbl .= '<td style="width:7%">';
            $rowLbl .= '<img alt="Remove" src="'.$this->webroot.'img/button/cross.png" class="btnRemoveLandingCost" align="absmiddle" style="cursor: pointer;" onmouseover="Tip(\'Remove\')" />';
            $rowLbl .= '&nbsp; <img alt="Up" src="'.$this->webroot.'img/button/move_up.png" class="btnMoveUpLandingCost" align="absmiddle" style="cursor: pointer;" onmouseover="Tip(\'Up\')" />';
            $rowLbl .= '&nbsp; <img alt="Down" src="'.$this->webroot . 'img/button/move_down.png" class="btnMoveDownLandingCost" align="absmiddle" style="cursor: pointer;" onmouseover="Tip(\'Down\')" />';
            $rowLbl .= '</td>';
            // Close Tr
            $rowLbl .= '</tr>';
        }
        $rowList['error']  = 0;
        $rowList['result'] = $rowLbl;
        echo json_encode($rowList);
        exit;
    }
    
    function close($id = null) {
        if (!$id) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $user = $this->getCurrentUser();
        $landingCost = $this->LandingCost->read(null, $id);
        if($landingCost['LandingCost']['status'] == 1){
            $this->Helper->saveUserActivity($user['User']['id'], 'Landed Cost', 'Close', $id);
            $modified = date("Y-m-d H:i:s");                    
            $this->LandingCost->updateAll(
                    array('LandingCost.status' => 2, "LandingCost.modified_by" => $user['User']['id'], 'LandingCost.modified' => "'$modified'"),
                    array('LandingCost.id' => $id)
            );
            echo MESSAGE_DATA_HAS_BEEN_CLOSED;
            exit;
        } else {
            echo MESSAGE_DATA_COULD_NOT_BE_SAVED;
            exit;
        }
    }

}

?>