<?php

namespace CentreonRemote\Domain\Service;

class PollerConnectionConfigurationService extends ServerConnectionConfigurationService
{

    protected function insertConfigCentreonBroker($serverID)
    {
        $configCentreonBrokerData = $this->getResource('cfg_centreonbroker.php');
        $configCentreonBrokerData = $configCentreonBrokerData($serverID, $this->name);
        $configCentreonBrokerInfoData = $this->getResource('cfg_centreonbroker_info.php');
        $configCentreonBrokerInfoData = $configCentreonBrokerInfoData();

        $moduleID = $this->insertWithAdapter('cfg_centreonbroker', $configCentreonBrokerData[0]);

        foreach ($configCentreonBrokerInfoData['central-broker']['logger'] as $row) {
            $row['config_id'] = $moduleID;
            $this->insertWithAdapter('cfg_centreonbroker_info', $row);
        }

        foreach ($configCentreonBrokerInfoData['central-broker']['output_forward'] as $row) {
            if ($row['config_key'] == 'host') {
                $row['config_value'] = $this->centralIp;
            }

            $row['config_id'] = $moduleID;
            $this->insertWithAdapter('cfg_centreonbroker_info', $row);
        }
    }
}
