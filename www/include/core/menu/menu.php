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

/*
 * Path to the configuration dir
 */
$path = "./include/core/menu/templates";
$user_update_pref = "./include/core/menu/userMenuPreferences.php";

/*
 * Smarty template Init
 */
$tpl = new Smarty();
$tpl = initSmartyTpl($path, $tpl);

/*
 * Var init
 */
$sep = null;
$elemArr = array(1 => array(), 2 => array(), 3 => array(), 4 => array());

/*
 * Special Case
 * Put the authentication in the URL
 */
$auth = null;

require_once("./include/core/menu/menuJS.php");
require_once _CENTREON_PATH_ . 'www/class/centreonMenu.class.php';
require_once _CENTREON_PATH_ . 'www/class/centreonLang.class.php';

$centreonMenu = new CentreonMenu(new CentreonLang(_CENTREON_PATH_, $centreon));

/*
 * block headerHTML
 */
$tpl->assign("urlLogo", 'img/centreon.png');

/**
 * ACL
 */
if ($centreon->user->access->admin == 0) {
    $tabActionACL = $centreon->user->access->getActions();
    if (isset($tabActionACL["top_counter"])) {
        $tpl->assign("displayTopCounter", 1);
    } else {
        $tpl->assign("displayTopCounter", 0);
    }
    if (isset($tabActionACL["poller_stats"])) {
        $tpl->assign("displayPollerStats", 1);
    } else {
        $tpl->assign("displayPollerStats", 0);
    }
} else {
    $tpl->assign("displayTopCounter", 1);
    $tpl->assign("displayPollerStats", 1);
}

$tpl->assign("Help", _("Help"));
$tpl->assign("Documentation", _("Documentation"));
$tpl->assign("p", $p);
$tpl->assign("sound_status", isset($_SESSION['disable_sound']) ? 'off' : 'on');
$tpl->assign("sound_action", isset($_SESSION['disable_sound']) ? 'jQuery().centreon_notify_start();' : 'jQuery().centreon_notify_stop();');
$tpl->assign("date_time_format_status", _("d/m/Y H:i:s"));

/*
 * Display Login
 */
$tpl->assign("user_login", $centreon->user->get_alias());

/*
 * Fixe ACL
 */
$lcaSTR = "";
if (!$is_admin) {
    $lcaSTR = "AND topology_page IN (".$centreon->user->access->getTopologyString().")";
}

/*
 * Grab elements for level 1
 */
$rq = "SELECT * FROM topology WHERE topology_parent IS NULL $lcaSTR AND topology_show = '1' ORDER BY topology_order";
$DBRESULT = $pearDB->query($rq);
for ($i = 0; $DBRESULT->numRows() && ($elem = $DBRESULT->fetchRow()); $i++) {
    $pageAccess = $centreon->user->access->page($elem["topology_page"]);
    if (($pageAccess == "1") || ($pageAccess == "2")) {
        $elemArr[1][$i] = array("Menu1ClassImg" => $level1 == $elem["topology_page"] ? "menu1_bgimg" : "id_".$elem["topology_id"],
                            "Menu1Page" => $elem["topology_page"] ,
                            "Menu1Url" => "main.php?p=".$elem["topology_page"].$elem["topology_url_opt"],
                            "Menu1UrlPopup" => $elem["topology_popup"],
                            "Menu1UrlPopupOpen" => $elem["topology_url"],
                            "Menu1Name" => $centreonMenu->translate($elem['topology_modules'], $elem['topology_url'], $elem["topology_name"]),
                            "Menu1Popup" => $elem["topology_popup"] ? true : false);
    }
}
$DBRESULT->free();

$userUrl = "main.php?p=50104&o=c";

$logDate = $centreon->CentreonGMT->getDate(_("Y/m/d G:i"), time(), $centreon->user->getMyGMT());
$logOutUrl = "index.php?disconnect=1";

/*
 * Define autologin URL
 */
if (isset($centreon->optGen["display_autologin_shortcut"])) {
    $userAlias = $centreon->user->get_alias();
    if ($centreon->optGen["enable_autologin"] && $centreon->optGen["display_autologin_shortcut"]) {
        $tpl->assign("autoLoginEnable", 1);
    } else {
        $tpl->assign("autoLoginEnable", 0);
    }
    if ($centreon->user->getToken()) {
        $autoLoginUrl = "";
        if (!strstr($_SERVER['REQUEST_URI'], '?')) {
            $root_menu = get_my_first_allowed_root_menu($centreon->user->access->topologyStr);
            $autoLoginUrl .= "?p=".$root_menu["topology_page"];
        }
        $autoLoginUrl .= "&autologin=1&useralias=$userAlias&token=".$centreon->user->getToken();
        
        $prefix = '';
        if (!strncmp($_SERVER["SERVER_PROTOCOL"], "HTTP/", 5)) {
            $prefix .= "http://";
        } else {
            $prefix .= "https://";
        }
        $prefix .= $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
        $prefix = str_replace("main.php", "index.php", $prefix);
        $msg = _("Right Click here to add an autologin link directly to your bookmarks");
        $tpl->assign("autoLoginUrl", $prefix.$autoLoginUrl);
    } else {
        $msg = _("Please define autologin authentication key in your profile.");
        $tpl->assign("autoLoginUrl", '#');
    }
    $tpl->assign("CentreonAutologin", $msg);
}

/*
 * Grab elements for level 2
 */
$rq = "SELECT topology_page, topology_url_opt, topology_popup, topology_url, topology_name, topology_modules FROM topology WHERE topology_parent = '".$level1."' $lcaSTR AND topology_show = '1'  ORDER BY topology_group, topology_order";
$DBRESULT = $pearDB->query($rq);
$firstP = null;
$sep = "&nbsp;";
for ($i = 0; $DBRESULT->numRows() && ($elem = $DBRESULT->fetchRow()); $i++) {
    $firstP ? null : $firstP = $elem["topology_page"];
    
    $pageAccess = $centreon->user->access->page($elem["topology_page"]);
    if (($pageAccess == "1") || ($pageAccess == "2")) {
        $elemArr[2][$i] = array("Menu2Sep" => $sep,
                            "Menu2Url" => "main.php?p=".$elem["topology_page"].$elem["topology_url_opt"],
                            "Menu2UrlPopup" => $elem["topology_popup"],
                            "Menu2UrlPopupOpen" => $elem["topology_url"].$auth,
                            "Menu2Name" =>  $centreonMenu->translate($elem['topology_modules'], $elem['topology_url'], $elem["topology_name"]),
                            "Menu2Popup" => $elem["topology_popup"] ? true : false);
    }
    $sep = "&nbsp;|&nbsp;";
}

/*
 * Grab elements for level 3
 */
$request = "SELECT * FROM topology WHERE topology_parent = '".($level2 ? $level1.$level2 : $firstP)."' $lcaSTR AND topology_show = '1' AND topology_page is not null ORDER BY topology_group, topology_order";
$DBRESULT = $pearDB->query($request);
for ($i = 0; $elem = $DBRESULT->fetchRow(); $i++) {
    # grab menu title for each group
    $DBRESULT_title = $pearDB->query("SELECT topology_name FROM topology WHERE topology_parent = '".$elem["topology_parent"]."' AND topology_show = '1' AND topology_page IS NULL AND topology_group = '".$elem["topology_group"]."' LIMIT 1");
    $title = "";
    $topoName = $DBRESULT_title->fetchRow();
    if ($DBRESULT_title->numRows()) {
        $title = _($topoName['topology_name']);
    } else {
        $title = _("Main Menu");
    }

    $pageAccess = $centreon->user->access->page($elem["topology_page"]);
    if (($pageAccess == "1") || ($pageAccess == "2")) {
        $Menu3Url = "main.php?p=".$elem["topology_page"].$elem["topology_url_opt"];
        $elemArr[3][$elem["topology_group"]]["title"] = $title;
        $elemArr[3][$elem["topology_group"]]["tab"][$i] = array("Menu3Url" => $Menu3Url,
                            "Menu3ID" => $elem["topology_page"],
                            "MenuStyleClass" => $elem["topology_style_class"],
                            "MenuStyleID" => $elem["topology_style_id"],
                            "MenuOnClick" => $elem["topology_OnClick"],
                            "MenuIsOnClick" => $elem["topology_OnClick"] ? true : false,
                            "Menu3UrlPopup" => $elem["topology_url"],
                            "Menu3Name" => $centreonMenu->translate($elem['topology_modules'], $elem['topology_url'], $elem["topology_name"]),
                            "Menu3Popup" => $elem["topology_popup"] ? true : false);
    }
}
unset($elem);

/*
 * Grab elements for level 4
 */
if ($level1 && $level2 && $level3) {
    $request = "SELECT topology_page, topology_url_opt, topology_url, topology_OnClick, topology_name, topology_popup, topology_modules FROM topology WHERE topology_parent = '".$level1.$level2.$level3."' $lcaSTR AND topology_show = '1' ORDER BY topology_order";
    $DBRESULT = $pearDB->query($request);
    for ($i = 0; $elem = $DBRESULT->fetchRow(); $i++) {
        $pageAccess = $centreon->user->access->page($elem["topology_page"]);
        if (($pageAccess == "1") || ($pageAccess == "2")) {
            $elemArr[4][$level1.$level2.$level3][$i] = array("Menu4Url" => "main.php?p=".$elem["topology_page"].$elem["topology_url_opt"],
                                                             "Menu4UrlPopup" => $elem["topology_url"],
                                                             "MenuOnClick" => $elem["topology_OnClick"],
                                                             "MenuIsOnClick" => $elem["topology_OnClick"] ? true : false,
                                                             "Menu4Name" => $centreonMenu->translate($elem['topology_modules'], $elem['topology_url'], $elem["topology_name"]),
                                                             "Menu4Popup" => $elem["topology_popup"] ? true : false);
        }
        $centreonLang->bindLang();
    }
}

/*
 * Create Menu Level 1-2-3-4
 */
$tpl->assign("UserInfoUrl", $userUrl);
$tpl->assign("UserName", $centreon->user->get_alias());
$tpl->assign("Date", $logDate);
$tpl->assign("LogOut", $logOut);
$tpl->assign("LogOutUrl", $logOutUrl);
$tpl->assign("Menu1Color", "menu_1");
$tpl->assign("Menu1ID", "menu1_bgcolor");
$tpl->assign("Menu2Color", "menu_2");
$tpl->assign("Menu2ID", "menu2_bgcolor");
$tpl->assign("Menu3Color", "menu_3");
$tpl->assign("Menu3ID", "menu3_bgcolor");
$tpl->assign("Menu4Color", "menu_4");
$tpl->assign("Menu4ID", "menu4_bgcolor");
$tpl->assign("connected_users", _("Connected Users"));
$tpl->assign("main_menu", _("Main Menu"));

/*
 * Send ACL Topology in template
 */
$tpl->assign("topology", $centreon->user->access->topology);

/*
 * Assign for Smarty Template
 */
$tpl->assign("elemArr1", $elemArr[1]);
count($elemArr[2]) ? $tpl->assign("elemArr2", $elemArr[2]) : null;
count($elemArr[3]) ? $tpl->assign("elemArr3", $elemArr[3]) : null;
count($elemArr[4]) ? $tpl->assign("elemArr4", $elemArr[4]) : null;
$tpl->assign("idParent", $level1.$level2.$level3);

/*
 * User Online
 */
if ($is_admin) {
    $tab_user = array();
    $tab_user_admin = array();
    $tab_user_non_admin = array();
    $DBRESULT = $pearDB->query("SELECT session.session_id, contact.contact_alias, contact.contact_admin, session.user_id, session.ip_address FROM session, contact WHERE contact.contact_id = session.user_id
        ORDER BY contact.contact_alias");
    while ($session = $DBRESULT->fetchRow()) {
        if ($session["contact_admin"] == 1) {
            $tab_user_admin[$session["user_id"]] = array("ip"=>$session["ip_address"], "id"=>$session["user_id"], "alias"=>$session["contact_alias"], "admin"=>$session["contact_admin"]);
        } else {
            $tab_user_non_admin[$session["user_id"]] = array("ip"=>$session["ip_address"], "id"=>$session["user_id"], "alias"=>$session["contact_alias"], "admin"=>$session["contact_admin"]);
        }
    }
    
    $tab_user = array_merge($tab_user_admin, $tab_user_non_admin);
    unset($tab_user_admin);
    unset($tab_user_non_admin);
    $DBRESULT->free();
    $tpl->assign("tab_user", $tab_user);
}
$tpl->assign('amIadmin', $centreon->user->admin);

/*
 * Display
 */
$tpl->display("BlockHeader.ihtml");
$tpl->display("menu.ihtml");
count($elemArr[3]) ? $tpl->display("BlockMenuType3.ihtml") : $tpl->display("noLeftMenu.ihtml");
