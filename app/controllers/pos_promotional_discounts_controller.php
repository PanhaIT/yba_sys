<?php

class PosPromotionalDiscountsController extends AppController {

    var $name = 'PosPromotionalDiscounts';
    var $components = array('Helper');

    function index() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        // User Activity
        $this->Helper->saveUserActivity($user['User']['id'], 'Pos Promotional Discount', 'Dashboard');
    }

    function ajax() {
        $this->layout = 'ajax';
    }

    function view($id = null) {
        $this->layout = 'ajax';
        if (!$id) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $user = $this->getCurrentUser();
        // User Activity
        $this->Helper->saveUserActivity($user['User']['id'], 'Pos Promotional Discount', 'View', $id);
        $this->data = $this->PosPromotionalDiscount->read(null, $id);
    }

    function add() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        if (!empty($this->data)) {
            if ($this->Helper->checkDouplicate('branch_id', 'pos_promotional_discounts', $this->data['PosPromotionalDiscount']['branch_id'], 'is_active = 1 AND branch_id = '.$this->data['PosPromotionalDiscount']['branch_id'])) {
                // User Activity
                $this->Helper->saveUserActivity($user['User']['id'], 'Pos Promotional Discount', 'Save Add New (Branch Has Existed)');
                echo MESSAGE_DATA_ALREADY_EXISTS_IN_THE_SYSTEM;
                exit;
            }
            $r = 0;
            $restCode = array();
            $dateNow  = date("Y-m-d H:i:s");
            $this->PosPromotionalDiscount->create();
            $this->data['PosPromotionalDiscount']['sys_code']   = md5(rand().strtotime(date("Y-m-d H:i:s")).$user['User']['id']);
            $this->data['PosPromotionalDiscount']['branch_id'] = $this->data['PosPromotionalDiscount']['branch_id'];
            $this->data['PosPromotionalDiscount']['created']    = $dateNow;
            $this->data['PosPromotionalDiscount']['created_by'] = $user['User']['id'];
            $this->data['PosPromotionalDiscount']['is_active']  = 1;
            if ($this->PosPromotionalDiscount->save($this->data)) {
                $saveId = $this->PosPromotionalDiscount->id;
                if (!empty($this->data['PosPromotionalDiscount']['promotion_type_id'])) {
                    for ($i = 0; $i < sizeof($this->data['PosPromotionalDiscount']['promotion_type_id']); $i++) {
                        mysql_query("INSERT INTO promotype_branches (pos_promotional_discount_id, promotion_type_id) VALUES ('" . $saveId . "', '" . $this->data['PosPromotionalDiscount']['promotion_type_id'][$i] . "')");
                    }
                }
                // Save File Send
                $this->Helper->sendFileToSync($restCode, 0, 0);
                // Save User Activity
                $this->Helper->saveUserActivity($user['User']['id'], 'Pos Promotional Discount', 'Save Add New', $saveId);
                echo MESSAGE_DATA_HAS_BEEN_SAVED;
                exit;
            } else {
                // User Activity
                $this->Helper->saveUserActivity($user['User']['id'], 'Pos Promotional Discount', 'Save Add New (Error)');
                echo MESSAGE_DATA_COULD_NOT_BE_SAVED;
                exit;
            }
        }
        // User Activity
        $this->Helper->saveUserActivity($user['User']['id'], 'Pos Promotional Discount', 'Add New');
        $promotionTypes = ClassRegistry::init('PromotionType')->find('list', array('order' => 'id', 'conditions' => array('PromotionType.is_active' => 1)));
        $branches = ClassRegistry::init('Branch')->find('all',
            array(
                'joins' => array(array('table' => 'user_branches', 'type' => 'inner', 'conditions' => array('user_branches.branch_id=Branch.id'))),
                'conditions' => array('Branch.is_active = 1 AND Branch.id NOT IN(SELECT branch_id FROM pos_promotional_discounts WHERE is_active=1)', 'user_branches.user_id=' . $user['User']['id']),
                'group' => array('Branch.id')
            ));
        $this->set(compact('branches','promotionTypes'));
    }

    function edit($id = null) {
        $this->layout = 'ajax';
        if (!$id && empty($this->data)) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $user = $this->getCurrentUser();
        if (!empty($this->data)) {
            if ($this->Helper->checkDouplicateEdit('branch_id', 'pos_promotional_discounts', $id, $this->data['PosPromotionalDiscount']['branch_id'], 'is_active = 1 AND branch_id = '.$this->data['PosPromotionalDiscount']['branch_id'])) {
                // User Activity
                $this->Helper->saveUserActivity($user['User']['id'], 'Pos Promotional Discount', 'Save Edit (Currency Has Existed)', $id);
                echo MESSAGE_DATA_ALREADY_EXISTS_IN_THE_SYSTEM;
                exit;
            }
            $r = 0;
            $dateNow  = date("Y-m-d H:i:s");
            $this->data['PosPromotionalDiscount']['modified'] = $dateNow;
            $this->data['PosPromotionalDiscount']['modified_by'] = $user['User']['id'];
            $this->data['PosPromotionalDiscount']['branch_id'] = $this->data['PosPromotionalDiscount']['branch_id'];
            if ($this->PosPromotionalDiscount->save($this->data)) {
                mysql_query("DELETE FROM promotype_branches WHERE pos_promotional_discount_id=" . $id);
                if (!empty($this->data['PosPromotionalDiscount']['promotion_type_id'])) {
                    for ($i = 0; $i < sizeof($this->data['PosPromotionalDiscount']['promotion_type_id']); $i++) {
                        mysql_query("INSERT INTO promotype_branches (pos_promotional_discount_id, promotion_type_id) VALUES ('" . $id . "', '" . $this->data['PosPromotionalDiscount']['promotion_type_id'][$i] . "')");
                    }
                }
                // Save User Activity
                $this->Helper->saveUserActivity($user['User']['id'], 'Pos Promotional Discount', 'Save Edit', $id);
                echo MESSAGE_DATA_HAS_BEEN_SAVED;
                exit;
            } else {
                // User Activity
                $this->Helper->saveUserActivity($user['User']['id'], 'Pos Promotional Discount', 'Save Edit (Error)', $id);
                echo MESSAGE_DATA_COULD_NOT_BE_SAVED;
                exit;
            }
        }
        // User Activity
        $this->Helper->saveUserActivity($user['User']['id'], 'Pos Promotional Discount', 'Edit', $id);
        $this->data = $this->PosPromotionalDiscount->read(null, $id);
        $promotionTypes = ClassRegistry::init('PromotionType')->find('list', array('order' => 'id', 'conditions' => array('PromotionType.is_active' => 1)));
        $promotionTypesSellecteds = ClassRegistry::init('PromotypeBranche')->find('list', array('fields' => array('PromotypeBranche.id', 'PromotypeBranche.promotion_type_id'), 'order' => 'PromotypeBranche.id', 'conditions' => array('PromotypeBranche.pos_promotional_discount_id' => $id)));
        $promotionTypesSellected = array();
        foreach ($promotionTypesSellecteds as $pt) {
            array_push($promotionTypesSellected, $pt);
        }
        $branches = ClassRegistry::init('Branch')->find('all',
            array(
                'joins' => array(array('table' => 'user_branches', 'type' => 'inner', 'conditions' => array('user_branches.branch_id=Branch.id'))),
                'conditions' => array('Branch.is_active = 1 AND Branch.id NOT IN(SELECT branch_id FROM pos_promotional_discounts WHERE is_active=1 AND branch_id<> "'.$this->data['PosPromotionalDiscount']['branch_id'].'")', 'user_branches.user_id=' . $user['User']['id']),
                'group' => array('Branch.id')
            ));
        $this->set(compact('branches','promotionTypes','promotionTypesSellected'));
    }

    function delete($id = null) {
        if (!$id) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $dateNow  = date("Y-m-d H:i:s");
        $user = $this->getCurrentUser();
        $this->data = $this->PosPromotionalDiscount->read(null, $id);
        mysql_query("UPDATE `pos_promotional_discounts` SET `is_active`=2, `modified`='".$dateNow."', `modified_by`=".$user['User']['id']." WHERE `id`=".$id.";");
        // Save User Activity
        $this->Helper->saveUserActivity($user['User']['id'], 'Pos Promotional Discount', 'Delete', $id);
        echo MESSAGE_DATA_HAS_BEEN_DELETED;
        exit;
    }
}

?>