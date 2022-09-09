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
class LoggerSql
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
                'config_value'    => '/var/log/centreon-broker/broker-sql.log',
                'config_group'    => 'logger',
                'config_group_id' => '0',
                'grp_level'       => '0',
            ],
            [
                'config_key'      => 'config',
                'config_value'    => 'yes',
                'config_group'    => 'logger',
                'config_group_id' => '0',
                'grp_level'       => '0',
            ],
            [
                'config_key'      => 'debug',
                'config_value'    => 'no',
                'config_group'    => 'logger',
                'config_group_id' => '0',
                'grp_level'       => '0',
            ],
            [
                'config_key'      => 'error',
                'config_value'    => 'yes',
                'config_group'    => 'logger',
                'config_group_id' => '0',
                'grp_level'       => '0',
            ],
            [
                'config_key'      => 'info',
                'config_value'    => 'no',
                'config_group'    => 'logger',
                'config_group_id' => '0',
                'grp_level'       => '0',
            ],
            [
                'config_key'      => 'level',
                'config_value'    => 'low',
                'config_group'    => 'logger',
                'config_group_id' => '0',
                'grp_level'       => '0',
            ],
            [
                'config_key'      => 'max_size',
                'config_value'    => '',
                'config_group'    => 'logger',
                'config_group_id' => '0',
                'grp_level'       => '0',
            ],
            [
                'config_key'      => 'type',
                'config_value'    => 'file',
                'config_group'    => 'logger',
                'config_group_id' => '0',
                'grp_level'       => '0',
            ],
            [
                'config_key'      => 'blockId',
                'config_value'    => '3_17',
                'config_group'    => 'logger',
                'config_group_id' => '0',
                'grp_level'       => '0',
            ],
        ];
    }
}
