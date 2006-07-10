<?php
/**
Oreon is developped with GPL Licence 2.0 :
http://www.gnu.org/licenses/gpl.txt
Developped by : Julien Mathis - Romain Le Merlus

Adapted to Pear library by Merethis company, under direction of Cedrick Facon, Romain Le Merlus, Julien Mathis

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

	$handle = create_file($nagiosCFGPath."services.cfg", $oreon->user->get_name());
	$res =& $pearDB->query("SELECT * FROM service ORDER BY `service_register`, `service_description`");
	$service = array();
	$i = 1;
	$str = NULL;
	while($res->fetchInto($service))	{
		$BP = false;
		if ($ret["level"]["level"] == 1)
			array_key_exists($service["service_id"], $gbArr[4]) ? $BP = true : NULL;
		else if ($ret["level"]["level"] == 2)
			array_key_exists($service["service_id"], $gbArr[4]) ? $BP = true : NULL;
		else if ($ret["level"]["level"] == 3)
			$BP = true;
		if ($BP)	{
			if ($service["service_register"] && isACheckGraphService($service["service_id"]))	{
				#
				## Create a definition for each Service (need to put the service ID)
				#
				$parent = false;
				$hostArr = array();
				//HostGroup Relation
				$hostGroup = array();
				$res2 =& $pearDB->query("SELECT hostgroup_hg_id FROM host_service_relation hsr WHERE hsr.service_service_id ='".$service["service_id"]."'");
				while($res2->fetchInto($hostGroup))	{
					$BP = false;
					if ($ret["level"]["level"] == 1)
						array_key_exists($hostGroup["hostgroup_hg_id"], $gbArr[3]) ? $BP = true : NULL;
					else if ($ret["level"]["level"] == 2)
						array_key_exists($hostGroup["hostgroup_hg_id"], $gbArr[3]) ? $BP = true : NULL;
					else if ($ret["level"]["level"] == 3)
						$BP = true;
					if ($BP)	{
						$res3 =& $pearDB->query("SELECT host.host_id, host.host_name FROM hostgroup_relation hgr, host WHERE hgr.hostgroup_hg_id ='".$hostGroup["hostgroup_hg_id"]."' AND hgr.host_host_id = host.host_id");
						while($res3->fetchInto($host))	{
							$BP = false;
							if ($ret["level"]["level"] == 1)
								array_key_exists($host["host_id"], $gbArr[2]) ? $BP = true : NULL;
							else if ($ret["level"]["level"] == 2)
								array_key_exists($host["host_id"], $gbArr[2]) ? $BP = true : NULL;
							else if ($ret["level"]["level"] == 3)
								$BP = true;
							if ($BP)	{
								$parent = true;
								$hostArr[$host["host_id"]] = $host["host_name"];
							}
						}
					}
				}
				$res2->free();
				unset($hostGroup);
				if (!$parent)	{
					//Host Relation
					$host = array();
					$res2 =& $pearDB->query("SELECT host.host_id, host.host_name FROM host_service_relation hsr, host WHERE hsr.service_service_id ='".$service["service_id"]."' AND hsr.host_host_id = host.host_id");
					while($res2->fetchInto($host))	{
						$BP = false;
						if ($ret["level"]["level"] == 1)
							array_key_exists($host["host_id"], $gbArr[2]) ? $BP = true : NULL;
						else if ($ret["level"]["level"] == 2)
							array_key_exists($host["host_id"], $gbArr[2]) ? $BP = true : NULL;
						else if ($ret["level"]["level"] == 3)
							$BP = true;
						if ($BP)	{
							$parent = true;
							$hostArr[$host["host_id"]] = $host["host_name"];
						}
					}
					$res2->free();
					unset($host);
				}
				foreach ($hostArr as $key=>$value)	{
					$strTMP = NULL;
					$ret["comment"]["comment"] ? ($strTMP .= "# '" . $service["service_description"] . "' service definition " . $i . "\n") : NULL;
					$ret["comment"]["comment"] ? ($strTMP .= "# ID_OREON:'" . $service["service_id"] . "'\n") : NULL;
					if ($ret["comment"]["comment"] && $service["service_comment"])	{
						$comment = array();
						$comment = explode("\n", $service["service_comment"]);
						foreach ($comment as $cmt)
							$strTMP .= "# ".$cmt."\n";
					}
					$strTMP .= "define service{\n";
					$strTMP .= print_line("host_name", $value);
					if (!$service["service_register"] && $service["service_description"])	{
						$strTMP .= print_line("name", $service["service_description"]);
						$strTMP .= print_line("service_description", $service["service_description"]);
					}
					else if ($service["service_description"]) 
						$strTMP .= print_line("service_description", $service["service_description"]);
					//Template Model Relation
					if ($service["service_template_model_stm_id"]) {
						$serviceTemplate = array();
						$res2 =& $pearDB->query("SELECT service.service_description FROM service WHERE service.service_id = '".$service["service_template_model_stm_id"]."'");
						while($res2->fetchInto($serviceTemplate))
							$strTMP .= print_line("use", $serviceTemplate["service_description"]);
						$res2->free();
						unset($serviceTemplate);		
					}
					// Nagios V2 : Servicegroups relation
					if ($oreon->user->get_version() == 2)	{
						$serviceGroup = array();
						$strTMPTemp = NULL;
						$res2 =& $pearDB->query("SELECT sg.sg_id, sg.sg_name FROM servicegroup_relation sgr, servicegroup sg WHERE sgr.service_service_id = '".$service["service_id"]."' AND sgr.servicegroup_sg_id = sg.sg_id ORDER BY `sg_name`");
						while($res2->fetchInto($serviceGroup))	{
							$BP = false;
							if ($ret["level"]["level"] == 1)
								array_key_exists($serviceGroup["sg_id"], $gbArr[5]) ? $BP = true : NULL;
							else if ($ret["level"]["level"] == 2)
								array_key_exists($serviceGroup["sg_id"], $gbArr[5]) ? $BP = true : NULL;
							else if ($ret["level"]["level"] == 3)
								$BP = true;
							if ($BP)
								$strTMPTemp != NULL ? $strTMPTemp .= ", ".$serviceGroup["sg_name"] : $strTMPTemp = $serviceGroup["sg_name"];
						}
						$res2->free();
						if ($strTMPTemp) $strTMP .= print_line("servicegroups", $strTMPTemp);
						unset($serviceGroup);
						unset($strTMPTemp);
					}
					if ($service["service_is_volatile"] != 2) $strTMP .= print_line("is_volatile", $service["service_is_volatile"] == 1 ? "1": "0");
					//Check Command
					$command = NULL;
					$command = getMyCheckCmdGraph($service["service_id"], $key);
					if ($command)
						$strTMP .= print_line("check_command", $command);
					//
					if ($service["service_max_check_attempts"] != NULL) $strTMP .= print_line("max_check_attempts", $service["service_max_check_attempts"]);
					if ($service["service_normal_check_interval"] != NULL) $strTMP .= print_line("normal_check_interval", $service["service_normal_check_interval"]);
					if ($service["service_retry_check_interval"] != NULL) $strTMP .= print_line("retry_check_interval", $service["service_retry_check_interval"]);
					if ($service["service_active_checks_enabled"] != 2) $strTMP .= print_line("active_checks_enabled", $service["service_active_checks_enabled"] == 1 ? "1": "0");
					if ($service["service_passive_checks_enabled"] != 2) $strTMP .= print_line("passive_checks_enabled", $service["service_passive_checks_enabled"] == 1 ? "1": "0");
					//Check Period
					$timePeriod = array();
					$res2 =& $pearDB->query("SELECT tp.tp_name FROM timeperiod tp WHERE tp.tp_id = '".$service["timeperiod_tp_id"]."' LIMIT 1");
					while($res2->fetchInto($timePeriod))
						$strTMP .= print_line("check_period", $timePeriod["tp_name"]);
					$res2->free();
					unset($timePeriod);
					//
					if ($service["service_parallelize_check"] != 2) $strTMP .= print_line("parallelize_check", $service["service_parallelize_check"] == 1 ? "1": "0");
					if ($service["service_obsess_over_service"] != 2) $strTMP .= print_line("obsess_over_service", $service["service_obsess_over_service"] == 1 ? "1": "0");
					if ($service["service_check_freshness"] != 2) $strTMP .= print_line("check_freshness", $service["service_check_freshness"] == 1 ? "1": "0");
					if ($service["service_freshness_threshold"] != NULL) $strTMP .= print_line("freshness_threshold", $service["service_freshness_threshold"]);
					//Event_handler
					$command = array();
					$service["command_command_id_arg2"] = str_replace('#BR#', "\\n", $service["command_command_id_arg2"]);
					$service["command_command_id_arg2"] = str_replace('#T#', "\\t", $service["command_command_id_arg2"]);
					$service["command_command_id_arg2"] = str_replace('#R#', "\\r", $service["command_command_id_arg2"]);
					$service["command_command_id_arg2"] = str_replace('#S#', "/", $service["command_command_id_arg2"]);
					$service["command_command_id_arg2"] = str_replace('#BS#', "\\", $service["command_command_id_arg2"]);
					$res2 =& $pearDB->query("SELECT cmd.command_name FROM command cmd WHERE cmd.command_id = '".$service["command_command_id2"]."' LIMIT 1");
					while($res2->fetchInto($command))
						$strTMP .= print_line("event_handler", strstr($command["command_name"], "check_graph_") ? $command["command_name"].$service["command_command_id_arg2"]."!".$service["service_id"] : $command["command_name"].$service["command_command_id_arg2"]);
					$res2->free();
					unset($command);
					//
					if ($service["service_event_handler_enabled"] != 2) $strTMP .= print_line("event_handler_enabled", $service["service_event_handler_enabled"] == 1 ? "1": "0");
					if ($service["service_low_flap_threshold"] != NULL) $strTMP .= print_line("low_flap_threshold", $service["service_low_flap_threshold"]);
					if ($service["service_high_flap_threshold"] != NULL) $strTMP .= print_line("high_flap_threshold", $service["service_high_flap_threshold"]);
					if ($service["service_flap_detection_enabled"] != 2) $strTMP .= print_line("flap_detection_enabled", $service["service_flap_detection_enabled"] == 1 ? "1": "0");
					if ($service["service_process_perf_data"] != 2) $strTMP .= print_line("process_perf_data", $service["service_process_perf_data"] == 1 ? "1": "0");
					if ($service["service_retain_status_information"] != 2) $strTMP .= print_line("retain_status_information", $service["service_retain_status_information"] == 1 ? "1": "0");
					if ($service["service_retain_nonstatus_information"] != 2) $strTMP .= print_line("retain_nonstatus_information", $service["service_retain_nonstatus_information"] == 1 ? "1": "0");
					if ($service["service_notification_interval"] != NULL) $strTMP .= print_line("notification_interval", $service["service_notification_interval"]);
					// Timeperiod name
					$timePeriod = array();
					$res2 =& $pearDB->query("SELECT tp.tp_name FROM timeperiod tp WHERE tp.tp_id = '".$service["timeperiod_tp_id2"]."' LIMIT 1");
					while($res2->fetchInto($timePeriod))
						$strTMP .= print_line("notification_period", $timePeriod["tp_name"]);
					$res2->free();
					unset($timePeriod);
					//
					if ($service["service_notification_options"]) $strTMP .= print_line("notification_options", $service["service_notification_options"]);
					if ($service["service_notifications_enabled"] != 2) $strTMP .= print_line("notifications_enabled", $service["service_notifications_enabled"] == 1 ? "1": "0");
					// Contact Group Relation
					$contactGroup = array();
					$strTMPTemp = NULL;
					$res2 =& $pearDB->query("SELECT cg.cg_id, cg.cg_name FROM contactgroup_service_relation csr, contactgroup cg WHERE csr.service_service_id = '".$service["service_id"]."' AND csr.contactgroup_cg_id = cg.cg_id ORDER BY `cg_name`");
					while($res2->fetchInto($contactGroup))	{
						$BP = false;
						if ($ret["level"]["level"] == 1)
							array_key_exists($contactGroup["cg_id"], $gbArr[1]) ? $BP = true : NULL;
						else if ($ret["level"]["level"] == 2)
							array_key_exists($contactGroup["cg_id"], $gbArr[1]) ? $BP = true : NULL;
						else if ($ret["level"]["level"] == 3)
							$BP = true;
						if ($BP)
							$strTMPTemp != NULL ? $strTMPTemp .= ", ".$contactGroup["cg_name"] : $strTMPTemp = $contactGroup["cg_name"];
					}
					$res2->free();
					if ($strTMPTemp) $strTMP .= print_line("contact_groups", $strTMPTemp);
					unset($contactGroup);
					//
					if ($service["service_stalking_options"]) $strTMP .= print_line("stalking_options", $service["service_stalking_options"]);
					if (!$service["service_register"]) $strTMP .= print_line("register", "0");
					$strTMP .= "}\n\n";
					$i++;
					$str .= $strTMP;
					unset($parent);
					unset($strTMPTemp);					
				}
			}
			else	{
				#
				## Can merge multiple Host or HostGroup Definition
				#
				$strTMP = NULL;
				$parent = false;
				$ret["comment"]["comment"] ? ($strTMP .= "# '" . $service["service_description"] . "' service definition " . $i . "\n") : NULL;
				$ret["comment"]["comment"] ? ($strTMP .= "# ID_OREON:'" . $service["service_id"] . "'\n") : NULL;
				if ($ret["comment"]["comment"] && $service["service_comment"])	{
					$comment = array();
					$comment = explode("\n", $service["service_comment"]);
					foreach ($comment as $cmt)
						$strTMP .= "# ".$cmt."\n";
				}
				$strTMP .= "define service{\n";
				if ($service["service_register"])	{
					//HostGroup Relation
					$hostGroup = array();
					$strTMPTemp = NULL;
					$res2 =& $pearDB->query("SELECT hg.hg_id, hg.hg_name FROM host_service_relation hsr, hostgroup hg WHERE hsr.service_service_id ='".$service["service_id"]."' AND hsr.hostgroup_hg_id = hg.hg_id");
					while($res2->fetchInto($hostGroup))	{
						$BP = false;
						if ($ret["level"]["level"] == 1)
							array_key_exists($hostGroup["hg_id"], $gbArr[3]) ? $BP = true : NULL;
						else if ($ret["level"]["level"] == 2)
							array_key_exists($hostGroup["hg_id"], $gbArr[3]) ? $BP = true : NULL;
						else if ($ret["level"]["level"] == 3)
							$BP = true;
						if ($BP)	{
							$parent = true;
							$strTMPTemp != NULL ? $strTMPTemp .= ", ".$hostGroup["hg_name"] : $strTMPTemp = $hostGroup["hg_name"];
						}
					}
					$res2->free();
					if ($strTMPTemp) $strTMP .= print_line("hostgroup_name", $strTMPTemp);
					unset($hostGroup);
					unset($strTMPTemp);
					if (!$parent)	{
						//Host Relation
						$host = array();
						$strTMPTemp = NULL;
						$res2 =& $pearDB->query("SELECT host.host_id, host.host_name FROM host_service_relation hsr, host WHERE hsr.service_service_id ='".$service["service_id"]."' AND hsr.host_host_id = host.host_id");
						while($res2->fetchInto($host))	{
							$BP = false;
							if ($ret["level"]["level"] == 1)
								array_key_exists($host["host_id"], $gbArr[2]) ? $BP = true : NULL;
							else if ($ret["level"]["level"] == 2)
								array_key_exists($host["host_id"], $gbArr[2]) ? $BP = true : NULL;
							else if ($ret["level"]["level"] == 3)
								$BP = true;
							if ($BP)	{
								$parent = true;
								$strTMPTemp != NULL ? $strTMPTemp .= ", ".$host["host_name"] : $strTMPTemp = $host["host_name"];
							}
						}
						$res2->free();
						if ($strTMPTemp) $strTMP .= print_line("host_name", $strTMPTemp);
						unset($host);
					}
					unset($strTMPTemp);
				//
				}
				if (!$service["service_register"] && $service["service_description"])	{
					$strTMP .= print_line("name", $service["service_description"]);
					$strTMP .= print_line("service_description", $service["service_description"]);
				}
				else if ($service["service_description"]) 
					$strTMP .= print_line("service_description", $service["service_description"]);
				//Template Model Relation
				if ($service["service_template_model_stm_id"]) {
					$serviceTemplate = array();
					$res2 =& $pearDB->query("SELECT service.service_description FROM service WHERE service.service_id = '".$service["service_template_model_stm_id"]."'");
					while($res2->fetchInto($serviceTemplate))
						$strTMP .= print_line("use", $serviceTemplate["service_description"]);
					$res2->free();
					unset($serviceTemplate);		
				}
				// Nagios V2 : Servicegroups relation
				if ($oreon->user->get_version() == 2)	{
					$serviceGroup = array();
					$strTMPTemp = NULL;
					$res2 =& $pearDB->query("SELECT sg.sg_id, sg.sg_name FROM servicegroup_relation sgr, servicegroup sg WHERE sgr.service_service_id = '".$service["service_id"]."' AND sgr.servicegroup_sg_id = sg.sg_id ORDER BY `sg_name`");
					while($res2->fetchInto($serviceGroup))	{
						$BP = false;
						if ($ret["level"]["level"] == 1)
							array_key_exists($serviceGroup["sg_id"], $gbArr[5]) ? $BP = true : NULL;
						else if ($ret["level"]["level"] == 2)
							array_key_exists($serviceGroup["sg_id"], $gbArr[5]) ? $BP = true : NULL;
						else if ($ret["level"]["level"] == 3)
							$BP = true;
						if ($BP)
							$strTMPTemp != NULL ? $strTMPTemp .= ", ".$serviceGroup["sg_name"] : $strTMPTemp = $serviceGroup["sg_name"];
					}
					$res2->free();
					if ($strTMPTemp) $strTMP .= print_line("servicegroups", $strTMPTemp);
					unset($serviceGroup);
					unset($strTMPTemp);
				}
				if ($service["service_is_volatile"] != 2) $strTMP .= print_line("is_volatile", $service["service_is_volatile"] == 1 ? "1": "0");
				//Check Command
				$command = NULL;
				$command = getMyCheckCmdGraph($service["service_id"]);
				if ($command)
					$strTMP .= print_line("check_command", $command);
				//
				if ($service["service_max_check_attempts"] != NULL) $strTMP .= print_line("max_check_attempts", $service["service_max_check_attempts"]);
				if ($service["service_normal_check_interval"] != NULL) $strTMP .= print_line("normal_check_interval", $service["service_normal_check_interval"]);
				if ($service["service_retry_check_interval"] != NULL) $strTMP .= print_line("retry_check_interval", $service["service_retry_check_interval"]);
				if ($service["service_active_checks_enabled"] != 2) $strTMP .= print_line("active_checks_enabled", $service["service_active_checks_enabled"] == 1 ? "1": "0");
				if ($service["service_passive_checks_enabled"] != 2) $strTMP .= print_line("passive_checks_enabled", $service["service_passive_checks_enabled"] == 1 ? "1": "0");
				//Check Period
				$timePeriod = array();
				$res2 =& $pearDB->query("SELECT tp.tp_name FROM timeperiod tp WHERE tp.tp_id = '".$service["timeperiod_tp_id"]."' LIMIT 1");
				while($res2->fetchInto($timePeriod))
					$strTMP .= print_line("check_period", $timePeriod["tp_name"]);
				$res2->free();
				unset($timePeriod);
				//
				if ($service["service_parallelize_check"] != 2) $strTMP .= print_line("parallelize_check", $service["service_parallelize_check"] == 1 ? "1": "0");
				if ($service["service_obsess_over_service"] != 2) $strTMP .= print_line("obsess_over_service", $service["service_obsess_over_service"] == 1 ? "1": "0");
				if ($service["service_check_freshness"] != 2) $strTMP .= print_line("check_freshness", $service["service_check_freshness"] == 1 ? "1": "0");
				if ($service["service_freshness_threshold"] != NULL) $strTMP .= print_line("freshness_threshold", $service["service_freshness_threshold"]);
				//Event_handler
				$command = array();
				$service["command_command_id_arg2"] = str_replace('#BR#', "\\n", $service["command_command_id_arg2"]);
				$service["command_command_id_arg2"] = str_replace('#T#', "\\t", $service["command_command_id_arg2"]);
				$service["command_command_id_arg2"] = str_replace('#R#', "\\r", $service["command_command_id_arg2"]);
				$service["command_command_id_arg2"] = str_replace('#S#', "/", $service["command_command_id_arg2"]);
				$service["command_command_id_arg2"] = str_replace('#BS#', "\\", $service["command_command_id_arg2"]);
				$res2 =& $pearDB->query("SELECT cmd.command_name FROM command cmd WHERE cmd.command_id = '".$service["command_command_id2"]."' LIMIT 1");
				while($res2->fetchInto($command))
					$strTMP .= print_line("event_handler", strstr($command["command_name"], "check_graph_") ? $command["command_name"].$service["command_command_id_arg2"]."!".$service["service_id"] : $command["command_name"].$service["command_command_id_arg2"]);
				$res2->free();
				unset($command);
				//
				if ($service["service_event_handler_enabled"] != 2) $strTMP .= print_line("event_handler_enabled", $service["service_event_handler_enabled"] == 1 ? "1": "0");
				if ($service["service_low_flap_threshold"] != NULL) $strTMP .= print_line("low_flap_threshold", $service["service_low_flap_threshold"]);
				if ($service["service_high_flap_threshold"] != NULL) $strTMP .= print_line("high_flap_threshold", $service["service_high_flap_threshold"]);
				if ($service["service_flap_detection_enabled"] != 2) $strTMP .= print_line("flap_detection_enabled", $service["service_flap_detection_enabled"] == 1 ? "1": "0");
				if ($service["service_process_perf_data"] != 2) $strTMP .= print_line("process_perf_data", $service["service_process_perf_data"] == 1 ? "1": "0");
				if ($service["service_retain_status_information"] != 2) $strTMP .= print_line("retain_status_information", $service["service_retain_status_information"] == 1 ? "1": "0");
				if ($service["service_retain_nonstatus_information"] != 2) $strTMP .= print_line("retain_nonstatus_information", $service["service_retain_nonstatus_information"] == 1 ? "1": "0");
				if ($service["service_notification_interval"] != NULL) $strTMP .= print_line("notification_interval", $service["service_notification_interval"]);
				// Timeperiod name
				$timePeriod = array();
				$res2 =& $pearDB->query("SELECT tp.tp_name FROM timeperiod tp WHERE tp.tp_id = '".$service["timeperiod_tp_id2"]."' LIMIT 1");
				while($res2->fetchInto($timePeriod))
					$strTMP .= print_line("notification_period", $timePeriod["tp_name"]);
				$res2->free();
				unset($timePeriod);
				//
				if ($service["service_notification_options"]) $strTMP .= print_line("notification_options", $service["service_notification_options"]);
				if ($service["service_notifications_enabled"] != 2) $strTMP .= print_line("notifications_enabled", $service["service_notifications_enabled"] == 1 ? "1": "0");
				// Contact Group Relation
				$contactGroup = array();
				$strTMPTemp = NULL;
				$res2 =& $pearDB->query("SELECT cg.cg_id, cg.cg_name FROM contactgroup_service_relation csr, contactgroup cg WHERE csr.service_service_id = '".$service["service_id"]."' AND csr.contactgroup_cg_id = cg.cg_id ORDER BY `cg_name`");
				while($res2->fetchInto($contactGroup))	{
					$BP = false;
					if ($ret["level"]["level"] == 1)
						array_key_exists($contactGroup["cg_id"], $gbArr[1]) ? $BP = true : NULL;
					else if ($ret["level"]["level"] == 2)
						array_key_exists($contactGroup["cg_id"], $gbArr[1]) ? $BP = true : NULL;
					else if ($ret["level"]["level"] == 3)
						$BP = true;
					if ($BP)
						$strTMPTemp != NULL ? $strTMPTemp .= ", ".$contactGroup["cg_name"] : $strTMPTemp = $contactGroup["cg_name"];
				}
				$res2->free();
				if ($strTMPTemp) $strTMP .= print_line("contact_groups", $strTMPTemp);
				unset($contactGroup);
				//
				if ($service["service_stalking_options"]) $strTMP .= print_line("stalking_options", $service["service_stalking_options"]);
				if (!$service["service_register"]) $strTMP .= print_line("register", "0");
				$strTMP .= "}\n\n";
				if ($parent || !$service["service_register"])	{
					$i++;
					$str .= $strTMP;
				}
				unset($parent);
				unset($strTMPTemp);
			}
		}
	}
	unset($service);
	write_in_file($handle, html_entity_decode($str, ENT_QUOTES), $nagiosCFGPath."services.cfg");
	fclose($handle);
	$res->free();
	unset($str);
	unset($i);
?>