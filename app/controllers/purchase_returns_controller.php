<?php

class PurchaseReturnsController extends AppController {

    var $name = 'PurchaseReturns';
    var $components = array('Helper', 'Inventory');

    function index() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        $this->Helper->saveUserActivity($user['User']['id'], 'Purchase Return', 'Dashboard');
        $locationGroups = ClassRegistry::init('LocationGroup')->find('all', array('joins' => array(array('table' => 'user_location_groups', 'type' => 'inner', 'conditions' => array('user_location_groups.location_group_id=LocationGroup.id'))),'conditions' => array('user_location_groups.user_id=' . $user['User']['id'], 'LocationGroup.is_active' => '1', 'LocationGroup.location_group_type_id != 1')));
        $locations = ClassRegistry::init('Location')->find('all', array('joins' => array(array('table' => 'user_locations', 'type' => 'inner', 'conditions' => array('user_locations.location_id=Location.id'))), 'conditions' => array('user_locations.user_id=' . $user['User']['id'], 'Location.is_active=1'), 'order' => 'Location.name'));
        $this->set(compact('locationGroups', 'locations'));
    }

    function ajax($filterStatus = 'all', $balance = 'all', $vendor = "all", $date = '') {
        $this->layout = 'ajax';
        $this->set(compact('filterStatus', 'balance', 'vendor',  'date'));
    }

    function view($id = null) {
        $this->layout = 'ajax';
        if (!empty($id)) {
            $user = $this->getCurrentUser();
            $this->Helper->saveUserActivity($user['User']['id'], 'Purchase Return', 'View', $id);
            $this->data = $this->PurchaseReturn->read(null, $id);
            $purchaseReturn = ClassRegistry::init('PurchaseReturn')->find("first", array('conditions' => array('PurchaseReturn.id' => $id)));
            if (!empty($purchaseReturn)) {
                $purchaseReturnDetails  = ClassRegistry::init('PurchaseReturnDetail')->find("all", array('conditions' => array('PurchaseReturnDetail.purchase_return_id' => $id)));
                $purchaseReturnReceipts = ClassRegistry::init('PurchaseReturnReceipt')->find("all", array('conditions' => array('PurchaseReturnReceipt.purchase_return_id' => $id, 'PurchaseReturnReceipt.is_void' => 0)));
                $purchaseReturnServices = ClassRegistry::init('PurchaseReturnService')->find('all', array('conditions' => array('PurchaseReturnService.purchase_return_id' => $id)));
                $this->set(compact('purchaseReturn', 'purchaseReturnDetails', 'purchaseReturnServices', 'purchaseReturnReceipts'));
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
            $dateNow  = date("Y-m-d H:i:s");
            $this->loadModel('PurchaseReturnDetail');
            $this->loadModel('PurchaseReturnService');
            $this->loadModel('PurchaseReturnReceipt');
            $this->loadModel('GeneralLedger');
            $this->loadModel('GeneralLedgerDetail');
            $this->loadModel('InventoryValuation');
            $this->loadModel('AccountType');
            $this->loadModel('Company');
            $this->loadModel('PurchaseReturnReceive');
            $this->loadModel('Transaction');
            $this->loadModel('TransactionDetail');
            
            if($this->data['PurchaseReturn']['preview_id'] != ''){
                $product_return = $this->PurchaseReturn->read(null, $this->data['PurchaseReturn']['preview_id']);
                // Check Save Transaction
                $checkTransaction = true;
                $transactionLogId = 0;
                $sqlCheck = mysql_query("SELECT * FROM transactions WHERE type = 'Purchase Return' AND action = 1 AND module_id = ".$this->data['PurchaseReturn']['preview_id']);
                if(mysql_num_rows($sqlCheck)){
                    $rowCheck  = mysql_fetch_array($sqlCheck);
                    $sqlDetail = mysql_query("SELECT * FROM transaction_details WHERE transaction_id = ".$rowCheck['id']);
                    $rowDetail = mysql_num_rows($sqlDetail);
                    $transactionLogId = $rowCheck['id'];
                    if($rowDetail > 0){
                        // Check Total Transaction
                        $totalD = $rowCheck['products'] + $rowCheck['service'];
                        if($totalD != $rowDetail){
                            $checkTransaction = false;
                        } else {
                            $totalAcctD = 0;
                            while($rowD = mysql_fetch_array($sqlDetail)){
                                $totalAcctD += $rowD['save_acct'];
                                if($rowD['type'] == 1){
                                    if($rowD['inventory_valutaion'] != '1'){
                                        $checkTransaction = false;
                                        break;
                                    }
                                    if($product_return['PurchaseReturn']['status'] == 2){
                                        if($rowD['inventory'] != '1' || $rowD['inventory_total'] != '1' || $rowD['loc_inventory'] != '1' || $rowD['loc_inventory_total'] != '1' || $rowD['loc_inventory_detail'] != '1' || $rowD['g_inventory'] != '1' || $rowD['g_inventory_detail'] != '1'){
                                            $checkTransaction = false;
                                            break;
                                        }
                                    }
                                }
                            }
                            if($checkTransaction == true){
                                // Check Account
                                $sqlAcct = mysql_query("SELECT COUNT(id) FROM general_ledger_details WHERE general_ledger_id = (SELECT id FROM general_ledgers WHERE purchase_return_id = ".$this->data['PurchaseReturn']['preview_id']." AND purchase_return_receipt_id IS NULL LIMIT 1)");
                                if(mysql_num_rows($sqlAcct)){
                                    $rowAcct = mysql_fetch_array($sqlAcct);
                                    if($rowAcct[0] != ($totalAcctD + $rowCheck['save_acct'])){
                                        $checkTransaction = false;
                                    }
                                } else {
                                    $checkTransaction = false;
                                }
                            }
                        }
                    } else {
                        $checkTransaction = false;
                    }
                }
                if($checkTransaction == true){
                    // Remove Transaction Log
                    mysql_query("DELETE FROM transactions WHERE id = ".$transactionLogId);
                    mysql_query("DELETE FROM transaction_details WHERE transaction_id = ".$transactionLogId);
                    // Update
                    $this->PurchaseReturn->updateAll(
                        array('PurchaseReturn.status' => -1, 'PurchaseReturn.modified_by' => $user['User']['id']), array('PurchaseReturn.id' => $this->data['PurchaseReturn']['preview_id'])
                    );
                    $this->GeneralLedger->updateAll(
                            array('GeneralLedger.is_active' => 2, 'GeneralLedger.modified_by' => $user['User']['id']), array('GeneralLedger.purchase_return_id' => $this->data['PurchaseReturn']['preview_id'])
                    );
                    $this->InventoryValuation->updateAll(
                            array('InventoryValuation.is_active' => "2"), array('InventoryValuation.purchase_return_id' => $this->data['PurchaseReturn']['preview_id'])
                    );
                    
                    if($product_return['PurchaseReturn']['status'] == 2){
                        $purchaseReturnDetails = ClassRegistry::init('PurchaseReturnDetail')->find("all", array('conditions' => array('PurchaseReturnDetail.purchase_return_id' => $this->data['PurchaseReturn']['preview_id'])));
                        foreach($purchaseReturnDetails AS $purchaseReturnDetail){
                            $qtyOrderSmall = ($purchaseReturnDetail['PurchaseReturnDetail']['qty'] * $purchaseReturnDetail['PurchaseReturnDetail']['conversion']);
                            // Update Inventory (Purchase Return)
                            $data = array();
                            $data['module_type']        = 20;
                            $data['purchase_return_id'] = $this->data['PurchaseReturn']['preview_id'];
                            $data['product_id']         = $purchaseReturnDetail['PurchaseReturnDetail']['product_id'];
                            $data['location_id']        = $product_return['PurchaseReturn']['location_id'];
                            $data['location_group_id']  = $product_return['PurchaseReturn']['location_group_id'];
                            $data['expired_date'] = $purchaseReturnDetail['PurchaseReturnDetail']['expired_date']!=""?$purchaseReturnDetail['PurchaseReturnDetail']['expired_date']:'0000-00-00';
                            $data['lots_number']  = $purchaseReturnDetail['PurchaseReturnDetail']['lots_number']!=""?$purchaseReturnDetail['PurchaseReturnDetail']['lots_number']:0;
                            $data['date']         = $product_return['PurchaseReturn']['order_date'];
                            $data['total_qty']    = $qtyOrderSmall;
                            $data['total_order']  = $qtyOrderSmall;
                            $data['total_free']   = 0;
                            $data['user_id']      = $user['User']['id'];
                            $data['customer_id']  = "";
                            $data['vendor_id']    = $product_return['PurchaseReturn']['vendor_id'];
                            $data['unit_cost']    = 0;
                            $data['unit_price']   = 0;
                            $data['transaction_id'] = '';
                            // Update Invetory Location
                            $this->Inventory->saveInventory($data);
                            // Update Inventory Group
                            $this->Inventory->saveGroupTotalDetail($data);
                        }
                    }
                } else {
                    $this->Helper->saveUserActivity($user['User']['id'], 'Purchase Return', 'Save Add New (Error Save Transaction)');
                    $result['code'] = 2;
                    echo json_encode($result);
                    exit;
                }
            }

            $result = array();
            $this->PurchaseReturn->create();
            $this->GeneralLedger->create();

            //  Find Chart Account
            $apAccount = $this->AccountType->findById(14);
            $purchaseReturn = array();
            $purchaseReturn['PurchaseReturn']['sys_code']   = md5(rand().strtotime(date("Y-m-d H:i:s")).$user['User']['id']);
            $purchaseReturn['PurchaseReturn']['created']    = $dateNow;
            $purchaseReturn['PurchaseReturn']['created_by'] = $user['User']['id'];
            $purchaseReturn['PurchaseReturn']['company_id'] = $this->data['PurchaseReturn']['company_id'];
            $purchaseReturn['PurchaseReturn']['branch_id']  = $this->data['PurchaseReturn']['branch_id'];
            $purchaseReturn['PurchaseReturn']['location_group_id'] = $this->data['PurchaseReturn']['location_group_id'];
            $purchaseReturn['PurchaseReturn']['location_id'] = $this->data['PurchaseReturn']['location_id'];
            $purchaseReturn['PurchaseReturn']['vendor_id']   = $this->data['PurchaseReturn']['vendor_id'];
            $purchaseReturn['PurchaseReturn']['currency_id'] = $this->data['PurchaseReturn']['currency_id'];
            $purchaseReturn['PurchaseReturn']['note']    = $this->data['PurchaseReturn']['note'];
            $purchaseReturn['PurchaseReturn']['ap_id']   = $apAccount['AccountType']['chart_account_id'];
            $purchaseReturn['PurchaseReturn']['pr_code'] = $this->data['PurchaseReturn']['pr_code'];
            $purchaseReturn['PurchaseReturn']['balance'] = $this->data['PurchaseReturn']['total_amount'] + $this->data['PurchaseReturn']['total_vat'];
            $purchaseReturn['PurchaseReturn']['total_amount'] = $this->data['PurchaseReturn']['total_amount'];
            $purchaseReturn['PurchaseReturn']['order_date']  = $this->data['PurchaseReturn']['order_date'];
            $purchaseReturn['PurchaseReturn']['vat_percent'] = $this->data['PurchaseReturn']['vat_percent'];
            $purchaseReturn['PurchaseReturn']['total_vat']   = $this->data['PurchaseReturn']['total_vat'];
            $purchaseReturn['PurchaseReturn']['vat_setting_id']  = $this->data['PurchaseReturn']['vat_setting_id'];
            $purchaseReturn['PurchaseReturn']['vat_calculate']   = $this->data['PurchaseReturn']['vat_calculate'];
            $purchaseReturn['PurchaseReturn']['vat_chart_account_id'] = $this->data['PurchaseReturn']['vat_chart_account_id'];
            $purchaseReturn['PurchaseReturn']['status'] = 2;
            if ($this->PurchaseReturn->save($purchaseReturn)) {
                $purchaseReturnId = $this->PurchaseReturn->id;
                $company         = $this->Company->read(null, $this->data['PurchaseReturn']['company_id']);
                $classId         = $this->Helper->getClassId($company['Company']['id'], $company['Company']['classes'], $this->data['PurchaseReturn']['location_group_id']);
                if($this->data['PurchaseReturn']['pr_code'] == ''){
                    $branchCode  = ClassRegistry::init('ModuleCodeBranch')->find('first', array('conditions' => array('ModuleCodeBranch.branch_id' => $this->data['PurchaseReturn']['branch_id'])));
                    $this->data['PurchaseReturn']['pr_code'] = date("y").$branchCode['ModuleCodeBranch']['br_code'];
                    // Get Module Code
                    $modCode = $this->Helper->getModuleCode($this->data['PurchaseReturn']['pr_code'], $purchaseReturnId, 'pr_code', 'purchase_returns', 'status != -1 AND branch_id = '.$this->data['PurchaseReturn']['branch_id']);
                    // Updaet Module Code
                    mysql_query("UPDATE purchase_returns SET pr_code = '".$modCode."' WHERE id = ".$purchaseReturnId);
                } else {
                    $modCode     = $this->data['PurchaseReturn']['pr_code'];
                }
                // Transaction 
                $transactionAcct = 0;
                $transactionPro  = 0;
                $transactionSer  = 0;
                $transaction = array();
                $this->Transaction->create();
                $transaction['Transaction']['module_id']  = $purchaseReturnId;
                $transaction['Transaction']['type']       = 'Purchase Return';
                $transaction['Transaction']['created']    = $dateNow;
                $transaction['Transaction']['created_by'] = $user['User']['id'];
                $this->Transaction->save($transaction);
                $transactionId = $this->Transaction->id;
                // Create General Ledger
                $generalLedger = array();
                $this->GeneralLedger->create();
                $generalLedger['GeneralLedger']['sys_code']   = md5(rand().strtotime(date("Y-m-d H:i:s")).$user['User']['id']);
                $generalLedger['GeneralLedger']['purchase_return_id'] = $purchaseReturnId;
                $generalLedger['GeneralLedger']['date'] = $this->data['PurchaseReturn']['order_date'];
                $generalLedger['GeneralLedger']['reference']  = $modCode;
                $generalLedger['GeneralLedger']['created']    = $dateNow;
                $generalLedger['GeneralLedger']['created_by'] = $user['User']['id'];
                $generalLedger['GeneralLedger']['is_sys'] = 1;
                $generalLedger['GeneralLedger']['is_adj'] = 0;
                $generalLedger['GeneralLedger']['is_active'] = 1;
                $this->GeneralLedger->save($generalLedger);
                $generalLedgerId = $this->GeneralLedger->id;
                // General Ledger Detail (A/P)
                $this->GeneralLedgerDetail->create();
                $generalLedgerDetail = array();
                $generalLedgerDetail['GeneralLedgerDetail']['general_ledger_id'] = $generalLedgerId;
                $generalLedgerDetail['GeneralLedgerDetail']['chart_account_id']  = $purchaseReturn['PurchaseReturn']['ap_id'];
                $generalLedgerDetail['GeneralLedgerDetail']['location_group_id'] = $purchaseReturn['PurchaseReturn']['location_group_id'];
                $generalLedgerDetail['GeneralLedgerDetail']['company_id']  = $purchaseReturn['PurchaseReturn']['company_id'];
                $generalLedgerDetail['GeneralLedgerDetail']['branch_id']   = $purchaseReturn['PurchaseReturn']['branch_id'];
                $generalLedgerDetail['GeneralLedgerDetail']['location_id'] = $purchaseReturn['PurchaseReturn']['location_id'];
                $generalLedgerDetail['GeneralLedgerDetail']['type']   = 'Purchase Return';
                $generalLedgerDetail['GeneralLedgerDetail']['debit']  = $purchaseReturn['PurchaseReturn']['balance'];
                $generalLedgerDetail['GeneralLedgerDetail']['credit'] = 0;
                $generalLedgerDetail['GeneralLedgerDetail']['memo']   = 'ICS: Purchase Return # ' . $modCode;
                $generalLedgerDetail['GeneralLedgerDetail']['vendor_id'] = $purchaseReturn['PurchaseReturn']['vendor_id'];
                $generalLedgerDetail['GeneralLedgerDetail']['class_id']  = $classId;
                $this->GeneralLedgerDetail->save($generalLedgerDetail);
                $transactionAcct++;
                if (($purchaseReturn['PurchaseReturn']['total_vat']) > 0) {
                    $this->GeneralLedgerDetail->create();
                    $generalLedgerDetail['GeneralLedgerDetail']['chart_account_id'] = $purchaseReturn['PurchaseReturn']['vat_chart_account_id'];
                    $generalLedgerDetail['GeneralLedgerDetail']['debit']  = 0;
                    $generalLedgerDetail['GeneralLedgerDetail']['credit'] = $purchaseReturn['PurchaseReturn']['total_vat'];
                    $generalLedgerDetail['GeneralLedgerDetail']['memo']   = 'ICS: Purchase Return # ' . $modCode . ' Total VAT';
                    $this->GeneralLedgerDetail->save($generalLedgerDetail);
                    $transactionAcct++;
                }

                for ($i = 0; $i < sizeof($_POST['product']); $i++) {
                    if (!empty($_POST['product_id'][$i])) {
                        $tranDetailAcct = 0;
                        $purchaseReturnDetail = array();
                        // Purchase Return Detail
                        $this->PurchaseReturnDetail->create();
                        $purchaseReturnDetail['PurchaseReturnDetail']['sys_code'] = md5(rand().strtotime(date("Y-m-d H:i:s")).$user['User']['id']);
                        $purchaseReturnDetail['PurchaseReturnDetail']['purchase_return_id'] = $purchaseReturnId;
                        $purchaseReturnDetail['PurchaseReturnDetail']['product_id']   = $_POST['product_id'][$i];
                        $purchaseReturnDetail['PurchaseReturnDetail']['qty']          = $_POST['qty'][$i];
                        $purchaseReturnDetail['PurchaseReturnDetail']['qty_uom_id']   = $_POST['qty_uom_id'][$i];
                        $purchaseReturnDetail['PurchaseReturnDetail']['conversion']   = $_POST['conversion'][$i];
                        $purchaseReturnDetail['PurchaseReturnDetail']['unit_price']   = $_POST['unit_price'][$i];
                        $purchaseReturnDetail['PurchaseReturnDetail']['total_price']  = $_POST['total_price'][$i];
                        $purchaseReturnDetail['PurchaseReturnDetail']['note']         = $_POST['note'][$i];
                        if($_POST['lots_number'][$i] != '' && $_POST['lots_number'][$i] != '0'){
                            $lotsNumber = $_POST['lots_number'][$i];
                        } else {
                            $lotsNumber = '0';
                        }
                        $purchaseReturnDetail['PurchaseReturnDetail']['lots_number'] = $lotsNumber;
                        if($_POST['expired_date'][$i] != '' && $_POST['expired_date'][$i] != '0000-00-00'){
                            $expiredDate = $_POST['expired_date'][$i];
                        } else {
                            $expiredDate = '0000-00-00';
                        }
                        $purchaseReturnDetail['PurchaseReturnDetail']['expired_date'] = $expiredDate;
                        $this->PurchaseReturnDetail->save($purchaseReturnDetail);
                        $purchaseReturnDetailId = $this->PurchaseReturnDetail->id;
                        $transactionPro++;
                        $qtyOrder      = $_POST['qty'][$i] / ($_POST['small_val_uom'][$i] / $_POST['conversion'][$i]);
                        $qtyOrderSmall = $_POST['qty'][$i] * $_POST['conversion'][$i];
                        
                        // Transaction Detail
                        $tranDetail = array();
                        $this->TransactionDetail->create();
                        $tranDetail['TransactionDetail']['transaction_id']  = $transactionId;
                        $tranDetail['TransactionDetail']['type']       = 1;
                        $tranDetail['TransactionDetail']['module_id']  = $purchaseReturnDetailId;
                        $this->TransactionDetail->save($tranDetail);
                        $tranDetailId = $this->TransactionDetail->id;
                        
                        // Update Inventory (Purchase Return)
                        $data = array();
                        $data['module_type']        = 7;
                        $data['purchase_return_id'] = $purchaseReturnId;
                        $data['product_id']         = $_POST['product_id'][$i];
                        $data['location_id']        = $purchaseReturn['PurchaseReturn']['location_id'];
                        $data['location_group_id']  = $purchaseReturn['PurchaseReturn']['location_group_id'];
                        $data['expired_date'] = $purchaseReturnDetail['PurchaseReturnDetail']['expired_date'];
                        $data['lots_number']  = $purchaseReturnDetail['PurchaseReturnDetail']['lots_number'];
                        $data['date']         = $purchaseReturn['PurchaseReturn']['order_date'];
                        $data['total_qty']    = $qtyOrderSmall;
                        $data['total_order']  = $qtyOrderSmall;
                        $data['total_free']   = 0;
                        $data['user_id']      = $user['User']['id'];
                        $data['customer_id']  = "";
                        $data['vendor_id']    = $purchaseReturn['PurchaseReturn']['vendor_id'];
                        $data['unit_cost']    = 0;
                        $data['unit_price']   = 0;
                        $data['transaction_id'] = $tranDetailId;
                        // Update Invetory Location
                        $this->Inventory->saveInventory($data);
                        // Update Inventory Group
                        $this->Inventory->saveGroupTotalDetail($data);

                        // Insert Into Receive
                        $billReturnReceive = array();
                        $this->PurchaseReturnReceive->create();
                        $billReturnReceive['PurchaseReturnReceive']['purchase_return_id'] = $purchaseReturnId;
                        $billReturnReceive['PurchaseReturnReceive']['purchase_return_detail_id'] = $purchaseReturnDetailId;
                        $billReturnReceive['PurchaseReturnReceive']['product_id']    = $_POST['product_id'][$i];
                        $billReturnReceive['PurchaseReturnReceive']['qty']           = $_POST['qty'][$i];
                        $billReturnReceive['PurchaseReturnReceive']['qty_uom_id']    = $_POST['qty_uom_id'][$i];
                        $billReturnReceive['PurchaseReturnReceive']['conversion']    = $_POST['conversion'][$i];
                        $billReturnReceive['PurchaseReturnReceive']['lots_number']   = $data['lots_number'];
                        $this->PurchaseReturnReceive->save($billReturnReceive);

                        // Inventory Valuation
                        $inv_valutaion = array();
                        $this->InventoryValuation->create();
                        $inv_valutaion['InventoryValuation']['sys_code']   = md5(rand().strtotime(date("Y-m-d H:i:s")).$user['User']['id']);
                        $inv_valutaion['InventoryValuation']['transaction_detail_id'] = $tranDetailId;
                        $inv_valutaion['InventoryValuation']['purchase_return_id'] = $purchaseReturnId;
                        $inv_valutaion['InventoryValuation']['company_id'] = $this->data['PurchaseReturn']['company_id'];
                        $inv_valutaion['InventoryValuation']['branch_id']  = $this->data['PurchaseReturn']['branch_id'];
                        $inv_valutaion['InventoryValuation']['type']       = "Purchase Return";
                        $inv_valutaion['InventoryValuation']['date']       = $purchaseReturn['PurchaseReturn']['order_date'];
                        $inv_valutaion['InventoryValuation']['created']    = $dateNow;
                        $inv_valutaion['InventoryValuation']['pid']        = $_POST['product_id'][$i];
                        $inv_valutaion['InventoryValuation']['small_qty']  = "-" . $qtyOrderSmall;
                        $inv_valutaion['InventoryValuation']['qty']   = "-" . $this->Helper->replaceThousand(number_format($qtyOrder, 6));
                        $inv_valutaion['InventoryValuation']['cost']  = null;
                        $inv_valutaion['InventoryValuation']['price'] = $_POST['unit_price'][$i] * ($_POST['small_val_uom'][$i] / $_POST['conversion'][$i]);
                        $inv_valutaion['InventoryValuation']['is_var_cost'] = 1;
                        $this->InventoryValuation->saveAll($inv_valutaion);
                        $inv_valutation_id = $this->InventoryValuation->getLastInsertId();
                        
                        // Update GL for Inventory
                        $this->GeneralLedgerDetail->create();
                        $queryInvAccount = mysql_query("SELECT IFNULL((IFNULL((SELECT chart_account_id FROM accounts WHERE product_id = " . $_POST['product_id'][$i] . " AND account_type_id=1),(SELECT chart_account_id FROM pgroup_accounts WHERE pgroup_id = (SELECT pgroup_id FROM product_pgroups WHERE product_id = " . $_POST['product_id'][$i] . " ORDER BY id  DESC LIMIT 1) AND account_type_id=1))),(SELECT chart_account_id FROM account_types WHERE id=1))");
                        $dataInvAccount = mysql_fetch_array($queryInvAccount);
                        $generalLedgerDetail['GeneralLedgerDetail']['chart_account_id'] = $dataInvAccount[0];
                        $generalLedgerDetail['GeneralLedgerDetail']['service_id'] = NULL;
                        $generalLedgerDetail['GeneralLedgerDetail']['product_id'] = $_POST['product_id'][$i];
                        $generalLedgerDetail['GeneralLedgerDetail']['inventory_valuation_id'] = $inv_valutation_id;
                        $generalLedgerDetail['GeneralLedgerDetail']['inventory_valuation_is_debit'] = 0;
                        $generalLedgerDetail['GeneralLedgerDetail']['debit']  = 0;
                        $generalLedgerDetail['GeneralLedgerDetail']['credit'] = $_POST['total_price'][$i];
                        $generalLedgerDetail['GeneralLedgerDetail']['memo']   = 'ICS: Inventory Purchase Return # ' . $modCode . ' Product # ' . $_POST['product'][$i];
                        $this->GeneralLedgerDetail->save($generalLedgerDetail);
                        $tranDetailAcct++;

                        // Update GL COGS
                        $this->GeneralLedgerDetail->create();
                        $queryCogsAccount = mysql_query("SELECT IFNULL((IFNULL((SELECT chart_account_id FROM accounts WHERE product_id = " . $_POST['product_id'][$i] . " AND account_type_id=2),(SELECT chart_account_id FROM pgroup_accounts WHERE pgroup_id = (SELECT pgroup_id FROM product_pgroups WHERE product_id = " . $_POST['product_id'][$i] . " ORDER BY id  DESC LIMIT 1) AND account_type_id=2))),(SELECT chart_account_id FROM account_types WHERE id=2))");
                        $dataCogsAccount  = mysql_fetch_array($queryCogsAccount);
                        $generalLedgerDetail['GeneralLedgerDetail']['chart_account_id'] = $dataCogsAccount[0];
                        $generalLedgerDetail['GeneralLedgerDetail']['inventory_valuation_id'] = $inv_valutation_id;
                        $generalLedgerDetail['GeneralLedgerDetail']['inventory_valuation_is_debit'] = 1;
                        $generalLedgerDetail['GeneralLedgerDetail']['debit']  = 0;
                        $generalLedgerDetail['GeneralLedgerDetail']['credit'] = 0;
                        $generalLedgerDetail['GeneralLedgerDetail']['memo']   = 'ICS: COGS Purchase Return # ' . $modCode . ' Product # ' . $_POST['product'][$i];
                        $this->GeneralLedgerDetail->save($generalLedgerDetail);
                        $tranDetailAcct++;
                        // Update Transaction Detail
                        mysql_query("UPDATE transaction_details SET save_acct = ".$tranDetailAcct." WHERE id = ".$tranDetailId);
                    } else if (!empty($_POST['service_id'][$i])) {
                        $tranDetailAcct = 0;
                        // Sales Order Service
                        $purchaseReturnService = array();
                        $this->PurchaseReturnService->create();
                        $purchaseReturnService['PurchaseReturnService']['purchase_return_id'] = $purchaseReturnId;
                        $purchaseReturnService['PurchaseReturnService']['service_id']  = $_POST['service_id'][$i];
                        $purchaseReturnService['PurchaseReturnService']['qty']         = $_POST['qty'][$i];
                        $purchaseReturnService['PurchaseReturnService']['unit_price']  = $_POST['unit_price'][$i];
                        $purchaseReturnService['PurchaseReturnService']['total_price'] = $_POST['total_price'][$i];
                        $purchaseReturnService['PurchaseReturnService']['note']        = $_POST['note'][$i];
                        $this->PurchaseReturnService->save($purchaseReturnService);
                        $PurchaseReturnServiceId = $this->PurchaseReturnService->id;
                        $transactionSer++;
                        // General Ledger Detail (Service)
                        $this->GeneralLedgerDetail->create();
                        $queryServiceAccount = mysql_query("SELECT IFNULL((SELECT chart_account_id FROM services WHERE id=" . $_POST['service_id'][$i] . "),(SELECT chart_account_id FROM account_types WHERE id=9))");
                        $dataServiceAccount = mysql_fetch_array($queryServiceAccount);
                        $generalLedgerDetail['GeneralLedgerDetail']['chart_account_id'] = $dataServiceAccount[0];
                        $generalLedgerDetail['GeneralLedgerDetail']['service_id'] = $_POST['service_id'][$i];
                        $generalLedgerDetail['GeneralLedgerDetail']['product_id'] = NULL;
                        $generalLedgerDetail['GeneralLedgerDetail']['inventory_valuation_id'] = NULL;
                        $generalLedgerDetail['GeneralLedgerDetail']['inventory_valuation_is_debit'] = NULL;
                        $generalLedgerDetail['GeneralLedgerDetail']['debit']  = 0;
                        $generalLedgerDetail['GeneralLedgerDetail']['credit'] = $_POST['total_price'][$i];
                        $generalLedgerDetail['GeneralLedgerDetail']['memo']   = 'ICS: Purchase Return  # ' . $modCode . ' Service # ' . $_POST['product'][$i];
                        $this->GeneralLedgerDetail->save($generalLedgerDetail);
                        $tranDetailAcct++;
                        // Transaction Detail
                        $tranDetail = array();
                        $this->TransactionDetail->create();
                        $tranDetail['TransactionDetail']['transaction_id']  = $transactionId;
                        $tranDetail['TransactionDetail']['type']       = 2;
                        $tranDetail['TransactionDetail']['module_id']  = $PurchaseReturnServiceId;
                        $tranDetail['TransactionDetail']['save_acct']  = $tranDetailAcct;
                        $this->TransactionDetail->save($tranDetail);
                    }
                }
                // Update Transaction Save
                mysql_query("UPDATE transactions SET save_acct = ".$transactionAcct.", products=".$transactionPro.", service=".$transactionSer." WHERE id = ".$transactionId);
                // Recalculate Average Cost
                mysql_query("UPDATE tracks SET val='".$this->data['PurchaseReturn']['order_date']."', is_recalculate = 1 WHERE id=1");
                $this->Helper->saveUserActivity($user['User']['id'], 'Purchase Return', 'Save Add New', $purchaseReturnId);
                $result['id']    = $purchaseReturnId;
                $result['error'] = 0;
                echo json_encode($result);
                exit;
            } else{
                $this->Helper->saveUserActivity($user['User']['id'], 'Purchase Return', 'Save Add New (Error)');
                $result['error'] = 1;
                echo json_encode($result);
                exit;
            }
        }
        $this->Helper->saveUserActivity($user['User']['id'], 'Purchase Return', 'Add New');
        $companies = ClassRegistry::init('Company')->find('all', array('joins' => array(array('table' => 'user_companies', 'type' => 'inner', 'conditions' => array('user_companies.company_id=Company.id'))), 'fields' => array('Company.id', 'Company.name', 'Company.vat_calculate'), 'conditions' => array('Company.is_active = 1', 'user_companies.user_id=' . $user['User']['id'])));
        $branches  = ClassRegistry::init('Branch')->find('all', array('joins' => array(array('table' => 'user_branches', 'type' => 'inner', 'conditions' => array('user_branches.branch_id=Branch.id')), array('table' => 'module_code_branches AS ModuleCodeBranch', 'type' => 'left', 'conditions' => array('ModuleCodeBranch.branch_id=Branch.id'))), 'fields' => array('Branch.id', 'Branch.name', 'Branch.company_id', 'ModuleCodeBranch.br_code', 'Branch.currency_id', 'Currency.symbol'), 'conditions' => array('Branch.is_active = 1', 'user_branches.user_id=' . $user['User']['id'])));
        $locationGroups = ClassRegistry::init('LocationGroup')->find('list', array('joins' => array(array('table' => 'user_location_groups', 'type' => 'inner', 'conditions' => array('user_location_groups.location_group_id=LocationGroup.id'))),'conditions' => array('user_location_groups.user_id=' . $user['User']['id'], 'LocationGroup.is_active' => '1', 'LocationGroup.location_group_type_id != 1')));
        $locations = ClassRegistry::init('Location')->find('all', array('joins' => array(array('table' => 'user_locations', 'type' => 'inner', 'conditions' => array('user_locations.location_id=Location.id'))), 'conditions' => array('user_locations.user_id=' . $user['User']['id'] . ' AND Location.is_active=1'), 'order' => 'Location.name'));
        $this->set(compact("locationGroups", "locations", "apAccountId", "companies", "branches"));
    }

    function orderDetails() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        $branches = ClassRegistry::init('Branch')->find('all', array('joins' => array(array('table' => 'user_branches', 'type' => 'inner', 'conditions' => array('user_branches.branch_id=Branch.id')), array('table' => 'module_code_branches AS ModuleCodeBranch', 'type' => 'left', 'conditions' => array('ModuleCodeBranch.branch_id=Branch.id'))), 'fields' => array('Branch.id', 'Branch.name', 'Branch.company_id', 'ModuleCodeBranch.br_code', 'Branch.currency_id', 'Currency.symbol'), 'conditions' => array('Branch.is_active = 1', 'user_branches.user_id=' . $user['User']['id'])));
        $uoms = ClassRegistry::init('Uom')->find('all', array('fields' => array('Uom.id', 'Uom.name'), 'conditions' => array('Uom.is_active' => 1)));
        $this->set(compact('branches', 'uoms'));
    }

    function miscellaneous() {
        $this->layout = 'ajax';
    }

    function discount() {
        $this->layout = 'ajax';
        $discounts = ClassRegistry::init('Discount')->find("all", array('conditions' => array('Discount.is_active' => 1), 'order' => array('id DESC')));
        $this->set(compact('discounts'));
    }

    function product($companyId, $branchId = null, $locationId = null, $brId = null) {
        $this->layout = 'ajax';
        $this->set('orderDate', $_POST['order_date']);
        $this->set(compact('companyId', 'branchId', 'locationId', 'orderDate', 'brId'));
    }

    function product_ajax($companyId, $branchId = null, $locationId = null, $orderDate = null, $category = null) {
        $this->layout = 'ajax';
        $this->set('brId', $_GET['br_id']);
        $this->set(compact('companyId', 'branchId', 'locationId', 'orderDate', 'category'));
    }

    function aging($id = null) {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        if (!empty($this->data)) {
            $dateNow  = date("Y-m-d H:i:s");
            $cashBankAccount = ClassRegistry::init('AccountType')->findById(6);
            $cashBankAccountId = $cashBankAccount['AccountType']['chart_account_id'];
            $result = array();
            $purchaseR = array();
            $purchaseR['PurchaseReturn']['id'] = $this->data['PurchaseReturn']['id'];
            $purchaseR['PurchaseReturn']['modified']    = $dateNow;
            $purchaseR['PurchaseReturn']['modified_by'] = $user['User']['id'];
            $purchaseR['PurchaseReturn']['balance'] = $this->data['PurchaseReturn']['balance_us'];
            if ($this->PurchaseReturn->save($purchaseR)) {
                $purchaseReturn  = $this->PurchaseReturn->findById($this->data['PurchaseReturn']['id']);
                $lastExchangeRate = ClassRegistry::init('ExchangeRate')->find("first", array("conditions" => array(
                                "ExchangeRate.branch_id" => $purchaseReturn['PurchaseReturn']['branch_id'],
                                "ExchangeRate.currency_id" => $this->data['PurchaseReturn']['currency_id']), "order" => array("ExchangeRate.created desc")));
                if(!empty($lastExchangeRate) && $lastExchangeRate['ExchangeRate']['rate_to_sell'] > 0){
                    $exchangeRateId = $lastExchangeRate['ExchangeRate']['id'];
                    $totalPaidOther = ($this->data['PurchaseReturn']['amount_other'] / $lastExchangeRate['ExchangeRate']['rate_to_sell']);
                } else {
                    $exchangeRateId = 0;
                    $totalPaidOther = 0;
                }
                $totalPaid  = $this->data['PurchaseReturn']['amount_us'] + $totalPaidOther;
                if($totalPaid <= $purchaseReturn['PurchaseReturn']['balance']){
                    // Load Model
                    $this->loadModel('PurchaseReturnReceipt');
                    $this->loadModel('GeneralLedger');
                    $this->loadModel('GeneralLedgerDetail');
                    $this->loadModel('Company');
                    $this->loadModel('Transaction');
                    $transactionAcct = 0;
                    // Purchase Return Receipt
                    $purchaseReturnReceipt = array();
                    $this->PurchaseReturnReceipt->create();
                    $purchaseReturnReceipt['PurchaseReturnReceipt']['sys_code'] = md5(rand().strtotime(date("Y-m-d H:i:s")).$user['User']['id']);
                    $purchaseReturnReceipt['PurchaseReturnReceipt']['purchase_return_id'] = $this->data['PurchaseReturn']['id'];
                    $purchaseReturnReceipt['PurchaseReturnReceipt']['branch_id']          = $purchaseReturn['PurchaseReturn']['branch_id'];
                    $purchaseReturnReceipt['PurchaseReturnReceipt']['exchange_rate_id']   = $exchangeRateId;
                    $purchaseReturnReceipt['PurchaseReturnReceipt']['currency_id'] = $this->data['PurchaseReturn']['currency_id'];
                    $purchaseReturnReceipt['PurchaseReturnReceipt']['chart_account_id']   = $cashBankAccountId;
                    $purchaseReturnReceipt['PurchaseReturnReceipt']['receipt_code']  = '';
                    $purchaseReturnReceipt['PurchaseReturnReceipt']['amount_us']     = $this->data['PurchaseReturn']['amount_us'];
                    $purchaseReturnReceipt['PurchaseReturnReceipt']['amount_other']  = $this->data['PurchaseReturn']['amount_other'];
                    $purchaseReturnReceipt['PurchaseReturnReceipt']['total_amount']  = $this->data['PurchaseReturn']['total_amount'];
                    $purchaseReturnReceipt['PurchaseReturnReceipt']['balance']       = $this->data['PurchaseReturn']['balance_us'];
                    $purchaseReturnReceipt['PurchaseReturnReceipt']['balance_other'] = $this->data['PurchaseReturn']['balance_other'];
                    $purchaseReturnReceipt['PurchaseReturnReceipt']['created_by']    = $user['User']['id'];
                    $purchaseReturnReceipt['PurchaseReturnReceipt']['pay_date']      = $this->data['PurchaseReturn']['pay_date']!=''?$this->data['PurchaseReturn']['pay_date']:'0000-00-00';
                    if ($this->data['PurchaseReturn']['balance_us'] > 0) {
                        $purchaseReturnReceipt['PurchaseReturnReceipt']['due_date']  = $this->data['PurchaseReturn']['aging']!=''?$this->data['PurchaseReturn']['aging']:'0000-00-00';
                    }
                    $this->PurchaseReturnReceipt->save($purchaseReturnReceipt);
                    $result['sr_id'] = $this->PurchaseReturnReceipt->id;
                    // Update Code & Change SO Generate Code
                    $modComCode = ClassRegistry::init('ModuleCodeBranch')->find('first', array('conditions' => array("ModuleCodeBranch.branch_id" => $purchaseReturn['PurchaseReturn']['branch_id'])));
                    $repCode    = date("y").$modComCode['ModuleCodeBranch']['br_rep_code'];
                    // Get Module Code
                    $modCode    = $this->Helper->getModuleCode($repCode, $result['sr_id'], 'receipt_code', 'purchase_return_receipts', 'is_void = 0 AND branch_id = '.$purchaseReturn['PurchaseReturn']['branch_id']);
                    // Updaet Module Code
                    mysql_query("UPDATE purchase_return_receipts SET receipt_code = '".$modCode."' WHERE id = ".$result['sr_id']);
                    
                    // Save General Ledger Detail
                    $this->GeneralLedger->create();
                    $generalLedger = array();
                    $generalLedger['GeneralLedger']['sys_code']           = md5(rand().strtotime(date("Y-m-d H:i:s")).$user['User']['id']);
                    $generalLedger['GeneralLedger']['purchase_return_id'] = $this->data['PurchaseReturn']['id'];
                    $generalLedger['GeneralLedger']['purchase_return_receipt_id'] = $result['sr_id'];
                    $generalLedger['GeneralLedger']['date']       = $this->data['PurchaseReturn']['pay_date']!=''?$this->data['PurchaseReturn']['pay_date']:'0000-00-00';
                    $generalLedger['GeneralLedger']['reference']  = $purchaseReturn['PurchaseReturn']['pr_code'];
                    $generalLedger['GeneralLedger']['created_by'] = $user['User']['id'];
                    $generalLedger['GeneralLedger']['is_sys']    = 1;
                    $generalLedger['GeneralLedger']['is_adj']    = 0;
                    $generalLedger['GeneralLedger']['is_active'] = 1;
                    if ($this->GeneralLedger->save($generalLedger)) {
                        $company  = $this->Company->read(null, $purchaseReturn['PurchaseReturn']['company_id']);
                        $classId  = $this->Helper->getClassId($company['Company']['id'], $company['Company']['classes'], $purchaseReturn['PurchaseReturn']['location_group_id']);
                        $this->GeneralLedgerDetail->create();
                        $generalLedgerDetail = array();
                        $generalLedgerDetail['GeneralLedgerDetail']['general_ledger_id'] = $this->GeneralLedger->id;
                        $generalLedgerDetail['GeneralLedgerDetail']['company_id'] = $purchaseReturn['PurchaseReturn']['company_id'];
                        $generalLedgerDetail['GeneralLedgerDetail']['branch_id']  = $purchaseReturn['PurchaseReturn']['branch_id'];
                        $generalLedgerDetail['GeneralLedgerDetail']['chart_account_id'] = $cashBankAccountId;
                        $generalLedgerDetail['GeneralLedgerDetail']['location_group_id'] = $purchaseReturn['PurchaseReturn']['location_group_id'];
                        $generalLedgerDetail['GeneralLedgerDetail']['location_id'] = $purchaseReturn['PurchaseReturn']['location_id'];
                        $generalLedgerDetail['GeneralLedgerDetail']['type'] = 'Purchase Return Payment';
                        $generalLedgerDetail['GeneralLedgerDetail']['debit'] = $totalPaid;
                        $generalLedgerDetail['GeneralLedgerDetail']['credit'] = 0;
                        $generalLedgerDetail['GeneralLedgerDetail']['memo'] = 'ICS: Purchase Return # ' . $purchaseReturn['PurchaseReturn']['pr_code'];
                        $generalLedgerDetail['GeneralLedgerDetail']['vendor_id'] = $purchaseReturn['PurchaseReturn']['vendor_id'];
                        $generalLedgerDetail['GeneralLedgerDetail']['class_id'] = $classId;
                        $this->GeneralLedgerDetail->save($generalLedgerDetail);
                        $transactionAcct++;

                        $this->GeneralLedgerDetail->create();
                        $generalLedgerDetail['GeneralLedgerDetail']['chart_account_id'] = $purchaseReturn['PurchaseReturn']['ap_id'];
                        $generalLedgerDetail['GeneralLedgerDetail']['debit'] = 0;
                        $generalLedgerDetail['GeneralLedgerDetail']['credit'] = $totalPaid;
                        $this->GeneralLedgerDetail->save($generalLedgerDetail);
                        $transactionAcct++;
                    }
                    // Transaction
                    $transaction = array();
                    $this->Transaction->create();
                    $transaction['Transaction']['module_id']  = $result['sr_id'];
                    $transaction['Transaction']['type']       = 'Purchase Return Receipt';
                    $transaction['Transaction']['save_acct']  = $transactionAcct;
                    $transaction['Transaction']['created']    = $dateNow;
                    $transaction['Transaction']['created_by'] = $user['User']['id'];
                    $this->Transaction->save($transaction);
                    $this->Helper->saveUserActivity($user['User']['id'], 'Purchase Return Payment', 'Save Add New', $result['sr_id']);
                    echo json_encode($result);
                    exit;
                } else {
                    $this->Helper->saveUserActivity($user['User']['id'], 'Purchase Return Payment', 'Save Add New (Error)');
                    $result['sr_id'] = 0;
                    echo json_encode($result);
                    exit;
                }
            } else {
                $this->Helper->saveUserActivity($user['User']['id'], 'Purchase Return Payment', 'Save Add New (Error)');
                $result['sr_id'] = 0;
                echo json_encode($result);
                exit;
            }
        }
        if (!empty($id)) {
            $this->data = $this->PurchaseReturn->read(null, $id);
            $purchaseReturn = ClassRegistry::init('PurchaseReturn')->find("first", array('conditions' => array('PurchaseReturn.id' => $id)));
            if (!empty($purchaseReturn)) {
                $this->Helper->saveUserActivity($user['User']['id'], 'Purchase Return Payment', 'Add New');
                $pbcWPos = ClassRegistry::init('InvoicePbcWithPb')->find("all", array('conditions' => array('InvoicePbcWithPb.purchase_return_id' => $id, 'InvoicePbcWithPb.status>0')));
                $purchaseReturnDetails  = ClassRegistry::init('PurchaseReturnDetail')->find("all", array('conditions' => array('PurchaseReturnDetail.purchase_return_id' => $id)));
                $purchaseReturnServices = ClassRegistry::init('PurchaseReturnService')->find("all", array('conditions' => array('PurchaseReturnService.purchase_return_id' => $id)));
                $purchaseReturnReceipts = ClassRegistry::init('PurchaseReturnReceipt')->find("all", array('conditions' => array('PurchaseReturnReceipt.purchase_return_id' => $id, 'PurchaseReturnReceipt.is_void' => 0)));
                $this->set(compact('purchaseReturn', 'purchaseReturnDetails', 'purchaseReturnServices', 'purchaseReturnReceipts', 'pbcWPos'));
            } else {
                exit;
            }
        } else {
            exit;
        }
    }

    function printInvoice($id = null) {
        if (!empty($id)) {
            $this->layout = 'ajax';
            $purchaseReturn = ClassRegistry::init('PurchaseReturn')->find("first", array('conditions' => array('PurchaseReturn.id' => $id)));
            if (!empty($purchaseReturn)) {
                $pbcPbs = ClassRegistry::init('InvoicePbcWithPb')->find("all", array('conditions' => array('InvoicePbcWithPb.purchase_return_id' => $id, 'InvoicePbcWithPb.status>0'),
                    'group' => 'InvoicePbcWithPb.purchase_order_id',
                    'fields' => array('sum(InvoicePbcWithPb.total_cost) as total_cost', 'InvoicePbcWithPb.*', 'PurchaseOrder.*')
                ));
                $purchaseReturnDetails = ClassRegistry::init('PurchaseReturnDetail')->find("all", array('conditions' => array('PurchaseReturnDetail.purchase_return_id' => $id)));
                $purchaseReturnMiscs = ClassRegistry::init('PurchaseReturnMisc')->find("all", array('conditions' => array('PurchaseReturnMisc.purchase_return_id' => $id)));
                $purchaseReturnServices = ClassRegistry::init('PurchaseReturnService')->find("all", array('conditions' => array('PurchaseReturnService.purchase_return_id' => $id)));

                $location = ClassRegistry::init('Location')->find("first", array("conditions" => array("Location.id" => $purchaseReturn['PurchaseReturn']['location_id'], "Location.is_active" => "1")));
                $this->set(compact('purchaseReturn', 'purchaseReturnDetails', 'purchaseReturnMiscs', 'purchaseReturnServices', "location", "pbcPbs"));
            } else {
                exit;
            }
        } else {
            exit;
        }
    }

    function printReceipt($receiptId = null) {
        if (!empty($receiptId)) {
            $this->layout = 'ajax';
            $sr = ClassRegistry::init('PurchaseReturnReceipt')->find("first", array('conditions' => array('PurchaseReturnReceipt.id' => $receiptId, 'PurchaseReturnReceipt.is_void' => 0)));

            $purchaseReturn = ClassRegistry::init('PurchaseReturn')->find("first", array('conditions' => array('PurchaseReturn.id' => $sr['PurchaseReturn']['id'])));
            if (!empty($purchaseReturn)) {
                $lastExchangeRate = ClassRegistry::init('ExchangeRate')->find("first", array(
                    "conditions" => array("ExchangeRate.is_active" => 1),
                    "order" => array("ExchangeRate.created desc")
                        )
                );
                $location = ClassRegistry::init('Location')->find("first", array("conditions" => array("Location.id" => $purchaseReturn['PurchaseReturn']['location_id'], "Location.is_active" => "1")));
                $purchaseReturnDetails = ClassRegistry::init('PurchaseReturnDetail')->find("all", array('conditions' => array('PurchaseReturnDetail.purchase_return_id' => $sr['PurchaseReturn']['id'])));
                $purchaseReturnServices = ClassRegistry::init('PurchaseReturnService')->find("all", array('conditions' => array('PurchaseReturnService.purchase_return_id' => $sr['PurchaseReturn']['id'])));
                $purchaseReturnMiscs = ClassRegistry::init('PurchaseReturnMisc')->find("all", array('conditions' => array('PurchaseReturnMisc.purchase_return_id' => $sr['PurchaseReturn']['id'])));
                $purchaseReturnReceipts = ClassRegistry::init('PurchaseReturnReceipt')->find("all", array('conditions' => array('PurchaseReturnReceipt.purchase_return_id' => $sr['PurchaseReturn']['id'], 'PurchaseReturnReceipt.is_void' => 0)));

                $this->set(compact('purchaseReturn', 'purchaseReturnDetails', 'purchaseReturnMiscs', 'purchaseReturnServices', 'purchaseReturnReceipts', 'sr', 'lastExchangeRate', 'location'));
            } else {
                exit;
            }
        } else {
            exit;
        }
    }

    function printReceiptCurrent($receiptId = null) {
        if (!empty($receiptId)) {
            $this->layout = 'ajax';
            $sr = ClassRegistry::init('PurchaseReturnReceipt')->find("first", array('conditions' => array('PurchaseReturnReceipt.id' => $receiptId, 'PurchaseReturnReceipt.is_void' => 0)));

            $purchaseReturn = ClassRegistry::init('PurchaseReturn')->find("first", array('conditions' => array('PurchaseReturn.id' => $sr['PurchaseReturn']['id'])));
            if (!empty($purchaseReturn)) {
                $lastExchangeRate = ClassRegistry::init('ExchangeRate')->find("first", array(
                    "conditions" => array("ExchangeRate.is_active" => 1),
                    "order" => array("ExchangeRate.created desc")
                        )
                );
                $location = ClassRegistry::init('Location')->find("first", array("conditions" => array("Location.id" => $purchaseReturn['PurchaseReturn']['location_id'], "Location.is_active" => "1")));
                $purchaseReturnDetails = ClassRegistry::init('PurchaseReturnDetail')->find("all", array('conditions' => array('PurchaseReturnDetail.purchase_return_id' => $sr['PurchaseReturn']['id'])));
                $purchaseReturnServices = ClassRegistry::init('PurchaseReturnService')->find("all", array('conditions' => array('PurchaseReturnService.purchase_return_id' => $sr['PurchaseReturn']['id'])));
                $purchaseReturnMiscs = ClassRegistry::init('PurchaseReturnMisc')->find("all", array('conditions' => array('PurchaseReturnMisc.purchase_return_id' => $sr['PurchaseReturn']['id'])));
                $purchaseReturnReceipts = ClassRegistry::init('PurchaseReturnReceipt')->find("all", array('conditions' => array('PurchaseReturnReceipt.id <= ' . $receiptId, 'PurchaseReturnReceipt.purchase_return_id' => $sr['PurchaseReturn']['id'], 'PurchaseReturnReceipt.is_void' => 0)));

                $this->set(compact('purchaseReturn', 'purchaseReturnDetails', 'purchaseReturnMiscs', 'purchaseReturnServices', 'purchaseReturnReceipts', 'sr', 'lastExchangeRate', 'location'));
            } else {
                exit;
            }
        } else {
            exit;
        }
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

    function invoice($companyId, $branchId, $chartAccountId, $vendorId, $balance, $prId) {
        $this->layout = 'ajax';
        $this->set('companyId', $companyId);
        $this->set('branchId', $branchId);
        $this->set('chartAccountId', $chartAccountId);
        $this->set('vendorId', $vendorId);
        $this->set('balance', $this->Helper->replaceThousand($balance));
        $this->data = $this->PurchaseReturn->read(null, $prId);
    }

    function invoiceAjax($companyId, $branchId, $chartAccountId, $vendorId, $cg = null) {
        $this->layout = 'ajax';
        $this->set('companyId', $companyId);
        $this->set('branchId', $branchId);
        $this->set('chartAccountId', $chartAccountId);
        $this->set('vendorId', $vendorId);
        $this->set('cg', $cg);
    }

    function applyToInvoice($prId, $prBalance, $date) {
        $this->layout = 'ajax';
        $this->loadModel('InvoicePbcWithPb');
        $this->loadModel('GeneralLedger');
        $this->loadModel('GeneralLedgerDetail');
        $user = $this->getCurrentUser();
        $purchase_return = $this->PurchaseReturn->read(null, $prId);
        $PurchaseReturn = array();
        $PurchaseReturn['PurchaseReturn']['id'] = $prId;
        $total_amount_invoice = 0;
        if (!empty($_POST['purchase_order'])) {
            $dateNow  = date("Y-m-d H:i:s");
            for ($i = 0; $i < sizeOf($_POST['purchase_order']); $i++) {
                if ($_POST['purchase_order'][$i] != "" && $_POST['purchase_order'][$i] > 0) {                    
                    $prWPo = array();
                    $saleBalance = 0;
                    $queryInvoice = mysql_query("SELECT balance, sys_code, po_code, ap_id FROM purchase_orders WHERE id=" . $_POST['purchase_order'][$i]);
                    $dataInvoice = mysql_fetch_array($queryInvoice);
                    if ($prBalance > 0) {
                        $saleBalance = $dataInvoice['balance'] - $_POST['invoice_price_pbc'][$i];
                        mysql_query("UPDATE purchase_orders SET balance = " . $saleBalance . " WHERE id=" . $_POST['purchase_order'][$i]);
                        $this->InvoicePbcWithPb->create();
                        $prWPo['InvoicePbcWithPb']['sys_code']   = md5(rand().strtotime(date("Y-m-d H:i:s")).$user['User']['id']);
                        $prWPo['InvoicePbcWithPb']['purchase_return_id'] = $prId;
                        $prWPo['InvoicePbcWithPb']['purchase_order_id'] = $_POST['purchase_order'][$i];
                        $prWPo['InvoicePbcWithPb']['total_cost'] = $_POST['invoice_price_pbc'][$i];
                        $prWPo['InvoicePbcWithPb']['apply_date'] = $date;
                        $prWPo['InvoicePbcWithPb']['created']    = $dateNow;
                        $prWPo['InvoicePbcWithPb']['created_by'] = $user['User']['id'];
                        $prWPo['InvoicePbcWithPb']['status'] = 1;
                        $this->InvoicePbcWithPb->save($prWPo);
                        $brPbId = $this->InvoicePbcWithPb->id;
                        // Save General Ledger Detail
                        $this->GeneralLedger->create();
                        $generalLedger = array();
                        $generalLedger['GeneralLedger']['sys_code']  = md5(rand().strtotime(date("Y-m-d H:i:s")).$user['User']['id']);
                        $generalLedger['GeneralLedger']['invoice_pbc_with_pbs_id'] = $brPbId;
                        $generalLedger['GeneralLedger']['purchase_return_id'] = $prId;
                        $generalLedger['GeneralLedger']['purchase_order_id']  = NULL;
                        $generalLedger['GeneralLedger']['date']       = $date;
                        $generalLedger['GeneralLedger']['reference']  = $purchase_return['PurchaseReturn']['pr_code'];
                        $generalLedger['GeneralLedger']['created_by'] = $user['User']['id'];
                        $generalLedger['GeneralLedger']['is_sys'] = 1;
                        $generalLedger['GeneralLedger']['is_adj'] = 0;
                        $generalLedger['GeneralLedger']['is_active'] = 1;
                        if ($this->GeneralLedger->save($generalLedger)) {
                            $glId = $this->GeneralLedger->id;
                            $this->GeneralLedgerDetail->create();
                            $generalLedgerDetail = array();
                            $generalLedgerDetail['GeneralLedgerDetail']['general_ledger_id'] = $glId;
                            $generalLedgerDetail['GeneralLedgerDetail']['chart_account_id']  = $purchase_return['PurchaseReturn']['ap_id'];
                            $generalLedgerDetail['GeneralLedgerDetail']['company_id']        = $purchase_return['PurchaseReturn']['company_id'];
                            $generalLedgerDetail['GeneralLedgerDetail']['branch_id']         = $purchase_return['PurchaseReturn']['branch_id'];
                            $generalLedgerDetail['GeneralLedgerDetail']['type']   = 'Apply Purchase Bill';
                            $generalLedgerDetail['GeneralLedgerDetail']['debit']  = 0;
                            $generalLedgerDetail['GeneralLedgerDetail']['credit'] = $_POST['invoice_price_pbc'][$i];
                            $generalLedgerDetail['GeneralLedgerDetail']['memo']   = 'ICS: Apply PR # '.$purchase_return['PurchaseReturn']['pr_code'].' and PB # ' . $dataInvoice['po_code'];
                            $generalLedgerDetail['GeneralLedgerDetail']['vendor_id'] = $purchase_return['PurchaseReturn']['vendor_id'];
                            $this->GeneralLedgerDetail->save($generalLedgerDetail);
                        }
                        // Save General Ledger Detail
                        $this->GeneralLedger->create();
                        $generalLedger['GeneralLedger']['sys_code']  = md5(rand().strtotime(date("Y-m-d H:i:s")).$user['User']['id']);
                        $generalLedger['GeneralLedger']['purchase_order_id']  = $_POST['purchase_order'][$i];
                        $generalLedger['GeneralLedger']['purchase_return_id'] = NULL;
                        $generalLedger['GeneralLedger']['reference']  = $dataInvoice['po_code'];
                        if ($this->GeneralLedger->save($generalLedger)) {
                            $glId = $this->GeneralLedger->id;
                            $this->GeneralLedgerDetail->create();
                            $generalLedgerDetail = array();
                            $generalLedgerDetail['GeneralLedgerDetail']['general_ledger_id'] = $glId;
                            $generalLedgerDetail['GeneralLedgerDetail']['chart_account_id']  = $dataInvoice['ap_id'];
                            $generalLedgerDetail['GeneralLedgerDetail']['company_id']        = $purchase_return['PurchaseReturn']['company_id'];
                            $generalLedgerDetail['GeneralLedgerDetail']['branch_id']         = $purchase_return['PurchaseReturn']['branch_id'];
                            $generalLedgerDetail['GeneralLedgerDetail']['type']   = 'Apply Purchase Return';
                            $generalLedgerDetail['GeneralLedgerDetail']['debit']  = $_POST['invoice_price_pbc'][$i];
                            $generalLedgerDetail['GeneralLedgerDetail']['credit'] = 0;
                            $generalLedgerDetail['GeneralLedgerDetail']['memo']   = 'ICS: Apply PR # '.$purchase_return['PurchaseReturn']['pr_code'].' and PB # ' . $dataInvoice['po_code'];
                            $generalLedgerDetail['GeneralLedgerDetail']['vendor_id'] = $purchase_return['PurchaseReturn']['vendor_id'];
                            $this->GeneralLedgerDetail->save($generalLedgerDetail);
                        }
                        $this->Helper->saveUserActivity($user['User']['id'], 'Purchase Return Receipt', 'Save Apply to PB', $this->InvoicePbcWithPb->id);
                        $total_amount_invoice += $_POST['invoice_price_pbc'][$i];
                    }
                }
            }
        }
        $PurchaseReturn['PurchaseReturn']['balance'] = $prBalance - $total_amount_invoice;
        $PurchaseReturn['PurchaseReturn']['total_amount_po'] = ($purchase_return['PurchaseReturn']['total_amount_po'] < 0 ? 0 : $purchase_return['PurchaseReturn']['total_amount_po'] + $total_amount_invoice);
        $PurchaseReturn['PurchaseReturn']['modified']    = $dateNow;
        $PurchaseReturn['PurchaseReturn']['modified_by'] = $user['User']['id'];
        $this->PurchaseReturn->save($PurchaseReturn);
        exit();
    }

    function service($companyId, $branchId) {
        $this->layout = 'ajax';
        $serviceP = array();
        $sqlSG = mysql_query("SELECT section_id FROM services WHERE is_active = 1 GROUP BY section_id;");
        while($rowSG = mysql_fetch_array($sqlSG)){
            $serviceP[] = $rowSG['section_id'];
        }
        $conSection = '';
        if(!empty($serviceP)){
            $conSection = ' AND Pgroup.id IN ('.implode(",",$serviceP).')';
        }
        $sections = ClassRegistry::init('Pgroup')->find("list", array("conditions" => array("Pgroup.is_active = 1".$conSection)));
        $services = $this->serviceCombo($companyId, $branchId);
        $this->set(compact('sections', 'services'));
    }

    function serviceCombo($companyId, $branchId) {
        $array = array();
        $services = ClassRegistry::init('Service')->find("all", array("conditions" => array("Service.company_id=" . $companyId. " AND Service.is_active = 1", "Service.id IN (SELECT service_id FROM service_branches WHERE branch_id = ".$branchId.")")));
        foreach ($services as $service) {
            $uomId = $service['Service']['uom_id']!=''?$service['Service']['uom_id']:'';
            array_push($array, array('value' => $service['Service']['id'], 'name' => $service['Service']['code']." - ".$service['Service']['name'], 'class' => $service['Pgroup']['id'], 'abbr' => $service['Service']['name'], 'price' => $service['Service']['unit_price'], 'scode' => $service['Service']['code'], 'suom' => $uomId));
        }
        return $array;
    }

    function searchProduct() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        $joinProductgroup  = array(
                             'table' => 'product_pgroups',
                             'type' => 'INNER',
                             'alias' => 'ProductPgroup',
                             'conditions' => array('ProductPgroup.product_id = Product.id')
                             );
        $joinProductBranch  = array(
                             'table' => 'product_branches',
                             'type' => 'INNER',
                             'alias' => 'ProductBranch',
                             'conditions' => array(
                                 'ProductBranch.product_id = Product.id',
                                 'ProductBranch.branch_id IN (SELECT branch_id FROM user_branches WHERE user_id = '.$user['User']['id'].')'
                             ));
        $joinPgroup  = array(
                             'table' => 'pgroups',
                             'type' => 'INNER',
                             'alias' => 'Pgroup',
                             'conditions' => array(
                                 'Pgroup.id = ProductPgroup.pgroup_id',
                                 '(Pgroup.user_apply = 0 OR (Pgroup.user_apply = 1 AND Pgroup.id IN (SELECT pgroup_id FROM user_pgroups WHERE user_id = '.$user['User']['id'].')))'
                             )
                          );
        $joins = array(
            $joinProductgroup,
            $joinPgroup,
            $joinProductBranch
        );
        $products = ClassRegistry::init('Product')->find('all', array(
                        'conditions' => array('OR' => array(
                                'Product.code LIKE' => '%' . trim($this->params['url']['q']) . '%',
                                'Product.barcode LIKE' => '%' . trim($this->params['url']['q']) . '%',
                                'Product.name LIKE' => '%' . trim($this->params['url']['q']) . '%',
                            ), 'Product.company_id IN (SELECT company_id FROM user_companies WHERE user_id = '.$user['User']['id'].')'
                            , 'Product.is_active' => 1
                            , 'Product.is_packet' => 0
                            , 'Product.price_uom_id > 0'
                            , 'Product.small_val_uom > 0'
                        ),
                        'joins' => $joins,
                        'group' => array(
                            'Product.id'
                        )
                    ));
        $this->set(compact('products'));
    }

    function searchProductByCode($company_id, $branch_id, $brId = null) {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        $product_code = !empty($this->data['code']) ? $this->data['code'] : "";
        $order_date   = $this->data['order_date'];
        $location_id  = $this->data['location_id'];
        $lotsNumber   = $this->data['lots_number']!=''?$this->data['lots_number']:'0';
        $expiredDate  = $this->data['expired_date']!=''?$this->data['expired_date']:'0000-00-00';
        $dateNow   = date("Y-m-d");
        $tableName = $location_id."_inventory_totals";
        if(strtotime($order_date) < strtotime($dateNow) ){
            $sumEnding = "(StockDaily.total_cycle + StockDaily.total_cm + StockDaily.total_pb + StockDaily.total_to_in) - (StockDaily.total_so + StockDaily.total_pos + StockDaily.total_pbc + StockDaily.total_to_out)";
            $joinStockDaily = array(
                                'table' => $location_id."_inventory_total_details",
                                'type' => 'INNER',
                                'alias' => 'StockDaily',
                                'conditions' => array(
                                    'StockDaily.product_id = Product.id',
                                    "StockDaily.date <= '".$order_date."'",
                                    "StockDaily.lots_number = '".$lotsNumber."'",
                                    "StockDaily.expired_date = '".$expiredDate."'"
                                ));
            $joinInventory  = "";
            $inventoryField = "SUM(IFNULL(".$sumEnding.",0)) AS total_qty";
            $groupBy        = "Product.id";
        }else{
            $joinInventory  = array(
                                 'table' => $tableName,
                                 'type' => 'INNER',
                                 'alias' => 'InventoryTotal',
                                 'conditions' => array(
                                     'InventoryTotal.product_id = Product.id',
                                     "InventoryTotal.lots_number = '".$lotsNumber."'"
                                 ));
            $joinStockDaily = "";
            $inventoryField = "SUM(InventoryTotal.total_qty - InventoryTotal.total_order) AS total_qty";
            $groupBy        = "Product.id";
        }
        $joinProductgroup  = array(
                             'table' => 'product_pgroups',
                             'type' => 'INNER',
                             'alias' => 'ProductPgroup',
                             'conditions' => array('ProductPgroup.product_id = Product.id')
                             );
        $joinProductBranch  = array(
                             'table' => 'product_branches',
                             'type' => 'INNER',
                             'alias' => 'ProductBranch',
                             'conditions' => array(
                                 'ProductBranch.product_id = Product.id',
                                 'ProductBranch.branch_id = '.$branch_id
                             ));
        $joinPgroup  = array(
                             'table' => 'pgroups',
                             'type' => 'INNER',
                             'alias' => 'Pgroup',
                             'conditions' => array(
                                 'Pgroup.id = ProductPgroup.pgroup_id',
                                 '(Pgroup.user_apply = 0 OR (Pgroup.user_apply = 1 AND Pgroup.id IN (SELECT pgroup_id FROM user_pgroups WHERE user_id = '.$user['User']['id'].')))'
                             )
                          );
        
        $joins = array(
            $joinInventory,
            $joinStockDaily,
            $joinProductgroup,
            $joinPgroup,
            $joinProductBranch
        );

        $product = ClassRegistry::init('Product')->find('first', array(
                    'fields' => array(
                        'Product.id',
                        'Product.name',
                        'Product.code',
                        'Product.description',
                        'Product.small_val_uom',
                        'Product.default_cost',
                        'Product.price_uom_id',
                        'Product.unit_cost',
                        'Product.is_lots',
                        'Product.is_expired_date',
                        $inventoryField,
                    ),
                    'conditions' => array(
                        array(
                            "OR" => array(
                                'trim(Product.code)' => trim($product_code),
                                'trim(Product.barcode)' => trim($product_code)
                            )
                        ),
                        'Product.company_id' => $company_id,
                        'Product.is_active' => 1,
                        'Product.is_packet' => 0,
                        'Product.price_uom_id > 0',
                        'Product.small_val_uom > 0'
                    ),
                    'joins' => $joins,
                    'group' => $groupBy
                ));
        $this->set(compact('product', 'order_date', 'location_id', 'brId', 'lotsNumber', 'expiredDate'));
    }

    function edit($id = null) {
        $this->layout = 'ajax';
        if (!$id && empty($this->data)) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }

        $user = $this->getCurrentUser();
        if (!empty($this->data)) {
            $product_return = $this->PurchaseReturn->read(null, $this->data['id']);
            if ($product_return['PurchaseReturn']['status'] == 2) {
                $dateNow   = date("Y-m-d H:i:s");
                $statuEdit = "-1";
                $result    = array();
                $this->loadModel('PurchaseReturnDetail');
                $this->loadModel('PurchaseReturnService');
                $this->loadModel('PurchaseReturnReceipt');
                $this->loadModel('GeneralLedger');
                $this->loadModel('GeneralLedgerDetail');
                $this->loadModel('AccountType');
                $this->loadModel('InventoryValuation');
                $this->loadModel('Company');
                $this->loadModel('PurchaseReturnReceive');
                $this->loadModel('Transaction');
                $this->loadModel('TransactionDetail');
                // Check Save Transaction
                $checkTransaction = true;
                $transactionLogId = 0;
                $sqlCheck = mysql_query("SELECT * FROM transactions WHERE type = 'Purchase Return' AND action = 1 AND module_id = ".$this->data['id']);
                if(mysql_num_rows($sqlCheck)){
                    $rowCheck  = mysql_fetch_array($sqlCheck);
                    $sqlDetail = mysql_query("SELECT * FROM transaction_details WHERE transaction_id = ".$rowCheck['id']);
                    $rowDetail = mysql_num_rows($sqlDetail);
                    $transactionLogId = $rowCheck['id'];
                    if($rowDetail > 0){
                        // Check Total Transaction
                        $totalD = $rowCheck['products'] + $rowCheck['service'];
                        if($totalD != $rowDetail){
                            $checkTransaction = false;
                        } else {
                            $totalAcctD = 0;
                            while($rowD = mysql_fetch_array($sqlDetail)){
                                $totalAcctD += $rowD['save_acct'];
                                if($rowD['type'] == 1){
                                    if($rowD['inventory_valutaion'] != '1'){
                                        $checkTransaction = false;
                                        break;
                                    }
                                    if($product_return['PurchaseReturn']['status'] == 2){
                                        if($rowD['inventory'] != '1' || $rowD['inventory_total'] != '1' || $rowD['loc_inventory'] != '1' || $rowD['loc_inventory_total'] != '1' || $rowD['loc_inventory_detail'] != '1' || $rowD['g_inventory'] != '1' || $rowD['g_inventory_detail'] != '1'){
                                            $checkTransaction = false;
                                            break;
                                        }
                                    }
                                }
                            }
                            if($checkTransaction == true){
                                // Check Account
                                $sqlAcct = mysql_query("SELECT COUNT(id) FROM general_ledger_details WHERE general_ledger_id = (SELECT id FROM general_ledgers WHERE purchase_return_id = ".$this->data['id']." AND purchase_return_receipt_id IS NULL LIMIT 1)");
                                if(mysql_num_rows($sqlAcct)){
                                    $rowAcct = mysql_fetch_array($sqlAcct);
                                    if($rowAcct[0] != ($totalAcctD + $rowCheck['save_acct'])){
                                        $checkTransaction = false;
                                    }
                                } else {
                                    $checkTransaction = false;
                                }
                            }
                        }
                    } else {
                        $checkTransaction = false;
                    }
                }
                if($checkTransaction == true){
                    // Remove Transaction Log
                    mysql_query("DELETE FROM transactions WHERE id = ".$transactionLogId);
                    mysql_query("DELETE FROM transaction_details WHERE transaction_id = ".$transactionLogId);
                    // Update
                    $this->PurchaseReturn->updateAll(
                        array('PurchaseReturn.status' => $statuEdit, 'PurchaseReturn.modified_by' => $user['User']['id']), array('PurchaseReturn.id' => $this->data['id'])
                    );
                    $this->GeneralLedger->updateAll(
                            array('GeneralLedger.is_active' => 2, 'GeneralLedger.modified_by' => $user['User']['id']), array('GeneralLedger.purchase_return_id' => $this->data['id'])
                    );
                    $this->InventoryValuation->updateAll(
                            array('InventoryValuation.is_active' => "2"), array('InventoryValuation.purchase_return_id' => $this->data['id'])
                    );
                    if($product_return['PurchaseReturn']['status'] == 2){
                        $purchaseReturnDetails = ClassRegistry::init('PurchaseReturnDetail')->find("all", array('conditions' => array('PurchaseReturnDetail.purchase_return_id' => $this->data['id'])));
                        foreach($purchaseReturnDetails AS $purchaseReturnDetail){
                            $qtyOrderSmall = ($purchaseReturnDetail['PurchaseReturnDetail']['qty'] * $purchaseReturnDetail['PurchaseReturnDetail']['conversion']);
                            // Update Inventory (Purchase Return)
                            $data = array();
                            $data['module_type']        = 20;
                            $data['purchase_return_id'] = $this->data['id'];
                            $data['product_id']         = $purchaseReturnDetail['PurchaseReturnDetail']['product_id'];
                            $data['location_id']        = $product_return['PurchaseReturn']['location_id'];
                            $data['location_group_id']  = $product_return['PurchaseReturn']['location_group_id'];
                            $data['expired_date'] = $purchaseReturnDetail['PurchaseReturnDetail']['expired_date']!=""?$purchaseReturnDetail['PurchaseReturnDetail']['expired_date']:'0000-00-00';
                            $data['lots_number']  = $purchaseReturnDetail['PurchaseReturnDetail']['lots_number']!=""?$purchaseReturnDetail['PurchaseReturnDetail']['lots_number']:0;
                            $data['date']         = $product_return['PurchaseReturn']['order_date'];
                            $data['total_qty']    = $qtyOrderSmall;
                            $data['total_order']  = $qtyOrderSmall;
                            $data['total_free']   = 0;
                            $data['user_id']      = $user['User']['id'];
                            $data['customer_id']  = "";
                            $data['vendor_id']    = $product_return['PurchaseReturn']['vendor_id'];
                            $data['unit_cost']    = 0;
                            $data['unit_price']   = 0;
                            $data['transaction_id'] = '';
                            // Update Invetory Location
                            $this->Inventory->saveInventory($data);
                            // Update Inventory Group
                            $this->Inventory->saveGroupTotalDetail($data);
                        }
                    }
                } else {
                    $this->Helper->saveUserActivity($user['User']['id'], 'Purchase Return', 'Save Edit (Error Save Transaction)');
                    $result['error'] = 1;
                    echo json_encode($result);
                    exit;
                }

                $this->PurchaseReturn->create();
                $this->GeneralLedger->create();

                //  Find Chart Account
                $apAccount = $this->AccountType->findById(14);
                $purchaseReturn = array();
                $purchaseReturn['PurchaseReturn']['sys_code']   = md5(rand().strtotime(date("Y-m-d H:i:s")).$user['User']['id']);
                $purchaseReturn['PurchaseReturn']['created']    = $dateNow;
                $purchaseReturn['PurchaseReturn']['created_by'] = $user['User']['id'];
                $purchaseReturn['PurchaseReturn']['company_id'] = $this->data['PurchaseReturn']['company_id'];
                $purchaseReturn['PurchaseReturn']['branch_id']  = $this->data['PurchaseReturn']['branch_id'];
                $purchaseReturn['PurchaseReturn']['location_group_id'] = $this->data['PurchaseReturn']['location_group_id'];
                $purchaseReturn['PurchaseReturn']['location_id'] = $this->data['PurchaseReturn']['location_id'];
                $purchaseReturn['PurchaseReturn']['vendor_id']   = $this->data['PurchaseReturn']['vendor_id'];
                $purchaseReturn['PurchaseReturn']['currency_id'] = $this->data['PurchaseReturn']['currency_id'];
                $purchaseReturn['PurchaseReturn']['note']    = $this->data['PurchaseReturn']['note'];
                $purchaseReturn['PurchaseReturn']['ap_id']   = $apAccount['AccountType']['chart_account_id'];
                $purchaseReturn['PurchaseReturn']['pr_code'] = $product_return['PurchaseReturn']['pr_code'];
                $purchaseReturn['PurchaseReturn']['total_amount'] = $this->data['PurchaseReturn']['total_amount'];
                $purchaseReturn['PurchaseReturn']['balance']      = $this->data['PurchaseReturn']['total_amount'] + $this->data['PurchaseReturn']['total_vat'];
                $purchaseReturn['PurchaseReturn']['total_amount'] = $this->data['PurchaseReturn']['total_amount'];
                $purchaseReturn['PurchaseReturn']['order_date']   = $this->data['PurchaseReturn']['order_date'];
                $purchaseReturn['PurchaseReturn']['vat_percent']  = $this->data['PurchaseReturn']['vat_percent'];
                $purchaseReturn['PurchaseReturn']['total_vat']    = $this->data['PurchaseReturn']['total_vat'];
                $purchaseReturn['PurchaseReturn']['vat_setting_id']  = $this->data['PurchaseReturn']['vat_setting_id'];
                $purchaseReturn['PurchaseReturn']['vat_calculate']   = $this->data['PurchaseReturn']['vat_calculate'];
                $purchaseReturn['PurchaseReturn']['vat_chart_account_id'] = $this->data['PurchaseReturn']['vat_chart_account_id'];
                $purchaseReturn['PurchaseReturn']['status'] = 2;
                if ($this->PurchaseReturn->save($purchaseReturn)) {
                    $result['id'] = $purchaseReturnId = $this->PurchaseReturn->id;
                    $company      = $this->Company->read(null, $this->data['PurchaseReturn']['company_id']);
                    $classId      = $this->Helper->getClassId($company['Company']['id'], $company['Company']['classes'], $this->data['PurchaseReturn']['location_group_id']);
                    $BRCode       = $product_return['PurchaseReturn']['pr_code'];
                    // Transaction 
                    $transactionAcct = 0;
                    $transactionPro  = 0;
                    $transactionSer  = 0;
                    $transaction = array();
                    $this->Transaction->create();
                    $transaction['Transaction']['module_id']  = $purchaseReturnId;
                    $transaction['Transaction']['type']       = 'Purchase Return';
                    $transaction['Transaction']['created']    = $dateNow;
                    $transaction['Transaction']['created_by'] = $user['User']['id'];
                    $this->Transaction->save($transaction);
                    $transactionId = $this->Transaction->id;
                    // Create General Ledger
                    $generalLedger = array();
                    $this->GeneralLedger->create();
                    $generalLedger['GeneralLedger']['sys_code']   = md5(rand().strtotime(date("Y-m-d H:i:s")).$user['User']['id']);
                    $generalLedger['GeneralLedger']['purchase_return_id'] = $purchaseReturnId;
                    $generalLedger['GeneralLedger']['date'] = $this->data['PurchaseReturn']['order_date'];
                    $generalLedger['GeneralLedger']['reference']  = $BRCode;
                    $generalLedger['GeneralLedger']['created']    = $dateNow;
                    $generalLedger['GeneralLedger']['created_by'] = $user['User']['id'];
                    $generalLedger['GeneralLedger']['is_sys'] = 1;
                    $generalLedger['GeneralLedger']['is_adj'] = 0;
                    $generalLedger['GeneralLedger']['is_active'] = 1;
                    $this->GeneralLedger->save($generalLedger);
                    $generalLedgerId = $this->GeneralLedger->id;
                    // General Ledger Detail (A/P)
                    $this->GeneralLedgerDetail->create();
                    $generalLedgerDetail = array();
                    $generalLedgerDetail['GeneralLedgerDetail']['general_ledger_id'] = $generalLedgerId;
                    $generalLedgerDetail['GeneralLedgerDetail']['chart_account_id']  = $purchaseReturn['PurchaseReturn']['ap_id'];
                    $generalLedgerDetail['GeneralLedgerDetail']['location_group_id'] = $purchaseReturn['PurchaseReturn']['location_group_id'];
                    $generalLedgerDetail['GeneralLedgerDetail']['company_id']  = $purchaseReturn['PurchaseReturn']['company_id'];
                    $generalLedgerDetail['GeneralLedgerDetail']['branch_id']   = $purchaseReturn['PurchaseReturn']['branch_id'];
                    $generalLedgerDetail['GeneralLedgerDetail']['location_id'] = $purchaseReturn['PurchaseReturn']['location_id'];
                    $generalLedgerDetail['GeneralLedgerDetail']['type']   = 'Purchase Return';
                    $generalLedgerDetail['GeneralLedgerDetail']['debit']  = $purchaseReturn['PurchaseReturn']['balance'];
                    $generalLedgerDetail['GeneralLedgerDetail']['credit'] = 0;
                    $generalLedgerDetail['GeneralLedgerDetail']['memo']   = 'ICS: Purchase Return # ' . $BRCode;
                    $generalLedgerDetail['GeneralLedgerDetail']['vendor_id'] = $purchaseReturn['PurchaseReturn']['vendor_id'];
                    $generalLedgerDetail['GeneralLedgerDetail']['class_id']  = $classId;
                    $this->GeneralLedgerDetail->save($generalLedgerDetail);
                    $transactionAcct++;

                    if (($purchaseReturn['PurchaseReturn']['total_vat']) > 0) {
                        $this->GeneralLedgerDetail->create();
                        $generalLedgerDetail['GeneralLedgerDetail']['chart_account_id'] = $purchaseReturn['PurchaseReturn']['vat_chart_account_id'];
                        $generalLedgerDetail['GeneralLedgerDetail']['debit']  = 0;
                        $generalLedgerDetail['GeneralLedgerDetail']['credit'] = $purchaseReturn['PurchaseReturn']['total_vat'];
                        $generalLedgerDetail['GeneralLedgerDetail']['memo']   = 'ICS: Purchase Return # ' . $BRCode . ' Total VAT';
                        $this->GeneralLedgerDetail->save($generalLedgerDetail);
                        $transactionAcct++;
                    }

                    for ($i = 0; $i < sizeof($_POST['product']); $i++) {
                        if (!empty($_POST['product_id'][$i])) {
                            $tranDetailAcct = 0;
                            // Purchase Return Detail
                            $this->PurchaseReturnDetail->create();
                            $purchaseReturnDetail = array();
                            $purchaseReturnDetail['PurchaseReturnDetail']['sys_code']   = md5(rand().strtotime(date("Y-m-d H:i:s")).$user['User']['id']);
                            $purchaseReturnDetail['PurchaseReturnDetail']['purchase_return_id'] = $purchaseReturnId;
                            $purchaseReturnDetail['PurchaseReturnDetail']['product_id']   = $_POST['product_id'][$i];
                            $purchaseReturnDetail['PurchaseReturnDetail']['qty']          = $_POST['qty'][$i];
                            $purchaseReturnDetail['PurchaseReturnDetail']['qty_uom_id']   = $_POST['qty_uom_id'][$i];
                            $purchaseReturnDetail['PurchaseReturnDetail']['conversion']   = $_POST['conversion'][$i];
                            $purchaseReturnDetail['PurchaseReturnDetail']['unit_price']   = $_POST['unit_price'][$i];
                            $purchaseReturnDetail['PurchaseReturnDetail']['total_price']  = $_POST['total_price'][$i];
                            $purchaseReturnDetail['PurchaseReturnDetail']['note']         = $_POST['note'][$i];
                            if($_POST['lots_number'][$i] != '' && $_POST['lots_number'][$i] != '0'){
                                $lotsNumber = $_POST['lots_number'][$i];
                            } else {
                                $lotsNumber = '0';
                            }
                            $purchaseReturnDetail['PurchaseReturnDetail']['lots_number'] = $lotsNumber;
                            if($_POST['expired_date'][$i] != '' && $_POST['expired_date'][$i] != '0000-00-00'){
                                $expiredDate = $_POST['expired_date'][$i];
                            } else {
                                $expiredDate = '0000-00-00';
                            }
                            $purchaseReturnDetail['PurchaseReturnDetail']['expired_date'] = $expiredDate;
                            $this->PurchaseReturnDetail->save($purchaseReturnDetail);
                            $purchaseReturnDetailId = $this->PurchaseReturnDetail->id;
                            $transactionPro++;
                            $qtyOrder      = $_POST['qty'][$i] / ($_POST['small_val_uom'][$i] / $_POST['conversion'][$i]);
                            $qtyOrderSmall = $_POST['qty'][$i] * $_POST['conversion'][$i];
                            
                            // Transaction Detail
                            $tranDetail = array();
                            $this->TransactionDetail->create();
                            $tranDetail['TransactionDetail']['transaction_id']  = $transactionId;
                            $tranDetail['TransactionDetail']['type']       = 1;
                            $tranDetail['TransactionDetail']['module_id']  = $purchaseReturnDetailId;
                            $this->TransactionDetail->save($tranDetail);
                            $tranDetailId = $this->TransactionDetail->id;
                            
                            // Update Inventory (Purchase Return)
                            $data = array();
                            $data['module_type']        = 7;
                            $data['purchase_return_id'] = $purchaseReturnId;
                            $data['product_id']         = $_POST['product_id'][$i];
                            $data['location_id']        = $purchaseReturn['PurchaseReturn']['location_id'];
                            $data['location_group_id']  = $purchaseReturn['PurchaseReturn']['location_group_id'];
                            $data['expired_date'] = $purchaseReturnDetail['PurchaseReturnDetail']['expired_date'];
                            $data['lots_number']  = $purchaseReturnDetail['PurchaseReturnDetail']['lots_number'];
                            $data['date']         = $purchaseReturn['PurchaseReturn']['order_date'];
                            $data['total_qty']    = $qtyOrderSmall;
                            $data['total_order']  = $qtyOrderSmall;
                            $data['total_free']   = 0;
                            $data['user_id']      = $user['User']['id'];
                            $data['customer_id']  = "";
                            $data['vendor_id']    = $purchaseReturn['PurchaseReturn']['vendor_id'];
                            $data['unit_cost']    = 0;
                            $data['unit_price']   = 0;
                            $data['transaction_id'] = $tranDetailId;
                            // Update Invetory Location
                            $this->Inventory->saveInventory($data);
                            // Update Inventory Group
                            $this->Inventory->saveGroupTotalDetail($data);
                            
                            //Insert Into Receive
                            $billReturnReceive = array();
                            $this->PurchaseReturnReceive->create();
                            $billReturnReceive['PurchaseReturnReceive']['purchase_return_id'] = $purchaseReturnId;
                            $billReturnReceive['PurchaseReturnReceive']['purchase_return_detail_id'] = $purchaseReturnDetailId;
                            $billReturnReceive['PurchaseReturnReceive']['product_id']    = $_POST['product_id'][$i];
                            $billReturnReceive['PurchaseReturnReceive']['qty']           = $_POST['qty'][$i];
                            $billReturnReceive['PurchaseReturnReceive']['qty_uom_id']    = $_POST['qty_uom_id'][$i];
                            $billReturnReceive['PurchaseReturnReceive']['conversion']    = $_POST['conversion'][$i];
                            $billReturnReceive['PurchaseReturnReceive']['lots_number']   = $data['lots_number'];
                            $this->PurchaseReturnReceive->save($billReturnReceive);

                            // Inventory Valuation
                            $inv_valutaion = array();
                            $this->InventoryValuation->create();
                            $inv_valutaion['InventoryValuation']['sys_code']  = md5(rand().strtotime(date("Y-m-d H:i:s")).$user['User']['id']);
                            $inv_valutaion['InventoryValuation']['transaction_detail_id'] = $tranDetailId;
                            $inv_valutaion['InventoryValuation']['purchase_return_id'] = $purchaseReturnId;
                            $inv_valutaion['InventoryValuation']['company_id'] = $this->data['PurchaseReturn']['company_id'];
                            $inv_valutaion['InventoryValuation']['branch_id']  = $this->data['PurchaseReturn']['branch_id'];
                            $inv_valutaion['InventoryValuation']['type']      = "Purchase Return";
                            $inv_valutaion['InventoryValuation']['date']      = $purchaseReturn['PurchaseReturn']['order_date'];
                            $inv_valutaion['InventoryValuation']['created']   = $dateNow;
                            $inv_valutaion['InventoryValuation']['pid']       = $_POST['product_id'][$i];
                            $inv_valutaion['InventoryValuation']['small_qty'] = "-" . $qtyOrderSmall;
                            $inv_valutaion['InventoryValuation']['qty']   = "-" . $this->Helper->replaceThousand(number_format($qtyOrder, 6));
                            $inv_valutaion['InventoryValuation']['cost']  = null;
                            $inv_valutaion['InventoryValuation']['price'] = $_POST['unit_price'][$i] * ($_POST['small_val_uom'][$i] / $_POST['conversion'][$i]);
                            $inv_valutaion['InventoryValuation']['is_var_cost'] = 1;
                            $this->InventoryValuation->saveAll($inv_valutaion);
                            $inv_valutation_id = $this->InventoryValuation->id;
                            
                            // Update GL for Inventory
                            $this->GeneralLedgerDetail->create();
                            $queryInvAccount = mysql_query("SELECT IFNULL((IFNULL((SELECT chart_account_id FROM accounts WHERE product_id = " . $_POST['product_id'][$i] . " AND account_type_id=1),(SELECT chart_account_id FROM pgroup_accounts WHERE pgroup_id = (SELECT pgroup_id FROM product_pgroups WHERE product_id = " . $_POST['product_id'][$i] . " ORDER BY id  DESC LIMIT 1) AND account_type_id=1))),(SELECT chart_account_id FROM account_types WHERE id=1))");
                            $dataInvAccount = mysql_fetch_array($queryInvAccount);
                            $generalLedgerDetail['GeneralLedgerDetail']['chart_account_id'] = $dataInvAccount[0];
                            $generalLedgerDetail['GeneralLedgerDetail']['service_id'] = NULL;
                            $generalLedgerDetail['GeneralLedgerDetail']['product_id'] = $_POST['product_id'][$i];
                            $generalLedgerDetail['GeneralLedgerDetail']['inventory_valuation_id'] = $inv_valutation_id;
                            $generalLedgerDetail['GeneralLedgerDetail']['inventory_valuation_is_debit'] = 0;
                            $generalLedgerDetail['GeneralLedgerDetail']['debit']  = 0;
                            $generalLedgerDetail['GeneralLedgerDetail']['credit'] = $_POST['total_price'][$i];
                            $generalLedgerDetail['GeneralLedgerDetail']['memo']   = 'ICS: Inventory Purchase Return # ' . $BRCode . ' Product # ' . $_POST['product'][$i];
                            $this->GeneralLedgerDetail->save($generalLedgerDetail);
                            $tranDetailAcct++;

                            // Update GL COGS
                            $this->GeneralLedgerDetail->create();
                            $queryCogsAccount = mysql_query("SELECT IFNULL((IFNULL((SELECT chart_account_id FROM accounts WHERE product_id = " . $_POST['product_id'][$i] . " AND account_type_id=2),(SELECT chart_account_id FROM pgroup_accounts WHERE pgroup_id = (SELECT pgroup_id FROM product_pgroups WHERE product_id = " . $_POST['product_id'][$i] . " ORDER BY id  DESC LIMIT 1) AND account_type_id=2))),(SELECT chart_account_id FROM account_types WHERE id=2))");
                            $dataCogsAccount  = mysql_fetch_array($queryCogsAccount);
                            $generalLedgerDetail['GeneralLedgerDetail']['chart_account_id'] = $dataCogsAccount[0];
                            $generalLedgerDetail['GeneralLedgerDetail']['inventory_valuation_id'] = $inv_valutation_id;
                            $generalLedgerDetail['GeneralLedgerDetail']['inventory_valuation_is_debit'] = 1;
                            $generalLedgerDetail['GeneralLedgerDetail']['debit']  = 0;
                            $generalLedgerDetail['GeneralLedgerDetail']['credit'] = 0;
                            $generalLedgerDetail['GeneralLedgerDetail']['memo']   = 'ICS: COGS Purchase Return # ' . $BRCode . ' Product # ' . $_POST['product'][$i];
                            $this->GeneralLedgerDetail->save($generalLedgerDetail);
                            $tranDetailAcct++;
                            // Update Transaction Detail
                            mysql_query("UPDATE transaction_details SET save_acct = ".$tranDetailAcct." WHERE id = ".$tranDetailId);
                        } else if (!empty($_POST['service_id'][$i])) {
                            $tranDetailAcct = 0;
                            // Service
                            $purchaseReturnService = array();
                            $this->PurchaseReturnService->create();
                            $purchaseReturnService['PurchaseReturnService']['purchase_return_id'] = $purchaseReturnId;
                            $purchaseReturnService['PurchaseReturnService']['service_id']  = $_POST['service_id'][$i];
                            $purchaseReturnService['PurchaseReturnService']['qty']         = $_POST['qty'][$i];
                            $purchaseReturnService['PurchaseReturnService']['unit_price']  = $_POST['unit_price'][$i];
                            $purchaseReturnService['PurchaseReturnService']['total_price'] = $_POST['total_price'][$i];
                            $purchaseReturnService['PurchaseReturnService']['note']        = $_POST['note'][$i];
                            $this->PurchaseReturnService->save($purchaseReturnService);
                            $PurchaseReturnServiceId = $this->PurchaseReturnService->id;
                            $transactionSer++;
                            // General Ledger Detail (Service)
                            $this->GeneralLedgerDetail->create();
                            $queryServiceAccount = mysql_query("SELECT IFNULL((SELECT chart_account_id FROM services WHERE id=" . $_POST['service_id'][$i] . "),(SELECT chart_account_id FROM account_types WHERE id=9))");
                            $dataServiceAccount = mysql_fetch_array($queryServiceAccount);
                            $generalLedgerDetail['GeneralLedgerDetail']['chart_account_id'] = $dataServiceAccount[0];
                            $generalLedgerDetail['GeneralLedgerDetail']['service_id'] = $_POST['service_id'][$i];
                            $generalLedgerDetail['GeneralLedgerDetail']['product_id'] = NULL;
                            $generalLedgerDetail['GeneralLedgerDetail']['inventory_valuation_id'] = NULL;
                            $generalLedgerDetail['GeneralLedgerDetail']['inventory_valuation_is_debit'] = NULL;
                            $generalLedgerDetail['GeneralLedgerDetail']['debit']  = 0;
                            $generalLedgerDetail['GeneralLedgerDetail']['credit'] = $_POST['total_price'][$i];
                            $generalLedgerDetail['GeneralLedgerDetail']['memo']   = 'ICS: Purchase Return  # ' . $BRCode . ' Service # ' . $_POST['product'][$i];
                            $this->GeneralLedgerDetail->save($generalLedgerDetail);
                            $tranDetailAcct++;
                            // Transaction Detail
                            $tranDetail = array();
                            $this->TransactionDetail->create();
                            $tranDetail['TransactionDetail']['transaction_id']  = $transactionId;
                            $tranDetail['TransactionDetail']['type']       = 2;
                            $tranDetail['TransactionDetail']['module_id']  = $PurchaseReturnServiceId;
                            $tranDetail['TransactionDetail']['save_acct']  = $tranDetailAcct;
                            $this->TransactionDetail->save($tranDetail);
                        }
                    }
                    // Update Transaction Save
                    mysql_query("UPDATE transactions SET save_acct = ".$transactionAcct.", products=".$transactionPro.", service=".$transactionSer." WHERE id = ".$transactionId);
                    // Recalculate Average Cost
                    mysql_query("UPDATE tracks SET val='".$purchaseReturn['PurchaseReturn']['order_date']."', is_recalculate = 1 WHERE id=1");
                    $this->Helper->saveUserActivity($user['User']['id'], 'Purchase Return', 'Save Edit', $this->data['id'], $purchaseReturnId);
                    echo json_encode($result);
                    exit;
                } else {
                    $this->Helper->saveUserActivity($user['User']['id'], 'Purchase Return', 'Save Edit (Error)', $this->data['id']);
                    $result['error'] = 1;
                    echo json_encode($result);
                    exit;
                }
            } else {
                $this->Helper->saveUserActivity($user['User']['id'], 'Purchase Return', 'Save Edit (Error Status)', $this->data['id']);
                $result['error'] = 1;
                echo json_encode($result);
                exit;
            }
        }
        $this->Helper->saveUserActivity($user['User']['id'], 'Purchase Return', 'Edit', $id);
        $companies = ClassRegistry::init('Company')->find('all', array('joins' => array(array('table' => 'user_companies', 'type' => 'inner', 'conditions' => array('user_companies.company_id=Company.id'))), 'fields' => array('Company.id', 'Company.name', 'Company.vat_calculate'), 'conditions' => array('Company.is_active = 1', 'user_companies.user_id=' . $user['User']['id'])));
        $branches  = ClassRegistry::init('Branch')->find('all', array('joins' => array(array('table' => 'user_branches', 'type' => 'inner', 'conditions' => array('user_branches.branch_id=Branch.id')), array('table' => 'module_code_branches AS ModuleCodeBranch', 'type' => 'left', 'conditions' => array('ModuleCodeBranch.branch_id=Branch.id'))), 'fields' => array('Branch.id', 'Branch.name', 'Branch.company_id', 'ModuleCodeBranch.br_code', 'Branch.currency_id', 'Currency.symbol'), 'conditions' => array('Branch.is_active = 1', 'user_branches.user_id=' . $user['User']['id'])));
        $locationGroups = ClassRegistry::init('LocationGroup')->find('list', array('joins' => array(array('table' => 'user_location_groups', 'type' => 'inner', 'conditions' => array('user_location_groups.location_group_id=LocationGroup.id'))),'conditions' => array('user_location_groups.user_id=' . $user['User']['id'], 'LocationGroup.is_active' => '1', 'LocationGroup.location_group_type_id != 1')));
        $locations = ClassRegistry::init('Location')->find('all', array('joins' => array(array('table' => 'user_locations', 'type' => 'inner', 'conditions' => array('user_locations.location_id=Location.id'))), 'conditions' => array('user_locations.user_id=' . $user['User']['id'] . ' AND Location.is_active=1'), 'order' => 'Location.name'));
        $purchase_returns = ClassRegistry::init('PurchaseReturn')->find('first', array('conditions' => array('PurchaseReturn.status = 2', 'PurchaseReturn.id' => $id)));
        $this->set(compact("purchase_returns", "locationGroups", "locations", "apAccountId", "id", "companies", "branches"));
    }

    function editDetail($id = null) {
        $this->layout = 'ajax';
        $uoms = ClassRegistry::init('Uom')->find('all', array('fields' => array('Uom.id', 'Uom.name'), 'conditions' => array('Uom.is_active' => 1)));
        if (!empty($id)) {
            $user = $this->getCurrentUser();
            $branches  = ClassRegistry::init('Branch')->find('all', array('joins' => array(array('table' => 'user_branches', 'type' => 'inner', 'conditions' => array('user_branches.branch_id=Branch.id')), array('table' => 'module_code_branches AS ModuleCodeBranch', 'type' => 'left', 'conditions' => array('ModuleCodeBranch.branch_id=Branch.id'))), 'fields' => array('Branch.id', 'Branch.name', 'Branch.company_id', 'ModuleCodeBranch.br_code', 'Branch.currency_id', 'Currency.symbol'), 'conditions' => array('Branch.is_active = 1', 'user_branches.user_id=' . $user['User']['id'])));
            $purchase_returns = ClassRegistry::init('PurchaseReturn')->find('first', array('conditions' => array('PurchaseReturn.status = 2', 'PurchaseReturn.id' => $id)));
            $purchaseReturnDetails  = ClassRegistry::init('PurchaseReturnDetail')->find('all', array('conditions' => array('PurchaseReturnDetail.purchase_return_id' => $id)));
            $purchaseReturnServices = ClassRegistry::init('PurchaseReturnService')->find('all', array('conditions' => array('PurchaseReturnService.purchase_return_id' => $id)));
            $this->set(compact('branches', 'uoms', "purchaseReturnDetails", "purchase_returns", "purchaseReturnServices"));
        } else {
            $this->set(compact('uoms'));
        }
    }

//    function receive($id = null) {
//        $this->layout = 'ajax';
//        if (!$id && empty($this->data)) {
//            exit;
//        }
//        $user = $this->getCurrentUser();
//        if (!empty($this->data)) {            
//            $purcahse_return = $this->PurchaseReturn->read(null, $this->data['pr_id']);
//            if ($purcahse_return['PurchaseReturn']['status'] == 1 || $purcahse_return['PurchaseReturn']['status'] == 3) {
//                $access = true;
//                $productOrder = array();
//                $sqlDetail = mysql_query("SELECT id, product_id, SUM(IFNULL(qty,0) * conversion) AS qty FROM purchase_return_details WHERE purchase_return_id =".$this->data['pr_id']." AND id NOT IN (SELECT purchase_return_detail_id FROM purchase_return_receives GROUP BY purchase_return_detail_id) GROUP BY product_id");
//                if(@mysql_num_rows($sqlDetail)) {
//                    // Check With Current Stock
//                    while($rowDetail = mysql_fetch_array($sqlDetail)){
//                        if (array_key_exists($rowDetail['product_id'], $productOrder)){
//                            $productOrder[$rowDetail['product_id']]['qty'] += $rowDetail['qty'];
//                        } else {
//                            $productOrder[$rowDetail['product_id']]['qty'] = $rowDetail['qty'];
//                        }
//                    }
//                }
//                // Check Qty in Stock Before Save
//                foreach($productOrder AS $key => $order){
//                    $sqlTotal = mysql_query("SELECT (SUM(IFNULL(total_qty,0) - IFNULL(total_order,0)) + (SELECT IFNULL(SUM(qty),0) FROM stock_orders WHERE product_id = ".$key." AND purchase_return_id = ".$this->data['pr_id'].")) AS total_qty FROM ".$purcahse_return['PurchaseReturn']['location_group_id']."_group_totals WHERE product_id = ".$key." AND location_group_id = ".$purcahse_return['PurchaseReturn']['location_group_id']." AND location_id = ".$purcahse_return['PurchaseReturn']['location_id']." GROUP BY product_id;");
//                    $rowTotal = mysql_fetch_array($sqlTotal);
//                    if($rowTotal['total_qty'] < $order['qty']){
//                        $access = false;
//                    }
//                }
//                if($access == true) {
//                    $r = 0;
//                    $restCode = array();
//                    $dateNow  = date("Y-m-d H:i:s");
//                    $this->loadModel('PurchaseReturnReceive');
//                    $datePbc  = $purcahse_return['PurchaseReturn']['order_date'];
//                    // Calculate Location, Lot, Expired Date
//                    $sqlOrder = mysql_query("SELECT stock_orders.*, products.price_uom_id FROM stock_orders INNER JOIN products ON products.id = stock_orders.product_id WHERE stock_orders.purchase_return_id = ".$this->data['pr_id']);
//                    while($rowOrder = mysql_fetch_array($sqlOrder)){
//                        // Reset Stock Order
//                        $this->Inventory->saveGroupQtyOrder($rowOrder['location_group_id'], $rowOrder['location_id'], $rowOrder['product_id'], $rowOrder['lots_number'], $rowOrder['lots_number'], $rowOrder['qty'], $datePbc, '-');
//                        $sqlUom = mysql_query("SELECT IFNULL((SELECT to_uom_id FROM uom_conversions WHERE from_uom_id = ".$rowOrder['price_uom_id']." AND is_small_uom = 1 LIMIT 1), ".$rowOrder['price_uom_id'].")");
//                        $rowUom = mysql_fetch_array($sqlUom);
//                        // Get Lots, Expired, Total Qty
//                        $invInfos   = array();
//                        $index      = 0;
//                        $totalOrder = $rowOrder['qty'];
//                        // Calculate Location, Lot, Expired Date
//                        $sqlInventory = mysql_query("SELECT SUM(IFNULL(group_totals.total_qty,0)) AS total_qty, group_totals.location_id AS location_id, group_totals.lots_number AS lots_number, group_totals.lots_number AS lots_number FROM ".$rowOrder['location_group_id']."_group_totals AS group_totals WHERE group_totals.location_id = ".$rowOrder['location_id']." AND group_totals.product_id = ".$rowOrder['product_id']." GROUP BY group_totals.location_id, group_totals.product_id, group_totals.lots_number, group_totals.lots_number HAVING total_qty > 0 ORDER BY group_totals.lots_number, group_totals.lots_number, group_totals.location_id ASC");
//                        while($rowInventory = mysql_fetch_array($sqlInventory)) {
//                            // Check Order
//                            $stockOrder = 0;
//                            $sqlStock = mysql_query("SELECT SUM(qty) FROM stock_orders WHERE purchase_return_id IS NULL AND product_id = ".$rowOrder['product_id']." AND location_group_id = ".$rowOrder['location_group_id']." AND location_id = ".$rowInventory['location_id']." AND lots_number = '".$rowInventory['lots_number']."' AND lots_number = '".$rowInventory['lots_number']."'");
//                            if(mysql_num_rows($sqlStock)){
//                                $rowStock = mysql_fetch_array($sqlStock);
//                                $stockOrder = $rowStock[0];
//                            }
//                            $totalStock = $rowInventory['total_qty'] - $stockOrder;
//                            if($totalOrder > 0 && $totalStock > 0){
//                                if($totalStock >= $totalOrder) {
//                                    $invInfos[$index]['total_qty']    = $totalOrder;
//                                    $invInfos[$index]['location_id']  = $rowInventory['location_id'];
//                                    $invInfos[$index]['lots_number']  = $rowInventory['lots_number'];
//                                    $invInfos[$index]['lots_number'] = $rowInventory['lots_number'];
//                                    $totalOrder = 0;
//                                    ++$index;
//                                } else if($totalStock < $totalOrder) {
//                                    $invInfos[$index]['total_qty']    = $rowInventory['total_qty'];
//                                    $invInfos[$index]['location_id']  = $rowInventory['location_id'];
//                                    $invInfos[$index]['lots_number']  = $rowInventory['lots_number'];
//                                    $invInfos[$index]['lots_number'] = $rowInventory['lots_number'];
//                                    $totalOrder = $totalOrder - $totalStock;
//                                    ++$index;
//                                }
//                            }
//                        }
//                        // Cut Stock
//                        foreach($invInfos AS $invInfo){
//                            // Update Inventory (Purchase Return)
//                            $data = array();
//                            $data['module_type']        = 7;
//                            $data['purchase_return_id'] = $rowOrder['purchase_return_id'];
//                            $data['product_id']         = $rowOrder['product_id'];
//                            $data['location_id']        = $invInfo['location_id'];
//                            $data['location_group_id']  = $rowOrder['location_group_id'];
//                            $data['lots_number']  = '0';
//                            $data['lots_number'] = '0000-00-00';
//                            $data['date']         = $datePbc;
//                            $data['total_qty']    = $invInfo['total_qty'];
//                            $data['total_order']  = $invInfo['total_qty'];
//                            $data['total_free']   = 0;
//                            $data['user_id']      = $user['User']['id'];
//                            $data['customer_id']  = "";
//                            $data['vendor_id']    = $purcahse_return['PurchaseReturn']['vendor_id'];
//                            $data['unit_cost']    = 0;
//                            $data['unit_price']   = 0;
//                            // Update Invetory Location
//                            $this->Inventory->saveInventory($data);
//                            // Update Inventory Group
//                            $this->Inventory->saveGroupTotalDetail($data);
//                            // Convert to REST
//                            $restCode[$r] = $this->Helper->convertToDataSync($data, 'inventories');
//                            $restCode[$r]['module_type']  = 7;
//                            $restCode[$r]['total_qty']    = $invInfo['total_qty'];
//                            $restCode[$r]['total_order']  = $invInfo['total_qty'];
//                            $restCode[$r]['total_free']   = 0;
//                            $restCode[$r]['lots_number'] = $data['lots_number'];
//                            $restCode[$r]['customer_id']  = "";
//                            $restCode[$r]['unit_cost']    = 0;
//                            $restCode[$r]['unit_price']   = 0;
//                            $restCode[$r]['vendor_id']    = $this->Helper->getSQLSyncCode("vendors", $purcahse_return['PurchaseReturn']['vendor_id']);
//                            $restCode[$r]['purchase_return_id'] = $this->Helper->getSQLSyncCode("purchase_returns", $rowOrder['purchase_return_id']);
//                            $restCode[$r]['product_id']        = $this->Helper->getSQLSyncCode("products", $rowOrder['product_id']);
//                            $restCode[$r]['location_id']       = $this->Helper->getSQLSyncCode("locations", $invInfo['location_id']);
//                            $restCode[$r]['location_group_id'] = $this->Helper->getSQLSyncCode("location_groups", $rowOrder['location_group_id']);
//                            $restCode[$r]['user_id']           = $this->Helper->getSQLSyncCode("users", $user['User']['id']);
//                            $restCode[$r]['dbtype']  = 'saveInv,GroupDetail';
//                            $restCode[$r]['actodo']  = 'inv';
//                            $r++;
//                            
//                            //Insert Into Delivery Detail
//                            $billReturnReceive = array();
//                            $this->PurchaseReturnReceive->create();
//                            $billReturnReceive['PurchaseReturnReceive']['purchase_return_id'] = $rowOrder['purchase_return_id'];
//                            $billReturnReceive['PurchaseReturnReceive']['purchase_return_detail_id'] = $rowOrder['purchase_return_detail_id'];
//                            $billReturnReceive['PurchaseReturnReceive']['product_id']    = $rowOrder['product_id'];
//                            $billReturnReceive['PurchaseReturnReceive']['qty_uom_id']    = $rowUom[0];
//                            $billReturnReceive['PurchaseReturnReceive']['lots_number']   = $invInfo['lots_number']!=''?$invInfo['lots_number']:0;
//                            $billReturnReceive['PurchaseReturnReceive']['lots_number']  = $invInfo['lots_number']!='0000-00-00'?$invInfo['lots_number']:'0000-00-00';
//                            $billReturnReceive['PurchaseReturnReceive']['qty'] = $invInfo['total_qty'];
//                            $this->PurchaseReturnReceive->save($billReturnReceive);
//                            // Convert to REST
//                            $restCode[$r] = $this->Helper->convertToDataSync($billReturnReceive['PurchaseReturnReceive'], 'purchase_return_receives');
//                            $restCode[$r]['dbtodo'] = 'purchase_return_receives';
//                            $restCode[$r]['actodo'] = 'is';
//                            $r++;
//                        }
//                    }
//                    // Update Sales Order
//                    mysql_query("UPDATE purchase_returns SET status = 2, `modified` = '".$dateNow."', `modified_by` = ".$user['User']['id']." WHERE  `id`= " . $this->data['pr_id']);
//                    // Convert to REST
//                    $restCode[$r]['status']      = 2;
//                    $restCode[$r]['modified']    = $dateNow;
//                    $restCode[$r]['modified_by'] = $this->Helper->getSQLSysCode("users", $user['User']['id']);
//                    $restCode[$r]['dbtodo'] = 'purchase_returns';
//                    $restCode[$r]['actodo'] = 'ut';
//                    $restCode[$r]['con']    = "sys_code = '".$purcahse_return['PurchaseReturn']['sys_code']."'";
//                    // Detele Tmp Stock Order
//                    mysql_query("DELETE FROM `stock_orders` WHERE  `purchase_return_id`=".$this->data['pr_id'].";");
//                    // Save File Send
//                    $this->Helper->sendFileToSync($restCode, 0, 0);
//                    $this->Helper->saveUserActivity($user['User']['id'], 'Purchase Return', 'Save Receive', $this->data['pr_id']);
//                    echo MESSAGE_DATA_HAS_BEEN_SAVED;
//                    exit;
//                }
//            } else {
//                $this->Helper->saveUserActivity($user['User']['id'], 'Purchase Return', 'Save Receive (Error Status)', $this->data['pr_id']);
//                echo 'error';
//                exit;
//            }
//        }
//        $this->Helper->saveUserActivity($user['User']['id'], 'Purchase Return', 'Receive', $id);
//        $purchase_returns = ClassRegistry::init('PurchaseReturn')->find('first', array('conditions' => array('PurchaseReturn.id' => $id)));
//        $purchaseReturnDetails = ClassRegistry::init('PurchaseReturnDetail')->find('all', array('conditions' => array('PurchaseReturnDetail.purchase_return_id' => $id)));
//        $this->set(compact("purchase_returns", "purchaseReturnDetails"));
//    }

    function void($id = null) {
        if (!$id) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $user = $this->getCurrentUser();
        $this->loadModel('GeneralLedger');
        $this->loadModel('InventoryValuation');
        $this->loadModel('PurchaseReturnDetail');
        $this->loadModel('Transaction');
        $queryHasReceipt = mysql_query("SELECT id FROM purchase_return_receipts WHERE purchase_return_id=" . $id . " AND is_void = 0");
        $queryHasApplyInv = mysql_query("SELECT id FROM invoice_pbc_with_pbs WHERE purchase_return_id=" . $id . " AND status > 0");
        if (@mysql_num_rows($queryHasReceipt) || @mysql_num_rows($queryHasApplyInv)) {
            $this->Helper->saveUserActivity($user['User']['id'], 'Purchase Return', 'Void (Transaction with other modules)', $id);
            echo MESSAGE_DATA_COULD_NOT_BE_SAVED;
            exit;
        }
        $product_return = $this->PurchaseReturn->read(null, $id);
        if (!isset($product_return['PurchaseReturn']['order_date']) || is_null($product_return['PurchaseReturn']['order_date']) || $product_return['PurchaseReturn']['order_date'] == '0000-00-00' || $product_return['PurchaseReturn']['order_date'] == '') {
            $this->Helper->saveUserActivity($user['User']['id'], 'Purchase Return', 'Void (Error Order Date)', $id);
            echo MESSAGE_DATA_COULD_NOT_BE_SAVED;
            exit;
        }
        if($product_return['PurchaseReturn']['order_date'] == 1){
            $this->Helper->saveUserActivity($user['User']['id'], 'Purchase Return', 'Void (Error Status)', $id);
            echo MESSAGE_DATA_COULD_NOT_BE_SAVED;
            exit;
        }
        // Check Save Transaction
        $checkTransaction = true;
        $transactionLogId = 0;
        $sqlCheck = mysql_query("SELECT * FROM transactions WHERE type = 'Purchase Return' AND action = 1 AND module_id = ".$id);
        if(mysql_num_rows($sqlCheck)){
            $rowCheck  = mysql_fetch_array($sqlCheck);
            $sqlDetail = mysql_query("SELECT * FROM transaction_details WHERE transaction_id = ".$rowCheck['id']);
            $rowDetail = mysql_num_rows($sqlDetail);
            $transactionLogId = $rowCheck['id'];
            if($rowDetail > 0){
                // Check Total Transaction
                $totalD = $rowCheck['products'] + $rowCheck['service'];
                if($totalD != $rowDetail){
                    $checkTransaction = false;
                } else {
                    $totalAcctD = 0;
                    while($rowD = mysql_fetch_array($sqlDetail)){
                        $totalAcctD += $rowD['save_acct'];
                        if($rowD['type'] == 1){
                            if($rowD['inventory_valutaion'] != '1'){
                                $checkTransaction = false;
                                break;
                            }
                            if($product_return['PurchaseReturn']['status'] == 2){
                                if($rowD['inventory'] != '1' || $rowD['inventory_total'] != '1' || $rowD['loc_inventory'] != '1' || $rowD['loc_inventory_total'] != '1' || $rowD['loc_inventory_detail'] != '1' || $rowD['g_inventory'] != '1' || $rowD['g_inventory_detail'] != '1'){
                                    $checkTransaction = false;
                                    break;
                                }
                            }
                        }
                    }
                    if($checkTransaction == true){
                        // Check Account
                        $sqlAcct = mysql_query("SELECT COUNT(id) FROM general_ledger_details WHERE general_ledger_id = (SELECT id FROM general_ledgers WHERE purchase_return_id = ".$id." AND purchase_return_receipt_id IS NULL LIMIT 1)");
                        if(mysql_num_rows($sqlAcct)){
                            $rowAcct = mysql_fetch_array($sqlAcct);
                            if($rowAcct[0] != ($totalAcctD + $rowCheck['save_acct'])){
                                $checkTransaction = false;
                            }
                        } else {
                            $checkTransaction = false;
                        }
                    }
                }
            } else {
                $checkTransaction = false;
            }
        }
        if($checkTransaction == false){
            $this->Helper->saveUserActivity($user['User']['id'], 'Purchase Return', 'Void (Error Save Transaction)', $id);
            echo MESSAGE_CLOUD_NOT_EDIT_TRANSACTION;
            exit;
        }
        // Remove Transaction Log
        if($transactionLogId > 0){
            mysql_query("DELETE FROM transactions WHERE id = ".$transactionLogId);
            mysql_query("DELETE FROM transaction_details WHERE transaction_id = ".$transactionLogId);
        }
        $this->PurchaseReturn->updateAll(
                array('PurchaseReturn.status' => 0, 'PurchaseReturn.modified_by' => $user['User']['id']), array('PurchaseReturn.id' => $id)
        );
        $this->InventoryValuation->updateAll(
                array('InventoryValuation.is_active' => "2"), array('InventoryValuation.purchase_return_id' => $id)
        );
        $this->GeneralLedger->updateAll(
                array('GeneralLedger.is_active' => 2, 'GeneralLedger.modified_by' => $user['User']['id']), array('GeneralLedger.purchase_return_id' => $id)
        );
        $dateNow  = date("Y-m-d H:i:s");
        $this->Transaction->create();
        $transaction = array();
        $transaction['Transaction']['module_id']  = $id;
        $transaction['Transaction']['type']       = 'Purchase Return';
        $transaction['Transaction']['action']     = 2;
        $transaction['Transaction']['created']    = $dateNow;
        $transaction['Transaction']['created_by'] = $user['User']['id'];
        $this->Transaction->save($transaction);
        if($product_return['PurchaseReturn']['status'] == 2){
            $purchaseReturnDetails = ClassRegistry::init('PurchaseReturnDetail')->find("all", array('conditions' => array('PurchaseReturnDetail.purchase_return_id' => $id)));
            foreach($purchaseReturnDetails AS $purchaseReturnDetail){
                $qtyOrderSmall = ($purchaseReturnDetail['PurchaseReturnDetail']['qty'] * $purchaseReturnDetail['PurchaseReturnDetail']['conversion']);
                // Update Inventory (Purchase Return)
                $data = array();
                $data['module_type']        = 20;
                $data['purchase_return_id'] = $id;
                $data['product_id']         = $purchaseReturnDetail['PurchaseReturnDetail']['product_id'];
                $data['location_id']        = $product_return['PurchaseReturn']['location_id'];
                $data['location_group_id']  = $product_return['PurchaseReturn']['location_group_id'];
                $data['expired_date'] = '0000-00-00';
                $data['lots_number']  = $purchaseReturnDetail['PurchaseReturnDetail']['lots_number']!=""?$purchaseReturnDetail['PurchaseReturnDetail']['lots_number']:0;
                $data['date']         = $product_return['PurchaseReturn']['order_date'];
                $data['total_qty']    = $qtyOrderSmall;
                $data['total_order']  = $qtyOrderSmall;
                $data['total_free']   = 0;
                $data['user_id']      = $user['User']['id'];
                $data['customer_id']  = "";
                $data['vendor_id']    = $product_return['PurchaseReturn']['vendor_id'];
                $data['unit_cost']    = 0;
                $data['unit_price']   = 0;
                $data['transaction_id'] = '';
                // Update Invetory Location
                $this->Inventory->saveInventory($data);
                // Update Inventory Group
                $this->Inventory->saveGroupTotalDetail($data);
            }
        }
        // Recalculate Average Cost
        $dateReca = date("Y-m-d", strtotime(date("Y-m-d", strtotime($product_return['PurchaseReturn']['order_date'])) . " -1 day"));
        mysql_query("UPDATE tracks SET val='".$dateReca."', is_recalculate = 1 WHERE id=1");
        $this->Helper->saveUserActivity($user['User']['id'], 'Purchase Return', 'Void', $id);
        echo MESSAGE_DATA_HAS_BEEN_DELETED;
        exit;
    }

    function voidReceipt($id) {
        if (!$id) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $user = $this->getCurrentUser();
        $this->loadModel('GeneralLedger');
        $this->loadModel('PurchaseReturnReceipt');
        $this->loadModel('Transaction');
        $receipt = ClassRegistry::init('PurchaseReturnReceipt')->find("first", array('conditions' => array('PurchaseReturnReceipt.id' => $id)));
        if(!empty($receipt) && @$receipt['PurchaseReturnReceipt']['is_void'] == 0){
            // Check Save Transaction
            $checkTransaction = true;
            $transactionLogId = 0;
            $sqlCheck = mysql_query("SELECT * FROM transactions WHERE type = 'Purchase Return Receipt' AND action = 1 AND module_id = ".$id);
            if(mysql_num_rows($sqlCheck)){
                $rowCheck = mysql_fetch_array($sqlCheck);
                $transactionLogId = $rowCheck['id'];
                // Check Account
                $sqlAcct = mysql_query("SELECT COUNT(id) FROM general_ledger_details WHERE general_ledger_id = (SELECT id FROM general_ledgers WHERE purchase_return_receipt_id = ".$id." LIMIT 1)");
                if(mysql_num_rows($sqlAcct)){
                    $rowAcct = mysql_fetch_array($sqlAcct);
                    if($rowAcct[0] != $rowCheck['save_acct']){
                        $checkTransaction = false;
                    }
                } else {
                    $checkTransaction = false;
                }
            }
            if($checkTransaction == false){
                $this->Helper->saveUserActivity($user['User']['id'], 'Purchase Return Receipt', 'Void (Error Save Transaction)', $id);
                echo MESSAGE_CLOUD_NOT_EDIT_TRANSACTION;
                exit;
            }
            $this->PurchaseReturnReceipt->updateAll(
                    array('PurchaseReturnReceipt.is_void' => 1, 'PurchaseReturnReceipt.modified_by' => $user['User']['id']), array('PurchaseReturnReceipt.id' => $id)
            );
            $exchangeRate = ClassRegistry::init('ExchangeRate')->find("first", array("conditions" => array("ExchangeRate.id" => $receipt['PurchaseReturnReceipt']['exchange_rate_id'])));
            if(!empty($exchangeRate) && $exchangeRate['ExchangeRate']['rate_to_sell'] > 0){
                $totalPaidOther = $receipt['PurchaseReturnReceipt']['amount_other'] / $exchangeRate['ExchangeRate']['rate_to_sell'];
            } else {
                $totalPaidOther = 0;
            }
            $total_amount = $receipt['PurchaseReturnReceipt']['amount_us'] + $totalPaidOther;

            mysql_query("UPDATE purchase_returns SET balance = balance+" . $total_amount . " WHERE id=" . $receipt['PurchaseReturnReceipt']['purchase_return_id']);
            $this->GeneralLedger->updateAll(
                    array('GeneralLedger.is_active' => 2, 'GeneralLedger.modified_by' => $user['User']['id']), array('GeneralLedger.purchase_return_receipt_id' => $id)
            );
            // Transaction
            $dateNow  = date("Y-m-d H:i:s");
            $this->Transaction->create();
            $transaction = array();
            $transaction['Transaction']['module_id']  = $id;
            $transaction['Transaction']['type']       = 'Purchase Return Receipt';
            $transaction['Transaction']['action']     = 2;
            $transaction['Transaction']['created']    = $dateNow;
            $transaction['Transaction']['created_by'] = $user['User']['id'];
            $this->Transaction->save($transaction);
            $this->Helper->saveUserActivity($user['User']['id'], 'Purchase Return Receipt', 'Void', $id);
            echo MESSAGE_DATA_HAS_BEEN_DELETED;
            exit;
        }else{
            $this->Helper->saveUserActivity($user['User']['id'], 'Purchase Bill Receipt', 'Void (Error)', $id);
            echo MESSAGE_DATA_COULD_NOT_BE_SAVED;
            exit;
        }
    }

    function deletePbcWPo($id) {
        if (!$id) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $dateNow  = date("Y-m-d H:i:s");
        $result = array();
        $user = $this->getCurrentUser();
        $this->loadModel('InvoicePbcWithPb');
        $this->loadModel('GeneralLedger');
        $pbcWPo = $this->InvoicePbcWithPb->read(null, $id);
        if ($pbcWPo['InvoicePbcWithPb']['status'] == 1) {
            mysql_query("UPDATE purchase_orders SET balance = balance + " . $pbcWPo['InvoicePbcWithPb']['total_cost'] . " WHERE id=" . $pbcWPo['InvoicePbcWithPb']['purchase_order_id']);
            mysql_query("UPDATE purchase_returns SET balance = balance + " . $pbcWPo['InvoicePbcWithPb']['total_cost'] . ", total_amount_po =total_amount_po - " . $pbcWPo['InvoicePbcWithPb']['total_cost'] . " WHERE id=" . $pbcWPo['InvoicePbcWithPb']['purchase_return_id']);
            $this->data['InvoicePbcWithPb']['id'] = $id;
            $this->data['InvoicePbcWithPb']['modified']    = $dateNow;
            $this->data['InvoicePbcWithPb']['modified_by'] = $user['User']['id'];
            $this->data['InvoicePbcWithPb']['status'] = 0;
            $this->InvoicePbcWithPb->save($this->data);
            $this->GeneralLedger->updateAll(
                    array('GeneralLedger.is_active' => 2, 'GeneralLedger.modified_by' => $user['User']['id']),
                    array('GeneralLedger.invoice_pbc_with_pbs_id' => $id)
            );
            $this->Helper->saveUserActivity($user['User']['id'], 'Purchase Return Receipt', 'Void Apply to PB', $id);
            $result['result']   = 1;
        } else {
            $this->Helper->saveUserActivity($user['User']['id'], 'Purchase Return Receipt', 'Void Apply to PB (Error)', $id);
            $result['result']   = 2;
        }
        echo json_encode($result);
        exit;
    }

    
    function pickProduct($billReturnDetailId = null, $locationGroupId = null){        
        $this->layout = 'ajax';        
        if(empty($billReturnDetailId) || empty($locationGroupId)){
            echo MESSAGE_DATA_INVALID;
            exit;
        }      
        $purchaseReturnDetail = ClassRegistry::init('PurchaseReturnDetail')->find("first", array('conditions' => array('PurchaseReturnDetail.id' => $billReturnDetailId)));
        $this->set(compact("billReturnDetailId", "locationGroupId", "purchaseReturnDetail"));        
    }
    
    function pickProductAjax($productId = null, $locationGroupId = null, $smallUomLabel = null, $smallUomId = null){
        $this->layout = 'ajax';
        $this->set(compact('productId', 'locationGroupId', 'smallUomLabel', 'smallUomId'));
    }
    
    function pickProductSave(){
        $this->layout = 'ajax';
        if(!empty($this->data)){
            $user = $this->getCurrentUser();
            $sql = mysql_query("SELECT id FROM purchase_return_receives WHERE purchase_return_detail_id = ".$this->data['bill_return_detail_id']);
            if(!mysql_num_rows($sql)){
                $this->loadModel('PurchaseReturnReceive');
                $billReturn = ClassRegistry::init('PurchaseReturn')->find("first", array('conditions' => array('PurchaseReturn.id' => $this->data['bill_return_id'])));
                // Reset Stock Order
                $sqlResetOrder = mysql_query("SELECT * FROM stock_orders WHERE `purchase_return_id`=".$this->data['bill_return_id']." AND purchase_return_detail_id = ".$this->data['bill_return_detail_id'].";");
                while($rowResetOrder = mysql_fetch_array($sqlResetOrder)){
                    $this->Inventory->saveGroupQtyOrder($rowResetOrder['location_group_id'], $rowResetOrder['location_id'], $rowResetOrder['product_id'], $rowResetOrder['lots_number'], $rowResetOrder['lots_number'], $rowResetOrder['qty'], $rowResetOrder['date'], '-');
                }
                // Detele Tmp Stock Order
                mysql_query("DELETE FROM `stock_orders` WHERE  `purchase_return_id`=".$this->data['bill_return_id']." AND purchase_return_detail_id = ".$this->data['bill_return_detail_id'].";");   
                for($i = 0; $i < sizeof($_POST['qty_pick']); $i++){
                    // Update Inventory (Purchase Return)
                    $data = array();
                    $data['module_type']        = 7;
                    $data['purchase_return_id'] = $this->data['bill_return_id'];
                    $data['product_id']         = $this->data['product_id'];
                    $data['location_id']        = $_POST['location_id'][$i];
                    $data['location_group_id']  = $billReturn['PurchaseReturn']['location_group_id'];
                    $data['lots_number']  = $_POST['lots_number'][$i]!=''?$_POST['lots_number'][$i]:0;
                    $data['lots_number'] = $_POST['lots_number'][$i]!=''?$_POST['lots_number'][$i]:'0000-00-00';
                    $data['date']         = $billReturn['PurchaseReturn']['order_date'];
                    $data['total_qty']    = $_POST['qty_pick'][$i];
                    $data['total_order']  = $_POST['qty_pick'][$i];
                    $data['total_free']   = 0;
                    $data['user_id']      = $user['User']['id'];
                    $data['customer_id']  = "";
                    $data['vendor_id']    = $billReturn['PurchaseReturn']['vendor_id'];
                    $data['unit_cost']    = 0;
                    // Update Invetory Location
                    $this->Inventory->saveInventory($data);
                    // Update Inventory Group
                    $this->Inventory->saveGroupTotalDetail($data);
                    
                    $this->PurchaseReturnReceive->create();
                    $dnExpried = array();
                    $dnExpried['PurchaseReturnReceive']['purchase_return_id'] = $this->data['bill_return_id'];
                    $dnExpried['PurchaseReturnReceive']['purchase_return_detail_id'] = $this->data['bill_return_detail_id'];
                    $dnExpried['PurchaseReturnReceive']['product_id']   = $this->data['product_id'];
                    $dnExpried['PurchaseReturnReceive']['qty']          = $_POST['qty_pick'][$i];
                    $dnExpried['PurchaseReturnReceive']['qty_uom_id']   = $_POST['uom'][$i];
                    $dnExpried['PurchaseReturnReceive']['lots_number']  = $_POST['lots_number'][$i]!=''?$_POST['lots_number'][$i]:0;
                    $dnExpried['PurchaseReturnReceive']['lots_number'] = $_POST['lots_number'][$i]!=''?$_POST['lots_number'][$i]:'0000-00-00';
                    $this->PurchaseReturnReceive->save($dnExpried);
                }
                $this->Helper->saveUserActivity($user['User']['id'], 'Purchase Return', 'Save Product Pick One', $this->data['bill_return_id']);
                $invalid['success'] = 1;
            }else{
                $this->Helper->saveUserActivity($user['User']['id'], 'Purchase Return', 'Save Product Pick One (Existed)', $this->data['bill_return_id']);
                $invalid['ready'] = 1;
            }
            echo json_encode($invalid);
            exit();
        }
    }
    
    function searchVendor() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        $userPermission = 'Vendor.id IN (SELECT vendor_id FROM vendor_companies WHERE company_id IN (SELECT company_id FROM user_companies WHERE user_id ='.$user['User']['id'].'))';
        $vendors = ClassRegistry::init('Vendor')->find('all', array(
                    'conditions' => array('OR' => array(
                            'Vendor.name LIKE' => '%'.$this->params['url']['q'].'%',
                            'Vendor.vendor_code LIKE' => '%'.$this->params['url']['q'].'%',
                        ), 'Vendor.is_active' => 1, $userPermission
                    ),
                ));
        if (!empty($vendors)) {
            foreach ($vendors as $vendor) {
                $queryNetDays = mysql_query('SELECT (SELECT net_days FROM payment_terms WHERE id=payment_term_id) FROM vendors WHERE id=' . $vendor['Vendor']['id']);
                $dataNetDays  = mysql_fetch_array($queryNetDays);
                $sqlCompany   = mysql_query("SELECT GROUP_CONCAT(company_id) AS company_id FROM vendor_companies WHERE vendor_id = ".$vendor['Vendor']['id']);
                $rowCompany   = mysql_fetch_array($sqlCompany);
                echo "{$vendor['Vendor']['id']}.*{$vendor['Vendor']['name']}.*{$vendor['Vendor']['vendor_code']}.*{$dataNetDays[0]}.*{$rowCompany[0]}\n";
            }
        }
        exit;
    }
    
    function purchaseBill($companyId = null, $branchId = null, $vendorId = ''){
        $this->layout = 'ajax';
        $this->set(compact('companyId', 'branchId', 'vendorId'));
    }
    
    function purchaseBillAjax($companyId, $branchId, $vendorId = ''){
        $this->layout = 'ajax';
        $this->set(compact('companyId', 'branchId', 'vendorId'));
    }
    
    function getProductByExp($productId, $locationId, $orderDate = 0){
        $this->layout = 'ajax';
        if(empty($productId) || empty($locationId) || empty($orderDate)){
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $this->set(compact('productId', 'orderDate', 'locationId'));
    }
    
}

?>
