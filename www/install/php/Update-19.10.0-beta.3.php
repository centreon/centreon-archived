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

$result = $pearDB->query("SELECT `value` FROM options WHERE `key` = 'rrdcached_enable' ");
$cache = $result->fetch();

if ($cache['value']) {

    try {
        $pearDB->beginTransaction();

        $res = $pearDB->query(
            "SELECT * FROM cfg_centreonbroker_info WHERE `config_key` = 'type' AND `config_value` = 'rrd'"
        );
        $result = $pearDB->query("SELECT `value` FROM options WHERE `key` = 'rrdcached_port' ");
        $port = $result->fetch();

        while ($row = $res->fetch()) {
            if ($port['value']) {
                $query = 'INSERT INTO cfg_centreonbroker_info (config_id, config_key, config_value, '
                    . 'config_group, config_group_id ) VALUES '
                    . '( ' . $row['config_id'] . ',"rrd_cached_option","tcp",'
                    . $row['config_group'] . ',' . $row['config_group_id'] . ' ),'
                    . '( ' . $row['config_id'] . ',"rrd_cached","' . $port['value'] . '",'
                    . $row['config_group'] . ',' . $row['config_group_id'] . ' )';
                $pearDB->query($query);
            } else {
                $result = $pearDB->query("SELECT `value` FROM options WHERE `key` = 'rrdcached_unix_path' ");
                $path = $result->fetch();

                $query = 'INSERT INTO cfg_centreonbroker_info (config_id, config_key, config_value, '
                    . 'config_group, config_group_id ) VALUES '
                    . '( ' . $row['config_id'] . ',"rrd_cached_option","unix",'
                    . $row['config_group'] . ',' . $row['config_group_id'] . ' ),'
                    . '( ' . $row['config_id'] . ',"rrd_cached","' . $path['value'] . '",'
                    . $row['config_group'] . ',' . $row['config_group_id'] . ' )';
                $pearDB->query($query);
            }
        }
        $pearDB->query(
            "DELETE FROM options WHERE `key` = 'rrdcached_enable' 
                OR `key` = 'rrdcached_port' OR `key` = 'rrdcached_unix_path'"
        );
        $pearDB->commit();
    } catch (\PDOException $e) {

        $centreonLog->insertLog(
            2, // sql-error.log
            "UPGRADE : Unable to move rrd global cache option on broker form"
        );
        $pearDB->rollBack();
    }
}


