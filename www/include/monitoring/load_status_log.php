<?php
/**
Oreon is developped with GPL Licence 2.0 :
http://www.gnu.org/licenses/gpl.txt
Developped by : Julien Mathis - Romain Le Merlus - Christophe Coraboeuf

Adapted to Pear library Quickform & Template_PHPLIB by Merethis company, under direction of Cedrick Facon

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
	
if (!isset($oreon)){
  exit();
}

$debug = 0;

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
  $host_data["status"] = $log['2'];
  $host_data["last_check"] = $log['3'];
  $host_data["last_stat"] = $log['4'];
  $host_data["acknowledged"] = $log['5'];
  $host_data["time_up"] = $log['6'];
  $host_data["time_down"] = $log['7'];
  $host_data["time_unrea"] = $log['8'];
  $host_data["last_notifi"] = $log['9'];
  $host_data["curr_not_number"] = $log['10'];
  $host_data["not_en"] = $log['11'];
  $host_data["ev_handler_en"] = $log['12'];
  $host_data["checks_en"] = $log['13'];
  $host_data["flap_detect_en"] = $log['14'];
  $host_data["flapping"] = $log['15'];
  $host_data["percent_stat_change"] = $log['16'];
  $host_data["sch_downtime_death"] = $log['17'];
  $host_data["failure_prediction_en"] = $log['18'];
  $host_data["process_performance_data"] = $log['19'];
  $host_data["output"] = $log['20'];
  return ($host_data);
}

function get_service_data($log){
  $svc_data["host_name"] = $log[1];
  $svc_data["description"] = $log[2];
  $svc_data["status"] = $log[3];
  $svc_data["retry"] = $log[4];
  $svc_data["stat_type"] = $log[5];
  $svc_data["last_check"] = $log[6];
  $svc_data["next_check"] = $log[7];
  $svc_data["check_type"] = $log[8];
  $svc_data["checks_en"] = $log[9];
  $svc_data["accept_passive_check"] = $log[10];
  $svc_data["ev_handler_en"] = $log[11];
  $svc_data["last_change"] = $log[12];
  $svc_data["pb_aknowledged"] = $log[13];
  $svc_data["last_hard_stat"] = $log[14];
  $svc_data["ok"] = $log[15];
  $svc_data["warning"] = $log[16];
  $svc_data["unknown"] = $log[17];
  $svc_data["critical"] = $log[18];
  $svc_data["last_notification"] = $log[19];
  $svc_data["current_not_nb"] = $log[20];
  $svc_data["not_en"] = $log[21];
  $svc_data["latency"] = $log[22];
  $svc_data["exec_time"] = $log[23];
  $svc_data["flap_detect_en"] = $log[24];
  $svc_data["svc_is_flapping"] = $log[25];
  $svc_data["percent_stat_change"] = $log[26];
  $svc_data["sch_downtime_death"] = $log[27];
  $svc_data["failure_prediction_en"] = $log[28];
  $svc_data["process_perf_date"] = $log[29];
  $svc_data["obsess_over_service"] = $log[30];
  $svc_data["output"] = $log[31];
  // Stat bonus
  $svc_data["total_running"] = $log[15]+$log[16]+$log[17]+$log[18];
  return ($svc_data);
}

//	xdebug_start_trace(); 

$t_begin = microtime_float();

// Open File
if (file_exists($oreon->Nagioscfg["status_file"])){
  $log_file = fopen($oreon->Nagioscfg["status_file"], "r");
  $status_proc = 1;
} else {
  $log_file = 0;
  $status_proc = 0;
}

// init table
$service = array();
$host_status = array();
$service_status = array();
$host_services = array();
$metaService_status = array();
$tab_host_service = array();

// Read 

$lca =& $oreon->user->lcaHost;
$version = $oreon->user->get_version();

$tab_status_svc = array("0" => "OK", "1" => "WARNING", "2" => "CRITICAL", "3" => "UNKNOWN", "4" => "PENDING");
$tab_status_host = array("0" => "UP", "1" => "DOWN", "2" => "UNREACHABLE");
	    
if ($version == 1){
  if ($log_file)
    while ($str = fgets($log_file))		{
      	// set last update 
     	$last_update = date("d-m-Y h:i:s");
      	if (!preg_match("/^\#.*/", $str)){		// get service stat
			$log = split(";", $str);
			if (preg_match("/^[\[\]0-9]* SERVICE[.]*/", $str)){
	  			if (array_search($log["1"], $lca)){
					$service_status[$log["1"]."_".$log["2"]] = get_service_data($log);
		    		$tab_host_service[$log["1"]][$log["2"]] = "1";
		 		} else if (!strcmp($log[1], "Meta_Module")){
		    		$metaService_status[$log["2"]] = get_service_data($log);
	  			}
			} else if (preg_match("/^[\[\]0-9]* HOST[.]*/", $str)){ // get host stat
	  			if (array_search($log["1"], $lca)){
	    			$host_status[$log["1"]] = get_host_data($log);
	    			$tab_host_service[$log["1"]] = array();
	  			}
			} else if (preg_match("/^[\[\]0-9]* PROGRAM[.]*/", $str))
	  			$program_data = get_program_data($log, $status_proc);
	  	}
      	unset($str);
	}
} else {
  if ($log_file)
    while ($str = fgets($log_file)) {
      	// set last update
      	$last_update = date("d-m-Y h:i:s");
      	if (!preg_match("/^\#.*/", $str)){
			if (preg_match("/^service/", $str)){   
			  $log = array();
			  while ($str2 = fgets($log_file))
	          	if (!strpos($str2, "}")){      
		      		if (preg_match("/([A-Za-z0-9\_\-]*)\=(.*)[\ \t]*/", $str2, $tab))
						$log[$tab[1]] = $tab[2];
		    	} else
		      		break;
				if (isset($log['host_name']) && array_search($log['host_name'], $lca)){
					$svc_data["host_name"] = $log["host_name"];
					$svc_data["description"] = $log["service_description"];
					$svc_data["status"] = $tab_status_svc[$log['current_state']];
					$svc_data["retry"] = $log["current_attempt"];
					$svc_data["stat_type"] = $log["state_type"];
					$svc_data["last_check"] = $log["last_check"];
					$svc_data["next_check"] = $log["next_check"];
					$svc_data["check_type"] = $log["check_type"];
					$svc_data["checks_en"] = $log["active_checks_enabled"];
					$svc_data["accept_passive_check"] = $log["passive_checks_enabled"];
					$svc_data["ev_handler_en"] = $log["event_handler_enabled"];
					$svc_data["last_change"] = $log["last_state_change"];
					$svc_data["pb_aknowledged"] = $log["problem_has_been_acknowledged"];
					$svc_data["last_hard_stat"] = $log["last_hard_state_change"];
					$svc_data["ok"] = "";
					$svc_data["warning"] = "";
					$svc_data["unknown"] = "";
					$svc_data["critical"] = "";
					$svc_data["last_notification"] = $log["last_notification"];
					$svc_data["current_not_nb"] = $log["current_notification_number"];
					$svc_data["not_en"] = $log["notifications_enabled"];
					$svc_data["latency"] = $log["check_latency"];
					$svc_data["exec_time"] = $log["check_execution_time"];
					$svc_data["flap_detect_en"] = $log["flap_detection_enabled"];
					$svc_data["svc_is_flapping"] = $log["is_flapping"];
					$svc_data["percent_stat_change"] = $log["percent_state_change"];
					$svc_data["sch_downtime_death"] = $log["scheduled_downtime_depth"];
					$svc_data["failure_prediction_en"] = $log["failure_prediction_enabled"];
					$svc_data["process_perf_date"] = $log["process_performance_data"];
					$svc_data["obsess_over_service"] = $log["obsess_over_service"];
					$svc_data["output"] = $log["plugin_output"];
					$service_status[$svc_data["host_name"] . "_" . $svc_data["description"]] = $svc_data;
					$tab_host_service[$svc_data["host_name"]][$svc_data["description"]] = "1";
				} else if (strstr("Meta_Module", $log['host_name'])){
					$svc_data["host_name"] = $log["host_name"];
					$svc_data["description"] = $log["service_description"];
					$svc_data["status"] = $tab_status_svc[$log['current_state']];
					$svc_data["retry"] = $log["current_attempt"];
					$svc_data["last_check"] = $log["last_check"];
					$svc_data["next_check"] = $log["next_check"];
					$svc_data["check_type"] = $log["check_type"];
					$svc_data["checks_en"] = $log["active_checks_enabled"];
					$svc_data["accept_passive_check"] = $log["passive_checks_enabled"];
					$svc_data["ev_handler_en"] = $log["event_handler_enabled"];
					$svc_data["last_change"] = $log["last_state_change"];
					$svc_data["output"] = $log["plugin_output"];
					$metaService_status[$log["service_description"]] = $svc_data;
				}
		  		unset($log);
		  } else if (preg_match("/^host/", $str)){ // get host stat
			$log = array();
		  	while ($str2 = fgets($log_file))
		    	if (!strpos($str2, "}")){
		      		if (preg_match("/([A-Za-z0-9\_\-]*)\=(.*)[\ \t]*/", $str2, $tab)){
						$log [$tab[1]] = $tab[2];
		      		} 
		    	} else
		      		break;
				if (array_search($log['host_name'], $lca)){
					$host_data["host_name"] = $log['host_name'];
					$host_data["status"] = $tab_status_host[$log['current_state']];
					$host_data["last_check"] = $log['last_check'];
					$host_data["last_stat"] = $log['last_state_change'];
					$host_data["acknowledged"] = $log['problem_has_been_acknowledged'];
					$host_data["time_up"] = "";
					$host_data["time_down"] = "";
					$host_data["time_unrea"] = "";
					$host_data["last_notifi"] = $log['last_notification'];
					$host_data["curr_not_number"] = $log['current_notification_number'];
					$host_data["not_en"] = $log['notifications_enabled'];
					$host_data["ev_handler_en"] = $log['event_handler_enabled'];
					$host_data["checks_en"] = $log['active_checks_enabled'];
					$host_data["flap_detect_en"] = $log['flap_detection_enabled'];
					$host_data["flapping"] = $log['is_flapping'];
					$host_data["percent_stat_change"] = $log['percent_state_change'];
					$host_data["sch_downtime_death"] = $log['scheduled_downtime_depth'];
					$host_data["failure_prediction_en"] = $log['failure_prediction_enabled'];
					$host_data["process_performance_data"] = $log['process_performance_data'];
					isset($log['plugin_output']) ? $host_data["output"] = $log['plugin_output']: $host_data["output"] = "";
					$host_status[$host_data["host_name"]] = $host_data;
					$tab_host_service[$host_data["host_name"]] = array();
		  		}	
				unset($log);
			} else if (preg_match("/^program/", $str)){
	          	$log = array();
	          	while ($str2 = fgets($log_file))
	            	if (!strpos($str2, "}")){
	              		if (preg_match("/([A-Za-z0-9\_\-]*)\=([A-Za-z0-9\_\-\.\,\(\)\[\]\ \=\%\;\:]+)/", $str2, $tab)){
	                		$pgr_nagios_stat[$tab[1]] = $tab[2];
	              		}
	            	} else
	              		break;
	          	unset($log);
	        } else if (preg_match("/^info/", $str)){
	          	$log = array();
	          	while ($str2 = fgets($log_file))
	            	if (!strpos($str2, "}")){
	              		if (preg_match("/([A-Za-z0-9\_\-]*)\=([A-Za-z0-9\_\-\.\,\(\)\[\]\ \=\%\;\:]+)/", $str2, $tab)){
	                		$pgr_nagios_stat[$tab[1]] = $tab[2];
	              		}
	            	} else
	              		break;
	          	unset($log);
	        }
			unset($str);	
      	}
    }
}

$row_data = array();
if (isset($_GET["o"]) && $_GET["o"] == "svcSch" && !isset($_GET["sort_types"])){
	$_GET["sort_types"] = "next_check";
	$_GET["order"] = "SORT_ASC";
}

if (isset($_GET["sort_types"]) && $_GET["sort_types"]){
  foreach ($service_status as $key => $row)
    $row_data[$key] = $row[$_GET["sort_types"]];
  !strcmp(strtoupper($_GET["order"]), "SORT_ASC") ? array_multisort($row_data, SORT_ASC, $service_status) : array_multisort($row_data, SORT_DESC, $service_status);
}
if (isset($_GET["sort_typeh"]) && $_GET["sort_typeh"]){
  foreach ($host_status as $key => $row)
    $row_data[$key] = $row[$_GET["sort_typeh"]];
  !strcmp(strtoupper($_GET["order"]), "SORT_ASC") ? array_multisort($row_data, SORT_ASC, $host_status) : array_multisort($row_data, SORT_DESC, $host_status);
}

if ($debug){ ?>
 <textarea cols='200' rows='50'><? print_r($host_status);print_r($service_status);?></textarea><?	
}

?> 