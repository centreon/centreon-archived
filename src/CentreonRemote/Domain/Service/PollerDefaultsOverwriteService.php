<?php

namespace CentreonRemote\Domain\Service;

use CentreonRemote\Domain\Resources\DefaultConfig\CfgNagiosBrokerModule;
use CentreonRemote\Domain\Resources\DefaultConfig\CfgCentreonBrokerInfo;

class PollerDefaultsOverwriteService
{

    /** @var int */
    private $pollerID = null;

    /** @var int[] */
    private $brokerConfigIDs = [];

    /** @var int[] */
    private $nagiosConfigIDs = [];

    /**
     * @param int $pollerID
     */
    public function setPollerID($pollerID): void
    {
        $this->pollerID = $pollerID;
    }

    /**
     * @param array<string|int, array<string,int>>  $data - the table data for all pollers
     * @param string $columnName - the name of the column which is FK to the poller
     * @param string $resourceName - the name of the table for which the data is
     *
     * @return array<string|int, array<string,int>>
     */
    private function findPollerAndSetResourceData(array $data, $columnName, $resourceName)
    {
        // Remove remote poller resources in the array by the column name and pollerID
        $data = array_filter($data, function ($pollerData) use ($columnName) {
            return $pollerData[$columnName] != $this->pollerID;
        });

        // Get default data for the specified resource
        $defaultData = $resourceName::getConfiguration();

        // Make the data multidimensional array if its not, so it can be merged
        $dataToMerge = is_array($defaultData[key($defaultData)]) ? $defaultData : [$defaultData];

        // Set the correct pollerID in the column name which is FK to the poller
        foreach ($dataToMerge as $key => $arrayData) {
            $dataToMerge[$key][$columnName] = $this->pollerID;
        }

        return array_merge($data, $dataToMerge);
    }

    /**
     * Get poller information
     *
     * @param array<string|int, array<string,int>> $data the poller data
     * @return array<string|int, array<string,int>> the complete poller data
     */
    public function getNagiosServer(array $data): array
    {
        return $this->findPollerAndSetResourceData(
            $data,
            'id',
            'CentreonRemote\Domain\Resources\DefaultConfig\NagiosServer'
        );
    }

    /**
     * Get engine information
     *
     * @param array<string|int, array<string,int>> $data the engine data
     * @return array<string|int, array<string,int>> the complete engine data
     */
    public function getCfgNagios(array $data)
    {
        $configsOfRemote = array_filter($data, function ($pollerData) {
            return $pollerData['nagios_server_id'] == $this->pollerID;
        });
        $this->nagiosConfigIDs = array_column($configsOfRemote, 'nagios_id');

        return $this->findPollerAndSetResourceData(
            $data,
            'nagios_server_id',
            'CentreonRemote\Domain\Resources\DefaultConfig\CfgNagios'
        );
    }

    /**
     * Get engine broker module information
     *
     * @param array<string|int, array<string,int>> $data the engine broker module data
     * @return array<string|int, array<string,int>> the complete engine broker module data
     */
    public function getCfgNagiosBroker(array $data)
    {
        // Remove nagios config info which is related to the broker module of the remote poller
        $data = array_filter($data, function ($pollerData) {
            return !in_array($pollerData['cfg_nagios_id'], $this->nagiosConfigIDs);
        });

        $defaultData = CfgNagiosBrokerModule::getConfiguration();

        return array_merge($defaultData, $data);
    }

    /**
     * Get broker information
     *
     * @param array<string|int, array<string,int>> $data the broker data
     * @return array<string|int, array<string,int>> the complete broker data
     */
    public function getCfgCentreonBroker(array $data)
    {
        $configsOfRemote = array_filter($data, function ($pollerData) {
            return $pollerData['ns_nagios_server'] == $this->pollerID;
        });
        $this->brokerConfigIDs = array_column($configsOfRemote, 'config_id');

        return $this->findPollerAndSetResourceData(
            $data,
            'ns_nagios_server',
            'CentreonRemote\Domain\Resources\DefaultConfig\CfgCentreonBroker'
        );
    }

    /**
     * Get broker detailed information
     *
     * @param array<string|int, array<string,int>> $data the broker detailed data
     * @return array<string|int, array<string,int>> the complete broker detailed data
     */
    public function getCfgCentreonBrokerInfo(array $data)
    {
        // Remove broker config info which is related to the broker module of the remote poller
        $data = array_filter($data, function ($pollerData) {
            return !in_array($pollerData['config_id'], $this->brokerConfigIDs);
        });

        $defaultData = CfgCentreonBrokerInfo::getConfiguration();

        return array_merge($defaultData, $data);
    }

    /**
     * Get global macro information
     *
     * @param array<string|int, array<string,int>> $data the global macro data
     * @return array the complete global macro data
     */
    public function getCfgResource(array $data): array
    {
        // prepare _instance_id for method findPollerAndSetResourceData
        foreach ($data as $key => $val) {
            $instanceIds = explode(',', $val['_instance_id']);

            if (in_array($this->pollerID, $instanceIds)) {
                $data[$key]['_instance_id'] = $this->pollerID;
            } else {
                $data[$key]['_instance_id'] = '';
            }
        }

        return $this->findPollerAndSetResourceData(
            $data,
            '_instance_id',
            'CentreonRemote\Domain\Resources\DefaultConfig\CfgResource'
        );
    }
}
