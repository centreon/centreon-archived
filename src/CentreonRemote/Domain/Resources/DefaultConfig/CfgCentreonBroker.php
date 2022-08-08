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

namespace CentreonRemote\Domain\Resources\DefaultConfig;

/**
 * Get broker configuration template
 */
class CfgCentreonBroker
{
    /**
     * Get template configuration
     * @todo move it as yml
     *
     * @return array<int, array<string,int|string>> the configuration template
     */
    public static function getConfiguration(): array
    {
        return [
            [
                'config_id'              => 1,
                'config_name'            => 'central-broker-master',
                'config_filename'        => 'central-broker.json',
                'config_write_timestamp' => '1',
                'config_write_thread_id' => '0',
                'config_activate'        => '1',
                'event_queue_max_size'   => 100000,
                'cache_directory'        => '@centreonbroker_varlib@',
                'command_file'           => '@centreonbroker_varlib@/command.sock',
                'daemon'                 => 1,
            ],
            [
                'config_id'              => 2,
                'config_name'            => 'central-rrd-master',
                'config_filename'        => 'central-rrd.json',
                'config_write_timestamp' => '1',
                'config_write_thread_id' => '0',
                'config_activate'        => '1',
                'ns_nagios_server'       => 1,
                'event_queue_max_size'   => 100000,
                'cache_directory'        => '@centreonbroker_varlib@',
                'daemon'                 => 1,
            ],
            [
                'config_id'              => 3,
                'config_name'            => 'central-module-master',
                'config_filename'        => 'central-module.json',
                'config_write_timestamp' => '0',
                'config_write_thread_id' => '0',
                'config_activate'        => '1',
                'ns_nagios_server'       => 1,
                'event_queue_max_size'   => 100000,
                'cache_directory'        => '@monitoring_var_lib@',
                'daemon'                 => 0,
            ],
        ];
    }
}
