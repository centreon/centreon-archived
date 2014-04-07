<?php
session_start();
require_once '../functions.php';

$result = 0;
if (!isset($_POST['BROKER_MODULE']) || !$_POST['BROKER_MODULE']) {
    $result = 'jQuery("select[name=BROKER_MODULE]").next().html("Please select a broker module.");';
} else {
    $lines = getParamLines('../../var/brokers', $_POST['BROKER_MODULE']);
    $isRequired = array();
    $type = array();
    foreach ($lines as $line) {
        if ($line) {
            if ($line[0] == '#') {
                continue;
            }
            list($key, $label, $required, $paramType, $default) = explode(';', $line);
            $isRequired[$key] = $required;
            $type[$key] = $paramType;
        }
    }
    $err = "";
    foreach ($_POST as $k => $v) {
        if ($_POST[$k] == '' && isset($isRequired[$k]) && $isRequired[$k]) {
            $err .= 'jQuery("input[name='.$k.']").next().html("Parameter is required");';
        } elseif ($_POST[$k] && isset($type[$k]) && $type[$k] == 0) { // is directory
            if (!is_dir($_POST[$k])) {
                $err .= 'jQuery("input[name='.$k.']").next().html("Directory not found");';
            }
        } elseif ($_POST[$k] && isset($type[$k]) && $type[$k] == 1) { // is file
            if (!is_file($_POST[$k])) {
                $err .= 'jQuery("input[name='.$k.']").next().html("File not found");';
            }
        }
        $_SESSION[$k] = rtrim($v, "/");
    }
}
if ($err) {
    echo $err;
} else {
    echo $result;
}