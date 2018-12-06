<?php

namespace CentreonRemote\Domain\Resources\RemoteConfig;

/**
 * Get broker configuration template
 */
class NagiosServer
{
    /**
     * Get template configuration
     * @todo move it as yml
     *
     * @param string $name the poller name
     * @param string $ip the poller ip address
     * @return array the configuration template
     */
    public static function getConfiguration (string $name, string $ip): array
    {
        return [
            'name'                       => $name,
            'localhost'                  => '0',
            'is_default'                 => '0',
            'ns_ip_address'              => $ip,
            'ns_activate'                => '1',
            'ns_status'                  => '0',
            'init_script'                => 'centengine',
            'init_system'                => 'systemv',
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
            'centreonbroker_logs_path'   => '/var/log/centreon-broker/',
        ];
    }
}


