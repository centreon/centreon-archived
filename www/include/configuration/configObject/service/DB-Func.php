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

	if (!isset($oreon) && !isset($centreon)) {
		exit ();
	}

	/**
	 * For ACL
	 *
	 * @param CentreonDB $db
	 * @param int $hostId
	 * @return null
	 */
	function setHostChangeFlag($db, $hostId = null, $hostgroupId = null)
	{
        if (isset($hostId)) {
            $table = "acl_resources_host_relations";
            $field = "host_host_id";
            $val = $hostId;
        } elseif (isset($hostgroupId)) {
            $table = "acl_resources_hg_relations";
            $field = "hg_hg_id";
            $val = $hostgroupId;
        } else {
            return null;
        }
	    $query = "UPDATE acl_resources SET changed = 1
        		  WHERE acl_res_id IN (SELECT acl_res_id
        							   FROM $table
        							   WHERE $field = ".$db->escape($val).")";
        $db->query($query);
        return null;
	}

	/**
	 * This is a quickform rule for checking if all the argument fields are filled
	 *
	 * @return bool
	 */
	function argHandler()
	{
	    $argArray = $_POST;
	    $argTab = array();
		foreach ($argArray as $key => $value) {
		    if (preg_match('/^ARG(\d+)/', $key, $matches)) {
			    $argTab[$matches[1]] = $value;
			}
        }
		$fill = false;
		$nofill = false;
		foreach ($argTab as $val) {
		    if ($val != "") {
		        $fill = true;
		    } else {
                $nofill = true;
		    }
        }
        if ($fill === true && $nofill === true) {
            return false;
        }
        return true;
	}

	/**
	 * Returns the formatted string for command arguments
	 *
	 * @param $argArray
	 * @return string
	 */
    function getCommandArgs($argArray = array(), $conf = array())
    {
        if (isset($conf['command_command_id_arg'])) {
            return $conf['command_command_id_arg'];
        }
        $argTab = array();
		foreach ($argArray as $key => $value) {
		    if (preg_match('/^ARG(\d+)/', $key, $matches)) {
			    $argTab[$matches[1]] = $value;
				$argTab[$matches[1]] = str_replace("\n", "#BR#", $argTab[$matches[1]]);
			    $argTab[$matches[1]] = str_replace("\t", "#T#", $argTab[$matches[1]]);
                $argTab[$matches[1]] = str_replace("\r", "#R#", $argTab[$matches[1]]);
			}
        }
		ksort($argTab);
		$str = "";
		foreach ($argTab as $val) {
		    if ($val != "") {
		        $str .= "!" . $val;
		    }
        }
        if (!strlen($str)) {
            return null;
        }
        return $str;
    }

	function getHostServiceCombo($service_id = null, $service_description = null)
	{
		global $pearDB;
		if ($service_id == null || $service_description == null) {
			return;
		}

		$query = "SELECT h.host_name " .
				"FROM host h, host_service_relation hsr " .
				"WHERE h.host_id = hsr.host_host_id " .
				"AND hsr.service_service_id = '".$service_id."' LIMIT 1";
		$DBRES = $pearDB->query($query);

		if (!$DBRES->numRows()) {
			$combo = "- / " . $service_description;
		} else {
			$row = $DBRES->fetchRow();
			$combo = $row['host_name'] . " / ". $service_description;
		}

		return $combo;
	}

	function serviceExists($name = null)
	{
		global $pearDB, $centreon;

		$DBRESULT = $pearDB->query("SELECT service_description FROM service WHERE service_description = '".CentreonDB::escape($centreon->checkIllegalChar($name))."'");
		if ($DBRESULT->numRows() >= 1) {
			return true;
		}
		return false;
	}

	function testServiceTemplateExistence ($name = null)
	{
		global $pearDB, $form, $centreon;

		$id = null;
		if (isset($form)) {
			$id = $form->getSubmitValue('service_id');
		}
		$DBRESULT = $pearDB->query("SELECT service_description, service_id FROM service WHERE service_register = '0' AND service_description = '".CentreonDB::escape($centreon->checkIllegalChar($name))."'");
		$service = $DBRESULT->fetchRow();
		#Modif case
		if ($DBRESULT->numRows() >= 1 && $service["service_id"] == $id) {
			return true;
		} elseif ($DBRESULT->numRows() >= 1 && $service["service_id"] != $id) { #Duplicate entry
			return false;
		} else {
			return true;
		}
	}

	function testServiceExistence ($name = null, $hPars = array(), $hgPars = array())
	{
		global $pearDB, $centreon;
		global $form;
		$id = null;
		if (isset($form) && !count($hPars) && !count($hgPars))	{
			$arr = $form->getSubmitValues();
			if (isset($arr["service_id"])) {
				$id = $arr["service_id"];
			}
			if (isset($arr["service_hPars"])) {
				$hPars = $arr["service_hPars"];
			} else {
				$hPars = array();
			}
			if (isset($arr["service_hgPars"])) {
				$hgPars = $arr["service_hgPars"];
			} else {
				$hgPars = array();
			}
		}
		foreach ($hPars as $host)	{
			$DBRESULT = $pearDB->query("SELECT service_id FROM service, host_service_relation hsr WHERE hsr.host_host_id = '".$host."' AND hsr.service_service_id = service_id AND service.service_description = '".CentreonDB::escape($centreon->checkIllegalChar($name))."'");
			$service = $DBRESULT->fetchRow();
			#Duplicate entry
			if ($DBRESULT->numRows() >= 1 && $service["service_id"] != $id) {
				return false;
			}
			$DBRESULT->free();
		}
		foreach ($hgPars as $hostgroup)	{
			$DBRESULT = $pearDB->query("SELECT service_id FROM service, host_service_relation hsr WHERE hsr.hostgroup_hg_id = '".$hostgroup."' AND hsr.service_service_id = service_id AND service.service_description = '".CentreonDB::escape($centreon->checkIllegalChar($name))."'");
			$service = $DBRESULT->fetchRow();
			#Duplicate entry
			if ($DBRESULT->numRows() >= 1 && $service["service_id"] != $id) {
				return false;
			}
			$DBRESULT->free();
		}
		return true;
	}

	function enableServiceInDB ($service_id = null, $service_arr = array())
	{
		if (!$service_id && !count($service_arr)) {
		    return;
		}
		global $pearDB, $centreon;
		if ($service_id) {
			$service_arr = array($service_id=>"1");
		}
		foreach ($service_arr as $key=>$value) {
			$DBRESULT = $pearDB->query("UPDATE service SET service_activate = '1' WHERE service_id = '".$key."'");
			$DBRESULT2 = $pearDB->query("SELECT service_description FROM `service` WHERE service_id = '".$key."' LIMIT 1");
			$row = $DBRESULT2->fetchRow();
			$centreon->CentreonLogAction->insertLog("service", $key, getHostServiceCombo($key, $row['service_description']), "enable");
		}
	}

	function disableServiceInDB ($service_id = null, $service_arr = array())
	{
		if (!$service_id && !count($service_arr)){
		    return;
		}
		global $pearDB, $centreon;
		if ($service_id) {
			$service_arr = array($service_id=>"1");
		}
		foreach ($service_arr as $key=>$value) {
			$DBRESULT = $pearDB->query("UPDATE service SET service_activate = '0' WHERE service_id = '".$key."'");
			$DBRESULT2 = $pearDB->query("SELECT service_description FROM `service` WHERE service_id = '".$key."' LIMIT 1");
			$row = $DBRESULT2->fetchRow();
			$centreon->CentreonLogAction->insertLog("service", $key, getHostServiceCombo($key, $row['service_description']), "disable");
		}
	}

	function deleteServiceInDB ($services = array())
	{
		global $pearDB, $centreon;

		foreach ($services as $key => $value) {
			$DBRESULT = $pearDB->query("SELECT service_id FROM service WHERE service_template_model_stm_id = '".$key."'");
			while ($row = $DBRESULT->fetchRow())	{
				$DBRESULT2 = $pearDB->query("UPDATE service SET service_template_model_stm_id = NULL WHERE service_id = '".$row["service_id"]."'");
			}
			$DBRESULT3 = $pearDB->query("SELECT service_description FROM `service` WHERE `service_id` = '".$key."' LIMIT 1");
			$svcname = $DBRESULT3->fetchRow();
			$centreon->CentreonLogAction->insertLog("service", $key, getHostServiceCombo($key, $svcname['service_description']), "d");
			$DBRESULT = $pearDB->query("DELETE FROM service WHERE service_id = '".$key."'");
			$DBRESULT = $pearDB->query("DELETE FROM on_demand_macro_service WHERE svc_svc_id = '".$key."'");
			$DBRESULT = $pearDB->query("DELETE FROM contact_service_relation WHERE service_service_id = '".$key."'");
		}
	}

	function divideGroupedServiceInDB ($service_id = null, $service_arr = array(), $toHost = null)
	{
		global $pearDB, $pearDBO;

		if (!$service_id && !count($service_arr)) {
			return;
		}

		if ($service_id) {
			$service_arr = array($service_id => "1");
		}

		foreach ($service_arr as $key => $value) {
			$lap = 0;
			$DBRESULT = $pearDB->query("SELECT * FROM host_service_relation WHERE service_service_id = '".$key."'");
			while ($relation = $DBRESULT->fetchRow()) {
				if ($relation["hostgroup_hg_id"]) {
					if (isset($toHost)) {
						$sv_id = null;
						$DBRESULT2 = $pearDB->query("DELETE FROM host_service_relation WHERE service_service_id = '".$key."' AND hostgroup_hg_id = '".$relation["hostgroup_hg_id"]."'");
						$hosts = getMyHostGroupHosts($relation["hostgroup_hg_id"]);
						$lap = 0;
						foreach ($hosts as $host_id) {
							if ($lap) {
								$sv_id = multipleServiceInDB(array($key=>"1"), array($key=>"1"), $host_id, 0, null, array(), array($relation["hostgroup_hg_id"] => null));
								$DBRESULT3 = $pearDBO->query("UPDATE index_data SET service_id = '".$sv_id."' WHERE host_id = '".$host_id."' AND service_id = '".$key."'");
							} else {
								$DBRESULT2 = $pearDB->query("INSERT INTO host_service_relation (host_host_id, service_service_id) VALUES ('$host_id', '".$key."')");
								setHostChangeFlag($pearDB, $host_id, null);
							}
							$lap++;
						}
					} else {
						if ($lap) {
							$sv_id = null;
							$DBRESULT2 = $pearDB->query("DELETE FROM host_service_relation WHERE service_service_id = '".$key."' AND hostgroup_hg_id = '".$relation["hostgroup_hg_id"]."'");
							$sv_id = multipleServiceInDB(array($key => "1"), array($key => "1"), null, 0, $relation["hostgroup_hg_id"], array(), array($relation["hostgroup_hg_id"] => null));
							if ($sv_id)	{
								$hosts = getMyHostGroupHosts($relation["hostgroup_hg_id"]);
								foreach ($hosts as $host)	{
									$DBRESULT3 = $pearDBO->query("UPDATE index_data SET service_id = '".$sv_id."' WHERE host_id = '".$host."' AND service_id = '".$key."'");
								}
							}
						}
						$lap++;
					}
				} elseif ($relation["host_host_id"]) {
					if ($lap)	{
						$sv_id = null;
						$DBRESULT2 = $pearDB->query("DELETE FROM host_service_relation WHERE service_service_id = '".$key."' AND host_host_id = '".$relation["host_host_id"]."'");
						$sv_id = multipleServiceInDB(array($key=>"1"), array($key=>"1"), $relation["host_host_id"], 0, NULL, array($relation["host_host_id"]=>NULL), array());
						if ($sv_id)	{
							$DBRESULT3 = $pearDBO->query("UPDATE index_data SET service_id = '".$sv_id."' WHERE host_id = '".$relation["host_host_id"]."' AND service_id = '".$key."'");
						}
					}
					$lap++;
				}
			}
		}
	}

	function multipleServiceInDB($services = array(), $nbrDup = array(), $host = null, $descKey = 1, $hostgroup = NULL, $hPars = array(), $hgPars = array())
	{
		global $pearDB, $centreon;

		# $descKey param is a flag. If 1, we know we have to rename description because it's a traditionnal duplication. If 0, we don't have to, beacause we duplicate services for an Host duplication
		# Foreach Service
		$maxId["MAX(service_id)"] = null;
		foreach($services as $key=>$value)	{
			# Get all information about it
			$DBRESULT = $pearDB->query("SELECT * FROM service WHERE service_id = '".$key."' LIMIT 1");
			$row = $DBRESULT->fetchRow();
			$row["service_id"] = '';
			# Loop on the number of Service we want to duplicate
			for ($i = 1; $i <= $nbrDup[$key]; $i++)	{
				$val = null;
				# Create a sentence which contains all the value
				foreach ($row as $key2=>$value2) {
					if ($key2 == "service_description" && $descKey) {
						$service_description = $value2 = $value2."_".$i;
					} elseif ($key2 == "service_description") {
						$service_description = null;
					}
					$val ? $val .= ($value2 != null ? (", '".$pearDB->escape($value2)."'") : ", NULL") : $val .= ($value2 !=NULL ? ("'".$pearDB->escape($value2)."'") : "NULL");
					if ($key2 != "service_id") {
						$fields[$key2] = $value2;
					}
					if (isset($service_description)) {
						$fields["service_description"] = $service_description;
					}
				}
				if (!count($hPars)) {
					$hPars = getMyServiceHosts($key);
				}
				if (!count($hgPars)) {
					$hgPars = getMyServiceHostGroups($key);
				}
				if (($row["service_register"] && testServiceExistence($service_description, $hPars, $hgPars)) ||
				    (!$row["service_register"] && testServiceTemplateExistence($service_description))) {
					$hPars = array();
					$hgPars = array();
					(isset($val) && $val != "NULL" && $val) ? $rq = "INSERT INTO service VALUES (".$val.")" : $rq = NULL;
					if (isset($rq)) {
						$DBRESULT = $pearDB->query($rq);
						$DBRESULT = $pearDB->query("SELECT MAX(service_id) FROM service");
						$maxId = $DBRESULT->fetchRow();
						if (isset($maxId["MAX(service_id)"]))	{
							# Host duplication case -> Duplicate the Service for the Host we create
							if ($host) {
								$pearDB->query("INSERT INTO host_service_relation VALUES ('', NULL, '".$host."', NULL, '".$maxId["MAX(service_id)"]."')");
								setHostChangeFlag($pearDB, $host, null);
							} elseif ($hostgroup) {
								$pearDB->query("INSERT INTO host_service_relation VALUES ('', '".$hostgroup."', NULL, NULL, '".$maxId["MAX(service_id)"]."')");
								setHostChangeFlag($pearDB, null, $hostgroup);
							} else {
								# Service duplication case -> Duplicate the Service for each relation the base Service have
								$DBRESULT = $pearDB->query("SELECT DISTINCT host_host_id, hostgroup_hg_id FROM host_service_relation WHERE service_service_id = '".$key."'");
								$fields["service_hPars"] = "";
								$fields["service_hgPars"] = "";
								while($service = $DBRESULT->fetchRow()) {
									if ($service["host_host_id"]) {
										$DBRESULT2 = $pearDB->query("INSERT INTO host_service_relation VALUES ('', NULL, '".$service["host_host_id"]."', NULL, '".$maxId["MAX(service_id)"]."')");
										setHostChangeFlag($pearDB, $service['host_host_id'], null);
										$fields["service_hPars"] .= $service["host_host_id"] . ",";
									} elseif ($service["hostgroup_hg_id"]) {
										$DBRESULT2 = $pearDB->query("INSERT INTO host_service_relation VALUES ('', '".$service["hostgroup_hg_id"]."', NULL, NULL, '".$maxId["MAX(service_id)"]."')");
										setHostChangeFlag($pearDB, null, $service["hostgroup_hg_id"]);
										$fields["service_hgPars"] .= $service["hostgroup_hg_id"] . ",";
									}
								}
								$fields["service_hPars"] = trim($fields["service_hPars"], ",");
								$fields["service_hgPars"] = trim($fields["service_hgPars"], ",");
							}

							/*
							 * Contact duplication
							 */
							$DBRESULT = $pearDB->query("SELECT DISTINCT contact_id FROM contact_service_relation WHERE service_service_id = '".$key."'");
							$fields["service_cs"] = "";
							while ($C = $DBRESULT->fetchRow()) {
								$DBRESULT2 = $pearDB->query("INSERT INTO contact_service_relation VALUES ('', '".$maxId["MAX(service_id)"]."', '".$C["contact_id"]."')");
								$fields["service_cs"] .= $C["contact_id"] . ",";
							}
							$fields["service_cs"] = trim($fields["service_cs"], ",");

							/*
							 * ContactGroup duplication
							 */
							$DBRESULT = $pearDB->query("SELECT DISTINCT contactgroup_cg_id FROM contactgroup_service_relation WHERE service_service_id = '".$key."'");
							$fields["service_cgs"] = "";
							while($Cg = $DBRESULT->fetchRow()) {
								$DBRESULT2 = $pearDB->query("INSERT INTO contactgroup_service_relation VALUES ('', '".$Cg["contactgroup_cg_id"]."', '".$maxId["MAX(service_id)"]."')");
								$fields["service_cgs"] .= $Cg["contactgroup_cg_id"] . ",";
							}
							$fields["service_cgs"] = trim($fields["service_cgs"], ",");

							/*
							 * Servicegroup duplication
							 */
							$DBRESULT = $pearDB->query("SELECT DISTINCT host_host_id, hostgroup_hg_id, servicegroup_sg_id FROM servicegroup_relation WHERE service_service_id = '".$key."'");
							$fields["service_sgs"] = "";
							while($Sg = $DBRESULT->fetchRow()) {
								if (isset($host) && $host) {
								    $host_id = $host;
								} else {
								    $Sg["host_host_id"] ? $host_id = "'".$Sg["host_host_id"]."'" : $host_id = "NULL";
								}
							    if (isset($hostgroup) && $hostgroup) {
							        $hg_id = $hostgroup;
							    } else {
							        $Sg["hostgroup_hg_id"] ? $hg_id = "'".$Sg["hostgroup_hg_id"]."'" : $hg_id = "NULL";
							    }
								$DBRESULT2 = $pearDB->query("INSERT INTO servicegroup_relation (host_host_id, hostgroup_hg_id, service_service_id, servicegroup_sg_id) VALUES (".$host_id.", ".$hg_id.", '".$maxId["MAX(service_id)"]."', '".$Sg["servicegroup_sg_id"]."')");
								if ($Sg["host_host_id"]) {
									$fields["service_sgs"] .= $Sg["host_host_id"] . ",";
								}
							}
							$fields["service_sgs"] = trim($fields["service_sgs"], ",");


							/*
							 * Trap link ducplication
							 */
							$DBRESULT = $pearDB->query("SELECT DISTINCT traps_id FROM traps_service_relation WHERE service_id = '".$key."'");
							$fields["service_traps"] = "";
							while($traps = $DBRESULT->fetchRow()){
								$DBRESULT2 = $pearDB->query("INSERT INTO traps_service_relation VALUES ('', '".$traps["traps_id"]."', '".$maxId["MAX(service_id)"]."')");
								$fields["service_traps"] .= $traps["traps_id"] . ",";
							}
							$fields["service_traps"] = trim($fields["service_traps"], ",");

							/*
							 * Extended information duplication
							 */
							$DBRESULT = $pearDB->query("SELECT * FROM extended_service_information WHERE service_service_id = '".$key."'");
							while($esi = $DBRESULT->fetchRow())	{
								$val = null;
								$esi["service_service_id"] = $maxId["MAX(service_id)"];
								$esi["esi_id"] = null;
								foreach ($esi as $key2=>$value2)
									$val ? $val .= ($value2 != null ? (", '".$pearDB->escape($value2)."'"):", NULL") : $val .= ($value2 != null ? ("'".$pearDB->escape($value2)."'"):"NULL");
								$val ? $rq = "INSERT INTO extended_service_information VALUES (".$val.")" : $rq = null;
								$DBRESULT2 = $pearDB->query($rq);
								if ($key2 != "esi_id") {
									$fields[$key2] = $value2;
								}
							}
							/*
							 *  on demand macros
							 */
							$mTpRq1 = "SELECT * FROM `on_demand_macro_service` WHERE `svc_svc_id` ='".$key."'";
						 	$DBRESULT3 = $pearDB->query($mTpRq1);
							while ($sv = $DBRESULT3->fetchRow()) {
								$macName = str_replace("\$", "", $sv["svc_macro_name"]);
							    $macVal = $sv['svc_macro_value'];
								$mTpRq2 = "INSERT INTO `on_demand_macro_service` (`svc_svc_id`, `svc_macro_name`, `svc_macro_value`) VALUES " .
											"('".$maxId["MAX(service_id)"]."', '\$".$macName."\$', '". $pearDB->escape($macVal) ."')";
						 		$DBRESULT4 = $pearDB->query($mTpRq2);
								$fields["_".strtoupper($macName)."_"] = $sv['svc_macro_value'];
							}

							/*
							 *  Service categories
							 */
							$mTpRq1 = "SELECT * FROM `service_categories_relation` WHERE `service_service_id` = '".$key."'";
						 	$DBRESULT3 = $pearDB->query($mTpRq1);
							while ($sv = $DBRESULT3->fetchRow()) {
								$mTpRq2 = "INSERT INTO `service_categories_relation` (`service_service_id`, `sc_id`) VALUES " .
											"('".$maxId["MAX(service_id)"]."', '". $sv['sc_id'] ."')";
						 		$DBRESULT4 = $pearDB->query($mTpRq2);
							}

							/*
							 *  get svc desc
							 */
							$query = "SELECT service_description FROM service WHERE service_id = '".$maxId["MAX(service_id)"]."' LIMIT 1";
							$DBRES = $pearDB->query($query);
							if ($DBRES->numRows()) {
								$row2 = $DBRES->fetchRow();
								$description = $row2['service_description'];
								$centreon->CentreonLogAction->insertLog("service", $maxId["MAX(service_id)"], getHostServiceCombo($maxId["MAX(service_id)"], $description), "a", $fields);
							}
						}
					}
				}
			}
		}
		return ($maxId["MAX(service_id)"]);
	}

	function updateServiceInDB ($service_id = null, $from_MC = false)
	{
		global $form;

		if (!$service_id) {
			return;
		}

		$ret = $form->getSubmitValues();

		if ($from_MC) {
			updateService_MC($service_id);
		} else {
			updateService($service_id, $from_MC);
		}
		# Function for updating cg
		# 1 - MC with deletion of existing cg
		# 2 - MC with addition of new cg
		# 3 - Normal update
		if (isset($ret["mc_mod_cgs"]["mc_mod_cgs"]) && $ret["mc_mod_cgs"]["mc_mod_cgs"]) {
			updateServiceContactGroup($service_id);
			updateServiceContact($service_id);
		} elseif (isset($ret["mc_mod_cgs"]["mc_mod_cgs"]) && !$ret["mc_mod_cgs"]["mc_mod_cgs"]) {
			updateServiceContactGroup_MC($service_id);
			updateServiceContact_MC($service_id);
		} else {
			updateServiceContactGroup($service_id);
			updateServiceContact($service_id);
		}

		# Function for updating notification options
		# 1 - MC with deletion of existing options (Replacement)
		# 2 - MC with addition of new options (incremental)
		# 3 - Normal update
		if (isset($ret["mc_mod_notifopts"]["mc_mod_notifopts"]) && $ret["mc_mod_notifopts"]["mc_mod_notifopts"]) {
				updateServiceNotifs($service_id);
		} 	elseif (isset($ret["mc_mod_notifopts"]["mc_mod_notifopts"]) && !$ret["mc_mod_notifopts"]["mc_mod_notifopts"]) {
				updateServiceNotifs_MC($service_id);
		} else {
			updateServiceNotifs($service_id);
		}

		# Function for updating notification interval options
		# 1 - MC with deletion of existing options (Replacement)
		# 2 - MC with addition of new options (incremental)
		# 3 - Normal update
		if (isset($ret["mc_mod_notifopt_notification_interval"]["mc_mod_notifopt_notification_interval"]) && $ret["mc_mod_notifopt_notification_interval"]["mc_mod_notifopt_notification_interval"]) {
			updateServiceNotifOptionInterval($service_id);
		} 	elseif (isset($ret["mc_mod_notifopt_notification_interval"]["mc_mod_notifopt_notification_interval"]) && !$ret["mc_mod_notifopt_notification_interval"]["mc_mod_notifopt_notification_interval"]) {
			updateServiceNotifOptionInterval_MC($service_id);
		} else {
			updateServiceNotifOptionInterval($service_id);
		}

		# Function for updating first notification delay options
		# 1 - MC with deletion of existing options (Replacement)
		# 2 - MC with addition of new options (incremental)
		# 3 - Normal update, default behavior
		if (isset($ret["mc_mod_notifopt_first_notification_delay"]["mc_mod_notifopt_first_notification_delay"]) && $ret["mc_mod_notifopt_first_notification_delay"]["mc_mod_notifopt_first_notification_delay"]) {
			updateServiceNotifOptionFirstNotificationDelay($service_id);
		} 	elseif (isset($ret["mc_mod_notifopt_first_notification_delay"]["mc_mod_notifopt_first_notification_delay"]) && !$ret["mc_mod_notifopt_first_notification_delay"]["mc_mod_notifopt_first_notification_delay"]) {
			updateServiceNotifOptionFirstNotificationDelay_MC($service_id);
		} else {
			updateServiceNotifOptionFirstNotificationDelay($service_id);
		}


		# Function for updating notification timeperiod options
		# 1 - MC with deletion of existing options (Replacement)
		# 2 - MC with addition of new options (incremental)
		# 3 - Normal update
		if (isset($ret["mc_mod_notifopt_timeperiod"]["mc_mod_notifopt_timeperiod"]) && $ret["mc_mod_notifopt_timeperiod"]["mc_mod_notifopt_timeperiod"]) {
			updateServiceNotifOptionTimeperiod($service_id);
		} 	elseif (isset($ret["mc_mod_notifopt_timeperiod"]["mc_mod_notifopt_timeperiod"]) && !$ret["mc_mod_notifopt_timeperiod"]["mc_mod_notifopt_timeperiod"]) {
			updateServiceNotifOptionTimeperiod_MC($service_id);
		} else {
			updateServiceNotifOptionTimeperiod($service_id);
		}


		# Function for updating host/hg parent
		# 1 - MC with deletion of existing host/hg parent
		# 2 - MC with addition of new host/hg parent
		# 3 - Normal update
		if (isset($ret["mc_mod_Pars"]["mc_mod_Pars"]) && $ret["mc_mod_Pars"]["mc_mod_Pars"]) {
			updateServiceHost($service_id);
		} elseif (isset($ret["mc_mod_Pars"]["mc_mod_Pars"]) && !$ret["mc_mod_Pars"]["mc_mod_Pars"]) {
			updateServiceHost_MC($service_id);
		} else {
			updateServiceHost($service_id);
		}

		# Function for updating sg
		# 1 - MC with deletion of existing sg
		# 2 - MC with addition of new sg
		# 3 - Normal update
		if (isset($ret["mc_mod_sgs"]["mc_mod_sgs"]) && $ret["mc_mod_sgs"]["mc_mod_sgs"]) {
			updateServiceServiceGroup($service_id);
		} elseif (isset($ret["mc_mod_sgs"]["mc_mod_sgs"]) && !$ret["mc_mod_sgs"]["mc_mod_sgs"]) {
			updateServiceServiceGroup_MC($service_id);
		} else {
			updateServiceServiceGroup($service_id);
		}

		if ($from_MC) {
			updateServiceExtInfos_MC($service_id);
		} else {
			updateServiceExtInfos($service_id);
		}
		# Function for updating traps
		# 1 - MC with deletion of existing traps
		# 2 - MC with addition of new traps
		# 3 - Normal update
		if (isset($ret["mc_mod_traps"]["mc_mod_traps"]) && $ret["mc_mod_traps"]["mc_mod_traps"]) {
			updateServiceTrap($service_id);
		} elseif (isset($ret["mc_mod_traps"]["mc_mod_traps"]) && !$ret["mc_mod_traps"]["mc_mod_traps"]) {
			updateServiceTrap_MC($service_id);
		} else {
			updateServiceTrap($service_id);
		}
		# Function for updating categories
		# 1 - MC with deletion of existing categories
		# 2 - MC with addition of new categories
		# 3 - Normal update
		if (isset($ret["mc_mod_sc"]["mc_mod_sc"]) && $ret["mc_mod_sc"]["mc_mod_sc"]) {
			updateServiceCategories($service_id);
		} elseif (isset($ret["mc_mod_sc"]["mc_mod_sc"]) && !$ret["mc_mod_sc"]["mc_mod_sc"]) {
			updateServiceCategories_MC($service_id);
		} else {
			updateServiceCategories($service_id);
		}
	}

	function insertServiceInDB ($ret = array(), $macro_on_demand = null)
	{
		global $centreon;

		$tmp_fields = array();
		$tmp_fields = insertService($ret, $macro_on_demand);
		$service_id = $tmp_fields['service_id'];
		updateServiceContactGroup($service_id, $ret);
		updateServiceContact($service_id, $ret);
		updateServiceNotifs($service_id, $ret);
		updateServiceNotifOptionInterval($service_id, $ret);
		updateServiceNotifOptionTimeperiod($service_id, $ret);
		updateServiceNotifOptionFirstNotificationDelay($service_id, $ret);
		updateServiceHost($service_id, $ret);
		updateServiceServiceGroup($service_id, $ret);
		insertServiceExtInfos($service_id, $ret);
		updateServiceTrap($service_id, $ret);
		updateServiceCategories($service_id, $ret);
		$centreon->user->access->updateACL();
		$fields = $tmp_fields['fields'];
		$centreon->CentreonLogAction->insertLog("service", $service_id, getHostServiceCombo($service_id, CentreonDB::escape($fields["service_description"])), "a", $fields);
		return ($service_id);
	}

	function insertService($ret = array(), $macro_on_demand = null)
	{
		global $form, $pearDB, $centreon;

		$service = new CentreonService($pearDB);

		if (!count($ret)) {
			$ret = $form->getSubmitValues();
		}

		$ret["service_description"] = $service->checkIllegalChar($ret["service_description"]);

		if (isset($ret["command_command_id_arg2"]) && $ret["command_command_id_arg2"] != null)		{
			$ret["command_command_id_arg2"] = str_replace("\n", "#BR#", $ret["command_command_id_arg2"]);
			$ret["command_command_id_arg2"] = str_replace("\t", "#T#", $ret["command_command_id_arg2"]);
			$ret["command_command_id_arg2"] = str_replace("\r", "#R#", $ret["command_command_id_arg2"]);
		}
		$rq = "INSERT INTO service " .
				"(service_template_model_stm_id, command_command_id, timeperiod_tp_id, command_command_id2, timeperiod_tp_id2, " .
				"service_description, service_alias, service_is_volatile, service_max_check_attempts, service_normal_check_interval, " .
				"service_retry_check_interval, service_active_checks_enabled, " .
				"service_passive_checks_enabled, service_obsess_over_service, service_check_freshness, service_freshness_threshold, " .
				"service_event_handler_enabled, service_low_flap_threshold, service_high_flap_threshold, service_flap_detection_enabled, " .
				"service_process_perf_data, service_retain_status_information, service_retain_nonstatus_information, service_notification_interval, " .
				"service_notification_options, service_notifications_enabled, service_stalking_options, service_first_notification_delay ,service_comment, command_command_id_arg, command_command_id_arg2, " .
				"service_register, service_activate) " .
				"VALUES ( ";
				isset($ret["service_template_model_stm_id"]) && $ret["service_template_model_stm_id"] != NULL ? $rq .= "'".$ret["service_template_model_stm_id"]."', ": $rq .= "NULL, ";
				isset($ret["command_command_id"]) && $ret["command_command_id"] != NULL ? $rq .= "'".$ret["command_command_id"]."', ": $rq .= "NULL, ";
				isset($ret["timeperiod_tp_id"]) && $ret["timeperiod_tp_id"] != NULL ? $rq .= "'".$ret["timeperiod_tp_id"]."', ": $rq .= "NULL, ";
				isset($ret["command_command_id2"]) && $ret["command_command_id2"] != NULL ? $rq .= "'".$ret["command_command_id2"]."', ": $rq .= "NULL, ";
				isset($ret["timeperiod_tp_id2"]) && $ret["timeperiod_tp_id2"] != NULL ? $rq .= "'".$ret["timeperiod_tp_id2"]."', ": $rq .= "NULL, ";
				isset($ret["service_description"]) && $ret["service_description"] != NULL ? $rq .= "'".CentreonDB::escape($ret["service_description"])."', ": $rq .= "NULL, ";
				isset($ret["service_alias"]) && $ret["service_alias"] != NULL ? $rq .= "'".CentreonDB::escape($ret["service_alias"])."', ": $rq .= "NULL, ";
				isset($ret["service_is_volatile"]) && $ret["service_is_volatile"]["service_is_volatile"] != 2 ? $rq .= "'".$ret["service_is_volatile"]["service_is_volatile"]."', ": $rq .= "'2', ";
				isset($ret["service_max_check_attempts"]) && $ret["service_max_check_attempts"] != NULL ? $rq .= "'".$ret["service_max_check_attempts"]."', " : $rq .= "NULL, ";
				isset($ret["service_normal_check_interval"]) && $ret["service_normal_check_interval"] != NULL ? $rq .= "'".$ret["service_normal_check_interval"]."', ": $rq .= "NULL, ";
				isset($ret["service_retry_check_interval"]) && $ret["service_retry_check_interval"] != NULL ? $rq .= "'".$ret["service_retry_check_interval"]."', ": $rq .= "NULL, ";
				isset($ret["service_active_checks_enabled"]["service_active_checks_enabled"]) && $ret["service_active_checks_enabled"]["service_active_checks_enabled"] != 2 ? $rq .= "'".$ret["service_active_checks_enabled"]["service_active_checks_enabled"]."', ": $rq .= "'2', ";
				isset($ret["service_passive_checks_enabled"]["service_passive_checks_enabled"]) && $ret["service_passive_checks_enabled"]["service_passive_checks_enabled"] != 2 ? $rq .= "'".$ret["service_passive_checks_enabled"]["service_passive_checks_enabled"]."', ": $rq .= "'2', ";
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
				isset($ret["service_first_notification_delay"]) && $ret["service_first_notification_delay"] != NULL ? $rq .= "'".$ret["service_first_notification_delay"]."', " : $rq .= "NULL, ";

				isset($ret["service_comment"]) && $ret["service_comment"] != NULL ? $rq .= "'".CentreonDB::escape($ret["service_comment"])."', " : $rq .= "NULL, ";
				$ret['command_command_id_arg'] = getCommandArgs($_POST, $ret);
				isset($ret["command_command_id_arg"]) && $ret["command_command_id_arg"] != NULL ? $rq .= "'".CentreonDB::escape($ret["command_command_id_arg"])."', " : $rq .= "NULL, ";


				isset($ret["command_command_id_arg2"]) && $ret["command_command_id_arg2"] != NULL ? $rq .= "'".CentreonDB::escape($ret["command_command_id_arg2"])."', " : $rq .= "NULL, ";
				isset($ret["service_register"]["service_register"]) && $ret["service_register"]["service_register"] != NULL ? $rq .= "'".$ret["service_register"]["service_register"]."', " : $rq .= "NULL, ";
				isset($ret["service_activate"]["service_activate"]) && $ret["service_activate"]["service_activate"] != NULL ? $rq .= "'".$ret["service_activate"]["service_activate"]."'" : $rq .= "NULL";
				$rq .= ")";
		$DBRESULT = $pearDB->query($rq);
		$DBRESULT = $pearDB->query("SELECT MAX(service_id) FROM service");
		$service_id = $DBRESULT->fetchRow();

		/*
		 *  Insert on demand macros
		 */
		if (isset($macro_on_demand)) {
			$my_tab = $macro_on_demand;
		} elseif (isset($ret['nbOfMacro'])) {
			$my_tab = $ret;
		}
		if (isset($my_tab['nbOfMacro'])) {
			$already_stored = array();
	 		for ($i=0; $i <= $my_tab['nbOfMacro']; $i++) {
	 			$macInput = "macroInput_" . $i;
	 			$macValue = "macroValue_" . $i;
	 			if (isset($my_tab[$macInput]) && !isset($already_stored[strtolower($my_tab[$macInput])]) && $my_tab[$macInput]) {
		 			$my_tab[$macInput] = str_replace("\$_SERVICE", "", $my_tab[$macInput]);
		 			$my_tab[$macInput] = str_replace("\$", "", $my_tab[$macInput]);
		 			$macName = $my_tab[$macInput];
		 			$macVal = $my_tab[$macValue];
		 			$rq = "INSERT INTO on_demand_macro_service (`svc_macro_name`, `svc_macro_value`, `svc_svc_id`) VALUES ('\$_SERVICE". CentreonDB::escape(strtoupper($macName)) ."\$', '". CentreonDB::escape($macVal) ."', ". $service_id["MAX(service_id)"] .")";
			 		$DBRESULT = $pearDB->query($rq);
					$fields["_".strtoupper($my_tab[$macInput])."_"] = $my_tab[$macValue];
					$already_stored[strtolower($my_tab[$macInput])] = 1;
	 			}
	 		}
		}

		if (isset($ret["service_template_model_stm_id"])) {
		    $fields["service_template_model_stm_id"] = $ret["service_template_model_stm_id"];
		}
		if (isset($ret["command_command_id"])) {
		    $fields["command_command_id"] = $ret["command_command_id"];
		}
		if (isset($ret['timeperiod_tp_id'])) {
		    $fields["timeperiod_tp_id"] = $ret["timeperiod_tp_id"];
		}
		if (isset($ret["command_command_id2"])) {
		    $fields["command_command_id2"] = $ret["command_command_id2"];
		}
		if (isset($ret["timeperiod_tp_id2"])) {
		    $fields["timeperiod_tp_id2"] = $ret["timeperiod_tp_id2"];
		}
		if (isset($ret["service_description"])) {
		    $fields["service_description"] = CentreonDB::escape($ret["service_description"]);
		}
		if (isset($ret["service_alias"])) {
		    $fields["service_alias"] = CentreonDB::escape($ret["service_alias"]);
		}
		if (isset($ret["service_is_volatile"]) &&
		    isset($ret["service_is_volatile"]['service_is_volatile'])) {
		    $fields["service_is_volatile"] = $ret["service_is_volatile"]["service_is_volatile"];
		}
		if (isset($ret["service_max_check_attempts"])) {
		    $fields["service_max_check_attempts"] = $ret["service_max_check_attempts"];
		}
		if (isset($ret["service_normal_check_interval"])) {
		    $fields["service_normal_check_interval"] = $ret["service_normal_check_interval"];
		}
		if (isset($ret["service_retry_check_interval"])) {
		    $fields["service_retry_check_interval"] = $ret["service_retry_check_interval"];
		}
		if (isset($ret["service_active_checks_enabled"]) &&
		    isset($ret["service_active_checks_enabled"]["service_active_checks_enabled"])) {
		    $fields["service_active_checks_enabled"] = $ret["service_active_checks_enabled"]["service_active_checks_enabled"];
		}
		if (isset($ret["service_passive_checks_enabled"]) &&
		    isset($ret['service_passive_checks_enabled']['service_passive_checks_enabled'])) {
		    $fields["service_passive_checks_enabled"] = $ret["service_passive_checks_enabled"]["service_passive_checks_enabled"];
		}
		if (isset($ret["service_obsess_over_service"]) &&
		    isset($ret["service_obsess_over_service"]["service_obsess_over_service"])) {
		    $fields["service_obsess_over_service"] = $ret["service_obsess_over_service"]["service_obsess_over_service"];
		}
		if (isset($ret["service_check_freshness"]) &&
		    isset($ret["service_check_freshness"]["service_check_freshness"])) {
		    $fields["service_check_freshness"] = $ret["service_check_freshness"]["service_check_freshness"];
		}
		if (isset($ret["service_freshness_threshold"])) {
		    $fields["service_freshness_threshold"] = $ret["service_freshness_threshold"];
		}
		if (isset($ret["service_event_handler_enabled"]) &&
		    isset($ret["service_event_handler_enabled"]["service_event_handler_enabled"])) {
		    $fields["service_event_handler_enabled"] = $ret["service_event_handler_enabled"]["service_event_handler_enabled"];
		}
		if (isset($ret["service_low_flap_threshold"])) {
		    $fields["service_low_flap_threshold"] = $ret["service_low_flap_threshold"];
		}
		if (isset($ret["service_high_flap_threshold"])) {
		    $fields["service_high_flap_threshold"] = $ret["service_high_flap_threshold"];
		}
		if (isset($ret["service_flap_detection_enabled"]) &&
		    isset($ret["service_flap_detection_enabled"]["service_flap_detection_enabled"])) {
		    $fields["service_flap_detection_enabled"] = $ret["service_flap_detection_enabled"]["service_flap_detection_enabled"];
		}
		if (isset($ret["service_process_perf_data"]) &&
		    isset($ret["service_process_perf_data"]["service_process_perf_data"])) {
		    $fields["service_process_perf_data"] = $ret["service_process_perf_data"]["service_process_perf_data"];
		}
		if (isset($ret["service_retain_status_information"]) &&
		    isset($ret["service_retain_status_information"]["service_retain_status_information"])) {
		    $fields["service_retain_status_information"] = $ret["service_retain_status_information"]["service_retain_status_information"];
		}
		if (isset($ret["service_retain_nonstatus_information"]) &&
		    isset($ret["service_retain_nonstatus_information"]["service_retain_nonstatus_information"])) {
		    $fields["service_retain_nonstatus_information"] = $ret["service_retain_nonstatus_information"]["service_retain_nonstatus_information"];
		}
		if (isset($ret["service_notification_interval"])) {
		    $fields["service_notification_interval"] = $ret["service_notification_interval"];
		}
		if (isset($ret["service_first_notification_delay"])) {
		    $fields["service_first_notification_delay"] = $ret["service_first_notification_delay"];
		}
		$fields["service_notifOpts"] = "";
		if (isset($ret["service_notifOpts"])) {
			$fields["service_notifOpts"] = implode(",", array_keys($ret["service_notifOpts"]));
		}
		if (isset($ret["service_notifications_enabled"]) &&
		    isset($ret["service_notifications_enabled"]["service_notifications_enabled"])) {
		    $fields["service_notifications_enabled"] = $ret["service_notifications_enabled"]["service_notifications_enabled"];
		}
		$fields["service_stalOpts"] = "";
		if (isset($ret["service_stalOpts"])) {
			$fields["service_stalOpts"] = implode(",", array_keys($ret["service_stalOpts"]));
		}
		if (isset($ret["service_comment"])) {
		    $fields["service_comment"] = CentreonDB::escape($ret["service_comment"]);
		}
		if (isset($ret["command_command_id_arg"])) {
		    $fields["command_command_id_arg"] = CentreonDB::escape($ret["command_command_id_arg"]);
		}
		if (isset($ret["command_command_id_arg2"])) {
		    $fields["command_command_id_arg2"] = CentreonDB::escape($ret["command_command_id_arg2"]);
		}
		if (isset($ret["service_register"]) && isset($ret["service_register"]["service_register"])) {
		    $fields["service_register"] = $ret["service_register"]["service_register"];
		}
		if (isset($ret["service_activate"]) && isset($ret["service_activate"]["service_activate"])) {
		    $fields["service_activate"] = $ret["service_activate"]["service_activate"];
		}
		if (isset($ret["esi_notes"])) {
		    $fields["esi_notes"] = CentreonDB::escape($ret["esi_notes"]);
		}
		if (isset($ret["esi_notes_url"])) {
		    $fields["esi_notes_url"] = CentreonDB::escape($ret["esi_notes_url"]);
		}
		if (isset($ret["esi_action_url"])) {
		    $fields["esi_action_url"] = CentreonDB::escape($ret["esi_action_url"]);
		}
		if (isset($ret["esi_icon_image"])) {
		    $fields["esi_icon_image"] = CentreonDB::escape($ret["esi_icon_image"]);
		}
		if (isset($ret["esi_icon_image_alt"])) {
		    $fields["esi_icon_image_alt"] = CentreonDB::escape($ret["esi_icon_image_alt"]);
		}
		if (isset($ret["graph_id"])) {
		    $fields["graph_id"] = $ret["graph_id"];
		}
		$fields["service_cs"] = "";
		if (isset($ret["service_cs"])) {
			$fields["service_cs"] = implode(",", $ret["service_cs"]);
		}
		$fields["service_cgs"] = "";
		if (isset($ret["service_cgs"])) {
			$fields["service_cgs"] = implode(",", $ret["service_cgs"]);
		}
		$fields["service_sgs"] = "";
		if (isset($ret["service_sgs"])) {
			$fields["service_sgs"] = implode(",", $ret["service_sgs"]);
		}
		$fields["service_hPars"] = "";
		if (isset($ret["service_hPars"])) {
			$fields["service_hPars"] = implode(",", $ret["service_hPars"]);
		}
		$fields["service_hgPars"] = "";
		if (isset($ret["service_hgPars"])) {
			$fields["service_hgPars"] = implode(",", $ret["service_hgPars"]);
		}
		$fields["service_categories"] = "";
		if (isset($ret["service_categories"])) {
			$fields["service_categories"] = implode(",", $ret["service_categories"]);
		}
		$fields["service_traps"] = "";
		if (isset($ret["service_traps"])) {
			$fields["service_traps"] = implode(",", $ret["service_traps"]);
		}
		$centreon->CentreonLogAction->insertLog("service", $service_id["MAX(service_id)"], getHostServiceCombo($service_id["MAX(service_id)"], CentreonDB::escape($ret["service_description"])), "a", $fields);
		return (array("service_id" => $service_id["MAX(service_id)"], "fields" => $fields));
	}

	function insertServiceExtInfos($service_id = null, $ret)	{
		if (!$service_id) return;
		global $form;
		global $pearDB;
		if (!count($ret))
			$ret = $form->getSubmitValues();
		/*
		 * Check if image selected isn't a directory
		 */
		if (isset($ret["esi_icon_image"]) && strrchr("REP_", $ret["esi_icon_image"]))
			$ret["esi_icon_image"] = NULL;
		/*
		 *
		 */
		$rq = 	"INSERT INTO `extended_service_information` " .
				"( `esi_id` , `service_service_id`, `esi_notes` , `esi_notes_url` , " .
				"`esi_action_url` , `esi_icon_image` , `esi_icon_image_alt`, `graph_id` )" .
				"VALUES ( ";
		$rq .= "NULL, ".$service_id.", ";
		isset($ret["esi_notes"]) && $ret["esi_notes"] != NULL ? $rq .= "'".CentreonDB::escape($ret["esi_notes"])."', ": $rq .= "NULL, ";
		isset($ret["esi_notes_url"]) && $ret["esi_notes_url"] != NULL ? $rq .= "'".CentreonDB::escape($ret["esi_notes_url"])."', ": $rq .= "NULL, ";
		isset($ret["esi_action_url"]) && $ret["esi_action_url"] != NULL ? $rq .= "'".CentreonDB::escape($ret["esi_action_url"])."', ": $rq .= "NULL, ";
		isset($ret["esi_icon_image"]) && $ret["esi_icon_image"] != NULL ? $rq .= "'".CentreonDB::escape($ret["esi_icon_image"])."', ": $rq .= "NULL, ";
		isset($ret["esi_icon_image_alt"]) && $ret["esi_icon_image_alt"] != NULL ? $rq .= "'".CentreonDB::escape($ret["esi_icon_image_alt"])."', ": $rq .= "NULL, ";
		isset($ret["graph_id"]) && $ret["graph_id"] != NULL ? $rq .= "'".$ret["graph_id"]."'": $rq .= "NULL";
		$rq .= ")";
		$DBRESULT = $pearDB->query($rq);
	}

	/** *************************************
	 *
	 * Update service informations
	 * @param $service_id
	 * @param $from_MC
	 */
	function updateService($service_id = null, $from_MC = false)	{
		global $form, $pearDB, $centreon;

		if (!$service_id) {
			return;
		}

		$service = new CentreonService($pearDB);

		$ret = array();
		$ret = $form->getSubmitValues();

		if (isset($ret['sg_name'])) {
		    $ret["sg_name"] = $centreon->checkIllegalChar($ret["sg_name"]);
		}

		if (isset($ret["command_command_id_arg2"]) && $ret["command_command_id_arg2"] != NULL)		{
			$ret["command_command_id_arg2"] = str_replace("\n", "#BR#", $ret["command_command_id_arg2"]);
			$ret["command_command_id_arg2"] = str_replace("\t", "#T#", $ret["command_command_id_arg2"]);
			$ret["command_command_id_arg2"] = str_replace("\r", "#R#", $ret["command_command_id_arg2"]);
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
		/*$rq .= "timeperiod_tp_id2 = ";
		isset($ret["timeperiod_tp_id2"]) && $ret["timeperiod_tp_id2"] != NULL ? $rq .= "'".$ret["timeperiod_tp_id2"]."', ": $rq .= "NULL, ";*/
		# If we are doing a MC, we don't have to set name and alias field
		if (!$from_MC)	{
			$rq .= "service_description = ";
			isset($ret["service_description"]) && $ret["service_description"] != NULL ? $rq .= "'".CentreonDB::escape($ret["service_description"])."', ": $rq .= "NULL, ";
		}
		$rq .= "service_alias = ";
		isset($ret["service_alias"]) && $ret["service_alias"] != NULL ? $rq .= "'".CentreonDB::escape($ret["service_alias"])."', ": $rq .= "NULL, ";
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
		/*$rq .= "service_notification_interval = ";
		isset($ret["service_notification_interval"]) && $ret["service_notification_interval"] != NULL ? $rq .= "'".$ret["service_notification_interval"]."', " : $rq .= "NULL, ";*/
	/*	$rq .= "service_notification_options = ";
		isset($ret["service_notifOpts"]) && $ret["service_notifOpts"] != NULL ? $rq .= "'".implode(",", array_keys($ret["service_notifOpts"]))."', " : $rq .= "NULL, "; */
		$rq .= "service_notifications_enabled = ";
		isset($ret["service_notifications_enabled"]["service_notifications_enabled"]) && $ret["service_notifications_enabled"]["service_notifications_enabled"] != 2 ? $rq .= "'".$ret["service_notifications_enabled"]["service_notifications_enabled"]."', " : $rq .= "'2', ";
		/*$rq .= "service_first_notification_delay = ";
		isset($ret["service_first_notification_delay"]) && $ret["service_first_notification_delay"] != NULL ? $rq .= "'".$ret["service_first_notification_delay"]."', " : $rq .= " NULL, ";*/
		$rq .= "service_stalking_options = ";
		isset($ret["service_stalOpts"]) && $ret["service_stalOpts"] != NULL ? $rq .= "'".implode(",", array_keys($ret["service_stalOpts"]))."', " : $rq .= "NULL, ";

		$rq .= "service_comment = ";
		isset($ret["service_comment"]) && $ret["service_comment"] != NULL ? $rq .= "'".CentreonDB::escape($ret["service_comment"])."', " : $rq .= "NULL, ";

		$ret["command_command_id_arg"] = getCommandArgs($_POST, $ret);
		$rq .= "command_command_id_arg = ";
		isset($ret["command_command_id_arg"]) && $ret["command_command_id_arg"] != NULL ? $rq .= "'".CentreonDB::escape($ret["command_command_id_arg"])."', " : $rq .= "NULL, ";
		$rq .= "command_command_id_arg2 = ";
		isset($ret["command_command_id_arg2"]) && $ret["command_command_id_arg2"] != NULL ? $rq .= "'".CentreonDB::escape($ret["command_command_id_arg2"])."', " : $rq .= "NULL, ";
		$rq .= "service_register = ";
		isset($ret["service_register"]["service_register"]) && $ret["service_register"]["service_register"] != NULL ? $rq .= "'".$ret["service_register"]["service_register"]."', " : $rq .= "NULL, ";
		$rq .= "service_activate = ";
		isset($ret["service_activate"]["service_activate"]) && $ret["service_activate"]["service_activate"] != NULL ? $rq .= "'".$ret["service_activate"]["service_activate"]."' " : $rq .= "NULL ";
		$rq .= "WHERE service_id = '".$service_id."'";
		$DBRESULT = $pearDB->query($rq);

		/*
		 *  Update demand macros
		 */
		if (isset($_POST['nbOfMacro'])) {
			$already_stored = array();
			$DBRESULT = $pearDB->query("DELETE FROM `on_demand_macro_service` WHERE `svc_svc_id`='".$service_id."'");

	 		for ($i=0; $i <= $_POST['nbOfMacro']; $i++) {
	 			$macInput = "macroInput_" . $i;
	 			$macValue = "macroValue_" . $i;
	 			if (isset($_POST[$macInput]) && !isset($already_stored[strtolower($_POST[$macInput])]) && $_POST[$macInput]) {
		 			$_POST[$macInput] = str_replace("\$_SERVICE", "", $_POST[$macInput]);
		 			$_POST[$macInput] = str_replace("\$", "", $_POST[$macInput]);
		 			$macName = $_POST[$macInput];
		 			$macVal = $_POST[$macValue];
		 			$rq = "INSERT INTO on_demand_macro_service (`svc_macro_name`, `svc_macro_value`, `svc_svc_id`) VALUES ('\$_SERVICE". CentreonDB::escape(strtoupper($macName)) ."\$', '". CentreonDB::escape($macVal) ."', ". $service_id .")";
			 		$DBRESULT = $pearDB->query($rq);
					$fields["_".strtoupper($_POST[$macInput])."_"] = $_POST[$macValue];
					$already_stored[strtolower($_POST[$macInput])] = 1;
	 			}
	 		}
		}
		$fields["service_template_model_stm_id"] = $ret["service_template_model_stm_id"];
		$fields["command_command_id"] = $ret["command_command_id"];
		$fields["timeperiod_tp_id"] = $ret["timeperiod_tp_id"];
		$fields["command_command_id2"] = $ret["command_command_id2"];
		//$fields["timeperiod_tp_id2"] = $ret["timeperiod_tp_id2"];
		$fields["service_description"] = CentreonDB::escape($ret["service_description"]);
		if (isset($fields["service_alias"]))
			$fields["service_alias"] = CentreonDB::escape($ret["service_alias"]);
		$fields["service_is_volatile"] = $ret["service_is_volatile"]["service_is_volatile"];
		$fields["service_max_check_attempts"] = $ret["service_max_check_attempts"];
		$fields["service_normal_check_interval"] = $ret["service_normal_check_interval"];
		$fields["service_retry_check_interval"] = $ret["service_retry_check_interval"];
		$fields["service_active_checks_enabled"] = $ret["service_active_checks_enabled"]["service_active_checks_enabled"];
		$fields["service_passive_checks_enabled"] = $ret["service_passive_checks_enabled"]["service_passive_checks_enabled"];
		$fields["service_obsess_over_service"] = $ret["service_obsess_over_service"]["service_obsess_over_service"];
		$fields["service_check_freshness"] = $ret["service_check_freshness"]["service_check_freshness"];
		$fields["service_freshness_threshold"] = $ret["service_freshness_threshold"];
		$fields["service_event_handler_enabled"] = $ret["service_event_handler_enabled"]["service_event_handler_enabled"];
		$fields["service_low_flap_threshold"] = $ret["service_low_flap_threshold"];
		$fields["service_high_flap_threshold"] = $ret["service_high_flap_threshold"];
		$fields["service_flap_detection_enabled"] = $ret["service_flap_detection_enabled"]["service_flap_detection_enabled"];
		$fields["service_process_perf_data"] = $ret["service_process_perf_data"]["service_process_perf_data"];
		$fields["service_retain_status_information"] = $ret["service_retain_status_information"]["service_retain_status_information"];
		$fields["service_retain_nonstatus_information"] = $ret["service_retain_nonstatus_information"]["service_retain_nonstatus_information"];
	//	$fields["service_notification_interval"] = $ret["service_notification_interval"];
	//	$fields["service_first_notification_delay"] = $ret["service_first_notification_delay"];
		/*$fields["service_notifOpts"] = "";
		if (isset($ret["service_notifOpts"]))
			$fields["service_notifOpts"] = implode(",", array_keys($ret["service_notifOpts"]));*/
		if (isset($fields["service_notifications_enabled"]))
			$fields["service_notifications_enabled"] = $fields["service_notifications_enabled"]["service_notifications_enabled"];
		$fields["service_stalOpts"] = "";
		if (isset($ret["service_stalOpts"]))
			$fields["service_stalOpts"] = implode(",", array_keys($ret["service_stalOpts"]));
		$fields["service_comment"] = CentreonDB::escape($ret["service_comment"]);
		$fields["command_command_id_arg"] = CentreonDB::escape($ret["command_command_id_arg"]);
		$fields["command_command_id_arg2"] = CentreonDB::escape($ret["command_command_id_arg2"]);
		$fields["service_register"] = $ret["service_register"]["service_register"];
		$fields["service_activate"] = $ret["service_activate"]["service_activate"];
		$fields["esi_notes"] = CentreonDB::escape($ret["esi_notes"]);
		$fields["esi_notes_url"] = CentreonDB::escape($ret["esi_notes_url"]);
		$fields["esi_action_url"] = CentreonDB::escape($ret["esi_action_url"]);
		$fields["esi_icon_image"] = CentreonDB::escape($ret["esi_icon_image"]);
		$fields["esi_icon_image_alt"] = CentreonDB::escape($ret["esi_icon_image_alt"]);
		$fields["graph_id"] = $ret["graph_id"];
		$fields["service_cs"] = "";
		if (isset($ret["service_cs"]))
			$fields["service_cs"] = implode(",", $ret["service_cs"]);
		$fields["service_cgs"] = "";
		if (isset($ret["service_cgs"]))
			$fields["service_cgs"] = implode(",", $ret["service_cgs"]);
		$fields["service_sgs"] = "";
		if (isset($ret["service_sgs"]))
			$fields["service_sgs"] = implode(",", $ret["service_sgs"]);
		$fields["service_hPars"] = "";
		if (isset($ret["service_hPars"]))
			$fields["service_hPars"] = implode(",", $ret["service_hPars"]);
		$fields["service_hgPars"] = "";
		if (isset($ret["service_hgPars"]))
			$fields["service_hgPars"] = implode(",", $ret["service_hgPars"]);
		$fields["service_categories"] = "";
		if (isset($ret["service_categories"]))
			$fields["service_categories"] = implode(",", $ret["service_categories"]);
		$fields["service_traps"] = "";
		if (isset($ret["service_traps"]))
			$fields["service_traps"] = implode(",", $ret["service_traps"]);
		$centreon->CentreonLogAction->insertLog("service", $service_id["MAX(service_id)"], getHostServiceCombo($service_id, CentreonDB::escape($ret["service_description"])), "c", $fields);
		$centreon->user->access->updateACL();
	}

	function updateService_MC($service_id = null)	{
		if (!$service_id)
			return;
		global $form, $pearDB, $centreon;

		$service = new CentreonService($pearDB);

		$ret = array();
		$ret = $form->getSubmitValues();

		$ret["sg_name"] = $centreon->checkIllegalChar($ret["sg_name"]);

		if (isset($ret["command_command_id_arg"]) && $ret["command_command_id_arg"] != NULL)		{
			$ret["command_command_id_arg"] = str_replace("\n", "#BR#", $ret["command_command_id_arg"]);
			$ret["command_command_id_arg"] = str_replace("\t", "#T#", $ret["command_command_id_arg"]);
			$ret["command_command_id_arg"] = str_replace("\r", "#R#", $ret["command_command_id_arg"]);
		}
		if (isset($ret["command_command_id_arg2"]) && $ret["command_command_id_arg2"] != NULL)		{
			$ret["command_command_id_arg2"] = str_replace("\n", "#BR#", $ret["command_command_id_arg2"]);
			$ret["command_command_id_arg2"] = str_replace("\t", "#T#", $ret["command_command_id_arg2"]);
			$ret["command_command_id_arg2"] = str_replace("\r", "#R#", $ret["command_command_id_arg2"]);"', " ;
		}

		$rq = "UPDATE service SET ";
		if (isset($ret["service_template_model_stm_id"]) && $ret["service_template_model_stm_id"] != NULL) {
			$rq .= "service_template_model_stm_id = '".$ret["service_template_model_stm_id"]."', ";
			$fields["service_template_model_stm_id"] = $ret["service_template_model_stm_id"];
		}
		if (isset($ret["command_command_id"]) && $ret["command_command_id"] != NULL) {
			$rq .= "command_command_id = '".$ret["command_command_id"]."', ";
			$fields["command_command_id"] = $ret["command_command_id"];
		}
		if (isset($ret["timeperiod_tp_id"]) && $ret["timeperiod_tp_id"] != NULL) {
			$rq .= "timeperiod_tp_id = '".$ret["timeperiod_tp_id"]."', ";
			$fields["timeperiod_tp_id"] = $ret["timeperiod_tp_id"];
		}
		if (isset($ret["command_command_id2"]) && $ret["command_command_id2"] != NULL) {
			$rq .= "command_command_id2 = '".$ret["command_command_id2"]."', ";
			$fields["command_command_id2"] = $ret["command_command_id2"];
		}
		/*if (isset($ret["timeperiod_tp_id2"]) && $ret["timeperiod_tp_id2"] != NULL) {
			$rq .= "timeperiod_tp_id2 = '".$ret["timeperiod_tp_id2"]."', ";
			$fields["timeperiod_tp_id2"] = $ret["timeperiod_tp_id2"];
		}*/
		if (isset($ret["service_alias"]) && $ret["service_alias"] != NULL) {
			$rq .= "service_alias = '".$ret["service_alias"]."', ";
			$fields["service_alias"] = $ret["service_alias"];
		}
		if (isset($ret["service_is_volatile"]["service_is_volatile"]) && $ret["service_is_volatile"]["service_is_volatile"] != 2) {
			$rq .= "service_is_volatile = '".$ret["service_is_volatile"]["service_is_volatile"]."', ";
			$fields["service_is_volatile"] = $ret["service_is_volatile"]["service_is_volatile"];
		}
		if (isset($ret["service_max_check_attempts"]) && $ret["service_max_check_attempts"] != NULL) {
			$rq .= "service_max_check_attempts = '".$ret["service_max_check_attempts"]."', ";
			$fields["service_max_check_attempts"] = $ret["service_max_check_attempts"];
		}
		if (isset($ret["service_normal_check_interval"]) && $ret["service_normal_check_interval"] != NULL) {
			$rq .= "service_normal_check_interval = '".$ret["service_normal_check_interval"]."', ";
			$fields["service_normal_check_interval"] = $ret["service_normal_check_interval"];
		}
		if (isset($ret["service_retry_check_interval"]) && $ret["service_retry_check_interval"] != NULL) {
			$rq .= "service_retry_check_interval = '".$ret["service_retry_check_interval"]."', ";
			$fields["service_retry_check_interval"] = $ret["service_retry_check_interval"];
		}
		if (isset($ret["service_active_checks_enabled"]["service_active_checks_enabled"])) {
			$rq .= "service_active_checks_enabled = '".$ret["service_active_checks_enabled"]["service_active_checks_enabled"]."', ";
			$fields["service_active_checks_enabled"] = $fields["service_active_checks_enabled"]["service_active_checks_enabled"];
		}
		if (isset($ret["service_passive_checks_enabled"]["service_passive_checks_enabled"])) {
			$rq .= "service_passive_checks_enabled = '".$ret["service_passive_checks_enabled"]["service_passive_checks_enabled"]."', ";
			$fields["service_passive_checks_enabled"] = $ret["service_passive_checks_enabled"]["service_passive_checks_enabled"];
		}
		if (isset($ret["service_obsess_over_service"]["service_obsess_over_service"])) {
			$rq .= "service_obsess_over_service = '".$ret["service_obsess_over_service"]["service_obsess_over_service"]."', ";
			$fields["service_obsess_over_service"] = $ret["service_obsess_over_service"]["service_obsess_over_service"];
		}
		if (isset($ret["service_check_freshness"]["service_check_freshness"])) {
			$rq .= "service_check_freshness = '".$ret["service_check_freshness"]["service_check_freshness"]."', ";
			$fields["service_check_freshness"] = $ret["service_check_freshness"]["service_check_freshness"];
		}
		if (isset($ret["service_freshness_threshold"]) && $ret["service_freshness_threshold"] != NULL) {
			$rq .= "service_freshness_threshold = '".$ret["service_freshness_threshold"]."', ";
			$fields["service_freshness_threshold"] = $ret["service_freshness_threshold"];
		}
		if (isset($ret["service_event_handler_enabled"]["service_event_handler_enabled"])) {
			$rq .= "service_event_handler_enabled = '".$ret["service_event_handler_enabled"]["service_event_handler_enabled"]."', ";
			$fields["service_event_handler_enabled"] = $ret["service_event_handler_enabled"]["service_event_handler_enabled"];
		}
		if (isset($ret["service_low_flap_threshold"]) && $ret["service_low_flap_threshold"] != NULL) {
			$rq .= "service_low_flap_threshold = '".$ret["service_low_flap_threshold"]."', ";
			$fields["service_low_flap_threshold"] = $ret["service_low_flap_threshold"];
		}
		if (isset($ret["service_high_flap_threshold"]) && $ret["service_high_flap_threshold"] != NULL) {
			$rq .= "service_high_flap_threshold = '".$ret["service_high_flap_threshold"]."', ";
			$fields["service_high_flap_threshold"] = $ret["service_high_flap_threshold"];
		}
		if (isset($ret["service_flap_detection_enabled"]["service_flap_detection_enabled"])) {
			$rq .= "service_flap_detection_enabled = '".$ret["service_flap_detection_enabled"]["service_flap_detection_enabled"]."', ";
			$fields["service_flap_detection_enabled"] = $ret["service_flap_detection_enabled"]["service_flap_detection_enabled"];
		}
		if (isset($ret["service_process_perf_data"]["service_process_perf_data"])) {
			$rq .= "service_process_perf_data = '".$ret["service_process_perf_data"]["service_process_perf_data"]."', ";
			$fields["service_process_perf_data"] = $ret["service_process_perf_data"]["service_process_perf_data"];
		}
		if (isset($ret["service_retain_status_information"]["service_retain_status_information"])) {
			$rq .= "service_retain_status_information = '".$ret["service_retain_status_information"]["service_retain_status_information"]."', ";
			$fields["service_retain_status_information"]["service_retain_status_information"];
		}
		if (isset($ret["service_retain_nonstatus_information"]["service_retain_nonstatus_information"])) {
			$rq .= "service_retain_nonstatus_information = '".$ret["service_retain_nonstatus_information"]["service_retain_nonstatus_information"]."', ";
			$fields["service_retain_nonstatus_information"]["service_retain_nonstatus_information"];
		}
		/*if (isset($ret["service_notification_interval"]) && $ret["service_notification_interval"] != NULL) {
			$rq .= "service_notification_interval = '".$ret["service_notification_interval"]."', ";
			$fields["service_notification_interval"] = $ret["service_notification_interval"];
		}*/
	    /*if (isset($ret["service_first_notification_delay"]) && $ret["service_first_notification_delay"] != NULL) {
			$rq .= "service_first_notification_delay = '".$ret["service_first_notification_delay"]."', ";
			$fields["service_first_notification_delay"] = $ret["service_first_notification_delay"];
		}*/
		/*if (isset($ret["service_notifOpts"]) && $ret["service_notifOpts"] != NULL) {
			$rq .= "service_notification_options = '".implode(",", array_keys($ret["service_notifOpts"]))."', ";
			$fields["service_notifOpts"] = implode(",", array_keys($ret["service_notifOpts"]));
		}*/
		if (isset($ret["service_notifications_enabled"]["service_notifications_enabled"])) {
			$rq .= "service_notifications_enabled = '".$ret["service_notifications_enabled"]["service_notifications_enabled"]."', ";
			$fields["service_notifications_enabled"] = $ret["service_notifications_enabled"]["service_notifications_enabled"];
		}
		if (isset($ret["service_stalOpts"]) && $ret["service_stalOpts"] != NULL) {
			$rq .= "service_stalking_options = '".implode(",", array_keys($ret["service_stalOpts"]))."', ";
			$fields["service_stalOpts"] = implode(",", array_keys($ret["service_stalOpts"]));
		}
		if (isset($ret["service_comment"]) && $ret["service_comment"] != NULL) {
			$rq .= "service_comment = '".CentreonDB::escape($ret["service_comment"])."', ";
			$fields["service_comment"] = CentreonDB::escape($ret["service_comment"]);
		}
		$ret["command_command_id_arg"] = getCommandArgs($_POST, $ret);
		if (isset($ret["command_command_id_arg"]) && $ret["command_command_id_arg"] != NULL) {
			$rq .= "command_command_id_arg = '".CentreonDB::escape($ret["command_command_id_arg"])."', ";
			$fields["command_command_id_arg"] = CentreonDB::escape($ret["command_command_id_arg"]);
		}
		if (isset($ret["command_command_id_arg2"]) && $ret["command_command_id_arg2"] != NULL) {
			$rq .= "command_command_id_arg2 = '".CentreonDB::escape($ret["command_command_id_arg2"])."', ";
			$fields["command_command_id_arg2"] = CentreonDB::escape($ret["command_command_id_arg2"]);
		}
		if (isset($ret["service_register"]["service_register"]) && $ret["service_register"]["service_register"] != NULL) {
			$rq .= "service_register = '".$ret["service_register"]["service_register"]."', ";
			$fields["service_register"] = $ret["service_register"]["service_register"];
		}
		if (isset($ret["service_activate"]["service_activate"]) && $ret["service_activate"]["service_activate"] != NULL) {
			$rq .= "service_activate = '".$ret["service_activate"]["service_activate"]."', ";
			$fields["service_activate"] = $ret["service_activate"]["service_activate"];
		}

		if (isset($ret["esi_notes"]) && $ret["esi_notes"] != NULL)
			$fields["esi_notes"] = CentreonDB::escape($ret["esi_notes"]);
		if (isset($ret["esi_notes_url"]) && $ret["esi_notes_url"] != NULL)
			$fields["esi_notes_url"] = CentreonDB::escape($ret["esi_notes_url"]);
		if (isset($ret["esi_action_url"]) && $ret["esi_action_url"] != NULL)
			$fields["esi_action_url"] = CentreonDB::escape($ret["esi_action_url"]);
		if (isset($ret["esi_icon_image"]) && $ret["esi_icon_image"] != NULL)
			$fields["esi_icon_image"] = CentreonDB::escape($ret["esi_icon_image"]);
		if (isset($ret["esi_icon_image_alt"]) && $ret["esi_icon_image_alt"] != NULL)
			$fields["esi_icon_image_alt"] = CentreonDB::escape($ret["esi_icon_image_alt"]);
		if (isset($ret["graph_id"]) && $ret["graph_id"] != NULL)
			$fields["graph_id"] = $ret["graph_id"];
		if (isset($ret["service_cs"]) && $ret["service_cs"] != NULL)
			$fields["service_cs"] = implode(",", $ret["service_cs"]);
		if (isset($ret["service_cgs"]) && $ret["service_cgs"] != NULL)
			$fields["service_cgs"] = implode(",", $ret["service_cgs"]);
		if (isset($ret["service_sgs"]) && $ret["service_sgs"] != NULL)
			$fields["service_sgs"] = implode(",", $ret["service_sgs"]);
		if (isset($ret["service_hPars"]) && $ret["service_hPars"] != NULL)
			$fields["service_hPars"] = implode(",", $ret["service_hPars"]);
		if (isset($ret["service_hgPars"]) && $ret["service_hgPars"] != NULL)
			$fields["service_hgPars"] = implode(",", $ret["service_hgPars"]);
		if (isset($ret["service_categories"]) && $ret["service_categories"] != NULL)
			$fields["service_categories"] = implode(",", $ret["service_categories"]);
		if (isset($ret["service_traps"]) && $ret["service_traps"] != NULL)
			$fields["service_traps"] = implode(",", $ret["service_traps"]);

		if (strcmp("UPDATE service SET ", $rq))	{
			# Delete last ',' in request
			$rq[strlen($rq)-2] = " ";
			$rq .= "WHERE service_id = '".$service_id."'";
			$DBRESULT = $pearDB->query($rq);
		}

		/*
		 *  Update on demand macros
		 */
		if (isset($_POST['nbOfMacro']) && $_POST['nbOfMacro']) {
			$already_stored = array();
			$already_stored_in_db = array();

			$rq = "SELECT svc_macro_name FROM `on_demand_macro_service` WHERE `svc_svc_id`=" . $service_id;
			$DBRESULT = $pearDB->query($rq);
			while ($mac = $DBRESULT->fetchRow()) {
				$tmp = str_replace("\$_SERVICE", "", $mac["svc_macro_name"]);
				$tmp = str_replace("\$", "", $tmp);
				$tmp = strtolower($tmp);
				$already_stored_in_db[$tmp] = 1;
			}


	 		for ($i=0; $i <= $_POST['nbOfMacro']; $i++)
	 		{
	 			$macInput = "macroInput_" . $i;
	 			$macValue = "macroValue_" . $i;
	 			if (isset($_POST[$macInput]) && isset($already_stored_in_db[strtolower($_POST[$macInput])])) {
	 				$_POST[$macInput] = str_replace("\$_SERVICE", "", $_POST[$macInput]);
		 			$_POST[$macInput] = str_replace("\$", "", $_POST[$macInput]);
		 			$macName = $_POST[$macInput];
		 			$macVal = $_POST[$macValue];
	 				$rq = "UPDATE on_demand_macro_service SET `svc_macro_value`='". CentreonDB::escape($macVal) . "'".
	 					  " WHERE `svc_svc_id`=" . $service_id .
	 					  " AND `svc_macro_name`='\$_SERVICE" . CentreonDB::escape($macName) . "\$'";
			 		$DBRESULT = $pearDB->query($rq);
	 			}
	 			elseif (isset($_POST[$macInput]) && !isset($already_stored[strtolower($_POST[$macInput])]) && $_POST[$macInput]) {
		 			$_POST[$macInput] = str_replace("\$_SERVICE", "", $_POST[$macInput]);
		 			$_POST[$macInput] = str_replace("\$", "", $_POST[$macInput]);
		 			$macName = $_POST[$macInput];
		 			$macVal = $_POST[$macValue];
		 			$rq = "INSERT INTO on_demand_macro_service (`svc_macro_name`, `svc_macro_value`, `svc_svc_id`) VALUES ('\$_SERVICE". CentreonDB::escape(strtoupper($macName)) ."\$', '". CentreonDB::escape($macVal) ."', ". $service_id .")";
			 		$DBRESULT = $pearDB->query($rq);
					$already_stored[$_POST[$macInput]] = 1;
	 			}
	 			$fields["_".strtoupper($_POST[$macInput])."_"] = $_POST[$macValue];
	 		}
		}
		$centreon->CentreonLogAction->insertLog("service", $service_id, getHostServiceCombo($service_id, getMyServiceName($service_id), ENT_QUOTES, "UTF-8"), "mc", $fields);
	}

	/*
	 *  For Nagios 3
	 */
	function updateServiceContact($service_id = null, $ret = array())	{
		if (!$service_id) return;
		global $form;
		global $pearDB;
		$rq = "DELETE FROM contact_service_relation ";
		$rq .= "WHERE service_service_id = '".$service_id."'";
		$DBRESULT = $pearDB->query($rq);
		if (isset($ret["service_cs"]))
			$ret = $ret["service_cs"];
		else
			$ret = $form->getSubmitValue("service_cs");
		for($i = 0; $i < count($ret); $i++)	{
			$rq = "INSERT INTO contact_service_relation ";
			$rq .= "(contact_id, service_service_id) ";
			$rq .= "VALUES ";
			$rq .= "('".$ret[$i]."', '".$service_id."')";
			$DBRESULT = $pearDB->query($rq);
		}
	}

	function updateServiceContactGroup($service_id = null, $ret = array())	{
		if (!$service_id) return;
		global $form;
		global $pearDB;
		$rq = "DELETE FROM contactgroup_service_relation ";
		$rq .= "WHERE service_service_id = '".$service_id."'";
		$DBRESULT = $pearDB->query($rq);
		if (isset($ret["service_cgs"]))
			$ret = $ret["service_cgs"];
		else
			$ret = $form->getSubmitValue("service_cgs");
		$cg = new CentreonContactgroup($pearDB);
		for($i = 0; $i < count($ret); $i++)	{
		    if (!is_numeric($ret[$i])) {
		        $res = $cg->insertLdapGroup($ret[$i]);
		        if ($res != 0) {
		            $ret[$i] = $res;
		        } else {
		            continue;
		        }
			}
			if (isset($ret[$i]) && $ret[$i] && $ret[$i] != "") {
    			$rq = "INSERT INTO contactgroup_service_relation ";
    			$rq .= "(contactgroup_cg_id, service_service_id) ";
    			$rq .= "VALUES ";
    			$rq .= "('".$ret[$i]."', '".$service_id."')";
    			$DBRESULT = $pearDB->query($rq);
			}
		}
	}


	function updateServiceNotifs($service_id = null, $ret = array())	{
		if (!$service_id) return;
		global $form;
		global $pearDB;

		if (isset($ret["service_notifOpts"]))
			$ret = $ret["service_notifOpts"];
		else
			$ret = $form->getSubmitValue("service_notifOpts");

		$rq = "UPDATE service SET " ;
		$rq .= "service_notification_options = ";
		isset($ret) && $ret != NULL ? $rq .= "'".implode(",", array_keys($ret))."' " : $rq .= "NULL ";
		$rq .= "WHERE service_id = '".$service_id."'";
		$DBRESULT =& $pearDB->query($rq);

	}

	# For massive change. incremental mode
	function updateServiceNotifs_MC($service_id = null)	{
		if (!$service_id) return;
		global $form;
		global $pearDB;

		$rq = "SELECT * FROM service ";
		$rq .= "WHERE service_id = '".$service_id."' LIMIT 1";
		$DBRESULT =& $pearDB->query($rq);
		$service = array();
		$service = array_map("myDecodeService", $DBRESULT->fetchRow());

		$ret = $form->getSubmitValue("service_notifOpts");

		isset($service["service_notification_options"]) && $service["service_notification_options"] != NULL ? $temp = $service["service_notification_options"] . ",". implode(",", array_keys($ret)) : $tmp = implode(",", array_keys($ret)) ;

		if (isset($temp) && $temp != NULL) {
		    $rq = "UPDATE service SET " ;
			$rq .= "service_notification_options = '". trim ($temp ,',')."' ";
			$rq .= "WHERE service_id = '".$service_id."'";
			$DBRESULT =& $pearDB->query($rq);
		}

	}


	function updateServiceNotifOptionInterval($service_id = null, $ret = array())	{
		if (!$service_id) return;
		global $form;
		global $pearDB;

		if (isset($ret["service_notification_interval"]))
			$ret = $ret["service_notification_interval"];
		else
			$ret = $form->getSubmitValue("service_notification_interval");

		$rq = "UPDATE service SET " ;
		$rq .= "service_notification_interval = ";
		isset($ret) && $ret != NULL ? $rq .= "'".$ret."' " : $rq .= "NULL ";
		$rq .= "WHERE service_id = '".$service_id."'";
		$DBRESULT =& $pearDB->query($rq);
	}

	# For massive change. incremental mode
	function updateServiceNotifOptionInterval_MC($service_id = null)	{
		if (!$service_id) return;
		global $form;
		global $pearDB;

		$ret = $form->getSubmitValue("service_notification_interval");

		if (isset($ret) && $ret != NULL) {
		    $rq = "UPDATE service SET " ;
			$rq .= "service_notification_interval = '".$ret."' ";
			$rq .= "WHERE service_id = '".$service_id."'";
			$DBRESULT =& $pearDB->query($rq);
		}

	}

	function updateServiceNotifOptionTimeperiod($service_id = null, $ret = array())	{
		if (!$service_id) return;
		global $form;
		global $pearDB;

		if (isset($ret["timeperiod_tp_id2"]))
			$ret = $ret["timeperiod_tp_id2"];
		else
			$ret = $form->getSubmitValue("timeperiod_tp_id2");

		$rq = "UPDATE service SET " ;
		$rq .= "timeperiod_tp_id2 = ";
		isset($ret) && $ret != NULL ? $rq .= "'".$ret."' " : $rq .= "NULL ";
		$rq .= "WHERE service_id = '".$service_id."'";
		$DBRESULT =& $pearDB->query($rq);
	}

	# For massive change. incremental mode
	function updateServiceNotifOptionTimeperiod_MC($service_id = null)	{
		if (!$service_id) return;
		global $form;
		global $pearDB;

		$ret = $form->getSubmitValue("timeperiod_tp_id2");

		if (isset($ret) && $ret != NULL) {
		    $rq = "UPDATE service SET " ;
			$rq .= "timeperiod_tp_id2 = '".$ret."' ";
			$rq .= "WHERE service_id = '".$service_id."'";
			$DBRESULT =& $pearDB->query($rq);
		}

	}

	function updateServiceNotifOptionFirstNotificationDelay($service_id = null, $ret = array())	{
		if (!$service_id) return;
		global $form;
		global $pearDB;

		if (isset($ret["service_first_notification_delay"]))
			$ret = $ret["service_first_notification_delay"];
		else
			$ret = $form->getSubmitValue("service_first_notification_delay");

		$rq = "UPDATE service SET " ;
		$rq .= "service_first_notification_delay = ";
		isset($ret) && $ret != NULL ? $rq .= "'".$ret."' " : $rq .= "NULL ";
		$rq .= "WHERE service_id = '".$service_id."'";
		$DBRESULT =& $pearDB->query($rq);
	}

	# For massive change. incremental mode
	function updateServiceNotifOptionFirstNotificationDelay_MC($service_id = null)	{
		if (!$service_id) return;
		global $form;
		global $pearDB;

		$ret = $form->getSubmitValue("service_first_notification_delay");

		if (isset($ret) && $ret != NULL) {
		    $rq = "UPDATE service SET " ;
			$rq .= "service_first_notification_delay = '".$ret."' ";
			$rq .= "WHERE service_id = '".$service_id."'";
			$DBRESULT =& $pearDB->query($rq);
		}

	}


	# For massive change. We just add the new list if the elem doesn't exist yet
	function updateServiceContactGroup_MC($service_id = null)	{
		if (!$service_id) return;
		global $form;
		global $pearDB;
		$rq = "SELECT * FROM contactgroup_service_relation ";
		$rq .= "WHERE service_service_id = '".$service_id."'";
		$DBRESULT = $pearDB->query($rq);
		$cgs = array();
		while($arr = $DBRESULT->fetchRow())
			$cgs[$arr["contactgroup_cg_id"]] = $arr["contactgroup_cg_id"];
		$ret = $form->getSubmitValue("service_cgs");
		$cg = new CentreonContactgroup($pearDB);
		for($i = 0; $i < count($ret); $i++)	{
			if (!isset($cgs[$ret[$i]]))	{
    			if (!is_numeric($ret[$i])) {
    		        $res = $cg->insertLdapGroup($ret[$i]);
    		        if ($res != 0) {
    		            $ret[$i] = $res;
    		        } else {
    		            continue;
    		        }
    			}
    			if (isset($ret[$i]) && $ret[$i] && $ret[$i] != "") {
    				$rq = "INSERT INTO contactgroup_service_relation ";
    				$rq .= "(contactgroup_cg_id, service_service_id) ";
    				$rq .= "VALUES ";
    				$rq .= "('".$ret[$i]."', '".$service_id."')";
    				$DBRESULT = $pearDB->query($rq);
    			}
			}
		}
	}

	# For massive change. We just add the new list if the elem doesn't exist yet
	function updateServiceContact_MC($service_id = null)	{
		if (!$service_id) return;
		global $form;
		global $pearDB;
		$rq = "SELECT * FROM contact_service_relation ";
		$rq .= "WHERE service_service_id = '".$service_id."'";
		$DBRESULT = $pearDB->query($rq);
		$cgs = array();
		while($arr = $DBRESULT->fetchRow())
			$cs[$arr["contact_id"]] = $arr["contact_id"];
		$ret = $form->getSubmitValue("service_cs");
		for($i = 0; $i < count($ret); $i++)	{
			if (!isset($cs[$ret[$i]]))	{
				$rq = "INSERT INTO contact_service_relation ";
				$rq .= "(contact_id, service_service_id) ";
				$rq .= "VALUES ";
				$rq .= "('".$ret[$i]."', '".$service_id."')";
				$DBRESULT = $pearDB->query($rq);
			}
		}
	}

	function updateServiceServiceGroup($service_id = null, $ret = array())	{
		if (!$service_id) return;
		global $form;
		global $pearDB;
		$rq = "DELETE FROM servicegroup_relation ";
		$rq .= "WHERE service_service_id = '".$service_id."'";
		$DBRESULT = $pearDB->query($rq);
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
					$DBRESULT = $pearDB->query($rq);
				}
			else if (count($ret1))
				foreach($ret1 as $key=>$value)	{
					$rq = "INSERT INTO servicegroup_relation ";
					$rq .= "(host_host_id, hostgroup_hg_id, service_service_id, servicegroup_sg_id) ";
					$rq .= "VALUES ";
					$rq .= "('".$value."', NULL, '".$service_id."', '".$ret[$i]."')";
					$DBRESULT = $pearDB->query($rq);
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
		$DBRESULT = $pearDB->query($rq);
		$hsgs = array();
		$hgsgs = array();
		while($arr = $DBRESULT->fetchRow())	{
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
						$DBRESULT = $pearDB->query($rq);
					}
				}
			else if (count($ret1))
				foreach($ret1 as $h) 	{
					if (!isset($hsgs[$h]))	{
						$rq = "INSERT INTO servicegroup_relation ";
						$rq .= "(host_host_id, hostgroup_hg_id, service_service_id, servicegroup_sg_id) ";
						$rq .= "VALUES ";
						$rq .= "('".$h."', NULL, '".$service_id."', '".$ret[$i]."')";
						$DBRESULT = $pearDB->query($rq);
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
		$DBRESULT = $pearDB->query($rq);
		if (isset($ret["service_traps"]))
			$ret = $ret["service_traps"];
		else
			$ret = $form->getSubmitValue("service_traps");
		for($i = 0; $i < count($ret); $i++)	{
			$rq = "INSERT INTO traps_service_relation ";
			$rq .= "(traps_id, service_id) ";
			$rq .= "VALUES ";
			$rq .= "('".$ret[$i]."', '".$service_id."')";
			$DBRESULT = $pearDB->query($rq);
		}
	}

	# For massive change. We just add the new list if the elem doesn't exist yet
	function updateServiceTrap_MC($service_id = null)	{
		if (!$service_id) return;
		global $form;
		global $pearDB;
		$rq = "SELECT * FROM traps_service_relation ";
		$rq .= "WHERE service_id = '".$service_id."'";
		$DBRESULT = $pearDB->query($rq);
		$traps = array();
		while ($arr = $DBRESULT->fetchRow())
			$traps[$arr["traps_id"]] = $arr["traps_id"];
		$ret = $form->getSubmitValue("service_traps");
		for($i = 0; $i < count($ret); $i++)	{
			if (!isset($traps[$ret[$i]]))	{
				$rq = "INSERT INTO traps_service_relation ";
				$rq .= "(traps_id, service_id) ";
				$rq .= "VALUES ";
				$rq .= "('".$ret[$i]."', '".$service_id."')";
				$DBRESULT = $pearDB->query($rq);
			}
		}
	}

	function updateServiceHost($service_id = null, $ret = array())
	{
		global $form, $pearDB;
		if (!$service_id) {
			return;
		}

		$ret1 = array();
		$ret2 = array();

		if (isset($ret["service_hPars"])) {
			$ret1 = $ret["service_hPars"];
		} else {
			$ret1 = $form->getSubmitValue("service_hPars");
		}
		if (isset($ret["service_hgPars"])) {
			$ret2 = $ret["service_hgPars"];
		} else {
			$ret2 = $form->getSubmitValue("service_hgPars");
		}

		/*
		 * Get actual config
		 */
		$rq = "SELECT host_host_id FROM escalation_service_relation " .
				" WHERE service_service_id = '".$service_id."'";
		$DBRESULT = $pearDB->query($rq);
		$cacheEsc = array();
		while ($data = $DBRESULT->fetchRow()) {
			$cacheEsc[$data['host_host_id']] = 1;
		}

		/*
		 * Get actual config
		 */
		$rq = "SELECT host_host_id FROM host_service_relation " .
				" WHERE service_service_id = '".$service_id."'";
		$DBRESULT = $pearDB->query($rq);
		$cache = array();
		while ($data = $DBRESULT->fetchRow()) {
			$cache[$data['host_host_id']] = 1;
		}

		if (count($ret1) == 1) {
			foreach ($cache as $host_id => $flag) {
				if (!isset($cacheEsc[$host_id]) && count($cacheEsc)) {
					$pearDB->query("UPDATE escalation_service_relation SET host_host_id = '".$ret1[0]."' WHERE service_service_id = '".$service_id."'");
				}
			}
		} else {
			foreach ($cache as $host_id) {
				if (!isset($cache[$host_id]) && count($cacheEsc)) {
					$pearDB->query("DELETE FROM escalation_service_relation WHERE host_host_id = '".$ret1[0]."' AND service_service_id = '".$service_id."'");
				}
			}
		}

		$rq = "DELETE FROM host_service_relation ";
		$rq .= "WHERE service_service_id = '".$service_id."'";
		$DBRESULT = $pearDB->query($rq);

		if (count($ret2)) {
			for ($i = 0; $i < count($ret2); $i++)	{
				$rq = "INSERT INTO host_service_relation ";
				$rq .= "(hostgroup_hg_id, host_host_id, servicegroup_sg_id, service_service_id) ";
				$rq .= "VALUES ";
				$rq .= "('".$ret2[$i]."', NULL, NULL, '".$service_id."')";
				$DBRESULT = $pearDB->query($rq);
				setHostChangeFlag($pearDB, null, $ret2[$i]);
			}
		} else if (count($ret1)) {
			for ($i = 0; $i < count($ret1); $i++)	{
				$rq = "INSERT INTO host_service_relation ";
				$rq .= "(hostgroup_hg_id, host_host_id, servicegroup_sg_id, service_service_id) ";
				$rq .= "VALUES ";
				$rq .= "(NULL, '".$ret1[$i]."', NULL, '".$service_id."')";
				$DBRESULT = $pearDB->query($rq);
				setHostChangeFlag($pearDB, $ret1[$i], null);
			}
		}
	}

	# For massive change. We just add the new list if the elem doesn't exist yet
	function updateServiceHost_MC($service_id = null)	{
		if (!$service_id) return;
		global $form, $pearDB;
		$rq = "SELECT * FROM host_service_relation ";
		$rq .= "WHERE service_service_id = '".$service_id."'";
		$DBRESULT = $pearDB->query($rq);
		$hsvs = array();
		$hgsvs = array();
		while($arr = $DBRESULT->fetchRow())	{
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
					$DBRESULT = $pearDB->query($rq);
					$rq = "INSERT INTO host_service_relation ";
					$rq .= "(hostgroup_hg_id, host_host_id, servicegroup_sg_id, service_service_id) ";
					$rq .= "VALUES ";
					$rq .= "('".$ret2[$i]."', NULL, NULL, '".$service_id."')";
					$DBRESULT = $pearDB->query($rq);
					setHostChangeFlag($pearDB, null, $ret2[$i]);
				}
			}
		else if (count($ret1))
			for($i = 0; $i < count($ret1); $i++)	{
				if (!isset($hsvs[$ret1[$i]]))	{
					$rq = "DELETE FROM host_service_relation ";
					$rq .= "WHERE service_service_id = '".$service_id."' AND hostgroup_hg_id IS NOT NULL";
					$DBRESULT = $pearDB->query($rq);
					$rq = "INSERT INTO host_service_relation ";
					$rq .= "(hostgroup_hg_id, host_host_id, servicegroup_sg_id, service_service_id) ";
					$rq .= "VALUES ";
					$rq .= "(NULL, '".$ret1[$i]."', NULL, '".$service_id."')";
					$DBRESULT = $pearDB->query($rq);
					setHostChangeFlag($pearDB, $ret1[$i], null);
				}
			}
	}

	function updateServiceExtInfos($service_id = null, $ret = array())	{
		if (!$service_id) return;
		global $form, $pearDB;
		if (!count($ret))
			$ret = $form->getSubmitValues();
		/*
		 * Check if image selected isn't a directory
		 */
		if (isset($ret["esi_icon_image"]) && strrchr("REP_", $ret["esi_icon_image"]))
			$ret["esi_icon_image"] = NULL;
		/*
		 *
		 */
		$rq = "UPDATE extended_service_information ";
		$rq .= "SET esi_notes = ";
		isset($ret["esi_notes"]) && $ret["esi_notes"] != NULL ? $rq .= "'".CentreonDB::escape($ret["esi_notes"])."', ": $rq .= "NULL, ";
		$rq .= "esi_notes_url = ";
		isset($ret["esi_notes_url"]) && $ret["esi_notes_url"] != NULL ? $rq .= "'".CentreonDB::escape($ret["esi_notes_url"])."', ": $rq .= "NULL, ";
		$rq .= "esi_action_url = ";
		isset($ret["esi_action_url"]) && $ret["esi_action_url"] != NULL ? $rq .= "'".CentreonDB::escape($ret["esi_action_url"])."', ": $rq .= "NULL, ";
		$rq .= "esi_icon_image = ";
		isset($ret["esi_icon_image"]) && $ret["esi_icon_image"] != NULL ? $rq .= "'".CentreonDB::escape($ret["esi_icon_image"])."', ": $rq .= "NULL, ";
		$rq .= "esi_icon_image_alt = ";
		isset($ret["esi_icon_image_alt"]) && $ret["esi_icon_image_alt"] != NULL ? $rq .= "'".CentreonDB::escape($ret["esi_icon_image_alt"])."', ": $rq .= "NULL, ";
		$rq .= "graph_id = ";
		isset($ret["graph_id"]) && $ret["graph_id"] != NULL ? $rq .= "'".CentreonDB::escape($ret["graph_id"])."' ": $rq .= "NULL ";
		$rq .= "WHERE service_service_id = '".$service_id."'";
		$DBRESULT = $pearDB->query($rq);
	}

	function updateServiceExtInfos_MC($service_id = null)	{
		if (!$service_id) return;
		global $form, $pearDB;
		$ret = $form->getSubmitValues();
		$rq = "UPDATE extended_service_information SET ";
		if (isset($ret["esi_notes"]) && $ret["esi_notes"] != NULL) $rq .= "esi_notes = '".CentreonDB::escape($ret["esi_notes"])."', ";
		if (isset($ret["esi_notes_url"]) && $ret["esi_notes_url"] != NULL) $rq .= "esi_notes_url = '".CentreonDB::escape($ret["esi_notes_url"])."', ";
		if (isset($ret["esi_action_url"]) && $ret["esi_action_url"] != NULL) $rq .= "esi_action_url = '".CentreonDB::escape($ret["esi_action_url"])."', ";
		if (isset($ret["esi_icon_image"]) && $ret["esi_icon_image"] != NULL) $rq .= "esi_icon_image = '".CentreonDB::escape($ret["esi_icon_image"])."', ";
		if (isset($ret["esi_icon_image_alt"]) && $ret["esi_icon_image_alt"] != NULL) $rq .= "esi_icon_image_alt = '".CentreonDB::escape($ret["esi_icon_image_alt"])."', ";
		if (isset($ret["graph_id"]) && $ret["graph_id"] != NULL) $rq .= "graph_id = '".CentreonDB::escape($ret["graph_id"])."', ";
		if (strcmp("UPDATE extended_service_information SET ", $rq))	{
			# Delete last ',' in request
			$rq[strlen($rq)-2] = " ";
			$rq .= "WHERE service_service_id = '".$service_id."'";
			$DBRESULT = $pearDB->query($rq);
		}
	}

	function updateServiceTemplateUsed($useTpls = array())	{
		if(!count($useTpls)) return;
		global $pearDB;
		require_once "./include/common/common-Func.php";
		foreach ($useTpls as $key=>$value)	{
			$DBRESULT = $pearDB->query("UPDATE service SET service_template_model_stm_id = '".getMyServiceTPLID($value)."' WHERE service_id = '".$key."'");
		}
	}

	function updateServiceCategories_MC($service_id = null, $ret = array())	{
		if (!$service_id)
			return;
		global $form, $pearDB;

		if (isset($ret["service_categories"]))
			$ret = $ret["service_categories"];
		else
			$ret = $form->getSubmitValue("service_categories");
		for ($i = 0; $i < count($ret); $i++)	{
			$rq = "INSERT INTO service_categories_relation ";
			$rq .= "(sc_id, service_service_id) ";
			$rq .= "VALUES ";
			$rq .= "('".$ret[$i]."', '".$service_id."')";
			$DBRESULT = $pearDB->query($rq);
		}
	}

	function updateServiceCategories($service_id = null, $ret = array())	{
		if (!$service_id) return;
		global $form, $pearDB;
		$rq = "DELETE FROM service_categories_relation WHERE service_service_id = '".$service_id."'";
		$DBRESULT = $pearDB->query($rq);
		if (isset($ret["service_categories"]))
			$ret = $ret["service_categories"];
		else
			$ret = $form->getSubmitValue("service_categories");
		for($i = 0; $i < count($ret); $i++)	{
			$rq = "INSERT INTO service_categories_relation ";
			$rq .= "(sc_id, service_service_id) ";
			$rq .= "VALUES ";
			$rq .= "('".$ret[$i]."', '".$service_id."')";
			$DBRESULT = $pearDB->query($rq);
		}
	}
?>