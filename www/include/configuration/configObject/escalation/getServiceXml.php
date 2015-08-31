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
require_once $centreon_path . "www/class/centreon.class.php";
require_once $centreon_path . "www/class/centreonSession.class.php";
require_once $centreon_path . "www/class/centreonXML.class.php";
require_once $centreon_path . "www/class/centreonDB.class.php";
require_once $centreon_path . "www/class/centreonUser.class.php";
require_once $centreon_path . "www/class/centreonACL.class.php";

session_start();

if (!isset($_SESSION['centreon']) || !isset($_POST['host_id'])) {
    exit;
}
$centreon = $_SESSION['centreon'];
$db = new CentreonDB();
$pearDB = $db; // global var
$hostId = $_POST['host_id'];
$acl = $centreon->user->access;
$xml = new CentreonXML();
$xml->startElement("response");
if ($hostId != "") {
    $aclFrom = "";
    if (!$centreon->user->admin) {
        $aclDbName = $acl->getNameDBAcl($centreon->broker->getBroker());
        $aclFrom = ", $aclDbName.centreon_acl acl ";
    }
    $query = "SELECT DISTINCT h.host_id, h.host_name, s.service_id, s.service_description
    		  FROM service s, host_service_relation hsr, host h $aclFrom
    		  WHERE s.service_id = hsr.service_service_id
    		  AND hsr.host_host_id = h.host_id ";
    if ($hostId) {
        $query .= " AND h.host_id = " . $db->escape($hostId);
    }
    $query .= " AND s.service_register = '1' ";
    if (!$centreon->user->admin) {
        $query .= " AND h.host_id = acl.host_id
                    AND acl.service_id = s.service_id
                    AND acl.group_id IN (".$acl->getAccessGroupsString().") ";
    }
    /* service by host group */
    $query .= " UNION ";
    $query .= "SELECT DISTINCT h.host_id, h.host_name, s.service_id, s.service_description
        FROM service s, host_service_relation hsr, hostgroup_relation hgr, host h $aclFrom
        WHERE s.service_id = hsr.service_service_id
        AND hsr.hostgroup_hg_id = hgr.hostgroup_hg_id
        AND hgr.host_host_id = h.host_id ";
    if ($hostId) {
        $query .= " AND h.host_id = " . $db->escape($hostId);
    }
    $query .= " AND s.service_register = '1' ";
    if (!$centreon->user->admin) {
        $query .= " AND h.host_id = acl.host_id
                    AND acl.service_id = s.service_id
                    AND acl.group_id IN (".$acl->getAccessGroupsString().") ";
    }

    $query .= " ORDER BY host_name, service_description ";
    $res = $db->query($query);
    while ($row = $res->fetchRow()) {
        $xml->startElement("services");
        $xml->writeElement("id", $row['host_id']."-".$row['service_id']);
        $xml->writeElement("description", sprintf("%s - %s", $row['host_name'], $row['service_description']));
        $xml->endElement();
    }
}
$xml->endElement();
header('Content-Type: text/xml');
header('Pragma: no-cache');
header('Expires: 0');
header('Cache-Control: no-cache, must-revalidate');
$xml->output();
