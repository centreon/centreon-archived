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

/*
 * Path to the configuration dir
 */
$path = "./include/configuration/configObject/command/";

/*
 * PHP functions
 */
require_once $path . "DB-Func.php";
require_once "./include/common/common-Func.php";

$command_id = filter_var(
    $_GET['command_id'] ?? $_POST['command_id'] ?? null,
    FILTER_VALIDATE_INT
);

$type = filter_var(
    $_POST["command_type"]["command_type"] ?? $_GET['type'] ?? $_POST['type'] ?? 2,
    FILTER_VALIDATE_INT
);

$select = filter_var_array(
    getSelectOption(),
    FILTER_VALIDATE_INT
);

$dupNbr = filter_var_array(
    getDuplicateNumberOption(),
    FILTER_VALIDATE_INT
);

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
        case "h": // Show Help Command
        default:
            require_once($path . "minHelpCommand.php");
            break;
    }
} else {
    switch ($o) {
        case "a": // Add a Command
        case "w": // Watch a Command
        case "c": // Modify a Command
            require_once($path . "formCommand.php");
            break;
        case "m": // Duplicate n Commands
            purgeOutdatedCSRFTokens();
            if (isCSRFTokenValid()) {
                purgeCSRFToken();
                multipleCommandInDB(
                    is_array($select) ? $select : array(),
                    is_array($dupNbr) ? $dupNbr : array()
                );
            } else {
                unvalidFormMessage();
            }
            require_once($path . "listCommand.php");
            break;
        case "d": // Delete n Commands
            purgeOutdatedCSRFTokens();
            if (isCSRFTokenValid()) {
                purgeCSRFToken();
                deleteCommandInDB(is_array($select) ? $select : array());
            } else {
                unvalidFormMessage();
            }
            require_once($path . "listCommand.php");
            break;
        case "me":
            purgeOutdatedCSRFTokens();
            if (isCSRFTokenValid()) {
                purgeCSRFToken();
                changeCommandStatus(null, is_array($select) ? $select : array(), 1);
            } else {
                unvalidFormMessage();
            }
            require_once($path . "listCommand.php");
            break;
        case "md":
            purgeOutdatedCSRFTokens();
            if (isCSRFTokenValid()) {
                purgeCSRFToken();
                changeCommandStatus(null, is_array($select) ? $select : array(), 0);
            } else {
                unvalidFormMessage();
            }
            require_once($path . "listCommand.php");
            break;
        case "en":
            purgeOutdatedCSRFTokens();
            if (isCSRFTokenValid()) {
                purgeCSRFToken();
                if ($command_id !== false) {
                    changeCommandStatus($command_id, null, 1);
                }
            } else {
                unvalidFormMessage();
            }
            require_once($path . "listCommand.php");
            break;
        case "di":
            purgeOutdatedCSRFTokens();
            if (isCSRFTokenValid()) {
                purgeCSRFToken();
                if ($command_id !== false) {
                    changeCommandStatus($command_id, null, 0);
                }
            } else {
                unvalidFormMessage();
            }
            require_once($path . "listCommand.php");
            break;
        default:
            require_once($path . "listCommand.php");
            break;
    }
}
