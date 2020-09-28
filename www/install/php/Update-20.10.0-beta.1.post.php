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
    // Check if the server is a Remote or a Central
    $type = 'central';
    $showPage = '0';
    $serverType = $pearDB->query("
        SELECT `value` FROM `informations`
        WHERE `key` = 'isRemote'
    ");
    if ('yes' === $serverType->fetch()['value']) {
        $type = 'remote';
        $showPage = '1';
    }
    // Check if the server is enabled
    $errorMessage = "Unable to find the server in 'nagios_server' table.";
    $serverQuery = $pearDB->query("
        SELECT `id`, `name` FROM nagios_server
        WHERE localhost = '1' AND ns_activate = '1'
    ");
    // Insert the server in 'platform_topology' table
    if ($row = $serverQuery->fetch()) {
        $errorMessage = "Unable to insert server in 'platform_topology' table.";
        $stmt = $pearDB->prepare("
            INSERT INTO `platform_topology` (`address`, `name`, `type`, `parent_id`, `server_id`)
            VALUES (:centralAddress, :name, :type, NULL, :id)
        ");
        $stmt->bindValue(':centralAddress', $_SERVER['SERVER_ADDR'], \PDO::PARAM_STR);
        $stmt->bindValue(':name', $row['name'], \PDO::PARAM_STR);
        $stmt->bindValue(':type', $type, \PDO::PARAM_STR);
        $stmt->bindValue(':id', (int)$row['id'], \PDO::PARAM_INT);
        $stmt->execute();
    }

    /**
     * activate remote access page in topology menu
     */
    // Create a new menu page related to remote. Hidden by default on a Central
    $errorMessage = "Unable to insert remote credential page in 'topology' table.";
    // This page is displayed only on remote platforms.
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

    // get topology local server id
    $localStmt = $pearDB->query(
        "SELECT `id` FROM `platform_topology` 
        WHERE `server_id` = (SELECT `id` FROM nagios_server WHERE localhost = '1')"
    );
    $parentId = $localStmt->fetchColumn();

    // get nagios_server children
    $childStmt = $pearDB->query(
        "SELECT `id`, `name`, `ns_ip_address`, `ns_ip_address`, `remote_id` FROM nagios_server WHERE localhost != '1'"
    );

    while ($row = $childStmt->fetch()) {
        //check remote/poller
        $parent = $parentId;
        $serverType = 'poller';
        if ($row['remote_id']) {
            $parent = $row['remote_id'];
        }

        $remoteServerQuery = $pearDB->query(
            "SELECT `id` FROM remote_servers WHERE ip = '" . $row['ns_ip_address'] . "'"
        );
        $remoteId = $remoteServerQuery->fetchColumn();
        if ($remoteId) {
            //is remote
            $serverType = 'remote';
        }

        $errorMessage = "Unable to insert " . $serverType . ":" . $row['name'] . " in 'topology' table.";
        $stmt = $pearDB->prepare(
            "INSERT INTO `platform_topology` (`address`, `name`, `type`, `parent_id`, `server_id`)
            VALUES (:centralAddress, :name, :serverType, :parent, :id)"
        );
        $stmt->bindValue(':centralAddress', $row['ns_ip_address'], \PDO::PARAM_STR);
        $stmt->bindValue(':name', $row['name'], \PDO::PARAM_STR);
        $stmt->bindValue(':serverType', $serverType, \PDO::PARAM_STR);
        $stmt->bindValue(':parent', (int) $parent, \PDO::PARAM_INT);
        $stmt->bindValue(':id', (int) $row['id'], \PDO::PARAM_INT);
        $stmt->execute();
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
