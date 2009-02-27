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
	
	/* Nagios Process Information */
	$tpl->assign("processInfoLabel", _("Nagios Process Information"));	
	$tpl->assign("str_prog_version", _("Program Version"));
	$tpl->assign("str_prog_start_time", _("Program Start Time"));
	$tpl->assign("str_last_log_rotation", _("Last Log File Rotation:"));
	$tpl->assign("str_last_command_check", _("Last External Command Check:"));	 	
	$tpl->assign("str_process_id", _("Nagios PID"));
	$tpl->assign("str_notifications_enabled", _("Notifications enabled?"));
	$tpl->assign("str_currently_running", _("Currently running?"));
	$tpl->assign("str_host_check_execute", _("Host Checks Being Executed?"));
	$tpl->assign("str_passive_host_checks_enabled", _("Passive Host Checks Being Accepted?"));
	$tpl->assign("str_service_check_execute", _("Service Checks Being Executed?"));
	$tpl->assign("str_passive_service_checks_enabled", _("Passive Service Checks Being Accepted?"));
	$tpl->assign("str_event_handlers_enabled", _("Event Handlers Enabled?"));
	$tpl->assign("str_obsess_over_hosts", _("Obsessing Over Hosts?"));
	$tpl->assign("str_obsess_over_services", _("Obsessing Over Services?"));
	$tpl->assign("str_process_performance_data", _("Performance Data Being Processed?"));	
	$tpl->assign("str_flap_detection", _("Flap detection enabled?"));
	$tpl->assign("yes_no_tab", $yes_no_tab); 
	
	/* Process commands */
	$tpl->assign("commandLabel", _("Process Commands"));
	$tpl->assign("str_shutdown", _("Shutdown the Nagios process"));
	$tpl->assign("str_restart", _("Restart the Nagios process"));
	
	$tpl->assign("str_notif_enable", _("Enable notifications"));
	$tpl->assign("str_notif_disable", _("Disable notifications"));
	
	$tpl->assign("str_start_svc_check", _("Start executing service checks"));
	$tpl->assign("str_stop_svc_check", _("Stop executing service checks"));
	
	$tpl->assign("str_start_passive_svc_check", _("Start accepting passive service checks"));
	$tpl->assign("str_stop_passive_svc_check", _("Stop accepting passive service checks"));
	
	$tpl->assign("str_start_host_check", _("Start executing host checks"));
	$tpl->assign("str_stop_host_check", _("Stop executing host checks"));
	
	$tpl->assign("str_start_passive_host_check", _("Start accepting passive host checks"));
	$tpl->assign("str_stop_passive_host_check", _("Stop accepting passive host checks"));
	
	$tpl->assign("str_handler_enable", _("Enable event handlers"));
	$tpl->assign("str_handler_disable", _("Disable event handlers"));
	
	$tpl->assign("str_start_host_obsess", _("Start obsessing over hosts"));
	$tpl->assign("str_stop_host_obsess", _("Stop obsessing over hosts"));
	
	$tpl->assign("str_start_svc_obsess", _("Start obsessing over services"));
	$tpl->assign("str_stop_svc_obsess", _("Stop obsessing over services"));
	
	$tpl->assign("str_flap_detection_enable", _("Enable flap detection"));
	$tpl->assign("str_flap_detection_disable", _("Disable flap detection"));
	
	$tpl->assign("str_perfdata_enable", _("Enable performance data"));
	$tpl->assign("str_perfdata_disable", _("Disable performance data"));
	
	$tpl->assign("shutdown_img", "<img src='./img/icones/16x16/stop.gif'>");
	$tpl->assign("restart_img", "<img src='./img/icones/16x16/refresh.gif'>");
	$tpl->assign("disable_img", "<img src='./img/icones/16x16/delete2.gif'>");
	$tpl->assign("enable_img", "<img src='./img/icones/16x16/flag_green.gif'>");
	
	$action_list = $oreon->user->access->getActions();	
	$tpl->assign("admin", $oreon->user->admin);
	$tpl->assign("action_list", $action_list);	
	$count_actions = 0;
	foreach ($action_list as $value) {
		if (preg_match("/^global_/", $value)) {		
			$count_actions = 1;
			break;
		}
	}	
	$tpl->assign("count_action", $count_actions);
	$tpl->display("processInfo.ihtml");
?>
<script type="text/javascript">	
	
	var glb_confirm = '<?php  echo _("Submit command"); ?>';
	
	function send_command(cmd, poller) {
		if (!confirm(glb_confirm + " " + cmd + "?")) {
			return 0;
		}
		if (window.XMLHttpRequest) { 
	        xhr_cmd = new XMLHttpRequest();
	    }
	    else if (window.ActiveXObject) 
	    {
	        xhr_cmd = new ActiveXObject("Microsoft.XMLHTTP");
	    }
	    xhr_cmd.onreadystatechange = function() { display_result(xhr_cmd); };
	   	xhr_cmd.open("GET", "./include/nagiosStats/processCommands.php?cmd=" + cmd + "&poller=" + poller, true);
    	xhr_cmd.send(null);
	}
	
	function display_result(xhr_cmd) {
		if (xhr_cmd.readyState != 4 && xhr_cmd.readyState != "complete")
			return(0);
		var msg_result;		
		var docXML= xhr_cmd.responseXML;
		var items_state = docXML.getElementsByTagName("result");
		var received_command = docXML.getElementsByTagName("cmd");
		var state = items_state.item(0).firstChild.data;
		var executed_command = received_command.item(0).firstChild.data;
		
		if (state == "0") {
			 msg_result = executed_command + ' sent';
		}
		else {
			 msg_result = 'Failed ' + executed_command;
		}
		<?php
		require_once "./class/centreonMsg.class.php";
		?>
		_setTextStyle("centreonMsg", "bold");	
		_setText("centreonMsg", msg_result);
		_nextLine("centreonMsg");
		_setTimeout("centreonMsg", 3);
	}
</script>
