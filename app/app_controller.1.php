<?php

// GZip components
ob_start("ob_gzhandler");

class AppController extends Controller {

    var $helpers = array('Html', 'Form', 'Javascript', 'Session');
    var $components = array('Session');
    var $menu = array();
    
    function menu() {
        $this->menu = array(
            array('text' => MENU_DASHBOARD, 'url' => '/dashboards/index', 'target' => 'ajax'),
            array('text' => MENU_INVENTORY_MANAGEMENT, 'url' => '', 'target' => '',
                'submenu' => array(
                    array('text' => MENU_PRODUCT_SERVICE, 'url' => '/products/index', 'target' => 'ajax'),
                    array('text' => MENU_VENDOR, 'url' => '/vendors/index', 'target' => 'ajax')
                    // array('text' => MENU_INVENTORY_ADJUSTMENT, 'url' => '/inv_adjs/index', 'target' => 'ajax'),
                    // array('text' => MENU_SALES_MIX, 'url' => '/inventory_physicals/index', 'target' => 'ajax'),
                    // array('text' => MENU_TRANSFER_ORDER_MANAGEMENT, 'url' => '/transfer_orders/index', 'target' => 'ajax')
            )),
            array('text' => MENU_PURCHASING_MANAGEMENT, 'url' => '', 'target' => '',
                'submenu' => array(
                    // array('text' => MENU_PURCHASE_REQUEST_MANAGEMENT, 'url' => '/purchase_requests/index', 'target' => 'ajax'),
                    array('text' => MENU_PURCHASE_ORDER_MANAGEMENT, 'url' => '/purchase_bills/index', 'target' => 'ajax'),
                    // array('text' => MENU_PAY_BILLS, 'url' => '/pay_bills/index', 'target' => 'ajax'),
                    // array('text' => MENU_PURCHASE_RETURN_MANAGEMENT, 'url' => '/purchase_returns/index', 'target' => 'ajax'),
                    // array('text' => MENU_LANDED_COST, 'url' => '/landing_costs/index', 'target' => 'ajax'),
                    // array('text' => MENU_EXPENSE, 'url' => '/expenses/index', 'target' => 'ajax'),
                    // array('text' => MENU_VENDOR, 'url' => '/vendors/index', 'target' => 'ajax')
                )
            ),
            array('text' => MENU_SALES_MANAGEMENT, 'url' => '', 'target' => '',
                'submenu' => array(
                    // array('text' => MENU_QUOTATION, 'url' => 'quotations/index', 'target' => 'ajax'),
                    // array('text' => MENU_ORDER, 'url' => '/orders/index', 'target' => 'ajax'),
                    // array('text' => MENU_SALES_ORDER_MANAGEMENT, 'url' => '/sales_orders/index', 'target' => 'ajax'),
                    // array('text' => MENU_DELIVERY_MANAGEMENT, 'url' => '/deliveries/index', 'target' => 'ajax'),
                    // array('text' => MENU_PICK_SLIP, 'url' => '/pick_slips/index', 'target' => 'ajax'),
                    // array('text' => MENU_RECEIVE_PAYMENTS, 'url' => '/receive_payments/index', 'target' => 'ajax'),
                    // array('text' => MENU_CREDIT_MEMO_MANAGEMENT, 'url' => '/credit_memos/index', 'target' => 'ajax'),
                    // array('text' => MENU_OTHER_INCOME, 'url' => '/other_incomes/index', 'target' => 'ajax'),
                    array('text' => MENU_CUSTOMER_MANAGEMENT, 'url' => '/customers/index', 'target' => 'ajax'),
                    array('text' => MENU_DISCOUNT_PROMOTION, 'url' => '/promotionals/index', 'target' => 'ajax'),
                    array('text' => MENU_PROMOTIONAL_POINT, 'url' => '/promotional_points/index', 'target' => 'ajax')
                )
            ),
            array('text' => MENU_POS, 'url' => '/point_of_sales/add', 'target' => 'blank',
                // 'submenu' => array(
                //     array('text' => MENU_POS_CUSTOMER_DISPLAY, 'url' => '/point_of_sales/customerDisplay', 'target' => 'blank'),
                //     array('text' => MENU_SHIFT_CONTROL, 'url' => '/shifts/index', 'target' => 'ajax'),
                //     array('text' => MENU_SHIFT_COLLECT_SHIFT, 'url' => '/shift_collects/index', 'target' => 'ajax')
                // )
            ),
            array('text' => MENU_TODO_LIST, 'url' => '/todo_lists/add', 'target' => 'blank'),
            array('text' => MENU_REPORT, 'url' => '', 'target' => '',
                'submenu' => array(
                    // array('text' => MENU_REPORT_CUSTOMER_BALANCE, 'url' => '', 'target' => '',
                    //     'submenu' => array(
                    //         array('text' => MENU_ACCOUNT_RECEIVABLE, 'url' => '/reports/accountReceivable', 'target' => 'ajax'),
                    //         array('text' => MENU_REPORT_CUSTOMER_BALANCE, 'url' => '/reports/customerBalance', 'target' => 'ajax'),
                    //         array('text' => MENU_REPORT_OPEN_INVOICE, 'url' => '/reports/openInvoiceByRep', 'target' => 'ajax'),
                    //         array('text' => MENU_REPORT_STATEMENT, 'url' => '/reports/statement', 'target' => 'ajax')
                    //     )
                    // ),
                    // array('text' => MENU_REPORT_SALES_TRANSACTION, 'url' => '', 'target' => '',
                    //     'submenu' => array(                            
                    //         array('text' => MENU_REPORT_SALES_ORDER_BY_ITEM, 'url' => '/reports/salesByItem', 'target' => 'ajax'),
                    //         array('text' => MENU_REPORT_SALES_ORDER_BY_CUSTOMER, 'url' => '/reports/salesByCustomer', 'target' => 'ajax'),
                    //         array('text' => MENU_REPORT_SALES_BY_REP, 'url' => '/reports/salesBySalesman', 'target' => 'ajax'),
                    //         array('text' => MENU_SALES_TOP_ITEM, 'url' => '/reports/salesTopItem', 'target' => 'ajax'),
                    //         array('text' => MENU_SALES_TOP_CUSTOMER, 'url' => '/reports/salesTopCustomer', 'target' => 'ajax'),
                    //         array('text' => MENU_REPORT_ITEM_BY_LAST_SOLD_DATE, 'url' => '/reports/salesByItemLast', 'target' => 'ajax'),
                    //         array('text' => MENU_REPORT_INVOICE, 'url' => '/reports/invoice', 'target' => 'ajax'),
                    //         array('text' => MENU_REPORT_INVOICE." (Summary)", 'url' => '/reports/totalSaleSummary', 'target' => 'ajax'),
                    //         array('text' => MENU_REPORT_POS, 'url' => '/reports/pos', 'target' => 'ajax'),
                    //         array('text' => MENU_REPORT_SALES_ORDER_INVOICE, 'url' => '/reports/customerInvoice', 'target' => 'ajax'),
                    //         array('text' => MENU_REPORT_CREDIT_MEMO_INVOICE, 'url' => '/reports/customerInvoiceCredit', 'target' => 'ajax'),
                    //         array('text' => MENU_REPORT_DISCOUNT_SUMMARY, 'url' => '/reports/customerDiscount', 'target' => 'ajax'),
                    //         array('text' => MENU_REPORT_RECEIVE_PAYMENTS, 'url' => '/reports/customerReceivePayment', 'target' => 'ajax'),
                    //         array('text' => MENU_REPORT_SHIFT_CONTROL, 'url' => '/reports/posShiftControl', 'target' => 'ajax'),
                    //         array('text' => MENU_REPORT_COLLECT_SHIFT_BY_USER, 'url' => 'reports/posCollectShiftByUser', 'target' => 'ajax'),
                    //         array('text' => MENU_SHIFT_CONTROL, 'url' => '/shifts/index', 'target' => 'ajax'),
                    //     )
                    // ),
                    // array('text' => MENU_REPORT_VENDOR_BALANCE, 'url' => '', 'target' => '',
                    //     'submenu' => array(
                    //         array('text' => MENU_ACCOUNT_PAYABLE, 'url' => '/reports/accountPayable', 'target' => 'ajax'),
                    //         array('text' => MENU_REPORT_VENDOR_BALANCE, 'url' => '/reports/vendorBalance', 'target' => 'ajax'),
                    //         array('text' => MENU_REPORT_OPEN_BILL, 'url' => '/reports/openBill', 'target' => 'ajax')
                    //     )
                    // ),
                    // array('text' => MENU_REPORT_PURCHASE_TRANSACTION, 'url' => '', 'target' => '',
                    //     'submenu' => array(
                    //         array('text' => MENU_PURCHASE_INVOICE, 'url' => '/reports/purchaseInvoice', 'target' => 'ajax'),
                    //         array('text' => MENU_REPORT_PURCHASE_TOP_BOTTOM_VENDOR, 'url' => '/reports/purchaseTopVendor', 'target' => 'ajax'),
                    //         array('text' => MENU_REPORT_TOTAL_PURCHASE, 'url' => '/reports/totalPurchaseSummary', 'target' => 'ajax'),
                    //         array('text' => MENU_PURCHASE_BY_ITEM, 'url' => '/reports/purchaseByItem', 'target' => 'ajax'),
                    //         array('text' => MENU_PURCHASE_INVOICE_CREDIT, 'url' => '/reports/purchaseInvoiceCredit', 'target' => 'ajax'),
                    //         array('text' => MENU_REPORT_PAY_BILLS, 'url' => '/reports/vendorPayBill', 'target' => 'ajax')
                    //     )
                    // ),
                    array('text' => MENU_REPORT_STOCK_INVENTORY, 'url' => '', 'target' => '',
                        'submenu' => array(
                            // array('text' => MENU_PRODUCT_INVENTORY, 'url' => '/reports/product', 'target' => 'ajax'),
                            array('text' => MENU_PRODUCT_INVENTORY_ACTIVITY, 'url' => '/reports/inventoryActivity', 'target' => 'ajax'),
                            // array('text' => MENU_PRODUCT_INVENTORY_VALUATION, 'url' => '/reports/inventoryValuation', 'target' => 'ajax'),
                            // array('text' => MENU_PRODUCT_INVENTORY_ADJUSTMENT, 'url' => '/reports/inventoryAdjustment', 'target' => 'ajax'),
                            // array('text' => MENU_PRODUCT_INVENTORY_ADJUSTMENT_BY_ITEM, 'url' => '/reports/inventoryAdjustmentByItem', 'target' => 'ajax'),
                            // array('text' => MENU_REPORT_TRANSFER_ORDER, 'url' => '/reports/transferOrder', 'target' => 'ajax'),
                            // array('text' => MENU_REPORT_TRANSFER_ORDER_BY_ITEM, 'url' => '/reports/transferByItem', 'target' => 'ajax'),
                        )
                    ),
                    // array('text' => MENU_REPORT_FINANCIAL, 'url' => '', 'target' => '',
                    //     'submenu' => array(
                    //         array('text' => MENU_PROFIT_AND_LOSS, 'url' => '/reports/profitLoss', 'target' => 'ajax'),
                    //     )
                    // ),
                    // array('text' => MENU_REPORT_LIST, 'url' => '', 'target' => '',
                    //     'submenu' => array(
                    //         array('text' => MENU_PRODUCT_AVERAGE_COST, 'url' => '/reports/productAverageCost', 'target' => 'ajax'),
                    //         array('text' => MENU_PRODUCT_PRICE, 'url' => '/reports/productPrice', 'target' => 'ajax'),
                    //         array('text' => REPORT_PRODUCT_LIST, 'url' => '/reports/productList', 'target' => 'ajax'),
                    //         array('text' => REPORT_CUSTOMER_LIST, 'url' => '/reports/customerList', 'target' => 'ajax'),
                    //         array('text' => REPORT_VENDOR_LIST, 'url' => '/reports/vendorList', 'target' => 'ajax'),
                    //     )
                    // ),
                    // array('text' => MENU_USERS, 'url' => '', 'target' => '',
                    //     'submenu' => array(
                    //         array('text' => MENU_USER_RIGHTS, 'url' => '/reports/userRights', 'target' => 'ajax'),
                    //         array('text' => MENU_USER_LOG, 'url' => '/reports/userLog', 'target' => 'ajax')
                    //     )
                    // )
                )
            ),
            array('text' => MENU_SYSTEM_SETTINGS, 'url' => '/settings/config', 'target' => 'ajax')
        );
    }

    function beforeFilter() {
        // Check Default Configuration
        $this->Helper->checkDefaultConfig();
        /**
         *  set default language
         */
        if (!$this->Session->check('lang')) {
            $this->Session->write('lang', 'en');
        }
        require_once('../../app/webroot/lang/' . $this->Session->read('lang') . '.php');

        /**
         * define path
         */
        require_once('../../app/webroot/path.php');

        /**
         * Access Rules
         */
        $accessRules = array();
        $queryGroup = mysql_query("SELECT id FROM groups WHERE is_active=1");
        while ($dataGroup = mysql_fetch_array($queryGroup)) {
            $permission  = null;
            $queryModule = mysql_query("SELECT module_id FROM permissions WHERE group_id=" . $dataGroup[0]);
            while ($dataModule   = mysql_fetch_array($queryModule)) {
                $queryPermission = mysql_query("SELECT module_details.controllers, module_details.views FROM module_details INNER JOIN modules ON modules.id = module_details.module_id AND modules.id=" . $dataModule[0] . " AND modules.status = 1 ORDER BY controllers");
                $firstControllerName = "";
                while ($dataPermission = mysql_fetch_array($queryPermission)) {
                    $firstControllerName = $dataPermission['controllers'];
                    if ($firstControllerName != $dataPermission['controllers']) {
                        $permission[$dataPermission['controllers']] = array($dataPermission['views']);
                    } else {
                        $permission[$dataPermission['controllers']][] = $dataPermission['views'];
                    }
                }
            }
            $accessRules[$dataGroup[0]] = $permission;
        }
        $_SESSION['accessRules'] = $accessRules;

        $this->menu();

        if ($this->params['controller'] != 'users' || ($this->params['controller'] == 'users' && !in_array($this->params['action'], array('lang', 'checkDuplicate', 'checkDuplicate2', 'login', 'logout', 'profile', 'backup', 'smartcode', 'silentOps', 'silentOps2', 'checkInvAdj', 'approveInvAdj', 'addToDetail', 'checkStatusTo', 'receiveToAll', 'checkReceiveAllTO', 'deliveryStock', 'checkDnPickUp', 'deliveryPos', 'approveInventoryPhysical', 'systemConfig', 'sync', 'connection')))) {
            if ($this->checkAccess() == false) {
                echo "No Authentication";
                exit();
            }
        }
    }

    function afterFilter() {
        
    }

    function checkAccess($controller = null, $action = null) {
        if (!$controller) {
            $controller = $this->params['controller'];
        }
        if (!$action) {
            $action = $this->params['action'];
        }

        $users = $this->getCurrentUser();
        if (!$users) {
            $this->redirect('/users/login');
        } else {
            // Check Session
            $sqlCheckSession = mysql_query("SELECT id FROM users WHERE id = ".$users['User']['id']." AND session_id = '".$this->Session->id(session_id())."'");
            if(mysql_num_rows($sqlCheckSession)){
                // Update Session Active
                mysql_query("UPDATE users SET session_active= '".date("Y-m-d H:i:s")."' WHERE id = ".$users['User']['id']." AND session_id = '".$this->Session->id(session_id())."'");
            } else {
                $this->Session->destroy();
                $this->redirect('/users/login');
            }
            $this->set('user', $users);
            $this->set('menu', $this->menu);
        }

        $accessRules = $_SESSION['accessRules'];
        $queryUserGroup = mysql_query("SELECT group_id FROM user_groups WHERE user_id=" . $users['User']['id']);
        while ($dataUserGroup = mysql_fetch_array($queryUserGroup)) {
            if (!empty($accessRules[$dataUserGroup['group_id']][$controller]) && (is_array($accessRules[$dataUserGroup['group_id']][$controller]) && in_array($action, $accessRules[$dataUserGroup['group_id']][$controller]))) {
                return true;
            }
        }
        return false;
    }

    function getDefaultPage($userId = null) {
        if (!empty($this->menu) && count($this->menu) > 0) {
            if(!empty($userId)){
                $sqlModule = mysql_query("SELECT GROUP_CONCAT(name) FROM module_types WHERE id IN (SELECT module_type_id FROM modules WHERE id IN (SELECT module_id FROM permissions WHERE group_id IN (SELECT group_id FROM user_groups WHERE user_id = ".$userId.")))");
                $rowModule = mysql_fetch_array($sqlModule);
                if($rowModule[0] == 'Dashboard,Todo List'){//Point Of Sales,
                    // return array('controller' => 'point_of_sales', 'action' => 'add');
                    return array('controller' => 'todo_lists', 'action' => 'add');
                } else {
                    $place = explode('/', $this->menu[0]['url']);
                    return array('controller' => $place[0], 'action' => $place[1] . '/' . $place[2]);
                }
            } else {
                $place = explode('/', $this->menu[0]['url']);
                return array('controller' => $place[0], 'action' => $place[1] . '/' . $place[2]);
            }
        } else {
            return array('controller' => 'users', 'action' => 'logout');
        }
    }

    /**
     * Read user object from session
     */
    function getCurrentUser() {
        if ($this->Session->check('User')) {
            return $this->Session->read('User');
        } else {
            return false;
        }
    }
    
    /**
     * Read user object from session
     */
    function getSecurityCode(){
        if ($this->Session->check('Security')) {
            return $this->Session->read('Security');
        } else {
            return false;
        }
    }
    

    /**
     * Write user object into session when login
     */
    function setCurrentUser($user) {
        $this->Session->write('User', $user);
        $this->Session->write('Security', '');
        
    }

}

?>