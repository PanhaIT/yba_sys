<?php

class InvAdjsController extends AppController {

    var $uses = 'CycleProduct';
    var $components = array('Helper', 'Inventory');

    function index() {
        $this->layout = 'ajax';
        // Run Config Path
        $this->Helper->checkConfigPath('Test Path');
        $user = $this->getCurrentUser();
        $locationGroups = ClassRegistry::init('LocationGroup')->find('list', array('joins' => array(array('table' => 'user_location_groups', 'type' => 'inner', 'conditions' => array('user_location_groups.location_group_id=LocationGroup.id'))),'conditions' => array('user_location_groups.user_id=' . $user['User']['id'], 'LocationGroup.is_active' => '1', 'LocationGroup.location_group_type_id != 1')));
        $this->set(compact('locationGroups'));
        $this->Helper->saveUserActivity($user['User']['id'], 'Inventory Adjustment', 'Dashboard');
    }

    function ajax($warehouse = "all", $status = "all", $date = "") {
        $this->layout = 'ajax';
        $this->set(compact('warehouse', 'status', 'date'));
    }

    function add() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        $locationGroups = ClassRegistry::init('LocationGroup')->find('list', array('joins' => array(array('table' => 'user_location_groups', 'type' => 'inner', 'conditions' => array('user_location_groups.location_group_id=LocationGroup.id')), array('table' => 'locations', 'type' => 'inner', 'conditions' => array('locations.location_group_id=LocationGroup.id'))), 'conditions' => array('user_location_groups.user_id=' . $user['User']['id'], 'LocationGroup.is_active' => '1', 'LocationGroup.location_group_type_id != 1'), 'group' => 'LocationGroup.id'));
        $companies = ClassRegistry::init('Company')->find('all', array('joins' => array(array('table' => 'user_companies', 'type' => 'inner', 'conditions' => array('user_companies.company_id=Company.id'))), 'fields' => array('Company.id', 'Company.name', 'Company.vat_calculate'), 'conditions' => array('Company.is_active = 1', 'user_companies.user_id=' . $user['User']['id'])));
        $branches  = ClassRegistry::init('Branch')->find('all', array('joins' => array(array('table' => 'user_branches', 'type' => 'inner', 'conditions' => array('user_branches.branch_id=Branch.id')), array('table' => 'module_code_branches AS ModuleCodeBranch', 'type' => 'left', 'conditions' => array('ModuleCodeBranch.branch_id=Branch.id'))), 'fields' => array('Branch.id', 'Branch.name', 'Branch.company_id', 'ModuleCodeBranch.adj_code', 'Branch.currency_id', 'Currency.symbol'), 'conditions' => array('Branch.is_active = 1', 'user_branches.user_id=' . $user['User']['id'])));
        $this->Helper->saveUserActivity($user['User']['id'], 'Inventory Adjustment', 'Add New');
        $this->set(compact('locationGroups', 'companies', 'branches'));
    }
    
    function addDetail() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        $branches = ClassRegistry::init('Branch')->find('all', array('joins' => array(array('table' => 'user_branches', 'type' => 'inner', 'conditions' => array('user_branches.branch_id=Branch.id')), array('table' => 'module_code_branches AS ModuleCodeBranch', 'type' => 'left', 'conditions' => array('ModuleCodeBranch.branch_id=Branch.id'))), 'fields' => array('Branch.id', 'Branch.name', 'Branch.company_id', 'ModuleCodeBranch.adj_code', 'Branch.currency_id', 'Currency.symbol'), 'conditions' => array('Branch.is_active = 1', 'user_branches.user_id=' . $user['User']['id'])));
        $this->set(compact('branches'));
    }
    
    function edit($id) {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        $this->Helper->saveUserActivity($user['User']['id'], 'Inventory Adjustment', 'Edit', $id);
        $this->data = $this->CycleProduct->read(null, $id);
        $locationGroups = ClassRegistry::init('LocationGroup')->find('list', array('joins' => array(array('table' => 'user_location_groups', 'type' => 'inner', 'conditions' => array('user_location_groups.location_group_id=LocationGroup.id')), array('table' => 'locations', 'type' => 'inner', 'conditions' => array('locations.location_group_id=LocationGroup.id'))), 'conditions' => array('user_location_groups.user_id=' . $user['User']['id'], 'LocationGroup.is_active' => '1', 'LocationGroup.location_group_type_id != 1'), 'group' => 'LocationGroup.id'));
        $companies = ClassRegistry::init('Company')->find('all', array('joins' => array(array('table' => 'user_companies', 'type' => 'inner', 'conditions' => array('user_companies.company_id=Company.id'))), 'fields' => array('Company.id', 'Company.name', 'Company.vat_calculate'), 'conditions' => array('Company.is_active = 1', 'user_companies.user_id=' . $user['User']['id'])));
        $branches  = ClassRegistry::init('Branch')->find('all', array('joins' => array(array('table' => 'user_branches', 'type' => 'inner', 'conditions' => array('user_branches.branch_id=Branch.id')), array('table' => 'module_code_branches AS ModuleCodeBranch', 'type' => 'left', 'conditions' => array('ModuleCodeBranch.branch_id=Branch.id'))), 'fields' => array('Branch.id', 'Branch.name', 'Branch.company_id', 'ModuleCodeBranch.adj_code', 'Branch.currency_id', 'Currency.symbol'), 'conditions' => array('Branch.is_active = 1', 'user_branches.user_id=' . $user['User']['id'])));
        $adjAccountId = $this->data['CycleProduct']['deposit_to'];
        $this->set(compact('id', 'locationGroups', 'companies', 'branches', 'adjAccountId'));
    }

    function editDetail($cycleProductId = null, $locationGroup) {
        $this->layout = 'ajax';
        $cycleProduct = $this->CycleProduct->read(null, $cycleProductId);
        $user = $this->getCurrentUser();
        $branches  = ClassRegistry::init('Branch')->find('all', array('joins' => array(array('table' => 'user_branches', 'type' => 'inner', 'conditions' => array('user_branches.branch_id=Branch.id')), array('table' => 'module_code_branches AS ModuleCodeBranch', 'type' => 'left', 'conditions' => array('ModuleCodeBranch.branch_id=Branch.id'))), 'fields' => array('Branch.id', 'Branch.name', 'Branch.company_id', 'ModuleCodeBranch.adj_code', 'Branch.currency_id', 'Currency.symbol'), 'conditions' => array('Branch.is_active = 1', 'user_branches.user_id=' . $user['User']['id'])));
        $this->set(compact('branches', 'cycleProductId', 'cycleProduct', 'locationGroup'));
    }

    function uom() {
        $this->layout = 'ajax';
    }

    function save() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        if ($this->Helper->checkDouplicate('reference', 'cycle_products', $this->data['InvAdj']['reference'], 'status > 0')) {
            $this->Helper->saveUserActivity($user['User']['id'], 'Inventory Adjustment', 'Save Add New (Reference ready existed)');
            echo 'duplicate';
            exit();
        }
        if (preg_match('/^[0-9]{2}\/[0-9]{2}\/[0-9]{4}$/', $this->data['InvAdj']['date'])) {
            $this->data['InvAdj']['date'] = $this->Helper->dateConvert($this->data['InvAdj']['date']);
        }
        if (!empty($_POST['new_qty']) && sizeof($_POST['new_qty']) > 0) {
            $dateNow  = date("Y-m-d H:i:s");
            $adjAccountId = $this->data['InvAdj']['chart_account_id'];
            // Insert Into Cycle Products
            $this->CycleProduct->create();
            $this->data['CycleProduct']['sys_code']   = md5(rand().strtotime(date("Y-m-d H:i:s")).$user['User']['id']);
            $this->data['CycleProduct']['created']    = $dateNow;
            $this->data['CycleProduct']['date']               = $this->data['InvAdj']['date'];
            $this->data['CycleProduct']['location_group_id']  = $this->data['InvAdj']['location_group_id'];
            $this->data['CycleProduct']['company_id']         = $this->data['InvAdj']['company_id'];
            $this->data['CycleProduct']['branch_id']          = $this->data['InvAdj']['branch_id'];
            $this->data['CycleProduct']['deposit_to']         = $adjAccountId;
            $this->data['CycleProduct']['note']               = $this->data['InvAdj']['note'];
            $this->data['CycleProduct']['created_by']         = $user['User']['id'];
            $this->CycleProduct->save($this->data);
            $cycleProductId = $this->CycleProduct->id;
            // Get Module Code
            $modCode = $this->Helper->getModuleCode($this->data['InvAdj']['reference'], $cycleProductId, 'reference', 'cycle_products', 'status >= 0 AND branch_id = '.$this->data['InvAdj']['branch_id']);
            // Updaet Module Code
            $this->data['CycleProduct']['reference'] = $modCode;
            // Insert Into Cycle Product Details
            $this->loadModel('CycleProductDetail');
            $this->loadModel('StockOrder');
            $grandTotal = 0;
            for ($i = 0; $i < sizeof($_POST['new_qty']); $i++) {
                if ($this->Helper->preventInput($_POST['qty_difference'][$i]) != '') {
                    $sqlPro = mysql_query("SELECT * FROM products WHERE id = ".$_POST['product_id'][$i]);
                    $rowPro = mysql_fetch_array($sqlPro);
                    if($rowPro['unit_cost'] > 0){
                        $unitCost = $rowPro['unit_cost'] / $rowPro['small_val_uom'];
                    } else {
                        $unitCost = 0;
                    }
                    $totalCost   = $unitCost * $this->Helper->preventInput($_POST['qty_difference'][$i]);
                    $grandTotal += $totalCost;
//                    if($totalCost < 0){
//                        $totalCost = $totalCost * -1;
//                    }
                    $lotNum = $this->Helper->preventInput($_POST['lots_number'][$i])!=''?$this->Helper->preventInput($_POST['lots_number'][$i]):0;
                    $expDate = $this->Helper->preventInput($_POST['expired_date'][$i])!=''?$this->Helper->preventInput($_POST['expired_date'][$i]):'0000-00-00';
                    $this->CycleProductDetail->create();
                    $cycleDetail = array();
                    $cycleDetail['CycleProductDetail']['cycle_product_id'] = $cycleProductId;
                    $cycleDetail['CycleProductDetail']['product_id']   = $this->Helper->preventInput($_POST['product_id'][$i]);
                    $cycleDetail['CycleProductDetail']['location_id']  = $this->Helper->preventInput($_POST['location_id'][$i]);
                    $cycleDetail['CycleProductDetail']['lots_number']  = $lotNum;
                    $cycleDetail['CycleProductDetail']['expired_date'] = $expDate;
                    $cycleDetail['CycleProductDetail']['current_qty']  = $this->Helper->preventInput($_POST['total_qty'][$i]);
                    $cycleDetail['CycleProductDetail']['new_qty']      = $this->Helper->preventInput($_POST['new_qty'][$i]);
                    $cycleDetail['CycleProductDetail']['qty_difference'] = $this->Helper->preventInput($_POST['qty_difference'][$i]);
                    $cycleDetail['CycleProductDetail']['unit_cost']    = $unitCost;
                    $cycleDetail['CycleProductDetail']['total_cost']   = $totalCost;
                    $this->CycleProductDetail->save($cycleDetail);
                    if($this->Helper->preventInput($_POST['qty_difference'][$i]) < 0){
                        $this->StockOrder->create();
                        $tmpInvAdj = array();
                        $tmpInvAdj['StockOrder']['sys_code'] = md5(rand().strtotime(date("Y-m-d H:i:s")).$user['User']['id']);
                        $tmpInvAdj['StockOrder']['cycle_product_id'] = $cycleProductId;
                        $tmpInvAdj['StockOrder']['product_id']    = $this->Helper->preventInput($_POST['product_id'][$i]);
                        $tmpInvAdj['StockOrder']['location_group_id']   = $this->data['InvAdj']['location_group_id'];
                        $tmpInvAdj['StockOrder']['location_id']   = $this->Helper->preventInput($_POST['location_id'][$i]);
                        $tmpInvAdj['StockOrder']['lots_number']   = $lotNum;
                        $tmpInvAdj['StockOrder']['expired_date']  = $expDate;
                        $tmpInvAdj['StockOrder']['date'] = $this->data['InvAdj']['date'];
                        $tmpInvAdj['StockOrder']['qty']  = $this->Helper->preventInput($_POST['qty_difference'][$i]) * -1;
                        $this->StockOrder->save($tmpInvAdj);
                        $this->Inventory->saveGroupQtyOrder($this->data['InvAdj']['location_group_id'], $this->Helper->preventInput($_POST['location_id'][$i]), $_POST['product_id'][$i], $lotNum, $expDate, $this->Helper->preventInput($_POST['qty_difference'][$i] * -1), $this->data['InvAdj']['date'], '+');
                    }
                }
            }
            mysql_query("UPDATE cycle_products SET reference = '".$modCode."', total_cost = ".$grandTotal." WHERE id = ".$cycleProductId);
            // Save User Activity
            $this->Helper->saveUserActivity($user['User']['id'], 'Inventory Adjustment', 'Save Add New', $cycleProductId);
            echo $cycleProductId;
        } else {
            $this->Helper->saveUserActivity($user['User']['id'], 'Inventory Adjustment', 'Save Add New (Error)');
            echo 'error';
        }
        exit;
    }

    function saveEdit() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        $invAdj = $this->CycleProduct->read(null, $this->data['InvAdj']['id']);
        if ($invAdj['CycleProduct']['status'] != 1) {
            $this->Helper->saveUserActivity($user['User']['id'], 'Inventory Adjustment', 'Save Edit (Error Status)', $this->data['InvAdj']['id']);
            echo 'error';
            exit;
        }
        if (!empty($_POST['new_qty']) && sizeof($_POST['new_qty']) > 0) {
            $this->loadModel('StockOrder');
            $statuEdit = "-1";
            $dateNow   = date("Y-m-d H:i:s");
            $adjAccountId = $this->data['InvAdj']['chart_account_id'];
            // Reset Stock Order
            $sqlResetOrder = mysql_query("SELECT * FROM stock_orders WHERE `cycle_product_id`=".$this->data['InvAdj']['id'].";");
            while($rowResetOrder = mysql_fetch_array($sqlResetOrder)){
                $this->Inventory->saveGroupQtyOrder($rowResetOrder['location_group_id'], $rowResetOrder['location_id'], $rowResetOrder['product_id'], $rowResetOrder['lots_number'], $rowResetOrder['expired_date'], $rowResetOrder['qty'], $rowResetOrder['date'], '-');
            }
            // Detele Tmp Stock Order
            mysql_query("DELETE FROM `stock_orders` WHERE  `cycle_product_id`=".$this->data['InvAdj']['id'].";");
            if($this->data['InvAdj']['company_id'] != $invAdj['CycleProduct']['company_id']){
                $statuEdit = 0;
            }
            // Update Status Request Stock Edit
            $this->CycleProduct->updateAll(
                    array('CycleProduct.status' => $statuEdit, "modified_by"=>$user['User']['id']), array('CycleProduct.id' => $this->data['InvAdj']['id'])
            );
            $this->CycleProduct->create();
            $this->data['CycleProduct']['sys_code']   = md5(rand().strtotime(date("Y-m-d H:i:s")).$user['User']['id']);
            $this->data['CycleProduct']['created']    = $dateNow;
            $this->data['CycleProduct']['reference']  = $invAdj['CycleProduct']['reference'];
            $this->data['CycleProduct']['date']       = $this->data['InvAdj']['date'];
            $this->data['CycleProduct']['location_group_id'] = $this->data['InvAdj']['location_group_id'];
            $this->data['CycleProduct']['company_id'] = $this->data['InvAdj']['company_id'];
            $this->data['CycleProduct']['branch_id']  = $this->data['InvAdj']['branch_id'];
            $this->data['CycleProduct']['deposit_to'] = $adjAccountId;
            $this->data['CycleProduct']['note']       = $this->data['InvAdj']['note'];
            $this->data['CycleProduct']['created_by'] = $user['User']['id'];
            $this->data['CycleProduct']['status']     = 1;
            $this->CycleProduct->save($this->data);
            $cycleProductId = $this->CycleProduct->id;
            if($this->data['InvAdj']['branch_id'] != $invAdj['CycleProduct']['branch_id']){
                // Get Module Code
                $modCode = $this->Helper->getModuleCode($this->data['InvAdj']['reference'], $cycleProductId, 'reference', 'cycle_products', 'status >= 0 AND branch_id = '.$this->data['InvAdj']['branch_id']);
                // Updaet Module Code
                $this->data['CycleProduct']['reference'] = $modCode;
                mysql_query("UPDATE cycle_products SET reference = '".$modCode."' WHERE id = ".$cycleProductId);
            }
            // Insert Into Cycle Product Details
            $this->loadModel('CycleProductDetail');
            $this->loadModel('StockOrder');
            $grandTotal = 0;
            for ($i = 0; $i < sizeof($_POST['new_qty']); $i++) {
                if ($this->Helper->preventInput($_POST['qty_difference'][$i]) != '') {
                    $sqlPro = mysql_query("SELECT * FROM products WHERE id = ".$_POST['product_id'][$i]);
                    $rowPro = mysql_fetch_array($sqlPro);
                    if($rowPro['unit_cost'] > 0){
                        $unitCost = $rowPro['unit_cost'] / $rowPro['small_val_uom'];
                    } else {
                        $unitCost = 0;
                    }
                    $totalCost  = $unitCost * $this->Helper->preventInput($_POST['qty_difference'][$i]);
                    $grandTotal += $totalCost;
//                    if($totalCost < 0){
//                        $totalCost = $totalCost * -1;
//                    }
                    $lotNum  = $this->Helper->preventInput($_POST['lots_number'][$i])!=''?$this->Helper->preventInput($_POST['lots_number'][$i]):0;
                    $expDate = $this->Helper->preventInput($_POST['expired_date'][$i])!=''?$this->Helper->preventInput($_POST['expired_date'][$i]):'0000-00-00';
                    $this->CycleProductDetail->create();
                    $cycleDetail = array();
                    $cycleDetail['CycleProductDetail']['cycle_product_id'] = $cycleProductId;
                    $cycleDetail['CycleProductDetail']['product_id']   = $this->Helper->preventInput($_POST['product_id'][$i]);
                    $cycleDetail['CycleProductDetail']['location_id']  = $this->Helper->preventInput($_POST['location_id'][$i]);
                    $cycleDetail['CycleProductDetail']['lots_number']  = $lotNum;
                    $cycleDetail['CycleProductDetail']['expired_date'] = $expDate;
                    $cycleDetail['CycleProductDetail']['current_qty']  = $this->Helper->preventInput($_POST['total_qty'][$i]);
                    $cycleDetail['CycleProductDetail']['new_qty']      = $this->Helper->preventInput($_POST['new_qty'][$i]);
                    $cycleDetail['CycleProductDetail']['qty_difference'] = $this->Helper->preventInput($_POST['qty_difference'][$i]);
                    $cycleDetail['CycleProductDetail']['unit_cost']    = $unitCost;
                    $cycleDetail['CycleProductDetail']['total_cost']   = $totalCost;
                    $this->CycleProductDetail->save($cycleDetail);
                    if($this->Helper->preventInput($_POST['qty_difference'][$i]) < 0){
                        $this->StockOrder->create();
                        $tmpInvAdj = array();
                        $tmpInvAdj['StockOrder']['cycle_product_id'] = $cycleProductId;
                        $tmpInvAdj['StockOrder']['product_id']    = $this->Helper->preventInput($_POST['product_id'][$i]);
                        $tmpInvAdj['StockOrder']['location_group_id']   = $this->data['InvAdj']['location_group_id'];
                        $tmpInvAdj['StockOrder']['location_id']   = $this->Helper->preventInput($_POST['location_id'][$i]);
                        $tmpInvAdj['StockOrder']['lots_number']   = $lotNum;
                        $tmpInvAdj['StockOrder']['expired_date']  = $expDate;
                        $tmpInvAdj['StockOrder']['date'] = $this->data['InvAdj']['date'];
                        $tmpInvAdj['StockOrder']['qty']  = $this->Helper->preventInput($_POST['qty_difference'][$i]) * -1;
                        $this->StockOrder->save($tmpInvAdj);
                        $this->Inventory->saveGroupQtyOrder($this->data['InvAdj']['location_group_id'], $this->Helper->preventInput($_POST['location_id'][$i]), $_POST['product_id'][$i], $lotNum, $expDate, $this->Helper->preventInput($_POST['qty_difference'][$i] * -1), $this->data['InvAdj']['date'], '+');
                    }
                }
            }
            mysql_query("UPDATE cycle_products SET total_cost = ".$grandTotal." WHERE id = ".$cycleProductId);
            // Save User Activity
            $this->Helper->saveUserActivity($user['User']['id'], 'Inventory Adjustment', 'Save Edit', $this->data['InvAdj']['id'], $cycleProductId);
            echo $cycleProductId;
        } else {
            $this->Helper->saveUserActivity($user['User']['id'], 'Inventory Adjustment', 'Save Edit (Error)', $this->data['InvAdj']['id']);
            echo 'error';
        }
        exit;
    }
    
    function approve() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        $result = array();
        $sqlCheckConfig = mysql_query("SELECT id FROM test");
        if (!mysql_num_rows($sqlCheckConfig)) {
            $result['error'] = 1;
            echo json_encode($result);
            exit();
        }
        $queryAdj = mysql_query("SELECT cycle_product_id FROM crontab_inv_adjs WHERE cycle_product_id=" . $this->Helper->preventInput($_POST['cycle_product_id']));
        if(!mysql_num_rows($queryAdj)){
            mysql_query("INSERT INTO crontab_inv_adjs (cycle_product_id,status,created,created_by) VALUES ('" . $this->Helper->preventInput($_POST['cycle_product_id']) . "',1,now(),'" . $user['User']['id'] . "')");
        }
        shell_exec("wget -b -q -O public/logs/approveInvAdj?id=".$this->Helper->preventInput($_POST['cycle_product_id'])." '".LINK_URL."approveInvAdj/" . $this->Helper->preventInput($_POST['cycle_product_id']) . "?user=".$user['User']['id']."' ".LINK_URL_SSL);
        $this->Helper->saveUserActivity($user['User']['id'], 'Inventory Adjustment', 'Click Approve', $_POST['cycle_product_id']);
        $result['error'] = 0;
        $result['id'] = $_POST['cycle_product_id'];
        echo json_encode($result);
        exit();
    }

    function printInvoice($id = null) {
        $this->layout = 'ajax';
        $this->set('id', $this->Helper->preventInput($id));
    }
    
    function delete($id = null) {
        if (!$id) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $dateNow  = date("Y-m-d H:i:s");
        $user = $this->getCurrentUser();
        $this->data = $this->CycleProduct->read(null, $id);
        mysql_query("UPDATE `cycle_products` SET `status`=0, `modified`='".$dateNow."', `modified_by`=".$user['User']['id']." WHERE `id`=".$id.";");
        // Reset Stock Order
        $sqlResetOrder = mysql_query("SELECT * FROM stock_orders WHERE `cycle_product_id`=".$id.";");
        while($rowResetOrder = mysql_fetch_array($sqlResetOrder)){
            $this->Inventory->saveGroupQtyOrder($rowResetOrder['location_group_id'], $rowResetOrder['location_id'], $rowResetOrder['product_id'], $rowResetOrder['lots_number'], $rowResetOrder['expired_date'], $rowResetOrder['qty'], $rowResetOrder['date'], '-');
        }
        // Detele Tmp Stock Order
        mysql_query("DELETE FROM `stock_orders` WHERE `cycle_product_id`=".$id.";");
        // Save User Activity
        $this->Helper->saveUserActivity($user['User']['id'], 'Inventory Adjustment', 'Delete', $id);
        echo MESSAGE_DATA_HAS_BEEN_DELETED;
        exit;
    }
    
    function searchProductSku($companyId, $branchId, $code){
        if (!$code) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $user = $this->getCurrentUser();
        $locationGroup = $this->Helper->preventInput($_POST['location_group_id']);
        $product = ClassRegistry::init('Product')->find('first', array(
            'fields' => array(
                'Product.id',
                'Product.name',
                'Product.barcode',
                'Product.code',
                'Product.small_val_uom',
                'Product.price_uom_id',
                'Product.is_expired_date',
                'Product.is_lots',
                'Uom.name',
                'Uom.abbr'
            ),
            'conditions' => array(
                'Product.company_id' => $companyId,
                "OR" => array(
                        'trim(Product.code) = ("' . mysql_real_escape_string(trim($code)) . '")',
                        'trim(Product.barcode) = ("' . mysql_real_escape_string(trim($code)) . '")',
                        'trim(Product.name) = ("' . mysql_real_escape_string(trim($code)) . '")',
                        'trim(ProductWithSku.sku) = ("' . mysql_real_escape_string(trim($code)) . '")'
                    ),
                'Product.is_active' => 1,
                'Product.is_packet' => 0
            ),
            'joins' => array(
                            array(
                                'table' => 'uoms',
                                'type' => 'INNER',
                                'alias' => 'Uom',
                                'conditions' => array(
                                    'Uom.id = Product.price_uom_id'
                                )
                            ),
                            array(
                                'table' => 'product_branches',
                                'type' => 'INNER',
                                'alias' => 'ProductBranch',
                                'conditions' => array(
                                    'ProductBranch.product_id = Product.id',
                                    'ProductBranch.branch_id = '.$branchId
                            )),
                            array(
                                'table' => 'product_pgroups',
                                'type' => 'INNER',
                                'alias' => 'ProductPgroup',
                                'conditions' => array('ProductPgroup.product_id = Product.id')
                            ),
                            array(
                                'table' => 'pgroups',
                                'type' => 'INNER',
                                'alias' => 'Pgroup',
                                'conditions' => array(
                                    'Pgroup.id = ProductPgroup.pgroup_id',
                                    '(Pgroup.user_apply = 0 OR (Pgroup.user_apply = 1 AND Pgroup.id IN (SELECT pgroup_id FROM user_pgroups WHERE user_id = '.$user['User']['id'].')))'
                                )
                            ),
                            array(
                                'table' => 'product_with_skus',
                                'type' => 'LEFT',
                                'alias' => 'ProductWithSku',
                                'conditions' => array(
                                    'ProductWithSku.product_id = Product.id'
                                )
                            )
                      ),
            'group' => array(
                'Product.id',
                'Product.code',
                'Product.name',
                'Product.price_uom_id',
            )
        ));
        if(!empty($product)){
            $sqlLoc = mysql_query("SELECT id, name FROM locations WHERE is_active = 1 AND location_group_id = {$locationGroup}");
            while($rowLoc = mysql_fetch_array($sqlLoc)){
                $product['Location'][] = $rowLoc['id']."--".$rowLoc['name'];
            }
            $smallUomLabel = "";
            $qry = mysql_query("SELECT (SELECT abbr FROM uoms WHERE id = uom_conversions.to_uom_id) as abbr FROM uom_conversions WHERE from_uom_id = " . $product['Product']['price_uom_id'] . " AND is_small_uom = 1 AND is_active = 1");
            while (@$d = mysql_fetch_array($qry)) {
                $smallUomLabel = $d['abbr'];
            }
            $product['Product']['code']    = $product['Product']['code'];
            $product['Product']['barcode'] = $product['Product']['barcode'];
            $product['Product']['name']    = $product['Product']['name'];
            $product['Product']['small_uom_label'] = $smallUomLabel;
        }
        echo json_encode($product);
        exit;
    }
    
    function searchProductPuc($companyId, $branchId, $code){
        if (!$code) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $user = $this->getCurrentUser();
        $locationGroup = $this->Helper->preventInput($_POST['location_group_id']);
        $product = ClassRegistry::init('Product')->find('first', array(
            'fields' => array(
                'Product.id',
                'Product.name',
                'Product.barcode',
                'Product.code',
                'Product.small_val_uom',
                'Product.price_uom_id',
                'Product.is_expired_date',
                'Product.is_lots',
                'Uom.name',
                'Uom.abbr'
            ),
            'conditions' => array(
                'Product.company_id' => $companyId,
                "OR" => array(
                        'trim(Product.code) = ("' . mysql_real_escape_string(trim($code)) . '")',
                        'trim(Product.barcode) = ("' . mysql_real_escape_string(trim($code)) . '")',
                        'trim(Product.name) = ("' . mysql_real_escape_string(trim($code)) . '")',
                        'trim(ProductWithSku.sku) = ("' . mysql_real_escape_string(trim($code)) . '")'
                    ),
                'Product.is_active' => 1,
                'Product.is_packet' => 0
            ),
            'joins' => array(
                            array(
                                'table' => 'uoms',
                                'type' => 'INNER',
                                'alias' => 'Uom',
                                'conditions' => array(
                                    'Uom.id = Product.price_uom_id'
                                )
                            ),
                            array(
                                'table' => 'product_pgroups',
                                'type' => 'INNER',
                                'alias' => 'ProductPgroup',
                                'conditions' => array('ProductPgroup.product_id = Product.id')
                            ),
                            array(
                                'table' => 'product_branches',
                                'type' => 'INNER',
                                'alias' => 'ProductBranch',
                                'conditions' => array(
                                    'ProductBranch.product_id = Product.id',
                                    'ProductBranch.branch_id = '.$branchId
                            )),
                            array(
                                'table' => 'pgroups',
                                'type' => 'INNER',
                                'alias' => 'Pgroup',
                                'conditions' => array(
                                    'Pgroup.id = ProductPgroup.pgroup_id',
                                    '(Pgroup.user_apply = 0 OR (Pgroup.user_apply = 1 AND Pgroup.id IN (SELECT pgroup_id FROM user_pgroups WHERE user_id = '.$user['User']['id'].')))'
                                )
                            ),
                            array(
                                'table' => 'product_with_skus',
                                'type' => 'LEFT',
                                'alias' => 'ProductWithSku',
                                'conditions' => array(
                                    'ProductWithSku.product_id = Product.id'
                                )
                            )
                      ),
            'group' => array(
                'Product.id',
                'Product.code',
                'Product.name',
                'Product.price_uom_id',
            )
        ));
        if(!empty($product)){
            $sqlLoc = mysql_query("SELECT id, name FROM locations WHERE is_active = 1 AND location_group_id = {$locationGroup}");
            while($rowLoc = mysql_fetch_array($sqlLoc)){
                $product['Location'][] = $rowLoc['id']."--".$rowLoc['name'];
            }
            $smallUomLabel = "";
            $qry = mysql_query("SELECT (SELECT abbr FROM uoms WHERE id = uom_conversions.to_uom_id) as abbr FROM uom_conversions WHERE from_uom_id = " . $product['Product']['price_uom_id'] . " AND is_small_uom = 1 AND is_active = 1");
            while (@$d = mysql_fetch_array($qry)) {
                $smallUomLabel = $d['abbr'];
            }
            $product['Product']['code']    = $product['Product']['code'];
            $product['Product']['barcode'] = $product['Product']['barcode'];
            $product['Product']['name']    = $product['Product']['name'];
            $product['Product']['small_uom_label'] = $smallUomLabel;
        }
        echo json_encode($product);
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
    
    function product($companyId = null, $branchId = null, $locationGroup, $category = null){
        $this->layout = 'ajax';
        $this->set(compact("companyId", "branchId", "locationGroup", "category"));
    }
    
    function productAjax($companyId = null, $branchId = null, $locationGroup, $category = null){
        $this->layout = 'ajax';
        $this->set(compact("companyId", "branchId", "locationGroup", "category"));
    }
    
    function getTotalQtyOnHand($location, $adjDate, $lotsNumber, $expired = null){
        $this->layout = 'ajax';
        if(!empty($location) && !empty($adjDate) && !empty($expired) && !empty($_POST['product_id'])){
            $invAdjId = $_POST['inv_adj_id'];
            $dateNow  = date("Y-m-d");
            $productId = $this->Helper->preventInput($_POST['product_id']);
            if(empty($expired) || $expired == 'none'){
                $expired = '0000-00-00';
            }
            if($adjDate < $dateNow){
                $sqlTotal = mysql_query("SELECT SUM((total_cycle + total_to_in + total_cm + total_pb + total_cus_consign_in) - (total_so + total_pos + total_pbc + total_to_out + total_cus_consign_out + total_order)) AS total_qty FROM `".$this->Helper->preventInput($location)."_inventory_total_details` WHERE product_id = {$productId} AND lots_number = '{$this->Helper->preventInput($lotsNumber)}' AND expired_date = '{$this->Helper->preventInput($expired)}' AND date <= '".$adjDate."' GROUP BY product_id, expired_date");
            }else{
                $sqlTotal = mysql_query("SELECT SUM(total_qty - total_order) AS total_qty FROM `".$this->Helper->preventInput($location)."_inventory_totals` WHERE product_id = {$productId} AND lots_number = '{$this->Helper->preventInput($lotsNumber)}' AND expired_date = '{$this->Helper->preventInput($expired)}' GROUP BY product_id, expired_date");
            }
            // Get Total Qty in Order
            $totalOrder = 0;
            if(!empty($invAdjId)){
                $cycleProduct = $this->CycleProduct->read(null, $invAdjId);
                $sqlOrder = mysql_query("SELECT sum(sor.qty) as total_order FROM `stock_orders` as sor WHERE sor.cycle_product_id = ".$invAdjId." AND sor.product_id = ".$productId." AND sor.location_group_id = ".$cycleProduct['CycleProduct']['location_group_id']." AND location_id = ".$this->Helper->preventInput($location)." AND lots_number = '{$this->Helper->preventInput($lotsNumber)}' AND expired_date = '{$this->Helper->preventInput($expired)}' AND date <= '".$adjDate."' GROUP BY sor.product_id");
                while($rOrder=mysql_fetch_array($sqlOrder)){
                    $totalOrder = $rOrder['total_order'];
                }
            }
            if(mysql_num_rows($sqlTotal)){
                $rowTotal = mysql_fetch_array($sqlTotal);
                $totalQty = ($rowTotal[0] + $totalOrder)>0?($rowTotal[0] + $totalOrder):0;
            }else{
                $totalQty = 0;
            }
            echo $totalQty;
        }
        exit;
    }
    
    function viewAdjustmentIssued($branch = 'all'){
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        // Check Module Exist
        $sqlDash = mysql_query("SELECT id FROM user_dashboards WHERE module_id = 499 AND user_id = {$user['User']['id']} LIMIT 1");
        if(!mysql_num_rows($sqlDash)){
            $this->loadModel('UserDashboard');
            $userDash = array();
            $userDash['UserDashboard']['user_id']      = $user['User']['id'];
            $userDash['UserDashboard']['module_id']    = 499;
            $userDash['UserDashboard']['display']      = 1;
            $userDash['UserDashboard']['auto_refresh'] = 1;
            $userDash['UserDashboard']['time_refresh'] = 5;
            $this->UserDashboard->save($userDash);
        }
        $this->set(compact('branch'));
    }
}

?>