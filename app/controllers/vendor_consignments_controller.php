<?php

class VendorConsignmentsController extends AppController {

    var $name = 'VendorConsignments';
    var $components = array('Helper', 'Inventory');

    function index() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        $this->Helper->saveUserActivity($user['User']['id'], 'Vendor Consignment', 'Dashboard');
        // Get Loction Setting
        $locSetting = ClassRegistry::init('LocationSetting')->findById(1);
        $locCon     = '';
        if($locSetting['LocationSetting']['location_status'] == 1){
            $locCon = ' AND Location.is_for_sale = 0';
        }
        $locationGroups = ClassRegistry::init('LocationGroup')->find('list', array('joins' => array(array('table' => 'user_location_groups', 'type' => 'inner', 'conditions' => array('user_location_groups.location_group_id=LocationGroup.id'))),'conditions' => array('user_location_groups.user_id=' . $user['User']['id'], 'LocationGroup.is_active' => '1', 'LocationGroup.location_group_type_id != 1')));
        $locations  = ClassRegistry::init('Location')->find('all', array('joins' => array(array('table' => 'user_locations', 'type' => 'inner', 'conditions' => array('user_locations.location_id=Location.id'))), 'conditions' => array('user_locations.user_id=' . $user['User']['id'] . ' AND Location.is_active=1'.$locCon), 'order' => 'Location.name'));
        $this->set(compact('locationGroups', 'locations'));
    }

    function ajax($filterStatus = 'all', $vendor = 'all', $date = '') {
        $this->layout = 'ajax';
        $this->set(compact('filterStatus', 'vendor', 'date'));
    }

    function view($id = null) {
        $this->layout = 'ajax';
        if (!empty($id)) {
            $user = $this->getCurrentUser();
            $this->Helper->saveUserActivity($user['User']['id'], 'Vendor Consignment', 'View', $id);
            $vendorConsignment = $this->VendorConsignment->find("first", array('conditions' => array('VendorConsignment.id' => $id)));
            if (!empty($vendorConsignment)) {
                $vendor = ClassRegistry::init('Vendor')->find("first", array('conditions' => array('Vendor.id' => $vendorConsignment['VendorConsignment']['vendor_id'])));
                $vendorConsignmentDetails = ClassRegistry::init('VendorConsignmentDetail')->find("all", array('conditions' => array('VendorConsignmentDetail.vendor_consignment_id' => $id)));
                $this->set(compact('vendorConsignment', 'vendorConsignmentDetails', 'vendor'));
            }
        }
    }

    function add() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        if (!empty($this->data)) {                            
            if ($this->data['VendorConsignment']['total_amount'] != "") {
                $result = array();
                $this->loadModel('VendorConsignmentDetail');
                $this->loadModel('Company');
                
                $this->VendorConsignment->create();
                $this->data['VendorConsignment']['created_by'] = $user['User']['id'];
                $this->data['VendorConsignment']['status']  = 1;
                // Check Total Deposit & PO Applied
                if ($this->VendorConsignment->save($this->data)) {
                    $result['vendor_consignment_id'] = $vendorConsignmentId = $this->VendorConsignment->id;
                    // Get Module Code
                    $modCode = $this->Helper->getModuleCode($this->data['VendorConsignment']['code'], $vendorConsignmentId, 'code', 'vendor_consignments', 'status != -1');
                    // Updaet Module Code
                    mysql_query("UPDATE vendor_consignments SET code = '".$modCode."' WHERE id = ".$vendorConsignmentId);
                    
                    for ($i = 0; $i < sizeof($_POST['product_id']); $i++) {
                        if ($_POST['product_id'][$i] != '' && $_POST['qty_uom_id'][$i] != '' && $_POST['qty'][$i] != '' && $_POST['qty'][$i] != null && ($_POST['qty'][$i]) > 0) {
                            // Save Product in pruchase order detail
                            $this->VendorConsignmentDetail->create();
                            $VendorConsignmentDetail = array();
                            $VendorConsignmentDetail['VendorConsignmentDetail']['vendor_consignment_id'] = $vendorConsignmentId;
                            $VendorConsignmentDetail['VendorConsignmentDetail']['product_id'] = $_POST['product_id'][$i];
                            $VendorConsignmentDetail['VendorConsignmentDetail']['qty']        = $_POST['qty'][$i];
                            $VendorConsignmentDetail['VendorConsignmentDetail']['qty_uom_id'] = $_POST['qty_uom_id'][$i];
                            $VendorConsignmentDetail['VendorConsignmentDetail']['unit_cost']    = $_POST['unit_cost'][$i];
                            $VendorConsignmentDetail['VendorConsignmentDetail']['total_cost']   = $_POST['total_cost'][$i];
                            $VendorConsignmentDetail['VendorConsignmentDetail']['conversion']   = $_POST['conversion'][$i];
                            $VendorConsignmentDetail['VendorConsignmentDetail']['lots_number']  = $_POST['lots_number'][$i]!=''?$_POST['lots_number'][$i]:0;
                            $VendorConsignmentDetail['VendorConsignmentDetail']['date_expired'] = $_POST['date_expired'][$i]!=''?$_POST['date_expired'][$i]:'0000-00-00';
                            $VendorConsignmentDetail['VendorConsignmentDetail']['note']         = $_POST['note'][$i];
                            $this->VendorConsignmentDetail->save($VendorConsignmentDetail);     
                        } 
                    }
                    // Return Vendor Consignment Id
                    $this->Helper->saveUserActivity($user['User']['id'], 'Vendor Consignment', 'Save Add New', $vendorConsignmentId);
                    $result['code']  = 3;
                    $result['vendor_consignment_id'] = $vendorConsignmentId;
                    echo json_encode($result);
                    exit;
                } else {      
                    $this->Helper->saveUserActivity($user['User']['id'], 'Vendor Consignment', 'Save Add New (Error)');
                    $result['code'] = 2;
                    echo json_encode($result);
                    exit;
                }
            } else {
                $this->Helper->saveUserActivity($user['User']['id'], 'Vendor Consignment', 'Save Add New (Error Total Amount)');
                $result['code'] = 2;
                echo json_encode($result);
                exit;
            }
        }
        $this->Helper->saveUserActivity($user['User']['id'], 'Vendor Consignment', 'Add New');
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
                            'fields' => array('Branch.id', 'Branch.name', 'Branch.company_id', 'ModuleCodeBranch.ven_consign_code', 'Branch.currency_id', 'Currency.symbol'),
                            'conditions' => array('Branch.is_active = 1', 'user_branches.user_id=' . $user['User']['id'])
                        ));
        // Get Loction Setting
        $locSetting = ClassRegistry::init('LocationSetting')->findById(1);
        $locCon     = '';
        if($locSetting['LocationSetting']['location_status'] == 1){
            $locCon = ' AND Location.is_for_sale = 0';
        }
        $locations  = ClassRegistry::init('Location')->find('all', array('joins' => array(array('table' => 'user_locations', 'type' => 'inner', 'conditions' => array('user_locations.location_id=Location.id'))), 'conditions' => array('user_locations.user_id=' . $user['User']['id'] . ' AND Location.is_active=1'.$locCon), 'order' => 'Location.name'));
        $locationGroups = ClassRegistry::init('LocationGroup')->find('list', array('joins' => array(array('table' => 'user_location_groups', 'type' => 'inner', 'conditions' => array('user_location_groups.location_group_id=LocationGroup.id'))),'conditions' => array('user_location_groups.user_id=' . $user['User']['id'], 'LocationGroup.is_active' => '1', 'LocationGroup.location_group_type_id != 1')));
        $uoms = ClassRegistry::init('Uom')->find('all', array('fields' => array('Uom.id', 'Uom.name'), 'conditions' => array('Uom.is_active' => 1)));
        $this->set(compact("locations", "uoms", 'locationGroups', 'companies', 'branches'));
    }

    function delete($id = null) {
        if (!$id) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $user = $this->getCurrentUser();
        $vendorConsignment = $this->VendorConsignment->read(null, $id);
        if (!isset($vendorConsignment['VendorConsignment']['date']) || is_null($vendorConsignment['VendorConsignment']['date']) || $vendorConsignment['VendorConsignment']['date'] == '0000-00-00' || $vendorConsignment['VendorConsignment']['date'] == '') {
            $this->Helper->saveUserActivity($user['User']['id'], 'Vendor Consignment', 'Void (Error Order Date)', $id);
            echo MESSAGE_DATA_COULD_NOT_BE_SAVED;
            exit;
        }
        $this->VendorConsignment->updateAll(
                array('VendorConsignment.status' => "0", 'VendorConsignment.modified_by' => $user['User']['id']), array('VendorConsignment.id' => $id)
        );
        $this->Helper->saveUserActivity($user['User']['id'], 'Vendor Consignment', 'Void', $id);
        echo MESSAGE_DATA_HAS_BEEN_DELETED;
        exit;
    }

    function edit($id=null) {
        $this->layout = 'ajax';
        if (!$id) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $user = $this->getCurrentUser();
        if (!empty($this->data)) {
            $vendorConsignment          = $this->VendorConsignment->read(null, $id);
            if ($vendorConsignment['VendorConsignment']['status'] == 1) {
                if ($this->data['VendorConsignment']['total_amount'] != "") {
                    $result = array();
                    $statuEdit = "-1";
                    // Load Model
                    $this->loadModel('VendorConsignmentDetail');
                    $this->VendorConsignment->updateAll(
                            array('VendorConsignment.status' => $statuEdit, 'VendorConsignment.modified_by' => $user['User']['id']), array('VendorConsignment.id' => $id)
                    );

                    $pbCode = $this->data['VendorConsignment']['code'];
                    $this->VendorConsignment->create();
                    $this->data['VendorConsignment']['invoice_date'] = ((empty($this->data['VendorConsignment']['invoice_date']))?'0000-00-00':$this->data['VendorConsignment']['invoice_date']);
                    $this->data['VendorConsignment']['code']       = $vendorConsignment['VendorConsignment']['code'];
                    $this->data['VendorConsignment']['created_by'] = $user['User']['id'];
                    $this->data['VendorConsignment']['status']  = 1;
                    if ($this->VendorConsignment->save($this->data)) {
                        $result['vendor_consignment_id'] = $vendorConsignmentId = $this->VendorConsignment->id;
                        if($this->data['VendorConsignment']['branch_id'] != $vendorConsignment['VendorConsignment']['branch_id']){
                            // Get Module Code
                            $modCode = $this->Helper->getModuleCode($pbCode, $vendorConsignmentId, 'code', 'vendor_consignments', 'status != -1');
                            // Updaet Module Code
                            mysql_query("UPDATE vendor_consignments SET code = '".$modCode."' WHERE id = ".$vendorConsignmentId);
                        }
                        for ($i = 0; $i < sizeof($_POST['product_id']); $i++) {
                            if ($_POST['product_id'][$i] != '' && $_POST['qty_uom_id'][$i] != '' && $_POST['qty'][$i] != '' && $_POST['qty'][$i] != null && $_POST['qty'][$i] > 0) {
                                // Save Product in pruchase order detail
                                $this->VendorConsignmentDetail->create();
                                $VendorConsignmentDetail = array();
                                $VendorConsignmentDetail['VendorConsignmentDetail']['vendor_consignment_id'] = $vendorConsignmentId;
                                $VendorConsignmentDetail['VendorConsignmentDetail']['product_id'] = $_POST['product_id'][$i];
                                $VendorConsignmentDetail['VendorConsignmentDetail']['qty']        = $_POST['qty'][$i];
                                $VendorConsignmentDetail['VendorConsignmentDetail']['qty_uom_id'] = $_POST['qty_uom_id'][$i];
                                $VendorConsignmentDetail['VendorConsignmentDetail']['unit_cost']    = $_POST['unit_cost'][$i];
                                $VendorConsignmentDetail['VendorConsignmentDetail']['total_cost']   = $_POST['total_cost'][$i];
                                $VendorConsignmentDetail['VendorConsignmentDetail']['conversion']   = $_POST['conversion'][$i];
                                $VendorConsignmentDetail['VendorConsignmentDetail']['lots_number']  = $_POST['lots_number'][$i]!=''?$_POST['lots_number'][$i]:0;
                                $VendorConsignmentDetail['VendorConsignmentDetail']['date_expired'] = $_POST['date_expired'][$i]!=''?$_POST['date_expired'][$i]:'0000-00-00';
                                $VendorConsignmentDetail['VendorConsignmentDetail']['note']         = $_POST['note'][$i];
                                $this->VendorConsignmentDetail->save($VendorConsignmentDetail);     
                            }
                        }
                        // Return Vendor Consignment Id
                        $this->Helper->saveUserActivity($user['User']['id'], 'Vendor Consignment', 'Save Edit', $id, $vendorConsignmentId);
                        $result['code']  = 3;
                        $result['vendor_consignment_id'] = $vendorConsignmentId;
                        echo json_encode($result);
                        exit;
                    } else {         
                        $this->Helper->saveUserActivity($user['User']['id'], 'Vendor Consignment', 'Save Edit (Error)', $id);
                        $result['code'] = 2;
                        echo json_encode($result);
                        exit;
                    }

                } else {
                    $this->Helper->saveUserActivity($user['User']['id'], 'Vendor Consignment', 'Save Edit (Error Status)', $id);
                    $result['code'] = 2;
                    echo json_encode($result);
                    exit;
                }
            } else {
                $this->Helper->saveUserActivity($user['User']['id'], 'Vendor Consignment', 'Save Edit (Error has transaction with other modules)', $id);
                $result['code'] = 2;
                echo json_encode($result);
                exit;
            }
        }
        if (!empty($id)) {
            $this->data = $this->VendorConsignment->read(null, $id);
            if ($this->data['VendorConsignment']['status'] == 1) {
                $this->Helper->saveUserActivity($user['User']['id'], 'Vendor Consignment', 'Edit', $id);
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
                                    'fields' => array('Branch.id', 'Branch.name', 'Branch.company_id', 'ModuleCodeBranch.ven_consign_code', 'Branch.currency_id', 'Currency.symbol'),
                                    'conditions' => array('Branch.is_active = 1', 'user_branches.user_id=' . $user['User']['id'])
                                ));
                // Get Loction Setting
                $locSetting = ClassRegistry::init('LocationSetting')->findById(1);
                $locCon     = '';
                if($locSetting['LocationSetting']['location_status'] == 1){
                    $locCon = ' AND Location.is_for_sale = 0';
                }
                $locationGroups = ClassRegistry::init('LocationGroup')->find('list', array('joins' => array(array('table' => 'user_location_groups', 'type' => 'inner', 'conditions' => array('user_location_groups.location_group_id=LocationGroup.id'))),'conditions' => array('user_location_groups.user_id=' . $user['User']['id'], 'LocationGroup.is_active' => '1', 'LocationGroup.location_group_type_id != 1')));
                $locations = ClassRegistry::init('Location')->find('all', array('joins' => array(array('table' => 'user_locations', 'type' => 'inner', 'conditions' => array('user_locations.location_id=Location.id'))), 'conditions' => array('user_locations.user_id=' . $user['User']['id'] . ' AND Location.is_active=1'.$locCon), 'order' => 'Location.name'));
                $uoms = ClassRegistry::init('Uom')->find('all', array('fields' => array('Uom.id', 'Uom.name'), 'conditions' => array('Uom.is_active' => 1)));
                $vendorConsignmentDetails = ClassRegistry::init('VendorConsignmentDetail')->find("all", array('conditions' => array('VendorConsignmentDetail.vendor_consignment_id' => $id)));
                $this->set(compact('vendorConsignment', 'vendorConsignmentDetails', 'uoms', 'locations', 'locationGroups', 'id', 'companies', 'branches'));
            } else {
                $this->Helper->saveUserActivity($user['User']['id'], 'Vendor Consignment', 'Edit (Error ID)', $id);
                echo MESSAGE_DATA_INVALID;
                exit;
            }
        }
    }

    function product($companyId = null, $branchId = null, $locationId = null) {
        $this->layout = "ajax";
        $this->set(compact('companyId', 'branchId', 'locationId'));
    }

    function productAjax($companyId = null, $branchId = null, $locationId = null, $category = null) {
        $this->layout = "ajax";
        $this->set(compact('companyId', 'branchId', 'locationId', 'category'));
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
    
    function searchProductCode($companyId = null, $branchId = null, $code = null, $field = null) {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        $searchField = "";
        if($field == 1){
            $searchField = "trim(p.code) = '" . trim($code) . "' ";
        } else if($field == 2){
            $searchField = "trim(p.barcode) = '" . trim($code) . "'";
        }
        $cmt = "SELECT p.id,                                 
                p.code,
                p.barcode,
                p.name,
                p.unit_cost,  
                p.small_val_uom,
                p.is_expired_date,
                p.is_packet,
                p.price_uom_id,
                IF((SELECT count(*) FROM inventories WHERE product_id = p.id AND unit_cost > 0) > 0,(SELECT unit_cost FROM inventories WHERE product_id = p.id AND unit_cost > 0 ORDER BY id DESC LIMIT 1), p.default_cost) AS unitCost                             
                FROM  products as p
                INNER JOIN product_branches ON product_branches.product_id = p.id AND product_branches.branch_id = ".$branchId."
                INNER JOIN uoms as u ON u.id = p.price_uom_id
                INNER JOIN product_pgroups ON product_pgroups.product_id = p.id
                INNER JOIN pgroups ON pgroups.id = product_pgroups.pgroup_id AND (pgroups.user_apply = 0 OR (pgroups.user_apply = 1 AND pgroups.id IN (SELECT pgroup_id FROM user_pgroups WHERE user_id = ".$user['User']['id'].")))
                WHERE p.is_active = 1 AND ".$searchField." AND ((p.price_uom_id IS NOT NULL AND p.is_packet = 0) OR (p.price_uom_id IS NULL AND p.is_packet = 1)) AND p.company_id = ".$companyId."
                GROUP BY p.code, p.name
                ORDER BY p.code";
        $product = mysql_query($cmt);
        if (@$num = mysql_num_rows($product)) {
            while ($aRow = mysql_fetch_array($product)) {
                   $packetList = '';
                   $productId = $aRow['id'];
                   $productSku = $aRow['code'];
                   $productPUC = $aRow['barcode'];
                   $productName = $aRow['name'];
                   $isExpired = $aRow['is_expired_date'];
                   $smallUomVal = $aRow['small_val_uom'];
                   $unitCost = $aRow['unitCost'];
                   $mainUomId = $aRow['price_uom_id'];
                   if($aRow['is_packet'] == 1){
                        $index = 1;
                        $sqlPacket = mysql_query("SELECT products.code, product_with_packets.qty_uom_id, product_with_packets.qty, product_with_packets.conversion FROM product_with_packets INNER JOIN products ON products.id = product_with_packets.packet_product_id WHERE product_with_packets.main_product_id = ".$productId);
                        while($rowPacket = mysql_fetch_array($sqlPacket)){
                            if($index > 1){
                                $packetList .= "**";
                            }
                            $qtyOrder = $rowPacket['qty'];
                            $packetList .= $rowPacket['code']."||".$rowPacket['qty_uom_id']."||".$qtyOrder;
                            $index++;
                        }
                   }
                   echo $productId."--".$productSku."--".$productPUC."--".$productName."--".$isExpired."--".$unitCost."--".$smallUomVal."--".$mainUomId."--".$packetList;
            }
        } else {
            echo TABLE_NO_PRODUCT;                       
        }
        exit;
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
    
    function printInvoice($id = null) {
        if (!empty($id)) {
            $this->layout = 'ajax';
            $vendorConsignment = ClassRegistry::init('VendorConsignment')->find("first", array('conditions' => array('VendorConsignment.id' => $id)));
            if (!empty($vendorConsignment)) {
                $vendorConsignmentDetails = ClassRegistry::init('VendorConsignmentDetail')->find("all", array('conditions' => array('VendorConsignmentDetail.vendor_consignment_id' => $id)));
                $location = ClassRegistry::init('Location')->find("first", array('conditions' => array("Location.id" => $vendorConsignment['VendorConsignment']['location_id'], "Location.is_active" => "1")));
                $this->set(compact('vendorConsignment', 'vendorConsignmentDetails', 'location'));
            } else {
                exit;
            }
        } else {
            exit;
        }
    }
    
    function receive($id = null){
        $this->layout = 'ajax';
        if (!$id && empty($this->data)) {
            $result['error'] = 0;
            echo json_encode($result);
            exit;
        }
        $user = $this->getCurrentUser();
        if (!empty($this->data)) {
            $result = array();
            $result['error'] = 0;
            $vendorConsignment = $this->VendorConsignment->read(null, $this->data['vendor_consignment_id']);
            if ($vendorConsignment['VendorConsignment']['status'] == 1) {
                $dateVendorConsignment  = $vendorConsignment['VendorConsignment']['date'];
                $vendorConsignmentDetails = ClassRegistry::init('VendorConsignmentDetail')->find("all", array('conditions' => array('VendorConsignmentDetail.vendor_consignment_id' => $this->data['vendor_consignment_id'])));
                // List Sale Detail
                foreach ($vendorConsignmentDetails AS $vendorConsignmentDetail) {
                    $totalOrder = $vendorConsignmentDetail['VendorConsignmentDetail']['qty'] * $vendorConsignmentDetail['VendorConsignmentDetail']['conversion'];
                    // Update Inventory (In)
                    $data = array();
                    $data['module_type']       = 16;
                    $data['vendor_consignment_id'] = $vendorConsignment['VendorConsignment']['id'];
                    $data['product_id']        = $vendorConsignmentDetail['VendorConsignmentDetail']['product_id'];
                    $data['location_id']       = $vendorConsignment['VendorConsignment']['location_id'];
                    $data['location_group_id'] = $vendorConsignment['VendorConsignment']['location_group_id'];
                    $data['lots_number']  = $vendorConsignmentDetail['VendorConsignmentDetail']['lots_number']!=''?$vendorConsignmentDetail['VendorConsignmentDetail']['lots_number']:0;
                    $data['expired_date'] = $vendorConsignmentDetail['VendorConsignmentDetail']['date_expired']!=''?$vendorConsignmentDetail['VendorConsignmentDetail']['date_expired']:'0000-00-00';
                    $data['date']         = $dateVendorConsignment;
                    $data['total_qty']    = $totalOrder;
                    $data['total_order']  = $totalOrder;
                    $data['total_free']   = 0;
                    $data['user_id']      = $user['User']['id'];
                    $data['customer_id']  = "";
                    $data['vendor_id']    = $vendorConsignment['VendorConsignment']['vendor_id'];
                    $data['unit_cost']    = 0;
                    $data['unit_price']   = 0;
                    // Update Invetory Location
                    $this->Inventory->saveInventory($data);
                    // Update Inventory Group
                    $this->Inventory->saveGroupTotalDetail($data);
                }
                // Update VendorConsignment
                mysql_query("UPDATE vendor_consignments SET status = 2 WHERE id = ".$vendorConsignment['VendorConsignment']['id']);
                $this->Helper->saveUserActivity($user['User']['id'], 'Vendor Consignment', 'Save Receive', $vendorConsignment['VendorConsignment']['id']);
            } else {
                $this->Helper->saveUserActivity($user['User']['id'], 'Vendor Consignment', 'Save Receive (Error Status)', $vendorConsignment['VendorConsignment']['id']);
                $result['error'] = 1;
            }
            echo json_encode($result);
            exit;
        }
        $this->Helper->saveUserActivity($user['User']['id'], 'Vendor Consignment', 'Receive', $id);
        $vendorConsignment = $this->VendorConsignment->read(null, $id);
        if (!empty($vendorConsignment)) {
            $vendorConsignmentDetails = ClassRegistry::init('VendorConsignmentDetail')->find("all", array('conditions' => array('VendorConsignmentDetail.vendor_consignment_id' => $id)));
            $this->set(compact('vendorConsignment', 'vendorConsignmentDetails'));
        } else {
            exit;
        }
    }

}

?>