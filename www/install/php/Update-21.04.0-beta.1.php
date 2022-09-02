<?php

/*
 * Copyright 2005 - 2021 Centreon (https://www.centreon.com/)
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
$versionOfTheUpgrade = 'UPGRADE - 21.04.0-beta.1: ';

$criteriasConcordanceArray = [
    'states' => [
        'acknowledged' => 'Acknowledged',
        'in_downtime' => 'In downtime',
        'unhandled_problems' => 'Unhandled',
    ],
    'resource_types' => [
        'host' => 'Host',
        'service' => 'Service',
    ],
    'statuses' => [
        'OK' => 'Ok',
        'UP' => 'Up',
        'WARNING' => 'Warning',
        'DOWN' => 'Down',
        'CRITICAL' => 'Critical',
        'UNREACHABLE' => 'Unreachable',
        'UNKNOWN' => 'Unknown',
        'PENDING' => 'Pending'
    ],
];

/**
 * Query without transaction
 */
try {
    if (!$pearDB->isColumnExist('cfg_centreonbroker', 'log_directory')) {
        // An update is required
        $errorMessage = 'Impossible to alter the table cfg_centreonbroker with log_directory';
        $pearDB->query(
            'ALTER TABLE `cfg_centreonbroker` ADD COLUMN `log_directory` VARCHAR(255) AFTER `config_write_thread_id`'
        );
    }
    if (!$pearDB->isColumnExist('cfg_centreonbroker', 'log_filename')) {
        // An update is required
        $errorMessage = 'Impossible to alter the table cfg_centreonbroker with log_filename';
        $pearDB->query(
            'ALTER TABLE `cfg_centreonbroker` ADD COLUMN `log_filename` VARCHAR(255) AFTER `log_directory`'
        );
    }
    if (!$pearDB->isColumnExist('cfg_centreonbroker', 'log_max_size')) {
        // An update is required
        $errorMessage = 'Impossible to alter the table cfg_centreonbroker with log_max_size';
        $pearDB->query(
            'ALTER TABLE `cfg_centreonbroker` ADD COLUMN `log_max_size` INT(11) NOT NULL DEFAULT 0 AFTER `log_filename`'
        );
    }

    $errorMessage = 'Impossible to create the table cb_log';
    $pearDB->query(
        'CREATE TABLE IF NOT EXISTS `cb_log`
        (`id` INT PRIMARY KEY NOT NULL AUTO_INCREMENT,`name` varchar(255) NOT NULL)'
    );

    $errorMessage = 'Impossible to create the table cb_log_level';
    $pearDB->query(
        'CREATE TABLE IF NOT EXISTS `cb_log_level`
        (`id` INT PRIMARY KEY NOT NULL AUTO_INCREMENT,`name` varchar(255) NOT NULL)'
    );

    $errorMessage = 'Impossible to create the table cfg_centreonbroker_log';
    $pearDB->query(
        'CREATE TABLE IF NOT EXISTS `cfg_centreonbroker_log`
        (`id` INT PRIMARY KEY NOT NULL AUTO_INCREMENT,
        `id_centreonbroker` INT(11)  NOT NULL,
        `id_log` INT(11)  NOT NULL,
        `id_level` INT(11)  NOT NULL)'
    );

    if ($pearDB->isColumnExist('cfg_nagios', 'use_aggressive_host_checking')) {
        // An update is required
        $errorMessage = 'Impossible to drop column use_aggressive_host_checking from cfg_nagios';
        $pearDB->query('ALTER TABLE `cfg_nagios` DROP COLUMN `use_aggressive_host_checking`');
    }

    $errorMessage = "";
} catch (\Exception $e) {
    $centreonLog->insertLog(
        4,
        $versionOfTheUpgrade . $errorMessage .
        " - Code : " . (int)$e->getCode() .
        " - Error : " . $e->getMessage() .
        " - Trace : " . $e->getTraceAsString()
    );
    throw new \Exception($versionOfTheUpgrade . $errorMessage, (int)$e->getCode(), $e);
}

/**
 * Query with transaction
 */
try {
    $pearDB->beginTransaction();
    /**
     * Retrieve user filters
     */
    $statement = $pearDB->query(
        "SELECT `id`, `criterias` FROM `user_filter`"
    );

    $translatedFilters = [];

    while ($filter = $statement->fetch()) {
        $id = $filter['id'];
        $decodedCriterias = json_decode($filter['criterias'], true);
        // Adding the default sorting in the criterias
        foreach ($decodedCriterias as $criteriaKey => $criteria) {
            $name = $criteria['name'];
            // Checking if the filter contains criterias we want to migrate
            if (array_key_exists($name, $criteriasConcordanceArray) === true) {
                foreach ($criteria['value'] as $index => $value) {
                    $decodedCriterias[$criteriaKey]['value'][$index]['name'] =
                        $criteriasConcordanceArray[$name][$value['id']];
                }
            }
        }

        $decodedCriterias[] = [
            'name' => 'sort',
            'type' => 'array',
            'value' => [
                'status_severity_code' => "asc"
            ],
        ];

        $translatedFilters[$id] = json_encode($decodedCriterias);
    }

    /**
     * UPDATE SQL request on the filters
     */

    foreach ($translatedFilters as $id => $criterias) {
        $errorMessage = "Unable to update filter values in user_filter table.";
        $statement = $pearDB->prepare(
            "UPDATE `user_filter` SET `criterias` = :criterias WHERE `id` = :id"
        );
        $statement->bindValue(':id', (int) $id, \PDO::PARAM_INT);
        $statement->bindValue(':criterias', $criterias, \PDO::PARAM_STR);
        $statement->execute();
    }

    //queries for broker logs
    $errorMessage = "Unable to Update cfg_centreonbroker";
    $pearDB->query(
        "UPDATE `cfg_centreonbroker` SET `log_directory` = '/var/log/centreon-broker/'"
    );
    $errorMessage = "Unable to set cb_log";
    $pearDB->query(
        "INSERT INTO `cb_log` (`name`)
        VALUES ('core'), ('config'), ('sql'), ('processing'), ('perfdata'),
               ('bbdo'), ('tcp'), ('tls'), ('lua'), ('bam')"
    );
    $errorMessage = "Unable to set cb_log_level";
    $pearDB->query(
        "INSERT INTO `cb_log_level` (`name`)
        VALUES ('disabled'), ('critical'), ('error'), ('warning'), ('info'), ('debug'), ('trace')"
    );
    $stmt = $pearDB->query(
        "SELECT config_id FROM cfg_centreonbroker"
    );
    while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
        $pearDB->query(
            "INSERT INTO `cfg_centreonbroker_log` (`id_centreonbroker`, `id_log`, `id_level`)
            VALUES (" . $row['config_id'] . ",1,5),
                   (" . $row['config_id'] . ",2,3),
                   (" . $row['config_id'] . ",3,3),
                   (" . $row['config_id'] . ",4,3),
                   (" . $row['config_id'] . ",5,3),
                   (" . $row['config_id'] . ",6,3),
                   (" . $row['config_id'] . ",7,3),
                   (" . $row['config_id'] . ",8,3),
                   (" . $row['config_id'] . ",9,3),
                   (" . $row['config_id'] . ",10,3)"
        );
    }
    $pearDB->commit();
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
