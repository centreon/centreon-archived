<?
/**
Oreon is developped with GPL Licence 2.0 :
http://www.gnu.org/licenses/gpl.txt
Developped by : Julien Mathis - Romain Le Merlus

The Software is provided to you AS IS and WITH ALL FAULTS.
OREON makes no representation and gives no warranty whatsoever,
whether express or implied, and without limitation, with regard to the quality,
safety, contents, performance, merchantability, non-infringement or suitability for
any particular or intended purpose of the Software found on the OREON web site.
In no event will OREON be liable for any direct, indirect, punitive, special,
incidental or consequential damages however they may arise and even if OREON has
been previously advised of the possibility of such damages.

For information : contact@oreon.org
*/
	if (!isset($oreon))
		exit();
	$pagination = "maxViewMonitoring";		

	# set limit & num
	$res =& $pearDB->query("SELECT maxViewMonitoring FROM general_opt LIMIT 1");
	$gopt = array_map("myDecode", $res->fetchRow());		

	!isset ($_GET["limit"]) ? $limit = $gopt["maxViewMonitoring"] : $limit = $_GET["limit"];
	!isset($_GET["num"]) ? $num = 0 : $num = $_GET["num"];
	!isset($_GET["search"]) ? $search = 0 : $search = $_GET["search"];


	$tab_class = array("0" => "list_one", "1" => "list_two");
	$rows = 0;
	$service_status_num = array();
	if (isset($service_status))
		foreach ($service_status as $name => $svc){			
			$tmp = array();
			$tmp[0] = $name;		
			$service_status[$name]["status"] = $svc["status"];
			$service_status[$name]["status_td"] = "<td  class='ListColCenter' style='background:" . $oreon->optGen["color_".strtolower($svc["status"])] . "'>" . $svc["status"] . "</td>";
			$service_status[$name]["last_check"] = date($lang["date_time_format_status"], $svc["last_check"]);
			$service_status[$name]["last_change"] = Duration::toString(time() - $svc["last_change"]);
			$service_status[$name]["class"] = $tab_class[$rows % 2];
			$tmp[1] = $service_status[$name];
			$service_status_num[$rows++] = $tmp;
		}
	
	# Smarty template Init
	$tpl = new Smarty();
	$tpl = initSmartyTpl($path, $tpl, "/templates/");
	
	$lang['mon_host'] = "Hosts";
	$tpl->assign("p", $p);
	$tpl->assign("num", $num);
	$tpl->assign("limit", $limit);
	$tpl->assign("mon_host", $lang['mon_host']);
	$tpl->assign("mon_status", $lang['mon_status']);
	$tpl->assign("mon_ip", $lang['mon_ip']); 
	$tpl->assign("mon_last_check", $lang['mon_last_check']); 
	$tpl->assign("mon_duration", $lang['mon_duration']);
	$tpl->assign("mon_status_information", $lang['mon_status_information']); 




	# view tab
	$displayTab = array();
	$start = $num*$limit;
	for($i=$start; $i < ($limit+$start) && isset($service_status_num[$i])  ;$i++)
		$displayTab[$service_status_num[$i][0]] = $service_status_num[$i][1];
		$service_status = $displayTab;


	$form = new HTML_QuickForm('select_form', 'GET', "?p=".$p);


	$renderer =& new HTML_QuickForm_Renderer_ArraySmarty($tpl);
	$form->accept($renderer);


	if (isset($service_status))
		$tpl->assign("service_status", $service_status);
	if (!isset($_GET["sort_types"]))
		$_GET["sort_types"] = "host_name";
	$tpl->assign("sort_type", $_GET["sort_types"]);
	if (!isset($_GET["order"]))
		$_GET["order"] = "sort_asc";
	

	isset($_GET["host_name"]) ? $host_name = $_GET["host_name"] : $host_name = NULL;
	$tpl->assign("host_name", $host_name);
	isset($_GET["status"]) ? $status = $_GET["status"] : $status = NULL;
	$tpl->assign("status", $status);


	$tpl->assign("begin", $num);
	$tpl->assign("end", $limit);
	$tpl->assign("lang", $lang);
	$tpl->assign("order", $_GET["order"]);
	$tab_order = array("sort_asc" => "sort_desc", "sort_desc" => "sort_asc"); 
	$tpl->assign("tab_order", $tab_order);	

	
	$tpl->assign('form', $renderer->toArray());	
	$tpl->display("service.ihtml");

	$tpl = new Smarty();
	$tpl = initSmartyTpl("./", $tpl);
	$tpl->assign('lang', $lang);
	if ($oreon->optGen["nagios_version"] == 2 && isset($pgr_nagios_stat["created"])) 	
		$pgr_nagios_stat["created"] = date("d/m/Y G:i", $pgr_nagios_stat["created"]);
	else
		$pgr_nagios_stat["created"] = 0;
	$tpl->display("include/common/legend.ihtml");
	?>	