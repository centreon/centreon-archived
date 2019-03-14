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

/*
 * Create tempory table to delete duplicate entries
 */
$query = 'CREATE TABLE `centreon_acl_new` ( ' .
    '`group_id` int(11) NOT NULL, ' .
    '`host_id` int(11) NOT NULL, ' .
    '`service_id` int(11) DEFAULT NULL, ' .
    'UNIQUE KEY (`group_id`,`host_id`,`service_id`), ' .
    'KEY `index1` (`host_id`,`service_id`,`group_id`) ' .
    ') ENGINE=InnoDB DEFAULT CHARSET=utf8 ';
$pearDBO->query($query);

/**
 * Checking if centAcl.php is running and waiting 2min for it to stop before locking cron_operation table
 */
$query = "SELECT running FROM cron_operation WHERE `name` = 'centAcl.php'";
$i = 0;
while ($i < 120) {
    $i++;
    $result = $pearDB->query($query);
    while ($row = $result->fetchRow()) {
        if ($row['running'] == "1") {
            sleep(1);
        } else {
            break(2);
        }
    }
}

/**
 * Lock centAcl cron during upgrade
 */
$query = "UPDATE cron_operation SET running = '1' WHERE `name` = 'centAcl.php'";
$pearDB->query($query);

/**
 * Copy data from old table to new table with duplicate entries deletion
 */
$query = 'INSERT INTO centreon_acl_new (group_id, host_id, service_id) ' .
    'SELECT group_id, host_id, service_id FROM centreon_acl ' .
    'GROUP BY group_id, host_id, service_id';
$pearDBO->query($query);

/**
 * Drop old table with duplicate entries
 */
$query = 'DROP TABLE centreon_acl';
$pearDBO->query($query);

/**
 * Rename temporary table to stable table
 */
$query = 'ALTER TABLE centreon_acl_new RENAME TO centreon_acl';
$pearDBO->query($query);

/**
 * Unlock centAcl cron during upgrade
 */
$query = "UPDATE cron_operation SET running = '0' WHERE `name` = 'centAcl.php'";
$pearDB->query($query);
