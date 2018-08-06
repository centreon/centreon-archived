<?php

// This is a Centreon Poller/Remote

// The only difference of configuration between a Centreon Central Server
// and a Centreon Remote Server is the “Output IPv4” configuration
// on “Central Broker” to forward all events received on Remote Server to the Centreon Central Server.

$data = [
    'name'                       => '', //TODO input
    'localhost'                  => '0',
    'is_default'                 => '0',
    'ns_ip_address'              => '', //TODO input
    'ns_activate'                => '1',
    'ns_status'                  => '0',
    'init_script'                => 'centengine',
    'init_system'                => null,
    'monitoring_engine'          => 'CENGINE',
    'nagios_bin'                 => '/usr/sbin/centengine',
    'nagiostats_bin'             => '/usr/sbin/centenginestats',
    'nagios_perfdata'            => '/var/log/centreon-engine/service-perfdata',
    'centreonbroker_cfg_path'    => '/etc/centreon-broker',
    'centreonbroker_module_path' => '/usr/share/centreon/lib/centreon-broker',
    'centreonconnector_path'     => '/usr/lib64/centreon-connector',
    'ssh_port'                   => '22',
    'ssh_private_key'            => null,
    'init_script_centreontrapd'  => 'centreontrapd',
    'snmp_trapd_path_conf'       => '/etc/snmp/centreon_traps/',
    'centreonbroker_logs_path'   => '/var/log/centreon-broker/watchdog.log',
];

// `name`
// `localhost`
// `is_default`
// `ns_ip_address`
// `ns_activate`
// `ns_status`
// `init_script`
// `init_system`
// `monitoring_engine`
// `nagios_bin`
// `nagiostats_bin`
// `nagios_perfdata`
// `centreonbroker_cfg_path`
// `centreonbroker_module_path`
// `centreonconnector_path`
// `ssh_port`
// `ssh_private_key`
// `init_script_centreontrapd`
// `snmp_trapd_path_conf`
// `centreonbroker_logs_path`
