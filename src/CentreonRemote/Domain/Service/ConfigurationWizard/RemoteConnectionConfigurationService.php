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

namespace CentreonRemote\Domain\Service\ConfigurationWizard;

use CentreonRemote\Domain\Resources\RemoteConfig\CfgCentreonBroker;
use CentreonRemote\Domain\Resources\DefaultConfig\CfgCentreonBrokerLog;
use CentreonRemote\Domain\Resources\RemoteConfig\CfgCentreonBrokerInfo;

class RemoteConnectionConfigurationService extends ServerConnectionConfigurationService
{
    /**
     * @inheritDoc
     */
    protected function isRemote(): bool
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    protected function insertConfigCentreonBroker(int $serverID): void
    {
        $brokerConfiguration = CfgCentreonBroker::getConfiguration($serverID, $this->name);
        $brokerInfoConfiguration = CfgCentreonBrokerInfo::getConfiguration(
            $this->name,
            $this->dbUser,
            $this->dbPassword
        );

        $this->brokerID = (int) $this->insertWithAdapter('cfg_centreonbroker', $brokerConfiguration['broker']);
        $moduleID = (int) $this->insertWithAdapter('cfg_centreonbroker', $brokerConfiguration['module']);
        $rrdID = (int) $this->insertWithAdapter('cfg_centreonbroker', $brokerConfiguration['rrd']);

        $this->insertBrokerLog(CfgCentreonBrokerLog::getConfiguration($this->brokerID));
        $this->insertBrokerLog(CfgCentreonBrokerLog::getConfiguration($moduleID));
        $this->insertBrokerLog(CfgCentreonBrokerLog::getConfiguration($rrdID));

        $this->insertBrokerInfo($this->brokerID, $brokerInfoConfiguration['central-broker']);
        $this->insertBrokerInfo($moduleID, $brokerInfoConfiguration['central-module']);
        $this->insertBrokerInfo($rrdID, $brokerInfoConfiguration['central-rrd']);
    }

    /**
     * insert broker information
     *
     * @param int $configurationId
     * @param array<string,array<string,mixed> $brokerInfo
     */
    private function insertBrokerInfo(int $configurationId, array $brokerInfo): void
    {
        foreach ($brokerInfo as $brokerConfig => $brokerData) {
            foreach ($brokerData as $row) {
                $row['config_id'] = $configurationId;

                if ($brokerConfig === 'output_forward' && $row['config_key'] === 'host') {
                    $row['config_value'] = $this->centralIp;
                }

                $this->insertWithAdapter('cfg_centreonbroker_info', $row);
            }
        }
    }
}
