<?php
/*
 * Copyright 2005-2018 Centreon
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

/**
 * Checking if the DB is already upgraded
 */
$needUpgrade = "false";

/**
 * Declaring a global param to avoid centAcl.php to disconnect the DBs until the update is achieved
 */
if (!defined('UPGRADE_PROCESS')) {
    define('UPGRADE_PROCESS', 'True');
}

$unlockTable = 'UNLOCK TABLES';
$stmt = $pearDB->query($unlockTable);
$stmt = $pearDBO->query($unlockTable);

$showKeysQuery = 'SHOW FIELDS FROM centreon_acl';
$result = $pearDBO->query($showKeysQuery);

$row = array();
while ($row = $result->fetchRow()) {
    if ($row['Key'] === "MUL") {
        $needUpgrade = "true";
    }
}

if ($needUpgrade === "true") {
    /**
     * Checking if centAcl.php is running and waiting 2min for it to stop before locking cron_operation table
     */
    $i = 0;
    while ($i < 120) {
        $searchStatus = 'SELECT running FROM cron_operation WHERE `name` = \'centAcl.php\'';
        $result = $pearDB->query($searchStatus);
        $row = array();
        while ($row = $result->fetchRow()) {
            if ($row['running'] == "1") {
                sleep(1);
            } else {
                $lockTable = 'LOCK TABLES cron_operation READ';
                $stmt = $pearDB->query($lockTable);
                break;
            }
        }
        $i++;
    }

    /**
    * Retrieving index occurrences, dropping keys and centreon_acl table's data
    */
    $stmt = $pearDBO->query($unlockTable);

    $searchIndex = 'SHOW INDEX FROM centreon_acl WHERE Key_name LIKE \'index%\'';
    $stmt = $pearDBO->query($searchIndex);

    $queryValues = array();
    while ($row = $result->fetchRow()) {
        $queryValues[$row['Key_name']] = $row['Key_name'];
    }

    foreach ($queryValues as $key => $value) {
        $dropQuery = 'ALTER TABLE centreon_acl DROP KEY ' . $value;
        $stmt = $pearDBO->query($dropQuery);
    }

    $truncateQuery = 'TRUNCATE centreon_acl';
    $stmt = $pearDBO->query($truncateQuery);

    /**
     * Updating DBs and reloading cron_operation
     */
    $add_query = 'ALTER TABLE centreon_acl ADD PRIMARY KEY (`group_id`,`host_id`,`service_id`)';
    $stmt = $pearDBO->query($add_query);

    $stmt = $pearDB->query($unlockTable);

    $updateQuery = 'UPDATE acl_groups SET acl_group_changed = 1';
    $stmt = $pearDB->query($updateQuery);
}
?>