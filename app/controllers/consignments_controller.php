<?php

class ConsignmentsController extends AppController {

    var $name = 'Consignments';
    var $components = array('Helper', 'Inventory');
    
    function viewByUser() {
        $this->layout = 'ajax';
    }

    function index() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        $this->Helper->saveUserActivity($user['User']['id'], 'Consignment', 'Dashboard');
    }

    function ajax($customer = 'all', $filterStatus = 'all', $date = '') {
        $this->layout = 'ajax';
        $this->set(compact('customer', 'filterStatus', 'company', 'date'));
    }

    function view($id = null) {
        $this->layout = 'ajax';
        if (!empty($id)) {
            $user = $this->getCurrentUser();
            $this->data = $this->Consignment->read(null, $id);
            if (!empty($this->data)) {
                $this->Helper->saveUserActivity($user['User']['id'], 'Consignment', 'View', $id);
                $consignmentDetails = ClassRegistry::init('ConsignmentDetail')->find("all", array('conditions' => array('ConsignmentDetail.consignment_id' => $id)));
                $this->set(compact('consignmentDetails'));
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
            for ($i = 0; $i < sizeof($_POST['product']); $i++) {
                if($_POST['product_id'][$i] != ""){
                    if (array_key_exists($_POST['product_id'][$i], $productOrder)){
                        $productOrder[$_POST['product_id'][$i]]['qty'] += $this->Helper->replaceThousand($_POST['qty'][$i] * $_POST['conversion'][$i]);
                    } else {
                        $productOrder[$_POST['product_id'][$i]]['qty'] = $this->Helper->replaceThousand($_POST['qty'][$i] * $_POST['conversion'][$i]);
                    }
                }
            }
            // Check Qty in Stock Before Save
            // Get Loction Setting
            $locSetting = ClassRegistry::init('LocationSetting')->findById(4);
            $locCon     = '';
            if($locSetting['LocationSetting']['location_status'] == 1){
                $locCon = ' AND is_for_sale = 1';
            }
            foreach($productOrder AS $key => $order){
                // Get Total Qty In Stock
                $totalStockAv = 0;
                $sqlInv = mysql_query("SELECT SUM(total_qty - total_order) FROM `".$this->data['Consignment']['location_group_id']."_group_totals` WHERE product_id = ".$key." AND location_group_id = " . $this->data['Consignment']['location_group_id']." AND location_id IN (SELECT id FROM locations WHERE location_group_id = ".$this->data['Consignment']['location_group_id'].$locCon.")");
                while($rInv=mysql_fetch_array($sqlInv)){
                    $totalStockAv = $rInv[0];
                }
                $qtyOrder  = $order['qty'];
                if($qtyOrder > $totalStockAv){
                    $checkErrorStock = 2; 
                    $listOutStock .= $key."|".$totalStockAv."-";
                }
            }
            if($checkErrorStock == 1){
                $result = array();
                // Load Model
                $this->loadModel('ConsignmentTermCondition');
                $this->loadModel('ConsignmentDetail');
                $this->loadModel('StockOrder');
                
                $this->Consignment->create();
                $consignment = array();
                $consignment['Consignment']['code']              = $this->data['Consignment']['code'];
                $consignment['Consignment']['company_id']        = $this->data['Consignment']['company_id'];
                $consignment['Consignment']['branch_id']         = $this->data['Consignment']['branch_id'];
                $consignment['Consignment']['location_group_id'] = $this->data['Consignment']['location_group_id'];
                $consignment['Consignment']['customer_id']       = $this->data['Consignment']['customer_id'];
                $consignment['Consignment']['customer_contact_id'] = $this->data['Consignment']['customer_contact_id'];
                $consignment['Consignment']['currency_id']  = $this->data['Consignment']['currency_id'];
                $consignment['Consignment']['sales_rep_id']  = $this->data['Consignment']['sales_rep_id'];
                $consignment['Consignment']['total_amount'] = $this->data['Consignment']['total_amount'];
                $consignment['Consignment']['date']   = $this->data['Consignment']['date'];
                $consignment['Consignment']['note']   = $this->data['Consignment']['note'];
                $consignment['Consignment']['price_type_id'] = $this->data['Consignment']['price_type_id'];
                $consignment['Consignment']['created_by'] = $user['User']['id'];
                $consignment['Consignment']['status'] = 1;
                if ($this->Consignment->save($consignment)) {
                    $result['consign_id'] = $consignmentId = $this->Consignment->id;
                    // Get Module Code
                    $modCode = $this->Helper->getModuleCode($this->data['Consignment']['code'], $consignmentId, 'code', 'consignments', 'status != -1 AND branch_id = '.$this->data['Consignment']['branch_id']);
                    // Updaet Module Code
                    mysql_query("UPDATE consignments SET code = '".$modCode."' WHERE id = ".$consignmentId);
                    // Insert Term & Condition
                    if(!empty($_POST['term_condition_type_id'])){
                        for ($i = 0; $i < sizeof($_POST['term_condition_type_id']); $i++) {
                            if(!empty($_POST['term_condition_id'][$i])){
                                $termCondition = array();
                                // Term Condition
                                $this->ConsignmentTermCondition->create();
                                $termCondition['ConsignmentTermCondition']['consignment_id'] = $consignmentId;
                                $termCondition['ConsignmentTermCondition']['term_condition_type_id'] = $_POST['term_condition_type_id'][$i];
                                $termCondition['ConsignmentTermCondition']['term_condition_id'] = $_POST['term_condition_id'][$i];
                                $this->ConsignmentTermCondition->save($termCondition);
                            }
                        }
                    }
                    for ($i = 0; $i < sizeof($_POST['product']); $i++) {
                        if (!empty($_POST['product_id'][$i])) {
                            /* Sales Order Detail */
                            $consignmentDetail = array();
                            $this->ConsignmentDetail->create();
                            $consignmentDetail['ConsignmentDetail']['consignment_id']   = $consignmentId;
                            $consignmentDetail['ConsignmentDetail']['product_id']  = $_POST['product_id'][$i];
                            $consignmentDetail['ConsignmentDetail']['qty_uom_id']  = $_POST['qty_uom_id'][$i];
                            $consignmentDetail['ConsignmentDetail']['qty']         = $_POST['qty'][$i];
                            $consignmentDetail['ConsignmentDetail']['unit_price']  = $_POST['unit_price'][$i];
                            $consignmentDetail['ConsignmentDetail']['total_price'] = $_POST['total_price'][$i];
                            $consignmentDetail['ConsignmentDetail']['conversion']  = $_POST['conversion'][$i];
                            $consignmentDetail['ConsignmentDetail']['note']  = $_POST['note'][$i];
                            $this->ConsignmentDetail->save($consignmentDetail);
                            $consignmentDetailId = $this->ConsignmentDetail->id;
                            $conLocInv  = "IN (SELECT id FROM locations WHERE location_group_id = ".$consignment['Consignment']['location_group_id'].")";
                            $invInfos   = array();
                            $index      = 0;
                            $totalOrder = $_POST['qty'][$i] * $_POST['conversion'][$i];
                            // Calculate Location, Lot, Expired Date
                            $sqlInventory = mysql_query("SELECT SUM(IFNULL(group_totals.total_qty,0) - IFNULL(group_totals.total_order,0)) AS total_qty, group_totals.location_id AS location_id, group_totals.lots_number AS lots_number, group_totals.expired_date AS expired_date FROM ".$consignment['Consignment']['location_group_id']."_group_totals AS group_totals WHERE group_totals.location_id ".$conLocInv." AND group_totals.product_id = ".$_POST['product_id'][$i]." GROUP BY group_totals.location_id, group_totals.product_id, group_totals.lots_number, group_totals.expired_date HAVING total_qty > 0 ORDER BY group_totals.expired_date, group_totals.lots_number, group_totals.total_qty ASC");
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
                                $tmpDelivey['StockOrder']['consignment_id'] = $consignmentId;
                                $tmpDelivey['StockOrder']['consignment_detail_id'] = $consignmentDetailId;
                                $tmpDelivey['StockOrder']['product_id']    = $_POST['product_id'][$i];
                                $tmpDelivey['StockOrder']['location_group_id']   = $consignment['Consignment']['location_group_id'];
                                $tmpDelivey['StockOrder']['location_id']   = $invInfo['location_id'];
                                $tmpDelivey['StockOrder']['lots_number']   = $invInfo['lots_number'];
                                $tmpDelivey['StockOrder']['expired_date']  = $invInfo['expired_date'];
                                $tmpDelivey['StockOrder']['date']  = $consignment['Consignment']['date'];
                                $tmpDelivey['StockOrder']['qty'] = $invInfo['total_qty'];
                                $this->StockOrder->save($tmpDelivey);
                                $this->Inventory->saveGroupQtyOrder($consignment['Consignment']['location_group_id'], $invInfo['location_id'], $_POST['product_id'][$i], $invInfo['lots_number'], $invInfo['expired_date'], $invInfo['total_qty'], $consignment['Consignment']['date'], '+');
                            }
                        }
                    }
                    $this->Helper->saveUserActivity($user['User']['id'], 'Consignment', 'Save Add New', $consignmentId);
                    echo json_encode($result);
                    exit;
                } else {
                    $this->Helper->saveUserActivity($user['User']['id'], 'Consignment', 'Save Add New (Error)');
                    $result['code'] = 2;
                    echo json_encode($result);
                    exit;
                }
            }else{
                $this->Helper->saveUserActivity($user['User']['id'], 'Consignment', 'Save Add New (Error Out of Stock)');
                // Error Out of Stock
                $result['listOutStock'] = $listOutStock;
                $result['code'] = 3;
                echo json_encode($result);
                exit;
            }
        }
        $this->Helper->saveUserActivity($user['User']['id'], 'Consignment', 'Add New');
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
                            'fields' => array('Branch.id', 'Branch.name', 'Branch.company_id', 'ModuleCodeBranch.cus_consign_code', 'Branch.currency_id', 'Currency.symbol'),
                            'conditions' => array('Branch.is_active = 1', 'user_branches.user_id=' . $user['User']['id'])
                        ));
        $locationGroups = ClassRegistry::init('LocationGroup')->find('list', array('fields' => array('LocationGroup.id', 'LocationGroup.name'),'joins' => array($joinUsers, $joinLocation),'conditions' => array('user_location_groups.user_id=' . $user['User']['id'], 'LocationGroup.is_active' => '1', 'LocationGroup.location_group_type_id != 1'), 'group' => 'LocationGroup.id'));
        $this->set(compact("locationGroups","companies","branches"));
    }

    function orderDetails() {
        $this->layout = 'ajax';
        $uoms = ClassRegistry::init('Uom')->find('all', array('fields' => array('Uom.id', 'Uom.name'), 'conditions' => array('Uom.is_active' => 1)));
        $this->set(compact('uoms'));
    }

    function product($companyId = null, $locationGroupId = null, $branchId = null, $saleOderId = null) {
        $this->layout = 'ajax';
        $this->set('orderDate', $_POST['order_date']);
        $this->set(compact('companyId', 'branchId', 'locationGroupId', 'saleOderId'));
    }

    function product_ajax($companyId, $locationGroupId, $branchId, $orderDate, $category = null) {
        $this->layout = 'ajax';
        $this->set('saleOderId', $_GET['sale_order_id']);
        $this->set(compact('companyId', 'branchId', 'locationGroupId', 'orderDate', 'category'));
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
        $userPermission = 'Customer.id IN (SELECT customer_id FROM customer_companies WHERE company_id IN (SELECT company_id FROM user_companies WHERE user_id ='.$user['User']['id'].'))';
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
    
    function employee($companyId) {
        $this->layout = 'ajax';
        if(!empty($companyId)){
            $this->set('companyId', $companyId);
        }else{
            exit;
        }
    }

    function employeeAjax($companyId, $group = null) {
        $this->layout = 'ajax';
        if(!empty($companyId)){
            $this->set('companyId', $companyId);
            $this->set('group', $group);
        }else{
            exit;
        }
    }

    function void($id = null) {
        if (!$id) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $user = $this->getCurrentUser();
        $consignment = $this->Consignment->read(null, $id);
        if($consignment['Consignment']['status'] == 1){
            $this->Consignment->updateAll(
                    array('Consignment.status' => 0, 'Consignment.modified_by' => $user['User']['id']),
                    array('Consignment.id' => $id)
            );
            // Reset Stock Order
            $sqlResetOrder = mysql_query("SELECT * FROM stock_orders WHERE `consignment_id`=".$id.";");
            while($rowResetOrder = mysql_fetch_array($sqlResetOrder)){
                $this->Inventory->saveGroupQtyOrder($rowResetOrder['location_group_id'], $rowResetOrder['location_id'], $rowResetOrder['product_id'], $rowResetOrder['lots_number'], $rowResetOrder['expired_date'], $rowResetOrder['qty'], $rowResetOrder['date'], '-');
            }
            // Detele Tmp Stock Order
            mysql_query("DELETE FROM `stock_orders` WHERE  `consignment_id`=".$id.";");
            $this->Helper->saveUserActivity($user['User']['id'], 'Consignment', 'Void', $id);
            echo MESSAGE_DATA_HAS_BEEN_DELETED;
            exit;
        } else {
            $this->Helper->saveUserActivity($user['User']['id'], 'Consignment', 'Void (Error Status)', $id);
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
            $productOrder    = array();
            for ($i = 0; $i < sizeof($_POST['product']); $i++) {
                if($_POST['product_id'][$i] != ""){
                    if (array_key_exists($_POST['product_id'][$i], $productOrder)){
                        $productOrder[$_POST['product_id'][$i]]['qty'] += $this->Helper->replaceThousand($_POST['qty'][$i] * $_POST['conversion'][$i]);
                    } else {
                        $productOrder[$_POST['product_id'][$i]]['qty'] = $this->Helper->replaceThousand($_POST['qty'][$i] * $_POST['conversion'][$i]);
                    }
                }
            }
            // Check Qty in Stock Before Save
            // Get Loction Setting
            $locSetting = ClassRegistry::init('LocationSetting')->findById(4);
            $locCon     = '';
            if($locSetting['LocationSetting']['location_status'] == 1){
                $locCon = ' AND is_for_sale = 1';
            }
            foreach($productOrder AS $key => $order){
                // Get Total Qty in Stock
                $totalStockAv = 0;
                $sqlInv = mysql_query("SELECT SUM(total_qty) FROM `".$this->data['Consignment']['location_group_id']."_group_totals` WHERE product_id = ".$key." AND location_id IN (SELECT id FROM locations WHERE location_group_id = " . $this->data['Consignment']['location_group_id'].$locCon.")");
                while($rInv=mysql_fetch_array($sqlInv)){
                    $totalStockAv = $rInv[0];
                }
                // Get Total Qty in Order
                $totalOrder = 0;
                $sqlOrder = mysql_query("SELECT sum(sor.qty) as total_order FROM `stock_orders` as sor WHERE sor.consignment_id = ".$id." AND sor.product_id = ".$key." AND sor.location_group_id = ".$this->data['Consignment']['location_group_id']." AND date = '".$this->data['Consignment']['date']."' GROUP BY sor.product_id");
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
                $consignment = $this->Consignment->read(null, $id);
                if ($consignment['Consignment']['status'] == 1) {
                    $result = array();
                    $statuEdit = "-1";
                    // Load Model
                    $this->loadModel('ConsignmentTermCondition');
                    $this->loadModel('StockOrder');
                    $this->loadModel('ConsignmentDetail');
                    // Reset Stock Order
                    $sqlResetOrder = mysql_query("SELECT * FROM stock_orders WHERE `consignment_id`=".$id.";");
                    while($rowResetOrder = mysql_fetch_array($sqlResetOrder)){
                        $this->Inventory->saveGroupQtyOrder($rowResetOrder['location_group_id'], $rowResetOrder['location_id'], $rowResetOrder['product_id'], $rowResetOrder['lots_number'], $rowResetOrder['expired_date'], $rowResetOrder['qty'], $rowResetOrder['date'], '-');
                    }
                    // Detele Tmp Stock Order
                    mysql_query("DELETE FROM `stock_orders` WHERE  `consignment_id`=".$id.";");
                    // Update Status Edit
                    $this->Consignment->updateAll(
                            array('Consignment.status' => $statuEdit, 'Consignment.modified_by' => $user['User']['id']),
                            array('Consignment.id' => $id)
                    );
                    
                    $this->Consignment->create();
                    $consignment = array();
                    $consignment['Consignment']['code']              = $this->data['Consignment']['code'];
                    $consignment['Consignment']['company_id']        = $this->data['Consignment']['company_id'];
                    $consignment['Consignment']['branch_id']         = $this->data['Consignment']['branch_id'];
                    $consignment['Consignment']['location_group_id'] = $this->data['Consignment']['location_group_id'];
                    $consignment['Consignment']['customer_id']       = $this->data['Consignment']['customer_id'];
                    $consignment['Consignment']['customer_contact_id'] = $this->data['Consignment']['customer_contact_id'];
                    $consignment['Consignment']['currency_id']  = $this->data['Consignment']['currency_id'];
                    $consignment['Consignment']['sales_rep_id']  = $this->data['Consignment']['sales_rep_id'];
                    $consignment['Consignment']['total_amount'] = $this->data['Consignment']['total_amount'];
                    $consignment['Consignment']['date']   = $this->data['Consignment']['date'];
                    $consignment['Consignment']['note']   = $this->data['Consignment']['note'];
                    $consignment['Consignment']['price_type_id'] = $this->data['Consignment']['price_type_id'];
                    $consignment['Consignment']['created_by'] = $user['User']['id'];
                    $consignment['Consignment']['status'] = 1;
                    if ($this->Consignment->save($consignment)) {
                        $result['consign_id'] = $consignmentId = $this->Consignment->id;
                        if($this->data['Consignment']['branch_id'] != $consignment['Consignment']['branch_id']){
                            // Get Module Code
                            $modCode = $this->Helper->getModuleCode($this->data['Consignment']['code'], $consignmentId, 'code', 'consignments', 'status != -1 AND branch_id = '.$this->data['Consignment']['branch_id']);
                            // Updaet Module Code
                            mysql_query("UPDATE consignments SET code = '".$modCode."' WHERE id = ".$consignmentId);
                        }
                        // Insert Term & Condition
                        if(!empty($_POST['term_condition_type_id'])){
                            for ($i = 0; $i < sizeof($_POST['term_condition_type_id']); $i++) {
                                if(!empty($_POST['term_condition_id'][$i])){
                                    $termCondition = array();
                                    // Term Condition
                                    $this->ConsignmentTermCondition->create();
                                    $termCondition['ConsignmentTermCondition']['consignment_id'] = $consignmentId;
                                    $termCondition['ConsignmentTermCondition']['term_condition_type_id'] = $_POST['term_condition_type_id'][$i];
                                    $termCondition['ConsignmentTermCondition']['term_condition_id'] = $_POST['term_condition_id'][$i];
                                    $this->ConsignmentTermCondition->save($termCondition);
                                }
                            }
                        }
                        for ($i = 0; $i < sizeof($_POST['product']); $i++) {
                            if (!empty($_POST['product_id'][$i])) {
                                /* Sales Order Detail */
                                $consignmentDetail = array();
                                $this->ConsignmentDetail->create();
                                $consignmentDetail['ConsignmentDetail']['consignment_id']   = $consignmentId;
                                $consignmentDetail['ConsignmentDetail']['product_id']  = $_POST['product_id'][$i];
                                $consignmentDetail['ConsignmentDetail']['qty_uom_id']  = $_POST['qty_uom_id'][$i];
                                $consignmentDetail['ConsignmentDetail']['qty']         = $_POST['qty'][$i];
                                $consignmentDetail['ConsignmentDetail']['unit_price']  = $_POST['unit_price'][$i];
                                $consignmentDetail['ConsignmentDetail']['total_price'] = $_POST['total_price'][$i];
                                $consignmentDetail['ConsignmentDetail']['conversion']  = $_POST['conversion'][$i];
                                $consignmentDetail['ConsignmentDetail']['note']  = $_POST['note'][$i];
                                $this->ConsignmentDetail->save($consignmentDetail);
                                $consignmentDetailId = $this->ConsignmentDetail->id;
                                $conLocInv  = "IN (SELECT id FROM locations WHERE location_group_id = ".$consignment['Consignment']['location_group_id'].")";
                                $invInfos   = array();
                                $index      = 0;
                                $totalOrder = $_POST['qty'][$i] * $_POST['conversion'][$i];
                                // Calculate Location, Lot, Expired Date
                                $sqlInventory = mysql_query("SELECT SUM(IFNULL(group_totals.total_qty,0) - IFNULL(group_totals.total_order,0)) AS total_qty, group_totals.location_id AS location_id, group_totals.lots_number AS lots_number, group_totals.expired_date AS expired_date FROM ".$consignment['Consignment']['location_group_id']."_group_totals AS group_totals WHERE group_totals.location_id ".$conLocInv." AND group_totals.product_id = ".$_POST['product_id'][$i]." GROUP BY group_totals.location_id, group_totals.product_id, group_totals.lots_number, group_totals.expired_date HAVING total_qty > 0 ORDER BY group_totals.expired_date, group_totals.lots_number, group_totals.total_qty ASC");
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
                                    $tmpDelivey['StockOrder']['consignment_id'] = $consignmentId;
                                    $tmpDelivey['StockOrder']['consignment_detail_id'] = $consignmentDetailId;
                                    $tmpDelivey['StockOrder']['product_id']    = $_POST['product_id'][$i];
                                    $tmpDelivey['StockOrder']['location_group_id']   = $consignment['Consignment']['location_group_id'];
                                    $tmpDelivey['StockOrder']['location_id']   = $invInfo['location_id'];
                                    $tmpDelivey['StockOrder']['lots_number']   = $invInfo['lots_number'];
                                    $tmpDelivey['StockOrder']['expired_date']  = $invInfo['expired_date'];
                                    $tmpDelivey['StockOrder']['date']  = $consignment['Consignment']['date'];
                                    $tmpDelivey['StockOrder']['qty'] = $invInfo['total_qty'];
                                    $this->StockOrder->save($tmpDelivey);
                                    $this->Inventory->saveGroupQtyOrder($consignment['Consignment']['location_group_id'], $invInfo['location_id'], $_POST['product_id'][$i], $invInfo['lots_number'], $invInfo['expired_date'], $invInfo['total_qty'], $consignment['Consignment']['date'], '+');
                                }
                            }
                        }
                        $this->Helper->saveUserActivity($user['User']['id'], 'Consignment', 'Save Edit', $id, $consignmentId);
                        echo json_encode($result);
                        exit;
                    } else {
                        $this->Helper->saveUserActivity($user['User']['id'], 'Consignment', 'Save Edit (Error)', $id);
                        // Error Saves
                        $result['error'] = 2;
                        echo json_encode($result);
                        exit;
                    }
                } else {
                    // Error Saves
                    $this->Helper->saveUserActivity($user['User']['id'], 'Consignment', 'Save Edit (Error Status)', $id);
                    $result['error'] = 2;
                    echo json_encode($result);
                    exit;
                }
            }else{
                $this->Helper->saveUserActivity($user['User']['id'], 'Consignment', 'Save Edit (Error Out of Stock)', $id);
                // Error Out Of Stock
                $result['listOutStock'] = $listOutStock;
                $result['code'] = '3';
                echo json_encode($result);
                exit;
            }
        }

        if (!empty($id)) {
            $this->Helper->saveUserActivity($user['User']['id'], 'Consignment', 'Edit', $id);
            $this->data = $this->Consignment->read(null, $id);
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
                                'fields' => array('Branch.id', 'Branch.name', 'Branch.company_id', 'ModuleCodeBranch.cus_consign_code', 'Branch.currency_id', 'Currency.symbol'),
                                'conditions' => array('Branch.is_active = 1', 'user_branches.user_id=' . $user['User']['id'])
                            ));
            $locationGroups = ClassRegistry::init('LocationGroup')->find('list', array('fields' => array('LocationGroup.id', 'LocationGroup.name'),'joins' => array($joinUsers, $joinLocation),'conditions' => array('user_location_groups.user_id=' . $user['User']['id'], 'LocationGroup.is_active' => '1', 'LocationGroup.location_group_type_id != 1'), 'group' => 'LocationGroup.id'));
            $this->set(compact("locationGroups","companies","branches"));
        }else{
            exit;
        }
    }
    
    function editDetail($consignmentId = null) {
        $this->layout = 'ajax';
        if ($consignmentId >= 0) {
            $consignment         = ClassRegistry::init('Consignment')->find("first", array('conditions' => array('Consignment.id' => $consignmentId)));
            $consignmentDetails  = ClassRegistry::init('ConsignmentDetail')->find("all", array('conditions' => array('ConsignmentDetail.consignment_id' => $consignmentId)));
            $uoms = ClassRegistry::init('Uom')->find('all', array('fields' => array('Uom.id', 'Uom.name'), 'conditions' => array('Uom.is_active' => 1)));
            $locSetting = ClassRegistry::init('LocationSetting')->findById(4);
            $this->set(compact('consignment', 'consignmentDetails', 'uoms', 'locSetting'));
        } else {
            exit;
        }
    }

    function searchProduct() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        $joinProductBranch  = array(
                             'table' => 'product_branches',
                             'type' => 'INNER',
                             'alias' => 'ProductBranch',
                             'conditions' => array(
                                 'ProductBranch.product_id = Product.id',
                                 'ProductBranch.branch_id IN (SELECT branch_id FROM user_branches WHERE user_id = '.$user['User']['id'].')'
                             ));
        $joinProductgroup  = array(
                             'table' => 'product_pgroups',
                             'type' => 'INNER',
                             'alias' => 'ProductPgroup',
                             'conditions' => array('ProductPgroup.product_id = Product.id')
                             );
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
                                'Product.code LIKE' => '%' . $this->params['url']['q'] . '%',
                                'Product.barcode LIKE' => '%' . $this->params['url']['q'] . '%',
                                'Product.name LIKE' => '%' . $this->params['url']['q'] . '%',
                            ), 'Product.company_id IN (SELECT company_id FROM user_companies WHERE user_id = '.$user['User']['id'].')'
                            , 'Product.is_active' => 1
                            , '((Product.price_uom_id IS NOT NULL AND Product.is_packet = 0) OR (Product.price_uom_id IS NULL AND Product.is_packet = 1))'
                        ),
                        'joins' => $joins,
                        'group' => array(
                            'Product.id'
                        )
                    ));
        $this->set(compact('products'));
    }

    function searchProductByCode($company_id, $customerId, $branchId, $consignmentId = null) {
        $this->layout = 'ajax';
        $product_code       = !empty($this->data['code']) ? $this->data['code'] : "";
        $location_group_id  = $this->data['location_group_id'];
        $dateOrder = $this->data['order_date'];
        $user      = $this->getCurrentUser();
        // Get Loction Setting
        $locSetting = ClassRegistry::init('LocationSetting')->findById(4);
        $locCon     = '';
        if($locSetting['LocationSetting']['location_status'] == 1){
            $locCon = ' AND is_for_sale = 1';
        }
        $joinProductBranch  = array(
                             'table' => 'product_branches',
                             'type' => 'INNER',
                             'alias' => 'ProductBranch',
                             'conditions' => array(
                                 'ProductBranch.product_id = Product.id',
                                 'ProductBranch.branch_id' => $branchId
                             ));
        $joinInventory  = array(
                             'table' => $location_group_id.'_group_totals',
                             'type' => 'LEFT',
                             'alias' => 'InventoryTotal',
                             'conditions' => array(
                                 'InventoryTotal.product_id = Product.id',
                                 'InventoryTotal.location_group_id' => $location_group_id,
                                 '(InventoryTotal.total_qty) > 0',
                                 'InventoryTotal.location_id IN (SELECT id FROM locations WHERE location_group_id = '.$location_group_id.' AND is_active = 1'.$locCon.')'
                             )
                          );
        $joinProductgroup  = array(
                             'table' => 'product_pgroups',
                             'type' => 'INNER',
                             'alias' => 'ProductPgroup',
                             'conditions' => array('ProductPgroup.product_id = Product.id')
                             );
        $joinPgroup  = array(
                             'table' => 'pgroups',
                             'type' => 'INNER',
                             'alias' => 'Pgroup',
                             'conditions' => array(
                                 'Pgroup.id = ProductPgroup.pgroup_id',
                                 '(Pgroup.user_apply = 0 OR (Pgroup.user_apply = 1 AND Pgroup.id IN (SELECT pgroup_id FROM user_pgroups WHERE user_id = '.$user['User']['id'].')))'
                             )
                          );
        $groupBy       = "Product.id, InventoryTotal.product_id";
        $joins = array(
            $joinInventory,
            $joinProductgroup,
            $joinPgroup,
            $joinProductBranch
        );

        $product = ClassRegistry::init('Product')->find('first', array(
                    'fields' => array(
                        'Product.id',
                        'Product.code',
                        'Product.barcode',
                        'Product.name',
                        'Product.small_val_uom',
                        'Product.price_uom_id',
                        'Product.is_packet',
                        'SUM(InventoryTotal.total_qty - InventoryTotal.total_order) AS total_qty',
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
                        "((is_not_for_sale = 0 AND period_from IS NULL AND period_to IS NULL) OR (is_not_for_sale = 0 AND period_from <= '".$dateOrder."' AND period_to >= '".$dateOrder."') OR (is_not_for_sale = 1 AND period_from IS NOT NULL AND period_to IS NOT NULL AND '".$dateOrder."' NOT BETWEEN period_from AND period_to))",
                        '((Product.price_uom_id IS NOT NULL AND Product.is_packet = 0) OR (Product.price_uom_id IS NULL AND Product.is_packet = 1))'
                    ),
                    'joins' => $joins,
                    'group' => $groupBy
                ));
        $this->set(compact('product', 'location_group_id', 'saleOrderId', 'dateOrder', 'customerId'));
    }
    
    function printInvoice($id = null){
        $this->layout = 'ajax';
        if (!empty($id)) {
            $consignment = $this->Consignment->read(null, $id);
            if (!empty($consignment)) {
                $consignmentDetails = ClassRegistry::init('ConsignmentDetail')->find("all", array('conditions' => array('ConsignmentDetail.consignment_id' => $id)));
                $this->set(compact('consignment', 'consignmentDetails'));
            } else {
                exit;
            }
        } else {
            exit;
        }
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
    
    function receive($id){
        $this->layout = 'ajax';
        if (!$id) {
            $result['error'] = 0;
            echo json_encode($result);
            exit;
        }
        $user = $this->getCurrentUser();
        if (!empty($this->data)) {
            $result = array();
            $result['error'] = 0;
            $productOrder = array();
            $consignment = $this->Consignment->read(null, $this->data['consignment_id']);
            if ($consignment['Consignment']['status'] == 1) {
                $checkErrorStock = 1;
                $consignmentDetails = ClassRegistry::init('ConsignmentDetail')->find("all", array('conditions' => array('ConsignmentDetail.consignment_id' => $this->data['consignment_id'], 'ConsignmentDetail.id NOT IN (SELECT consignment_detail_id FROM consignment_receives GROUP BY consignment_detail_id)')));
                foreach ($consignmentDetails AS $consignmentDetail) {
                    if (array_key_exists($consignmentDetail['ConsignmentDetail']['product_id'], $productOrder)){
                        $productOrder[$consignmentDetail['ConsignmentDetail']['product_id']]['qty'] += $this->Helper->replaceThousand($consignmentDetail['ConsignmentDetail']['qty'] * $consignmentDetail['ConsignmentDetail']['conversion']);
                    } else {
                        $productOrder[$consignmentDetail['ConsignmentDetail']['product_id']]['qty'] = $this->Helper->replaceThousand($consignmentDetail['ConsignmentDetail']['qty'] * $consignmentDetail['ConsignmentDetail']['conversion']);
                    }
                }
                foreach($productOrder AS $key => $order){
                    $sqlTotal = mysql_query("SELECT (SUM(IFNULL(total_qty,0) - IFNULL(total_order,0)) + (SELECT IFNULL(SUM(qty),0) FROM stock_orders WHERE product_id = ".$key." AND consignment_id = ".$consignment['Consignment']['id'].")) AS total_qty FROM ".$consignment['Consignment']['location_group_id']."_group_totals WHERE product_id = ".$key." AND location_group_id = ".$consignment['Consignment']['location_group_id']." AND location_id IN (SELECT id FROM locations WHERE location_group_id = ".$consignment['Consignment']['location_group_id'].") GROUP BY product_id;");
                    $rowTotal = mysql_fetch_array($sqlTotal);
                    if($rowTotal['total_qty'] < $order['qty']){
                        $checkErrorStock = 2;
                    }
                }
                if($checkErrorStock == 1){
                    $customerLocation = ClassRegistry::init('Location')->find("first", array('conditions' => array('Location.location_group_id' => $consignment['Consignment']['location_group_to_id'])));
                    $dateConsignment  = $consignment['Consignment']['date'];
                    $this->loadModel('ConsignmentReceive');
                    $customerWarehouse = ClassRegistry::init('LocationGroup')->find("first", array('conditions' => array('LocationGroup.customer_id' => $consignment['Consignment']['customer_id'])));
                    if(!empty($customerWarehouse)){
                        $customerLocation = ClassRegistry::init('Location')->find("first", array('conditions' => array('Location.location_group_id' => $customerWarehouse['LocationGroup']['id'])));
                        $warehouseId = $customerWarehouse['LocationGroup']['id'];
                        $locationId  = $customerLocation['Location']['id'];
                    } else {
                        $this->loadModel('LocationGroup');
                        $this->loadModel('Location');
                        $this->LocationGroup->create();
                        $this->data['LocationGroup']['sys_code'] = md5(rand().strtotime(date("Y-m-d H:i:s")).$user['User']['id']);
                        $this->data['LocationGroup']['location_group_type_id'] = 1;
                        $this->data['LocationGroup']['code'] = $consignment['Customer']['name'];
                        $this->data['LocationGroup']['name'] = $consignment['Customer']['name'];
                        $this->data['LocationGroup']['customer_id'] = $consignment['Consignment']['customer_id'];
                        $this->data['LocationGroup']['created_by']  = $user['User']['id'];
                        $this->data['LocationGroup']['is_active']   = 1;
                        if ($this->LocationGroup->save($this->data)) {
                            $warehouseId = $lastInsertId = $this->LocationGroup->id;
                            // Create Table For Store Total
                            mysql_query("CREATE TABLE `".$lastInsertId."_group_totals` (
                                                `product_id` INT(11) NOT NULL DEFAULT '0',
                                                `lots_number` VARCHAR(50) NOT NULL DEFAULT '0' COLLATE 'utf8_unicode_ci',
                                                `expired_date` DATE NOT NULL,
                                                `location_id` INT(11) NOT NULL DEFAULT '0',
                                                `location_group_id` INT(11) NOT NULL DEFAULT '0',
                                                `total_qty` DECIMAL(15,3) NULL DEFAULT '0',
                                                `total_order` DECIMAL(15,3) NULL DEFAULT '0',
                                                PRIMARY KEY (`product_id`, `location_id`, `location_group_id`, `lots_number`, `expired_date`),
                                                INDEX `index_keys` (`product_id`, `location_id`, `location_group_id`, `lots_number`, `expired_date`)
                                        )
                                        COLLATE='utf8_unicode_ci'
                                        ENGINE=InnoDB;");
                            // Create Table For Store Total Detail
                            mysql_query("CREATE TABLE `".$lastInsertId."_group_total_details` (
                                                `product_id` INT(11) NOT NULL DEFAULT '0',
                                                `location_group_id` INT(11) NOT NULL DEFAULT '0',
                                                `total_cycle` DECIMAL(15,3) NULL DEFAULT '0',
                                                `total_so` DECIMAL(15,3) NULL DEFAULT '0',
                                                `total_so_free` DECIMAL(15,3) NULL DEFAULT '0',
                                                `total_pos` DECIMAL(15,3) NULL DEFAULT '0',
                                                `total_pos_free` DECIMAL(15,3) NULL DEFAULT '0',
                                                `total_pb` DECIMAL(15,3) NULL DEFAULT '0',
                                                `total_pbc` DECIMAL(15,3) NULL DEFAULT '0',
                                                `total_cm` DECIMAL(15,3) NULL DEFAULT '0',
                                                `total_cm_free` DECIMAL(15,3) NULL DEFAULT '0',
                                                `total_to_in` DECIMAL(15,3) NULL DEFAULT '0',
                                                `total_to_out` DECIMAL(15,3) NULL DEFAULT '0',
                                                `total_cus_consign_in` DECIMAL(15,3) NULL DEFAULT '0',
                                                `total_cus_consign_out` DECIMAL(15,3) NULL DEFAULT '0',
                                                `total_ven_consign_in` DECIMAL(15,3) NULL DEFAULT '0',
                                                `total_ven_consign_out` DECIMAL(15,3) NULL DEFAULT '0',
                                                `total_order` DECIMAL(15,3) NULL DEFAULT '0',
                                                `date` DATE NOT NULL,
                                                PRIMARY KEY (`product_id`, `location_group_id`, `date`),
                                                INDEX `index_key` (`product_id`, `location_group_id`, `date`)
                                        )
                                        COLLATE='utf8_unicode_ci'
                                        ENGINE=InnoDB;");
                            $this->loadModel('Location');
                            $this->Location->create();
                            $location = array();
                            $location['Location']['sys_code'] = md5(rand().strtotime(date("Y-m-d H:i:s")).$user['User']['id']);
                            $location['Location']['location_group_id'] = $lastInsertId;
                            $location['Location']['name'] = $this->data['LocationGroup']['name'];
                            $location['Location']['is_for_sale'] = 1;
                            $location['Location']['created_by']  = $user['User']['id'];
                            $location['Location']['is_active']   = 1;
                            if ($this->Location->save($location)) {
                                $locationId = $this->Location->getLastInsertId();
                                mysql_query("CREATE TABLE `".$locationId."_inventories` (
                                                    `id` BIGINT(20) NOT NULL AUTO_INCREMENT,
                                                    `consignment_id` BIGINT(20) NULL DEFAULT NULL,
                                                    `consignment_return_id` BIGINT(20) NULL DEFAULT NULL,
                                                    `vendor_consignment_id` BIGINT(20) NULL DEFAULT NULL,
                                                    `vendor_consignment_return_id` BIGINT(20) NULL DEFAULT NULL,
                                                    `cycle_product_id` BIGINT(20) NULL DEFAULT NULL,
                                                    `cycle_product_detail_id` BIGINT(20) NULL DEFAULT NULL,
                                                    `sales_order_id` BIGINT(20) NULL DEFAULT NULL,
                                                    `point_of_sales_id` BIGINT(20) NULL DEFAULT NULL,
                                                    `credit_memo_id` BIGINT(20) NULL DEFAULT NULL,
                                                    `purchase_order_id` BIGINT(20) NULL DEFAULT NULL,
                                                    `purchase_return_id` BIGINT(20) NULL DEFAULT NULL,
                                                    `transfer_order_id` BIGINT(20) NULL DEFAULT NULL,
                                                    `type` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8_unicode_ci',
                                                    `customer_id` INT(11) NULL DEFAULT NULL,
                                                    `vendor_id` INT(11) NULL DEFAULT NULL,
                                                    `product_id` INT(11) NOT NULL,
                                                    `location_id` INT(11) NOT NULL,
                                                    `location_group_id` INT(11) NOT NULL,
                                                    `qty` DECIMAL(15,3) NOT NULL,
                                                    `unit_cost` DECIMAL(15,3) NULL DEFAULT '0.000',
                                                    `date` DATE NOT NULL,
                                                    `lots_number` VARCHAR(50) NULL DEFAULT '0' COLLATE 'utf8_unicode_ci',
                                                    `date_expired` DATE NULL DEFAULT NULL,
                                                    `created` DATETIME NOT NULL,
                                                    `created_by` BIGINT(11) NOT NULL,
                                                    `modified` DATETIME NOT NULL,
                                                    `modified_by` BIGINT(11) NULL DEFAULT NULL,
                                                    `is_active` TINYINT(4) NULL DEFAULT '1',
                                                    PRIMARY KEY (`id`),
                                                    INDEX `product_id` (`product_id`),
                                                    INDEX `location_id` (`location_id`),
                                                    INDEX `lots_number` (`lots_number`),
                                                    INDEX `qty` (`qty`),
                                                    INDEX `location_group_id` (`location_group_id`)
                                            )
                                            COLLATE='utf8_unicode_ci'
                                            ENGINE=InnoDB;");
                                mysql_query("CREATE TABLE `".$locationId."_inventory_totals` (
                                                    `product_id` INT(11) NOT NULL DEFAULT '0',
                                                    `location_id` INT(11) NOT NULL DEFAULT '0',
                                                    `lots_number` VARCHAR(50) NOT NULL DEFAULT '0' COLLATE 'utf8_unicode_ci',
                                                    `expired_date` DATE NOT NULL,
                                                    `total_qty` DECIMAL(15,3) NULL DEFAULT '0',
                                                    `total_order` DECIMAL(15,3) NULL DEFAULT '0',
                                                    PRIMARY KEY (`product_id`, `location_id`, `lots_number`, `expired_date`),
                                                    INDEX `index_keys` (`product_id`, `location_id`, `lots_number`, `expired_date`)
                                            )
                                            COLLATE='utf8_unicode_ci'
                                            ENGINE=InnoDB;");
                                mysql_query("CREATE TABLE `".$locationId."_inventory_total_details` (
                                                    `product_id` INT(11) NOT NULL DEFAULT '0',
                                                    `location_id` INT(11) NOT NULL DEFAULT '0',
                                                    `lots_number` VARCHAR(50) NOT NULL DEFAULT '0' COLLATE 'utf8_unicode_ci',
                                                    `expired_date` DATE NOT NULL,
                                                    `total_cycle` DECIMAL(15,3) NULL DEFAULT '0',
                                                    `total_so` DECIMAL(15,3) NULL DEFAULT '0',
                                                    `total_pos` DECIMAL(15,3) NULL DEFAULT '0',
                                                    `total_pb` DECIMAL(15,3) NULL DEFAULT '0',
                                                    `total_pbc` DECIMAL(15,3) NULL DEFAULT '0',
                                                    `total_cm` DECIMAL(15,3) NULL DEFAULT '0',
                                                    `total_to_in` DECIMAL(15,3) NULL DEFAULT '0',
                                                    `total_to_out` DECIMAL(15,3) NULL DEFAULT '0',
                                                    `total_cus_consign_in` DECIMAL(15,3) NULL DEFAULT '0',
                                                    `total_cus_consign_out` DECIMAL(15,3) NULL DEFAULT '0',
                                                    `total_ven_consign_in` DECIMAL(15,3) NULL DEFAULT '0',
                                                    `total_ven_consign_out` DECIMAL(15,3) NULL DEFAULT '0',
                                                    `total_order` DECIMAL(15,3) NULL DEFAULT '0',
                                                    `date` DATE NOT NULL,
                                                    PRIMARY KEY (`product_id`, `location_id`, `lots_number`, `expired_date`, `date`),
                                                    INDEX `index_keys` (`product_id`, `location_id`, `lots_number`, `expired_date`, `date`)
                                            )
                                            COLLATE='utf8_unicode_ci'
                                            ENGINE=InnoDB;");
                            }
                        }
                    }
                    // Calculate Location, Lot, Expired Date
                    $sqlOrder = mysql_query("SELECT * FROM stock_orders WHERE consignment_id = ".$consignment['Consignment']['id']);
                    while($rowOrder = mysql_fetch_array($sqlOrder)){
                        // Reset Stock Order
                        $this->Inventory->saveGroupQtyOrder($rowOrder['location_group_id'], $rowOrder['location_id'], $rowOrder['product_id'], $rowOrder['lots_number'], $rowOrder['expired_date'], $rowOrder['qty'], $dateConsignment, '-');
                        // Get Lots, Expired, Total Qty
                        $invInfos   = array();
                        $index      = 0;
                        $totalOrder = $rowOrder['qty'];
                        // Calculate Location, Lot, Expired Date
                        $sqlInventory = mysql_query("SELECT SUM(IFNULL(group_totals.total_qty,0)) AS total_qty, group_totals.location_id AS location_id, group_totals.lots_number AS lots_number, group_totals.expired_date AS expired_date FROM ".$rowOrder['location_group_id']."_group_totals AS group_totals WHERE group_totals.location_id IN (SELECT id FROM locations WHERE location_group_id = ".$rowOrder['location_group_id'].") AND group_totals.product_id = ".$rowOrder['product_id']." GROUP BY group_totals.location_id, group_totals.product_id, group_totals.lots_number, group_totals.expired_date HAVING total_qty > 0 ORDER BY group_totals.lots_number, group_totals.expired_date, group_totals.location_id ASC");
                        while($rowInventory = mysql_fetch_array($sqlInventory)) {
                            // Check Order
                            $stockOrder = 0;
                            $sqlStock = mysql_query("SELECT SUM(qty) FROM stock_orders WHERE sales_order_id IS NULL AND consignment_id IS NULL AND product_id = ".$rowOrder['product_id']." AND location_group_id = ".$rowOrder['location_group_id']." AND location_id = ".$rowInventory['location_id']." AND lots_number = '".$rowInventory['lots_number']."' AND expired_date = '".$rowInventory['expired_date']."'");
                            if(mysql_num_rows($sqlStock)){
                                $rowStock = mysql_fetch_array($sqlStock);
                                $stockOrder = $rowStock[0];
                            }
                            $totalStock = $rowInventory['total_qty'] - $stockOrder;
                            if($totalOrder > 0 && $totalStock > 0){
                                if($totalStock >= $totalOrder) {
                                    $invInfos[$index]['total_qty']    = $totalOrder;
                                    $invInfos[$index]['location_id']  = $rowInventory['location_id'];
                                    $invInfos[$index]['lots_number']  = $rowInventory['lots_number'];
                                    $invInfos[$index]['expired_date'] = $rowInventory['expired_date'];
                                    $totalOrder = 0;
                                    ++$index;
                                } else if($totalStock < $totalOrder) {
                                    $invInfos[$index]['total_qty']    = $rowInventory['total_qty'];
                                    $invInfos[$index]['location_id']  = $rowInventory['location_id'];
                                    $invInfos[$index]['lots_number']  = $rowInventory['lots_number'];
                                    $invInfos[$index]['expired_date'] = $rowInventory['expired_date'];
                                    $totalOrder = $totalOrder - $totalStock;
                                    ++$index;
                                }
                            }
                        }
                        // Cut Stock
                        foreach($invInfos AS $invInfo){
                            // Update Inventory (Out)
                            $data = array();
                            $data['module_type']       = 13;
                            $data['consignment_id']    = $consignment['Consignment']['id'];
                            $data['product_id']        = $rowOrder['product_id'];
                            $data['location_id']       = $invInfo['location_id'];
                            $data['location_group_id'] = $rowOrder['location_group_id'];
                            $data['lots_number']  = $invInfo['lots_number']!=''?$invInfo['lots_number']:0;
                            $data['expired_date'] = $invInfo['expired_date']!='0000-00-00'?$invInfo['expired_date']:'0000-00-00';
                            $data['date']         = $dateConsignment;
                            $data['total_qty']    = $invInfo['total_qty'];
                            $data['total_order']  = $invInfo['total_qty'];
                            $data['total_free']   = 0;
                            $data['user_id']      = $user['User']['id'];
                            $data['customer_id']  = $consignment['Consignment']['customer_id'];
                            $data['vendor_id']    = "";
                            $data['unit_cost']    = 0;
                            $data['unit_price']   = 0;
                            // Update Inventory Group
                            $this->Inventory->saveGroupTotalDetail($data);
                            // Update Invetory Location
                            $this->Inventory->saveInventory($data);

                            // Update Inventory (In)
                            $dataIn = array();
                            $dataIn['module_type']       = 12;
                            $dataIn['consignment_id']    = $consignment['Consignment']['id'];
                            $dataIn['product_id']        = $rowOrder['product_id'];
                            $dataIn['location_id']       = $locationId;
                            $dataIn['location_group_id'] = $warehouseId;
                            $dataIn['lots_number']  = $invInfo['lots_number']!=''?$invInfo['lots_number']:0;
                            $dataIn['expired_date'] = $invInfo['expired_date']!='0000-00-00'?$invInfo['expired_date']:'0000-00-00';
                            $dataIn['date']         = $dateConsignment;
                            $dataIn['total_qty']    = $invInfo['total_qty'];
                            $dataIn['total_order']  = $invInfo['total_qty'];
                            $dataIn['total_free']   = 0;
                            $dataIn['user_id']      = $user['User']['id'];
                            $dataIn['customer_id']  = $consignment['Consignment']['customer_id'];
                            $dataIn['vendor_id']    = "";
                            $dataIn['unit_cost']    = 0;
                            $dataIn['unit_price']   = 0;
                            // Update Inventory Group
                            $this->Inventory->saveGroupTotalDetail($dataIn);
                            // Update Invetory Location
                            $this->Inventory->saveInventory($dataIn);

                            //Insert Into Delivery Detail
                            $consignmentReceive = array();
                            $this->ConsignmentReceive->create();
                            $consignmentReceive['ConsignmentReceive']['consignment_id'] = $consignment['Consignment']['id'];
                            $consignmentReceive['ConsignmentReceive']['consignment_detail_id'] = $consignmentDetail['ConsignmentDetail']['id'];
                            $consignmentReceive['ConsignmentReceive']['product_id']    = $rowOrder['product_id'];
                            $consignmentReceive['ConsignmentReceive']['location_id']   = $invInfo['location_id'];
                            $consignmentReceive['ConsignmentReceive']['lots_number']   = $invInfo['lots_number']!=''?$invInfo['lots_number']:0;
                            $consignmentReceive['ConsignmentReceive']['expired_date']  = $invInfo['expired_date']!='0000-00-00'?$invInfo['expired_date']:'0000-00-00';
                            $consignmentReceive['ConsignmentReceive']['total_qty']     = $invInfo['total_qty'];
                            $this->ConsignmentReceive->save($consignmentReceive);
                        }
                    }
                    // Update Consignment
                    mysql_query("UPDATE consignments SET status = 2, location_group_to_id = ".$warehouseId." WHERE id = ".$consignment['Consignment']['id']);
                    // Delete Tmp Stock Order
                    mysql_query("DELETE FROM `stock_orders` WHERE  `consignment_id`= " . $consignment['Consignment']['id']);
                    $this->Helper->saveUserActivity($user['User']['id'], 'Consignment', 'Save Receive', $consignment['Consignment']['id']);
                } else {
                    $this->Helper->saveUserActivity($user['User']['id'], 'Consignment', 'Save Receive (Out of Stock)', $consignment['Consignment']['id']);
                    $result['error'] = 2;
                }
            } else {
                $this->Helper->saveUserActivity($user['User']['id'], 'Consignment', 'Save Receive (Error Status)', $consignment['Consignment']['id']);
                $result['error'] = 1;
            }
            echo json_encode($result);
            exit;
        }
        $this->Helper->saveUserActivity($user['User']['id'], 'Consignment', 'Receive', $id);
        $this->data = $this->Consignment->read(null, $id);
        if (!empty($this->data)) {
            $consignmentDetails = ClassRegistry::init('ConsignmentDetail')->find("all", array('conditions' => array('ConsignmentDetail.consignment_id' => $id)));
            $this->set(compact('consignmentDetails'));
        } else {
            exit;
        }
    }
    
    function pickProduct($id = null, $locationGroupId = null){
        $this->layout = 'ajax';
        if(empty($id) || empty($locationGroupId)){
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $consignmentDetail = ClassRegistry::init('ConsignmentDetail')->find("first", array('conditions' => array('ConsignmentDetail.id' => $id)));
        $this->set(compact("id", "locationGroupId", "consignmentDetail"));
    }
    
    function pickProductAjax($productId = null, $locationGroupId = null, $smallUomLabel = null){
        $this->layout = 'ajax';
        $this->set('productId', $productId);
        $this->set('locationGroupId', $locationGroupId);
        $this->set('smallUomLabel', $smallUomLabel);
    }
    
    function pickProductSave(){
        $this->layout = 'ajax';
        if(!empty($this->data)){
            $user = $this->getCurrentUser();
            $sql = mysql_query("SELECT id FROM consignment_receives WHERE consignment_detail_id = ".$this->data['consignment_detail_id']);
            if(!mysql_num_rows($sql)){
                $consignment     = ClassRegistry::init('Consignment')->find("first", array('conditions' => array('Consignment.id' => $this->data['consignment_id'])));
                $dateConsignment = $consignment['Consignment']['date'];
                // Check Customer Warehouse
                $customerLocation = ClassRegistry::init('Location')->find("first", array('conditions' => array('Location.location_group_id' => $consignment['Consignment']['location_group_to_id'])));
                $this->loadModel('ConsignmentReceive');
                $customerWarehouse = ClassRegistry::init('LocationGroup')->find("first", array('conditions' => array('LocationGroup.customer_id' => $consignment['Consignment']['customer_id'])));
                if(!empty($customerWarehouse)){
                    $customerLocation = ClassRegistry::init('Location')->find("first", array('conditions' => array('Location.location_group_id' => $customerWarehouse['LocationGroup']['id'])));
                    $warehouseId = $customerWarehouse['LocationGroup']['id'];
                    $locationId  = $customerLocation['Location']['id'];
                } else {
                    $this->loadModel('LocationGroup');
                    $this->loadModel('Location');
                    $this->LocationGroup->create();
                    $this->data['LocationGroup']['sys_code'] = md5(rand().strtotime(date("Y-m-d H:i:s")).$user['User']['id']);
                    $this->data['LocationGroup']['location_group_type_id'] = 1;
                    $this->data['LocationGroup']['code'] = $consignment['Customer']['name'];
                    $this->data['LocationGroup']['name'] = $consignment['Customer']['name'];
                    $this->data['LocationGroup']['customer_id'] = $consignment['Consignment']['customer_id'];
                    $this->data['LocationGroup']['created_by']  = $user['User']['id'];
                    $this->data['LocationGroup']['is_active']   = 1;
                    if ($this->LocationGroup->save($this->data)) {
                        $warehouseId = $lastInsertId = $this->LocationGroup->id;
                        // Create Table For Store Total
                        mysql_query("CREATE TABLE `".$lastInsertId."_group_totals` (
                                            `product_id` INT(11) NOT NULL DEFAULT '0',
                                            `lots_number` VARCHAR(50) NOT NULL DEFAULT '0' COLLATE 'utf8_unicode_ci',
                                            `expired_date` DATE NOT NULL,
                                            `location_id` INT(11) NOT NULL DEFAULT '0',
                                            `location_group_id` INT(11) NOT NULL DEFAULT '0',
                                            `total_qty` DECIMAL(15,3) NULL DEFAULT '0',
                                            `total_order` DECIMAL(15,3) NULL DEFAULT '0',
                                            PRIMARY KEY (`product_id`, `location_id`, `location_group_id`, `lots_number`, `expired_date`),
                                            INDEX `index_keys` (`product_id`, `location_id`, `location_group_id`, `lots_number`, `expired_date`)
                                    )
                                    COLLATE='utf8_unicode_ci'
                                    ENGINE=InnoDB;");
                        // Create Table For Store Total Detail
                        mysql_query("CREATE TABLE `".$lastInsertId."_group_total_details` (
                                            `product_id` INT(11) NOT NULL DEFAULT '0',
                                            `location_group_id` INT(11) NOT NULL DEFAULT '0',
                                            `total_cycle` DECIMAL(15,3) NULL DEFAULT '0',
                                            `total_so` DECIMAL(15,3) NULL DEFAULT '0',
                                            `total_so_free` DECIMAL(15,3) NULL DEFAULT '0',
                                            `total_pos` DECIMAL(15,3) NULL DEFAULT '0',
                                            `total_pos_free` DECIMAL(15,3) NULL DEFAULT '0',
                                            `total_pb` DECIMAL(15,3) NULL DEFAULT '0',
                                            `total_pbc` DECIMAL(15,3) NULL DEFAULT '0',
                                            `total_cm` DECIMAL(15,3) NULL DEFAULT '0',
                                            `total_cm_free` DECIMAL(15,3) NULL DEFAULT '0',
                                            `total_to_in` DECIMAL(15,3) NULL DEFAULT '0',
                                            `total_to_out` DECIMAL(15,3) NULL DEFAULT '0',
                                            `total_cus_consign_in` DECIMAL(15,3) NULL DEFAULT '0',
                                            `total_cus_consign_out` DECIMAL(15,3) NULL DEFAULT '0',
                                            `total_ven_consign_in` DECIMAL(15,3) NULL DEFAULT '0',
                                            `total_ven_consign_out` DECIMAL(15,3) NULL DEFAULT '0',
                                            `total_order` DECIMAL(15,3) NULL DEFAULT '0',
                                            `date` DATE NOT NULL,
                                            PRIMARY KEY (`product_id`, `location_group_id`, `date`),
                                            INDEX `index_key` (`product_id`, `location_group_id`, `date`)
                                    )
                                    COLLATE='utf8_unicode_ci'
                                    ENGINE=InnoDB;");
                        $this->loadModel('Location');
                        $this->Location->create();
                        $location = array();
                        $location['Location']['sys_code'] = md5(rand().strtotime(date("Y-m-d H:i:s")).$user['User']['id']);
                        $location['Location']['location_group_id'] = $lastInsertId;
                        $location['Location']['name'] = $this->data['LocationGroup']['name'];
                        $location['Location']['is_for_sale'] = 1;
                        $location['Location']['created_by']  = $user['User']['id'];
                        $location['Location']['is_active']   = 1;
                        if ($this->Location->save($location)) {
                            $locationId = $this->Location->getLastInsertId();
                            mysql_query("CREATE TABLE `".$locationId."_inventories` (
                                                `id` BIGINT(20) NOT NULL AUTO_INCREMENT,
                                                `consignment_id` BIGINT(20) NULL DEFAULT NULL,
                                                `consignment_return_id` BIGINT(20) NULL DEFAULT NULL,
                                                `vendor_consignment_id` BIGINT(20) NULL DEFAULT NULL,
                                                `vendor_consignment_return_id` BIGINT(20) NULL DEFAULT NULL,
                                                `cycle_product_id` BIGINT(20) NULL DEFAULT NULL,
                                                `cycle_product_detail_id` BIGINT(20) NULL DEFAULT NULL,
                                                `sales_order_id` BIGINT(20) NULL DEFAULT NULL,
                                                `point_of_sales_id` BIGINT(20) NULL DEFAULT NULL,
                                                `credit_memo_id` BIGINT(20) NULL DEFAULT NULL,
                                                `purchase_order_id` BIGINT(20) NULL DEFAULT NULL,
                                                `purchase_return_id` BIGINT(20) NULL DEFAULT NULL,
                                                `transfer_order_id` BIGINT(20) NULL DEFAULT NULL,
                                                `type` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8_unicode_ci',
                                                `customer_id` INT(11) NULL DEFAULT NULL,
                                                `vendor_id` INT(11) NULL DEFAULT NULL,
                                                `product_id` INT(11) NOT NULL,
                                                `location_id` INT(11) NOT NULL,
                                                `location_group_id` INT(11) NOT NULL,
                                                `qty` DECIMAL(15,3) NOT NULL,
                                                `unit_cost` DECIMAL(15,3) NULL DEFAULT '0.000',
                                                `date` DATE NOT NULL,
                                                `lots_number` VARCHAR(50) NULL DEFAULT '0' COLLATE 'utf8_unicode_ci',
                                                `date_expired` DATE NULL DEFAULT NULL,
                                                `created` DATETIME NOT NULL,
                                                `created_by` BIGINT(11) NOT NULL,
                                                `modified` DATETIME NOT NULL,
                                                `modified_by` BIGINT(11) NULL DEFAULT NULL,
                                                `is_active` TINYINT(4) NULL DEFAULT '1',
                                                PRIMARY KEY (`id`),
                                                INDEX `product_id` (`product_id`),
                                                INDEX `location_id` (`location_id`),
                                                INDEX `lots_number` (`lots_number`),
                                                INDEX `qty` (`qty`),
                                                INDEX `location_group_id` (`location_group_id`)
                                        )
                                        COLLATE='utf8_unicode_ci'
                                        ENGINE=InnoDB;");
                            mysql_query("CREATE TABLE `".$locationId."_inventory_totals` (
                                                `product_id` INT(11) NOT NULL DEFAULT '0',
                                                `location_id` INT(11) NOT NULL DEFAULT '0',
                                                `lots_number` VARCHAR(50) NOT NULL DEFAULT '0' COLLATE 'utf8_unicode_ci',
                                                `expired_date` DATE NOT NULL,
                                                `total_qty` DECIMAL(15,3) NULL DEFAULT '0',
                                                `total_order` DECIMAL(15,3) NULL DEFAULT '0',
                                                PRIMARY KEY (`product_id`, `location_id`, `lots_number`, `expired_date`),
                                                INDEX `index_keys` (`product_id`, `location_id`, `lots_number`, `expired_date`)
                                        )
                                        COLLATE='utf8_unicode_ci'
                                        ENGINE=InnoDB;");
                            mysql_query("CREATE TABLE `".$locationId."_inventory_total_details` (
                                                `product_id` INT(11) NOT NULL DEFAULT '0',
                                                `location_id` INT(11) NOT NULL DEFAULT '0',
                                                `lots_number` VARCHAR(50) NOT NULL DEFAULT '0' COLLATE 'utf8_unicode_ci',
                                                `expired_date` DATE NOT NULL,
                                                `total_cycle` DECIMAL(15,3) NULL DEFAULT '0',
                                                `total_so` DECIMAL(15,3) NULL DEFAULT '0',
                                                `total_pos` DECIMAL(15,3) NULL DEFAULT '0',
                                                `total_pb` DECIMAL(15,3) NULL DEFAULT '0',
                                                `total_pbc` DECIMAL(15,3) NULL DEFAULT '0',
                                                `total_cm` DECIMAL(15,3) NULL DEFAULT '0',
                                                `total_to_in` DECIMAL(15,3) NULL DEFAULT '0',
                                                `total_to_out` DECIMAL(15,3) NULL DEFAULT '0',
                                                `total_cus_consign_in` DECIMAL(15,3) NULL DEFAULT '0',
                                                `total_cus_consign_out` DECIMAL(15,3) NULL DEFAULT '0',
                                                `total_ven_consign_in` DECIMAL(15,3) NULL DEFAULT '0',
                                                `total_ven_consign_out` DECIMAL(15,3) NULL DEFAULT '0',
                                                `total_order` DECIMAL(15,3) NULL DEFAULT '0',
                                                `date` DATE NOT NULL,
                                                PRIMARY KEY (`product_id`, `location_id`, `lots_number`, `expired_date`, `date`),
                                                INDEX `index_keys` (`product_id`, `location_id`, `lots_number`, `expired_date`, `date`)
                                        )
                                        COLLATE='utf8_unicode_ci'
                                        ENGINE=InnoDB;");
                        }
                    }
                }
                // Reset Stock Order
                $sqlResetOrder = mysql_query("SELECT * FROM stock_orders WHERE `consignment_id`=".$this->data['consignment_id']." AND consignment_detail_id = ".$this->data['consignment_detail_id'].";");
                while($rowResetOrder = mysql_fetch_array($sqlResetOrder)){
                    $this->Inventory->saveGroupQtyOrder($rowResetOrder['location_group_id'], $rowResetOrder['location_id'], $rowResetOrder['product_id'], $rowResetOrder['lots_number'], $rowResetOrder['expired_date'], $rowResetOrder['qty'], $rowResetOrder['date'], '-');
                }
                // Detele Tmp Stock Order
                mysql_query("DELETE FROM `stock_orders` WHERE  `consignment_id`=".$this->data['consignment_id']." AND consignment_detail_id = ".$this->data['consignment_detail_id'].";");
                for($i = 0; $i < sizeof($_POST['qty_pick']); $i++){
                    // Update Inventory (Out)
                    $data = array();
                    $data['module_type']       = 13;
                    $data['consignment_id']    = $consignment['Consignment']['id'];
                    $data['product_id']        = $this->data['product_id'];
                    $data['location_id']       = $_POST['location_id'][$i];
                    $data['location_group_id'] = $consignment['Consignment']['location_group_id'];
                    $data['lots_number']  = $_POST['lots_number'][$i]!=''?$_POST['lots_number'][$i]:0;
                    $data['expired_date'] = $_POST['expired_date'][$i]!='0000-00-00'?$_POST['expired_date'][$i]:'0000-00-00';
                    $data['date']         = $dateConsignment;
                    $data['total_qty']    = $_POST['qty_pick'][$i];
                    $data['total_order']  = $_POST['qty_pick'][$i];
                    $data['total_free']   = 0;
                    $data['user_id']      = $user['User']['id'];
                    $data['customer_id']  = $consignment['Consignment']['customer_id'];
                    $data['vendor_id']    = "";
                    $data['unit_cost']    = 0;
                    $data['unit_price']   = 0;
                    // Update Inventory Group
                    $this->Inventory->saveGroupTotalDetail($data);
                    // Update Invetory Location
                    $this->Inventory->saveInventory($data);

                    // Update Inventory (In)
                    $dataIn = array();
                    $dataIn['module_type']       = 12;
                    $dataIn['consignment_id']    = $consignment['Consignment']['id'];
                    $dataIn['product_id']        = $this->data['product_id'];
                    $dataIn['location_id']       = $locationId;
                    $dataIn['location_group_id'] = $warehouseId;
                    $dataIn['lots_number']  = $_POST['lots_number'][$i]!=''?$_POST['lots_number'][$i]:0;
                    $dataIn['expired_date'] = $_POST['expired_date'][$i]!='0000-00-00'?$_POST['expired_date'][$i]:'0000-00-00';
                    $dataIn['date']         = $dateConsignment;
                    $dataIn['total_qty']    = $_POST['qty_pick'][$i];
                    $dataIn['total_order']  = $_POST['qty_pick'][$i];
                    $dataIn['total_free']   = 0;
                    $dataIn['user_id']      = $user['User']['id'];
                    $dataIn['customer_id']  = $consignment['Consignment']['customer_id'];
                    $dataIn['vendor_id']    = "";
                    $dataIn['unit_cost']    = 0;
                    $dataIn['unit_price']   = 0;
                    // Update Inventory Group
                    $this->Inventory->saveGroupTotalDetail($dataIn);
                    // Update Invetory Location
                    $this->Inventory->saveInventory($dataIn);

                    //Insert Into Delivery Detail
                    $consignmentReceive = array();
                    $this->ConsignmentReceive->create();
                    $consignmentReceive['ConsignmentReceive']['consignment_id'] = $consignment['Consignment']['id'];
                    $consignmentReceive['ConsignmentReceive']['consignment_detail_id'] = $this->data['consignment_detail_id'];
                    $consignmentReceive['ConsignmentReceive']['product_id']    = $this->data['product_id'];
                    $consignmentReceive['ConsignmentReceive']['location_id']   = $_POST['location_id'][$i];
                    $consignmentReceive['ConsignmentReceive']['lots_number']   = $_POST['lots_number'][$i]!=''?$_POST['lots_number'][$i]:0;
                    $consignmentReceive['ConsignmentReceive']['expired_date']  = $_POST['expired_date'][$i]!='0000-00-00'?$_POST['expired_date'][$i]:'0000-00-00';
                    $consignmentReceive['ConsignmentReceive']['total_qty']     = $_POST['qty_pick'][$i];
                    $this->ConsignmentReceive->save($consignmentReceive);
                }
                $this->Helper->saveUserActivity($user['User']['id'], 'Consignment', 'Save Product Pick One', $this->data['consignment_id']);
                $invalid['success'] = 1;
            } else {
                $this->Helper->saveUserActivity($user['User']['id'], 'Consignment', 'Save Product Pick One (Error Ready)', $this->data['consignment_id']);
                $invalid['ready'] = 1;
            }
            echo json_encode($invalid);
            exit();
        }
    }

}

?>