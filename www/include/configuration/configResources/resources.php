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
 * SVN : $URL$
 * SVN : $Id$
 *
 */

if (!isset($oreon)) {
    exit();
}

/*
 * Pear library
 */
require_once "HTML/QuickForm.php";
require_once 'HTML/QuickForm/advmultiselect.php';
require_once 'HTML/QuickForm/Renderer/ArraySmarty.php';

/*
 * Path to the configuration dir
 */
$path = "./include/configuration/configResources/";

/*
 * PHP functions
 */
require_once $path."DB-Func.php";
require_once "./include/common/common-Func.php";

define('MACRO_ADD', 'a');
define('MACRO_DELETE', 'd');
define('MACRO_DISABLE', 'u');
define('MACRO_DUPLICATE', 'm');
define('MACRO_ENABLE', 's');
define('MACRO_MODIFY', 'c');
define('MACRO_WATCH', 'w');

$action = filter_var(
    call_user_func(function () {
        if (isset($_POST['o1'])) {
            return $_POST['o1'];
        } elseif (isset($_POST['o2'])) {
            return $_POST['o2'];
        } else {
            return null;
        }
    }),
    FILTER_VALIDATE_REGEXP,
    array(
        "options" => array("regexp"=>"/^(a|c|d|m|s|u|w)$/")
    )
);
if ($action !== false) {
    $o = $action;
}

// If resource_id is not correctly typed, value will be set to false
$resource_id = filter_var(
    call_user_func(function () {
        if (isset($_GET["resource_id"])) {
            return $_GET["resource_id"];
        } elseif (isset($_POST["resource_id"])) {
            return $_POST["resource_id"];
        } else {
            return null;
        }
    }),
    FILTER_VALIDATE_INT
);

// If one data is not correctly typed in array, it will be set to false
$select = filter_var_array(
    call_user_func(function () {
        if (isset($_GET["select"])) {
            return $_GET["select"];
        } elseif (isset($_POST["select"])) {
            return $_POST["select"];
        } else {
            return array();
        }
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

/* Set the real page */
if ($ret['topology_page'] != "" && $p != $ret['topology_page']) {
    $p = $ret['topology_page'];
}

$acl = $oreon->user->access;
$serverString = $acl->getPollerString();
$allowedResourceConf = array();
if ($serverString != "''" && !empty($serverString)) {
    $sql = "SELECT resource_id
            FROM cfg_resource_instance_relations
            WHERE instance_id IN (" . $serverString . ")";
    $res = $pearDB->query($sql);
    while ($row = $res->fetchRow()) {
        $allowedResourceConf[$row['resource_id']] = true;
    }
}

switch ($o) {
    case MACRO_ADD:
        /*
         * Add a Resource
         */
        require_once($path . "formResources.php");
        break;
    case MACRO_WATCH:
        /*
         * Watch a Resource
         */
        require_once($path . "formResources.php");
        break;
    case MACRO_MODIFY:
        /*
         * Modify a Resource
         */
        require_once($path . "formResources.php");
        break;
    case MACRO_ENABLE:
        /*
         * Activate a Resource
         */
        if ($resource_id !== false) {
            enableResourceInDB($resource_id);
        }
        require_once($path . "listResources.php");
        break;
    case MACRO_DISABLE:
        /*
         * Desactivate a Resource
         */
        if ($resource_id !== false) {
            disableResourceInDB($resource_id);
        }
        require_once($path . "listResources.php");
        break;
    case MACRO_DUPLICATE:
        /*
         * Duplicate n Resources
         */
        if (!in_array(false, $select) && !in_array(false, $dupNbr)) {
            duplicateResources(
                $select,
                $dupNbr
            );
        }
        require_once($path . "listResources.php");
        break;
    case MACRO_DELETE:
        /*
         * Delete n Resources
         */
        if (!in_array(false, $select)) {
            deleteResourcesInDB($select);
        }
        require_once($path . "listResources.php");
        break;
    default:
        require_once($path . "listResources.php");
        break;
}
