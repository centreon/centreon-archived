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

//error specific content
$versionOfTheUpgrade = 'UPGRADE - 20.10.6 : ';
$errorMessage = '';

try {
    //engine postpone
    if ($pearDB->isColumnExist('cfg_nagios', 'postpone_notification_to_timeperiod')) {
        // An update is required
        $errorMessage = 'Impossible to drop postpone_notification_to_timeperiod from fg_nagios';
        $pearDB->query('ALTER TABLE `cfg_nagios` DROP COLUMN `postpone_notification_to_timeperiod`');
    }

    // Platform_topology refacto
    if (0 === $pearDB->isColumnExist('platform_topology', 'pending')) {
        //Create the new column
        $errorMessage = "Unable to add pending column to platform_topology table";
        $pearDB->query(
            "ALTER TABLE `platform_topology` ADD COLUMN `pending` enum('0','1') DEFAULT '1' AFTER `parent_id`"
        );
    }
    $errorMessage = '';
} catch (\Exception $e) {
    (new CentreonLog())->insertLog(
        4,
        $versionOfTheUpgrade . $errorMessage .
        " - Code : " . (int)$e->getCode() .
        " - Error : " . $e->getMessage() .
        " - Trace : " . $e->getTraceAsString()
    );
    throw new \Exception($versionOfTheUpgrade . $errorMessage, (int)$e->getCode(), $e);
}

/**
 * Queries needing exception management and rollback if failing
 */
try {
    $pearDB->beginTransaction();
    $errorMessage = "Unable to update pending state on platform_topology table";
    // find registered platforms
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
} catch (\Exception $e) {
    $pearDB->rollBack();
    (new CentreonLog())->insertLog(
        4,
        $versionOfTheUpgrade . $errorMessage .
        " - Code : " . (int)$e->getCode() .
        " - Error : " . $e->getMessage() .
        " - Trace : " . $e->getTraceAsString()
    );
    throw new \Exception($versionOfTheUpgrade . $errorMessage, (int)$e->getCode(), $e);
}
