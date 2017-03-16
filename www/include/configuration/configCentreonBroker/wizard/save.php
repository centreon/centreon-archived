<?php
/*
 * Copyright 2005-2012 Centreon
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

if (!isset($centreon)) {
    exit();
}

require_once './class/centreonConfigCentreonBroker.php';

$central_module_configuration = array(
    'name' => $wizard->getValue(2, 'prefix_configname') . '-module-master',
    'filename' => 'central-module.xml',
    'activate' => array('activate' => 0),
    'activate_watchdog' => array('activate_watchdog' => 0),
    'write_timestamp' => array('write_timestamp' => 1),
    'write_thread_id' => array('write_thread_id' => 1),
    'ns_nagios_server' => $wizard->getValue(2, 'requester_id'),
    'cache_directory' => '/var/lib/centreon-broker',
    'logger' => array(
        array(
            'name' => '/var/log/centreon-broker/central-module.log',
            'config' => array('config' => 'yes'),
            'debug' => array('debug' => 'no'),
            'error' => array('error' => 'yes'),
            'info' => array('info' => 'no'),
            'level' => 'low',
            'type' => 'file',
            'blockId' => '3_17',
        )
    ),
    'output' => array(
        array(
            'name' => 'Central',
            'port' => '5669',
            'failover' => '',
            'retry_interval' => '',
            'buffering_timeout' => '',
            'host' => '127.0.0.1',
            'protocol' => 'bbdo',
            'negociation' => array('negociation' => 'yes'),
            'tls' => array('tls' => 'no'),
            'private_key' => '',
            'public_cert' => '',
            'ca_certificate' => '',
            'compression' => array('compression' => 'auto'),
            'compression_level' => '',
            'compression_buffer' => '',
            'type' => 'ipv4',
            'blockId' => '1_3',
        )
    )
);

$poller_module_configuration = array(
    'name' => $wizard->getValue(2, 'configname') . '-module',
    'filename' => 'poller-module.xml',
    'activate' => array('activate' => 0),
    'activate_watchdog' => array('activate_watchdog' => 0),
    'write_timestamp' => array('write_timestamp' => 1),
    'write_thread_id' => array('write_thread_id' => 1),
    'ns_nagios_server' => $wizard->getValue(2, 'requester_id'),
    'cache_directory' => '/var/lib/centreon-broker',
    'logger' => array(
        array(
            'name' => '/var/log/centreon-broker/poller-module.log',
            'config' => array('config' => 'yes'),
            'debug' => array('debug' => 'no'),
            'error' => array('error' => 'yes'),
            'info' => array('info' => 'no'),
            'level' => 'low',
            'type' => 'file',
            'blockId' => '3_17',
        )
    ),
    'output' => array(
        array(
            'name' => 'Central',
            'port' => '5669',
            'failver' => '',
            'retry_interval' => '',
            'buffering_timeout' => '',
            'host' => $wizard->getValue(2, 'central_address'),
            'protocol' => 'bbdo',
            'negociation' => array('negociation' => 'yes'),
            'tls' => array('tls' => 'no'),
            'private_key' => '',
            'public_cert' => '',
            'ca_certificate' => '',
            'compression' => array('compression' => 'auto'),
            'compression_level' => '',
            'compression_buffer' => '',
            'type' => 'ipv4',
            'blockId' => '1_3',
        )
    )
);

$central_broker_configuration = array(
    'name' => $wizard->getValue(2, 'prefix_configname') . '-broker-master',
    'filename' => 'central-broker.xml',
    'activate' => array('activate' => 0),
    'activate_watchdog' => array('activate_watchdog' => 1),
    'write_timestamp' => array('write_timestamp' => 1),
    'write_thread_id' => array('write_thread_id' => 1),
    'ns_nagios_server' => $wizard->getValue(2, 'requester_id'),
    'cache_directory' => '/var/lib/centreon-broker',
    'event_queue_max_size' => '100000',
    'logger' => array(
        array(
            'name' => '/var/log/centreon-broker/central-broker-master.log',
            'config' => array('config' => 'yes'),
            'debug' => array('debug' => 'no'),
            'error' => array('error' => 'yes'),
            'info' => array('info' => 'no'),
            'level' => 'low',
            'type' => 'file',
            'blockId' => '3_17',
        )
    ),
    'input' => array(
        array(
            'name' => $wizard->getValue(2, 'prefix_configname') . '-broker-master-input',
            'port' => '5669',
            'retry_interval' => '60',
            'buffering_timeout' => '0',
            'host' => '',
            'protocol' => 'bbdo',
            'negociation' => array('negociation' => 'yes'),
            'tls' => array('tls' => 'no'),
            'private_key' => '',
            'public_cert' => '',
            'ca_certificate' => '',
            'compression' => array('compression' => 'auto'),
            'compression_level' => '',
            'compression_buffer' => '',
            'type' => 'ipv4',
            'blockId' => '2_3',
        )
    ),
    'output' => array(
        array(
            'name' => $wizard->getValue(2, 'prefix_configname') . '-broker-master-sql',
            'db_type' => 'mysql',
            'failover' => '',
            'db_host' => $conf_centreon['hostCentstorage'],
            'retry_interval' => '60',
            'buffering_timeout' => '0',
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
            'name' =>  $wizard->getValue(2, 'prefix_configname') . '-broker-master-perfdata',
            'interval' => '300',
            'length' => '15552000',
            'failover' => '',
            'retry_interval' => '60',
            'buffering_timeout' => '0',
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
            'name' => $wizard->getValue(2, 'prefix_configname') . '-broker-master-rrd',
            'port' => '5670',
            'failover' => '',
            'retry_interval' => '60',
            'buffering_timeout' => '0',
            'host' => '127.0.0.1',
            'protocol' => 'bbdo',
            'negociation' => array('negociation' => 'yes'),
            'tls' => array('tls' => 'no'),
            'private_key' => '',
            'public_cert' => '',
            'ca_certificate' => '',
            'compression' => array('compression' => 'auto'),
            'compression_level' => '',
            'compression_buffer' => '',
            'type' => 'ipv4',
            'blockId' => '1_3',
        )
    )
);

$central_rrd_configuration = array(
    'name' => $wizard->getValue(2, 'prefix_configname') . '-rrd-master',
    'filename' => 'central-rrd.xml',
    'activate' => array('activate' => 0),
    'activate_watchdog' => array('activate_watchdog' => 1),
    'write_timestamp' => array('write_timestamp' => 1),
    'write_thread_id' => array('write_thread_id' => 1),
    'ns_nagios_server' => $wizard->getValue(2, 'requester_id'),
    'cache_directory' => '/var/lib/centreon-broker',
    'event_queue_max_size' => '100000',
    'logger' => array(
        array(
            'name' => '/var/log/centreon-broker/central-rrd-master.log',
            'config' => array('config' => 'yes'),
            'debug' => array('debug' => 'no'),
            'error' => array('error' => 'yes'),
            'info' => array('info' => 'no'),
            'level' => 'low',
            'type' => 'file',
            'blockId' => '3_17',
        )
    ),
    'input' => array(
        array(
            'name' => $wizard->getValue(2, 'prefix_configname') . '-rrd-master-input',
            'port' => '5670',
            'retry_interval' => '60',
            'buffering_timeout' => '0',
            'host' => '',
            'protocol' => 'bbdo',
            'negociation' => array('negociation' => 'yes'),
            'tls' => array('tls' => 'no'),
            'private_key' => '',
            'public_cert' => '',
            'ca_certificate' => '',
            'compression' => array('compression' => 'auto'),
            'compression_level' => '',
            'compression_buffer' => '',
            'type' => 'ipv4',
            'blockId' => '2_3',
        )
    ),
    'output' => array(
        array(
            'name' => 'RRD',
            'metrics_path' => '/var/lib/centreon/metrics',
            'status_path' => '/var/lib/centreon/status',
            'retry_interval' => '60',
            'buffering_timeout' => '0',
            'path' => '',
            'port' => '',
            'type' => 'rrd',
            'blockId' => '1_13',
        )
    )
);

/*
 * Get values
 */
$cBroker = new CentreonConfigCentreonBroker($pearDB);
switch ($wizard->getValue(1, 'configtype')) {
    case 'central':
        $sName = $central_module_configuration['name'];
        $iExist = $cBroker->isExist($sName);
        
        if ($iExist > 0) {
            $msgErr[] = _("The config name already exists");
            break;
        }
        if (false === $cBroker->insertConfig($central_module_configuration)) {
            $msgErr[] = _('Error while inserting central-module configuration');
            break;
        }
        if (false === $cBroker->insertConfig($central_broker_configuration)) {
            $msgErr[] = _('Error while inserting central-broker configuration');
            break;
        }
        if (false === $cBroker->insertConfig($central_rrd_configuration)) {
            $msgErr[] = _('Error while inserting central-rrd configuration');
            break;
        }
        break;
    case 'poller':
        $sName = $poller_module_configuration['name'];
        $iExist = $cBroker->isExist($sName);

        if ($iExist > 0) {
            $msgErr[] = _("The config name already exists") . ": ".$sName;
            break;
        }
        
        if (false === $cBroker->insertConfig($poller_module_configuration)) {
            $msgErr[] = _('Error while inserting poller-module configuration');
            break;
        }
        break;
    default:
        $msgErr[] = _('Bad configuration type');
}
