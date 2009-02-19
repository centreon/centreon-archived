<?php
/*
 * Centreon is developped with GPL Licence 2.0 :
 * http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 * Developped by : Julien Mathis - Romain Le Merlus 
 * 
 * The Software is provided to you AS IS and WITH ALL FAULTS.
 * Centreon makes no representation and gives no warranty whatsoever,
 * whether express or implied, and without limitation, with regard to the quality,
 * any particular or intended purpose of the Software found on the Centreon web site.
 * In no event will Centreon be liable for any direct, indirect, punitive, special,
 * incidental or consequential damages however they may arise and even if Centreon has
 * been previously advised of the possibility of such damages.
 * 
 * For information : contact@centreon.com
 */
	if (!isset($oreon))
		exit();
	
	$path = "./include/options/session/";	
	
	require_once "./include/common/common-Func.php";
	require_once "./class/centreonMsg.class.php";
		
	if (isset($_GET["o"]) && $_GET["o"] == "k"){
		$pearDB->query("DELETE FROM session WHERE session_id = '".$_GET["session_id"]."'");
		$msg = new CentreonMsg();
		$msg->setTextStyle("bold");
		$msg->setText(_("User kicked"));
		$msg->setTimeOut("3");
	}	

	# Smarty template Init
	$tpl = new Smarty();
	$tpl = initSmartyTpl($path, $tpl);

	$res =& $pearDB->query("SELECT * FROM session");
	$session_data = array();
	$cpt = 0;
	while ($r =& $res->fetchRow()){
		$session_data[$cpt] = array();
		if ($cpt % 2)
			$session_data[$cpt]["class"] = "list_one";
		else
			$session_data[$cpt]["class"] = "list_two";
		$resUser =& $pearDB->query("SELECT contact_name, contact_admin FROM contact WHERE contact_id = '".$r["user_id"]."'");
		$rU =& $resUser->fetchRow();	
		$session_data[$cpt]["user_id"] = $r["user_id"];
		$session_data[$cpt]["user_alias"] = $rU["contact_name"];
		$session_data[$cpt]["admin"] = $rU["contact_admin"];
		$resCP =& $pearDB->query("SELECT topology_name, topology_icone, topology_page, topology_url_opt FROM topology WHERE topology_page = '".$r["current_page"]."'");
		$rCP =& $resCP->fetchRow();
		$session_data[$cpt]["ip_address"] = $r["ip_address"];
		$session_data[$cpt]["current_page"] = $r["current_page"].$rCP["topology_url_opt"];
		$session_data[$cpt]["topology_name"] = _($rCP["topology_name"]);
		if ($rCP["topology_icone"])
			$session_data[$cpt]["topology_icone"] = "<img src='".$rCP["topology_icone"]."'>";
		else
			$session_data[$cpt]["topology_icone"] = "&nbsp;";
		$session_data[$cpt]["last_reload"] = date("H:i:s", $r["last_reload"]);
		$session_data[$cpt]["actions"] = "<a href='./main.php?p=$p&o=k&session_id=".$r["session_id"]."'><img src='./img/icones/16x16/flash.gif' border='0' alt='"._("Kick User")."' title='"._("Kick User")."'></a>";
		$cpt++;
	}
	if (isset($msg))
		$tpl->assign("msg", $msg);
		
	$tpl->assign("session_data", $session_data);
	$tpl->assign("wi_user", _("Users"));
	$tpl->assign("wi_where", _("Position"));
	$tpl->assign("wi_last_req", _("Last request"));
	$tpl->assign("distant_location", _("IP Address"));
	$tpl->display("connected_user.ihtml");
?>