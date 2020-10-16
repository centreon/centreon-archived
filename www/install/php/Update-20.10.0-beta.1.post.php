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
$versionOfTheUpgrade = 'UPGRADE - 20.10.0-beta.1.post : ';

/**
 * Queries needing exception management and rollback if failing
 */
try {
    $pearDB->beginTransaction();
    /**
     * register server to 'platform_status' table
     */
    // Correct 'isCentral' flag value
    $errorMessage = "Unable to get server data from the 'informations' table.";
    $result = $pearDB->query("
        SELECT count(*) as `count` FROM `informations`
        WHERE (`key` = 'isRemote' AND `value` = 'no') OR (`key` = 'isCentral' AND `value` = 'no')
    ");
    $row = $result->fetch();
    if (2 === (int)$row['count']) {
        $errorMessage = "Unable to modify isCentral flag value in 'informations' table.";
        $stmt = $pearDB->query("UPDATE `informations` SET `value` = 'yes' WHERE `key` = 'isCentral'");
    }
    /**
     * activate remote access page in topology menu
     */
    $showPage = '0';
    $serverType = $pearDB->query("
        SELECT `value` FROM `informations`
        WHERE `key` = 'isRemote'
    ");
    if ('yes' === $serverType->fetch()['value']) {
        $showPage = '1';
    }
    // Create a new menu page related to remote. Hidden by default on a Central
    // This page is displayed only on remote platforms.
    $errorMessage = "Unable to insert 'Remote access' page in 'topology' table.";
    $stmt = $pearDB->query("
        INSERT INTO `topology` (
            `topology_name`, `topology_parent`, `topology_page`, `topology_order`, `topology_group`,
            `topology_url`, `topology_url_opt`,
            `topology_popup`, `topology_modules`, `topology_show`,
            `topology_style_class`, `topology_style_id`, `topology_OnClick`, `readonly`
        ) VALUES (
            'Remote access', 501, 50120, 25, 1,
            './include/Administration/parameters/parameters.php', '&o=remote',
            '0', '0', '" . $showPage . "',
            NULL, NULL, NULL, '1'
        )
    ");

    // migrate resource status menu acl
    $errorMessage = "Unable to update acl of resource status page.";

    $resourceStatusQuery = $pearDB->query(
        "SELECT topology_id, topology_page FROM topology WHERE topology_page IN (2, 200)"
    );

    $topologyAclStatement = $pearDB->prepare(
        "SELECT DISTINCT(tr1.acl_topo_id)
        FROM acl_topology_relations tr1
        WHERE tr1.acl_topo_id NOT IN (
            SELECT tr2.acl_topo_id
            FROM acl_topology_relations tr2, topology t2
            WHERE tr2.topology_topology_id = t2.topology_id
            AND t2.topology_page = :topology_page
        )
        AND tr1.acl_topo_id IN (
            SELECT tr3.acl_topo_id
            FROM acl_topology_relations tr3, topology t3
            WHERE tr3.topology_topology_id = t3.topology_id
            AND t3.topology_page IN (20201, 20202)
        )"
    );

    $topologyInsertStatement = $pearDB->prepare("
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

    while ($resourceStatusPage = $resourceStatusQuery->fetch()) {
        $topologyAclStatement->bindValue(':topology_page', (int) $resourceStatusPage['topology_page'], \PDO::PARAM_INT);
        $topologyAclStatement->execute();

        while ($row = $topologyAclStatement->fetch()) {
            $topologyInsertStatement->bindValue(':topology_id', (int) $resourceStatusPage['topology_id'], \PDO::PARAM_INT);
            $topologyInsertStatement->bindValue(':acl_topology_id', (int) $row['acl_topo_id'], \PDO::PARAM_INT);
            $topologyInsertStatement->execute();
        }
    }

    $monitoringTopologyStatement = $pearDB->query(
        "SELECT DISTINCT(tr1.acl_topo_id)
        FROM acl_topology_relations tr1
        WHERE tr1.acl_topo_id NOT IN (
            SELECT tr2.acl_topo_id
            FROM acl_topology_relations tr2, topology t2
            WHERE tr2.topology_topology_id = t2.topology_id
            AND t2.topology_page = 2
        )
        AND tr1.acl_topo_id IN (
            SELECT tr3.acl_topo_id
            FROM acl_topology_relations tr3, topology t3
            WHERE tr3.topology_topology_id = t3.topology_id
            AND t3.topology_page = 200
        )"
    );

    $monitoringPageQuery = $pearDB->query(
        "SELECT topology_id FROM topology WHERE topology_page = 2"
    );
    $monitoringPage = $monitoringPageQuery->fetch();

    while ($topology = $monitoringTopologyStatement->fetch()) {
        if ($monitoringPage !== false) {
            $topologyInsertStatement->bindValue(':topology_id', (int) $monitoringPage['topology_id'], \PDO::PARAM_INT);
            $topologyInsertStatement->bindValue(':acl_topology_id', (int) $topology['acl_topo_id'], \PDO::PARAM_INT);
            $topologyInsertStatement->execute();
        }
    }

    $pearDB->commit();
    $errorMessage = "";
} catch (\Exception $e) {
    $pearDB->rollBack();
    $centreonLog->insertLog(
        4,
        $versionOfTheUpgrade . $errorMessage .
        " - Code : " . (int)$e->getCode() .
        " - Error : " . $e->getMessage() .
        " - Trace : " . $e->getTraceAsString()
    );
    throw new \Exception($versionOfTheUpgrade . $errorMessage, (int)$e->getCode(), $e);
}
