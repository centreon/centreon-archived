<?php

return function ($serverID, $pollerName) {
    $configName = strtolower(str_replace(' ', '-', $pollerName));

    return [
        [
            'config_name'            => "central-broker-{$configName}",
            'config_filename'        => "{$configName}-broker.xml",
            'config_write_timestamp' => '0',
            'config_write_thread_id' => '0',
            'config_activate'        => '1',
            'ns_nagios_server'       => $serverID,
            'event_queue_max_size'   => '100000',
            'command_file'           => '',
            'cache_directory'        => '/var/lib/centreon-broker',
            'stats_activate'         => '0',
            'correlation_activate'   => '0',
            'daemon'                 => '1',
        ],
        [
            'config_name'            => "central-module-{$configName}",
            'config_filename'        => "{$configName}-module.xml",
            'config_write_timestamp' => '0',
            'config_write_thread_id' => '0',
            'config_activate'        => '1',
            'ns_nagios_server'       => $serverID,
            'event_queue_max_size'   => '100000',
            'command_file'           => '',
            'cache_directory'        => '/var/lib/centreon-engine',
            'stats_activate'         => '0',
            'correlation_activate'   => '0',
            'daemon'                 => '0',
        ],
        [
            'config_name'            => "central-rrd-{$configName}",
            'config_filename'        => "{$configName}-rrd.xml",
            'config_write_timestamp' => '0',
            'config_write_thread_id' => '0',
            'config_activate'        => '1',
            'ns_nagios_server'       => $serverID,
            'event_queue_max_size'   => '100000',
            'command_file'           => '',
            'cache_directory'        => '/var/lib/centreon-broker',
            'stats_activate'         => '0',
            'correlation_activate'   => '0',
            'daemon'                 => '1',
        ]
    ];
};
