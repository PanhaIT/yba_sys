<?php

class PurchaseReceivesController extends AppController {

    var $name = 'PurchaseReceives';
    var $components = array('Helper', 'Inventory');

    function index($time = null) {
        $this->layout = "ajax";        
        $user = $this->getCurrentUser();
        // Get Loction Setting
        $locSetting = ClassRegistry::init('LocationSetting')->findById(1);
        $locCon     = '';
        if($locSetting['LocationSetting']['location_status'] == 1){
            $locCon = ' AND Location.is_for_sale = 0';
        }
        $locationGroups = ClassRegistry::init('LocationGroup')->find('list', array('joins' => array(array('table' => 'user_location_groups', 'type' => 'inner', 'conditions' => array('user_location_groups.location_group_id=LocationGroup.id'))),'conditions' => array('user_location_groups.user_id=' . $user['User']['id'], 'LocationGroup.is_active' => '1', 'LocationGroup.location_group_type_id != 1')));
        $locations  = ClassRegistry::init('Location')->find('all', array('joins' => array(array('table' => 'user_locations', 'type' => 'inner', 'conditions' => array('user_locations.location_id=Location.id'))), 'conditions' => array('user_locations.user_id=' . $user['User']['id'] . ' AND Location.is_active=1'.$locCon), 'order' => 'Location.name'));
        $this->set(compact('locationGroups', 'locations'));
        $this->set('time',$time);
    }

    function ajax($locationGroup = 'all', $location = 'all', $status = 'all') {
        $this->layout = "ajax";
        if ($status) {
            $this->set('status', $status);
        }        
        $this->set('locationGroup', $locationGroup);
        $this->set('location', $location);                
    }

    function receive($id = null) {
        $this->layout = "ajax";
        if (!$id) {
            echo "Invalid Id";
            exit;
        }
        $user = $this->getCurrentUser();
        $this->Helper->saveUserActivity($user['User']['id'], 'Purchase Receive', 'Receive', $id);
        $this->data = ClassRegistry::init('PurchaseOrder')->read(null, $id);
        $code = $this->Helper->getAutoGenerateGoodsReceiptCode();
        $this->set('order', $this->data);
        $this->set('id', $id);
        $this->set('code', $code);
    }

    function receiveDetail($id = null) {
        $this->layout = "ajax";
        if (!$id) {
            echo "Invalid Id";
            exit;
        }
        $this->data = ClassRegistry::init('PurchaseOrder')->read(null, $id);
        $this->set('order', $this->data);
        $this->set('id', $id);
        $db = ConnectionManager::getDataSource('default');
        mysql_select_db($db->config['database']);
    }
    
    function receiveAll(){
        $this->layout = "ajax";
        $user = $this->getCurrentUser();
        if(!empty($this->data)){               
            $purchase_order = ClassRegistry::init('PurchaseOrder')->read(null, $this->data['purchase_order_id']);
            if($purchase_order['PurchaseOrder']['status'] > 0 && $purchase_order['PurchaseOrder']['status'] < 3){    
                $r = 0;
                $restCode = array();
                $dateNow  = date("Y-m-d H:i:s");
                // Load Model
                $this->loadModel('PurchaseReceiveResult');
                $this->PurchaseReceiveResult->create();
                $purchaseRecResult = array();
                $purchaseRecResult['PurchaseReceiveResult']['sys_code'] = md5(rand().strtotime(date("Y-m-d H:i:s")).$user['User']['id']);
                $purchaseRecResult['PurchaseReceiveResult']['purchase_order_id'] = $purchase_order['PurchaseOrder']['id'];
                $purchaseRecResult['PurchaseReceiveResult']['date'] = $_POST['date_receive_all'];
                $purchaseRecResult['PurchaseReceiveResult']['created']    = $dateNow;
                $purchaseRecResult['PurchaseReceiveResult']['created_by'] = $user['User']['id'];
                if($this->PurchaseReceiveResult->save($purchaseRecResult)){
                    $purchaseRecResultId = $this->PurchaseReceiveResult->id;
                    // Update Code Receive Result Code
                    $sqlRecCode = mysql_query("SELECT CONCAT('".date("y")."GRR','',LPAD(((SELECT count(tmp.id) FROM `purchase_receive_results` as tmp WHERE tmp.code LIKE '".date("y")."GRR%' AND tmp.id < ".$purchaseRecResultId.") + 1),7,'0')) AS code");
                    $rowRecCode = mysql_fetch_array($sqlRecCode);
                    mysql_query("UPDATE purchase_receive_results SET code = '".$rowRecCode['code']."' WHERE id = ".$purchaseRecResultId);
                    // Convert to REST
                    $restCode[$r] = $this->Helper->convertToDataSync($purchaseRecResult['PurchaseReceiveResult'], 'purchase_receive_results');
                    $restCode[$r]['code']   = $rowRecCode['code'];
                    $restCode[$r]['dbtodo'] = 'purchase_receive_results';
                    $restCode[$r]['actodo'] = 'is';
                    $r++;
                    
                    for($i=0; $i < sizeof($_POST['product_id']); $i++){
                        if($_POST['product_id'][$i] != '' AND $_POST['purchase_qty'][$i] != '' AND $_POST['purchase_qty'][$i] > 0 && $_POST['purchase_uom'][$i] != null && $_POST['purchase_uom'][$i] != ""){
                            $qty_receiving = $_POST['uom_conversion'][$i] * $_POST['purchase_qty'][$i];
                            if($qty_receiving > 0){
                                $purchaseDetals = explode(',', $_POST['purchase_detail_id'][$i]);
                                // Get Decimal
                                $sqlOption = mysql_query("SELECT product_cost_decimal FROM setting_options");
                                $rowOption = mysql_fetch_array($sqlOption);
                                foreach($purchaseDetals AS $purchaseDetal){
                                    $sqlCost = mysql_query("SELECT unit_cost, (qty + qty_free) AS qty, new_unit_cost FROM purchase_order_details WHERE id = ".$purchaseDetal);
                                    $rowCost = mysql_fetch_array($sqlCost);
                                    $sqlPro  = mysql_query("SELECT small_val_uom, unit_cost, sys_code FROM products WHERE id = ".$_POST['product_id'][$i]);
                                    $rowPro  = mysql_fetch_array($sqlPro);
                                    $newCost = $this->Helper->replaceThousand(number_format($rowCost['new_unit_cost'], $rowOption[0]));
                                    if($purchase_order['PurchaseOrder']['is_update_cost'] == 0){
                                        // Update unit cost for product                             
                                        mysql_query("UPDATE products SET unit_cost = '".$newCost."' WHERE id=".$_POST['product_id'][$i]);
                                        // Convert to REST
                                        $restCode[$r]['unit_cost'] = $newCost;
                                        $restCode[$r]['dbtodo'] = 'products';
                                        $restCode[$r]['actodo'] = 'ut';
                                        $restCode[$r]['con']    = "sys_code = '".$rowPro['sys_code']."'";
                                        $r++;
                                        if ($rowPro['unit_cost'] != $newCost) {
                                            mysql_query("INSERT INTO `product_unit_cost_histories` (`product_id`, `purchase_order_id`, `old_cost`, `new_cost`, `type`, `created`, `created_by`) 
                                                         VALUES (".$_POST['product_id'][$i].", ".$this->data['purchase_order_id'].", ".$_POST['old_unit_cost'][$i].", ".$newCost.", 'PB', '".$dateNow."', ".$user['User']['id'].");");
                                            // Convert to REST
                                            $restCode[$r]['old_cost']   = $_POST['old_unit_cost'][$i];
                                            $restCode[$r]['new_cost']   = $newCost;
                                            $restCode[$r]['created']    = $dateNow;
                                            $restCode[$r]['created_by'] = $this->Helper->getSQLSysCode("users", $user['User']['id']);
                                            $restCode[$r]['product_id'] = $this->Helper->getSQLSysCode("products", $_POST['product_id'][$i]);
                                            $restCode[$r]['purchase_order_id'] = $this->Helper->getSQLSysCode("purchase_orders", $this->data['purchase_order_id']);
                                            $restCode[$r]['dbtodo'] = 'product_unit_cost_histories';
                                            $restCode[$r]['actodo'] = 'is';
                                            $r++;
                                        }

                                        if($this->data['calculate_cogs'] == 2){
                                            // Insert Inventory Unit Cost
                                            mysql_query("INSERT INTO `inventory_unit_costs` (`product_id`, `total_qty`, `unit_cost`, `created`)
                                                         VALUES (".$_POST['product_id'][$i].", ".$qty_receiving.", ".$newCost.", '".date("Y-m-d H:i:s")."');");
                                        }
                                    }
                                }

                                $datePb = $this->data['order_date'];                            
                                $date_expried  = $_POST['expired_date_inventory'][$i]!=''?$_POST['expired_date_inventory'][$i]:'0000-00-00';
                                $date_received = $_POST['date_receive_one'][$i] !='' ? $_POST['date_receive_one'][$i] : $_POST['date_receive_all'];

                                // Update Inventory (Purchase)
                                $data = array();
                                $data['module_type']       = 6;
                                $data['purchase_order_id'] = $purchase_order['PurchaseOrder']['id'];
                                $data['product_id']        = $_POST['product_id'][$i];
                                $data['location_id']       = $this->data['location'];
                                $data['location_group_id'] = $purchase_order['PurchaseOrder']['location_group_id'];
                                $data['lots_number']  = $_POST['lots_number'][$i]!=''?$_POST['lots_number'][$i]:0;
                                $data['expired_date'] = $date_expried;
                                $data['date']         = $datePb;
                                $data['total_qty']    = $qty_receiving;
                                $data['total_order']  = $qty_receiving;
                                $data['total_free']   = 0;
                                $data['user_id']      = $user['User']['id'];
                                $data['customer_id']  = "";
                                $data['vendor_id']    = $this->data['vendor'];
                                $data['unit_cost']    = $_POST['cost_inventory'][$i];
                                $data['unit_price']   = 0;
                                // Update Invetory Location
                                $this->Inventory->saveInventory($data);
                                // Update Inventory Group
                                $this->Inventory->saveGroupTotalDetail($data);
                                // Convert to REST
                                $restCode[$r] = $this->Helper->convertToDataSync($data, 'inventories');
                                $restCode[$r]['module_type']  = 6;
                                $restCode[$r]['total_qty']    = $qty_receiving;
                                $restCode[$r]['total_order']  = $qty_receiving;
                                $restCode[$r]['total_free']   = 0;
                                $restCode[$r]['expired_date'] = $date_expried;
                                $restCode[$r]['customer_id']  = "";
                                $restCode[$r]['vendor_id']    = $this->Helper->getSQLSyncCode("vendors", $this->data['vendor']);
                                $restCode[$r]['purchase_order_id'] = $this->Helper->getSQLSyncCode("purchase_orders", $purchase_order['PurchaseOrder']['id']);
                                $restCode[$r]['product_id']        = $this->Helper->getSQLSyncCode("products", $_POST['product_id'][$i]);
                                $restCode[$r]['location_id']       = $this->Helper->getSQLSyncCode("locations", $this->data['location']);
                                $restCode[$r]['location_group_id'] = $this->Helper->getSQLSyncCode("location_groups", $purchase_order['PurchaseOrder']['location_group_id']);
                                $restCode[$r]['user_id']           = $this->Helper->getSQLSyncCode("users", $user['User']['id']);
                                $restCode[$r]['dbtype']  = 'saveInv,GroupDetail';
                                $restCode[$r]['actodo']  = 'inv';
                                $r++;

                                // Purchase Receive
                                ClassRegistry::init('PurchaseReceive')->create();
                                $this->data['PurchaseReceive']['purchase_receive_result_id'] = $purchaseRecResultId;
                                $this->data['PurchaseReceive']['purchase_order_id'] = $this->data['purchase_order_id'];
                                $this->data['PurchaseReceive']['purchase_order_detail_id'] = $_POST['purchase_detail_id'][$i];
                                $this->data['PurchaseReceive']['product_id'] = $_POST['product_id'][$i];
                                $this->data['PurchaseReceive']['qty'] = $_POST['purchase_qty'][$i];
                                $this->data['PurchaseReceive']['qty_uom_id'] = $_POST['purchase_uom'][$i];
                                $this->data['PurchaseReceive']['conversion'] = $_POST['uom_conversion'][$i];
                                $this->data['PurchaseReceive']['received_date'] = $date_received;
                                $this->data['PurchaseReceive']['lots_number']   = $_POST['lots_number'][$i];
                                $this->data['PurchaseReceive']['date_expired']  = $date_expried;
                                $this->data['PurchaseReceive']['created']    = $dateNow;
                                $this->data['PurchaseReceive']['created_by'] = $user['User']['id'];
                                $this->data['PurchaseReceive']['status'] = 1;
                                ClassRegistry::init('PurchaseReceive')->save($this->data);
                                // Convert to REST
                                $restCode[$r] = $this->Helper->convertToDataSync($this->data['PurchaseReceive'], 'purchase_receives');
                                $restCode[$r]['dbtodo'] = 'purchase_receives';
                                $restCode[$r]['actodo'] = 'is';
                                $r++;
                            }
                        }
                    }
                    $sqlPb = mysql_query("SELECT sum(qty + qty_free) as total FROM purchase_order_details WHERE purchase_order_id = " . $this->data['purchase_order_id']);
                    $totalPb = mysql_fetch_array($sqlPb);
                    $sqlReceive = mysql_query("SELECT sum(qty) as total FROM purchase_receives WHERE purchase_order_id = " . $this->data['purchase_order_id'] . " AND status = 1");
                    $totalReceive = mysql_fetch_array($sqlReceive);
                    $this->data['PurchaseOrder']['id'] = $this->data['purchase_order_id'];
                    if ($totalReceive[0] >= $totalPb[0]) {
                        $this->data['PurchaseOrder']['status'] = 3;
                    }else{
                        $this->data['PurchaseOrder']['status'] = 2;
                    }
                    $this->data['PurchaseOrder']['modified_by'] = $user['User']['id'];
                    ClassRegistry::init('PurchaseOrder')->saveAll($this->data);
                    // Convert to REST
                    $restCode[$r]['status']      = $this->data['PurchaseOrder']['status'];
                    $restCode[$r]['modified']    = $dateNow;
                    $restCode[$r]['modified_by'] = $this->Helper->getSQLSysCode("users", $user['User']['id']);
                    $restCode[$r]['dbtodo'] = 'purchase_orders';
                    $restCode[$r]['actodo'] = 'ut';
                    $restCode[$r]['con']    = "sys_code = '".$purchase_order['PurchaseOrder']['sys_code']."'";
                    // Save File Send
                    $this->Helper->sendFileToSync($restCode, 0, 0);
                    $this->Helper->saveUserActivity($user['User']['id'], 'Purchase Receive', 'Save Add New', $purchaseRecResultId);
                    $result['id'] = $purchaseRecResultId;
                    echo json_encode($result);
                    exit;
                }else{
                    $this->Helper->saveUserActivity($user['User']['id'], 'Purchase Receive', 'Save Receive (Error)', $this->data['purchase_order_id']);
                    $result['id'] = 0;
                    echo json_encode($result);
                    exit;
                }
            }else{
                $this->Helper->saveUserActivity($user['User']['id'], 'Purchase Receive', 'Save Receive (Error Status)', $this->data['purchase_order_id']);
                $result['id'] = 0;
                echo json_encode($result);
                exit;
            }
        }else{
            $this->Helper->saveUserActivity($user['User']['id'], 'Purchase Receive', 'Save Receive (Error Data POST)', $this->data['purchase_order_id']);
            $result['id'] = 0;
            echo json_encode($result);
            exit; 
        }
    }
    
    function view($id = null){
        $this->layout = "ajax";
        if (!$id) {
            echo "Invalid Id";
            exit;
        }
        $user = $this->getCurrentUser();
        $this->Helper->saveUserActivity($user['User']['id'], 'Purchase Receive', 'View', $id);
        $this->data = ClassRegistry::init('PurchaseOrder')->read(null, $id);
        $resultDetails = ClassRegistry::init('PurchaseReceiveResult')->find('all', array('conditions' => array('PurchaseReceiveResult.purchase_order_id' => $id)));
        $this->set('order', $this->data);
        $this->set('resultDetails', $resultDetails);
    }
    
    function printInvoice($id = null){
        $this->layout = "ajax";
        if (!$id) {
            echo "Invalid Id";
            exit;
        }
        $this->data = ClassRegistry::init('PurchaseReceiveResult')->find('first', array('conditions' => array('PurchaseReceiveResult.id' => $id)));
    }

}

?>
