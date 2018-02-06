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

session_start();
require_once realpath(dirname(__FILE__) . "/../../../../config/centreon.config.php");
require_once _CENTREON_PATH_.'/www/class/centreonDB.class.php';
require_once '../../steps/functions.php';

$current = $_POST['current'];
$next = $_POST['next'];
$status = 0;

/**
 * Variables for upgrade scripts
 */
try {
    $pearDB = new CentreonDB('centreon', 3, true);
    $pearDBO = new CentreonDB('centstorage', 3, true);
} catch (Exception $e) {
    exitUpgradeProcess(1, $current, $next, $e->getMessage());
}

/**
 * Upgrade storage sql
 */
$storageSql = '../../sql/centstorage/Update-CSTG-'.$current.'_to_'.$next.'.sql';
if (is_file($storageSql)) {
    $result = splitQueries($storageSql, ';', $pearDBO, '../../tmp/Update-CSTG-'.$current.'_to_'.$next);
    if ("0" != $result) {
        exitUpgradeProcess(1, $current, $next, $result);
    }
}

/**
 * Pre upgrade PHP
 */
$prePhp = '../../php/Update-'.$current.'_to_'.$next.'.php';
if (is_file($prePhp)) {
    try {
        include_once $prePhp;
    } catch (Exception $e) {
        exitUpgradeProcess(1, $current, $next, $e->getMessage());
    }
}

/**
 * Upgrade configuration sql
 */
$confSql = '../../sql/centreon/Update-DB-'.$current.'_to_'.$next.'.sql';
if (is_file($confSql)) {
    $result = splitQueries($confSql, ';', $pearDB, '../../tmp/Update-DB-'.$current.'_to_'.$next);
    if ("0" != $result) {
        exitUpgradeProcess(1, $current, $next, $result);
    }
}

/**
 * Post upgrade PHP
 */
$postPhp = '../../php/Update-'.$current.'_to_'.$next.'.post.php';
if (is_file($postPhp)) {
    try {
        include_once $postPhp;
    } catch (Exception $e) {
        exitUpgradeProcess(1, $current, $next, $e->getMessage());
    }
}

$res = $pearDB->query("SELECT `value` FROM `informations` WHERE `key` = 'version'");
$row = $res->fetchRow();
$current = $row['value'];
$next = '';
if ($handle = opendir('../../sql/centreon')) {
    while (false !== ($file = readdir($handle))) {
        if (preg_match('/Update-DB-'.preg_quote($current).'_to_([a-zA-Z0-9\-\.]+)\.sql/', $file, $matches)) {
            $next = $matches[1];
        }
    }
    closedir($handle);
}
$_SESSION['CURRENT_VERSION'] = $current;
$okMsg = "<span style='color:#88b917;'>OK</span>";
exitUpgradeProcess($status, $current, $next, $okMsg);
