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

	$tab_class = array("0" => "list_one", "1" => "list_two");
	$cpt = 0;
	foreach ($host_status as $name => $h){
		$res =& $pearDB->query("SELECT host_address FROM host WHERE host_name = '".$name."'");
			$res->fetchInto($host);
			$host_status[$name]["address"] = $host["host_address"];
			$host_status[$name]["status_td"] = "<td bgcolor='" . $oreon->optGen["color_".strtolower($h["status"])] . "' align='center'>" . $h["status"] . "</td>";
			$host_status[$name]["last_check"] = date($lang["date_time_format_status"], $h["last_check"]);
			$host_status[$name]["last_stat"] = Duration::toString(time() - $h["last_stat"]);
			$host_status[$name]["class"] = $tab_class[$cpt % 2];
			$host_status[$name]["name"] = $name;
			$cpt++;
	}
	
	# Smarty template Init
	$tpl = new Smarty();
	$tpl = initSmartyTpl($path, $tpl, "/templates/");
	
	$lang['mon_host'] = "Hosts";
	$tpl
	->assign("p", $p);
	$tpl->assign("mon_host", $lang['mon_host']);
	$tpl->assign("mon_status", $lang['mon_status']);
	$tpl->assign("mon_last_check", $lang['mon_last_check']); 
	$tpl->assign("mon_duration", $lang['mon_duration']);
	$tpl->assign("mon_status_information", $lang['mon_status_information']); 
	$tpl->assign("host_status", $host_status);
	if (!isset($_GET["sort_typeh"]))
		$_GET["sort_typeh"] = "name";
	$tpl->assign("sort_type", $_GET["sort_typeh"]);
	if (!isset($_GET["order"]))
		$_GET["order"] = "sort_asc";
	$tpl->assign("order", $_GET["order"]);
	$tab_order = array("sort_asc" => "sort_desc", "sort_desc" => "sort_asc"); 
	$tpl->assign("tab_order", $tab_order);
	$tpl->assign("lang", $lang);
	$tpl->display("host_problem.ihtml");

	$tpl = new Smarty();
	$tpl = initSmartyTpl("./", $tpl);
	$tpl->assign('lang', $lang);
	$tpl->display("include/common/legend.ihtml");	
?>	