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
/*
 * Path to the configuration dir
 */
$path = "./include/options/accessLists/menusACL/";

require_once $path . "DB-Func.php";
require_once "./include/common/common-Func.php";

define('ACL_ADD', 'a');
define('ACL_WATCH', 'w');
define('ACL_MODIFY', 'c');
define('ACL_ENABLE', 's');
define('ACL_MULTI_ENABLE', 'ms');
define('ACL_DISABLE', 'u');
define('ACL_MULTI_DISABLE', 'mu');
define('ACL_DUPLICATE', 'm');
define('ACL_DELETE', 'd');

$aclTopologyId = filter_var(
    $_GET["acl_topo_id"] ?? $_POST["acl_topo_id"] ?? null,
    FILTER_VALIDATE_INT
);

$duplicateNbr = filter_var_array(
    $_GET["dupNbr"] ?? $_POST["dupNbr"] ?? array(),
    FILTER_VALIDATE_INT
);

// If one data are not correctly typed in array, it will be set to false
$selectIds = filter_var_array(
    $_GET["select"] ?? $_POST["select"] ?? array(),
    FILTER_VALIDATE_INT
);

$action = filter_var(
    $_POST['o1'] ?? $_POST['o2'] ?? null,
    FILTER_VALIDATE_REGEXP,
    array(
        "options" => array("regexp" => "/([a|c|d|m|s|u|w]{1})/")
    )
);
if ($action !== false) {
    $o = $action;
}

switch ($o) {
    case ACL_ADD:
        require_once($path . "formMenusAccess.php");
        break;
    case ACL_WATCH:
        if (is_int($aclTopologyId)) {
            require_once($path . "formMenusAccess.php");
        } else {
            require_once($path . "listsMenusAccess.php");
        }
        break;
    case ACL_MODIFY:
        if (is_int($aclTopologyId)) {
            require_once($path . "formMenusAccess.php");
        } else {
            require_once($path . "listsMenusAccess.php");
        }
        break;
    case ACL_ENABLE:
        if (is_int($aclTopologyId)) {
            enableLCAInDB($aclTopologyId);
        }
        require_once($path . "listsMenusAccess.php");
        break;
    case ACL_MULTI_ENABLE:
        if (isCSRFTokenValid()) {
            if (!in_array(false, $selectIds)) {
                enableLCAInDB(null, $selectIds);
            }
        } else {
            unvalidFormMessage();
        }
        require_once($path . "listsMenusAccess.php");
        break;
    case ACL_DISABLE:
        if (isCSRFTokenValid()) {
            if (is_int($aclTopologyId)) {
                disableLCAInDB($aclTopologyId);
            }
        } else {
            unvalidFormMessage();
        }
        require_once($path . "listsMenusAccess.php");
        break;
    case ACL_MULTI_DISABLE:
        if (isCSRFTokenValid()) {
            if (!in_array(false, $selectIds)) {
                disableLCAInDB(null, $selectIds);
            }
        } else {
            unvalidFormMessage();
        }
        require_once($path . "listsMenusAccess.php");
        break;
    case ACL_DUPLICATE:
        if (isCSRFTokenValid()) {
            if (!in_array(false, $selectIds) && !in_array(false, $duplicateNbr)) {
                multipleLCAInDB($selectIds, $duplicateNbr);
            }
        } else {
            unvalidFormMessage();
        }
        require_once($path . "listsMenusAccess.php");
        break;
    case ACL_DELETE:
        if (isCSRFTokenValid()) {
            if (!in_array(false, $selectIds)) {
                deleteLCAInDB($selectIds);
            }
        } else {
            unvalidFormMessage();
        }
        require_once($path . "listsMenusAccess.php");
        break;
    default:
        require_once($path . "listsMenusAccess.php");
        break;
}
