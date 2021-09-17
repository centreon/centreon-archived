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
$versionOfTheUpgrade = 'UPGRADE - 20.10.12: ';

$pearDB = new CentreonDB('centreon', 3, false);

/**
 * Query with transaction
 */
try {
    $pearDB->beginTransaction();

    //Purge all session.
    $errorMessage = 'Impossible to purge the table session';
    $pearDB->query("DELETE FROM `session`");

    $errorMessage = 'Impossible to purge the table ws_token';
    $pearDB->query("DELETE FROM `ws_token`");

    $constraintStatement = $pearDB->query(
        "SELECT COUNT(*) as count FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS WHERE CONSTRAINT_NAME='session_ibfk_1'"
    );
    if (($constraint = $constraintStatement->fetch()) && $constraint['count'] === 0) {
        $errorMessage = 'Impossible to add Delete Cascade constraint on the table session';
        $pearDB->query(
            "ALTER TABLE `session` ADD CONSTRAINT `session_ibfk_1` FOREIGN KEY (`user_id`) " .
            "REFERENCES `contact` (`contact_id`) ON DELETE CASCADE"
        );
    }

    $errorMessage = "Impossible to drop column 'contact_platform_data_sending' from 'contact' table";
    $pearDB->query("ALTER TABLE `contact` DROP COLUMN `contact_platform_data_sending`");

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
