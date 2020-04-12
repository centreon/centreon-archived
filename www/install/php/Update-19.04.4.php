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
 * LDAP auto or manual synchronization feature
 */
try {
    $pearDB->query('SET SESSION innodb_strict_mode=OFF');

    // Adding two columns to check last user's LDAP sync timestamp
    if (!$pearDB->isColumnExist('contact', 'contact_ldap_last_sync')) {
        //$pearDB = "centreon"
        //$pearDBO = "realtime"
        $pearDB->query(
            "ALTER TABLE `contact` ADD COLUMN `contact_ldap_last_sync` INT(11) NOT NULL DEFAULT 0"
        );
    }
    if (!$pearDB->isColumnExist('contact', 'contact_ldap_required_sync')) {
        $pearDB->query(
            "ALTER TABLE `contact` ADD COLUMN `contact_ldap_required_sync` enum('0','1') NOT NULL DEFAULT '0'"
        );
    }

    // Adding a column to check last specific LDAP sync timestamp
    $needToUpdateValues = false;
    if (!$pearDB->isColumnExist('auth_ressource', 'ar_sync_base_date')) {
        $pearDB->query(
            "ALTER TABLE `auth_ressource` ADD COLUMN `ar_sync_base_date` INT(11) DEFAULT 0"
        );
        $needToUpdateValues = true;
    }
} catch (\PDOException $e) {
    $centreonLog->insertLog(
        2,
        "UPGRADE : 19.04.4 Unable to add LDAP new feature's tables in the database"
    );
} finally {
    $pearDB->query('SET SESSION innodb_strict_mode=ON');
}

// Initializing reference synchronization time for all LDAP configurations */
if ($needToUpdateValues) {
    try {
        $stmt = $pearDB->prepare(
            "UPDATE `auth_ressource` SET `ar_sync_base_date` = :minusTime"
        );
        $stmt->bindValue(':minusTime', time(), \PDO::PARAM_INT);
        $stmt->execute();
    } catch (\PDOException $e) {
        $centreonLog->insertLog(
            2,
            "UPGRADE : 19.04.4 Unable to initialize LDAP reference date"
        );
    }

    /* Adding to each LDAP configuration two new fields */
    try {
        // field to enable the automatic sync at login
        $addSyncStateField = $pearDB->prepare(
            "INSERT IGNORE INTO auth_ressource_info
            (`ar_id`, `ari_name`, `ari_value`)
            VALUES (:arId, 'ldap_auto_sync', '1')"
        );
        // interval between two sync at login
        $addSyncIntervalField = $pearDB->prepare(
            "INSERT IGNORE INTO auth_ressource_info
            (`ar_id`, `ari_name`, `ari_value`)
            VALUES (:arId, 'ldap_sync_interval', '1')"
        );

        $pearDB->beginTransaction();
        $stmt = $pearDB->query("SELECT DISTINCT(ar_id) FROM auth_ressource");
        while ($row = $stmt->fetch()) {
            $addSyncIntervalField->bindValue(':arId', $row['ar_id'], \PDO::PARAM_INT);
            $addSyncIntervalField->execute();
            $addSyncStateField->bindValue(':arId', $row['ar_id'], \PDO::PARAM_INT);
            $addSyncStateField->execute();
        }
        $pearDB->commit();
    } catch (\PDOException $e) {
        $centreonLog->insertLog(
            1, // ldap.log
            "UPGRADE PROCESS : Error - Please open your LDAP configuration and save manually each LDAP form"
        );
        $centreonLog->insertLog(
            2, // sql-error.log
            "UPGRADE : 19.04.4 Unable to add LDAP new fields"
        );
        $pearDB->rollBack();
    }
}

// update topology of poller wizard to display breadcrumb
$pearDB->query(
    'UPDATE topology
    SET topology_parent = 60901,
    topology_page = 60959,
    topology_group = 1,
    topology_show = "0"
    WHERE topology_url LIKE "/poller-wizard/%"'
);


try {
    // Add trap regexp matching
    if (!$pearDB->isColumnExist('traps', 'traps_mode')) {
        $pearDB->query('SET SESSION innodb_strict_mode=OFF');
        $pearDB->query(
            "ALTER TABLE `traps` ADD COLUMN `traps_mode` enum('0','1') DEFAULT '0' AFTER `traps_oid`"
        );
    }
} catch (\PDOException $e) {
    $centreonLog->insertLog(
        2,
        "UPGRADE : 19.04.4 Unable to modify regexp matching in the database"
    );
} finally {
    $pearDB->query('SET SESSION innodb_strict_mode=ON');
}
