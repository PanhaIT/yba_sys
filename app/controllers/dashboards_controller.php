<?php

class DashboardsController extends AppController {

    var $uses = array('User');
    var $components = array('Helper');

    function index() {
        $user = $this->getCurrentUser();
        $branches = ClassRegistry::init('Branch')->find('all', array('joins' => array(array('table' => 'user_branches', 'type' => 'inner', 'conditions' => array('user_branches.branch_id=Branch.id'))),'conditions' => array('Branch.is_active = 1', 'user_branches.user_id=' . $user['User']['id'])));
        $this->set(compact('branches'));
    }
    
    // function share(){
    //     $this->layout = 'ajax';
    //     $shareOpt  = $_POST['option'];
    //     $shareUser = $_POST['user'];
    //     $shareEcpt = $_POST['except'];
    //     $saveOpt   = $_POST['save'];
    //     if(empty($shareOpt)){
    //         echo MESSAGE_DATA_INVALID;
    //         exit;
    //     }
    //     $this->set(compact('shareOpt', 'shareUser', 'shareEcpt', 'saveOpt'));
    // }
    
    // function shareSave(){
    //     $this->layout = 'ajax';
    //     $moduleTypeId = $_POST['mtid'];
    //     $shareOption  = $_POST['sp'];
    //     $shareUser    = $_POST['susr'];
    //     $shareExcept  = $_POST['suect'];
    //     if($moduleTypeId != '' && $shareOption != ''){
    //         $user = $this->getCurrentUser();
    //         // Disabled Old Share
    //         mysql_query("UPDATE user_shares SET is_active = 2 WHERE user_id = ".$user['User']['id']." AND module_type_id = ".$moduleTypeId);
    //         $this->loadModel('UserShare');
    //         $userShare = array();
    //         $userShare['UserShare']['user_id'] = $user['User']['id'];
    //         $userShare['UserShare']['module_type_id'] = $moduleTypeId;
    //         $userShare['UserShare']['share_option']   = $shareOption;
    //         $userShare['UserShare']['share_users']    = $shareUser;
    //         $userShare['UserShare']['share_except_users'] = $shareExcept;
    //         $this->UserShare->save($userShare);
    //         echo $this->UserShare->id;
    //     } else {
    //         echo '0';
    //     }
    //     exit;
    // }
    
    // function userDashboard($controller = null, $module = null, $auto = 1, $time = 1, $display = 1){
    //     $this->layout = 'ajax';
    //     $msg = array();
    //     if(($auto == 1 || $auto == 2) && ($display == 1 || $display == 2)  && !empty($controller) && !empty($module) && !empty($time)){
    //         $dateTime = date("Y-m-d H:i:s");
    //         $user = $this->getCurrentUser();
    //         $sqlMod = mysql_query("SELECT module_id FROM module_details WHERE controllers = '{$controller}' AND views = '{$module}' LIMIT 1");
    //         $rowMod = mysql_fetch_array($sqlMod);
    //         // Check Module Exist
    //         $sqlDash = mysql_query("SELECT id FROM user_dashboards WHERE module_id = {$rowMod[0]} AND user_id = {$user['User']['id']} LIMIT 1");
    //         if(mysql_num_rows($sqlDash)){
    //             $rowDash = mysql_fetch_array($sqlDash);
    //             mysql_query("UPDATE user_dashboards SET auto_refresh = {$auto}, time_refresh = {$time}, display = {$display}, modified = '{$dateTime}'  WHERE id = ".$rowDash['id']);
    //         } else {
    //             $this->loadModel('UserDashboard');
    //             $userDash = array();
    //             $userDash['UserDashboard']['user_id'] = $user['User']['id'];
    //             $userDash['UserDashboard']['module_id']    = $rowMod[0];
    //             $userDash['UserDashboard']['display']      = $display;
    //             $userDash['UserDashboard']['auto_refresh'] = $auto;
    //             $userDash['UserDashboard']['time_refresh'] = $time;
    //             $this->UserDashboard->save($userDash);
    //         }
    //         $msg['result'] = 1;
    //     } else {
    //         $msg['result'] = 0;
    //     }
    //     echo json_encode($msg);
    //     exit;
    // }
    
    function getProductCache(){
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        $joinProductBranch = array('table' => 'product_branches', 'type' => 'INNER', 'alias' => 'ProductBranch', 'conditions' => array('ProductBranch.product_id = Product.id', 'ProductBranch.branch_id IN (SELECT branch_id FROM user_branches WHERE user_id = '.$user['User']['id'].')'));
        $joinProductgroup  = array('table' => 'product_pgroups', 'type' => 'INNER', 'alias' => 'ProductPgroup', 'conditions' => array('ProductPgroup.product_id = Product.id'));
        $joinPgroup = array('table' => 'pgroups', 'type' => 'INNER', 'alias' => 'Pgroup', 'conditions' => array('Pgroup.id = ProductPgroup.pgroup_id', '(Pgroup.user_apply = 0 OR (Pgroup.user_apply = 1 AND Pgroup.id IN (SELECT pgroup_id FROM user_pgroups WHERE user_id = '.$user['User']['id'].')))'));
        $joinUom    = array('table' => 'uoms', 'type' => 'INNER', 'alias' => 'Uom','conditions' => array('Uom.id = Product.price_uom_id'));
        $joins      = array($joinProductgroup, $joinPgroup, $joinProductBranch, $joinUom);
        $products   = ClassRegistry::init('Product')->find('all', array(
                        'fields' => array('Product.id', 'Product.code', 'Product.barcode', 'Product.name', 'Product.photo', 'Uom.id', 'Uom.abbr'),
                        'conditions' => array('Product.company_id IN (SELECT company_id FROM user_companies WHERE user_id = '.$user['User']['id'].')', 'Product.is_active' => 1, '(Product.price_uom_id IS NOT NULL AND Product.is_packet = 0)'),
                        'joins' => $joins,
                        'group' => array('Product.id')));
        $result = array();
        $i = 0;
        foreach($products AS $product){
            $sqlBranch = mysql_query("SELECT GROUP_CONCAT(branch_id) FROM product_branches WHERE product_id = ".$product['Product']['id']." AND branch_id IN (SELECT branch_id FROM user_branches WHERE user_id = ".$user['User']['id'].")");
            $rowBranch = mysql_fetch_array($sqlBranch);
            $photo = "img/button/no-images.png";
            if($product['Product']['photo'] != ""){
                $photo = "public/product_photo/".$product['Product']['photo'];
            }
            // Price
            $priceTypeId = '';
            $sqlPType = mysql_query("SELECT price_type_id FROM pos_price_types WHERE company_id = 1 AND is_active = 1 LIMIT 1;");
            if(mysql_num_rows($sqlPType)){
                $rowPType = mysql_fetch_array($sqlPType);
                $priceTypeId = $rowPType[0];
            }
            $price = 0;
            $sqlPrice = mysql_query("SELECT products.unit_cost, product_prices.price_type_id, product_prices.amount, product_prices.percent, product_prices.add_on, product_prices.set_type FROM product_prices INNER JOIN products ON products.id = product_prices.product_id WHERE product_prices.product_id =".$product['Product']['id']." AND price_type_id = ".$priceTypeId." AND product_prices.branch_id IN (SELECT branch_id FROM user_branches WHERE user_id = ".$user['User']['id'].") AND product_prices.uom_id =".$product['Uom']['id']." LIMIT 1");
            if(mysql_num_rows($sqlPrice)){
                while($rowPrice = mysql_fetch_array($sqlPrice)){
                    $unitCost = $this->Helper->replaceThousand(number_format($rowPrice['unit_cost'] /  1, 2));
                    if($rowPrice['set_type'] == 1){
                        $price = $rowPrice['amount'];
                    }else if($rowPrice['set_type'] == 2){
                        $percent = ($unitCost * $rowPrice['percent']) / 100;
                        $price = $unitCost + $percent;
                    }else if($rowPrice['set_type'] == 3){
                        $price = $unitCost + $rowPrice['add_on'];
                    }
                }
            }
            $result['Product'][$i]["branch_id"]  = $rowBranch[0];
            $result['Product'][$i]["sku"]        = $product['Product']['code'];
            $result['Product'][$i]["upc"]        = $product['Product']['barcode'];
            $result['Product'][$i]["name"]       = $product['Product']['name'];
            $result['Product'][$i]["uom"]        = $product['Uom']['abbr'];
            $result['Product'][$i]["uom_id"]     = $product['Uom']['id'];
            $result['Product'][$i]["price"]      = $price;
            $result['Product'][$i]["icon"]       = $photo;
            $i++;
            $productSku = mysql_query("SELECT sku, uoms.abbr, uom_id FROM product_with_skus INNER JOIN uoms ON uoms.id = product_with_skus.uom_id WHERE product_id = '".$product['Product']['id']."'");
            while($rowSku = mysql_fetch_array($productSku)){
                $price = 0;
                $sqlPrice = mysql_query("SELECT products.unit_cost, product_prices.price_type_id, product_prices.amount, product_prices.percent, product_prices.add_on, product_prices.set_type FROM product_prices INNER JOIN products ON products.id = product_prices.product_id WHERE product_prices.product_id =".$product['Product']['id']." AND price_type_id = ".$priceTypeId." AND product_prices.branch_id IN (SELECT branch_id FROM user_branches WHERE user_id = ".$user['User']['id'].") AND product_prices.uom_id =".$rowSku['uom_id']." LIMIT 1");
                if(mysql_num_rows($sqlPrice)){
                    while($rowPrice = mysql_fetch_array($sqlPrice)){
                        $unitCost = $this->Helper->replaceThousand(number_format($rowPrice['unit_cost'] /  1, 2));
                        if($rowPrice['set_type'] == 1){
                            $price = $rowPrice['amount'];
                        }else if($rowPrice['set_type'] == 2){
                            $percent = ($unitCost * $rowPrice['percent']) / 100;
                            $price = $unitCost + $percent;
                        }else if($rowPrice['set_type'] == 3){
                            $price = $unitCost + $rowPrice['add_on'];
                        }
                    }
                }
                $result['Product'][$i]["branch_id"]  = $rowBranch[0];
                $result['Product'][$i]["sku"]        = $rowSku['sku'];
                $result['Product'][$i]["upc"]        = $product['Product']['barcode'];
                $result['Product'][$i]["name"]       = $product['Product']['name'];
                $result['Product'][$i]["uom"]        = $rowSku['abbr'];
                $result['Product'][$i]["uom_id"]     = $rowSku['uom_id'];
                $result['Product'][$i]["price"]      = $price;
                $result['Product'][$i]["icon"]       = $photo;
                $i++;
            }
        }
        $sqlCache = mysql_query("SELECT * FROM cache_datas WHERE type = 'Products' LIMIT 1");
        $rowCache = mysql_fetch_array($sqlCache);
        $result['modified'] = $rowCache['modified'];
        echo json_encode($result);
        exit();
    }
    
    function viewTotalSales($branch = 'all', $dateRange = null, $group = null, $chart = null){
        $this->layout = 'ajax';
        if(empty($dateRange) || empty($group) || empty($chart)){
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $user = $this->getCurrentUser();
        // Module Id 
        $sqlMod = mysql_query("SELECT id FROM modules WHERE name = 'Total Sales By Graph' LIMIT 1");
        $rowMod = mysql_fetch_array($sqlMod);
        // Check Module Exist
        $sqlDash = mysql_query("SELECT id FROM user_dashboards WHERE module_id = ".$rowMod[0]." AND user_id = {$user['User']['id']} LIMIT 1");
        if(!mysql_num_rows($sqlDash)){
            $this->loadModel('UserDashboard');
            $userDash = array();
            $userDash['UserDashboard']['user_id']      = $user['User']['id'];
            $userDash['UserDashboard']['module_id']    = $rowMod[0];
            $userDash['UserDashboard']['display']      = 1;
            $userDash['UserDashboard']['auto_refresh'] = 1;
            $userDash['UserDashboard']['time_refresh'] = 30;
            $this->UserDashboard->save($userDash);
        }
        $this->set(compact('branch', 'dateRange', 'group', 'chart'));
    }
    
    function viewExpenseGraph($branch = 'all', $dateRange = null){
        $this->layout = 'ajax';
        if(empty($dateRange)){
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $user = $this->getCurrentUser();
        // Module Id 
        $sqlMod = mysql_query("SELECT id FROM modules WHERE name = 'Expense (Graph)' LIMIT 1");
        $rowMod = mysql_fetch_array($sqlMod);
        // Check Module Exist
        $sqlDash = mysql_query("SELECT id FROM user_dashboards WHERE module_id = ".$rowMod[0]." AND user_id = {$user['User']['id']} LIMIT 1");
        if(!mysql_num_rows($sqlDash)){
            $this->loadModel('UserDashboard');
            $userDash = array();
            $userDash['UserDashboard']['user_id']      = $user['User']['id'];
            $userDash['UserDashboard']['module_id']    = $rowMod[0];
            $userDash['UserDashboard']['display']      = 1;
            $userDash['UserDashboard']['auto_refresh'] = 1;
            $userDash['UserDashboard']['time_refresh'] = 30;
            $this->UserDashboard->save($userDash);
        }
        $this->set(compact('branch', 'dateRange'));
    }
    
    function viewSalesTop10Graph($branch = 'all', $dateRange = null){
        $this->layout = 'ajax';
        if(empty($dateRange)){
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $user = $this->getCurrentUser();
        // Module Id 
        $sqlMod = mysql_query("SELECT id FROM modules WHERE name = 'Sales Top 10 Items (Graph)' LIMIT 1");
        $rowMod = mysql_fetch_array($sqlMod);
        // Check Module Exist
        $sqlDash = mysql_query("SELECT id FROM user_dashboards WHERE module_id = ".$rowMod[0]." AND user_id = {$user['User']['id']} LIMIT 1");
        if(!mysql_num_rows($sqlDash)){
            $this->loadModel('UserDashboard');
            $userDash = array();
            $userDash['UserDashboard']['user_id']      = $user['User']['id'];
            $userDash['UserDashboard']['module_id']    = $rowMod[0];
            $userDash['UserDashboard']['display']      = 1;
            $userDash['UserDashboard']['auto_refresh'] = 1;
            $userDash['UserDashboard']['time_refresh'] = 30;
            $this->UserDashboard->save($userDash);
        }
        $this->set(compact('branch', 'dateRange'));
    }
    
    function viewProfitLoss($branch = 'all'){
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        // Module Id 
        $sqlMod = mysql_query("SELECT id FROM modules WHERE name = 'Profit & Loss (Graph)' LIMIT 1");
        $rowMod = mysql_fetch_array($sqlMod);
        // Check Module Exist
        $sqlDash = mysql_query("SELECT id FROM user_dashboards WHERE module_id = ".$rowMod[0]." AND user_id = {$user['User']['id']} LIMIT 1");
        if(!mysql_num_rows($sqlDash)){
            $this->loadModel('UserDashboard');
            $userDash = array();
            $userDash['UserDashboard']['user_id']      = $user['User']['id'];
            $userDash['UserDashboard']['module_id']    = $rowMod[0];
            $userDash['UserDashboard']['display']      = 1;
            $userDash['UserDashboard']['auto_refresh'] = 1;
            $userDash['UserDashboard']['time_refresh'] = 30;
            $this->UserDashboard->save($userDash);
        }
        $this->set(compact('branch'));
    }
    
    function viewReceivable($branch = 'all'){
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        // Module Id 
        $sqlMod = mysql_query("SELECT id FROM modules WHERE name = 'Total Receivables' LIMIT 1");
        $rowMod = mysql_fetch_array($sqlMod);
        // Check Module Exist
        $sqlDash = mysql_query("SELECT id FROM user_dashboards WHERE module_id = ".$rowMod[0]." AND user_id = {$user['User']['id']} LIMIT 1");
        if(!mysql_num_rows($sqlDash)){
            $this->loadModel('UserDashboard');
            $userDash = array();
            $userDash['UserDashboard']['user_id']      = $user['User']['id'];
            $userDash['UserDashboard']['module_id']    = $rowMod[0];
            $userDash['UserDashboard']['display']      = 1;
            $userDash['UserDashboard']['auto_refresh'] = 1;
            $userDash['UserDashboard']['time_refresh'] = 30;
            $this->UserDashboard->save($userDash);
        }
        $this->set(compact('branch'));
    }
    
    function viewPayable($branch = 'all'){
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        // Module Id 
        $sqlMod = mysql_query("SELECT id FROM modules WHERE name = 'Total Payables' LIMIT 1");
        $rowMod = mysql_fetch_array($sqlMod);
        // Check Module Exist
        $sqlDash = mysql_query("SELECT id FROM user_dashboards WHERE module_id = ".$rowMod[0]." AND user_id = {$user['User']['id']} LIMIT 1");
        if(!mysql_num_rows($sqlDash)){
            $this->loadModel('UserDashboard');
            $userDash = array();
            $userDash['UserDashboard']['user_id']      = $user['User']['id'];
            $userDash['UserDashboard']['module_id']    = $rowMod[0];
            $userDash['UserDashboard']['display']      = 1;
            $userDash['UserDashboard']['auto_refresh'] = 1;
            $userDash['UserDashboard']['time_refresh'] = 30;
            $this->UserDashboard->save($userDash);
        }
        $this->set(compact('branch'));
    }
    
    function viewReorderLevel($warehouse = 'all'){
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        // Module Id 
        $sqlMod = mysql_query("SELECT id FROM modules WHERE name = 'Product Reorder Level' LIMIT 1");
        $rowMod = mysql_fetch_array($sqlMod);
        // Check Module Exist
        $sqlDash = mysql_query("SELECT id FROM user_dashboards WHERE module_id = ".$rowMod[0]." AND user_id = {$user['User']['id']} LIMIT 1");
        if(!mysql_num_rows($sqlDash)){
            $this->loadModel('UserDashboard');
            $userDash = array();
            $userDash['UserDashboard']['user_id']      = $user['User']['id'];
            $userDash['UserDashboard']['module_id']    = $rowMod[0];
            $userDash['UserDashboard']['display']      = 1;
            $userDash['UserDashboard']['auto_refresh'] = 1;
            $userDash['UserDashboard']['time_refresh'] = 30;
            $this->UserDashboard->save($userDash);
        }
        $this->set(compact('warehouse'));
    }
    
    function viewReorderLevelAjax($warehouse = 'all'){
        $this->layout = 'ajax';
        $this->set(compact('warehouse'));
    }
    
    function viewCustomerPaymentAlert($branch = 'all'){
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        // Module Id 
        $sqlMod = mysql_query("SELECT id FROM modules WHERE name = 'Customer Payment Alert' LIMIT 1");
        $rowMod = mysql_fetch_array($sqlMod);
        // Check Module Exist
        $sqlDash = mysql_query("SELECT id FROM user_dashboards WHERE module_id = ".$rowMod[0]." AND user_id = {$user['User']['id']} LIMIT 1");
        if(!mysql_num_rows($sqlDash)){
            $this->loadModel('UserDashboard');
            $userDash = array();
            $userDash['UserDashboard']['user_id']      = $user['User']['id'];
            $userDash['UserDashboard']['module_id']    = $rowMod[0];
            $userDash['UserDashboard']['display']      = 1;
            $userDash['UserDashboard']['auto_refresh'] = 1;
            $userDash['UserDashboard']['time_refresh'] = 30;
            $this->UserDashboard->save($userDash);
        }
        $this->set(compact('branch'));
    }
    
    function refreshMenu(){
        $this->layout = 'ajax';
    }

}

?>