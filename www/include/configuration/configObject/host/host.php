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

// If host_id is not correctly typed, value will be set to false
$host_id = filter_var(
    call_user_func(function () {
        if (isset($_GET["host_id"])) {
            return $_GET["host_id"];
        } elseif (isset($_POST["host_id"])) {
            return $_POST["host_id"];
        } else {
            return null;
        }
    }),
    FILTER_VALIDATE_INT
);

// select can be an array of integer or a string of integers separated by comma
$select = filter_var_array(
    call_user_func(function () {
        $selectValue = array();
        if (isset($_GET["select"])) {
            $selectValue = $_GET["select"];
        } elseif (isset($_POST["select"])) {
            $selectValue = $_POST["select"];
        }

        // when the data is sent from the form, the format is "1,2,5"
        // so we need to split it by comma, and validate that each element is an integer
        if (!is_array($selectValue)) {
            $selectValue = array_filter(explode(',', $selectValue));
        }
        return $selectValue;
    }),
    FILTER_VALIDATE_INT
);

// If one data is not correctly typed in array, it will be set to false
$dupNbr = filter_var_array(
    call_user_func(function () {
        if (isset($_GET["dupNbr"])) {
            return $_GET["dupNbr"];
        } elseif (isset($_POST["dupNbr"])) {
            return $_POST["dupNbr"];
        } else {
            return array();
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
global $path;

$path = "./include/configuration/configObject/host/";

/*
 * PHP functions
 */
require_once $path."DB-Func.php";
require_once "./include/common/common-Func.php";

define('HOST_ADD', 'a');
define('HOST_DELETE', 'd');
define('HOST_DEPLOY', 'dp');
define('HOST_DISABLE', 'u');
define('HOST_MASSIVE_DISABLE', 'mu');
define('HOST_DUPLICATE', 'm');
define('HOST_ENABLE', 's');
define('HOST_MASSIVE_ENABLE', 'ms');
define('HOST_MODIFY', 'c');
define('HOST_MASSIVE_CHANGE', 'mc');
define('HOST_WATCH', 'w');

$action = filter_var(
    call_user_func(function () {
        if (!empty($_POST["o1"])) {
            return $_POST["o1"];
        } elseif (!empty($_POST["o2"])) {
            return $_POST["o2"];
        } else {
            return null;
        }
    }),
    FILTER_VALIDATE_REGEXP,
    array(
        "options" => array("regexp"=>"/^(a|c|mc|d|dp|m|ms|s|u|mu|w)$/")
    )
);
if ($action !== false) {
    $o = $action;
}

/* Set the real page */
if ($ret2 && $ret2['topology_page'] != "" && $p != $ret2['topology_page']) {
    $p = $ret2['topology_page'];
} elseif ($ret['topology_page'] != "" && $p != $ret['topology_page']) {
    $p = $ret['topology_page'];
}

$acl = $centreon->user->access;
$dbmon = new CentreonDB('centstorage');
$aclDbName = $acl->getNameDBAcl('broker');
$hgs = $acl->getHostGroupAclConf(null, 'broker');
$aclHostString = $acl->getHostsString('ID', $dbmon);
$aclPollerString = $acl->getPollerString();

switch ($o) {
    case HOST_ADD:
        require_once($path."formHost.php");
        break;
    case HOST_WATCH:
        require_once($path."formHost.php");
        break;
    case HOST_MODIFY:
        require_once($path."formHost.php");
        break;
    case HOST_MASSIVE_CHANGE:
        require_once($path."formHost.php");
        break;
    case HOST_ENABLE:
        if ($host_id !== false) {
            enableHostInDB($host_id);
        }
        require_once($path."listHost.php");
        break;
    case HOST_MASSIVE_ENABLE:
        if (!in_array(false, $select)) {
            enableHostInDB(null, $select);
        }
        require_once($path."listHost.php");
        break;
    case HOST_DISABLE:
        if ($host_id !== false) {
            disableHostInDB($host_id);
        }
        require_once($path."listHost.php");
        break;
    case HOST_MASSIVE_DISABLE:
        if (!in_array(false, $select)) {
            disableHostInDB(null, $select);
        }
        require_once($path."listHost.php");
        break;
    case HOST_DUPLICATE: // Duplicate one or more hosts
        if (!in_array(false, $select) && !in_array(false, $dupNbr)) {
            multipleHostInDB($select, $dupNbr);
        }
        require_once($path."listHost.php");
        break;
    case HOST_DELETE: // Delete one or more hosts
        if (!in_array(false, $select)) {
            deleteHostInDB($select);
        }
        require_once($path."listHost.php");
        break;
    case HOST_DEPLOY: // Deploy one or more hosts
        if (!in_array(false, $select)) {
            applytpl($select);
        }
        require_once($path."listHost.php");
        break;
    default:
        require_once($path."listHost.php");
        break;
}
