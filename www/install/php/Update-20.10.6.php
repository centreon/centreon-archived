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

// error specific content
$versionOfTheUpgrade = 'UPGRADE - 20.10.6 : ';

// Part requiring rollback management
try {
    // Platform_topology refacto
    $errorMessage = "Unable to add pending column to platform_topology table";
    $statement = $pearDB->query(
        "SELECT COLUMN_DEFAULT
        FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = 'centreon'
          AND TABLE_NAME = 'platform_topology'
          AND COLUMN_NAME = 'pending'"
    );
    if ($statement->fetch(\PDO::FETCH_ASSOC) === false) {
        //Create the new column
        $pearDB->query(
            "ALTER TABLE `platform_topology` ADD COLUMN `pending` enum('0','1') DEFAULT ('1') AFTER `parent_id`"
        );
    }

    $pearDB->beginTransaction();
    $errorMessage = "Unable to update pending state on platform_topology table";
    // find registered platforms in monitoring table
    $statement = $pearDB->query(
        "SELECT id FROM `platform_topology`
        WHERE `type` NOT IN ('central', 'remote', 'poller') OR `server_id` IS NOT NULL"
    );
    while ($row = $statement->fetch(\PDO::FETCH_ASSOC)) {
        $result[] = $row['id'];
    }
    if (empty($result)) {
        throw new Exception('Cannot find the monitoring platform in platform_topology table');
    }
    $registeredPlatforms = implode(', ', $result);

    $pearDB->query(
        "UPDATE `platform_topology` SET `pending` = '0'
        WHERE id IN ($registeredPlatforms)"
    );
    $pearDB->commit();
    $errorMessage = '';
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
