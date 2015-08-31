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

	if (!isset($oreon))
		exit();

	if (!is_dir($nagiosCFGPath.$tab['id']."/"))
		mkdir($nagiosCFGPath.$tab['id']."/");

	$handle = create_file($nagiosCFGPath.$tab['id']."/contacts.cfg", $oreon->user->get_name());
	$DBRESULT = $pearDB->query("SELECT * FROM contact WHERE `contact_register` = '1' ORDER BY `contact_name`");
	$contact = array();
	$i = 1;
	$str = NULL;
	$queryGetUserAlias = 'SELECT contact_name FROM contact WHERE contact_id = %d';
	$userCache = array();
	while ($contact = $DBRESULT->fetchRow())	{
		if (isset($gbArr[0][$contact["contact_id"]]))	{
			$ret["comment"] ? ($str .= "# '".$contact["contact_name"]."' contact definition ".$i."\n") : NULL;
			if ($ret["comment"] && $contact["contact_comment"])	{
				$comment = array();
				$comment = explode("\n", $contact["contact_comment"]);
				foreach ($comment as $cmt) {
					$str .= "# ".$cmt."\n";
				}
			}

			/*
			 * Start Object
			 */
			$str .= "define contact{\n";
			if ($contact["contact_name"]) {
				$str .= print_line("contact_name", $contact["contact_name"]);
			}
			if ($contact["contact_alias"]) {
			    $str .= print_line("alias", $contact["contact_alias"]);
			}

			/*
			 * Contact Groups in Contact
			 */
			$contactGroup = array();
			$strTemp = NULL;
			$DBRESULT2 = $pearDB->query("SELECT cg.cg_name, cg.cg_id FROM contactgroup_contact_relation ccr, contactgroup cg WHERE ccr.contact_contact_id = '".$contact["contact_id"]."' AND ccr.contactgroup_cg_id = cg.cg_id ORDER BY `cg_name`");
			while ($contactGroup = $DBRESULT2->fetchRow())	{
				if (isset($gbArr[1][$contactGroup["cg_id"]]))
					$strTemp != NULL ? $strTemp .= ", ".str_replace(" ", "_", $contactGroup["cg_name"]) : $strTemp = str_replace(" ", "_", $contactGroup["cg_name"]);
			}
			$DBRESULT2->free();
			if ($strTemp)
				$str .= print_line("contactgroups", $strTemp);
			unset($contactGroup);
			unset($strTemp);

			/*
			 * Timeperiod for host & service
			 */
			$timeperiod = array();
			$DBRESULT2 = $pearDB->query("SELECT cct.timeperiod_tp_id AS cctTP1, cct.timeperiod_tp_id2 AS cctTP2, tp.tp_id, tp.tp_name FROM contact cct, timeperiod tp WHERE cct.contact_id = '".$contact["contact_id"]."' AND (tp.tp_id = cct.timeperiod_tp_id OR tp.tp_id = cct.timeperiod_tp_id2) ORDER BY `cctTP1`");
			while ($timeperiod = $DBRESULT2->fetchRow())	{
				$timeperiod["cctTP1"] == $timeperiod["tp_id"] ? $str .= print_line("host_notification_period", $timeperiod["tp_name"]) : NULL;
				$timeperiod["cctTP2"] == $timeperiod["tp_id"] ? $str .= print_line("service_notification_period", $timeperiod["tp_name"]) : NULL;
			}
			$DBRESULT2->free();

			unset($timeperiod);
			if ($contact["contact_host_notification_options"])
				$str .= print_line("host_notification_options", $contact["contact_host_notification_options"]);
			if ($contact["contact_service_notification_options"])
				$str .= print_line("service_notification_options", $contact["contact_service_notification_options"]);

			/*
			 * Host & Service notification command
			 */
			$command = array();
			$strTemp = NULL;
			$DBRESULT2 = $pearDB->query("SELECT cmd.command_name FROM contact_hostcommands_relation chr, command cmd WHERE chr.contact_contact_id = '".$contact["contact_id"]."' AND chr.command_command_id = cmd.command_id ORDER BY `command_name`");
			while ($command = $DBRESULT2->fetchRow())
				$strTemp != NULL ? $strTemp .= ", ".$command["command_name"] : $strTemp = $command["command_name"];
			$DBRESULT2->free();
			if ($strTemp) $str .= print_line("host_notification_commands", $strTemp);
			unset($command);
			unset($strTemp);

			$command = array();
			$strTemp = NULL;
			$DBRESULT2 = $pearDB->query("SELECT cmd.command_name FROM contact_servicecommands_relation csr, command cmd WHERE csr.contact_contact_id = '".$contact["contact_id"]."' AND csr.command_command_id = cmd.command_id ORDER BY `command_name`");
			while ($command = $DBRESULT2->fetchRow())
				$strTemp != NULL ? $strTemp .= ", ".$command["command_name"] : $strTemp = $command["command_name"];
			$DBRESULT2->free();
			if ($strTemp)
				$str .= print_line("service_notification_commands", $strTemp);
			unset($command);
			unset($strTemp);

			/*
			 * Misc
			 */
			if ($contact["contact_email"])
				$str .= print_line("email", $contact["contact_email"]);
			if ($contact["contact_pager"])
				$str .= print_line("pager", $contact["contact_pager"]);

			/*
			 * ADDRESSX
			 */
			if (isset($contact["contact_address1"]) && $contact["contact_address1"])
				$str .= print_line("address1", $contact["contact_address1"]);
			if (isset($contact["contact_address2"]) && $contact["contact_address2"])
				$str .= print_line("address2", $contact["contact_address2"]);
			if (isset($contact["contact_address3"]) && $contact["contact_address3"])
				$str .= print_line("address3", $contact["contact_address3"]);
			if (isset($contact["contact_address4"]) && $contact["contact_address4"])
				$str .= print_line("address4", $contact["contact_address4"]);
			if (isset($contact["contact_address5"]) && $contact["contact_address5"])
				$str .= print_line("address5", $contact["contact_address5"]);
			if (isset($contact["contact_address6"]) && $contact["contact_address6"])
				$str .= print_line("address6", $contact["contact_address6"]);

            if (isset($contact["contact_enable_notifications"]) && $contact["contact_enable_notifications"] != 2) {
                $str .= print_line('host_notifications_enabled', $contact['contact_enable_notifications']);
                $str .= print_line('service_notifications_enabled', $contact['contact_enable_notifications']);
            }
                        
			/*
			 * Template
			 */
			if (isset($contact['contact_template_id']) && $contact['contact_template_id'] != 0) {
			    if (!isset($userCache[$contact['contact_template_id']])) {
			        $res = $pearDB->query(sprintf($queryGetUserAlias, $contact['contact_template_id']));
			        if (!PEAR::isError($res)) {
			            $row = $res->fetchRow();
			            $userCache[$contact['contact_template_id']] = $row['contact_name'];
			        }
			    }
			    if (isset($userCache[$contact['contact_template_id']])) {
			        $str .= print_line('use', $userCache[$contact['contact_template_id']]);
			    }
			}

			$str .= "}\n\n";
			$i++;
		}
	}
	write_in_file($handle, html_entity_decode($str, ENT_QUOTES, "UTF-8"), $nagiosCFGPath.$tab['id']."/contacts.cfg");
	fclose($handle);
	
	setFileMod($nagiosCFGPath.$tab['id']."/contacts.cfg");
	
	$DBRESULT->free();
	unset($contact);
	unset($str);
	unset($i);
?>