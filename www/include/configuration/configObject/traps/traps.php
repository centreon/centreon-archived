<?php
/*
 * Copyright 2005-2015 Centreon
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

$inputArguments = array(
    'traps_id' => FILTER_SANITIZE_STRING,
    'select' => FILTER_SANITIZE_STRING,
    'dupNbr' => FILTER_SANITIZE_STRING
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
    case "a":
        require_once($path."formTraps.php");
        break; #Add a Trap
    case "w":
        require_once($path."formTraps.php");
        break; #Watch a Trap
    case "c":
        require_once($path."formTraps.php");
        break; #Modify a Trap
    case "m":
        $trapObj->duplicate(isset($select) ? $select : array(), $dupNbr);
        require_once($path."listTraps.php");
        break; #Duplicate n Traps
    case "d":
        $trapObj->delete(isset($select) ? $select : array());
        require_once($path."listTraps.php");
        break; #Delete n Traps
    default:
        require_once($path."listTraps.php");
        break;
}
