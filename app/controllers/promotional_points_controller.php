<?php
// include('includes/function.php');
class PromotionalPointsController extends AppController {

    var $name = 'PromotionalPoints';
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
        $this->data = $this->PromotionalPoint->read(null, $id);
        $promotionPointDetails = ClassRegistry::init('PromotionalPointDetail')->find('all', array("conditions" => array("PromotionalPointDetail.promotional_point_id" => $id)));
        $this->set(compact('promotionPointDetails'));
    }

    function add($clone= null) {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        $this->loadModel('PromotionalPointDetail');
        if (!empty($this->data)) {
            if($this->data['PromotionalPoint']['date'] != '' && $this->data['PromotionalPoint']['start'] != '' && $this->data['PromotionalPoint']['end'] != ''){
                // Insert New Promotional
                $this->PromotionalPoint->create();
                if(!empty($this->data['PromotionalPoint']['branch_id'])){
                    $this->data['PromotionalPoint']['apply'] = 1;
                } else {
                    $this->data['PromotionalPoint']['branch_id'] = null;
                    $this->data['PromotionalPoint']['apply']     = 2;
                }
                $this->data['PromotionalPoint']['total_point']     = $this->data['PromotionalPoint']['total_point'];
                $this->data['PromotionalPoint']['point_in_dollar'] = $this->data['PromotionalPoint']['point_in_dollar'];
                $this->data['PromotionalPoint']['code']       = 'PRP';
                $this->data['PromotionalPoint']['pgroup_id']  = $this->data['PromotionalPoint']['pgroup_id'];
                $this->data['PromotionalPoint']['created_by'] = $user['User']['id'];
                $this->data['PromotionalPoint']['status']     = 1;
                if ($this->PromotionalPoint->save($this->data)) {
                    // Get Promotional Id
                    $promotionPointId = $this->PromotionalPoint->id;
                    // Get Module Code
                    $modCode = $this->Helper->getModuleCode($this->data['PromotionalPoint']['code'], $promotionPointId, 'code', 'promotional_points', 'branch_id="'.$this->data['PromotionalPoint']['branch_id'].'" AND status != -1');
                    // Update Module Code
                    mysql_query("UPDATE promotional_points SET code = '".$modCode."' WHERE id = ".$promotionPointId);
                    // product group
                    if(!empty($this->data['PromotionalPoint']['product_id'])){
                        mysql_query("UPDATE `promotional_points` SET `is_apply_item`=1 WHERE id='".$promotionPointId."'");
                        for($i=0;$i<sizeof($this->data['PromotionalPoint']['product_id']);$i++){
                            mysql_query("INSERT INTO promotional_point_details(promotional_point_id,product_request_id) VALUES ('".$promotionPointId."','".$this->data['PromotionalPoint']['product_id'][$i]."') ");
                        }
                    }else{
                        mysql_query("UPDATE `promotional_points` SET `is_apply_item`=0 WHERE id='".$promotionPointId."'");
                    }
                    // mysql_query("TRUNCATE TABLE `promotional_pgroup_tmps`;");
                    $result['id'] = $promotionPointId;
                    $result['error'] = 0;
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
            $this->data = $this->PromotionalPoint->read(null, $clone);
            $promotionPointDetails = ClassRegistry::init('PromotionalPointDetail')->find('all', array("conditions" => array("PromotionalPointDetail.promotional_point_id" => $clone)));
            $this->set(compact("promotionPointDetails"));
        }
        $conditionUser='';
        $pgroups = ClassRegistry::init('Pgroup')->find('list', array('order' => 'id', 'conditions' => array('Pgroup.is_active' => 1, $conditionUser)));
        $branches  = ClassRegistry::init('Branch')->find('list', array('joins' => array(array('table' => 'user_branches', 'type' => 'inner', 'conditions' => array('user_branches.branch_id=Branch.id'))), 'fields' => array('Branch.id', 'Branch.name'), 'conditions' => array('Branch.is_active = 1', 'user_branches.user_id=' . $user['User']['id'])));
        $this->set(compact("branches","pgroups"));
    }

    function edit($id = null) {
        $this->layout = 'ajax';
        if (!$id && empty($this->data)) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $user = $this->getCurrentUser();
        if (!empty($this->data)) {
            if($this->data['PromotionalPoint']['date'] != '' && $this->data['PromotionalPoint']['start'] != '' && $this->data['PromotionalPoint']['end'] != ''){
                $promotionalPoint = $this->PromotionalPoint->read(null, $id);
                // Update Status Promotional Edit
                $this->PromotionalPoint->updateAll(
                    array('PromotionalPoint.status' => "-1", "modified_by"=>$user['User']['id']), array('PromotionalPoint.id' => $id)
                );
                // Insert New Promotional
                $this->PromotionalPoint->create();
                if(!empty($this->data['PromotionalPoint']['branch_id'])){
                    $this->data['PromotionalPoint']['apply'] = 1;
                } else {
                    $this->data['PromotionalPoint']['branch_id'] = null;
                    $this->data['PromotionalPoint']['apply'] = 2;
                }
                $this->data['PromotionalPoint']['total_point']     = $this->data['PromotionalPoint']['total_point'];
                $this->data['PromotionalPoint']['point_in_dollar'] = $this->data['PromotionalPoint']['point_in_dollar'];
                $this->data['PromotionalPoint']['pgroup_id']  = $this->data['PromotionalPoint']['pgroup_id'];
                $this->data['PromotionalPoint']['code']       = $promotionalPoint['PromotionalPoint']['code'];
                $this->data['PromotionalPoint']['created_by'] = $promotionalPoint['PromotionalPoint']['created_by'];
                $this->data['PromotionalPoint']['edited']     = date("Y-m-d H:i:s");
                $this->data['PromotionalPoint']['edited_by']  = $user['User']['id'];
                $this->data['PromotionalPoint']['status']     = 1;
                if ($this->PromotionalPoint->save($this->data)) {
                    // Get Promotional Id
                    $promotionPointId = $this->PromotionalPoint->id;
                    // product group
                    if(!empty($this->data['PromotionalPoint']['product_id'])){
                        mysql_query("UPDATE `promotional_points` SET `is_apply_item`=1 WHERE id='".$promotionPointId."'");
                        for($i=0;$i<sizeof($this->data['PromotionalPoint']['product_id']);$i++){
                            mysql_query("INSERT INTO promotional_point_details(promotional_point_id,product_request_id) VALUES ('".$promotionPointId."','".$this->data['PromotionalPoint']['product_id'][$i]."') ");
                        }
                    }else{
                        mysql_query("UPDATE `promotional_points` SET `is_apply_item`=0 WHERE id='".$promotionPointId."'");
                    }
                    // mysql_query("TRUNCATE TABLE `promotional_pgroup_tmps`;");
                    $result['id'] = $promotionPointId;
                    $result['error'] = 0;
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
        if (empty($this->data)) {
            $this->data = $this->PromotionalPoint->read(null, $id);
            $conditionUser='';
            $pgroups = ClassRegistry::init('Pgroup')->find('list', array('order' => 'id', 'conditions' => array('Pgroup.is_active' => 1, $conditionUser)));
            $pgroupsSellecteds = ClassRegistry::init('PromotionalPointPgroup')->find('list', array('fields' => array('PromotionalPointPgroup.id', 'PromotionalPointPgroup.pgroup_id'), 'order' => 'PromotionalPointPgroup.id', 'conditions' => array('PromotionalPointPgroup.promotional_point_id' => $id)));
            $pgroupsSellected = array();
            foreach ($pgroupsSellecteds as $pg) {
                array_push($pgroupsSellected, $pg);
            }
            $promotionPointDetails = ClassRegistry::init('PromotionalPointDetail')->find('all', array("conditions" => array("PromotionalPointDetail.promotional_point_id" => $id)));
            $branches  = ClassRegistry::init('Branch')->find('list', array('joins' => array(array('table' => 'user_branches', 'type' => 'inner', 'conditions' => array('user_branches.branch_id=Branch.id'))), 'fields' => array('Branch.id', 'Branch.name'), 'conditions' => array('Branch.is_active = 1', 'user_branches.user_id=' . $user['User']['id'])));
            $this->set(compact("promotionPointDetails", "branches","pgroups","pgroupsSellected"));
        }
    }

    function delete($id = null) {
        if (!$id) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $user = $this->getCurrentUser();
        mysql_query("UPDATE `promotional_points` SET `status` = 0, `modified`='".date("Y-m-d H:i:s")."', `modified_by`=".$user['User']['id']." WHERE `id`=".$id.";");
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
        mysql_query("UPDATE `promotional_points` SET `status` = ".$approve.", `approved`='".date("Y-m-d H:i:s")."', `approved_by`=".$user['User']['id']." WHERE `id`=".$id.";");
        echo MESSAGE_DATA_HAS_BEEN_SAVED;
        exit;
    }
    
    function cancel($id = null) {
        if (!$id) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $user = $this->getCurrentUser();
        mysql_query("UPDATE `promotional_points` SET `status` = -2, `modified`='".date("Y-m-d H:i:s")."', `modified_by`=".$user['User']['id']." WHERE `id`=".$id.";");
        echo MESSAGE_DATA_HAS_BEEN_SAVED;
        exit;
    }

    function searchProduct() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        $pgroupPermission = '';
        // if($pgroupId!='all'){
        //     $pgroupPermission = 'Product.id IN (SELECT product_id FROM product_pgroups WHERE pgroup_id ='.$pgroupId.')';
        // }
        $products = ClassRegistry::init('Product')->find('all', array(
                    'conditions' => array('OR' => array(
                            'Product.name LIKE' => '%' . $this->params['url']['q'] . '%',
                            'Product.code LIKE ' => '%' . $this->params['url']['q'] . '%',), 'Product.is_active' => 1, $pgroupPermission)));
        $this->set(compact('products'));
    }

    function product($branchId = 'all',$pgroupId = 'all') {
        $this->layout = 'ajax';
        $this->set(compact('branchId','pgroupId'));
    }

    function productAjax($branchId = 'all',$pgroupId = 'all') {
        $this->layout = 'ajax';
        $this->set(compact('branchId','pgroupId'));
    }

    function checkPgroup($pgroupId=null,$isCheck=null){
        $this->layout = 'ajax';
        $result=array();
        if($isCheck==1){
            if($pgroupId!='all'){
                $sqlProPgorup=mysql_query("SELECT pgroup_id FROM promotional_pgroup_tmps WHERE pgroup_id='".$pgroupId."' ");
                if(mysql_num_rows($sqlProPgorup)){
                    $result['old_pgroup_id']=0;
                    $result['check']=0;
                }else{
                    $sqlProPgorup=mysql_query("SELECT pgroup_id FROM promotional_pgroup_tmps WHERE pgroup_id IS NOT NULL ");
                    $rowPgroup=mysql_fetch_array($sqlProPgorup);
                    $result['old_pgroup_id']=$rowPgroup[0];
                    $result['check']=1;
                }
            }
        }else if($isCheck==0){
            $result['old_pgroup_id']="";
            $result['check']="";
            if($pgroupId>0){
                mysql_query("TRUNCATE TABLE `promotional_pgroup_tmps`;");
                mysql_query("INSERT INTO `promotional_pgroup_tmps`(`pgroup_id`) VALUES ('".$pgroupId."')");
            }
        }else{
            mysql_query("TRUNCATE TABLE `promotional_pgroup_tmps`;");
        }
        echo json_encode($result);
        exit;
    }

    function checkDuplicateStartEndDate($id) {
        $this->layout = 'ajax';
        $start = DATE("Y-m-d", strtotime($_POST['start']));
        $end   = DATE("Y-m-d", strtotime($_POST['end']));
        if($start != '' && $end != ''){
            $compareId = "";
            if ($id>0) {
                $compareId = " AND pp.id <> " . $id;
            }
            $sqlCheckDate=mysql_query("SELECT pp.start,pp.end FROM promotional_points pp WHERE pp.status>0 AND pp.start='".$start."' AND pp.end='".$end."' ".$compareId." LIMIT 01");
            if(mysql_num_rows($sqlCheckDate)){
                $result = 'available';
            }else{
                $result = 'not available';
            }
            echo $result;
        }else{
            echo "Error Date";
        }
        exit;
    }
}

?>