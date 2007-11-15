<?php
/**
Centreon is developped with GPL Licence 2.0 :
http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
Developped by : Julien Mathis - Romain Le Merlus - Christophe Coraboeuf

The Software is provided to you AS IS and WITH ALL FAULTS.
OREON makes no representation and gives no warranty whatsoever,
whether express or implied, and without limitation, with regard to the quality,
safety, contents, performance, merchantability, non-infringement or suitability for
any particular or intended purpose of the Software found on the OREON web site.
In no event will OREON be liable for any direct, indirect, punitive, special,
incidental or consequential damages however they may arise and even if OREON has
been previously advised of the possibility of such damages.

For information : contact@oreon-project.org
*/
	if (!isset($oreon))
		exit();

	# Path to the configuration dir
	$path = "./menu/";

	# ODS Database retrieve information
	$DBRESULT =& $pearDBO->query("SELECT * FROM config LIMIT 1");
	if (PEAR::isError($DBRESULT))
		print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";	
	$result_config = $DBRESULT->fetchRow();		
	if (isset($result_config) && $result_config)	
		$gopt = array_map("myDecode", $result_config);

	# Smarty template Init
	$tpl = new Smarty();
	$tpl = initSmartyTpl($path, $tpl);

	# Var init
	$sep = NULL;
	$elemArr = array(1=>array(), 2=>array(), 3=>array(), 4=>array());

	# Special Case
	# Put the authentification in the URL
	$auth = NULL;

	# block headerHTML
	$version = $oreon->user->get_version();

	$fileStatus = $oreon->Nagioscfg["status_file"];
	$fileOreonConf = $oreon->optGen["oreon_path"];

	$color = array();

	$color["OK"] = 			$oreon->optGen["color_ok"];
	$color["CRITICAL"] = 	$oreon->optGen["color_critical"];
	$color["WARNING"] = 	$oreon->optGen["color_warning"];
	$color["PENDING"] =  	$oreon->optGen["color_pending"];
	$color["UNKNOWN"] =  	$oreon->optGen["color_unknown"];
	$color["UP"] =  		$oreon->optGen["color_up"];
	$color["DOWN"] =  		$oreon->optGen["color_down"];
	$color["UNREACHABLE"] = $oreon->optGen["color_unreachable"];

	$tpl->assign("urlLogo", 'img/logo_centreon_wt.gif');
	$tpl->assign("lang", $lang);
	$tpl->assign("p", $p);
	$tpl->assign("color", $color);
	$tpl->assign("version", $version);
	$tpl->assign("fileStatus", $fileStatus);
	$tpl->assign("fileOreonConf", $fileOreonConf);
	$tpl->assign("date_time_format_status", $lang["date_time_format_status"]);

	# Grab elements for level 1
	$rq = "SELECT * FROM topology WHERE topology_parent IS NULL AND topology_page IN (".$oreon->user->lcaTStr.") AND topology_show = '1' ORDER BY topology_order";
	$DBRESULT =& $pearDB->query($rq);
	if (PEAR::isError($DBRESULT))
		print ($DBRESULT->getMessage());
	for($i = 0; $DBRESULT->numRows() && $DBRESULT->fetchInto($elem);$i++)
		$elemArr[1][$i] = array("Menu1ClassImg" => $level1 == $elem["topology_page"] ? "menu1_bgimg" : NULL,
								"Menu1Url" => "oreon.php?p=".$elem["topology_page"].$elem["topology_url_opt"],
								"Menu1UrlPopup" => $elem["topology_popup"],
								"Menu1UrlPopupOpen" => $elem["topology_url"],
								"Menu1Name" => array_key_exists($elem["topology_name"], $lang) ? $lang[$elem["topology_name"]] : "#UNDEF#",
								"Menu1Popup" => $elem["topology_popup"] ? true : false);
	$userUrl = "oreon.php?p=50104&o=c";
    $logDate = date($lang['header_format']);
    $logOut = $lang['m_logout'];
    $logOutUrl = "index.php?disconnect=1";

	# Grab elements for level 2
	$rq = "SELECT * FROM topology WHERE topology_parent = '".$level1."' AND topology_page IN (".$oreon->user->lcaTStr.") AND topology_show = '1'  ORDER BY topology_group, topology_order";
	$DBRESULT =& $pearDB->query($rq);
	if (PEAR::isError($DBRESULT))
		print ($DBRESULT->getMessage());
	$firstP = NULL;
	$sep = "&nbsp;";
	for($i = 0; $DBRESULT->numRows() && $DBRESULT->fetchInto($elem); $i++)	{
		$elem["topology_url"] == "./ext/osm/osm_jnlp.php" ? $auth = "?al=".md5($oreon->user->get_alias())."&pwd=".$oreon->user->get_passwd() : $auth = NULL;
		$firstP ? null : $firstP = $elem["topology_page"];
	    $elemArr[2][$i] = array("Menu2Sep" => $sep,
								"Menu2Url" => "oreon.php?p=".$elem["topology_page"].$elem["topology_url_opt"],
								"Menu2UrlPopup" => $elem["topology_popup"],
								"Menu2UrlPopupOpen" => $elem["topology_url"].$auth,
								"Menu2Name" => array_key_exists($elem["topology_name"], $lang) ? $lang[$elem["topology_name"]] : "#UNDEF#",
								"Menu2Popup" => $elem["topology_popup"] ? true : false);
		$sep = "|";
	}


	# Grab elements for level 3
	$rq = "SELECT * FROM topology WHERE topology_parent = '".($level2 ? $level1.$level2 : $firstP)."' AND topology_page IN (".$oreon->user->lcaTStr.") AND topology_show = '1' AND topology_page is not null ORDER BY topology_group, topology_order";
	$DBRESULT =& $pearDB->query($rq);
	if (PEAR::isError($DBRESULT))
		print ($DBRESULT->getMessage());
	for($i = 0; $DBRESULT->fetchInto($elem);$i++)	{

		if(!$gopt["archive_log"] && ( $elem["topology_page"] == 20311 || $elem["topology_page"] == 20312 || $elem["topology_page"] == 20313 || $elem["topology_page"] == 20314))
		{
			;
		}
		else
		{
			# grab menu title for each group
			$rq_title = "SELECT topology_name FROM topology WHERE topology_parent = '".$elem["topology_parent"]."' AND topology_show = '1' AND topology_page IS NULL AND topology_group = '".$elem["topology_group"]."' LIMIT 1";
			$DBRESULT_title =& $pearDB->query($rq_title);
			if (PEAR::isError($DBRESULT_title))
				print ($DBRESULT_title->getMessage());
			$title = "";
			if ($title = $DBRESULT_title->fetchRow())
				$title = $lang[$title["topology_name"]];
			else
				$title = $lang["m_main_menu"];
	
			$Menu3Url = "oreon.php?p=".$elem["topology_page"].$elem["topology_url_opt"];
			$elemArr[3][$elem["topology_group"]]["title"] = $title;
		    $elemArr[3][$elem["topology_group"]]["tab"][$i] = array("Menu3Icone" => $elem["topology_icone"],
									"Menu3Url" => $Menu3Url,
									"Menu3ID" => $elem["topology_page"],
									"MenuStyleClass" => $elem["topology_style_class"],
									"MenuStyleID" => $elem["topology_style_id"],
									"MenuOnClick" => $elem["topology_OnClick"],
									"MenuIsOnClick" => $elem["topology_OnClick"] ? true : false,
									"Menu3UrlPopup" => $elem["topology_url"],
									"Menu3Name" => array_key_exists($elem["topology_name"], $lang) ? $lang[$elem["topology_name"]] : "#UNDEF#",
									"Menu3Popup" => $elem["topology_popup"] ? true : false);
		}
	}
	unset($elem);
	# Grab elements for level 4
	if ($level1 && $level2 && $level3){
		$rq = "SELECT * FROM topology WHERE topology_parent = '".$level1.$level2.$level3."' AND topology_page IN (".$oreon->user->lcaTStr.") AND topology_show = '1' ORDER BY topology_order";
		$DBRESULT =& $pearDB->query($rq);
		if (PEAR::isError($DBRESULT))
			print ($DBRESULT->getMessage());
		for ($i = 0; $DBRESULT->fetchInto($elem);$i++){
			$elemArr[4][$level1.$level2.$level3][$i] = array("Menu4Icone" => $elem["topology_icone"],
										"Menu4Url" => "oreon.php?p=".$elem["topology_page"].$elem["topology_url_opt"],
										"Menu4UrlPopup" => $elem["topology_url"],
										"MenuOnClick" => $elem["topology_OnClick"],
										"MenuIsOnClick" => $elem["topology_OnClick"] ? true : false,
										"Menu4Name" => array_key_exists($elem["topology_name"], $lang) ? $lang[$elem["topology_name"]] : "#UNDEF#",
										"Menu4Popup" => $elem["topology_popup"] ? true : false);
		}
	}	
	
	# Create Menu Level 1-2-3-4
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

	$tpl->assign("connected_users", $lang["m_connected_users"]);
	$tpl->assign("main_menu", $lang["m_main_menu"]);

	# Assign for Smarty Template
	$tpl->assign("elemArr1", $elemArr[1]);
	count($elemArr[2]) ? $tpl->assign("elemArr2", $elemArr[2]) : NULL;
	count($elemArr[3]) ? $tpl->assign("elemArr3", $elemArr[3]) : NULL;
	count($elemArr[4]) ? $tpl->assign("elemArr4", $elemArr[4]) : NULL;

	$tpl->assign("idParent", $level1.$level2.$level3);

	# Legend icon
	$tpl->assign("legend1", $lang['m_help']);
	$tpl->assign("legend2", $lang['lgd_legend']);

	# User Online	
	$tab_user = array();
	$DBRESULT =& $pearDB->query("SELECT session.session_id, contact.contact_alias, contact.contact_admin, session.user_id, session.ip_address FROM session, contact WHERE contact.contact_id = session.user_id");
	if (PEAR::isError($DBRESULT))
		print ($DBRESULT->getMessage());
	while ($DBRESULT->fetchInto($session)){
		$tab_user[$session["user_id"]] = array();
		$tab_user[$session["user_id"]]["ip"] = $session["ip_address"];
		$tab_user[$session["user_id"]]["id"] = $session["user_id"];
		$tab_user[$session["user_id"]]["alias"] = $session["contact_alias"];
		$tab_user[$session["user_id"]]["admin"] = $session["contact_admin"];
	}
		
	$tpl->assign("tab_user", $tab_user);
	$tpl->assign('lang', $lang);
	$tpl->assign('amIadmin', $oreon->user->admin);
	
	# Display
	$tpl->display("BlockHeader.ihtml");
	$tpl->display("BlockMenuType1.ihtml");
	count($elemArr[2]) ? $tpl->display("BlockMenuType2.ihtml") : NULL;
	count($elemArr[3]) ? $tpl->display("BlockMenuType3.ihtml") : print '<div id="contener"><!-- begin contener --><table id="Tcontener"><tr><td id="Tmainpage" class="TcTD">';
?>