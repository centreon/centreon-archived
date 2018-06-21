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

isset($_GET["list"]) ? $mG = $_GET["list"] : $mG = null;
isset($_POST["list"]) ? $mP = $_POST["list"] : $mP = null;
$mG ? $list = $mG : $list = $mP;

isset($_GET["id"]) ? $mG = $_GET["id"] : $mG = null;
isset($_POST["id"]) ? $mP = $_POST["id"] : $mP = null;
$mG ? $id = $mG : $id = $mP;

isset($_GET["name"]) ? $nameG = $_GET["name"] : $nameG = null;
isset($_POST["name"]) ? $nameP = $_POST["name"] : $nameP = null;
$nameG ? $name = $nameG : $name = $nameP;

$operationType = '';
if ($name !== null) {
    $operationType = 'install';
} elseif ($id !== null) {
    $operationType = 'upgrade';
}

/*
 * Path to the options dir
 */
$path = "./include/options/oreon/modules/";

require_once "./include/common/common-Func.php";
require_once $path ."DB-Func.php";

$factory = new \CentreonLegacy\Core\Utils\Factory($dependencyInjector);
$utils = $factory->newUtils();

$moduleFactory = new \CentreonLegacy\Core\Module\Factory($dependencyInjector, $utils);
$moduleInfoObj = $moduleFactory->newInformation();

if ($list) {
    require_once($path."listModules.php");
} else {
    switch ($o) {
        case "i":
        case "w":
            require_once($path . "installForm.php");
            break;
        case "u":
            require_once($path . "upgradeForm.php");
            break;
        case "d":
        default:
            require_once($path . "listModules.php");
            break;
    }
}
