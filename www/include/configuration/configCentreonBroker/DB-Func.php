<?php
/*
 * Copyright 2005-2015 Centreon
 * Centreon is developped by : Julien Mathis and Romain Le Merlus under
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
 */

/**
 *
 * Test broker file config existance
 * @param $name
 */
function testExistence($name = null)
{
    global $pearDB, $form;

    $id = null;

    if (isset($form)) {
        $id = $form->getSubmitValue('id');
    }

    $dbResult = $pearDB->query("SELECT config_name, config_id 
                                FROM `cfg_centreonbroker` 
                                WHERE `config_name` = '" . htmlentities($name, ENT_QUOTES, "UTF-8") . "'");
    $ndomod = $dbResult->fetch();
    if ($dbResult->rowCount() >= 1 && $ndomod["config_id"] == $id) {
        return true;
    } elseif ($dbResult->rowCount() >= 1 && $ndomod["config_id"] != $id) {
        return false;
    } else {
        return true;
    }
}

/**
 * Enable a Centreon Broker configuration
 *
 * @param int $id The Centreon Broker configuration in database
 */
function enableCentreonBrokerInDB($id)
{
    global $pearDB;

    if (!$id) {
        return;
    }

    $query = "UPDATE cfg_centreonbroker SET config_activate = '1' WHERE config_id = " . $id;
    $pearDB->query($query);
}

/**
 * Disable a Centreon Broker configuration
 *
 * @param int $id The Centreon Broker configuration in database
 */
function disablCentreonBrokerInDB($id)
{
    global $pearDB;

    if (!$id) {
        return;
    }

    $query = "UPDATE cfg_centreonbroker SET config_activate = '0' WHERE config_id = " . $id;
    $pearDB->query($query);
}

/**
 * Delete Centreon Broker configurations
 *
 * @param array $id The Centreon Broker configuration in database
 */
function deleteCentreonBrokerInDB($ids = array())
{
    global $pearDB;

    foreach ($ids as $key => $value) {
        $pearDB->query("DELETE FROM cfg_centreonbroker WHERE config_id = " . $key);
    }
}

/**
 * Get the information of a server
 *
 * @param int $id
 * @return array
 */
function getCentreonBrokerInformation($id)
{
    global $pearDB;
    $query =
        "SELECT config_name, config_filename, ns_nagios_server, stats_activate,
            config_write_timestamp, config_write_thread_id, config_activate, event_queue_max_size,
            cache_directory, command_file, daemon, pool_size, log_directory, log_filename, log_max_size
        FROM cfg_centreonbroker
        WHERE config_id = " . $id;
    try {
        $res = $pearDB->query($query);
    } catch (\PDOException $e) {
        $brokerConf = [
            "name" => '',
            "filename" => '',
            "log_directory" => '/var/log/centreon-broker/',
            "write_timestamp" => '1',
            "write_thread_id" => '1',
            "activate_watchdog" => '1',
            "activate" => '1',
            "event_queue_max_size" => '',
            "pool_size" => null,
        ];
    }
    $row = $res->fetch();
    if (!isset($brokerConf)) {
        $brokerConf = [
            "id" => $id,
            "name" => $row['config_name'],
            "filename" => $row['config_filename'],
            "ns_nagios_server" => $row['ns_nagios_server'],
            "activate" => $row['config_activate'],
            "activate_watchdog" => $row['daemon'],
            "stats_activate" => $row['stats_activate'],
            "write_timestamp" => $row['config_write_timestamp'],
            "write_thread_id" => $row['config_write_thread_id'],
            "event_queue_max_size" => $row['event_queue_max_size'],
            "cache_directory" => $row['cache_directory'],
            "command_file" => $row['command_file'],
            "daemon" => $row['daemon'],
            "pool_size" => $row['pool_size'],
            "log_directory" => $row['log_directory'],
            "log_filename" => $row['log_filename'],
            "log_max_size" => $row['log_max_size'],
        ];
    }
    /*
     * Log
     */
    $brokerLogConf = [];
    $query = "SELECT log.`name`, relation.`id_level`
            FROM `cb_log` log
            LEFT JOIN `cfg_centreonbroker_log` relation
                ON relation.`id_log`  = log.`id`
            WHERE relation.`id_centreonbroker` = " . $id;
    try {
        $res = $pearDB->query($query);
    } catch (\PDOException $e) {
        return $brokerConf;
    }
    while ($row = $res->fetch()) {
        $brokerLogConf['log_' . $row['name']] = $row['id_level'];
    }
    $result = array_merge($brokerConf, $brokerLogConf);
    return $result;
}

/**
 * Duplicate a configuration
 *
 * @param array $ids List of id CentreonBroker configuration
 * @param array $nbr List of number a duplication
 */
function multipleCentreonBrokerInDB($ids, $nbrDup)
{
    global $pearDB;
    foreach ($ids as $id => $value) {
        $cbObj = new CentreonConfigCentreonBroker($pearDB);

        $query = "SELECT config_name, config_filename, config_activate, ns_nagios_server,
            event_queue_max_size, cache_directory, daemon "
            . "FROM cfg_centreonbroker "
            . "WHERE config_id = " . $id . " ";
        $dbResult = $pearDB->query($query);
        $row = $dbResult->fetch();
        $dbResult->closeCursor();

        # Prepare values
        $values = array();
        $values['activate_watchdog']['activate_watchdog'] = '0';
        $values['activate']['activate'] = '0';
        $values['ns_nagios_server'] = $row['ns_nagios_server'];
        $values['event_queue_max_size'] = $row['event_queue_max_size'];
        $values['cache_directory'] = $row['cache_directory'];
        $values['activate_watchdog']['activate_watchdog'] = $row['daemon'];
        $query = "SELECT config_key, config_value, config_group, config_group_id "
            . "FROM cfg_centreonbroker_info "
            . "WHERE config_id = " . $id . " ";
        $dbResult = $pearDB->query($query);
        $values['output'] = array();
        $values['input'] = array();
        $values['logger'] = array();
        while ($rowOpt = $dbResult->fetch()) {
            if ($rowOpt['config_key'] == 'filters') {
                continue;
            } elseif ($rowOpt['config_key'] == 'category') {
                $config_key = 'filters__' . $rowOpt['config_group_id'] . '__category';
                $values[$rowOpt['config_group']][$rowOpt['config_group_id']][$config_key] = $rowOpt['config_value'];
            } else {
                $values[$rowOpt['config_group']][$rowOpt['config_group_id']][$rowOpt['config_key']] =
                    $rowOpt['config_value'];
            }
        }
        $dbResult->closeCursor();

        # Convert values radio button
        foreach ($values as $group => $groups) {
            if (is_array($groups)) {
                foreach ($groups as $gid => $infos) {
                    if (isset($infos['blockId'])) {
                        list($tagId, $typeId) = explode('_', $infos['blockId']);
                        $fieldtype = $cbObj->getFieldtypes($typeId);
                    } else {
                        $fieldtype = array();
                    }

                    if (is_array($infos)) {
                        foreach ($infos as $key => $value) {
                            if (isset($fieldtype[$key]) && $fieldtype[$key] == 'radio') {
                                $values[$group][$gid][$key] = array($key => $value);
                            }
                        }
                    }
                }
            }
        }

        # Copy the configuration
        $j = 1;
        for ($i = 1; $i <= $nbrDup[$id]; $i++) {
            $nameNOk = true;

            # Find the name
            while ($nameNOk) {
                $newname = $row['config_name'] . '_' . $j;
                $newfilename = $j . '_' . $row['config_filename'];
                $query = "SELECT COUNT(*) as nb FROM cfg_centreonbroker WHERE config_name = '" . $newname . "'";
                $res = $pearDB->query($query);
                $rowNb = $res->fetch();
                if ($rowNb['nb'] == 0) {
                    $nameNOk = false;
                }
                $j++;
            }
            $values['name'] = $newname;
            $values['filename'] = $newfilename;
            $cbObj->insertConfig($values);
        }
    }
}

/**
 * Rule to check if given value is positive and numeric
 *
 * @param $size
 * @return bool
 */
function isPositiveNumeric($size): bool
{
    if (!is_numeric($size)) {
        return false;
    }
    $isPositive = false;
    if ((int)$size === (int)abs($size)) {
        $isPositive = true;
    }
    return $isPositive;
}
