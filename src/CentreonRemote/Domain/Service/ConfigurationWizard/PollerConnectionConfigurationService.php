<?php

namespace CentreonRemote\Domain\Service\ConfigurationWizard;

class PollerConnectionConfigurationService extends ServerConnectionConfigurationService
{

    protected function insertConfigCentreonBroker($serverID)
    {
        $configCentreonBrokerData = $this->getResource('cfg_centreonbroker.php');
        $configCentreonBrokerData = $configCentreonBrokerData($serverID, $this->name);
        $configCentreonBrokerInfoData = $this->getResource('cfg_centreonbroker_info.php');
        $configCentreonBrokerInfoData = $configCentreonBrokerInfoData(null, null);

        $moduleID = $this->insertWithAdapter('cfg_centreonbroker', $configCentreonBrokerData['module']);

        foreach ($configCentreonBrokerInfoData['central-module']['logger'] as $row) {
            $row['config_id'] = $moduleID;
            $this->insertWithAdapter('cfg_centreonbroker_info', $row);
        }

        foreach ($configCentreonBrokerInfoData['central-module']['output'] as $row) {
            if ($row['config_key'] == 'host') {
                $row['config_value'] = $this->centralIp;
            }

            $row['config_id'] = $moduleID;
            $this->insertWithAdapter('cfg_centreonbroker_info', $row);
        }
    }
}
