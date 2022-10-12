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
$versionOfTheUpgrade = 'UPGRADE - 22.04.6: ';
$errorMessage = '';

try {
    $errorMessage = "Impossible to update 'hosts' table";
    if (! str_contains(strtolower($pearDBO->getColumnType('hosts', 'notification_number')), 'bigint')) {
        $pearDBO->beginTransaction();
        $pearDBO->query("UPDATE `hosts` SET `notification_number`= 0 WHERE `notification_number`< 0");
        $pearDBO->query("ALTER TABLE `hosts` MODIFY `notification_number` BIGINT(20) UNSIGNED DEFAULT NULL");
    }

    $errorMessage = "Impossible to update 'services' table";
    if (! str_contains(strtolower($pearDBO->getColumnType('services', 'notification_number')), 'bigint')) {
        $pearDBO->beginTransaction();
        $pearDBO->query("UPDATE `services` SET `notification_number`= 0 WHERE `notification_number`< 0");
        $pearDBO->query("ALTER TABLE `services` MODIFY `notification_number` BIGINT(20) UNSIGNED DEFAULT NULL");
    }
} catch (\Exception $e) {
    if ($pearDBO->inTransaction()) {
        $pearDBO->rollBack();
    }

    $centreonLog->insertLog(
        4,
        $versionOfTheUpgrade . $errorMessage .
        " - Code : " . (int)$e->getCode() .
        " - Error : " . $e->getMessage() .
        " - Trace : " . $e->getTraceAsString()
    );

    throw new \Exception($versionOfTheUpgrade . $errorMessage, (int) $e->getCode(), $e);
}
