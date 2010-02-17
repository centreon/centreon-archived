<?php
/*
 * Copyright 2005-2009 MERETHIS
 * Centreon is developped by : Julien Mathis and Romain Le Merlus under
 * GPL Licence 2.0.
 * 
 * This program is free software; you can redistribute it and/or modify it under 
 * the terms of the GNU General Public License as published by the Free Software 
 * Foundation ; either version 2 of the License.
 * 
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A 
 * PARTICULAR PURPOSE. See the GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License along with 
 * this program; if not, see <http://www.gnu.org/licenses>.
 * 
 * Linking this program statically or dynamically with other modules is making a 
 * combined work based on this program. Thus, the terms and conditions of the GNU 
 * General Public License cover the whole combination.
 * 
 * As a special exception, the copyright holders of this program give MERETHIS 
 * permission to link this program with independent modules to produce an executable, 
 * regardless of the license terms of these independent modules, and to copy and 
 * distribute the resulting executable under terms of MERETHIS choice, provided that 
 * MERETHIS also meet, for each linked independent module, the terms  and conditions 
 * of the license of that module. An independent module is a module which is not 
 * derived from this program. If you modify this program, you may extend this 
 * exception to your version of the program, but you are not obliged to do so. If you
 * do not wish to do so, delete this exception statement from your version.
 * 
 * For more information : contact@centreon.com
 * 
 * SVN : $URL$
 * SVN : $Id$
 * 
 */

	if (!isset($oreon))
		exit(); 
	
	require_once "./include/monitoring/common-Func.php";

	require_once 'HTML/QuickForm.php';
	require_once 'HTML/QuickForm/Renderer/ArraySmarty.php';
	require_once "./class/centreonDB.class.php";	
	require_once "./class/centreonGMT.class.php";	

	/*
	 * Init GMT class
	 */
	$centreonGMT = new CentreonGMT($pearDB);
	$centreonGMT->getMyGMTFromSession(session_id(), $pearDB);

	unset($tpl);
	unset($path);

	$pearDBndo = new CentreonDB("ndo");
	
	$form = new HTML_QuickForm('form', 'post', "?p=".$p);	

	/*
	 * Get Poller List
	 */
	$ndo_base_prefix = getNDOPrefix();
	$tab_nagios_server = array();
	$DBRESULT =& $pearDB->query("SELECT n.id, ndomod.instance_name, n.name " .
								"FROM `cfg_ndomod` ndomod, `nagios_server` n " .
								"WHERE ndomod.activate = '1' " .
								"AND ndomod.ns_nagios_server = n.id " .
								"ORDER BY n.localhost DESC");
						
	while ($nagios =& $DBRESULT->fetchRow()) {
		$tab_nagios_server[$nagios['id']] = $nagios['name'];		
		
		$DBRESULT2 =& $pearDBndo->query("SELECT instance_id FROM `".$ndo_base_prefix."instances` WHERE instance_name LIKE '".$nagios['name']."'");
		$row =& $DBRESULT2->fetchRow();
		$DBRESULT2->free();
		
		$instance_id = $row['instance_id'];
		if ($instance_id) {
			
			$DBRESULT3 =& $pearDBndo->query(
					"SELECT program_version, programstatus_id, " .
					"pp.instance_id, UNIX_TIMESTAMP(status_update_time),  UNIX_TIMESTAMP(program_start_time),  UNIX_TIMESTAMP(program_end_time), " .
					"is_currently_running, ps.process_id, daemon_mode, UNIX_TIMESTAMP(last_command_check), " .
					"UNIX_TIMESTAMP(last_log_rotation), notifications_enabled, active_service_checks_enabled, passive_service_checks_enabled, " .
					"active_host_checks_enabled, passive_host_checks_enabled, event_handlers_enabled, flap_detection_enabled, " .
					"failure_prediction_enabled, process_performance_data, obsess_over_hosts, obsess_over_services, " .
					"modified_host_attributes, modified_service_attributes, global_host_event_handler, global_service_event_handler " .
					"FROM `". $ndo_base_prefix . "programstatus` ps, `". $ndo_base_prefix . "processevents` pp WHERE ps.instance_id = '".$instance_id."' AND pp.instance_id = '".$instance_id."' ORDER BY program_date DESC LIMIT 1");
						
			$data =& $DBRESULT3->fetchRow();
			
			/*
			 * Convert Date
			 */
			if ($data["UNIX_TIMESTAMP(last_log_rotation)"] != 0)
				$data['last_log_rotation'] = $centreonGMT->getDate(_("d/m/Y H:i:s"), $data["UNIX_TIMESTAMP(last_log_rotation)"]);
			else
				$data['last_log_rotation'] = "N/A";
				
			$data['program_start_time'] = $centreonGMT->getDate(_("d/m/Y H:i:s"), $data["UNIX_TIMESTAMP(program_start_time)"]);
			$data['program_end_time'] = $centreonGMT->getDate(_("d/m/Y H:i:s"), $data["UNIX_TIMESTAMP(program_end_time)"]);
			$data['last_command_check'] = $centreonGMT->getDate(_("d/m/Y H:i:s"), $data["UNIX_TIMESTAMP(last_command_check)"]);
			
			$procInfo[$nagios['id']] = $data;
			$DBRESULT3->free();
		}		
	}	
	$DBRESULT->free();
	
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
	$yes_no_tab = array("0" => _("No"), "1" => _("Yes"));
	
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
	
	
	$str_shutdown = _("Shutdown the Nagios process");
	$str_start = _("Start the Nagios process");
	$str_restart = _("Restart the Nagios process");
	$str_notif_enable = _("Enable notifications");
	$str_notif_disable = _("Disable notifications");
	$str_start_svc_check = _("Start executing service checks");
	$str_stop_svc_check = _("Stop executing service checks");
	$str_start_passive_svc_check = _("Start accepting passive service checks");
	$str_stop_passive_svc_check = _("Stop accepting passive service checks");
	$str_start_host_check = _("Start executing host checks");
	$str_stop_host_check = _("Stop executing host checks");
	$str_start_passive_host_check = _("Start accepting passive host checks");
	$str_stop_passive_host_check = _("Stop accepting passive host checks");
	$str_handler_enable = _("Enable event handlers");
	$str_handler_disable = _("Disable event handlers");
	$str_start_host_obsess = _("Start obsessing over hosts");
	$str_stop_host_obsess = _("Stop obsessing over hosts");
	$str_start_svc_obsess = _("Start obsessing over services");
	$str_stop_svc_obsess = _("Stop obsessing over services");
	$str_flap_detection_enable = _("Enable flap detection");
	$str_flap_detection_disable = _("Disable flap detection");
	$str_perfdata_enable = _("Enable performance data");
	$str_perfdata_disable = _("Disable performance data");
	
	/* Process commands */
	$tpl->assign("commandLabel", _("Process Commands"));
	$tpl->assign("str_shutdown", $str_shutdown);
	$tpl->assign("str_start", $str_start);
	$tpl->assign("str_restart", $str_restart);
	
	$tpl->assign("str_notif_enable", $str_notif_enable);
	$tpl->assign("str_notif_disable", $str_notif_disable);
	
	$tpl->assign("str_start_svc_check", $str_start_svc_check);
	$tpl->assign("str_stop_svc_check", $str_stop_svc_check);
	
	$tpl->assign("str_start_passive_svc_check", $str_start_passive_svc_check);
	$tpl->assign("str_stop_passive_svc_check", $str_stop_passive_svc_check);
	
	$tpl->assign("str_start_host_check", $str_start_host_check);
	$tpl->assign("str_stop_host_check", $str_stop_host_check);
	
	$tpl->assign("str_start_passive_host_check", $str_start_passive_host_check);
	$tpl->assign("str_stop_passive_host_check", $str_stop_passive_host_check);
	
	$tpl->assign("str_handler_enable", $str_handler_enable);
	$tpl->assign("str_handler_disable", $str_handler_disable);
	
	$tpl->assign("str_start_host_obsess", $str_start_host_obsess);
	$tpl->assign("str_stop_host_obsess", $str_stop_host_obsess);
	
	$tpl->assign("str_start_svc_obsess", $str_start_svc_obsess);
	$tpl->assign("str_stop_svc_obsess", $str_stop_svc_obsess);
	
	$tpl->assign("str_flap_detection_enable", $str_flap_detection_enable);
	$tpl->assign("str_flap_detection_disable", $str_flap_detection_disable);
	
	$tpl->assign("str_perfdata_enable", $str_perfdata_enable);
	$tpl->assign("str_perfdata_disable", $str_perfdata_disable);
	
	$shutdown_img = "<img src='./img/icones/16x16/stop.gif'>";
	$start_img = "<img src='./img/icones/16x16/media_play.gif'>";
	$restart_img = "<img src='./img/icones/16x16/refresh.gif'>";
	$disable_img = "<img src='./img/icones/16x16/delete2.gif'>";
	$enable_img = "<img src='./img/icones/16x16/flag_green.gif'>";
	
	$tpl->assign("shutdown_img", $shutdown_img);
	$tpl->assign("start_img", $start_img);
	$tpl->assign("restart_img", $restart_img);
	$tpl->assign("disable_img", $disable_img);
	$tpl->assign("enable_img", $enable_img);
	
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
	var _sid = '<?php echo session_id();?>';
	var glb_confirm = '<?php  echo _("Submit command?"); ?>';
	
	var labels = new Array();
	
	labels['global_shutdown'] = new Array();	
	labels['global_shutdown'][0] = "<?php echo $str_shutdown;?>";
	labels['global_shutdown'][1] = "<?php echo $str_shutdown;?>";
	labels['global_shutdown'][2] = "<?php echo $shutdown_img;?>";
	labels['global_shutdown'][3] = "<?php echo $shutdown_img;?>";
	
	labels['global_start'] = new Array();
	labels['global_start'][0] = "<?php echo $str_start;?>";
	labels['global_start'][1] = "<?php echo $str_start;?>";
	labels['global_start'][2] = "<?php echo $start_img;?>";
	labels['global_start'][3] = "<?php echo $start_img;?>";

	labels['global_restart'] = new Array();
	labels['global_restart'][0] = "<?php echo $str_restart;?>";	
	labels['global_restart'][1] = "<?php echo $str_restart;?>";
	labels['global_restart'][2] = "<?php echo $restart_img;?>";
	labels['global_restart'][3] = "<?php echo $restart_img;?>";
	
	labels['global_notifications'] = new Array();
	labels['global_notifications'][0] = "<?php echo $str_notif_enable;?>";
	labels['global_notifications'][1] = "<?php echo $str_notif_disable;?>";
	labels['global_notifications'][2] = "<?php echo $enable_img;?>";
	labels['global_notifications'][3] = "<?php echo $disable_img;?>";
	
	labels['global_service_checks'] = new Array();
	labels['global_service_checks'][0] = "<?php echo $str_start_svc_check;?>";
	labels['global_service_checks'][1] = "<?php echo $str_stop_svc_check;?>";
	labels['global_service_checks'][2] = "<?php echo $enable_img;?>";
	labels['global_service_checks'][3] = "<?php echo $disable_img;?>";
	
	labels['global_service_passive_checks'] = new Array();
	labels['global_service_passive_checks'][0] = "<?php echo $str_start_passive_svc_check;?>";
	labels['global_service_passive_checks'][1] = "<?php echo $str_stop_passive_svc_check;?>";
	labels['global_service_passive_checks'][2] = "<?php echo $enable_img;?>";
	labels['global_service_passive_checks'][3] = "<?php echo $disable_img;?>";
	
	labels['global_host_checks'] = new Array();
	labels['global_host_checks'][0] = "<?php echo $str_start_host_check;?>";
	labels['global_host_checks'][1] = "<?php echo $str_stop_host_check;?>";
	labels['global_host_checks'][2] = "<?php echo $enable_img;?>";
	labels['global_host_checks'][3] = "<?php echo $disable_img;?>";
	
	labels['global_host_passive_checks'] = new Array();
	labels['global_host_passive_checks'][0] = "<?php echo $str_start_passive_host_check;?>";
	labels['global_host_passive_checks'][1] = "<?php echo $str_stop_passive_host_check;?>";
	labels['global_host_passive_checks'][2] = "<?php echo $enable_img;?>";
	labels['global_host_passive_checks'][3] = "<?php echo $disable_img;?>";
	
	labels['global_event_handler'] = new Array();
	labels['global_event_handler'][0] = "<?php echo $str_handler_enable;?>";
	labels['global_event_handler'][1] = "<?php echo $str_handler_disable;?>";
	labels['global_event_handler'][2] = "<?php echo $enable_img;?>";
	labels['global_event_handler'][3] = "<?php echo $disable_img;?>";
	
	labels['global_service_obsess'] = new Array();
	labels['global_service_obsess'][0] = "<?php echo $str_start_svc_obsess;?>";
	labels['global_service_obsess'][1] = "<?php echo $str_stop_svc_obsess;?>";
	labels['global_service_obsess'][2] = "<?php echo $enable_img;?>";
	labels['global_service_obsess'][3] = "<?php echo $disable_img;?>";
	
	labels['global_host_obsess'] = new Array();
	labels['global_host_obsess'][0] = "<?php echo $str_start_host_obsess;?>";
	labels['global_host_obsess'][1] = "<?php echo $str_stop_host_obsess;?>";
	labels['global_host_obsess'][2] = "<?php echo $enable_img;?>";
	labels['global_host_obsess'][3] = "<?php echo $disable_img;?>";
	
	labels['global_flap_detection'] = new Array();
	labels['global_flap_detection'][0] = "<?php echo $str_flap_detection_enable;?>";
	labels['global_flap_detection'][1] = "<?php echo $str_flap_detection_disable;?>";
	labels['global_flap_detection'][2] = "<?php echo $enable_img;?>";
	labels['global_flap_detection'][3] = "<?php echo $disable_img;?>";
	
	labels['global_perf_data'] = new Array();
	labels['global_perf_data'][0] = "<?php echo $str_perfdata_enable;?>";
	labels['global_perf_data'][1] = "<?php echo $str_perfdata_disable;?>";
	labels['global_perf_data'][2] = "<?php echo $enable_img;?>";
	labels['global_perf_data'][3] = "<?php echo $disable_img;?>";	
	
	function send_command(cmd, actiontype, poller) {		
		if (!confirm(glb_confirm)) {
			return 0;
		}
		if (window.XMLHttpRequest) { 
	        xhr_cmd = new XMLHttpRequest();
	    }
	    else if (window.ActiveXObject) 
	    {
	        xhr_cmd = new ActiveXObject("Microsoft.XMLHTTP");
	    }
	    xhr_cmd.onreadystatechange = function() { display_result(xhr_cmd, poller, cmd + '_' + poller); };
	    xhr_cmd.open("GET", "./include/nagiosStats/processCommands.php?cmd=" + cmd + "&poller=" + poller + "&sid=" + _sid + "&type=" + actiontype, true);
	    xhr_cmd.send(null);
	}
	
	function display_result(xhr_cmd, poller, div_id) {
		if (xhr_cmd.readyState != 4 && xhr_cmd.readyState != "complete")
			return(0);			
		var msg_result;		
		var docXML= xhr_cmd.responseXML;
		var items_state = docXML.getElementsByTagName("result");
		var received_command = docXML.getElementsByTagName("cmd");		
		var acttype = docXML.getElementsByTagName("actiontype");
		var state = items_state.item(0).firstChild.data;						
		var executed_command = received_command.item(0).firstChild.data;
		var actiontype = acttype.item(0).firstChild.data;
		var img_src;
		
		var tmp = atoi(actiontype) + 2;
		img_src= labels[executed_command][tmp];
		
		if (state == "0") {
			 msg_result = 'Command sent';			 
			 document.getElementById(div_id).innerHTML = img_src + "&nbsp;<a href='#' onClick='send_command(\"" + executed_command + "\", " + actiontype + ", \""+ poller +"\")'>"+ labels[executed_command][actiontype] +"</a>";
		}
		else {
			 msg_result = 'Failed to send command';
		}
		<?php
		require_once "./class/centreonMsg.class.php";
		?>
		_clear("centreonMsg");
		_setTextStyle("centreonMsg", "bold");	
		_setText("centreonMsg", msg_result);
		_nextLine("centreonMsg");
		_setTimeout("centreonMsg", 3);
	}
</script>
