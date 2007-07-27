<?
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
	
	!isset($_GET["num"]) ? $begin = 0 : $begin = $_GET["num"];
	!isset($_GET["limit"]) ? $nb = 20 : $nb = $begin + $_GET["limit"];
	
	include("./include/common/autoNumLimit.php");
	
	# start quickSearch form
	include_once("./include/common/quickSearch.php");
	# end quickSearch form

	$color_en = array("1" => "#00ff00", "0" => "#ff0000");
	$color_en_label = array("1" => $lang['enable'], "0" => $lang['disable']);

	$tab_color = array("0" => "list_one", "1" => "list_two");
	$c = 0;
	if (isset($service_status))
		foreach ($service_status as $name => $svc){
			$svc["next_check"] ? $service_status[$name]["next_check"] = date($lang["date_time_format_status"], $svc["next_check"]) : $service_status[$name]["next_check"] = "";
			$svc["last_check"] ? $service_status[$name]["last_check"] = date($lang["date_time_format_status"], $svc["last_check"]) : $service_status[$name]["last_check"] = "";
			isset($host_status[$svc["host_name"]]) && $host_status[$svc["host_name"]]["current_state"] == "DOWN" ? $service_status[$name]["class"] = "list_down" : $service_status[$name]["class"] = $tab_color[++$c % 2];
			$c++;
		}
	
	# Smarty template Init
	$tpl = new Smarty();
	$tpl = initSmartyTpl($path, $tpl, "/templates/");
	
	$tpl->assign("p", $p);
	$tpl->assign("mon_host", $lang['m_mon_hosts']);
	$tpl->assign("mon_status", $lang['mon_status']);
	$tpl->assign("mon_ip", $lang['mon_ip']); 
	$tpl->assign("mon_last_check", $lang['mon_last_check']); 
	$tpl->assign("mon_duration", $lang['mon_duration']);
	$tpl->assign("mon_status_information", $lang['mon_status_information']); 
	
	if (isset($service_status))
		$tpl->assign("service_status", $service_status);
	if (!isset($_GET["sort_types"]))
		$_GET["sort_types"] = "next_check";
	$tpl->assign("sort_type", $_GET["sort_types"]);
	if (!isset($_GET["order"]))
		$_GET["order"] = "sort_asc";

	isset($_GET["host_name"]) ? $host_name = $_GET["host_name"] : $host_name = NULL;
	$tpl->assign("host_name", $host_name);
	isset($_GET["status"]) ? $status = $_GET["status"] : $status = NULL;
	$tpl->assign("status", $status);
	$tpl->assign("refresh", $oreon->optGen["oreon_refresh"]);
	$tpl->assign("begin", $begin);
	$tpl->assign("end", $nb);
	$tpl->assign("lang", $lang);
	$tpl->assign("color_en", $color_en);
	$tpl->assign("color_en_label", $color_en_label);
	$tpl->assign("order", $_GET["order"]);
	$tab_order = array("sort_asc" => "sort_desc", "sort_desc" => "sort_asc"); 
	$tpl->assign("tab_order", $tab_order);
	$tpl->display("serviceSchedule.ihtml");

	$tpl = new Smarty();
	$tpl = initSmartyTpl("./", $tpl);
	$tpl->assign('lang', $lang);
?>	
</div>