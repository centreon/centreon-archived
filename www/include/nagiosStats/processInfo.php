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
	
	require_once "./include/monitoring/common-Func.php";

	require_once 'HTML/QuickForm.php';
	require_once 'HTML/QuickForm/Renderer/ArraySmarty.php';	
	require_once "./class/centreonDB.class.php";

	unset($tpl);
	unset($path);

	$pearDBndo = new CentreonDB("ndo");
	
	$form = new HTML_QuickForm('form', 'post', "?p=".$p);	

	/*
	 * Get Poller List
	 */
	$ndo_base_prefix = getNDOPrefix();
	$tab_nagios_server = array();
	$DBRESULT =& $pearDB->query("SELECT ndomod.id, ndomod.instance_name, n.name " .
								"FROM `cfg_ndomod` ndomod, `nagios_server` n " .
								"WHERE ndomod.activate = '1' " .
								"AND ndomod.ns_nagios_server = n.id " .
								"ORDER BY n.localhost DESC");
						
	while ($nagios =& $DBRESULT->fetchRow()) {
		$tab_nagios_server[$nagios['id']] = $nagios['name'];		
		$DBRESULT2 =& $pearDBndo->query("SELECT instance_id FROM `".$ndo_base_prefix."instances` WHERE instance_name LIKE '".$nagios['instance_name']."'");
		$row =& $DBRESULT2->fetchRow();
		$instance_id = $row['instance_id'];
		if ($instance_id) {
			$DBRESULT3 =& $pearDBndo->query("SELECT * FROM `". $ndo_base_prefix . "programstatus` pm, `". $ndo_base_prefix . "processevents` p WHERE pm.instance_id = '".$instance_id."' AND p.instance_id = '".$instance_id."' LIMIT 1");
			$procInfo[$nagios['id']] =& $DBRESULT3->fetchRow();
		}		
	}	
	$host_list = array();
	$tab_server = array();
	$cpt = 0;
	foreach ($tab_nagios_server as $key => $value){
		$host_list[$key] = $value;
		$tab_server[$cpt] = $value;
		$cpt++;
	}

	$path = "./include/nagiosStats/";
		
	/*
	 * Smarty template Init
	 */
	 
	$tpl = new Smarty();
	$tpl = initSmartyTpl($path, $tpl, "./");	
	
	$renderer = new HTML_QuickForm_Renderer_ArraySmarty($tpl);		
	$yes_no_tab = array("0" => "No", "1" => "Yes");
	
	/*
	 * Assign values
	 */	
	$tpl->assign("procInfo", $procInfo); 
		
	if (isset($host_list) && $host_list)
		$tpl->assign('host_list', $host_list);
		
	if (isset($tab_server) && $tab_server)
		$tpl->assign('tab_server', $tab_server);		
		
	$tpl->assign("processInfoLabel", _("Nagios Process Information"));	
	$tpl->assign("str_prog_version", _("Program Version"));
	$tpl->assign("str_prog_start_time", _("Program Start Time"));
	$tpl->assign("str_last_log_rotation", _("Last Log File Rotation:"));
	$tpl->assign("str_last_command_check", _("Last External Command Check:"));	 	
	$tpl->assign("str_process_id", _("Nagios PID"));
	$tpl->assign("str_notifications_enabled", _("Notifications enabled?"));
	$tpl->assign("str_currently_running", _("Currently running?"));
	$tpl->assign("str_host_check_execute", _("Host Checks Being Executed?"));
	$tpl->assign("str_passive_host_checks_enabled", _("Passive Service Checks Being Accepted?"));
	$tpl->assign("str_service_check_execute", _("Service Checks Being Executed?"));
	$tpl->assign("str_passive_service_checks_enabled", _("Passive Service Checks Being Accepted?"));
	$tpl->assign("str_event_handlers_enabled", _("Event Handlers Enabled?"));
	$tpl->assign("str_obsess_over_hosts", _("Obsessing Over Hosts?"));
	$tpl->assign("str_obsess_over_services", _("Obsessing Over Services?"));
	$tpl->assign("str_process_performance_data", _("Performance Data Being Processed?"));	 
	$tpl->assign("yes_no_tab", $yes_no_tab); 
	$tpl->display("processInfo.ihtml");
?>