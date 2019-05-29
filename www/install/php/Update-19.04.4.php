<?php
/*
 * Copyright 2005-2019 Centreon
 * Centreon is developed by : Julien Mathis and Romain Le Merlus under
 * GPL Licence 2.0.
 *
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License as published by the Free Software
 * Foundation ; either version 2 of the License.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A
 * PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with
 * this program; if not, see <http://www.gnu.org/licenses>.
 *
 * Linking this program statically or dynamically with other modules is making a
 * combined work based on this program. Thus, the terms and conditions of the GNU
 * General Public License cover the whole combination.
 *
 * As a special exception, the copyright holders of this program give Centreon
 * permission to link this program with independent modules to produce an executable,
 * regardless of the license terms of these independent modules, and to copy and
 * distribute the resulting executable under terms of Centreon choice, provided that
 * Centreon also meet, for each linked independent module, the terms  and conditions
 * of the license of that module. An independent module is a module which is not
 * derived from this program. If you modify this program, you may extend this
 * exception to your version of the program, but you are not obliged to do so. If you
 * do not wish to do so, delete this exception statement from your version.
 *
 * For more information : contact@centreon.com
 *
 *
 */

$classPath = __DIR__ . "/../..";
include_once $classPath . "/class/centreonLog.class.php";
$centreonLog = new CentreonLog();

/* LDAP auto or manual synchronization feature  */
try {
    // Add columns to check last user's LDAP sync timestamp
    $pearDB->query(
        "ALTER TABLE `contact` ADD COLUMN IF NOT EXISTS `contact_ldap_last_sync` INT(11) NOT NULL DEFAULT 0;"
    );
    $pearDB->query(
        "ALTER TABLE `contact` ADD COLUMN IF NOT EXISTS `contact_ldap_required_sync` enum('0','1') NOT NULL DEFAULT '0';"
    );

    // Add a column to check last specific LDAP sync timestamp
    $pearDB->query(
        "ALTER TABLE `auth_ressource` ADD COLUMN IF NOT EXISTS `ar_sync_base_date` INT(11) DEFAULT 0;"
    );
} catch (\PDOException $e) {
    $centreonLog->insertLog(
        2,
        "UPGRADE : Unable to add LDAP new feature's tables in the database"
    );
}

/* Initializing base reference synchronization date for all LDAP configurations */
try {
    $stmt = $pearDB->prepare(
        "UPDATE `auth_ressource` SET `ar_sync_base_date` = :minusTime;"
    );
    $stmt->bindValue(':minusTime', time() - 3600, \PDO::PARAM_INT);
    $stmt->execute();
} catch (\PDOException $e) {
    $centreonLog->insertLog(
        2,
        "UPGRADE : Unable to initialize LDAP reference date"
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
        "UPGRADE PROCESS : Please open your LDAP configuration and save manually the form"
    );
    $centreonLog->insertLog(
        2, // sql-error.log
        "UPGRADE : Unable to add LDAP new fields"
    );
    $pearDB->rollBack();
}
