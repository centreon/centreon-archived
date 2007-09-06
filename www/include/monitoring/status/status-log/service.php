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
		
	$pagination = "maxViewMonitoring";
	
	include("./include/common/autoNumLimit.php");

	#create javascript for refresh ajax
	//include('./include/monitoring/status/makeJS.php');
	
	# set limit & num
	$DBRESULT =& $pearDB->query("SELECT maxViewMonitoring FROM general_opt LIMIT 1");
	if (PEAR::isError($DBRESULT))
		print "Mysql Error : ".$DBRESULT->getMessage();
	$gopt = array_map("myDecode", $DBRESULT->fetchRow());

	!isset($_GET["sort_types"]) ? $sort_types = 0 : $sort_types = $_GET["sort_types"];

	# start quickSearch form
	include_once("./include/common/quickSearch.php");
	# end quickSearch form
	
	$tab_class = array("0" => "list_one", "1" => "list_two");
	$rows = 0;
	$service_status_num = array();
	if (isset($service_status))
		foreach ($service_status as $name => $svc){
			if (!isset($_GET["host_name"]) || (isset($_GET["host_name"]) && $_GET["host_name"] == $service_status[$name]["host_name"])){
				$tmp = array();
				$tmp[0] = $name;
				$service_status[$name]["host_status"] = $host_status[$service_status[$name]["host_name"]]["current_state"];
				$service_status[$name]["host_has_been_acknowledged"] = $host_status[$service_status[$name]["host_name"]]["problem_has_been_acknowledged"];
				$service_status[$name]["host_active_checks_enabled"] = $host_status[$service_status[$name]["host_name"]]["active_checks_enabled"];
				$service_status[$name]["host_passive_checks_enabled"] = $host_status[$service_status[$name]["host_name"]]["passive_checks_enabled"];
				$service_status[$name]["host_notifications_enabled"] = $host_status[$service_status[$name]["host_name"]]["notifications_enabled"];
				$service_status[$name]["host_scheduled_downtime_depth"] = $host_status[$service_status[$name]["host_name"]]["scheduled_downtime_depth"];
				$service_status[$name]["service_scheduled_downtime_depth"] = $svc["scheduled_downtime_depth"];
				$service_status[$name]["host_color"] = $oreon->optGen["color_".strtolower($service_status[$name]["host_status"])];
				$service_status[$name]["status_color"] = $oreon->optGen["color_".strtolower($svc["current_state"])];
				if ($svc["last_check"]){
					$service_status[$name]["last_check"] = date($lang["date_time_format_status"], $svc["last_check"]);
					$service_status[$name]["last_state_change"] = Duration::toString(time() - $svc["last_state_change"]);
				} else {
					$service_status[$name]["last_check"] = "";
					$service_status[$name]["last_state_change"] = "";
				}
				$service_status[$name]["class"] = $tab_class[$rows % 2];
				$service_status[$name]["service_description-link"] = str_replace("/", "#S#", $service_status[$name]["service_description"]);
				$service_status[$name]["service_description-link"] = str_replace("\\", "#BS#", $service_status[$name]["service_description-link"]);
				$tmp[1] = $service_status[$name];
				$service_status_num[$rows++] = $tmp;
			}
		}
		
	include("./include/common/checkPagination.php");
	
	# Smarty template Init
	$tpl = new Smarty();
	$tpl = initSmartyTpl($path, $tpl, "/templates/");
	
	$tpl->assign("p", $p);
	$tpl->assign('o', $o);
	$tpl->assign("sort_types", $sort_types);
	$tpl->assign("num", $num);
	$tpl->assign("limit", $limit);
	$tpl->assign("mon_host", $lang['m_mon_hosts']);
	$tpl->assign("mon_status", $lang['mon_status']);
	$tpl->assign("mon_ip", $lang['mon_ip']); 
	$tpl->assign("mon_last_check", $lang['mon_last_check']); 
	$tpl->assign("mon_duration", $lang['mon_duration']);
	$tpl->assign("mon_status_information", $lang['mon_status_information']); 

	# view tab
	$displayTab = array();
	$start = $num * $limit;
	for($i=$start ; $i < ($limit+$start) && isset($service_status_num[$i]) ;$i++)
		$displayTab[$service_status_num[$i][0]] = $service_status_num[$i][1];
	$service_status = $displayTab;


	$form = new HTML_QuickForm('select_form', 'GET', "?p=".$p);	
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
	$tpl->assign("order", strtolower($_GET["order"]));
	$tab_order = array("sort_asc" => "sort_desc", "sort_desc" => "sort_asc"); 
	$tpl->assign("tab_order", $tab_order);	

    $tpl->assign('time', time());
    $tpl->assign('fileStatus',  $oreon->Nagioscfg["status_file"]);
    $tpl->assign('fileOreonConf', $oreon->optGen["oreon_path"]);
    $tpl->assign('color_OK', $oreon->optGen["color_ok"]);
    $tpl->assign('color_CRITICAL', $oreon->optGen["color_critical"]);
    $tpl->assign('color_WARNING', $oreon->optGen["color_warning"]);
    $tpl->assign('color_UNKNOWN', $oreon->optGen["color_unknown"]);
    $tpl->assign('color_PENDING', $oreon->optGen["color_pending"]);
    $tpl->assign('color_UP', $oreon->optGen["color_up"]);
    $tpl->assign('color_DOWN', $oreon->optGen["color_down"]);
    $tpl->assign('color_UNREACHABLE', $oreon->optGen["color_unreachable"]);

	$version = $oreon->user->get_version();

    $tpl->assign("version", $version);
    $DBRESULT =& $pearDB->query("SELECT * FROM session WHERE CONVERT( `session_id` USING utf8 ) = '". session_id() ."' AND `user_id` = '".$oreon->user->user_id."' LIMIT 1");
    if (PEAR::isError($DBRESULT))
		print "Mysql Error : ".$DBRESULT->getMessage();
    $session =& $DBRESULT->fetchRow();

    $tpl->assign('slastreload', $session["last_reload"]);
    $tpl->assign('smaxtime', $session_expire["session_expire"]);
    $tpl->assign('limit', $limit);
    $tpl->assign('num', $num);
    $tpl->assign('search', $search);
    $tpl->assign('search_type_host', $search_type_host);
    $tpl->assign('search_type_service', $search_type_service);
	$tpl->assign("refresh", $oreon->optGen["oreon_refresh"]);

	##Toolbar select $lang["lgd_more_actions"]
	?>
	<SCRIPT LANGUAGE="JavaScript">
	function setO(_i) {
		document.forms['form'].elements['cmd'].value = _i;
		document.forms['form'].elements['o1'].selectedIndex = 0;
		document.forms['form'].elements['o2'].selectedIndex = 0;
	}
	</SCRIPT>
	<?

	$attrs = array(	'onchange'=>"javascript: setO(this.form.elements['o1'].value); submit();");
    $form->addElement('select', 'o1', NULL, array(	NULL	=>	$lang["lgd_more_actions"], 
													"1"		=>	$lang['m_mon_resubmit_im_checks'], 
													"2"		=>	$lang['m_mon_resubmit_im_checks_f'], 
													"70" 	=> 	$lang['m_mon_acknoledge_thos_svc_pb'], 
													"71" 	=> 	$lang['m_mon_stop_acknoledge_svc_pb'],
													"80" 	=> 	$lang['m_mon_notification_svc_en'], 
													"81" 	=> 	$lang['m_mon_notification_svc_dis'],
													"90" 	=> 	$lang['m_mon_check_svc_en'], 
													"91" 	=> 	$lang['m_mon_check_svc_dis'],
													"72" 	=> 	$lang['m_mon_acknoledge_thos_host_pb'],
													"73" 	=> 	$lang['m_mon_stop_acknoledge_host_pb'], 
													"82" 	=> 	$lang['m_mon_notification_host_en'],
													"83" 	=> 	$lang['m_mon_notification_host_dis'],
													"92" 	=> 	$lang['m_mon_check_host_en'],
													"93" 	=> 	$lang['m_mon_check_host_dis']), $attrs);
	
	$form->setDefaults(array('o1' => NULL));
	$o1 =& $form->getElement('o1');
	$o1->setValue(NULL);

	$attrs = array('onchange'=>"javascript: setO(this.form.elements['o2'].value); submit();");
    $form->addElement('select', 'o2', NULL, array(	NULL	=>	$lang["lgd_more_actions"], 
													"1"		=>	$lang['m_mon_resubmit_im_checks'], 
													"2"		=>	$lang['m_mon_resubmit_im_checks_f'], 
													"70" 	=> 	$lang['m_mon_acknoledge_thos_svc_pb'], 
													"71" 	=> 	$lang['m_mon_stop_acknoledge_svc_pb'],
													"80" 	=> 	$lang['m_mon_notification_svc_en'], 
													"81" 	=> 	$lang['m_mon_notification_svc_dis'],
													"90" 	=> 	$lang['m_mon_check_svc_en'], 
													"91" 	=> 	$lang['m_mon_check_svc_dis'],
													"72" 	=> 	$lang['m_mon_acknoledge_thos_host_pb'],
													"73" 	=> 	$lang['m_mon_stop_acknoledge_host_pb'], 
													"82" 	=> 	$lang['m_mon_notification_host_en'],
													"83" 	=> 	$lang['m_mon_notification_host_dis'],
													"92" 	=> 	$lang['m_mon_check_host_en'],
													"93" 	=> 	$lang['m_mon_check_host_dis']), $attrs);
	$form->setDefaults(array('o2' => NULL));
	$o2 =& $form->getElement('o2');
	$o2->setValue(NULL);
	$o2->setSelected(NULL);	
	$tpl->assign('limit', $limit);

	$renderer =& new HTML_QuickForm_Renderer_ArraySmarty($tpl);
	$form->accept($renderer);

	$tpl->assign('form', $renderer->toArray());	
	$tpl->display("service.ihtml");

	$tpl = new Smarty();
	$tpl = initSmartyTpl("./", $tpl);
	$tpl->assign('lang', $lang);

	if ($oreon->optGen["nagios_version"] == 2 && isset($pgr_nagios_stat["created"])) 	
		$pgr_nagios_stat["created"] = date("d/m/Y G:i", $pgr_nagios_stat["created"]);
	else
		$pgr_nagios_stat["created"] = 0;
?>	