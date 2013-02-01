<?php
/*
 * Copyright 2005-2012 MERETHIS
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
 * As a special exception, the copyright holders of this program give MERETHIS
 * permission to link this program with independent modules to produce an executable,
 * regardless of the license terms of these independent modules, and to copy and
 * distribute the resulting executable under terms of MERETHIS choice, provided that
 * MERETHIS also meet, for each linked independent module, the terms  and conditions
 * of the license of that module. An independent module is a module which is not
 * derived from this program. If you modify this program, you may extend this
 * exception to your version of the program, but you are not obliged to do so. If you
 * do not wish to do so, delete this exception statement from your version.
 *
 * For more information : contact@centreon.com
 *
 * SVN : $URL$
 * SVN : $Id$
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
                        'protocol' => 'ndo',
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
                        'protocol' => 'ndo',
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
                        'protocol' => 'ndo',
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
                        'protocol' => 'ndo',
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
                        'protocol' => 'ndo',
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
                        'protocol' => 'ndo',
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
                        'protocol' => 'ndo',
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
                        'protocol' => 'ndo',
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
                        'protocol' => 'ndo',
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