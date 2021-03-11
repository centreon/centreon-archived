<?php

/*
 * Copyright 2005-2021 Centreon
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
$server_id = filter_var(
    $_GET["server_id"] ?? $_POST["server_id"] ?? null,
    FILTER_VALIDATE_INT
);

$select = filter_var_array(
    $_GET["select"] ?? $_POST["select"] ?? [],
    FILTER_VALIDATE_INT
);

$dupNbr = filter_var_array(
    $_GET["dupNbr"] ?? $_POST["dupNbr"] ?? [],
    FILTER_VALIDATE_INT
);

// Path to the configuration dir
$path = "./include/configuration/configServers/";

// PHP functions
require_once $path . "DB-Func.php";
require_once "./include/common/common-Func.php";

/* Set the real page */
if ($ret['topology_page'] != "" && $p != $ret['topology_page']) {
    $p = $ret['topology_page'];
}

$serverResult =
    $centreon->user->access->getPollerAclConf(
        [
            'fields' => ['id', 'name', 'last_restart'],
            'order' => ['name'],
            'keys' => ['id'],
        ]
    );

$instanceObj = new CentreonInstance($pearDB);

define('SERVER_ADD', 'a');
define('SERVER_DELETE', 'd');
define('SERVER_DISABLE', 'u');
define('SERVER_DUPLICATE', 'm');
define('SERVER_ENABLE', 's');
define('SERVER_MODIFY', 'c');
define('SERVER_WATCH', 'w');

$action = filter_var(
    $_POST['o1'] ?? $_POST['o2'] ?? null,
    FILTER_VALIDATE_REGEXP,
    ['options' => ['regexp' => '/^(a|c|d|m|s|u|w)$/']]
);
if ($action !== false) {
    $o = $action;
}

switch ($o) {
    case SERVER_ADD:
    case SERVER_WATCH:
    case SERVER_MODIFY:
        require_once($path . "formServers.php");
        break;
    case SERVER_ENABLE:
        if ($server_id !== false) {
            enableServerInDB($server_id);
        }
        require_once($path . "listServers.php");
        break;
    case SERVER_DISABLE:
        if ($server_id !== false) {
            disableServerInDB($server_id);
        }
        require_once($path . "listServers.php");
        break;
    case SERVER_DUPLICATE:
        if (!in_array(false, $select) && !in_array(false, $dupNbr)) {
            duplicateServer($select, $dupNbr);
        }
        require_once($path . "listServers.php");
        break;
    case SERVER_DELETE:
        if (!in_array(false, $select)) {
            deleteServerInDB($select);
        }
    //then require the same file than default
    default:
        require_once($path . "listServers.php");
        break;
}
