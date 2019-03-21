<?php

namespace CentreonRemote\Domain\Resources\RemoteConfig;

use CentreonRemote\Domain\Resources\RemoteConfig\BrokerInfo\LoggerBroker;
use CentreonRemote\Domain\Resources\RemoteConfig\BrokerInfo\LoggerModule;
use CentreonRemote\Domain\Resources\RemoteConfig\BrokerInfo\LoggerRrd;
use CentreonRemote\Domain\Resources\RemoteConfig\BrokerInfo\InputBroker;
use CentreonRemote\Domain\Resources\RemoteConfig\BrokerInfo\InputRrd;
use CentreonRemote\Domain\Resources\RemoteConfig\BrokerInfo\OutputPerfdata;
use CentreonRemote\Domain\Resources\RemoteConfig\BrokerInfo\OutputRrd;
use CentreonRemote\Domain\Resources\RemoteConfig\BrokerInfo\OutputRrdMaster;
use CentreonRemote\Domain\Resources\RemoteConfig\BrokerInfo\OutputSqlMaster;
use CentreonRemote\Domain\Resources\RemoteConfig\BrokerInfo\OutputForwardMaster;
use CentreonRemote\Domain\Resources\RemoteConfig\BrokerInfo\OutputModuleMaster;

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
     * @return array the configuration template
     */
    public static function getConfiguration(string $serverName, $dbUser, $dbPassword): array
    {
        $serverName = strtolower(str_replace(' ', '-', $serverName));

        $data = [
            'central-broker' => [
                'logger'          => LoggerBroker::getConfiguration(),
                'broker'          => InputBroker::getConfiguration(),
                'output_rrd'      => OutputRrdMaster::getConfiguration(),
                'output_forward'  => OutputForwardMaster::getConfiguration(),
                'output_prefdata' => OutputPerfdata::getConfiguration($dbUser, $dbPassword),
                'output_sql'      => OutputSqlMaster::getConfiguration($dbUser, $dbPassword),
            ],
            'central-module' => [
                'logger' => LoggerModule::getConfiguration(),
                'output' => OutputModuleMaster::getConfiguration(),
            ],
            'central-rrd' => [
                'logger' => LoggerRrd::getConfiguration(),
                'input'  => InputRrd::getConfiguration(),
                'output' => OutputRrd::getConfiguration(),
            ]
        ];

        // update logs paths
        $data['central-broker']['logger'][0]['config_value'] = "/var/log/centreon-broker/broker-{$serverName}.log";
        $data['central-module']['logger'][0]['config_value'] = "/var/log/centreon-broker/module-{$serverName}.log";
        $data['central-rrd']['logger'][0]['config_value'] = "/var/log/centreon-broker/rrd-{$serverName}.log";

        return $data;
    }
}
