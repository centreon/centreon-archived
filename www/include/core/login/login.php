<?php

use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\ConsoleOutput;

/*
 * Copyright 2005-2021 Centreon
 * Centreon is developed by : Julien Mathis and Romain Le Merlus under
 * GPL Licence 2.0.
 *
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License as published by the Free Software
 * Foundation ; either version 2 of the License.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A
 * PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with
 * this program; if not, see <http://www.gnu.org/licenses>.
 *
 * Linking this program statically or dynamically with other modules is making a
 * combined work based on this program. Thus, the terms and conditions of the GNU
 * General Public License cover the whole combination.
 *
 * As a special exception, the copyright holders of this program give Centreon
 * permission to link this program with independent modules to produce an executable,
 * regardless of the license terms of these independent modules, and to copy and
 * distribute the resulting executable under terms of Centreon choice, provided that
 * Centreon also meet, for each linked independent module, the terms  and conditions
 * of the license of that module. An independent module is a module which is not
 * derived from this program. If you modify this program, you may extend this
 * exception to your version of the program, but you are not obliged to do so. If you
 * do not wish to do so, delete this exception statement from your version.
 *
 * For more information : contact@centreon.com
 *
 */

require_once $centreon_path . "/bootstrap.php";

/**
 * Getting Centreon Version
 */
$result = $pearDB->query("SELECT `value` FROM `informations` WHERE `key` = 'version' LIMIT 1");
$release = $result->fetch();

/**
 * Getting OpenId Connect login state
 */
$result = $pearDB->query("SELECT `value` FROM `options` WHERE `key` = 'openid_connect_enable' LIMIT 1");
$openIdConnectEnabled = $result->fetch()["value"];

$result = $pearDB->query("SELECT `value` FROM `options` WHERE `key` = 'openid_connect_mode' LIMIT 1");
$openIdConnectMode = $result->fetch()["value"];

$result = $pearDB->query("SELECT `value` FROM `options` WHERE `key` = 'send_statistics' LIMIT 1");
$sendStatisticsResult = $result->fetch();
if($sendStatisticsResult === false || $sendStatisticsResult["value"] == "0") {
    $sendStatistics = false;
} else {
    $sendStatistics = true;
}
/**
 * Getting Platform Type.
 */
$result = $pearDB->query("SELECT `value` FROM `informations` WHERE `key` = 'isRemote'");
$isRemote = false;
if ($row = $result->fetch()) {
    $isRemote = $row['value'] === 'yes';
}

/**
 * Getting Centreon UUID.
 */
$centreonUUID = new CentreonUUID($pearDB);
$uuid = $centreonUUID->getUUID();

/**
 * Getting Platform statistics.
 */
$output = new ConsoleOutput();
$logger = new ConsoleLogger($output);
$centreonStats = new CentreonStatistics($logger);
$stats = $centreonStats->getPlatformInfo();

/**
 * Getting License informations.
 */
$productLicense = 'Open Source';
$licenseClientName = null;
try {
    $centreonModules = ['epp', 'bam', 'map', 'mbi'];
    $licenseObject = $dependencyInjector['lm.license'];
    $licenseInformation = [];
    foreach ($centreonModules as $module) {
        $licenseObject->setProduct($module);
        $isLicenseValid = $licenseObject->validate(false);
        if ($isLicenseValid && !empty($licenseObject->getData())) {
            $licenseInformation[$module] = $licenseObject->getData();
            $licenseClientName = $licenseInformation[$module]['client']['name'];
            if ($module === 'epp') {
                $productLicense = 'IT Edition';
            }
            if (in_array($module, ['mbi', 'bam', 'map'])) {
                $productLicense = 'Business Edition';
            }
        }
    }
} catch (\Exception $ex) {
    error_log($ex->getMessage());
}

/**
 * Defining Login Form
 */
$form = new HTML_QuickFormCustom('Form', 'post', './index.php');

$optionsAliasField = array('placeholder' => _("Login"), 'class' => 'inputclassic', 'autocomplete' => 'off');
$form->addElement('text', 'useralias', null, $optionsAliasField);

$optionsPasswordField = array('placeholder' => _("Password"), 'class' => 'inputclassicPass');
$form->addElement('password', 'password', null, $optionsPasswordField);

$submitLogin = $form->addElement('submit', 'submitLogin', _("Connect"), array('class' => 'btc bt_info'));

$loginValidate = $form->validate();

require_once __DIR__ . "/processLogin.php";

/**
 * Set login messages (errors)
 */
$loginMessages = array();
if (isset($msg_error) && $msg_error != '') {
    $loginMessages[] = $msg_error;
} elseif (isset($_POST["centreon_token"])) {
    $loginMessages[] = _('Your credentials are incorrect.');
}

if (isset($_GET["disconnect"]) && $_GET["disconnect"] == 2) {
    $loginMessages[] = _('Your session is expired.');
}

if ($file_install_access) {
    $loginMessages[] = $error_msg;
}

if (isset($msg) && $msg) {
    $loginMessages[] = $msg;
}

/**
 * Adding hidden value
 */
if (isset($_GET['p'])) {
    $pageElement = $form->addElement('hidden', 'p');
    $pageElement->setValue($_GET['p']);
}

/**
 * Adding validation rule
 */
$form->addRule('useralias', _("You must specify a username"), 'required');
$form->addRule('password', _("You must specify a password"), 'required');

/**
 * Form parameters
 */
if (isset($freeze) && $freeze) {
    $form->freeze();
}
if ($file_install_access) {
    $submitLogin->freeze();
}

/*
 * Smarty template Init
 */
$tpl = new \Smarty();
$tpl = initSmartyTpl(__DIR__ . '/template/', $tpl);

// Initializing variables
$tpl->assign('loginMessages', $loginMessages);
$tpl->assign('centreonVersion', 'v. ' . $release['value']);
$tpl->assign('currentDate', date("d/m/Y"));
$tpl->assign('openIdConnectEnabled', $openIdConnectEnabled);
$tpl->assign('openIdConnectMode', $openIdConnectMode);
$tpl->assign('uuid', $uuid);
$tpl->assign('stats', $stats);
$tpl->assign('isRemote', $isRemote);
$tpl->assign('productLicense', $productLicense);
if (!empty($licenseInformation)) {
    $tpl->assign('licenseClientName', $licenseClientName);
}
$tpl->assign('sendStatistics', $sendStatistics);

// Redirect User
$redirect = filter_input(
    INPUT_GET,
    'redirect',
    FILTER_SANITIZE_STRING,
    array('options' => array('default' => ''))
);
$tpl->assign('redirect', $redirect);

// Applying and Displaying template
$renderer = new HTML_QuickForm_Renderer_ArraySmarty($tpl, true);
$renderer->setRequiredTemplate('{$label}&nbsp;<font color="red" size="1">*</font>');
$renderer->setErrorTemplate('<font color="red">{$error}</font><br />{$html}');
$form->accept($renderer);
$tpl->assign('form', $renderer->toArray());

/*
 * Display login Page
 */
$tpl->display("login.ihtml");
