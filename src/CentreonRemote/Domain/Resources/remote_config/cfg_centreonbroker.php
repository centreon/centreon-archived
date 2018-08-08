<?php

return function ($pollerName) {
    $pollerName = str_replace(' ', '_', $pollerName);

    return [
        'config_name'            => "{$pollerName} module",
        'config_filename'        => "{$pollerName}-module.xml",
        'config_write_timestamp' => '1',
        'config_write_thread_id' => '1',
        'config_activate'        => '1',
        'ns_nagios_server'       => '1',
        'event_queue_max_size'   => '100000',
        'command_file'           => '',
        'cache_directory'        => '/var/lib/centreon-broker',
        'stats_activate'         => '1',
        'correlation_activate'   => '0',
        'daemon'                 => '0',
    ];
};
