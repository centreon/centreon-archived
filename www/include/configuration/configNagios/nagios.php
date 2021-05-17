<?php

/*
 * Copyright 2005-2021 Centreon
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

$nagiosId = filter_var(
    $_GET['nagios_id'] ?? $_POST['nagios_id'],
    FILTER_VALIDATE_INT
) ?: null;

$select = filter_var_array(
    $_GET["select"] ?? $_POST["select"] ?? [],
    FILTER_VALIDATE_INT
);

$dupNbr = filter_var_array(
    $_GET["dupNbr"] ?? $_POST["dupNbr"] ?? [],
    FILTER_VALIDATE_INT
);

/* Path to the configuration dir */
$path = "./include/configuration/configNagios/";

/* PHP functions */
require_once __DIR__ . '/DB-Func.php';
require_once "./include/common/common-Func.php";

/* Set the real page */
if ($ret['topology_page'] != "" && $p != $ret['topology_page']) {
    $p = $ret['topology_page'];
}

$acl = $oreon->user->access;
$serverString = $acl->getPollerString();
$allowedMainConf = array();
if ($serverString != "''" && !empty($serverString)) {
    $sql = "SELECT nagios_id FROM cfg_nagios WHERE nagios_server_id IN (" . $serverString . ")";
    $res = $pearDB->query($sql);
    while ($row = $res->fetchRow()) {
        $allowedMainConf[$row['nagios_id']] = true;
    }
}

switch ($o) {
    case "a":
        require_once(__DIR__ . '/formNagios.php');
        break; #Add Nagios.cfg
    case "w":
        require_once(__DIR__ . '/formNagios.php');
        break; #Watch Nagios.cfg
    case "c":
        require_once(__DIR__ . '/formNagios.php');
        break; #Modify Nagios.cfg
    case "s":
        enableNagiosInDB($nagiosId);
        require_once(__DIR__ . '/listNagios.php');
        break; #Activate a nagios CFG
    case "u":
        disableNagiosInDB($nagiosId);
        require_once(__DIR__ . '/listNagios.php');
        break; #Desactivate a nagios CFG
    case "m":
        multipleNagiosInDB(isset($select) ? $select : array(), $dupNbr);
        require_once(__DIR__ . '/listNagios.php');
        break; #Duplicate n nagios CFGs
    case "d":
        deleteNagiosInDB(isset($select) ? $select : array());
        require_once(__DIR__ . '/listNagios.php');
        break; #Delete n nagios CFG
    default:
        require_once(__DIR__ . '/listNagios.php');
        break;
}
