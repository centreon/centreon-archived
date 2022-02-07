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

require_once realpath(__DIR__ . '/../config/centreon.config.php');

$etc = _CENTREON_ETC_;

define('SMARTY_DIR', realpath('../vendor/smarty/smarty/libs/') . '/');

ini_set('display_errors', 'Off');

require_once $classdir . "/centreon.class.php";
require_once $classdir . "/centreonSession.class.php";
require_once $classdir . "/centreonAuth.SSO.class.php";
require_once $classdir . "/centreonLog.class.php";
require_once $classdir . "/centreonDB.class.php";

/*
 * Get auth type
 */
global $pearDB;
$pearDB = new CentreonDB();

$dbResult = $pearDB->query("SELECT * FROM `options`");
while ($generalOption = $dbResult->fetch()) {
    $generalOptions[$generalOption["key"]] = $generalOption["value"];
}
$dbResult->closeCursor();

CentreonSession::start();

/*
 * Already connected
 */
if (isset($_SESSION["centreon"])) {
    $centreon = &$_SESSION["centreon"];
    header('Location: main.php');
}

/*
 * Check PHP version
 *
 *  Centreon >= 18.10 doesn't support PHP < 7.1
 *
 */
if (version_compare(phpversion(), '7.1') < 0) {
    echo "<div class='msg'> PHP version is < 7.1. Please Upgrade PHP</div>";
} else {
    include_once "./include/core/login/login.php";
}
