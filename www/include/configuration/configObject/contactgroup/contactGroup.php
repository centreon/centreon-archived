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

isset($_GET["cg_id"]) ? $cG = $_GET["cg_id"] : $cG = null;
isset($_POST["cg_id"]) ? $cP = $_POST["cg_id"] : $cP = null;
$cG ? $cg_id = $cG : $cg_id = $cP;

isset($_GET["select"]) ? $cG = $_GET["select"] : $cG = null;
isset($_POST["select"]) ? $cP = $_POST["select"] : $cP = null;
$cG ? $select = $cG : $select = $cP;

isset($_GET["dupNbr"]) ? $cG = $_GET["dupNbr"] : $cG = null;
isset($_POST["dupNbr"]) ? $cP = $_POST["dupNbr"] : $cP = null;
$cG ? $dupNbr = $cG : $dupNbr = $cP;

/*
 * Path to the configuration dir
 */
$path = "./include/configuration/configObject/contactgroup/";

/*
 * PHP functions
 */
require_once $path . "DB-Func.php";
require_once "./include/common/common-Func.php";

/* Set the real page */
if ($ret['topology_page'] != "" && $p != $ret['topology_page']) {
    $p = $ret['topology_page'];
}

$acl = $centreon->user->access;
$allowedContacts = $acl->getContactAclConf(array(
    'fields' => array('contact_id', 'contact_name'),
    'keys' => array('contact_id'),
    'get_row' => 'contact_name',
    'order' => 'contact_name'
));
$allowedAclGroups = $acl->getAccessGroups();
$contactstring = "";
if (count($allowedContacts)) {
    $first = true;
    foreach ($allowedContacts as $key => $val) {
        if ($first) {
            $first = false;
        } else {
            $contactstring .= ",";
        }
        $contactstring .= "'" . $key . "'";
    }
} else {
    $contactstring = "''";
}

switch ($o) {
    case "a":
        /*
         * Add a contactgroup
         */
        require_once($path . "formContactGroup.php");
        break;
    case "w":
        /*
         * Watch a contactgroup
         */
        require_once($path . "formContactGroup.php");
        break;
    case "c":
        /*
         * Modify a contactgroup
         */
        require_once($path . "formContactGroup.php");
        break;
    case "s":
        /*
         * Activate a contactgroup
         */
        if (isCSRFTokenValid()) {
            enableContactGroupInDB($cg_id);
        } else {
            unvalidFormMessage();
        }
        require_once($path . "listContactGroup.php");
        break;
    case "u":
        /*
         * Desactivate a contactgroup
         */
        if (isCSRFTokenValid()) {
            disableContactGroupInDB($cg_id);
        } else {
            unvalidFormMessage();
        }
        require_once($path . "listContactGroup.php");
        break;
    case "m":
        /*
         * Duplicate n contact group
         */
        if (isCSRFTokenValid()) {
            multipleContactGroupInDB(isset($select) ? $select : array(), $dupNbr);
        } else {
            unvalidFormMessage();
        }
        require_once($path . "listContactGroup.php");
        break;
    case "d":
        /*
         * Delete a contact group
         */
        if (isCSRFTokenValid()) {
            deleteContactGroupInDB(isset($select) ? $select : array());
        } else {
            unvalidFormMessage();
        }
        require_once($path . "listContactGroup.php");
        break;
    case "dn":
        require_once $path . 'displayNotification.php';
        break;
    default:
        require_once($path . "listContactGroup.php");
        break;
}
