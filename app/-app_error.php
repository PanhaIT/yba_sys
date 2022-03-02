<?php

class AppError extends ErrorHandler {
    function error404($params) {
        $this->controller->redirect(array('controller'=>'users', 'action'=>'login'));
    }
    function missingController($params) {
        $this->controller->redirect(array('controller'=>'users', 'action'=>'login'));
    }
    function missingAction($params) {
        $this->controller->redirect(array('controller'=>'users', 'action'=>'login'));
    }
}

?>