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
	if (!isset ($oreon))
		exit ();

	function testServiceTemplateExistence ($name = NULL)	{
		global $pearDB;
		global $form;
		$id = NULL;
		if (isset($form))
			$id = $form->getSubmitValue('service_id');
		$name = str_replace('/', "#S#", $name);
		$name = str_replace('\\', "#BS#", $name);
		$DBRESULT =& $pearDB->query("SELECT service_description, service_id FROM service WHERE service_register = '0' AND service_description = '".htmlentities($name, ENT_QUOTES)."'");
		if (PEAR::isError($DBRESULT))
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";
		$service =& $DBRESULT->fetchRow();
		#Modif case
		if ($DBRESULT->numRows() >= 1 && $service["service_id"] == $id)
			return true;
		#Duplicate entry
		else if ($DBRESULT->numRows() >= 1 && $service["service_id"] != $id)
			return false;
		else
			return true;
	}
			
	function testServiceExistence ($name = NULL, $hPars = array(), $hgPars = array())	{
		global $pearDB;
		global $form;
		$id = NULL;
		if (isset($form) && !count($hPars) && !count($hgPars))	{
			$arr = $form->getSubmitValues();
			if (isset($arr["service_id"]))
				$id = $arr["service_id"];
			if (isset($arr["service_hPars"]))
				$hPars = $arr["service_hPars"];
			else
				$hPars = array();
			if (isset($arr["service_hgPars"]))
				$hgPars = $arr["service_hgPars"];
			else
				$hgPars = array();
		}
		$name = str_replace('/', "#S#", $name);
		$name = str_replace('\\', "#BS#", $name);
		foreach ($hPars as $host)	{
			$DBRESULT =& $pearDB->query("SELECT service_id FROM service, host_service_relation hsr WHERE hsr.host_host_id = '".$host."' AND hsr.service_service_id = service_id AND service.service_description = '".htmlentities($name, ENT_QUOTES)."'");
			if (PEAR::isError($DBRESULT))
				print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";
			$service =& $DBRESULT->fetchRow();
			#Duplicate entry
			if ($DBRESULT->numRows() >= 1 && $service["service_id"] != $id)
				return false;
			$DBRESULT->free();
		}
		foreach ($hgPars as $hostgroup)	{
			$DBRESULT =& $pearDB->query("SELECT service_id FROM service, host_service_relation hsr WHERE hsr.hostgroup_hg_id = '".$hostgroup."' AND hsr.service_service_id = service_id AND service.service_description = '".htmlentities($name, ENT_QUOTES)."'");
			if (PEAR::isError($DBRESULT))
				print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";
			$service =& $DBRESULT->fetchRow();
			#Duplicate entry
			if ($DBRESULT->numRows() >= 1 && $service["service_id"] != $id)
				return false;
			$DBRESULT->free();
		}			
		return true;
	}
	
	function enableServiceInDB ($service_id = null, $service_arr = array())	{
		if (!$service_id && !count($service_arr)) return;
		global $pearDB;
		if ($service_id)
			$service_arr = array($service_id=>"1");
		foreach($service_arr as $key=>$value)	{
			$DBRESULT =& $pearDB->query("UPDATE service SET service_activate = '1' WHERE service_id = '".$key."'");
			if (PEAR::isError($DBRESULT))
				print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";
		}
	}
	
	function disableServiceInDB ($service_id = null, $service_arr = array())	{
		if (!$service_id && !count($service_arr)) return;
		global $pearDB;
		if ($service_id)
			$service_arr = array($service_id=>"1");
		foreach($service_arr as $key=>$value)	{
			$DBRESULT =& $pearDB->query("UPDATE service SET service_activate = '0' WHERE service_id = '".$key."'");
			if (PEAR::isError($DBRESULT))
				print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";
		}
	}
	function deleteServiceInDB ($services = array())	{
		global $pearDB;
		global $oreon;
		foreach($services as $key=>$value)	{
			$DBRESULT =& $pearDB->query("SELECT service_id FROM service WHERE service_template_model_stm_id = '".$key."'");
			if (PEAR::isError($DBRESULT))
				print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";
			while ($DBRESULT->fetchInto($row))	{
				$DBRESULT2 =& $pearDB->query("UPDATE service SET service_template_model_stm_id = NULL WHERE service_id = '".$row["service_id"]."'");
				if (PEAR::isError($DBRESULT2))
					print "DB Error : ".$DBRESULT2->getDebugInfo()."<br>";
			}
			$DBRESULT =& $pearDB->query("DELETE FROM service WHERE service_id = '".$key."'");
			if (PEAR::isError($DBRESULT))
				print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";
			$files = glob($oreon->optGen["oreon_rrdbase_path"]."*_".$key.".rrd");
			foreach ($files as $filename)
				unlink ($filename);
		}
	}
	
	function divideGroupedServiceInDB ($service_id = null, $service_arr = array())	{
		if (!$service_id && !count($service_arr)) return;
		global $pearDB, $pearDBO;
		
		if ($service_id)
			$service_arr = array($service_id=>"1");
		foreach($service_arr as $key=>$value)	{
			$lap= 0;
			$DBRESULT =& $pearDB->query("SELECT * FROM host_service_relation WHERE service_service_id = '".$key."'");
			if (PEAR::isError($DBRESULT))
				print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";
			while ($DBRESULT->fetchInto($relation))	{
				if ($relation["hostgroup_hg_id"])	{
					if ($lap)	{
						$sv_id = NULL;
						$DBRESULT2 =& $pearDB->query("DELETE FROM host_service_relation WHERE service_service_id = '".$key."' AND hostgroup_hg_id = '".$relation["hostgroup_hg_id"]."'");
						if (PEAR::isError($DBRESULT2))
							print "DB Error : ".$DBRESULT2->getDebugInfo()."<br>";
						$sv_id = multipleServiceInDB(array($key=>"1"), array($key=>"1"), NULL, 0, $relation["hostgroup_hg_id"], array(), array($relation["hostgroup_hg_id"]=>NULL));
						if ($sv_id)	{
							$hosts = getMyHostGroupHosts($relation["hostgroup_hg_id"]);
							foreach($hosts as $host)	{
								$DBRESULT3 = $pearDBO->query("UPDATE index_data SET service_id = '".$sv_id."' WHERE host_id = '".$host."' AND service_id = '".$key."'");
								if (PEAR::isError($DBRESULT3))
									print "DB Error : ".$DBRESULT3->getDebugInfo()."<br>";
							}
						}
					}
					$lap++;
				}
				else if ($relation["host_host_id"])	{
					if ($lap)	{
						$sv_id = NULL;
						$DBRESULT2 =& $pearDB->query("DELETE FROM host_service_relation WHERE service_service_id = '".$key."' AND host_host_id = '".$relation["host_host_id"]."'");
						if (PEAR::isError($DBRESULT2))
							print "DB Error : ".$DBRESULT2->getDebugInfo()."<br>";
						$sv_id = multipleServiceInDB(array($key=>"1"), array($key=>"1"), $relation["host_host_id"], 0, NULL, array($relation["host_host_id"]=>NULL), array());
						if ($sv_id)	{
							$DBRESULT3 = $pearDBO->query("UPDATE index_data SET service_id = '".$sv_id."' WHERE host_id = '".$relation["host_host_id"]."' AND service_id = '".$key."'");
							if (PEAR::isError($DBRESULT3))
								print "DB Error : ".$DBRESULT3->getDebugInfo()."<br>";
						}
					}
					$lap++;
				}	
			}
		}
	}
		
	function multipleServiceInDB ($services = array(), $nbrDup = array(), $host = NULL, $descKey = 1, $hostgroup = NULL, $hPars = array(), $hgPars = array())	{
		# $descKey param is a flag. If 1, we know we have to rename description because it's a traditionnal duplication. If 0, we don't have to, beacause we duplicate services for an Host duplication
		# Foreach Service
		$maxId["MAX(service_id)"] = NULL;
		foreach($services as $key=>$value)	{
			global $pearDB;
			# Get all information about it
			$DBRESULT =& $pearDB->query("SELECT * FROM service WHERE service_id = '".$key."' LIMIT 1");
			if (PEAR::isError($DBRESULT))
				print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";
			$row = $DBRESULT->fetchRow();			
			$row["service_id"] = '';
			# Loop on the number of Service we want to duplicate
			for ($i = 1; $i <= $nbrDup[$key]; $i++)	{
				$val = null;
				# Create a sentence which contains all the value
				foreach ($row as $key2=>$value2)	{
					if ($key2 == "service_description" && $descKey)
						$service_description = $value2 = $value2."_".$i;
					else if ($key2 == "service_description")
						$service_description = NULL;
					$val ? $val .= ($value2!=NULL?(", '".$value2."'"):", NULL") : $val .= ($value2!=NULL?("'".$value2."'"):"NULL");
				}
				if (!count($hPars))
					$hPars = getMyServiceHosts($key);
				if (!count($hgPars))
					$hgPars = getMyServiceHostGroups($key);
				if (($row["service_register"] && testServiceExistence($service_description, $hPars, $hgPars)) || (!$row["service_register"] && testServiceTemplateExistence($service_description)))	{
					$val ? $rq = "INSERT INTO service VALUES (".$val.")" : $rq = null;
					$DBRESULT =& $pearDB->query($rq);
					if (PEAR::isError($DBRESULT))
						print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";
					$DBRESULT =& $pearDB->query("SELECT MAX(service_id) FROM service");
					if (PEAR::isError($DBRESULT))
						print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";
					$maxId =& $DBRESULT->fetchRow();
					if (isset($maxId["MAX(service_id)"]))	{
						# Host duplication case -> Duplicate the Service for the Host we create
						if ($host)
							$pearDB->query("INSERT INTO host_service_relation VALUES ('', NULL, '".$host."', NULL, '".$maxId["MAX(service_id)"]."')");
						else if ($hostgroup)
							$pearDB->query("INSERT INTO host_service_relation VALUES ('', '".$hostgroup."', NULL, NULL, '".$maxId["MAX(service_id)"]."')");
						else	{
						# Service duplication case -> Duplicate the Service for each relation the base Service have
							$DBRESULT =& $pearDB->query("SELECT DISTINCT host_host_id, hostgroup_hg_id FROM host_service_relation WHERE service_service_id = '".$key."'");
							if (PEAR::isError($DBRESULT))
								print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";
							while($DBRESULT->fetchInto($service))	{
								if ($service["host_host_id"])				
									$DBRESULT2 =& $pearDB->query("INSERT INTO host_service_relation VALUES ('', NULL, '".$service["host_host_id"]."', NULL, '".$maxId["MAX(service_id)"]."')");
								else if ($service["hostgroup_hg_id"])	
									$DBRESULT2 =& $pearDB->query("INSERT INTO host_service_relation VALUES ('', '".$service["hostgroup_hg_id"]."', NULL, NULL, '".$maxId["MAX(service_id)"]."')");
								if (PEAR::isError($DBRESULT2))
									print "DB Error : ".$DBRESULT2->getDebugInfo()."<br>";
							}
						}
						$DBRESULT =& $pearDB->query("SELECT DISTINCT contactgroup_cg_id FROM contactgroup_service_relation WHERE service_service_id = '".$key."'");
						if (PEAR::isError($DBRESULT))
							print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";
						while($DBRESULT->fetchInto($Cg)){
							$DBRESULT2 =& $pearDB->query("INSERT INTO contactgroup_service_relation VALUES ('', '".$Cg["contactgroup_cg_id"]."', '".$maxId["MAX(service_id)"]."')");
							if (PEAR::isError($DBRESULT2))
								print "DB Error : ".$DBRESULT2->getDebugInfo()."<br>";
						}
						$DBRESULT =& $pearDB->query("SELECT DISTINCT host_host_id, hostgroup_hg_id, servicegroup_sg_id FROM servicegroup_relation WHERE service_service_id = '".$key."'");
						if (PEAR::isError($DBRESULT))
							print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";
						while($DBRESULT->fetchInto($Sg)){
							$Sg["host_host_id"] ? $host_id = "'".$Sg["host_host_id"]."'" : $host_id = "NULL";
							$Sg["hostgroup_hg_id"] ? $hg_id = "'".$Sg["hostgroup_hg_id"]."'" : $hg_id = "NULL";
							$DBRESULT2 =& $pearDB->query("INSERT INTO servicegroup_relation (host_host_id, hostgroup_hg_id, service_service_id, servicegroup_sg_id) VALUES (".$host_id.", ".$hg_id.", '".$maxId["MAX(service_id)"]."', '".$Sg["servicegroup_sg_id"]."')");
							if (PEAR::isError($DBRESULT2))
								print "DB Error : ".$DBRESULT2->getDebugInfo()."<br>";
						}
						$DBRESULT =& $pearDB->query("SELECT DISTINCT traps_id FROM traps_service_relation WHERE service_id = '".$key."'");
						if (PEAR::isError($DBRESULT))
							print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";
						while($DBRESULT->fetchInto($traps)){
							$DBRESULT2 =& $pearDB->query("INSERT INTO traps_service_relation VALUES ('', '".$traps["traps_id"]."', '".$maxId["MAX(service_id)"]."')");
							if (PEAR::isError($DBRESULT2))
								print "DB Error : ".$DBRESULT2->getDebugInfo()."<br>";
						}
						$DBRESULT =& $pearDB->query("SELECT * FROM extended_service_information WHERE service_service_id = '".$key."'");
						if (PEAR::isError($DBRESULT))
							print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";
						while($DBRESULT->fetchInto($esi))	{
							$val = null;
							$esi["service_service_id"] = $maxId["MAX(service_id)"];
							$esi["esi_id"] = NULL;
							foreach ($esi as $key2=>$value2)
								$val ? $val .= ($value2!=NULL?(", '".$value2."'"):", NULL") : $val .= ($value2!=NULL?("'".$value2."'"):"NULL");
							$val ? $rq = "INSERT INTO extended_service_information VALUES (".$val.")" : $rq = null;
							$DBRESULT2 =& $pearDB->query($rq);
							if (PEAR::isError($DBRESULT2))
								print "DB Error : ".$DBRESULT2->getDebugInfo()."<br>";
						}
					}
				}
			}
		}
		return ($maxId["MAX(service_id)"]);
	}
	
	function updateServiceInDB ($service_id = NULL, $from_MC = false)	{
		if (!$service_id) return;
		global $form;
		$ret = $form->getSubmitValues();
		if ($from_MC)
			updateService_MC($service_id);
		else
			updateService($service_id, $from_MC);
		# Function for updating cg
		# 1 - MC with deletion of existing cg
		# 2 - MC with addition of new cg
		# 3 - Normal update
		if (isset($ret["mc_mod_cgs"]["mc_mod_cgs"]) && $ret["mc_mod_cgs"]["mc_mod_cgs"])
			updateServiceContactGroup($service_id);
		else if (isset($ret["mc_mod_cgs"]["mc_mod_cgs"]) && !$ret["mc_mod_cgs"]["mc_mod_cgs"])
			updateServiceContactGroup_MC($service_id);	
		else
			updateServiceContactGroup($service_id);	

		# Function for updating host/hg parent
		# 1 - MC with deletion of existing host/hg parent
		# 2 - MC with addition of new host/hg parent
		# 3 - Normal update
		if (isset($ret["mc_mod_Pars"]["mc_mod_Pars"]) && $ret["mc_mod_Pars"]["mc_mod_Pars"])
			updateServiceHost($service_id);
		else if (isset($ret["mc_mod_Pars"]["mc_mod_Pars"]) && !$ret["mc_mod_Pars"]["mc_mod_Pars"])
			updateServiceHost_MC($service_id);
		else
			updateServiceHost($service_id);
					
		# Function for updating sg
		# 1 - MC with deletion of existing sg
		# 2 - MC with addition of new sg
		# 3 - Normal update
		if (isset($ret["mc_mod_sgs"]["mc_mod_sgs"]) && $ret["mc_mod_sgs"]["mc_mod_sgs"])
			updateServiceServiceGroup($service_id);
		else if (isset($ret["mc_mod_sgs"]["mc_mod_sgs"]) && !$ret["mc_mod_sgs"]["mc_mod_sgs"])
			updateServiceServiceGroup_MC($service_id);
		else
			updateServiceServiceGroup($service_id);

		if ($from_MC)
			updateServiceExtInfos_MC($service_id);
		else
			updateServiceExtInfos($service_id);
		# Function for updating traps
		# 1 - MC with deletion of existing traps
		# 2 - MC with addition of new traps
		# 3 - Normal update
		if (isset($ret["mc_mod_traps"]["mc_mod_traps"]) && $ret["mc_mod_traps"]["mc_mod_traps"])
			updateServiceTrap($service_id);
		else if (isset($ret["mc_mod_traps"]["mc_mod_traps"]) && $ret["mc_mod_traps"]["mc_mod_traps"])
			updateServiceTrap_MC($service_id);
		else
			updateServiceTrap($service_id);
		# Function for updating categories
		# 1 - MC with deletion of existing categories
		# 2 - MC with addition of new categories
		# 3 - Normal update
		if (isset($ret["mc_mod_catgeories"]["mc_mod_catgeories"]) && $ret["mc_mod_catgeories"]["mc_mod_catgeories"])
			updateServiceCategories($service_id);
		else if (isset($ret["mc_mod_catgeories"]["mc_mod_catgeories"]) && $ret["mc_mod_catgeories"]["mc_mod_catgeories"])
			updateServiceCategories_MC($service_id);
		else
			updateServiceCategories($service_id);
	}	
	
	function insertServiceInDB ($ret = array())	{
		$service_id = insertService($ret);
		updateServiceContactGroup($service_id, $ret);
		updateServiceHost($service_id, $ret);
		updateServiceServiceGroup($service_id, $ret);
		insertServiceExtInfos($service_id, $ret);
		updateServiceTrap($service_id, $ret);
		updateServiceCategories($service_id, $ret);
		return ($service_id);
	}
	
	function insertService($ret = array())	{
		global $form;
		global $pearDB;
		if (!count($ret))
			$ret = $form->getSubmitValues();
		if (isset($ret["command_command_id_arg"]) && $ret["command_command_id_arg"] != NULL)		{
			$ret["command_command_id_arg"] = str_replace("\n", "#BR#", $ret["command_command_id_arg"]);
			$ret["command_command_id_arg"] = str_replace("\t", "#T#", $ret["command_command_id_arg"]);
			$ret["command_command_id_arg"] = str_replace("\r", "#R#", $ret["command_command_id_arg"]);
			$ret["command_command_id_arg"] = str_replace('/', "#S#", $ret["command_command_id_arg"]);
			$ret["command_command_id_arg"] = str_replace('\\', "#BS#", $ret["command_command_id_arg"]);
		}
		if (isset($ret["command_command_id_arg2"]) && $ret["command_command_id_arg2"] != NULL)		{
			$ret["command_command_id_arg2"] = str_replace("\n", "#BR#", $ret["command_command_id_arg2"]);
			$ret["command_command_id_arg2"] = str_replace("\t", "#T#", $ret["command_command_id_arg2"]);
			$ret["command_command_id_arg2"] = str_replace("\r", "#R#", $ret["command_command_id_arg2"]);
			$ret["command_command_id_arg2"] = str_replace('/', "#S#", $ret["command_command_id_arg2"]);
			$ret["command_command_id_arg2"] = str_replace('\\', "#BS#", $ret["command_command_id_arg2"]);
		}
		if (isset($ret["service_description"]) && $ret["service_description"] != NULL)		{
			$ret["service_description"] = str_replace('/', "#S#", $ret["service_description"]);
			$ret["service_description"] = str_replace('\\', "#BS#", $ret["service_description"]);
		}
		if (isset($ret["service_alias"]) && $ret["service_alias"] != NULL)		{
			$ret["service_alias"] = str_replace('/', "#S#", $ret["service_alias"]);
			$ret["service_alias"] = str_replace('\\', "#BS#", $ret["service_alias"]);
		}
		$rq = "INSERT INTO service " .
				"(service_template_model_stm_id, command_command_id, timeperiod_tp_id, command_command_id2, timeperiod_tp_id2, purge_policy_id, " .
				"service_description, service_alias, service_is_volatile, service_max_check_attempts, service_normal_check_interval, service_retry_check_interval, service_active_checks_enabled, " .
				"service_passive_checks_enabled, service_parallelize_check, service_obsess_over_service, service_check_freshness, service_freshness_threshold, " .
				"service_event_handler_enabled, service_low_flap_threshold, service_high_flap_threshold, service_flap_detection_enabled, " .
				"service_process_perf_data, service_retain_status_information, service_retain_nonstatus_information, service_notification_interval, " .
				"service_notification_options, service_notifications_enabled, service_stalking_options, service_comment, command_command_id_arg, command_command_id_arg2, service_register, service_activate) " .
				"VALUES ( ";
				isset($ret["service_template_model_stm_id"]) && $ret["service_template_model_stm_id"] != NULL ? $rq .= "'".$ret["service_template_model_stm_id"]."', ": $rq .= "NULL, ";
				isset($ret["command_command_id"]) && $ret["command_command_id"] != NULL ? $rq .= "'".$ret["command_command_id"]."', ": $rq .= "NULL, ";
				isset($ret["timeperiod_tp_id"]) && $ret["timeperiod_tp_id"] != NULL ? $rq .= "'".$ret["timeperiod_tp_id"]."', ": $rq .= "NULL, ";
				isset($ret["command_command_id2"]) && $ret["command_command_id2"] != NULL ? $rq .= "'".$ret["command_command_id2"]."', ": $rq .= "NULL, ";
				isset($ret["timeperiod_tp_id2"]) && $ret["timeperiod_tp_id2"] != NULL ? $rq .= "'".$ret["timeperiod_tp_id2"]."', ": $rq .= "NULL, ";
				isset($ret["purge_policy_id"]) && $ret["purge_policy_id"] != NULL ? $rq .= "'".$ret["purge_policy_id"]."', ": $rq .= "NULL, ";
				isset($ret["service_description"]) && $ret["service_description"] != NULL ? $rq .= "'".htmlentities($ret["service_description"], ENT_QUOTES)."', ": $rq .= "NULL, ";
				isset($ret["service_alias"]) && $ret["service_alias"] != NULL ? $rq .= "'".htmlentities($ret["service_alias"], ENT_QUOTES)."', ": $rq .= "NULL, ";
				isset($ret["service_is_volatile"]) && $ret["service_is_volatile"]["service_is_volatile"] != 2 ? $rq .= "'".$ret["service_is_volatile"]["service_is_volatile"]."', ": $rq .= "'2', ";
				isset($ret["service_max_check_attempts"]) && $ret["service_max_check_attempts"] != NULL ? $rq .= "'".$ret["service_max_check_attempts"]."', " : $rq .= "NULL, ";
				isset($ret["service_normal_check_interval"]) && $ret["service_normal_check_interval"] != NULL ? $rq .= "'".$ret["service_normal_check_interval"]."', ": $rq .= "NULL, ";
				isset($ret["service_retry_check_interval"]) && $ret["service_retry_check_interval"] != NULL ? $rq .= "'".$ret["service_retry_check_interval"]."', ": $rq .= "NULL, ";
				isset($ret["service_active_checks_enabled"]["service_active_checks_enabled"]) && $ret["service_active_checks_enabled"]["service_active_checks_enabled"] != 2 ? $rq .= "'".$ret["service_active_checks_enabled"]["service_active_checks_enabled"]."', ": $rq .= "'2', ";
				isset($ret["service_passive_checks_enabled"]["service_passive_checks_enabled"]) && $ret["service_passive_checks_enabled"]["service_passive_checks_enabled"] != 2 ? $rq .= "'".$ret["service_passive_checks_enabled"]["service_passive_checks_enabled"]."', ": $rq .= "'2', ";
				isset($ret["service_parallelize_check"]["service_parallelize_check"]) && $ret["service_parallelize_check"]["service_parallelize_check"] != 2 ? $rq .= "'".$ret["service_parallelize_check"]["service_parallelize_check"]."', ": $rq .= "'2', ";
				isset($ret["service_obsess_over_service"]["service_obsess_over_service"]) && $ret["service_obsess_over_service"]["service_obsess_over_service"] != 2 ? $rq .= "'".$ret["service_obsess_over_service"]["service_obsess_over_service"]."', ": $rq .= "'2', ";
				isset($ret["service_check_freshness"]["service_check_freshness"]) && $ret["service_check_freshness"]["service_check_freshness"] != 2 ? $rq .= "'".$ret["service_check_freshness"]["service_check_freshness"]."', ": $rq .= "'2', ";
				isset($ret["service_freshness_threshold"]) && $ret["service_freshness_threshold"] != NULL ? $rq .= "'".$ret["service_freshness_threshold"]."', ": $rq .= "NULL, ";
				isset($ret["service_event_handler_enabled"]["service_event_handler_enabled"]) && $ret["service_event_handler_enabled"]["service_event_handler_enabled"] != 2 ? $rq .= "'".$ret["service_event_handler_enabled"]["service_event_handler_enabled"]."', ": $rq .= "'2', ";
				isset($ret["service_low_flap_threshold"]) && $ret["service_low_flap_threshold"] != NULL ? $rq .= "'".$ret["service_low_flap_threshold"]."', " : $rq .= "NULL, ";
				isset($ret["service_high_flap_threshold"]) && $ret["service_high_flap_threshold"] != NULL ? $rq .= "'".$ret["service_high_flap_threshold"]."', " : $rq .= "NULL, ";
				isset($ret["service_flap_detection_enabled"]["service_flap_detection_enabled"]) && $ret["service_flap_detection_enabled"]["service_flap_detection_enabled"] != 2 ? $rq .= "'".$ret["service_flap_detection_enabled"]["service_flap_detection_enabled"]."', " : $rq .= "'2', ";
				isset($ret["service_process_perf_data"]["service_process_perf_data"]) && $ret["service_process_perf_data"]["service_process_perf_data"] != 2 ? $rq .= "'".$ret["service_process_perf_data"]["service_process_perf_data"]."', " : $rq .= "'2', ";
				isset($ret["service_retain_status_information"]["service_retain_status_information"]) && $ret["service_retain_status_information"]["service_retain_status_information"] != 2 ? $rq .= "'".$ret["service_retain_status_information"]["service_retain_status_information"]."', " : $rq .= "'2', ";
				isset($ret["service_retain_nonstatus_information"]["service_retain_nonstatus_information"]) && $ret["service_retain_nonstatus_information"]["service_retain_nonstatus_information"] != 2 ? $rq .= "'".$ret["service_retain_nonstatus_information"]["service_retain_nonstatus_information"]."', " : $rq .= "'2', ";
				isset($ret["service_notification_interval"]) && $ret["service_notification_interval"] != NULL ? $rq .= "'".$ret["service_notification_interval"]."', " : $rq .= "NULL, ";
				isset($ret["service_notifOpts"]) && $ret["service_notifOpts"] != NULL ? $rq .= "'".implode(",", array_keys($ret["service_notifOpts"]))."', " : $rq .= "NULL, ";
				isset($ret["service_notifications_enabled"]["service_notifications_enabled"]) && $ret["service_notifications_enabled"]["service_notifications_enabled"] != 2 ? $rq .= "'".$ret["service_notifications_enabled"]["service_notifications_enabled"]."', " : $rq .= "'2', ";
				isset($ret["service_stalOpts"]) && $ret["service_stalOpts"] != NULL ? $rq .= "'".implode(",", array_keys($ret["service_stalOpts"]))."', " : $rq .= "NULL, ";
				
				if (isset($ret["service_comment"]) && $ret["service_comment"])	{
					$ret["service_comment"] = str_replace('/', "#S#", $ret["service_comment"]);
					$ret["service_comment"] = str_replace('\\', "#BS#", $ret["service_comment"]);
				}
				isset($ret["service_comment"]) && $ret["service_comment"] != NULL ? $rq .= "'".htmlentities($ret["service_comment"], ENT_QUOTES)."', " : $rq .= "NULL, ";
				
				isset($ret["command_command_id_arg"]) && $ret["command_command_id_arg"] != NULL ? $rq .= "'".htmlentities($ret["command_command_id_arg"], ENT_QUOTES)."', " : $rq .= "NULL, ";
				isset($ret["command_command_id_arg2"]) && $ret["command_command_id_arg2"] != NULL ? $rq .= "'".htmlentities($ret["command_command_id_arg2"], ENT_QUOTES)."', " : $rq .= "NULL, ";
				isset($ret["service_register"]["service_register"]) && $ret["service_register"]["service_register"] != NULL ? $rq .= "'".$ret["service_register"]["service_register"]."', " : $rq .= "NULL, ";
				isset($ret["service_activate"]["service_activate"]) && $ret["service_activate"]["service_activate"] != NULL ? $rq .= "'".$ret["service_activate"]["service_activate"]."'" : $rq .= "NULL";
				$rq .= ")";
		$DBRESULT =& $pearDB->query($rq);
		if (PEAR::isError($DBRESULT))
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";
		$DBRESULT =& $pearDB->query("SELECT MAX(service_id) FROM service");
		if (PEAR::isError($DBRESULT))
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";
		$service_id = $DBRESULT->fetchRow();
		return ($service_id["MAX(service_id)"]);
	}
	
	function insertServiceExtInfos($service_id = null, $ret)	{
		if (!$service_id) return;
		global $form;
		global $pearDB;
		if (!count($ret))
			$ret = $form->getSubmitValues();
		$rq = 	"INSERT INTO `extended_service_information` " .
				"( `esi_id` , `service_service_id`, `esi_notes` , `esi_notes_url` , " .
				"`esi_action_url` , `esi_icon_image` , `esi_icon_image_alt`, `graph_id` )" .
				"VALUES ( ";
		$rq .= "NULL, ".$service_id.", ";
		isset($ret["esi_notes"]) && $ret["esi_notes"] != NULL ? $rq .= "'".htmlentities($ret["esi_notes"], ENT_QUOTES)."', ": $rq .= "NULL, ";
		isset($ret["esi_notes_url"]) && $ret["esi_notes_url"] != NULL ? $rq .= "'".htmlentities($ret["esi_notes_url"], ENT_QUOTES)."', ": $rq .= "NULL, ";
		isset($ret["esi_action_url"]) && $ret["esi_action_url"] != NULL ? $rq .= "'".htmlentities($ret["esi_action_url"], ENT_QUOTES)."', ": $rq .= "NULL, ";
		isset($ret["esi_icon_image"]) && $ret["esi_icon_image"] != NULL ? $rq .= "'".htmlentities($ret["esi_icon_image"], ENT_QUOTES)."', ": $rq .= "NULL, ";
		isset($ret["esi_icon_image_alt"]) && $ret["esi_icon_image_alt"] != NULL ? $rq .= "'".htmlentities($ret["esi_icon_image_alt"], ENT_QUOTES)."', ": $rq .= "NULL, ";
		isset($ret["graph_id"]) && $ret["graph_id"] != NULL ? $rq .= "'".$ret["graph_id"]."'": $rq .= "NULL";
		$rq .= ")";
		$DBRESULT =& $pearDB->query($rq);
		if (PEAR::isError($DBRESULT))
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";
	}
	
	function updateService($service_id = null, $from_MC = false)	{
		if (!$service_id) return;
		global $form;
		global $pearDB;
		$ret = array();
		$ret = $form->getSubmitValues();
		if (isset($ret["command_command_id_arg"]) && $ret["command_command_id_arg"] != NULL)		{
			$ret["command_command_id_arg"] = str_replace("\n", "#BR#", $ret["command_command_id_arg"]);
			$ret["command_command_id_arg"] = str_replace("\t", "#T#", $ret["command_command_id_arg"]);
			$ret["command_command_id_arg"] = str_replace("\r", "#R#", $ret["command_command_id_arg"]);
			$ret["command_command_id_arg"] = str_replace('/', "#S#", $ret["command_command_id_arg"]);
			$ret["command_command_id_arg"] = str_replace('\\', "#BS#", $ret["command_command_id_arg"]);
		}		
		if (isset($ret["command_command_id_arg2"]) && $ret["command_command_id_arg2"] != NULL)		{
			$ret["command_command_id_arg2"] = str_replace("\n", "#BR#", $ret["command_command_id_arg2"]);
			$ret["command_command_id_arg2"] = str_replace("\t", "#T#", $ret["command_command_id_arg2"]);
			$ret["command_command_id_arg2"] = str_replace("\r", "#R#", $ret["command_command_id_arg2"]);
			$ret["command_command_id_arg2"] = str_replace('/', "#S#", $ret["command_command_id_arg2"]);
			$ret["command_command_id_arg2"] = str_replace('\\', "#BS#", $ret["command_command_id_arg2"]);
		}		
		if (isset($ret["service_description"]) && $ret["service_description"] != NULL)		{
			$ret["service_description"] = str_replace('/', "#S#", $ret["service_description"]);
			$ret["service_description"] = str_replace('\\', "#BS#", $ret["service_description"]);
		}
		if (isset($ret["service_alias"]) && $ret["service_alias"] != NULL)		{
			$ret["service_alias"] = str_replace('/', "#S#", $ret["service_alias"]);
			$ret["service_alias"] = str_replace('\\', "#BS#", $ret["service_alias"]);
		}
		$rq = "UPDATE service SET " ;
		$rq .= "service_template_model_stm_id = ";
		isset($ret["service_template_model_stm_id"]) && $ret["service_template_model_stm_id"] != NULL ? $rq .= "'".$ret["service_template_model_stm_id"]."', ": $rq .= "NULL, ";
		$rq .= "command_command_id = ";		
		isset($ret["command_command_id"]) && $ret["command_command_id"] != NULL ? $rq .= "'".$ret["command_command_id"]."', ": $rq .= "NULL, ";
		$rq .= "timeperiod_tp_id = ";
		isset($ret["timeperiod_tp_id"]) && $ret["timeperiod_tp_id"] != NULL ? $rq .= "'".$ret["timeperiod_tp_id"]."', ": $rq .= "NULL, ";
		$rq .= "command_command_id2 = ";
		isset($ret["command_command_id2"]) && $ret["command_command_id2"] != NULL ? $rq .= "'".$ret["command_command_id2"]."', ": $rq .= "NULL, ";
		$rq .= "timeperiod_tp_id2 = ";
		isset($ret["timeperiod_tp_id2"]) && $ret["timeperiod_tp_id2"] != NULL ? $rq .= "'".$ret["timeperiod_tp_id2"]."', ": $rq .= "NULL, ";
		$rq .= "purge_policy_id = ";
		isset($ret["purge_policy_id"]) && $ret["purge_policy_id"] != NULL ? $rq .= "'".$ret["purge_policy_id"]."', ": $rq .= "NULL, ";
		# If we are doing a MC, we don't have to set name and alias field
		if (!$from_MC)	{
			$rq .= "service_description = ";
			isset($ret["service_description"]) && $ret["service_description"] != NULL ? $rq .= "'".htmlentities($ret["service_description"], ENT_QUOTES)."', ": $rq .= "NULL, ";
		}
		$rq .= "service_alias = ";
		isset($ret["service_alias"]) && $ret["service_alias"] != NULL ? $rq .= "'".htmlentities($ret["service_alias"], ENT_QUOTES)."', ": $rq .= "NULL, ";
		$rq .= "service_is_volatile = ";
		isset($ret["service_is_volatile"]["service_is_volatile"]) && $ret["service_is_volatile"]["service_is_volatile"] != 2 ? $rq .= "'".$ret["service_is_volatile"]["service_is_volatile"]."', ": $rq .= "'2', ";
		$rq .= "service_max_check_attempts = ";
		isset($ret["service_max_check_attempts"]) && $ret["service_max_check_attempts"] != NULL ? $rq .= "'".$ret["service_max_check_attempts"]."', " : $rq .= "NULL, ";
		$rq .= "service_normal_check_interval = ";
		isset($ret["service_normal_check_interval"]) && $ret["service_normal_check_interval"] != NULL ? $rq .= "'".$ret["service_normal_check_interval"]."', ": $rq .= "NULL, ";
		$rq .= "service_retry_check_interval = ";
		isset($ret["service_retry_check_interval"]) && $ret["service_retry_check_interval"] != NULL ? $rq .= "'".$ret["service_retry_check_interval"]."', ": $rq .= "NULL, ";
		$rq .= "service_active_checks_enabled = ";
		isset($ret["service_active_checks_enabled"]["service_active_checks_enabled"]) && $ret["service_active_checks_enabled"]["service_active_checks_enabled"] != 2 ? $rq .= "'".$ret["service_active_checks_enabled"]["service_active_checks_enabled"]."', ": $rq .= "'2', ";
		$rq .= "service_passive_checks_enabled = ";
		isset($ret["service_passive_checks_enabled"]["service_passive_checks_enabled"]) && $ret["service_passive_checks_enabled"]["service_passive_checks_enabled"] != 2 ? $rq .= "'".$ret["service_passive_checks_enabled"]["service_passive_checks_enabled"]."', ": $rq .= "'2', ";
		$rq .= "service_parallelize_check = ";
		isset($ret["service_parallelize_check"]["service_parallelize_check"]) && $ret["service_parallelize_check"]["service_parallelize_check"] != 2 ? $rq .= "'".$ret["service_parallelize_check"]["service_parallelize_check"]."', ": $rq .= "'2', ";
		$rq .= "service_obsess_over_service = ";
		isset($ret["service_obsess_over_service"]["service_obsess_over_service"]) && $ret["service_obsess_over_service"]["service_obsess_over_service"] != 2 ? $rq .= "'".$ret["service_obsess_over_service"]["service_obsess_over_service"]."', ": $rq .= "'2', ";
		$rq .= "service_check_freshness = ";
		isset($ret["service_check_freshness"]["service_check_freshness"]) && $ret["service_check_freshness"]["service_check_freshness"] != 2 ? $rq .= "'".$ret["service_check_freshness"]["service_check_freshness"]."', ": $rq .= "'2', ";
		$rq .= "service_freshness_threshold = ";
		isset($ret["service_freshness_threshold"]) && $ret["service_freshness_threshold"] != NULL ? $rq .= "'".$ret["service_freshness_threshold"]."', ": $rq .= "NULL, ";
		$rq .= "service_event_handler_enabled = ";
		isset($ret["service_event_handler_enabled"]["service_event_handler_enabled"]) && $ret["service_event_handler_enabled"]["service_event_handler_enabled"] != 2 ? $rq .= "'".$ret["service_event_handler_enabled"]["service_event_handler_enabled"]."', ": $rq .= "'2', ";
		$rq .= "service_low_flap_threshold = ";
		isset($ret["service_low_flap_threshold"]) && $ret["service_low_flap_threshold"] != NULL ? $rq .= "'".$ret["service_low_flap_threshold"]."', " : $rq .= "NULL, ";
		$rq .= "service_high_flap_threshold = ";
		isset($ret["service_high_flap_threshold"]) && $ret["service_high_flap_threshold"] != NULL ? $rq .= "'".$ret["service_high_flap_threshold"]."', " : $rq .= "NULL, ";
		$rq .= "service_flap_detection_enabled = ";
		isset($ret["service_flap_detection_enabled"]["service_flap_detection_enabled"]) && $ret["service_flap_detection_enabled"]["service_flap_detection_enabled"] != 2 ? $rq .= "'".$ret["service_flap_detection_enabled"]["service_flap_detection_enabled"]."', " : $rq .= "'2', ";
		$rq .= "service_process_perf_data = ";
		isset($ret["service_process_perf_data"]["service_process_perf_data"]) && $ret["service_process_perf_data"]["service_process_perf_data"] != 2 ? $rq .= "'".$ret["service_process_perf_data"]["service_process_perf_data"]."', " : $rq .= "'2', ";
		$rq .= "service_retain_status_information = ";
		isset($ret["service_retain_status_information"]["service_retain_status_information"]) && $ret["service_retain_status_information"]["service_retain_status_information"] != 2 ? $rq .= "'".$ret["service_retain_status_information"]["service_retain_status_information"]."', " : $rq .= "'2', ";
		$rq .= "service_retain_nonstatus_information = ";
		isset($ret["service_retain_nonstatus_information"]["service_retain_nonstatus_information"]) && $ret["service_retain_nonstatus_information"]["service_retain_nonstatus_information"] != 2 ? $rq .= "'".$ret["service_retain_nonstatus_information"]["service_retain_nonstatus_information"]."', " : $rq .= "'2', ";
		$rq .= "service_notification_interval = ";
		isset($ret["service_notification_interval"]) && $ret["service_notification_interval"] != NULL ? $rq .= "'".$ret["service_notification_interval"]."', " : $rq .= "NULL, ";
		$rq .= "service_notification_options = ";
		isset($ret["service_notifOpts"]) && $ret["service_notifOpts"] != NULL ? $rq .= "'".implode(",", array_keys($ret["service_notifOpts"]))."', " : $rq .= "NULL, ";
		$rq .= "service_notifications_enabled = ";
		isset($ret["service_notifications_enabled"]["service_notifications_enabled"]) && $ret["service_notifications_enabled"]["service_notifications_enabled"] != 2 ? $rq .= "'".$ret["service_notifications_enabled"]["service_notifications_enabled"]."', " : $rq .= "'2', ";
		$rq .= "service_stalking_options = ";
		isset($ret["service_stalOpts"]) && $ret["service_stalOpts"] != NULL ? $rq .= "'".implode(",", array_keys($ret["service_stalOpts"]))."', " : $rq .= "NULL, ";
		
		$rq .= "service_comment = ";
		$ret["service_comment"] = str_replace("/", '#S#', $ret["service_comment"]);
		$ret["service_comment"] = str_replace("\\", '#BS#', $ret["service_comment"]);				
		isset($ret["service_comment"]) && $ret["service_comment"] != NULL ? $rq .= "'".htmlentities($ret["service_comment"], ENT_QUOTES)."', " : $rq .= "NULL, ";

		$rq .= "command_command_id_arg = ";
		isset($ret["command_command_id_arg"]) && $ret["command_command_id_arg"] != NULL ? $rq .= "'".htmlentities($ret["command_command_id_arg"], ENT_QUOTES)."', " : $rq .= "NULL, ";
		$rq .= "command_command_id_arg2 = ";
		isset($ret["command_command_id_arg2"]) && $ret["command_command_id_arg2"] != NULL ? $rq .= "'".htmlentities($ret["command_command_id_arg2"], ENT_QUOTES)."', " : $rq .= "NULL, ";
		$rq .= "service_register = ";
		isset($ret["service_register"]["service_register"]) && $ret["service_register"]["service_register"] != NULL ? $rq .= "'".$ret["service_register"]["service_register"]."', " : $rq .= "NULL, ";
		$rq .= "service_activate = ";
		isset($ret["service_activate"]["service_activate"]) && $ret["service_activate"]["service_activate"] != NULL ? $rq .= "'".$ret["service_activate"]["service_activate"]."'" : $rq .= "NULL ";
		$rq .= "WHERE service_id = '".$service_id."'";
		$DBRESULT =& $pearDB->query($rq);
		if (PEAR::isError($DBRESULT))
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";
	}
	
	function updateService_MC($service_id = null)	{
		if (!$service_id) return;
		global $form;
		global $pearDB;
		$ret = array();
		$ret = $form->getSubmitValues();
		if (isset($ret["command_command_id_arg"]) && $ret["command_command_id_arg"] != NULL)		{
			$ret["command_command_id_arg"] = str_replace("\n", "#BR#", $ret["command_command_id_arg"]);
			$ret["command_command_id_arg"] = str_replace("\t", "#T#", $ret["command_command_id_arg"]);
			$ret["command_command_id_arg"] = str_replace("\r", "#R#", $ret["command_command_id_arg"]);
			$ret["command_command_id_arg"] = str_replace('/', "#S#", $ret["command_command_id_arg"]);
			$ret["command_command_id_arg"] = str_replace('\\', "#BS#", $ret["command_command_id_arg"]);
		}		
		if (isset($ret["command_command_id_arg2"]) && $ret["command_command_id_arg2"] != NULL)		{
			$ret["command_command_id_arg2"] = str_replace("\n", "#BR#", $ret["command_command_id_arg2"]);
			$ret["command_command_id_arg2"] = str_replace("\t", "#T#", $ret["command_command_id_arg2"]);
			$ret["command_command_id_arg2"] = str_replace("\r", "#R#", $ret["command_command_id_arg2"]);"', " ;
			$ret["command_command_id_arg2"] = str_replace('/', "#S#", $ret["command_command_id_arg2"]);
			$ret["command_command_id_arg2"] = str_replace('\\', "#BS#", $ret["command_command_id_arg2"]);
		}		
		if (isset($ret["service_description"]) && $ret["service_description"] != NULL)		{
			$ret["service_description"] = str_replace('/', "#S#", $ret["service_description"]);
			$ret["service_description"] = str_replace('\\', "#BS#", $ret["service_description"]);
		}
		if (isset($ret["service_alias"]) && $ret["service_alias"] != NULL)		{
			$ret["service_alias"] = str_replace('/', "#S#", $ret["service_alias"]);
			$ret["service_alias"] = str_replace('\\', "#BS#", $ret["service_alias"]);
		}
		$rq = "UPDATE service SET ";
		if (isset($ret["service_template_model_stm_id"]) && $ret["service_template_model_stm_id"] != NULL) $rq .= "service_template_model_stm_id = '".$ret["service_template_model_stm_id"]."', ";
		if (isset($ret["command_command_id"]) && $ret["command_command_id"] != NULL) $rq .= "command_command_id = '".$ret["command_command_id"]."', ";
		if (isset($ret["timeperiod_tp_id"]) && $ret["timeperiod_tp_id"] != NULL) $rq .= "timeperiod_tp_id = '".$ret["timeperiod_tp_id"]."', ";
		if (isset($ret["command_command_id2"]) && $ret["command_command_id2"] != NULL) $rq .= "command_command_id2 = '".$ret["command_command_id2"]."', ";
		if (isset($ret["timeperiod_tp_id2"]) && $ret["timeperiod_tp_id2"] != NULL) $rq .= "timeperiod_tp_id2 = '".$ret["timeperiod_tp_id2"]."', ";
		if (isset($ret["purge_policy_id"]) && $ret["purge_policy_id"] != NULL) $rq .= "purge_policy_id = '".$ret["purge_policy_id"]."', ";
		if (isset($ret["service_alias"]) && $ret["service_alias"] != NULL) $rq .= "service_alias = '".$ret["service_alias"]."', ";
		if (isset($ret["service_is_volatile"]["service_is_volatile"]) && $ret["service_is_volatile"]["service_is_volatile"] != 2) $rq .= "service_is_volatile = '".$ret["service_is_volatile"]["service_is_volatile"]."', ";
		if (isset($ret["service_max_check_attempts"]) && $ret["service_max_check_attempts"] != NULL) $rq .= "service_max_check_attempts = '".$ret["service_max_check_attempts"]."', ";
		if (isset($ret["service_normal_check_interval"]) && $ret["service_normal_check_interval"] != NULL) $rq .= "service_normal_check_interval = '".$ret["service_normal_check_interval"]."', ";
		if (isset($ret["service_retry_check_interval"]) && $ret["service_retry_check_interval"] != NULL) $rq .= "service_retry_check_interval = '".$ret["service_retry_check_interval"]."', ";
		if (isset($ret["service_active_checks_enabled"]["service_active_checks_enabled"])) $rq .= "service_active_checks_enabled = '".$ret["service_active_checks_enabled"]["service_active_checks_enabled"]."', ";
		if (isset($ret["service_passive_checks_enabled"]["service_passive_checks_enabled"])) $rq .= "service_passive_checks_enabled = '".$ret["service_passive_checks_enabled"]["service_passive_checks_enabled"]."', ";
		if (isset($ret["service_parallelize_check"]["service_parallelize_check"])) $rq .= "service_parallelize_check = '".$ret["service_parallelize_check"]["service_parallelize_check"]."', ";
		if (isset($ret["service_obsess_over_service"]["service_obsess_over_service"])) $rq .= "service_obsess_over_service = '".$ret["service_obsess_over_service"]["service_obsess_over_service"]."', ";
		if (isset($ret["service_check_freshness"]["service_check_freshness"])) $rq .= "service_check_freshness = '".$ret["service_check_freshness"]["service_check_freshness"]."', ";
		if (isset($ret["service_freshness_threshold"]) && $ret["service_freshness_threshold"] != NULL) $rq .= "service_freshness_threshold = '".$ret["service_freshness_threshold"]."', ";
		if (isset($ret["service_event_handler_enabled"]["service_event_handler_enabled"])) $rq .= "service_event_handler_enabled = '".$ret["service_event_handler_enabled"]["service_event_handler_enabled"]."', ";
		if (isset($ret["service_low_flap_threshold"]) && $ret["service_low_flap_threshold"] != NULL) $rq .= "service_low_flap_threshold = '".$ret["service_low_flap_threshold"]."', ";
		if (isset($ret["service_high_flap_threshold"]) && $ret["service_high_flap_threshold"] != NULL) $rq .= "service_high_flap_threshold = '".$ret["service_high_flap_threshold"]."', ";
		if (isset($ret["service_flap_detection_enabled"]["service_flap_detection_enabled"])) $rq .= "service_flap_detection_enabled = '".$ret["service_flap_detection_enabled"]["service_flap_detection_enabled"]."', ";
		if (isset($ret["service_process_perf_data"]["service_process_perf_data"])) $rq .= "service_process_perf_data = '".$ret["service_process_perf_data"]["service_process_perf_data"]."', ";
		if (isset($ret["service_retain_status_information"]["service_retain_status_information"])) $rq .= "service_retain_status_information = '".$ret["service_retain_status_information"]["service_retain_status_information"]."', ";
		if (isset($ret["service_retain_nonstatus_information"]["service_retain_nonstatus_information"])) $rq .= "service_retain_nonstatus_information = '".$ret["service_retain_nonstatus_information"]["service_retain_nonstatus_information"]."', ";
		if (isset($ret["service_notification_interval"]) && $ret["service_notification_interval"] != NULL) $rq .= "service_notification_interval = '".$ret["service_notification_interval"]."', ";
		if (isset($ret["service_notifOpts"]) && $ret["service_notifOpts"] != NULL) $rq .= "service_notification_options = '".implode(",", array_keys($ret["service_notifOpts"]))."', ";
		if (isset($ret["service_notifications_enabled"]["service_notifications_enabled"])) $rq .= "service_notifications_enabled = '".$ret["service_notifications_enabled"]["service_notifications_enabled"]."', ";
		if (isset($ret["service_stalOpts"]) && $ret["service_stalOpts"] != NULL) $rq .= "service_stalking_options = '".implode(",", array_keys($ret["service_stalOpts"]))."', ";
		if (isset($ret["service_comment"]) && $ret["service_comment"] != NULL) $rq .= "service_comment = '".htmlentities($ret["service_comment"], ENT_QUOTES)."', ";
		if (isset($ret["command_command_id_arg"]) && $ret["command_command_id_arg"] != NULL) $rq .= "command_command_id_arg = '".htmlentities($ret["command_command_id_arg"], ENT_QUOTES)."', ";
		if (isset($ret["command_command_id_arg2"]) && $ret["command_command_id_arg2"] != NULL) $rq .= "command_command_id_arg2 = '".htmlentities($ret["command_command_id_arg2"], ENT_QUOTES)."', ";
		if (isset($ret["service_register"]["service_register"]) && $ret["service_register"]["service_register"] != NULL) $rq .= "service_register = '".$ret["service_register"]["service_register"]."', ";
		if (isset($ret["service_activate"]["service_activate"]) && $ret["service_activate"]["service_activate"] != NULL) $rq .= "service_activate = '".$ret["service_activate"]["service_activate"]."', ";
		if (strcmp("UPDATE service SET ", $rq))	{
			# Delete last ',' in request
			$rq[strlen($rq)-2] = " ";
			$rq .= "WHERE service_id = '".$service_id."'";
			$DBRESULT =& $pearDB->query($rq);
			if (PEAR::isError($DBRESULT))
				print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";
		}
	}
		
	function updateServiceContactGroup($service_id = null, $ret = array())	{
		if (!$service_id) return;
		global $form;
		global $pearDB;
		$rq = "DELETE FROM contactgroup_service_relation ";
		$rq .= "WHERE service_service_id = '".$service_id."'";
		$DBRESULT =& $pearDB->query($rq);
		if (PEAR::isError($DBRESULT))
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";
		if (isset($ret["service_cgs"]))
			$ret = $ret["service_cgs"];
		else
			$ret = $form->getSubmitValue("service_cgs");
		for($i = 0; $i < count($ret); $i++)	{
			$rq = "INSERT INTO contactgroup_service_relation ";
			$rq .= "(contactgroup_cg_id, service_service_id) ";
			$rq .= "VALUES ";
			$rq .= "('".$ret[$i]."', '".$service_id."')";
			$DBRESULT =& $pearDB->query($rq);
			if (PEAR::isError($DBRESULT))
				print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";
		}
	}

	# For massive change. We just add the new list if the elem doesn't exist yet
	function updateServiceContactGroup_MC($service_id = null)	{
		if (!$service_id) return;
		global $form;
		global $pearDB;
		$rq = "SELECT * FROM contactgroup_service_relation ";
		$rq .= "WHERE service_service_id = '".$service_id."'";
		$DBRESULT =& $pearDB->query($rq);
		if (PEAR::isError($DBRESULT))
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";
		$cgs = array();
		while($DBRESULT->fetchInto($arr))
			$cgs[$arr["contactgroup_cg_id"]] = $arr["contactgroup_cg_id"];
		$ret = $form->getSubmitValue("service_cgs");
		for($i = 0; $i < count($ret); $i++)	{
			if (!isset($cgs[$ret[$i]]))	{
				$rq = "INSERT INTO contactgroup_service_relation ";
				$rq .= "(contactgroup_cg_id, service_service_id) ";
				$rq .= "VALUES ";
				$rq .= "('".$ret[$i]."', '".$service_id."')";
				$DBRESULT =& $pearDB->query($rq);
				if (PEAR::isError($DBRESULT))
					print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";
			}
		}
	}
	
	function updateServiceServiceGroup($service_id = null, $ret = array())	{
		if (!$service_id) return;
		global $form;
		global $pearDB;
		$rq = "DELETE FROM servicegroup_relation ";
		$rq .= "WHERE service_service_id = '".$service_id."'";
		$DBRESULT =& $pearDB->query($rq);
		if (PEAR::isError($DBRESULT))
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";
		if (isset($ret["service_sgs"]))
			$ret = $ret["service_sgs"];
		else
			$ret = $form->getSubmitValue("service_sgs");
		for($i = 0; $i < count($ret); $i++)	{
			/* We need to record each relation for host / hostgroup selected */			
			if (isset($ret["service_hPars"]))
				$ret1 = $ret["service_hPars"];
			else
				$ret1 = getMyServiceHosts($service_id);
			if (isset($ret["service_hgPars"]))
				$ret2 = $ret["service_hgPars"];
			else
				$ret2 = getMyServiceHostGroups($service_id);
			 if (count($ret2))
				foreach($ret2 as $key=>$value)	{
					$rq = "INSERT INTO servicegroup_relation ";
					$rq .= "(host_host_id, hostgroup_hg_id, service_service_id, servicegroup_sg_id) ";
					$rq .= "VALUES ";
					$rq .= "(NULL, '".$value."', '".$service_id."', '".$ret[$i]."')";
					$DBRESULT =& $pearDB->query($rq);
					if (PEAR::isError($DBRESULT))
						print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";
				}
			else if (count($ret1))
				foreach($ret1 as $key=>$value)	{
					$rq = "INSERT INTO servicegroup_relation ";
					$rq .= "(host_host_id, hostgroup_hg_id, service_service_id, servicegroup_sg_id) ";
					$rq .= "VALUES ";
					$rq .= "('".$value."', NULL, '".$service_id."', '".$ret[$i]."')";
					$DBRESULT =& $pearDB->query($rq);
					if (PEAR::isError($DBRESULT))
						print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";
				}
		}
	}	

	# For massive change. We just add the new list if the elem doesn't exist yet
	function updateServiceServiceGroup_MC($service_id = null)	{
		if (!$service_id) return;
		global $form;
		global $pearDB;
		$rq = "SELECT * FROM servicegroup_relation ";
		$rq .= "WHERE service_service_id = '".$service_id."'";
		$DBRESULT =& $pearDB->query($rq);
		if (PEAR::isError($DBRESULT))
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";
		$hsgs = array();
		$hgsgs = array();
		while($DBRESULT->fetchInto($arr))	{
			if ($arr["host_host_id"])
				$hsgs[$arr["host_host_id"]] = $arr["host_host_id"];
			if ($arr["hostgroup_hg_id"])
				$hgsgs[$arr["hostgroup_hg_id"]] = $arr["hostgroup_hg_id"];
		}
		$ret = $form->getSubmitValue("service_sgs");
		for($i = 0; $i < count($ret); $i++)	{
			/* We need to record each relation for host / hostgroup selected */
			$ret1 = getMyServiceHosts($service_id);
			$ret2 = getMyServiceHostGroups($service_id);
			 if (count($ret2))
				foreach($ret2 as $hg)	{
					if (!isset($hgsgs[$hg]))	{
						$rq = "INSERT INTO servicegroup_relation ";
						$rq .= "(host_host_id, hostgroup_hg_id, service_service_id, servicegroup_sg_id) ";
						$rq .= "VALUES ";
						$rq .= "(NULL, '".$hg."', '".$service_id."', '".$ret[$i]."')";
						$DBRESULT =& $pearDB->query($rq);
						if (PEAR::isError($DBRESULT))
							print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";
					}
				}
			else if (count($ret1))
				foreach($ret1 as $h) 	{
					if (!isset($hsgs[$h]))	{
						$rq = "INSERT INTO servicegroup_relation ";
						$rq .= "(host_host_id, hostgroup_hg_id, service_service_id, servicegroup_sg_id) ";
						$rq .= "VALUES ";
						$rq .= "('".$h."', NULL, '".$service_id."', '".$ret[$i]."')";
						$DBRESULT =& $pearDB->query($rq);
						if (PEAR::isError($DBRESULT))
							print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";
					}
				}
		}
	}	
	
	function updateServiceTrap($service_id = null, $ret = array())	{
		if (!$service_id) return;
		global $form;
		global $pearDB;
		$rq = "DELETE FROM traps_service_relation ";
		$rq .= "WHERE service_id = '".$service_id."'";
		$DBRESULT =& $pearDB->query($rq);
		if (PEAR::isError($DBRESULT))
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";
		if (isset($ret["service_traps"]))
			$ret = $ret["service_traps"];
		else
			$ret = $form->getSubmitValue("service_traps");
		for($i = 0; $i < count($ret); $i++)	{
			$rq = "INSERT INTO traps_service_relation ";
			$rq .= "(traps_id, service_id) ";
			$rq .= "VALUES ";
			$rq .= "('".$ret[$i]."', '".$service_id."')";
			$DBRESULT =& $pearDB->query($rq);
			if (PEAR::isError($DBRESULT))
				print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";
		}
	}	
	
	# For massive change. We just add the new list if the elem doesn't exist yet
	function updateServiceTrap_MC($service_id = null)	{
		if (!$service_id) return;
		global $form;
		global $pearDB;
		$rq = "SELECT * FROM traps_service_relation ";
		$rq .= "WHERE service_id = '".$service_id."'";
		$DBRESULT =& $pearDB->query($rq);
		if (PEAR::isError($DBRESULT))
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";
		$traps = array();
		while($DBRESULT->fetchInto($arr))
			$traps[$arr["traps_id"]] = $arr["traps_id"];
		$ret = $form->getSubmitValue("service_traps");
		for($i = 0; $i < count($ret); $i++)	{
			if (!isset($traps[$ret[$i]]))	{
				$rq = "INSERT INTO traps_service_relation ";
				$rq .= "(traps_id, service_id) ";
				$rq .= "VALUES ";
				$rq .= "('".$ret[$i]."', '".$service_id."')";
				$DBRESULT =& $pearDB->query($rq);
				if (PEAR::isError($DBRESULT))
					print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";
			}
		}
	}
	
	function updateServiceHost($service_id = null, $ret = array())	{
		if (!$service_id) return;
		global $form;
		global $pearDB;
		$rq = "DELETE FROM host_service_relation ";
		$rq .= "WHERE service_service_id = '".$service_id."'";
		$DBRESULT =& $pearDB->query($rq);
		if (PEAR::isError($DBRESULT))
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";
		$ret1 = array();
		$ret2 = array();
		if (isset($ret["service_hPars"]))
			$ret1 = $ret["service_hPars"];
		else
			$ret1 = $form->getSubmitValue("service_hPars");
		if (isset($ret["service_hgPars"]))
			$ret2 = $ret["service_hgPars"];
		else
			$ret2 = $form->getSubmitValue("service_hgPars");
		 if (count($ret2))
			for($i = 0; $i < count($ret2); $i++)	{
				$rq = "INSERT INTO host_service_relation ";
				$rq .= "(hostgroup_hg_id, host_host_id, servicegroup_sg_id, service_service_id) ";
				$rq .= "VALUES ";
				$rq .= "('".$ret2[$i]."', NULL, NULL, '".$service_id."')";
				$DBRESULT =& $pearDB->query($rq);
				if (PEAR::isError($DBRESULT))
					print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";
			}
		else if (count($ret1))
			for($i = 0; $i < count($ret1); $i++)	{
				$rq = "INSERT INTO host_service_relation ";
				$rq .= "(hostgroup_hg_id, host_host_id, servicegroup_sg_id, service_service_id) ";
				$rq .= "VALUES ";
				$rq .= "(NULL, '".$ret1[$i]."', NULL, '".$service_id."')";
				$DBRESULT =& $pearDB->query($rq);
				if (PEAR::isError($DBRESULT))
					print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";
			}
	}
	
	# For massive change. We just add the new list if the elem doesn't exist yet
	function updateServiceHost_MC($service_id = null)	{
		if (!$service_id) return;
		global $form, $pearDB;
		$rq = "SELECT * FROM host_service_relation ";
		$rq .= "WHERE service_service_id = '".$service_id."'";
		$DBRESULT =& $pearDB->query($rq);
		if (PEAR::isError($DBRESULT))
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";
		$hsvs = array();
		$hgsvs = array();
		while($DBRESULT->fetchInto($arr))	{
			if ($arr["host_host_id"])
				$hsvs[$arr["host_host_id"]] = $arr["host_host_id"];
			if ($arr["hostgroup_hg_id"])
				$hgsvs[$arr["hostgroup_hg_id"]] = $arr["hostgroup_hg_id"];
		}
		$ret1 = array();
		$ret2 = array();
		$ret1 = $form->getSubmitValue("service_hPars");
		$ret2 = $form->getSubmitValue("service_hgPars");
		 if (count($ret2))
			for($i = 0; $i < count($ret2); $i++)	{
				if (!isset($hgsvs[$ret2[$i]]))	{
					$rq = "DELETE FROM host_service_relation ";
					$rq .= "WHERE service_service_id = '".$service_id."' AND host_host_id IS NOT NULL";
					$DBRESULT =& $pearDB->query($rq);
					if (PEAR::isError($DBRESULT))
						print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";
					$rq = "INSERT INTO host_service_relation ";
					$rq .= "(hostgroup_hg_id, host_host_id, servicegroup_sg_id, service_service_id) ";
					$rq .= "VALUES ";
					$rq .= "('".$ret2[$i]."', NULL, NULL, '".$service_id."')";
					$DBRESULT =& $pearDB->query($rq);
					if (PEAR::isError($DBRESULT))
						print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";
				}
			}
		else if (count($ret1))
			for($i = 0; $i < count($ret1); $i++)	{
				if (!isset($hsvs[$ret1[$i]]))	{
					$rq = "DELETE FROM host_service_relation ";
					$rq .= "WHERE service_service_id = '".$service_id."' AND hostgroup_hg_id IS NOT NULL";
					$DBRESULT =& $pearDB->query($rq);
					if (PEAR::isError($DBRESULT))
						print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";
					$rq = "INSERT INTO host_service_relation ";
					$rq .= "(hostgroup_hg_id, host_host_id, servicegroup_sg_id, service_service_id) ";
					$rq .= "VALUES ";
					$rq .= "(NULL, '".$ret1[$i]."', NULL, '".$service_id."')";
					$DBRESULT =& $pearDB->query($rq);
					if (PEAR::isError($DBRESULT))
						print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";
				}
			}
	}
	
	function updateServiceExtInfos($service_id = null, $ret = array())	{
		if (!$service_id) return;
		global $form, $pearDB;
		if (!count($ret))
			$ret = $form->getSubmitValues();
		$rq = "UPDATE extended_service_information ";		
		$rq .= "SET esi_notes = ";
		isset($ret["esi_notes"]) && $ret["esi_notes"] != NULL ? $rq .= "'".htmlentities($ret["esi_notes"], ENT_QUOTES)."', ": $rq .= "NULL, ";
		$rq .= "esi_notes_url = ";
		isset($ret["esi_notes_url"]) && $ret["esi_notes_url"] != NULL ? $rq .= "'".htmlentities($ret["esi_notes_url"], ENT_QUOTES)."', ": $rq .= "NULL, ";
		$rq .= "esi_action_url = ";
		isset($ret["esi_action_url"]) && $ret["esi_action_url"] != NULL ? $rq .= "'".htmlentities($ret["esi_action_url"], ENT_QUOTES)."', ": $rq .= "NULL, ";
		$rq .= "esi_icon_image = ";
		isset($ret["esi_icon_image"]) && $ret["esi_icon_image"] != NULL ? $rq .= "'".htmlentities($ret["esi_icon_image"], ENT_QUOTES)."', ": $rq .= "NULL, ";
		$rq .= "esi_icon_image_alt = ";
		isset($ret["esi_icon_image_alt"]) && $ret["esi_icon_image_alt"] != NULL ? $rq .= "'".htmlentities($ret["esi_icon_image_alt"], ENT_QUOTES)."', ": $rq .= "NULL, ";
		$rq .= "graph_id = ";
		isset($ret["graph_id"]) && $ret["graph_id"] != NULL ? $rq .= "'".htmlentities($ret["graph_id"], ENT_QUOTES)."' ": $rq .= "NULL ";
		$rq .= "WHERE service_service_id = '".$service_id."'";
		$DBRESULT =& $pearDB->query($rq);
		if (PEAR::isError($DBRESULT))
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";
	}
	
	function updateServiceExtInfos_MC($service_id = null)	{
		if (!$service_id) return;
		global $form, $pearDB;
		$ret = $form->getSubmitValues();
		$rq = "UPDATE extended_service_information SET ";
		if (isset($ret["esi_notes"]) && $ret["esi_notes"] != NULL) $rq .= "esi_notes = '".htmlentities($ret["esi_notes"], ENT_QUOTES)."', ";
		if (isset($ret["esi_notes_url"]) && $ret["esi_notes_url"] != NULL) $rq .= "esi_notes_url = '".htmlentities($ret["esi_notes_url"], ENT_QUOTES)."', ";
		if (isset($ret["esi_action_url"]) && $ret["esi_action_url"] != NULL) $rq .= "esi_action_url = '".htmlentities($ret["esi_action_url"], ENT_QUOTES)."', ";
		if (isset($ret["esi_icon_image"]) && $ret["esi_icon_image"] != NULL) $rq .= "esi_icon_image = '".htmlentities($ret["esi_icon_image"], ENT_QUOTES)."', ";
		if (isset($ret["esi_icon_image_alt"]) && $ret["esi_icon_image_alt"] != NULL) $rq .= "esi_icon_image_alt = '".htmlentities($ret["esi_icon_image_alt"], ENT_QUOTES)."', ";
		if (isset($ret["graph_id"]) && $ret["graph_id"] != NULL) $rq .= "graph_id = '".htmlentities($ret["graph_id"], ENT_QUOTES)."', ";
		if (strcmp("UPDATE extended_service_information SET ", $rq))	{
			# Delete last ',' in request
			$rq[strlen($rq)-2] = " ";
			$rq .= "WHERE service_service_id = '".$service_id."'";
			$DBRESULT =& $pearDB->query($rq);
			if (PEAR::isError($DBRESULT))
				print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";
		}
	}
	
	function updateServiceTemplateUsed($useTpls = array())	{
		if(!count($useTpls)) return;
		global $pearDB;
		require_once "./include/common/common-Func.php";
		foreach ($useTpls as $key=>$value)	{
			$DBRESULT =& $pearDB->query("UPDATE service SET service_template_model_stm_id = '".getMyServiceTPLID($value)."' WHERE service_id = '".$key."'");
			if (PEAR::isError($DBRESULT))
				print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";
		}
	}
	
	function updateServiceCategories_MC($service_id = null, $ret = array())	{
		if (!$service_id) return;
	}
	function updateServiceCategories($service_id = null, $ret = array())	{
		if (!$service_id) return;
		global $form, $pearDB;
		$rq = "DELETE FROM service_categories_relation WHERE service_service_id = '".$service_id."'";
		$DBRESULT =& $pearDB->query($rq);
		if (PEAR::isError($DBRESULT))
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";
		if (isset($ret["service_categories"]))
			$ret = $ret["service_categories"];
		else
			$ret = $form->getSubmitValue("service_categories");
		for($i = 0; $i < count($ret); $i++)	{
			$rq = "INSERT INTO service_categories_relation ";
			$rq .= "(sc_id, service_service_id) ";
			$rq .= "VALUES ";
			$rq .= "('".$ret[$i]."', '".$service_id."')";
			$DBRESULT =& $pearDB->query($rq);
			if (PEAR::isError($DBRESULT))
				print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";
		}
	}
?>