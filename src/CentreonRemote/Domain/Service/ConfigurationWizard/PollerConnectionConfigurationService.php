<?php

namespace CentreonRemote\Domain\Service\ConfigurationWizard;

use Centreon\Domain\Repository\Interfaces\CfgCentreonBrokerInterface;
use Centreon\Domain\Service\BrokerConfigurationService;

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
    public function setBrokerRepository(CfgCentreonBrokerInterface $cfgCentreonBroker)
    {
        $this->brokerRepository = $cfgCentreonBroker;
    }

    /**
     * Set broker configuration service to broker info configuration
     *
     * @param BrokerConfigurationService $brokerConfigurationService the service to manage broker confiration
     */
    public function setBrokerConfigurationService(BrokerConfigurationService $brokerConfigurationService)
    {
        $this->brokerConfigurationService = $brokerConfigurationService;
    }

    protected function insertConfigCentreonBroker($serverID)
    {
        $configCentreonBrokerData = $this->getResource('cfg_centreonbroker.php');
        $configCentreonBrokerData = $configCentreonBrokerData($serverID, $this->name);
        $configCentreonBrokerInfoData = $this->getResource('cfg_centreonbroker_info.php');
        $configCentreonBrokerInfoData = $configCentreonBrokerInfoData($this->name, null, null);

        $outputHost = $this->centralIp;
        $onePeerRetentionMode = 'no';
        $moduleID = $this->insertWithAdapter('cfg_centreonbroker', $configCentreonBrokerData['module']);

        foreach ($configCentreonBrokerInfoData['central-module']['logger'] as $row) {
            $row['config_id'] = $moduleID;
            $this->insertWithAdapter('cfg_centreonbroker_info', $row);
        }

        // if one peer retention mode is enabled,
        // we need to add an input in central broker configuration
        if ($this->isOpenBrokerFlow) {
            // update poller broker module parameters for one peer retention
            $outputHost = '';
            $onePeerRetentionMode = 'yes';

            // get template function to generate input flow in central broker configuration
            $openFlowInputConfig = $this->getResource('central_input_flow_one_peer_retention.php');

            // get central broker config id
            // we need it to add an input to pull broker data from distant poller
            $centralBrokerConfigId = $this->brokerRepository->findCentralBrokerConfigId();

            // add broker input configuration on central to get data from poller
            $brokerInfosEntities = $openFlowInputConfig($this->serverIp);
            $this->brokerConfigurationService->addFlow($centralBrokerConfigId, 'input', $brokerInfosEntities);
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
