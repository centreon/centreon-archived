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
	
	function testExistence ($name = NULL)	{
		global $pearDB;
		global $form;
		$id = NULL;
		if (isset($form))
			$id = $form->getSubmitValue('esc_id');
		$res =& $pearDB->query("SELECT esc_name, esc_id FROM escalation WHERE esc_name = '".$name."'");
		if (PEAR::isError($pearDB)) {
			print "Mysql Error : ".$pearDB->getMessage();
		}
		$esc =& $res->fetchRow();
		#Modif case
		if ($res->numRows() >= 1 && $esc["esc_id"] == $id)	
			return true;
		#Duplicate entry
		else if ($res->numRows() >= 1 && $esc["esc_id"] != $id)
			return false;
		else
			return true;
	}
		
	function deleteEscalationInDB ($escalations = array())	{
		global $pearDB;
		foreach($escalations as $key=>$value)
		{
			$pearDB->query("DELETE FROM escalation WHERE esc_id = '".$key."'");
			if (PEAR::isError($pearDB)) {
				print "Mysql Error : ".$pearDB->getMessage();
			}
		}
	}
	
	function multipleEscalationInDB ($escalations = array(), $nbrDup = array())	{
		foreach($escalations as $key=>$value)	{
			global $pearDB;
			$res =& $pearDB->query("SELECT * FROM escalation WHERE esc_id = '".$key."' LIMIT 1");
			if (PEAR::isError($pearDB)) {
				print "Mysql Error : ".$pearDB->getMessage();
			}
			$row = $res->fetchRow();
			$row["esc_id"] = '';
			for ($i = 1; $i <= $nbrDup[$key]; $i++)	{
				$val = null;
				foreach ($row as $key2=>$value2)	{
					$key2 == "esc_name" ? ($esc_name = $value2 = $value2." ".$i) : null;
					$val ? $val .= ($value2!=NULL?(", '".$value2."'"):", NULL") : $val .= ($value2!=NULL?("'".$value2."'"):"NULL");
				}
				if (testExistence($esc_name))	{
					$val ? $rq = "INSERT INTO escalation VALUES (".$val.")" : $rq = null;
					$pearDB->query($rq);
					if (PEAR::isError($pearDB)) {
						print "Mysql Error : ".$pearDB->getMessage();
					}
					$res =& $pearDB->query("SELECT MAX(esc_id) FROM escalation");
					if (PEAR::isError($pearDB)) {
						print "Mysql Error : ".$pearDB->getMessage();
					}
					$maxId =& $res->fetchRow();
					if (isset($maxId["MAX(esc_id)"]))	{
						$res =& $pearDB->query("SELECT DISTINCT contactgroup_cg_id FROM escalation_contactgroup_relation WHERE escalation_esc_id = '".$key."'");
						if (PEAR::isError($pearDB)) {
							print "Mysql Error : ".$pearDB->getMessage();
						}
						while($res->fetchInto($cg))
						{
							$pearDB->query("INSERT INTO escalation_contactgroup_relation VALUES ('', '".$maxId["MAX(esc_id)"]."', '".$cg["contactgroup_cg_id"]."')");
							if (PEAR::isError($pearDB)) {
								print "Mysql Error : ".$pearDB->getMessage();
							}
						}
						$res =& $pearDB->query("SELECT DISTINCT host_host_id FROM escalation_host_relation WHERE escalation_esc_id = '".$key."'");
						if (PEAR::isError($pearDB)) {
							print "Mysql Error : ".$pearDB->getMessage();
						}
						while($res->fetchInto($host))
						{
							$pearDB->query("INSERT INTO escalation_host_relation VALUES ('', '".$maxId["MAX(esc_id)"]."', '".$host["host_host_id"]."')");
							if (PEAR::isError($pearDB)) {
								print "Mysql Error : ".$pearDB->getMessage();
							}
						}
						$res =& $pearDB->query("SELECT DISTINCT hostgroup_hg_id FROM escalation_hostgroup_relation WHERE escalation_esc_id = '".$key."'");
						while($res->fetchInto($hg))
						{
							$pearDB->query("INSERT INTO escalation_hostgroup_relation VALUES ('', '".$maxId["MAX(esc_id)"]."', '".$hg["hostgroup_hg_id"]."')");
							if (PEAR::isError($pearDB)) {
								print "Mysql Error : ".$pearDB->getMessage();
							}
						}
						$res =& $pearDB->query("SELECT * FROM escalation_service_relation WHERE escalation_esc_id = '".$key."'");
						while($res->fetchInto($sv))
						{
							$pearDB->query("INSERT INTO escalation_service_relation VALUES ('', '".$maxId["MAX(esc_id)"]."', '".$sv["service_service_id"]."', '".$sv["host_host_id"]."')");
							if (PEAR::isError($pearDB)) {
								print "Mysql Error : ".$pearDB->getMessage();
							}
						}
						$res =& $pearDB->query("SELECT DISTINCT meta_service_meta_id FROM escalation_meta_service_relation WHERE escalation_esc_id = '".$key."'");
						while($res->fetchInto($sv))
						{
							$pearDB->query("INSERT INTO escalation_meta_service_relation VALUES ('', '".$maxId["MAX(esc_id)"]."', '".$sv["meta_service_meta_id"]."')");
							if (PEAR::isError($pearDB)) {
								print "Mysql Error : ".$pearDB->getMessage();
							}
						}
					}
				}
			}
		}
	}
	
	function updateEscalationInDB ($esc_id = NULL)	{
		if (!$esc_id) exit();
		updateEscalation($esc_id);
		updateEscalationContactGroups($esc_id);
		updateEscalationHosts($esc_id);
		updateEscalationHostGroups($esc_id);
		updateEscalationServices($esc_id);
		updateEscalationMetaServices($esc_id);
	}	
	
	function insertEscalationInDB ()	{
		$esc_id = insertEscalation();
		updateEscalationContactGroups($esc_id);
		updateEscalationHosts($esc_id);
		updateEscalationHostGroups($esc_id);
		updateEscalationServices($esc_id);
		updateEscalationMetaServices($esc_id);
		return ($esc_id);
	}
	
	function insertEscalation()	{
		global $form;
		global $pearDB;
		$ret = array();
		$ret = $form->getSubmitValues();
		$rq = "INSERT INTO escalation ";
		$rq .= "(esc_name, first_notification, last_notification, notification_interval, escalation_period, escalation_options1, escalation_options2, esc_comment) ";
		$rq .= "VALUES (";
		isset($ret["esc_name"]) && $ret["esc_name"] != NULL ? $rq .= "'".htmlentities($ret["esc_name"], ENT_QUOTES)."', " : $rq .= "NULL, ";
		isset($ret["first_notification"]) && $ret["first_notification"] != NULL ? $rq .= "'".htmlentities($ret["first_notification"], ENT_QUOTES)."', " : $rq .= "NULL, ";
		isset($ret["last_notification"]) && $ret["last_notification"] != NULL ? $rq .= "'".htmlentities($ret["last_notification"], ENT_QUOTES)."', " : $rq .= "NULL, ";
		isset($ret["notification_interval"]) && $ret["notification_interval"] != NULL ? $rq .= "'".htmlentities($ret["notification_interval"], ENT_QUOTES)."', " : $rq .= "NULL, ";
		isset($ret["escalation_period"]) && $ret["escalation_period"] != NULL ? $rq .= "'".htmlentities($ret["escalation_period"], ENT_QUOTES)."', " : $rq .= "NULL, ";
		isset($ret["escalation_options1"]) && $ret["escalation_options1"] != NULL ? $rq .= "'".implode(",", array_keys($ret["escalation_options1"]))."', " : $rq .= "NULL, ";
		isset($ret["escalation_options2"]) && $ret["escalation_options2"] != NULL ? $rq .= "'".implode(",", array_keys($ret["escalation_options2"]))."', " : $rq .= "NULL, ";
		isset($ret["esc_comment"]) && $ret["esc_comment"] != NULL ? $rq .= "'".htmlentities($ret["esc_comment"], ENT_QUOTES)."' " : $rq .= "NULL ";
		$rq .= ")";
		$pearDB->query($rq);
		$res =& $pearDB->query("SELECT MAX(esc_id) FROM escalation");
		if (PEAR::isError($pearDB)) {
			print "Mysql Error : ".$pearDB->getMessage();
		}
		$esc_id = $res->fetchRow();
		return ($esc_id["MAX(esc_id)"]);
	}
	
	function updateEscalation($esc_id = null)	{
		if (!$esc_id) exit();
		global $form;
		global $pearDB;
		$ret = array();
		$ret = $form->getSubmitValues();
		$rq = "UPDATE escalation SET ";
		$rq .= "esc_name = ";
		isset($ret["esc_name"]) && $ret["esc_name"] != NULL ? $rq .= "'".htmlentities($ret["esc_name"], ENT_QUOTES)."', " : $rq .= "NULL, ";
		$rq .= "first_notification = ";
		isset($ret["first_notification"]) && $ret["first_notification"] != NULL ? $rq .= "'".htmlentities($ret["first_notification"], ENT_QUOTES)."', " : $rq .= "NULL, ";
		$rq .= "last_notification = ";
		isset($ret["last_notification"]) && $ret["last_notification"] != NULL ? $rq .= "'".htmlentities($ret["last_notification"], ENT_QUOTES)."', " : $rq .= "NULL, ";
		$rq .= "notification_interval = ";
		isset($ret["notification_interval"]) && $ret["notification_interval"] != NULL ? $rq .= "'".htmlentities($ret["notification_interval"], ENT_QUOTES)."', " : $rq .= "NULL, ";
		$rq .= "escalation_period = ";
		isset($ret["escalation_period"]) && $ret["escalation_period"] != NULL ? $rq .= "'".htmlentities($ret["escalation_period"], ENT_QUOTES)."', " : $rq .= "NULL, ";
		$rq .= "escalation_options1 = ";
		isset($ret["escalation_options1"]) && $ret["escalation_options1"] != NULL ? $rq .= "'".implode(",", array_keys($ret["escalation_options1"]))."', " : $rq .= "NULL, ";
		$rq .= "escalation_options2 = ";
		isset($ret["escalation_options2"]) && $ret["escalation_options2"] != NULL ? $rq .= "'".implode(",", array_keys($ret["escalation_options2"]))."', " : $rq .= "NULL, ";
		$rq .= "esc_comment = ";
		isset($ret["esc_comment"]) && $ret["esc_comment"] != NULL ? $rq .= "'".htmlentities($ret["esc_comment"], ENT_QUOTES)."' " : $rq .= "NULL ";
		$rq .= "WHERE esc_id = '".$esc_id."'";
		$pearDB->query($rq);
		if (PEAR::isError($pearDB)) {
			print "Mysql Error : ".$pearDB->getMessage();
		}
	}
	
	function updateEscalationContactGroups($esc_id = null)	{
		if (!$esc_id) exit();
		global $form;
		global $pearDB;
		$rq = "DELETE FROM escalation_contactgroup_relation ";
		$rq .= "WHERE escalation_esc_id = '".$esc_id."'";
		$pearDB->query($rq);
		if (PEAR::isError($pearDB)) {
			print "Mysql Error : ".$pearDB->getMessage();
		}
		$ret = array();
		$ret = $form->getSubmitValue("esc_cgs");
		for($i = 0; $i < count($ret); $i++)	{
			$rq = "INSERT INTO escalation_contactgroup_relation ";
			$rq .= "(escalation_esc_id, contactgroup_cg_id) ";
			$rq .= "VALUES ";
			$rq .= "('".$esc_id."', '".$ret[$i]."')";
			$pearDB->query($rq);
			if (PEAR::isError($pearDB)) {
				print "Mysql Error : ".$pearDB->getMessage();
			}
		}
	}
	
	function updateEscalationHosts($esc_id = null)	{
		if (!$esc_id) exit();
		global $form;
		global $pearDB;
		$rq = "DELETE FROM escalation_host_relation ";
		$rq .= "WHERE escalation_esc_id = '".$esc_id."'";
		$pearDB->query($rq);
		if (PEAR::isError($pearDB)) {
			print "Mysql Error : ".$pearDB->getMessage();
		}
		$ret = array();
		$ret = $form->getSubmitValue("esc_hosts");
		for($i = 0; $i < count($ret); $i++)	{
			$rq = "INSERT INTO escalation_host_relation ";
			$rq .= "(escalation_esc_id, host_host_id) ";
			$rq .= "VALUES ";
			$rq .= "('".$esc_id."', '".$ret[$i]."')";
			$pearDB->query($rq);
			if (PEAR::isError($pearDB)) {
				print "Mysql Error : ".$pearDB->getMessage();
			}
		}
	}
	
	function updateEscalationHostGroups($esc_id = null)	{
		if (!$esc_id) exit();
		global $form;
		global $pearDB;
		$rq = "DELETE FROM escalation_hostgroup_relation ";
		$rq .= "WHERE escalation_esc_id = '".$esc_id."'";
		$pearDB->query($rq);
		if (PEAR::isError($pearDB)) {
			print "Mysql Error : ".$pearDB->getMessage();
		}
		$ret = array();
		$ret = $form->getSubmitValue("esc_hgs");
		for($i = 0; $i < count($ret); $i++)	{
			$rq = "INSERT INTO escalation_hostgroup_relation ";
			$rq .= "(escalation_esc_id, hostgroup_hg_id) ";
			$rq .= "VALUES ";
			$rq .= "('".$esc_id."', '".$ret[$i]."')";
			$pearDB->query($rq);
			if (PEAR::isError($pearDB)) {
				print "Mysql Error : ".$pearDB->getMessage();
			}
		}
	}
	
	function updateEscalationServices($esc_id = null)	{
		if (!$esc_id) exit();
		global $form;
		global $pearDB;
		$rq = "DELETE FROM escalation_service_relation ";
		$rq .= "WHERE escalation_esc_id = '".$esc_id."'";
		$pearDB->query($rq);
		if (PEAR::isError($pearDB)) {
			print "Mysql Error : ".$pearDB->getMessage();
		}
		$ret = array();
		$ret = $form->getSubmitValue("esc_hServices");
		for($i = 0; $i < count($ret); $i++)	{
			$exp = explode("_", $ret[$i]);
			if (count($exp) == 2)	{
				$rq = "INSERT INTO escalation_service_relation ";
				$rq .= "(escalation_esc_id, service_service_id, host_host_id) ";
				$rq .= "VALUES ";
				$rq .= "('".$esc_id."', '".$exp[1]."', '".$exp[0]."')";
				$pearDB->query($rq);
				if (PEAR::isError($pearDB)) {
					print "Mysql Error : ".$pearDB->getMessage();
				}
			}
		}
	}
	
	function updateEscalationMetaServices($esc_id = null)	{
		if (!$esc_id) exit();
		global $form;
		global $pearDB;
		$rq = "DELETE FROM escalation_meta_service_relation ";
		$rq .= "WHERE escalation_esc_id = '".$esc_id."'";
		$pearDB->query($rq);
		if (PEAR::isError($pearDB)) {
			print "Mysql Error : ".$pearDB->getMessage();
		}
		$ret = array();
		$ret = $form->getSubmitValue("esc_metas");
		for($i = 0; $i < count($ret); $i++)	{
			$rq = "INSERT INTO escalation_meta_service_relation ";
			$rq .= "(escalation_esc_id, meta_service_meta_id) ";
			$rq .= "VALUES ";
			$rq .= "('".$esc_id."', '".$ret[$i]."')";
			$pearDB->query($rq);
			if (PEAR::isError($pearDB)) {
				print "Mysql Error : ".$pearDB->getMessage();
			}
		}
	}
?>