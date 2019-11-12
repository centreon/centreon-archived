<?php
/*
 * Copyright 2005-2019 Centreon
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

isset($_GET["command_id"]) ? $cmdG = $_GET["command_id"] : $cmdG = null;
isset($_POST["command_id"]) ? $cmdP = $_POST["command_id"] : $cmdP = null;
$cmdG ? $command_id = $cmdG : $command_id = $cmdP;

isset($_GET["type"]) ? $typeG = $_GET["type"] : $typeG = null;
isset($_POST["type"]) ? $typeP = $_POST["type"] : $typeP = null;
$typeG ? $type = $typeG : $type = $typeP;

if (!isset($type) || !$type) {
    $type = 2;
}

isset($_POST["command_type"]) ? $type = $_POST["command_type"]["command_type"] : null;

isset($_GET["select"]) ? $cG = $_GET["select"] : $cG = null;
isset($_POST["select"]) ? $cP = $_POST["select"] : $cP = null;
$cG ? $select = $cG : $select = $cP;

isset($_GET["dupNbr"]) ? $cG = $_GET["dupNbr"] : $cG = null;
isset($_POST["dupNbr"]) ? $cP = $_POST["dupNbr"] : $cP = null;
$cG ? $dupNbr = $cG : $dupNbr = $cP;

/*
 * Path to the configuration dir
 */
$path = "./include/configuration/configObject/command/";

/*
 * PHP functions
 */
require_once $path."DB-Func.php";
require_once "./include/common/common-Func.php";

if (isset($_POST["o1"]) && isset($_POST["o2"])) {
    if ($_POST["o1"] != "") {
        $o = $_POST["o1"];
    }
    if ($_POST["o2"] != "") {
        $o = $_POST["o2"];
    }
}

$commandObj = new CentreonCommand($pearDB);
$lockedElements = $commandObj->getLockedCommands();

/* Set the real page */
if ($ret['topology_page'] != "" && $p != $ret['topology_page']) {
    $p = $ret['topology_page'];
}

if ($min) {
    switch ($o) {
        case "h":
            /*
             * Show Help Command
             */
            require_once($path."minHelpCommand.php");
            break;
        default:
            require_once($path."minHelpCommand.php");
            break;
    }
} else {
    switch ($o) {
        case "a":
            /*
             * Add a Command
             */
            require_once($path."formCommand.php");
            break;
        case "w":
            /*
             * Watch a Command
             */
            require_once($path."formCommand.php");
            break;
        case "c":
            /*
             * Modify a Command
             */
            require_once($path."formCommand.php");
            break;
        case "m":
            /*
             * Duplicate n Commands
             */
            multipleCommandInDB(isset($select) ? $select : array(), $dupNbr);
            require_once($path."listCommand.php");
            break;
        case "d":
            /*
             * Delete n Commands
             */
            deleteCommandInDB(isset($select) ? $select : array());
            require_once($path."listCommand.php");
            break;
        case "me":
            changeCommandStatus(null, isset($select) ? $select : array(), 1);
            require_once($path."listCommand.php");
            break;
        case "md":
            changeCommandStatus(null, isset($select) ? $select : array(), 0);
            require_once($path."listCommand.php");
            break;
        case "en":
            changeCommandStatus($cmdG, null, 1);
            require_once($path."listCommand.php");
            break;
        case "di":
            changeCommandStatus($cmdG, null, 0);
            require_once($path."listCommand.php");
            break;
        default:
            require_once($path."listCommand.php");
            break;
    }
}
