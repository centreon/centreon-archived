<?php
/*
 * Copyright 2005-2011 MERETHIS
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
 * SVN : $URL:
 * SVN : $Id:
 *
 */

    if (!isset($oreon))
		exit();
		
		
	/******************************************************
	 * Get list of contacts templates
	 ******************************************************/
	$handle = create_file($nagiosCFGPath . $tab['id'] . "/contactTemplates.cfg", $oreon->user->get_name());
	$queryGetTemplateContact = "SELECT contact_id, timeperiod_tp_id, timeperiod_tp_id2, contact_name, contact_alias,
			contact_host_notification_options, contact_service_notification_options, contact_email, contact_pager,
			contact_address1, contact_address2, contact_address3, contact_address4, contact_address5, contact_address6,
			contact_comment
		FROM contact
		WHERE contact_register = 0 AND contact_activate = '1'";
	$res = $pearDB->query($queryGetTemplateContact);
    $str = "";
    $i = 1;
	if (!PEAR::isError($res)) {
	    while ($contact = $res->fetchRow()) {
	        $ret["comment"] ? ($str .= "# '".$contact["contact_name"]."' contact definition ".$i."\n") : "";
	        if ($ret["comment"] && $contact["contact_comment"])	{
				$comment = array();
				$comment = explode("\n", $contact["contact_comment"]);
				foreach ($comment as $cmt)
					$str .= "# ".$cmt."\n";
			}
			
			/*
			 * Start Object
			 */
			$str .= "define contact{\n";
			if ($contact["contact_name"]) {
				$str .= print_line("name", $contact["contact_name"]);
			}
			if ($contact["contact_alias"]) { 
				$str .= print_line("alias", $contact["contact_alias"]);
			}
			
			/*
			 * Contact Groups in Contact
			 */
			/*
			 * REMOVE BECAUSE NOT WORK WITH NAGIOS
			 */
			/* $contactGroup = array();
			$strTemp = NULL;
			$queryCgContact = "SELECT cg.cg_name, cg.cg_id
				FROM contactgroup_contact_relation ccr, contactgroup cg
				WHERE ccr.contact_contact_id = %d AND ccr.contactgroup_cg_id = cg.cg_id
				ORDER BY `cg_name`";
			$DBRESULT2 = $pearDB->query(sprintf($queryCgContact, $contact["contact_id"]));
			while ($contactGroup = $DBRESULT2->fetchRow()) {
				if (isset($gbArr[1][$contactGroup["cg_id"]])) {
					$strTemp != NULL ? $strTemp .= ", ".$contactGroup["cg_name"] : $strTemp = $contactGroup["cg_name"];
				}
			}
			$DBRESULT2->free();
			if ($strTemp) {
			    $str .= print_line("contactgroups", $strTemp);
			}
			unset($contactGroup);
			unset($strTemp); */
			
			/*
			 * Timeperiod for host & service
			 */
			$timeperiod = array();
			$queryTimePeriod = "SELECT cct.timeperiod_tp_id AS cctTP1, cct.timeperiod_tp_id2 AS cctTP2, tp.tp_id, tp.tp_name FROM contact cct, timeperiod tp
				WHERE cct.contact_id = %d AND (tp.tp_id = cct.timeperiod_tp_id OR tp.tp_id = cct.timeperiod_tp_id2)
				ORDER BY `cctTP1`";
			$DBRESULT2 = $pearDB->query(sprintf($queryTimePeriod, $contact["contact_id"]));
			while ($timeperiod = $DBRESULT2->fetchRow())	{
				$timeperiod["cctTP1"] == $timeperiod["tp_id"] ? $str .= print_line("host_notification_period", $timeperiod["tp_name"]) : NULL;
				$timeperiod["cctTP2"] == $timeperiod["tp_id"] ? $str .= print_line("service_notification_period", $timeperiod["tp_name"]) : NULL;
			}
			$DBRESULT2->free();
			
			unset($timeperiod);
			if ($contact["contact_host_notification_options"]) {
				$str .= print_line("host_notification_options", $contact["contact_host_notification_options"]);
			}
			if ($contact["contact_service_notification_options"]) {
				$str .= print_line("service_notification_options", $contact["contact_service_notification_options"]);
			}
			
			/*
			 * Host & Service notification command
			 */
			$command = array();
			$strTemp = NULL;
			$queryHostNotification = "SELECT cmd.command_name
				FROM contact_hostcommands_relation chr, command cmd
				WHERE chr.contact_contact_id = %d AND chr.command_command_id = cmd.command_id ORDER BY `command_name`";
			$DBRESULT2 = $pearDB->query(sprintf($queryHostNotification, $contact["contact_id"]));
			while ($command = $DBRESULT2->fetchRow()) {
				$strTemp != NULL ? $strTemp .= ", ".$command["command_name"] : $strTemp = $command["command_name"];
			}
			$DBRESULT2->free();
			if ($strTemp) $str .= print_line("host_notification_commands", $strTemp);
			unset($command);
			unset($strTemp);
			
			$command = array();
			$strTemp = NULL;
			$queryServiceNotification = "SELECT cmd.command_name
				FROM contact_servicecommands_relation csr, command cmd
				WHERE csr.contact_contact_id = %d AND csr.command_command_id = cmd.command_id
				ORDER BY `command_name`";
			$DBRESULT2 = $pearDB->query(sprintf($queryServiceNotification, $contact["contact_id"]));
			while ($command = $DBRESULT2->fetchRow()) {
				$strTemp != NULL ? $strTemp .= ", ".$command["command_name"] : $strTemp = $command["command_name"];
			}
			$DBRESULT2->free();
			if ($strTemp) 
				$str .= print_line("service_notification_commands", $strTemp);
			unset($command);
			unset($strTemp);
			
			/*
			 * Misc
			 */
			if ($contact["contact_email"]) { 
				$str .= print_line("email", $contact["contact_email"]);
			}
			if ($contact["contact_pager"]) { 
				$str .= print_line("pager", $contact["contact_pager"]);
			}
			
			/*
			 * ADDRESSX
			 */
			if (isset($contact["contact_address1"]) && $contact["contact_address1"]) {
				$str .= print_line("address1", $contact["contact_address1"]);
			}
			if (isset($contact["contact_address2"]) && $contact["contact_address2"]) { 
				$str .= print_line("address2", $contact["contact_address2"]);
			}
			if (isset($contact["contact_address3"]) && $contact["contact_address3"]) { 
				$str .= print_line("address3", $contact["contact_address3"]);
			}
			if (isset($contact["contact_address4"]) && $contact["contact_address4"]) { 
				$str .= print_line("address4", $contact["contact_address4"]);
			}
			if (isset($contact["contact_address5"]) && $contact["contact_address5"]) { 
				$str .= print_line("address5", $contact["contact_address5"]);
			}
			if (isset($contact["contact_address6"]) && $contact["contact_address6"]) { 
				$str .= print_line("address6", $contact["contact_address6"]);
			}
			
			/*
			 * Set register = 0 for template
			 */
			$str .= print_line("register", "0");
			
			$str .= "}\n\n";
			$i++;
	    }
	    $res->free();
	}
	write_in_file($handle, html_entity_decode($str, ENT_QUOTES, "UTF-8"), $nagiosCFGPath . $tab['id'] . "/contactTemplates.cfg");
    fclose($handle);
    unset($str);
?>