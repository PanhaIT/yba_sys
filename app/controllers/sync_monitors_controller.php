<?php

class SyncMonitorsController extends AppController {

    var $uses = array('User');
    var $components = array('Helper');

    function index() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        // User Activity
        $this->Helper->saveUserActivity($user['User']['id'], 'SYNC Monitor', 'Dashboard');
    }
    
    function checkConnection(){
        $this->layout = 'ajax';
        $curl  = curl_init();
        curl_setopt($curl, CURLOPT_URL, "https://dst2xvw0t.udaya.asia:8281");
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl, CURLOPT_NOSIGNAL, 1);
        curl_setopt($curl, CURLOPT_TIMEOUT_MS, 1000);
        curl_exec($curl);
        $curl_errno = curl_errno($curl);
        curl_close ($curl);
        if ($curl_errno > 0) {
            $return = 0;
        } else {
            $return = 1;
        }
        echo $return;
        exit;
    }

}

?>