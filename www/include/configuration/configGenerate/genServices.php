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

	if (!is_dir($nagiosCFGPath.$tab['id']."/")) {
		mkdir($nagiosCFGPath.$tab['id']."/");
	}

	$handle = create_file($nagiosCFGPath.$tab['id']."/services.cfg", $oreon->user->get_name());
	$DBRESULT =& $pearDB->query("SELECT * FROM service ORDER BY `service_register`, `service_description`");
	if (PEAR::isError($DBRESULT))
		print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";
	$service = array();
	$i = 1;
	$str = NULL;
	while($DBRESULT->fetchInto($service))	{
		$BP = false;
		$LinkedToHost = 0;
		$strDef = "";
		array_key_exists($service["service_id"], $gbArr[4]) ? $BP = true : NULL;
		
		$service["service_description"] = str_replace('#S#', "/", $service["service_description"]);
		$service["service_description"] = str_replace('#BS#', "\\", $service["service_description"]);
		$service["service_alias"] = str_replace('#S#', "/", $service["service_alias"]);
		$service["service_alias"] = str_replace('#BS#', "\\", $service["service_alias"]);
		if ($BP)	{
			#
			## Can merge multiple Host or HostGroup Definition
			#
			$strTMP = NULL;
			$parent = false;
			$ret["comment"] ? ($strTMP .= "# '" . $service["service_description"] . "' service definition " . $i . "\n") : NULL;
			if ($ret["comment"] && $service["service_comment"])	{
				$comment = array();
				$comment = explode("\n", $service["service_comment"]);
				foreach ($comment as $cmt)
					$strTMP .= "# ".$cmt."\n";
			}
			$strTMP .= "define service{\n";
			if ($service["service_register"])	{
				#HostGroup Relation
				$hostGroup = array();
				$strTMPTemp = NULL;
				$DBRESULT2 =& $pearDB->query("SELECT hg.hg_id, hg.hg_name FROM host_service_relation hsr, hostgroup hg WHERE hsr.service_service_id ='".$service["service_id"]."' AND hsr.hostgroup_hg_id = hg.hg_id");
				if (PEAR::isError($DBRESULT2))
					print "DB Error : ".$DBRESULT2->getDebugInfo()."<br>";
				while($DBRESULT2->fetchInto($hostGroup))	{
					$BP = false;
					array_key_exists($hostGroup["hg_id"], $gbArr[3]) ? $BP = true : NULL;
					
					if ($BP && isset($generatedHG[$hostGroup["hg_id"]]) && $generatedHG[$hostGroup["hg_id"]]){
						$parent = true;
						$strTMPTemp != NULL ? $strTMPTemp .= ", ".$hostGroup["hg_name"] : $strTMPTemp = $hostGroup["hg_name"];
						$LinkedToHost++;
					}
				}
				$DBRESULT2->free();
				if ($strTMPTemp) $strTMP .= print_line("hostgroup_name", $strTMPTemp);
				unset($hostGroup);
				unset($strTMPTemp);
				if (!$parent)	{
					# Host Relation
					$host = array();
					$strTMPTemp = NULL;
					$DBRESULT2 =& $pearDB->query("SELECT host.host_id, host.host_name FROM host_service_relation hsr, host WHERE hsr.service_service_id ='".$service["service_id"]."' AND hsr.host_host_id = host.host_id");
					if (PEAR::isError($DBRESULT2))
						print "DB Error : ".$DBRESULT2->getDebugInfo()."<br>";
					while($DBRESULT2->fetchInto($host))	{
						$BP = false;
						array_key_exists($host["host_id"], $gbArr[2]) ? $BP = true : NULL;
						
						if ($BP)	{
							$parent = true;
							if (isHostOnThisInstance($host["host_id"], $tab['id'])){
								$strTMPTemp != NULL ? $strTMPTemp .= ", ".$host["host_name"] : $strTMPTemp = $host["host_name"];
								$LinkedToHost++;
							}
						}
					}
					$DBRESULT2->free();
					if ($strTMPTemp) $strTMP .= print_line("host_name", $strTMPTemp);
					unset($host);
				}
				unset($strTMPTemp);
			}
			if (!$service["service_register"] && $service["service_description"])	{
				$strTMP .= print_line("name", $service["service_description"]);
				$strTMP .= print_line("service_description", $service["service_description"]);
			}
			else if ($service["service_description"]) 
				$strTMP .= print_line("service_description", $service["service_description"]);
			# Template Model Relation
			if ($service["service_template_model_stm_id"]) {
				$serviceTemplate = array();
				$DBRESULT2 =& $pearDB->query("SELECT service.service_description FROM service WHERE service.service_id = '".$service["service_template_model_stm_id"]."' LIMIT 1");
				if (PEAR::isError($DBRESULT2))
					print "DB Error : ".$DBRESULT2->getDebugInfo()."<br>";
				while($DBRESULT2->fetchInto($serviceTemplate))	{
					$serviceTemplate["service_description"] = str_replace('#S#', "/", $serviceTemplate["service_description"]);
					$serviceTemplate["service_description"] = str_replace('#BS#', "\\", $serviceTemplate["service_description"]);
					$strTMP .= print_line("use", $serviceTemplate["service_description"]);
				}
				$DBRESULT2->free();
				unset($serviceTemplate);		
			}
			# Nagios V2 : Servicegroups relation
			if ($oreon->user->get_version() == 2)	{
				$serviceGroup = array();
				$strTMPTemp = NULL;
				$DBRESULT2 =& $pearDB->query("SELECT DISTINCT sg.sg_id, sg.sg_name FROM servicegroup_relation sgr, servicegroup sg WHERE sgr.service_service_id = '".$service["service_id"]."' AND sgr.servicegroup_sg_id = sg.sg_id ORDER BY `sg_name`");
				if (PEAR::isError($DBRESULT2))
					print "DB Error : ".$DBRESULT2->getDebugInfo()."<br>";
				while($DBRESULT2->fetchInto($serviceGroup))	{
					$BP = false;
					array_key_exists($serviceGroup["sg_id"], $gbArr[5]) ? $BP = true : NULL;
					
					if ($BP)
						$strTMPTemp != NULL ? $strTMPTemp .= ", ".$serviceGroup["sg_name"] : $strTMPTemp = $serviceGroup["sg_name"];
				}
				$DBRESULT2->free();
				if ($strTMPTemp) $strTMP .= print_line("servicegroups", $strTMPTemp);
				unset($serviceGroup);
				unset($strTMPTemp);
			}
			if ($service["service_is_volatile"] != 2) $strTMP .= print_line("is_volatile", $service["service_is_volatile"] == 1 ? "1": "0");
			# Check Command
			$command = NULL;
			$command = getMyCheckCmdParam($service["service_id"]);
			if ($command)
				$strTMP .= print_line("check_command", $command);
			#
			
			if ($service["service_max_check_attempts"] != NULL) $strTMP .= print_line("max_check_attempts", $service["service_max_check_attempts"]);
			if ($service["service_normal_check_interval"] != NULL) $strTMP .= print_line("normal_check_interval", $service["service_normal_check_interval"]);
			if ($service["service_retry_check_interval"] != NULL) $strTMP .= print_line("retry_check_interval", $service["service_retry_check_interval"]);
			if ($service["service_active_checks_enabled"] != 2) $strTMP .= print_line("active_checks_enabled", $service["service_active_checks_enabled"] == 1 ? "1": "0");
			if ($service["service_passive_checks_enabled"] != 2) $strTMP .= print_line("passive_checks_enabled", $service["service_passive_checks_enabled"] == 1 ? "1": "0");
			
			/*
			 * Check Period
			 */
			
			$timePeriod = array();
			$DBRESULT2 =& $pearDB->query("SELECT tp.tp_name FROM timeperiod tp WHERE tp.tp_id = '".$service["timeperiod_tp_id"]."' LIMIT 1");
			if (PEAR::isError($DBRESULT2))
				print "DB Error : ".$DBRESULT2->getDebugInfo()."<br>";
			while($DBRESULT2->fetchInto($timePeriod))
				$strTMP .= print_line("check_period", $timePeriod["tp_name"]);
			$DBRESULT2->free();
			unset($timePeriod);
			#
			if ($service["service_parallelize_check"] != 2) $strTMP .= print_line("parallelize_check", $service["service_parallelize_check"] == 1 ? "1": "0");
			if ($service["service_obsess_over_service"] != 2) $strTMP .= print_line("obsess_over_service", $service["service_obsess_over_service"] == 1 ? "1": "0");
			if ($service["service_check_freshness"] != 2) $strTMP .= print_line("check_freshness", $service["service_check_freshness"] == 1 ? "1": "0");
			if ($service["service_freshness_threshold"] != NULL) $strTMP .= print_line("freshness_threshold", $service["service_freshness_threshold"]);
			
			/*
			 * Event_handler
			 */
			 
			$command = array();
			$service["command_command_id_arg2"] = str_replace('#BR#', "\\n", $service["command_command_id_arg2"]);
			$service["command_command_id_arg2"] = str_replace('#T#', "\\t", $service["command_command_id_arg2"]);
			$service["command_command_id_arg2"] = str_replace('#R#', "\\r", $service["command_command_id_arg2"]);
			$service["command_command_id_arg2"] = str_replace('#S#', "/", $service["command_command_id_arg2"]);
			$service["command_command_id_arg2"] = str_replace('#BS#', "\\", $service["command_command_id_arg2"]);
			$DBRESULT2 =& $pearDB->query("SELECT cmd.command_name FROM command cmd WHERE cmd.command_id = '".$service["command_command_id2"]."' LIMIT 1");
			if (PEAR::isError($DBRESULT2))
				print "DB Error : ".$DBRESULT2->getDebugInfo()."<br>";
			while($DBRESULT2->fetchInto($command))
				$strTMP .= print_line("event_handler", strstr($command["command_name"], "check_graph_") ? $command["command_name"].$service["command_command_id_arg2"]."!".$service["service_id"] : $command["command_name"].$service["command_command_id_arg2"]);
			$DBRESULT2->free();
			unset($command);
			#
			if ($service["service_event_handler_enabled"] != 2) $strTMP .= print_line("event_handler_enabled", $service["service_event_handler_enabled"] == 1 ? "1": "0");
			if ($service["service_low_flap_threshold"] != NULL) $strTMP .= print_line("low_flap_threshold", $service["service_low_flap_threshold"]);
			if ($service["service_high_flap_threshold"] != NULL) $strTMP .= print_line("high_flap_threshold", $service["service_high_flap_threshold"]);
			if ($service["service_flap_detection_enabled"] != 2) $strTMP .= print_line("flap_detection_enabled", $service["service_flap_detection_enabled"] == 1 ? "1": "0");
			if ($service["service_process_perf_data"] != 2) $strTMP .= print_line("process_perf_data", $service["service_process_perf_data"] == 1 ? "1": "0");
			if ($service["service_retain_status_information"] != 2) $strTMP .= print_line("retain_status_information", $service["service_retain_status_information"] == 1 ? "1": "0");
			if ($service["service_retain_nonstatus_information"] != 2) $strTMP .= print_line("retain_nonstatus_information", $service["service_retain_nonstatus_information"] == 1 ? "1": "0");
			if ($service["service_notification_interval"] != NULL) $strTMP .= print_line("notification_interval", $service["service_notification_interval"]);
			# Timeperiod name
			$timePeriod = array();
			$DBRESULT2 =& $pearDB->query("SELECT tp.tp_name FROM timeperiod tp WHERE tp.tp_id = '".$service["timeperiod_tp_id2"]."' LIMIT 1");
			if (PEAR::isError($DBRESULT2))
				print "DB Error : ".$DBRESULT2->getDebugInfo()."<br>";
			while($DBRESULT2->fetchInto($timePeriod))
				$strTMP .= print_line("notification_period", $timePeriod["tp_name"]);
			$DBRESULT2->free();
			unset($timePeriod);
			#
			if ($service["service_notification_options"]) $strTMP .= print_line("notification_options", $service["service_notification_options"]);
			if ($service["service_notifications_enabled"] != 2) $strTMP .= print_line("notifications_enabled", $service["service_notifications_enabled"] == 1 ? "1": "0");
			# Contact Group Relation
			$contactGroup = array();
			$strTMPTemp = NULL;
			$DBRESULT2 =& $pearDB->query("SELECT cg.cg_id, cg.cg_name FROM contactgroup_service_relation csr, contactgroup cg WHERE csr.service_service_id = '".$service["service_id"]."' AND csr.contactgroup_cg_id = cg.cg_id ORDER BY `cg_name`");
			if (PEAR::isError($DBRESULT2))
				print "DB Error : ".$DBRESULT2->getDebugInfo()."<br>";
			while($DBRESULT2->fetchInto($contactGroup))	{
				$BP = false;
				array_key_exists($contactGroup["cg_id"], $gbArr[1]) ? $BP = true : NULL;
				
				if ($BP)
					$strTMPTemp != NULL ? $strTMPTemp .= ", ".$contactGroup["cg_name"] : $strTMPTemp = $contactGroup["cg_name"];
			}
			$DBRESULT2->free();
			if ($strTMPTemp) $strTMP .= print_line("contact_groups", $strTMPTemp);
			unset($contactGroup);
			#
			if ($service["service_stalking_options"]) $strTMP .= print_line("stalking_options", $service["service_stalking_options"]);
			if (!$service["service_register"]) $strTMP .= print_line("register", "0");
			
			if (isset($service["service_register"]) && $service["service_register"] == 0){
				$DBRESULT_TEMP =& $pearDB->query("SELECT host_name FROM host, host_service_relation WHERE `service_service_id` = '".$service["service_id"]."' AND `host_id` = `host_host_id`");
				if (PEAR::isError($DBRESULT_TEMP))
					print "DB Error : ".$DBRESULT_TEMP->getDebugInfo()."<br>";
				while($DBRESULT_TEMP->fetchInto($template_link))
					$strTMP .= print_line("#TEMPLATE-HOST-LINK", $template_link["host_name"]);
				unset($template_link);
				unset($DBRESULT_TEMP);
			}
			
			$strTMP .= "}\n\n";
			if (!$service["service_register"] || $LinkedToHost)	{
				$i++;
				$str .= $strTMP;
			}
			unset($parent);
			unset($strTMPTemp);
		}
	}
	unset($service);
	write_in_file($handle, html_entity_decode($str, ENT_QUOTES), $nagiosCFGPath.$tab['id']."/services.cfg");
	fclose($handle);
	$DBRESULT->free();
	unset($str);
	unset($i);
?>