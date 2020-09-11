<?php

/*
 * Copyright 2005 - 2020 Centreon (https://www.centreon.com/)
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
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

include_once __DIR__ . "/../../class/centreonLog.class.php";
$centreonLog = new CentreonLog();

//error specific content
$versionOfTheUpgrade = 'UPGRADE - 20.10.0 : ';

/**
 * Queries which don't need rollback and won't throw an exception
 */
try {
    $errorMessage = "Unable to update acl of resource status page.";

    $topologyAclQuery = $pearDB->query(
        "SELECT DISTINCT(tr1.acl_topo_id)
        FROM acl_topology_relations tr1
        WHERE tr1.acl_topo_id NOT IN (
            SELECT tr2.acl_topo_id
            FROM acl_topology_relations tr2, topology t2
            WHERE tr2.topology_topology_id = t2.topology_id
            AND t2.topology_page = 200
        )
        AND tr1.acl_topo_id IN (
            SELECT tr3.acl_topo_id
            FROM acl_topology_relations tr3, topology t3
            WHERE tr3.topology_topology_id = t3.topology_id
            AND t3.topology_page IN (20201, 20202)
        )"
    );

    $resourceStatusQuery = $pearDB->query(
        "SELECT topology_id FROM topology WHERE topology_page = 200"
    );
    if ($resourceStatusPage = $resourceStatusQuery->fetch()) {
        $stmt = $pearDB->prepare("
            INSERT INTO `acl_topology_relations` (
                `topology_topology_id`,
                `acl_topo_id`,
                `access_right`
            ) VALUES (
                :topology_id,
                :acl_topology_id,
                1
            )
        ");

        while ($row = $topologyAclQuery->fetch()) {
            $stmt->bindValue(':topology_id', $resourceStatusPage['topology_id'], \PDO::PARAM_INT);
            $stmt->bindValue(':acl_topology_id', $row['acl_topo_id'], \PDO::PARAM_INT);
            $stmt->execute();
        }
    }
} catch (Exception $e) {
    $centreonLog->insertLog(
        4,
        $versionOfTheUpgrade . $errorMessage .
        " - Code : " . (int)$e->getCode() .
        " - Error : " . $e->getMessage() .
        " - Trace : " . $e->getTraceAsString()
    );
}
