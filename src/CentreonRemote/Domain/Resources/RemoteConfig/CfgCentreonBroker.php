<?php

namespace CentreonRemote\Domain\Resources\RemoteConfig;

class CfgCentreonBroker
{
    public static function getConfiguration ($serverID, $pollerName)
    {
        $configName = strtolower(str_replace(' ', '-', $pollerName));

        return [
            'broker' => [
                'config_name'            => "{$configName}-broker",
                'config_filename'        => "{$configName}-broker.xml",
                'config_write_timestamp' => '0',
                'config_write_thread_id' => '0',
                'config_activate'        => '1',
                'ns_nagios_server'       => $serverID,
                'event_queue_max_size'   => '100000',
                'command_file'           => '',
                'cache_directory'        => '/var/lib/centreon-broker',
                'stats_activate'         => '1',
                'correlation_activate'   => '0',
                'daemon'                 => '1',
            ],
            'module' => [
                'config_name'            => "{$configName}-module",
                'config_filename'        => "{$configName}-module.xml",
                'config_write_timestamp' => '0',
                'config_write_thread_id' => '0',
                'config_activate'        => '1',
                'ns_nagios_server'       => $serverID,
                'event_queue_max_size'   => '100000',
                'command_file'           => '',
                'cache_directory'        => '/var/lib/centreon-engine',
                'stats_activate'         => '1',
                'correlation_activate'   => '0',
                'daemon'                 => '0',
            ],
            'rrd' => [
                'config_name'            => "{$configName}-rrd",
                'config_filename'        => "{$configName}-rrd.xml",
                'config_write_timestamp' => '0',
                'config_write_thread_id' => '0',
                'config_activate'        => '1',
                'ns_nagios_server'       => $serverID,
                'event_queue_max_size'   => '100000',
                'command_file'           => '',
                'cache_directory'        => '/var/lib/centreon-broker',
                'stats_activate'         => '1',
                'correlation_activate'   => '0',
                'daemon'                 => '1',
            ]
        ];
    }
}
