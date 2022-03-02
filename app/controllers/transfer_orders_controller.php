<?php

class TransferOrdersController extends AppController {

    var $name = 'TransferOrders';
    var $components = array('Helper', 'Inventory');
    
    function viewByUser() {
        $this->layout = 'ajax';
    }

    function index() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        $this->set('user',$user);
        $this->Helper->saveUserActivity($user['User']['id'], 'Transfer Order', 'Dashboard');
    }

    function ajax($dateFrom = 'all', $dateTo = 'all', $fromWarehouse = 'all', $toWarehouse = 'all', $status = 'all') {
        $this->layout = 'ajax';
        $this->set(compact('dateFrom', 'dateTo', 'fromWarehouse', 'toWarehouse', 'status'));
    }

    function view($id = null) {
        $this->layout = 'ajax';
        if (!$id) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $user = $this->getCurrentUser();
        $this->Helper->saveUserActivity($user['User']['id'], 'Transfer Order', 'View', $id);
        $this->data = $this->TransferOrder->read(null, $id);
        $fromLocationGroups = ClassRegistry::init('LocationGroup')->find('first', array('conditions' => array('LocationGroup.id' => $this->data['TransferOrder']['from_location_group_id'])));
        $toLocationGroups   = ClassRegistry::init('LocationGroup')->find('first', array("conditions" => array('LocationGroup.id' => $this->data['TransferOrder']['to_location_group_id'])));
        $transferOrderDetails = ClassRegistry::init('TransferOrderDetail')->find('all', array("conditions" => array("TransferOrderDetail.transfer_order_id"=>$this->data['TransferOrder']['id'])));
        $this->set(compact("fromLocationGroups", "toLocationGroups","transferOrderDetails"));
    }
    
    function add(){
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        if (!empty($this->data)) {
            $result  = array();
            $access = true;
            for ($i = 0; $i < sizeof($_POST['product_id']); $i++) {
                $sqlInv = mysql_query("SELECT SUM((inv.total_pb + total_to_in + total_cm + total_cycle + total_cus_consign_in) - (inv.total_so + inv.total_pos + inv.total_pbc + inv.total_to_out + total_cus_consign_out + inv.total_order)) AS total_qty FROM {$_POST['location_from_id'][$i]}_inventory_total_details AS inv WHERE inv.product_id = {$_POST['product_id'][$i]} AND inv.lots_number = '{$_POST['lots_number'][$i]}' AND inv.expired_date = '{$_POST['expired_date'][$i]}' AND inv.date <= '{$this->data['TransferOrder']['order_date']}' GROUP BY inv.product_id");
                $rowInv = mysql_fetch_array($sqlInv);
                $totalOrder = ($_POST['qty'][$i] * $_POST['conversion'][$i]);
                if($totalOrder > $rowInv[0]){
                    $access = false;
                }
            }
            if($access == false){
                $result['id'] = 0;
                $result['error'] = 2;
                echo json_encode($result);
                exit;
            }
            $dateNow  = date("Y-m-d H:i:s");
            $this->loadModel('StockOrder');
            // Check Warehouse Option Allow Approval
//            $warehouseOption = ClassRegistry::init('LocationGroup')->findById($this->data['TransferOrder']['from_location_group_id']);
            // Load Begin / Create for Transfer Order
            $this->TransferOrder->create();
            // Insert Into Transfer Order
            $transferOrder = array();
            $transferOrder['TransferOrder']['sys_code']                 = md5(rand().strtotime(date("Y-m-d H:i:s")).$user['User']['id']);
            $transferOrder['TransferOrder']['created']                  = $dateNow;
            $transferOrder['TransferOrder']['company_id']               = $this->data['TransferOrder']['company_id'];
            $transferOrder['TransferOrder']['branch_id']                = $this->data['TransferOrder']['branch_id'];
            $transferOrder['TransferOrder']['from_location_group_id']   = $this->data['TransferOrder']['from_location_group_id'];
            $transferOrder['TransferOrder']['to_location_group_id']     = $this->data['TransferOrder']['to_location_group_id'];
            $transferOrder['TransferOrder']['to_code']                  = $this->data['TransferOrder']['to_code'];
            $transferOrder['TransferOrder']['order_date']               = $this->data['TransferOrder']['order_date'];
            $transferOrder['TransferOrder']['fulfillment_date']         = ((empty($this->data['TransferOrder']['fulfillment_date']))?'0000-00-00':$this->data['TransferOrder']['fulfillment_date']);
            $transferOrder['TransferOrder']['note']                     = $this->data['TransferOrder']['note'];
            $transferOrder['TransferOrder']['total_cost']               = $this->data['TransferOrder']['total_cost'];
            $transferOrder['TransferOrder']['balance']                  = $this->data['TransferOrder']['total_cost'];
            $transferOrder['TransferOrder']['status']                   = 3;
            $transferOrder['TransferOrder']['created_by']               = $user['User']['id'];
            $transferOrder['TransferOrder']['is_approve']               = 3;
//            if($warehouseOption['LocationGroup']['stock_tranfer_confirm'] == 1){
//                $transferOrder['TransferOrder']['is_approve'] = 0;
//            }
            if ($this->TransferOrder->save($transferOrder)) {
                // Get Transfer Order Id
                $error = 0;
                $transferOrderId = $this->TransferOrder->id;
                // Get Module Code
                $modCode = $this->Helper->getModuleCode($this->data['TransferOrder']['to_code'], $transferOrderId, 'to_code', 'transfer_orders', 'status >= 0 AND branch_id = '.$this->data['TransferOrder']['branch_id']);
                // Updaet Module Code
                $transferOrder['TransferOrder']['to_code'] = $modCode;
                mysql_query("UPDATE transfer_orders SET to_code = '".$modCode."' WHERE id = ".$transferOrderId);
                // Load Begin / Create for Transfer Order
                $this->loadModel('TransferOrderDetail');
                $this->loadModel('TransferReceiveResult');
                $this->loadModel('TransferReceive');
                $this->TransferReceiveResult->create();
                $transferRecResult = array();
                $transferRecResult['TransferReceiveResult']['sys_code'] = md5(rand().strtotime(date("Y-m-d H:i:s")).$user['User']['id']);
                $transferRecResult['TransferReceiveResult']['created']  = $dateNow;
                $transferRecResult['TransferReceiveResult']['code']     = 'TR';
                $transferRecResult['TransferReceiveResult']['transfer_order_id'] = $transferOrderId;
                $transferRecResult['TransferReceiveResult']['date'] = $this->data['TransferOrder']['order_date'];
                $transferRecResult['TransferReceiveResult']['created_by'] = $user['User']['id'];
                $this->TransferReceiveResult->save($transferRecResult);
                $transferReceiveId = $this->TransferReceiveResult->id;
                // Get Module Code
                $modTRCode = $this->Helper->getModuleCode('TR', $transferReceiveId, 'code', 'transfer_receive_results', '1');
                // Updaet Module Code
                $transferRecResult['TransferReceiveResult']['code'] = $modTRCode;
                mysql_query("UPDATE transfer_receive_results SET code = '".$modCode."' WHERE id = ".$transferReceiveId);
                // Insert Transfer Detail
                for ($i = 0; $i < sizeof($_POST['product_id']); $i++) {
                    $this->TransferOrderDetail->create();
                    $transferOrderDetail = array();
                    $transferOrderDetail['TransferOrderDetail']['sys_code']             = md5(rand().strtotime(date("Y-m-d H:i:s")).$user['User']['id']);
                    $transferOrderDetail['TransferOrderDetail']['transfer_order_id']    = $transferOrderId;
                    $transferOrderDetail['TransferOrderDetail']['lots_number']          = $_POST['lots_number'][$i]!=""?$_POST['lots_number'][$i]:'0';
                    $transferOrderDetail['TransferOrderDetail']['expired_date']         = $_POST['expired_date'][$i]!=""?$_POST['expired_date'][$i]:'0000-00-00';
                    $transferOrderDetail['TransferOrderDetail']['location_from_id']     = $_POST['location_from_id'][$i];
                    $transferOrderDetail['TransferOrderDetail']['location_to_id']       = $_POST['location_to_id'][$i];
                    $transferOrderDetail['TransferOrderDetail']['product_id']           = $_POST['product_id'][$i];
                    $transferOrderDetail['TransferOrderDetail']['qty']                  = $_POST['qty'][$i];
                    $transferOrderDetail['TransferOrderDetail']['qty_uom_id']           = $_POST['qty_uom_id'][$i];
                    $transferOrderDetail['TransferOrderDetail']['conversion']           = $_POST['conversion'][$i];
                    $transferOrderDetail['TransferOrderDetail']['unit_cost']            = $_POST['unit_cost'][$i];
                    $transferOrderDetail['TransferOrderDetail']['total_cost']           = $_POST['total_cost'][$i];
                    $this->TransferOrderDetail->save($transferOrderDetail);
                    $transferOrderDetailId = $this->TransferOrderDetail->id;
                    $qty_inv = $this->Helper->replaceThousand($_POST['qty'][$i]) * $this->Helper->replaceThousand($_POST['conversion'][$i]);
                    $dateNow = $this->data['TransferOrder']['order_date'];
                    /* Transfer Order Out */
                    // Update Inventory (Transfer Out)
                    $dataOut = array();
                    $dataOut['module_type']         = 3;
                    $dataOut['transfer_order_id']   = $transferOrderId;
                    $dataOut['product_id']          = $_POST['product_id'][$i];
                    $dataOut['location_id']         = $_POST['location_from_id'][$i];
                    $dataOut['location_group_id']   = $transferOrder['TransferOrder']['from_location_group_id'];
                    $dataOut['lots_number']         = $transferOrderDetail['TransferOrderDetail']['lots_number'];
                    $dataOut['expired_date']        = $transferOrderDetail['TransferOrderDetail']['expired_date'];
                    $dataOut['date']                = $dateNow;
                    $dataOut['total_qty']           = $qty_inv;
                    $dataOut['total_order']         = $qty_inv;
                    $dataOut['total_free']          = 0;
                    $dataOut['user_id']             = $user['User']['id'];
                    $dataOut['customer_id']         = "";
                    $dataOut['vendor_id']           = "";
                    $dataOut['unit_cost']           = 0;
                    $dataOut['unit_price']          = 0;
                    $dataOut['transaction_id']      = '';
                    // Update Invetory Location
                    $this->Inventory->saveInventory($dataOut);
                    // Update Inventory Group
                    $this->Inventory->saveGroupTotalDetail($dataOut);

                    /* Transfer Order In */
                    // Update Inventory (Transfer In)
                    $dataIn = array();
                    $dataIn['module_type']       = 2;
                    $dataIn['transfer_order_id'] = $transferOrderId;
                    $dataIn['product_id']        = $_POST['product_id'][$i];
                    $dataIn['location_id']       = $_POST['location_to_id'][$i];
                    $dataIn['location_group_id'] = $transferOrder['TransferOrder']['to_location_group_id'];
                    $dataIn['lots_number']  = $transferOrderDetail['TransferOrderDetail']['lots_number'];
                    $dataIn['expired_date'] = $transferOrderDetail['TransferOrderDetail']['expired_date'];
                    $dataIn['date']         = $dateNow;
                    $dataIn['total_qty']    = $qty_inv;
                    $dataIn['total_order']  = $qty_inv;
                    $dataIn['total_free']   = 0;
                    $dataIn['user_id']      = $user['User']['id'];
                    $dataIn['customer_id']  = "";
                    $dataIn['vendor_id']    = "";
                    $dataIn['unit_cost']    = 0;
                    $dataIn['unit_price']   = 0;
                    $dataIn['transaction_id'] = '';
                    // Update Invetory Location
                    $this->Inventory->saveInventory($dataIn);
                    // Update Inventory Group
                    $this->Inventory->saveGroupTotalDetail($dataIn);

                    // Save Transfer Receive
                    $this->TransferReceive->create();
                    $transferReceive = array();
                    $transferReceive['TransferReceive']['transfer_receive_result_id'] = $transferReceiveId;
                    $transferReceive['TransferReceive']['transfer_order_detail_id']   = $transferOrderDetailId;
                    $transferReceive['TransferReceive']['transfer_order_id']          = $transferOrderId;
                    $transferReceive['TransferReceive']['lots_number']  = $transferOrderDetail['TransferOrderDetail']['lots_number'];
                    $transferReceive['TransferReceive']['expired_date'] = $transferOrderDetail['TransferOrderDetail']['expired_date'];
                    $transferReceive['TransferReceive']['product_id']   = $_POST['product_id'][$i];
                    $transferReceive['TransferReceive']['qty']          = $_POST['qty'][$i];
                    $transferReceive['TransferReceive']['qty_uom_id']   = $_POST['qty_uom_id'][$i];
                    $transferReceive['TransferReceive']['conversion']   = $_POST['conversion'][$i];
                    $transferReceive['TransferReceive']['status']       = 1;
                    $transferReceive['TransferReceive']['created_by']   = $user['User']['id'];
                    $this->TransferReceive->saveAll($transferReceive);
                }
                // Save User Activity
                $this->Helper->saveUserActivity($user['User']['id'], 'Transfer Order', 'Save Add New', $transferOrderId);
            }else{
                $this->Helper->saveUserActivity($user['User']['id'], 'Transfer Order', 'Save Add New (Error)');
                $transferOrderId = 0;
                $error = 1;
            }
            // Assign Value to Out Put
            $result['id'] = $transferOrderId;
            $result['error'] = $error;
            echo json_encode($result);
            exit;
        }
        $this->Helper->saveUserActivity($user['User']['id'], 'Transfer Order', 'Add New');
        $companies = ClassRegistry::init('Company')->find('all', array('joins' => array(array('table' => 'user_companies', 'type' => 'inner', 'conditions' => array('user_companies.company_id=Company.id'))),'fields' => array('Company.id', 'Company.name', 'Company.vat_calculate'), 'conditions' => array('Company.is_active = 1', 'user_companies.user_id=' . $user['User']['id'])));
        $branches  = ClassRegistry::init('Branch')->find('all', array('joins' => array(array('table' => 'user_branches', 'type' => 'inner', 'conditions' => array('user_branches.branch_id=Branch.id')),array('table' => 'module_code_branches AS ModuleCodeBranch', 'type' => 'left', 'conditions' => array('ModuleCodeBranch.branch_id=Branch.id'))), 'fields' => array('Branch.id', 'Branch.name', 'Branch.company_id', 'ModuleCodeBranch.to_code', 'Branch.currency_id', 'Currency.symbol'), 'conditions' => array('Branch.is_active = 1', 'user_branches.user_id=' . $user['User']['id'])));
        $fromLocationGroups = ClassRegistry::init('LocationGroup')->find('list', array('joins' => array(array('table' => 'user_location_groups', 'type' => 'inner', 'conditions' => array('user_location_groups.location_group_id=LocationGroup.id')),  array('table' => 'locations', 'type' => 'inner', 'conditions' => array('locations.location_group_id=LocationGroup.id'))), 'conditions' => array('user_location_groups.user_id=' . $user['User']['id'], 'LocationGroup.is_active' => '1', 'LocationGroup.location_group_type_id != 1'), 'group' => 'LocationGroup.id'));
        $toLocationGroups   = ClassRegistry::init('LocationGroup')->find('list', array('joins' => array(array('table' => 'locations', 'type' => 'inner', 'conditions' => array('locations.location_group_id=LocationGroup.id'))), "conditions" => array('LocationGroup.is_active' => '1', 'LocationGroup.location_group_type_id != 1'), 'group' => 'LocationGroup.id'));
        $this->set(compact("fromLocationGroups", "toLocationGroups", "companies", "branches"));
    }
    
    function edit($id = null) {
        $this->layout = 'ajax';
        if (!$id && empty($this->data)) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $user = $this->getCurrentUser();
        if (!empty($this->data)) {
            $to = $this->TransferOrder->read(null, $this->data['TransferOrder']['id']);
            // Varaible Process Save
            $access = true;
            if($this->data['TransferOrder']['id'] != 35){
                for ($i = 0; $i < sizeof($_POST['product_id']); $i++) {
                    $sqlInv = mysql_query("SELECT SUM((inv.total_pb + total_to_in + total_cm + total_cycle + total_cus_consign_in) - (inv.total_so + inv.total_pos + inv.total_pbc + inv.total_to_out + total_cus_consign_out + inv.total_order)) AS total_qty FROM {$_POST['location_from_id'][$i]}_inventory_total_details AS inv WHERE inv.product_id = {$_POST['product_id'][$i]} AND inv.lots_number = '{$_POST['lots_number'][$i]}' AND inv.expired_date = '{$_POST['expired_date'][$i]}' AND inv.date <= '{$this->data['TransferOrder']['order_date']}' GROUP BY inv.product_id");
                    $rowInv = mysql_fetch_array($sqlInv);
                    // T.O By Edit Order
                    $losNum   = $_POST['lots_number'][$i]!=""?$_POST['lots_number'][$i]:'0';
                    $expDate  = $_POST['expired_date'][$i]!=""?$_POST['expired_date'][$i]:'0000-00-00';
                    $sqlOrder = mysql_query("SELECT SUM(qty) AS qty FROM stock_orders WHERE transfer_order_id = ".$this->data['TransferOrder']['id']." AND product_id = ".$_POST['product_id'][$i]." AND location_group_id = {$to['TransferOrder']['from_location_group_id']} AND location_id = {$_POST['location_from_id'][$i]} AND lots_number = '{$losNum}' AND expired_date = '{$expDate}' AND date = '".$to['TransferOrder']['order_date']."' GROUP BY product_id");
                    $rowOrder = mysql_fetch_array($sqlOrder);
                    $totalOrder = ($_POST['qty'][$i] * $_POST['conversion'][$i]);
                    if($totalOrder > ($rowInv[0] + $rowOrder[0])){
                        $access = false;
                    }
                }
            }
            if($access == false){
                $result['id'] = 0;
                $result['error'] = 2;
                echo json_encode($result);
                exit;
            }
            $this->loadModel('StockOrder');
            if($to['TransferOrder']['status'] != 3){
                $result['id']    = 0;
                $result['error'] = 1;
                echo json_encode($result);
                exit;
            }
            $statuEdit = "-1";
            $dateNow   = date("Y-m-d H:i:s");
            if($this->data['TransferOrder']['company_id'] != $to['TransferOrder']['company_id']){
                $statuEdit = 0;
            }
            // Update Status Transfer Order Edit
            $this->TransferOrder->updateAll(
                    array('TransferOrder.status' => $statuEdit, "modified_by"=>$user['User']['id']), array('TransferOrder.id' => $this->data['TransferOrder']['id'])
            );
            // Transfer Order Detail
            $transferOrderDetails = ClassRegistry::init('TransferOrderDetail')->find('all', array("conditions" => array("TransferOrderDetail.transfer_order_id" => $this->data['TransferOrder']['id'])));
            foreach($transferOrderDetails AS $transferOrderDetail){
                if($this->data['TransferOrder']['id'] == 35){
                    $qty_inv = 12;
                } else {
                    $qty_inv = $this->Helper->replaceThousand($transferOrderDetail['TransferOrderDetail']['qty']) * $this->Helper->replaceThousand($transferOrderDetail['TransferOrderDetail']['conversion']);
                }
                $dateNow = $to['TransferOrder']['order_date'];
                /* Transfer Order Out */
                // Update Inventory (Transfer Out)
                $dataOut = array();
                $dataOut['module_type']       = 2;
                $dataOut['transfer_order_id'] = $to['TransferOrder']['id'];
                $dataOut['product_id']        = $transferOrderDetail['TransferOrderDetail']['product_id'];
                $dataOut['location_id']       = $transferOrderDetail['TransferOrderDetail']['location_from_id'];
                $dataOut['location_group_id'] = $to['TransferOrder']['from_location_group_id'];
                $dataOut['lots_number']  = $transferOrderDetail['TransferOrderDetail']['lots_number'];
                $dataOut['expired_date'] = $transferOrderDetail['TransferOrderDetail']['expired_date'];
                $dataOut['date']         = $dateNow;
                $dataOut['total_qty']    = $qty_inv;
                $dataOut['total_order']  = $qty_inv;
                $dataOut['total_free']   = 0;
                $dataOut['user_id']      = $user['User']['id'];
                $dataOut['customer_id']  = "";
                $dataOut['vendor_id']    = "";
                $dataOut['unit_cost']    = 0;
                $dataOut['unit_price']   = 0;
                $dataOut['transaction_id'] = '';
                // Update Invetory Location
                $this->Inventory->saveInventory($dataOut);
                // Update Inventory Group
                $this->Inventory->saveGroupTotalDetail($dataOut);

                /* Transfer Order In */
                // Update Inventory (Transfer In)
                $dataIn = array();
                $dataIn['module_type']       = 3;
                $dataIn['transfer_order_id'] = $to['TransferOrder']['id'];
                $dataIn['product_id']        = $transferOrderDetail['TransferOrderDetail']['product_id'];
                $dataIn['location_id']       = $transferOrderDetail['TransferOrderDetail']['location_to_id'];
                $dataIn['location_group_id'] = $to['TransferOrder']['to_location_group_id'];
                $dataIn['lots_number']  = $transferOrderDetail['TransferOrderDetail']['lots_number'];
                $dataIn['expired_date'] = $transferOrderDetail['TransferOrderDetail']['expired_date'];
                $dataIn['date']         = $dateNow;
                $dataIn['total_qty']    = $qty_inv;
                $dataIn['total_order']  = $qty_inv;
                $dataIn['total_free']   = 0;
                $dataIn['user_id']      = $user['User']['id'];
                $dataIn['customer_id']  = "";
                $dataIn['vendor_id']    = "";
                $dataIn['unit_cost']    = 0;
                $dataIn['unit_price']   = 0;
                $dataIn['transaction_id'] = '';
                // Update Invetory Location
                $this->Inventory->saveInventory($dataIn);
                // Update Inventory Group
                $this->Inventory->saveGroupTotalDetail($dataIn);
            }
            // Check Warehouse Option Allow Approval
//            $warehouseOption = ClassRegistry::init('LocationGroup')->findById($this->data['TransferOrder']['from_location_group_id']);
            // Load Begin / Create for Transfer Order
            $this->TransferOrder->create();
            // Insert Into Transfer Order
            $transferOrder = array();
            $transferOrder['TransferOrder']['sys_code']                 = md5(rand().strtotime(date("Y-m-d H:i:s")).$user['User']['id']);
            $transferOrder['TransferOrder']['company_id']               = $this->data['TransferOrder']['company_id'];
            $transferOrder['TransferOrder']['branch_id']                = $this->data['TransferOrder']['branch_id'];
            $transferOrder['TransferOrder']['from_location_group_id']   = $this->data['TransferOrder']['from_location_group_id'];
            $transferOrder['TransferOrder']['to_location_group_id']     = $this->data['TransferOrder']['to_location_group_id'];
            $transferOrder['TransferOrder']['to_code']                  = $to['TransferOrder']['to_code'];
            $transferOrder['TransferOrder']['order_date']               = $this->data['TransferOrder']['order_date'];
            $transferOrder['TransferOrder']['fulfillment_date']         = ((empty($this->data['TransferOrder']['fulfillment_date']))?'0000-00-00':$this->data['TransferOrder']['fulfillment_date']);
            $transferOrder['TransferOrder']['note']                     = $this->data['TransferOrder']['note'];
            $transferOrder['TransferOrder']['total_cost']               = $this->data['TransferOrder']['total_cost'];
            $transferOrder['TransferOrder']['balance']                  = $this->data['TransferOrder']['total_cost'];
            $transferOrder['TransferOrder']['is_approve']               = 3;
            $transferOrder['TransferOrder']['status']                   = 3;
            $transferOrder['TransferOrder']['created_by']               = $user['User']['id'];
//            if($warehouseOption['LocationGroup']['stock_tranfer_confirm'] == 1){
//                $transferOrder['TransferOrder']['is_approve'] = 0;
//            }
            if ($this->TransferOrder->save($transferOrder)) {
                // Get Transfer Order Id
                $error = 0;
                $transferOrderId = $this->TransferOrder->id;
                if($this->data['TransferOrder']['company_id'] != $to['TransferOrder']['company_id']){
                    // Get Module Code
                    $modCode = $this->Helper->getModuleCode($this->data['TransferOrder']['to_code'], $transferOrderId, 'to_code', 'transfer_orders', 'status >= 0 AND branch_id = '.$this->data['TransferOrder']['branch_id']);
                    // Updaet Module Code
                    $transferOrder['TransferOrder']['to_code'] = $modCode;
                    mysql_query("UPDATE transfer_orders SET to_code = '".$modCode."' WHERE id = ".$transferOrderId);
                }
                // Load Begin / Create for Transfer Order
                $this->loadModel('TransferOrderDetail');
                $this->loadModel('TransferReceiveResult');
                $this->loadModel('TransferReceive');
                $this->TransferReceiveResult->create();
                $transferRecResult = array();
                $transferRecResult['TransferReceiveResult']['sys_code'] = md5(rand().strtotime(date("Y-m-d H:i:s")).$user['User']['id']);
                $transferRecResult['TransferReceiveResult']['created']  = $dateNow;
                $transferRecResult['TransferReceiveResult']['code']     = 'TR';
                $transferRecResult['TransferReceiveResult']['transfer_order_id'] = $transferOrderId;
                $transferRecResult['TransferReceiveResult']['date'] = $this->data['TransferOrder']['order_date'];
                $transferRecResult['TransferReceiveResult']['created_by'] = $user['User']['id'];
                $this->TransferReceiveResult->save($transferRecResult);
                $transferReceiveId = $this->TransferReceiveResult->id;
                // Get Module Code
                $modTRCode = $this->Helper->getModuleCode('TR', $transferReceiveId, 'code', 'transfer_receive_results', '1');
                // Updaet Module Code
                $transferRecResult['TransferReceiveResult']['code'] = $modTRCode;
                mysql_query("UPDATE transfer_receive_results SET code = '".$modTRCode."' WHERE id = ".$transferReceiveId);
                // Insert Transfer Detail
                for ($i = 0; $i < sizeof($_POST['product_id']); $i++) {
                    $this->TransferOrderDetail->create();
                    $transferOrderDetail = array();
                    $transferOrderDetail['TransferOrderDetail']['sys_code']             = md5(rand().strtotime(date("Y-m-d H:i:s")).$user['User']['id']);
                    $transferOrderDetail['TransferOrderDetail']['transfer_order_id']    = $transferOrderId;
                    $transferOrderDetail['TransferOrderDetail']['lots_number']          = $_POST['lots_number'][$i]!=""?$_POST['lots_number'][$i]:'0';
                    $transferOrderDetail['TransferOrderDetail']['expired_date']         = $_POST['expired_date'][$i]!=""?$_POST['expired_date'][$i]:'0000-00-00';
                    $transferOrderDetail['TransferOrderDetail']['location_from_id']     = $_POST['location_from_id'][$i];
                    $transferOrderDetail['TransferOrderDetail']['location_to_id']       = $_POST['location_to_id'][$i];
                    $transferOrderDetail['TransferOrderDetail']['product_id']           = $_POST['product_id'][$i];
                    $transferOrderDetail['TransferOrderDetail']['qty']                  = $_POST['qty'][$i];
                    $transferOrderDetail['TransferOrderDetail']['qty_uom_id']           = $_POST['qty_uom_id'][$i];
                    $transferOrderDetail['TransferOrderDetail']['conversion']           = $_POST['conversion'][$i];
                    $transferOrderDetail['TransferOrderDetail']['unit_cost']            = $_POST['unit_cost'][$i];
                    $transferOrderDetail['TransferOrderDetail']['total_cost']           = $_POST['total_cost'][$i];
                    $this->TransferOrderDetail->save($transferOrderDetail);
                    $transferOrderDetailId = $this->TransferOrderDetail->id;
                    $qty_inv = $this->Helper->replaceThousand($_POST['qty'][$i]) * $this->Helper->replaceThousand($_POST['conversion'][$i]);
                    /* Transfer Order Out */
                    // Update Inventory (Transfer Out)
                    $dataOut = array();
                    $dataOut['module_type']       = 3;
                    $dataOut['transfer_order_id'] = $transferOrderId;
                    $dataOut['product_id']        = $_POST['product_id'][$i];
                    $dataOut['location_id']       = $_POST['location_from_id'][$i];
                    $dataOut['location_group_id'] = $transferOrder['TransferOrder']['from_location_group_id'];
                    $dataOut['lots_number']  = $transferOrderDetail['TransferOrderDetail']['lots_number'];
                    $dataOut['expired_date'] = $transferOrderDetail['TransferOrderDetail']['expired_date'];
                    $dataOut['date']         = $dateNow;
                    $dataOut['total_qty']    = $qty_inv;
                    $dataOut['total_order']  = $qty_inv;
                    $dataOut['total_free']   = 0;
                    $dataOut['user_id']      = $user['User']['id'];
                    $dataOut['customer_id']  = "";
                    $dataOut['vendor_id']    = "";
                    $dataOut['unit_cost']    = 0;
                    $dataOut['unit_price']   = 0;
                    $dataOut['transaction_id']  = '';
                    // Update Invetory Location
                    $this->Inventory->saveInventory($dataOut);
                    // Update Inventory Group
                    $this->Inventory->saveGroupTotalDetail($dataOut);

                    /* Transfer Order In */
                    // Update Inventory (Transfer In)
                    $dataIn = array();
                    $dataIn['module_type']       = 2;
                    $dataIn['transfer_order_id'] = $transferOrderId;
                    $dataIn['product_id']        = $_POST['product_id'][$i];
                    $dataIn['location_id']       = $_POST['location_to_id'][$i];
                    $dataIn['location_group_id'] = $transferOrder['TransferOrder']['to_location_group_id'];
                    $dataIn['lots_number']  = $transferOrderDetail['TransferOrderDetail']['lots_number'];
                    $dataIn['expired_date'] = $transferOrderDetail['TransferOrderDetail']['expired_date'];
                    $dataIn['date']         = $dateNow;
                    $dataIn['total_qty']    = $qty_inv;
                    $dataIn['total_order']  = $qty_inv;
                    $dataIn['total_free']   = 0;
                    $dataIn['user_id']      = $user['User']['id'];
                    $dataIn['customer_id']  = "";
                    $dataIn['vendor_id']    = "";
                    $dataIn['unit_cost']    = 0;
                    $dataIn['unit_price']   = 0;
                    $dataIn['transaction_id'] = '';
                    // Update Invetory Location
                    $this->Inventory->saveInventory($dataIn);
                    // Update Inventory Group
                    $this->Inventory->saveGroupTotalDetail($dataIn);

                    // Save Transfer Receive
                    $this->TransferReceive->create();
                    $transferReceive = array();
                    $transferReceive['TransferReceive']['transfer_receive_result_id'] = $transferReceiveId;
                    $transferReceive['TransferReceive']['transfer_order_detail_id']   = $transferOrderDetailId;
                    $transferReceive['TransferReceive']['transfer_order_id']          = $transferOrderId;
                    $transferReceive['TransferReceive']['lots_number']  = $transferOrderDetail['TransferOrderDetail']['lots_number'];
                    $transferReceive['TransferReceive']['expired_date'] = $transferOrderDetail['TransferOrderDetail']['expired_date'];
                    $transferReceive['TransferReceive']['product_id']   = $_POST['product_id'][$i];
                    $transferReceive['TransferReceive']['qty']          = $_POST['qty'][$i];
                    $transferReceive['TransferReceive']['qty_uom_id']   = $_POST['qty_uom_id'][$i];
                    $transferReceive['TransferReceive']['conversion']   = $_POST['conversion'][$i];
                    $transferReceive['TransferReceive']['status']       = 1;
                    $transferReceive['TransferReceive']['created_by']   = $user['User']['id'];
                    $this->TransferReceive->saveAll($transferReceive);
                }
                // Save User Activity
                $this->Helper->saveUserActivity($user['User']['id'], 'Transfer Order', 'Save Edit', $id, $transferOrderId);
            }else{
                $this->Helper->saveUserActivity($user['User']['id'], 'Transfer Order', 'Save Edit (Error)', $id);
                $transferOrderId = 0;
                $error = 1;
            }
            // Assign Value to Out Put
            $result['id'] = $transferOrderId;
            $result['error'] = $error;
            echo json_encode($result);
            exit;
        }
        if (empty($this->data)) {
            $this->Helper->saveUserActivity($user['User']['id'], 'Transfer Order', 'Edit', $id);
            $this->data = $this->TransferOrder->read(null, $id);
            $companies = ClassRegistry::init('Company')->find('all', array('joins' => array(array('table' => 'user_companies', 'type' => 'inner', 'conditions' => array('user_companies.company_id=Company.id'))), 'fields' => array('Company.id', 'Company.name', 'Company.vat_calculate'), 'conditions' => array('Company.is_active = 1', 'user_companies.user_id=' . $user['User']['id'])));
            $branches = ClassRegistry::init('Branch')->find('all', array('joins' => array(array('table' => 'user_branches', 'type' => 'inner', 'conditions' => array('user_branches.branch_id=Branch.id')),array('table' => 'module_code_branches AS ModuleCodeBranch', 'type' => 'left', 'conditions' => array('ModuleCodeBranch.branch_id=Branch.id'))), 'fields' => array('Branch.id', 'Branch.name', 'Branch.company_id', 'ModuleCodeBranch.to_code', 'Branch.currency_id', 'Currency.symbol'), 'conditions' => array('Branch.is_active = 1', 'user_branches.user_id=' . $user['User']['id'])));
            $fromLocationGroups = ClassRegistry::init('LocationGroup')->find('list', array('joins' => array(array('table' => 'user_location_groups', 'type' => 'inner', 'conditions' => array('user_location_groups.location_group_id=LocationGroup.id')), array('table' => 'locations', 'type' => 'inner', 'conditions' => array('locations.location_group_id=LocationGroup.id'))), 'conditions' => array('user_location_groups.user_id=' . $user['User']['id'], 'LocationGroup.is_active' => '1', 'LocationGroup.location_group_type_id != 1'), 'group' => 'LocationGroup.id'));
            $toLocationGroups   = ClassRegistry::init('LocationGroup')->find('list', array('joins' => array(array('table' => 'locations', 'type' => 'inner', 'conditions' => array('locations.location_group_id=LocationGroup.id'))), "conditions" => array('LocationGroup.is_active' => '1', 'LocationGroup.location_group_type_id != 1'), 'group' => 'LocationGroup.id'));
            $transferOrderDetails = ClassRegistry::init('TransferOrderDetail')->find('all', array("conditions" => array("TransferOrderDetail.transfer_order_id"=>$this->data['TransferOrder']['id'])));
            $this->set(compact("fromLocationGroups", "toLocationGroups", "transferOrderDetails", "companies", "branches"));
        }
    }

    function delete($id = null) {
        if (!$id) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $dateNow  = date("Y-m-d H:i:s");
        $user = $this->getCurrentUser();
        $this->data = $this->TransferOrder->read(null, $id);
        if($this->data['TransferOrder']['status'] == 3){
            mysql_query("UPDATE `transfer_orders` SET `status`=0, `modified`='".$dateNow."', `modified_by`=".$user['User']['id']." WHERE `id`=".$id.";");
            // Transfer Order Detail
            $transferOrderDetails = ClassRegistry::init('TransferOrderDetail')->find('all', array("conditions" => array("TransferOrderDetail.transfer_order_id" => $this->data['TransferOrder']['id'])));
            foreach($transferOrderDetails AS $transferOrderDetail){
                $qty_inv = $this->Helper->replaceThousand($transferOrderDetail['TransferOrderDetail']['qty']) * $this->Helper->replaceThousand($transferOrderDetail['TransferOrderDetail']['conversion']);
                $dateNow = $this->data['TransferOrder']['order_date'];
                /* Transfer Order Out */
                // Update Inventory (Transfer Out)
                $dataOut = array();
                $dataOut['module_type']       = 2;
                $dataOut['transfer_order_id'] = $this->data['TransferOrder']['id'];
                $dataOut['product_id']        = $transferOrderDetail['TransferOrderDetail']['product_id'];
                $dataOut['location_id']       = $transferOrderDetail['TransferOrderDetail']['location_from_id'];
                $dataOut['location_group_id'] = $this->data['TransferOrder']['from_location_group_id'];
                $dataOut['lots_number']  = $transferOrderDetail['TransferOrderDetail']['lots_number'];
                $dataOut['expired_date'] = $transferOrderDetail['TransferOrderDetail']['expired_date'];
                $dataOut['date']         = $dateNow;
                $dataOut['total_qty']    = $qty_inv;
                $dataOut['total_order']  = $qty_inv;
                $dataOut['total_free']   = 0;
                $dataOut['user_id']      = $user['User']['id'];
                $dataOut['customer_id']  = "";
                $dataOut['vendor_id']    = "";
                $dataOut['unit_cost']    = 0;
                $dataOut['unit_price']   = 0;
                $data['transaction_id']  = '';
                // Update Invetory Location
                $this->Inventory->saveInventory($dataOut);
                // Update Inventory Group
                $this->Inventory->saveGroupTotalDetail($dataOut);

                /* Transfer Order In */
                // Update Inventory (Transfer In)
                $dataIn = array();
                $dataIn['module_type']       = 3;
                $dataIn['transfer_order_id'] = $this->data['TransferOrder']['id'];
                $dataIn['product_id']        = $transferOrderDetail['TransferOrderDetail']['product_id'];
                $dataIn['location_id']       = $transferOrderDetail['TransferOrderDetail']['location_to_id'];
                $dataIn['location_group_id'] = $this->data['TransferOrder']['to_location_group_id'];
                $dataIn['lots_number']  = $transferOrderDetail['TransferOrderDetail']['lots_number'];
                $dataIn['expired_date'] = $transferOrderDetail['TransferOrderDetail']['expired_date'];
                $dataIn['date']         = $dateNow;
                $dataIn['total_qty']    = $qty_inv;
                $dataIn['total_order']  = $qty_inv;
                $dataIn['total_free']   = 0;
                $dataIn['user_id']      = $user['User']['id'];
                $dataIn['customer_id']  = "";
                $dataIn['vendor_id']    = "";
                $dataIn['unit_cost']    = 0;
                $dataIn['unit_price']   = 0;
                $data['transaction_id'] = '';
                // Update Invetory Location
                $this->Inventory->saveInventory($dataIn);
                // Update Inventory Group
                $this->Inventory->saveGroupTotalDetail($dataIn);
            }
            // Save User Activity
            $this->Helper->saveUserActivity($user['User']['id'], 'Transfer Order', 'Delete', $id);
            echo MESSAGE_DATA_HAS_BEEN_DELETED;
        } else {
            $this->Helper->saveUserActivity($user['User']['id'], 'Transfer Order', 'Error Delete', $id);
            echo MESSAGE_DATA_COULD_NOT_BE_SAVED;
        }
        exit;
    }

    function printInvoice($receiptId = null) {
        if (!empty($receiptId)) {
            $this->layout = 'ajax';
            $this->data = $this->TransferOrder->read(null, $receiptId);
            $fromLocationGroups = ClassRegistry::init('LocationGroup')->find('first', array('conditions' => array('LocationGroup.id' => $this->data['TransferOrder']['from_location_group_id'], 'LocationGroup.is_active' => '1')));
            $toLocationGroups   = ClassRegistry::init('LocationGroup')->find('first', array("conditions" => array('LocationGroup.id' => $this->data['TransferOrder']['to_location_group_id'], 'LocationGroup.is_active' => '1')));
            $transferOrderDetails = ClassRegistry::init('TransferOrderDetail')->find('all', array("conditions" => array("TransferOrderDetail.transfer_order_id"=>$this->data['TransferOrder']['id'])));
            $this->set(compact("fromLocationGroups", "toLocationGroups","transferOrderDetails"));
        } else {
            exit;
        }
    }
    
    function printConsignment($receiptId = null, $head = 2) {
        if (!empty($receiptId)) {
            $this->layout = 'ajax';
            $this->data = $this->TransferOrder->read(null, $receiptId);
            $fromLocationGroups = ClassRegistry::init('LocationGroup')->find('first', array('conditions' => array('LocationGroup.id' => $this->data['TransferOrder']['from_location_group_id'], 'LocationGroup.is_active' => '1')));
            $toLocationGroups   = ClassRegistry::init('LocationGroup')->find('first', array("conditions" => array('LocationGroup.id' => $this->data['TransferOrder']['to_location_group_id'], 'LocationGroup.is_active' => '1')));
            $transferOrderDetails = ClassRegistry::init('TransferOrderDetail')->find('all', array("conditions" => array("TransferOrderDetail.transfer_order_id"=>$this->data['TransferOrder']['id'])));
            $this->set(compact("fromLocationGroups", "toLocationGroups", "transferOrderDetails", "head"));
        } else {
            exit;
        }
    }
    
    function searchProductCode($companyId = null, $branchId = null, $code = null, $locationGroupFrom = null, $locationGroupTo = null) {
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
                                 'ProductBranch.branch_id = '.$branchId
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
        $product  = ClassRegistry::init('Product')->find('first', array(
                        'conditions' => array('Product.is_active' => 1, 'Product.is_packet' => 0, 'Product.company_id' => $companyId, "OR" => array ('Product.code' => $code, 'Product.barcode' => $code)), 
                        'fields' => array('Product.id', 'Product.code', 'Product.barcode', 'Product.name', 'Product.price_uom_id', 'Product.small_val_uom', 'Product.unit_cost'),
                        'joins' => $joins,
                        'group' => array(
                            'Product.id'
                        )
                    ));
        $uomList  = array();
        $location = array();
        $products = array();
        if(!empty($product)){
            // Get UOM List
            $uomList['Uom']['option'] = "";
            $query = mysql_query("SELECT id,name,abbr,1 AS conversion FROM uoms WHERE id=".$product['Product']['price_uom_id']."
                                UNION
                                SELECT id,name,abbr,(SELECT value FROM uom_conversions WHERE is_active=1 AND from_uom_id=".$product['Product']['price_uom_id']." AND to_uom_id=uoms.id) AS conversion FROM uoms WHERE id IN (SELECT to_uom_id FROM uom_conversions WHERE is_active=1 AND from_uom_id=".$product['Product']['price_uom_id'].")
                                ORDER BY conversion ASC");
            $i = 1;
            while($data=mysql_fetch_array($query)){
                $selected = "";
                $isMain   = "other";
                if($data['id'] == $product['Product']['price_uom_id']){   
                    $selected = ' selected="selected" ';
                }
                if($data['id'] == $product['Product']['price_uom_id']){
                    $isMain = "first";
                }
                $uomList['Uom']['option'] .= '<option '.$selected.' data-item="'.$isMain.'" conversion="'.$data['conversion'].'" value="'.$data['id'].'">'.trim($data['name']).'</option>';
                $i++;
            }
            // Calucate Location From 
            $location['LocationFrom']['option'] = '<option value="">'.INPUT_SELECT.'</option>';
            $sqlLocation = mysql_query("SELECT id, name FROM locations WHERE is_active = 1 AND location_group_id = {$locationGroupFrom} ORDER BY name");
            while($rowLocation = mysql_fetch_array($sqlLocation)){
                $location['LocationFrom']['option'] .= '<option value="'.$rowLocation['id'].'">'.htmlspecialchars(trim($rowLocation['name']), ENT_QUOTES, 'UTF-8').'</option>';
            } 
            // Get Location To
            $location['LocationTo']['option'] = '<option value="">'.INPUT_SELECT.'</option>';
            $sqlLocationTo = mysql_query("SELECT id, name FROM locations WHERE is_active = 1 AND location_group_id = {$locationGroupTo} ORDER BY name");
            while($rowLocationTo = mysql_fetch_array($sqlLocationTo)){
                $location['LocationTo']['option'] .= '<option value="'.$rowLocationTo['id'].'">'.htmlspecialchars(trim($rowLocationTo['name']), ENT_QUOTES, 'UTF-8').'</option>';
            }
            // Get Product Information
            $products['Product']['id']      = $product['Product']['id'];
            $products['Product']['code']    = $product['Product']['code'];
            $products['Product']['barcode'] = $product['Product']['barcode'];
            $products['Product']['name']    = $product['Product']['name'];
            $products['Product']['lots_number']   = "";
            $products['Product']['date_expired']  = "";
            $products['Product']['total_qty']     = 0;
            $products['Product']['location_id']   = "";
            $products['Product']['small_val_uom'] = $product['Product']['small_val_uom'];
            $products['Product']['total_qty_label'] = 0;
            $products['Product']['unit_cost'] = $product['Product']['unit_cost'];
            echo json_encode($products)."||".json_encode($location)."||".json_encode($uomList);
        }else{
            echo '';
        }
        exit;
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
                        ),
                        'joins' => $joins,
                        'group' => array(
                            'Product.id'
                        )
                    ));
        $this->set(compact('products'));
    }
    
    function getProductTotalQty($id = null, $locationGroupFrom = null, $transferId = null){
        $this->layout = "ajax";
        if(empty($id) && empty($locationGroupFrom)){
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $id = $this->Helper->preventInput($id);
        $date = $this->Helper->preventInput($_POST['date']);
        $locationGroupFrom = $this->Helper->preventInput($locationGroupFrom);
        $this->set(compact("id", "locationGroupFrom", "date", "transferId"));
    }
    
    function product($companyId = null, $branchId = null, $locationGroupFrom = null, $locationGroupTo = null){
        $this->layout = "ajax";
        $locationGroupFrom = $this->Helper->preventInput($locationGroupFrom);
        $locationGroupTo = $this->Helper->preventInput($locationGroupTo);
        $this->set(compact("companyId", "branchId", "locationGroupFrom", "locationGroupTo"));
    }
    
    function productAjax($companyId = null, $branchId = null,$locationGroupFrom = null, $locationGroupTo = null, $category = null){
        $this->layout = "ajax";
        $locationGroupFrom = $this->Helper->preventInput($locationGroupFrom);
        $locationGroupTo = $this->Helper->preventInput($locationGroupTo);
        $this->set(compact("companyId", "branchId", "locationGroupFrom", "locationGroupTo", "category"));
    }
    
    function requestStock($companyId = null){
        $this->layout = "ajax";
        $this->set(compact("companyId"));
    }
    
    function requestStockAjax($companyId = null){
        $this->layout = "ajax";
        $this->set(compact("companyId"));
    }
    
    function getProductFromRequest($requestId = null, $fromLocationGroup = null, $toLocationGroup = null, $date = null){
        $this->layout = "ajax";
        $result = array();
        if (!$requestId) {
            $result['error'] = 1;
            echo json_encode($result);
            exit;
        }
        $lotDisplay = '';
        $sqlSettingUomDeatil = mysql_query("SELECT uom_detail_option FROM setting_options");
        $rowSettingUomDetail = mysql_fetch_array($sqlSettingUomDeatil);
        if($rowSettingUomDetail[0] == 0){ 
            $lotDisplay = 'display: none;';
        }
        $rowList = array();
        $rowLbl  = "";
        $index   = 0;
        $sqlRequestDetail = mysql_query("SELECT products.code AS code, products.barcode AS barcode, products.name AS name, products.price_uom_id AS price_uom_id, products.small_val_uom AS small_val_uom, request_stock_details.product_id AS product_id, request_stock_details.qty_uom_id AS qty_uom_id, request_stock_details.qty AS qty, SUM(request_stock_details.qty * request_stock_details.conversion) AS total_request FROM request_stock_details INNER JOIN products ON products.id = request_stock_details.product_id WHERE request_stock_details.request_stock_id = ".$requestId." GROUP BY request_stock_details.product_id, request_stock_details.qty_uom_id;");
        while($rowDetail = mysql_fetch_array($sqlRequestDetail)){
            $uomSmallVal   = 1;
            $uomSmallLabel = "";
            $mainUomName   = "";
            $totalQtyRequest = $rowDetail['total_request'];
            $checkInv = 0;
            $sqlLocation = mysql_query("SELECT id, name FROM locations WHERE is_active = 1 AND location_group_id = {$fromLocationGroup} ORDER BY name");
            while($rowLocation = mysql_fetch_array($sqlLocation)){
                $sqlInv = mysql_query("SELECT p.price_uom_id AS price_uom_id, p.id AS id, p.small_val_uom AS small_val_uom, p.code AS code, p.barcode AS barcode, p.name AS name, inv.lots_number AS lots_number, inv.expired_date AS date_expired, SUM((inv.total_pb + total_to_in + total_cm + total_cycle) - (inv.total_so + inv.total_pos + inv.total_pbc + inv.total_to_out + inv.total_order)) AS total_qty, u.abbr AS abbr FROM {$rowLocation['id']}_inventory_total_details AS inv INNER JOIN products AS p ON p.id = inv.product_id INNER JOIN uoms AS u ON u.id = p.price_uom_id WHERE inv.product_id = {$rowDetail['product_id']} AND inv.date <= '{$date}' GROUP BY inv.product_id, inv.lots_number, inv.expired_date ORDER BY p.code");
                if(@mysql_num_rows($sqlInv)){
                    while($row = mysql_fetch_array($sqlInv)){
                        $checkInv = 1;
                        if($totalQtyRequest > 0){
                            if($totalQtyRequest > $row['total_qty']){
                                // Get Small UOm
                                $sqlUomSm = mysql_query("SELECT id, abbr FROM uoms WHERE id = (SELECT id FROM uom_conversions WHERE from_uom_id = {$row['price_uom_id']} AND is_small_uom = 1 AND is_active = 1 ORDER BY id DESC LIMIT 1)");
                                if(mysql_num_rows($sqlUomSm)){
                                    $rowUomSm = mysql_fetch_array($sqlUomSm);
                                    $uomSmallId = $rowUomSm['id'];
                                    $uomSmallLabel = $rowUomSm['abbr'];
                                }else{
                                    $uomSmallId = $row['price_uom_id'];
                                }
                                $uomSmallVal = $row['small_val_uom'];
                                $mainUomName = $row['abbr'];
                                $conversion  = 1;
                                // Get UOM List
                                // UoM As Small & Conversion Small
                                $uomList = "";
                                $query = mysql_query("SELECT id,name,abbr,1 AS conversion FROM uoms WHERE id=".$rowDetail['price_uom_id']."
                                                    UNION
                                                    SELECT id,name,abbr,(SELECT value FROM uom_conversions WHERE is_active=1 AND from_uom_id=".$rowDetail['price_uom_id']." AND to_uom_id=uoms.id) AS conversion FROM uoms WHERE id IN (SELECT to_uom_id FROM uom_conversions WHERE is_active=1 AND from_uom_id=".$rowDetail['price_uom_id'].")
                                                    ORDER BY conversion ASC");
                                $i = 1;
                                $length = mysql_num_rows($query);
                                while($data=mysql_fetch_array($query)){
                                    $selected = "";
                                    $isMain   = "other";
                                    $isSmall  = 0;
                                    if($data['id'] == $uomSmallId){   
                                        $selected   = ' selected="selected" ';
                                    }
                                    if($data['id'] == $rowDetail['price_uom_id']){
                                        $isMain = "first";
                                    }
                                    // Check Is Small UOM
                                    if($length == $i){
                                        $isSmall = 1;
                                    }
                                    $uomList .= '<option '.$selected.' data-sm="'.$isSmall.'" data-item="'.$isMain.'" conversion="'.$data['conversion'].'" value="'.$data['id'].'">'.$data['name'].'</option>';
                                    $i++;
                                }
                                // Open Tr
                                $rowLbl .= '<tr class="recordTODetail">';
                                // Index
                                $rowLbl .= '<td class="first" style="width:4%;">'.++$index.'</td>';
                                // UPC
                                $rowLbl .= '<td style="width:8%;"><span class="PUCLabel">'.$rowDetail['barcode'].'</span></td>';
                                // SKU
                                $rowLbl .= '<td style="width:8%;"><span class="SKULabel">'.$rowDetail['code'].'</span></td>';
                                // Product
                                $rowLbl .= '<td style="width:16%;">';
                                $rowLbl .= '<input type="hidden" value="'.$rowDetail['product_id'].'" class="product_id" id="product_id_'.$index.'" name="product_id[]" />';
                                $rowLbl .= '<input type="text"   value="'.str_replace('"', '&quot;', $rowDetail['name']).'"  class="productName" id="productName_'.$index.'" style="width: 90%;" readonly="readonly" />';
                                $rowLbl .= '</td>';
                                // Lot Number
                                $rowLbl .= '<td style="width:10%; '.$lotDisplay.'">';
                                $rowLbl .= '<input type="hidden" value="'.$row['lots_number'].'" class="lots_number" name="lots_number[]" />';
                                $rowLbl .= '<span class="lotsNoLabel">'.$row['lots_number'].'</span>';
                                $rowLbl .= '</td>';
                                // Expired Date
                                if($row['date_expired'] != '0000-00-00'){
                                    $dateExp = $this->Helper->dateShort($row['date_expired']);
                                }else{
                                    $dateExp = '';
                                }
                                $rowLbl .= '<td style="width:11%;">';
                                $rowLbl .= '<input type="hidden" value="'.$row['date_expired'].'" class="expired_date" name="expired_date[]" />';
                                $rowLbl .= '<span class="ExpDateLabel">'.$dateExp.'</span>';
                                $rowLbl .= '</td>';
                                // Location Group From
                                // Calucate Location From 
                                $optionLocFrom = '<option value="">'.INPUT_SELECT.'</option>';
                                $sqlLocationF = mysql_query("SELECT id, name FROM locations WHERE is_active = 1 AND location_group_id = {$fromLocationGroup} ORDER BY name");
                                while($rowLocationF = mysql_fetch_array($sqlLocationF)){
                                    if($rowLocationF['id'] == $rowLocation['id']){
                                        $selectedFrom = 'selected="selected"';
                                    }else{
                                        $selectedFrom = '';
                                    }
                                    $optionLocFrom .= '<option '.$selectedFrom.' value="'.$rowLocationF['id'].'">'.$rowLocationF['name'].'</option>';
                                }

                                $rowLbl .= '<td style="width:11%;">';
                                $rowLbl .= '<select id="location_from_id_'.$index.'" class="location_from_id validate[required]" name="location_from_id[]" style="width: 90%;">'.$optionLocFrom.'</select>';
                                $rowLbl .= '</td>';
                                // Location Group To
                                // Calucate Location To 
                                $optionLocTo = '<option value="">'.INPUT_SELECT.'</option>';
                                $sqlLocationT = mysql_query("SELECT id, name FROM locations WHERE is_active = 1 AND location_group_id = {$toLocationGroup} ORDER BY name");
                                while($rowLocationT = mysql_fetch_array($sqlLocationT)){
                                    $optionLocTo .= '<option value="'.$rowLocationT['id'].'">'.$rowLocationT['name'].'</option>';
                                }

                                $rowLbl .= '<td style="width:11%;">';
                                $rowLbl .= '<select id="location_to_id_'.$index.'" class="location_to_id validate[required]" name="location_to_id[]" style="width: 90%;">'.$optionLocTo.'</select>';
                                $rowLbl .= '</td>';
                                // Qty
                                $rowLbl .= '<td style="width:7%;">';
                                $rowLbl .= '<input type="hidden" value="'.$rowDetail['small_val_uom'].'" class="smallUomVal" />';
                                $rowLbl .= '<input type="hidden" value="'.$conversion.'" class="conversion" name="conversion[]" />';
                                $rowLbl .= '<input type="hidden" value="'.$row['total_qty'].'" class="total_qty" />';
                                $rowLbl .= '<input type="hidden" value="'.$this->Helper->showTotalQty($row['total_qty'], $mainUomName, $uomSmallVal, $uomSmallLabel).'"  class="total_qty_label" />';
                                $rowLbl .= '<input type="text" value="'.$row['total_qty'].'" name="qty[]" id="qty_'.$index.'" class="qty validate[required,min[1]]" style="width: 90%;" />';
                                $rowLbl .= '</td>';
                                // UOM
                                $rowLbl .= '<td style="width:10%;">';
                                $rowLbl .= '<select class="qty_uom_id" name="qty_uom_id[]" id="qty_uom_id'.$index.'" style="width: 90%;">'.$uomList.'</select>';
                                $rowLbl .= '</td>';
                                // Button Remove
                                $rowLbl .= '<td style="width:4%;">';
                                $rowLbl .= '<img alt="Remove" src="'.$this->webroot.'img/button/cross.png" class="btnRemoveTO" align="absmiddle" style="cursor: pointer;" onmouseover="Tip(\'Remove\')" />';
                                $rowLbl .= '</td>';
                                // Close Tr
                                $rowLbl .= '</tr>';
                                $totalQtyRequest = $totalQtyRequest - $row['total_qty'];
                            }else{
                                // Get Small UOm
                                $sqlUomSm = mysql_query("SELECT id, abbr FROM uoms WHERE id = (SELECT id FROM uom_conversions WHERE from_uom_id = {$row['price_uom_id']} AND is_small_uom = 1 AND is_active = 1 ORDER BY id DESC LIMIT 1)");
                                if(mysql_num_rows($sqlUomSm)){
                                    $rowUomSm = mysql_fetch_array($sqlUomSm);
                                    $uomSmallId = $rowUomSm['id'];
                                    $uomSmallLabel = $rowUomSm['abbr'];
                                }else{
                                    $uomSmallId = $row['price_uom_id'];
                                }
                                $uomSmallVal = $row['small_val_uom'];
                                $mainUomName = $row['abbr'];
                                $conversion  = 0;
                                // Get UOM List
                                $uomList = "";
                                $query = mysql_query("SELECT id,name,abbr,1 AS conversion FROM uoms WHERE id=".$rowDetail['price_uom_id']."
                                                    UNION
                                                    SELECT id,name,abbr,(SELECT value FROM uom_conversions WHERE is_active=1 AND from_uom_id=".$rowDetail['price_uom_id']." AND to_uom_id=uoms.id) AS conversion FROM uoms WHERE id IN (SELECT to_uom_id FROM uom_conversions WHERE is_active=1 AND from_uom_id=".$rowDetail['price_uom_id'].")
                                                    ORDER BY conversion ASC");
                                $i = 1;
                                $length = mysql_num_rows($query);
                                while($data=mysql_fetch_array($query)){
                                    $selected = "";
                                    $isMain   = "other";
                                    $isSmall  = 0;
                                    if($data['id'] == $rowDetail['qty_uom_id']){   
                                        $selected = ' selected="selected" ';
                                        $conversion = ($rowDetail['small_val_uom'] / $data['conversion']);
                                    }
                                    if($data['id'] == $rowDetail['price_uom_id']){
                                        $isMain = "first";
                                    }
                                    // Check Is Small UOM
                                    if($length == $i){
                                        $isSmall = 1;
                                    }
                                    $uomList .= '<option '.$selected.' data-sm="'.$isSmall.'" data-item="'.$isMain.'" conversion="'.$data['conversion'].'" value="'.$data['id'].'">'.$data['name'].'</option>';
                                    $i++;
                                }
                                // Open Tr
                                $rowLbl .= '<tr class="recordTODetail">';
                                // Index
                                $rowLbl .= '<td class="first" style="width:4%;">'.++$index.'</td>';
                                // UPC
                                $rowLbl .= '<td style="width:8%;"><span class="PUCLabel">'.$rowDetail['barcode'].'</span></td>';
                                // SKU
                                $rowLbl .= '<td style="width:8%;"><span class="SKULabel">'.$rowDetail['code'].'</span></td>';
                                // Product
                                $rowLbl .= '<td style="width:16%;">';
                                $rowLbl .= '<input type="hidden" value="'.$rowDetail['product_id'].'" class="product_id" id="product_id_'.$index.'" name="product_id[]" />';
                                $rowLbl .= '<input type="text"   value="'.str_replace('"', '&quot;', $rowDetail['name']).'"  class="productName" id="productName_'.$index.'" style="width: 90%;" readonly="readonly" />';
                                $rowLbl .= '</td>';
                                // Lot Number
                                $rowLbl .= '<td style="width:10%; '.$lotDisplay.'">';
                                $rowLbl .= '<input type="hidden" value="'.$row['lots_number'].'" class="lots_number" name="lots_number[]" />';
                                $rowLbl .= '<span class="lotsNoLabel">'.$row['lots_number'].'</span>';
                                $rowLbl .= '</td>';
                                // Expired Date
                                if($row['date_expired'] != '0000-00-00'){
                                    $dateExp = $this->Helper->dateShort($row['date_expired']);
                                }else{
                                    $dateExp = '';
                                }
                                $rowLbl .= '<td style="width:11%;">';
                                $rowLbl .= '<input type="hidden" value="'.$row['date_expired'].'" class="expired_date" name="expired_date[]" />';
                                $rowLbl .= '<span class="ExpDateLabel">'.$dateExp.'</span>';
                                $rowLbl .= '</td>';
                                // Location Group From
                                // Calucate Location From 
                                $optionLocFrom = '<option value="">'.INPUT_SELECT.'</option>';
                                $sqlLocationF = mysql_query("SELECT id, name FROM locations WHERE is_active = 1 AND location_group_id = {$fromLocationGroup} ORDER BY name");
                                while($rowLocationF = mysql_fetch_array($sqlLocationF)){
                                    if($rowLocationF['id'] == $rowLocation['id']){
                                        $selectedFrom = 'selected="selected"';
                                    }else{
                                        $selectedFrom = '';
                                    }
                                    $optionLocFrom .= '<option '.$selectedFrom.' value="'.$rowLocationF['id'].'">'.$rowLocationF['name'].'</option>';
                                }

                                $rowLbl .= '<td style="width:11%;">';
                                $rowLbl .= '<select id="location_from_id_'.$index.'" class="location_from_id validate[required]" name="location_from_id[]" style="width: 90%;">'.$optionLocFrom.'</select>';
                                $rowLbl .= '</td>';
                                // Location Group To
                                // Calucate Location To 
                                $optionLocTo = '<option value="">'.INPUT_SELECT.'</option>';
                                $sqlLocationT = mysql_query("SELECT id, name FROM locations WHERE is_active = 1 AND location_group_id = {$toLocationGroup} ORDER BY name");
                                while($rowLocationT = mysql_fetch_array($sqlLocationT)){
                                    $optionLocTo .= '<option value="'.$rowLocationT['id'].'">'.$rowLocationT['name'].'</option>';
                                }

                                $rowLbl .= '<td style="width:11%;">';
                                $rowLbl .= '<select id="location_to_id_'.$index.'" class="location_to_id validate[required]" name="location_to_id[]" style="width: 90%;">'.$optionLocTo.'</select>';
                                $rowLbl .= '</td>';
                                // Qty
                                $rowLbl .= '<td style="width:7%;">';
                                $rowLbl .= '<input type="hidden" value="'.$rowDetail['small_val_uom'].'" class="smallUomVal" />';
                                $rowLbl .= '<input type="hidden" value="'.$conversion.'" class="conversion" name="conversion[]" />';
                                $rowLbl .= '<input type="hidden" value="'.$totalQtyRequest.'" class="total_qty" />';
                                $rowLbl .= '<input type="hidden" value="'.$this->Helper->showTotalQty($totalQtyRequest, $mainUomName, $uomSmallVal, $uomSmallLabel).'"  class="total_qty_label" />';
                                $rowLbl .= '<input type="text" value="'.$rowDetail['qty'].'" name="qty[]" id="qty_'.$index.'" class="qty validate[required,min[1]]" style="width: 90%;" />';
                                $rowLbl .= '</td>';
                                // UOM
                                $rowLbl .= '<td style="width:10%;">';
                                $rowLbl .= '<select class="qty_uom_id" name="qty_uom_id[]" id="qty_uom_id'.$index.'" style="width: 90%;">'.$uomList.'</select>';
                                $rowLbl .= '</td>';
                                // Button Remove
                                $rowLbl .= '<td style="width:4%;">';
                                $rowLbl .= '<img alt="Remove" src="'.$this->webroot.'img/button/cross.png" class="btnRemoveTO" align="absmiddle" style="cursor: pointer;" onmouseover="Tip(\'Remove\')" />';
                                $rowLbl .= '</td>';
                                // Close Tr
                                $rowLbl .= '</tr>';
                                $totalQtyRequest = 0;
                            }
                        }
                    }
                }
            }
        }
        $rowList['error']  = 0;
        $rowList['result'] = $rowLbl;
        echo json_encode($rowList);
        exit;
    }
    
    function searchRequestStockCode($code = null){
        $this->layout = 'ajax';
        if (empty($code)) {
            $result['error'] = 1;
            echo json_encode($result);
            exit;
        }
        $user = $this->getCurrentUser();
        $userPermission = 'RequestStock.company_id IN (SELECT company_id FROM user_companies WHERE user_id ='.$user['User']['id'].') AND RequestStock.branch_id IN (SELECT branch_id FROM user_branches WHERE user_id ='.$user['User']['id'].')';
        $requestStock = ClassRegistry::init('RequestStock')->find('first', array(
                            'conditions' => array(
                                'RequestStock.code' => $code,
                                'RequestStock.status' => 1,
                                $userPermission
                            )
                          ));
        if(empty($requestStock)){
            $requestStock['error'] = 1;
        }
        echo json_encode($requestStock);
        exit;
    }
    
    function viewTransferIssued(){
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        // Check Module Exist
        $sqlDash = mysql_query("SELECT id FROM user_dashboards WHERE module_id = 500 AND user_id = {$user['User']['id']} LIMIT 1");
        if(!mysql_num_rows($sqlDash)){
            $this->loadModel('UserDashboard');
            $userDash = array();
            $userDash['UserDashboard']['user_id']      = $user['User']['id'];
            $userDash['UserDashboard']['module_id']    = 500;
            $userDash['UserDashboard']['display']      = 1;
            $userDash['UserDashboard']['auto_refresh'] = 1;
            $userDash['UserDashboard']['time_refresh'] = 5;
            $this->UserDashboard->save($userDash);
        }
    }
    
    function approve($id = null, $approve = 1) {
        if (!$id) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $user = $this->getCurrentUser();
        $modified = date("Y-m-d H:i:s");    
        $this->TransferOrder->updateAll(
                array('TransferOrder.is_approve' => $approve, "TransferOrder.approved_by"=>$user['User']['id'], 'TransferOrder.approved' => "'$modified'"), 
                array('TransferOrder.id' => $id)
        );
        if($approve == 2){
            // Reset Stock Order
            $sqlResetOrder = mysql_query("SELECT * FROM stock_orders WHERE `transfer_order_id`=".$id.";");
            while($rowResetOrder = mysql_fetch_array($sqlResetOrder)){
                $this->Inventory->saveGroupQtyOrder($rowResetOrder['location_group_id'], $rowResetOrder['location_id'], $rowResetOrder['product_id'], $rowResetOrder['lots_number'], $rowResetOrder['expired_date'], $rowResetOrder['qty'], $rowResetOrder['date'], '-');
            }
            // Detele Tmp Stock Order
            mysql_query("DELETE FROM `stock_orders` WHERE `transfer_order_id`=".$id.";");
        }
        echo MESSAGE_DATA_HAS_BEEN_SAVED;
        exit;
    }
    
    function aging($id = null) {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        if (!empty($this->data)) {
            $dateNow  = date("Y-m-d H:i:s");
            $transferOrder  = array();
            $transferOrder['TransferOrder']['id']          = $this->data['TransferOrder']['id'];
            $transferOrder['TransferOrder']['modified']    = $dateNow;
            $transferOrder['TransferOrder']['modified_by'] = $user['User']['id'];
            $transferOrder['TransferOrder']['balance']     = $this->data['TransferOrder']['balance_us'];
            if ($this->TransferOrder->save($transferOrder)) {
                $transferOrder = $this->TransferOrder->findById($this->data['TransferOrder']['id']);
                $lastExchangeRate = ClassRegistry::init('ExchangeRate')->find("first", array("conditions" => array(
                                "ExchangeRate.branch_id" => $transferOrder['TransferOrder']['branch_id'],
                                "ExchangeRate.currency_id" => 2), "order" => array("ExchangeRate.created desc")));
                if(!empty($lastExchangeRate) && $lastExchangeRate['ExchangeRate']['rate_to_sell'] > 0){
                    $exchangeRateId = $lastExchangeRate['ExchangeRate']['id'];
                    $totalPaidOther = ($this->data['TransferOrder']['amount_other'] / $lastExchangeRate['ExchangeRate']['rate_to_sell']);
                    $totalDisOther  = ($this->data['TransferOrder']['discount_other'] / $lastExchangeRate['ExchangeRate']['rate_to_sell']);
                } else {
                    $exchangeRateId = 0;
                    $totalPaidOther = 0;
                    $totalDisOther  = 0;
                }                
                
                // Transfer Order Receipt
                $this->loadModel("TransferOrderReceipt");
                $this->TransferOrderReceipt->create();
                $transferOrderReceipt = array();
                $transferOrderReceipt['TransferOrderReceipt']['sys_code']           = md5(rand().strtotime(date("Y-m-d H:i:s")).$user['User']['id']);
                $transferOrderReceipt['TransferOrderReceipt']['transfer_order_id']  = $transferOrder['TransferOrder']['id'];
                $transferOrderReceipt['TransferOrderReceipt']['branch_id']          = $transferOrder['TransferOrder']['branch_id'];
                $transferOrderReceipt['TransferOrderReceipt']['exchange_rate_id']   = $exchangeRateId;
                $transferOrderReceipt['TransferOrderReceipt']['currency_id'] = 2;
                $transferOrderReceipt['TransferOrderReceipt']['receipt_code']   = '';
                $transferOrderReceipt['TransferOrderReceipt']['amount_us']      = $this->data['TransferOrder']['amount_us'];
                $transferOrderReceipt['TransferOrderReceipt']['amount_other']   = $this->data['TransferOrder']['amount_other'];
                $transferOrderReceipt['TransferOrderReceipt']['discount_us']    = $this->data['TransferOrder']['discount_us'];
                $transferOrderReceipt['TransferOrderReceipt']['discount_other'] = $this->data['TransferOrder']['discount_other'];
                $transferOrderReceipt['TransferOrderReceipt']['total_amount']   = $this->data['TransferOrder']['total_cost'];
                $transferOrderReceipt['TransferOrderReceipt']['balance']        = $this->data['TransferOrder']['balance_us'];
                $transferOrderReceipt['TransferOrderReceipt']['balance_other']  = $this->data['TransferOrder']['balance_other'];
                $transferOrderReceipt['TransferOrderReceipt']['created']      = $dateNow;
                $transferOrderReceipt['TransferOrderReceipt']['created_by']   = $user['User']['id'];
                $transferOrderReceipt['TransferOrderReceipt']['pay_date']     = $this->data['TransferOrder']['pay_date']!=''?$this->data['TransferOrder']['pay_date']:'0000-00-00';
                // Get Total Paid
                if ($this->data['TransferOrder']['balance_us'] > 0) {
                    $transferOrderReceipt['TransferOrderReceipt']['due_date'] = $this->data['TransferOrder']['aging']!=''?$this->data['TransferOrder']['aging']:'0000-00-00';
                }
                $this->TransferOrderReceipt->save($transferOrderReceipt);
                $result['to_id'] = $this->TransferOrderReceipt->id;
                // Get Module Code
                $modCode  = $this->Helper->getModuleCode(date("Y")."TOR", $result['to_id'], 'receipt_code', 'transfer_order_receipts', 'is_void = 0 AND branch_id = '.$transferOrder['TransferOrder']['branch_id']);
                // Updaet Module Code
                mysql_query("UPDATE transfer_order_receipts SET receipt_code = '".$modCode."' WHERE id = ".$result['to_id']);
                echo json_encode($result);
                exit;
            }else {
                $this->Helper->saveUserActivity($user['User']['id'], 'Transfer Order Receipt', 'Save Add New (Error)');
                $result['to_id'] = 0;
                echo json_encode($result);
                exit;
            }
        }
        if (!empty($id)) {
            $this->data = $this->TransferOrder->read(null, $id);
            $transferOrder = ClassRegistry::init('TransferOrder')->find("first",
                            array('conditions' => array('TransferOrder.id' => $id)));
            if (!empty($transferOrder)) {
                $this->Helper->saveUserActivity($user['User']['id'], 'Sales Invoice Receipt', 'Add New', $id);
                $fromLocationGroups = ClassRegistry::init('LocationGroup')->find('first', array('conditions' => array('LocationGroup.id' => $this->data['TransferOrder']['from_location_group_id'])));
                $toLocationGroups   = ClassRegistry::init('LocationGroup')->find('first', array("conditions" => array('LocationGroup.id' => $this->data['TransferOrder']['to_location_group_id'])));
                $transferOrderDetails  = ClassRegistry::init('TransferOrderDetail')->find("all", array('conditions' => array('TransferOrderDetail.transfer_order_id' => $id)));
                $transferOrderReceipts = ClassRegistry::init('TransferOrderReceipt')->find("all", array('conditions' => array('TransferOrderReceipt.transfer_order_id' => $id, 'TransferOrderReceipt.is_void' => 0)));
                $this->set(compact('transferOrder', 'fromLocationGroups', 'toLocationGroups', 'transferOrderDetails', 'transferOrderReceipts'));
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
            $sr = ClassRegistry::init('TransferOrderReceipt')->find("first",
                            array('conditions' => array('TransferOrderReceipt.id' => $receiptId, 'TransferOrderReceipt.is_void' => 0)));
            $transferOrder = ClassRegistry::init('TransferOrder')->find("first",
                            array('conditions' => array('TransferOrder.id' => $sr['TransferOrder']['id'])));
            if (!empty($transferOrder)) {
                $lastExchangeRate = ClassRegistry::init('ExchangeRate')->find("first",
                                array(
                                    "conditions" => array("ExchangeRate.is_active" => 1),
                                    "order" => array("ExchangeRate.created desc")
                                )
                );
                $fromLocationGroups = ClassRegistry::init('LocationGroup')->find('first', array('conditions' => array('LocationGroup.id' => $sr['TransferOrder']['from_location_group_id'])));
                $toLocationGroups   = ClassRegistry::init('LocationGroup')->find('first', array("conditions" => array('LocationGroup.id' => $sr['TransferOrder']['to_location_group_id'])));
                $transferOrderDetails = ClassRegistry::init('TransferOrderDetail')->find("all",
                                array('conditions' => array('TransferOrderDetail.transfer_order_id' => $sr['TransferOrder']['id'])));
                $transferOrderReceipts = ClassRegistry::init('TransferOrderReceipt')->find("all",
                                array('conditions' => array('TransferOrderReceipt.transfer_order_id' => $sr['TransferOrder']['id'], 'TransferOrderReceipt.is_void' => 0)));

                $this->set(compact('transferOrder', 'fromLocationGroups', 'toLocationGroups', 'transferOrderDetails', 'transferOrderReceipts', 'lastExchangeRate', 'sr'));
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
            $sr = ClassRegistry::init('TransferOrderReceipt')->find("first",
                            array('conditions' => array('TransferOrderReceipt.id' => $receiptId, 'TransferOrderReceipt.is_void' => 0)));
            $transferOrder = ClassRegistry::init('TransferOrder')->find("first",
                            array('conditions' => array('TransferOrder.id' => $sr['TransferOrder']['id'])));
            if (!empty($transferOrder)) {
                $lastExchangeRate = ClassRegistry::init('ExchangeRate')->find("first",
                                array(
                                    "conditions" => array("ExchangeRate.is_active" => 1),
                                    "order" => array("ExchangeRate.created desc")
                                )
                );
                $fromLocationGroups = ClassRegistry::init('LocationGroup')->find('first', array('conditions' => array('LocationGroup.id' => $sr['TransferOrder']['from_location_group_id'])));
                $toLocationGroups   = ClassRegistry::init('LocationGroup')->find('first', array("conditions" => array('LocationGroup.id' => $sr['TransferOrder']['to_location_group_id'])));
                $transferOrderDetails = ClassRegistry::init('TransferOrderDetail')->find("all",
                                array('conditions' => array('TransferOrderDetail.transfer_order_id' => $sr['TransferOrder']['id'])));
                $transferOrderReceipts = ClassRegistry::init('TransferOrderReceipt')->find("all",
                                array('conditions' => array('TransferOrderReceipt.id <= ' . $receiptId, 'TransferOrderReceipt.transfer_order_id' => $sr['TransferOrder']['id'], 'TransferOrderReceipt.is_void' => 0)));

                $this->set(compact('transferOrder', 'fromLocationGroups', 'toLocationGroups', 'transferOrderDetails', 'transferOrderReceipts', 'lastExchangeRate', 'sr'));
            } else {
                exit;
            }
        } else {
            exit;
        }
    }
    
    function voidReceipt($id) {
        if (!$id) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $this->loadModel('TransferOrderReceipt');
        $this->loadModel('Transaction');
        $receipt = ClassRegistry::init('TransferOrderReceipt')->find("first",
                        array('conditions' => array('TransferOrderReceipt.id' => $id, 'TransferOrderReceipt.is_void' => 0)));
        $user = $this->getCurrentUser();
        if(!empty($receipt) && @$receipt['TransferOrderReceipt']['is_void'] == 0){
            $this->TransferOrderReceipt->updateAll(
                    array('TransferOrderReceipt.is_void' => 1, 'TransferOrderReceipt.modified_by' => $user['User']['id']),
                    array('TransferOrderReceipt.id' => $id)
            );
            $exchangeRate = ClassRegistry::init('ExchangeRate')->find("first", array("conditions" => array("ExchangeRate.id" => $receipt['TransferOrderReceipt']['exchange_rate_id'])));
            if(!empty($exchangeRate) && $exchangeRate['ExchangeRate']['rate_to_sell'] > 0){
                $paidAmtOther = 0;
                $paidDisOther = 0;
                if($receipt['TransferOrderReceipt']['amount_other'] > 0){
                    $paidAmtOther = ($receipt['TransferOrderReceipt']['amount_other'] / $exchangeRate['ExchangeRate']['rate_to_sell']);
                }
                if($receipt['TransferOrderReceipt']['discount_other'] > 0){
                    $paidDisOther = ($receipt['TransferOrderReceipt']['discount_other'] / $exchangeRate['ExchangeRate']['rate_to_sell']);
                }
                $totalPaidOther =  + $paidAmtOther + $paidDisOther;
            } else {
                $totalPaidOther = 0;
            }
            $total_amount   = $receipt['TransferOrderReceipt']['amount_us'] + $receipt['TransferOrderReceipt']['discount_us'] + $totalPaidOther;
            mysql_query("UPDATE transfer_orders SET balance = balance+" . $total_amount . " WHERE id=" . $receipt['TransferOrderReceipt']['transfer_order_id']);
            $this->Helper->saveUserActivity($user['User']['id'], 'Transfer Order Receipt', 'Void', $id);
            echo MESSAGE_DATA_HAS_BEEN_DELETED;
        }else{
            $this->Helper->saveUserActivity($user['User']['id'], 'Transfer Order Receipt', 'Void (Error)', $id);
            echo MESSAGE_DATA_INVALID;
        }
        exit;
    }
    
}

?>