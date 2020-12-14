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

global $form_service_type;
$form_service_type = "BYHOSTGROUP";

$service_id = filter_var(
    call_user_func(function () {
        if (isset($_GET["service_id"])) {
            return $_GET["service_id"];
        } elseif (isset($_POST["service_id"])) {
            return $_POST["service_id"];
        } else {
            return null;
        }
    }),
    FILTER_VALIDATE_INT
);

/*
 * Pear library
 */
require_once "HTML/QuickForm.php";
require_once 'HTML/QuickForm/select2.php';
require_once 'HTML/QuickForm/Renderer/ArraySmarty.php';

/*
 * Path to the configuration dir
 */
$path = "./include/configuration/configObject/service/";

/*
 * PHP functions
 */
require_once("./class/centreonDB.class.php");

$pearDBO = new CentreonDB("centstorage");

require_once $path."DB-Func.php";
require_once "./include/common/common-Func.php";

// select can be an array of integer or a string of integers separated by comma
$select = filter_var_array(
    getSelectOption(),
    FILTER_VALIDATE_INT
);

// If one data is not correctly typed in array, it will be set to false
$dupNbr = filter_var_array(
    getDuplicateNumberOption(),
    FILTER_VALIDATE_INT
);

if (isset($_POST["o1"]) && isset($_POST["o2"])) {
    if ($_POST["o1"] != "") {
        $o = $_POST["o1"];
    }
    if ($_POST["o2"] != "") {
        $o = $_POST["o2"];
    }
}

/* Set the real page */
if ($ret['topology_page'] != "" && $p != $ret['topology_page']) {
    $p = $ret['topology_page'];
}

$acl = $centreon->user->access;
$acldbname = $acl->getNameDBAcl();

define('SERVICE_ADD', 'a');
define('SERVICE_WATCH', 'w');
define('SERVICE_MODIFY', 'c');
define('SERVICE_MASSIVE_CHANGE', 'mc');
define('SERVICE_DIVISION', 'dv');
define('SERVICE_MOVE_TO_HOST', 'mvH');
define('SERVICE_ACTIVATION', 's');
define('SERVICE_MASSIVE_ACTIVATION', 'ms');
define('SERVICE_DEACTIVATION', 'u');
define('SERVICE_MASSIVE_DEACTIVATION', 'mu');
define('SERVICE_DUPLICATION', 'm');
define('SERVICE_DELETION', 'd');

switch ($o) {
    case SERVICE_ADD:
    case SERVICE_WATCH:
    case SERVICE_MODIFY:
    case SERVICE_MASSIVE_CHANGE:
        require_once($path . "formService.php");
        break;
    case SERVICE_DIVISION:
        divideGroupedServiceInDB(null, isset($select) ? $select : array());
        require_once($path . "listServiceByHostGroup.php");
        break;
    case SERVICE_MOVE_TO_HOST:
        divideGroupedServiceInDB(null, isset($select) ? $select : array(), 1);
        require_once($path . "listServiceByHostGroup.php");
        break;
    case SERVICE_ACTIVATION:
        enableServiceInDB($service_id);
        require_once($path . "listServiceByHostGroup.php");
        break;
    case SERVICE_MASSIVE_ACTIVATION:
        enableServiceInDB(null, isset($select) ? $select : array());
        require_once($path . "listServiceByHostGroup.php");
        break;
    case SERVICE_DEACTIVATION:
        disableServiceInDB($service_id);
        require_once($path . "listServiceByHostGroup.php");
        break; #Desactivate a service
    case SERVICE_MASSIVE_DEACTIVATION:
        disableServiceInDB(null, isset($select) ? $select : array());
        require_once($path . "listServiceByHostGroup.php");
        break;
    case SERVICE_DUPLICATION:
        multipleServiceInDB(isset($select) ? $select : array(), $dupNbr);
        require_once($path . "listServiceByHostGroup.php");
        break;
    case SERVICE_DELETION:
        deleteServiceInDB(isset($select) ? $select : array());
        require_once($path."listServiceByHostGroup.php");
        break;
    default:
        require_once($path."listServiceByHostGroup.php");
        break;
}
