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

require_once "@CENTREON_ETC@/centreon.conf.php";

/*
 * Include Classes
 */
require_once $centreon_path . "www/class/centreon.class.php";
require_once $centreon_path . "www/class/centreonUser.class.php";
require_once $centreon_path . "www/class/centreonSession.class.php";
require_once $centreon_path . "www/class/centreonXML.class.php";
require_once $centreon_path . "www/class/centreonDB.class.php";
require_once $centreon_path . "www/class/centreonACL.class.php";
require_once $centreon_path . "www/class/centreonBroker.class.php";

session_start();

/*
 * Check Sessions
 */
if (!isset($_SESSION['centreon']) || !isset($_POST['host_id'])) {
    exit;
}

/*
 * Get Params
 */
$centreon = $_SESSION['centreon'];
$acl = $centreon->user->access;
$hostId = $_POST['host_id'];

/*
 * Init DB Object
 */
$db = new CentreonDB();
$pearDB = $db;

$aclFrom = "";
$aclCond = "";
if (!$centreon->user->admin) {
    $dbmon = $acl->getNameDBAcl($centreon->broker->getBroker());
    $aclFrom = ", $dbmon.centreon_acl acl ";
    $aclCond = " WHERE res.host_id = acl.host_id 
                 AND acl.service_id = res.service_id 
                 AND acl.group_id IN (".$acl->getAccessGroupsString().") ";
}

/*
 * Start XML
 */
$xml = new CentreonXML();
$xml->startElement("response");

if (isset($hostId)) {
	if ($hostId == 0) {
		$query = "SELECT DISTINCT res.service_id, res.service_description, res.host_name, res.host_id FROM (
					SELECT s.service_id, s.service_description, h.host_name, h.host_id 
					FROM service s, host h, host_service_relation hsr 
					WHERE hsr.hostgroup_hg_id IS NULL 
                    AND h.host_id = hsr.host_host_id 
					AND s.service_id = hsr.service_service_id 
					AND s.service_register = '1'
					UNION 
					SELECT s.service_id, s.service_description, h.host_name, h.host_id 
					FROM service s, hostgroup_relation hgr, host h, host_service_relation hsr 
					WHERE hsr.hostgroup_hg_id = hgr.hostgroup_hg_id 
                    AND hgr.host_host_id = h.host_id 
					AND s.service_id = hsr.service_service_id 
					AND s.service_register = '1'
				) AS res $aclFrom $aclCond
				ORDER BY res.host_name, res.service_description";
	} else {
		$query = "SELECT DISTINCT res.service_id, res.service_description, res.host_name, res.host_id FROM (
					SELECT s.service_id, s.service_description, h.host_name, h.host_id 
					FROM service s, host h, host_service_relation hsr 
					WHERE hsr.hostgroup_hg_id IS NULL 
                                        AND h.host_id = '" . $db->escape($hostId). "' 
                                        AND h.host_id = hsr.host_host_id 
                                        AND s.service_id = hsr.service_service_id 
                                        AND s.service_register = '1' 
					UNION 
					SELECT s.service_id, s.service_description, h.host_name, h.host_id 
					FROM service s, host h, host_service_relation hsr 
					WHERE hsr.host_host_id IS NULL 
                                        AND hsr.hostgroup_hg_id IN (SELECT hostgroup_hg_id 
                                                                    FROM hostgroup_relation 
                                                                    WHERE host_host_id = '" . $db->escape($hostId). "') 
                                        AND h.host_id = '" . $db->escape($hostId). "' 
                                        AND s.service_id = hsr.service_service_id 
                                        AND s.service_register = '1' 
                                ) AS res $aclFrom $aclCond
				ORDER BY res.host_name, res.service_description";
	}
	$res = $db->query($query);
	while ($row = $res->fetchRow()) {
		$xml->startElement("services");
		$xml->writeElement("id", $row['host_id']."_".$row['service_id']);
		$xml->writeElement("description", $row['host_name'] . " - " . $row['service_description']);
		$xml->endElement();
	}
}
$xml->endElement();

header('Content-Type: text/xml');
header('Pragma: no-cache');
header('Expires: 0');
header('Cache-Control: no-cache, must-revalidate');

$xml->output();

?>
