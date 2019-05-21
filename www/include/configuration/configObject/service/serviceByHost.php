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
$form_service_type = "BYHOST";


isset($_GET["service_id"]) ? $sG = $_GET["service_id"] : $sG = null;
isset($_POST["service_id"]) ? $sP = $_POST["service_id"] : $sP = null;
$sG ? $service_id = CentreonDB::escape($sG) : $service_id = CentreonDB::escape($sP);

isset($_GET["select"]) ? $cG = $_GET["select"] : $cG = null;
isset($_POST["select"]) ? $cP = $_POST["select"] : $cP = null;
$cG ? $select = $cG : $select = $cP;

isset($_GET["dupNbr"]) ? $cG = $_GET["dupNbr"] : $cG = null;
isset($_POST["dupNbr"]) ? $cP = $_POST["dupNbr"] : $cP = null;
$cG ? $dupNbr = $cG : $dupNbr = $cP;

/*
 * Path to the configuration dir
 */
$path = "./include/configuration/configObject/service/";

/*
 * PHP functions
 */
require_once $path."DB-Func.php";
require_once "./include/common/common-Func.php";

/*
 * Create a suffix for file name in order to redirect service by hostgroup
 * on a good page.
 */
$linkType = '';

/*
 * Check if a service is a service by hostgroup or not
 */
$request = "SELECT * FROM host_service_relation WHERE service_service_id = '".(int)$service_id."'";
$DBRESULT = $pearDB->query($request);
while ($data = $DBRESULT->fetchRow()) {
    if (isset($data["hostgroup_hg_id"]) && $data["hostgroup_hg_id"] != "") {
        $linkType = 'Group';
        $form_service_type = "BYHOSTGROUP";
    }
}

/*
 * Check options
 */
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
$aclDbName = $acl->getNameDBAcl();

switch ($o) {
    case "a":
        require_once($path."formService.php");
        break; #Add a service
    case "w":
        require_once($path."formService.php");
        break; #Watch a service
    case "c":
        require_once($path."formService.php");
        break; #Modify a service
    case "mc":
        require_once($path."formService.php");
        break; #Massive change
    case "dv":
        divideGroupedServiceInDB(null, isset($select) ? $select : array());
        require_once($path."listServiceByHost$linkType.php");
        break; # Divide service linked to n hosts
    case "s":
        enableServiceInDB($service_id);
        require_once($path."listServiceByHost$linkType.php");
        break; #Activate a service
    case "ms":
        enableServiceInDB(null, isset($select) ? $select : array());
        require_once($path."listServiceByHost$linkType.php");
        break;
    case "u":
        disableServiceInDB($service_id);
        require_once($path."listServiceByHost$linkType.php");
        break; #Desactivate a service
    case "mu":
        disableServiceInDB(null, isset($select) ? $select : array());
        require_once($path."listServiceByHost$linkType.php");
        break;
    case "m":
        multipleServiceInDB(isset($select) ? $select : array(), $dupNbr);
        require_once($path."listServiceByHost$linkType.php");
        break; #Duplicate n services
    case "d":
        deleteServiceInDB(isset($select) ? $select : array());
        require_once($path."listServiceByHost$linkType.php");
        break; #Delete n services
    default:
        require_once($path."listServiceByHost$linkType.php");
        break;
}
