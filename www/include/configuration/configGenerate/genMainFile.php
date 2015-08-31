<?php
/*
 * Copyright 2015 Centreon (http://www.centreon.com/)
 * 
 * Centreon is a full-fledged industry-strength solution that meets 
 * the needs in IT infrastructure and application monitoring for 
 * service performance.
 * 
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * 
 *    http://www.apache.org/licenses/LICENSE-2.0  
 * 
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * 
 * For more information : contact@centreon.com
 * 
 */

	/*
	 * Get interval_lenth value
	 */
	$interval_length = 60;
	$query = "SELECT * FROM options WHERE `key` LIKE 'interval_length'";
	$res = $pearDB->query($query);
	if (false === PEAR::isError($res) && $res->numRows() == 1) {
		$row = $res->fetchRow();
		$interval_length = (int)$row['value'];
		$nagios["interval_length"] = $interval_length;
	}

	/*
	 * Update all interval_length value for each poller.
	 */
	$query = "UPDATE cfg_nagios SET interval_length = '".$interval_length."'";
	$res = $pearDB->query($query);
	if (PEAR::isError($res) == true) {
		print "Cannot update interval_length informations. Please check SQL logs.\n<br>";
	}
	
	$nagios["cfg_dir"] = NULL;
	foreach ($nagios as $key => $value)	{
		if ($value != NULL && $key != "nagios_id" && $key != "nagios_name" && $key != "nagios_server_id" && $key != "nagios_comment" && $key != "nagios_activate")	{
			if ($key == "enable_notifications" && $value == 2);
			else if ($key == "execute_service_checks" && $value == 2);
			else if ($key == "accept_passive_service_checks" && $value == 2);
			else if ($key == "execute_host_checks" && $value == 2);
			else if ($key == "accept_passive_host_checks" && $value == 2);
			else if ($key == "enable_event_handlers" && $value == 2);
			else if ($key == "check_external_commands" && $value == 2);
			else if ($key == "retain_state_information" && $value == 2);
			else if ($key == "use_retained_program_state" && $value == 2);
			else if ($key == "use_retained_scheduling_info" && $value == 2);
            else if ($key == "cfg_file");
			else if ($key == "use_syslog" && $value == 2);
			else if ($key == "log_notifications" && $value == 2);
			else if ($key == "log_service_retries" && $value == 2);
			else if ($key == "log_host_retries" && $value == 2);
			else if ($key == "log_event_handlers" && $value == 2);
			else if ($key == "log_initial_states" && $value == 2);
			else if ($key == "log_external_commands" && $value == 2);
			else if ($key == "log_passive_checks" && $value == 2);
			else if ($key == "auto_reschedule_checks" && $value == 2);
			else if ($key == "use_aggressive_host_checking" && $value == 2);
			else if ($key == "enable_flap_detection" && $value == 2);
			else if ($key == "soft_state_dependencies" && $value == 2);
			else if ($key == "obsess_over_services" && $value == 2);
			else if ($key == "obsess_over_hosts" && $value == 2);
			else if ($key == "process_performance_data" && $value == 2);
			else if ($key == "check_for_orphaned_hosts" && $value == 2);
			else if ($key == "check_for_orphaned_services" && $value == 2);
			else if ($key == "check_service_freshness" && $value == 2);
			else if ($key == "check_host_freshness" && $value == 2);
			else if ($key == "use_regexp_matching" && $value == 2);
			else if ($key == "use_true_regexp_matching" && $value == 2);
			else if ($key == "service_inter_check_delay_method" && $value == 2);
			else if ($key == "host_inter_check_delay_method" && $value == 2);
			else if ($key == "enable_predictive_host_dependency_checks" && $value == 2);
			else if ($key == "enable_predictive_service_dependency_checks" && $value == 2);
			else if ($key == "use_large_installation_tweaks" && $value == 2);
			else if ($key == "free_child_process_memory" && $value == 2);
			else if ($key == "child_processes_fork_twice" && $value == 2);
			else if ($key == "enable_environment_macros" && $value == 2);
            else if ($key == "use_setpgid" && ($value == 2 || $tab['monitoring_engine'] != 'CENGINE'));
			else if ($key == "enable_embedded_perl" && $value == 2);
			else if ($key == "use_embedded_perl_implicitly" && $value == 2);
			else if ($key == "host_perfdata_file_mode" && $value == 2);
			else if ($key == "translate_passive_host_checks" && $value == 2);
            else if (($key == "temp_file") && ((isset($tab['monitoring_engine']) && $tab['monitoring_engine'] == "CENGINE")));
            else if (($key == "p1_file") && ((isset($tab['monitoring_engine']) && $tab['monitoring_engine'] == "CENGINE")));
            else if (($key == "nagios_user") && ((isset($tab['monitoring_engine']) && $tab['monitoring_engine'] == "CENGINE")));
            else if (($key == "nagios_group") && ((isset($tab['monitoring_engine']) && $tab['monitoring_engine'] == "CENGINE")));
            else if (($key == "log_rotation_method") && ((isset($tab['monitoring_engine']) && $tab['monitoring_engine'] == "CENGINE")));
            else if (($key == "log_archive_path") && ((isset($tab['monitoring_engine']) && $tab['monitoring_engine'] == "CENGINE")));
            else if (($key == "lock_file") && ((isset($tab['monitoring_engine']) && $tab['monitoring_engine'] == "CENGINE")));
            else if (($key == "daemon_dumps_core") && ((isset($tab['monitoring_engine']) && $tab['monitoring_engine'] == "CENGINE")));
			else if ($key == "global_host_event_handler" && $value)	{
				$DBRESULT2 = $pearDB->query("SELECT command_name FROM `command` WHERE command_id = '".$value."'");
				$row = $DBRESULT2->fetchRow();
				$str .= $key."=".$row["command_name"]."\n";
			} else if ($key == "global_service_event_handler" && $value)	{
				$DBRESULT2 = $pearDB->query("SELECT command_name FROM `command` WHERE command_id = '".$value."'");
				$row = $DBRESULT2->fetchRow();
				$str .= $key."=".$row["command_name"]."\n";
			} else if ($key == "ocsp_command" && $value)	{
				$DBRESULT2 = $pearDB->query("SELECT command_name FROM `command` WHERE command_id = '".$value."'");
				$row = $DBRESULT2->fetchRow();
				$str .= $key."=".$row["command_name"]."\n";
			} else if ($key == "ochp_command" && $value)	{
				$DBRESULT2 = $pearDB->query("SELECT command_name FROM `command` WHERE command_id = '".$value."'");
				$row = $DBRESULT2->fetchRow();
				$str .= $key."=".$row["command_name"]."\n";
			} else if ($key == "host_perfdata_command" && $value)	{
				$DBRESULT2 = $pearDB->query("SELECT command_name FROM `command` WHERE command_id = '".$value."'");
				$row = $DBRESULT2->fetchRow();
				$str .= $key."=".$row["command_name"]."\n";
			} else if ($key == "service_perfdata_command" && $value)	{
				$DBRESULT2 = $pearDB->query("SELECT command_name FROM `command` WHERE command_id = '".$value."'");
				$row = $DBRESULT2->fetchRow();
				$str .= $key."=".$row["command_name"]."\n";
			} else if ($key == "host_perfdata_file_processing_command" && $value)	{
				$DBRESULT2 = $pearDB->query("SELECT command_name FROM `command` WHERE command_id = '".$value."'");
				$row = $DBRESULT2->fetchRow();
				$str .= $key."=".$row["command_name"]."\n";
			} else if ($key == "service_perfdata_file_processing_command" && $value)	{
				$DBRESULT2 = $pearDB->query("SELECT command_name FROM `command` WHERE command_id = '".$value."'");
				$row = $DBRESULT2->fetchRow();
				$str .= $key."=".$row["command_name"]."\n";
			} else if (($key == "nagios_user" || $key == "nagios_group") && $value)	{
				if (isset($tab['monitoring_engine']) && ($tab["monitoring_engine"] == "ICINGA")) {
					$key = str_replace("nagios", "icinga", $key);
				}
				$str .= $key."=".$value."\n";
			} else if ($key == "use_check_result_path")	{
				if (isset($tab['monitoring_engine']) && ($tab["monitoring_engine"] == "CENGINE") && $value != 2) {
                    if (isset($tab['monitoring_engine_version']) &&
                            (CentreonUtils::compareVersion($tab["engine_version"], "1.4.0") >= 1 )) {
                        $str .= $key."=".$value."\n";
                    }
				}
			}
            else if ($key == "broker_module") {
				foreach ($nagios["broker_module"] as $kBrm => $vBrm)
					if ( $vBrm["broker_module"] != NULL )
						$str .= $key."=".$vBrm["broker_module"]."\n";

			} else if ($key == "debug_level_opt");
			else
				$str .= $key."=".$value."\n";
		}
	}
	if (isset($tab['monitoring_engine']) && $tab['monitoring_engine'] == "NAGIOS") {
	    $str .= "check_for_updates=0\n";
	}
//	$str .= "bare_update_checks=1\n";
?>
