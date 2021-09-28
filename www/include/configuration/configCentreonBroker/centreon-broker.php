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


isset($_GET["id"]) ? $cG = $_GET["id"] : $cG = null;
isset($_POST["id"]) ? $cP = $_POST["id"] : $cP = null;
$cG ? $id = $cG : $id = $cP;

isset($_GET["select"]) ? $cG = $_GET["select"] : $cG = null;
isset($_POST["select"]) ? $cP = $_POST["select"] : $cP = null;
$cG ? $select = $cG : $select = $cP;

isset($_GET["dupNbr"]) ? $cG = $_GET["dupNbr"] : $cG = null;
isset($_POST["dupNbr"]) ? $cP = $_POST["dupNbr"] : $cP = null;
$cG ? $dupNbr = $cG : $dupNbr = $cP;

require_once './class/centreonConfigCentreonBroker.php';

/*
 * Path to the configuration dir
 */
$path = "./include/configuration/configCentreonBroker/";

/*
 * PHP functions
 */
require_once $path . "DB-Func.php";
require_once "./include/common/common-Func.php";

/**
 *  Page forbidden if server is a remote
 */
$isRemote = false;

$result = $pearDB->query("SELECT `value` FROM `informations` WHERE `key` = 'isRemote'");
if ($row = $result->fetch()) {
    $isRemote = $row['value'] === 'yes';
}

if ($isRemote) {
    require_once($path . "../../core/errors/alt_error.php");
    exit();
}

/* Set the real page */
if (isset($ret) && is_array($ret) && $ret['topology_page'] != "" && $p != $ret['topology_page']) {
    $p = $ret['topology_page'];
}

$acl = $centreon->user->access;
$serverString = trim($acl->getPollerString());
$allowedBrokerConf = array();

if ($serverString != "''" && !empty($serverString)) {
    $sql = "SELECT config_id FROM cfg_centreonbroker WHERE ns_nagios_server IN (" . $serverString . ")";
    $res = $pearDB->query($sql);
    while ($row = $res->fetchRow()) {
        $allowedBrokerConf[$row['config_id']] = true;
    }
}
switch ($o) {
    case "a":
        require_once($path . "formCentreonBroker.php");
        break; // Add CentreonBroker

    case "w":
        require_once($path . "formCentreonBroker.php");
        break; // Watch CentreonBroker

    case "c":
        require_once($path . "formCentreonBroker.php");
        break; // modify CentreonBroker

    case "s":
        purgeOutdatedCSRFTokens();
        if (isCSRFTokenValid()) {
            purgeCSRFToken();
            enableCentreonBrokerInDB($id);
        } else {
            unvalidFormMessage();
        }
        require_once($path . "listCentreonBroker.php");
        break; // Activate a CentreonBroker CFG

    case "u":
        purgeOutdatedCSRFTokens();
        if (isCSRFTokenValid()) {
            purgeCSRFToken();
            disablCentreonBrokerInDB($id);
        } else {
            unvalidFormMessage();
        }
        require_once($path . "listCentreonBroker.php");
        break; // Desactivate a CentreonBroker CFG

    case "m":
        purgeOutdatedCSRFTokens();
        if (isCSRFTokenValid()) {
            purgeCSRFToken();
            multipleCentreonBrokerInDB(isset($select) ? $select : array(), $dupNbr);
        } else {
            unvalidFormMessage();
        }
        require_once($path . "listCentreonBroker.php");
        break; // Duplicate n CentreonBroker CFGs

    case "d":
        purgeOutdatedCSRFTokens();
        if (isCSRFTokenValid()) {
            purgeCSRFToken();
            deleteCentreonBrokerInDB(isset($select) ? $select : array());
        } else {
            unvalidFormMessage();
        }
        require_once($path . "listCentreonBroker.php");
        break; // Delete n CentreonBroker CFG

    default:
        require_once($path . "listCentreonBroker.php");
        break;
}
