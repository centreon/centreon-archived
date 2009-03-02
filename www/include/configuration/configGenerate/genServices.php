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

	require_once ("@CENTREON_ETC@/centreon.conf.php");
	require_once ($centreon_path . "/www/class/centreonService.class.php");

	/*
	 * Create file
	 */

	$handle = create_file($nagiosCFGPath.$tab['id']."/services.cfg", $oreon->user->get_name());
	
	/*
	 * Get Service List
	 */
	$DBRESULT =& $pearDB->query("SELECT * FROM `service` WHERE `service_activate` = '1' ORDER BY `service_register`, `service_description`");
	$service = array();
	$i = 1;
	$str = NULL;
	while ($service =& $DBRESULT->fetchRow()) {
		$LinkedToHost = 0;
		$strDef = "";
		
		/*
		 * Convert spécial char
		 */
		$service["service_description"] = convertServiceSpecialChar($service["service_description"]);
		$service["service_alias"] = convertServiceSpecialChar($service["service_alias"]);
				
		if (isset($gbArr[4][$service["service_id"]])) {
			
			/*
			 * Check GMT compatibility
			 * 
			 */
			 
			if ($oreon->CentreonGMT->used() == 1 && $service["service_register"] == 1) {
					
					/*
					 * List of HostGroups to be checked
					 */
					
					$hostGroups = array();
					
					/*
					 * HostGroup Relation
					 */
					$DBRESULT2 =& $pearDB->query(	"SELECT hg.hg_id, hg.hg_name " .
													"FROM host_service_relation hsr, hostgroup hg " .
													"WHERE hsr.service_service_id ='".$service["service_id"]."' " .
															"AND hsr.hostgroup_hg_id = hg.hg_id");
					while ($hostGroup =& $DBRESULT2->fetchRow())	{
						if (isset($generatedHG[$hostGroup["hg_id"]]) && $generatedHG[$hostGroup["hg_id"]]){
							$hostGroups[$hostGroup["hg_id"]] = $hostGroup["hg_name"];
							$LinkedToHost++;
						}
						unset($hostGroup);
					}
					$DBRESULT2->free();
					
					/*
					 * Hosts Relations
					 */
					$hosts = array();
					$DBRESULT2 =& $pearDB->query(	"SELECT host.host_id, host_location, host.host_name " .
													"FROM host_service_relation hsr, host " .
													"WHERE hsr.service_service_id ='".$service["service_id"]."' " .
															"AND hsr.host_host_id = host.host_id");
					while ($host =& $DBRESULT2->fetchRow())	{
						if (isset($gbArr[2][$host["host_id"]])) {
							if (isset($host_instance[$host["host_id"]])) {
								$parent = true;
								if (!isset($hosts[$host["host_location"]]))
									$hosts[$host["host_location"]] = array();
								$hosts[$host["host_location"]][$host["host_id"]] = $host["host_name"];
								$LinkedToHost++;
							}
						}
					}
					$DBRESULT2->free();
					unset($host);

					$strTMPTemp = "";
					if (count($hosts) || count($hostGroups)) {
						/*
						 * Emulate HG GTM management
						 */
						if (count($hostGroups)) {
							foreach ($hostGroups as $hg_id => $hg_name){
								$hostList = getMyHostGroupHosts($hg_id);
								foreach ($hostList as $host_id){
									$host_location = getMyHostFieldOnHost($host_id, "host_location");
									if (!isset($hosts[$host_location]))
										$hosts[$host_location] = array();
									$hosts[$host_location][$host_id] = getMyHostField($host_id, "host_name");
									unset($host_location);
									
								}
								unset($hg_id);
								unset($hg_name);
								unset($hostList);
							}
						}
						
						/*
						 * Generate by host
						 */
						if (count($hosts)) {
							foreach ($hosts as $gmt => $host) {
								$strTMP = NULL;
								$parent = false;
								$ret["comment"] ? ($strTMP .= "# '" . $service["service_description"] . "' service definition " . $i . "\n") : NULL;
								if ($ret["comment"] && $service["service_comment"])	{
									$comment = array();
									$comment = explode("\n", $service["service_comment"]);
									foreach ($comment as $cmt)
										$strTMP .= "# ".$cmt."\n";
								}
								/*
								 * Adjust host_location and time period name
								 */
								 if ($gmt > 0)
								 	$gmt = "-".$gmt;
								 else if ($gmt < 0)
								 	$gmt = abs($gmt);
								 else
									 $gmt = "";
					 
								$strTMP .= "define service{\n";
								
								/*
								 * Host List
								 */
								$strTMPHost = "";
								foreach ($host as $host_id => $hostList){
									if ($strTMPHost != "")
										$strTMPHost .= ",";
									$strTMPHost .= $hostList;
								}
								
								$strTMP .= print_line("host_name", $strTMPHost);
								unset($strTMPHost);
								
								$strTMP .= print_line("service_description", $service["service_description"]);
								
								/*
								 * Template Model Relation
								 */
								if ($service["service_template_model_stm_id"]) {
									$serviceTemplate = array();
									$DBRESULT2 =& $pearDB->query("SELECT service.service_description FROM service WHERE service.service_id = '".$service["service_template_model_stm_id"]."' LIMIT 1");
									while ($serviceTemplate =& $DBRESULT2->fetchRow())
										$strTMP .= print_line("use", convertServiceSpecialChar($serviceTemplate["service_description"]));
									$DBRESULT2->free();
									unset($serviceTemplate);		
								}
								
								
								$serviceGroup = array();
								$strTMPSG = NULL;
								$DBRESULT2 =& $pearDB->query("SELECT DISTINCT sg.sg_id, sg.sg_name FROM servicegroup_relation sgr, servicegroup sg WHERE sgr.service_service_id = '".$service["service_id"]."' AND sgr.servicegroup_sg_id = sg.sg_id ORDER BY `sg_name`");
								while ($serviceGroup =& $DBRESULT2->fetchRow())	{
									if (isset($gbArr[5][$serviceGroup["sg_id"]]))
										$strTMPSG != NULL ? $strTMPSG .= ", ".$serviceGroup["sg_name"] : $strTMPSG = $serviceGroup["sg_name"];
								}
								$DBRESULT2->free();
								if ($strTMPSG) 
									$strTMP .= print_line("servicegroups", $strTMPSG);
								unset($serviceGroup);
								unset($strTMPSG);
							
								if ($service["service_is_volatile"] != 2) 
									$strTMP .= print_line("is_volatile", $service["service_is_volatile"] == 1 ? "1": "0");
								
								/*
								 * Check Command
								 */
								$command = NULL;
								$command = getMyCheckCmdParam($service["service_id"]);
								if ($command)
									$strTMP .= print_line("check_command", $command);
								
								if ($service["service_max_check_attempts"] != NULL) 
									$strTMP .= print_line("max_check_attempts", $service["service_max_check_attempts"]);
								if ($service["service_normal_check_interval"] != NULL) 
									$strTMP .= print_line("normal_check_interval", $service["service_normal_check_interval"]);
								if ($service["service_retry_check_interval"] != NULL) 
									$strTMP .= print_line("retry_check_interval", $service["service_retry_check_interval"]);
								if ($service["service_active_checks_enabled"] != 2) 
									$strTMP .= print_line("active_checks_enabled", $service["service_active_checks_enabled"] == 1 ? "1": "0");
								if ($service["service_passive_checks_enabled"] != 2) 
									$strTMP .= print_line("passive_checks_enabled", $service["service_passive_checks_enabled"] == 1 ? "1": "0");
								
								/*
								 * Check Period
								 */
								
								if (!$service["timeperiod_tp_id"]) 
									$service["timeperiod_tp_id"] = getMyServiceField($service["service_id"], "timeperiod_tp_id");	
								$strTMP .= print_line("check_period", $timeperiods[$service["timeperiod_tp_id"]]."_GMT".$gmt);

								if ($service["service_parallelize_check"] != 2) 
									$strTMP .= print_line("parallelize_check", $service["service_parallelize_check"] == 1 ? "1": "0");
								if ($service["service_obsess_over_service"] != 2)
									$strTMP .= print_line("obsess_over_service", $service["service_obsess_over_service"] == 1 ? "1": "0");
								if ($service["service_check_freshness"] != 2) 
									$strTMP .= print_line("check_freshness", $service["service_check_freshness"] == 1 ? "1": "0");
								if ($service["service_freshness_threshold"] != NULL) 
									$strTMP .= print_line("freshness_threshold", $service["service_freshness_threshold"]);
								
								/*
								 * Event_handler
								 */
								 
								if ($service["command_command_id2"])
									$strTMP .= print_line("event_handler", $commands[$service["command_command_id2"]].$service["command_command_id_arg2"]);
								
								if ($service["service_event_handler_enabled"] != 2) 
									$strTMP .= print_line("event_handler_enabled", $service["service_event_handler_enabled"] == 1 ? "1": "0");
								if ($service["service_low_flap_threshold"] != NULL) 
									$strTMP .= print_line("low_flap_threshold", $service["service_low_flap_threshold"]);
								if ($service["service_high_flap_threshold"] != NULL) 
									$strTMP .= print_line("high_flap_threshold", $service["service_high_flap_threshold"]);
								if ($service["service_flap_detection_enabled"] != 2) 
									$strTMP .= print_line("flap_detection_enabled", $service["service_flap_detection_enabled"] == 1 ? "1": "0");
								if ($service["service_process_perf_data"] != 2) 
									$strTMP .= print_line("process_perf_data", $service["service_process_perf_data"] == 1 ? "1": "0");
								if ($service["service_retain_status_information"] != 2) 
									$strTMP .= print_line("retain_status_information", $service["service_retain_status_information"] == 1 ? "1": "0");
								if ($service["service_retain_nonstatus_information"] != 2) 
									$strTMP .= print_line("retain_nonstatus_information", $service["service_retain_nonstatus_information"] == 1 ? "1": "0");
								
								/*
								 * Notifications
								 */
								if (!$service["timeperiod_tp_id2"])
									$service["timeperiod_tp_id2"] = getMyServiceField($service["service_id"], "timeperiod_tp_id2");
								$strTMP .= print_line("notification_period", $timeperiods[$service["timeperiod_tp_id2"]]."_GMT".$gmt);

								if ($service["service_notification_interval"] != NULL) 
									$strTMP .= print_line("notification_interval", $service["service_notification_interval"]);
								if ($service["service_notification_options"]) 
									$strTMP .= print_line("notification_options", $service["service_notification_options"]);
								if ($service["service_notifications_enabled"] != 2) 
									$strTMP .= print_line("notifications_enabled", $service["service_notifications_enabled"] == 1 ? "1": "0");
								
								# Contact Group Relation
								$contactGroup = array();
								$strTMPTemp = NULL;
								$DBRESULT2 =& $pearDB->query("SELECT cg.cg_id, cg.cg_name FROM contactgroup_service_relation csr, contactgroup cg WHERE csr.service_service_id = '".$service["service_id"]."' AND csr.contactgroup_cg_id = cg.cg_id ORDER BY `cg_name`");
								while ($contactGroup =& $DBRESULT2->fetchRow())	{
									$BP = false;
									array_key_exists($contactGroup["cg_id"], $gbArr[1]) ? $BP = true : NULL;
									
									if ($BP)
										$strTMPTemp != NULL ? $strTMPTemp .= ", ".$contactGroup["cg_name"] : $strTMPTemp = $contactGroup["cg_name"];
								}
								$DBRESULT2->free();
								if ($strTMPTemp) 
									$strTMP .= print_line("contact_groups", $strTMPTemp);
								unset($contactGroup);
								
								/*
								 * Contact Relation only for Nagios 3
								 */
								if ($oreon->user->get_version() >= 3) {
									$contact = array();
									$strTMPTemp = NULL;
									$DBRESULT2 =& $pearDB->query("SELECT c.contact_id, c.contact_name FROM contact_service_relation csr, contact c WHERE csr.service_service_id = '".$service["service_id"]."' AND csr.contact_id = c.contact_id ORDER BY `contact_name`");
									while ($contact =& $DBRESULT2->fetchRow())	{
										$BP = false;
										isset($gbArr[0][$contact["contact_id"]]) ? $BP = true : NULL;					
										if ($BP)
											$strTMPTemp != NULL ? $strTMPTemp .= ", ".$contact["contact_name"] : $strTMPTemp = $contact["contact_name"];
									}
									$DBRESULT2->free();
									if ($strTMPTemp) $strTMP .= print_line("contacts", $strTMPTemp);
									unset($contact);
								}
								
								
								if ($service["service_stalking_options"]) 
									$strTMP .= print_line("stalking_options", $service["service_stalking_options"]);
								if (!$service["service_register"]) 
									$strTMP .= print_line("register", "0");
								
								if (isset($service["service_register"]) && $service["service_register"] == 0){
									$DBRESULT_TEMP =& $pearDB->query("SELECT host_name FROM host, host_service_relation WHERE `service_service_id` = '".$service["service_id"]."' AND `host_id` = `host_host_id`");
									while($template_link =& $DBRESULT_TEMP->fetchRow())
										$strTMP .= print_line(";TEMPLATE-HOST-LINK", $template_link["host_name"]);
									unset($template_link);
									unset($DBRESULT_TEMP);
								}
								
								
								if ($oreon->user->get_version() >= 3) {
									/*
									 * On-demand macros
									 */
									$rq = "SELECT svc_macro_name, svc_macro_value FROM on_demand_macro_service WHERE `svc_svc_id`=" . $service['service_id'];
									$DBRESULT3 =& $pearDB->query($rq);
									while($od_macro = $DBRESULT3->fetchRow()) {
										$mac_name = str_replace("\$_SERVICE", "_", $od_macro['svc_macro_name']);
										$mac_name = str_replace("\$", "", $mac_name);
										$mac_value = $od_macro['svc_macro_value'];
										$strTMP .= print_line($mac_name, $mac_value);
									}
									$DBRESULT3->free();
								
									/*
									 * Extended Informations
									 */
								
									$DBRESULT3 =& $pearDB->query("SELECT * FROM extended_service_information esi WHERE esi.service_service_id = '".$service["service_id"]."'");
									$esi =& $DBRESULT3->fetchRow();
									if ($field = $esi["esi_notes"])
										$strTMP .= print_line("notes", $field);
									if ($field = $esi["esi_notes_url"])
										$strTMP .= print_line("notes_url", $field);
									if ($field = $esi["esi_action_url"])
										$strTMP .= print_line("action_url", $field);
									if ($field = getMyHostExtendedInfoImage($esi["service_id"], "esi_icon_image"))
										$strTMP .= print_line("icon_image", $field);
									if ($field = $esi["esi_icon_image_alt"])
										$strTMP .= print_line("icon_image_alt", $field);
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
					}
			} else {
			
				/*
				 *  Can merge multiple Host or HostGroup Definition
				 */
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
					/*
					 * HostGroup Relation
					 */
					$hostGroup = array();
					$strTMPTemp = NULL;
					$DBRESULT2 =& $pearDB->query("SELECT hg.hg_id, hg.hg_name FROM host_service_relation hsr, hostgroup hg WHERE hsr.service_service_id ='".$service["service_id"]."' AND hsr.hostgroup_hg_id = hg.hg_id");
					while ($hostGroup =& $DBRESULT2->fetchRow())	{
						$BP = false;
						if (isset($generatedHG[$hostGroup["hg_id"]]) && $generatedHG[$hostGroup["hg_id"]]){
							$parent = true;
							$strTMPTemp != NULL ? $strTMPTemp .= ", ".$hostGroup["hg_name"] : $strTMPTemp = $hostGroup["hg_name"];
							$LinkedToHost++;
						}
					}
					$DBRESULT2->free();
					if ($strTMPTemp) 
						$strTMP .= print_line("hostgroup_name", $strTMPTemp);
					unset($hostGroup);
					unset($strTMPTemp);
					
					//if (!$parent)	{
						/*
						 * Host Relation
						 */
						$host = array();
						$strTMPTemp = NULL;
						$DBRESULT2 =& $pearDB->query("SELECT host.host_id, host.host_name FROM host_service_relation hsr, host WHERE hsr.service_service_id ='".$service["service_id"]."' AND hsr.host_host_id = host.host_id");
						while ($host =& $DBRESULT2->fetchRow())	{
							if (isset($gbArr[2][$host["host_id"]]))	{
								$parent = true;
								if (isset($host_instance[$host["host_id"]])){
									$strTMPTemp != NULL ? $strTMPTemp .= ", ".$host["host_name"] : $strTMPTemp = $host["host_name"];
									$LinkedToHost++;
								}
							}
						}
						$DBRESULT2->free();
						if ($strTMPTemp) 
							$strTMP .= print_line("host_name", $strTMPTemp);
						unset($host);
					//}
					unset($strTMPTemp);
				}
				if (!$service["service_register"] && $service["service_description"])	{
					$strTMP .= print_line("name", $service["service_description"]);
					$strTMP .= print_line("service_description", $service["service_alias"]);
				} else if ($service["service_description"]) 
					$strTMP .= print_line("service_description", $service["service_description"]);
				
				/*
				 * Template Model Relation
				 */
				if ($service["service_template_model_stm_id"]) {
					$serviceTemplate = array();
					$DBRESULT2 =& $pearDB->query("SELECT service.service_description FROM service WHERE service.service_id = '".$service["service_template_model_stm_id"]."' LIMIT 1");
					while ($serviceTemplate =& $DBRESULT2->fetchRow())
						$strTMP .= print_line("use", convertServiceSpecialChar($serviceTemplate["service_description"]));
					$DBRESULT2->free();
					unset($serviceTemplate);		
				}
				
				
				$serviceGroup = array();
				$strTMPTemp = NULL;
				$DBRESULT2 =& $pearDB->query("SELECT DISTINCT sg.sg_id, sg.sg_name FROM servicegroup_relation sgr, servicegroup sg WHERE sgr.service_service_id = '".$service["service_id"]."' AND sgr.servicegroup_sg_id = sg.sg_id ORDER BY `sg_name`");
				while($serviceGroup =& $DBRESULT2->fetchRow())	{
					$BP = false;
					isset($gbArr[5][$serviceGroup["sg_id"]]) ? $BP = true : NULL;
					
					if ($BP)
						$strTMPTemp != NULL ? $strTMPTemp .= ", ".$serviceGroup["sg_name"] : $strTMPTemp = $serviceGroup["sg_name"];
				}
				$DBRESULT2->free();
				if ($strTMPTemp) 
					$strTMP .= print_line("servicegroups", $strTMPTemp);
				unset($serviceGroup);
				unset($strTMPTemp);
			
				if ($service["service_is_volatile"] != 2) 
					$strTMP .= print_line("is_volatile", $service["service_is_volatile"] == 1 ? "1": "0");
				
				/*
				 * Check Command
				 */
				$command = NULL;
				$command = getMyCheckCmdParam($service["service_id"]);
				if ($command)
					$strTMP .= print_line("check_command", $command);
				
				if ($service["service_max_check_attempts"] != NULL) 
					$strTMP .= print_line("max_check_attempts", $service["service_max_check_attempts"]);
				if ($service["service_normal_check_interval"] != NULL) 
					$strTMP .= print_line("normal_check_interval", $service["service_normal_check_interval"]);
				if ($service["service_retry_check_interval"] != NULL) 
					$strTMP .= print_line("retry_check_interval", $service["service_retry_check_interval"]);
				if ($service["service_active_checks_enabled"] != 2) 
					$strTMP .= print_line("active_checks_enabled", $service["service_active_checks_enabled"] == 1 ? "1": "0");
				if ($service["service_passive_checks_enabled"] != 2) 
					$strTMP .= print_line("passive_checks_enabled", $service["service_passive_checks_enabled"] == 1 ? "1": "0");
				
				/*
				 * Check Period
				 */
				
				if ($service["timeperiod_tp_id"])
					$strTMP .= print_line("check_period", $timeperiods[$service["timeperiod_tp_id"]]);
				if ($service["service_parallelize_check"] != 2) 
					$strTMP .= print_line("parallelize_check", $service["service_parallelize_check"] == 1 ? "1": "0");
				if ($service["service_obsess_over_service"] != 2)
					$strTMP .= print_line("obsess_over_service", $service["service_obsess_over_service"] == 1 ? "1": "0");
				if ($service["service_check_freshness"] != 2) 
					$strTMP .= print_line("check_freshness", $service["service_check_freshness"] == 1 ? "1": "0");
				if ($service["service_freshness_threshold"] != NULL) 
					$strTMP .= print_line("freshness_threshold", $service["service_freshness_threshold"]);
				
				/*
				 * Event_handler
				 */
				 
				if ($service["command_command_id2"])
					$strTMP .= print_line("event_handler", $commands[$service["command_command_id2"]].$service["command_command_id_arg2"]);
				
				if ($service["service_event_handler_enabled"] != 2) 
					$strTMP .= print_line("event_handler_enabled", $service["service_event_handler_enabled"] == 1 ? "1": "0");
				if ($service["service_low_flap_threshold"] != NULL) 
					$strTMP .= print_line("low_flap_threshold", $service["service_low_flap_threshold"]);
				if ($service["service_high_flap_threshold"] != NULL) 
					$strTMP .= print_line("high_flap_threshold", $service["service_high_flap_threshold"]);
				if ($service["service_flap_detection_enabled"] != 2) 
					$strTMP .= print_line("flap_detection_enabled", $service["service_flap_detection_enabled"] == 1 ? "1": "0");
				if ($service["service_process_perf_data"] != 2) 
					$strTMP .= print_line("process_perf_data", $service["service_process_perf_data"] == 1 ? "1": "0");
				if ($service["service_retain_status_information"] != 2) 
					$strTMP .= print_line("retain_status_information", $service["service_retain_status_information"] == 1 ? "1": "0");
				if ($service["service_retain_nonstatus_information"] != 2) 
					$strTMP .= print_line("retain_nonstatus_information", $service["service_retain_nonstatus_information"] == 1 ? "1": "0");
				
				/*
				 * Notifications
				 */
				if ($service["timeperiod_tp_id2"])
					$strTMP .= print_line("notification_period", $timeperiods[$service["timeperiod_tp_id2"]]);
				if ($service["service_notification_interval"] != NULL) 
					$strTMP .= print_line("notification_interval", $service["service_notification_interval"]);
				if ($service["service_notification_options"]) 
					$strTMP .= print_line("notification_options", $service["service_notification_options"]);
				if ($service["service_notifications_enabled"] != 2) 
					$strTMP .= print_line("notifications_enabled", $service["service_notifications_enabled"] == 1 ? "1": "0");
				
				# Contact Group Relation
				$contactGroup = array();
				$strTMPTemp = NULL;
				$DBRESULT2 =& $pearDB->query("SELECT cg.cg_id, cg.cg_name FROM contactgroup_service_relation csr, contactgroup cg WHERE csr.service_service_id = '".$service["service_id"]."' AND csr.contactgroup_cg_id = cg.cg_id ORDER BY `cg_name`");
				while ($contactGroup =& $DBRESULT2->fetchRow())	{
					$BP = false;
					array_key_exists($contactGroup["cg_id"], $gbArr[1]) ? $BP = true : NULL;
					
					if ($BP)
						$strTMPTemp != NULL ? $strTMPTemp .= ", ".$contactGroup["cg_name"] : $strTMPTemp = $contactGroup["cg_name"];
				}
				$DBRESULT2->free();
				if ($strTMPTemp) 
					$strTMP .= print_line("contact_groups", $strTMPTemp);
				unset($contactGroup);
				
				/*
				 * Contact Relation only for Nagios 3
				 */
				if ($oreon->user->get_version() >= 3) {
					$contact = array();
					$strTMPTemp = NULL;
					$DBRESULT2 =& $pearDB->query("SELECT c.contact_id, c.contact_name FROM contact_service_relation csr, contact c WHERE csr.service_service_id = '".$service["service_id"]."' AND csr.contact_id = c.contact_id ORDER BY `contact_name`");
					while ($contact =& $DBRESULT2->fetchRow())	{
						$BP = false;
						isset($gbArr[0][$contact["contact_id"]]) ? $BP = true : NULL;					
						if ($BP)
							$strTMPTemp != NULL ? $strTMPTemp .= ", ".$contact["contact_name"] : $strTMPTemp = $contact["contact_name"];
					}
					$DBRESULT2->free();
					if ($strTMPTemp) $strTMP .= print_line("contacts", $strTMPTemp);
					unset($contact);
				}
				
				
				if ($service["service_stalking_options"]) 
					$strTMP .= print_line("stalking_options", $service["service_stalking_options"]);
				if (!$service["service_register"]) 
					$strTMP .= print_line("register", "0");
				
				if (isset($service["service_register"]) && $service["service_register"] == 0){
					$DBRESULT_TEMP =& $pearDB->query("SELECT host_name FROM host, host_service_relation WHERE `service_service_id` = '".$service["service_id"]."' AND `host_id` = `host_host_id`");
					while($template_link =& $DBRESULT_TEMP->fetchRow())
						$strTMP .= print_line(";TEMPLATE-HOST-LINK", $template_link["host_name"]);
					unset($template_link);
					unset($DBRESULT_TEMP);
				}
				
				if ($oreon->user->get_version() >= 3) {
					/*
					 * On-demand macros
					 */
					$rq = "SELECT svc_macro_name, svc_macro_value FROM on_demand_macro_service WHERE `svc_svc_id`=" . $service['service_id'];
					$DBRESULT3 =& $pearDB->query($rq);
					while($od_macro = $DBRESULT3->fetchRow()) {
						$mac_name = str_replace("\$_SERVICE", "_", $od_macro['svc_macro_name']);
						$mac_name = str_replace("\$", "", $mac_name);
						$mac_value = $od_macro['svc_macro_value'];
						$strTMP .= print_line($mac_name, $mac_value);
					}
					$DBRESULT3->free();
				
					/*
					 * Extended Informations
					 */
					$svc_method = new CentreonService($pearDB);
					
					$DBRESULT3 =& $pearDB->query("SELECT * FROM extended_service_information esi WHERE esi.service_service_id = '".$service["service_id"]."'");
					$esi =& $DBRESULT3->fetchRow();
					if ($field = $esi["esi_notes"])
						$strTMP .= print_line("notes", $svc_method->replaceMacroInString($service["service_id"], $field));
					if ($field = $esi["esi_notes_url"])
						$strTMP .= print_line("notes_url", $svc_method->replaceMacroInString($service["service_id"], $field));
					if ($field = $esi["esi_action_url"])
						$strTMP .= print_line("action_url", $svc_method->replaceMacroInString($service["service_id"], $field));
					if ($field = getMyHostExtendedInfoImage($esi["service_id"], "esi_icon_image"))
						$strTMP .= print_line("icon_image", $svc_method->replaceMacroInString($service["service_id"], $field));
					if ($field = $esi["esi_icon_image_alt"])
						$strTMP .= print_line("icon_image_alt", $svc_method->replaceMacroInString($service["service_id"], $field));
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
	}
	unset($service);
	write_in_file($handle, html_entity_decode($str, ENT_QUOTES), $nagiosCFGPath.$tab['id']."/services.cfg");
	fclose($handle);
	$DBRESULT->free();
	unset($str);
	unset($i);
?>