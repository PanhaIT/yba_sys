<?php

foreach ($_POST as $key => $value) {
    if (!is_array($value)) {
        $_POST[$key] = trim($value);
    }
}

function getNameById($id, $tableName) {
    $str = mysql_query("SELECT name FROM $tableName WHERE id = '$id' LIMIT 1");
    $name = '';
    if (mysql_num_rows($str) > 0) {
        $row = mysql_fetch_array($str);
        $name = $row[0];
    }
    return $name;
}

function getUserNameById($id) {
    $str = mysql_query("SELECT username FROM users WHERE id = '$id' LIMIT 1");
    $username = '';
    if (mysql_num_rows($str) > 0) {
        $row = mysql_fetch_array($str);
        $username = $row[0];
    }
    return $username;
}

?>