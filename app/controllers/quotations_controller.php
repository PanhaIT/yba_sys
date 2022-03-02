<?php

class QuotationsController extends AppController {

    var $name = 'Quotations';
    var $components = array('Helper', 'ProductCom');

    function index() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        $this->set('user', $user);
        $this->Helper->saveUserActivity($user['User']['id'], 'Quotation', 'Dashboard');
    }

    function ajax($customer = 'all', $status = 'all', $approve = 'all', $date = '') {
        $this->layout = 'ajax';
        $this->set(compact('customer', 'status', 'approve', 'date'));
    }

    function add() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        if (!empty($this->data)) {
            $result  = array();
            $dateNow = date("Y-m-d H:i:s");
            // Load Table
            $this->loadModel('QuotationDetail');
            $this->loadModel('QuotationService');
            // Update
            if($this->data['Quotation']['preview_id'] != ''){
                mysql_query("UPDATE quotations SET status = -1, modified = '".$dateNow."', modified_by = ".$user['User']['id']." WHERE id = ".$this->data['Quotation']['preview_id']);
            }
            // Quotation
            $this->Quotation->create();
            $quotation = array();
            $quotation['Quotation']['sys_code']   = md5(rand().strtotime(date("Y-m-d H:i:s")).$user['User']['id']);
            $quotation['Quotation']['company_id'] = $this->data['Quotation']['company_id'];
            $quotation['Quotation']['branch_id']  = $this->data['Quotation']['branch_id'];
            $quotation['Quotation']['customer_id'] = $this->data['Quotation']['customer_id'];
            $quotation['Quotation']['quotation_code'] = $this->data['Quotation']['quotation_code'];
            $quotation['Quotation']['quotation_date'] = $this->data['Quotation']['quotation_date'];
            $quotation['Quotation']['currency_id']  = $this->data['Quotation']['currency_id'];
            $quotation['Quotation']['price_type_id']  = $this->data['Quotation']['price_type_id'];
            $quotation['Quotation']['note']          = $this->data['Quotation']['note'];
            $quotation['Quotation']['total_amount']  = $this->data['Quotation']['total_amount'];
            $quotation['Quotation']['total_deposit'] = $this->data['Quotation']['total_deposit'];
            $quotation['Quotation']['discount']      = $this->data['Quotation']['discount'];
            $quotation['Quotation']['discount_percent'] = $this->data['Quotation']['discount_percent'];
            $quotation['Quotation']['total_vat'] = $this->data['Quotation']['total_vat'];
            $quotation['Quotation']['vat_percent'] = $this->data['Quotation']['vat_percent'];
            $quotation['Quotation']['vat_setting_id'] = $this->data['Quotation']['vat_setting_id'];
            $quotation['Quotation']['vat_calculate'] = $this->data['Quotation']['vat_calculate'];
            $quotation['Quotation']['share_save_option'] = $this->data['Quotation']['share_save_option'];
            $quotation['Quotation']['user_share_id'] = $this->data['Quotation']['user_share_id'];
            $quotation['Quotation']['share_option']  = $this->data['Quotation']['share_option'];
            $quotation['Quotation']['share_user']    = $this->data['Quotation']['share_user'];
            $quotation['Quotation']['share_except_user']   = $this->data['Quotation']['share_except_user'];
            $quotation['Quotation']['created_by'] = $user['User']['id'];
            $quotation['Quotation']['status'] = 1;
            if ($this->Quotation->save($quotation)) {
                $quotationId = $this->Quotation->id;
                // Get Module Code
                $modCode = $this->Helper->getModuleCode($this->data['Quotation']['quotation_code'], $quotationId, 'quotation_code', 'quotations', 'status >= 0 AND branch_id = '.$this->data['Quotation']['branch_id']);
                // Updaet Module Code
                mysql_query("UPDATE quotations SET quotation_code = '".$modCode."' WHERE id = ".$quotationId);
                for ($i = 0; $i < sizeof($_POST['product']); $i++) {
                    if (!empty($_POST['product_id'][$i])) {
                        $quotationDetail = array();
                        // Quotation Detail
                        $this->QuotationDetail->create();
                        $quotationDetail['QuotationDetail']['quotation_id'] = $quotationId;
                        $quotationDetail['QuotationDetail']['product_id']   = $_POST['product_id'][$i];
                        $quotationDetail['QuotationDetail']['qty']          = $_POST['qty'][$i];
                        $quotationDetail['QuotationDetail']['qty_free']     = $_POST['qty_free'][$i];
                        $quotationDetail['QuotationDetail']['qty_uom_id']   = $_POST['qty_uom_id'][$i];
                        $quotationDetail['QuotationDetail']['conversion']   = $_POST['conversion'][$i];
                        $quotationDetail['QuotationDetail']['discount_id']  = $_POST['discount_id'][$i];
                        $quotationDetail['QuotationDetail']['discount_amount']  = $_POST['discount'][$i];
                        $quotationDetail['QuotationDetail']['discount_percent'] = $_POST['discount_percent'][$i];
                        $quotationDetail['QuotationDetail']['unit_cost']    = $_POST['unit_cost'][$i];
                        $quotationDetail['QuotationDetail']['unit_price']   = $_POST['unit_price'][$i];
                        $quotationDetail['QuotationDetail']['total_price']  = $_POST['total_price_bf_dis'][$i];
                        $this->QuotationDetail->save($quotationDetail);
                    } else if(!empty($_POST['service_id'][$i])){
                        $quotationService = array();
                        // Quotation Detail
                        $this->QuotationService->create();
                        $quotationService['QuotationService']['quotation_id'] = $quotationId;
                        $quotationService['QuotationService']['service_id']   = $_POST['service_id'][$i];
                        $quotationService['QuotationService']['qty']          = $_POST['qty'][$i];
                        $quotationService['QuotationService']['qty_free']          = $_POST['qty_free'][$i];
                        $quotationService['QuotationService']['conversion']   = $_POST['conversion'][$i];
                        $quotationService['QuotationService']['discount_id']  = $_POST['discount_id'][$i];
                        $quotationService['QuotationService']['discount_amount']  = $_POST['discount'][$i];
                        $quotationService['QuotationService']['discount_percent'] = $_POST['discount_percent'][$i];
                        $quotationService['QuotationService']['unit_price']   = $_POST['unit_price'][$i];
                        $quotationService['QuotationService']['total_price']  = $_POST['total_price_bf_dis'][$i];
                        $this->QuotationService->save($quotationService);
                    }
                }
                $this->Helper->saveUserActivity($user['User']['id'], 'Quotation', 'Save Add New', $quotationId);
                $result['id']    = $quotationId;
                $result['code']  = $modCode;
                $result['error'] = 0;
                echo json_encode($result);
                exit;
            }else {
                $this->Helper->saveUserActivity($user['User']['id'], 'Quotation', 'Save Add New (Error)');
                $result['error'] = 1;
                echo json_encode($result);
                exit;
            }
        }
        $this->Helper->saveUserActivity($user['User']['id'], 'Quotation', 'Add New');
        $companies = ClassRegistry::init('Company')->find('all', array('joins' => array(array('table' => 'user_companies', 'type' => 'inner', 'conditions' => array('user_companies.company_id=Company.id'))), 'fields' => array('Company.id', 'Company.name', 'Company.vat_calculate'), 'conditions' => array('Company.is_active = 1', 'user_companies.user_id=' . $user['User']['id'])));
        $branches  = ClassRegistry::init('Branch')->find('all', array('joins' => array(array('table' => 'user_branches', 'type' => 'inner', 'conditions' => array('user_branches.branch_id=Branch.id')), array('table' => 'module_code_branches AS ModuleCodeBranch', 'type' => 'left', 'conditions' => array('ModuleCodeBranch.branch_id=Branch.id'))), 'fields' => array('Branch.id', 'Branch.name', 'Branch.company_id', 'ModuleCodeBranch.quote_code', 'Branch.currency_id', 'Currency.symbol'), 'conditions' => array('Branch.is_active = 1', 'user_branches.user_id=' . $user['User']['id'])));
        $this->set(compact("companies", "branches"));
    }

    function orderDetails() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        $branches = ClassRegistry::init('Branch')->find('all',
                        array(
                            'joins' => array(
                                array('table' => 'user_branches', 'type' => 'inner', 'conditions' => array('user_branches.branch_id=Branch.id')),
                                array('table' => 'module_code_branches AS ModuleCodeBranch', 'type' => 'left', 'conditions' => array('ModuleCodeBranch.branch_id=Branch.id'))
                            ),
                            'fields' => array('Branch.id', 'Branch.name', 'Branch.company_id', 'ModuleCodeBranch.inv_code', 'Branch.currency_id', 'Currency.symbol'),
                            'conditions' => array('Branch.is_active = 1', 'user_branches.user_id=' . $user['User']['id'])
                        ));
        $uoms = ClassRegistry::init('Uom')->find('all', array('fields' => array('Uom.id', 'Uom.name'), 'conditions' => array('Uom.is_active' => 1)));
        $this->set(compact("uoms", "branches"));
    }
    
    function customer($companyId = null) {
        $this->layout = 'ajax';
        $this->set(compact('companyId'));
    }

    function customerAjax($companyId = null, $group = null) {
        $this->layout = 'ajax';
        $this->set(compact('companyId', 'group'));
    }
    
    function product($companyId = null, $branchId = null) {
        $this->layout = 'ajax';
        $orderDate    = $_POST['order_date'];
        $this->set(compact('companyId', 'branchId', 'orderDate'));
    }

    function productAjax($companyId = null, $branchId = null, $category = null) {
        $this->layout = 'ajax';
        $orderDate    = $_GET['order_date'];
        $this->set(compact('companyId', 'branchId', 'category', 'orderDate'));
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
                                'Product.code LIKE' => '%' . trim($this->params['url']['q']) . '%',
                                'Product.barcode LIKE' => '%' . trim($this->params['url']['q']) . '%',
                                'Product.name LIKE' => '%' . trim($this->params['url']['q']) . '%',
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

    function searchProductByCode($companyId = null, $customerId = 0, $branchId = null) {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        $order_date  = $_POST['order_date'];
        $joinProductBranch  = array(
                             'table' => 'product_branches',
                             'type' => 'INNER',
                             'alias' => 'ProductBranch',
                             'conditions' => array(
                                 'ProductBranch.product_id = Product.id',
                                 'ProductBranch.branch_id' => $branchId
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
        $product_code = !empty($this->data['code']) ? $this->data['code'] : "";
        $product = ClassRegistry::init('Product')->find('first', array(
            'fields' => array(
                'Product.id',
                'Product.name',
                'Product.code',
                'Product.barcode',
                'Product.price_uom_id',
                'Product.is_packet',
                'Product.small_val_uom'
            ),
            'conditions' => array(
                array(
                    "OR" => array(
                        'trim(Product.code)' => trim($product_code),
                        'trim(Product.barcode)' => trim($product_code)
                    )
                ), 'Product.is_active' => 1
                , 'Product.company_id' => $companyId
                , '((Product.price_uom_id IS NOT NULL AND Product.is_packet = 0) OR (Product.price_uom_id IS NULL AND Product.is_packet = 1))'
                , "((is_not_for_sale = 0 AND period_from IS NULL AND period_to IS NULL) OR (is_not_for_sale = 0 AND period_from <= '".$order_date."' AND period_to >= '".$order_date."') OR (is_not_for_sale = 1 AND period_from IS NOT NULL AND period_to IS NOT NULL AND '".$order_date."' NOT BETWEEN period_from AND period_to))"
            ),
            'joins' => $joins,
            'group' => array(
                'Product.id'
            )
        ));
        $this->set(compact('product', 'customerId'));

        $db = ConnectionManager::getDataSource('default');
        mysql_select_db($db->config['database']);
    }
    
    function edit($id = null) {
        $this->layout = 'ajax';
        if (!$id && empty($this->data)) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $user = $this->getCurrentUser();
        if (!empty($this->data)) {
            $Quotation = $this->Quotation->read(null, $this->data['id']);
            if ($Quotation['Quotation']['status'] == 1) {
                $result      = array();
                $statuEdit   = "-1";
                // Update Status As Edit
                $this->Quotation->updateAll(
                        array('Quotation.status' => $statuEdit, 'Quotation.modified_by' => $user['User']['id']), array('Quotation.id' => $this->data['id'])
                );
                // Load Table
                $this->loadModel('QuotationDetail');
                $this->loadModel('QuotationService');
                // Quotation
                $quoteCode = $Quotation['Quotation']['quotation_code'];
                $this->Quotation->create();
                $quotation = array();
                $quotation['Quotation']['sys_code']       = md5(rand().strtotime(date("Y-m-d H:i:s")).$user['User']['id']);
                $quotation['Quotation']['quotation_code'] = $quoteCode;
                $quotation['Quotation']['company_id']     = $this->data['Quotation']['company_id'];
                $quotation['Quotation']['branch_id']      = $this->data['Quotation']['branch_id'];
                $quotation['Quotation']['customer_id']    = $this->data['Quotation']['customer_id'];
                $quotation['Quotation']['quotation_date'] = $this->data['Quotation']['quotation_date'];
                $quotation['Quotation']['currency_id']  = $this->data['Quotation']['currency_id'];
                $quotation['Quotation']['price_type_id']  = $this->data['Quotation']['price_type_id'];
                $quotation['Quotation']['note']          = $this->data['Quotation']['note'];
                $quotation['Quotation']['total_amount']  = $this->data['Quotation']['total_amount'];
                $quotation['Quotation']['total_deposit'] = $this->data['Quotation']['total_deposit'];
                $quotation['Quotation']['discount']      = $this->data['Quotation']['discount'];
                $quotation['Quotation']['discount_percent'] = $this->data['Quotation']['discount_percent'];
                $quotation['Quotation']['total_vat']   = $this->data['Quotation']['total_vat'];
                $quotation['Quotation']['vat_percent'] = $this->data['Quotation']['vat_percent'];
                $quotation['Quotation']['vat_setting_id'] = $this->data['Quotation']['vat_setting_id'];
                $quotation['Quotation']['vat_calculate']  = $this->data['Quotation']['vat_calculate'];
                $quotation['Quotation']['share_save_option'] = $this->data['Quotation']['share_save_option'];
                $quotation['Quotation']['user_share_id'] = $this->data['Quotation']['user_share_id'];
                $quotation['Quotation']['share_option']  = $this->data['Quotation']['share_option'];
                $quotation['Quotation']['share_user']    = $this->data['Quotation']['share_user'];
                $quotation['Quotation']['share_except_user']   = $this->data['Quotation']['share_except_user'];
                $quotation['Quotation']['edited']      = date("Y-m-d H:i:s");
                $quotation['Quotation']['edited_by']   = $user['User']['id'];
                $quotation['Quotation']['created']     = $Quotation['Quotation']['created'];
                $quotation['Quotation']['created_by']  = $Quotation['Quotation']['created_by'];
                $quotation['Quotation']['status'] = 1;
                if ($this->Quotation->save($quotation)) {
                    $result['id'] = $quotationId = $this->Quotation->id;
                    if($this->data['Quotation']['branch_id'] != $Quotation['Quotation']['branch_id']){
                        // Get Module Code
                        $modCode = $this->Helper->getModuleCode($this->data['Quotation']['quotation_code'], $quotationId, 'quotation_code', 'quotations', 'status >= 0 AND branch_id = '.$this->data['Quotation']['branch_id']);
                        // Updaet Module Code
                        mysql_query("UPDATE quotations SET quotation_code = '".$modCode."' WHERE id = ".$quotationId);
                    }
                    for ($i = 0; $i < sizeof($_POST['product']); $i++) {
                        if (!empty($_POST['product_id'][$i])) {
                            $quotationDetail = array();
                            // Quotation Detail
                            $this->QuotationDetail->create();
                            $quotationDetail['QuotationDetail']['quotation_id'] = $quotationId;
                            $quotationDetail['QuotationDetail']['product_id']   = $_POST['product_id'][$i];
                            $quotationDetail['QuotationDetail']['qty']          = $_POST['qty'][$i];
                            $quotationDetail['QuotationDetail']['qty_free']          = $_POST['qty_free'][$i];
                            $quotationDetail['QuotationDetail']['qty_uom_id']   = $_POST['qty_uom_id'][$i];
                            $quotationDetail['QuotationDetail']['conversion']   = $_POST['conversion'][$i];
                            $quotationDetail['QuotationDetail']['discount_id']  = $_POST['discount_id'][$i];
                            $quotationDetail['QuotationDetail']['discount_amount']  = $_POST['discount'][$i];
                            $quotationDetail['QuotationDetail']['discount_percent'] = $_POST['discount_percent'][$i];
                            $quotationDetail['QuotationDetail']['unit_cost']    = $_POST['unit_cost'][$i];
                            $quotationDetail['QuotationDetail']['unit_price']   = $_POST['unit_price'][$i];
                            $quotationDetail['QuotationDetail']['total_price']  = $_POST['total_price_bf_dis'][$i];
                            $this->QuotationDetail->save($quotationDetail);

                        } else if(!empty($_POST['service_id'][$i])){
                            $quotationService = array();
                            // Quotation Detail
                            $this->QuotationService->create();
                            $quotationService['QuotationService']['quotation_id'] = $quotationId;
                            $quotationService['QuotationService']['service_id']   = $_POST['service_id'][$i];
                            $quotationService['QuotationService']['qty']          = $_POST['qty'][$i];
                            $quotationService['QuotationService']['conversion']   = $_POST['conversion'][$i];
                            $quotationService['QuotationService']['discount_id']  = $_POST['discount_id'][$i];
                            $quotationService['QuotationService']['discount_amount']  = $_POST['discount'][$i];
                            $quotationService['QuotationService']['discount_percent'] = $_POST['discount_percent'][$i];
                            $quotationService['QuotationService']['unit_price']   = $_POST['unit_price'][$i];
                            $quotationService['QuotationService']['total_price']  = $_POST['total_price_bf_dis'][$i];
                            $this->QuotationService->save($quotationService);
                        }
                    }
                    $this->Helper->saveUserActivity($user['User']['id'], 'Quotation', 'Save Edit', $this->data['id'], $quotationId);
                    echo json_encode($result);
                    exit;
                } else {
                    $this->Helper->saveUserActivity($user['User']['id'], 'Quotation', 'Save Edit (Error)', $this->data['id']);
                    $result['error'] = 1;
                    echo json_encode($result);
                    exit;
                }
            }else{
                $this->Helper->saveUserActivity($user['User']['id'], 'Quotation', 'Save Edit (Error Status)', $this->data['id']);
                $result['error'] = 1;
                echo json_encode($result);
                exit;
            }
        }
        $this->Helper->saveUserActivity($user['User']['id'], 'Quotation', 'Edit', $id);
        $this->data = ClassRegistry::init('Quotation')->find('first', array('conditions' => array('Quotation.status = 1', 'Quotation.id' => $id)));
        $companies  = ClassRegistry::init('Company')->find('all', array('joins' => array(array('table' => 'user_companies', 'type' => 'inner', 'conditions' => array('user_companies.company_id=Company.id'))), 'fields' => array('Company.id', 'Company.name', 'Company.vat_calculate'), 'conditions' => array('Company.is_active = 1', 'user_companies.user_id=' . $user['User']['id'])));
        $branches   = ClassRegistry::init('Branch')->find('all', array('joins' => array(array('table' => 'user_branches', 'type' => 'inner', 'conditions' => array('user_branches.branch_id=Branch.id')), array('table' => 'module_code_branches AS ModuleCodeBranch', 'type' => 'left', 'conditions' => array('ModuleCodeBranch.branch_id=Branch.id'))),'fields' => array('Branch.id', 'Branch.name', 'Branch.company_id', 'ModuleCodeBranch.quote_code', 'Branch.currency_id', 'Currency.symbol'), 'conditions' => array('Branch.is_active = 1', 'user_branches.user_id=' . $user['User']['id'])));
        $this->set(compact("companies", "branches"));
        if ($this->data['Quotation']['status'] != 1) {
            echo "Sorry Cannot Edit";
            exit;
        }
    }
    
    function editDetails($id = null) {
        $this->layout = 'ajax';
        if($id >= 0){
            $user = $this->getCurrentUser();
            $quotation = ClassRegistry::init('Quotation')->find("first", array('conditions' => array('Quotation.id' => $id)));
            $quotationDetails  = ClassRegistry::init('QuotationDetail')->find("all", array('conditions' => array('QuotationDetail.quotation_id' => $id)));
            $quotationServices = ClassRegistry::init('QuotationService')->find("all", array('conditions' => array('QuotationService.quotation_id' => $id)));
            $branches = ClassRegistry::init('Branch')->find('all', array('joins' => array(array('table' => 'user_branches', 'type' => 'inner', 'conditions' => array('user_branches.branch_id=Branch.id')), array('table' => 'module_code_branches AS ModuleCodeBranch', 'type' => 'left', 'conditions' => array('ModuleCodeBranch.branch_id=Branch.id'))), 'fields' => array('Branch.id', 'Branch.name', 'Branch.company_id', 'ModuleCodeBranch.inv_code', 'Branch.currency_id', 'Currency.symbol'), 'conditions' => array('Branch.is_active = 1', 'user_branches.user_id=' . $user['User']['id'])));
            $uoms = ClassRegistry::init('Uom')->find('all', array('fields' => array('Uom.id', 'Uom.name'), 'conditions' => array('Uom.is_active' => 1)));
            $this->set(compact('uoms', 'quotationDetails', 'quotationServices', 'quotation', 'branches'));
        } else {
            exit;
        }
    }
    
    function delete($id = null) {
        if (!$id) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $user = $this->getCurrentUser();
        $quotation = ClassRegistry::init('Quotation')->find("first", array('conditions' => array('Quotation.id' => $id)));
        // Get Total Deposit From GL
        $sqlGL = mysql_query("SELECT SUM(IFNULL(total_deposit,0)) FROM general_ledgers WHERE apply_to_id = ".$id." AND deposit_type = 4 AND is_active != 2");
        $rowGL = mysql_fetch_array($sqlGL);
        if ($quotation['Quotation']['status'] == 1 && $rowGL[0] == 0) {
            // Update Credit Memo
            $this->Quotation->updateAll(
                    array('Quotation.status' => 0, 'Quotation.modified_by' => $user['User']['id']), array('Quotation.id' => $id)
            );
            $this->Helper->saveUserActivity($user['User']['id'], 'Quotation', 'Delete', $id);
            echo MESSAGE_DATA_HAS_BEEN_DELETED;
            exit;
        }else{
            $this->Helper->saveUserActivity($user['User']['id'], 'Quotation', 'Delete (Error)', $id);
            echo MESSAGE_DATA_COULD_NOT_BE_SAVED;
            exit;
        }
    }
    
    function view($id = null) {
        $this->layout = 'ajax';
        if (!empty($id)) {
            $user = $this->getCurrentUser();
            $this->data = $this->Quotation->read(null, $id);
            if (!empty($this->data)) {
                $this->Helper->saveUserActivity($user['User']['id'], 'Quotation', 'View', $id);
                $quotationDetails = ClassRegistry::init('QuotationDetail')->find("all", array('conditions' => array('QuotationDetail.quotation_id' => $id)));
                $quotationServices = ClassRegistry::init('QuotationService')->find("all", array('conditions' => array('QuotationService.quotation_id' => $id)));
                $this->set(compact('quotationDetails', 'quotationServices'));
            } else {
                exit;
            }
        } else {
            exit;
        }
    }
    
    function printInvoice($id = null, $head = 0) {
        $this->layout = 'ajax';
        if (!empty($id)) {
            $this->data = $this->Quotation->read(null, $id);
            if (!empty($this->data)) {
                $quotationDetails  = ClassRegistry::init('QuotationDetail')->find("all", array('conditions' => array('QuotationDetail.quotation_id' => $id)));
                $quotationServices = ClassRegistry::init('QuotationService')->find("all", array('conditions' => array('QuotationService.quotation_id' => $id)));
                $quotationMiscs    = ClassRegistry::init('QuotationMisc')->find("all", array('conditions' => array('QuotationMisc.quotation_id' => $id)));
                $this->set(compact('quotationDetails', 'quotationServices', 'quotationMiscs', 'head'));
            } else {
                exit;
            }
        } else {
            exit;
        }
    }
    
    function close($id = null) {
        if (!$id) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $user = $this->getCurrentUser();
        $modified = date("Y-m-d H:i:s");                    
        $this->Quotation->updateAll(
                array('Quotation.is_close' => "1", "Quotation.modified_by" => $user['User']['id'], 'Quotation.modified' => "'$modified'"),
                array('Quotation.id' => $id)
        );
        $this->Helper->saveUserActivity($user['User']['id'], 'Quotation', 'Close', $id);
        echo MESSAGE_DATA_HAS_BEEN_CLOSED;
        exit;
    }
    
    function open($id = null) {
        if (!$id) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $user = $this->getCurrentUser();
        $modified = date("Y-m-d H:i:s"); 
        $this->Quotation->updateAll(
                array('Quotation.is_close' => "0", "Quotation.modified_by" => $user['User']['id'], 'Quotation.modified' => "'$modified'"),
                array('Quotation.id' => $id)
        );
        $this->Helper->saveUserActivity($user['User']['id'], 'Quotation', 'Open', $id);
        echo MESSAGE_DATA_HAS_BEEN_SAVED;
        exit;
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
        $services = ClassRegistry::init('Service')->find("all", array("conditions" => array("Service.company_id=" . $companyId . " AND Service.branch_id=" . $branchId . " AND Service.is_active = 1 AND Service.type = 1")));
        foreach ($services as $service) {
            $uomId = $service['Service']['uom_id']!=''?$service['Service']['uom_id']:'';
            array_push($array, array('value' => $service['Service']['id'], 'name' => $service['Service']['code']." - ".$service['Service']['name'], 'class' => $service['Pgroup']['id'], 'abbr' => $service['Service']['name'], 'price' => $service['Service']['unit_price'], 'scode' => $service['Service']['code'], 'suom' => $uomId));
        }
        return $array;
    }
    
    function miscellaneous() {
        $this->layout = 'ajax';
    }
    
    function discount($companyId = null) {
        $this->layout = 'ajax';
        if (!$companyId) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $discounts = ClassRegistry::init('Discount')->find("all", array('conditions' => array('Discount.is_active' => 1, 'Discount.company_id' => $companyId), 'order' => array('id DESC')));
        $this->set(compact('discounts'));
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
    
    function invoiceDiscount(){
        $this->layout = 'ajax';
    }
    
    function searchCustomer(){
        $this->layout = 'ajax';
        $search = $this->params['url']['q'];
        $this->set(compact('search'));
    }
    
    function approve($id){
        $this->layout = 'ajax';
        if (!$id) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $user = $this->getCurrentUser();
        $modified = date("Y-m-d H:i:s");                    
        $this->Quotation->updateAll(
                array('Quotation.is_approve' => "1", "Quotation.approved_by" => $user['User']['id'], 'Quotation.approved' => "'$modified'"),
                array('Quotation.id' => $id)
        );
        $this->Helper->saveUserActivity($user['User']['id'], 'Quotation', 'Save Approve', $id);
        echo MESSAGE_DATA_HAS_BEEN_SAVED;
        exit;
    }
    
    function saveShareQuote($id){
        $this->layout = 'ajax';
        if (!$id) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $shareSaveOpt = $_POST['saveOpt'];
        $userShareId  = $_POST['shareId'];
        $shareOption  = $_POST['shareOpt'];
        $shareUser    = $_POST['shareUser'];
        $shareEctUser = $_POST['shareEct'];
        $this->data = $this->Quotation->read(null, $id);
        $user = $this->getCurrentUser();
        if($this->data['Quotation']['created_by'] == $user['User']['id']){
            mysql_query("UPDATE quotations SET share_save_option = '".$shareSaveOpt."', user_share_id = '".$userShareId."', share_option = '".$shareOption."', share_user = '".$shareUser."', share_except_user = '".$shareEctUser."' WHERE id = ".$id);
            $this->Helper->saveUserActivity($user['User']['id'], 'Quotation', 'Save User Share', $id);
            echo MESSAGE_DATA_HAS_BEEN_SAVED;
        } else {
            $this->Helper->saveUserActivity($user['User']['id'], 'Quotation', 'Save User Share (Error)', $id);
            echo MESSAGE_DATA_COULD_NOT_BE_SAVED;
        }
        exit;
    }
    
    function history($quotationCode = null) {
        $this->layout = 'ajax';
        if(empty($quotationCode)) {
            exit;
        }
        $this->set(compact('quotationCode'));
    }
    
    function viewQuotationNoApprove(){
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        // Check Module Exist
        $sqlDash = mysql_query("SELECT id FROM user_dashboards WHERE module_id = 501 AND user_id = {$user['User']['id']} LIMIT 1");
        if(!mysql_num_rows($sqlDash)){
            $this->loadModel('UserDashboard');
            $userDash = array();
            $userDash['UserDashboard']['user_id']      = $user['User']['id'];
            $userDash['UserDashboard']['module_id']    = 501;
            $userDash['UserDashboard']['display']      = 1;
            $userDash['UserDashboard']['auto_refresh'] = 1;
            $userDash['UserDashboard']['time_refresh'] = 5;
            $this->UserDashboard->save($userDash);
        }
    }
    
    function productHistory($productId = null, $customerId = null) {
        $this->layout = 'ajax';
        if (!empty($productId)) {
            $user = $this->getCurrentUser();
            $this->Helper->saveUserActivity($user['User']['id'], 'Quotation', 'View Product History', '');
            $product = ClassRegistry::init('Product')->find("first", array('conditions' => array('Product.id' => $productId)));
            $this->set(compact('product', 'customerId'));
        } else {
            exit;
        }
    }
    
    function productHistoryAjax($productId = null, $customerId = null) {
        $this->layout = 'ajax';
        $this->set(compact('productId', 'customerId'));
    }

}

?>