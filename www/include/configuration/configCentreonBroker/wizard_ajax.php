<?php
/*
 * Copyright 2015 Centreon (http://www.centreon.com/)
 * 
 * Centreon is a full-fledged industry-strength solution that meets 
 * the needs in IT infrastructure and application monitoring for 
 * service performance.
 * 
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * 
 *    http://www.apache.org/licenses/LICENSE-2.0  
 * 
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * 
 * For more information : contact@centreon.com
 * 
 */

if (!isset($oreon)) {
    exit();
 }

/*
 * Path to the configuration dir
 */
$path = "./include/configuration/configCentreonBroker/";

/*
 * PHP functions
 */
require_once "./include/common/common-Func.php";
require_once "./class/centreonWizard.php";

/*
 * Smarty template Init
 */
$tpl = new Smarty();
$tpl = initSmartyTpl($path . 'wizard/', $tpl);

if (!isset($_SESSION['wizard'])) {
    $_SESSION['wizard'] = array();
 }

if (!isset($_POST['name']) || !isset($_POST['uuid']) || !isset($_POST['step'])) {
    $tpl->assign('strerr', _('Bad arguments for wizard.'));
    $tpl->display("error.ihtml");
    return;
 }
$name = $_POST['name'];
$step = $_POST['step'];
$uuid = $_POST['uuid'];
$finish = false;
if (isset($_POST['finish'])) {
    $finish = $_POST['finish'];
 }

if (false === isset($_SESSION['wizard'][$name][$uuid])) {
    $tpl->assign('strerr', _('The wizard is not correctly initialized.'));
    $tpl->display("error.ihtml");
    return;
 }
$wizard = unserialize($_SESSION['wizard'][$name][$uuid]);
if (false === $wizard->testUuid($uuid)) {
    $tpl->assign('strerr', _('The wizard is corrupted.'));
    $tpl->display("error.ihtml");
    return;
 }

if (isset($_POST['values'])) {
    $wizard->addValues($step - 1, $_POST['values']);
    $_SESSION['wizard'][$name][$uuid] = serialize($wizard);
 }

$lang = array();
$msgErr = array();
if ($finish) {
    include $path . 'wizard/save.php';
    if (count($msgErr) > 0) {
        $page = 'error.ihtml';
        $tpl->assign('strerr', _('Error while saving configuration.'));
    } else {
        $page = 'finish.ihtml';
        $lang['configuration_saved'] = _('Configuration saved.');
    }
 } else {
    switch ($step) {
    case 1:
        $lang['welcome'] = _('Welcome to Centreon Broker configuration');
        $lang['steptext'] = _('Choose a configuration template:');
        $lang['central_configuration_without_poller'] = _('Central without poller');
        $lang['central_configuration_with_poller'] = _('Central with pollers');
        $lang['poller_configuration'] = _('Simple poller');
        $page = 'step1.ihtml';
        break;
    case 2:
        include $path . 'wizard/step2.php';
        break;
    default:
        $tpl->assign('strerr', "The step doesn't exist.");
        $page = 'error.ihtml';
        break;
    }
 }
$tpl->assign('lang', $lang);
$tpl->assign('msgErr', $msgErr);
$tpl->display($page);
