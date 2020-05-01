<?php
/*
 * Copyright 2005 - 2019 Centreon (https://www.centreon.com/)
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

try {
    // Change traps_execution_command from varchar(255) to text
    $pearDB->query(
        "ALTER TABLE `traps` MODIFY COLUMN `traps_execution_command` text DEFAULT NULL"
    );

    //Add trap regexp matching
    if (!$pearDB->isColumnExist('traps', 'traps_mode')) {
        $pearDB->query(
            "ALTER TABLE `traps` ADD COLUMN `traps_mode` ENUM('0', '1') DEFAULT '0' AFTER `traps_oid`"
        );
    }

    // Add trap filter
    $pearDB->query(
        "ALTER TABLE `traps` MODIFY COLUMN `traps_exec_interval_type` ENUM('0','1','2','3') NULL DEFAULT '0'"
    );
} catch (\PDOException $e) {
    $centreonLog->insertLog(
        2,
        "UPGRADE : Unable to process 19.04.3 upgrade"
    );
}
