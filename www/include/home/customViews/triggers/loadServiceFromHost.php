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
require_once $centreon_path . "www/class/centreonDB.class.php";
require_once $centreon_path . "www/class/centreonSession.class.php";
require_once $centreon_path . "www/class/centreonUser.class.php";
require_once $centreon_path . "www/class/centreonXML.class.php";
require_once $centreon_path . "www/class/centreonBroker.class.php";

session_start();

if (!isset($_POST['data']) || !isset($_SESSION['centreon'])) {
    exit;
}

$centreon = $_SESSION['centreon'];
$data = $_POST['data'];
$db = new CentreonDB();
$pearDB = $db;
if (CentreonSession::checkSession(session_id(), $db) == 0) {
    exit;
}
$brk = new CentreonBroker($db);
if ($brk->getBroker() == 'broker') {
    $monitoringDb = new CentreonDB('centstorage');
} else {
    $monitoringDb = new CentreonDB('ndo');
}

$xml = new CentreonXML();

$xml->startElement('response');
try {
    $xml->startElement('options');
    if ($data) {
        $aclString = $centreon->user->access->queryBuilder('AND', 
                                                      's.service_id',
                                                      $centreon->user->access->getServicesString('ID', $monitoringDb));
        $sql = "SELECT service_id, service_description
        		FROM service s, host_service_relation hsr
        		WHERE hsr.host_host_id = " . $db->escape($data) . "
        		AND hsr.service_service_id = s.service_id ";
        $sql .= $aclString;
        $sql .= " UNION ";
        $sql .= " SELECT service_id, service_description
        		FROM service s, host_service_relation hsr, hostgroup_relation hgr
        		WHERE hsr.hostgroup_hg_id = hgr.hostgroup_hg_id
        		AND hgr.host_host_id = ".$db->escape($data)."
        		AND hsr.service_service_id = s.service_id ";
        $sql .= $aclString;
        $sql .= " ORDER BY service_description ";
        $res = $db->query($sql);
        while ($row = $res->fetchRow()) {
            $xml->startElement('option');
            $xml->writeElement('id', $row['service_id']);
            $xml->writeElement('label', $row['service_description']);
            $xml->endElement();
        }
    }
    $xml->endElement();
} catch (CentreonCustomViewException $e) {
    $xml->writeElement('error', $e->getMessage());
} catch (CentreonWidgetException $e) {
    $xml->writeElement('error', $e->getMessage());
}
$xml->endElement();
header('Content-Type: text/xml');
header('Pragma: no-cache');
header('Expires: 0');
header('Cache-Control: no-cache, must-revalidate');
$xml->output();
?>