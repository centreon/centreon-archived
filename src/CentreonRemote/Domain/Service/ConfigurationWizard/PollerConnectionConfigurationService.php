<?php

namespace CentreonRemote\Domain\Service\ConfigurationWizard;

class PollerConnectionConfigurationService extends ServerConnectionConfigurationService
{

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

        if ($this->isOpenBrokerFlow) {
            $outputHost = '';
            $onePeerRetentionMode = 'yes';
            $openFlowInputConfig = $this->getResource('input_poller_open_flow.php');

            foreach ($openFlowInputConfig($this->serverIp) as $openFlowRow) {
                $openFlowRow['config_id'] = $moduleID;
                $this->insertWithAdapter('cfg_centreonbroker_info', $openFlowRow);
            }
        }

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
