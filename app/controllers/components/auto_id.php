<?php

/**
 * Description of Helper
 *
 * @author UDAYA
 */
App::import('model', 'ExtendAppModel');

class AutoIdComponent extends Object {

    function generateAutoCode($table, $field, $len, $char, $year = 1, $status = 'is_active = 1') {
        $db = ConnectionManager::getDataSource('default');
        mysql_select_db($db->config['database']);
        $con = '';
        if($year == 1){
            $year =  date('y');
        }else{
            $year = "";
        }
        if($status != ''){
            if($table == 'shifts'){
                $status = "status > 0";
            }
            $con = ' AND '.$status;
        }
        $queryAutoId = mysql_query("SELECT COUNT(" . $field . ") FROM " . $table . " WHERE " . $field . " LIKE '" . $year . $char . "%'{$con}");
        $dataAutoId = mysql_fetch_array($queryAutoId);
        return $year . $char . str_pad($dataAutoId[0] + 1, $len, '0', STR_PAD_LEFT);
    }

    function generateAutoCodeTwo($table, $field, $len, $char, $year = 1, $status = 'is_active = 1') {
        $db = ConnectionManager::getDataSource('default');
        mysql_select_db($db->config['database']);
        $con = '';
        if($year == 1){
            $year =  date('y');
        }else{
            $year = "";
        }
        if($status != ''){
            if($table == 'shifts'){
                $status = "status > 0";
            }
            $con = ' AND '.$status;
        }
        $queryAutoId = mysql_query("SELECT COUNT(" . $field . ") FROM " . $table . " WHERE " . $field . " LIKE '" . $year . $char."%'{$con}");
        $dataAutoId = mysql_fetch_array($queryAutoId);
        return $year . $char . str_pad($dataAutoId[0] + 1, $len, '0', STR_PAD_LEFT);
    }
    
    function moduleGenerateCode($modCode, $modId, $field, $table, $con, $length = 7){
        $db = ConnectionManager::getDataSource('default');
        mysql_select_db($db->config['database']);
        // Replace Year
        if($table != 'chart_accounts'){
            $year    = date("y");
            $codeRp  = str_replace($year,"",$modCode);
            $newCode = $codeRp.date("ym")."-";
        } else {
            $newCode = $modCode;
        }
        $sqCode  = mysql_query("SELECT CONCAT('".$newCode."','',LPAD(((SELECT count(tmp.".$field.") FROM `".$table."` as tmp WHERE tmp.id < ".$modId." AND ".$con." AND tmp.".$field." LIKE '".$newCode."%') + 1),".$length.",'0'));");
        $code    = mysql_fetch_array($sqCode);
        return $code[0];
    }

}

?>