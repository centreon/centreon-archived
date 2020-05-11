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
    $pearDB->query('SET SESSION innodb_strict_mode=OFF');

    // Add HTTPS connexion to Remote Server
    if (!$pearDB->isColumnExist('remote_servers', 'http_method')) {
        $pearDB->query(
            "ALTER TABLE remote_servers ADD COLUMN `http_method` enum('http','https') NOT NULL DEFAULT 'http'"
        );
    }
    if (!$pearDB->isColumnExist('remote_servers', 'http_port')) {
        $pearDB->query(
            "ALTER TABLE remote_servers ADD COLUMN `http_port` int(11) NULL DEFAULT NULL"
        );
    }
    if (!$pearDB->isColumnExist('remote_servers', 'no_check_certificate')) {
        $pearDB->query(
            "ALTER TABLE remote_servers ADD COLUMN `no_check_certificate` enum('0','1') NOT NULL DEFAULT '0'"
        );
    }
    if (!$pearDB->isColumnExist('remote_servers', 'no_proxy')) {
        $pearDB->query(
            "ALTER TABLE remote_servers ADD COLUMN `no_proxy` enum('0','1') NOT NULL DEFAULT '0'"
        );
    }
} catch (\PDOException $e) {
    $centreonLog->insertLog(
        2,
        "UPGRADE : Unable to process 19.04.1 upgrade"
    );
} finally {
    $pearDB->query('SET SESSION innodb_strict_mode=ON');
}
