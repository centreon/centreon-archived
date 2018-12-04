<?php
/*
 * Copyright 2005-2018 Centreon
 * Centreon is developped by : Julien Mathis and Romain Le Merlus under
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
 
if (!isset($centreon)) {
    exit();
}

define('TRAP_ADD', 'a');
define('TRAP_DELETE', 'd');
define('TRAP_DUPLICATE', 'm');
define('TRAP_MODIFY', 'c');
define('TRAP_WATCH', 'w');

$inputArguments = array(
    'traps_id' => FILTER_VALIDATE_INT,
    'select' => array(
        'filter' => FILTER_VALIDATE_INT,
        'flags' => FILTER_REQUIRE_ARRAY
    ),
    'dupNbr' => array(
        'filter' => FILTER_VALIDATE_INT,
        'flags' => FILTER_REQUIRE_ARRAY
    ),
);
$inputGet = filter_input_array(
    INPUT_GET,
    $inputArguments
);
$inputPost = filter_input_array(
    INPUT_POST,
    $inputArguments
);

$inputs = array();
foreach ($inputArguments as $argumentName => $argumentValue) {
    if (!is_null($inputGet[$argumentName])) {
        $inputs[$argumentName] = $inputGet[$argumentName];
    } else {
        $inputs[$argumentName] = $inputPost[$argumentName];
    }
}

$traps_id = $inputs["traps_id"];
$select = $inputs["select"];
$dupNbr = $inputs["dupNbr"];

/* Pear library */
require_once "HTML/QuickForm.php";
require_once 'HTML/QuickForm/select2.php';
require_once 'HTML/QuickForm/Renderer/ArraySmarty.php';

/* Path to the configuration dir */
$path = "./include/configuration/configObject/traps/";

/* PHP functions */
require_once './class/centreonTraps.class.php';
require_once "./include/common/common-Func.php";

$trapObj = new CentreonTraps($pearDB, $oreon);
$acl = $centreon->user->access;
$aclDbName = $acl->getNameDBAcl();
$dbmon = new CentreonDB('centstorage');
$sgs = $acl->getServiceGroupAclConf(null, 'broker');
$severityObj = new CentreonCriticality($pearDB);

/* Set the real page */
if ($ret['topology_page'] != "" && $p != $ret['topology_page']) {
    $p = $ret['topology_page'];
}

switch ($o) {
    case TRAP_ADD:
        require_once($path."formTraps.php");
        break;
    case TRAP_WATCH:
        require_once($path."formTraps.php");
        break;
    case TRAP_MODIFY:
        require_once($path."formTraps.php");
        break;
    case TRAP_DUPLICATE:
        if (!in_array(false, $select) && !in_array(false, $dupNbr)) {
            $trapObj->duplicate($select, $dupNbr);
        }
        require_once($path."listTraps.php");
        break;
    case TRAP_DELETE:
        if (!in_array(false, $select)) {
            $trapObj->delete($select);
        }
        require_once($path."listTraps.php");
        break;
    default:
        require_once($path."listTraps.php");
        break;
}
