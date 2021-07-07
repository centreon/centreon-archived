<?php

namespace CentreonRemote\Domain\Resources\RemoteConfig;

/**
 * Get broker configuration template
 */
class CfgCentreonBroker
{
    /**
     * Get template configuration
     * @todo move it as yml
     *
     * @param int $serverID the poller id
     * @param string $pollerName the poller name
     * @return array the configuration template
     */
    public static function getConfiguration(int $serverID, string $pollerName): array
    {
        $configName = strtolower(str_replace(' ', '-', $pollerName));

        return [
            'broker' => [
                'config_name'            => "{$configName}-broker",
                'config_filename'        => "{$configName}-broker.json",
                'config_write_timestamp' => '0',
                'config_write_thread_id' => '0',
                'config_activate'        => '1',
                'ns_nagios_server'       => $serverID,
                'event_queue_max_size'   => '100000',
                'command_file'           => '',
                'cache_directory'        => '/var/lib/centreon-broker',
                'log_directory'          => '/var/log/centreon-broker',
                'stats_activate'         => '1',
                'daemon'                 => '1',
            ],
            'module' => [
                'config_name'            => "{$configName}-module",
                'config_filename'        => "{$configName}-module.json",
                'config_write_timestamp' => '0',
                'config_write_thread_id' => '0',
                'config_activate'        => '1',
                'ns_nagios_server'       => $serverID,
                'event_queue_max_size'   => '100000',
                'command_file'           => '',
                'cache_directory'        => '/var/lib/centreon-engine',
                'log_directory'          => '/var/log/centreon-broker',
                'stats_activate'         => '1',
                'daemon'                 => '0',
            ],
            'rrd' => [
                'config_name'            => "{$configName}-rrd",
                'config_filename'        => "{$configName}-rrd.json",
                'config_write_timestamp' => '0',
                'config_write_thread_id' => '0',
                'config_activate'        => '1',
                'ns_nagios_server'       => $serverID,
                'event_queue_max_size'   => '100000',
                'command_file'           => '',
                'cache_directory'        => '/var/lib/centreon-broker',
                'log_directory'          => '/var/log/centreon-broker',
                'stats_activate'         => '1',
                'daemon'                 => '1',
            ]
        ];
    }
}
