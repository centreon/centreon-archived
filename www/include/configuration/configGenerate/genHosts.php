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
	 
	/*
	 * Get Template cache
	 */
	$templateCache = array();
	$DBRESULT =& $pearDB->query("SELECT host_name, host_host_id, `order` FROM `host_template_relation`, host WHERE host_template_relation.host_tpl_id = host.host_id ORDER BY `order`");
	while ($h =& $DBRESULT->fetchRow()) {
		if (!isset($templateCache[$h["host_host_id"]]))
			$templateCache[$h["host_host_id"]] = array();
		$templateCache[$h["host_host_id"]][] = $h["host_name"]; 
	}
	$DBRESULT->free();
	unset($h);
	
	/*
	 * Create HG Cache
	 */
	$hgCache = array();
	$DBRESULT2 =& $pearDB->query("SELECT hgr.hostgroup_hg_id, hgr.host_host_id, hg.hg_name FROM hostgroup_relation hgr, hostgroup hg WHERE hgr.hostgroup_hg_id = hg.hg_id");
	while ($hg =& $DBRESULT2->fetchRow()) {
		if (!isset($hgCache[$hg["host_host_id"]]))
			$hgCache[$hg["host_host_id"]] = array();
		$hgCache[$hg["host_host_id"]][$hg["hostgroup_hg_id"]] = $hg["hg_name"];
	}
	$DBRESULT->free();
	unset($hg);
	
	/*
	 * Init Table for filled HG
	 */
	$HGFilled = array();

	/*
	 * Create Contact Cache 
	 */
	$cctCache = array();
	$DBRESULT2 =& $pearDB->query("SELECT c.contact_id, c.contact_name, chr.host_host_id FROM contact_host_relation chr, contact c WHERE chr.contact_id = c.contact_id");
	while ($contact =& $DBRESULT2->fetchRow())	{
		if (!isset($cctCache[$contact["host_host_id"]]))
			$cctCache[$contact["host_host_id"]] = array();
		$cctCache[$contact["host_host_id"]][$contact["contact_id"]] = $contact["contact_name"];
	}
	$DBRESULT->free();
	unset($contact);		

	/*
	 * Create Cache for CG
	 */
	$cgCache = array();
	$DBRESULT2 =& $pearDB->query("SELECT cg.cg_id, cg.cg_name, chr.host_host_id FROM contactgroup_host_relation chr, contactgroup cg WHERE chr.contactgroup_cg_id = cg.cg_id ORDER BY `cg_name`");
	while ($cg =& $DBRESULT2->fetchRow())	{
		if (!isset($cgCache[$cg["host_host_id"]]))
			$cgCache[$cg["host_host_id"]] = array();
		$cgCache[$cg["host_host_id"]][$cg["cg_id"]] = $cg["cg_name"];
		
	}
	$DBRESULT->free();
	unset($cg);
	
	/*
	 * Build GMT cache
	 */
	$gmtCache = array();

	/*
	 * Init Buffer for hostgroup used in hosts
	 */
	global $hgHostGenerated;
	$hgHostGenerated = array();


	/*
	 * Create host object
	 */
	$host_method = new CentreonHost($pearDB);

	/******************************************************
	 * Host Generation
	 ******************************************************/
	$handle = create_file($nagiosCFGPath.$tab['id']."/hosts.cfg", $oreon->user->get_name());
	
	/*
	 * Create Host Lists
	 */
	$hostGenerated = array();
	$DBRESULT =& $pearDB->query("SELECT host.* " .
							"FROM host, ns_host_relation " .
							"WHERE host_activate = '1' " .
							"	AND host_register = '1' AND host.host_id = ns_host_relation.host_host_id " .
							"	AND ns_host_relation.nagios_server_id = '".$tab['id']."' " .
							"ORDER BY `host_register`, `host_name`");
	$i = 1;
	$str = NULL;
	$host = array();
	while ($host =& $DBRESULT->fetchRow())	{
		if (isset($host_instance[$host["host_id"]])) {			
			if (isset($gbArr[2][$host["host_id"]]))	{
				
				$hostGenerated[$host["host_id"]] = $host["host_name"];
											
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
				$gmtCache[$host["host_id"]] = $host["host_location_tp"];
				
				$str .= "define host{\n";
				if (!$host["host_register"] && $host["host_name"])	
					$str .= print_line("name", $host["host_name"]);
				else
					if ($host["host_name"]) $str .= print_line("host_name", $host["host_name"]);
				
				/*
				 *  Multi Templates
				 */
				if (isset($templateCache[$host["host_id"]])) {
					$tpl_str = "";	
					foreach ($templateCache[$host["host_id"]] as $host_template) {
						if ($tpl_str != "") {
							$tpl_str .= ",";
						}
						$tpl_str .= $host_template;
					}
				}
				if (isset($tpl_str) && $tpl_str != "") 
					$str .= print_line("use", $tpl_str);
				unset($tpl_str);
				
				if ($host["host_alias"])
					$str .= print_line("alias", $host["host_alias"]);
				if ($host["host_address"])	
					$str .= print_line("address", $host["host_address"]);
				/*
                 * Write Host_id
                 */
                $str .= print_line("_HOST_ID", $host["host_id"]);
                
				if ($host["host_register"] == 1 && $host["host_location"] != "")
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
				while ($hostParent =& $DBRESULT2->fetchRow())	{
					$DBRESULT3 =& $pearDB->query("SELECT * FROM ns_host_relation WHERE host_host_id = '".$hostParent["host_id"]."' AND nagios_server_id = '".$tab['id']."'");
					if (verifyIfMustBeGenerated($hostParent["host_id"], $gbArr[2], $ret) && $DBRESULT3->numRows()) {
						$strTemp != NULL ? $strTemp .= ", ".$hostParent["host_name"] : $strTemp = $hostParent["host_name"];
					}
				}
				$DBRESULT2->free();
				
				if ($strTemp) 
					$str .= print_line("parents", $strTemp);
				unset($hostParent);
				unset($strTemp);

				/*
				 * Hostgroups relation
				 */
				$strTemp = "";
				if (isset($hgCache[$host["host_id"]])) {
					foreach ($hgCache[$host["host_id"]] as $hgs) {
						if ($strTemp != "") {
							$strTemp .= ",";
						}
						$strTemp .= $hgs;
						$HGFilled[$hgs] = $hgs;
						$hgHostGenerated[$hgs] = 1;
					}					
				}
				if ($strTemp) 
					$str .= print_line("hostgroups", $strTemp);
				unset($strTemp);
				
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
				 * Nagios V2 & V3 : contactGroups relation
				 */
				$strTemp = "";
				if (isset($cgCache[$host["host_id"]])) {
					foreach ($cgCache[$host["host_id"]] as $cg) {
						if ($strTemp != "")
							$strTemp .= ",";
						$strTemp .= $cg;
					}
					if ($strTemp) 
						$str .= print_line("contact_groups", $strTemp);
					unset($strTemp);
				}
				
				/*
				 * Nagios V3 : contacts relation
				 */			
				$strTemp = "";
				if (isset($cctCache[$host["host_id"]])) {
					foreach ($cctCache[$host["host_id"]] as $contact) {
						if ($strTemp != "")
							$strTemp .= ",";
						$strTemp .= $contact;
					}
					if ($strTemp) 
						$str .= print_line("contacts", $strTemp);
					unset($strTemp);
				}
				
				if ($host["host_notification_interval"] != NULL) 
					$str .= print_line("notification_interval", $host["host_notification_interval"]);
				if ($host["host_first_notification_delay"] != NULL) 
					$str .= print_line("first_notification_delay", $host["host_first_notification_delay"]);
				
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

				$rq = "SELECT `host_macro_name`, `host_macro_value` FROM `on_demand_macro_host` WHERE `host_host_id` = '" . $host['host_id']."'";
				$DBRESULT3 =& $pearDB->query($rq);
				while ($od_macro =& $DBRESULT3->fetchRow()) {
					$mac_name = str_replace("\$_HOST", "_", $od_macro['host_macro_name']);
					$mac_name = str_replace("\$", "", $mac_name);
					$mac_value = $od_macro['host_macro_value'];
					$str .= print_line($mac_name, $mac_value);
				}

				/*
				 * Extended Informations
				 */
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
        			$str .= print_line("icon_image_alt", $host_method->replaceMacroInString($host["host_id"], $ehi["ehi_icon_image_alt"]));
				if ($ehi["ehi_vrml_image"])
        			$str .= print_line("vrml_image", $host_method->replaceMacroInString($host["host_id"], getMyHostExtendedInfoImage($host["host_id"], "ehi_vrml_image", 1)));
				if ($ehi["ehi_statusmap_image"])
        			$str .= print_line("statusmap_image", $host_method->replaceMacroInString($host["host_id"], getMyHostExtendedInfoImage($host["host_id"], "ehi_statusmap_image", 1)));
				if ($ehi["ehi_2d_coords"])
        			$str .= print_line("2d_coords", $host_method->replaceMacroInString($host["host_id"], $ehi["ehi_2d_coords"]));
				if ($ehi["ehi_3d_coords"])
        			$str .= print_line("3d_coords", $host_method->replaceMacroInString($host["host_id"], $ehi["ehi_3d_coords"]));
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