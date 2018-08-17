<?php

namespace CentreonRemote\Domain\Service;

class RemoteConnectionConfigurationService extends ServerConnectionConfigurationService
{

    protected function insertConfigCentreonBroker($serverID)
    {
        $configCentreonBrokerData = $this->getResource('cfg_centreonbroker.php');
        $configCentreonBrokerData = $configCentreonBrokerData($serverID, $this->name);
        $configCentreonBrokerInfoData = $this->getResource('cfg_centreonbroker_info.php');
        $configCentreonBrokerInfoData = $configCentreonBrokerInfoData();

        $brokerID = $this->insertWithAdapter('cfg_centreonbroker', $configCentreonBrokerData[0]);
        $moduleID = $this->insertWithAdapter('cfg_centreonbroker', $configCentreonBrokerData[1]);
        $rrdID = $this->insertWithAdapter('cfg_centreonbroker', $configCentreonBrokerData[2]);

        foreach ($configCentreonBrokerInfoData['central-broker'] as $brokerConfig => $brokerData) {
            foreach ($brokerData as $row) {
                if ($brokerConfig == 'output_forward' && $row['config_key'] == 'host') {
                    $row['config_value'] = $this->centralIp;
                }

                $row['config_id'] = $brokerID;
                $this->insertWithAdapter('cfg_centreonbroker_info', $row);
            }
        }

        foreach ($configCentreonBrokerInfoData['central-module'] as $brokerData) {
            foreach ($brokerData as $row) {
                $row['config_id'] = $moduleID;
                $this->insertWithAdapter('cfg_centreonbroker_info', $row);
            }
        }

        foreach ($configCentreonBrokerInfoData['central-rrd'] as $brokerData) {
            foreach ($brokerData as $row) {
                $row['config_id'] = $rrdID;
                $this->insertWithAdapter('cfg_centreonbroker_info', $row);
            }
        }
    }
}
