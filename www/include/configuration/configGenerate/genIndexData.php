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
 
define('DAY_SECS', 86400);
define('NB_REQUEST', 1000);

/* RRD retention cache */
$retentionCache = array();
$serviceRetention = array();
$retentionRes = $pearDB->query("SELECT host_host_id as id, MAX(hg_rrd_retention) as retention
                                FROM hostgroup, hostgroup_relation
                                WHERE hostgroup.hg_id = hostgroup_relation.hostgroup_hg_id
                                GROUP BY host_host_id");
while ($row = $retentionRes->fetchRow()) {
    if (!isset($retentionCache[$row['id']]) && $row['retention']) {
        $retentionCache[$row['id']] = $row['retention'] * DAY_SECS;
    }
}

/* Change index data info */
$indexToAdd = array();
$listIndexData = getListIndexData();

// Get all service into Centreon Configuration
$hostSvcSql = "SELECT host_id, service_id, host_name, service_description
FROM host_service_relation hsr, host h, service s
WHERE hsr.host_host_id IS NOT NULL
AND hsr.host_host_id = h.host_id
AND hsr.service_service_id = s.service_id
AND s.service_register IN ('1', '2')
UNION
SELECT host_id, service_id, host_name, service_description
FROM host_service_relation hsr, hostgroup_relation hgr, host h, service s
WHERE hsr.hostgroup_hg_id = hgr.hostgroup_hg_id
AND hgr.host_host_id = h.host_id
AND hsr.service_service_id = s.service_id
AND s.service_register = '1'";
$hostSvcRes = $pearDB->query($hostSvcSql);
$hostSvc = array();
while ($hostSvcRow = $hostSvcRes->fetchRow()) {
    
    $relation = $hostSvcRow['host_id'].';'.$hostSvcRow['service_id'];
    $hostSvc[$hostSvcRow['host_id']][$hostSvcRow['service_id']] = true;
    if (!isset($listIndexData[$relation])) {
        $indexToAdd[] = array('host_id' => $hostSvcRow['host_id'],
                              'service_id' => $hostSvcRow['service_id'],
                              'host_name' => $hostSvcRow['host_name'],
                              'service_description' => $hostSvcRow['service_description']);
    }

    $host_id = $hostSvcRow['host_id'];
    if (isset($retentionCache[$host_id])) {
        $serviceRetention[$retentionCache[$host_id]][] = $host_id.';'.$hostSvcRow['service_id'];
    }
}

// Select service to delete into index_data
$res = $pearDBO->query("SELECT host_id, service_id FROM index_data");
$toDelete = "";
$i = 0;
$sql = "UPDATE index_data SET to_delete = 1 "
     . "WHERE CONCAT(host_id,';',service_id) IN (%s)";
while ($row = $res->fetchRow()) {
    $hid = $row['host_id'];
    $sid = $row['service_id'];
    if (!isset($hostSvc[$hid]) || !isset($hostSvc[$hid][$sid])) {
        $toDelete .= "'$hid;$sid',";
    }
    $i++;
    if (($i % NB_REQUEST) == 0) {
        if ($toDelete != "") {
            $pearDBO->query(sprintf($sql, substr($toDelete, 0, (strlen($toDelete)-1))));
            $toDelete = "";
        }
    }
}
if ($toDelete != "") {
    $pearDBO->query(sprintf($sql, substr($toDelete, 0, (strlen($toDelete)-1))));
}
unset($hostSvc);


//
$queryAddIndex = "INSERT INTO index_data (host_id, host_name, service_id, service_description, to_delete) VALUES ";
$queryAddIndexValues = "(%d, '%s', %d, '%s', 0),";
$valuesToAdd = "";
$i = 0;

if ($toDelete != "") {
    $pearDBO->query(sprintf($sql, substr($toDelete, 0, (strlen($toDelete)-1))));
}
unset($hostSvc);


// 
$queryAddIndex = "INSERT INTO index_data (host_id, host_name, service_id, service_description, to_delete) VALUES ";
$queryAddIndexValues = "(%d, '%s', %d, '%s', 0),";
$valuesToAdd = "";
$i = 0;
foreach ($indexToAdd as $index) {
    $valuesToAdd .= sprintf($queryAddIndexValues, $index['host_id'], $index['host_name'], $index['service_id'], $index['service_description']);
    $i++;
    if (($i % NB_REQUEST) == 0) {
        if ($valuesToAdd != "") {
            $pearDBO->query($queryAddIndex.substr($valuesToAdd, 0, (strlen($valuesToAdd)-1)));
            $valuesToAdd = "";
        }
    }
}

if ($valuesToAdd != "") {
    $pearDBO->query($queryAddIndex.substr($valuesToAdd, 0, (strlen($valuesToAdd)-1)));
}


$pearDBO->query('UPDATE index_data SET rrd_retention = NULL');
foreach ($serviceRetention as $retentionValue => $retention) {
    $combostr = "";
    foreach ($retention as $hostServiceCombo) {
        if ($combostr != "") {
            $combostr .= ", ";
        }
        $combostr .= "'".$pearDBO->escape($hostServiceCombo)."'";
    }
    if ($combostr != "") {
        $pearDBO->query('UPDATE index_data SET rrd_retention = '.$pearDBO->escape($retentionValue).' WHERE CONCAT(host_id, ";", service_id) IN ('.$combostr.')');
    }
}
