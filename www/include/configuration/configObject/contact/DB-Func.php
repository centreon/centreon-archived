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
 
	if (!isset ($oreon))
		exit ();

	function testContactExistence ($name = NULL)	{
		global $pearDB;
		global $form;
		$id = NULL;
		if (isset($form))
			$id = $form->getSubmitValue('contact_id');
		$DBRESULT =& $pearDB->query("SELECT contact_name, contact_id FROM contact WHERE contact_name = '".htmlentities($name, ENT_QUOTES)."'");
		if (PEAR::isError($DBRESULT))
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
		$contact =& $DBRESULT->fetchRow();
		#Modif case
		if ($DBRESULT->numRows() >= 1 && $contact["contact_id"] == $id)
			return true;
		#Duplicate entry
		else if ($DBRESULT->numRows() >= 1 && $contact["contact_id"] != $id)
			return false;
		else
			return true;
	}

	function testAliasExistence ($alias = NULL)	{
		global $pearDB;
		global $form;
		$id = NULL;
		if (isset($form))
			$id = $form->getSubmitValue('contact_id');
		$DBRESULT =& $pearDB->query("SELECT contact_alias, contact_id FROM contact WHERE contact_alias = '".htmlentities($alias, ENT_QUOTES)."'");
		if (PEAR::isError($DBRESULT))
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
		$contact =& $DBRESULT->fetchRow();
		#Modif case
		if ($DBRESULT->numRows() >= 1 && $contact["contact_id"] == $id)
			return true;
		#Duplicate entry
		else if ($DBRESULT->numRows() >= 1 && $contact["contact_id"] != $id)
			return false;
		else
			return true;
	}

	function keepOneContactAtLeast()	{
		global $pearDB;
		global $form;
		$DBRESULT =& $pearDB->query("SELECT COUNT(*) AS nbr_valid FROM contact WHERE contact_activate = '1' AND contact_oreon = '1'");
		if (PEAR::isError($DBRESULT))
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
		if (isset($form))
			$cct_oreon = $form->getSubmitValue('contact_oreon');
		else
			$cct_oreon["contact_oreon"] = 0;
		if (isset($form))
			$cct_activate = $form->getSubmitValue('contact_activate');
		else
			$cct_activate["contact_activate"] = 0;
		$contact = $DBRESULT->fetchRow();
		if ($contact["nbr_valid"] == 1 && ($cct_oreon["contact_oreon"] == 0 || $cct_activate["contact_activate"] == 0))
			return false;
		return true;
	}

	function enableContactInDB ($contact_id = null, $contact_arr = array())	{
		if (!$contact_id && !count($contact_arr)) return;
		global $pearDB;
		if ($contact_id)
			$contact_arr = array($contact_id=>"1");
		foreach($contact_arr as $key=>$value)	{
			$DBRESULT =& $pearDB->query("UPDATE contact SET contact_activate = '1' WHERE contact_id = '".$key."'");
			if (PEAR::isError($DBRESULT))
				print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
		}
	}

	function disableContactInDB ($contact_id = null, $contact_arr = array())	{
		if (!$contact_id && !count($contact_arr)) return;
		global $pearDB;
		if ($contact_id)
			$contact_arr = array($contact_id=>"1");
		foreach($contact_arr as $key=>$value)	{
			if (keepOneContactAtLeast())	{
				$DBRESULT =& $pearDB->query("UPDATE contact SET contact_activate = '0' WHERE contact_id = '".$key."'");
				if (PEAR::isError($DBRESULT))
					print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
			}
		}
	}

	function deleteContactInDB ($contacts = array())	{
		global $pearDB;
		foreach($contacts as $key=>$value)	{
			$DBRESULT =& $pearDB->query("DELETE FROM contact WHERE contact_id = '".$key."'");
			if (PEAR::isError($DBRESULT))
				print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
		}
	}

	function multipleContactInDB ($contacts = array(), $nbrDup = array())	{
		foreach($contacts as $key=>$value)	{
			global $pearDB;
			$DBRESULT =& $pearDB->query("SELECT * FROM contact WHERE contact_id = '".$key."' LIMIT 1");
			if (PEAR::isError($DBRESULT))
				print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
			$row = $DBRESULT->fetchRow();
			$row["contact_id"] = '';
			for ($i = 1; $i <= $nbrDup[$key]; $i++)	{
				$val = null;
				foreach ($row as $key2=>$value2)	{
					$key2 == "contact_name" ? ($contact_name = $value2 = $value2."_".$i) : null;
					$key2 == "contact_alias" ? ($contact_alias = $value2 = $value2."_".$i) : null;
					$val ? $val .= ($value2!=NULL?(", '".$value2."'"):", NULL") : $val .= ($value2 != NULL?("'".$value2."'"):"NULL");
				}
				if (testContactExistence($contact_name) && testAliasExistence($contact_alias))	{
					$val ? $rq = "INSERT INTO contact VALUES (".$val.")" : $rq = null;
					$DBRESULT =& $pearDB->query($rq);
					if (PEAR::isError($DBRESULT))
						print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
					$DBRESULT =& $pearDB->query("SELECT MAX(contact_id) FROM contact");
					if (PEAR::isError($DBRESULT))
						print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
					$maxId =& $DBRESULT->fetchRow();
					if (isset($maxId["MAX(contact_id)"]))	{
						$DBRESULT =& $pearDB->query("SELECT DISTINCT command_command_id FROM contact_hostcommands_relation WHERE contact_contact_id = '".$key."'");
						if (PEAR::isError($DBRESULT))
							print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
						while($DBRESULT->fetchInto($hostCmd))	{
							$DBRESULT2 =& $pearDB->query("INSERT INTO contact_hostcommands_relation VALUES ('', '".$maxId["MAX(contact_id)"]."', '".$hostCmd["command_command_id"]."')");
							if (PEAR::isError($DBRESULT2))
								print "DB Error : ".$DBRESULT2->getDebugInfo()."<br />";
						}
						$DBRESULT =& $pearDB->query("SELECT DISTINCT command_command_id FROM contact_servicecommands_relation WHERE contact_contact_id = '".$key."'");
						if (PEAR::isError($DBRESULT))
							print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
						while($DBRESULT->fetchInto($serviceCmd))	{
							$DBRESULT2 =& $pearDB->query("INSERT INTO contact_servicecommands_relation VALUES ('', '".$maxId["MAX(contact_id)"]."', '".$serviceCmd["command_command_id"]."')");
							if (PEAR::isError($DBRESULT2))
								print "DB Error : ".$DBRESULT2->getDebugInfo()."<br />";
						}
						$DBRESULT =& $pearDB->query("SELECT DISTINCT contactgroup_cg_id FROM contactgroup_contact_relation WHERE contact_contact_id = '".$key."'");
						if (PEAR::isError($DBRESULT))
							print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
						while($DBRESULT->fetchInto($Cg))	{
							$DBRESULT2 =& $pearDB->query("INSERT INTO contactgroup_contact_relation VALUES ('', '".$maxId["MAX(contact_id)"]."', '".$Cg["contactgroup_cg_id"]."')");
							if (PEAR::isError($DBRESULT2))
								print "DB Error : ".$DBRESULT2->getDebugInfo()."<br />";
						}
					}
				}
			}
		}
	}

	function updateContactInDB ($contact_id = NULL, $from_MC = false)	{
		if (!$contact_id) return;
		global $form;
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
	}
	
	function insertContactInDB ($ret = array())	{
		$contact_id = insertContact($ret);
		updateContactHostCommands($contact_id, $ret);
		updateContactServiceCommands($contact_id, $ret);
		updateContactContactGroup($contact_id, $ret);
		return ($contact_id);
	}

	function insertContact($ret = array())	{
		global $form;
		global $pearDB;
		if (!count($ret))
			$ret = $form->getSubmitValues();
		$rq = "INSERT INTO `contact` ( " .
				"`contact_id` , `timeperiod_tp_id` , `timeperiod_tp_id2` , `contact_name` , " .
				"`contact_alias` , `contact_passwd` , `contact_lang` , " .
				"`contact_host_notification_options` , `contact_service_notification_options` , " .
				"`contact_email` , `contact_pager` , `contact_comment` , `contact_oreon` , `contact_admin` , `contact_type_msg`, `contact_activate`, `contact_auth_type`, `contact_ldap_dn` )" .
				"VALUES ( ";
		$rq .= "NULL, ";
		isset($ret["timeperiod_tp_id"]) && $ret["timeperiod_tp_id"] != NULL ? $rq .= "'".$ret["timeperiod_tp_id"]."', ": $rq .= "NULL, ";
		isset($ret["timeperiod_tp_id2"]) && $ret["timeperiod_tp_id2"] != NULL ? $rq .= "'".$ret["timeperiod_tp_id2"]."', ": $rq .= "NULL, ";
		isset($ret["contact_name"]) && $ret["contact_name"] != NULL ? $rq .= "'".htmlentities($ret["contact_name"], ENT_QUOTES)."', ": $rq .= "NULL, ";
		isset($ret["contact_alias"]) && $ret["contact_alias"] != NULL ? $rq .= "'".htmlentities($ret["contact_alias"], ENT_QUOTES)."', ": $rq .= "NULL, ";
		isset($ret["contact_passwd"]) && $ret["contact_passwd"] != NULL ? $rq .= "'".md5($ret["contact_passwd"])."', ": $rq .= "NULL, ";
		isset($ret["contact_lang"]) && $ret["contact_lang"] != NULL ? $rq .= "'".htmlentities($ret["contact_lang"], ENT_QUOTES)."', ": $rq .= "NULL, ";
		isset($ret["contact_hostNotifOpts"]) && $ret["contact_hostNotifOpts"] != NULL ? $rq .= "'".implode(",", array_keys($ret["contact_hostNotifOpts"]))."', ": $rq .= "NULL, ";
		isset($ret["contact_svNotifOpts"]) && $ret["contact_svNotifOpts"] != NULL ? $rq .= "'".implode(",", array_keys($ret["contact_svNotifOpts"]))."', ": $rq .= "NULL, ";
		isset($ret["contact_email"]) && $ret["contact_email"] != NULL ? $rq .= "'".htmlentities($ret["contact_email"], ENT_QUOTES)."', ": $rq .= "NULL, ";
		isset($ret["contact_pager"]) && $ret["contact_pager"] != NULL ? $rq .= "'".htmlentities($ret["contact_pager"], ENT_QUOTES)."', ": $rq .= "NULL, ";
		isset($ret["contact_comment"]) && $ret["contact_comment"] != NULL ? $rq .= "'".htmlentities($ret["contact_comment"], ENT_QUOTES)."', ": $rq .= "NULL, ";
		isset($ret["contact_oreon"]["contact_oreon"]) && $ret["contact_oreon"]["contact_oreon"] != NULL ? $rq .= "'".$ret["contact_oreon"]["contact_oreon"]."', ": $rq .= "NULL, ";
		isset($ret["contact_admin"]["contact_admin"]) && $ret["contact_admin"]["contact_admin"] != NULL ? $rq .= "'".$ret["contact_admin"]["contact_admin"]."', ": $rq .= "NULL, ";
		isset($ret["contact_type_msg"]) && $ret["contact_type_msg"] != NULL ? $rq .= "'".$ret["contact_type_msg"]."', ": $rq .= "NULL, ";
		isset($ret["contact_activate"]["contact_activate"]) && $ret["contact_activate"]["contact_activate"] != NULL ? $rq .= "'".$ret["contact_activate"]["contact_activate"]."', ": $rq .= "NULL, ";
		isset($ret["contact_auth_type"]) && $ret["contact_auth_type"] != NULL ? $rq .= "'".$ret["contact_auth_type"]."', ": $rq .= "'local', ";
		isset($ret["contact_ldap_dn"]) && $ret["contact_ldap_dn"] != NULL ? $rq .= "'".$ret["contact_ldap_dn"]."' ": $rq .= "NULL ";
		$rq .= ")";
		$DBRESULT =& $pearDB->query($rq);
		if (PEAR::isError($DBRESULT))
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
		$DBRESULT =& $pearDB->query("SELECT MAX(contact_id) FROM contact");
		if (PEAR::isError($DBRESULT))
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
		$contact_id = $DBRESULT->fetchRow();
		return ($contact_id["MAX(contact_id)"]);
	}

	function updateContact($contact_id = null, $from_MC = false)	{
		if (!$contact_id) return;
		global $form;
		global $pearDB, $oreon;
		$ret = array();
		$ret = $form->getSubmitValues();
		$rq = "UPDATE contact ";
		$rq .= "SET timeperiod_tp_id = ";
		isset($ret["timeperiod_tp_id"]) && $ret["timeperiod_tp_id"] != NULL ? $rq .= "'".$ret["timeperiod_tp_id"]."', ": $rq .= "NULL, ";
		$rq .= "timeperiod_tp_id2 = ";
		isset($ret["timeperiod_tp_id2"]) && $ret["timeperiod_tp_id2"] != NULL ? $rq .= "'".$ret["timeperiod_tp_id2"]."', ": $rq .= "NULL, ";
		# If we are doing a MC, we don't have to set name and alias field
		if (!$from_MC)	{
			$rq .= "contact_name = ";
			isset($ret["contact_name"]) && $ret["contact_name"] != NULL ? $rq .= "'".htmlentities($ret["contact_name"], ENT_QUOTES)."', ": $rq .= "NULL, ";
			$rq .= "contact_alias = ";
			isset($ret["contact_alias"]) && $ret["contact_alias"] != NULL ? $rq .= "'".htmlentities($ret["contact_alias"], ENT_QUOTES)."', ": $rq .= "NULL, ";
		}
		if (isset($ret["contact_passwd"]) && $ret["contact_passwd"])
			$rq .= "contact_passwd = '".md5($ret["contact_passwd"])."', ";	
		$rq .=	"contact_lang = ";
		isset($ret["contact_lang"]) && $ret["contact_lang"] != NULL ? $rq .= "'".htmlentities($ret["contact_lang"], ENT_QUOTES)."', ": $rq .= "NULL, ";
		$rq .= 	"contact_host_notification_options = ";
		isset($ret["contact_hostNotifOpts"]) && $ret["contact_hostNotifOpts"] != NULL ? $rq .= "'".implode(",", array_keys($ret["contact_hostNotifOpts"]))."', ": $rq .= "NULL, ";
		$rq .= "contact_service_notification_options = ";
		isset($ret["contact_svNotifOpts"]) && $ret["contact_svNotifOpts"] != NULL ? $rq .= "'".implode(",", array_keys($ret["contact_svNotifOpts"]))."', ": $rq .= "NULL, ";
		$rq .= "contact_email = ";
		isset($ret["contact_email"]) && $ret["contact_email"] != NULL ? $rq .= "'".htmlentities($ret["contact_email"], ENT_QUOTES)."', ": $rq .= "NULL, ";
		$rq .= "contact_pager = ";
		isset($ret["contact_pager"]) && $ret["contact_pager"] != NULL ? $rq .= "'".htmlentities($ret["contact_pager"], ENT_QUOTES)."', ": $rq .= "NULL, ";
		$rq .= "contact_comment = ";
		isset($ret["contact_comment"]) && $ret["contact_comment"] != NULL ? $rq .= "'".htmlentities($ret["contact_comment"], ENT_QUOTES)."', ": $rq .= "NULL, ";
		$rq .= "contact_oreon = ";
		isset($ret["contact_oreon"]["contact_oreon"]) && $ret["contact_oreon"]["contact_oreon"] != NULL ? $rq .= "'".$ret["contact_oreon"]["contact_oreon"]."', ": $rq .= "NULL, ";
		$rq .= "contact_admin = ";
		isset($ret["contact_admin"]["contact_admin"]) && $ret["contact_admin"]["contact_admin"] != NULL ? $rq .= "'".$ret["contact_admin"]["contact_admin"]."', ": $rq .= "NULL, ";
		$rq .= "contact_type_msg = ";
		isset($ret["contact_type_msg"]) && $ret["contact_type_msg"] != NULL ? $rq .= "'".$ret["contact_type_msg"]."', ": $rq .= "NULL, ";
		$rq .= "contact_activate = ";
		isset($ret["contact_activate"]["contact_activate"]) && $ret["contact_activate"]["contact_activate"] != NULL ? $rq .= "'".$ret["contact_activate"]["contact_activate"]."', ": $rq .= "NULL, ";
		$rq .= "contact_auth_type = ";
		isset($ret["contact_auth_type"]) && $ret["contact_auth_type"] != NULL ? $rq .= "'".$ret["contact_auth_type"]."', ": $rq .= "'local', ";
		$rq .= "contact_ldap_dn = ";
		isset($ret["contact_ldap_dn"]) && $ret["contact_ldap_dn"] != NULL ? $rq .= "'".$ret["contact_ldap_dn"]."' ": $rq .= "NULL ";
		$rq .= "WHERE contact_id = '".$contact_id."'";
		$DBRESULT =& $pearDB->query($rq);
		if (PEAR::isError($DBRESULT))
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
		if (isset($ret["contact_lang"]) && $ret["contact_lang"] != NULL && $contact_id == $oreon->user->get_id()) {
			$oreon->user->set_lang($ret["contact_lang"]);
		}
	}

	function updateContact_MC($contact_id = null)	{
		if (!$contact_id) return;
		global $form;
		global $pearDB;
		$ret = array();
		$ret = $form->getSubmitValues();
		$rq = "UPDATE contact SET ";
		if (isset($ret["timeperiod_tp_id"]) && $ret["timeperiod_tp_id"] != NULL) $rq .= "timeperiod_tp_id = '".$ret["timeperiod_tp_id"]."', ";
		if (isset($ret["timeperiod_tp_id2"]) && $ret["timeperiod_tp_id2"] != NULL) $rq .= "timeperiod_tp_id2 = '".$ret["timeperiod_tp_id2"]."', ";
		if (isset($ret["contact_passwd"]) && $ret["contact_passwd"])
			$rq .= "contact_passwd = '".md5($ret["contact_passwd"])."', ";
		if (isset($ret["contact_lang"]) && $ret["contact_lang"] != NULL) $rq .= "contact_lang = '".htmlentities($ret["contact_lang"], ENT_QUOTES)."', ";
		if (isset($ret["contact_hostNotifOpts"]) && $ret["contact_hostNotifOpts"] != NULL) $rq .= "contact_host_notification_options = '".implode(",", array_keys($ret["contact_hostNotifOpts"]))."', ";
		if (isset($ret["contact_svNotifOpts"]) && $ret["contact_svNotifOpts"] != NULL) $rq .= "contact_service_notification_options = '".implode(",", array_keys($ret["contact_svNotifOpts"]))."', ";
		if (isset($ret["contact_email"]) && $ret["contact_email"] != NULL) $rq .= "contact_email = '".htmlentities($ret["contact_email"], ENT_QUOTES)."', ";
		if (isset($ret["contact_pager"]) && $ret["contact_pager"] != NULL) $rq .= "contact_pager = '".htmlentities($ret["contact_pager"], ENT_QUOTES)."', ";
		if (isset($ret["contact_comment"]) && $ret["contact_comment"] != NULL) $rq .= "contact_comment = '".htmlentities($ret["contact_comment"], ENT_QUOTES)."', ";
		if (isset($ret["contact_oreon"]["contact_oreon"]) && $ret["contact_oreon"]["contact_oreon"] != NULL) $rq .= "contact_oreon = '".$ret["contact_oreon"]["contact_oreon"]."', "; 
		if (isset($ret["contact_admin"]["contact_admin"]) && $ret["contact_admin"]["contact_admin"] != NULL) $rq .= "contact_admin = '".$ret["contact_admin"]["contact_admin"]."', ";
		if (isset($ret["contact_type_msg"]) && $ret["contact_type_msg"] != NULL) $rq .= "contact_type_msg = '".$ret["contact_type_msg"]."', ";
		if (isset($ret["contact_activate"]["contact_activate"]) && $ret["contact_activate"]["contact_activate"] != NULL) $rq .= "contact_activate = '".$ret["contact_activate"]["contact_activate"]."', ";
		if (isset($ret["contact_auth_type"]) && $ret["contact_auth_type"] != NULL) $rq .= "contact_auth_type = '".$ret["contact_auth_type"]."', ";
		if (isset($ret["contact_ldap_dn"]) && $ret["contact_ldap_dn"] != NULL) $rq .= "contact_ldap_dn = '".$ret["contact_ldap_dn"]."', ";
		if (strcmp("UPDATE contact SET ", $rq))	{
			# Delete last ',' in request
			$rq[strlen($rq)-2] = " ";
			$rq .= "WHERE contact_id = '".$contact_id."'";
			$DBRESULT =& $pearDB->query($rq);
			if (PEAR::isError($DBRESULT))
				print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
		}
	}

	function updateContactHostCommands($contact_id = null, $ret = array())	{
		if (!$contact_id) return;
		global $form;
		global $pearDB;
		$rq = "DELETE FROM contact_hostcommands_relation ";
		$rq .= "WHERE contact_contact_id = '".$contact_id."'";
		$DBRESULT =& $pearDB->query($rq);
		if (PEAR::isError($DBRESULT))
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
		if (isset($ret["contact_hostNotifCmds"]))
			$ret = $ret["contact_hostNotifCmds"];
		else
			$ret = $form->getSubmitValue("contact_hostNotifCmds");
		for($i = 0; $i < count($ret); $i++)	{
			$rq = "INSERT INTO contact_hostcommands_relation ";
			$rq .= "(contact_contact_id, command_command_id) ";
			$rq .= "VALUES ";
			$rq .= "('".$contact_id."', '".$ret[$i]."')";
			$DBRESULT =& $pearDB->query($rq);
			if (PEAR::isError($DBRESULT))
				print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
		}
	}

	# For massive change. We just add the new list if the elem doesn't exist yet
	function updateContactHostCommands_MC($contact_id = null, $ret = array())	{
		if (!$contact_id) return;
		global $form;
		global $pearDB;
		$rq = "SELECT * FROM contact_hostcommands_relation ";
		$rq .= "WHERE contact_contact_id = '".$contact_id."'";
		$DBRESULT =& $pearDB->query($rq);
		if (PEAR::isError($DBRESULT))
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
		$cmds = array();
		while($DBRESULT->fetchInto($arr))
			$cmds[$arr["command_command_id"]] = $arr["command_command_id"];
		$ret = $form->getSubmitValue("contact_hostNotifCmds");
		for($i = 0; $i < count($ret); $i++)	{
			if (!isset($cmds[$ret[$i]]))	{
				$rq = "INSERT INTO contact_hostcommands_relation ";
				$rq .= "(contact_contact_id, command_command_id) ";
				$rq .= "VALUES ";
				$rq .= "('".$contact_id."', '".$ret[$i]."')";
				$DBRESULT =& $pearDB->query($rq);
				if (PEAR::isError($DBRESULT))
					print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
			}
		}
	}

	function updateContactServiceCommands($contact_id = null, $ret = array())	{
		if (!$contact_id) return;
		global $form;
		global $pearDB;
		$rq = "DELETE FROM contact_servicecommands_relation ";
		$rq .= "WHERE contact_contact_id = '".$contact_id."'";
		$DBRESULT =& $pearDB->query($rq);
		if (PEAR::isError($DBRESULT))
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
		if (isset($ret["contact_svNotifCmds"]))
			$ret = $ret["contact_svNotifCmds"];
		else
			$ret = $form->getSubmitValue("contact_svNotifCmds");
		for($i = 0; $i < count($ret); $i++)	{
			$rq = "INSERT INTO contact_servicecommands_relation ";
			$rq .= "(contact_contact_id, command_command_id) ";
			$rq .= "VALUES ";
			$rq .= "('".$contact_id."', '".$ret[$i]."')";
			$DBRESULT =& $pearDB->query($rq);
			if (PEAR::isError($DBRESULT))
				print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
		}
	}
	
	# For massive change. We just add the new list if the elem doesn't exist yet
	function updateContactServiceCommands_MC($contact_id = null, $ret = array())	{
		if (!$contact_id) return;
		global $form;
		global $pearDB;
		$rq = "SELECT * FROM contact_servicecommands_relation ";
		$rq .= "WHERE contact_contact_id = '".$contact_id."'";
		$DBRESULT =& $pearDB->query($rq);
		if (PEAR::isError($DBRESULT))
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
		$cmds = array();
		while($DBRESULT->fetchInto($arr))
			$cmds[$arr["command_command_id"]] = $arr["command_command_id"];
		$ret = $form->getSubmitValue("contact_svNotifCmds");
		for($i = 0; $i < count($ret); $i++)	{
			if (!isset($cmds[$ret[$i]]))	{
				$rq = "INSERT INTO contact_servicecommands_relation ";
				$rq .= "(contact_contact_id, command_command_id) ";
				$rq .= "VALUES ";
				$rq .= "('".$contact_id."', '".$ret[$i]."')";
				$DBRESULT =& $pearDB->query($rq);
				if (PEAR::isError($DBRESULT))
					print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
			}
		}
	}

	function updateContactContactGroup($contact_id = null, $ret = array())	{
		if (!$contact_id) return;
		global $form;
		global $pearDB;
		$rq = "DELETE FROM contactgroup_contact_relation ";
		$rq .= "WHERE contact_contact_id = '".$contact_id."'";
		$DBRESULT =& $pearDB->query($rq);
		if (PEAR::isError($DBRESULT))
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
		if (isset($ret["contact_cgNotif"]))
			$ret = $ret["contact_cgNotif"];
		else
			$ret = $form->getSubmitValue("contact_cgNotif");
		for($i = 0; $i < count($ret); $i++)	{
			$rq = "INSERT INTO contactgroup_contact_relation ";
			$rq .= "(contact_contact_id, contactgroup_cg_id) ";
			$rq .= "VALUES ";
			$rq .= "('".$contact_id."', '".$ret[$i]."')";
			$DBRESULT =& $pearDB->query($rq);
			if (PEAR::isError($DBRESULT))
				print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
		}
	}

	# For massive change. We just add the new list if the elem doesn't exist yet
	function updateContactContactGroup_MC($contact_id = null, $ret = array())	{
		if (!$contact_id) return;
		global $form;
		global $pearDB;
		$rq = "SELECT * FROM contactgroup_contact_relation ";
		$rq .= "WHERE contact_contact_id = '".$contact_id."'";
		$DBRESULT =& $pearDB->query($rq);
		if (PEAR::isError($DBRESULT))
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
		$cmds = array();
		while($DBRESULT->fetchInto($arr))
			$cmds[$arr["contactgroup_cg_id"]] = $arr["contactgroup_cg_id"];
		$ret = $form->getSubmitValue("contact_cgNotif");
		for($i = 0; $i < count($ret); $i++)	{
			if (!isset($cmds[$ret[$i]]))	{
				$rq = "INSERT INTO contactgroup_contact_relation ";
				$rq .= "(contact_contact_id, contactgroup_cg_id) ";
				$rq .= "VALUES ";
				$rq .= "('".$contact_id."', '".$ret[$i]."')";
				$DBRESULT =& $pearDB->query($rq);
				if (PEAR::isError($DBRESULT))
					print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
			}
		}
	}

	function insertLdapContactInDB($tmpContacts = array())	{
		global $nbr;
		global $oreon;
		$tmpConf = array();
		foreach ($tmpContacts["select"] as $select_key=>$select_value) {
			$tmpContacts["contact_name"][$select_key] = str_replace(" ", "_", $tmpContacts["contact_name"][$select_key]);
			if (isset($tmpContacts["contact_name"][$select_key]) && testContactExistence($tmpContacts["contact_name"][$select_key]))	{
				$tmpConf["contact_name"] = $tmpContacts["contact_name"][$select_key];
				$tmpConf["contact_alias"] = $tmpContacts["contact_alias"][$select_key];
				$tmpConf["contact_email"] = $tmpContacts["contact_email"][$select_key];
				$tmpConf["contact_oreon"]["contact_oreon"] = "0";
				$tmpConf["contact_admin"]["contact_admin"] = "0";
				$tmpConf["contact_type_msg"] = "txt";
				$tmpConf["contact_lang"] = "en";
				$tmpConf["contact_auth_type"] = "ldap";
				$tmpConf["contact_ldap_dn"] = $tmpContacts["dn"][$select_key];;
				$tmpConf["contact_activate"]["contact_activate"] = "1";
				$tmpConf["contact_comment"] = "Ldap Import - " .  date("d/m/Y - H:i:s", time());
				insertContactInDB($tmpConf);
				unset($tmpConf);
			}
		}
		return false;
	}
?>