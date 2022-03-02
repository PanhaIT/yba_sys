<?php

class VendorConsignmentReturnsController extends AppController {

    var $name = 'VendorConsignmentReturns';
    var $components = array('Helper', 'Inventory');
    
    function viewByUser() {
        $this->layout = 'ajax';
    }

    function index() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        $this->Helper->saveUserActivity($user['User']['id'], 'Vendor Consignment Return', 'Dashboard');
    }

    function ajax($vendor = 'all', $filterStatus = 'all', $date = '') {
        $this->layout = 'ajax';
        $this->set(compact('vendor', 'filterStatus', 'date'));
    }

    function view($id = null) {
        $this->layout = 'ajax';
        if (!empty($id)) {
            $user = $this->getCurrentUser();
            $this->data = $this->VendorConsignmentReturn->read(null, $id);
            if (!empty($this->data)) {
                $this->Helper->saveUserActivity($user['User']['id'], 'Vendor Consignment Return', 'View', $id);
                $vendorConsignmentReturnDetails = ClassRegistry::init('VendorConsignmentReturnDetail')->find("all", array('conditions' => array('VendorConsignmentReturnDetail.vendor_consignment_return_id' => $id)));
                $this->set(compact('vendorConsignmentReturnDetails'));
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
            $listOutStock    = "";
            $productOrder    = array();
            for ($i = 0; $i < sizeof($_POST['product']); $i++) {
                if($_POST['product_id'][$i] != ""){
                    $productExp = $_POST['date_expired'][$i];
                    $productLot = $_POST['lots_number'][$i];
                    if($_POST['date_expired'][$i] == ''){
                        $productExp = '0000-00-00';
                    }
                    if($_POST['lots_number'][$i] == ''){
                        $productLot = 0;
                    }
                    $key = $_POST['product_id'][$i]."|".$productLot."|".$productExp;
                    if (array_key_exists($key, $productOrder)){
                        $productOrder[$key]['qty'] += $this->Helper->replaceThousand($_POST['qty'][$i] * $_POST['conversion'][$i]);
                        $productOrder[$key]['default_order'] += $_POST['default_order'][$i];
                    } else {
                        $productOrder[$key]['qty'] = $this->Helper->replaceThousand($_POST['qty'][$i] * $_POST['conversion'][$i]);
                        $productOrder[$key]['default_order'] = $_POST['default_order'][$i];
                    }
                }
            }
            foreach($productOrder AS $key => $order){
                $extract     = explode("|", $key);
                $productId   = $extract[0];
                $lotsNumber  = $extract[1];
                $expDate     = $extract[2];
                // Total PB Receive
                $totalPB = 0;
                $sqlPB = mysql_query("SELECT SUM(purchase_receives.qty * purchase_receives.conversion) AS total_pb FROM purchase_receives INNER JOIN purchase_orders ON purchase_orders.id = purchase_receives.purchase_order_id AND purchase_orders.vendor_consignment_id = {$this->data['VendorConsignmentReturn']['vendor_consignment_id']} AND purchase_orders.status > 0 WHERE purchase_orders.location_group_id = ".$this->data['VendorConsignmentReturn']['location_group_id']." AND purchase_orders.location_id = ".$this->data['VendorConsignmentReturn']['location_id']." AND purchase_receives.product_id = {$productId} AND purchase_receives.lots_number = '".$lotsNumber."' AND purchase_receives.date_expired = '".$expDate."'");
                if(mysql_num_rows($sqlPB)){
                    $rowPB = mysql_fetch_array($sqlPB);
                    $totalPB = $rowPB[0];
                }
                // Total Vendor Return Consignment
                $totalReturn = 0;
                $sqlReturn = mysql_query("SELECT SUM(qty * conversion) AS total_return FROM vendor_consignment_return_details INNER JOIN vendor_consignment_returns ON vendor_consignment_returns.id = vendor_consignment_return_details.vendor_consignment_return_id AND vendor_consignment_returns.status > 0 AND vendor_consignment_returns.location_group_id = ".$this->data['VendorConsignmentReturn']['location_group_id']." AND vendor_consignment_returns.location_id = ".$this->data['VendorConsignmentReturn']['location_id']." WHERE product_id = {$productId} AND lots_number = '".$lotsNumber."' AND date_expired = '".$expDate."'");
                if(mysql_num_rows($sqlReturn)){
                    $rowReturn = mysql_fetch_array($sqlReturn);
                    $totalReturn = $rowReturn[0];
                }
                $qtyOrder   = $order['qty'];
                $totalStock = ($order['default_order'] - $totalPB - $totalReturn)>0?($order['default_order'] - $totalPB - $totalReturn):0;
                if($qtyOrder > $totalStock){
                    $checkErrorStock = 2; 
                    $listOutStock .= $productId."|".$totalStock."-";
                }
            }
            if($checkErrorStock == 1){
                $result = array();
                // Load Model
                $this->loadModel('VendorConsignmentReturnDetail');
                $this->loadModel('StockOrder');
                
                $this->VendorConsignmentReturn->create();
                $vendorConsignmentReturn = array();
                $vendorConsignmentReturn['VendorConsignmentReturn']['code']              = $this->data['VendorConsignmentReturn']['code'];
                $vendorConsignmentReturn['VendorConsignmentReturn']['company_id']        = $this->data['VendorConsignmentReturn']['company_id'];
                $vendorConsignmentReturn['VendorConsignmentReturn']['branch_id']         = $this->data['VendorConsignmentReturn']['branch_id'];
                $vendorConsignmentReturn['VendorConsignmentReturn']['location_group_id'] = $this->data['VendorConsignmentReturn']['location_group_id'];
                $vendorConsignmentReturn['VendorConsignmentReturn']['location_id'] = $this->data['VendorConsignmentReturn']['location_id'];
                $vendorConsignmentReturn['VendorConsignmentReturn']['vendor_id']   = $this->data['VendorConsignmentReturn']['vendor_id'];
                $vendorConsignmentReturn['VendorConsignmentReturn']['vendor_consignment_id']  = $this->data['VendorConsignmentReturn']['vendor_consignment_id'];
                $vendorConsignmentReturn['VendorConsignmentReturn']['date']   = $this->data['VendorConsignmentReturn']['date'];
                $vendorConsignmentReturn['VendorConsignmentReturn']['note']   = $this->data['VendorConsignmentReturn']['note'];
                $vendorConsignmentReturn['VendorConsignmentReturn']['created_by'] = $user['User']['id'];
                $vendorConsignmentReturn['VendorConsignmentReturn']['status'] = 1;
                if ($this->VendorConsignmentReturn->save($vendorConsignmentReturn)) {
                    $result['vendor_consign_return_id'] = $vendorConsignmentReturnId = $this->VendorConsignmentReturn->id;
                    // Get Module Code
                    $modCode = $this->Helper->getModuleCode($this->data['VendorConsignmentReturn']['code'], $vendorConsignmentReturnId, 'code', 'vendor_consignment_returns', 'status != -1 AND branch_id = '.$this->data['VendorConsignmentReturn']['branch_id']);
                    // Updaet Module Code
                    mysql_query("UPDATE vendor_consignment_returns SET code = '".$modCode."' WHERE id = ".$vendorConsignmentReturnId);
                    for ($i = 0; $i < sizeof($_POST['product']); $i++) {
                        if (!empty($_POST['product_id'][$i])) {
                            /* Vendor Consignment Detail */
                            $vendorConsignmentReturnDetail = array();
                            $this->VendorConsignmentReturnDetail->create();
                            $vendorConsignmentReturnDetail['VendorConsignmentReturnDetail']['vendor_consignment_return_id']   = $vendorConsignmentReturnId;
                            $vendorConsignmentReturnDetail['VendorConsignmentReturnDetail']['product_id']  = $_POST['product_id'][$i];
                            $vendorConsignmentReturnDetail['VendorConsignmentReturnDetail']['qty_uom_id']  = $_POST['qty_uom_id'][$i];
                            $vendorConsignmentReturnDetail['VendorConsignmentReturnDetail']['default_order'] = $_POST['default_order'][$i];
                            $vendorConsignmentReturnDetail['VendorConsignmentReturnDetail']['qty']         = $_POST['qty'][$i];
                            $vendorConsignmentReturnDetail['VendorConsignmentReturnDetail']['lots_number'] = $_POST['lots_number'][$i]!=""?$_POST['lots_number'][$i]:0;
                            $vendorConsignmentReturnDetail['VendorConsignmentReturnDetail']['date_expired'] = $_POST['date_expired'][$i]!=""?$_POST['date_expired'][$i]:'0000-00-00';
                            $vendorConsignmentReturnDetail['VendorConsignmentReturnDetail']['conversion']   = $_POST['conversion'][$i];
                            $vendorConsignmentReturnDetail['VendorConsignmentReturnDetail']['note']  = $_POST['note'][$i];
                            $this->VendorConsignmentReturnDetail->save($vendorConsignmentReturnDetail);
                            
                            //Insert Into Tmp Order
                            $totalItem = $_POST['qty'][$i] * $_POST['conversion'][$i];
                            $tmpOrder = array();
                            $this->StockOrder->create();
                            $tmpOrder['StockOrder']['vendor_consignment_return_id'] = $vendorConsignmentReturnId;
                            $tmpOrder['StockOrder']['product_id'] = $_POST['product_id'][$i];
                            $tmpOrder['StockOrder']['location_group_id'] = $vendorConsignmentReturn['VendorConsignmentReturn']['location_group_id'];
                            $tmpOrder['StockOrder']['location_id']   = $vendorConsignmentReturn['VendorConsignmentReturn']['location_id'];
                            $tmpOrder['StockOrder']['lots_number']   = $_POST['lots_number'][$i];
                            $tmpOrder['StockOrder']['expired_date']  = $_POST['date_expired'][$i];
                            $tmpOrder['StockOrder']['date'] = $vendorConsignmentReturn['VendorConsignmentReturn']['date'];
                            $tmpOrder['StockOrder']['qty']  =  $totalItem;
                            $this->StockOrder->save($tmpOrder);
                            $this->Inventory->saveGroupQtyOrder($vendorConsignmentReturn['VendorConsignmentReturn']['location_group_id'], $vendorConsignmentReturn['VendorConsignmentReturn']['location_id'], $_POST['product_id'][$i], $_POST['lots_number'][$i], $_POST['date_expired'][$i], $totalItem, $vendorConsignmentReturn['VendorConsignmentReturn']['date'], '+');
                        }
                    }
                    $this->Helper->saveUserActivity($user['User']['id'], 'Vendor Consignment Return', 'Save Add New', $vendorConsignmentReturnId);
                    echo json_encode($result);
                    exit;
                } else {
                    $this->Helper->saveUserActivity($user['User']['id'], 'Vendor Consignment Return', 'Save Add New (Error)');
                    $result['code'] = 2;
                    echo json_encode($result);
                    exit;
                }
            }else{
                $this->Helper->saveUserActivity($user['User']['id'], 'Vendor Consignment Return', 'Save Add New (Error Out of Stock)');
                // Error Out of Stock
                $result['listOutStock'] = $listOutStock;
                $result['code'] = 3;
                echo json_encode($result);
                exit;
            }
        }
        $this->Helper->saveUserActivity($user['User']['id'], 'Vendor Consignment Return', 'Add New');
        // Get Loction Setting
        $locSetting = ClassRegistry::init('LocationSetting')->findById(1);
        $locCon     = '';
        if($locSetting['LocationSetting']['location_status'] == 1){
            $locCon = ' AND Location.is_for_sale = 0';
        }
        $locations  = ClassRegistry::init('Location')->find('all', array('joins' => array(array('table' => 'user_locations', 'type' => 'inner', 'conditions' => array('user_locations.location_id=Location.id'))), 'conditions' => array('user_locations.user_id=' . $user['User']['id'] . ' AND Location.is_active=1'.$locCon), 'order' => 'Location.name'));
        $locationGroups = ClassRegistry::init('LocationGroup')->find('list', array('joins' => array(array('table' => 'user_location_groups', 'type' => 'inner', 'conditions' => array('user_location_groups.location_group_id=LocationGroup.id'))),'conditions' => array('user_location_groups.user_id=' . $user['User']['id'], 'LocationGroup.is_active' => '1', 'LocationGroup.location_group_type_id != 1')));
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
                            'fields' => array('Branch.id', 'Branch.name', 'Branch.company_id', 'ModuleCodeBranch.ven_consign_return_code', 'Branch.currency_id', 'Currency.symbol'),
                            'conditions' => array('Branch.is_active = 1', 'user_branches.user_id=' . $user['User']['id'])
                        ));
        $this->set(compact("locationGroups", "locations", "companies", "branches"));
    }

    function orderDetails() {
        $this->layout = 'ajax';
        $uoms = ClassRegistry::init('Uom')->find('all', array('fields' => array('Uom.id', 'Uom.name'), 'conditions' => array('Uom.is_active' => 1)));
        $this->set(compact('uoms'));
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
        $vendorConsignmentReturn = $this->VendorConsignmentReturn->read(null, $id);
        if($vendorConsignmentReturn['VendorConsignmentReturn']['status'] == 1){
            $this->VendorConsignmentReturn->updateAll(
                    array('VendorConsignmentReturn.status' => 0, 'VendorConsignmentReturn.modified_by' => $user['User']['id']),
                    array('VendorConsignmentReturn.id' => $id)
            );
            // Reset Stock Order
            $sqlResetOrder = mysql_query("SELECT * FROM stock_orders WHERE `vendor_consignment_return_id`=".$id.";");
            while($rowResetOrder = mysql_fetch_array($sqlResetOrder)){
                $this->Inventory->saveGroupQtyOrder($rowResetOrder['location_group_id'], $rowResetOrder['location_id'], $rowResetOrder['product_id'], $rowResetOrder['lots_number'], $rowResetOrder['expired_date'], $rowResetOrder['qty'], $rowResetOrder['date'], '-');
            }
            // Detele Tmp Stock Order
            mysql_query("DELETE FROM `stock_orders` WHERE  `vendor_consignment_return_id`=".$id.";");
            $this->Helper->saveUserActivity($user['User']['id'], 'Vendor Consignment Return', 'Void', $id);
            echo MESSAGE_DATA_HAS_BEEN_DELETED;
            exit;
        } else {
            $this->Helper->saveUserActivity($user['User']['id'], 'Vendor Consignment Return', 'Void (Error Status)', $id);
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
                    $productExp = $_POST['date_expired'][$i];
                    $productLot = $_POST['lots_number'][$i];
                    if($_POST['date_expired'][$i] == ''){
                        $productExp = '0000-00-00';
                    }
                    if($_POST['lots_number'][$i] == ''){
                        $productLot = 0;
                    }
                    $key  = $_POST['product_id'][$i]."|".$productLot."|".$productExp;
                    if (array_key_exists($key, $productOrder)){
                        $productOrder[$key]['qty'] += $this->Helper->replaceThousand($_POST['qty'][$i] * $_POST['conversion'][$i]);
                        $productOrder[$key]['default_order'] += $_POST['default_order'][$i];
                    } else {
                        $productOrder[$key]['qty'] = $this->Helper->replaceThousand($_POST['qty'][$i] * $_POST['conversion'][$i]);
                        $productOrder[$key]['default_order'] = $_POST['default_order'][$i];
                    }
                }
            }
            foreach($productOrder AS $key => $order){
                $extract     = explode("|", $key);
                $productId   = $extract[0];
                $lotsNumber  = $extract[1];
                $expDate     = $extract[2];
                // Total PB Receive
                $totalPB = 0;
                $sqlPB = mysql_query("SELECT SUM(purchase_receives.qty * purchase_receives.conversion) AS total_pb FROM purchase_receives INNER JOIN purchase_orders ON purchase_orders.id = purchase_receives.purchase_order_id AND purchase_orders.vendor_consignment_id = {$this->data['VendorConsignmentReturn']['vendor_consignment_id']} AND purchase_orders.status > 0 WHERE purchase_orders.location_group_id = ".$this->data['VendorConsignmentReturn']['location_group_id']." AND purchase_orders.location_id = ".$this->data['VendorConsignmentReturn']['location_id']." AND purchase_receives.product_id = {$productId} AND purchase_receives.lots_number = '".$lotsNumber."' AND purchase_receives.date_expired = '".$expDate."'");
                if(mysql_num_rows($sqlPB)){
                    $rowPB = mysql_fetch_array($sqlPB);
                    $totalPB = $rowPB[0];
                }
                // Total Vendor Return Consignment
                $totalReturn = 0;
                $sqlReturn = mysql_query("SELECT SUM(qty * conversion) AS total_return FROM vendor_consignment_return_details INNER JOIN vendor_consignment_returns ON vendor_consignment_returns.id = vendor_consignment_return_details.vendor_consignment_return_id AND vendor_consignment_returns.status > 0 AND vendor_consignment_returns.location_group_id = ".$this->data['VendorConsignmentReturn']['location_group_id']." AND vendor_consignment_returns.location_id = ".$this->data['VendorConsignmentReturn']['location_id']." WHERE vendor_consignment_return_id != {$id} AND  product_id = {$productId} AND lots_number = '".$lotsNumber."' AND date_expired = '".$expDate."'");
                if(mysql_num_rows($sqlReturn)){
                    $rowReturn = mysql_fetch_array($sqlReturn);
                    $totalReturn = $rowReturn[0];
                }
                $qtyOrder   = $order['qty'];
                $totalStock = ($order['default_order'] - $totalPB - $totalReturn)>0?($order['default_order'] - $totalPB - $totalReturn):0;
                if($qtyOrder > $totalStock){
                    $checkErrorStock = 2; 
                    $listOutStock .= $productId."|".$totalStock."-";
                }
            }
            if($checkErrorStock == 1){
                $vendorConsignmentReturn = $this->VendorConsignmentReturn->read(null, $id);
                if ($vendorConsignmentReturn['VendorConsignmentReturn']['status'] == 1) {
                    $created   = $vendorConsignmentReturn['VendorConsignmentReturn']['created'];
                    $createdBy = $vendorConsignmentReturn['VendorConsignmentReturn']['created_by'];
                    $result = array();
                    $statuEdit = "-1";
                    // Load Model
                    $this->loadModel('StockOrder');
                    $this->loadModel('VendorConsignmentReturnDetail');
                    // Reset Stock Order
                    $sqlResetOrder = mysql_query("SELECT * FROM stock_orders WHERE `vendor_consignment_return_id`=".$id.";");
                    while($rowResetOrder = mysql_fetch_array($sqlResetOrder)){
                        $this->Inventory->saveGroupQtyOrder($rowResetOrder['location_group_id'], $rowResetOrder['location_id'], $rowResetOrder['product_id'], $rowResetOrder['lots_number'], $rowResetOrder['expired_date'], $rowResetOrder['qty'], $rowResetOrder['date'], '-');
                    }
                    // Detele Tmp Stock Order
                    mysql_query("DELETE FROM `stock_orders` WHERE  `vendor_consignment_return_id`=".$id.";");
                    // Update Status Edit
                    $this->VendorConsignmentReturn->updateAll(
                            array('VendorConsignmentReturn.status' => $statuEdit, 'VendorConsignmentReturn.modified_by' => $user['User']['id']),
                            array('VendorConsignmentReturn.id' => $id)
                    );
                    
                    $this->VendorConsignmentReturn->create();
                    $vendorConsignmentReturn = array();
                    $vendorConsignmentReturn['VendorConsignmentReturn']['code']              = $this->data['VendorConsignmentReturn']['code'];
                    $vendorConsignmentReturn['VendorConsignmentReturn']['company_id']        = $this->data['VendorConsignmentReturn']['company_id'];
                    $vendorConsignmentReturn['VendorConsignmentReturn']['branch_id']         = $this->data['VendorConsignmentReturn']['branch_id'];
                    $vendorConsignmentReturn['VendorConsignmentReturn']['location_group_id'] = $this->data['VendorConsignmentReturn']['location_group_id'];
                    $vendorConsignmentReturn['VendorConsignmentReturn']['location_id'] = $this->data['VendorConsignmentReturn']['location_id'];
                    $vendorConsignmentReturn['VendorConsignmentReturn']['vendor_id']   = $this->data['VendorConsignmentReturn']['vendor_id'];
                    $vendorConsignmentReturn['VendorConsignmentReturn']['vendor_consignment_id']  = $this->data['VendorConsignmentReturn']['vendor_consignment_id'];
                    $vendorConsignmentReturn['VendorConsignmentReturn']['date']   = $this->data['VendorConsignmentReturn']['date'];
                    $vendorConsignmentReturn['VendorConsignmentReturn']['note']   = $this->data['VendorConsignmentReturn']['note'];
                    $vendorConsignmentReturn['VendorConsignmentReturn']['created'] = $created;
                    $vendorConsignmentReturn['VendorConsignmentReturn']['created_by'] = $createdBy;
                    $vendorConsignmentReturn['VendorConsignmentReturn']['edited'] = date("Y-m-d H:i:s");
                    $vendorConsignmentReturn['VendorConsignmentReturn']['edited_by'] = $user['User']['id'];
                    $vendorConsignmentReturn['VendorConsignmentReturn']['status'] = 1;
                    if ($this->VendorConsignmentReturn->save($vendorConsignmentReturn)) {
                        $result['vendor_consign_return_id'] = $vendorConsignmentReturnId = $this->VendorConsignmentReturn->id;
                        if($this->data['VendorConsignmentReturn']['branch_id'] != $vendorConsignmentReturn['VendorConsignmentReturn']['branch_id']){
                            // Get Module Code
                            $modCode = $this->Helper->getModuleCode($this->data['VendorConsignmentReturn']['code'], $vendorConsignmentReturnId, 'code', 'vendor_consignment_returns', 'status != -1 AND branch_id = '.$this->data['VendorConsignmentReturn']['branch_id']);
                            // Updaet Module Code
                            mysql_query("UPDATE vendor_consignment_returns SET code = '".$modCode."' WHERE id = ".$vendorConsignmentReturnId);
                        }
                        for ($i = 0; $i < sizeof($_POST['product']); $i++) {
                            if (!empty($_POST['product_id'][$i])) {
                                /* Vendor Consignment Detail */
                                $vendorConsignmentReturnDetail = array();
                                $this->VendorConsignmentReturnDetail->create();
                                $vendorConsignmentReturnDetail['VendorConsignmentReturnDetail']['vendor_consignment_return_id']   = $vendorConsignmentReturnId;
                                $vendorConsignmentReturnDetail['VendorConsignmentReturnDetail']['product_id']  = $_POST['product_id'][$i];
                                $vendorConsignmentReturnDetail['VendorConsignmentReturnDetail']['qty_uom_id']  = $_POST['qty_uom_id'][$i];
                                $vendorConsignmentReturnDetail['VendorConsignmentReturnDetail']['default_order'] = $_POST['default_order'][$i];
                                $vendorConsignmentReturnDetail['VendorConsignmentReturnDetail']['qty']         = $_POST['qty'][$i];
                                $vendorConsignmentReturnDetail['VendorConsignmentReturnDetail']['lots_number']  = $_POST['lots_number'][$i]!=""?$_POST['lots_number'][$i]:0;
                                $vendorConsignmentReturnDetail['VendorConsignmentReturnDetail']['date_expired'] = $_POST['date_expired'][$i]!=""?$_POST['date_expired'][$i]:'0000-00-00';
                                $vendorConsignmentReturnDetail['VendorConsignmentReturnDetail']['conversion']   = $_POST['conversion'][$i];
                                $vendorConsignmentReturnDetail['VendorConsignmentReturnDetail']['note']  = $_POST['note'][$i];
                                $this->VendorConsignmentReturnDetail->save($vendorConsignmentReturnDetail);

                                //Insert Into Tmp Order
                                $totalItem = $_POST['qty'][$i] * $_POST['conversion'][$i];
                                $tmpOrder = array();
                                $this->StockOrder->create();
                                $tmpOrder['StockOrder']['vendor_consignment_return_id'] = $vendorConsignmentReturnId;
                                $tmpOrder['StockOrder']['product_id'] = $_POST['product_id'][$i];
                                $tmpOrder['StockOrder']['location_group_id'] = $vendorConsignmentReturn['VendorConsignmentReturn']['location_group_id'];
                                $tmpOrder['StockOrder']['location_id']   = $vendorConsignmentReturn['VendorConsignmentReturn']['location_id'];
                                $tmpOrder['StockOrder']['lots_number']   = $_POST['lots_number'][$i];
                                $tmpOrder['StockOrder']['expired_date']  = $_POST['date_expired'][$i];
                                $tmpOrder['StockOrder']['date'] = $vendorConsignmentReturn['VendorConsignmentReturn']['date'];
                                $tmpOrder['StockOrder']['qty']  =  $totalItem;
                                $this->StockOrder->save($tmpOrder);
                                $this->Inventory->saveGroupQtyOrder($vendorConsignmentReturn['VendorConsignmentReturn']['location_group_id'], $vendorConsignmentReturn['VendorConsignmentReturn']['location_id'], $_POST['product_id'][$i], $_POST['lots_number'][$i], $_POST['date_expired'][$i], $totalItem, $vendorConsignmentReturn['VendorConsignmentReturn']['date'], '+');
                            }
                        }
                        $this->Helper->saveUserActivity($user['User']['id'], 'Vendor Consignment Return', 'Save Edit', $id, $vendorConsignmentReturnId);
                        echo json_encode($result);
                        exit;
                    } else {
                        $this->Helper->saveUserActivity($user['User']['id'], 'Vendor Consignment Return', 'Save Edit (Error)', $id);
                        // Error Saves
                        $result['error'] = 2;
                        echo json_encode($result);
                        exit;
                    }
                } else {
                    // Error Saves
                    $this->Helper->saveUserActivity($user['User']['id'], 'Vendor Consignment Return', 'Save Edit (Error Status)', $id);
                    $result['error'] = 2;
                    echo json_encode($result);
                    exit;
                }
            }else{
                $this->Helper->saveUserActivity($user['User']['id'], 'Vendor Consignment Return', 'Save Edit (Error Out of Stock)', $id);
                // Error Out Of Stock
                $result['listOutStock'] = $listOutStock;
                $result['code'] = '3';
                echo json_encode($result);
                exit;
            }
        }

        if (!empty($id)) {
            $this->Helper->saveUserActivity($user['User']['id'], 'Vendor Consignment Return', 'Edit', $id);
            $this->data = $this->VendorConsignmentReturn->read(null, $id);
            // Get Loction Setting
            $locSetting = ClassRegistry::init('LocationSetting')->findById(1);
            $locCon     = '';
            if($locSetting['LocationSetting']['location_status'] == 1){
                $locCon = ' AND Location.is_for_sale = 0';
            }
            $locations  = ClassRegistry::init('Location')->find('all', array('joins' => array(array('table' => 'user_locations', 'type' => 'inner', 'conditions' => array('user_locations.location_id=Location.id'))), 'conditions' => array('user_locations.user_id=' . $user['User']['id'] . ' AND Location.is_active=1'.$locCon), 'order' => 'Location.name'));
            $locationGroups = ClassRegistry::init('LocationGroup')->find('list', array('joins' => array(array('table' => 'user_location_groups', 'type' => 'inner', 'conditions' => array('user_location_groups.location_group_id=LocationGroup.id'))),'conditions' => array('user_location_groups.user_id=' . $user['User']['id'], 'LocationGroup.is_active' => '1', 'LocationGroup.location_group_type_id != 1')));
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
                                'fields' => array('Branch.id', 'Branch.name', 'Branch.company_id', 'ModuleCodeBranch.ven_consign_return_code', 'Branch.currency_id', 'Currency.symbol'),
                                'conditions' => array('Branch.is_active = 1', 'user_branches.user_id=' . $user['User']['id'])
                            ));
            $this->set(compact("locationGroups","locations","companies","branches"));
        }else{
            exit;
        }
    }
    
    function editDetail($vendorConsignmentReturnId = null) {
        $this->layout = 'ajax';
        if ($vendorConsignmentReturnId >= 0) {
            $vendorConsignmentReturn         = ClassRegistry::init('VendorConsignmentReturn')->find("first", array('conditions' => array('VendorConsignmentReturn.id' => $vendorConsignmentReturnId)));
            $vendorConsignmentReturnDetails  = ClassRegistry::init('VendorConsignmentReturnDetail')->find("all", array('conditions' => array('VendorConsignmentReturnDetail.vendor_consignment_return_id' => $vendorConsignmentReturnId)));
            $uoms = ClassRegistry::init('Uom')->find('all', array('fields' => array('Uom.id', 'Uom.name'), 'conditions' => array('Uom.is_active' => 1)));
            $locSetting = ClassRegistry::init('LocationSetting')->findById(4);
            $this->set(compact('vendorConsignmentReturn', 'vendorConsignmentReturnDetails', 'uoms', 'locSetting'));
        } else {
            exit;
        }
    }

    function printInvoice($id = null){
        $this->layout = 'ajax';
        if (!empty($id)) {
            $vendorConsignmentReturn = $this->VendorConsignmentReturn->read(null, $id);
            if (!empty($vendorConsignmentReturn)) {
                $vendorConsignmentReturnDetails = ClassRegistry::init('VendorConsignmentReturnDetail')->find("all", array('conditions' => array('VendorConsignmentReturnDetail.vendor_consignment_return_id' => $id)));
                $this->set(compact('vendorConsignmentReturn', 'vendorConsignmentReturnDetails'));
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
            $result['error'] = 0;
            echo json_encode($result);
            exit;
        }
        $user = $this->getCurrentUser();
        if (!empty($this->data)) {
            $result = array();
            $result['error'] = 0;
            $vendorConsignmentReturn = $this->VendorConsignmentReturn->read(null, $this->data['vendor_consignment_return_id']);
            if ($vendorConsignmentReturn['VendorConsignmentReturn']['status'] == 1) {
                $checkErrorStock = 1;
                $productOrder = array();
                $vendorConsignmentReturnDetails = ClassRegistry::init('VendorConsignmentReturnDetail')->find("all", array('conditions' => array('VendorConsignmentReturnDetail.vendor_consignment_return_id' => $this->data['vendor_consignment_return_id'])));
                foreach ($vendorConsignmentReturnDetails AS $vendorConsignmentReturnDetail) {
                    $key      = $vendorConsignmentReturnDetail['VendorConsignmentReturnDetail']['product_id']."|".$vendorConsignmentReturnDetail['VendorConsignmentReturnDetail']['lots_number']."|".$vendorConsignmentReturnDetail['VendorConsignmentReturnDetail']['date_expired'];
                    if (array_key_exists($key, $productOrder)){
                        $productOrder[$key]['qty'] += $this->Helper->replaceThousand($vendorConsignmentReturnDetail['VendorConsignmentReturnDetail']['qty'] * $vendorConsignmentReturnDetail['VendorConsignmentReturnDetail']['conversion']);
                        $productOrder[$key]['default_order'] += $vendorConsignmentReturnDetail['VendorConsignmentReturnDetail']['default_order'];
                    } else {
                        $productOrder[$key]['qty'] = $this->Helper->replaceThousand($vendorConsignmentReturnDetail['VendorConsignmentReturnDetail']['qty'] * $vendorConsignmentReturnDetail['VendorConsignmentReturnDetail']['conversion']);
                        $productOrder[$key]['default_order'] = $vendorConsignmentReturnDetail['VendorConsignmentReturnDetail']['default_order'];
                    }
                }
                foreach($productOrder AS $key => $order){
                    $extract     = explode("|", $key);
                    $productId   = $extract[0];
                    $lotsNumber  = $extract[1];
                    $expDate     = $extract[2];
                    // Total PB Receive
                    $totalPB = 0;
                    $sqlPB = mysql_query("SELECT SUM(purchase_receives.qty * purchase_receives.conversion) AS total_pb FROM purchase_receives INNER JOIN purchase_orders ON purchase_orders.id = purchase_receives.purchase_order_id AND purchase_orders.vendor_consignment_id = {$vendorConsignmentReturn['VendorConsignmentReturn']['vendor_consignment_id']} AND purchase_orders.status > 0 WHERE purchase_orders.location_group_id = ".$vendorConsignmentReturn['VendorConsignmentReturn']['location_group_id']." AND purchase_orders.location_id = ".$vendorConsignmentReturn['VendorConsignmentReturn']['location_id']." AND purchase_receives.product_id = {$productId} AND purchase_receives.lots_number = '".$lotsNumber."' AND purchase_receives.date_expired = '".$expDate."'");
                    if(mysql_num_rows($sqlPB)){
                        $rowPB = mysql_fetch_array($sqlPB);
                        $totalPB = $rowPB[0];
                    }
                    // Total Vendor Return Consignment
                    $totalReturn = 0;
                    $sqlReturn = mysql_query("SELECT SUM(qty * conversion) AS total_return FROM vendor_consignment_return_details INNER JOIN vendor_consignment_returns ON vendor_consignment_returns.id = vendor_consignment_return_details.vendor_consignment_return_id AND vendor_consignment_returns.status > 0 AND vendor_consignment_returns.location_group_id = ".$vendorConsignmentReturn['VendorConsignmentReturn']['location_group_id']." AND vendor_consignment_returns.location_id = ".$vendorConsignmentReturn['VendorConsignmentReturn']['location_id']." WHERE vendor_consignment_return_id != ".$vendorConsignmentReturn['VendorConsignmentReturn']['id']." AND  product_id = {$productId} AND lots_number = '".$lotsNumber."' AND date_expired = '".$expDate."'");
                    if(mysql_num_rows($sqlReturn)){
                        $rowReturn = mysql_fetch_array($sqlReturn);
                        $totalReturn = $rowReturn[0];
                    }
                    $totalStock = ($order['default_order'] - $totalPB - $totalReturn)>0?($order['default_order'] - $totalPB - $totalReturn):0;
                    if($order['qty'] > $totalStock){
                        $checkErrorStock = 2;
                    }
                }
                if($checkErrorStock == 1){
                    $dateVendorConsignmentReturn  = $vendorConsignmentReturn['VendorConsignmentReturn']['date'];
                    // List Sale Detail
                    foreach ($vendorConsignmentReturnDetails AS $vendorConsignmentReturnDetail) {
                        $totalOrder = $vendorConsignmentReturnDetail['VendorConsignmentReturnDetail']['qty'] * $vendorConsignmentReturnDetail['VendorConsignmentReturnDetail']['conversion'];
                        // Update Inventory (Out)
                        $productLost = $vendorConsignmentReturnDetail['VendorConsignmentReturnDetail']['lots_number']!=''?$vendorConsignmentReturnDetail['VendorConsignmentReturnDetail']['lots_number']:0;
                        $productExp  = $vendorConsignmentReturnDetail['VendorConsignmentReturnDetail']['date_expired']!=''?$vendorConsignmentReturnDetail['VendorConsignmentReturnDetail']['date_expired']:'0000-00-00';
                        $data = array();
                        $data['module_type']       = 17;
                        $data['vendor_consignment_return_id'] = $vendorConsignmentReturn['VendorConsignmentReturn']['id'];
                        $data['product_id']        = $vendorConsignmentReturnDetail['VendorConsignmentReturnDetail']['product_id'];
                        $data['location_id']       = $vendorConsignmentReturn['VendorConsignmentReturn']['location_id'];
                        $data['location_group_id'] = $vendorConsignmentReturn['VendorConsignmentReturn']['location_group_id'];
                        $data['lots_number']  = $productLost;
                        $data['expired_date'] = $productExp;
                        $data['date']         = $dateVendorConsignmentReturn;
                        $data['total_qty']    = $totalOrder;
                        $data['total_order']  = $totalOrder;
                        $data['total_free']   = 0;
                        $data['user_id']      = $user['User']['id'];
                        $data['customer_id']  = "";
                        $data['vendor_id']    = $vendorConsignmentReturn['VendorConsignmentReturn']['vendor_id'];
                        $data['unit_cost']    = 0;
                        $data['unit_price']   = 0;
                        // Update Invetory Location
                        $this->Inventory->saveInventory($data);
                        // Update Inventory Group
                        $this->Inventory->saveGroupTotalDetail($data);
                        // Reset Stock Order
                        $this->Inventory->saveGroupQtyOrder($vendorConsignmentReturn['VendorConsignmentReturn']['location_group_id'], $vendorConsignmentReturn['VendorConsignmentReturn']['location_id'], $vendorConsignmentReturnDetail['VendorConsignmentReturnDetail']['product_id'], $productLost, $productExp, $totalOrder, $dateVendorConsignmentReturn, '-');
                    }
                    // Update VendorConsignmentReturn
                    mysql_query("UPDATE vendor_consignment_returns SET status = 2, modified = '".date("Y-m-d H:i:s")."', modified_by = ".$user['User']['id']." WHERE id = ".$vendorConsignmentReturn['VendorConsignmentReturn']['id']);
                    // Delete Tmp Stock Order
                    mysql_query("DELETE FROM `stock_orders` WHERE  `vendor_consignment_return_id`= " . $vendorConsignmentReturn['VendorConsignmentReturn']['id']);
                    $this->Helper->saveUserActivity($user['User']['id'], 'Vendor Consignment Return', 'Save Receive', $vendorConsignmentReturn['VendorConsignmentReturn']['id']);
                } else {
                    $this->Helper->saveUserActivity($user['User']['id'], 'Vendor Consignment Return', 'Save Receive (Out of Stock)', $vendorConsignmentReturn['VendorConsignmentReturn']['id']);
                    $result['error'] = 2;
                }
            } else {
                $this->Helper->saveUserActivity($user['User']['id'], 'Vendor Consignment Return', 'Save Receive (Error Status)', $vendorConsignmentReturn['VendorConsignmentReturn']['id']);
                $result['error'] = 1;
            }
            echo json_encode($result);
            exit;
        }
        $this->Helper->saveUserActivity($user['User']['id'], 'Vendor Consignment Return', 'Receive', $id);
        $this->data = $this->VendorConsignmentReturn->read(null, $id);
        if (!empty($this->data)) {
            $vendorConsignmentReturnDetails = ClassRegistry::init('VendorConsignmentReturnDetail')->find("all", array('conditions' => array('VendorConsignmentReturnDetail.vendor_consignment_return_id' => $id)));
            $this->set(compact('vendorConsignmentReturnDetails'));
        } else {
            exit;
        }
    }
    
    function vendorConsignment($companyId = null, $branchId = null, $locationGroupId = null, $locationId = null, $vendorId = '') {
        $this->layout = 'ajax';
        $this->set(compact('companyId', 'vendorId', 'branchId', 'locationGroupId', 'locationId'));
    }

    function vendorConsignmentAjax($companyId = null, $branchId = null, $locationGroupId = null, $locationId = null, $vendorId = '') {
        $this->layout = 'ajax';
        $this->set(compact('companyId', 'vendorId', 'branchId', 'locationGroupId', 'locationId'));
    }
    
    function getVendorConsignmentReturn($id = null, $dateOrder = null, $returnId = 0){
        $this->layout = 'ajax';
        $result = array();
        if (empty($id) && empty($dateOrder)) {
            $result['error'] = 1;
            echo json_encode($result);
            exit;
        }
        $sqlSettingUomDeatil = mysql_query("SELECT uom_detail_option, calculate_cogs FROM setting_options");
        $rowSettingUomDetail = mysql_fetch_array($sqlSettingUomDeatil);
        $vendorVendorConsignment = ClassRegistry::init('VendorConsignment')->read(null, $id);
        $rowList = array();
        $rowLbl  = "";
        $index   = '';
        // Get Product
        $sqlSalesDetail  = mysql_query("SELECT products.id AS product_id, products.code AS code, products.barcode AS barcode, products.name AS name, products.small_val_uom AS small_val_uom, products.price_uom_id AS price_uom_id, products.is_expired_date AS is_expired_date, sd.qty AS qty, sd.qty_uom_id AS qty_uom_id, sd.conversion AS conversion, IFNULL(IF(sd.lots_number!='',sd.lots_number,0), 0) AS lots_number, IFNULL(sd.date_expired, '0000-00-00') AS date_expired, sd.note AS note, so.location_group_id AS location_group_id FROM vendor_consignment_details AS sd INNER JOIN vendor_consignments AS so ON so.id = sd.vendor_consignment_id INNER JOIN products ON products.id = sd.product_id WHERE sd.vendor_consignment_id = ".$id.";");
        while($rowDetail = mysql_fetch_array($sqlSalesDetail)){
            // Total PB Receive
            $totalPB = 0;
            $sqlPB = mysql_query("SELECT SUM((purchase_order_details.qty + purchase_order_details.qty_free) * purchase_order_details.conversion) AS total_pb FROM purchase_order_details INNER JOIN purchase_orders ON purchase_orders.id = purchase_order_details.purchase_order_id AND purchase_orders.vendor_consignment_id = {$vendorVendorConsignment['VendorConsignment']['id']} AND purchase_orders.status > 0 WHERE purchase_orders.location_group_id = ".$vendorVendorConsignment['VendorConsignment']['location_group_id']." AND purchase_orders.location_id = ".$vendorVendorConsignment['VendorConsignment']['location_id']." AND purchase_order_details.product_id = {$rowDetail['product_id']} AND purchase_order_details.lots_number = '".$rowDetail['lots_number']."' AND purchase_order_details.date_expired = '".$rowDetail['date_expired']."'");
            if(mysql_num_rows($sqlPB)){
                $rowPB = mysql_fetch_array($sqlPB);
                $totalPB = $rowPB[0];
            }
            // Total Vendor Return Consignment
            $totalReturn = 0;
            $sqlReturn = mysql_query("SELECT SUM(qty * conversion) AS total_return FROM vendor_consignment_return_details INNER JOIN vendor_consignment_returns ON vendor_consignment_returns.id = vendor_consignment_return_details.vendor_consignment_return_id AND vendor_consignment_returns.status > 0 AND vendor_consignment_returns.location_group_id = ".$vendorVendorConsignment['VendorConsignment']['location_group_id']." AND vendor_consignment_returns.location_id = ".$vendorVendorConsignment['VendorConsignment']['location_id']." WHERE vendor_consignment_return_id != ".$returnId." AND  product_id = {$rowDetail['product_id']} AND lots_number = '".$rowDetail['lots_number']."' AND date_expired = '".$rowDetail['date_expired']."'");
            if(mysql_num_rows($sqlReturn)){
                $rowReturn = mysql_fetch_array($sqlReturn);
                $totalReturn = $rowReturn[0];
            }
            $defaultOrder = ($rowDetail['qty'] * $rowDetail['conversion']);
            $totalOrder   = $defaultOrder;
            $totalQtyVendorConsignmentReturn = ($defaultOrder - $totalPB - $totalReturn)>0?($defaultOrder - $totalPB - $totalReturn):0;
            $qty = $rowDetail['qty'];
            $isSmallSelected = 0;
            $conversion = $rowDetail['conversion'];
            if($totalQtyVendorConsignmentReturn < $totalOrder){
                $totalOrder = $totalQtyVendorConsignmentReturn;
                $qty = $totalQtyVendorConsignmentReturn;
                $isSmallSelected = 1;
                $conversion = 1;
            }
            $index = rand();
            $productName = str_replace('"', '&quot;', $rowDetail['name']);
            // Open Tr
            $rowLbl .= '<tr class="tblVendorConsignmentReturnList">';
            // Index
            $rowLbl .= '<td class="first" style="width:5%; text-align: center;padding: 0px; height: 30px;">'.++$index.'</td>';
            // UPC
            $rowLbl .= '<td style="width:10%; text-align: left; padding: 5px;"><input type="text" readonly="" class="lblUPC" value="'.$rowDetail['barcode'].'" /></td>';
            // SKU
            $rowLbl .= '<td style="width:10%; text-align: left; padding: 5px;"><input type="text" readonly="" class="lblSKU" value="'.$rowDetail['code'].'" /></td>';
            // Product
            $rowLbl .= '<td style="width:23%; text-align: left; padding: 5px;">';
            $rowLbl .= '<div class="inputContainer" style="width:100%">';
            $rowLbl .= '<input type="hidden" class="totalQtyVendorConsignmentReturn" value="'.$totalQtyVendorConsignmentReturn.'" />';
            $rowLbl .= '<input type="hidden" class="totalQtyOrderVendorConsignmentReturn" value="'.$totalOrder.'" />';
            $rowLbl .= '<input type="hidden" value="'.$defaultOrder.'" name="default_order[]" />';
            $rowLbl .= '<input type="hidden" id="product_id_'.$index.'" class="product_id" value="'.$rowDetail['product_id'].'" name="product_id[]" />';
            $rowLbl .= '<input type="hidden" value="'.$conversion.'" name="conversion[]" class="conversion" />';
            $rowLbl .= '<input type="hidden" value="'.$rowDetail['small_val_uom'].'" name="small_val_uom[]" class="small_val_uom" />';
            $rowLbl .= '<input type="hidden" value="'.$rowDetail['note'].'" name="note[]" id="note" readonly="readonly" class="note" />';
            $rowLbl .= '<input type="text" id="productName_'.$index.'" value="'.$productName.'" id="product" name="product[]" class="product validate[required]" style="width: 90%;" />';
            $rowLbl .= '<img alt="Note" src="'.$this->webroot.'img/button/note.png" class="noteAddVendorConsignmentReturn" align="absmiddle" style="cursor: pointer;" onmouseover="Tip(\'Note\')" />';
            $rowLbl .= '</div>';
            $rowLbl .= '</td>';
            // Lots Number
            $losDisplay = '';
            if($rowSettingUomDetail[0] == 0){
                $losDisplay = 'display: none;';
            }
            $rowLbl .= '<td style="width:11%; padding: 0px; text-align: center; '.$losDisplay.'">';
            $rowLbl .= '<div class="inputContainer" style="width:100%">';
            $rowLbl .= '<input type="text" readonly="readonly" value="'.$rowDetail['lots_number'].'" id="lots_number_'.$index.'" name="lots_number[]" style="width:90%;" class="lots_number" />';
            $rowLbl .= '</div>';
            $rowLbl .= '</td>';
            // Expiry Date
            $expDisplay = '';
            $class = '';
            if($rowDetail['is_expired_date'] == 0){
                $expDisplay = 'visibility: hidden;';
                $dateExp = '0000-00-00';
            } else {
                $dateExp = '';
                $class = 'class="date_expired validate[required]"';
                if($rowDetail['date_expired'] != "" && $rowDetail['date_expired'] != "0000-00-00"){
                    $dateExp = dateShort($rowDetail['date_expired']);
                }
            }
            $rowLbl .= '<td style="width:11%; text-align: center; padding: 0px;">';
            $rowLbl .= '<div class="inputContainer" style="width:100%">';
            $rowLbl .= '<input type="text" readonly="readonly" value="'.$dateExp.'" id="date_expired_'.$index.'" name="date_expired[]" style="width:90%; '.$expDisplay.'" '.$class.' />';
            $rowLbl .= '</div>';
            $rowLbl .= '</td>';
            // Qty
            $rowLbl .= '<td style="width:8%; text-align: center;padding: 0px;">';
            $rowLbl .= '<div class="inputContainer" style="width:100%">';
            $rowLbl .= '<input type="text" value="'.$qty.'" id="qty_'.$index.'" name="qty[]" style="width:90%;" class="qty interger" />';
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
                $selected   = "";
                $isMain     = "other";
                $isSmall    = 0;
                // Check With Qty UOM Id
                if($data['id'] == $rowDetail['qty_uom_id'] && $isSmallSelected == 0){   
                    $selected = ' selected="selected" ';
                }
                // Check With Product UOM Id
                if($data['id'] == $rowDetail['price_uom_id']){
                    $isMain = "first";
                }
                // Check Is Small UOM
                if($length == $i && $isSmallSelected == 1){
                    $isSmall  = 1;
                    $selected = ' selected="selected" ';
                }
                $optionUom .= '<option '.$selected.' data-sm="'.$isSmall.'" data-item="'.$isMain.'" value="'.$data['id'].'" conversion="'.$data['conversion'].'">'.$data['name'].'</option>';
                $i++;
            }
            $rowLbl .= '<td style="width:15%; padding: 0px; text-align: center">';
            $rowLbl .= '<div class="inputContainer" style="width:100%">';
            $rowLbl .= '<select id="qty_uom_id_'.$index.'" style="width:90%; height: 20px;" name="qty_uom_id[]" class="qty_uom_id validate[required]">'.$optionUom.'</select>';
            $rowLbl .= '</div>';
            $rowLbl .= '</td>';
            // Button Remove
            $rowLbl .= '<td style="width:7%">';
            $rowLbl .= '<img alt="Remove" src="'.$this->webroot.'img/button/cross.png" class="btnRemoveVendorConsignmentReturn" align="absmiddle" style="cursor: pointer;" onmouseover="Tip(\'Remove\')" />';
            $rowLbl .= '&nbsp; <img alt="Up" src="'.$this->webroot.'img/button/move_up.png" class="btnMoveUpVendorConsignmentReturn" align="absmiddle" style="cursor: pointer;" onmouseover="Tip(\'Up\')" />';
            $rowLbl .= '&nbsp; <img alt="Down" src="'.$this->webroot . 'img/button/move_down.png" class="btnMoveDownVendorConsignmentReturn" align="absmiddle" style="cursor: pointer;" onmouseover="Tip(\'Down\')" />';
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