<?php
/*
 * Copyright 2015 Centreon (http://www.centreon.com/)
 * 
 * Centreon is a full-fledged industry-strength solution that meets 
 * the needs in IT infrastructure and application monitoring for 
 * service performance.
 * 
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * 
 *    http://www.apache.org/licenses/LICENSE-2.0  
 * 
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * 
 * For more information : contact@centreon.com
 * 
 */

	include_once("@CENTREON_ETC@/centreon.conf.php");

	require_once $centreon_path . "/www/class/centreonDB.class.php";
	require_once $centreon_path . "/www/class/centreonXML.class.php";

	/** ************************************
	 * start init db
	 */
	$pearDB = new CentreonDB();

	/** ************************************
	 * start XML Flow
	 */
	$buffer = new CentreonXML();
	$buffer->startElement("services");

	$empty = 0;
	if (isset($_POST["host_id"])){
		$traps = array();
		if ($_POST["host_id"] == -1) {
			$DBRESULT = $pearDB->query("
				SELECT service_id, service_description, host_name, host_id FROM (
					SELECT s.service_id, s.service_description, h.host_name, h.host_id 
					FROM service s, host h, host_service_relation hsr 
					WHERE 
						hsr.hostgroup_hg_id IS NULL AND 
						h.host_id = hsr.host_host_id AND 
						s.service_id = hsr.service_service_id AND
				                s.service_register = '1'
					UNION 
					SELECT s.service_id, s.service_description, h.host_name, h.host_id 
					FROM service s, hostgroup_relation hgr, host h, host_service_relation hsr 
					WHERE 
						hsr.hostgroup_hg_id = hgr.hostgroup_hg_id AND
						hgr.host_host_id = h.host_id AND
						s.service_id = hsr.service_service_id AND
						s.service_register = '1'
				) AS res
				ORDER BY res.host_name, res.service_description");
		} else if ($_POST["host_id"] == -2) {
			$empty = 1;
		} else if ($_POST["host_id"] != 0) {
			$DBRESULT = $pearDB->query("
				SELECT service_id, service_description, host_name, host_id FROM (
					SELECT s.service_id, s.service_description, h.host_name, h.host_id 
					FROM service s, host h, host_service_relation hsr 
					WHERE 
						hsr.hostgroup_hg_id IS NULL AND 
						h.host_id = '" . $pearDB->escape($_POST["host_id"]). "' AND 
						h.host_id = hsr.host_host_id AND 
						s.service_id = hsr.service_service_id AND
						s.service_register = '1'
					UNION 
					SELECT s.service_id, s.service_description, h.host_name, h.host_id 
					FROM service s, host h, host_service_relation hsr 
					WHERE 
						hsr.host_host_id IS NULL AND 
						hsr.hostgroup_hg_id IN (SELECT hostgroup_hg_id FROM hostgroup_relation WHERE host_host_id = '" . $pearDB->escape($_POST["host_id"]). "') AND 
						h.host_id = '" . $pearDB->escape($_POST["host_id"]). "' AND
						s.service_id = hsr.service_service_id AND
                                                s.service_register = '1'
				) AS res
				ORDER BY res.host_name, res.service_description");
		}

		if ($empty != 1) {
			while ($service = $DBRESULT->fetchRow()){
				$buffer->startElement("service");
				$buffer->writeElement("id", $service["host_id"] . "-" . $service['service_id']);
				$buffer->writeElement("name", $service["host_name"] . " - " . $service['service_description'], false);
				$buffer->endElement();
			}
			$DBRESULT->free();
		}
	} else {
		$buffer->writeElement("error", "host_id not found");
	}
	$buffer->endElement();
	header('Content-Type: text/xml');
	$buffer->output();
?>
