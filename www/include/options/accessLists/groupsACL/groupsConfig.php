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

isset($_GET["acl_group_id"]) ? $cG = $_GET["acl_group_id"] : $cG = null;
isset($_POST["acl_group_id"]) ? $cP = $_POST["acl_group_id"] : $cP = null;
$cG ? $acl_group_id = $cG : $acl_group_id = $cP;

isset($_GET["select"]) ? $cG = $_GET["select"] : $cG = null;
isset($_POST["select"]) ? $cP = $_POST["select"] : $cP = null;
$cG ? $select = $cG : $select = $cP;

isset($_GET["dupNbr"]) ? $cG = $_GET["dupNbr"] : $cG = null;
isset($_POST["dupNbr"]) ? $cP = $_POST["dupNbr"] : $cP = null;
$cG ? $dupNbr = $cG : $dupNbr = $cP;

/*
 *  Path to the configuration dir
 */
$path = "./include/options/accessLists/groupsACL/";

/* 
 * PHP functions 
 */
require_once $path . "DB-Func.php";
require_once "./include/common/common-Func.php";

if (isset($_POST["o1"]) && isset($_POST["o2"])) {
    if ($_POST["o1"] != "") {
        $o = $_POST["o1"];
    }
    if ($_POST["o2"] != "") {
        $o = $_POST["o2"];
    }
}

switch ($o) {
    case "a":
        require_once($path . "formGroupConfig.php");
        break; #Add a  an access group
    case "w":
        require_once($path . "formGroupConfig.php");
        break; #Watch a  an access group
    case "c":
        require_once($path . "formGroupConfig.php");
        break; #Modify a  an access group
    case "s":
        enableGroupInDB($acl_group_id);
        require_once($path . "listGroupConfig.php");
        break; #Activate a contactgroup
    case "ms":
        enableGroupInDB(null, isset($select) ? $select : array());
        require_once($path . "listGroupConfig.php");
        break; #Activate n access group
    case "u":
        disableGroupInDB($acl_group_id);
        require_once($path . "listGroupConfig.php");
        break; #Desactivate a contactgroup
    case "mu":
        disableGroupInDB(null, isset($select) ? $select : array());
        require_once($path . "listGroupConfig.php");
        break; #Desactivate n access group
    case "m":
        multipleGroupInDB(isset($select) ? $select : array(), $dupNbr);
        require_once($path . "listGroupConfig.php");
        break; #Duplicate n access group
    case "d":
        deleteGroupInDB(isset($select) ? $select : array());
        require_once($path . "listGroupConfig.php");
        break; #Delete n access group
    default:
        require_once($path . "listGroupConfig.php");
        break;
}
