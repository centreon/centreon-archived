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

use CentreonRemote\Domain\Resources\RemoteConfig\BrokerInfo\InputBroker;
use CentreonRemote\Domain\Resources\RemoteConfig\BrokerInfo\InputRrd;
use CentreonRemote\Domain\Resources\RemoteConfig\BrokerInfo\OutputRrd;
use CentreonRemote\Domain\Resources\RemoteConfig\BrokerInfo\OutputRrdMaster;
use CentreonRemote\Domain\Resources\RemoteConfig\BrokerInfo\OutputForwardMaster;
use CentreonRemote\Domain\Resources\RemoteConfig\BrokerInfo\OutputModuleMaster;
use CentreonRemote\Domain\Resources\RemoteConfig\BrokerInfo\OutputUnifiedSql;

/**
 * Get broker configuration template
 */
class CfgCentreonBrokerInfo
{
    /**
     * Get template configuration
     * @todo move it as yml
     *
     * @param string $serverName the poller name
     * @param string|null $dbUser the database user
     * @param string|null $dbPassword the database password
     * @return array<string, array<string, array<int, array<string>>>> the configuration template
     */
    public static function getConfiguration(string $serverName, $dbUser, $dbPassword): array
    {
        $serverName = strtolower(str_replace(' ', '-', $serverName));

        $data = [
            'central-broker' => [
                'broker'          => InputBroker::getConfiguration(),
                'output_rrd'      => OutputRrdMaster::getConfiguration(),
                'output_forward'  => OutputForwardMaster::getConfiguration(),
                'output_unified_sql' => OutputUnifiedSql::getConfiguration($dbUser, $dbPassword),
            ],
            'central-module' => [
                'output' => OutputModuleMaster::getConfiguration(),
            ],
            'central-rrd' => [
                'input'  => InputRrd::getConfiguration(),
                'output' => OutputRrd::getConfiguration(),
            ]
        ];
        return $data;
    }
}
