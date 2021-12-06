<?php

/**
 * Copyright 2005-2020 Centreon
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

require_once realpath(dirname(__FILE__) . "/../../../../config/centreon.config.php");
require_once _CENTREON_PATH_ . "www/class/centreon.class.php";
require_once _CENTREON_PATH_ . "www/class/centreonDB.class.php";
require_once _CENTREON_PATH_ . "www/class/centreonWidget.class.php";
require_once _CENTREON_PATH_ . "www/class/centreonSession.class.php";
require_once _CENTREON_PATH_ . "www/class/centreonUser.class.php";

session_start();
session_write_close();

if (!isset($_SESSION['centreon'])) {
    exit();
}

$elementId = filter_input(
    INPUT_POST,
    'elementId',
    FILTER_VALIDATE_REGEXP,
    [
        'options' => ['regexp' => '/^title_\d+$/']
    ]
);

if ($elementId === null) {
    echo 'missing elementId argument';
    exit();
} elseif ($elementId === false) {
    echo 'elementId must use following regexp format : "title_\d+"';
    exit();
}

$widgetId = null;
if (preg_match('/^title_(\d+)$/', $_POST['elementId'], $matches)) {
    $widgetId = $matches[1];
}

$newName = filter_input(
    INPUT_POST,
    'newName',
    FILTER_SANITIZE_STRING
);
if ($newName === null) {
    echo 'missing newName argument';
    exit();
}

$centreon = $_SESSION['centreon'];
$db = new CentreonDB();

if (CentreonSession::checkSession(session_id(), $db) == 0) {
    exit();
}

$widgetObj = new CentreonWidget($centreon, $db);
try {
    echo $widgetObj->rename($widgetId, $newName);
} catch (CentreonWidgetException $e) {
    echo $e->getMessage();
}
