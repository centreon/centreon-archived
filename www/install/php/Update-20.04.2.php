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

require_once __DIR__ . '/../../class/centreonLog.class.php';
$centreonLog = new CentreonLog();

// error specific content
$versionOfTheUpgrade = 'UPGRADE - 20.04.2 : ';
$errorMessage = '';

/**
 * Queries needing exception management BUT no rollback if failing
 */
try {
    /*
     * Get timezones and add "Asia/Yangon" if doesn't exist
     */
    $errorMessage = 'Cannot retrieve timezone list';
    $res = $pearDB->query(
        "SELECT timezone_name FROM timezone
        WHERE timezone_name = 'Asia/Yangon'"
    );
    $timezone = $res->fetch();
    if (false === $timezone) {
        $errorMessage = 'Cannot add Asia/Yangon to timezone list';
        $stmt = $pearDB->query(
            'INSERT INTO timezone (timezone_name, timezone_offset, timezone_dst_offset, timezone_description)
            VALUE ("Asia/Yangon", "+06:30", "+06:30", NULL)'
        );
    }
} catch (\Exception $e) {
    $centreonLog->insertLog(
        4,
        $versionOfTheUpgrade . $errorMessage .
        " - Code : " . $e->getCode() .
        " - Error : " . $e->getMessage() .
        " - Trace : " . $e->getTraceAsString()
    );
    throw new \Exception($versionOfTheUpgrade . $errorMessage, $e->getCode(), $e);
}
