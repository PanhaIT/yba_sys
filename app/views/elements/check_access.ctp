<?php

if (!function_exists('checkAccess')) {

    function checkAccess($userId = null, $controller = null, $action = null) {
        if (!$controller) {
            $controller = $this->params['controller'];
        }
        if (!$action) {
            $action = $this->params['action'];
        }
        $accessRules    = $_SESSION['accessRules'];
        $queryUserGroup = mysql_query("SELECT group_id FROM user_groups WHERE user_id=" . $userId);
        while ($dataUserGroup = mysql_fetch_array($queryUserGroup)) {
            if (!empty($accessRules[$dataUserGroup['group_id']][$controller]) && (is_array($accessRules[$dataUserGroup['group_id']][$controller]) && in_array($action, $accessRules[$dataUserGroup['group_id']][$controller]))) {
                return true;
            }
        }
        return false;
    }
}
?>