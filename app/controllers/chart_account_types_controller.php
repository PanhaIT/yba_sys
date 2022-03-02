<?php

class ChartAccountTypesController extends AppController {

    var $name = 'ChartAccountTypes';
    var $components = array('Helper');

    function index() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        $this->Helper->saveUserActivity($user['User']['id'], 'Chart Account Type', 'Dashborad');
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
        $this->Helper->saveUserActivity($user['User']['id'], 'Chart Account Type', 'View', $id);
        $this->set('chartAccountType', $this->ChartAccountType->read(null, $id));
    }

    function status($id = null, $status = 0) {
        if (!$id) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $user = $this->getCurrentUser();
        $this->Helper->saveUserActivity($user['User']['id'], 'Chart Account Type', 'Change Status', $id);
        $this->ChartAccountType->updateAll(
                array('ChartAccountType.is_active' => $status),
                array('ChartAccountType.id' => $id)
        );
        echo MESSAGE_DATA_HAS_BEEN_SAVED;
        exit;
    }

}

?>