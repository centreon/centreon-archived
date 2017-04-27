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

// Set logging options
if (defined("E_DEPRECATED")) {
    ini_set("error_reporting", E_ALL & ~E_NOTICE & ~E_STRICT & ~E_DEPRECATED);
} else {
    ini_set("error_reporting", E_ALL & ~E_NOTICE & ~E_STRICT);
}

/*
 * Purge Values
 */
if (function_exists('filter_var')){
    foreach ($_GET as $key => $value){
        if (!is_array($value)){
            $_GET[$key] = filter_var($value, FILTER_SANITIZE_SPECIAL_CHARS);
        }
    }
}

$inputArguments = array(
    'p' => FILTER_SANITIZE_NUMBER_INT,
    'o' => FILTER_SANITIZE_STRING,
    'min' => FILTER_SANITIZE_STRING,
    'type' => FILTER_SANITIZE_STRING,
    'search' => FILTER_SANITIZE_STRING,
    'limit' => FILTER_SANITIZE_STRING,
    'num' => FILTER_SANITIZE_NUMBER_INT
);
$inputGet = filter_input_array(
    INPUT_GET,
    $inputArguments
);
$inputPost = filter_input_array(
    INPUT_POST,
    $inputArguments
);

$inputs = array();
foreach ($inputArguments as $argumentName => $argumentValue) {
    if (!is_null($inputGet[$argumentName])) {
        $inputs[$argumentName] = $inputGet[$argumentName];
    } else {
        $inputs[$argumentName] = $inputPost[$argumentName];
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

require_once _CENTREON_PATH_ . "www/autoloader.php";

/*
 * LCA Init Common Var
 */
global $is_admin;
$is_admin = $centreon->user->admin;

$query = "SELECT topology_parent,topology_name,topology_id,topology_url,topology_page " .
            " FROM topology WHERE topology_page = '".$p."'";
$DBRESULT = $pearDB->query($query);
$redirect = $DBRESULT->fetchRow();

/*
 * Init URL
 */
$url = "";
$acl_page = $centreon->user->access->page($p, true);
if ($acl_page == 1 || $acl_page == 2) {
    if ($redirect["topology_page"] < 100) {
        $ret = get_child($redirect["topology_page"], $centreon->user->access->topologyStr);
        if (!$ret['topology_page']) {
            if (file_exists($redirect["topology_url"])) {
                $url = $redirect["topology_url"];
                reset_search_page($url);
            } else {
                $url = "./include/core/errors/alt_error.php";
            }
        } else {
            $ret2 = get_child($ret['topology_page'], $centreon->user->access->topologyStr);
            if ($ret2["topology_url_opt"]) {
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
            } else {
                $url = "./include/core/errors/alt_error.php";
            }
        }
    } elseif ($redirect["topology_page"] >= 100 && $redirect["topology_page"] < 1000) {
        $ret = get_child($redirect["topology_page"], $centreon->user->access->topologyStr);
        if (!$ret['topology_page']) {
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
        if (!$ret['topology_page']) {
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

/*
 * Display Menu
 */
if (!$min) {
    include_once "./include/core/menu/menu.php";
}

if (!$centreon->user->showDiv("header")) {
?><script type="text/javascript">
    new Effect.toggle('header', 'appear', { duration : 0, afterFinish: function() { 
        setQuickSearchPosition(); } 
    });
</script> <?php
}
if (!$centreon->user->showDiv("menu_3")) {
?><script type="text/javascript">
    new Effect.toggle('menu_3', 'appear', { duration : 0 });
</script> <?php
}
if (!$centreon->user->showDiv("menu_2")) {
?><script type="text/javascript">
    new Effect.toggle('menu_2', 'appear', { duration : 0 });
</script> <?php
}

/*
 * Display PathWay
 */
if ($min != 1) {
    include_once "./include/core/pathway/pathway.php";
}

/*
 * Go on our page
 */
if ($min != 1) {
    require_once("./class/centreonMsg.class.php");
    $msg = new CentreonMsg();
    if (!$centreon->user->admin && !count($centreon->user->access->getAccessGroups())) {
        $msg->setImage("./img/icons/warning.png");
        $msg->setTextStyle("bold");
        $msg->setText(_("You are not in an access group"));
        $msg->setTimeOut("3");
    }
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
    'search' => FILTER_SANITIZE_STRING,
    'search_service' => FILTER_SANITIZE_STRING,
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
        switch ($argumentName) {
            case 'limit':
                if (!is_null($inputGet[$argumentName])) {
                    $centreon->historyLimit[$url] = $inputGet[$argumentName];
                } elseif (!is_null($inputPost[$argumentName])) {
                    $centreon->historyLimit[$url] = $inputPost[$argumentName];
                } else {
                    $centreon->historyLimit[$url] = 30;
                }
                break;
            case 'num':
                if (!is_null($inputGet[$argumentName])) {
                    $centreon->historyPage[$url] = $inputGet[$argumentName];
                } elseif (!is_null($inputPost[$argumentName])) {
                    $centreon->historyPage[$url] = $inputPost[$argumentName];
                } else {
                    $centreon->historyPage[$url] = 0;
                }
                break;
            case 'search':
                if (!is_null($inputGet[$argumentName])) {
                    $centreon->historySearch[$url] = $inputGet[$argumentName];
                } elseif (!is_null($inputPost[$argumentName])) {
                    $centreon->historySearch[$url] = $inputPost[$argumentName];
                } else {
                    $centreon->historySearch[$url] = '';
                }
                break;
            case 'search_service':
                if (!is_null($inputGet[$argumentName])) {
                    $centreon->historySearchService[$url] = $inputGet[$argumentName];
                } elseif (!is_null($inputPost[$argumentName])) {
                    $centreon->historySearchService[$url] = $inputPost[$argumentName];
                } else {
                    $centreon->historySearchService[$url] = '';
                }
                break;
            default:
                continue;
                break;
        }

    }
}

/*
 * Display Footer
 */
if (!$min) {
    print "\t\t\t</td>\t\t</tr>\t</table>\n</div>";
}

/*
 * Include Footer 
 */
include_once "./include/core/footer/footer.php";

