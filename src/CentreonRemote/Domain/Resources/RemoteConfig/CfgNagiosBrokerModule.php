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
class CfgNagiosBrokerModule
{
    /**
     * Get template configuration
     * @todo move it as yml
     *
     * @param int $configID the broker config id
     * @param string $pollerName the poller name
     * @return array<int, array<string,string|int>> the configuration template
     */
    public static function getConfiguration(int $configID, string $pollerName): array
    {
        $pollerName = strtolower(str_replace(' ', '-', $pollerName));

        return [
            [
                'cfg_nagios_id' => $configID,
                'broker_module' => "/usr/lib64/nagios/cbmod.so /etc/centreon-broker/{$pollerName}-module.json",
            ],
            [
                'cfg_nagios_id' => $configID,
                'broker_module' => '/usr/lib64/centreon-engine/externalcmd.so',
            ],
        ];
    }
}
