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

namespace CentreonRemote\Domain\Resources\RemoteConfig\BrokerInfo;

/**
 * Get broker configuration template
 */
class OutputRrd
{
    /**
     * Get template configuration
     * @todo move it as yml
     *
     * @return array<int, string[]> the configuration template
     */
    public static function getConfiguration(): array
    {
        return [
            [
                'config_key'      => 'name',
                'config_value'    => 'central-rrd-master-output',
                'config_group'    => 'output',
                'config_group_id' => '0',
                'grp_level'       => '0',
            ],
            [
                'config_key'      => 'metrics_path',
                'config_value'    => '/var/lib/centreon/metrics/',
                'config_group'    => 'output',
                'config_group_id' => '0',
                'grp_level'       => '0',
            ],
            [
                'config_key'      => 'failover',
                'config_value'    => '',
                'config_group'    => 'output',
                'config_group_id' => '0',
                'grp_level'       => '0',
            ],
            [
                'config_key'      => 'status_path',
                'config_value'    => '/var/lib/centreon/status/',
                'config_group'    => 'output',
                'config_group_id' => '0',
                'grp_level'       => '0',
            ],
            [
                'config_key'      => 'retry_interval',
                'config_value'    => '15',
                'config_group'    => 'output',
                'config_group_id' => '0',
                'grp_level'       => '0',
            ],
            [
                'config_key'      => 'buffering_timeout',
                'config_value'    => '0',
                'config_group'    => 'output',
                'config_group_id' => '0',
                'grp_level'       => '0',
            ],
            [
                'config_key'      => 'path',
                'config_value'    => '',
                'config_group'    => 'output',
                'config_group_id' => '0',
                'grp_level'       => '0',
            ],
            [
                'config_key'      => 'port',
                'config_value'    => '',
                'config_group'    => 'output',
                'config_group_id' => '0',
                'grp_level'       => '0',
            ],
            [
                'config_key'      => 'write_metrics',
                'config_value'    => 'yes',
                'config_group'    => 'output',
                'config_group_id' => '0',
                'grp_level'       => '0',
            ],
            [
                'config_key'      => 'write_status',
                'config_value'    => 'yes',
                'config_group'    => 'output',
                'config_group_id' => '0',
                'grp_level'       => '0',
            ],
            [
                'config_key'      => 'store_in_data_bin',
                'config_value'    => 'yes',
                'config_group'    => 'output',
                'config_group_id' => '0',
                'grp_level'       => '0',
            ],
            [
                'config_key'      => 'insert_in_index_data',
                'config_value'    => '1',
                'config_group'    => 'output',
                'config_group_id' => '0',
                'grp_level'       => '0',
            ],
            [
                'config_key'      => 'type',
                'config_value'    => 'rrd',
                'config_group'    => 'output',
                'config_group_id' => '0',
                'grp_level'       => '0',
            ],
            [
                'config_key'      => 'blockId',
                'config_value'    => '1_13',
                'config_group'    => 'output',
                'config_group_id' => '0',
                'grp_level'       => '0',
            ],
        ];
    }
}
