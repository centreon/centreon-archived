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
		
	function testHostExistence ($name = NULL)	{
		global $pearDB;
		global $form;
		$id = NULL;
		if (isset($form))
			$id = $form->getSubmitValue('host_id');
		$DBRESULT =& $pearDB->query("SELECT host_name, host_id FROM host WHERE host_name = '".htmlentities($name, ENT_QUOTES)."'");
		if (PEAR::isError($DBRESULT))
			print "DB Error : SELECT host_name, host_id FROM host WHERE host_name = '".htmlentities($name, ENT_QUOTES)."' : ".$DBRESULT->getMessage()."<br>";
		$host =& $DBRESULT->fetchRow();
		#Modif case
		if ($DBRESULT->numRows() >= 1 && $host["host_id"] == $id)	
			return true;
		#Duplicate entry
		else if ($DBRESULT->numRows() >= 1 && $host["host_id"] != $id)
			return false;
		else
			return true;
	}
	
	function enableHostInDB ($host_id = null)	{
		if (!$host_id) return;
		global $pearDB;
		$DBRESULT =& $pearDB->query("UPDATE host SET host_activate = '1' WHERE host_id = '".$host_id."'");
		if (PEAR::isError($DBRESULT))
			print "DB Error : UPDATE host SET host_activate = '1' WHERE host_id = '".$host_id."' : ".$DBRESULT->getMessage()."<br>"."<br>";
	}
	
	function disableHostInDB ($host_id = null)	{
		if (!$host_id) return;
		global $pearDB;
		$DBRESULT =& $pearDB->query("UPDATE host SET host_activate = '0' WHERE host_id = '".$host_id."'");
		if (PEAR::isError($DBRESULT))
			print "DB Error : UPDATE host SET host_activate = '0' WHERE host_id = '".$host_id."' : ".$DBRESULT->getMessage()."<br>";
	}
	
	function deleteHostInDB ($hosts = array())	{
		global $pearDB, $oreon;
		foreach($hosts as $key=>$value)	{
			$rq = "SELECT @nbr := (SELECT COUNT( * ) FROM host_service_relation WHERE service_service_id = hsr.service_service_id GROUP BY service_service_id ) AS nbr, hsr.service_service_id FROM host_service_relation hsr, host WHERE hsr.host_host_id = '".$key."' AND host.host_id = hsr.host_host_id AND host.host_register = '1'";
			$DBRESULT = & $pearDB->query($rq);
			if (PEAR::isError($DBRESULT))
				print "DB Error : SELECT @nbr := (SELECT COUNT( * ) FROM host_service_relation.. : ".$DBRESULT->getMessage()."<br>";
			while ($DBRESULT->fetchInto($row))
				if ($row["nbr"] == 1)	{
					$DBRESULT2 =& $pearDB->query("DELETE FROM service WHERE service_id = '".$row["service_service_id"]."'");
					if (PEAR::isError($DBRESULT2))
						print "DB Error : DELETE FROM service WHERE service_id = '".$row["service_service_id"]."' : ".$DBRESULT2->getMessage()."<br>";
				}
			$DBRESULT =& $pearDB->query("DELETE FROM host WHERE host_id = '".$key."'");
			if (PEAR::isError($DBRESULT)) 
				print "DB Error : DELETE FROM host WHERE host_id = '".$key."' : ".$DBRESULT->getMessage()."<br>";
			$files = glob($oreon->optGen["oreon_rrdbase_path"].$key."_*.rrd");
			foreach ($files as $filename)
				unlink ($filename);
		}
	}
	
	function multipleHostInDB ($hosts = array(), $nbrDup = array())	{
		foreach($hosts as $key=>$value)	{
			global $pearDB;
			global $path;
			global $oreon;
			$DBRESULT =& $pearDB->query("SELECT * FROM host WHERE host_id = '".$key."' LIMIT 1");
			if (PEAR::isError($DBRESULT))
				print "DB Error : SELECT * FROM host WHERE host_id = '".$key."' LIMIT 1 : ".$DBRESULT->getMessage()."<br>";
			$row = $DBRESULT->fetchRow();
			$row["host_id"] = '';
			for ($i = 1; $i <= $nbrDup[$key]; $i++)	{
				$val = null;
				foreach ($row as $key2=>$value2)	{
					$key2 == "host_name" ? ($host_name = $value2 = $value2."_".$i) : null;
					$val ? $val .= ($value2!=NULL?(", '".$value2."'"):", NULL") : $val .= ($value2!=NULL?("'".$value2."'"):"NULL");
				}
				if (testHostExistence($host_name))	{
					$val ? $rq = "INSERT INTO host VALUES (".$val.")" : $rq = null;
					$DBRESULT =& $pearDB->query($rq);
					if (PEAR::isError($DBRESULT))
						print "DB Error : INSERT INTO host VALUES (".$val.") : ".$DBRESULT->getMessage()."<br>";
					$DBRESULT =& $pearDB->query("SELECT MAX(host_id) FROM host");
					if (PEAR::isError($DBRESULT))
						print "DB Error : SELECT MAX(host_id) FROM host : ".$DBRESULT->getMessage()."<br>";
					$maxId =& $DBRESULT->fetchRow();
					if (isset($maxId["MAX(host_id)"]))	{
						# Update LCA
						$DBRESULT1 =& $pearDB->query("SELECT contactgroup_cg_id FROM contactgroup_contact_relation WHERE contact_contact_id = '".$oreon->user->get_id()."'");
						if (PEAR::isError($DBRESULT1))
							print "DB Error : SELECT contactgroup_cg_id FROM contactgroup_contact_relation.. : ".$DBRESULT1->getMessage()."<br>";
						while($DBRESULT1->fetchInto($contactGroup))	{
						 	$DBRESULT2 =& $pearDB->query("SELECT lca_define_lca_id FROM lca_define_contactgroup_relation ldcgr WHERE ldcgr.contactgroup_cg_id = '".$contactGroup["contactgroup_cg_id"]."'");	
							if (PEAR::isError($DBRESULT2))
								print "DB Error : SELECT lca_define_lca_id FROM lca_define_contactgroup_relation ldcgr.. : ".$DBRESULT2->getMessage()."<br>";
							while ($DBRESULT2->fetchInto($lca))	{
								$rq = "INSERT INTO lca_define_host_relation ";
								$rq .= "(lca_define_lca_id, host_host_id) ";
								$rq .= "VALUES ";
								$rq .= "('".$lca["lca_define_lca_id"]."', '".$maxId["MAX(host_id)"]."')";
								$DBRESULT3 =& $pearDB->query($rq);
								if (PEAR::isError($DBRESULT3))
									print "DB Error : INSERT INTO lca_define_host_relation.. : ".$DBRESULT3->getMessage()."<br>";
							}
						}
						#
						$DBRESULT =& $pearDB->query("SELECT DISTINCT host_parent_hp_id FROM host_hostparent_relation WHERE host_host_id = '".$key."'");
						if (PEAR::isError($DBRESULT))
							print "DB Error : SELECT DISTINCT host_parent_hp_id FROM host_hostparent_relation.. ".$DBRESULT->getMessage()."<br>";
						while($DBRESULT->fetchInto($host)){
							$DBRESULT1 =& $pearDB->query("INSERT INTO host_hostparent_relation VALUES ('', '".$host["host_parent_hp_id"]."', '".$maxId["MAX(host_id)"]."')");	
							if (PEAR::isError($DBRESULT1))
								print "DB Error : INSERT INTO host_hostparent_relation VALUES.. ".$DBRESULT1->getMessage()."<br>";
						}
						# We need to duplicate the entire Service and not only create a new relation for it in the DB / Need Service functions
						require_once($path."../service/DB-Func.php");
						$hostInf = $maxId["MAX(host_id)"];
						$serviceArr = array();
						$serviceNbr = array();
						# Get all Services link to the Host
						$DBRESULT =& $pearDB->query("SELECT DISTINCT service_service_id FROM host_service_relation WHERE host_host_id = '".$key."'");
						if (PEAR::isError($DBRESULT))
							print "DB Error : SELECT DISTINCT service_service_id FROM host_service_relation WHERE host_host_id = '".$key."'.. : ".$DBRESULT->getMessage()."<br>";
						while($DBRESULT->fetchInto($service))	{
							# If the Service is link with several Host, we keep this property and don't duplicate it, just create a new relation with the new Host
							$DBRESULT2 =& $pearDB->query("SELECT COUNT(*) FROM host_service_relation WHERE service_service_id = '".$service["service_service_id"]."'");
							if (PEAR::isError($DBRESULT2))
								print "DB Error : SELECT COUNT(*) FROM host_service_relation.. ".$DBRESULT2->getMessage()."<br>";
							$mulHostSv = $DBRESULT2->fetchrow();
							if ($mulHostSv["COUNT(*)"] > 1)	{
								$DBRESULT3 =& $pearDB->query("INSERT INTO host_service_relation VALUES ('', NULL, '".$maxId["MAX(host_id)"]."', NULL, '".$service["service_service_id"]."')");
								if (PEAR::isError($DBRESULT3))
									print "DB Error : INSERT INTO host_service_relation VALUES.. ".$DBRESULT3->getMessage()."<br>";
							}
							else	{
								$serviceArr[$service["service_service_id"]] = $service["service_service_id"];
								$serviceNbr[$service["service_service_id"]] = 1;
							}
						}
						# Register Host -> Duplicate the Service list
						if ($row["host_register"])
							multipleServiceInDB($serviceArr, $serviceNbr, $hostInf, 0);
						# Host Template -> Link to the existing Service Template List
						else	{
							$DBRESULT =& $pearDB->query("SELECT DISTINCT service_service_id FROM host_service_relation WHERE host_host_id = '".$key."'");
							if (PEAR::isError($DBRESULT))
								print "DB Error : ".$DBRESULT->getMessage()."<br>";
							while($DBRESULT->fetchInto($svs)){
								$DBRESULT1 =& $pearDB->query("INSERT INTO host_service_relation VALUES ('', NULL, '".$maxId["MAX(host_id)"]."', NULL, '".$svs["service_service_id"]."')");
								if (PEAR::isError($DBRESULT1))
									print "DB Error : ".$DBRESULT1->getMessage()."<br>";
							}
						}
						$DBRESULT =& $pearDB->query("SELECT DISTINCT contactgroup_cg_id FROM contactgroup_host_relation WHERE host_host_id = '".$key."'");
						if (PEAR::isError($DBRESULT))
							print "DB Error : ".$DBRESULT->getMessage()."<br>";
						while($DBRESULT->fetchInto($Cg)){
							$DBRESULT1 =& $pearDB->query("INSERT INTO contactgroup_host_relation VALUES ('', '".$maxId["MAX(host_id)"]."', '".$Cg["contactgroup_cg_id"]."')");
							if (PEAR::isError($DBRESULT1))
								print "DB Error : ".$DBRESULT1->getMessage()."<br>";
						}
						$DBRESULT =& $pearDB->query("SELECT DISTINCT hostgroup_hg_id FROM hostgroup_relation WHERE host_host_id = '".$key."'");
						if (PEAR::isError($DBRESULT))
							print "DB Error : ".$DBRESULT->getMessage()."<br>";
						while($DBRESULT->fetchInto($Hg)){
							$DBRESULT1 =& $pearDB->query("INSERT INTO hostgroup_relation VALUES ('', '".$Hg["hostgroup_hg_id"]."', '".$maxId["MAX(host_id)"]."')");
							if (PEAR::isError($DBRESULT1))
								print "DB Error : ".$DBRESULT1->getMessage()."<br>";
						}
						$DBRESULT =& $pearDB->query("SELECT * FROM extended_host_information WHERE host_host_id = '".$key."'");
						if (PEAR::isError($DBRESULT))
							print "DB Error : ".$DBRESULT->getMessage()."<br>";
						while($DBRESULT->fetchInto($ehi))	{
							$val = null;
							$ehi["host_host_id"] = $maxId["MAX(host_id)"];
							$ehi["ehi_id"] = NULL;
							foreach ($ehi as $key2=>$value2)
								$val ? $val .= ($value2!=NULL?(", '".$value2."'"):", NULL") : $val .= ($value2!=NULL?("'".$value2."'"):"NULL");
							$val ? $rq = "INSERT INTO extended_host_information VALUES (".$val.")" : $rq = null;
							$DBRESULT2 =& $pearDB->query($rq);
							if (PEAR::isError($DBRESULT2))
								print "DB Error : ".$DBRESULT2->getMessage()."<br>";
						}
					}
				}
			}
		}
	}
	
	function updateHostInDB ($host_id = NULL)	{
		if (!$host_id) return;
		updateHost($host_id);
		updateHostHostParent($host_id);
		updateHostHostChild($host_id);
		updateHostContactGroup($host_id);
		updateHostHostGroup($host_id);
		updateHostTemplateService($host_id);
		global $form;
		$ret = $form->getSubmitValues();
		if (isset($ret["dupSvTplAssoc"]["dupSvTplAssoc"]) && $ret["dupSvTplAssoc"]["dupSvTplAssoc"] && $ret["host_template_model_htm_id"])
			createHostTemplateService($host_id, $ret["host_template_model_htm_id"]);
		updateHostExtInfos($host_id);
	}	
	
	function insertHostInDB ($ret = array())	{
		$host_id = insertHost($ret);
		updateHostHostParent($host_id, $ret);
		updateHostHostChild($host_id, $ret);
		updateHostContactGroup($host_id, $ret);
		updateHostHostGroup($host_id, $ret);
		updateHostTemplateService($host_id, $ret);
		global $form;
		$ret = $form->getSubmitValues();
		if (isset($ret["dupSvTplAssoc"]["dupSvTplAssoc"]) && $ret["dupSvTplAssoc"]["dupSvTplAssoc"] && $ret["host_template_model_htm_id"])
			createHostTemplateService($host_id, $ret["host_template_model_htm_id"]);
		insertHostExtInfos($host_id, $ret);
		return ($host_id);
	}
	
	function insertHost($ret)	{
		global $form, $pearDB, $oreon;
		if (!count($ret))
			$ret = $form->getSubmitValues();
		if (isset($ret["command_command_id_arg1"]) && $ret["command_command_id_arg1"] != NULL)		{
			$ret["command_command_id_arg1"] = str_replace("\n", "#BR#", $ret["command_command_id_arg1"]);
			$ret["command_command_id_arg1"] = str_replace("\t", "#T#", $ret["command_command_id_arg1"]);
			$ret["command_command_id_arg1"] = str_replace("\r", "#R#", $ret["command_command_id_arg1"]);
			$ret["command_command_id_arg1"] = str_replace('/', "#S#", $ret["command_command_id_arg1"]);
			$ret["command_command_id_arg1"] = str_replace('\\', "#BS#", $ret["command_command_id_arg1"]);
		}
		if (isset($ret["command_command_id_arg2"]) && $ret["command_command_id_arg2"] != NULL)		{
			$ret["command_command_id_arg2"] = str_replace("\n", "#BR#", $ret["command_command_id_arg2"]);
			$ret["command_command_id_arg2"] = str_replace("\t", "#T#", $ret["command_command_id_arg2"]);
			$ret["command_command_id_arg2"] = str_replace("\r", "#R#", $ret["command_command_id_arg2"]);
			$ret["command_command_id_arg2"] = str_replace('/', "#S#", $ret["command_command_id_arg2"]);
			$ret["command_command_id_arg2"] = str_replace('\\', "#BS#", $ret["command_command_id_arg2"]);
		}
		
		$rq = "INSERT INTO host " .
			"(host_template_model_htm_id, command_command_id, command_command_id_arg1, timeperiod_tp_id, timeperiod_tp_id2, purge_policy_id, command_command_id2, command_command_id_arg2," .
			"host_name, host_alias, host_address, host_max_check_attempts, host_check_interval, host_active_checks_enabled, " .
			"host_passive_checks_enabled, host_checks_enabled, host_obsess_over_host, host_check_freshness, host_freshness_threshold, " .
			"host_event_handler_enabled, host_low_flap_threshold, host_high_flap_threshold, host_flap_detection_enabled, " .
			"host_process_perf_data, host_retain_status_information, host_retain_nonstatus_information, host_notification_interval, " .
			"host_notification_options, host_notifications_enabled, host_stalking_options, host_snmp_community, host_snmp_version, host_comment, host_register, host_activate) " .
			"VALUES ( ";
			isset($ret["host_template_model_htm_id"]) && $ret["host_template_model_htm_id"] != NULL ? $rq .= "'".$ret["host_template_model_htm_id"]."', ": $rq .= "NULL, ";
			isset($ret["command_command_id"]) && $ret["command_command_id"] != NULL ? $rq .= "'".$ret["command_command_id"]."', ": $rq .= "NULL, ";
			isset($ret["command_command_id_arg1"]) && $ret["command_command_id_arg1"] != NULL ? $rq .= "'".$ret["command_command_id_arg1"]."', ": $rq .= "NULL, ";
			isset($ret["timeperiod_tp_id"]) && $ret["timeperiod_tp_id"] != NULL ? $rq .= "'".$ret["timeperiod_tp_id"]."', ": $rq .= "NULL, ";
			isset($ret["timeperiod_tp_id2"]) && $ret["timeperiod_tp_id2"] != NULL ? $rq .= "'".$ret["timeperiod_tp_id2"]."', ": $rq .= "NULL, ";
			isset($ret["purge_policy_id"]) && $ret["purge_policy_id"] != NULL ? $rq .= "'".$ret["purge_policy_id"]."', ": $rq .= "NULL, ";
			isset($ret["command_command_id2"]) && $ret["command_command_id2"] != NULL ? $rq .= "'".$ret["command_command_id2"]."', ": $rq .= "NULL, ";
			isset($ret["command_command_id_arg2"]) && $ret["command_command_id_arg2"] != NULL ? $rq .= "'".$ret["command_command_id_arg2"]."', ": $rq .= "NULL, ";
			isset($ret["host_name"]) && $ret["host_name"] != NULL ? $rq .= "'".htmlentities($ret["host_name"], ENT_QUOTES)."', ": $rq .= "NULL, ";
			isset($ret["host_alias"]) && $ret["host_alias"] != NULL ? $rq .= "'".htmlentities($ret["host_alias"], ENT_QUOTES)."', ": $rq .= "NULL, ";
			isset($ret["host_address"]) && $ret["host_address"] != NULL ? $rq .= "'".htmlentities($ret["host_address"], ENT_QUOTES)."', ": $rq .= "NULL, ";
			isset($ret["host_max_check_attempts"]) && $ret["host_max_check_attempts"] != NULL ? $rq .= "'".$ret["host_max_check_attempts"]."', " : $rq .= "NULL, ";
			isset($ret["host_check_interval"]) && $ret["host_check_interval"] != NULL ? $rq .= "'".$ret["host_check_interval"]."', ": $rq .= "NULL, ";
			isset($ret["host_active_checks_enabled"]["host_active_checks_enabled"]) && $ret["host_active_checks_enabled"]["host_active_checks_enabled"] != 2 ? $rq .= "'".$ret["host_active_checks_enabled"]["host_active_checks_enabled"]."', ": $rq .= "'2', ";
			isset($ret["host_passive_checks_enabled"]["host_passive_checks_enabled"]) && $ret["host_passive_checks_enabled"]["host_passive_checks_enabled"] != 2 ? $rq .= "'".$ret["host_passive_checks_enabled"]["host_passive_checks_enabled"]."', ": $rq .= "'2', ";
			isset($ret["host_checks_enabled"]["host_checks_enabled"]) && $ret["host_checks_enabled"]["host_checks_enabled"] != 2 ? $rq .= "'".$ret["host_checks_enabled"]["host_checks_enabled"]."', ": $rq .= "'2', ";
			isset($ret["host_obsess_over_host"]["host_obsess_over_host"]) && $ret["host_obsess_over_host"]["host_obsess_over_host"] != 2 ? $rq .= "'".$ret["host_obsess_over_host"]["host_obsess_over_host"]."', ": $rq .= "'2', ";
			isset($ret["host_check_freshness"]["host_check_freshness"]) && $ret["host_check_freshness"]["host_check_freshness"] != 2 ? $rq .= "'".$ret["host_check_freshness"]["host_check_freshness"]."', ": $rq .= "'2', ";
			isset($ret["host_freshness_threshold"]) && $ret["host_freshness_threshold"] != NULL ? $rq .= "'".$ret["host_freshness_threshold"]."', ": $rq .= "NULL, ";
			isset($ret["host_event_handler_enabled"]["host_event_handler_enabled"]) && $ret["host_event_handler_enabled"]["host_event_handler_enabled"] != 2 ? $rq .= "'".$ret["host_event_handler_enabled"]["host_event_handler_enabled"]."', ": $rq .= "'2', ";
			isset($ret["host_low_flap_threshold"]) && $ret["host_low_flap_threshold"] != NULL ? $rq .= "'".$ret["host_low_flap_threshold"]."', " : $rq .= "NULL, ";
			isset($ret["host_high_flap_threshold"]) && $ret["host_high_flap_threshold"] != NULL ? $rq .= "'".$ret["host_high_flap_threshold"]."', " : $rq .= "NULL, ";
			isset($ret["host_flap_detection_enabled"]["host_flap_detection_enabled"]) && $ret["host_flap_detection_enabled"]["host_flap_detection_enabled"] != 2 ? $rq .= "'".$ret["host_flap_detection_enabled"]["host_flap_detection_enabled"]."', " : $rq .= "'2', ";
			isset($ret["host_process_perf_data"]["host_process_perf_data"]) && $ret["host_process_perf_data"]["host_process_perf_data"] != 2 ? $rq .= "'".$ret["host_process_perf_data"]["host_process_perf_data"]."', " : $rq .= "'2', ";
			isset($ret["host_retain_status_information"]["host_retain_status_information"]) && $ret["host_retain_status_information"]["host_retain_status_information"] != 2 ? $rq .= "'".$ret["host_retain_status_information"]["host_retain_status_information"]."', " : $rq .= "'2', ";
			isset($ret["host_retain_nonstatus_information"]["host_retain_nonstatus_information"]) && $ret["host_retain_nonstatus_information"]["host_retain_nonstatus_information"] != 2 ? $rq .= "'".$ret["host_retain_nonstatus_information"]["host_retain_nonstatus_information"]."', " : $rq .= "'2', ";
			isset($ret["host_notification_interval"]) && $ret["host_notification_interval"] != NULL ? $rq .= "'".$ret["host_notification_interval"]."', " : $rq .= "NULL, ";
			isset($ret["host_notifOpts"]) && $ret["host_notifOpts"] != NULL ? $rq .= "'".implode(",", array_keys($ret["host_notifOpts"]))."', " : $rq .= "NULL, ";
			isset($ret["host_notifications_enabled"]["host_notifications_enabled"]) && $ret["host_notifications_enabled"]["host_notifications_enabled"] != 2 ? $rq .= "'".$ret["host_notifications_enabled"]["host_notifications_enabled"]."', " : $rq .= "'2', ";
			isset($ret["host_stalOpts"]) && $ret["host_stalOpts"] != NULL ? $rq .= "'".implode(",", array_keys($ret["host_stalOpts"]))."', " : $rq .= "NULL, ";
			isset($ret["host_snmp_community"]) && $ret["host_snmp_community"] != NULL ? $rq .= "'".htmlentities($ret["host_snmp_community"], ENT_QUOTES)."', " : $rq .= "NULL, ";
			isset($ret["host_snmp_version"]) && $ret["host_snmp_version"] != NULL ? $rq .= "'".htmlentities($ret["host_snmp_version"], ENT_QUOTES)."', " : $rq .= "NULL, ";
			isset($ret["host_comment"]) && $ret["host_comment"] != NULL ? $rq .= "'".htmlentities($ret["host_comment"], ENT_QUOTES)."', " : $rq .= "NULL, ";
			isset($ret["host_register"]["host_register"]) && $ret["host_register"]["host_register"] != NULL ? $rq .= "'".$ret["host_register"]["host_register"]."', " : $rq .= "NULL, ";
			isset($ret["host_activate"]["host_activate"]) && $ret["host_activate"]["host_activate"] != NULL ? $rq .= "'".$ret["host_activate"]["host_activate"]."'" : $rq .= "NULL";
		$rq .= ")";
		$DBRESULT =& $pearDB->query($rq);
		if (PEAR::isError($DBRESULT))
			print "DB Error : INSERT INTO host.. : ".$DBRESULT->getMessage()."<br>";
		$DBRESULT =& $pearDB->query("SELECT MAX(host_id) FROM host");
		$host_id = $DBRESULT->fetchRow();		
		# Update LCA
		$DBRESULT1 =& $pearDB->query("SELECT contactgroup_cg_id FROM contactgroup_contact_relation WHERE contact_contact_id = '".$oreon->user->get_id()."'");
		if (PEAR::isError($DBRESULT1))
			print "DB Error : ".$DBRESULT1->getMessage()."<br>";
		while($DBRESULT1->fetchInto($contactGroup))	{
		 	$DBRESULT2 =& $pearDB->query("SELECT lca_define_lca_id FROM lca_define_contactgroup_relation ldcgr WHERE ldcgr.contactgroup_cg_id = '".$contactGroup["contactgroup_cg_id"]."'");	
			if (PEAR::isError($DBRESULT2))
				print "DB Error : ".$DBRESULT2->getMessage()."<br>";
			while ($DBRESULT2->fetchInto($lca))	{
				$rq = "INSERT INTO lca_define_host_relation ";
				$rq .= "(lca_define_lca_id, host_host_id) ";
				$rq .= "VALUES ";
				$rq .= "('".$lca["lca_define_lca_id"]."', '".$host_id["MAX(host_id)"]."')";
				$DBRESULT3 =& $pearDB->query($rq);
				if (PEAR::isError($DBRESULT3))
					print "DB Error : ".$DBRESULT3->getMessage()."<br>";
			}
		}
		#
		return ($host_id["MAX(host_id)"]);
	}	
	
	function insertHostExtInfos($host_id = null, $ret)	{
		if (!$host_id) return;
		global $form, $pearDB;
		if (!count($ret))
			$ret = $form->getSubmitValues();
		$rq = 	"INSERT INTO `extended_host_information` " .
				"( `ehi_id` , `host_host_id` , `ehi_notes` , `ehi_notes_url` , " .
				"`ehi_action_url` , `ehi_icon_image` , `ehi_icon_image_alt` , " .
				"`ehi_vrml_image` , `ehi_statusmap_image` , `ehi_2d_coords` , " .
				"`ehi_3d_coords`, `country_id`, `city_id` )" .
				"VALUES ( ";
		$rq .= "NULL, ".$host_id.", ";
		isset($ret["ehi_notes"]) && $ret["ehi_notes"] != NULL ? $rq .= "'".htmlentities($ret["ehi_notes"], ENT_QUOTES)."', ": $rq .= "NULL, ";
		isset($ret["ehi_notes_url"]) && $ret["ehi_notes_url"] != NULL ? $rq .= "'".htmlentities($ret["ehi_notes_url"], ENT_QUOTES)."', ": $rq .= "NULL, ";
		isset($ret["ehi_action_url"]) && $ret["ehi_action_url"] != NULL ? $rq .= "'".htmlentities($ret["ehi_action_url"], ENT_QUOTES)."', ": $rq .= "NULL, ";
		isset($ret["ehi_icon_image"]) && $ret["ehi_icon_image"] != NULL ? $rq .= "'".htmlentities($ret["ehi_icon_image"], ENT_QUOTES)."', ": $rq .= "NULL, ";
		isset($ret["ehi_icon_image_alt"]) && $ret["ehi_icon_image_alt"] != NULL ? $rq .= "'".htmlentities($ret["ehi_icon_image_alt"], ENT_QUOTES)."', ": $rq .= "NULL, ";
		isset($ret["ehi_vrml_image"]) && $ret["ehi_vrml_image"] != NULL ? $rq .= "'".htmlentities($ret["ehi_vrml_image"], ENT_QUOTES)."', ": $rq .= "NULL, ";
		isset($ret["ehi_statusmap_image"]) && $ret["ehi_statusmap_image"] != NULL ? $rq .= "'".htmlentities($ret["ehi_statusmap_image"], ENT_QUOTES)."', ": $rq .= "NULL, ";
		isset($ret["ehi_2d_coords"]) && $ret["ehi_2d_coords"] != NULL ? $rq .= "'".htmlentities($ret["ehi_2d_coords"], ENT_QUOTES)."', ": $rq .= "NULL, ";
		isset($ret["ehi_3d_coords"]) && $ret["ehi_3d_coords"] != NULL ? $rq .= "'".htmlentities($ret["ehi_3d_coords"], ENT_QUOTES)."', ": $rq .= "NULL, ";
		isset($ret["country_id"]) && $ret["country_id"] != NULL ? $rq .= "'".$ret["country_id"]."', ": $rq .= "NULL, ";
		if (isset($ret["city_name"]) && $ret["city_name"])	{
			$DBRESULT =& $pearDB->query("SELECT DISTINCT city_id FROM view_city WHERE city_name = '".$ret["city_name"]."' AND country_id = '".$ret["country_id"]."'");
			$city = $DBRESULT->fetchRow();
			$city["city_id"] ? $rq .= "'".$city["city_id"]."' ": $rq .= "NULL ";
		} else
			$rq .= "NULL ";
		$rq .= ")";
		$DBRESULT =& $pearDB->query($rq);
		if (PEAR::isError($DBRESULT))
			print "DB Error : ".$DBRESULT->getMessage()."<br>";
	}
	
	function updateHost($host_id = null)	{
		if (!$host_id) return;
		global $form;
		global $pearDB;
		$ret = array();
		$ret = $form->getSubmitValues();		
		if (isset($ret["command_command_id_arg1"]) && $ret["command_command_id_arg1"] != NULL)		{
			$ret["command_command_id_arg1"] = str_replace("\n", "#BR#", $ret["command_command_id_arg1"]);
			$ret["command_command_id_arg1"] = str_replace("\t", "#T#", $ret["command_command_id_arg1"]);
			$ret["command_command_id_arg1"] = str_replace("\r", "#R#", $ret["command_command_id_arg1"]);
			$ret["command_command_id_arg1"] = str_replace('/', "#S#", $ret["command_command_id_arg1"]);
			$ret["command_command_id_arg1"] = str_replace('\\', "#BS#", $ret["command_command_id_arg1"]);
		}
		if (isset($ret["command_command_id_arg2"]) && $ret["command_command_id_arg2"] != NULL)		{
			$ret["command_command_id_arg2"] = str_replace("\n", "#BR#", $ret["command_command_id_arg2"]);
			$ret["command_command_id_arg2"] = str_replace("\t", "#T#", $ret["command_command_id_arg2"]);
			$ret["command_command_id_arg2"] = str_replace("\r", "#R#", $ret["command_command_id_arg2"]);
			$ret["command_command_id_arg2"] = str_replace('/', "#S#", $ret["command_command_id_arg2"]);
			$ret["command_command_id_arg2"] = str_replace('\\', "#BS#", $ret["command_command_id_arg2"]);
		}
		$rq = "UPDATE host SET host_template_model_htm_id = ";
		isset($ret["host_template_model_htm_id"]) && $ret["host_template_model_htm_id"] != NULL ? $rq .= "'".$ret["host_template_model_htm_id"]."', ": $rq .= "NULL, ";
		$rq .= "command_command_id = ";		
		isset($ret["command_command_id"]) && $ret["command_command_id"] != NULL ? $rq .= "'".$ret["command_command_id"]."', ": $rq .= "NULL, ";		
		$rq .= "command_command_id_arg1 = ";		
		isset($ret["command_command_id_arg1"]) && $ret["command_command_id_arg1"] != NULL ? $rq .= "'".$ret["command_command_id_arg1"]."', ": $rq .= "NULL, ";
		$rq .= "timeperiod_tp_id = ";
		isset($ret["timeperiod_tp_id"]) && $ret["timeperiod_tp_id"] != NULL ? $rq .= "'".$ret["timeperiod_tp_id"]."', ": $rq .= "NULL, ";
		$rq .= "timeperiod_tp_id2 = ";
		isset($ret["timeperiod_tp_id2"]) && $ret["timeperiod_tp_id2"] != NULL ? $rq .= "'".$ret["timeperiod_tp_id2"]."', ": $rq .= "NULL, ";
		$rq .= "purge_policy_id = ";
		isset($ret["purge_policy_id"]) && $ret["purge_policy_id"] != NULL ? $rq .= "'".$ret["purge_policy_id"]."', ": $rq .= "NULL, ";
		$rq .= "command_command_id2 = ";
		isset($ret["command_command_id2"]) && $ret["command_command_id2"] != NULL ? $rq .= "'".$ret["command_command_id2"]."', ": $rq .= "NULL, ";
		$rq .= "command_command_id_arg2 = ";		
		isset($ret["command_command_id_arg2"]) && $ret["command_command_id_arg2"] != NULL ? $rq .= "'".$ret["command_command_id_arg2"]."', ": $rq .= "NULL, ";
		$rq .= "host_name = ";
		isset($ret["host_name"]) && $ret["host_name"] != NULL ? $rq .= "'".htmlentities($ret["host_name"], ENT_QUOTES)."', ": $rq .= "NULL, ";
		$rq .= "host_alias = ";
		isset($ret["host_alias"]) && $ret["host_alias"] != NULL ? $rq .= "'".htmlentities($ret["host_alias"], ENT_QUOTES)."', ": $rq .= "NULL, ";
		$rq .= "host_address = ";
		isset($ret["host_address"]) && $ret["host_address"] != NULL ? $rq .= "'".htmlentities($ret["host_address"], ENT_QUOTES)."', ": $rq .= "NULL, ";
		$rq .= "host_max_check_attempts = ";
		isset($ret["host_max_check_attempts"]) && $ret["host_max_check_attempts"] != NULL ? $rq .= "'".$ret["host_max_check_attempts"]."', " : $rq .= "NULL, ";
		$rq .= "host_check_interval = ";
		isset($ret["host_check_interval"]) && $ret["host_check_interval"]!= NULL ? $rq .= "'".$ret["host_check_interval"]."', ": $rq .= "NULL, ";
		$rq .= "host_active_checks_enabled = ";
		isset($ret["host_active_checks_enabled"]["host_active_checks_enabled"]) && $ret["host_active_checks_enabled"]["host_active_checks_enabled"] != 2 ? $rq .= "'".$ret["host_active_checks_enabled"]["host_active_checks_enabled"]."', ": $rq .= "'2', ";
		$rq .= "host_passive_checks_enabled = ";
		isset($ret["host_passive_checks_enabled"]["host_passive_checks_enabled"]) && $ret["host_passive_checks_enabled"]["host_passive_checks_enabled"] != 2 ? $rq .= "'".$ret["host_passive_checks_enabled"]["host_passive_checks_enabled"]."', ": $rq .= "'2', ";
		$rq .= "host_checks_enabled = ";
		isset($ret["host_checks_enabled"]["host_checks_enabled"]) && $ret["host_checks_enabled"]["host_checks_enabled"] != 2 ? $rq .= "'".$ret["host_checks_enabled"]["host_checks_enabled"]."', ": $rq .= "'2', ";
		$rq .= "host_obsess_over_host = ";
		isset($ret["host_obsess_over_host"]["host_obsess_over_host"]) && $ret["host_obsess_over_host"]["host_obsess_over_host"] != 2 ? $rq .= "'".$ret["host_obsess_over_host"]["host_obsess_over_host"]."', ": $rq .= "'2', ";
		$rq .= "host_check_freshness = ";
		isset($ret["host_check_freshness"]["host_check_freshness"]) && $ret["host_check_freshness"]["host_check_freshness"] != 2 ? $rq .= "'".$ret["host_check_freshness"]["host_check_freshness"]."', ": $rq .= "'2', ";
		$rq .= "host_freshness_threshold = ";
		isset($ret["host_freshness_threshold"]) && $ret["host_freshness_threshold"] != NULL ? $rq .= "'".$ret["host_freshness_threshold"]."', ": $rq .= "NULL, ";
		$rq .= "host_event_handler_enabled = ";
		isset($ret["host_event_handler_enabled"]["host_event_handler_enabled"]) && $ret["host_event_handler_enabled"]["host_event_handler_enabled"] != 2 ? $rq .= "'".$ret["host_event_handler_enabled"]["host_event_handler_enabled"]."', ": $rq .= "'2', ";
		$rq .= "host_low_flap_threshold = ";
		isset($ret["host_low_flap_threshold"]) && $ret["host_low_flap_threshold"]!= NULL ? $rq .= "'".$ret["host_low_flap_threshold"]."', " : $rq .= "NULL, ";
		$rq .= "host_high_flap_threshold = ";
		isset($ret["host_high_flap_threshold"]) && $ret["host_high_flap_threshold"] != NULL ? $rq .= "'".$ret["host_high_flap_threshold"]."', " : $rq .= "NULL, ";
		$rq .= "host_flap_detection_enabled = ";
		isset($ret["host_flap_detection_enabled"]["host_flap_detection_enabled"]) && $ret["host_flap_detection_enabled"]["host_flap_detection_enabled"] != 2 ? $rq .= "'".$ret["host_flap_detection_enabled"]["host_flap_detection_enabled"]."', " : $rq .= "'2', ";
		$rq .= "host_process_perf_data = ";
		isset($ret["host_process_perf_data"]["host_process_perf_data"]) && $ret["host_process_perf_data"]["host_process_perf_data"] != 2 ? $rq .= "'".$ret["host_process_perf_data"]["host_process_perf_data"]."', " : $rq .= "'2', ";
		$rq .= "host_retain_status_information = ";
		isset($ret["host_retain_status_information"]["host_retain_status_information"]) && $ret["host_retain_status_information"]["host_retain_status_information"] != 2 ? $rq .= "'".$ret["host_retain_status_information"]["host_retain_status_information"]."', " : $rq .= "'2', ";
		$rq .= "host_retain_nonstatus_information = ";
		isset($ret["host_retain_nonstatus_information"]["host_retain_nonstatus_information"]) && $ret["host_retain_nonstatus_information"]["host_retain_nonstatus_information"] != 2 ? $rq .= "'".$ret["host_retain_nonstatus_information"]["host_retain_nonstatus_information"]."', " : $rq .= "'2', ";
		$rq .= "host_notification_interval = ";
		isset($ret["host_notification_interval"]) && $ret["host_notification_interval"] != NULL ? $rq .= "'".$ret["host_notification_interval"]."', " : $rq .= "NULL, ";
		$rq .= "host_notification_options = ";
		isset($ret["host_notifOpts"]) && $ret["host_notifOpts"] != NULL ? $rq .= "'".implode(",", array_keys($ret["host_notifOpts"]))."', " : $rq .= "NULL, ";
		$rq .= "host_notifications_enabled = ";
		isset($ret["host_notifications_enabled"]["host_notifications_enabled"]) && $ret["host_notifications_enabled"]["host_notifications_enabled"] != 2 ? $rq .= "'".$ret["host_notifications_enabled"]["host_notifications_enabled"]."', " : $rq .= "'2', ";
		$rq .= "host_stalking_options = ";
		isset($ret["host_stalOpts"]) && $ret["host_stalOpts"] != NULL ? $rq .= "'".implode(",", array_keys($ret["host_stalOpts"]))."', " : $rq .= "NULL, ";
		$rq .= "host_snmp_community = ";
		isset($ret["host_snmp_community"]) && $ret["host_snmp_community"] != NULL ? $rq .= "'".htmlentities($ret["host_snmp_community"], ENT_QUOTES)."', " : $rq .= "NULL, ";
		$rq .= "host_snmp_version = ";
		isset($ret["host_snmp_version"]) && $ret["host_snmp_version"] != NULL ? $rq .= "'".htmlentities($ret["host_snmp_version"], ENT_QUOTES)."', " : $rq .= "NULL, ";
		$rq .= "host_comment = ";
		isset($ret["host_comment"]) && $ret["host_comment"] != NULL ? $rq .= "'".htmlentities($ret["host_comment"], ENT_QUOTES)."', " : $rq .= "NULL, ";
		$rq .= "host_register = ";
		isset($ret["host_register"]["host_register"]) && $ret["host_register"]["host_register"] != NULL ? $rq .= "'".$ret["host_register"]["host_register"]."', " : $rq .= "NULL, ";
		$rq .= "host_activate = ";
		isset($ret["host_activate"]["host_activate"]) && $ret["host_activate"]["host_activate"] != NULL ? $rq .= "'".$ret["host_activate"]["host_activate"]."'" : $rq .= "NULL ";
		$rq .= "WHERE host_id = '".$host_id."'";
		$DBRESULT =& $pearDB->query($rq);
		if (PEAR::isError($DBRESULT))
			print "DB Error : UPDATE host SET.. : ".$DBRESULT->getMessage()."<br>";
	}
	
	function updateHostHostParent($host_id = null, $ret = array())	{
		if (!$host_id) return;
		global $form;
		global $pearDB;
		$rq = "DELETE FROM host_hostparent_relation ";
		$rq .= "WHERE host_host_id = '".$host_id."'";
		$DBRESULT =& $pearDB->query($rq);
		if (PEAR::isError($DBRESULT))
			print "DB Error : ".$DBRESULT->getMessage()."<br>";
		if (isset($ret["host_parents"]))
			$ret = $ret["host_parents"];
		else
			$ret = $form->getSubmitValue("host_parents");
		for($i = 0; $i < count($ret); $i++)	{
			$rq = "INSERT INTO host_hostparent_relation ";
			$rq .= "(host_parent_hp_id, host_host_id) ";
			$rq .= "VALUES ";
			$rq .= "('".$ret[$i]."', '".$host_id."')";
			$DBRESULT =& $pearDB->query($rq);
			if (PEAR::isError($DBRESULT))
				print "DB Error : ".$DBRESULT->getMessage()."<br>";
		}
	}
	
	function updateHostHostChild($host_id = null)	{
		if (!$host_id) return;
		global $form, $pearDB;
		$rq = "DELETE FROM host_hostparent_relation ";
		$rq .= "WHERE host_parent_hp_id = '".$host_id."'";
		$DBRESULT =& $pearDB->query($rq);
		if (PEAR::isError($DBRESULT))
			print "DB Error : ".$DBRESULT->getMessage()."<br>";
		$ret = array();
		$ret = $form->getSubmitValue("host_childs");
		for($i = 0; $i < count($ret); $i++)	{
			$rq = "INSERT INTO host_hostparent_relation ";
			$rq .= "(host_parent_hp_id, host_host_id) ";
			$rq .= "VALUES ";
			$rq .= "('".$host_id."', '".$ret[$i]."')";
			$DBRESULT =& $pearDB->query($rq);
			if (PEAR::isError($DBRESULT))
				print "DB Error : ".$DBRESULT->getMessage()."<br>";
		}
	}


	function updateHostExtInfos($host_id = null, $ret = array())	{
		if (!$host_id) return;
		global $form, $pearDB;
		if (!count($ret))
			$ret = $form->getSubmitValues();
		$rq = "UPDATE extended_host_information ";		
		$rq .= "SET ehi_notes = ";
		isset($ret["ehi_notes"]) && $ret["ehi_notes"] != NULL ? $rq .= "'".htmlentities($ret["ehi_notes"], ENT_QUOTES)."', ": $rq .= "NULL, ";
		$rq .= "ehi_notes_url = ";
		isset($ret["ehi_notes_url"]) && $ret["ehi_notes_url"] != NULL ? $rq .= "'".htmlentities($ret["ehi_notes_url"], ENT_QUOTES)."', ": $rq .= "NULL, ";
		$rq .= "ehi_action_url = ";
		isset($ret["ehi_action_url"]) && $ret["ehi_action_url"] != NULL ? $rq .= "'".htmlentities($ret["ehi_action_url"], ENT_QUOTES)."', ": $rq .= "NULL, ";
		$rq .= "ehi_icon_image = ";
		isset($ret["ehi_icon_image"]) && $ret["ehi_icon_image"] != NULL ? $rq .= "'".htmlentities($ret["ehi_icon_image"], ENT_QUOTES)."', ": $rq .= "NULL, ";
		$rq .= "ehi_icon_image_alt = ";
		isset($ret["ehi_icon_image_alt"]) && $ret["ehi_icon_image_alt"] != NULL ? $rq .= "'".htmlentities($ret["ehi_icon_image_alt"], ENT_QUOTES)."', ": $rq .= "NULL, ";
		$rq .= "ehi_vrml_image = ";
		isset($ret["ehi_vrml_image"]) && $ret["ehi_vrml_image"] != NULL ? $rq .= "'".htmlentities($ret["ehi_vrml_image"], ENT_QUOTES)."', ": $rq .= "NULL, ";
		$rq .= "ehi_statusmap_image = ";
		isset($ret["ehi_statusmap_image"]) && $ret["ehi_statusmap_image"] != NULL ? $rq .= "'".htmlentities($ret["ehi_statusmap_image"], ENT_QUOTES)."', ": $rq .= "NULL, ";
		$rq .= "ehi_2d_coords = ";
		isset($ret["ehi_2d_coords"]) && $ret["ehi_2d_coords"] != NULL ? $rq .= "'".htmlentities($ret["ehi_2d_coords"], ENT_QUOTES)."', ": $rq .= "NULL, ";
		$rq .= "ehi_3d_coords = ";
		isset($ret["ehi_3d_coords"]) && $ret["ehi_3d_coords"] != NULL ? $rq .= "'".htmlentities($ret["ehi_3d_coords"], ENT_QUOTES)."', ": $rq .= "NULL, ";
		$rq .= "country_id = ";
		isset($ret["country_id"]) && $ret["country_id"] != NULL ? $rq .= "'".$ret["country_id"]."', ": $rq .= "NULL, ";
		$rq .= "city_id = ";
		if (isset($ret["city_name"]) && $ret["city_name"])	{
			$DBRESULT =& $pearDB->query("SELECT DISTINCT city_id FROM view_city WHERE city_name = '".$ret["city_name"]."' AND country_id = '".$ret["country_id"]."'");
			if (PEAR::isError($DBRESULT))
				print "DB Error : ".$DBRESULT->getMessage()."<br>";
			$city = $DBRESULT->fetchRow();
			$city["city_id"] ? $rq .= "'".$city["city_id"]."' ": $rq .= "NULL ";
		}	
		else
			$rq .= "NULL ";
		$rq .= "WHERE host_host_id = '".$host_id."'";
		$DBRESULT =& $pearDB->query($rq);
		if (PEAR::isError($DBRESULT))
			print "DB Error : ".$DBRESULT->getMessage()."<br>";
	}
	
	function updateHostContactGroup($host_id, $ret = array())	{
		if (!$host_id) return;
		global $form, $pearDB;
		$rq = "DELETE FROM contactgroup_host_relation ";
		$rq .= "WHERE host_host_id = '".$host_id."'";
		$DBRESULT = $pearDB->query($rq);
		if (PEAR::isError($DBRESULT))
			print "DB Error : ".$DBRESULT->getMessage()."<br>";
		isset($ret["host_cgs"]) ? $ret = $ret["host_cgs"] : $ret = $form->getSubmitValue("host_cgs");
		for($i = 0; $i < count($ret); $i++)	{
			$rq = "INSERT INTO contactgroup_host_relation ";
			$rq .= "(host_host_id, contactgroup_cg_id) ";
			$rq .= "VALUES ";
			$rq .= "('".$host_id."', '".$ret[$i]."')";
			$DBRESULT = $pearDB->query($rq);
			if (PEAR::isError($DBRESULT))
				print "DB Error : ".$DBRESULT->getMessage()."<br>";
		}
	}
	
	function updateHostHostGroup($host_id, $ret = array())	{
		if (!$host_id) return;
		global $form;
		global $pearDB;
		$rq = "DELETE FROM hostgroup_relation ";
		$rq .= "WHERE host_host_id = '".$host_id."'";
		$DBRESULT =& $pearDB->query($rq);
		if (PEAR::isError($DBRESULT))
			print "DB Error : ".$DBRESULT->getMessage()."<br>";
		isset($ret["host_hgs"]) ? $ret = $ret["host_hgs"] : $ret = $form->getSubmitValue("host_hgs");
		for($i = 0; $i < count($ret); $i++)	{
			$rq = "INSERT INTO hostgroup_relation ";
			$rq .= "(hostgroup_hg_id, host_host_id) ";
			$rq .= "VALUES ";
			$rq .= "('".$ret[$i]."', '".$host_id."')";
			$DBRESULT =& $pearDB->query($rq);
			if (PEAR::isError($DBRESULT))
				print "DB Error : ".$DBRESULT->getMessage()."<br>";
		}
	}
	
	function createHostTemplateService($host_id = null, $htm_id = NULL)	{
		if (!$host_id || !$htm_id) return;
		global $pearDB, $path, $oreon;
		require_once($path."../service/DB-Func.php");
		while (1)	{
			$DBRESULT =& $pearDB->query("SELECT service_service_id FROM host_service_relation WHERE host_host_id = '".$htm_id."'");
			while ($DBRESULT->fetchInto($row))	{
				//$desc =& getMyServiceName($row["service_service_id"]);
				$alias =& getMyServiceAlias($row["service_service_id"]);
				if (testServiceExistence ($alias, array(0=>$host_id)))	{
					$service = array("service_template_model_stm_id" => $row["service_service_id"], "service_description"=> $alias, "service_register"=>array("service_register"=> 1), "service_activate"=>array("service_activate" => 1));
					$service_id = insertServiceInDB($service);		
					$rq = "INSERT INTO host_service_relation ";
					$rq .= "(hostgroup_hg_id, host_host_id, servicegroup_sg_id, service_service_id) ";
					$rq .= "VALUES ";
					$rq .= "(NULL, '".$host_id."', NULL, '".$service_id."')";
					$DBRESULT2 =& $pearDB->query($rq);
					if (PEAR::isError($DBRESULT2))
						print "DB Error : ".$DBRESULT2->getMessage()."<br>";
				}
			}
			$htm_id = getMyHostTemplateModel($htm_id); 
			if (!$htm_id)
				break;
		}
	}
	
	function updateHostTemplateService($host_id = null)	{
		if (!$host_id) return;
		global $form, $pearDB;
		$DBRESULT =& $pearDB->query("SELECT host_register FROM host WHERE host_id = '".$host_id."'");
		$row =& $DBRESULT->fetchRow();
		if ($row["host_register"] == 0) 	{
			$rq = "DELETE FROM host_service_relation ";
			$rq .= "WHERE host_host_id = '".$host_id."'";
			$DBRESULT2 =& $pearDB->query($rq);
			if (PEAR::isError($DBRESULT2))
				print "DB Error : ".$DBRESULT2->getMessage()."<br>";
			$ret = array();
			$ret = $form->getSubmitValue("host_svTpls");
			for($i = 0; $i < count($ret); $i++)	{
				$rq = "INSERT INTO host_service_relation ";
				$rq .= "(hostgroup_hg_id, host_host_id, servicegroup_sg_id, service_service_id) ";
				$rq .= "VALUES ";
				$rq .= "(NULL, '".$host_id."', NULL, '".$ret[$i]."')";
				$DBRESULT2 =& $pearDB->query($rq);
				if (PEAR::isError($DBRESULT2))
					print "DB Error : ".$DBRESULT2->getMessage()."<br>";
			}
		}
	}

	function updateHostTemplateUsed($useTpls = array())	{
		if(!count($useTpls)) return;
		global $pearDB;
		require_once "./include/common/common-Func.php";
		foreach ($useTpls as $key=>$value){
			$DBRESULT =& $pearDB->query("UPDATE host SET host_template_model_htm_id = '".getMyHostID($value)."' WHERE host_id = '".$key."'");
			if (PEAR::isError($DBRESULT))
				print "DB Error : ".$DBRESULT->getMessage()."<br>";
		}			
	}
	
	function return_image_list($rep){
		global $oreon;
		$images = array(NULL=>NULL);
		$is_not_an_image = array("."=>".", ".."=>"..");
		if (substr($oreon->optGen["nagios_path_img"], -1) == "/" && isset($rep[0]) && $rep[0] == "/")
			$rep = substr($rep, 1);
		$handle = @opendir($oreon->optGen["nagios_path_img"].$rep);
		while (false !== ($filename = @readdir($handle))){
			if (!is_dir($oreon->optGen["nagios_path_img"].$rep."/".$filename) && !array_key_exists($filename, $is_not_an_image) && substr($filename, -1)!= "~"){
				$key = substr($oreon->optGen["nagios_path_img"].$rep."/".$filename, strlen($oreon->optGen["nagios_path_img"]));
				$images[$key] = $key;
			}
		}
		ksort($images);
		@closedir($handle);
		return ($images);
	}
?>