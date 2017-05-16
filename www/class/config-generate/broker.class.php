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

class Broker extends AbstractObjectXML
{
    protected $engine = null;
    protected $broker = null;
    protected $generate_filename = null;
    protected $object_name = null;
    protected $attributes_select = '
        config_id,
        config_name,
        config_filename,
        config_write_timestamp,
        config_write_thread_id,
        ns_nagios_server,
        event_queue_max_size,
        command_file,
        cache_directory,
        stats_activate,
        correlation_activate,
        daemon
    ';
    protected $attributes_select_parameters = '
        config_group,
        config_group_id,
        config_id,
        config_key,
        config_value,
        grp_level,
        subgrp_id,
        parent_grp_id,
        fieldIndex
    ';
    protected $attributes_engine_parameters = '
        id,
        name,
        centreonbroker_module_path,
        centreonbroker_cfg_path
    ';
    protected $exclude_parameters = array(
        'blockId'
    );
    protected $authorized_empty_field = array(
        'db_password'
    );
    protected $stmt_engine = null;
    protected $stmt_broker = null;
    protected $stmt_broker_parameters = null;
    protected $stmt_engine_parameters = null;

    private function generate($poller_id, $localhost)
    {
        if (is_null($this->stmt_broker)) {
            $this->stmt_broker = $this->backend_instance->db->prepare("SELECT 
              $this->attributes_select
            FROM cfg_centreonbroker
            WHERE ns_nagios_server = :poller_id
            AND config_activate = '1'
            ");
        }
        $this->stmt_broker->bindParam(':poller_id', $poller_id, PDO::PARAM_INT);
        $this->stmt_broker->execute();

        $this->getEngineParameters($poller_id);

        if (is_null($this->stmt_broker_parameters)) {
            $this->stmt_broker_parameters = $this->backend_instance->db->prepare("SELECT
              $this->attributes_select_parameters
            FROM cfg_centreonbroker_info
            WHERE config_id = :config_id
            ORDER BY config_group, config_group_id
            ");
        }

        $watchdog = array();

        $result = $this->stmt_broker->fetchAll(PDO::FETCH_ASSOC);
        foreach ($result as $row) {
            $this->generate_filename = $row['config_filename'];
            $object = array();
            $flow_count = 0;

            $config_name = $row['config_name'];
            $cache_directory = $row['cache_directory'];
            $stats_activate = $row['stats_activate'];
            $correlation_activate = $row['correlation_activate'];

            # Base parameters
            $object['broker_id'] = $row['config_id'];
            $object['broker_name'] = $row['config_name'];
            $object['poller_id'] = $this->engine['id'];
            $object['poller_name'] = $this->engine['name'];
            $object['module_directory'] = $this->engine['broker_modules_path'];
            $object['log_timestamp'] = $row['config_write_timestamp'];
            $object['log_thread_id'] = $row['config_write_thread_id'];
            $object['event_queue_max_size'] = $row['event_queue_max_size'];
            $object['command_file'] = $row['command_file'];
            $object['cache_directory'] = $cache_directory;

            if ($row['daemon'] == '1') {
                $watchdog[] = array(
                    'cbd' => array(
                        'name' => $row['config_name'],
                        'configuration_file' => $this->engine['broker_cfg_path'] . '/' . $row['config_filename'],
                        'run' => 1,
                        'reload' => 1
                    )
                );
            }

            $this->stmt_broker_parameters->bindParam(':config_id', $row['config_id'], PDO::PARAM_INT);
            $this->stmt_broker_parameters->execute();
            $resultParameters = $this->stmt_broker_parameters->fetchAll(PDO::FETCH_GROUP | PDO::FETCH_ASSOC);

            # Flow parameters
            foreach ($resultParameters as $key => $value) {
                foreach ($value as $subvalue) {
                    if (!isset($subvalue['fieldIndex']) ||
                        $subvalue['fieldIndex'] == "" ||
                        is_null($subvalue['fieldIndex'])
                    ) {
                        if (in_array($subvalue['config_key'], $this->exclude_parameters)) {
                            continue;
                        } elseif (trim($subvalue['config_value']) == '' &&
                            !in_array(
                                $subvalue['config_key'],
                                $this->authorized_empty_field
                            )
                        ) {
                            continue;
                        } else if ($subvalue['config_key'] == 'category') {
                            $object[$subvalue['config_group_id']][$key]['filters'][][$subvalue['config_key']] =
                                $subvalue['config_value'];
                        } else {
                            $object[$subvalue['config_group_id']][$key][$subvalue['config_key']] =
                                $subvalue['config_value'];
                            // Let broker insert in index data in pollers
                            if ($subvalue['config_key'] == 'type' && $subvalue['config_value'] == 'storage'
                                && !$localhost) {
                                $object[$subvalue['config_group_id']][$key]['insert_in_index_data'] = 'yes';
                            }
                        }
                    } else {
                        $res = explode('__', $subvalue['config_key'], 3);
                        $object[$subvalue['config_group_id']][$key][$subvalue['fieldIndex']][$res[0]][$res[1]] =
                            $subvalue['config_value'];
                    }
                    $flow_count++;
                }
            }

            # Stats parameters
            if ($stats_activate == '1') {
                $object[$flow_count]['stats'] = array(
                    'type' => 'stats',
                    'name' => $config_name . '-stats',
                    'json_fifo' => $cache_directory . '/' . $config_name . '-stats.json',
                );
            }

            # Generate file
            $this->generateFile($object, true, 'centreonBroker');
            $this->writeFile($this->backend_instance->getPath());
        }
        $watchdog[] = array(
            'log' => '/var/log/centreon-broker/watchdog.log'
        );
        $this->generate_filename = 'watchdog.xml';
        $this->generateFile($watchdog, true, 'centreonbroker');
        $this->writeFile($this->backend_instance->getPath());
    }

    private function getEngineParameters($poller_id)
    {
        if (is_null($this->stmt_engine_parameters)) {
            $this->stmt_engine_parameters = $this->backend_instance->db->prepare("SELECT
              $this->attributes_engine_parameters
            FROM nagios_server
            WHERE id = :poller_id
            ");
        }
        $this->stmt_engine_parameters->bindParam(':poller_id', $poller_id, PDO::PARAM_INT);
        $this->stmt_engine_parameters->execute();
        try {
            $row = $this->stmt_engine_parameters->fetch(PDO::FETCH_ASSOC);
            $this->engine['id'] = $row['id'];
            $this->engine['name'] = $row['name'];
            $this->engine['broker_modules_path'] = $row['centreonbroker_module_path'];
            $this->engine['broker_cfg_path'] = $row['centreonbroker_cfg_path'];
        } catch (Exception $e) {
            throw new Exception('Exception received : ' . $e->getMessage() . "\n");
        }
    }

    public function generateFromPoller($poller)
    {
        $this->generate($poller['id'], $poller['localhost']);
    }
}
