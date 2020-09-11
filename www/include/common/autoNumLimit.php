<?php
/*
 * Copyright 2005-2019 Centreon
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

$limitNotInRequestParameter = !isset($_POST['limit']) && !isset($_GET['limit']);
$historyLimitNotDefault = isset($centreon->historyLimit[$url]) && $centreon->historyLimit[$url] !== 30;
$sessionLimitKey = "results_limit_{$url}";

// Setting the limit filter
if (isset($_POST['limit']) && $_POST['limit']) {
    $limit = filter_input(INPUT_POST, 'limit', FILTER_VALIDATE_INT, ['options' => ['default' => 20]]);
} elseif (isset($_GET['limit'])) {
    $limit = filter_input(INPUT_GET, 'limit', FILTER_VALIDATE_INT, ['options' => ['default' => 20]]);
} elseif ($limitNotInRequestParameter && $historyLimitNotDefault) {
    $limit = $centreon->historyLimit[$url];
} elseif (isset($_SESSION[$sessionLimitKey])) {
    $limit = $_SESSION[$sessionLimitKey];
} else {
    if (($p >= 200 && $p < 300) || ($p >= 20000 && $p < 30000)) {
        $dbResult = $pearDB->query("SELECT * FROM `options` WHERE `key` = 'maxViewMonitoring'");
    } else {
        $dbResult = $pearDB->query("SELECT * FROM `options` WHERE `key` = 'maxViewConfiguration'");
    }
    $gopt = $dbResult->fetch();
    if ((int)$gopt['value']) {
        $limit = (int)$gopt['value'];
    } else {
        $limit = 30;
    }
}

$_SESSION[$sessionLimitKey] = $limit;

// Setting the pagination filter
if (isset($_POST['num'])
    && isset($_POST['search'])
    || (isset($centreon->historyLastUrl) && $centreon->historyLastUrl !== $url)
) {
    // Checking if the current page and the last displayed page are the same and resetting the filters
    $num = 0;
} elseif (isset($_REQUEST['num'])) {
    // Checking if a pagination filter has been sent in the http request
    $num = filter_var(
        $_GET['num'] ?? $_POST['num'] ?? 0,
        FILTER_VALIDATE_INT
    );
} else {
    // Resetting the pagination filter
    $num = $centreon->historyPage[$url] ?? 0;
}

// Cast limit and num to avoid sql error on prepared statement (PDO::PARAM_INT)
$limit = (int)$limit;
$num = (int)$num;

global $search;
