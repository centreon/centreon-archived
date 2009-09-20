<?php
/*
 * Copyright 2005-2009 MERETHIS
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
 * As a special exception, the copyright holders of this program give MERETHIS 
 * permission to link this program with independent modules to produce an executable, 
 * regardless of the license terms of these independent modules, and to copy and 
 * distribute the resulting executable under terms of MERETHIS choice, provided that 
 * MERETHIS also meet, for each linked independent module, the terms  and conditions 
 * of the license of that module. An independent module is a module which is not 
 * derived from this program. If you modify this program, you may extend this 
 * exception to your version of the program, but you are not obliged to do so. If you
 * do not wish to do so, delete this exception statement from your version.
 * 
 * For more information : contact@centreon.com
 * 
 * SVN : $URL$
 * SVN : $Id$
 * 
 */
 
	if (!isset($oreon))
		exit();

	/*
	 * Path to the configuration dir
	 */
	$path = "./menu/templates";
	$user_update_pref = "./menu/userMenuPreferences.php";


	/*
	 * Smarty template Init
	 */
	$tpl = new Smarty();
	$tpl = initSmartyTpl($path, $tpl);

	/*
	 * Var init
	 */
	$sep = NULL;
	$elemArr = array(1 => array(), 2 => array(), 3 => array(), 4 => array());

	/*
	 * Special Case
	 * Put the authentification in the URL
	 */
	$auth = NULL;

	require_once("./menu/MenuJS.php");

	/*
	 * block headerHTML
	 */
	$version = $oreon->user->get_version();

	$fileStatus = $oreon->Nagioscfg["status_file"];
	$fileCentreonConf = $oreon->optGen["oreon_path"];

	$color = array();

	$color["OK"] = 			$oreon->optGen["color_ok"];
	$color["CRITICAL"] = 	$oreon->optGen["color_critical"];
	$color["WARNING"] = 	$oreon->optGen["color_warning"];
	$color["PENDING"] =  	$oreon->optGen["color_pending"];
	$color["UNKNOWN"] =  	$oreon->optGen["color_unknown"];

	$color["UP"] =  		$oreon->optGen["color_up"];
	$color["DOWN"] =  		$oreon->optGen["color_down"];
	$color["UNREACHABLE"] = $oreon->optGen["color_unreachable"];

	$tpl->assign("urlLogo", 'img/centreon.gif');
	
	$tpl->assign("Ok", _("Ok"));
	$tpl->assign("Warning", _("Warning"));
	$tpl->assign("Critical", _("Critical"));
	$tpl->assign("Unknown", _("Unknown"));
	$tpl->assign("Pending", _("Pending"));
	$tpl->assign("Up", _("Up"));
	$tpl->assign("Down", _("Down"));
	$tpl->assign("Unreachable", _("Unreachable"));
	$tpl->assign("Hosts_States", _("Host States"));
	$tpl->assign("Services_States", _("Service States"));
	$tpl->assign("Logout", _("Logout"));
	$tpl->assign("Help", _("Help"));
	$tpl->assign("Documentation", _("Documentation"));
	$tpl->assign("p", $p);
	$tpl->assign("color", $color);
	$tpl->assign("version", $version);
	$tpl->assign("fileStatus", $fileStatus);
	$tpl->assign("fileOreonConf", $fileCentreonConf);
	$tpl->assign("date_time_format_status", _("d/m/Y H:i:s"));

	/*
	 * Display Login
	 */
	$tpl->assign("user_login", $oreon->user->get_alias());
	$tpl->assign("loggedlabel", _("You are"));

	/*
	 * Fixe ACL
	 */

	$lcaSTR = "";
	if (!$is_admin)
		$lcaSTR = "AND topology_page IN (".$oreon->user->access->getTopologyString().")";

	/*
	 * Grab elements for level 1
	 */
	$rq = "SELECT * FROM topology WHERE topology_parent IS NULL $lcaSTR AND topology_show = '1' ORDER BY topology_order";
	$DBRESULT =& $pearDB->query($rq);
	for ($i = 0; $DBRESULT->numRows() && ($elem =& $DBRESULT->fetchRow()); $i++)
		$elemArr[1][$i] = array("Menu1ClassImg" => $level1 == $elem["topology_page"] ? "menu1_bgimg" : "id_".$elem["topology_id"],
								"Menu1Page" => $elem["topology_page"] ,
								"Menu1Url" => "main.php?p=".$elem["topology_page"].$elem["topology_url_opt"],
								"Menu1UrlPopup" => $elem["topology_popup"],
								"Menu1UrlPopupOpen" => $elem["topology_url"],
								"Menu1Name" => _($elem["topology_name"]),
								"Menu1Popup" => $elem["topology_popup"] ? true : false);
	$DBRESULT->free();
	
	$userUrl = "main.php?p=50104&o=c";
	
	$logDate = $oreon->CentreonGMT->getDate(_("Y/m/d G:i"), time(), $oreon->user->getMyGMT());
    $logOut = _("Logout");
    $logOutUrl = "index.php?disconnect=1";

	/*
	 * Define autologin URL
	 */
	if (isset($oreon->optGen["display_autologin_shortcut"])) {
		$userCrypted = $oreon->user->userCrypted;
		$passwdCrypted = $oreon->user->get_passwd();
		$autoLoginUrl = "p=$p&o=$o&min=$min&autologin=1&useralias=$userCrypted&password=$passwdCrypted";
		$tpl->assign("autoLoginEnable", $oreon->optGen["display_autologin_shortcut"]);
		$tpl->assign("autoLoginUrl", $autoLoginUrl);
		$tpl->assign("CentreonAutologin", _("Centreon Autologin URL"));		
	}

	/*
	 * Grab elements for level 2
	 */
	$rq = "SELECT topology_page, topology_url_opt, topology_popup, topology_url, topology_name FROM topology WHERE topology_parent = '".$level1."' $lcaSTR AND topology_show = '1'  ORDER BY topology_group, topology_order";
	$DBRESULT =& $pearDB->query($rq);
	$firstP = NULL;
	$sep = "&nbsp;";
	for ($i = 0; $DBRESULT->numRows() && ($elem =& $DBRESULT->fetchRow()); $i++)	{
		$firstP ? null : $firstP = $elem["topology_page"];
	    $elemArr[2][$i] = array("Menu2Sep" => $sep,
								"Menu2Url" => "main.php?p=".$elem["topology_page"].$elem["topology_url_opt"],
								"Menu2UrlPopup" => $elem["topology_popup"],
								"Menu2UrlPopupOpen" => $elem["topology_url"].$auth,
								"Menu2Name" => _($elem["topology_name"]),
								"Menu2Popup" => $elem["topology_popup"] ? true : false);
		$sep = "&nbsp;|&nbsp;";
	}

	/*
	 * Grab elements for level 3
	 */
	$request = "SELECT * FROM topology WHERE topology_parent = '".($level2 ? $level1.$level2 : $firstP)."' $lcaSTR AND topology_show = '1' AND topology_page is not null ORDER BY topology_group, topology_order";	
	$DBRESULT =& $pearDB->query($request);
	for ($i = 0; $elem =& $DBRESULT->fetchRow();$i++)	{
		# grab menu title for each group
		$DBRESULT_title =& $pearDB->query("SELECT topology_name FROM topology WHERE topology_parent = '".$elem["topology_parent"]."' AND topology_show = '1' AND topology_page IS NULL AND topology_group = '".$elem["topology_group"]."' LIMIT 1");
		$title = "";
		$topoName = $DBRESULT_title->fetchRow();
		if ($DBRESULT_title->numRows())
			$title = _($topoName['topology_name']);
		else
			$title = _("Main Menu");

		$Menu3Url = "main.php?p=".$elem["topology_page"].$elem["topology_url_opt"];
		$elemArr[3][$elem["topology_group"]]["title"] = $title;
	    $elemArr[3][$elem["topology_group"]]["tab"][$i] = array("Menu3Icone" => $elem["topology_icone"],
								"Menu3Url" => $Menu3Url,
								"Menu3ID" => $elem["topology_page"],
								"MenuStyleClass" => $elem["topology_style_class"],
								"MenuStyleID" => $elem["topology_style_id"],
								"MenuOnClick" => $elem["topology_OnClick"],
								"MenuIsOnClick" => $elem["topology_OnClick"] ? true : false,
								"Menu3UrlPopup" => $elem["topology_url"],
								"Menu3Name" => _($elem["topology_name"]),
								"Menu3Popup" => $elem["topology_popup"] ? true : false);
	}
	unset($elem);

	/*
	 * Grab elements for level 4
	 */
	if ($level1 && $level2 && $level3){
		$request = "SELECT topology_icone, topology_page, topology_url_opt, topology_url, topology_OnClick, topology_name, topology_popup FROM topology WHERE topology_parent = '".$level1.$level2.$level3."' $lcaSTR AND topology_show = '1' ORDER BY topology_order";
		$DBRESULT =& $pearDB->query($request);
		for ($i = 0; $elem =& $DBRESULT->fetchRow();$i++){
			$elemArr[4][$level1.$level2.$level3][$i] = array(	"Menu4Icone" => $elem["topology_icone"],
																"Menu4Url" => "main.php?p=".$elem["topology_page"].$elem["topology_url_opt"],
																"Menu4UrlPopup" => $elem["topology_url"],
																"MenuOnClick" => $elem["topology_OnClick"],
																"MenuIsOnClick" => $elem["topology_OnClick"] ? true : false,
																"Menu4Name" => _($elem["topology_name"]),
																"Menu4Popup" => $elem["topology_popup"] ? true : false);
		}
	}

	/*
	 * Create Menu Level 1-2-3-4
	 */
	$tpl->assign("PageID", $p);	 
	$tpl->assign("UserInfoUrl", $userUrl);
	$tpl->assign("UserName", $oreon->user->get_alias());
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
	$tpl->assign("connected_users", _("Connected"));
	$tpl->assign("main_menu", _("Main Menu"));
	
	/*
	 * Send ACL Topology in template
	 */
	$tpl->assign("topology", $oreon->user->access->topology);

	/*
	 * Assign for Smarty Template
	 */
	$tpl->assign("elemArr1", $elemArr[1]);
	count($elemArr[2]) ? $tpl->assign("elemArr2", $elemArr[2]) : NULL;
	count($elemArr[3]) ? $tpl->assign("elemArr3", $elemArr[3]) : NULL;
	count($elemArr[4]) ? $tpl->assign("elemArr4", $elemArr[4]) : NULL;
	$tpl->assign("idParent", $level1.$level2.$level3);
	
	/*
	 * Legend icon
	 */
	$tpl->assign("legend1", _("Help"));
	$tpl->assign("legend2", _("Legend"));

	/*
	 *  User's preference
	 */
	$tpl->assign("user_update_pref_header", $user_update_pref . "?uid=".$oreon->user->user_id."&div=header");
	$tpl->assign("user_update_pref_menu_3", $user_update_pref . "?uid=".$oreon->user->user_id."&div=menu_3");
	
	/*
	 * User Online
	 */
	if ($is_admin){
		$tab_user = array();
		$DBRESULT =& $pearDB->query("SELECT session.session_id, contact.contact_alias, contact.contact_admin, session.user_id, session.ip_address FROM session, contact WHERE contact.contact_id = session.user_id");
		while ($session =& $DBRESULT->fetchRow())
			$tab_user[$session["user_id"]] = array("ip"=>$session["ip_address"], "id"=>$session["user_id"], "alias"=>$session["contact_alias"], "admin"=>$session["contact_admin"]);	
		$DBRESULT->free();
		$tpl->assign("tab_user", $tab_user);
	}
	$tpl->assign('amIadmin', $oreon->user->admin);
	# Display
	$tpl->display("BlockHeader.ihtml");
	$tpl->display("menu.ihtml");
	//$tpl->display("BlockMenuType1.ihtml");	
	//count($elemArr[2]) ? $tpl->display("BlockMenuType2.ihtml") : NULL;
	count($elemArr[3]) ? $tpl->display("BlockMenuType3.ihtml") : print '<div id="contener"><!-- begin contener --><table id="Tcontener"><tr><td id="Tmainpage" class="TcTD">';
?>