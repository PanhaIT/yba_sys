<?php

class WarehouseMapsController extends AppController {

    var $uses = array('User');
    var $components = array('Helper');

    function index() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        $companies = ClassRegistry::init('Company')->find('list',
                        array(
                            'joins' => array(
                                array('table' => 'user_companies', 'type' => 'inner', 'conditions' => array('user_companies.company_id=Company.id')
                                )
                            ),
                            'conditions' => array('Company.is_active = 1', 'user_companies.user_id=' . $user['User']['id'])
                        )
        );
        $this->set(compact('companies'));
        $this->set('user',$user);
    }
    
    function viewLocationDetail($locationGroupId = null){
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        if (!$locationGroupId) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        // Get Loction Setting
        $locSetting = ClassRegistry::init('LocationSetting')->findById(1);
        $locCon     = '';
        if($locSetting['LocationSetting']['location_status'] == 1){
            $locCon = ' AND Location.is_for_sale = 0';
        }
        $queryLocationGroup = mysql_query("SELECT name FROM location_groups WHERE id = '".$locationGroupId."'");
        $dataLocationGroup  = mysql_fetch_array($queryLocationGroup);
        $locationGroupName  = $dataLocationGroup[0];
        $locations  = ClassRegistry::init('Location')->find('all', array('joins' => array(array('table' => 'user_locations', 'type' => 'inner', 'conditions' => array('user_locations.location_id=Location.id'))), 'conditions' => array('user_locations.user_id=' . $user['User']['id'] . ' AND Location.location_group_id = '.$locationGroupId.' AND Location.is_active=1'.$locCon), 'order' => 'Location.name'));
        $this->set(compact("locations", "locationGroupName"));
    }
    
    function viewProductWarehouse($locationGroupId = null) {
        $this->layout = 'ajax';
        if (!$locationGroupId) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $user = $this->getCurrentUser();
        $this->Helper->saveUserActivity($user['User']['id'], 'Location', 'View Product Warehouse', $locationGroupId);
        $this->set(compact("locationGroupId"));
    }
    
    function viewProductWarehouseAjax($locationGroupId = null, $category = null) {
        $this->layout = 'ajax';
        if (!$locationGroupId) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $this->set(compact("locationGroupId", "category"));
    }
    
    function viewProductLocation($locationId = null) {
        $this->layout = 'ajax';
        if (!$locationId) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $user = $this->getCurrentUser();
        $this->Helper->saveUserActivity($user['User']['id'], 'Location', 'View Product Location', $locationId);
        $this->set(compact("locationId"));
    }
    
    function viewProductLocationAjax($locationId = null, $category = null) {
        $this->layout = 'ajax';
        if (!$locationId) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $this->set(compact("locationId", "category"));
    }
}

?>