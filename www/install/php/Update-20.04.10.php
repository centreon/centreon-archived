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

// error specific content
$versionOfTheUpgrade = 'UPGRADE - 20.04.10 : ';
$errorMessage = '';

try {
    $statement = $pearDB->query(
        'SELECT COLUMN_DEFAULT
        FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = \'centreon\'
          AND TABLE_NAME = \'cfg_nagios\'
          AND COLUMN_NAME = \'postpone_notification_to_timeperiod\''
    );
    if (($result = $statement->fetch(\PDO::FETCH_ASSOC)) !== false) {
        $defaultValue = $result['COLUMN_DEFAULT'];
        if ($defaultValue !== '0') {
            // An update is required
            $errorMessage = 'Impossible to alter the table cfg_nagios';
            $pearDB->query(
                'ALTER TABLE `cfg_nagios` ADD COLUMN
                `postpone_notification_to_timeperiod` boolean DEFAULT false AFTER `nagios_group`'
            );
        }
    }
} catch (\Throwable $ex) {
    (new CentreonLog())->insertLog(
        4,
        $versionOfTheUpgrade . $errorMessage .
        " - Code : " . $ex->getCode() .
        " - Error : " . $ex->getMessage() .
        " - Trace : " . $ex->getTraceAsString()
    );
    throw new \Exception($versionOfTheUpgrade . $errorMessage, $ex->getCode(), $ex);
}
