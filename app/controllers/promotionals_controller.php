<?php

class PromotionalsController extends AppController {

    var $name = 'Promotionals';
    var $components = array('Helper');

    function index() {
        $this->layout = 'ajax';
    }

    function ajax($status = 'all', $fromYear = 'all') {
        $this->layout = 'ajax';
        $this->set(compact('status','fromYear'));
    }

    function view($id = null) {
        $this->layout = 'ajax';
        if (!$id) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $this->data = $this->Promotional->read(null, $id);
        $promotionDetails = ClassRegistry::init('PromotionalDetail')->find('all', array("conditions" => array("PromotionalDetail.promotional_id" => $id)));
        $this->set(compact('promotionDetails'));
    }

    function add($clone= null) {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        if (!empty($this->data)) {
            $result['error'] = 0;
            if($this->data['Promotional']['date'] != '' && $this->data['Promotional']['start'] != '' && $this->data['Promotional']['end'] != '' && !empty($_POST['product_request_id'])){
                // Insert New Promotional
                $this->Promotional->create();
                if(!empty($this->data['Promotional']['branch_id'])){
                    $this->data['Promotional']['apply'] = 1;
                } else {
                    $this->data['Promotional']['branch_id'] = null;
                    $this->data['Promotional']['apply'] = 2;
                }
                $this->data['Promotional']['code'] = 'PRC';
                $this->data['Promotional']['promotion_type'] = $this->data['Promotional']['promotion_type'];
                $this->data['Promotional']['created_by'] = $user['User']['id'];
                $this->data['Promotional']['status']     = 1;
                if ($this->Promotional->save($this->data)) {
                    // Get Promotional Id
                    $promotionId = $this->Promotional->id;
                    // Get Module Code
                    $modCode = $this->Helper->getModuleCode($this->data['Promotional']['code'], $promotionId, 'code', 'promotionals', 'status != -1');
                    // Updaet Module Code
                    mysql_query("UPDATE promotionals SET code = '".$modCode."' WHERE id = ".$promotionId);
                    // Load 
                    $this->loadModel('PromotionalDetail');
                    // Insert Promotional Detail
                    if($this->data['Promotional']['promotion_type']==1){
                        for ($i = 0; $i < sizeof($_POST['product_request_id']); $i++) {
                            $this->PromotionalDetail->create();
                            $promotionDetail = array();
                            $promotionDetail['PromotionalDetail']['promotional_id']      = $promotionId;
                            $promotionDetail['PromotionalDetail']['product_request_id']  = $_POST['product_request_id'][$i];
                            $promotionDetail['PromotionalDetail']['qty_request']         = $_POST['qty_request'][$i];
                            $promotionDetail['PromotionalDetail']['uom_request']         = $_POST['uom_request'][$i];
    
                            $promotionDetail['PromotionalDetail']['product_promo_id']    = $_POST['product_promo_id'][$i];
                            $promotionDetail['PromotionalDetail']['qty_promo']           = $_POST['qty_promo'][$i];
                            $promotionDetail['PromotionalDetail']['uom_promo']           = $_POST['uom_promo'][$i];
    
                            $promotionDetail['PromotionalDetail']['discount_percent']    = $_POST['discount_percent'][$i];
                            $promotionDetail['PromotionalDetail']['discount_amount']     = $_POST['discount_amount'][$i];
                            $promotionDetail['PromotionalDetail']['unit_price']          = $_POST['price'][$i];
                            $this->PromotionalDetail->save($promotionDetail);
                        }
                    }else if($this->data['Promotional']['promotion_type']==2){
                        for ($i = 0; $i < sizeof($_POST['product_promo_id']); $i++) {
                            if(!empty($_POST['product_promo_id'][$i]) && $_POST['qty_promo'][$i]>0  && $_POST['price'][$i]>0){
                                $this->PromotionalDetail->create();
                                $promotionDetail = array();
                                $promotionDetail['PromotionalDetail']['promotional_id']      = $promotionId;
                                $promotionDetail['PromotionalDetail']['product_promo_id']    = $_POST['product_promo_id'][$i];
                                $promotionDetail['PromotionalDetail']['qty_promo']           = $_POST['qty_promo'][$i];
                                $promotionDetail['PromotionalDetail']['uom_promo']           = $_POST['uom_promo'][$i];
                                $promotionDetail['PromotionalDetail']['unit_price']          = $_POST['price'][$i];
                                $this->PromotionalDetail->save($promotionDetail);
                            }
                        }
                    }else if($this->data['Promotional']['promotion_type']==3){
                        for ($i = 0; $i < sizeof($_POST['discount_percent']); $i++) {
                            if($_POST['discount_percent'][$i]>0  && $_POST['price'][$i]>0){
                                $this->PromotionalDetail->create();
                                $promotionDetail = array();
                                $promotionDetail['PromotionalDetail']['promotional_id']      = $promotionId;
                                $promotionDetail['PromotionalDetail']['unit_price']          = $_POST['price'][$i];
                                $promotionDetail['PromotionalDetail']['discount_percent']    = $_POST['discount_percent'][$i];
                                $this->PromotionalDetail->save($promotionDetail);
                            }
                        }
                    }
                    $result['id'] = $promotionId;
                    echo json_encode($result);
                    exit;
                } else {
                    $result['error'] = 1;
                    echo json_encode($result);
                    exit;
                }
            } else {
                $result['error'] = 2;
                echo json_encode($result);
                exit;
            }
        }
        if(!empty($clone)){
            $this->data = $this->Promotional->read(null, $clone);
            $promotionDetails = ClassRegistry::init('PromotionalDetail')->find('all', array("conditions" => array("PromotionalDetail.promotional_id" => $clone)));
            $this->set(compact("promotionDetails"));
        }
        $promotionTypes = ClassRegistry::init('PromotionType')->find('list', array('order' => 'id', 'conditions' => array('PromotionType.is_active' => 1)));
        $branches  = ClassRegistry::init('Branch')->find('list', array('joins' => array(array('table' => 'user_branches', 'type' => 'inner', 'conditions' => array('user_branches.branch_id=Branch.id'))), 'fields' => array('Branch.id', 'Branch.name'), 'conditions' => array('Branch.is_active = 1', 'user_branches.user_id=' . $user['User']['id'])));
        $this->set(compact("branches","promotionTypes"));
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
            if($this->data['Promotional']['date'] != '' && $this->data['Promotional']['start'] != '' && $this->data['Promotional']['end'] != '' && !empty($_POST['product_request_id'])){
                $promotional = $this->Promotional->read(null, $id);
                // Update Status Promotional Edit
                $this->Promotional->updateAll(
                        array('Promotional.status' => "-1", "modified_by"=>$user['User']['id']), array('Promotional.id' => $id)
                );
                // Insert New Promotional
                $this->Promotional->create();
                if(!empty($this->data['Promotional']['branch_id'])){
                    $this->data['Promotional']['apply'] = 1;
                } else {
                    $this->data['Promotional']['branch_id'] = null;
                    $this->data['Promotional']['apply'] = 2;
                }
                $this->data['Promotional']['code']       = $promotional['Promotional']['code'];
                $this->data['Promotional']['created_by'] = $promotional['Promotional']['created_by'];
                $this->data['Promotional']['edited']     = date("Y-m-d H:i:s");
                $this->data['Promotional']['edited_by']  = $user['User']['id'];
                $this->data['Promotional']['status']     = 1;
                if ($this->Promotional->save($this->data)) {
                    // Get Promotional Id
                    $promotionId = $this->Promotional->id;
                    // Load Model
                    $this->loadModel('PromotionalDetail');
                    // Insert Promotional Detail
                    for ($i = 0; $i < sizeof($_POST['product_request_id']); $i++) {
                        $this->PromotionalDetail->create();
                        $promotionDetail = array();
                        $promotionDetail['PromotionalDetail']['promotional_id']      = $promotionId;
                        $promotionDetail['PromotionalDetail']['product_request_id']  = $_POST['product_request_id'][$i];
                        $promotionDetail['PromotionalDetail']['qty_request']         = $_POST['qty_request'][$i];
                        $promotionDetail['PromotionalDetail']['uom_request']         = $_POST['uom_request'][$i];

                        $promotionDetail['PromotionalDetail']['product_promo_id']    = $_POST['product_promo_id'][$i];
                        $promotionDetail['PromotionalDetail']['qty_promo']           = $_POST['qty_promo'][$i];
                        $promotionDetail['PromotionalDetail']['uom_promo']           = $_POST['uom_promo'][$i];

                        $promotionDetail['PromotionalDetail']['discount_percent']    = $_POST['discount_percent'][$i];
                        $promotionDetail['PromotionalDetail']['discount_amount']     = $_POST['discount_amount'][$i];
                        $promotionDetail['PromotionalDetail']['unit_price']          = $_POST['price'][$i];
                        $this->PromotionalDetail->save($promotionDetail);
                    }
                    $result['id'] = $promotionId;
                    echo json_encode($result);
                    exit;
                } else {
                    $result['error'] = 1;
                    echo json_encode($result);
                    exit;
                }
            } else {
                $result['error'] = 1;
                echo json_encode($result);
                exit;
            }
        }
        if (empty($this->data)) {
            $this->data = $this->Promotional->read(null, $id);
            $promotionTypes = ClassRegistry::init('PromotionType')->find('list', array('order' => 'id', 'conditions' => array('PromotionType.is_active' => 1)));
            $promotionDetails = ClassRegistry::init('PromotionalDetail')->find('all', array("conditions" => array("PromotionalDetail.promotional_id" => $id)));
            $branches  = ClassRegistry::init('Branch')->find('list', array('joins' => array(array('table' => 'user_branches', 'type' => 'inner', 'conditions' => array('user_branches.branch_id=Branch.id'))), 'fields' => array('Branch.id', 'Branch.name'), 'conditions' => array('Branch.is_active = 1', 'user_branches.user_id=' . $user['User']['id'])));
            $this->set(compact("promotionDetails", "branches","promotionTypes"));
        }
    }

    function delete($id = null) {
        if (!$id) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $user = $this->getCurrentUser();
        mysql_query("UPDATE `promotionals` SET `status` = 0, `modified`='".date("Y-m-d H:i:s")."', `modified_by`=".$user['User']['id']." WHERE `id`=".$id.";");
        echo MESSAGE_DATA_HAS_BEEN_DELETED;
        exit;
    }

    function approve($id = null, $status = 2) {
        if (!$id) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $approve = 2;
        if($status == 1){
            $approve = -3;
        }
        $user = $this->getCurrentUser();
        mysql_query("UPDATE `promotionals` SET `status` = ".$approve.", `approved`='".date("Y-m-d H:i:s")."', `approved_by`=".$user['User']['id']." WHERE `id`=".$id.";");
        echo MESSAGE_DATA_HAS_BEEN_SAVED;
        exit;
    }
    
    function searchProduct(){
        $this->layout = 'ajax';
        $products = ClassRegistry::init('Product')->find('all', array(
                        'conditions' => array('OR' => array(
                                'Product.name LIKE' => '%' . $this->params['url']['q'] . '%',
                                'Product.barcode LIKE' => '%' . $this->params['url']['q'] . '%',
                                'Product.code LIKE' => '%' . $this->params['url']['q'] . '%'
                            ), 'Product.is_active' => 1
                        ),
                        'limit' => $this->params['url']['limit']
                    ));
        if (!empty($products)) {
            foreach ($products as $product) {
                echo "{$product['Product']['id']}.*{$product['Product']['code']}.*{$product['Product']['name']}.*{$product['Product']['price_uom_id']}\n";
            }
        } else {
            echo '';
        }
        exit;
    }

    function getRelativeUom($uomId = null, $uomSku = 'all', $productId = null, $branchId = null) {
        $this->layout = 'ajax';
        $this->set(compact('uomId', 'uomSku', 'productId', 'branchId'));
    }
    
    function cancel($id = null) {
        if (!$id) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $user = $this->getCurrentUser();
        mysql_query("UPDATE `promotionals` SET `status` = -2, `modified`='".date("Y-m-d H:i:s")."', `modified_by`=".$user['User']['id']." WHERE `id`=".$id.";");
        echo MESSAGE_DATA_HAS_BEEN_SAVED;
        exit;
    }

}

?>