<?php

/*
 * Copyright 2005 - 2022 Centreon (https://www.centreon.com/)
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * For more information : contact@centreon.com
 *
 */

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
     * @return array<string, array<string, int|string>> the configuration template
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
