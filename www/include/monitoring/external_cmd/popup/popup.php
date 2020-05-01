<?php

/*
 * Copyright 2005-2020 Centreon
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

// using bootstrap.php to load the paths and the DB configurations
require_once __DIR__ . '/../../../../../bootstrap.php';
require_once _CENTREON_PATH_ . 'vendor/autoload.php';
require_once _CENTREON_PATH_ . "www/class/centreonSession.class.php";
require_once _CENTREON_PATH_ . "www/class/centreon.class.php";
require_once _CENTREON_PATH_ . "www/class/centreonLang.class.php";
require_once _CENTREON_PATH_ . "www/include/common/common-Func.php";

$pearDB = $dependencyInjector['configuration_db'];

session_start();
session_write_close();

$centreon = $_SESSION['centreon'];

$centreonLang = new CentreonLang(_CENTREON_PATH_, $centreon);
$centreonLang->bindLang();

if (
    !isset($centreon)
    || !isset($_GET['o'])
    || !isset($_GET['cmd'])
    || !isset($_GET['p'])
) {
    exit();
}
if (session_id()) {
    $res = $pearDB->prepare("SELECT * FROM `session` WHERE `session_id` = :sid");
    $res->bindValue(':sid', session_id(), \PDO::PARAM_STR);
    $res->execute();
    if (!$session = $res->fetch()) {
        exit();
    }
} else {
    exit;
}

$o = htmlentities($_GET['o'], ENT_QUOTES, "UTF-8");
$p = htmlentities($_GET['p'], ENT_QUOTES, "UTF-8");
$cmd = htmlentities($_GET['cmd'], ENT_QUOTES, "UTF-8");

if ($cmd == 70 || $cmd == 72) {
    require_once _CENTREON_PATH_ . 'www/include/monitoring/external_cmd/popup/massive_ack.php';
} elseif ($cmd == 74 || $cmd == 75) {
    require_once _CENTREON_PATH_ . 'www/include/monitoring/external_cmd/popup/massive_downtime.php';
}
exit();
