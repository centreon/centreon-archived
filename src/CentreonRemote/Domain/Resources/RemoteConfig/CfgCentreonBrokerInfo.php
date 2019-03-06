<?php

namespace CentreonRemote\Domain\Resources\RemoteConfig;

use CentreonRemote\Domain\Resources\RemoteConfig\BrokerInfo\ {
    LoggerBroker,
    LoggerModule,
    LoggerRrd,
    InputBroker,
    InputRrd,
    OutputPerfdata,
    OutputRrd,
    OutputRrdMaster,
    OutputSqlMaster,
    OutputForwardMaster,
    OutputModuleMaster
};

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
    public static function getConfiguration (string $serverName, $dbUser, $dbPassword): array
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
