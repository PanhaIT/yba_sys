<?php

class TransferReceivesController extends AppController {

    var $uses = 'TransferOrders';
    var $components = array('Helper', 'Inventory');

    function index() {
        $this->layout = "ajax";
    }

    function ajax($status = 'all', $fromWarehouse = 'all', $toWarehouse = 'all', $date = '') {
        $this->layout = "ajax";
        $this->set(compact('status', 'fromWarehouse', 'toWarehouse', 'date'));
    }

    function receive($id = null) {
        $this->layout = "ajax";
        if (!$id) {
            echo "Invalid Id";
            exit;
        }
        $user = $this->getCurrentUser();
        $this->Helper->saveUserActivity($user['User']['id'], 'Transfer Receive', 'Receive', $id);
        $this->data = ClassRegistry::init('TransferOrder')->read(null, $id);
        $modCode    = ClassRegistry::init('ModuleCodeBranch')->find('first', array('conditions' => array("ModuleCodeBranch.branch_id" => $this->data['TransferOrder']['branch_id'])));
        $code       = date("y").$modCode['ModuleCodeBranch']['tr_code'];
        $this->set('order', $this->data);
        $this->set('id', $id);
        $this->set('code', $code);
        $this->set('locationGroup', ClassRegistry::init('LocationGroup')->find('all', array('conditions' => array("LocationGroup.is_active = 1"))));
    }

    function receiveDetail($id = null) {
        $this->layout = "ajax";
        if (!$id) {
            echo "Invalid Id";
            exit;
        }
        $this->data = ClassRegistry::init('TransferOrder')->read(null, $id);
        $this->set('id', $id);
        $db = ConnectionManager::getDataSource('default');
        mysql_select_db($db->config['database']);
    }

//    function action($record = null) {
//        $this->layout = "ajax";
//        if (!$record and empty($this->data)) {
//            echo "Invalid Data";
//            exit;
//        }
//        if (!empty($this->data)) {
//            $this->loadModel('TransferOrder');
//            $transfer_order = $this->TransferOrder->read(null, $this->data['TransferReceive']['transfer_order_id']);
//            if ($transfer_order['TransferOrder']['is_process'] == 0) {
//                $db = ConnectionManager::getDataSource('default');
//                mysql_select_db($db->config['database']);
//                $totalQtyStock = 0;
//                $user = $this->getCurrentUser();
//                if (preg_match('/^[0-9]{2}\/[0-9]{2}\/[0-9]{4}$/', $this->data['receive_date'])) {
//                    $this->data['receive_date'] = $this->Helper->dateConvert($this->data['receive_date']);
//                }
//                if (!isset($this->data['receive_date']) || is_null($this->data['receive_date']) || $this->data['receive_date'] == '0000-00-00' || $this->data['receive_date'] == '') {
//                    echo MESSAGE_DATA_COULD_NOT_BE_SAVED;
//                    exit;
//                }
//                if ($this->data['qty'] > 0) {
//                    // Calculate QTY in Inventory
//                    $qty_inv = ($this->data['qty'] * $this->data['value_uom']);
//                    // Check Total qty in Inventory Total
//                    $sql = mysql_query("SELECT total_qty FROM `" . $this->data['from_location'] . "_inventory_totals` WHERE product_id = " . $this->data['product_id'] . " and location_id = " . $this->data['from_location']." AND lots_number = '" . $this->data['lots_number']."' AND expired_date = '" . $this->data['exp_date']."'");
//                    $r = mysql_fetch_array($sql);
//                    $totalQtyStock = $r['total_qty'] * $this->data['value_uom'];
//
//                    if ($totalQtyStock >= $qty_inv) { // Check Total Qty And Qty Transfer
//                        $dateNow = $this->data['receive_date'];
//                         /*
//                        /* Update Inventory Location FROM */
//                        // Update Inventory (Transfer Out)
//                        $dataOut = array();
//                        $dataOut['module_type']       = 3;
//                        $dataOut['transfer_order_id'] = $this->data['TransferReceive']['transfer_order_id'];
//                        $dataOut['product_id']        = $this->data['product_id'];
//                        $dataOut['location_id']       = $this->data['from_location'];
//                        $dataOut['location_group_id'] = $this->data['from_location_group_id'];
//                        $dataOut['lots_number']  = $this->data['lots_number'];
//                        $dataOut['expired_date'] = $this->data['exp_date'];
//                        $dataOut['date']         = $dateNow;
//                        $dataOut['total_qty']    = $qty_inv;
//                        $dataOut['total_order']  = $qty_inv;
//                        $dataOut['total_free']   = 0;
//                        $dataOut['user_id']      = $user['User']['id'];
//                        $dataOut['customer_id']  = "";
//                        $dataOut['vendor_id']    = "";
//                        $dataOut['unit_cost']    = 0;
//                        // Update Invetory Location
//                        $this->Inventory->saveInventory($dataOut);
//                        // Update Inventory Group
//                        $this->Inventory->saveGroupTotalDetail($dataOut);
//                        
//                        /*
//                        /* Update Inventory Location To */
//                        // Update Inventory (Transfer In)
//                        $dataIn = array();
//                        $dataIn['module_type']       = 2;
//                        $dataIn['transfer_order_id'] = $this->data['TransferReceive']['transfer_order_id'];
//                        $dataIn['product_id']        = $this->data['product_id'];
//                        $dataIn['location_id']       = $this->data['to_location'];
//                        $dataIn['location_group_id'] = $this->data['to_location_group_id'];
//                        $dataIn['lots_number']  = $this->data['lots_number'];
//                        $dataIn['expired_date'] = $this->data['exp_date'];
//                        $dataIn['date']         = $dateNow;
//                        $dataIn['total_qty']    = $qty_inv;
//                        $dataIn['total_order']  = $qty_inv;
//                        $dataIn['total_free']   = 0;
//                        $dataIn['user_id']      = $user['User']['id'];
//                        $dataIn['customer_id']  = "";
//                        $dataIn['vendor_id']    = "";
//                        $dataIn['unit_cost']    = 0;
//                        // Update Invetory Location
//                        $this->Inventory->saveInventory($dataIn);
//                        // Update Inventory Group
//                        $this->Inventory->saveGroupTotalDetail($dataIn);
//                        
//                        // Save Transfer Receive
//                        $this->data['TransferReceive']['transfer_order_detail_id'] = $this->data['detail_id'];
//                        $this->data['TransferReceive']['lots_number'] = $this->data['lots_number'];
//                        $this->data['TransferReceive']['expired_date'] = $this->data['exp_date'];
//                        $this->data['TransferReceive']['product_id'] = $this->data['product_id'];
//                        $this->data['TransferReceive']['qty'] = $this->data['qty'];
//                        $this->data['TransferReceive']['conversion'] = $this->data['value_uom'];
//                        $this->data['TransferReceive']['status'] = 1;
//                        $this->data['TransferReceive']['created_by'] = $user['User']['id'];
//                        ClassRegistry::init('TransferReceive')->saveAll($this->data);
//
//                        // Update Status Transfer Order
//                        $sqlTo = mysql_query("SELECT sum(qty) as total FROM transfer_order_details WHERE transfer_order_id = " . $this->data['TransferReceive']['transfer_order_id']);
//                        $totalTo = mysql_fetch_array($sqlTo);
//                        $sqlReceive = mysql_query("SELECT sum(qty) as total FROM transfer_receives WHERE transfer_order_id = " . $this->data['TransferReceive']['transfer_order_id'] . " AND status = 1");
//                        $totalReceive = mysql_fetch_array($sqlReceive);
//                        $this->data['TransferOrder']['id'] = $this->data['TransferReceive']['transfer_order_id'];
//                        $this->data['TransferOrder']['modified_by'] = $user['User']['id'];
//                        if ($totalReceive[0] >= $totalTo[0]) {
//                            if($transfer_order['TransferOrder']['request_stock_id'] > 0){
//                                mysql_query("UPDATE `request_stocks` SET `status`=2, `modified` = '".date("Y-m-d H:i:s")."', `modified_by` = {$user['User']['id']} WHERE  `id`={$transfer_order['TransferOrder']['request_stock_id']};");
//                            }
//                            $this->data['TransferOrder']['status'] = 3;
//                        } else {
//                            $this->data['TransferOrder']['status'] = 2;
//                        }
//                        ClassRegistry::init('TransferOrder')->saveAll($this->data);
//                        echo MESSAGE_DATA_HAS_BEEN_SAVED;
//                        exit;
//                    } else {
//                        echo "<span style='color:red'>" . MESSAGE_SORRY_CANNOT_TRANSFER . $totalQtyStock . ">=" . $qty_inv . "</span>";
//                        exit;
//                    }
//                } else {
//                    echo MESSAGE_DATA_HAS_BEEN_SAVED;
//                    exit;
//                }
//            } else {
//                echo "<span style='color:red'>Sorry, This transfer order is processing...</span>";
//                exit;
//            }
//        }
//        $array = explode('|!|', $record);
//        $pro_id = $array[0];
//        $qty = $array[1];
//        $uom_id = $array[2];
//        $from_location = $array[3];
//        $to_location = $array[4];
//        $order_id = $array[5];
//        $uom_name = $array[7];
//        $detail_id = $array[8];
//        $conversion = $array[9];
//        $transferOrder = ClassRegistry::init('TransferOrder')->read(null, $order_id);
//        $lots_number = $_POST['lots_number'];
//        $exp_date    = $_POST['exp_date'];
//        $receiveNo   = $_POST['receive_num'];
//        $this->set(compact('receiveNo', 'lots_number', 'exp_date', 'pro_id', 'qty', 'uom_id', 'from_location', 'to_location', 'order_id', 'uom_name', 'detail_id', 'transferOrder', 'conversion'));
//        $db = ConnectionManager::getDataSource('default');
//        mysql_select_db($db->config['database']);
//    }

    function addReceiveCrontab() {
        $this->layout = "ajax";
        if (!empty($this->data)) {
            $db = ConnectionManager::getDataSource('default');
            mysql_select_db($db->config['database']);
            $user = $this->getCurrentUser();
            $transferOrder = ClassRegistry::init('TransferOrder')->read(null, $this->data['transfer_order_id']);
            if ($transferOrder['TransferOrder']['is_process'] == 0 && $transferOrder['TransferOrder']['status'] > 0 && $transferOrder['TransferOrder']['status'] < 3 && ($transferOrder['TransferOrder']['is_approve'] = 1 || $transferOrder['TransferOrder']['is_approve'] = 3) ){
                $this->data['TransferOrder']['id'] = $this->data['transfer_order_id'];
                $this->data['TransferOrder']['is_process'] = 1;
                if (ClassRegistry::init('TransferOrder')->save($this->data)) {
                    $json = $_POST['json'];
                    $rand = rand();
                    $name = (isset($_SESSION['sPosDb']) ? $_SESSION['sPosDb'] : $rand) . "_to_r_" . $this->data['transfer_order_id'];
                    $filename = "public/" . $name;
                    shell_exec("touch " . $filename);
                    if (file_exists($filename)) {
                        $file = fopen($filename, "w");
                        fwrite($file, $json);
                        fclose($file);
                        $url = LINK_URL . "receiveToAll?user=" . $user['User']['id'] . "&transfer_order_id=" . $this->data['transfer_order_id'] . "&order_date=" . $this->data['order_date'] . "&receive_number=" . $this->data['receive_number'] . "&receive_date=" . $this->data['receive_date'] . "&json=" . $name . "&pos=ud@y@ip@s" . (isset($_SESSION['sPosDb']) ? $_SESSION['sPosDb'] : '');
                        shell_exec("wget -b -q -P public/logs/ '" . $url . "' " . LINK_URL_SSL);
                        $invalid['to_id'] = $this->data['transfer_order_id'];
                        $this->Helper->saveUserActivity($user['User']['id'], 'Transfer Receive', 'Click Receive', $this->data['transfer_order_id']);
                    } else {
                        $this->Helper->saveUserActivity($user['User']['id'], 'Transfer Receive', 'Click Receive (Error)', $this->data['transfer_order_id']);
                        mysql_query("UPDATE `transfer_orders` SET `is_process` = 0 WHERE `id`=" . $this->data['transfer_order_id'] . " LIMIT 1;");
                        $invalid['error'] = 1;
                    }
                } else {
                    $invalid['error'] = 1;
                }
            } else {
                $invalid['is_process'] = 1;
            }
        } else {
            $invalid['error'] = 1;
        }
        echo json_encode($invalid);
        exit;
    }

//    function void($record = null) {
//        if (!$record) {
//            echo "Invalid Id";
//            exit;
//        }
//        $user = $this->getCurrentUser();
//        $array = explode("|!|", $record);
//        $order_id = $array[0];
//        $receive_id = $array[1];
//        $product_id = $array[3];
//        $from_location = $array[4];
//        $to_location = $array[5];
//        $modified = true;
//        $transferOrder = ClassRegistry::init('TransferOrder')->read(null, $order_id);
//        $received = ClassRegistry::init('TransferReceive')->find('first', array("conditions" => array("TransferReceive.id" => $receive_id)));
//        if (preg_match('/^[0-9]{2}\/[0-9]{2}\/[0-9]{4}$/', $received['TransferReceive']['transfer_receive_date'])) {
//            $received['TransferReceive']['transfer_receive_date'] = $this->Helper->dateConvert($received['TransferReceive']['transfer_receive_date']);
//        }
//        if (!isset($received['TransferReceive']['transfer_receive_date']) || is_null($received['TransferReceive']['transfer_receive_date']) || $received['TransferReceive']['transfer_receive_date'] == '0000-00-00' || $received['TransferReceive']['transfer_receive_date'] == '') {
//            echo MESSAGE_DATA_COULD_NOT_BE_SAVED;
//            exit;
//        }
//        $db = ConnectionManager::getDataSource('default');
//        mysql_select_db($db->config['database']);
//        // Check Total Qty in stock
//        $qty_inv = ($received['TransferReceive']['qty'] * $received['TransferReceive']['conversion']);
//        $totalQtyStock = 0;
//        $cmt = "SELECT total_qty FROM `" . $to_location . "_inventory_totals` WHERE product_id = " . $product_id . " AND location_id = " . $to_location;
//        $sql = mysql_query($cmt);
//        $r = mysql_fetch_array($sql);
//        $totalQtyStock = $r['total_qty'];
//
//        if ($qty_inv > $totalQtyStock) {
//            $modified = false;
//        }
//
//        if ($modified) {
//            $dateNow = $received['TransferReceive']['transfer_receive_date'];
//            /*
//             * Update Inventory From (Void Transfer Out)
//             */
//            $dataIn = array();
//            $dataIn['module_type']       = 4;
//            $dataIn['transfer_order_id'] = $order_id;
//            $dataIn['product_id']        = $product_id;
//            $dataIn['location_id']       = $from_location;
//            $dataIn['location_group_id'] = $transferOrder['TransferOrder']['from_location_group_id'];
//            $dataIn['lots_number']  = $received['TransferReceive']['lots_number'];
//            $dataIn['expired_date'] = $received['TransferReceive']['expired_date'];
//            $dataIn['date']         = $dateNow;
//            $dataIn['total_qty']    = $qty_inv;
//            $dataIn['total_order']  = $qty_inv;
//            $dataIn['total_free']   = 0;
//            $dataIn['user_id']      = $user['User']['id'];
//            $dataIn['customer_id']  = "";
//            $dataIn['vendor_id']    = "";
//            $dataIn['unit_cost']    = 0;
//            // Update Invetory Location
//            $this->Inventory->saveInventory($dataIn);
//            // Update Inventory Group
//            $this->Inventory->saveGroupTotalDetail($dataIn);
//            
//            /* 
//             * Update Inventory Location To (Void Transfer In)
//             */
//            // Update Inventory
//            $dataOut = array();
//            $dataOut['module_type']       = 5;
//            $dataOut['transfer_order_id'] = $order_id;
//            $dataOut['product_id']        = $product_id;
//            $dataOut['location_id']       = $to_location;
//            $dataOut['location_group_id'] = $transferOrder['TransferOrder']['to_location_group_id'];
//            $dataOut['lots_number']  = $received['TransferReceive']['lots_number'];
//            $dataOut['expired_date'] = $received['TransferReceive']['expired_date'];
//            $dataOut['date']         = $dateNow;
//            $dataOut['total_qty']    = $qty_inv;
//            $dataOut['total_order']  = $qty_inv;
//            $dataOut['total_free']   = 0;
//            $dataOut['user_id']      = $user['User']['id'];
//            $dataOut['customer_id']  = "";
//            $dataOut['vendor_id']    = "";
//            $dataOut['unit_cost']    = 0;
//            // Update Invetory Location
//            $this->Inventory->saveInventory($dataOut);
//            // Update Inventory Group
//            $this->Inventory->saveGroupTotalDetail($dataOut);
//
//            $this->data['TransferReceive']['id'] = $receive_id;
//            $this->data['TransferReceive']['modified_by'] = $user['User']['id'];
//            $this->data['TransferReceive']['status'] = 0;
//            ClassRegistry::init('TransferReceive')->saveAll($this->data);
//
//            $total_receive = ClassRegistry::init('TransferReceive')->find('first', array('fields' => array('sum(TransferReceive.qty) AS total_receive'), "conditions" => array("TransferReceive.transfer_order_id" => $order_id, 'TransferReceive.status' => 1)));
//
//            $this->data['TransferOrder']['id'] = $order_id;
//            $this->data['TransferOrder']['modified_by'] = $user['User']['id'];
//            if ($total_receive[0]['total_receive'] == 0) {
//                $this->data['TransferOrder']['status'] = 1;
//            } else {
//                $this->data['TransferOrder']['status'] = 2;
//            }
//
//            ClassRegistry::init('TransferOrder')->saveAll($this->data);
//
//            echo MESSAGE_DATA_HAS_BEEN_DELETED;
//            exit;
//        } else {
//            echo "<span style='color:red'>" . MESSAGE_SORRY_STOCK_MODIFIED . "</span>";
//            exit;
//        }
//    }
    
    function printInvoice($id = null){
        $this->layout = "ajax";
        if (!$id) {
            echo "Invalid Id";
            exit;
        }
        $this->data = ClassRegistry::init('TransferReceiveResult')->find('first', array('conditions' => array('TransferReceiveResult.transfer_order_id' => $id), 'order' => array('TransferReceiveResult.id DESC')));
        $transferReceiveDetails = ClassRegistry::init('TransferReceive')->find('all', array('conditions' => array('TransferReceive.transfer_receive_result_id' => $this->data['TransferReceiveResult']['id'])));
        $this->set(compact('transferReceiveDetails'));
    }
    
    function view($id = null){
        $this->layout = "ajax";
        if (!$id) {
            echo "Invalid Id";
            exit;
        }
        $user = $this->getCurrentUser();
        $this->Helper->saveUserActivity($user['User']['id'], 'Transfer Receive', 'View', $id);
        $order = ClassRegistry::init('TransferOrder')->read(null, $id);
        $fromLocationGroups = ClassRegistry::init('LocationGroup')->find('first', array('conditions' => array('LocationGroup.id' => $order['TransferOrder']['from_location_group_id'])));
        $toLocationGroups   = ClassRegistry::init('LocationGroup')->find('first', array("conditions" => array('LocationGroup.id' => $order['TransferOrder']['to_location_group_id'])));
        $resultDetails      = ClassRegistry::init('TransferReceiveResult')->find('all', array('conditions' => array('TransferReceiveResult.transfer_order_id' => $id)));
        $this->set(compact("order", "resultDetails", "fromLocationGroups", "toLocationGroups"));
    }

}

?>
