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
$tpl = initSmartyTpl($path, $tpl);

$wizardId = uniqid();

/*
 * Initialize the Wizard
 */
$wizard = new Centreon_Wizard('broker', $wizardId);

$_SESSION['wizard']['broker'][$wizardId] = serialize($wizard);

$tpl->assign('wizardId', $wizardId);

$tpl->display("wizard.ihtml");
