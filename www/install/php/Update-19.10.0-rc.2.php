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
 * Update session duration value to the max allowed duration set in the php
 * configuration file 50-centreon.ini
 */
try {
    $stmt = $pearDB->query(
        'SELECT `value` FROM `options` WHERE `key` = "session_expire"'
    );
    $sessionValue = $stmt->fetch();

    if ($sessionValue > 120) {
        $pearDB->query(
            'UPDATE `options` SET `value` = "120"
            WHERE `key` = "session_expire"'
        );
    }
} catch (\PDOException $e) {
    $centreonLog->insertLog(
        2,
        "UPGRADE : 19.10.0 Unable to modify session expiration value"
    );
}
