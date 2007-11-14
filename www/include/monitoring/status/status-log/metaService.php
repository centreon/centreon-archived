<?php
/**
Oreon is developped with GPL Licence 2.0 :
http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
Developped by : Julien Mathis - Romain Le Merlus

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
			
	$DBRESULT =& $pearDB->query("SELECT * FROM meta_service WHERE meta_activate = '1'");
	if (PEAR::isError($DBRESULT))
		print "Mysql Error : ".$DBRESULT->getMessage();
	while ($DBRESULT->fetchInto($meta)){
		$metaService_status_bis["meta_" . $meta["meta_id"]]["real_name"] = $meta["meta_name"]; 
		$metaService_status_bis["meta_" . $meta["meta_id"]]["id"] = $meta["meta_id"]; 
	}
	$tab_class = array("0" => "list_one", "1" => "list_two");
	$c = 0;
	if (isset($metaService_status)){
		foreach ($metaService_status as $name => $svc){
			if (strstr($name, "meta_") && isset($metaService_status[$name]["current_state"])){				
				$metaService_status_bis[$name]["status_color"] = $oreon->optGen["color_".strtolower($svc["current_state"])];
				$metaService_status_bis[$name]["last_check"] = date($lang["date_time_format_status"], $svc["last_check"]);
				$metaService_status_bis[$name]["last_state_change"] = Duration::toString(time() - $svc["last_state_change"]);
				$metaService_status_bis[$name]["class"] = $tab_class[$c % 2];
				$metaService_status_bis[$name]["current_state"] = $metaService_status[$name]["current_state"];
				$metaService_status_bis[$name]["plugin_output"] = $metaService_status[$name]["plugin_output"];
				$metaService_status_bis[$name]["current_attempt"] = $metaService_status[$name]["current_attempt"];
				$metaService_status_bis[$name]["notifications_enabled"] = $metaService_status[$name]["notifications_enabled"];
				$metaService_status_bis[$name]["active_checks_enabled"] = $metaService_status[$name]["active_checks_enabled"];
				$metaService_status_bis[$name]["passive_checks_enabled"] = $metaService_status[$name]["passive_checks_enabled"];
				$c++;
			}
		}
	}
				
	# Smarty template Init
	$tpl = new Smarty();
	$tpl = initSmartyTpl($path, $tpl, "/templates/");
	
	$lang['mon_host'] = "Hosts";
	$tpl->assign("p", $p);
	$tpl->assign("mon_status", $lang['mon_status']);
	$tpl->assign("mon_ip", $lang['mon_ip']); 
	$tpl->assign("mon_last_check", $lang['mon_last_check']); 
	$tpl->assign("mon_duration", $lang['mon_duration']);
	$tpl->assign("mon_status_information", $lang['mon_status_information']);

	if (!isset($_GET["sort_types"]))
		$_GET["sort_types"] = "host_name";
	$tpl->assign("sort_type", $_GET["sort_types"]);
	if (!isset($_GET["order"]))
		$_GET["order"] = "sort_asc";

	$tpl->assign("refresh", $oreon->optGen["oreon_refresh"]);
	!isset($_GET["num"]) ? $begin = 0 : $begin = $_GET["num"];
	!isset($_GET["limit"]) ? $nb = 20 : $nb = $begin + $_GET["limit"];

	if (isset($metaService_status_bis) )
		$tpl->assign("metaService_status", $metaService_status_bis);
	
	$tpl->assign("begin", $begin);
	$tpl->assign("end", $nb);
	$tpl->assign("lang", $lang);
	$tpl->assign("order", $_GET["order"]);
	$tab_order = array("sort_asc" => "sort_desc", "sort_desc" => "sort_asc"); 
	$tpl->assign("tab_order", $tab_order);
	$tpl->display("metaService.ihtml");	
?>