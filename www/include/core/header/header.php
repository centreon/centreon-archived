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

if (!defined('SMARTY_DIR')) {
    define('SMARTY_DIR', realpath('../vendor/smarty/smarty/libs/') . '/');
}

/*
 * Bench
 */
function microtime_float(): bool
{
    list($usec, $sec) = explode(" ", microtime());
    return ((float)$usec + (float)$sec);
}

set_time_limit(60);
$time_start = microtime_float();

$advanced_search = 0;

/*
 * Include
 */
include_once(realpath(dirname(__FILE__) . "/../../../../bootstrap.php"));

require_once "$classdir/centreonDB.class.php";
require_once "$classdir/centreonLang.class.php";
require_once "$classdir/centreonSession.class.php";
require_once "$classdir/centreon.class.php";
require_once "$classdir/centreonFeature.class.php";
require_once SMARTY_DIR . "SmartyBC.class.php";

/*
 * Create DB Connection
 *  - centreon
 *  - centstorage
 */
$pearDB = new CentreonDB();
$pearDBO = new CentreonDB("centstorage");

$centreonSession = new CentreonSession();

CentreonSession::start();

// Check session and drop all expired sessions
if (!$centreonSession->updateSession($pearDB)) {
    CentreonSession::stop();
}

$args = "&redirect=" . urlencode(http_build_query($_GET));

// check centreon session
// if session is not valid and autologin token is not given, then redirect to login page
if (!isset($_SESSION["centreon"])) {
    if (!isset($_GET['autologin'])) {
        include __DIR__ . '/../../../index.html';
    } else {
        $args = null;
        foreach ($_GET as $key => $value) {
            $args ? $args .= "&" . $key . "=" . $value : $args = $key . "=" . $value;
        }
        header("Location: index.php?" . $args . "");
    }
}

/*
 * Define Oreon var alias
 */
if (isset($_SESSION["centreon"])) {
    $oreon = $centreon = $_SESSION["centreon"];
}
if (!isset($centreon) || !is_object($centreon)) {
    exit();
}

/*
 * Init different elements we need in a lot of pages
 */
unset($centreon->optGen);
$centreon->initOptGen($pearDB);

if (!$p) {
    $rootMenu = getFirstAllowedMenu($centreon->user->access->topologyStr, $centreon->user->default_page);

    if ($rootMenu && $rootMenu['topology_url'] && $rootMenu['is_react']) {
        header("Location: .{$rootMenu['topology_url']}");
    } elseif ($rootMenu) {
        $p = $rootMenu["topology_page"];
        $tab = preg_split("/\=/", $rootMenu["topology_url_opt"]);

        if (isset($tab[1])) {
            $o = $tab[1];
        }
    }
}

/*
 * Cut Page ID
 */
$level1 = null;
$level2 = null;
$level3 = null;
$level4 = null;
switch (strlen($p)) {
    case 1:
        $level1 = $p;
        break;
    case 3:
        $level1 = substr($p, 0, 1);
        $level2 = substr($p, 1, 2);
        $level3 = substr($p, 3, 2);
        break;
    case 5:
        $level1 = substr($p, 0, 1);
        $level2 = substr($p, 1, 2);
        $level3 = substr($p, 3, 2);
        break;
    case 6:
        $level1 = substr($p, 0, 2);
        $level2 = substr($p, 2, 2);
        $level3 = substr($p, 3, 2);
        break;
    case 7:
        $level1 = substr($p, 0, 1);
        $level2 = substr($p, 1, 2);
        $level3 = substr($p, 3, 2);
        $level4 = substr($p, 5, 2);
        break;
    default:
        $level1 = $p;
        break;
}

/*
 * Define Skin path
 */

$tab_file_css = array();
$i = 0;
if ($handle = @opendir("./Themes/Centreon-2/Color")) {
    while ($file = @readdir($handle)) {
        if (is_file("./Themes/Centreon-2/Color" . "/" . $file)) {
            $tab_file_css[$i++] = $file;
        }
    }
    @closedir($handle);
}

$colorfile = "Color/" . $tab_file_css[0];

//Get CSS Order and color
$DBRESULT = $pearDB->query("SELECT `css_name` FROM `css_color_menu` WHERE `menu_nb` = '" . $level1 . "'");
if ($DBRESULT->rowCount() && ($elem = $DBRESULT->fetch())) {
    $colorfile = "Color/" . $elem["css_name"];
}

//Update Session Table For last_reload and current_page row
$page = '' . $level1 . $level2 . $level3 . $level4;
if (empty($page)) {
    $page = null;
}
$sessionStatement = $pearDB->prepare(
    "UPDATE `session`
    SET `current_page` = :currentPage
    WHERE `session_id` = :sessionId"
);
$sessionStatement->bindValue(':currentPage', $page, \PDO::PARAM_INT);
$sessionStatement->bindValue(':sessionId', session_id(), \PDO::PARAM_STR);
$sessionStatement->execute();

//Init Language
$centreonLang = new CentreonLang(_CENTREON_PATH_, $centreon);
$centreonLang->bindLang();
$centreonLang->bindLang('help');

/**
 * Initialize features flipping
 */
$centreonFeature = new CentreonFeature($pearDB);
