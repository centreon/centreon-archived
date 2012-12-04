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
		exit();
	}

	/**
	 *
	 * Test poller existance
	 * @param $name
	 */
	function testExistence ($name = NULL)	{
		global $pearDB, $form;
		$id = NULL;
		if (isset($form))
			$id = $form->getSubmitValue('id');
		$DBRESULT = $pearDB->query("SELECT name, id FROM `nagios_server` WHERE `name` = '".htmlentities($name, ENT_QUOTES, "UTF-8")."'");
		$ndomod = $DBRESULT->fetchRow();
		if ($DBRESULT->numRows() >= 1 && $ndomod["id"] == $id)#Modif case
			return true;
		else if ($DBRESULT->numRows() >= 1 && $ndomod["id"] != $id)#Duplicate entry
			return false;
		else
			return true;
	}

	function enableServerInDB ($id = null)	{
		global $pearDB;

		if (!$id)
			return;
		$DBRESULT = $pearDB->query("UPDATE `nagios_server` SET `ns_activate` = '1' WHERE id = '".$id."'");
	}

	function disableServerInDB ($id = null)	{
		if (!$id) return;
		global $pearDB;
		$DBRESULT = $pearDB->query("UPDATE `nagios_server` SET `ns_activate` = '0' WHERE id = '".$id."'");
	}

	function deleteServerInDB ($server = array())	{
		global $pearDB;
		foreach($server as $key => $value)	{
			$DBRESULT = $pearDB->query("DELETE FROM `nagios_server` WHERE id = '".$key."'");
		}
	}

	function multipleServerInDB ($server = array(), $nbrDup = array())	{
		global $pearDB;

		foreach ($server as $key => $value)	{
			$DBRESULT = $pearDB->query("SELECT * FROM `nagios_server` WHERE id = '".$key."' LIMIT 1");
			$row = $DBRESULT->fetchRow();
			$row["id"] = '';
			$row["ns_activate"] = '0';
			$row["is_default"] = '0';
			$row["localhost"] = '0';
			$DBRESULT->free();
			for ($i = 1; $i <= $nbrDup[$key]; $i++)	{
				$val = null;
				foreach ($row as $key2=>$value2)	{
					$key2 == "name" ? ($server_name = $value2 = $value2."_".$i) : null;
					$val ? $val .= ($value2 != NULL ? (", '".$value2."'"):", NULL") : $val .= ($value2 != NULL ? ("'".$value2."'") : "NULL");
				}
				if (testExistence($server_name))	{
					$val ? $rq = "INSERT INTO `nagios_server` VALUES (".$val.")" : $rq = null;
					$DBRESULT = $pearDB->query($rq);
				}
			}
		}
	}

	function updateServerInDB ($id = NULL)	{
		if (!$id)
			return;
		updateServer($id);
	}

	function insertServerInDB ()	{
		$id = insertServer();
		return ($id);
	}

	function insertServer($ret = array())	{
		global $form, $pearDB, $oreon;
		if (!count($ret))
			$ret = $form->getSubmitValues();
		$rq = "INSERT INTO `nagios_server` (`name` , `localhost` , `ns_ip_address`, `ssh_port`, `monitoring_engine`, `nagios_bin`, `nagiostats_bin` , `init_script`, `init_script_snmptt`, `nagios_perfdata` , `centreonbroker_cfg_path`, `centreonbroker_module_path`, `ssh_private_key`, `is_default`, `ns_activate`) ";
		$rq .= "VALUES (";
		isset($ret["name"]) && $ret["name"] != NULL ? $rq .= "'".htmlentities(trim($ret["name"]), ENT_QUOTES, "UTF-8")."', " : $rq .= "NULL, ";
		isset($ret["localhost"]["localhost"]) && $ret["localhost"]["localhost"] != NULL ? $rq .= "'".htmlentities($ret["localhost"]["localhost"], ENT_QUOTES, "UTF-8")."',  " : $rq .= "NULL, ";
        isset($ret["ns_ip_address"]) && $ret["ns_ip_address"] != NULL ? $rq .= "'".htmlentities(trim($ret["ns_ip_address"]), ENT_QUOTES, "UTF-8")."',  " : $rq .= "NULL, ";
        isset($ret["ssh_port"]) && $ret["ssh_port"] != NULL ? $rq .= "'".htmlentities(trim($ret["ssh_port"]), ENT_QUOTES, "UTF-8")."',  " : $rq .= "'22', ";
        isset($ret["monitoring_engine"]) && $ret["monitoring_engine"] != NULL ? $rq .= "'".htmlentities(trim($ret["monitoring_engine"]), ENT_QUOTES, "UTF-8")."',  " : $rq .= "NULL, ";
        isset($ret["nagios_bin"]) && $ret["nagios_bin"] != NULL ? $rq .= "'".htmlentities(trim($ret["nagios_bin"]), ENT_QUOTES, "UTF-8")."',  " : $rq .= "NULL, ";
        isset($ret["nagiostats_bin"]) && $ret["nagiostats_bin"] != NULL ? $rq .= "'".htmlentities(trim($ret["nagiostats_bin"]), ENT_QUOTES, "UTF-8")."',  " : $rq .= "NULL, ";
        isset($ret["init_script"]) && $ret["init_script"] != NULL ? $rq .= "'".htmlentities(trim($ret["init_script"]), ENT_QUOTES, "UTF-8")."',  " : $rq .= "NULL, ";
        isset($ret["init_script_snmptt"]) && $ret["init_script_snmptt"] != NULL ? $rq .= "'".htmlentities(trim($ret["init_script_snmptt"]), ENT_QUOTES, "UTF-8")."',  " : $rq .= "NULL, ";
        isset($ret["nagios_perfdata"]) && $ret["nagios_perfdata"] != NULL ? $rq .= "'".htmlentities(trim($ret["nagios_perfdata"]), ENT_QUOTES, "UTF-8")."',  " : $rq .= "NULL, ";
        isset($ret["centreonbroker_cfg_path"]) && $ret["centreonbroker_cfg_path"] != NULL ? $rq .= "'".htmlentities(trim($ret["centreonbroker_cfg_path"]), ENT_QUOTES, "UTF-8")."',  " : $rq .= "NULL, ";
        isset($ret["centreonbroker_module_path"]) && $ret["centreonbroker_module_path"] != NULL ? $rq .= "'".htmlentities(trim($ret["centreonbroker_module_path"]), ENT_QUOTES, "UTF-8")."',  " : $rq .= "NULL, ";
        isset($ret["ssh_private_key"]) && $ret["ssh_private_key"] != NULL ? $rq .= "'".htmlentities(trim($ret["ssh_private_key"]), ENT_QUOTES, "UTF-8")."',  " : $rq .= "NULL, ";
        isset($ret["is_default"]["is_default"]) && $ret["is_default"]["is_default"] != NULL ? $rq .= "'".htmlentities(trim($ret["is_default"]["is_default"]), ENT_QUOTES, "UTF-8")."',  " : $rq .= "NULL, ";
        isset($ret["ns_activate"]["ns_activate"]) && $ret["ns_activate"]["ns_activate"] != 2 ? $rq .= "'".$ret["ns_activate"]["ns_activate"]."'  "  : $rq .= "NULL)";
       	$rq .= ")";
       	$DBRESULT = $pearDB->query($rq);
		$DBRESULT = $pearDB->query("SELECT MAX(id) FROM `nagios_server`");
		$ndomod_id = $DBRESULT->fetchRow();
		$DBRESULT->free();
		return ($ndomod_id["MAX(id)"]);
	}

	function updateServer($id = null)	{
		global $form, $pearDB;
		if (!$id)
			return;
		$ret = array();
		$ret = $form->getSubmitValues();

		if ($ret["localhost"]["localhost"] == 1){
			$DBRESULT = $pearDB->query("UPDATE `nagios_server` SET `localhost` = '0'");
		}

		if ($ret["is_default"]["is_default"] == 1){
			$DBRESULT = $pearDB->query("UPDATE `nagios_server` SET `is_default` = '0'");
		}

		$rq = "UPDATE `nagios_server` SET ";
        isset($ret["name"]) && $ret["name"] != NULL ? $rq .= "name = '".htmlentities($ret["name"], ENT_QUOTES, "UTF-8")."', " : $rq .= "name = NULL, ";
        isset($ret["localhost"]["localhost"]) && $ret["localhost"]["localhost"] != NULL ? $rq .= "localhost = '".htmlentities($ret["localhost"]["localhost"], ENT_QUOTES, "UTF-8")."', " : $rq .= "localhost = NULL, ";
		isset($ret["ns_ip_address"]) && $ret["ns_ip_address"] != NULL ? $rq .= "ns_ip_address = '".htmlentities(trim($ret["ns_ip_address"]), ENT_QUOTES, "UTF-8")."',  " : $rq .= "ns_ip_address = NULL, ";
        isset($ret["ssh_port"]) && $ret["ssh_port"] != NULL ? $rq .= "ssh_port = '".htmlentities(trim($ret["ssh_port"]), ENT_QUOTES, "UTF-8")."',  " : $rq .= "ssh_port = '22', ";
        isset($ret["init_script"]) && $ret["init_script"] != NULL ? $rq .= "init_script = '".htmlentities(trim($ret["init_script"]), ENT_QUOTES, "UTF-8")."',  " : $rq .= "init_script = NULL, ";
        isset($ret["init_script_snmptt"]) && $ret["init_script_snmptt"] != NULL ? $rq .= "init_script_snmptt = '".htmlentities(trim($ret["init_script_snmptt"]), ENT_QUOTES, "UTF-8")."',  " : $rq .= "init_script_snmptt = NULL, ";
        isset($ret["monitoring_engine"]) && $ret["monitoring_engine"] != NULL ? $rq .= "monitoring_engine = '".htmlentities(trim($ret["monitoring_engine"]), ENT_QUOTES, "UTF-8")."',  " : $rq .= "monitoring_engine = NULL, ";
        isset($ret["nagios_bin"]) && $ret["nagios_bin"] != NULL ? $rq .= "nagios_bin = '".htmlentities(trim($ret["nagios_bin"]), ENT_QUOTES, "UTF-8")."',  " : $rq .= "nagios_bin = NULL, ";
        isset($ret["nagiostats_bin"]) && $ret["nagiostats_bin"] != NULL ? $rq .= "nagiostats_bin = '".htmlentities(trim($ret["nagiostats_bin"]), ENT_QUOTES, "UTF-8")."',  " : $rq .= "nagiostats_bin = NULL, ";
        isset($ret["nagios_perfdata"]) && $ret["nagios_perfdata"] != NULL ? $rq .= "nagios_perfdata = '".htmlentities(trim($ret["nagios_perfdata"]), ENT_QUOTES, "UTF-8")."',  " : $rq .= "nagios_perfdata = NULL, ";
        isset($ret["centreonbroker_cfg_path"]) && $ret["centreonbroker_cfg_path"] != NULL ? $rq .= "centreonbroker_cfg_path = '".htmlentities(trim($ret["centreonbroker_cfg_path"]), ENT_QUOTES, "UTF-8")."',  " : $rq .= "centreonbroker_cfg_path = NULL, ";
        isset($ret["centreonbroker_module_path"]) && $ret["centreonbroker_module_path"] != NULL ? $rq .= "centreonbroker_module_path = '".htmlentities(trim($ret["centreonbroker_module_path"]), ENT_QUOTES, "UTF-8")."',  " : $rq .= "centreonbroker_module_path = NULL, ";
        isset($ret["ssh_private_key"]) && $ret["ssh_private_key"] != NULL ? $rq .= "ssh_private_key = '".htmlentities(trim($ret["ssh_private_key"]), ENT_QUOTES, "UTF-8")."',  " : $rq .= "ssh_private_key = NULL, ";
        isset($ret["is_default"]) && $ret["is_default"] != NULL ? $rq .= "is_default = '".htmlentities(trim($ret["is_default"]["is_default"]), ENT_QUOTES, "UTF-8")."',  " : $rq .= "is_default = NULL, ";
        $rq .= "ns_activate = '".$ret["ns_activate"]["ns_activate"]."' ";
		$rq .= "WHERE id = '".$id."'";
		$DBRESULT = $pearDB->query($rq);
	}
	
	/**
	 * 
	 * Check if a service or an host has been 
	 * changed for a specific poller.
	 * @param unknown_type $poller_id
	 * @param unknown_type $last_restart
	 * @return number
	 */
	function checkChangeState($poller_id, $last_restart) {
		global $pearDB, $pearDBO, $conf_centreon;
		
		if (!isset($last_restart) || $last_restart == "") {
			return 0;
		}
	
		$request = "SELECT * 
						FROM log_action 
						WHERE 
							action_log_date > $last_restart AND 
							((object_type = 'host' AND 
							object_id IN (
								SELECT host_host_id 
								FROM ".$conf_centreon['db'].".ns_host_relation 
								WHERE nagios_server_id = '$poller_id'
							)) OR 
							(object_type = 'service') AND 
							object_id IN (
								SELECT service_service_id 
								FROM ".$conf_centreon['db'].".ns_host_relation nhr, ".$conf_centreon['db'].".host_service_relation hsr 
								WHERE nagios_server_id = '$poller_id' AND hsr.host_host_id = nhr.host_host_id
						))";
		$DBRESULT = $pearDBO->query($request);
		if ($DBRESULT->numRows()) {
			return 1;
		}
		return 0;
	}
?>