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
 * For information : contact@oreon-project.org
 */
	
	if (!isset($oreon))
	  exit();
	
	## Nagios 1 functions

	function get_program_data($log, $status_proc){
	  $pgr_nagios_stat = array();
	  $pgr_nagios_stat["program_start"] = $log['1'];
	  $pgr_nagios_stat["nagios_pid"] = $log['2'];
	  $pgr_nagios_stat["daemon_mode"] = $log['3'];
	  $pgr_nagios_stat["last_command_check"] = $log['4'];
	  $pgr_nagios_stat["last_log_rotation"] = $log['5'];
	  $pgr_nagios_stat["enable_notifications"] = $log['6'];
	  $pgr_nagios_stat["execute_service_checks"] = $log['7'];
	  $pgr_nagios_stat["accept_passive_service_checks"] = $log['8'];
	  $pgr_nagios_stat["enable_event_handlers"] = $log['9'];
	  $pgr_nagios_stat["obsess_over_services"] = $log['10'];
	  $pgr_nagios_stat["enable_flap_detection"] = $log['11'];
	  $pgr_nagios_stat["process_performance_data"] = $log['13'];
	  $pgr_nagios_stat["status_proc"] = $status_proc;
	  return ($pgr_nagios_stat);
	}
		
	function get_host_data($log){
	  $host_data["host_name"] = $log['1'];
	  $host_data["current_state"] = $log['2'];
	  $host_data["last_check"] = $log['3'];
	  $host_data["last_state_change"] = $log['4'];
	  $host_data["problem_has_been_acknowledged"] = $log['5'];
	  $host_data["time_up"] = $log['6'];
	  $host_data["time_down"] = $log['7'];
	  $host_data["time_unrea"] = $log['8'];
	  $host_data["last_notification"] = $log['9'];
	  $host_data["current_notification_number"] = $log['10'];
	  $host_data["notifications_enabled"] = $log['11'];
	  $host_data["event_handler_enabled"] = $log['12'];
	  $host_data["active_checks_enabled"] = $log['13'];
	  $host_data["flap_detection_enabled"] = $log['14'];
	  $host_data["is_flapping"] = $log['15'];
	  $host_data["percent_state_change"] = $log['16'];
	  $host_data["scheduled_downtime_depth"] = $log['17'];
	  $host_data["failure_prediction_enabled"] = $log['18'];
	  $host_data["process_performance_data"] = $log['19'];
	  $host_data["plugin_output"] = $log['20'];
	  return ($host_data);
	}
	
	function get_service_data($log){
	  $svc_data["host_name"] = $log[1];
	  $svc_data["service_description"] = $log[2];
	  $svc_data["current_state"] = $log[3];
	  $svc_data["current_attempt"] = $log[4];
	  $svc_data["stat_type"] = $log[5];
	  $svc_data["last_check"] = $log[6];
	  $svc_data["next_check"] = $log[7];
	  $svc_data["check_type"] = $log[8];
	  $svc_data["active_checks_enabled"] = $log[9];
	  $svc_data["passive_checks_enabled"] = $log[10];
	  $svc_data["event_handler_enabled"] = $log[11];
	  $svc_data["last_state_change"] = $log[12];
	  $svc_data["problem_has_been_acknowledged"] = $log[13];
	  $svc_data["last_hard_state_change"] = $log[14];
	  $svc_data["ok"] = $log[15];
	  $svc_data["warning"] = $log[16];
	  $svc_data["unknown"] = $log[17];
	  $svc_data["critical"] = $log[18];
	  $svc_data["last_notification"] = $log[19];
	  $svc_data["current_notification_number"] = $log[20];
	  $svc_data["notifications_enabled"] = $log[21];
	  $svc_data["check_latency"] = $log[22];
	  $svc_data["check_execution_time"] = $log[23];
	  $svc_data["flap_detection_enabled"] = $log[24];
	  $svc_data["is_flapping"] = $log[25];
	  $svc_data["percent_state_change"] = $log[26];
	  $svc_data["scheduled_downtime_depth"] = $log[27];
	  $svc_data["failure_prediction_enabled"] = $log[28];
	  $svc_data["process_performance_data"] = $log[29];
	  $svc_data["obsess_over_service"] = $log[30];
	  $svc_data["plugin_output"] = $log[31];
	  $svc_data["total_running"] = $log[15]+$log[16]+$log[17]+$log[18];
	  return ($svc_data);
	}

	## Parsing 
	
	function getProgramDataParsed($log, $status_proc){
	  $pgr_nagios_stat = array();
	  $pgr_nagios_stat["modified_host_attributes"] = 		$log['1'];
	  $pgr_nagios_stat["modified_service_attributes"] = 	$log['2'];
	  $pgr_nagios_stat["nagios_pid"] = 						$log['3'];
	  $pgr_nagios_stat["daemon_mode"] = 					$log['4'];
	  $pgr_nagios_stat["program_start"] = 					$log['5'];
	  $pgr_nagios_stat["last_command_check"] = 				$log['6'];
	  $pgr_nagios_stat["last_log_rotation"] = 				$log['7'];
	  $pgr_nagios_stat["enable_notifications"] = 			$log['8'];
	  $pgr_nagios_stat["active_service_checks_enabled"] = 	$log['9'];
	  $pgr_nagios_stat["passive_service_checks_enabled"] = 	$log['10'];
	  $pgr_nagios_stat["active_host_checks_enabled"] = 		$log['11'];
	  $pgr_nagios_stat["passive_host_checks_enabled"] = 	$log['13'];
	  $pgr_nagios_stat["enable_event_handlers"] = 			$log['14'];
	  $pgr_nagios_stat["obsess_over_services"] = 			$log['15'];
	  $pgr_nagios_stat["obsess_over_hosts"] = 				$log['16'];
	  $pgr_nagios_stat["check_service_freshness"] = 		$log['17'];
	  $pgr_nagios_stat["check_host_freshness"] = 			$log['18'];
	  $pgr_nagios_stat["enable_flap_detection"] = 			$log['19'];
	  $pgr_nagios_stat["enable_failure_prediction"] = 		$log['20'];
	  $pgr_nagios_stat["process_performance_data"] = 		$log['21'];
	  $pgr_nagios_stat["global_host_event_handler"] = 		$log['22'];
	  $pgr_nagios_stat["global_service_event_handler"] = 	$log['13'];
	  $pgr_nagios_stat["status_proc"] = $status_proc;
	  return ($pgr_nagios_stat);
	}
		
	function getHostDataParsed($log){
		
		$tab_status_host = array("0" => "UP", "1" => "DOWN", "2" => "UNREACHABLE");
	  	$host_data["host_name"] = 								$log['1'];
	  	$host_data["modified_attributes"] = 						$log['2'];
	  	$host_data["check_command"] = 							$log['3'];
	  	$host_data["event_handler"] = 							$log['4'];
	  	$host_data["has_been_checked"] = 							$log['5'];
	  	$host_data["should_be_scheduled"] = 						$log['6'];
	  	$host_data["check_execution_time"] = 						$log['7'];
	  	$host_data["check_latency"] = 							$log['8'];
	  	$host_data["check_type"] = 								$log['9'];
	  	$host_data["current_state"] = 							$tab_status_host[$log['10']];
	  	$host_data["last_hard_state"] = 							$log['11'];
	  	$host_data["plugin_output"] = 							$log['12'];
	  	$host_data["performance_data"] = 							$log['13'];
	  	$host_data["last_check"] = 								$log['14'];
	  	$host_data["next_check"] = 								$log['15'];
	  	$host_data["current_attempt"] = 							$log['16'];
	  	$host_data["max_attempts"] = 								$log['17'];
	  	$host_data["state_type"] = 								$log['18'];
	  	$host_data["last_state_change"] = 						$log['19'];
	  	$host_data["last_hard_state_change"] = 					$log['20'];
	  	$host_data["last_time_up"] = 								$log['21'];
	  	$host_data["last_time_down"] = 							$log['22'];
	  	$host_data["last_time_unreachable"] =	 					$log['23'];
	  	if (isset($log['24']))
			$host_data["last_notification"] = 					$log['24'];
	  	if (isset($log['25']))
		  	$host_data["next_notification"] = 					$log['25'];
	  	if (isset($log['26']))
		  	$host_data["no_more_notifications"] = 				$log['26'];
	  	if (isset($log['27']))
		  	$host_data["current_notification_number"] = 			$log['27'];
	  	if (isset($log['28']))
		  $host_data["notifications_enabled"] = 				$log['28'];
	  if (isset($log['29']))
		  $host_data["problem_has_been_acknowledged"] = 		$log['29'];
	  if (isset($log['30']))
		  $host_data["acknowledgement_type"] = 					$log['30'];
	  if (isset($log['31']))
		  $host_data["active_checks_enabled"] = 				$log['31'];
	  if (isset($log['32']))
		  $host_data["passive_checks_enabled"] = 				$log['32'];
	  if (isset($log['33']))
		  $host_data["event_handler_enabled"] = 				$log['33'];
	  if (isset($log['34']))
		  $host_data["flap_detection_enabled"] = 				$log['34'];
	  if (isset($log['35']))
		  $host_data["failure_prediction_enabled"] = 			$log['35'];
	  if (isset($log['36']))
		  $host_data["process_performance_data"] = 				$log['36'];
	  if (isset($log['37']))
		  $host_data["obsess_over_host"] = 						$log['37'];
	  if (isset($log['38']))
		  $host_data["last_update"] = 							$log['38'];
	  if (isset($log['39']))
		  $host_data["is_flapping"] = 							$log['39'];
	  if (isset($log['40']))
		  $host_data["percent_state_change"] = 					$log['40'];
	  if (isset($log['41']))
		  $host_data["scheduled_downtime_depth"] = 				$log['41'];
	  return ($host_data);
	}
	
	function getServiceDataParsed($log){
		$tab_status_svc = array("0" => "OK", "1" => "WARNING", "2" => "CRITICAL", "3" => "UNKNOWN", "4" => "PENDING");
	  	$svc_data["host_name"] = 				$log[1];
		$svc_data["service_description"] = 		$log[2];
		$svc_data["modified_attributes"] = 		$log[3];
		$svc_data["check_command"] = 			$log[4];
		$svc_data["event_handler"] = 			$log[5];
		$svc_data["has_been_checked"] = 		$log[6];
		$svc_data["should_be_scheduled"] = 		$log[7];
		$svc_data["check_execution_time"] = 	$log[8];
		$svc_data["check_latency"] = 			$log[9];
		$svc_data["check_type"] = 				$log[10];
		$svc_data["current_state"] = 			$tab_status_svc[$log[11]];
		$svc_data["last_hard_state"] = 			$log[12];
		$svc_data["current_attempt"] = 			$log[13];
		$svc_data["max_attempts"] = 			$log[14];
		$svc_data["state_type"] = 				$log[15];
		$svc_data["last_state_change"] = 		$log[16];
		$svc_data["last_hard_state_change"] = 	$log[17];
		$svc_data["last_time_ok"] = 			$log[18];
		$svc_data["last_time_warning"] = 		$log[19];
		$svc_data["last_time_unknown"] = 		$log[20];
		$svc_data["last_time_critical"] = 		$log[21];
		$svc_data["plugin_output"] = 			$log[22];
		$svc_data["performance_data"] = 		$log[23];
		$svc_data["last_check"] = 				$log[24];
		$svc_data["next_check"] = 				$log[25];
		$svc_data["current_notification_number"] = $log[26];
		$svc_data["last_notification"] = 		$log[27];
		$svc_data["next_notification"] = 		$log[28];
		$svc_data["no_more_notifications"] = 	$log[29];
		$svc_data["notifications_enabled"] = 	$log[30];
		$svc_data["active_checks_enabled"] = 	$log[31];
		$svc_data["passive_checks_enabled"] = 	$log[32];
		$svc_data["event_handler_enabled"] = 	$log[33];
		$svc_data["problem_has_been_acknowledged"] = $log[34];
		$svc_data["acknowledgement_type"] = 	$log[35];
		$svc_data["flap_detection_enabled"] = 	$log[36];
		$svc_data["failure_prediction_enabled"] = $log[37];
		$svc_data["process_performance_data"] = $log[38];
		$svc_data["obsess_over_service"] = 		$log[39];
		$svc_data["last_update"] = 				$log[40];
		$svc_data["is_flapping"] = 				$log[41];
		$svc_data["percent_state_change"] = 	$log[42];
		$svc_data["scheduled_downtime_depth"] = $log[43];
		return ($svc_data);
	}
	
?>