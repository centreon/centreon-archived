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
$versionOfTheUpgrade = 'UPGRADE - 21.04.0-beta1 : ';

/**
 * Query without transaction
 */
try {
    if (!$pearDB->isColumnExist('cfg_centreonbroker', 'log_directory')) {
        // An update is required
        $errorMessage = 'Impossible to alter the table cfg_centreonbroker with log_directory';
        $pearDB->query(
            'ALTER TABLE `cfg_centreonbroker` ADD COLUMN `log_directory` VARCHAR(255)'
        );
    }
    if (!$pearDB->isColumnExist('cfg_centreonbroker', 'log_filename')) {
        // An update is required
        $errorMessage = 'Impossible to alter the table cfg_centreonbroker with log_filename';
        $pearDB->query(
            'ALTER TABLE `cfg_centreonbroker` ADD COLUMN `log_filename` VARCHAR(255)'
        );
    }
    if (!$pearDB->isColumnExist('cfg_centreonbroker', 'log_max_size')) {
        // An update is required
        $errorMessage = 'Impossible to alter the table cfg_centreonbroker with log_max_size';
        $pearDB->query(
            'ALTER TABLE `cfg_centreonbroker` ADD COLUMN `log_max_size` INT(255)'
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
        VALUES ('disabled'), ('critical'), ('error'), ('warning'), ('information'), ('debug'), ('trace')"
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
} catch (\Throwable $ex) {
    $pearDB->rollBack();
    (new CentreonLog())->insertLog(
        4,
        $versionOfTheUpgrade . $errorMessage .
        " - Code : " . $ex->getCode() .
        " - Error : " . $ex->getMessage() .
        " - Trace : " . $ex->getTraceAsString()
    );
    throw new \Exception($versionOfTheUpgrade . $errorMessage, $ex->getCode(), $ex);
}
