<?php

namespace CentreonRemote\Domain\Service\ConfigurationWizard;

use CentreonRemote\Domain\Resources\RemoteConfig\CfgCentreonBroker;
use CentreonRemote\Domain\Resources\RemoteConfig\CfgCentreonBrokerInfo;

class RemoteConnectionConfigurationService extends ServerConnectionConfigurationService
{

    protected function insertConfigCentreonBroker(int $serverID): void
    {
        $configCentreonBrokerData = CfgCentreonBroker::getConfiguration($serverID, $this->name);
        $configCentreonBrokerInfoData = CfgCentreonBrokerInfo::getConfiguration(
            $this->name,
            $this->dbUser,
            $this->dbPassword
        );

        $this->brokerID = $this->insertWithAdapter('cfg_centreonbroker', $configCentreonBrokerData['broker']);
        $moduleID = $this->insertWithAdapter('cfg_centreonbroker', $configCentreonBrokerData['module']);
        $rrdID = $this->insertWithAdapter('cfg_centreonbroker', $configCentreonBrokerData['rrd']);

        foreach ($configCentreonBrokerInfoData['central-broker'] as $brokerConfig => $brokerData) {
            foreach ($brokerData as $row) {
                if ($brokerConfig == 'output_forward' && $row['config_key'] == 'host') {
                    $row['config_value'] = $this->centralIp;
                }

                $row['config_id'] = $this->brokerID;
                $this->insertWithAdapter('cfg_centreonbroker_info', $row);
            }
        }

        foreach ($configCentreonBrokerInfoData['central-module'] as $brokerConfig => $brokerData) {
            foreach ($brokerData as $row) {
                $row['config_id'] = $moduleID;
                $this->insertWithAdapter('cfg_centreonbroker_info', $row);
            }
        }

        foreach ($configCentreonBrokerInfoData['central-rrd'] as $brokerConfig => $brokerData) {
            foreach ($brokerData as $row) {
                $row['config_id'] = $rrdID;
                $this->insertWithAdapter('cfg_centreonbroker_info', $row);
            }
        }
    }

    protected function isRemote(): bool
    {
        return true;
    }
}
