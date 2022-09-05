<?php

/*
 * Copyright 2005 - 2022 Centreon (https://www.centreon.com/)
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

require_once __DIR__ . '/../../class/centreonLog.class.php';
$centreonLog = new CentreonLog();

//error specific content
$versionOfTheUpgrade = 'UPGRADE - 22.04.5: ';
$errorMessage = '';

try {
    if ($pearDB->isColumnExist('remote_servers', 'server_id') === 0) {
        $errorMessage = "Unable to add 'server_id' column to remote_servers table";
        $pearDB->query(
            "ALTER TABLE remote_servers
            ADD COLUMN `server_id` int(11) NOT NULL"
        );

        migrateRemoteServerRelations($pearDB);

        $errorMessage = "Unable to add foreign key constraint of remote_servers.server_id";
        $pearDB->query(
            "ALTER TABLE remote_servers
            ADD CONSTRAINT `remote_server_nagios_server_ibfk_1`
            FOREIGN KEY(`server_id`) REFERENCES `nagios_server` (`id`)
            ON DELETE CASCADE"
        );
    }
} catch (\Exception $e) {
    $centreonLog->insertLog(
        4,
        $versionOfTheUpgrade . $errorMessage .
        " - Code : " . (int)$e->getCode() .
        " - Error : " . $e->getMessage() .
        " - Trace : " . $e->getTraceAsString()
    );

    throw new \Exception($versionOfTheUpgrade . $errorMessage, (int) $e->getCode(), $e);
}

/**
 * Manage relations between remote servers and nagios servers
 *
 * @param \CentreonDB $pearDB
 */
function migrateRemoteServerRelations(\CentreonDB $pearDB): void
{
    $processedIps = [];

    $selectServerStatement = $pearDB->prepare(
        "SELECT id FROM nagios_server WHERE ns_ip_address = :ip_address"
    );
    $deleteRemoteStatement = $pearDB->prepare(
        "DELETE FROM remote_servers WHERE id = :id"
    );
    $updateRemoteStatement = $pearDB->prepare(
        "UPDATE remote_servers SET server_id = :server_id WHERE id = :id"
    );

    $result = $pearDB->query(
        "SELECT id, ip FROM remote_servers"
    );
    while ($remote = $result->fetch()) {
        $remoteIp = $remote['ip'];
        $remoteId = $remote['id'];
        if (in_array($remoteIp, $processedIps)) {
            $deleteRemoteStatement->bindValue(':id', $remoteId, \PDO::PARAM_INT);
            $deleteRemoteStatement->execute();
        }

        $processedIps[] = $remoteIp;

        $selectServerStatement->bindValue(':ip_address', $remoteIp, \PDO::PARAM_STR);
        $selectServerStatement->execute();
        if ($server = $selectServerStatement->fetch()) {
            $updateRemoteStatement->bindValue(':server_id', $server['id'], \PDO::PARAM_INT);
            $updateRemoteStatement->bindValue(':id', $remoteId, \PDO::PARAM_INT);
            $updateRemoteStatement->execute();
        } else {
            $deleteRemoteStatement->bindValue(':id', $remoteId, \PDO::PARAM_INT);
            $deleteRemoteStatement->execute();
        }
    }
}
