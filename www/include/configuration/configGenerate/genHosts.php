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
	require_once ($centreon_path . "/www/class/centreonHost.class.php");
	
	/*
	 * Create table for host / instance list.
	 */
	
	$host_instance = array();
	$DBRESULT =& $pearDB->query("SELECT * FROM `ns_host_relation` WHERE `nagios_server_id` = '".$tab['id']."'");
	while ($datas =& $DBRESULT->fetchRow())
		$host_instance[$datas["host_host_id"]] = $datas["host_host_id"];
	$DBRESULT->free();
				
	/*
	 * Get Command List
	 */
	$DBRESULT =& $pearDB->query('SELECT command_id, command_name FROM `command` ORDER BY `command_type`,`command_name`');
	$commands = array();
	while ($command =& $DBRESULT->fetchRow())	{
		$commands[$command["command_id"]] = $command["command_name"];
	}
	unset($command);
	 
	
	$handle = create_file($nagiosCFGPath.$tab['id']."/hosts.cfg", $oreon->user->get_name());
	$DBRESULT =& $pearDB->query("SELECT * " .
								"FROM host, ns_host_relation " .
								"WHERE host.host_id = ns_host_relation.host_host_id " .
								"	AND ns_host_relation.nagios_server_id = '".$tab['id']."' " .
								"	AND host_activate = '1' ORDER BY `host_register`, `host_name`");
	$host = array();
	$i = 1;
	$str = NULL;
	while ($host =& $DBRESULT->fetchRow())	{
		if (isset($host_instance[$host["host_id"]]) || $host["host_register"] == 0) {			
			if (isset($gbArr[2][$host["host_id"]]) || !$host["host_register"])	{								
				$ret["comment"] ? ($str .= "# '" . $host["host_name"]."' host definition ".$i."\n") : NULL;
				if ($ret["comment"] && $host["host_comment"])	{
					$comment = array();
					$comment = explode("\n", $host["host_comment"]);
					foreach ($comment as $cmt)
						$str .= "# ".$cmt."\n";
				}
				/*
				 * Adjust host_location and time period name
				 */
				 if ($host["host_location"] > 0)
				 	$host["host_location_tp"] = "-".$host["host_location"];
				 else if ($host["host_location"] < 0)
				 	$host["host_location_tp"] = abs($host["host_location"]);
				 else
					 $host["host_location_tp"] = "";
				
				$str .= "define host{\n";
				if (!$host["host_register"] && $host["host_name"])	
					$str .= print_line("name", $host["host_name"]);
				else
					if ($host["host_name"]) $str .= print_line("host_name", $host["host_name"]);
				
				/*
				 *  For Nagios 3 ::: Multi Templates
				 */
				if ($oreon->user->get_version() >= 3) {
					$rq = "SELECT host_tpl_id FROM `host_template_relation` WHERE host_host_id=" . $host["host_id"] . " ORDER BY `order`";
					$DBRESULT2 =& $pearDB->query($rq);
					$tpl_str = "";
					$first_on_list = 1;
					while ($tpl_id =& $DBRESULT2->fetchRow()) {
						$rq = "SELECT host_name FROM `host` WHERE host_id=" . $tpl_id["host_tpl_id"];
						$DBRESULT3 =& $pearDB->query($rq);
						while ($tpl_name =& $DBRESULT3->fetchRow()) { 
							if ($first_on_list) {
								$first_on_list = 0;
								$tpl_str .= $tpl_name["host_name"];
							} else
								$tpl_str .= "," . $tpl_name["host_name"];							
						}	
					}
					if ($DBRESULT2->numRows())
						$str .= print_line("use", $tpl_str);
					$DBRESULT2->free();
				} else if ($host["host_template_model_htm_id"]) {			
					/*
					 *  For Nagios 1 & 2
					 */
					$hostTemplate = array();
					$DBRESULT2 =& $pearDB->query("SELECT host.host_name FROM host WHERE host.host_id = '".$host["host_template_model_htm_id"]."'");
					while($hostTemplate = $DBRESULT2->fetchRow())
						$str .= print_line("use", $hostTemplate["host_name"]);
					$DBRESULT2->free();
					unset($hostTemplate);		
				}
				
				if ($host["host_alias"])
					$str .= print_line("alias", $host["host_alias"]);
				if ($host["host_address"])	
					$str .= print_line("address", $host["host_address"]);
				
				if ($host["host_register"] == 1)
					$str .= print_line("#location", $host["host_location"]);
				
				if ($host["host_snmp_community"])
					$str .= print_line("_SNMPCOMMUNITY", $host["host_snmp_community"]);
				if ($host["host_snmp_version"])
					$str .= print_line("_SNMPVERSION", $host["host_snmp_version"]);
				
				/* 
				 * Get Parents List for this host
				 */

				$hostParent = array();
				$strTemp = NULL;
				$DBRESULT2 =& $pearDB->query("SELECT host.host_id, host.host_name FROM host_hostparent_relation hhr, host WHERE hhr.host_host_id = '".$host["host_id"]."' AND hhr.host_parent_hp_id = host.host_id ORDER BY `host_name`");
				while($hostParent = $DBRESULT2->fetchRow())	{
					$DBRESULT3 =& $pearDB->query("SELECT * FROM ns_host_relation WHERE host_host_id = '".$hostParent["host_id"]."' AND nagios_server_id = '".$tab['id']."'");
					if (verifyIfMustBeGenerated($host["host_id"], $gbArr[2], $ret) && $DBRESULT3->numRows())
						$strTemp != NULL ? $strTemp .= ", ".$hostParent["host_name"] : $strTemp = $hostParent["host_name"];
				}
				$DBRESULT2->free();
				
				if ($strTemp) 
					$str .= print_line("parents", $strTemp);
				unset($hostParent);
				unset($strTemp);

				/*
				 * Hostgroups relation
				 */
				if ($oreon->user->get_version() >= 2)	{
					$hostGroup = array();
					$strTemp = NULL;
					$DBRESULT2 =& $pearDB->query("SELECT hg.hg_id, hg.hg_name FROM hostgroup_relation hgr, hostgroup hg WHERE hgr.host_host_id = '".$host["host_id"]."' AND hgr.hostgroup_hg_id = hg.hg_id ORDER BY `hg_name`");
					while ($hostGroup =& $DBRESULT2->fetchRow())	{
						if (isset($gbArr[3][$hostGroup["hg_id"]]))
							$strTemp != NULL ? $strTemp .= ", ".$hostGroup["hg_name"] : $strTemp = $hostGroup["hg_name"];
					}
					$DBRESULT2->free();
					unset($hostGroup);
					if ($strTemp) 
						$str .= print_line("hostgroups", $strTemp);
					unset($strTemp);
				}
				
				/*
				 * Check Command
				 */
				if (isset($host["command_command_id"]) && $host["command_command_id"]) {
					$host["command_command_id_arg1"] = removeSpecialChar($host["command_command_id_arg1"]);
					$str .= print_line("check_command", $commands[$host["command_command_id"]].$host["command_command_id_arg1"]);
				}
								
				if ($host["host_max_check_attempts"] != NULL) 	
					$str .= print_line("max_check_attempts", $host["host_max_check_attempts"]);
				if ($host["host_check_interval"] != NULL) 
					$str .= print_line("check_interval", $host["host_check_interval"]);
				if ($host["host_active_checks_enabled"] != 2) 
					$str .= print_line("active_checks_enabled", $host["host_active_checks_enabled"] == 1 ? "1": "0");
				if ($host["host_passive_checks_enabled"] != 2) 
					$str .= print_line("passive_checks_enabled", $host["host_passive_checks_enabled"] == 1 ? "1": "0");
				
				/*
				 * Check Period
				 */
				 
				 
				if ($host["host_register"] == 1) {
					if ((!isset($host["timeperiod_tp_id"]) && $host["host_location"] != 0)) {
						$host["timeperiod_tp_id"] = getMyHostField($host["host_id"], "timeperiod_tp_id");
					}
				} 
				
				if ($host["timeperiod_tp_id"])
					$str .= print_line("check_period", $timeperiods[$host["timeperiod_tp_id"]].($oreon->CentreonGMT->used() == 1 ? "_GMT".$host["host_location_tp"] : ""));

				if ($host["host_obsess_over_host"] != 2) 
					$str .= print_line("obsess_over_host", $host["host_obsess_over_host"] == 1 ? "1": "0");
				if ($host["host_check_freshness"] != 2) 
					$str .= print_line("check_freshness", $host["host_check_freshness"] == 1 ? "1": "0");
				if ($host["host_freshness_threshold"]) 
					$str .= print_line("freshness_threshold", $host["host_freshness_threshold"]);

				/*
				 * Event_handler
				 */
				$host["command_command_id_arg2"] = removeSpecialChar($host["command_command_id_arg2"]);
				if ($host["command_command_id2"])
					$str .= print_line("event_handler", $commands[$host["command_command_id2"]].$host["command_command_id_arg2"]);

				
				if ($host["host_event_handler_enabled"] != 2) 
					$str .= print_line("event_handler_enabled", $host["host_event_handler_enabled"] == 1 ? "1": "0");
				if ($host["host_low_flap_threshold"]) 
					$str .= print_line("low_flap_threshold", $host["host_low_flap_threshold"]);
				if ($host["host_high_flap_threshold"]) 
					$str .= print_line("high_flap_threshold", $host["host_high_flap_threshold"]);
				if ($host["host_flap_detection_enabled"] != 2) 
					$str .= print_line("flap_detection_enabled", $host["host_flap_detection_enabled"] == 1 ? "1": "0");
				if ($host["host_process_perf_data"] != 2) 
					$str .= print_line("process_perf_data", $host["host_process_perf_data"] == 1 ? "1": "0");
				if ($host["host_retain_status_information"] != 2) 
					$str .= print_line("retain_status_information", $host["host_retain_status_information"] == 1 ? "1": "0");
				if ($host["host_retain_nonstatus_information"] != 2) 
					$str .= print_line("retain_nonstatus_information", $host["host_retain_nonstatus_information"] == 1 ? "1": "0");
				
				/*
				 * Nagios V2 : contactGroups relation
				 */
				
				$contactGroup = array();
				$strTemp = NULL;
				$DBRESULT2 =& $pearDB->query("SELECT cg.cg_id, cg.cg_name FROM contactgroup_host_relation chr, contactgroup cg WHERE chr.host_host_id = '".$host["host_id"]."' AND chr.contactgroup_cg_id = cg.cg_id ORDER BY `cg_name`");
				while ($contactGroup =& $DBRESULT2->fetchRow())	{				
					if (isset($gbArr[1][$contactGroup["cg_id"]]))
						$strTemp != NULL ? $strTemp .= ", ".$contactGroup["cg_name"] : $strTemp = $contactGroup["cg_name"];
				}
				$DBRESULT2->free();
				unset($contactGroup);
				if ($strTemp) 
					$str .= print_line("contact_groups", $strTemp);
				unset($strTemp);
				
				/*
				 * Nagios V3 : contacts relation
				 */
				if ($oreon->user->get_version() >= 3)	{
					$contact = array();
					$strTemp = NULL;
					$DBRESULT2 =& $pearDB->query("SELECT c.contact_id, c.contact_name FROM contact_host_relation chr, contact c WHERE chr.host_host_id = '".$host["host_id"]."' AND chr.contact_id = c.contact_id ORDER BY `contact_name`");
					while ($contact =& $DBRESULT2->fetchRow())	{				
						if (isset($gbArr[0][$contact["contact_id"]]))
							$strTemp != NULL ? $strTemp .= ", ".$contact["contact_name"] : $strTemp = $contact["contact_name"];
					}
					$DBRESULT2->free();
					unset($contact);
					if ($strTemp) 
						$str .= print_line("contacts", $strTemp);
					unset($strTemp);
				}
				
				if ($host["host_notification_interval"] != NULL) 
					$str .= print_line("notification_interval", $host["host_notification_interval"]);
				
				/*
				 * Timeperiod name
				 */
				if ($host["host_register"] == 1) {
					if ((!isset($host["timeperiod_tp_id2"]) && $host["host_location"] != 0)) {
						$host["timeperiod_tp_id2"] = getMyHostField($host["host_id"], "timeperiod_tp_id2");
					}
				} 
				if ($host["timeperiod_tp_id2"])
					$str .= print_line("notification_period", $timeperiods[$host["timeperiod_tp_id2"]].($oreon->CentreonGMT->used() == 1 ? "_GMT".$host["host_location_tp"] : ""));
			
				if ($host["host_notification_options"]) 
					$str .= print_line("notification_options", $host["host_notification_options"]);
				if ($host["host_notifications_enabled"] != 2) 
					$str .= print_line("notifications_enabled", $host["host_notifications_enabled"] == 1 ? "1": "0");
				if ($host["host_stalking_options"]) 
					$str .= print_line("stalking_options", $host["host_stalking_options"]);
				if (!$host["host_register"]) 
					$str .= print_line("register", "0");
				
				/*
				 * On-demand macros
				 */
				if ($oreon->user->get_version() >= 3) {
					$rq = "SELECT `host_macro_name`, `host_macro_value` FROM `on_demand_macro_host` WHERE `host_host_id` = '" . $host['host_id']."'";
					$DBRESULT3 =& $pearDB->query($rq);
					while ($od_macro =& $DBRESULT3->fetchRow()) {
						$mac_name = str_replace("\$_HOST", "_", $od_macro['host_macro_name']);
						$mac_name = str_replace("\$", "", $mac_name);
						$mac_value = $od_macro['host_macro_value'];
						$str .= print_line($mac_name, $mac_value);
					}
				}
				
				$host_method = new CentreonHost($pearDB);
				
				if ($oreon->user->get_version() >= 3)	{
					$DBRESULT2 =& $pearDB->query("SELECT * FROM extended_host_information ehi WHERE ehi.host_host_id = '".$host["host_id"]."'");
					$ehi =& $DBRESULT2->fetchRow();
					if ($ehi["ehi_notes"])
						$str .= print_line("notes", $host_method->replaceMacroInString($host["host_id"], $ehi["ehi_notes"]));
					if ($ehi["ehi_notes_url"])
	        			$str .= print_line("notes_url", $host_method->replaceMacroInString($host["host_id"], $ehi["ehi_notes_url"]));
					if ($ehi["ehi_action_url"])
	        			$str .= print_line("action_url", $host_method->replaceMacroInString($host["host_id"], $ehi["ehi_action_url"]));
					if ($ehi["ehi_icon_image"])
	        			$str .= print_line("icon_image", $host_method->replaceMacroInString($host["host_id"], getMyHostExtendedInfoImage($host["host_id"], "ehi_icon_image", 1)));
					if ($ehi["ehi_icon_image_alt"])
	        			$str .= print_line("icon_image", $host_method->replaceMacroInString($host["host_id"], $ehi["ehi_icon_image_alt"]));
					if ($ehi["ehi_vrml_image"])
	        			$str .= print_line("vrml_image", $host_method->replaceMacroInString($host["host_id"], getMyHostExtendedInfoImage($host["host_id"], "ehi_vrml_image", 1)));
					if ($ehi["ehi_statusmap_image"])
	        			$str .= print_line("statusmap_image", $host_method->replaceMacroInString($host["host_id"], getMyHostExtendedInfoImage($host["host_id"], "ehi_statusmap_image", 1)));
					if ($ehi["ehi_2d_coords"])
	        			$str .= print_line("2d_coords", $host_method->replaceMacroInString($host["host_id"], $ehi["ehi_2d_coords"]));
					if ($ehi["ehi_3d_coords"])
	        			$str .= print_line("3d_coords", $host_method->replaceMacroInString($host["host_id"], $ehi["ehi_3d_coords"]));
					}
					$DBRESULT2->free();
				}
				$str .= "}\n\n";
				$i++;
			}
		}
				
		unset($host);
	
	write_in_file($handle, html_entity_decode($str, ENT_QUOTES), $nagiosCFGPath.$tab['id']."/hosts.cfg");
	fclose($handle);
	$DBRESULT->free();
	unset($str);
	unset($i);
?>