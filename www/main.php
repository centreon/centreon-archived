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

require_once __DIR__ . "/../bootstrap.php";

use CentreonLegacy\Core\Menu\Menu;

// Set logging options
if (defined("E_DEPRECATED")) {
    ini_set("error_reporting", E_ALL & ~E_NOTICE & ~E_STRICT & ~E_DEPRECATED);
} else {
    ini_set("error_reporting", E_ALL & ~E_NOTICE & ~E_STRICT);
}

/*
 * Purge Values
 */
foreach ($_GET as $key => $value) {
    if (!is_array($value)) {
        $_GET[$key] = \HtmlAnalyzer::sanitizeAndRemoveTags($value);
    }
}

$inputGet = [
    'p' => filter_input(INPUT_GET, 'p', FILTER_SANITIZE_NUMBER_INT),
    'num' => filter_input(INPUT_GET, 'num', FILTER_SANITIZE_NUMBER_INT),
    'o' => \HtmlAnalyzer::sanitizeAndRemoveTags($_GET['o'] ?? ''),
    'min' => \HtmlAnalyzer::sanitizeAndRemoveTags($_GET['min'] ?? ''),
    'type' => \HtmlAnalyzer::sanitizeAndRemoveTags($_GET['type'] ?? ''),
    'search' => \HtmlAnalyzer::sanitizeAndRemoveTags($_GET['search'] ?? ''),
    'limit' => \HtmlAnalyzer::sanitizeAndRemoveTags($_GET['limit'] ?? '')
];
$inputPost = [
    'p' => filter_input(INPUT_POST, 'p', FILTER_SANITIZE_NUMBER_INT),
    'num' => filter_input(INPUT_POST, 'num', FILTER_SANITIZE_NUMBER_INT),
    'o' => \HtmlAnalyzer::sanitizeAndRemoveTags($_POST['o'] ?? ''),
    'min' => \HtmlAnalyzer::sanitizeAndRemoveTags($_POST['min'] ?? ''),
    'type' => \HtmlAnalyzer::sanitizeAndRemoveTags($_POST['type'] ?? ''),
    'search' => \HtmlAnalyzer::sanitizeAndRemoveTags($_POST['search'] ?? ''),
    'limit' => \HtmlAnalyzer::sanitizeAndRemoveTags($_POST['limit'] ?? '')
];

$inputs = array();
foreach ($inputGet as $argumentName => $argumentValue) {
    if (!empty($inputGet[$argumentName]) && trim($inputGet[$argumentName]) != '') {
        $inputs[$argumentName] = $inputGet[$argumentName];
    } elseif (!empty($inputPost[$argumentName]) && trim($inputPost[$argumentName]) != '') {
        $inputs[$argumentName] = $inputPost[$argumentName];
    } else {
        $inputs[$argumentName] = null;
    }
}

$p = $inputs["p"];
$o = $inputs["o"];
$min = $inputs["min"];
$type = $inputs["type"];
$search = $inputs["search"];
$limit = $inputs["limit"];
$num = $inputs["num"];

/*
 * Include all func
 */
include_once("./include/common/common-Func.php");
include_once("./include/core/header/header.php");

$userAgent = $_SERVER['HTTP_USER_AGENT'];
$isMobile = strpos($userAgent, 'Mobil') !== false;

if ($isMobile) {
    $db = $dependencyInjector['configuration_db'];
    $menu = new Menu($db, $_SESSION['centreon']->user);
    $treeMenu = $menu->getMenu();
    require_once 'main.get.php';
} else {
    include('./index.html');
}
