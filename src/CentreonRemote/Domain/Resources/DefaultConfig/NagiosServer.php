<?php

namespace CentreonRemote\Domain\Resources\DefaultConfig;

/**
 * Get broker configuration template
 */
class NagiosServer
{
    /**
     * Get template configuration
     * @todo move it as yml
     *
     * @return array the configuration template
     */
    public static function getConfiguration(): array
    {
        return [
            'name'                       => 'Central',
            'localhost'                  => '1',
            'is_default'                 => 1,
            'last_restart'               => 0,
            'ns_ip_address'              => '127.0.0.1',
            'ns_activate'                => '1',
            'ns_status'                  => '0',
            'init_script'                => '@monitoring_init_script@',
            'monitoring_engine'          => 'CENGINE',
            'nagios_bin'                 => '@monitoring_binary@',
            'nagiostats_bin'             => '@centreon_engine_stats_binary@',
            'nagios_perfdata'            => '@monitoring_varlog@/service-perfdata',
            'centreonbroker_cfg_path'    => '@broker_etc@',
            'centreonbroker_module_path' => '@centreonbroker_lib@',
            'centreonconnector_path'     => '@centreon_engine_connectors@',
            'ssh_port'                   => 22,
            'ssh_private_key'            => null,
            'init_script_centreontrapd'  => 'centreontrapd',
            'snmp_trapd_path_conf'       => '/etc/snmp/centreon_traps/',
        ];
    }
}
