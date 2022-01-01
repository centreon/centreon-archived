<?php

namespace CentreonRemote\Domain\Resources\RemoteConfig;

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
                'output_prefdata' => OutputPerfdata::getConfiguration($dbUser, $dbPassword),
                'output_sql'      => OutputSqlMaster::getConfiguration($dbUser, $dbPassword),
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
