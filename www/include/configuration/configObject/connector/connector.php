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

require_once _CENTREON_PATH_ . 'www/class/centreonConnector.class.php';
$path = _CENTREON_PATH_ . 'www/include/configuration/configObject/connector/';
require_once $path . "DB-Func.php";

/*
 * Pear library
 */
require_once "HTML/QuickForm.php";
require_once 'HTML/QuickForm/select2.php';
require_once 'HTML/QuickForm/Renderer/ArraySmarty.php';

$connectorObj = new CentreonConnector($pearDB);

if (isset($_REQUEST['select'])) {
    $select = $_REQUEST['select'];
}

if (isset($_REQUEST['id'])) {
    $connector_id = $_REQUEST['id'];
}

if (isset($_REQUEST['options'])) {
    $options = $_REQUEST['options'];
}

/* Access level */
($centreon->user->access->page($p) == 1) ? $lvl_access = 'w' : $lvl_access = 'r';

switch ($o) {
    case "a":
        require_once($path.'formConnector.php');
        break;

    case "w":
        require_once($path.'formConnector.php');
        break;

    case "c":
        require_once($path.'formConnector.php');
        break;

    case "s":
        if ($lvl_access == "w") {
            $myConnector = $connectorObj->read($connector_id);
            $myConnector['enabled'] = '1';
            $connectorObj->update($connector_id, $myConnector);
        }
        require_once($path.'listConnector.php');
        break;

    case "u":
        if ($lvl_access == "w") {
            $myConnector = $connectorObj->read($connector_id);
            $myConnector['enabled'] = '0';
            $connectorObj->update($connector_id, $myConnector);
        }
        require_once($path.'listConnector.php');
        break;

    case "m":
        if ($lvl_access == "w") {
            $selectedConnectors = array_keys($select);
            foreach ($selectedConnectors as $connectorId) {
                $connectorObj->copy($connectorId, (int)$options[$connectorId]);
            }
        }
        require_once($path.'listConnector.php');
        break;

    case "d":
        if ($lvl_access == "w") {
            $selectedConnectors = array_keys($select);
            foreach ($selectedConnectors as $connectorId) {
                $connectorObj->delete($connectorId);
            }
        }
        require_once($path.'listConnector.php');
        break;

    default:
        require_once($path.'listConnector.php');
        break;
}
