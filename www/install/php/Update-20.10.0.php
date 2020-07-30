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
    // Create a new table used to get the platform topology and the relation between the servers
    $errorMessage = "Unable to create the new platform_topology table.";
    $pearDB->exec("
        CREATE TABLE `platform_topology` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `address` varchar(255) NOT NULL,
            `hostname` varchar(255) NOT NULL,
            `server_type` tinyint(1) NOT NULL DEFAULT 0,
            `parent_id` int(11) DEFAULT 0,
            `server_id` int(11),
            PRIMARY KEY (`id`),
            CONSTRAINT `platform_topology_ibfk_1` FOREIGN KEY (`server_id`)
            REFERENCES `nagios_server` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8
        COMMENT='Registration and parent relation Table used to set the platform topology'"
    );

    // Then insert the central as first platform and parent of all others
    $errorMessage = "Unable to insert the central in the platform_topology table.";
    $stmt = $pearDB->prepare(
        "INSERT INTO `platform_topology` (
            `address`,
            `hostname`,
            `server_type`,
            `parent_id`,
            `server_id`
        ) VALUES (
            :centralAddress,
            (SELECT `name` FROM nagios_server WHERE localhost = '1'),
            0,
            0,
            1
        )"
    );
    $stmt->bindValue(':centralAddress', $_SERVER['SERVER_ADDR'], \PDO::PARAM_STR);
    $stmt->execute();
} catch (Exception $e) {
    $centreonLog->insertLog(
        4,
        $versionOfTheUpgrade . $errorMessage .
        " - Code : " . (int)$e->getCode() .
        " - Error : " . $e->getMessage() .
        " - Trace : " . $e->getTraceAsString()
    );
}
