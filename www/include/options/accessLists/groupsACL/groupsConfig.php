<?php

/*
 * Copyright 2005-2022 Centreon
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

if (!isset($centreon)) {
    exit();
}

/*
 *  Path to the configuration dir
 */
$path = "./include/options/accessLists/groupsACL/";

/*
 * PHP functions
 */
require_once $path . "DB-Func.php";
require_once "./include/common/common-Func.php";

function sanitize_input_array(array $inputArray): array
{
    $sanitizedArray = [];
    foreach ($inputArray as $key => $value) {
        $key = filter_var($key, FILTER_VALIDATE_INT);
        $value = filter_var($value, FILTER_VALIDATE_INT);
        if (false !== $key && false !== $value) {
            $sanitizedArray[$key] = $value;
        }
    }
    return $sanitizedArray;
}

$dupNbr = $_GET['dupNbr'] ?? $_POST['dupNbr'] ?? null;
$dupNbr = is_array($dupNbr) ? sanitize_input_array($dupNbr) : [];

$select = $_GET['select'] ?? $_POST['select'] ?? null;
$select = is_array($select) ? sanitize_input_array($select) : [];

$acl_group_id = filter_var($_GET['acl_group_id'] ?? $_POST['acl_group_id'] ?? null, FILTER_VALIDATE_INT) ?? null;

// Caution $o may already be set from the GET or from the POST.
$postO = filter_var($_POST['o1'] ?? $_POST['o2'] ?? $o ?? null, FILTER_SANITIZE_STRING);
$o = ("" !== $postO) ? $postO : null;

switch ($o) {
    case "a":
        #Add an access group
    case "w":
        #Watch an access group
    case "c":
        #Modify an access group
        require_once($path . "formGroupConfig.php");
        break;
    case "s":
        purgeOutdatedCSRFTokens();
        if (isCSRFTokenValid()) {
            purgeCSRFToken();
            enableGroupInDB($acl_group_id);
        } else {
            unvalidFormMessage();
        }
        require_once($path . "listGroupConfig.php");
        break; #Activate a contactgroup
    case "ms":
        purgeOutdatedCSRFTokens();
        if (isCSRFTokenValid()) {
            purgeCSRFToken();
            enableGroupInDB(null, $select);
        } else {
            unvalidFormMessage();
        }
        require_once($path . "listGroupConfig.php");
        break; #Activate n access group
    case "u":
        purgeOutdatedCSRFTokens();
        if (isCSRFTokenValid()) {
            purgeCSRFToken();
            disableGroupInDB($acl_group_id);
        } else {
            unvalidFormMessage();
        }
        require_once($path . "listGroupConfig.php");
        break; #Desactivate a contactgroup
    case "mu":
        purgeOutdatedCSRFTokens();
        if (isCSRFTokenValid()) {
            purgeCSRFToken();
            disableGroupInDB(null, $select);
        } else {
            unvalidFormMessage();
        }
        require_once($path . "listGroupConfig.php");
        break; #Desactivate n access group
    case "m":
        purgeOutdatedCSRFTokens();
        if (isCSRFTokenValid()) {
            purgeCSRFToken();
            multipleGroupInDB($select, $dupNbr);
        } else {
            unvalidFormMessage();
        }
        require_once($path . "listGroupConfig.php");
        break; #Duplicate n access group
    case "d":
        purgeOutdatedCSRFTokens();
        if (isCSRFTokenValid()) {
            purgeCSRFToken();
            deleteGroupInDB($select);
        } else {
            unvalidFormMessage();
        }
        require_once($path . "listGroupConfig.php");
        break; #Delete n access group
    default:
        require_once($path . "listGroupConfig.php");
        break;
}
