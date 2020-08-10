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

/**
 * New configuration options for Centreon Engine
 */
try {
    $pearDB->query('SET SESSION innodb_strict_mode=OFF');
    if (!$pearDB->isColumnExist('cfg_nagios', 'enable_macros_filter')) {
        //$pearDB = "centreon"
        //$pearDBO = "realtime"
        $pearDB->query(
            "ALTER TABLE `cfg_nagios` ADD COLUMN `enable_macros_filter` ENUM('0', '1') DEFAULT '0'"
        );
    }
    if (!$pearDB->isColumnExist('cfg_nagios', 'macros_filter')) {
        $pearDB->query(
            "ALTER TABLE `cfg_nagios` ADD COLUMN `macros_filter` TEXT DEFAULT ''"
        );
    }
} catch (\PDOException $e) {
    $centreonLog->insertLog(
        2,
        "UPGRADE : 19.04.0 Unable to modify centreon engine in the database"
    );
} finally {
    $pearDB->query('SET SESSION innodb_strict_mode=ON');
}
