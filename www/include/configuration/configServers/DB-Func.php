<?php
/*
 * Copyright 2005-2015 Centreon
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
 * As a special exception, the copyright holders of this program give Centreon
 * permission to link this program with independent modules to produce an executable,
 * regardless of the license terms of these independent modules, and to copy and
 * distribute the resulting executable under terms of Centreon choice, provided that
 * Centreon also meet, for each linked independent module, the terms  and conditions
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
		global $pearDB, $pearDBO;

		foreach($server as $key => $value)	{
			$pearDB->query("DELETE FROM `nagios_server` WHERE id = '".$key."'");
            $pearDBO->query("UPDATE `instances` SET deleted = '1' WHERE instance_id = '".$key."'");
		}
	}

	function multipleServerInDB ($server = array(), $nbrDup = array())	{
		global $pearDB;

		foreach ($server as $key => $value)	{
			$DBRESULT = $pearDB->query("SELECT * FROM `nagios_server` WHERE id = '".$key."' LIMIT 1");
			$rowServer = $DBRESULT->fetchRow();
			$rowServer["id"] = '';
			$rowServer["ns_activate"] = '0';
			$rowServer["is_default"] = '0';
			$rowServer["localhost"] = '0';
			$DBRESULT->free();
			for ($i = 1; $i <= $nbrDup[$key]; $i++)	{
				$val = null;
				foreach ($rowServer as $key2=>$value2)	{
					$key2 == "name" ? ($server_name = $value2 = $value2."_".$i) : null;
					$val ? $val .= ($value2 != NULL ? (", '".$value2."'"):", NULL") : $val .= ($value2 != NULL ? ("'".$value2."'") : "NULL");
				}
				if (testExistence($server_name))	{
					$val ? $rq = "INSERT INTO `nagios_server` VALUES (".$val.")" : $rq = null;
					$DBRESULT = $pearDB->query($rq);
					$queryGetId = 'SELECT id
						FROM nagios_server
						WHERE name = "' . $server_name . '"';
					$res = $pearDB->query($queryGetId);
					if (false === PEAR::isError($res)) {
					    if ($res->numRows() > 0) {
					        $row = $res->fetchRow();
					        $queryRel = 'INSERT INTO cfg_resource_instance_relations
					        	(resource_id, instance_id)
					        	SELECT b.resource_id, ' . $row['id'] . '
					        		FROM cfg_resource_instance_relations as b
					        		WHERE b.instance_id = ' . $key;
					        $pearDB->query($queryRel);
                                                $queryCmd = 'INSERT INTO poller_command_relations (poller_id, command_id, command_order)
                                                            SELECT ' . $row['id'] . ', b.command_id, b.command_order
					        	    FROM poller_command_relations as b
                                                            WHERE b.poller_id = ' . $key;
					        $pearDB->query($queryCmd);
					    }
					}
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
                
		insertServerInCfgNagios($id);
		addUserRessource($id);
		return ($id);
	}

	function insertServer($ret = array())	{
		global $form, $pearDB, $oreon;
		if (!count($ret))
			$ret = $form->getSubmitValues();
        $rq = "INSERT INTO `nagios_server` (`name` , `localhost`, `ns_ip_address`, `ssh_port`, `monitoring_engine`, `nagios_bin`, `nagiostats_bin` , `init_script`, `init_script_centreontrapd`, `snmp_trapd_path_conf`, `nagios_perfdata` , `centreonbroker_cfg_path`, `centreonbroker_module_path`, `centreonconnector_path`, `ssh_private_key`, `is_default`, `ns_activate`) ";
		$rq .= "VALUES (";
		isset($ret["name"]) && $ret["name"] != NULL ? $rq .= "'".htmlentities(trim($ret["name"]), ENT_QUOTES, "UTF-8")."', " : $rq .= "NULL, ";
		isset($ret["localhost"]["localhost"]) && $ret["localhost"]["localhost"] != NULL ? $rq .= "'".htmlentities($ret["localhost"]["localhost"], ENT_QUOTES, "UTF-8")."',  " : $rq .= "NULL, ";
        //        isset($ret["description"]) && $ret["description"] != NULL ? $rq .= "'".htmlentities(trim($ret["description"]), ENT_QUOTES, "UTF-8")."',  " : $rq .= "NULL, ";
        isset($ret["ns_ip_address"]) && $ret["ns_ip_address"] != NULL ? $rq .= "'".htmlentities(trim($ret["ns_ip_address"]), ENT_QUOTES, "UTF-8")."',  " : $rq .= "NULL, ";
        isset($ret["ssh_port"]) && $ret["ssh_port"] != NULL ? $rq .= "'".htmlentities(trim($ret["ssh_port"]), ENT_QUOTES, "UTF-8")."',  " : $rq .= "'22', ";
        isset($ret["monitoring_engine"]) && $ret["monitoring_engine"] != NULL ? $rq .= "'".htmlentities(trim($ret["monitoring_engine"]), ENT_QUOTES, "UTF-8")."',  " : $rq .= "NULL, ";
        isset($ret["nagios_bin"]) && $ret["nagios_bin"] != NULL ? $rq .= "'".htmlentities(trim($ret["nagios_bin"]), ENT_QUOTES, "UTF-8")."',  " : $rq .= "NULL, ";
        isset($ret["nagiostats_bin"]) && $ret["nagiostats_bin"] != NULL ? $rq .= "'".htmlentities(trim($ret["nagiostats_bin"]), ENT_QUOTES, "UTF-8")."',  " : $rq .= "NULL, ";
        isset($ret["init_script"]) && $ret["init_script"] != NULL ? $rq .= "'".htmlentities(trim($ret["init_script"]), ENT_QUOTES, "UTF-8")."',  " : $rq .= "NULL, ";
        isset($ret["init_script_centreontrapd"]) && $ret["init_script_centreontrapd"] != NULL ? $rq .= "'".htmlentities(trim($ret["init_script_centreontrapd"]), ENT_QUOTES, "UTF-8")."',  " : $rq .= "NULL, ";
        isset($ret["snmp_trapd_path_conf"]) && $ret["snmp_trapd_path_conf"] != NULL ? $rq .= "'".htmlentities(trim($ret["snmp_trapd_path_conf"]), ENT_QUOTES, "UTF-8")."',  " : $rq .= "NULL, ";
        isset($ret["nagios_perfdata"]) && $ret["nagios_perfdata"] != NULL ? $rq .= "'".htmlentities(trim($ret["nagios_perfdata"]), ENT_QUOTES, "UTF-8")."',  " : $rq .= "NULL, ";
        isset($ret["centreonbroker_cfg_path"]) && $ret["centreonbroker_cfg_path"] != NULL ? $rq .= "'".htmlentities(trim($ret["centreonbroker_cfg_path"]), ENT_QUOTES, "UTF-8")."',  " : $rq .= "NULL, ";
        isset($ret["centreonbroker_module_path"]) && $ret["centreonbroker_module_path"] != NULL ? $rq .= "'".htmlentities(trim($ret["centreonbroker_module_path"]), ENT_QUOTES, "UTF-8")."',  " : $rq .= "NULL, ";
        isset($ret["centreonconnector_path"]) && $ret["centreonconnector_path"] != NULL ? $rq .= "'".htmlentities(trim($ret["centreonconnector_path"]), ENT_QUOTES, "UTF-8")."',  " : $rq .= "NULL, ";
        isset($ret["ssh_private_key"]) && $ret["ssh_private_key"] != NULL ? $rq .= "'".htmlentities(trim($ret["ssh_private_key"]), ENT_QUOTES, "UTF-8")."',  " : $rq .= "NULL, ";
        isset($ret["is_default"]["is_default"]) && $ret["is_default"]["is_default"] != NULL ? $rq .= "'".htmlentities(trim($ret["is_default"]["is_default"]), ENT_QUOTES, "UTF-8")."',  " : $rq .= "NULL, ";
        isset($ret["ns_activate"]["ns_activate"]) && $ret["ns_activate"]["ns_activate"] != 2 ? $rq .= "'".$ret["ns_activate"]["ns_activate"]."'  "  : $rq .= "NULL)";
       	$rq .= ")";
       	$DBRESULT = $pearDB->query($rq);
		$DBRESULT = $pearDB->query("SELECT MAX(id) as last_id FROM `nagios_server`");
		$poller = $DBRESULT->fetchRow();
		$DBRESULT->free();
                if (isset($_REQUEST['pollercmd'])) {
                    $instanceObj = new CentreonInstance($pearDB);
                    $instanceObj->setCommands($poller['last_id'], $_REQUEST['pollercmd']);
                }
		return ($poller["last_id"]);
	}

	function addUserRessource($serverId) {
	    global $pearDB;
	    $queryInsert = "INSERT INTO cfg_resource_instance_relations
	    	(resource_id, instance_id)
	    	VALUES (%s, %s)";
	    $queryGetResources = "SELECT resource_id, resource_name
	    	FROM cfg_resource
	    	ORDER BY resource_id";
	    $res = $pearDB->query($queryGetResources);
	    if (PEAR::isError($res)) {
	        return false;
	    }
	    $isInsert = array();
	    while ($row = $res->fetchRow()) {
	        if (!in_array($row['resource_name'], $isInsert)) {
	            $isInsert[] = $row['resource_name'];
	            $query = sprintf($queryInsert, $row['resource_id'], $serverId);
	            $pearDB->query($query);
	        }
	    }
	    return true;
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
        //isset($ret["description"]) && $ret["description"] != NULL ? $rq .= "description = '".htmlentities($ret["description"], ENT_QUOTES, "UTF-8")."', " : $rq .= "description = NULL, ";
        isset($ret["ns_ip_address"]) && $ret["ns_ip_address"] != NULL ? $rq .= "ns_ip_address = '".htmlentities(trim($ret["ns_ip_address"]), ENT_QUOTES, "UTF-8")."',  " : $rq .= "ns_ip_address = NULL, ";
        isset($ret["ssh_port"]) && $ret["ssh_port"] != NULL ? $rq .= "ssh_port = '".htmlentities(trim($ret["ssh_port"]), ENT_QUOTES, "UTF-8")."',  " : $rq .= "ssh_port = '22', ";
        isset($ret["init_script"]) && $ret["init_script"] != NULL ? $rq .= "init_script = '".htmlentities(trim($ret["init_script"]), ENT_QUOTES, "UTF-8")."',  " : $rq .= "init_script = NULL, ";
        isset($ret["init_script_centreontrapd"]) && $ret["init_script_centreontrapd"] != NULL ? $rq .= "init_script_centreontrapd = '".htmlentities(trim($ret["init_script_centreontrapd"]), ENT_QUOTES, "UTF-8")."',  " : $rq .= "init_script_centreontrapd = NULL, ";
        isset($ret["snmp_trapd_path_conf"]) && $ret["snmp_trapd_path_conf"] != NULL ? $rq .= "snmp_trapd_path_conf = '".htmlentities(trim($ret["snmp_trapd_path_conf"]), ENT_QUOTES, "UTF-8")."',  " : $rq .= "snmp_trapd_path_conf = NULL, ";
        isset($ret["monitoring_engine"]) && $ret["monitoring_engine"] != NULL ? $rq .= "monitoring_engine = '".htmlentities(trim($ret["monitoring_engine"]), ENT_QUOTES, "UTF-8")."',  " : $rq .= "monitoring_engine = NULL, ";
        isset($ret["nagios_bin"]) && $ret["nagios_bin"] != NULL ? $rq .= "nagios_bin = '".htmlentities(trim($ret["nagios_bin"]), ENT_QUOTES, "UTF-8")."',  " : $rq .= "nagios_bin = NULL, ";
        isset($ret["nagiostats_bin"]) && $ret["nagiostats_bin"] != NULL ? $rq .= "nagiostats_bin = '".htmlentities(trim($ret["nagiostats_bin"]), ENT_QUOTES, "UTF-8")."',  " : $rq .= "nagiostats_bin = NULL, ";
        isset($ret["nagios_perfdata"]) && $ret["nagios_perfdata"] != NULL ? $rq .= "nagios_perfdata = '".htmlentities(trim($ret["nagios_perfdata"]), ENT_QUOTES, "UTF-8")."',  " : $rq .= "nagios_perfdata = NULL, ";
        isset($ret["centreonbroker_cfg_path"]) && $ret["centreonbroker_cfg_path"] != NULL ? $rq .= "centreonbroker_cfg_path = '".htmlentities(trim($ret["centreonbroker_cfg_path"]), ENT_QUOTES, "UTF-8")."',  " : $rq .= "centreonbroker_cfg_path = NULL, ";
        isset($ret["centreonbroker_module_path"]) && $ret["centreonbroker_module_path"] != NULL ? $rq .= "centreonbroker_module_path = '".htmlentities(trim($ret["centreonbroker_module_path"]), ENT_QUOTES, "UTF-8")."',  " : $rq .= "centreonbroker_module_path = NULL, ";
        isset($ret["centreonconnector_path"]) && $ret["centreonconnector_path"] != NULL ? $rq .= "centreonconnector_path = '".htmlentities(trim($ret["centreonconnector_path"]), ENT_QUOTES, "UTF-8")."',  " : $rq .= "centreonconnector_path = NULL, ";
        isset($ret["ssh_private_key"]) && $ret["ssh_private_key"] != NULL ? $rq .= "ssh_private_key = '".htmlentities(trim($ret["ssh_private_key"]), ENT_QUOTES, "UTF-8")."',  " : $rq .= "ssh_private_key = NULL, ";
        isset($ret["is_default"]) && $ret["is_default"] != NULL ? $rq .= "is_default = '".htmlentities(trim($ret["is_default"]["is_default"]), ENT_QUOTES, "UTF-8")."',  " : $rq .= "is_default = NULL, ";
        $rq .= "ns_activate = '".$ret["ns_activate"]["ns_activate"]."' ";
		$rq .= "WHERE id = '".$id."'";
		$DBRESULT = $pearDB->query($rq);
                if (isset($_REQUEST['pollercmd'])) {
                    $instanceObj = new CentreonInstance($pearDB);
                    $instanceObj->setCommands($id, $_REQUEST['pollercmd']);
                }
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
        /**
         * Insert the instance in cfg_nagios
         * @param int $iId

         */
        function insertServerInCfgNagios($iId)
        {
            global $pearDB, $aInstanceDefaultValues;
            
            if (!isset($aInstanceDefaultValues) || !isset($iId)) {
                return;
            }
            $rq = "INSERT INTO `cfg_nagios` (`nagios_server_id`, `log_file`, `cfg_dir`, `object_cache_file`, `temp_file`, `temp_path`, `status_file`, 
            `p1_file`, `status_update_interval`, `nagios_user`, `nagios_group`, `enable_notifications`, `execute_service_checks`, `accept_passive_service_checks`, `execute_host_checks`, 
            `accept_passive_host_checks`, `enable_event_handlers`, `log_rotation_method`, `log_archive_path`, `check_external_commands`, `external_command_buffer_slots`, 
            `command_check_interval`, `command_file`, `lock_file`, `retain_state_information`, `state_retention_file`,`retention_update_interval`, `use_retained_program_state`, 
            `use_retained_scheduling_info`, `use_syslog`, `log_notifications`, `log_service_retries`, `log_host_retries`, `log_event_handlers`, `log_initial_states`, 
            `log_external_commands`, `log_passive_checks`, `sleep_time`, `service_inter_check_delay_method`, `host_inter_check_delay_method`, `service_interleave_factor`, 
            `max_concurrent_checks`, `max_service_check_spread`, `max_host_check_spread`, `check_result_reaper_frequency`, `max_check_result_reaper_time`, `interval_length`, 
            `auto_reschedule_checks`, `use_aggressive_host_checking`, `enable_flap_detection`, `low_service_flap_threshold`, `high_service_flap_threshold`, `low_host_flap_threshold`, 
            `high_host_flap_threshold`, `soft_state_dependencies`, `service_check_timeout`, `host_check_timeout`, `event_handler_timeout`, `notification_timeout`, `ocsp_timeout`, 
            `ochp_timeout`, `perfdata_timeout`, `obsess_over_services`, `obsess_over_hosts`, `process_performance_data`, `host_perfdata_file_mode`, `service_perfdata_file_mode`, 
            `check_for_orphaned_services`, `check_for_orphaned_hosts`, `check_service_freshness`, `check_host_freshness`, `date_format`, `illegal_object_name_chars`, 
            `illegal_macro_output_chars`, `use_regexp_matching`, `use_true_regexp_matching`, `admin_email`, `admin_pager`, `nagios_comment`, `nagios_activate`, 
            `event_broker_options`, `translate_passive_host_checks`, `enable_predictive_host_dependency_checks`, `enable_predictive_service_dependency_checks`, `passive_host_checks_are_soft`, 
            `use_large_installation_tweaks`, `free_child_process_memory`, `child_processes_fork_twice`, `enable_environment_macros`, `use_setpgid`, `enable_embedded_perl`, 
            `use_embedded_perl_implicitly`, `debug_file`, `debug_level`, `debug_level_opt`, `debug_verbosity`, `max_debug_file_size`, `daemon_dumps_core`, `cfg_file`, `use_check_result_path`) ";
            $rq .= "VALUES (";
            //$rq .= "'". $iId. "', '" . $aInstanceDefaultValues['log_file'] . "' , '" . $aInstanceDefaultValues['cfg_dir'] . "' , '" . $aInstanceDefaultValues['object_cache_file'] . "' , '" . $aInstanceDefaultValues['temp_file'] . "' , '" . $aInstanceDefaultValues['temp_path'] . "' , '" . $aInstanceDefaultValues['status_file'] . "' , '" . $aInstanceDefaultValues['check_result_path'] . "' , '" . $aInstanceDefaultValues['max_check_result_file_age'] . "' , '" . $aInstanceDefaultValues['p1_file'] . "' , '" . $aInstanceDefaultValues['status_update_interval'] . "' , '" . $aInstanceDefaultValues['nagios_user'] . "' , '" . $aInstanceDefaultValues['nagios_group'] . "' , '" . $aInstanceDefaultValues['enable_notifications'] . "' , '" . $aInstanceDefaultValues['execute_service_checks'] . "' , '" . $aInstanceDefaultValues['accept_passive_service_checks'] . "' , '" . $aInstanceDefaultValues['execute_host_checks'] . "' , '" . $aInstanceDefaultValues['accept_passive_host_checks'] . "' , '" . $aInstanceDefaultValues['enable_event_handlers'] . "' , '" . $aInstanceDefaultValues['log_rotation_method'] . "' , '" . $aInstanceDefaultValues['log_archive_path'] . "' , '" . $aInstanceDefaultValues['check_external_commands'] . "' , '" . $aInstanceDefaultValues['external_command_buffer_slots'] . "' , '" . $aInstanceDefaultValues['command_check_interval'] . "' , '" . $aInstanceDefaultValues['command_file'] . "' , '" . $aInstanceDefaultValues['downtime_file'] . "' , '" . $aInstanceDefaultValues['comment_file'] . "' , '" . $aInstanceDefaultValues['lock_file'] . "' , '" . $aInstanceDefaultValues['retain_state_information'] . "' , '" . $aInstanceDefaultValues['state_retention_file'] . "' , '" . $aInstanceDefaultValues['retention_update_interval'] . "' , '" . $aInstanceDefaultValues['use_retained_program_state'] . "' , '" . $aInstanceDefaultValues['use_retained_scheduling_info'] . "' , '" . $aInstanceDefaultValues['use_syslog'] . "' , '" . $aInstanceDefaultValues['log_notifications'] . "' , '" . $aInstanceDefaultValues['log_service_retries'] . "' , '" . $aInstanceDefaultValues['log_host_retries'] . "' , '" . $aInstanceDefaultValues['log_event_handlers'] . "' , '" . $aInstanceDefaultValues['log_initial_states'] . "' , '" . $aInstanceDefaultValues['log_external_commands'] . "' , '" . $aInstanceDefaultValues['log_passive_checks'] . "' , '" . $aInstanceDefaultValues['global_host_event_handler'] . "' , '" . $aInstanceDefaultValues['global_service_event_handler'] . "' , '" . $aInstanceDefaultValues['sleep_time'] . "' , '" . $aInstanceDefaultValues['service_inter_check_delay_method'] . "' , '" . $aInstanceDefaultValues['host_inter_check_delay_method'] . "' , '" . $aInstanceDefaultValues['service_interleave_factor'] . "' , '" . $aInstanceDefaultValues['max_concurrent_checks'] . "' , '" . $aInstanceDefaultValues['max_service_check_spread'] . "' , '" . $aInstanceDefaultValues['max_host_check_spread'] . "' , '" . $aInstanceDefaultValues['check_result_reaper_frequency'] . "' , '" . $aInstanceDefaultValues['interval_length'] . "' , '" . $aInstanceDefaultValues['auto_reschedule_checks'] . "' , '" . $aInstanceDefaultValues['auto_rescheduling_interval'] . "' , '" . $aInstanceDefaultValues['auto_rescheduling_window'] . "' , '" . $aInstanceDefaultValues['use_aggressive_host_checking'] . "' , '" . $aInstanceDefaultValues['enable_flap_detection'] . "' , '" . $aInstanceDefaultValues['low_service_flap_threshold'] . "' , '" . $aInstanceDefaultValues['high_service_flap_threshold'] . "' , '" . $aInstanceDefaultValues['low_host_flap_threshold'] . "' , '" . $aInstanceDefaultValues['high_host_flap_threshold'] . "' , '" . $aInstanceDefaultValues['soft_state_dependencies'] . "' , '" . $aInstanceDefaultValues['service_check_timeout'] . "' , '" . $aInstanceDefaultValues['host_check_timeout'] . "' , '" . $aInstanceDefaultValues['event_handler_timeout'] . "' , '" . $aInstanceDefaultValues['notification_timeout'] . "' , '" . $aInstanceDefaultValues['ocsp_timeout'] . "' , '" . $aInstanceDefaultValues['ochp_timeout'] . "' , '" . $aInstanceDefaultValues['perfdata_timeout'] . "' , '" . $aInstanceDefaultValues['obsess_over_services'] . "' , '" . $aInstanceDefaultValues['ocsp_command'] . "' , '" . $aInstanceDefaultValues['obsess_over_hosts'] . "' , '" . $aInstanceDefaultValues['ochp_command'] . "' , '" . $aInstanceDefaultValues['process_performance_data'] . "' , '" . $aInstanceDefaultValues['host_perfdata_command'] . "' , '" . $aInstanceDefaultValues['service_perfdata_command'] . "' , '" . $aInstanceDefaultValues['host_perfdata_file'] . "' , '" . $aInstanceDefaultValues['service_perfdata_file'] . "' , '" . $aInstanceDefaultValues['host_perfdata_file_template'] . "' , '" . $aInstanceDefaultValues['service_perfdata_file_template'] . "' , '" . $aInstanceDefaultValues['host_perfdata_file_mode'] . "' , '" . $aInstanceDefaultValues['service_perfdata_file_mode'] . "' , '" . $aInstanceDefaultValues['host_perfdata_file_processing_interval'] . "' , '" . $aInstanceDefaultValues['service_perfdata_file_processing_interval'] . "' , '" . $aInstanceDefaultValues['host_perfdata_file_processing_command'] . "' , '" . $aInstanceDefaultValues['service_perfdata_file_processing_command'] . "' , '" . $aInstanceDefaultValues['check_for_orphaned_services'] . "' , '" . $aInstanceDefaultValues['check_for_orphaned_hosts'] . "' , '" . $aInstanceDefaultValues['check_service_freshness'] . "' , '" . $aInstanceDefaultValues['service_freshness_check_interval'] . "' , '" . $aInstanceDefaultValues['freshness_check_interval'] . "' , '" . $aInstanceDefaultValues['check_host_freshness'] . "' , '" . $aInstanceDefaultValues['host_freshness_check_interval'] . "' , '" . $aInstanceDefaultValues['date_format'] . "' , '" . htmlentities($aInstanceDefaultValues['illegal_object_name_chars']) . "' , '" . htmlentities($aInstanceDefaultValues['illegal_macro_output_chars']) . "' , '" . $aInstanceDefaultValues['use_regexp_matching'] . "' , '" . $aInstanceDefaultValues['use_true_regexp_matching'] . "' , '" . $aInstanceDefaultValues['admin_email'] . "' , '" . $aInstanceDefaultValues['admin_pager'] . "' , '" . $aInstanceDefaultValues['nagios_comment'] . "' , '" . $aInstanceDefaultValues['nagios_activate'] . "' , '" . $aInstanceDefaultValues['event_broker_options'] . "' , '" . $aInstanceDefaultValues['translate_passive_host_checks'] . "' , '" . $aInstanceDefaultValues['enable_predictive_host_dependency_checks'] . "' , '" . $aInstanceDefaultValues['enable_predictive_service_dependency_checks'] . "' , '" . $aInstanceDefaultValues['cached_host_check_horizon'] . "' , '" . $aInstanceDefaultValues['cached_service_check_horizon'] . "' , '" . $aInstanceDefaultValues['passive_host_checks_are_soft'] . "' , '" . $aInstanceDefaultValues['use_large_installation_tweaks'] . "' , '" . $aInstanceDefaultValues['free_child_process_memory'] . "' , '" . $aInstanceDefaultValues['child_processes_fork_twice'] . "' , '" . $aInstanceDefaultValues['enable_environment_macros'] . "' , '" . $aInstanceDefaultValues['additional_freshness_latency'] . "' , '" . $aInstanceDefaultValues['enable_embedded_perl'] . "' , '" . $aInstanceDefaultValues['use_embedded_perl_implicitly'] . "' , '" . $aInstanceDefaultValues['debug_file'] . "' , '" . $aInstanceDefaultValues['debug_level'] . "' , '" . $aInstanceDefaultValues['debug_level_opt'] . "' , '" . $aInstanceDefaultValues['debug_verbosity'] . "' , '" . $aInstanceDefaultValues['max_debug_file_size'] . "' , '" . $aInstanceDefaultValues['cfg_file']."'";
            $rq .= "'". $iId. "', '".$aInstanceDefaultValues['log_file'] ."', '" .
            $aInstanceDefaultValues['cfg_dir'] ."', '" .
            $aInstanceDefaultValues['object_cache_file'] ."', '" .
            $aInstanceDefaultValues['temp_file'] ."', '" .
            $aInstanceDefaultValues['temp_path'] ."', '" .
            $aInstanceDefaultValues['status_file'] ."', '" .
            $aInstanceDefaultValues['p1_file'] ."', '" .
            $aInstanceDefaultValues['status_update_interval'] ."', '" .
            $aInstanceDefaultValues['nagios_user'] ."', '" .
            $aInstanceDefaultValues['nagios_group'] ."', '" .
            $aInstanceDefaultValues['enable_notifications'] ."', '" .
            $aInstanceDefaultValues['execute_service_checks'] ."', '" .
            $aInstanceDefaultValues['accept_passive_service_checks'] ."', '" .
            $aInstanceDefaultValues['execute_host_checks'] ."', '" .
            $aInstanceDefaultValues['accept_passive_host_checks'] ."', '" .
            $aInstanceDefaultValues['enable_event_handlers'] ."', '" .
            $aInstanceDefaultValues['log_rotation_method'] ."', '" .
            $aInstanceDefaultValues['log_archive_path'] ."', '" .
            $aInstanceDefaultValues['check_external_commands'] ."', '" .
            $aInstanceDefaultValues['external_command_buffer_slots'] ."', '" .
            $aInstanceDefaultValues['command_check_interval'] ."', '" .
            $aInstanceDefaultValues['command_file'] ."', '" .
            $aInstanceDefaultValues['lock_file'] ."', '" .
            $aInstanceDefaultValues['retain_state_information'] ."', '" .
            $aInstanceDefaultValues['state_retention_file' ] ."', '" .
            $aInstanceDefaultValues['retention_update_interval'] ."', '" .
            $aInstanceDefaultValues['use_retained_program_state'] ."', '" .
            $aInstanceDefaultValues['use_retained_scheduling_info'] ."', '" .
            $aInstanceDefaultValues['use_syslog'] ."', '" .
            $aInstanceDefaultValues['log_notifications'] ."', '" .
            $aInstanceDefaultValues['log_service_retries'] ."', '" .
            $aInstanceDefaultValues['log_host_retries'] ."', '" .
            $aInstanceDefaultValues['log_event_handlers'] ."', '" .
            $aInstanceDefaultValues['log_initial_states'] ."', '" .
            $aInstanceDefaultValues['log_external_commands'] ."', '" .
            $aInstanceDefaultValues['log_passive_checks'] ."', '" .
            $aInstanceDefaultValues['sleep_time'] ."', '" .
            $aInstanceDefaultValues['service_inter_check_delay_method'] ."', '" .
            $aInstanceDefaultValues['host_inter_check_delay_method'] ."', '" .
            $aInstanceDefaultValues['service_interleave_factor'] ."', '" .
            $aInstanceDefaultValues['max_concurrent_checks'] ."', '" .
            $aInstanceDefaultValues['max_service_check_spread'] ."', '" .
            $aInstanceDefaultValues['max_host_check_spread'] ."', '" .
            $aInstanceDefaultValues['check_result_reaper_frequency'] ."', '" .
            $aInstanceDefaultValues['max_check_result_reaper_time'] ."', '" .
            $aInstanceDefaultValues['interval_length'] ."', '" .
            $aInstanceDefaultValues['auto_reschedule_checks'] ."', '" .
            $aInstanceDefaultValues['use_aggressive_host_checking'] ."', '" .
            $aInstanceDefaultValues['enable_flap_detection'] ."', '" .
            $aInstanceDefaultValues['low_service_flap_threshold'] ."', '" .
            $aInstanceDefaultValues['high_service_flap_threshold'] ."', '" .
            $aInstanceDefaultValues['low_host_flap_threshold'] ."', '" .
            $aInstanceDefaultValues['high_host_flap_threshold'] ."', '" .
            $aInstanceDefaultValues['soft_state_dependencies'] ."', '" .
            $aInstanceDefaultValues['service_check_timeout'] ."', '" .
            $aInstanceDefaultValues['host_check_timeout'] ."', '" .
            $aInstanceDefaultValues['event_handler_timeout'] ."', '" .
            $aInstanceDefaultValues['notification_timeout'] ."', '" .
            $aInstanceDefaultValues['ocsp_timeout'] ."', '" .
            $aInstanceDefaultValues['ochp_timeout'] ."', '" .
            $aInstanceDefaultValues['perfdata_timeout'] ."', '" .
            $aInstanceDefaultValues['obsess_over_services'] ."', '" .
            $aInstanceDefaultValues['obsess_over_hosts'] ."', '" .
            $aInstanceDefaultValues['process_performance_data'] ."', '" .
            $aInstanceDefaultValues['host_perfdata_file_mode'] ."', '" .
            $aInstanceDefaultValues['service_perfdata_file_mode'] ."', '" .
            $aInstanceDefaultValues['check_for_orphaned_services'] ."', '" .
            $aInstanceDefaultValues['check_for_orphaned_hosts'] ."', '" .
            $aInstanceDefaultValues['check_service_freshness'] ."', '" .
            $aInstanceDefaultValues['check_host_freshness'] ."', '" .
            $aInstanceDefaultValues['date_format'] ."', '" .
            htmlentities($aInstanceDefaultValues['illegal_object_name_chars'], ENT_QUOTES, "UTF-8") ."', '" .
            htmlentities($aInstanceDefaultValues['illegal_macro_output_chars'], ENT_QUOTES, "UTF-8") ."', '" .
            $aInstanceDefaultValues['use_regexp_matching'] ."', '" .
            $aInstanceDefaultValues['use_true_regexp_matching'] ."', '" .
            $aInstanceDefaultValues['admin_email'] ."', '" .
            $aInstanceDefaultValues['admin_pager'] ."', '" .
            $aInstanceDefaultValues['nagios_comment'] ."', '" .
            $aInstanceDefaultValues['nagios_activate'] ."', '" .
            $aInstanceDefaultValues['event_broker_options'] ."', '" .
            $aInstanceDefaultValues['translate_passive_host_checks'] ."', '" .
            $aInstanceDefaultValues['enable_predictive_host_dependency_checks'] ."', '" .
            $aInstanceDefaultValues['enable_predictive_service_dependency_checks'] ."', '" .
            $aInstanceDefaultValues['passive_host_checks_are_soft'] ."', '" .
            $aInstanceDefaultValues['use_large_installation_tweaks'] ."', '" .
            $aInstanceDefaultValues['free_child_process_memory'] ."', '" .
            $aInstanceDefaultValues['child_processes_fork_twice'] ."', '" .
            $aInstanceDefaultValues['enable_environment_macros'] ."', '" .
            $aInstanceDefaultValues['use_setpgid'] ."', '" .
            $aInstanceDefaultValues['enable_embedded_perl'] ."', '" .
            $aInstanceDefaultValues['use_embedded_perl_implicitly'] ."', '" .
            $aInstanceDefaultValues['debug_file'] ."', '" .
            $aInstanceDefaultValues['debug_level'] ."', '" .
            $aInstanceDefaultValues['debug_level_opt'] ."', '" .
            $aInstanceDefaultValues['debug_verbosity'] ."', '" .
            $aInstanceDefaultValues['max_debug_file_size'] ."', '" .
            $aInstanceDefaultValues['daemon_dumps_core'] ."', '" .
            $aInstanceDefaultValues['cfg_file'] ."', '" .
            $aInstanceDefaultValues['use_check_result_path'] ."'";
            $rq .= ")";
            
            $res = $pearDB->query($rq);
            
            if (PEAR::isError($res)) {
                return false;
            }
        }
?>
