<?php

return function ($name, $ip) {
    return [
        'name'                       => $name,
        'localhost'                  => '0',
        'is_default'                 => '0',
        'ns_ip_address'              => $ip,
        'ns_activate'                => '1',
        'ns_status'                  => '0',
        'init_script'                => 'centengine',
        'init_system'                => null,
        'monitoring_engine'          => null,
        'nagios_bin'                 => '/usr/sbin/centengine',
        'nagiostats_bin'             => '/usr/sbin/centenginestats',
        'nagios_perfdata'            => '/var/log/centreon-engine/service-perfdata',
        'centreonbroker_cfg_path'    => '/etc/centreon-broker',
        'centreonbroker_module_path' => '/usr/share/centreon/lib/centreon-broker',
        'centreonconnector_path'     => null,
        'ssh_port'                   => '22',
        'ssh_private_key'            => null,
        'init_script_centreontrapd'  => 'centreontrapd',
        'snmp_trapd_path_conf'       => '/etc/snmp/centreon_traps/',
        'centreonbroker_logs_path'   => '/var/log/centreon-broker/',
    ];
};
