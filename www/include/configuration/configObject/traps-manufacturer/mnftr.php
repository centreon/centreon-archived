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

isset($_GET["id"]) ? $mnftrG = $_GET["id"] : $mnftrG = null;
isset($_POST["id"]) ? $mnftrP = $_POST["id"] : $mnftrP = null;
$mnftrG ? $id = $mnftrG : $id = $mnftrP;

isset($_GET["select"]) ? $cG = $_GET["select"] : $cG = null;
isset($_POST["select"]) ? $cP = $_POST["select"] : $cP = null;
$cG ? $select = $cG : $select = $cP;

isset($_GET["dupNbr"]) ? $cG = $_GET["dupNbr"] : $cG = null;
isset($_POST["dupNbr"]) ? $cP = $_POST["dupNbr"] : $cP = null;
$cG ? $dupNbr = $cG : $dupNbr = $cP;

#Pear library
require_once "HTML/QuickForm.php";
require_once 'HTML/QuickForm/Renderer/ArraySmarty.php';

#Path to the configuration dir
$path = "./include/configuration/configObject/traps-manufacturer/";

#PHP functions
require_once $path."DB-Func.php";
require_once "./include/common/common-Func.php";

/* Set the real page */
if ($ret['topology_page'] != "" && $p != $ret['topology_page']) {
    $p = $ret['topology_page'];
}

switch ($o) {
    case "a":
        require_once($path."formMnftr.php");
        break; #Add a Trap
    case "w":
        require_once($path."formMnftr.php");
        break; #Watch a Trap
    case "c":
        require_once($path."formMnftr.php");
        break; #Modify a Trap
    case "m":
        multipleMnftrInDB(isset($select) ? $select : array(), $dupNbr);
        require_once($path."listMnftr.php");
        break; #Duplicate n Traps
    case "d":
        deleteMnftrInDB(isset($select) ? $select : array());
        require_once($path."listMnftr.php");
        break; #Delete n Traps
    default:
        require_once($path."listMnftr.php");
        break;
}
