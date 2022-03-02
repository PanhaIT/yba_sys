<?php

class ShiftsController extends AppController {

    var $name = 'Shifts';
    var $components = array('Helper');

    function index() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        $this->Helper->saveUserActivity($user['User']['id'], 'Shift', 'Dashboard');
    }

    function ajax() {
        $this->layout = 'ajax';
    }
}

?>