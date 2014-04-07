<?php
session_start();
foreach ($_POST as $key => $value) {
    $_SESSION[$key] = $value;
}
$mandatoryFields = array('ADMIN_PASSWORD', 'confirm_password', 'firstname', 'lastname', 'email');
$strError = '';

$emailRegexp = '/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[_a-z0-9-]+(\.[_a-z0-9-]+)*$/';
if (!preg_match($emailRegexp, $_POST['email'])) {
    $strError .= 'jQuery("input[name=email]").next().html("Invalid E-mail");';
}

foreach ($mandatoryFields as $field) {
    if ($_POST[$field] == '') {
        $strError .= 'jQuery("input[name='.$field.']").next().html("Mandatory field");';
    }
}

if ($_POST['ADMIN_PASSWORD'] != $_POST['confirm_password']) {
    $strError .= 'jQuery("input[name=confirm_password]").next().html("Passwords do not match");';
}

if ($strError) {
    echo $strError;
} else {
    echo 0;
}