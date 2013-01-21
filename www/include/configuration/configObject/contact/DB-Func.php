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
 * SVN : $URL$
 * SVN : $Id$
 *
 */
	if (!isset($oreon)) {
		exit (); 
	}

	/**
	 *
	 * Test that a contact doesn't exists
	 * @param $name
	 */
	function testContactExistence ($name = NULL)	{
		global $pearDB, $form, $oreon;

		$id = NULL;
		if (isset($form)) {
			$id = $form->getSubmitValue('contact_id');
		}

		$DBRESULT = $pearDB->query("SELECT contact_name, contact_id FROM contact WHERE contact_name = '".htmlentities($oreon->checkIllegalChar($name), ENT_QUOTES, "UTF-8")."'");
		$contact = $DBRESULT->fetchRow();
		#Modif case
		if ($DBRESULT->numRows() >= 1 && $contact["contact_id"] == $id)
			return true;
		#Duplicate entry
		else if ($DBRESULT->numRows() >= 1 && $contact["contact_id"] != $id)
			return false;
		else
			return true;
	}

	/**
	 *
	 * Test that alias is not use
	 * @param $alias
	 */
	function testAliasExistence ($alias = NULL)	{
		global $pearDB, $form;
		$id = NULL;
		if (isset($form))
			$id = $form->getSubmitValue('contact_id');
		$DBRESULT = $pearDB->query("SELECT contact_alias, contact_id FROM contact WHERE contact_alias = '".htmlentities($alias, ENT_QUOTES, "UTF-8")."'");
		$contact = $DBRESULT->fetchRow();
		#Modif case
		if ($DBRESULT->numRows() >= 1 && $contact["contact_id"] == $id)
			return true;
		#Duplicate entry
		else if ($DBRESULT->numRows() >= 1 && $contact["contact_id"] != $id)
			return false;
		else
			return true;
	}

	/**
	 *
	 * Check if a least one contact is enable
	 */
	function keepOneContactAtLeast($ct_id = null) {
		global $pearDB, $form, $centreon;

		if (!isset($contact_id)) {
			$contact_id = $ct_id;
		} else if (isset($_GET["contact_id"])) {
			$contact_id = htmlentities($_GET["contact_id"], ENT_QUOTES, "UTF-8");;
		} else {
			$contact_id = $form->getSubmitValue('contact_id');
		}
		
		if (isset($form)) {
			$cct_oreon = $form->getSubmitValue('contact_oreon');
			$cct_activate = $form->getSubmitValue('contact_activate');
		} else {
			$cct_oreon = 0;
			$cct_activate = 0;
		}

		if ($contact_id == $centreon->user->get_id()) {
			return false;
		}
		
		/*
		 * Get activated contacts
		 */
		$DBRESULT = $pearDB->query("SELECT COUNT(*) AS nbr_valid FROM contact WHERE contact_activate = '1' AND contact_oreon = '1' AND contact_id <> '$contact_id'");
		$contacts = $DBRESULT->fetchRow();

		if ($contacts["nbr_valid"] == 0) {
			if ($cct_oreon == 0 || $cct_activate == 0) {
				return false;
			}
		}
		return true;
	}

	/**
	 *
	 * Enable contacts
	 * @param $contact_id
	 * @param $contact_arr
	 */
	function enableContactInDB ($contact_id = null, $contact_arr = array())	{
		global $pearDB, $oreon;

		if (!$contact_id && !count($contact_arr)) {
			return;
		}
		if ($contact_id) {
			$contact_arr = array($contact_id => "1");
		}

		foreach ($contact_arr as $key => $value)	{
			$DBRESULT = $pearDB->query("UPDATE contact SET contact_activate = '1' WHERE contact_id = '".$key."'");
			$DBRESULT2 = $pearDB->query("SELECT contact_name FROM `contact` WHERE `contact_id` = '".$key."' LIMIT 1");
			$row = $DBRESULT2->fetchRow();
			$oreon->CentreonLogAction->insertLog("contact", $key, $row['contact_name'], "enable");
		}
	}

	/**
	 *
	 * Disable Contacts
	 * @param $contact_id
	 * @param $contact_arr
	 */
	function disableContactInDB ($contact_id = null, $contact_arr = array())	{
		global $pearDB, $oreon;

		if (!$contact_id && !count($contact_arr)) {
			return;
		}
		if ($contact_id) {
			$contact_arr = array($contact_id => "1");
		}

		foreach ($contact_arr as $key => $value) {
			if (keepOneContactAtLeast($key)) {
				$pearDB->query("UPDATE contact SET contact_activate = '0' WHERE contact_id = '".$key."'");
				$DBRESULT2 = $pearDB->query("SELECT contact_name FROM `contact` WHERE `contact_id` = '".$key."' LIMIT 1");
				$row = $DBRESULT2->fetchRow();
				$oreon->CentreonLogAction->insertLog("contact", $key, $row['contact_name'], "disable");
			}
		}
	}


	/**
	 *
	 * Delete Contacts
	 * @param $contacts
	 */
	function deleteContactInDB ($contacts = array())	{
		global $pearDB, $oreon;

		foreach($contacts as $key => $value) {
			$DBRESULT2 = $pearDB->query("SELECT contact_name FROM `contact` WHERE `contact_id` = '".$key."' LIMIT 1");
			$row = $DBRESULT2->fetchRow();

			$DBRESULT = $pearDB->query("DELETE FROM contact WHERE contact_id = '".$key."'");
			$oreon->CentreonLogAction->insertLog("contact", $key, $row['contact_name'], "d");
		}
	}

	/**
	 *
	 * Duplicate contacts
	 * @param $contacts
	 * @param $nbrDup
	 */
	function multipleContactInDB ($contacts = array(), $nbrDup = array())	{
		global $pearDB, $oreon;

		foreach ($contacts as $key => $value)	{
			$DBRESULT = $pearDB->query("SELECT * FROM contact WHERE contact_id = '".$key."' LIMIT 1");
			$row = $DBRESULT->fetchRow();
			$row["contact_id"] = '';
			for ($i = 1; $i <= $nbrDup[$key]; $i++)	{
				$val = null;
				foreach ($row as $key2=>$value2)	{
					$key2 == "contact_name" ? ($contact_name = $value2 = $value2."_".$i) : null;
					$key2 == "contact_alias" ? ($contact_alias = $value2 = $value2."_".$i) : null;
					$val ? $val .= ($value2!=NULL?(", '".$value2."'"):", NULL") : $val .= ($value2 != NULL?("'".$value2."'"):"NULL");
					if ($key2 != "contact_id") {
						$fields[$key2] = $value2;
					}
					if (isset($contact_name)) {
					    $fields["contact_name"] = $contact_name;
					}
					if (isset($contact_alias)) {
					    $fields["contact_alias"] = $contact_alias;
					}
				}

				if (isset($row['contact_name'])) {
				    $row["contact_name"] = $oreon->checkIllegalChar($row["contact_name"]);
				}

				if (testContactExistence($contact_name) && testAliasExistence($contact_alias))	{
					$val ? $rq = "INSERT INTO contact VALUES (".$val.")" : $rq = null;
					$DBRESULT = $pearDB->query($rq);
					$DBRESULT = $pearDB->query("SELECT MAX(contact_id) FROM contact");
					$maxId = $DBRESULT->fetchRow();
					if (isset($maxId["MAX(contact_id)"]))	{
						/*
						 * Command update
						 */
						$DBRESULT = $pearDB->query("SELECT DISTINCT command_command_id FROM contact_hostcommands_relation WHERE contact_contact_id = '".$key."'");
						$fields["contact_hostNotifCmds"] = "";
						while($hostCmd = $DBRESULT->fetchRow())	{
							$DBRESULT2 = $pearDB->query("INSERT INTO contact_hostcommands_relation VALUES ('', '".$maxId["MAX(contact_id)"]."', '".$hostCmd["command_command_id"]."')");
							$fields["contact_hostNotifCmds"] .= $hostCmd["command_command_id"] . ",";
						}
						$fields["contact_hostNotifCmds"] = trim($fields["contact_hostNotifCmds"], ",");

						/*
						 * Commands update
						 */
						$DBRESULT = $pearDB->query("SELECT DISTINCT command_command_id FROM contact_servicecommands_relation WHERE contact_contact_id = '".$key."'");
						$fields["contact_svNotifCmds"] = "";
						while($serviceCmd = $DBRESULT->fetchRow())	{
							$DBRESULT2 = $pearDB->query("INSERT INTO contact_servicecommands_relation VALUES ('', '".$maxId["MAX(contact_id)"]."', '".$serviceCmd["command_command_id"]."')");
							$fields["contact_svNotifCmds"] .= $serviceCmd["command_command_id"] . ",";
						}
						$fields["contact_svNotifCmds"] = trim($fields["contact_svNotifCmds"], ",");

						/*
						 * Contact groups
						 */
						$DBRESULT = $pearDB->query("SELECT DISTINCT contactgroup_cg_id FROM contactgroup_contact_relation WHERE contact_contact_id = '".$key."'");
						$fields["contact_cgNotif"] = "";
						while($Cg = $DBRESULT->fetchRow())	{
							$DBRESULT2 = $pearDB->query("INSERT INTO contactgroup_contact_relation VALUES ('', '".$maxId["MAX(contact_id)"]."', '".$Cg["contactgroup_cg_id"]."')");
							$fields["contact_cgNotif"] .= $Cg["contactgroup_cg_id"] . ",";
						}
						$fields["contact_cgNotif"] = trim($fields["contact_cgNotif"], ",");
						$oreon->CentreonLogAction->insertLog("contact", $maxId["MAX(contact_id)"], $contact_name, "a", $fields);
					}
				}
			}
		}
	}

	function updateContactInDB ($contact_id = NULL, $from_MC = false)	{
		global $form;
		if (!$contact_id)
			return;
		$ret = $form->getSubmitValues();
		# Global function to use
		if ($from_MC)
			updateContact_MC($contact_id);
		else
			updateContact($contact_id, $from_MC);
		# Function for updating host commands
		# 1 - MC with deletion of existing cmds
		# 2 - MC with addition of new cmds
		# 3 - Normal update
		if (isset($ret["mc_mod_hcmds"]["mc_mod_hcmds"]) && $ret["mc_mod_hcmds"]["mc_mod_hcmds"])
			updateContactHostCommands($contact_id);
		else if (isset($ret["mc_mod_hcmds"]["mc_mod_hcmds"]) && !$ret["mc_mod_hcmds"]["mc_mod_hcmds"])
			updateContactHostCommands_MC($contact_id);
		else
			updateContactHostCommands($contact_id);
		# Function for updating service commands
		# 1 - MC with deletion of existing cmds
		# 2 - MC with addition of new cmds
		# 3 - Normal update
		if (isset($ret["mc_mod_svcmds"]["mc_mod_svcmds"]) && $ret["mc_mod_svcmds"]["mc_mod_svcmds"])
			updateContactServiceCommands($contact_id);
		else if (isset($ret["mc_mod_svcmds"]["mc_mod_svcmds"]) && !$ret["mc_mod_svcmds"]["mc_mod_svcmds"])
			updateContactServiceCommands_MC($contact_id);
		else
			updateContactServiceCommands($contact_id);
		# Function for updating contact groups
		# 1 - MC with deletion of existing cg
		# 2 - MC with addition of new cg
		# 3 - Normal update
		if (isset($ret["mc_mod_cg"]["mc_mod_cg"]) && $ret["mc_mod_cg"]["mc_mod_cg"])
			updateContactContactGroup($contact_id);
		else if (isset($ret["mc_mod_cg"]["mc_mod_cg"]) && !$ret["mc_mod_cg"]["mc_mod_cg"])
			updateContactContactGroup_MC($contact_id);
		else
			updateContactContactGroup($contact_id);

		/**
		 * ACL
		 */
		if (isset($ret["mc_mod_acl"]["mc_mod_acl"]) && $ret["mc_mod_acl"]["mc_mod_acl"]) {
			updateAccessGroupLinks($contact_id);
		} else if (isset($ret["mc_mod_acl"]["mc_mod_acl"]) && !$ret["mc_mod_acl"]["mc_mod_acl"]) {
			updateAccessGroupLinks_MC($contact_id, $ret["mc_mod_acl"]["mc_mod_acl"]);
		} else {
			updateAccessGroupLinks($contact_id);
		}
	}

	function insertContactInDB ($ret = array())	{
		$contact_id = insertContact($ret);
		updateContactHostCommands($contact_id, $ret);
		updateContactServiceCommands($contact_id, $ret);
		updateContactContactGroup($contact_id, $ret);
		updateAccessGroupLinks($contact_id);
		return ($contact_id);
	}

	function insertContact($ret = array())	{
		global $form, $pearDB, $oreon, $encryptType;

		if (!count($ret))
			$ret = $form->getSubmitValues();

		$ret["contact_name"] = $oreon->checkIllegalChar($ret["contact_name"]);

		$rq = "INSERT INTO `contact` ( " .
				"`contact_id` , `timeperiod_tp_id` , `timeperiod_tp_id2` , `contact_name` , " .
				"`contact_alias` , `contact_autologin_key` , `contact_passwd` , `contact_lang` , `contact_template_id`, " .
				"`contact_host_notification_options` , `contact_service_notification_options` , " .
				"`contact_email` , `contact_pager` , `contact_comment` , `contact_oreon`, `contact_register`, `contact_enable_notifications` , " .
				"`contact_admin` , `contact_type_msg`, `contact_activate`, `contact_auth_type`, " .
				"`contact_ldap_dn`, `contact_location`, `contact_address1`, `contact_address2`, " .
				"`contact_address3`, `contact_address4`, `contact_address5`, `contact_address6`)" .
				"VALUES ( ";
		$rq .= "NULL, ";
		isset($ret["timeperiod_tp_id"]) && $ret["timeperiod_tp_id"] != NULL ? $rq .= "'".$ret["timeperiod_tp_id"]."', ": $rq .= "NULL, ";
		isset($ret["timeperiod_tp_id2"]) && $ret["timeperiod_tp_id2"] != NULL ? $rq .= "'".$ret["timeperiod_tp_id2"]."', ": $rq .= "NULL, ";
		isset($ret["contact_name"]) && $ret["contact_name"] != NULL ? $rq .= "'".htmlentities($ret["contact_name"], ENT_QUOTES, "UTF-8")."', ": $rq .= "NULL, ";
		isset($ret["contact_alias"]) && $ret["contact_alias"] != NULL ? $rq .= "'".htmlentities($ret["contact_alias"], ENT_QUOTES, "UTF-8")."', ": $rq .= "NULL, ";
		isset($ret["contact_autologin_key"]) && $ret["contact_autologin_key"] != NULL ? $rq .= "'".htmlentities($ret["contact_autologin_key"], ENT_QUOTES)."', ": $rq .= "NULL, ";
		if ($encryptType == 1)
			isset($ret["contact_passwd"]) && $ret["contact_passwd"] != NULL ? $rq .= "'".md5($ret["contact_passwd"])."', ": $rq .= "NULL, ";
		else if ($encryptType == 2)
			isset($ret["contact_passwd"]) && $ret["contact_passwd"] != NULL ? $rq .= "'".sha1($ret["contact_passwd"])."', ": $rq .= "NULL, ";
		else
			isset($ret["contact_passwd"]) && $ret["contact_passwd"] != NULL ? $rq .= "'".md5($ret["contact_passwd"])."', ": $rq .= "NULL, ";

		isset($ret["contact_lang"]) && $ret["contact_lang"] != NULL ? $rq .= "'".htmlentities($ret["contact_lang"], ENT_QUOTES, "UTF-8")."', ": $rq .= "NULL, ";
		isset($ret["contact_template_id"]) && $ret["contact_template_id"] != NULL ? $rq .= "'".htmlentities($ret["contact_template_id"], ENT_QUOTES, "UTF-8")."', ": $rq .= "NULL, ";
		isset($ret["contact_hostNotifOpts"]) && $ret["contact_hostNotifOpts"] != NULL ? $rq .= "'".implode(",", array_keys($ret["contact_hostNotifOpts"]))."', ": $rq .= "NULL, ";
		isset($ret["contact_svNotifOpts"]) && $ret["contact_svNotifOpts"] != NULL ? $rq .= "'".implode(",", array_keys($ret["contact_svNotifOpts"]))."', ": $rq .= "NULL, ";
		isset($ret["contact_email"]) && $ret["contact_email"] != NULL ? $rq .= "'".htmlentities($ret["contact_email"], ENT_QUOTES, "UTF-8")."', ": $rq .= "NULL, ";
		isset($ret["contact_pager"]) && $ret["contact_pager"] != NULL ? $rq .= "'".htmlentities($ret["contact_pager"], ENT_QUOTES, "UTF-8")."', ": $rq .= "NULL, ";
		isset($ret["contact_comment"]) && $ret["contact_comment"] != NULL ? $rq .= "'".htmlentities($ret["contact_comment"], ENT_QUOTES, "UTF-8")."', ": $rq .= "NULL, ";

		if (isset($_POST["contact_select"]) && isset($_POST["contact_select"]["select"])) {
			$rq .= "'1', ";
		} else {
			isset($ret["contact_oreon"]["contact_oreon"]) && $ret["contact_oreon"]["contact_oreon"] != NULL ? $rq .= "'".$ret["contact_oreon"]["contact_oreon"]."', ": $rq .= " '1', ";
		}

		isset($ret["contact_register"]["contact_register"]) && $ret["contact_register"]["contact_register"] != NULL ? $rq .= "'".$ret["contact_register"]["contact_register"]."', ": $rq .= " '1', ";
		isset($ret["contact_enable_notifications"]["contact_enable_notifications"]) && $ret["contact_enable_notifications"]["contact_enable_notifications"] != NULL ? $rq .= "'".$ret["contact_enable_notifications"]["contact_enable_notifications"]."', ": $rq .= "NULL, ";
		isset($ret["contact_admin"]["contact_admin"]) && $ret["contact_admin"]["contact_admin"] != NULL ? $rq .= "'".$ret["contact_admin"]["contact_admin"]."', ": $rq .= "NULL, ";
		isset($ret["contact_type_msg"]) && $ret["contact_type_msg"] != NULL ? $rq .= "'".$ret["contact_type_msg"]."', ": $rq .= "NULL, ";
		isset($ret["contact_activate"]["contact_activate"]) && $ret["contact_activate"]["contact_activate"] != NULL ? $rq .= "'".$ret["contact_activate"]["contact_activate"]."', ": $rq .= "NULL, ";
		isset($ret["contact_auth_type"]) && $ret["contact_auth_type"] != NULL ? $rq .= "'".$ret["contact_auth_type"]."', ": $rq .= "'local', ";
		isset($ret["contact_ldap_dn"]) && $ret["contact_ldap_dn"] != NULL ? $rq .= "'". str_replace("\\", "\\\\", $ret["contact_ldap_dn"]) ."', ": $rq .= "NULL, ";
		isset($ret["contact_location"]) && $ret["contact_location"] != NULL ? $rq .= "'".$ret["contact_location"]."', ": $rq .= "NULL, ";
		isset($ret["contact_address1"]) && $ret["contact_address1"] != NULL ? $rq .= "'".htmlentities($ret["contact_address1"], ENT_QUOTES, "UTF-8")."', ": $rq .= "NULL, ";
		isset($ret["contact_address2"]) && $ret["contact_address2"] != NULL ? $rq .= "'".htmlentities($ret["contact_address2"], ENT_QUOTES, "UTF-8")."', ": $rq .= "NULL, ";
		isset($ret["contact_address3"]) && $ret["contact_address3"] != NULL ? $rq .= "'".htmlentities($ret["contact_address3"], ENT_QUOTES, "UTF-8")."', ": $rq .= "NULL, ";
		isset($ret["contact_address4"]) && $ret["contact_address4"] != NULL ? $rq .= "'".htmlentities($ret["contact_address4"], ENT_QUOTES, "UTF-8")."', ": $rq .= "NULL, ";
		isset($ret["contact_address5"]) && $ret["contact_address5"] != NULL ? $rq .= "'".htmlentities($ret["contact_address5"], ENT_QUOTES, "UTF-8")."', ": $rq .= "NULL, ";
		isset($ret["contact_address6"]) && $ret["contact_address6"] != NULL ? $rq .= "'".htmlentities($ret["contact_address6"], ENT_QUOTES, "UTF-8")."' ": $rq .= "NULL ";
		$rq .= ")";
		
		$DBRESULT = $pearDB->query($rq);
		$DBRESULT = $pearDB->query("SELECT MAX(contact_id) FROM contact");
		$contact_id = $DBRESULT->fetchRow();

		if (isset($ret["timeperiod_tp_id"]))
			$fields["timeperiod_tp_id"] = $ret["timeperiod_tp_id"];
		if (isset( $ret["timeperiod_tp_id2"]))
			$fields["timeperiod_tp_id2"] = $ret["timeperiod_tp_id2"];
		if (isset($ret["contact_name"]))
			$fields["contact_name"] = htmlentities($ret["contact_name"], ENT_QUOTES, "UTF-8");
		if (isset($ret["contact_alias"]))
			$fields["contact_alias"] = htmlentities($ret["contact_alias"], ENT_QUOTES, "UTF-8");
		if (isset($ret["contact_passwd"])) {
			if ($encryptType == 1)
				$fields["contact_passwd"] = md5($ret["contact_passwd"]);
			else if ($encryptType == 2)
				$fields["contact_passwd"] = sha1($ret["contact_passwd"]);
			else
				$fields["contact_passwd"] = md5($ret["contact_passwd"]);
		}
		if (isset($ret["contact_lang"]))
			$fields["contact_lang"] = htmlentities($ret["contact_lang"], ENT_QUOTES, "UTF-8");
		if (isset($ret["contact_hostNotifOpts"]))
			$fields["contact_hostNotifOpts"] = implode(",", array_keys($ret["contact_hostNotifOpts"]));
		if (isset($ret["contact_svNotifOpts"]))
			$fields["contact_svNotifOpts"] = implode(",", array_keys($ret["contact_svNotifOpts"]));
		if (isset($ret["contact_email"]))
			$fields["contact_email"] = htmlentities($ret["contact_email"], ENT_QUOTES, "UTF-8");
		if (isset($ret["contact_pager"]))
			$fields["contact_pager"] = htmlentities($ret["contact_pager"], ENT_QUOTES, "UTF-8");
		if (isset($ret["contact_comment"]))
			$fields["contact_comment"] = htmlentities($ret["contact_comment"], ENT_QUOTES, "UTF-8");
		if (isset($ret["contact_oreon"]["contact_oreon"]))
			$fields["contact_oreon"] = $ret["contact_oreon"]["contact_oreon"];
		if (isset($ret["contact_enable_notifications"]["contact_enable_notifications"]))
			$fields["contact_enable_notifications"] = $ret["contact_enable_notifications"]["contact_enable_notifications"];
		if (isset($ret["contact_admin"]["contact_admin"]))
			$fields["contact_admin"] = $ret["contact_admin"]["contact_admin"];
		if (isset($ret["contact_type_msg"]))
			$fields["contact_type_msg"] = $ret["contact_type_msg"];

		$fields["contact_activate"] = $ret["contact_activate"]["contact_activate"];
		$fields["contact_auth_type"] = $ret["contact_auth_type"];

		if (isset($ret["contact_ldap_dn"]))
			$fields["contact_ldap_dn"] = $ret["contact_ldap_dn"];
		if (isset($ret["contact_location"]))
			$fields["contact_location"] = $ret["contact_location"];
		if (isset($ret["contact_hostNotifCmds"]))
			$fields["contact_hostNotifCmds"] = implode(",", $ret["contact_hostNotifCmds"]);
		if (isset($ret["contact_svNotifCmds"]))
			$fields["contact_svNotifCmds"] = implode(",", $ret["contact_svNotifCmds"]);
		if (isset($ret["contact_cgNotif"]))
			$fields["contact_cgNotif"] = implode(",", $ret["contact_cgNotif"]);

		if (isset($ret["contact_address1"]))
			$fields["contact_address1"] = $ret["contact_address1"];
		if (isset($ret["contact_address2"]))
			$fields["contact_address2"] = $ret["contact_address2"];
		if (isset($ret["contact_address3"]))
			$fields["contact_address3"] = $ret["contact_address3"];
		if (isset($ret["contact_address4"]))
			$fields["contact_address4"] = $ret["contact_address4"];
		if (isset($ret["contact_address5"]))
			$fields["contact_address5"] = $ret["contact_address5"];
		if (isset($ret["contact_address6"]))
			$fields["contact_address6"] = $ret["contact_address6"];

		/*
		 * Write Actions Logs
		 */
		$oreon->CentreonLogAction->insertLog("contact", $contact_id["MAX(contact_id)"], $ret["contact_name"], "a", $fields);

		return ($contact_id["MAX(contact_id)"]);
	}

	function updateContact($contact_id = null, $from_MC = false)	{
		global $form, $pearDB, $oreon, $encryptType;
		if (!$contact_id)
			return;
		$ret = array();
		$ret = $form->getSubmitValues();

		$ret["contact_name"] = $oreon->checkIllegalChar($ret["contact_name"]);

		$rq = "UPDATE contact ";
		$rq .= "SET timeperiod_tp_id = ";
		isset($ret["timeperiod_tp_id"]) && $ret["timeperiod_tp_id"] != NULL ? $rq .= "'".$ret["timeperiod_tp_id"]."', ": $rq .= "NULL, ";
		$rq .= "timeperiod_tp_id2 = ";
		isset($ret["timeperiod_tp_id2"]) && $ret["timeperiod_tp_id2"] != NULL ? $rq .= "'".$ret["timeperiod_tp_id2"]."', ": $rq .= "NULL, ";
		# If we are doing a MC, we don't have to set name and alias field
		if (!$from_MC)	{
			$rq .= "contact_name = ";
			isset($ret["contact_name"]) && $ret["contact_name"] != NULL ? $rq .= "'".htmlentities($ret["contact_name"], ENT_QUOTES, "UTF-8")."', ": $rq .= "NULL, ";
			$rq .= "contact_alias = ";
			isset($ret["contact_alias"]) && $ret["contact_alias"] != NULL ? $rq .= "'".htmlentities($ret["contact_alias"], ENT_QUOTES, "UTF-8")."', ": $rq .= "NULL, ";
			$rq.= "contact_autologin_key = ";
			isset($ret["contact_autologin_key"]) && $ret["contact_autologin_key"] != NULL ? $rq .= "'".htmlentities($ret["contact_autologin_key"], ENT_QUOTES, "UTF-8")."', ": $rq .= "NULL, ";
		}
		if (isset($ret["contact_passwd"]) && $ret["contact_passwd"]) {
			if ($encryptType == 1)
				$rq .= "contact_passwd = '".md5($ret["contact_passwd"])."', ";
			else if ($encryptType == 2)
				$rq .= "contact_passwd = '".sha1($ret["contact_passwd"])."', ";
			else
				$rq .= "contact_passwd = '".md5($ret["contact_passwd"])."', ";
		}
		$rq .=	"contact_lang = ";
		isset($ret["contact_lang"]) && $ret["contact_lang"] != NULL ? $rq .= "'".htmlentities($ret["contact_lang"], ENT_QUOTES, "UTF-8")."', ": $rq .= "NULL, ";
		$rq .= 	"contact_host_notification_options = ";
		isset($ret["contact_hostNotifOpts"]) && $ret["contact_hostNotifOpts"] != NULL ? $rq .= "'".implode(",", array_keys($ret["contact_hostNotifOpts"]))."', ": $rq .= "NULL, ";
		$rq .= "contact_service_notification_options = ";
		isset($ret["contact_svNotifOpts"]) && $ret["contact_svNotifOpts"] != NULL ? $rq .= "'".implode(",", array_keys($ret["contact_svNotifOpts"]))."', ": $rq .= "NULL, ";
		$rq .= "contact_email = ";
		isset($ret["contact_email"]) && $ret["contact_email"] != NULL ? $rq .= "'".htmlentities($ret["contact_email"], ENT_QUOTES, "UTF-8")."', ": $rq .= "NULL, ";
		$rq .= "contact_pager = ";
		isset($ret["contact_pager"]) && $ret["contact_pager"] != NULL ? $rq .= "'".htmlentities($ret["contact_pager"], ENT_QUOTES, "UTF-8")."', ": $rq .= "NULL, ";

		$rq .= "contact_template_id = ";
		isset($ret["contact_template_id"]) && $ret["contact_template_id"] != NULL ? $rq .= "'".htmlentities($ret["contact_template_id"], ENT_QUOTES, "UTF-8")."', ": $rq .= "NULL, ";

		$rq .= "contact_comment = ";
		isset($ret["contact_comment"]) && $ret["contact_comment"] != NULL ? $rq .= "'".htmlentities($ret["contact_comment"], ENT_QUOTES, "UTF-8")."', ": $rq .= "NULL, ";
		$rq .= "contact_oreon = ";
		isset($ret["contact_oreon"]["contact_oreon"]) && $ret["contact_oreon"]["contact_oreon"] != NULL ? $rq .= "'".$ret["contact_oreon"]["contact_oreon"]."', ": $rq .= "NULL, ";
		$rq .= "contact_enable_notifications = ";
		isset($ret["contact_enable_notifications"]["contact_enable_notifications"]) && $ret["contact_enable_notifications"]["contact_enable_notifications"] != NULL ? $rq .= "'".$ret["contact_enable_notifications"]["contact_enable_notifications"]."', ": $rq .= "NULL, ";
		$rq .= "contact_admin = ";
		isset($ret["contact_admin"]["contact_admin"]) && $ret["contact_admin"]["contact_admin"] != NULL ? $rq .= "'".$ret["contact_admin"]["contact_admin"]."', ": $rq .= "NULL, ";

		$rq .= "contact_register = ";
		isset($ret["contact_register"]["contact_register"]) && $ret["contact_register"]["contact_register"] != NULL ? $rq .= "'".$ret["contact_register"]["contact_register"]."', ": $rq .= "NULL, ";

		$rq .= "contact_type_msg = ";
		isset($ret["contact_type_msg"]) && $ret["contact_type_msg"] != NULL ? $rq .= "'".$ret["contact_type_msg"]."', ": $rq .= "NULL, ";
		$rq .= "contact_activate = ";
		isset($ret["contact_activate"]["contact_activate"]) && $ret["contact_activate"]["contact_activate"] != NULL ? $rq .= "'".$ret["contact_activate"]["contact_activate"]."', ": $rq .= "NULL, ";
		$rq .= "contact_auth_type = ";
		isset($ret["contact_auth_type"]) && $ret["contact_auth_type"] != NULL ? $rq .= "'".$ret["contact_auth_type"]."', ": $rq .= "'local', ";
		$rq .= "contact_ldap_dn = ";
		isset($ret["contact_ldap_dn"]) && $ret["contact_ldap_dn"] != NULL ? $rq .= "'".str_replace("\\", "\\\\", $ret["contact_ldap_dn"])."', ": $rq .= "NULL, ";
		$rq .= "contact_location = ";
		isset($ret["contact_location"]) && $ret["contact_location"] != NULL ? $rq .= "'".$ret["contact_location"]."', ": $rq .= "NULL, ";

		$rq .= "contact_address1 = ";
		isset($ret["contact_address1"]) && $ret["contact_address1"] != NULL ? $rq .= "'".$ret["contact_address1"]."', ": $rq .= "NULL, ";
		$rq .= "contact_address2 = ";
		isset($ret["contact_address2"]) && $ret["contact_address2"] != NULL ? $rq .= "'".$ret["contact_address2"]."', ": $rq .= "NULL, ";
		$rq .= "contact_address3 = ";
		isset($ret["contact_address3"]) && $ret["contact_address3"] != NULL ? $rq .= "'".$ret["contact_address3"]."', ": $rq .= "NULL, ";
		$rq .= "contact_address4 = ";
		isset($ret["contact_address4"]) && $ret["contact_address4"] != NULL ? $rq .= "'".$ret["contact_address4"]."', ": $rq .= "NULL, ";
		$rq .= "contact_address5 = ";
		isset($ret["contact_address5"]) && $ret["contact_address5"] != NULL ? $rq .= "'".$ret["contact_address5"]."', ": $rq .= "NULL, ";
		$rq .= "contact_address6 = ";
		isset($ret["contact_address6"]) && $ret["contact_address6"] != NULL ? $rq .= "'".$ret["contact_address6"]."' ": $rq .= "NULL ";

		$rq .= "WHERE contact_id = '".$contact_id."'";
		
		$DBRESULT = $pearDB->query($rq);
		if (isset($ret["contact_lang"]) && $ret["contact_lang"] != NULL && $contact_id == $oreon->user->get_id()) {
			$oreon->user->set_lang($ret["contact_lang"]);
		}
		$fields["timeperiod_tp_id"] = $ret["timeperiod_tp_id"];
		$fields["timeperiod_tp_id2"] = $ret["timeperiod_tp_id2"];
		$fields["contact_name"] = htmlentities($ret["contact_name"], ENT_QUOTES, "UTF-8");
		$fields["contact_alias"] = htmlentities($ret["contact_alias"], ENT_QUOTES, "UTF-8");

		if ($encryptType == 1) {
			$fields["contact_passwd"] = md5($ret["contact_passwd"]);
		} else if ($encryptType == 2) {
			$fields["contact_passwd"] = sha1($ret["contact_passwd"]);
		} else {
			$fields["contact_passwd"] = md5($ret["contact_passwd"]);
		}

		$fields["contact_lang"] = htmlentities($ret["contact_lang"], ENT_QUOTES, "UTF-8");
		if (isset($ret["contact_hostNotifOpts"]))
			$fields["contact_hostNotifOpts"] = implode(",", array_keys($ret["contact_hostNotifOpts"]));
		if (isset($ret["contact_svNotifOpts"]))
			$fields["contact_svNotifOpts"] = implode(",", array_keys($ret["contact_svNotifOpts"]));
		$fields["contact_email"] = htmlentities($ret["contact_email"], ENT_QUOTES, "UTF-8");
		$fields["contact_pager"] = htmlentities($ret["contact_pager"], ENT_QUOTES, "UTF-8");
		$fields["contact_comment"] = htmlentities($ret["contact_comment"], ENT_QUOTES, "UTF-8");
		$fields["contact_oreon"] = $ret["contact_oreon"]["contact_oreon"];
		$fields["contact_admin"] = $ret["contact_admin"]["contact_admin"];
		if (isset($ret["contact_register"]) && isset($ret["contact_register"]["contact_register"]))
			$fields["contact_register"] = $ret["contact_register"]["contact_register"];
		$fields["contact_activate"] = $ret["contact_activate"]["contact_activate"];
		$fields["contact_auth_type"] = $ret["contact_auth_type"];
		if (isset($ret["contact_template_id"]))
			$fields["contact_template_id"] = $ret["contact_template_id"];
		if (isset($ret["contact_ldap_dn"]))
			$fields["contact_ldap_dn"] = $ret["contact_ldap_dn"];
		if (isset($ret["contact_location"]))
			$fields["contact_location"] = $ret["contact_location"];
		if (isset($ret["contact_hostNotifCmds"]))
			$fields["contact_hostNotifCmds"] = implode(",", $ret["contact_hostNotifCmds"]);
		if (isset($ret["contact_svNotifCmds"]))
			$fields["contact_svNotifCmds"] = implode(",", $ret["contact_svNotifCmds"]);
		if (isset($ret["contact_cgNotif"]))
			$fields["contact_cgNotif"] = implode(",", $ret["contact_cgNotif"]);
		$fields["contact_address1"] = $ret["contact_address1"];
		$fields["contact_address2"] = $ret["contact_address2"];
		$fields["contact_address3"] = $ret["contact_address3"];
		$fields["contact_address4"] = $ret["contact_address4"];
		$fields["contact_address5"] = $ret["contact_address5"];
		$fields["contact_address6"] = $ret["contact_address6"];
		/*
		 * Write Action logs
		 */
		$oreon->CentreonLogAction->insertLog("contact", $contact_id, $ret["contact_name"], "c", $fields);
	}

	function updateContact_MC($contact_id = null)	{
		global $form, $pearDB, $oreon, $encryptType;
		if (!$contact_id)
			return;
		$ret = array();
		$ret = $form->getSubmitValues();
		$rq = "UPDATE contact SET ";
		if (isset($ret["timeperiod_tp_id"]) && $ret["timeperiod_tp_id"] != NULL) {
			$rq .= "timeperiod_tp_id = '".$ret["timeperiod_tp_id"]."', ";
			$fields["timeperiod_tp_id"] = $ret["timeperiod_tp_id"];
		}
		if (isset($ret["timeperiod_tp_id2"]) && $ret["timeperiod_tp_id2"] != NULL) {
			$rq .= "timeperiod_tp_id2 = '".$ret["timeperiod_tp_id2"]."', ";
			$fields["timeperiod_tp_id2"] = $ret["timeperiod_tp_id2"];
		}
		if (isset($ret["contact_passwd"]) && $ret["contact_passwd"]) {
			if ($encryptType == 1) {
				$rq .= "contact_passwd = '".md5($ret["contact_passwd"])."', ";
				$fields["contact_passwd"] = md5($ret["contact_passwd"]);
			} else if ($encryptType == 2) {
				$rq .= "contact_passwd = '".sha1($ret["contact_passwd"])."', ";
				$fields["contact_passwd"] = sha1($ret["contact_passwd"]);
			} else {
				$rq .= "contact_passwd = '".md5($ret["contact_passwd"])."', ";
				$fields["contact_passwd"] = md5($ret["contact_passwd"]);
			}
		}
		if (isset($ret["contact_lang"]) && $ret["contact_lang"] != NULL && $ret['contact_lang']) {
			$rq .= "contact_lang = '".htmlentities($ret["contact_lang"], ENT_QUOTES, "UTF-8")."', ";
			$fields["contact_lang"] = htmlentities($ret["contact_lang"], ENT_QUOTES, "UTF-8");
		}
	    if (isset($ret['contact_enable_notifications']['contact_enable_notifications']) && $ret['contact_enable_notifications']['contact_enable_notifications'] != null) {
            $rq .= "contact_enable_notifications = '".$pearDB->escape($ret['contact_enable_notifications']['contact_enable_notifications'])."', ";
            $fields["contact_enable_notifications"] = "'".$pearDB->escape($ret['contact_enable_notifications']['contact_enable_notifications'])."'";
		}
		if (isset($ret["contact_hostNotifOpts"]) && $ret["contact_hostNotifOpts"] != NULL) {
			$rq .= "contact_host_notification_options = '".implode(",", array_keys($ret["contact_hostNotifOpts"]))."', ";
			$fields["contact_hostNotifOpts"] = implode(",", array_keys($ret["contact_hostNotifOpts"]));
		}
		if (isset($ret["contact_svNotifOpts"]) && $ret["contact_svNotifOpts"] != NULL) {
			$rq .= "contact_service_notification_options = '".implode(",", array_keys($ret["contact_svNotifOpts"]))."', ";
			$fields["contact_svNotifOpts"] = implode(",", array_keys($ret["contact_svNotifOpts"]));
		}
		if (isset($ret["contact_email"]) && $ret["contact_email"] != NULL) {
			$rq .= "contact_email = '".htmlentities($ret["contact_email"], ENT_QUOTES, "UTF-8")."', ";
			$fields["contact_email"] = htmlentities($ret["contact_email"], ENT_QUOTES, "UTF-8");
		}
		if (isset($ret["contact_pager"]) && $ret["contact_pager"] != NULL) {
			$rq .= "contact_pager = '".htmlentities($ret["contact_pager"], ENT_QUOTES, "UTF-8")."', ";
			$fields["contact_pager"] = htmlentities($ret["contact_pager"], ENT_QUOTES, "UTF-8");
		}
		if (isset($ret["contact_comment"]) && $ret["contact_comment"] != NULL) {
			$rq .= "contact_comment = '".htmlentities($ret["contact_comment"], ENT_QUOTES, "UTF-8")."', ";
			$fields["contact_comment"] = htmlentities($ret["contact_comment"], ENT_QUOTES, "UTF-8");
		}
		if (isset($ret["contact_oreon"]["contact_oreon"]) && $ret["contact_oreon"]["contact_oreon"] != NULL) {
			$rq .= "contact_oreon = '".$ret["contact_oreon"]["contact_oreon"]."', ";
			$fields["contact_oreon"] = $ret["contact_oreon"]["contact_oreon"];
		}
		if (isset($ret["contact_admin"]["contact_admin"]) && $ret["contact_admin"]["contact_admin"] != NULL) {
			$rq .= "contact_admin = '".$ret["contact_admin"]["contact_admin"]."', ";
			$fields["contact_admin"] = $ret["contact_admin"]["contact_admin"];
		}
		if (isset($ret["contact_type_msg"]) && $ret["contact_type_msg"] != NULL) {
			$rq .= "contact_type_msg = '".$ret["contact_type_msg"]."', ";
			$fields["contact_type_msg"] = $ret["contact_type_msg"];
		}
		if (isset($ret["contact_activate"]["contact_activate"]) && $ret["contact_activate"]["contact_activate"] != NULL) {
			$rq .= "contact_activate = '".$ret["contact_activate"]["contact_activate"]."', ";
			$fields["contact_activate"] = $ret["contact_activate"]["contact_activate"];
		}
		if (isset($ret["contact_auth_type"]) && $ret["contact_auth_type"] != NULL) {
			$rq .= "contact_auth_type = '".$ret["contact_auth_type"]."', ";
			$fields["contact_auth_type"] = $ret["contact_auth_type"];
		}
		if (isset($ret["contact_ldap_dn"]) && $ret["contact_ldap_dn"] != NULL) {
			$rq .= "contact_ldap_dn = '".str_replace("\\", "\\\\", $ret["contact_ldap_dn"])."', ";
			$fields["contact_ldap_dn"] = $ret["contact_ldap_dn"];
		}
		if (isset($ret["contact_location"]) && $ret["contact_location"] != NULL) {
			$rq .= "contact_location = '".$ret["contact_location"]."', ";
			$fields["contact_location"] = $ret["contact_location"];
		}

		if (isset($ret["contact_address1"]) && $ret["contact_address1"] != NULL) {
			$rq .= "contact_address1 = '".$ret["contact_address1"]."', ";
			$fields["contact_address1"] = $ret["contact_address1"];
		}
		if (isset($ret["contact_address2"]) && $ret["contact_address2"] != NULL) {
			$rq .= "contact_address2 = '".$ret["contact_address2"]."', ";
			$fields["contact_address2"] = $ret["contact_address2"];
		}
		if (isset($ret["contact_address3"]) && $ret["contact_address3"] != NULL) {
			$rq .= "contact_address3 = '".$ret["contact_address3"]."', ";
			$fields["contact_address3"] = $ret["contact_address3"];
		}
		if (isset($ret["contact_address4"]) && $ret["contact_address4"] != NULL) {
			$rq .= "contact_address4 = '".$ret["contact_address4"]."', ";
			$fields["contact_address4"] = $ret["contact_address4"];
		}
		if (isset($ret["contact_address5"]) && $ret["contact_address5"] != NULL) {
			$rq .= "contact_address5 = '".$ret["contact_address5"]."', ";
			$fields["contact_address5"] = $ret["contact_address5"];
		}
		if (isset($ret["contact_address6"]) && $ret["contact_address6"] != NULL) {
			$rq .= "contact_address6 = '".$ret["contact_address6"]."', ";
			$fields["contact_address6"] = $ret["contact_address6"];
		}
                if (isset($ret['contact_template_id']) && $ret['contact_template_id']) {
                        $rq .= "contact_template_id = ".$pearDB->escape($ret['contact_template_id']).", ";
                        $fields['contact_template_id'] = $ret['contact_template_id'];
                }
                
		/*
		 * Delete last ',' in request
		 */
		if (strcmp("UPDATE contact SET ", $rq))	{
			$rq[strlen($rq)-2] = " ";
			$rq .= "WHERE contact_id = '".$contact_id."'";
			$DBRESULT = $pearDB->query($rq);

			$DBRESULT2 = $pearDB->query("SELECT contact_name FROM `contact` WHERE contact_id='".$contact_id."' LIMIT 1");
			$row = $DBRESULT2->fetchRow();
			/*
			 * Insert logs Actions
			 */
			$oreon->CentreonLogAction->insertLog("contact", $contact_id, $row["contact_name"], "mc", $fields);
		}
	}

	function updateContactHostCommands($contact_id = null, $ret = array())	{
		global $form, $pearDB;
		if (!$contact_id)
			return;
		$rq = "DELETE FROM contact_hostcommands_relation ";
		$rq .= "WHERE contact_contact_id = '".$contact_id."'";
		$DBRESULT = $pearDB->query($rq);
		if (isset($ret["contact_hostNotifCmds"]))
			$ret = $ret["contact_hostNotifCmds"];
		else
			$ret = $form->getSubmitValue("contact_hostNotifCmds");

		for ($i = 0; $i < count($ret); $i++)	{
			$rq = "INSERT INTO contact_hostcommands_relation ";
			$rq .= "(contact_contact_id, command_command_id) ";
			$rq .= "VALUES ";
			$rq .= "('".$contact_id."', '".$ret[$i]."')";
			$DBRESULT = $pearDB->query($rq);
		}
	}

	/*
	 * For massive change. We just add the new list if the elem doesn't exist yet
	 */

	function updateContactHostCommands_MC($contact_id = null, $ret = array())	{
		global $form, $pearDB;
		if (!$contact_id)
			return;
		$rq = "SELECT * FROM contact_hostcommands_relation ";
		$rq .= "WHERE contact_contact_id = '".$contact_id."'";
		$DBRESULT = $pearDB->query($rq);
		$cmds = array();
		while($arr = $DBRESULT->fetchRow())
			$cmds[$arr["command_command_id"]] = $arr["command_command_id"];
		$ret = $form->getSubmitValue("contact_hostNotifCmds");
		for ($i = 0; $i < count($ret); $i++)	{
			if (!isset($cmds[$ret[$i]]))	{
				$rq = "INSERT INTO contact_hostcommands_relation ";
				$rq .= "(contact_contact_id, command_command_id) ";
				$rq .= "VALUES ";
				$rq .= "('".$contact_id."', '".$ret[$i]."')";
				$DBRESULT = $pearDB->query($rq);
			}
		}
	}

	function updateContactServiceCommands($contact_id = null, $ret = array())	{
		global $form, $pearDB;
		if (!$contact_id)
			return;
		$rq = "DELETE FROM contact_servicecommands_relation ";
		$rq .= "WHERE contact_contact_id = '".$contact_id."'";
		$DBRESULT = $pearDB->query($rq);
		if (isset($ret["contact_svNotifCmds"]))
			$ret = $ret["contact_svNotifCmds"];
		else
			$ret = $form->getSubmitValue("contact_svNotifCmds");
		for ($i = 0; $i < count($ret); $i++)	{
			$rq = "INSERT INTO contact_servicecommands_relation ";
			$rq .= "(contact_contact_id, command_command_id) ";
			$rq .= "VALUES ";
			$rq .= "('".$contact_id."', '".$ret[$i]."')";
			$DBRESULT = $pearDB->query($rq);
		}
	}

	/*
	 * For massive change. We just add the new list if the elem doesn't exist yet
	 */
	function updateContactServiceCommands_MC($contact_id = null, $ret = array())	{
		global $form, $pearDB;
		if (!$contact_id)
			return;
		$rq = "SELECT * FROM contact_servicecommands_relation ";
		$rq .= "WHERE contact_contact_id = '".$contact_id."'";
		$DBRESULT = $pearDB->query($rq);
		$cmds = array();
		while($arr = $DBRESULT->fetchRow())
			$cmds[$arr["command_command_id"]] = $arr["command_command_id"];
		$ret = $form->getSubmitValue("contact_svNotifCmds");
		for ($i = 0; $i < count($ret); $i++)	{
			if (!isset($cmds[$ret[$i]]))	{
				$rq = "INSERT INTO contact_servicecommands_relation ";
				$rq .= "(contact_contact_id, command_command_id) ";
				$rq .= "VALUES ";
				$rq .= "('".$contact_id."', '".$ret[$i]."')";
				$DBRESULT = $pearDB->query($rq);
			}
		}
	}

	function updateContactContactGroup($contact_id = null, $ret = array())	{
		global $form, $pearDB;
		if (!$contact_id)
			return;
		$rq = "DELETE FROM contactgroup_contact_relation
			WHERE contact_contact_id = '".$contact_id."'
				AND contactgroup_cg_id IN (SELECT cg_id
					FROM contactgroup
					WHERE cg_type = 'local')";
		$DBRESULT = $pearDB->query($rq);
		if (isset($ret["contact_cgNotif"]))
			$ret = $ret["contact_cgNotif"];
		else
			$ret = $form->getSubmitValue("contact_cgNotif");
		for ($i = 0; $i < count($ret); $i++)	{
			$rq = "INSERT INTO contactgroup_contact_relation ";
			$rq .= "(contact_contact_id, contactgroup_cg_id) ";
			$rq .= "VALUES ";
			$rq .= "('".$contact_id."', '".$ret[$i]."')";
			$DBRESULT = $pearDB->query($rq);
		}
	}

	/*
	 * For massive change. We just add the new list if the elem doesn't exist yet
	 */
	function updateContactContactGroup_MC($contact_id = null, $ret = array())	{
		global $form, $pearDB;
		if (!$contact_id)
			return;
		$rq = "SELECT * FROM contactgroup_contact_relation ";
		$rq .= "WHERE contact_contact_id = '".$contact_id."'";
		$DBRESULT = $pearDB->query($rq);
		$cmds = array();
		while($arr = $DBRESULT->fetchRow())
			$cmds[$arr["contactgroup_cg_id"]] = $arr["contactgroup_cg_id"];
		$ret = $form->getSubmitValue("contact_cgNotif");

		for ($i = 0; $i < count($ret); $i++)	{
			if (!isset($cmds[$ret[$i]]))	{
				$rq = "INSERT INTO contactgroup_contact_relation ";
				$rq .= "(contact_contact_id, contactgroup_cg_id) ";
				$rq .= "VALUES ";
				$rq .= "('".$contact_id."', '".$ret[$i]."')";
				$DBRESULT = $pearDB->query($rq);
			}
		}
	}

	function insertLdapContactInDB($tmpContacts = array())	{
		global $nbr, $oreon, $pearDB;
		$tmpConf = array();
                $ldapInstances = array();
                $contactTemplates = array();
		foreach ($tmpContacts["select"] as $select_key => $select_value) {
                        $tmpContacts["contact_name"][$select_key] = str_replace(array(" ", ","), array("_", "_"), $tmpContacts["contact_name"][$select_key]);
			$arId = $tmpContacts["ar_id"][$select_key];
                        if (isset($tmpContacts["contact_name"][$select_key]) && testContactExistence($tmpContacts["contact_name"][$select_key]))	{
                            $tmpConf["contact_name"] = $tmpContacts["contact_name"][$select_key];
                            $tmpConf["contact_alias"] = $tmpContacts["contact_alias"][$select_key];
                            $tmpConf["contact_email"] = $tmpContacts["contact_email"][$select_key];
                            $tmpConf["contact_pager"] = $tmpContacts["contact_pager"][$select_key];
                            $tmpConf["contact_oreon"]["contact_oreon"] = "0";
                            $tmpConf["contact_admin"]["contact_admin"] = "0";
                            $tmpConf["contact_type_msg"] = "txt";
                            $tmpConf["contact_lang"] = "en_US";
                            $tmpConf["contact_auth_type"] = "ldap";
                            $tmpConf["contact_ldap_dn"] = $tmpContacts["dn"][$select_key];;
                            $tmpConf["contact_activate"]["contact_activate"] = "1";
                            $tmpConf["contact_comment"] = "Ldap Import - " .  date("d/m/Y - H:i:s", time());
                            $tmpConf["contact_location"] = "0";
                            $tmpConf["contact_register"] = "1";
                            insertContactInDB($tmpConf);
                            unset($tmpConf);
			}
			/*
                         * Get the contact_id
                         */
                        $query = "SELECT contact_id FROM contact WHERE contact_ldap_dn = '" . $tmpContacts["dn"][$select_key] ."'";
                        $res = $pearDB->query($query);
                        if (PEAR::isError($res)) {
                            return false;
                        }
                        $row = $res->fetchRow();
                        $contact_id = $row['contact_id'];
                        
                        if (!isset($ldapInstances[$arId])) {
                            $ldapInstances[$arId] = new CentreonLDAP($pearDB, null, $arId);
                            $ldapAdmin = new CentreonLDAPAdmin($pearDB);
                            $ldap = $ldapInstances[$arId];
                            $opt = $ldapAdmin->getGeneralOptions($arId);
                            if (isset($opt['ldap_contact_tmpl']) && $opt['ldap_contact_tmpl']) {
                                $contactTemplates[$arId] = $opt['ldap_contact_tmpl'];
                            }
                        } else {
                            $ldap = $ldapInstances[$arId];
                        }
                        $sqlUpdate = "UPDATE contact SET ar_id = ".$pearDB->escape($arId)." %s  WHERE contact_id = ".$contact_id;
                        $tmplSql = "";
                        if (isset($contactTemplates[$arId])) {
                            $tmplSql = ", contact_template_id = " . $pearDB->escape($contactTemplates[$arId]);
                        }
                        $pearDB->query(sprintf($sqlUpdate, $tmplSql));
                        $listGroup = $ldap->listGroupsForUser($tmpContacts["dn"][$select_key]);
                        if (count($listGroup) > 0) {
                            $query = "SELECT cg_id FROM contactgroup WHERE cg_name IN ('" . join("','", $listGroup) . "')";
                            $res = $pearDB->query($query);
                            if (PEAR::isError($res)) {
                                return false;
                            }
                            /*
                             * Insert the relation between contact and contact group
                             */
                            while ($row = $res->fetchRow()) {
                                $query = "INSERT INTO contactgroup_contact_relation (contactgroup_cg_id, contact_contact_id)
                                          VALUES (" . $row['cg_id'] . ", " . $contact_id . ")";
                                $pearDB->query($query);
                            }
                        }
                    }
                    return true;
	}

	/**
	 *
	 * Update ACL groups links with this user
	 * @param $contact_id
	 */
	function updateAccessGroupLinks($contact_id)	{
		global $form, $pearDB;

		if (!$contact_id) {
			return;
		}

		$ret = array();
		$ret = $form->getSubmitValues();

		/*
		 * Empty all ACL Links
		 */
		$DBRESULT = $pearDB->query("DELETE FROM acl_group_contacts_relations WHERE contact_contact_id = '".$contact_id."'");
		if (isset($ret["contact_acl_groups"])) {
			foreach ($ret["contact_acl_groups"] as $key => $value) {
				$rq = "INSERT INTO acl_group_contacts_relations ";
				$rq .= "(contact_contact_id, acl_group_id) ";
				$rq .= "VALUES ";
				$rq .= "('".$contact_id."', '".$value."')";
				$DBRESULT = $pearDB->query($rq);
			}
		}
	}

	/**
	 *
	 * Update ACL groups links with this user during massive changes
	 * @param $contact_id
	 * @param $ret
	 */
	function updateAccessGroupLinks_MC($contact_id, $flag) {
		global $form, $pearDB;

		if (!$contact_id) {
			return;
		}

		$ret = array();
		$ret = $form->getSubmitValues();

		/*
		 * Empty all ACL Links
		 */
		if ($flag) {
			$DBRESULT = $pearDB->query("DELETE FROM acl_group_contacts_relations WHERE contact_contact_id = '".$contact_id."'");
		}
		if (isset($ret["contact_acl_groups"])) {
			foreach ($ret["contact_acl_groups"] as $key => $value) {
				$rq = "INSERT INTO acl_group_contacts_relations ";
				$rq .= "(contact_contact_id, acl_group_id) ";
				$rq .= "VALUES ";
				$rq .= "('".$contact_id."', '".$value."')";
				$DBRESULT = $pearDB->query($rq);
			}
		}
	}

        /**
         * Get contact ID by name
         *
         * @param string $name
         * @return int
         */
        function getContactIdByName($name) {
            global $pearDB;

            $id = 0;
            $res = $pearDB->query("SELECT contact_id FROM contact WHERE contact_name = '".$pearDB->escape($name)."'");
            if ($res->numRows()) {
                $row = $res->fetchRow();
                $id = $row['contact_id'];
            }
            return $id;
        }
?>
