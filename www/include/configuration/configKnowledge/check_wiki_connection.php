<?php

$sql_host = $_POST['host'];
$sql_user = $_POST['user'];
$sql_pwd = $_POST['pwd'];
$sql_name = $_POST['name'];

try {
    $dbh = new PDO('mysql:host=' . $sql_host . ';dbname=' . $sql_name, $sql_user, $sql_pwd);
    die(json_encode(array('outcome' => true)));
} catch (PDOException $ex) {
    die(json_encode(array('outcome' => false, 'message' => $ex->getMessage())));
}
