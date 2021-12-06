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

//error specific content
$versionOfTheUpgrade = 'UPGRADE - 20.10.0-beta.2.post : ';

/**
 * Queries needing exception management and rollback if failing
 */
try {
    $pearDB->beginTransaction();
    // Remove data inserted in 20.10.0-beta1
    $pearDB->query("DELETE FROM `platform_topology`");

    /**
     * register server to 'platform_status' table
     */
    // Check if the server is a Remote or a Central
    $type = 'central';
    $serverType = $pearDB->query("
        SELECT `value` FROM `informations`
        WHERE `key` = 'isRemote'
    ");
    if ('yes' === $serverType->fetch()['value']) {
        $type = 'remote';
    }
    // Check if the server is enabled
    $errorMessage = "Unable to find the server in 'nagios_server' table.";
    $serverQuery = $pearDB->query("
        SELECT `id`, `name` FROM nagios_server
        WHERE localhost = '1' AND ns_activate = '1'
    ");

    $hostName = gethostname() ?: null;

    // Insert the server in 'platform_topology' table
    if ($row = $serverQuery->fetch()) {
        $errorMessage = "Unable to insert server in 'platform_topology' table.";
        $stmt = $pearDB->prepare("
            INSERT INTO `platform_topology` (`address`, `name`, `hostname`, `type`, `parent_id`, `server_id`)
            VALUES (:centralAddress, :name, :hostname, :type, NULL, :id)
        ");
        $stmt->bindValue(':centralAddress', $_SERVER['SERVER_ADDR'], \PDO::PARAM_STR);
        $stmt->bindValue(':name', $row['name'], \PDO::PARAM_STR);
        $stmt->bindValue(':hostname', $hostName, \PDO::PARAM_STR);
        $stmt->bindValue(':type', $type, \PDO::PARAM_STR);
        $stmt->bindValue(':id', (int)$row['id'], \PDO::PARAM_INT);
        $stmt->execute();
    }

    // get topology local server id
    $localStmt = $pearDB->query("
        SELECT `platform_topology`.`id` FROM `platform_topology`
        INNER JOIN nagios_server
        ON `platform_topology`.`server_id` = `nagios_server`.`id`
        WHERE `nagios_server`.`localhost` = '1'
    ");
    $parentId = $localStmt->fetchColumn();

    // get nagios_server children
    $childStmt = $pearDB->query(
        "SELECT `id`, `name`, `ns_ip_address`, `remote_id`
        FROM nagios_server WHERE localhost != '1' ORDER BY `remote_id`"
    );
    while ($row = $childStmt->fetch()) {
        //check for remote or poller child types
        $remoteServerQuery = $pearDB->prepare(
            "SELECT ns.id FROM nagios_server ns
            INNER JOIN remote_servers rs ON rs.ip = ns.ns_ip_address WHERE ip = :ipAddress"
        );
        $remoteServerQuery->bindValue(':ipAddress', $row['ns_ip_address'], \PDO::PARAM_STR);
        $remoteServerQuery->execute();
        $remoteId = $remoteServerQuery->fetchColumn();
        if (!empty($remoteId)) {
            //is remote
            $serverType = 'remote';
            $parent = $parentId;
        } else {
            $serverType = 'poller';
            $findParent = $pearDB->prepare('
                SELECT id from platform_topology WHERE `server_id` = :remoteId
            ');
            $findParent->bindValue(':remoteId', (int) $row['remote_id'], \PDO::PARAM_INT);
            $findParent->execute();
            $parent = $findParent->fetchColumn();
            if ($parent === false) {
                continue;
            }
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
    include_once __DIR__ . "/../../class/centreonLog.class.php";
    $centreonLog = new CentreonLog();
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
