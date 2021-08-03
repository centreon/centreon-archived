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

$versionOfTheUpgrade = 'UPGRADE - 21.10.0-beta.1: ';

$pearDB = new CentreonDB();

try {
    $pearDB->beginTransaction();
    $errorMessage = "Unable to check if table 'password_security_policy' exists";
    $dbResult = $pearDB->query("SHOW TABLES LIKE 'password_security_policy'");
    if ($dbResult->fetch()) {
        $errorMessage = "Unable to create table 'password_security_policy'";
        $pearDB->query(
            "CREATE TABLE `password_security_policy` (
            `password_length` int(11) UNSIGNED NOT NULL DEFAULT 12,
            `uppercase_characters` enum('0', '1') NOT NULL DEFAULT '1',
            `lowercase_characters` enum('0', '1') NOT NULL DEFAULT '1',
            `integer_characters` enum('0', '1') NOT NULL DEFAULT '1',
            `special_characters` enum('0', '1') NOT NULL DEFAULT '1',
            `attempts` int(11) UNSIGNED NOT NULL DEFAULT 5,
            `blocking_duration` int(11) UNSIGNED NOT NULL DEFAULT 900,
            `password_expiration` int(11) UNSIGNED NOT NULL DEFAULT 7776000,
            `delay_before_new_password` int(11) UNSIGNED NOT NULL DEFAULT 3600)"
        );

        $errorMessage = "Unable to create insert default configuration in 'password_security_policy'";
        $pearDB->query("INSERT INTO `password_security_policy`
        (`password_length`, `uppercase_characters`, `lowercase_characters`, `integer_characters`,
        `special_characters`, `attempts`, `blocking_duration`, `password_expiration`, `delay_before_new_password`)
        VALUES (12, '1', '1', '1', '1', 5, 900, 7776000, 3600)");
    }
    $pearDB->commit();
} catch (Exception $e) {
    $pearDB->rollback();
    $centreonLog->insertLog(
        4,
        $versionOfTheUpgrade . $errorMessage .
        " - Code : " . (int)$e->getCode() .
        " - Error : " . $e->getMessage() .
        " - Trace : " . $e->getTraceAsString()
    );
    throw new \Exception($versionOfTheUpgrade . $errorMessage, (int)$e->getCode(), $e);
}
