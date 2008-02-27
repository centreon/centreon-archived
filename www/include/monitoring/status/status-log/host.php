<?php
/**
Centreon is developped with GPL Licence 2.0 :
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

	$pagination = "maxViewMonitoring";
	
	# set limit & num
	$DBRESULT =& $pearDB->query("SELECT maxViewMonitoring FROM general_opt LIMIT 1");
	if (PEAR::isError($DBRESULT)) 
		print "Mysql Error : ".$DBRESULT->getMessage();
	$gopt = array_map("myDecode", $DBRESULT->fetchRow());

	!isset ($_GET["limit"]) ? $limit = $gopt["maxViewMonitoring"] : $limit = $_GET["limit"];
	!isset($_GET["num"]) ? $num = 0 : $num = $_GET["num"];
	!isset($_GET["search"]) ? $search = 0 : $search = $_GET["search"];

	$tab_class = array("0" => "list_one", "1" => "list_two");
	$rows = 0;
	$host_status_num = array();
	foreach ($host_status as $name => $h){
		$tmp = array();
		$tmp[0] = $name;			
		$DBRESULT =& $pearDB->query("SELECT host_address FROM host WHERE host_name = '".$name."'");
		if (PEAR::isError($DBRESULT)) 
			print "Mysql Error : ".$DBRESULT->getMessage();
		$DBRESULT->fetchInto($host);
		if ($oreon->user->admin || HadUserLca($pearDB) == 0 || (HadUserLca($pearDB) && isset($lcaHostByName["LcaHost"][$name]))){
			$host_status[$name]["address"] = $host["host_address"];
			$host_status[$name]["status_color"] = $oreon->optGen["color_".strtolower($h["current_state"])];
			$host_status[$name]["last_check"] = $host_status[$name]["last_check"] ? date(_("d/m/Y H:i:s"), $h["last_check"]) : "" ;
			$host_status[$name]["last_state_change"] = $host_status[$name]["last_state_change"] ? Duration::toString(time() - $h["last_state_change"]) : "";
			$host_status[$name]["class"] = $tab_class[$rows % 2];
			$host_status[$name]["name"] = $name;
			$host_status[$name]["active_checks_enabled"] = $host_status[$name]["active_checks_enabled"];
			$host_status[$name]["passive_checks_enabled"] = $host_status[$name]["passive_checks_enabled"];
			$host_status[$name]["has_been_acknowledged"] = $host_status[$name]["problem_has_been_acknowledged"];	
			$host_status[$name]["scheduled_downtime_depth"] = $host_status[$name]["scheduled_downtime_depth"];
			$host_status[$name]["notifications_enabled"] = $host_status[$name]["notifications_enabled"];
			$tmp[1] = $host_status[$name];
			$host_status_num[$rows++] = $tmp;
		}
	}

	# Smarty template Init
	$tpl = new Smarty();
	$tpl = initSmartyTpl($path, $tpl, "/templates/");

	# view tab
	$displayTab = array();
	$start = $num * $limit;
	for($i=$start; isset($host_status_num[$i]) && $i < $limit+$start ;$i++)
		$displayTab[$host_status_num[$i][0]] = $host_status_num[$i][1];
	$host_status = $displayTab;

	$form = new HTML_QuickForm('select_form', 'GET', "?p=".$p);
	$renderer =& new HTML_QuickForm_Renderer_ArraySmarty($tpl);
	$form->accept($renderer);
	
	$tpl->assign("p", $p);
	$tpl->assign("num", $num);
	$tpl->assign("limit", $limit);
	$tpl->assign("mon_host", _("Hosts"));
	$tpl->assign("mon_status", _("Status"));
	$tpl->assign("mon_last_check", _("Last Check")); 
	$tpl->assign("mon_duration", _("Duration"));
	$tpl->assign("mon_status_information", _("Status information")); 
	$tpl->assign("host_status", $host_status);
	if (!isset($_GET["sort_typeh"]))
		$_GET["sort_typeh"] = "name";
	$tpl->assign("sort_type", $_GET["sort_typeh"]);
	if (!isset($_GET["order"]))
		$_GET["order"] = "sort_asc";
	$tpl->assign("order", $_GET["order"]);
	$tab_order = array("sort_asc" => "sort_desc", "sort_desc" => "sort_asc"); 
	$tpl->assign("tab_order", $tab_order);

	$tpl->assign("refresh", $oreon->optGen["oreon_refresh"]);
	$DBRESULT =& $pearDB->query(	"SELECT * FROM session WHERE CONVERT( `session_id` USING utf8 ) = '".session_id()."' AND `user_id` = '".$oreon->user->user_id."' LIMIT 1");
	if (PEAR::isError($DBRESULT))
		print "Mysql Error : ".$DBRESULT->getMessage();
	$session =& $DBRESULT->fetchRow();
    $tpl->assign('sid', session_id());
    $tpl->assign('slastreload', $session["last_reload"]);
    $tpl->assign('smaxtime', $session_expire["session_expire"]);
	$tpl->assign("lang", $lang);
	$tpl->assign('form', $renderer->toArray());
	$tpl->display("host.ihtml");	
?>