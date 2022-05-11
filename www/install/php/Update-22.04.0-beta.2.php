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
$versionOfTheUpgrade = 'UPGRADE - 22.04.0-beta.2: ';
$errorMessage = '';

try {
    // Centengine logger v2
    if (
        $pearDB->isColumnExist('cfg_nagios', 'log_archive_path') === 1
        && $pearDB->isColumnExist('cfg_nagios', 'log_rotation_method') === 1
        && $pearDB->isColumnExist('cfg_nagios', 'daemon_dumps_core') === 1
    ) {
        $errorMessage = "Unable to remove log_archive_path,log_rotation_method,daemon_dumps_core from cfg_nagios table";
        $pearDB->query(
            "ALTER TABLE `cfg_nagios`
            DROP COLUMN `log_archive_path`,
            DROP COLUMN `log_rotation_method`,
            DROP COLUMN `daemon_dumps_core`"
        );
    }
    if ($pearDB->isColumnExist('cfg_nagios', 'logger_version') !== 1) {
        $errorMessage = "Unable to add logger_version to cfg_nagios table";
        $pearDB->query(
            "ALTER TABLE `cfg_nagios`
            ADD COLUMN `logger_version` enum('log_v2_enabled', 'log_legacy_enabled') DEFAULT 'log_v2_enabled'"
        );
    }

    $errorMessage = "Unable to update logger_version from cfg_nagios table";
    $pearDB->query(
        "UPDATE `cfg_nagios` set logger_version = 'log_legacy_enabled'"
    );
} catch (\Exception $e) {
    $centreonLog->insertLog(
        4,
        $versionOfTheUpgrade . $errorMessage .
        " - Code : " . (int)$e->getCode() .
        " - Error : " . $e->getMessage() .
        " - Trace : " . $e->getTraceAsString()
    );

    throw new \Exception($versionOfTheUpgrade . $errorMessage, (int) $e->getCode(), $e);
}
