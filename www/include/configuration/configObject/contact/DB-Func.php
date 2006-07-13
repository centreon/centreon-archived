<?
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
	if (!isset ($oreon))
		exit ();

	function testContactExistence ($name = NULL)	{
		global $pearDB;
		global $form;
		$id = NULL;
		if (isset($form))
			$id = $form->getSubmitValue('contact_id');
		$res =& $pearDB->query("SELECT contact_name, contact_id FROM contact WHERE contact_name = '".htmlentities($name, ENT_QUOTES)."'");
		$contact =& $res->fetchRow();
		#Modif case
		if ($res->numRows() >= 1 && $contact["contact_id"] == $id)
			return true;
		#Duplicate entry
		else if ($res->numRows() >= 1 && $contact["contact_id"] != $id)
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
		$res =& $pearDB->query("SELECT contact_alias, contact_id FROM contact WHERE contact_alias = '".htmlentities($alias, ENT_QUOTES)."'");
		$contact =& $res->fetchRow();
		#Modif case
		if ($res->numRows() >= 1 && $contact["contact_id"] == $id)
			return true;
		#Duplicate entry
		else if ($res->numRows() >= 1 && $contact["contact_id"] != $id)
			return false;
		else
			return true;
	}

	function enableContactInDB ($contact_id = null)	{
		if (!$contact_id) return;
		global $pearDB;
		$pearDB->query("UPDATE contact SET contact_activate = '1' WHERE contact_id = '".$contact_id."'");
	}

	function disableContactInDB ($contact_id = null)	{
		if (!$contact_id) return;
		global $pearDB;
		$pearDB->query("UPDATE contact SET contact_activate = '0' WHERE contact_id = '".$contact_id."'");
	}

	function deleteContactInDB ($contacts = array())	{
		global $pearDB;
		foreach($contacts as $key=>$value)
			$pearDB->query("DELETE FROM contact WHERE contact_id = '".$key."'");
	}

	function multipleContactInDB ($contacts = array(), $nbrDup = array())	{
		foreach($contacts as $key=>$value)	{
			global $pearDB;
			$res =& $pearDB->query("SELECT * FROM contact WHERE contact_id = '".$key."' LIMIT 1");
			$row = $res->fetchRow();
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
					$pearDB->query($rq);
					$res =& $pearDB->query("SELECT MAX(contact_id) FROM contact");
					$maxId =& $res->fetchRow();
					if (isset($maxId["MAX(contact_id)"]))	{
						$res =& $pearDB->query("SELECT DISTINCT command_command_id FROM contact_hostcommands_relation WHERE contact_contact_id = '".$key."'");
						while($res->fetchInto($hostCmd))
							$pearDB->query("INSERT INTO contact_hostcommands_relation VALUES ('', '".$maxId["MAX(contact_id)"]."', '".$hostCmd["command_command_id"]."')");
						$res =& $pearDB->query("SELECT DISTINCT command_command_id FROM contact_servicecommands_relation WHERE contact_contact_id = '".$key."'");
						while($res->fetchInto($serviceCmd))
							$pearDB->query("INSERT INTO contact_servicecommands_relation VALUES ('', '".$maxId["MAX(contact_id)"]."', '".$serviceCmd["command_command_id"]."')");
						$res =& $pearDB->query("SELECT DISTINCT contactgroup_cg_id FROM contactgroup_contact_relation WHERE contact_contact_id = '".$key."'");
						while($res->fetchInto($Cg))
							$pearDB->query("INSERT INTO contactgroup_contact_relation VALUES ('', '".$maxId["MAX(contact_id)"]."', '".$Cg["contactgroup_cg_id"]."')");
					}
				}
			}
		}
	}

	function updateContactInDB ($contact_id = NULL)	{
		if (!$contact_id) return;
		updateContact($contact_id);
		updateContactHostCommands($contact_id);
		updateContactServiceCommands($contact_id);
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
		$pearDB->query($rq);
		$res =& $pearDB->query("SELECT MAX(contact_id) FROM contact");
		$contact_id = $res->fetchRow();
		return ($contact_id["MAX(contact_id)"]);
	}

	function updateContact($contact_id = null)	{
		if (!$contact_id) return;
		global $form;
		global $pearDB;
		$ret = array();
		$ret = $form->getSubmitValues();
		$rq = "UPDATE contact ";
		$rq .= "SET timeperiod_tp_id = ";
		isset($ret["timeperiod_tp_id"]) && $ret["timeperiod_tp_id"] != NULL ? $rq .= "'".$ret["timeperiod_tp_id"]."', ": $rq .= "NULL, ";
		$rq .= "timeperiod_tp_id2 = ";
		isset($ret["timeperiod_tp_id2"]) && $ret["timeperiod_tp_id2"] != NULL ? $rq .= "'".$ret["timeperiod_tp_id2"]."', ": $rq .= "NULL, ";
		$rq .= "contact_name = ";
		isset($ret["contact_name"]) && $ret["contact_name"] != NULL ? $rq .= "'".htmlentities($ret["contact_name"], ENT_QUOTES)."', ": $rq .= "NULL, ";
		$rq .= "contact_alias = ";
		isset($ret["contact_alias"]) && $ret["contact_alias"] != NULL ? $rq .= "'".htmlentities($ret["contact_alias"], ENT_QUOTES)."', ": $rq .= "NULL, ";
		if (isset($ret["contact_oreon"]["contact_oreon"]) && $ret["contact_oreon"]["contact_oreon"] && isset($ret["contact_passwd"]) && $ret["contact_passwd"])
			$rq .= "contact_passwd = '".md5($ret["contact_passwd"])."', ";
		else if (isset($ret["contact_oreon"]["contact_oreon"]) && !$ret["contact_oreon"]["contact_oreon"])
			$rq .= "contact_passwd = NULL, ";
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
		$pearDB->query($rq);
	}

	function updateContactHostCommands($contact_id = null, $ret = array())	{
		if (!$contact_id) return;
		global $form;
		global $pearDB;
		$rq = "DELETE FROM contact_hostcommands_relation ";
		$rq .= "WHERE contact_contact_id = '".$contact_id."'";
		$pearDB->query($rq);
		if (isset($ret["contact_hostNotifCmds"]))
			$ret = $ret["contact_hostNotifCmds"];
		else
			$ret = $form->getSubmitValue("contact_hostNotifCmds");
		for($i = 0; $i < count($ret); $i++)	{
			$rq = "INSERT INTO contact_hostcommands_relation ";
			$rq .= "(contact_contact_id, command_command_id) ";
			$rq .= "VALUES ";
			$rq .= "('".$contact_id."', '".$ret[$i]."')";
			$pearDB->query($rq);
		}
	}

	function updateContactServiceCommands($contact_id = null, $ret = array())	{
		if (!$contact_id) return;
		global $form;
		global $pearDB;
		$rq = "DELETE FROM contact_servicecommands_relation ";
		$rq .= "WHERE contact_contact_id = '".$contact_id."'";
		$pearDB->query($rq);
		if (isset($ret["contact_svNotifCmds"]))
			$ret = $ret["contact_svNotifCmds"];
		else
			$ret = $form->getSubmitValue("contact_svNotifCmds");
		for($i = 0; $i < count($ret); $i++)	{
			$rq = "INSERT INTO contact_servicecommands_relation ";
			$rq .= "(contact_contact_id, command_command_id) ";
			$rq .= "VALUES ";
			$rq .= "('".$contact_id."', '".$ret[$i]."')";
			$pearDB->query($rq);
		}
	}

	function updateContactContactGroup($contact_id = null, $ret = array())	{
		if (!$contact_id) return;
		global $form;
		global $pearDB;
		$rq = "DELETE FROM contactgroup_contact_relation ";
		$rq .= "WHERE contact_contact_id = '".$contact_id."'";
		$pearDB->query($rq);
		if (isset($ret["contact_cgNotif"]))
			$ret = $ret["contact_cgNotif"];
		else
			$ret = $form->getSubmitValue("contact_cgNotif");
		for($i = 0; $i < count($ret); $i++)	{
			$rq = "INSERT INTO contactgroup_contact_relation ";
			$rq .= "(contact_contact_id, contactgroup_cg_id) ";
			$rq .= "VALUES ";
			$rq .= "('".$contact_id."', '".$ret[$i]."')";
			$pearDB->query($rq);
		}
	}
?>