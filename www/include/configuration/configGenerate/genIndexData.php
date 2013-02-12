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
 * SVN : $URL:$
 * SVN : $Id:$
 *
 */

/* Change index data info */
$indexToAdd = array();
$listIndexData = getListIndexData();

$hostSvcSql = "SELECT host_id, service_id, host_name, service_description
FROM host_service_relation hsr, host h, service s
WHERE hsr.host_host_id IS NOT NULL
AND hsr.host_host_id = h.host_id
AND h.host_activate = '1'
AND hsr.service_service_id = s.service_id
UNION
SELECT host_id, service_id, host_name, service_description
FROM host_service_relation hsr, hostgroup_relation hgr, host h, service s
WHERE hsr.hostgroup_hg_id = hgr.hostgroup_hg_id
AND hgr.host_host_id = h.host_id
AND h.host_activate = '1'
AND hsr.service_service_id = s.service_id";
$hostSvcRes = $pearDB->query($hostSvcSql);
$hostSvc = "";
while ($hostSvcRow = $hostSvcRes->fetchRow()) {
    if ($hostSvc != "") {
        $hostSvc .= ",";
    }
    $relation = $hostSvcRow['host_id'].';'.$hostSvcRow['service_id'];
    $hostSvc .= "'".$relation."'";
    if (!isset($listIndexData[$relation])) {
        $indexToAdd[] = array('host_id' => $hostSvcRow['host_id'],
                              'service_id' => $hostSvcRow['service_id'],
                              'host_name' => $hostSvcRow['host_name'],
                              'service_description' => $hostSvcRow['service_description']);
    }
}
if ($hostSvc != "") {
    $pearDBO->query("UPDATE index_data
            SET to_delete = 1
            WHERE CONCAT(host_id, ';', service_id) NOT IN (".$hostSvc.")");
}

$queryAddIndex = "INSERT INTO index_data (host_id, host_name, service_id, service_description, to_delete)
VALUES (%d, '%s', %d, '%s', 0)";
foreach ($indexToAdd as $index) {
    $queryAddIndexToExec = sprintf($queryAddIndex, $index['host_id'], $index['host_name'], $index['service_id'], $index['service_description']);
    $pearDBO->query($queryAddIndexToExec);
}
