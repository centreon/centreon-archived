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

 // Add columns to manage engine & broker restart/reload process
$pearDB->query('
    ALTER TABLE `nagios_server`
    ADD COLUMN `engine_restart_command` varchar(255) DEFAULT \'service centengine restart\' AFTER `monitoring_engine`
');
$pearDB->query('
    ALTER TABLE `nagios_server`
    ADD COLUMN `engine_reload_command` varchar(255) DEFAULT \'service centengine reload\' AFTER `engine_restart_command`
');
$pearDB->query('
    ALTER TABLE `nagios_server`
    ADD COLUMN `broker_reload_command` varchar(255) DEFAULT \'service cbd reload\' AFTER `nagios_perfdata`
');

$stmt = $pearDB->prepare('
    UPDATE `nagios_server`
    SET engine_restart_command = :engine_restart_command,
    engine_reload_command = :engine_reload_command,
    broker_reload_command = :broker_reload_command
    WHERE id = :id
');

$result = $pearDB->query('SELECT value FROM `options` WHERE `key` = \'broker_correlator_script\'');
$brokerServiceName = 'cbd';
if ($row = $result->fetch()) {
    if (!empty($row['value'])) {
        $brokerServiceName = $row['value'];
    }
}
$stmt->bindValue(':broker_reload_command', 'service ' . $brokerServiceName . ' reload', \PDO::PARAM_STR);

$result = $pearDB->query('SELECT id, init_script FROM `nagios_server`');

while ($row = $result->fetch()) {
    $engineServiceName = 'centengine';
    if (!empty($row['init_script'])) {
        $engineServiceName = $row['init_script'];
    }
    $stmt->bindValue(':id', $row['id'], \PDO::PARAM_INT);
    $stmt->bindValue(':engine_restart_command', 'service ' . $engineServiceName . ' restart', \PDO::PARAM_STR);
    $stmt->bindValue(':engine_reload_command', 'service ' . $engineServiceName . ' reload', \PDO::PARAM_STR);
    $stmt->execute();
}

// Remove deprecated engine & broker init script paths
$pearDB->query('ALTER TABLE `nagios_server` DROP COLUMN `init_script`');
$pearDB->query('DELETE FROM `options` WHERE `key` = \'broker_correlator_script\'');
