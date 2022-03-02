<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Helper
 *
 * @author UDAYA
 */
App::import('model', 'ChartAccountGroup');

class CoaComponent extends Object {

    var $components = array('Helper');

    function chartAccountGroupList() {
        $chartAccountGroupModel = new ChartAccountGroup();
        $chartAccountGroupLists = $chartAccountGroupModel->find("all", array("conditions" => array("ChartAccountGroup.is_active = 1"), 'order' => array('ChartAccountGroup.chart_account_type_id')));

        $options = array();
        foreach ($chartAccountGroupLists as $chartAccountGroupList) {
            $arr = array('value' => $chartAccountGroupList['ChartAccountGroup']['id'], 'name' => $chartAccountGroupList['ChartAccountGroup']['name'], 'class' => $chartAccountGroupList['ChartAccountGroup']['chart_account_type_id']);
            array_push($options, $arr);
        }
        return $options;
    }

}

?>