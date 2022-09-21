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

require_once __DIR__ . "/../bootstrap.php";

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

$inputs = [];
foreach ($inputGet as $argumentName => $argumentValue) {
    if (!empty($inputGet[$argumentName]) && trim($inputGet[$argumentName]) !== '') {
        $inputs[$argumentName] = $inputGet[$argumentName];
    } elseif (!empty($inputPost[$argumentName]) && trim($inputPost[$argumentName]) !== '') {
        $inputs[$argumentName] = $inputPost[$argumentName];
    } else {
        $inputs[$argumentName] = null;
    }
}

if (empty($p)) {
    $p = $inputs["p"];
}

$o = $inputs["o"];
$min = $inputs["min"];
$type = $inputs["type"];
$search = $inputs["search"];
$limit = $inputs["limit"];
$num = $inputs["num"];

/*
 * Include all func
 */
include_once "./include/common/common-Func.php";
include_once "./include/core/header/header.php";

$centreon->user->setCurrentPage($p);

/*
 * LCA Init Common Var
 */
global $is_admin;
$is_admin = $centreon->user->admin;

$query = "SELECT topology_parent,topology_name,topology_id,topology_url,topology_page " .
    " FROM topology WHERE topology_page = '" . $p . "'";
$DBRESULT = $pearDB->query($query);
$redirect = $DBRESULT->fetch();

/**
 *  Is server a remote ?
 */
global $isRemote;
$isRemote = false;

$result = $pearDB->query("SELECT `value` FROM `informations` WHERE `key` = 'isRemote'");
if ($row = $result->fetch()) {
    $isRemote = $row['value'] === 'yes';
}

/*
 * Init URL
 */
$url = "";
$acl_page = $centreon->user->access->page($p, true);
if ($redirect !== false && ($acl_page == 1 || $acl_page == 2)) {
    if ($redirect["topology_page"] < 100) {
        $ret = get_child($redirect["topology_page"], $centreon->user->access->topologyStr);
        if ($ret === false || !$ret['topology_page']) {
            if (file_exists($redirect["topology_url"])) {
                $url = $redirect["topology_url"];
                reset_search_page($url);
            } else {
                $url = "./include/core/errors/alt_error.php";
            }
        } else {
            $ret2 = get_child($ret['topology_page'], $centreon->user->access->topologyStr);
            if ($ret2 === false || $ret2["topology_url_opt"]) {
                if (!$o) {
                    $tab = preg_split("/\=/", $ret2["topology_url_opt"]);
                    $o = $tab[1];
                }
                $p = $ret2["topology_page"];
            }
            if (file_exists($ret2["topology_url"])) {
                $url = $ret2["topology_url"];
                reset_search_page($url);
                if ($ret2["topology_url_opt"]) {
                    $tab = preg_split("/\=/", $ret2["topology_url_opt"]);
                    $o = $tab[1];
                }
            } elseif ($ret['topology_url']) {
                $url = $ret['topology_url'];
                if ($ret['is_react'] === '1') {
                    // workaround to update react page without refreshing whole page
                    echo '<script>'
                        . 'window.top.history.pushState("", "", ".' . $ret['topology_url'] . '");'
                        . 'window.top.history.pushState("", "", ".' . $ret['topology_url'] . '");'
                        . 'window.top.history.go(-1);'
                        . '</script>';
                    exit();
                }
            } else {
                $url = "./include/core/errors/alt_error.php";
            }
        }
    } elseif ($redirect["topology_page"] >= 100 && $redirect["topology_page"] < 1000) {
        $ret = get_child($redirect["topology_page"], $centreon->user->access->topologyStr);
        if ($ret === false || !$ret['topology_page']) {
            if (file_exists($redirect["topology_url"])) {
                $url = $redirect["topology_url"];
                reset_search_page($url);
            } else {
                $url = "./include/core/errors/alt_error.php";
            }
        } else {
            if ($ret["topology_url_opt"]) {
                if (!$o) {
                    $tab = preg_split("/\=/", $ret["topology_url_opt"]);
                    $o = $tab[1];
                }
                $p = $ret["topology_page"];
            }
            if (file_exists($ret["topology_url"])) {
                $url = $ret["topology_url"];
                reset_search_page($url);
            } else {
                $url = "./include/core/errors/alt_error.php";
            }
        }
    } elseif ($redirect["topology_page"] >= 1000) {
        $ret = get_child($redirect["topology_page"], $centreon->user->access->topologyStr);
        if ($ret === false || !$ret['topology_page']) {
            if (file_exists($redirect["topology_url"])) {
                $url = $redirect["topology_url"];
                reset_search_page($url);
            } else {
                $url = "./include/core/errors/alt_error.php";
            }
        } else {
            if (file_exists($redirect["topology_url"]) && $ret['topology_page']) {
                $url = $redirect["topology_url"];
                reset_search_page($url);
            } else {
                $url = "./include/core/errors/alt_error.php";
            }
        }
    }
    if (isset($o) && $acl_page == 2) {
        if ($o == 'c') {
            $o = 'w';
        } elseif ($o == 'a') {
            $url = "./include/core/errors/alt_error.php";
        }
    }
} else {
    $url = "./include/core/errors/alt_error.php";
}

/*
 *  Header HTML
 */
include_once "./include/core/header/htmlHeader.php";

?>
<div id="centreonMsg" class="react-centreon-message"></div>

<script type='text/javascript'>
    //saving the user locale
    localStorage.setItem('locale', '<?php echo $centreon->user->get_lang() ?>');
</script>
<?php
if (!$centreon->user->showDiv("header")) {
    ?>
    <script type="text/javascript">
        new Effect.toggle('header', 'appear', {
            duration: 0, afterFinish: function () {
                setQuickSearchPosition();
            }
        });
    </script> <?php
}
if (!$centreon->user->showDiv("menu_3")) {
    ?>
    <script type="text/javascript">
        new Effect.toggle('menu_3', 'appear', {duration: 0});
    </script> <?php
}
if (!$centreon->user->showDiv("menu_2")) {
    ?>
    <script type="text/javascript">
        new Effect.toggle('menu_2', 'appear', {duration: 0});
    </script> <?php
}
?>
    <section class="main section-expand" style="padding-top: 4px;">
<?php
/*
 * Display PathWay
 */
if ($min != 1) {
    include_once "./include/core/pathway/pathway.php";
}

if (isset($url) && $url) {
    include_once $url;
}

if (!isset($centreon->historyPage)) {
    $centreon->createHistory();
}

/*
 * Keep in memory all informations about pagination, keyword for search...
 */
$inputArguments = array(
    'num' => FILTER_SANITIZE_NUMBER_INT,
    'limit' => FILTER_SANITIZE_NUMBER_INT
);
$inputGet = filter_input_array(
    INPUT_GET,
    $inputArguments
);
$inputPost = filter_input_array(
    INPUT_POST,
    $inputArguments
);

if (isset($url) && $url) {
    foreach ($inputArguments as $argumentName => $argumentFlag) {
        if ($argumentName === 'limit') {
            if (!empty($inputGet[$argumentName])) {
                $centreon->historyLimit[$url] = $inputGet[$argumentName];
            } elseif (!empty($inputPost[$argumentName])) {
                $centreon->historyLimit[$url] = $inputPost[$argumentName];
            } else {
                $centreon->historyLimit[$url] = 30;
            }
        }
    }
}

// Display Footer
if (!$min) {
    print "\t\t\t</td>\t\t</tr>\t</table>\n</div>";
}
?>
    </section>
<?php
// Include Footer
include_once "./include/core/footer/footerPart.php";

