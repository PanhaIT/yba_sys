<?php

class ConsignmentReturnsController extends AppController {

    var $name = 'ConsignmentReturns';
    var $components = array('Helper', 'Inventory');
    
    function viewByUser() {
        $this->layout = 'ajax';
    }

    function index() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        $this->Helper->saveUserActivity($user['User']['id'], 'Consignment Return', 'Dashboard');
    }

    function ajax($customer = 'all', $filterStatus = 'all', $date = '') {
        $this->layout = 'ajax';
        $this->set(compact('customer', 'filterStatus', 'company', 'date'));
    }

    function view($id = null) {
        $this->layout = 'ajax';
        if (!empty($id)) {
            $user = $this->getCurrentUser();
            $this->data = $this->ConsignmentReturn->read(null, $id);
            if (!empty($this->data)) {
                $this->Helper->saveUserActivity($user['User']['id'], 'Consignment Return', 'View', $id);
                $consignmentReturnDetails = ClassRegistry::init('ConsignmentReturnDetail')->find("all", array('conditions' => array('ConsignmentReturnDetail.consignment_return_id' => $id)));
                $this->set(compact('consignmentReturnDetails'));
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
            $checkErrorStock = 1;
            $listOutStock = "";
            $productOrder = array();
            // Get Customer Warehouse
            $customerWarehouse = ClassRegistry::init('LocationGroup')->find("first", array('conditions' => array('LocationGroup.customer_id' => $this->data['ConsignmentReturn']['customer_id'])));
            if(empty($customerWarehouse)){
                $this->Helper->saveUserActivity($user['User']['id'], 'Consignment Return', 'Invalid Customer Warehouse');
                $result['code'] = 4;
                echo json_encode($result);
                exit;
            }
            for ($i = 0; $i < sizeof($_POST['product']); $i++) {
                if($_POST['product_id'][$i] != ""){
                    $key = $_POST['product_id'][$i];
                    if (array_key_exists($key, $productOrder)){
                        $productOrder[$key]['qty'] += $this->Helper->replaceThousand($_POST['qty'][$i] * $_POST['conversion'][$i]);
                    } else {
                        $productOrder[$key]['qty'] = $this->Helper->replaceThousand($_POST['qty'][$i] * $_POST['conversion'][$i]);
                    }
                }
            }
            // Get Loction Setting
            $locSetting = ClassRegistry::init('LocationSetting')->findById(4);
            $locCon     = '';
            if($locSetting['LocationSetting']['location_status'] == 1){
                $locCon = ' AND is_for_sale = 1';
            }
            foreach($productOrder AS $key => $order){
                // Get Total Qty In Stock
                $totalStockAv = 0;
                $sqlInv = mysql_query("SELECT SUM(total_qty - total_order) FROM `".$this->data['ConsignmentReturn']['location_group_id']."_group_totals` WHERE product_id = " . $key . " AND location_group_id = " . $this->data['ConsignmentReturn']['location_group_id']." AND location_id IN (SELECT id FROM locations WHERE location_group_id = ".$this->data['ConsignmentReturn']['location_group_id'].$locCon.")");
                while($rInv=mysql_fetch_array($sqlInv)){
                    $totalStockAv = $rInv[0];
                }
                $qtyOrder = $order['qty'];
                if($qtyOrder > $totalStockAv){
                    $checkErrorStock = 2; 
                    $listOutStock .= $key."|".$totalStockAv."-";
                }
            }
            if($checkErrorStock == 1){
                $result = array();
                // Load Model
                $this->loadModel('ConsignmentReturnDetail');
                $this->loadModel('StockOrder');
                
                $this->ConsignmentReturn->create();
                $consignmentReturn = array();
                $consignmentReturn['ConsignmentReturn']['code']              = $this->data['ConsignmentReturn']['code'];
                $consignmentReturn['ConsignmentReturn']['company_id']        = $this->data['ConsignmentReturn']['company_id'];
                $consignmentReturn['ConsignmentReturn']['branch_id']         = $this->data['ConsignmentReturn']['branch_id'];
                $consignmentReturn['ConsignmentReturn']['consignment_id']    = $this->data['ConsignmentReturn']['consignment_id'];
                $consignmentReturn['ConsignmentReturn']['location_group_id']    = $this->data['ConsignmentReturn']['location_group_id'];
                $consignmentReturn['ConsignmentReturn']['location_group_to_id'] = $this->data['ConsignmentReturn']['location_group_to_id'];
                $consignmentReturn['ConsignmentReturn']['customer_id']         = $this->data['ConsignmentReturn']['customer_id'];
                $consignmentReturn['ConsignmentReturn']['customer_contact_id'] = $this->data['ConsignmentReturn']['customer_contact_id'];
                $consignmentReturn['ConsignmentReturn']['date']   = $this->data['ConsignmentReturn']['date'];
                $consignmentReturn['ConsignmentReturn']['note']   = $this->data['ConsignmentReturn']['note'];
                $consignmentReturn['ConsignmentReturn']['created_by'] = $user['User']['id'];
                $consignmentReturn['ConsignmentReturn']['status'] = 1;
                if ($this->ConsignmentReturn->save($consignmentReturn)) {
                    $result['consign_return_id'] = $consignmentReturnId = $this->ConsignmentReturn->id;
                    // Get Module Code
                    $modCode = $this->Helper->getModuleCode($this->data['ConsignmentReturn']['code'], $consignmentReturnId, 'code', 'consignment_returns', 'status != -1 AND branch_id = '.$this->data['ConsignmentReturn']['branch_id']);
                    // Updaet Module Code
                    mysql_query("UPDATE consignment_returns SET code = '".$modCode."' WHERE id = ".$consignmentReturnId);
                    for ($i = 0; $i < sizeof($_POST['product']); $i++) {
                        if (!empty($_POST['product_id'][$i])) {
                            /* Consignment Return Detail */
                            $consignmentReturnDetail = array();
                            $this->ConsignmentReturnDetail->create();
                            $consignmentReturnDetail['ConsignmentReturnDetail']['consignment_return_id']   = $consignmentReturnId;
                            $consignmentReturnDetail['ConsignmentReturnDetail']['product_id']   = $_POST['product_id'][$i];
                            $consignmentReturnDetail['ConsignmentReturnDetail']['qty_uom_id']   = $_POST['qty_uom_id'][$i];
                            $consignmentReturnDetail['ConsignmentReturnDetail']['qty']          = $_POST['qty'][$i];
                            $consignmentReturnDetail['ConsignmentReturnDetail']['lots_number']  = $_POST['lots_number'][$i]!=''?$_POST['lots_number'][$i]:0;
                            $consignmentReturnDetail['ConsignmentReturnDetail']['expired_date'] = $_POST['expired_date'][$i]!=''?$_POST['expired_date'][$i]:'0000-00-00';
                            $consignmentReturnDetail['ConsignmentReturnDetail']['conversion']   = $_POST['conversion'][$i];
                            $consignmentReturnDetail['ConsignmentReturnDetail']['note']  = $_POST['note'][$i];
                            $this->ConsignmentReturnDetail->save($consignmentReturnDetail);
                            
                            $conLocInv  = "IN (SELECT id FROM locations WHERE location_group_id = ".$consignmentReturn['ConsignmentReturn']['location_group_id'].")";
                            $invInfos   = array();
                            $index      = 0;
                            $totalOrder = $_POST['qty'][$i] * $_POST['conversion'][$i];
                            // Calculate Location, Lot, Expired Date
                            $sqlInventory = mysql_query("SELECT SUM(IFNULL(group_totals.total_qty,0) - IFNULL(group_totals.total_order,0)) AS total_qty, group_totals.location_id AS location_id, group_totals.lots_number AS lots_number, group_totals.expired_date AS expired_date FROM ".$consignmentReturn['ConsignmentReturn']['location_group_id']."_group_totals AS group_totals WHERE group_totals.location_id ".$conLocInv." AND group_totals.product_id = ".$_POST['product_id'][$i]." AND group_totals.lots_number = '".$consignmentReturnDetail['ConsignmentReturnDetail']['lots_number']."' AND group_totals.expired_date = '".$consignmentReturnDetail['ConsignmentReturnDetail']['expired_date']."' GROUP BY group_totals.location_id, group_totals.product_id, group_totals.lots_number, group_totals.expired_date HAVING total_qty > 0 ORDER BY group_totals.expired_date, group_totals.lots_number, group_totals.total_qty ASC");
                            while($rowInventory = mysql_fetch_array($sqlInventory)){
                                if($totalOrder > 0 && $rowInventory['total_qty'] > 0){
                                    if($rowInventory['total_qty'] >= $totalOrder){
                                        $invInfos[$index]['total_qty']    = $totalOrder;
                                        $invInfos[$index]['location_id']  = $rowInventory['location_id'];
                                        $invInfos[$index]['lots_number']  = $rowInventory['lots_number'];
                                        $invInfos[$index]['expired_date'] = $rowInventory['expired_date'];
                                        $totalOrder = 0;
                                        ++$index;
                                    }else if($rowInventory['total_qty'] < $totalOrder){
                                        $invInfos[$index]['total_qty']    = $rowInventory['total_qty'];
                                        $invInfos[$index]['location_id']  = $rowInventory['location_id'];
                                        $invInfos[$index]['lots_number']  = $rowInventory['lots_number'];
                                        $invInfos[$index]['expired_date'] = $rowInventory['expired_date'];
                                        $totalOrder = $totalOrder - $rowInventory['total_qty'];
                                        ++$index;
                                    }
                                }else{
                                    break;
                                }
                            }
                            // Update Inventory
                            foreach($invInfos AS $invInfo){
                                //Insert Into Tmp Sales
                                $tmpDelivey = array();
                                $this->StockOrder->create();
                                $tmpDelivey['StockOrder']['consignment_return_id'] = $consignmentReturnId;
                                $tmpDelivey['StockOrder']['product_id']    = $_POST['product_id'][$i];
                                $tmpDelivey['StockOrder']['location_group_id']   = $consignmentReturn['ConsignmentReturn']['location_group_id'];
                                $tmpDelivey['StockOrder']['location_id']   = $invInfo['location_id'];
                                $tmpDelivey['StockOrder']['lots_number']   = $invInfo['lots_number'];
                                $tmpDelivey['StockOrder']['expired_date']  = $invInfo['expired_date'];
                                $tmpDelivey['StockOrder']['date'] = $consignmentReturn['ConsignmentReturn']['date'];
                                $tmpDelivey['StockOrder']['qty']  = $invInfo['total_qty'];
                                $this->StockOrder->save($tmpDelivey);
                                $this->Inventory->saveGroupQtyOrder($consignmentReturn['ConsignmentReturn']['location_group_id'], $invInfo['location_id'], $_POST['product_id'][$i], $invInfo['lots_number'], $invInfo['expired_date'], $invInfo['total_qty'], $consignmentReturn['ConsignmentReturn']['date'], '+');
                            }
                        }
                    }
                    $this->Helper->saveUserActivity($user['User']['id'], 'Consignment Return', 'Save Add New', $consignmentReturnId);
                    echo json_encode($result);
                    exit;
                } else {
                    $this->Helper->saveUserActivity($user['User']['id'], 'Consignment Return', 'Save Add New (Error)');
                    $result['code'] = 2;
                    echo json_encode($result);
                    exit;
                }
            }else{
                $this->Helper->saveUserActivity($user['User']['id'], 'Consignment Return', 'Save Add New (Error Out of Stock)');
                // Error Out of Stock
                $result['listOutStock'] = $listOutStock;
                $result['code'] = 3;
                echo json_encode($result);
                exit;
            }
        }
        $this->Helper->saveUserActivity($user['User']['id'], 'Consignment Return', 'Add New');
        // Get Loction Setting
        $locSetting = ClassRegistry::init('LocationSetting')->findById(4);
        $locCon     = '';
        if($locSetting['LocationSetting']['location_status'] == 1){
            $locCon = 'locations.is_for_sale = 1';
        }
        $joinUsers = array('table' => 'user_location_groups', 'type' => 'INNER', 'conditions' => array('user_location_groups.location_group_id=LocationGroup.id'));
        $joinLocation = array('table' => 'locations', 'type' => 'INNER', 'conditions' => array('locations.location_group_id=LocationGroup.id', $locCon));
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
                            'fields' => array('Branch.id', 'Branch.name', 'Branch.company_id', 'ModuleCodeBranch.cus_consign_return_code', 'Branch.currency_id', 'Currency.symbol'),
                            'conditions' => array('Branch.is_active = 1', 'user_branches.user_id=' . $user['User']['id'])
                        ));
        $locationGroupTos = ClassRegistry::init('LocationGroup')->find('list', array('fields' => array('LocationGroup.id', 'LocationGroup.name'),'joins' => array($joinUsers, $joinLocation),'conditions' => array('user_location_groups.user_id=' . $user['User']['id'], 'LocationGroup.is_active' => '1', 'LocationGroup.location_group_type_id != 1'), 'group' => 'LocationGroup.id'));
        $this->set(compact("locationGroupTos","companies","branches"));
    }

    function orderDetails() {
        $this->layout = 'ajax';
        $uoms = ClassRegistry::init('Uom')->find('all', array('fields' => array('Uom.id', 'Uom.name'), 'conditions' => array('Uom.is_active' => 1)));
        $this->set(compact('uoms'));
    }

    function customer($companyId) {
        $this->layout = 'ajax';
        if(!empty($companyId)){
            $saleId = $_POST['sale_id'];
            $this->set("saleId", $saleId);
            $this->set('companyId', $companyId);
        }else{
            exit;
        }
    }

    function customer_ajax($companyId, $group = null) {
        $this->layout = 'ajax';
        if(!empty($companyId)){
            $saleId = $_GET['sale_id'];
            $this->set("saleId", $saleId);
            $this->set('companyId', $companyId);
            $this->set('group', $group);
        }else{
            exit;
        }
    }
    
    function searchCustomer() {
        Configure::write('debug', 0);
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        $userPermission = 'Customer.id IN (SELECT customer_id FROM customer_companies WHERE company_id IN (SELECT company_id FROM user_companies WHERE user_id ='.$user['User']['id'].')) AND Customer.id IN (SELECT customer_id FROM location_groups WHERE is_active = 1)';
        $customers = ClassRegistry::init('Customer')->find('all', array(
                    'conditions' => array('OR' => array(
                            'Customer.name LIKE' => '%' . $this->params['url']['q'] . '%',
                            'Customer.name_kh LIKE' => '%' . $this->params['url']['q'] . '%',
                            'Customer.customer_code LIKE' => '%' . $this->params['url']['q'] . '%',
                            'Customer.main_number LIKE' => '%' . $this->params['url']['q'] . '%',
                            'Customer.mobile_number LIKE' => '%' . $this->params['url']['q'] . '%',
                            'Customer.other_number LIKE' => '%' . $this->params['url']['q'] . '%',
                            'Customer.email LIKE' => '%' . $this->params['url']['q'] . '%',
                            'Customer.fax LIKE' => '%' . $this->params['url']['q'] . '%',
                        ), 'Customer.is_active' => 1, $userPermission
                    ),
                ));

        $this->set(compact('customers'));
    }
    
    function getCustomerContact($customerId){
        $this->layout = 'ajax';
        $result = '<option value="">'.INPUT_SELECT.'</option>';
        if (!empty($customerId)) {
            $customerContacts  = ClassRegistry::init('CustomerContact')->find("all", array('conditions' => array('CustomerContact.customer_id' => $customerId)));
            foreach($customerContacts AS $customerContact){
                $result .= '<option value="'.$customerContact['CustomerContact']['id'].'">'.$customerContact['CustomerContact']['contact_name'].'</option>';
            }
        }
        echo $result;
        exit;
    }

    function void($id = null) {
        if (!$id) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $user = $this->getCurrentUser();
        $consignmentReturn = $this->ConsignmentReturn->read(null, $id);
        if($consignmentReturn['ConsignmentReturn']['status'] == 1){
            $this->ConsignmentReturn->updateAll(
                    array('ConsignmentReturn.status' => 0, 'ConsignmentReturn.modified_by' => $user['User']['id']),
                    array('ConsignmentReturn.id' => $id)
            );
            // Reset Stock Order
            $sqlResetOrder = mysql_query("SELECT * FROM stock_orders WHERE `consignment_return_id`=".$id.";");
            while($rowResetOrder = mysql_fetch_array($sqlResetOrder)){
                $this->Inventory->saveGroupQtyOrder($rowResetOrder['location_group_id'], $rowResetOrder['location_id'], $rowResetOrder['product_id'], $rowResetOrder['lots_number'], $rowResetOrder['expired_date'], $rowResetOrder['qty'], $rowResetOrder['date'], '-');
            }
            // Detele Tmp Stock Order
            mysql_query("DELETE FROM `stock_orders` WHERE  `consignment_return_id`=".$id.";");
            $this->Helper->saveUserActivity($user['User']['id'], 'Consignment Return', 'Void', $id);
            echo MESSAGE_DATA_HAS_BEEN_DELETED;
            exit;
        } else {
            $this->Helper->saveUserActivity($user['User']['id'], 'Consignment Return', 'Void (Error Status)', $id);
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
            $checkErrorStock = 1;
            $listOutStock    = "";
            $productOrder = array();
            // Get Customer Warehouse
            $customerWarehouse = ClassRegistry::init('LocationGroup')->find("first", array('conditions' => array('LocationGroup.customer_id' => $this->data['ConsignmentReturn']['customer_id'])));
            if(empty($customerWarehouse)){
                $this->Helper->saveUserActivity($user['User']['id'], 'Consignment Return', 'Invalid Customer Warehouse');
                $result['code'] = 4;
                echo json_encode($result);
                exit;
            }
            for ($i = 0; $i < sizeof($_POST['product']); $i++) {
                if($_POST['product_id'][$i] != ""){
                    $key = $_POST['product_id'][$i];
                    if (array_key_exists($key, $productOrder)){
                        $productOrder[$key]['qty'] += $this->Helper->replaceThousand($_POST['qty'][$i] * $_POST['conversion'][$i]);
                    } else {
                        $productOrder[$key]['qty'] = $this->Helper->replaceThousand($_POST['qty'][$i] * $_POST['conversion'][$i]);
                    }
                    
                }
            }
            // Get Loction Setting
            $locSetting = ClassRegistry::init('LocationSetting')->findById(4);
            $locCon     = '';
            if($locSetting['LocationSetting']['location_status'] == 1){
                $locCon = ' AND is_for_sale = 1';
            }
            foreach($productOrder AS $key => $order){
                // Get Total Qty in Stock
                $totalStockAv = 0;
                $sqlInv = mysql_query("SELECT SUM(total_qty) FROM `".$this->data['ConsignmentReturn']['location_group_id']."_group_totals` WHERE product_id = ".$key." AND location_id IN (SELECT id FROM locations WHERE location_group_id = " . $this->data['ConsignmentReturn']['location_group_id'].$locCon.")");
                while($rInv=mysql_fetch_array($sqlInv)){
                    $totalStockAv = $rInv[0];
                }
                // Get Total Qty in Order
                $totalOrder = 0;
                $sqlOrder = mysql_query("SELECT sum(sor.qty) as total_order FROM `stock_orders` as sor WHERE sor.consignment_return_id = ".$id." AND sor.product_id = ".$key." AND sor.location_group_id = ".$this->data['ConsignmentReturn']['location_group_id']." AND date = '".$this->data['ConsignmentReturn']['date']."' GROUP BY sor.product_id");
                while(@$rOrder=mysql_fetch_array($sqlOrder)){
                    $totalOrder = $rOrder['total_order'];
                }
                $totalSale       = $totalStockAv + $totalOrder;
                $qtyOrder        = $order['qty'];
                $qtyStockCompare = $totalSale;
                if($qtyOrder > $qtyStockCompare){
                    $checkErrorStock = 2; 
                    $listOutStock .= $key."|".$totalSale."-";
                }
            }
            if($checkErrorStock == 1){
                $consignmentReturn = $this->ConsignmentReturn->read(null, $id);
                if ($consignmentReturn['ConsignmentReturn']['status'] == 1) {
                    $result = array();
                    $statuEdit = "-1";
                    // Load Model
                    $this->loadModel('StockOrder');
                    $this->loadModel('ConsignmentReturnDetail');
                    // Reset Stock Order
                    $sqlResetOrder = mysql_query("SELECT * FROM stock_orders WHERE `consignment_return_id`=".$id.";");
                    while($rowResetOrder = mysql_fetch_array($sqlResetOrder)){
                        $this->Inventory->saveGroupQtyOrder($rowResetOrder['location_group_id'], $rowResetOrder['location_id'], $rowResetOrder['product_id'], $rowResetOrder['lots_number'], $rowResetOrder['expired_date'], $rowResetOrder['qty'], $rowResetOrder['date'], '-');
                    }
                    // Detele Tmp Stock Order
                    mysql_query("DELETE FROM `stock_orders` WHERE `consignment_return_id`=".$id.";");
                    // Update Status Edit
                    $this->ConsignmentReturn->updateAll(
                            array('ConsignmentReturn.status' => $statuEdit, 'ConsignmentReturn.modified_by' => $user['User']['id']),
                            array('ConsignmentReturn.id' => $id)
                    );
                    
                    $this->ConsignmentReturn->create();
                    $consignmentReturn = array();
                    $consignmentReturn['ConsignmentReturn']['code']       = $this->data['ConsignmentReturn']['code'];
                    $consignmentReturn['ConsignmentReturn']['company_id'] = $this->data['ConsignmentReturn']['company_id'];
                    $consignmentReturn['ConsignmentReturn']['branch_id']  = $this->data['ConsignmentReturn']['branch_id'];
                    $consignmentReturn['ConsignmentReturn']['consignment_id'] = $this->data['ConsignmentReturn']['consignment_id'];
                    $consignmentReturn['ConsignmentReturn']['location_group_id']     = $this->data['ConsignmentReturn']['location_group_id'];
                    $consignmentReturn['ConsignmentReturn']['location_group_to_id']  = $this->data['ConsignmentReturn']['location_group_to_id'];
                    $consignmentReturn['ConsignmentReturn']['customer_id']         = $this->data['ConsignmentReturn']['customer_id'];
                    $consignmentReturn['ConsignmentReturn']['customer_contact_id'] = $this->data['ConsignmentReturn']['customer_contact_id'];
                    $consignmentReturn['ConsignmentReturn']['date']   = $this->data['ConsignmentReturn']['date'];
                    $consignmentReturn['ConsignmentReturn']['note']   = $this->data['ConsignmentReturn']['note'];
                    $consignmentReturn['ConsignmentReturn']['created_by'] = $user['User']['id'];
                    $consignmentReturn['ConsignmentReturn']['status'] = 1;
                    if ($this->ConsignmentReturn->save($consignmentReturn)) {
                        $result['consign_return_id'] = $consignmentReturnId = $this->ConsignmentReturn->id;
                        if($this->data['ConsignmentReturn']['branch_id'] != $consignmentReturn['ConsignmentReturn']['branch_id']){
                            // Get Module Code
                            $modCode = $this->Helper->getModuleCode($this->data['ConsignmentReturn']['code'], $consignmentReturnId, 'code', 'consignment_returns', 'status != -1 AND branch_id = '.$this->data['ConsignmentReturn']['branch_id']);
                            // Updaet Module Code
                            mysql_query("UPDATE consignment_returns SET code = '".$modCode."' WHERE id = ".$consignmentReturnId);
                        }
                        for ($i = 0; $i < sizeof($_POST['product']); $i++) {
                            if (!empty($_POST['product_id'][$i])) {
                                /* Consignment Return Detail */
                                $consignmentReturnDetail = array();
                                $this->ConsignmentReturnDetail->create();
                                $consignmentReturnDetail['ConsignmentReturnDetail']['consignment_return_id']   = $consignmentReturnId;
                                $consignmentReturnDetail['ConsignmentReturnDetail']['product_id']  = $_POST['product_id'][$i];
                                $consignmentReturnDetail['ConsignmentReturnDetail']['qty_uom_id']  = $_POST['qty_uom_id'][$i];
                                $consignmentReturnDetail['ConsignmentReturnDetail']['qty']         = $_POST['qty'][$i];
                                $consignmentReturnDetail['ConsignmentReturnDetail']['lots_number']  = $_POST['lots_number'][$i]!=''?$_POST['lots_number'][$i]:0;
                                $consignmentReturnDetail['ConsignmentReturnDetail']['expired_date'] = $_POST['expired_date'][$i]!=''?$_POST['expired_date'][$i]:'0000-00-00';
                                $consignmentReturnDetail['ConsignmentReturnDetail']['conversion']  = $_POST['conversion'][$i];
                                $consignmentReturnDetail['ConsignmentReturnDetail']['note']  = $_POST['note'][$i];
                                $this->ConsignmentReturnDetail->save($consignmentReturnDetail);

                                $conLocInv  = "IN (SELECT id FROM locations WHERE location_group_id = ".$consignmentReturn['ConsignmentReturn']['location_group_id'].")";
                                $invInfos   = array();
                                $index      = 0;
                                $totalOrder = $_POST['qty'][$i] * $_POST['conversion'][$i];
                                // Calculate Location, Lot, Expired Date
                                $sqlInventory = mysql_query("SELECT SUM(IFNULL(group_totals.total_qty,0) - IFNULL(group_totals.total_order,0)) AS total_qty, group_totals.location_id AS location_id, group_totals.lots_number AS lots_number, group_totals.expired_date AS expired_date FROM ".$consignmentReturn['ConsignmentReturn']['location_group_id']."_group_totals AS group_totals WHERE group_totals.location_id ".$conLocInv." AND group_totals.product_id = ".$_POST['product_id'][$i]." AND group_totals.lots_number = '".$consignmentReturnDetail['ConsignmentReturnDetail']['lots_number']."' AND group_totals.expired_date = '".$consignmentReturnDetail['ConsignmentReturnDetail']['expired_date']."' GROUP BY group_totals.location_id, group_totals.product_id, group_totals.lots_number, group_totals.expired_date HAVING total_qty > 0 ORDER BY group_totals.expired_date, group_totals.lots_number, group_totals.total_qty ASC");
                                while($rowInventory = mysql_fetch_array($sqlInventory)){
                                    if($totalOrder > 0 && $rowInventory['total_qty'] > 0){
                                        if($rowInventory['total_qty'] >= $totalOrder){
                                            $invInfos[$index]['total_qty']    = $totalOrder;
                                            $invInfos[$index]['location_id']  = $rowInventory['location_id'];
                                            $invInfos[$index]['lots_number']  = $rowInventory['lots_number'];
                                            $invInfos[$index]['expired_date'] = $rowInventory['expired_date'];
                                            $totalOrder = 0;
                                            ++$index;
                                        }else if($rowInventory['total_qty'] < $totalOrder){
                                            $invInfos[$index]['total_qty']    = $rowInventory['total_qty'];
                                            $invInfos[$index]['location_id']  = $rowInventory['location_id'];
                                            $invInfos[$index]['lots_number']  = $rowInventory['lots_number'];
                                            $invInfos[$index]['expired_date'] = $rowInventory['expired_date'];
                                            $totalOrder = $totalOrder - $rowInventory['total_qty'];
                                            ++$index;
                                        }
                                    }else{
                                        break;
                                    }
                                }
                                // Update Inventory
                                foreach($invInfos AS $invInfo){
                                    //Insert Into Tmp Sales
                                    $tmpDelivey = array();
                                    $this->StockOrder->create();
                                    $tmpDelivey['StockOrder']['consignment_return_id'] = $consignmentReturnId;
                                    $tmpDelivey['StockOrder']['product_id']     = $_POST['product_id'][$i];
                                    $tmpDelivey['StockOrder']['location_group_id']   = $consignmentReturn['ConsignmentReturn']['location_group_id'];
                                    $tmpDelivey['StockOrder']['location_id']   = $invInfo['location_id'];
                                    $tmpDelivey['StockOrder']['lots_number']   = $invInfo['lots_number'];
                                    $tmpDelivey['StockOrder']['expired_date']  = $invInfo['expired_date'];
                                    $tmpDelivey['StockOrder']['date']  = $consignmentReturn['ConsignmentReturn']['date'];
                                    $tmpDelivey['StockOrder']['qty'] = $invInfo['total_qty'];
                                    $this->StockOrder->save($tmpDelivey);
                                    $this->Inventory->saveGroupQtyOrder($consignmentReturn['ConsignmentReturn']['location_group_id'], $invInfo['location_id'], $_POST['product_id'][$i], $invInfo['lots_number'], $invInfo['expired_date'], $invInfo['total_qty'], $consignmentReturn['ConsignmentReturn']['date'], '+');
                                }
                            }
                        }
                        $this->Helper->saveUserActivity($user['User']['id'], 'Consignment Return', 'Save Edit', $id, $consignmentReturnId);
                        echo json_encode($result);
                        exit;
                    } else {
                        $this->Helper->saveUserActivity($user['User']['id'], 'Consignment Return', 'Save Edit (Error)', $id);
                        // Error Saves
                        $result['error'] = 2;
                        echo json_encode($result);
                        exit;
                    }
                } else {
                    // Error Saves
                    $this->Helper->saveUserActivity($user['User']['id'], 'Consignment Return', 'Save Edit (Error Status)', $id);
                    $result['error'] = 2;
                    echo json_encode($result);
                    exit;
                }
            }else{
                $this->Helper->saveUserActivity($user['User']['id'], 'Consignment Return', 'Save Edit (Error Out of Stock)', $id);
                // Error Out Of Stock
                $result['listOutStock'] = $listOutStock;
                $result['code'] = '3';
                echo json_encode($result);
                exit;
            }
        }

        if (!empty($id)) {
            $this->Helper->saveUserActivity($user['User']['id'], 'Consignment Return', 'Edit', $id);
            $this->data = $this->ConsignmentReturn->read(null, $id);
            // Get Loction Setting
            $locSetting = ClassRegistry::init('LocationSetting')->findById(4);
            $locCon     = '';
            if($locSetting['LocationSetting']['location_status'] == 1){
                $locCon = 'locations.is_for_sale = 1';
            }
            $joinUsers = array('table' => 'user_location_groups', 'type' => 'INNER', 'conditions' => array('user_location_groups.location_group_id=LocationGroup.id'));
            $joinLocation = array('table' => 'locations', 'type' => 'INNER', 'conditions' => array('locations.location_group_id=LocationGroup.id', $locCon));
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
                                'fields' => array('Branch.id', 'Branch.name', 'Branch.company_id', 'ModuleCodeBranch.cus_consign_return_code', 'Branch.currency_id', 'Currency.symbol'),
                                'conditions' => array('Branch.is_active = 1', 'user_branches.user_id=' . $user['User']['id'])
                            ));
            $locationGroupTos = ClassRegistry::init('LocationGroup')->find('list', array('fields' => array('LocationGroup.id', 'LocationGroup.name'),'joins' => array($joinUsers, $joinLocation),'conditions' => array('user_location_groups.user_id=' . $user['User']['id'], 'LocationGroup.is_active' => '1', 'LocationGroup.location_group_type_id != 1'), 'group' => 'LocationGroup.id'));
            $this->set(compact("locationGroupTos","companies","branches"));
        }else{
            exit;
        }
    }
    
    function editDetail($consignmentReturnId = null) {
        $this->layout = 'ajax';
        if ($consignmentReturnId >= 0) {
            $consignmentReturn         = ClassRegistry::init('ConsignmentReturn')->find("first", array('conditions' => array('ConsignmentReturn.id' => $consignmentReturnId)));
            $consignmentReturnDetails  = ClassRegistry::init('ConsignmentReturnDetail')->find("all", array('conditions' => array('ConsignmentReturnDetail.consignment_return_id' => $consignmentReturnId)));
            $uoms = ClassRegistry::init('Uom')->find('all', array('fields' => array('Uom.id', 'Uom.name'), 'conditions' => array('Uom.is_active' => 1)));
            $locSetting = ClassRegistry::init('LocationSetting')->findById(4);
            $this->set(compact('consignmentReturn', 'consignmentReturnDetails', 'uoms', 'locSetting'));
        } else {
            exit;
        }
    }
    
    function printInvoice($id = null){
        $this->layout = 'ajax';
        if (!empty($id)) {
            $consignmentReturn = $this->ConsignmentReturn->read(null, $id);
            if (!empty($consignmentReturn)) {
                $consignmentReturnDetails = ClassRegistry::init('ConsignmentReturnDetail')->find("all", array('conditions' => array('ConsignmentReturnDetail.consignment_return_id' => $id)));
                $this->set(compact('consignmentReturn', 'consignmentReturnDetails'));
            } else {
                exit;
            }
        } else {
            exit;
        }
    }
    
    function receive($id){
        $this->layout = 'ajax';
        if (!$id) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $user = $this->getCurrentUser();
        if (!empty($this->data)) {
            $result = array();
            $result['error'] = 0;
            $consignmentReturn = $this->ConsignmentReturn->read(null, $this->data['consignment_return_id']);
            if ($consignmentReturn['ConsignmentReturn']['status'] == 1) {
                $checkErrorStock = 1;
                $consignmentReturnDetails = ClassRegistry::init('ConsignmentReturnDetail')->find("all", array('conditions' => array('ConsignmentReturnDetail.consignment_return_id' => $this->data['consignment_return_id'])));
                $sqlInventory = mysql_query("SELECT SUM(qty) AS qty, product_id, location_group_id, location_id, lots_number, expired_date FROM stock_orders WHERE consignment_return_id = ".$consignmentReturn['ConsignmentReturn']['id']." GROUP BY product_id, location_group_id, location_id, lots_number, expired_date");
                while($invInfo = mysql_fetch_array($sqlInventory)){
                    // Get Total Qty in Stock
                    $totalStockAv = 0;
                    $sqlInv = mysql_query("SELECT SUM(total_qty) FROM `".$invInfo['location_group_id']."_group_totals` WHERE product_id = ".$invInfo['product_id']." AND location_id = ".$invInfo['location_id']." AND lots_number = '".$invInfo['lots_number']."' AND expired_date = '".$invInfo['expired_date']."'");
                    while($rInv=mysql_fetch_array($sqlInv)){
                        $totalStockAv = $rInv[0];
                    }
                    // Get Total Qty in Order
                    $totalOrder = 0;
                    $sqlOrder = mysql_query("SELECT sum(sor.qty) as total_order FROM `stock_orders` as sor WHERE sor.consignment_return_id = ".$consignmentReturn['ConsignmentReturn']['id']." AND sor.product_id = ".$invInfo['product_id']." AND sor.lots_number = '".$invInfo['lots_number']."' AND sor.expired_date = '".$invInfo['expired_date']."' AND sor.location_group_id = ".$invInfo['location_group_id']." AND date = '".$consignmentReturn['ConsignmentReturn']['date']."' GROUP BY sor.product_id");
                    while($rOrder = mysql_fetch_array($sqlOrder)){
                        $totalOrder = $rOrder['total_order'];
                    }
                    $totalSale       = $totalStockAv + $totalOrder;
                    $qtyOrder        = $invInfo['qty'];
                    $qtyStockCompare = $totalSale;
                    if($qtyOrder > $qtyStockCompare){
                        $checkErrorStock = 2; 
                    }
                }
                if($checkErrorStock == 1){
                    $customerLocation = ClassRegistry::init('Location')->find("first", array('conditions' => array('Location.location_group_id' => $consignmentReturn['ConsignmentReturn']['location_group_to_id'])));
                    $dateConsignmentReturn  = $consignmentReturn['ConsignmentReturn']['date'];
                    // List Sale Detail
                    foreach ($consignmentReturnDetails AS $consignmentReturnDetail) {
                        $totalOrder = $consignmentReturnDetail['ConsignmentReturnDetail']['qty'] * $consignmentReturnDetail['ConsignmentReturnDetail']['conversion'];

                        // Update Inventory Location Group (OUT)
                        $dataGroup = array();
                        $dataGroup['module_type'] = 15;
                        $dataGroup['consignment_return_id'] = $consignmentReturn['ConsignmentReturn']['id'];
                        $dataGroup['product_id']        = $consignmentReturnDetail['ConsignmentReturnDetail']['product_id'];
                        $dataGroup['location_group_id'] = $consignmentReturn['ConsignmentReturn']['location_group_id'];
                        $dataGroup['date']         = $dateConsignmentReturn;
                        $dataGroup['total_qty']    = $totalOrder;
                        $dataGroup['total_order']  = $totalOrder;
                        $dataGroup['total_free']   = 0;
                        // Update Inventory Group
                        $this->Inventory->saveGroupTotalDetail($dataGroup);
                        
                        // Update Inventory Location Group (IN)
                        $dataGroupIn = array();
                        $dataGroupIn['module_type'] = 14;
                        $dataGroupIn['consignment_return_id'] = $consignmentReturn['ConsignmentReturn']['id'];
                        $dataGroupIn['product_id']        = $consignmentReturnDetail['ConsignmentReturnDetail']['product_id'];
                        $dataGroupIn['location_group_id'] = $consignmentReturn['ConsignmentReturn']['location_group_to_id'];
                        $dataGroupIn['date']         = $dateConsignmentReturn;
                        $dataGroupIn['total_qty']    = $totalOrder;
                        $dataGroupIn['total_order']  = $totalOrder;
                        $dataGroupIn['total_free']   = 0;
                        // Update Inventory Group
                        $this->Inventory->saveGroupTotalDetail($dataGroupIn);
                    }
                    // Calculate Location, Lot, Expired Date
                    $sqlInventory = mysql_query("SELECT * FROM stock_orders WHERE consignment_return_id = ".$consignmentReturn['ConsignmentReturn']['id']);
                    while($invInfo = mysql_fetch_array($sqlInventory)){
                        // Update Inventory (Out)
                        $data = array();
                        $data['module_type']       = 15;
                        $data['consignment_return_id']    = $consignmentReturn['ConsignmentReturn']['id'];
                        $data['product_id']        = $invInfo['product_id'];
                        $data['location_id']       = $invInfo['location_id'];
                        $data['location_group_id'] = $invInfo['location_group_id'];
                        $data['lots_number']  = $invInfo['lots_number']!=''?$invInfo['lots_number']:0;
                        $data['expired_date'] = $invInfo['expired_date']!='0000-00-00'?$invInfo['expired_date']:'0000-00-00';
                        $data['date']         = $dateConsignmentReturn;
                        $data['total_qty']    = $invInfo['qty'];
                        $data['total_order']  = $invInfo['qty'];
                        $data['total_free']   = 0;
                        $data['user_id']      = $user['User']['id'];
                        $data['customer_id']  = $consignmentReturn['ConsignmentReturn']['customer_id'];
                        $data['vendor_id']    = "";
                        $data['unit_cost']    = 0;
                        $data['unit_price']   = 0;
                        // Update Invetory Location
                        $this->Inventory->saveInventory($data);
                        // Reset Stock Order
                        $this->Inventory->saveGroupQtyOrder($invInfo['location_group_id'], $invInfo['location_id'], $invInfo['product_id'], $invInfo['lots_number'], $invInfo['expired_date'], $invInfo['qty'], $dateConsignmentReturn, '-');

                        // Update Inventory (In)
                        $dataIn = array();
                        $dataIn['module_type']       = 14;
                        $dataIn['consignment_return_id']    = $consignmentReturn['ConsignmentReturn']['id'];
                        $dataIn['product_id']        = $invInfo['product_id'];
                        $dataIn['location_id']       = $customerLocation['Location']['id'];
                        $dataIn['location_group_id'] = $consignmentReturn['ConsignmentReturn']['location_group_to_id'];
                        $dataIn['lots_number']  = $invInfo['lots_number']!=''?$invInfo['lots_number']:0;
                        $dataIn['expired_date'] = $invInfo['expired_date']!='0000-00-00'?$invInfo['expired_date']:'0000-00-00';
                        $dataIn['date']         = $dateConsignmentReturn;
                        $dataIn['total_qty']    = $invInfo['qty'];
                        $dataIn['total_order']  = $invInfo['qty'];
                        $dataIn['total_free']   = 0;
                        $dataIn['user_id']      = $user['User']['id'];
                        $dataIn['customer_id']  = $consignmentReturn['ConsignmentReturn']['customer_id'];
                        $dataIn['vendor_id']    = "";
                        $dataIn['unit_cost']    = 0;
                        $dataIn['unit_price']   = 0;
                        // Update Invetory Location
                        $this->Inventory->saveInventory($dataIn);
                    }
                    // Update ConsignmentReturn
                    mysql_query("UPDATE consignment_returns SET status = 2 WHERE id = ".$consignmentReturn['ConsignmentReturn']['id']);
                    // Delete Tmp Stock Order
                    mysql_query("DELETE FROM `stock_orders` WHERE  `consignment_return_id`= " . $consignmentReturn['ConsignmentReturn']['id']);
                    $this->Helper->saveUserActivity($user['User']['id'], 'Consignment Return', 'Save Receive', $consignmentReturn['ConsignmentReturn']['id']);
                } else {
                    $this->Helper->saveUserActivity($user['User']['id'], 'Consignment Return', 'Save Receive (Out of Stock)', $consignmentReturn['ConsignmentReturn']['id']);
                    $result['error'] = 2;
                }
            } else {
                $this->Helper->saveUserActivity($user['User']['id'], 'Consignment Return', 'Save Receive (Error Status)', $consignmentReturn['ConsignmentReturn']['id']);
                $result['error'] = 1;
            }
            echo json_encode($result);
            exit;
        }
        $this->Helper->saveUserActivity($user['User']['id'], 'Consignment Return', 'Receive', $id);
        $this->data = $this->ConsignmentReturn->read(null, $id);
        if (!empty($this->data)) {
            $consignmentReturnDetails = ClassRegistry::init('ConsignmentReturnDetail')->find("all", array('conditions' => array('ConsignmentReturnDetail.consignment_return_id' => $id)));
            $this->set(compact('consignmentReturnDetails'));
        } else {
            exit;
        }
    }
    
    function consignment($companyId = null, $branchId = null, $customerId = '') {
        $this->layout = 'ajax';
        $this->set(compact('companyId', 'customerId', 'branchId'));
    }

    function consignmentAjax($companyId = null, $branchId = null, $customerId = '') {
        $this->layout = 'ajax';
        $this->set(compact('companyId', 'customerId', 'branchId'));
    }
    
    function getConsignmentReturn($id = null, $dateOrder = null, $returnId = 0){
        $this->layout = 'ajax';
        $result = array();
        if (empty($id) && empty($dateOrder)) {
            $result['error'] = 1;
            echo json_encode($result);
            exit;
        }
        include("includes/function.php");
        $consignment = ClassRegistry::init('Consignment')->read(null, $id);
        $user = $this->getCurrentUser();
        $dateNow = date("Y-m-d");
        $rowList = array();
        $rowLbl  = "";
        $index   = '';
        if((strtotime($dateOrder) < strtotime($dateNow))){
            /**
            * table MEMORY
            * default max_heap_table_size 16MB
            */
            $tableTmp = "consignment_return_tmp_inventory_".$user['User']['id'];
            mysql_query("SET max_heap_table_size = 1024*1024*1024");
            mysql_query("CREATE TABLE IF NOT EXISTS `$tableTmp` (
                            `id` bigint(20) NOT NULL AUTO_INCREMENT,
                            `date` date DEFAULT NULL,
                            `product_id` int(11) DEFAULT NULL,
                            `location_group_id` int(11) DEFAULT NULL,
                            `lots_number` VARCHAR(50) NOT NULL DEFAULT '0' COLLATE 'utf8_unicode_ci',
                            `expired_date` DATE NOT NULL,
                            `total_qty` DECIMAL(15,3) NULL DEFAULT '0',
                            PRIMARY KEY (`id`),
                            KEY `product_id` (`product_id`),
                            KEY `location_group_id` (`location_group_id`),
                            KEY `date` (`date`)
                          ) ENGINE=MEMORY DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;");
            mysql_query("TRUNCATE $tableTmp") or die(mysql_error());
            $sqlStock   = mysql_query("SELECT SUM(qty), location_group_id, lots_number, date_expired FROM inventories WHERE inventories.date <= '".$dateOrder."' AND inventories.location_group_id = {$consignment['Consignment']['location_group_to_id']} AND inventories.product_id IN (SELECT product_id FROM consignment_details WHERE consignment_id = ".$id.") GROUP BY product_id, location_group_id, lots_number, date_expired");
            while($dataTotal = mysql_fetch_array($sqlStock)){
                mysql_query("INSERT INTO $tableTmp (
                                    date,
                                    product_id,
                                    location_group_id,
                                    lots_number,
                                    expired_date,
                                    total_qty
                                ) VALUES (
                                    '" . $dateOrder . "',
                                    " . $dataTotal['product_id'] . ",
                                    " . $dataTotal['location_group_id'] . ",
                                    '" . $dataTotal['lots_number'] . "',
                                    '" . $dataTotal['date_expired'] . "',
                                    " . $dataTotal['qty'] . "
                                )") or die(mysql_error());
            }
        }
        // Get Product
        $sqlSalesDetail  = mysql_query("SELECT products.id AS product_id, products.code AS code, products.barcode AS barcode, products.name AS name, products.small_val_uom AS small_val_uom, products.price_uom_id AS price_uom_id, sd.total_qty AS qty, sd.lots_number AS lots_number, sd.expired_date AS expired_date, so.location_group_to_id AS location_group_to_id FROM consignment_receives AS sd INNER JOIN consignments AS so ON so.id = sd.consignment_id INNER JOIN products ON products.id = sd.product_id WHERE sd.consignment_id = ".$id.";");
        while($rowDetail = mysql_fetch_array($sqlSalesDetail)){
            // Get Total Qty In Stock
            $sqlInv = mysql_query("SELECT SUM(IFNULL(total_qty,0) - IFNULL(total_order,0)) AS total_qty FROM {$consignment['Consignment']['location_group_to_id']}_group_totals WHERE product_id ={$rowDetail['product_id']} AND lots_number = '{$rowDetail['lots_number']}' AND expired_date = '{$rowDetail['expired_date']}' AND location_id IN (SELECT id FROM locations WHERE location_group_id = ".$consignment['Consignment']['location_group_to_id']." GROUP BY id) GROUP BY product_id");
            $rowInv = mysql_fetch_array($sqlInv);
            $sqlInvOrder = mysql_query("SELECT sum(sor.qty) as total_order FROM `stock_orders` as sor WHERE sor.product_id = ".$rowDetail['product_id']." AND sor.consignment_return_id = ".$returnId." AND sor.location_group_id = ".$consignment['Consignment']['location_group_to_id']." AND sor.lots_number = '{$rowDetail['lots_number']}' AND sor.expired_date = '{$rowDetail['expired_date']}' GROUP BY sor.product_id");
            $rowInvOrder = mysql_fetch_array($sqlInvOrder);
            $totalInventory = ($rowInv['total_qty'] + $rowInvOrder['total_order']);
            if((strtotime($dateOrder) < strtotime($dateNow))){
                // Get Total Qty Pass
                $sqlTotalPass   = mysql_query("SELECT SUM(total_qty) AS total_qty FROM ".$tableTmp." WHERE product_id = ".$rowDetail['product_id']." AND date = '".$dateOrder."' AND location_group_id =".$consignment['Consignment']['location_group_to_id']." AND lots_number = '{$rowDetail['lots_number']}' AND expired_date = '{$rowDetail['expired_date']}'");
                $rowTotalPass   = mysql_fetch_array($sqlTotalPass);
                /** F-ID: 1100
                * Compare Total Qty in Pass and Current Date (rowTotalPass = Total Qty In Pass, totalInventory = Total Qty in Current)
                * IF PASS < CURRENT TotalQty = PASS
                * ELSE PASS >= CURRENT TotalQty = CURRENT
                */
               if($rowTotalPass['total_qty'] + $rowInvOrder['total_order'] < $totalInventory){
                   $totalInventory = $rowTotalPass['total_qty'];
               }
            }
            $totalQtyConsignmentReturn = $totalInventory;
            $totalOrder = $rowDetail['qty'];
            $isSmallSelected = 1;
            $conversion = 1;
            if($totalQtyConsignmentReturn < $totalOrder){
                $totalOrder = $totalQtyConsignmentReturn;
            }
            $index      = rand();
            $productName = str_replace('"', '&quot;', $rowDetail['name']);
            // Open Tr
            $rowLbl .= '<tr class="tblConsignmentReturnList">';
            // Index
            $rowLbl .= '<td class="first" style="width:5%; text-align: center;padding: 0px; height: 30px;">'.++$index.'</td>';
            // UPC
            $rowLbl .= '<td style="width:10%; text-align: left; padding: 5px;"><input type="text" readonly="" class="lblUPC" value="'.$rowDetail['barcode'].'" /></td>';
            // SKU
            $rowLbl .= '<td style="width:10%; text-align: left; padding: 5px;"><input type="text" readonly="" class="lblSKU" value="'.$rowDetail['code'].'" /></td>';
            // Product
            $rowLbl .= '<td style="width:23%; text-align: left; padding: 5px;">';
            $rowLbl .= '<div class="inputContainer" style="width:100%">';
            $rowLbl .= '<input type="hidden" class="totalQtyConsignmentReturn" value="'.$totalQtyConsignmentReturn.'" />';
            $rowLbl .= '<input type="hidden" class="totalQtyOrderConsignmentReturn" value="'.$totalOrder.'" />';
            $rowLbl .= '<input type="hidden" id="product_id_'.$index.'" class="product_id" value="'.$rowDetail['product_id'].'" name="product_id[]" />';
            $rowLbl .= '<input type="hidden" value="'.$conversion.'" name="conversion[]" class="conversion" />';
            $rowLbl .= '<input type="hidden" value="'.$rowDetail['small_val_uom'].'" name="small_val_uom[]" class="small_val_uom" />';
            $rowLbl .= '<input type="hidden" value="" name="note[]" class="note" />';
            $rowLbl .= '<input type="text" id="productName_'.$index.'" value="'.$productName.'" id="product" name="product[]" class="product validate[required]" style="width: 75%;" />';
            $rowLbl .= '<img alt="Note" src="'.$this->webroot.'img/button/note.png" class="noteAddCM" align="absmiddle" style="cursor: pointer;" onmouseover="Tip(\'Note\')" />';
            $rowLbl .= '</div>';
            $rowLbl .= '</td>';
            // Qty
            if($isSmallSelected == 1){
                $qty = $totalQtyConsignmentReturn;
            }
            $rowLbl .= '<td style="width:8%; text-align: center;padding: 0px;">';
            $rowLbl .= '<div class="inputContainer" style="width:100%">';
            $rowLbl .= '<input type="text" value="'.$qty.'" id="qty_'.$index.'" name="qty[]" style="width:70%;" class="qty interger" />';
            $rowLbl .= '</div>';
            $rowLbl .= '</td>';
            // UOM
            $query=mysql_query("SELECT id,name,abbr,1 AS conversion FROM uoms WHERE id=".$rowDetail['price_uom_id']."
                                UNION
                                SELECT id,name,abbr,(SELECT value FROM uom_conversions WHERE is_active=1 AND from_uom_id=".$rowDetail['price_uom_id']." AND to_uom_id=uoms.id) AS conversion FROM uoms WHERE id IN (SELECT to_uom_id FROM uom_conversions WHERE is_active=1 AND from_uom_id=".$rowDetail['price_uom_id'].")
                                ORDER BY conversion ASC");
            $i = 1;
            $length = mysql_num_rows($query);
            $optionUom = "";
            while($data=mysql_fetch_array($query)){
                $priceLbl   = "";
                $selected   = "";
                $isMain     = "other";
                $isSmall    = 0;
                // Check With Product UOM Id
                if($data['id'] == $rowDetail['price_uom_id']){
                    $isMain = "first";
                }
                // Check Is Small UOM
                if($length == $i && $isSmallSelected == 1){
                    $isSmall  = 1;
                    $selected = ' selected="selected" ';
                }
                // Get Price
                $sqlPrice = mysql_query("SELECT products.unit_cost, product_prices.price_type_id, product_prices.amount, product_prices.percent, product_prices.add_on, product_prices.set_type FROM product_prices INNER JOIN products ON products.id = product_prices.product_id WHERE product_prices.product_id =".$rowDetail['product_id']." AND product_prices.uom_id =".$data['id']);
                if(@mysql_num_rows($sqlPrice)){
                    $price = 0;
                    while($rowPrice = mysql_fetch_array($sqlPrice)){
                        $unitCost = $rowPrice['unit_cost'] /  $data['conversion'];
                        if($rowPrice['set_type'] == 1){
                            $price = $rowPrice['amount'];
                        }else if($rowPrice['set_type'] == 2){
                            $percent = ($unitCost * $rowPrice['percent']) / 100;
                            $price = $unitCost + $percent;
                        }else if($rowPrice['set_type'] == 3){
                            $price = $unitCost + $rowPrice['add_on'];
                        }
                        $priceLbl .= 'price-uom-'.$rowPrice['price_type_id'].'="'.$price.'" ';
                    }
                }else{
                    $priceLbl .= 'price-uom-1="0" price-uom-2="0"';
                }
                $optionUom .= '<option '.$priceLbl.' '.$selected.' data-sm="'.$isSmall.'" data-item="'.$isMain.'" value="'.$data['id'].'" conversion="'.$data['conversion'].'">'.$data['name'].'</option>';
                $i++;
            }
            $rowLbl .= '<td style="width:15%; padding: 0px; text-align: center">';
            $rowLbl .= '<div class="inputContainer" style="width:100%">';
            $rowLbl .= '<select id="qty_uom_id_'.$index.'" style="width:80%; height: 20px;" name="qty_uom_id[]" class="qty_uom_id validate[required]">'.$optionUom.'</select>';
            $rowLbl .= '</div>';
            $rowLbl .= '</td>';
            // Lots Number
            $lotsNumber = '';
            if($rowDetail['lots_number'] != '0' && $rowDetail['lots_number'] != ''){
                $lotsNumber = $rowDetail['lots_number'];
            }
            $rowLbl .= '<td style="width:11%; padding: 0px; text-align: center">';
            $rowLbl .= '<div class="inputContainer" style="width:100%">';
            $rowLbl .= $lotsNumber;
            $rowLbl .= '<input type="hidden" value="'.$rowDetail['lots_number'].'" name="lots_number[]" />';
            $rowLbl .= '</div>';
            $rowLbl .= '</td>';
            // Expiry Date
            $expriryDate = '';
            if($rowDetail['expired_date'] != '' && $rowDetail['expired_date'] != '0000-00-00'){
                $expriryDate = dateShort($rowDetail['expired_date']);
            }
            $rowLbl .= '<td style="width:11%; text-align: center; padding: 0px;">';
            $rowLbl .= '<div class="inputContainer" style="width:100%">';
            $rowLbl .= $expriryDate;
            $rowLbl .= '<input type="hidden" value="'.$rowDetail['expired_date'].'" name="expired_date[]" />';
            $rowLbl .= '</div>';
            $rowLbl .= '</td>';
            // Button Remove
            $rowLbl .= '<td style="width:7%">';
            $rowLbl .= '<img alt="Remove" src="'.$this->webroot.'img/button/cross.png" class="btnRemoveConsignmentReturn" align="absmiddle" style="cursor: pointer;" onmouseover="Tip(\'Remove\')" />';
            $rowLbl .= '&nbsp; <img alt="Up" src="'.$this->webroot.'img/button/move_up.png" class="btnMoveUpConsignmentReturn" align="absmiddle" style="cursor: pointer;" onmouseover="Tip(\'Up\')" />';
            $rowLbl .= '&nbsp; <img alt="Down" src="'.$this->webroot . 'img/button/move_down.png" class="btnMoveDownConsignmentReturn" align="absmiddle" style="cursor: pointer;" onmouseover="Tip(\'Down\')" />';
            $rowLbl .= '</td>';
            // Close Tr
            $rowLbl .= '</tr>';
        }
        $rowList['error']  = 0;
        $rowList['result'] = $rowLbl;
        echo json_encode($rowList);
        exit;
    }

}

?>