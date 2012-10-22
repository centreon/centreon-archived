<?php
session_start();

require_once '../functions.php';

foreach ($_POST as $key => $value) {
    $_SESSION[$key] = $value;
}

$mandatoryFields = array('CONFIGURATION_DB', 'STORAGE_DB', 'UTILS_DB',
                         'DB_USER', 'DB_PASS', 'db_pass_confirm');
$strError = '';
foreach ($mandatoryFields as $field) {
    if ($_POST[$field] == '') {
        $strError .= 'jQuery("input[name='.$field.']").next().html("Mandatory field");';
    }
}

if ($_POST['DB_PASS'] != $_POST['db_pass_confirm']) {
    $strError .= 'jQuery("input[name=db_pass_confirm]").next().html("Passwords do not match");';
}
if (!$strError) {
    $link = myConnect();
    if (false === $link) {
        $strError .= 'jQuery("input[name=ADDRESS]").next().html("'.mysql_error().'");';
    } else {
        $dbHost = $_SESSION['ADDRESS'];
        if ($dbHost == "") {
            $dbHost = "localhost";
        }
        $_SESSION['DB_HOST'] = $dbHost;
        
        if ($_SESSION['DB_PORT'] == "") {
            $_SESSION['DB_PORT'] = "3306";
        }
    }
    mysql_close($link);
}

if (isset($_POST['UTILS_DB'])) {
    $_SESSION['UTILS_DB'] = $_POST['UTILS_DB'];
}

if ($strError) {
    echo $strError;
} else {
    echo 0;
}