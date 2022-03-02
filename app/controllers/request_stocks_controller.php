<?php

class RequestStocksController extends AppController {

    var $name = 'RequestStocks';
    var $components = array('Helper');

    function index() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        $this->Helper->saveUserActivity($user['User']['id'], 'Request Stock', 'Dashboard');
    }

    function ajax($status = 'all', $warehouseTo = 'all', $date = '') {
        $this->layout = 'ajax';
        $this->set(compact('status', 'warehouseTo', 'date'));
    }

    function view($id = null) {
        $this->layout = 'ajax';
        if (!$id) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $user = $this->getCurrentUser();
        $this->Helper->saveUserActivity($user['User']['id'], 'Request Stock', 'View', $id);
        $this->data = $this->RequestStock->read(null, $id);
        $requestStockDetails = ClassRegistry::init('RequestStockDetail')->find('all', array("conditions" => array("RequestStockDetail.request_stock_id" => $id)));
        $fromLocationGroups = ClassRegistry::init('LocationGroup')->find('first', array('conditions' => array('LocationGroup.id' => $this->data['RequestStock']['from_location_group_id'])));
        $toLocationGroups   = ClassRegistry::init('LocationGroup')->find('first', array("conditions" => array('LocationGroup.id' => $this->data['RequestStock']['to_location_group_id'])));
        if(!empty($this->data['RequestStock']['transfer_order_id'])){
            $order = ClassRegistry::init('TransferOrder')->read(null, $this->data['RequestStock']['transfer_order_id']);
            $resultDetails      = ClassRegistry::init('TransferReceiveResult')->find('all', array('conditions' => array('TransferReceiveResult.transfer_order_id' => $this->data['RequestStock']['transfer_order_id'])));
            $this->set(compact("order", "resultDetails"));
        }
        $this->set(compact('requestStockDetails', 'fromLocationGroups', 'toLocationGroups'));
    }

    function add() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        if (!empty($this->data)) {
            $r = 0;
            $restCode = array();
            $dateNow  = date("Y-m-d H:i:s");
            $result['error'] = 0;
            // Insert New Request Stock
            $this->RequestStock->create();
            $this->data['RequestStock']['sys_code']   = md5(rand().strtotime(date("Y-m-d H:i:s")).$user['User']['id']);
            $this->data['RequestStock']['created']    = $dateNow;
            $this->data['RequestStock']['created_by'] = $user['User']['id'];
            $this->data['RequestStock']['status']     = 1;
            if ($this->RequestStock->save($this->data)) {
                // Get Request Stock Id
                $requestStockId = $this->RequestStock->id;
                // Get Module Code
                $modCode = $this->Helper->getModuleCode($this->data['RequestStock']['code'], $requestStockId, 'code', 'request_stocks', 'status >= 0 AND branch_id = '.$this->data['RequestStock']['branch_id']);
                // Updaet Module Code
                $this->data['RequestStock']['code'] = $modCode;
                mysql_query("UPDATE request_stocks SET code = '".$modCode."' WHERE id = ".$requestStockId);
                // Convert to REST
                $restCode[$r] = $this->Helper->convertToDataSync($this->data['RequestStock'], 'request_stocks');
                $restCode[$r]['modified'] = $dateNow;
                $restCode[$r]['dbtodo']   = 'request_stocks';
                $restCode[$r]['actodo']   = 'is';
                $r++;
                // Load 
                $this->loadModel('RequestStockDetail');
                // Insert Request Stock Detail
                for ($i = 0; $i < sizeof($_POST['product_id']); $i++) {
                    $this->RequestStockDetail->create();
                    $requestStockDetail = array();
                    $requestStockDetail['RequestStockDetail']['request_stock_id'] = $requestStockId;
                    $requestStockDetail['RequestStockDetail']['product_id']   = $_POST['product_id'][$i];
                    $requestStockDetail['RequestStockDetail']['qty']          = $_POST['qty'][$i];
                    $requestStockDetail['RequestStockDetail']['qty_uom_id']   = $_POST['qty_uom_id'][$i];
                    $requestStockDetail['RequestStockDetail']['conversion']   = $_POST['conversion'][$i];
                    $this->RequestStockDetail->save($requestStockDetail);
                    // Convert to REST
                    $restCode[$r] = $this->Helper->convertToDataSync($requestStockDetail['RequestStockDetail'], 'request_stock_details');
                    $restCode[$r]['dbtodo']   = 'request_stock_details';
                    $restCode[$r]['actodo']   = 'is';
                    $r++;
                }
                // Save File Send
                $this->Helper->sendFileToSync($restCode, $this->data['RequestStock']['company_id'], $this->data['RequestStock']['branch_id'], 1);
                // Save User Activity
                $this->Helper->saveUserActivity($user['User']['id'], 'Request Stock', 'Save Add New', $requestStockId);
                $result['id'] = $requestStockId;
                echo json_encode($result);
                exit;
            } else {
                $this->Helper->saveUserActivity($user['User']['id'], 'Request Stock', 'Save Add New (Error)');
                $result['error'] = 1;
                echo json_encode($result);
                exit;
            }
        }
        $this->Helper->saveUserActivity($user['User']['id'], 'Request Stock', 'Add New');
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
                            'fields' => array('Branch.id', 'Branch.name', 'Branch.company_id', 'ModuleCodeBranch.request_code', 'Branch.currency_id', 'Currency.symbol'),
                            'conditions' => array('Branch.is_active = 1', 'user_branches.user_id=' . $user['User']['id'])
                        ));
        $fromLocationGroups = ClassRegistry::init('LocationGroup')->find('list', 
                                array('joins' => array(
                                    array('table' => 'locations', 'type' => 'inner', 'conditions' => array('locations.location_group_id=LocationGroup.id'))),
                                'conditions' => array('LocationGroup.is_active' => '1', 'LocationGroup.location_group_type_id != 1'),
                                'group' => 'LocationGroup.id'));
        $toLocationGroups   = ClassRegistry::init('LocationGroup')->find('list', array(
                                'joins' => array(
                                    array('table' => 'user_location_groups', 'type' => 'inner', 'conditions' => array('user_location_groups.location_group_id=LocationGroup.id')), 
                                    array('table' => 'locations', 'type' => 'inner', 'conditions' => array('locations.location_group_id=LocationGroup.id'))),
                                "conditions" => array('user_location_groups.user_id=' . $user['User']['id'], 'LocationGroup.is_active' => '1', 'LocationGroup.location_group_type_id != 1'),
                                'group' => 'LocationGroup.id'));
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
            $result['error'] = 0;
            $requestStock = $this->RequestStock->read(null, $this->data['id']);
            if($requestStock['RequestStock']['status'] != 1){
                $this->Helper->saveUserActivity($user['User']['id'], 'Request Stock', 'Save Edit (Error Status)', $this->data['id']);
                $result['error'] = 1;
                echo json_encode($result);
                exit;
            }
            $statuEdit = "-1";
            $dateNow   = date("Y-m-d H:i:s");
            $rb = 0;
            $restBackCode  = array();
            if($this->data['RequestStock']['company_id'] != $requestStock['RequestStock']['company_id']){
                $statuEdit = 0;
            }
            // Update Status Request Stock Edit
            $this->RequestStock->updateAll(
                    array('RequestStock.status' => $statuEdit, "modified_by"=>$user['User']['id']), array('RequestStock.id' => $this->data['id'])
            );
            // Convert to REST
            $restBackCode[$rb]['status']   = $statuEdit;
            $restBackCode[$rb]['modified'] = $dateNow;
            $restBackCode[$rb]['modified_by'] = $this->Helper->getSQLSyncCode("users", $user['User']['id']);
            $restBackCode[$rb]['dbtodo'] = 'request_stocks';
            $restBackCode[$rb]['actodo'] = 'ut';
            $restBackCode[$rb]['con']    = "sys_code = '".$requestStock['RequestStock']['sys_code']."'";
            $rb++;
            // Save File Send Delete
            $this->Helper->sendFileToSync($restBackCode, $requestStock['RequestStock']['company_id'], $requestStock['RequestStock']['branch_id'], 1);
            // Insert New Request Stock
            $r = 0;
            $restCode  = array();
            $requestCode = $this->data['RequestStock']['code'];
            $this->RequestStock->create();
            $this->data['RequestStock']['code']       = $requestStock['RequestStock']['code'];
            $this->data['RequestStock']['created_by'] = $user['User']['id'];
            $this->data['RequestStock']['status']     = 1;
            if ($this->RequestStock->save($this->data)) {
                // Get Request Stock Id
                $requestStockId = $this->RequestStock->id;
                if($this->data['RequestStock']['company_id'] != $requestStock['RequestStock']['company_id']){
                    // Get Module Code
                    $modCode = $this->Helper->getModuleCode($requestCode, $requestStockId, 'code', 'request_stocks', 'status >= 0 AND branch_id = '.$this->data['RequestStock']['branch_id']);
                    // Updaet Module Code
                    $this->data['RequestStock']['code'] = $modCode;
                    mysql_query("UPDATE request_stocks SET code = '".$modCode."' WHERE id = ".$requestStockId);
                }
                // Convert to REST
                $restCode[$r] = $this->Helper->convertToDataSync($this->data['RequestStock'], 'request_stocks');
                $restCode[$r]['modified'] = $dateNow;
                $restCode[$r]['dbtodo']   = 'request_stocks';
                $restCode[$r]['actodo']   = 'is';
                $r++;
                // Load 
                $this->loadModel('RequestStockDetail');
                // Insert Request Stock Detail
                for ($i = 0; $i < sizeof($_POST['product_id']); $i++) {
                    $this->RequestStockDetail->create();
                    $requestStockDetail = array();
                    $requestStockDetail['RequestStockDetail']['request_stock_id'] = $requestStockId;
                    $requestStockDetail['RequestStockDetail']['product_id']   = $_POST['product_id'][$i];
                    $requestStockDetail['RequestStockDetail']['qty']          = $_POST['qty'][$i];
                    $requestStockDetail['RequestStockDetail']['qty_uom_id']   = $_POST['qty_uom_id'][$i];
                    $requestStockDetail['RequestStockDetail']['conversion']   = $_POST['conversion'][$i];
                    $this->RequestStockDetail->save($requestStockDetail);
                    // Convert to REST
                    $restCode[$r] = $this->Helper->convertToDataSync($requestStockDetail['RequestStockDetail'], 'request_stock_details');
                    $restCode[$r]['dbtodo']   = 'request_stock_details';
                    $restCode[$r]['actodo']   = 'is';
                    $r++;
                }
                // Save File Send
                $this->Helper->sendFileToSync($restCode, $this->data['RequestStock']['company_id'], $this->data['RequestStock']['branch_id'], 1);
                // Save User Activity
                $this->Helper->saveUserActivity($user['User']['id'], 'Request Stock', 'Save Edit', $this->data['id'], $requestStockId);
                $result['id'] = $requestStockId;
                echo json_encode($result);
                exit;
            } else {
                $this->Helper->saveUserActivity($user['User']['id'], 'Request Stock', 'Save Edit (Error)', $this->data['id']);
                $result['error'] = 1;
                echo json_encode($result);
                exit;
            }
        }
        if (empty($this->data)) {
            $this->Helper->saveUserActivity($user['User']['id'], 'Request Stock', 'Edit', $id);
            $this->data = $this->RequestStock->read(null, $id);
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
                                'fields' => array('Branch.id', 'Branch.name', 'Branch.company_id', 'ModuleCodeBranch.request_code', 'Branch.currency_id', 'Currency.symbol'),
                                'conditions' => array('Branch.is_active = 1', 'user_branches.user_id=' . $user['User']['id'])
                            ));
            $fromLocationGroups = ClassRegistry::init('LocationGroup')->find('list', 
                                array('joins' => array(
                                    array('table' => 'locations', 'type' => 'inner', 'conditions' => array('locations.location_group_id=LocationGroup.id'))),
                                'conditions' => array('LocationGroup.is_active' => '1', 'LocationGroup.location_group_type_id != 1'),
                                'group' => 'LocationGroup.id'));
            $toLocationGroups   = ClassRegistry::init('LocationGroup')->find('list', array(
                                    'joins' => array(
                                        array('table' => 'user_location_groups', 'type' => 'inner', 'conditions' => array('user_location_groups.location_group_id=LocationGroup.id')), 
                                        array('table' => 'locations', 'type' => 'inner', 'conditions' => array('locations.location_group_id=LocationGroup.id'))),
                                    "conditions" => array('user_location_groups.user_id=' . $user['User']['id'], 'LocationGroup.is_active' => '1', 'LocationGroup.location_group_type_id != 1'),
                                    'group' => 'LocationGroup.id'));
            $requestStockDetails = ClassRegistry::init('RequestStockDetail')->find('all', array("conditions" => array("RequestStockDetail.request_stock_id" => $id)));
            $this->set(compact("fromLocationGroups", "toLocationGroups", "requestStockDetails", "companies", "branches"));
        }
    }

    function delete($id = null) {
        if (!$id) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $r = 0;
        $restCode = array();
        $dateNow  = date("Y-m-d H:i:s");
        $user = $this->getCurrentUser();
        $this->data = $this->RequestStock->read(null, $id);
        mysql_query("UPDATE `request_stocks` SET `status` = 0, `modified`='".$dateNow."', `modified_by`=".$user['User']['id']." WHERE `id`=".$id.";");
        // Convert to REST
        $restCode[$r]['status']      = 0;
        $restCode[$r]['modified']    = $dateNow;
        $restCode[$r]['modified_by'] = $this->Helper->getSQLSyncCode("users", $user['User']['id']);
        $restCode[$r]['dbtodo'] = 'request_stocks';
        $restCode[$r]['actodo'] = 'ut';
        $restCode[$r]['con']    = "sys_code = '".$this->data['RequestStock']['sys_code']."'";
        // Save File Send
        $this->Helper->sendFileToSync($restCode, $this->data['RequestStock']['company_id'], $this->data['RequestStock']['branch_id'], 1);
        // Save User Activity
        $this->Helper->saveUserActivity($user['User']['id'], 'Request Stock', 'Delete', $id);
        echo MESSAGE_DATA_HAS_BEEN_DELETED;
        exit;
    }

    function searchProductCode($companyId = null, $branchId = null, $code = null) {
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
                        'fields' => array('Product.id', 'Product.price_uom_id', 'Product.barcode', 'Product.code', 'Product.name'),
                        'joins' => $joins,
                        'group' => array(
                            'Product.id'
                        )
                    ));
        if(!empty($product)){
            echo json_encode($product);
        }else{
            echo '1';
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
                                'Product.code LIKE' => '%' .trim($this->params['url']['q']) . '%',
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
    
    function product($companyId = null, $branchId = null){
        $this->layout = 'ajax';
        $this->set(compact('companyId', 'branchId'));
    }
    
    function productAjax($companyId = null, $branchId = null, $category = null){
        $this->layout = 'ajax';
        $this->set(compact('category', 'companyId', 'branchId'));
    }
    
    function printInvoice($receiptId = null) {
        if (!empty($receiptId)) {
            $this->layout = 'ajax';
            $this->data = $this->RequestStock->read(null, $receiptId);
            $fromLocationGroups = ClassRegistry::init('LocationGroup')->find('first', array('conditions' => array('LocationGroup.id' => $this->data['RequestStock']['from_location_group_id'], 'LocationGroup.is_active' => '1')));
            $toLocationGroups   = ClassRegistry::init('LocationGroup')->find('first', array("conditions" => array('LocationGroup.id' => $this->data['RequestStock']['to_location_group_id'], 'LocationGroup.is_active' => '1')));
            $requestStockDetails = ClassRegistry::init('RequestStockDetail')->find('all', array("conditions" => array("RequestStockDetail.request_stock_id" => $receiptId)));
            $this->set(compact("fromLocationGroups", "toLocationGroups", "requestStockDetails"));
        } else {
            exit;
        }
    }
    
    function viewRequestIssued(){
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        // Check Module Exist
        $sqlDash = mysql_query("SELECT id FROM user_dashboards WHERE module_id = 487 AND user_id = {$user['User']['id']} LIMIT 1");
        if(!mysql_num_rows($sqlDash)){
            $this->loadModel('UserDashboard');
            $userDash = array();
            $userDash['UserDashboard']['user_id']      = $user['User']['id'];
            $userDash['UserDashboard']['module_id']    = 487;
            $userDash['UserDashboard']['display']      = 1;
            $userDash['UserDashboard']['auto_refresh'] = 1;
            $userDash['UserDashboard']['time_refresh'] = 5;
            $this->UserDashboard->save($userDash);
        }
    }

}

?>