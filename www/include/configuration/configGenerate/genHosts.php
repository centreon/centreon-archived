<?php
/**
Oreon is developped with GPL Licence 2.0 :
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
	
	$handle = create_file($nagiosCFGPath.$tab['id']."/hosts.cfg", $oreon->user->get_name());
	$DBRESULT =& $pearDB->query("SELECT * FROM host ORDER BY `host_register`, `host_name`");
	if (PEAR::isError($DBRESULT))
		print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";
	$host = array();
	$i = 1;
	$str = NULL;
	while($DBRESULT->fetchInto($host))	{
		if (isHostOnThisInstance($host["host_id"], $tab['id']) || $host["host_register"] == 0) {
			$BP = false;
			if ($ret["level"]["level"] == 1)
				array_key_exists($host["host_id"], $gbArr[2]) ? $BP = true : NULL;
			else if ($ret["level"]["level"] == 2)
				array_key_exists($host["host_id"], $gbArr[2]) ? $BP = true : NULL;
			else if ($ret["level"]["level"] == 3)
				$BP = true;
			if (!$host["host_register"])
				$BP = true;
			if ($BP)	{
				$ret["comment"]["comment"] ? ($str .= "# '" . $host["host_name"]."' host definition ".$i."\n") : NULL;
				if ($ret["comment"]["comment"] && $host["host_comment"])	{
					$comment = array();
					$comment = explode("\n", $host["host_comment"]);
					foreach ($comment as $cmt)
						$str .= "# ".$cmt."\n";
				}
				$str .= "define host{\n";
				if (!$host["host_register"] && $host["host_name"])	
					$str .= print_line("name", $host["host_name"]);
				else
					if ($host["host_name"]) $str .= print_line("host_name", $host["host_name"]);
				
				/*
				 * Get Template Model Relation
				 */
				
				if ($host["host_template_model_htm_id"]) {
					$hostTemplate = array();
					$DBRESULT2 =& $pearDB->query("SELECT host.host_name FROM host WHERE host.host_id = '".$host["host_template_model_htm_id"]."'");
					if (PEAR::isError($DBRESULT2))
						print "DB Error : ".$DBRESULT2->getDebugInfo()."<br>";
					while($DBRESULT2->fetchInto($hostTemplate))
						$str .= print_line("use", $hostTemplate["host_name"]);
					$DBRESULT2->free();
					unset($hostTemplate);		
				}
				
				if ($host["host_alias"]) $str .= print_line("alias", $host["host_alias"]);
				if ($host["host_address"]) $str .= print_line("address", $host["host_address"]);
				
				/* 
				 * Get Parents List for this host
				 */

				$hostParent = array();
				$strTemp = NULL;
				$DBRESULT2 =& $pearDB->query("SELECT host.host_id, host.host_name FROM host_hostparent_relation hhr, host WHERE hhr.host_host_id = '".$host["host_id"]."' AND hhr.host_parent_hp_id = host.host_id ORDER BY `host_name`");
				if (PEAR::isError($DBRESULT2))
					print "DB Error : ".$DBRESULT2->getDebugInfo()."<br>";
				while($DBRESULT2->fetchInto($hostParent))	{
					if (verifyIfMustBeGenerated($host["host_id"], $gbArr[2], $ret))
						$strTemp != NULL ? $strTemp .= ", ".$hostParent["host_name"] : $strTemp = $hostParent["host_name"];
				}
				$DBRESULT2->free();
				if ($strTemp) $str .= print_line("parents", $strTemp);
				unset($hostParent);
				unset($strTemp);

				// Nagios V2 : Hostgroups relation
				if ($oreon->user->get_version() == 2)	{
					$hostGroup = array();
					$strTemp = NULL;
					$DBRESULT2 =& $pearDB->query("SELECT hg.hg_id, hg.hg_name FROM hostgroup_relation hgr, hostgroup hg WHERE hgr.host_host_id = '".$host["host_id"]."' AND hgr.hostgroup_hg_id = hg.hg_id ORDER BY `hg_name`");
					if (PEAR::isError($DBRESULT2))
						print "DB Error : ".$DBRESULT2->getDebugInfo()."<br>";
					while($DBRESULT2->fetchInto($hostGroup))	{
						$BP = false;
						if ($ret["level"]["level"] == 1)
							array_key_exists($hostGroup["hg_id"], $gbArr[3]) ? $BP = true : NULL;
						else if ($ret["level"]["level"] == 2)
							array_key_exists($hostGroup["hg_id"], $gbArr[3]) ? $BP = true : NULL;
						else if ($ret["level"]["level"] == 3)
							$BP = true;
						if ($BP)
							$strTemp != NULL ? $strTemp .= ", ".$hostGroup["hg_name"] : $strTemp = $hostGroup["hg_name"];
					}
					$DBRESULT2->free();
					unset($hostGroup);
					if ($strTemp) $str .= print_line("hostgroups", $strTemp);
					unset($strTemp);
				}
				
				/*
				 * Check Command
				 */
				 
				$command = array();
				$DBRESULT2 =& $pearDB->query("SELECT cmd.command_name FROM command cmd WHERE cmd.command_id = '".$host["command_command_id"]."' LIMIT 1");
				if (PEAR::isError($DBRESULT2))
					print "DB Error : ".$DBRESULT2->getDebugInfo()."<br>";
				
				$host["command_command_id_arg2"] = removeSpecialChar($host["command_command_id_arg2"]);
				
				while($DBRESULT2->fetchInto($command))
					$str .= print_line("check_command", $command["command_name"].$host["command_command_id_arg1"]);
				$DBRESULT2->free();
				unset($command);
				//
				if ($host["host_max_check_attempts"] != NULL) $str .= print_line("max_check_attempts", $host["host_max_check_attempts"]);
				if ($host["host_check_interval"] != NULL) $str .= print_line("check_interval", $host["host_check_interval"]);
				if ($oreon->user->get_version() == 1)
					if ($host["host_checks_enabled"] != 2) $str .= print_line("checks_enabled", $host["host_checks_enabled"] == 1 ? "1" : "0");
				if ($oreon->user->get_version() == 2)	{
					if ($host["host_active_checks_enabled"] != 2) $str .= print_line("active_checks_enabled", $host["host_active_checks_enabled"] == 1 ? "1": "0");
					if ($host["host_passive_checks_enabled"] != 2) $str .= print_line("passive_checks_enabled", $host["host_passive_checks_enabled"] == 1 ? "1": "0");
					//Check Period
					$timePeriod = array();
					$DBRESULT2 =& $pearDB->query("SELECT tp.tp_name FROM timeperiod tp WHERE tp.tp_id = '".$host["timeperiod_tp_id"]."' LIMIT 1");
					if (PEAR::isError($DBRESULT2))
						print "DB Error : ".$DBRESULT2->getDebugInfo()."<br>";
					while($DBRESULT2->fetchInto($timePeriod))
						$str .= print_line("check_period", $timePeriod["tp_name"]);
					$DBRESULT2->free();
					unset($timePeriod);
					//
					if ($host["host_obsess_over_host"] != 2) $str .= print_line("obsess_over_host", $host["host_obsess_over_host"] == 1 ? "1": "0");
					if ($host["host_check_freshness"] != 2) $str .= print_line("check_freshness", $host["host_check_freshness"] == 1 ? "1": "0");
					if ($host["host_freshness_threshold"]) $str .= print_line("freshness_threshold", $host["host_freshness_threshold"]);
				}
				//Event_handler
				$command = array();
				$DBRESULT2 =& $pearDB->query("SELECT cmd.command_name FROM command cmd WHERE cmd.command_id = '".$host["command_command_id2"]."' LIMIT 1");
				if (PEAR::isError($DBRESULT2))
					print "DB Error : ".$DBRESULT2->getDebugInfo()."<br>";
				$host["command_command_id_arg2"] = str_replace('#BR#', "\\n", $host["command_command_id_arg2"]);
				$host["command_command_id_arg2"] = str_replace('#T#', "\\t", $host["command_command_id_arg2"]);
				$host["command_command_id_arg2"] = str_replace('#R#', "\\r", $host["command_command_id_arg2"]);
				$host["command_command_id_arg2"] = str_replace('#S#', "/", $host["command_command_id_arg2"]);
				$host["command_command_id_arg2"] = str_replace('#BS#', "\\", $host["command_command_id_arg2"]);
					
				while($DBRESULT2->fetchInto($command))
					$str .= print_line("event_handler", $command["command_name"].$host["command_command_id_arg2"]);
				$DBRESULT2->free();
				unset($command);
				//
				if ($host["host_event_handler_enabled"] != 2) $str .= print_line("event_handler_enabled", $host["host_event_handler_enabled"] == 1 ? "1": "0");
				if ($host["host_low_flap_threshold"]) $str .= print_line("low_flap_threshold", $host["host_low_flap_threshold"]);
				if ($host["host_high_flap_threshold"]) $str .= print_line("high_flap_threshold", $host["host_high_flap_threshold"]);
				if ($host["host_flap_detection_enabled"] != 2) $str .= print_line("flap_detection_enabled", $host["host_flap_detection_enabled"] == 1 ? "1": "0");
				if ($host["host_process_perf_data"] != 2) $str .= print_line("process_perf_data", $host["host_process_perf_data"] == 1 ? "1": "0");
				if ($host["host_retain_status_information"] != 2) $str .= print_line("retain_status_information", $host["host_retain_status_information"] == 1 ? "1": "0");
				if ($host["host_retain_nonstatus_information"] != 2) $str .= print_line("retain_nonstatus_information", $host["host_retain_nonstatus_information"] == 1 ? "1": "0");
				//Nagios V2 : contactGroups relation
				if ($oreon->user->get_version() == 2)	{
					$contactGroup = array();
					$strTemp = NULL;
					$DBRESULT2 =& $pearDB->query("SELECT cg.cg_id, cg.cg_name FROM contactgroup_host_relation chr, contactgroup cg WHERE chr.host_host_id = '".$host["host_id"]."' AND chr.contactgroup_cg_id = cg.cg_id ORDER BY `cg_name`");
					if (PEAR::isError($DBRESULT2))
						print "DB Error : ".$DBRESULT2->getDebugInfo()."<br>";
					while($DBRESULT2->fetchInto($contactGroup))	{				
						$BP = false;
						if ($ret["level"]["level"] == 1)
							array_key_exists($contactGroup["cg_id"], $gbArr[1]) ? $BP = true : NULL;
						else if ($ret["level"]["level"] == 2)
							array_key_exists($contactGroup["cg_id"], $gbArr[1]) ? $BP = true : NULL;
						else if ($ret["level"]["level"] == 3)
							$BP = true;
						if ($BP)
							$strTemp != NULL ? $strTemp .= ", ".$contactGroup["cg_name"] : $strTemp = $contactGroup["cg_name"];
					}
					$DBRESULT2->free();
					unset($contactGroup);
					if ($strTemp) $str .= print_line("contact_groups", $strTemp);
					unset($strTemp);
				}
				//
				if ($host["host_notification_interval"] != NULL) $str .= print_line("notification_interval", $host["host_notification_interval"]);
				// Timeperiod name
				$timePeriod = array();
				$DBRESULT2 =& $pearDB->query("SELECT tp.tp_name FROM timeperiod tp WHERE tp.tp_id = '".$host["timeperiod_tp_id2"]."' LIMIT 1");
				if (PEAR::isError($DBRESULT2))
					print "DB Error : ".$DBRESULT2->getDebugInfo()."<br>";
				while($DBRESULT2->fetchInto($timePeriod))
					$str .= print_line("notification_period", $timePeriod["tp_name"]);
				$DBRESULT2->free();
				unset($timePeriod);
				//
				if ($host["host_notification_options"]) $str .= print_line("notification_options", $host["host_notification_options"]);
				if ($host["host_notifications_enabled"] != 2) $str .= print_line("notifications_enabled", $host["host_notifications_enabled"] == 1 ? "1": "0");
				if ($host["host_stalking_options"]) $str .= print_line("stalking_options", $host["host_stalking_options"]);
				if (!$host["host_register"]) $str .= print_line("register", "0");
				$str .= "}\n\n";
				$i++;
			}
		}
		unset($host);
	}
	write_in_file($handle, html_entity_decode($str, ENT_QUOTES), $nagiosCFGPath.$tab['id']."/hosts.cfg");
	fclose($handle);
	$DBRESULT->free();
	unset($str);
	unset($i);
?>