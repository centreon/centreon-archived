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

use Centreon\Domain\Repository\Interfaces\CfgCentreonBrokerInterface;
use Centreon\Domain\Service\BrokerConfigurationService;
use CentreonRemote\Domain\Resources\RemoteConfig\CfgCentreonBroker;
use CentreonRemote\Domain\Resources\DefaultConfig\CfgCentreonBrokerLog;
use CentreonRemote\Domain\Resources\RemoteConfig\CfgCentreonBrokerInfo;
use CentreonRemote\Domain\Resources\RemoteConfig\InputFlowOnePeerRetention;

class PollerConnectionConfigurationService extends ServerConnectionConfigurationService
{
    /**
     * @var CfgCentreonBrokerInterface
     */
    private $brokerRepository;

    /**
     * @var BrokerConfigurationService
     */
    private $brokerConfigurationService;

    /**
     * Set broker repository to manage general broker configuration
     *
     * @param CfgCentreonBrokerInterface $cfgCentreonBroker the centreon broker configuration repository
     */
    public function setBrokerRepository(CfgCentreonBrokerInterface $cfgCentreonBroker): void
    {
        $this->brokerRepository = $cfgCentreonBroker;
    }

    /**
     * Set broker configuration service to broker info configuration
     *
     * @param BrokerConfigurationService $brokerConfigurationService the service to manage broker confiration
     */
    public function setBrokerConfigurationService(BrokerConfigurationService $brokerConfigurationService): void
    {
        $this->brokerConfigurationService = $brokerConfigurationService;
    }

    /**
     * Insert centreon broker configuration to a given poller
     * this configuration i only for broker module (not cbd)
     *
     * @param int $serverID the poller id
     */
    protected function insertConfigCentreonBroker(int $serverID): void
    {
        $configCentreonBrokerData = CfgCentreonBroker::getConfiguration($serverID, $this->name);
        $configCentreonBrokerInfoData = CfgCentreonBrokerInfo::getConfiguration($this->name, null, null);

        $outputHost = $this->centralIp;
        $onePeerRetentionMode = 'no';
        $moduleID = $this->insertWithAdapter('cfg_centreonbroker', $configCentreonBrokerData['module']);

        $this->insertBrokerLog(
            CfgCentreonBrokerLog::getConfiguration(
                $this->getDbAdapter()->getCentreonDBInstance(),
                $moduleID
            )
        );

        // if one peer retention mode is enabled,
        // we need to add an input in central broker configuration
        if ($this->onePeerRetention) {
            // update poller broker module parameters for one peer retention
            $outputHost = '';
            $onePeerRetentionMode = 'yes';

            if ($this->isLinkedToCentralServer) {
                // get central broker config id
                // we need it to add an input to pull broker data from distant poller
                $centralBrokerConfigId = $this->brokerRepository->findCentralBrokerConfigId();

                // add broker input configuration on central to get data from poller
                $brokerInfosEntities = InputFlowOnePeerRetention::getConfiguration($this->name, $this->serverIp);
                $this->brokerConfigurationService->addFlow($centralBrokerConfigId, 'input', $brokerInfosEntities);
            }
        }

        // add poller module output flow to send data to the central server
        foreach ($configCentreonBrokerInfoData['central-module']['output'] as $row) {
            if ($row['config_key'] == 'host') {
                $row['config_value'] = $outputHost;
            } elseif ($row['config_key'] == 'one_peer_retention_mode') {
                $row['config_value'] = $onePeerRetentionMode;
            }

            $row['config_id'] = $moduleID;
            $this->insertWithAdapter('cfg_centreonbroker_info', $row);
        }
    }
}
