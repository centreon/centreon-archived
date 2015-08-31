<?php
/*
 * Copyright 2015 Centreon (http://www.centreon.com/)
 * 
 * Centreon is a full-fledged industry-strength solution that meets 
 * the needs in IT infrastructure and application monitoring for 
 * service performance.
 * 
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * 
 *    http://www.apache.org/licenses/LICENSE-2.0  
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

    if (!isset($oreon)) {
        exit();
    }

    require_once './class/centreonConfigCentreonBroker.php';

    /*
     * Get values
     */
    $cBroker = new CentreonConfigCentreonBroker($pearDB);
    switch ($wizard->getValue(1, 'configtype'))  {
        case 'central_without_poller':
            $configuration = array(
                'name' => $wizard->getValue(2, 'configname'),
                'filename' => 'central-broker.xml',
                'activate' => array('activate' => 0),
		        'write_timestamp' => array('write_timestamp' => 1),
		        'write_thread_id' => array('write_thread_id' => 1),
                'ns_nagios_server' => $wizard->getValue(2, 'requester_id'),
                'logger' => array(
                    array (
                        'name' => '/var/log/centreon-broker/central-broker-master.log',
                        'config' => array ('config' => 'yes'),
                        'debug' => array ('debug' => 'no'),
                        'error' => array ('error' => 'yes'),
                        'info' => array ('info' => 'no'),
                        'level' => 'low',
                        'type' => 'file',
                        'blockId' => '3_17',
                    )
                ),
                'output' => array(
                    array (
                        'name' => 'Storage',
                        'db_type' => 'mysql',
                        'failover' => 'Storage-failover',
                        'db_host' => $conf_centreon['hostCentstorage'],
                        'retry_interval' => '',
                        'buffering_timeout' => '',
                        'db_port' => '3306',
                        'db_user' => $conf_centreon['user'],
                        'db_password' => $conf_centreon['password'],
                        'db_name' => $conf_centreon['dbcstg'],
                        'queries_per_transaction' => '5000',
                        'read_timeout' => '5',
                        'type' => 'sql',
                        'blockId' => '1_16',
                    ),
                    array(
                        'name' => 'Storage-failover',
                        'failover' => '',
                        'path' => '/var/lib/centreon-broker/central-broker-sql-master.retention',
                        'retry_interval' => '',
                        'buffering_timeout' => '',
                        'protocol' => $wizard->getValue(2, 'protocol'),
                        'compression' => array ('compression' => 'no'),
                        'compression_level' => '',
                        'compression_buffer' => '',
                        'type' => 'file',
                        'blockId' => '1_11'
                    ),
                    array (
                        'name' => 'PerfData',
                        'interval' => '300',
                        'length' => '15552000',
                        'failover' => 'PerfData-failover',
                        'retry_interval' => '',
                        'buffering_timeout' => '',
                        'db_type' => 'mysql',
                        'db_host' => $conf_centreon['hostCentstorage'],
                        'db_port' => '3306',
                        'db_user' => $conf_centreon['user'],
                        'db_password' => $conf_centreon['password'],
                        'db_name' => $conf_centreon['dbcstg'],
                        'queries_per_transaction' => '5000',
                        'read_timeout' => '5',
                        'type' => 'storage',
                        'blockId' => '1_14',
                    ),
                    array(
                        'name' => 'PerfData-failover',
                        'failover' => '',
                        'path' => '/var/lib/centreon-broker/central-broker-perfdata-master.retention',
                        'retry_interval' => '',
                        'buffering_timeout' => '',
                        'protocol' => $wizard->getValue(2, 'protocol'),
                        'compression' => array ('compression' => 'no'),
                        'compression_level' => '',
                        'compression_buffer' => '',
                        'type' => 'file',
                        'blockId' => '1_11'
                    ),
                    array (
                        'name' => 'RRD',
                        'metrics_path' => '/var/lib/centreon/metrics',
                        'retry_interval' => '',
                        'buffering_timeout' => '',
                        'status_path' => '/var/lib/centreon/status',
                        'failover' => '',
                        'path' => '',
                        'port' => '',
                        'type' => 'rrd',
                        'blockId' => '1_13',
                    )
                ),
                'stats' => array(
                    array (
                        'name' => 'StatisticFile',
                        'fifo' => '/var/log/centreon-broker/central-module.stats',
                        'type' => 'stats',
						'blockId' => '5_23',
                    )
                )
            );
            if (false === $cBroker->insertConfig($configuration)) {
                $msgErr[] = _('Error while inserting central-module configuration');
            }
            break;
        case 'central_with_poller':
            $configuration = array(
                'name' => $wizard->getValue(2, 'prefix_configname') . '_cbd',
                'filename' => 'central-cbd.xml',
                'activate' => array('activate' => 0),
		        'write_timestamp' => array('write_timestamp' => 1),
		        'write_thread_id' => array('write_thread_id' => 1),
                'ns_nagios_server' => $wizard->getValue(2, 'requester_id'),
                'logger' => array(
                    array (
                        'name' => '/var/log/centreon-broker/central-cbd.log',
                        'config' => array ('config' => 'yes'),
                        'debug' => array ('debug' => 'no'),
                        'error' => array ('error' => 'yes'),
                        'info' => array ('info' => 'no'),
                        'level' => 'low',
                        'type' => 'file',
                        'blockId' => '3_17',
                    )
                ),
                'input' => array(
                    array(
                        'name' => 'Central_In',
                        'port' => '5669',
                        'retry_interval' => '',
                        'buffering_timeout' => '',
                        'host' => '',
                        'protocol' => $wizard->getValue(2, 'protocol'),
                        'negociation' => array('negociation' => 'yes'),
                        'tls' => array ('tls' => 'no'),
                        'private_key' => '',
                        'public_cert' => '',
                        'ca_certificate' => '',
                        'compression' => array ('compression' => 'no'),
                        'compression_level' => '',
                        'compression_buffer' => '',
                        'type' => 'ipv4',
                        'blockId' => '2_3',
                    )
                ),
                'output' => array(
                    array (
                        'name' => 'Storage',
                        'db_type' => 'mysql',
                        'failover' => 'Storage-failover',
                        'db_host' => $conf_centreon['hostCentstorage'],
                        'retry_interval' => '',
                        'buffering_timeout' => '',
                        'db_port' => '3306',
                        'db_user' => $conf_centreon['user'],
                        'db_password' => $conf_centreon['password'],
                        'db_name' => $conf_centreon['dbcstg'],
                        'queries_per_transaction' => '5000',
                        'read_timeout' => '5',
                        'type' => 'sql',
                        'blockId' => '1_16',
                    ),
                    array(
                        'name' => 'Storage-failover',
                        'failover' => '',
                        'path' => '/var/lib/centreon-broker/central-broker-sql-cbd.retention',
                        'retry_interval' => '',
                        'buffering_timeout' => '',
                        'protocol' => $wizard->getValue(2, 'protocol'),
                        'compression' => array ('compression' => 'no'),
                        'compression_level' => '',
                        'compression_buffer' => '',
                        'type' => 'file',
                        'blockId' => '1_11'
                    ),
                    array (
                        'name' => 'PerfData',
                        'interval' => '300',
                        'length' => '15552000',
                        'failover' => 'PerfData-failover',
                        'retry_interval' => '',
                        'buffering_timeout' => '',
                        'db_type' => 'mysql',
                        'db_host' => $conf_centreon['hostCentstorage'],
                        'db_port' => '3306',
                        'db_user' => $conf_centreon['user'],
                        'db_password' => $conf_centreon['password'],
                        'db_name' => $conf_centreon['dbcstg'],
                        'queries_per_transaction' => '5000',
                        'read_timeout' => '5',
                        'type' => 'storage',
                        'blockId' => '1_14',
                    ),
                    array(
                        'name' => 'PerfData-failover',
                        'failover' => '',
                        'path' => '/var/lib/centreon-broker/central-broker-perfdata-cbd.retention',
                        'retry_interval' => '',
                        'buffering_timeout' => '',
                        'protocol' => $wizard->getValue(2, 'protocol'),
                        'compression' => array ('compression' => 'no'),
                        'compression_level' => '',
                        'compression_buffer' => '',
                        'type' => 'file',
                        'blockId' => '1_11'
                    ),
                    array (
                        'name' => 'RRD',
                        'metrics_path' => '/var/lib/centreon/metrics',
                        'retry_interval' => '',
                        'buffering_timeout' => '',
                        'status_path' => '/var/lib/centreon/status',
                        'failover' => '',
                        'path' => '',
                        'port' => '',
                        'type' => 'rrd',
                        'blockId' => '1_13',
                    )
                ),
                'stats' => array(
                    array (
                        'name' => 'StatisticFile',
                        'fifo' => '/var/log/centreon-broker/central-module.stats',
                        'type' => 'stats',
						'blockId' => '5_23',
                    )
                )
            );
            if (false === $cBroker->insertConfig($configuration)) {
                $msgErr[] = _('Error while inserting central-module configuration');
                break;
            }
            $configuration = array(
                'name' => $wizard->getValue(2, 'prefix_configname') . '_module',
                'filename' => 'central-module.xml',
                'activate' => array('activate' => 0),
		        'write_timestamp' => array('write_timestamp' => 1),
		        'write_thread_id' => array('write_thread_id' => 1),
                'ns_nagios_server' => $wizard->getValue(2, 'requester_id'),
                'logger' => array(
                    array (
                        'name' => '/var/log/centreon-broker/central-module.log',
                        'config' => array ('config' => 'yes'),
                        'debug' => array ('debug' => 'no'),
                        'error' => array ('error' => 'yes'),
                        'info' => array ('info' => 'no'),
                        'level' => 'low',
                        'type' => 'file',
                        'blockId' => '3_17',
                    )
                ),
                'output' => array(
                    array (
                        'name' => 'Central',
                        'port' => '5669',
                        'failover' => 'CentralFailover',
                        'retry_interval' => '',
                        'buffering_timeout' => '',
                        'host' => '127.0.0.1',
                        'protocol' => $wizard->getValue(2, 'protocol'),
                        'negociation' => array('negociation' => 'yes'),
                        'tls' => array ('tls' => 'no'),
                        'private_key' => '',
                        'public_cert' => '',
                        'ca_certificate' => '',
                        'compression' => array ('compression' => 'no'),
                        'compression_level' => '',
                        'compression_buffer' => '',
                        'type' => 'ipv4',
                        'blockId' => '1_3',
                    ),
                    array (
                        'name' => 'CentralFailover',
                        'path' => '/var/log/centreon-broker/central-retention.dat',
                        'failover' => '',
                        'retry_interval' => '',
                        'protocol' => $wizard->getValue(2, 'protocol'),
                        'buffering_timeout' => '',
                        'compression' => array ('compression' => 'no'),
                        'compression_level' => '',
                        'compression_buffer' => '',
                        'type' => 'file',
                        'blockId' => '1_11',
                    )
                ),
                'stats' => array(
                    array (
                        'name' => 'StatisticFile',
                        'fifo' => '/var/log/centreon-broker/poller-module.stats',
                        'type' => 'stats',
						'blockId' => '5_23',
                    )
                )
            );
            if (false === $cBroker->insertConfig($configuration)) {
                $msgErr[] = _('Error while inserting central-module configuration');
            }
            break;
        case 'poller':
            $configuration = array(
                'name' => $wizard->getValue(2, 'configname'),
                'filename' => 'poller-module.xml',
                'activate' => array('activate' => 0),
		        'write_timestamp' => array('write_timestamp' => 1),
		        'write_thread_id' => array('write_thread_id' => 1),
                'ns_nagios_server' => $wizard->getValue(2, 'requester_id'),
                'logger' => array(
                    array (
                        'name' => '/var/log/centreon-broker/poller-module.log',
                        'config' => array ('config' => 'yes'),
                        'debug' => array ('debug' => 'no'),
                        'error' => array ('error' => 'yes'),
                        'info' => array ('info' => 'no'),
                        'level' => 'low',
                        'type' => 'file',
                        'blockId' => '3_17',
                    )
                ),
                'output' => array(
                    array (
                        'name' => 'Central',
                        'port' => '5669',
                        'failover' => 'CentralFailover',
                        'retry_interval' => '',
                        'buffering_timeout' => '',
                        'host' => $wizard->getValue(2, 'central_address'),
                        'protocol' => $wizard->getValue(2, 'protocol'),
                        'negociation' => array('negociation' => 'yes'),
                        'tls' => array ('tls' => 'no'),
                        'private_key' => '',
                        'public_cert' => '',
                        'ca_certificate' => '',
                        'compression' => array ('compression' => 'no'),
                        'compression_level' => '',
                        'compression_buffer' => '',
                        'type' => 'ipv4',
                        'blockId' => '1_3',
                    ),
                    array (
                        'name' => 'CentralFailover',
                        'path' => '/var/log/centreon-broker/central-retention.dat',
                        'failover' => '',
                        'retry_interval' => '',
                        'protocol' => $wizard->getValue(2, 'protocol'),
                        'buffering_timeout' => '',
                        'compression' => array ('compression' => 'no'),
                        'compression_level' => '',
                        'compression_buffer' => '',
                        'type' => 'file',
                        'blockId' => '1_11',
                    )
                ),
                'stats' => array(
                    array (
                        'name' => 'StatisticFile',
                        'fifo' => '/var/log/centreon-broker/poller-module.stats',
                        'type' => 'stats',
						'blockId' => '5_23',
                    )
                )
            );
            if (false === $cBroker->insertConfig($configuration)) {
                $msgErr[] = _('Error while inserting central-module configuration');
            }
            break;
        default:
            $msgErr[] = _('Bad configuration type');
    }
