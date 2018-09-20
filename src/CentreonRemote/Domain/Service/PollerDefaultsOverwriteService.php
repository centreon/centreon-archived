<?php

namespace CentreonRemote\Domain\Service;

class PollerDefaultsOverwriteService
{

    private $pollerID = null;

    private $brokerConfigIDs = [];

    private $resourcesPath = '/Domain/Resources/default_config/';

    /**
     * @param null $pollerID
     */
    public function setPollerID($pollerID)
    {
        $this->pollerID = $pollerID;
    }

    private function getResource($resourceName): array
    {
        return require_once dirname(dirname(dirname(__FILE__))) . "{$this->resourcesPath}{$resourceName}";
    }

    /**
     * @param array  $data - the table data for all pollers
     * @param string $columnName - the name of the column which is FK to the poller
     * @param string $resourceName - the name of the table for which the data is
     *
     * @return array
     */
    private function findPollerAndSetResourceData(array $data, $columnName, $resourceName)
    {
        // Remove remote poller resources in the array by the column name and pollerID
        $data = array_filter($data, function ($pollerData) use ($columnName) {
            return $pollerData[$columnName] != $this->pollerID;
        });

        // Get default data for the specified resource
        $defaultData = $this->getResource($resourceName);

        // Make the data multidimensional array if its not, so it can be merged
        $dataToMerge = is_array($defaultData[key($defaultData)]) ? $defaultData : [$defaultData];

        // Set the correct pollerID in the column name which is FK to the poller
        foreach ($dataToMerge as $key => $arrayData) {
            $dataToMerge[$key][$columnName] = $this->pollerID;
        }

        return array_merge($dataToMerge, $data);
    }

    public function setNagiosServer(array $data)
    {
        return $this->findPollerAndSetResourceData($data, 'id', 'nagios_server.php');
    }

    public function setCfgNagios(array $data)
    {
        return $this->findPollerAndSetResourceData($data, 'nagios_server_id', 'cfg_nagios.php');
    }

    public function setCfgNagiosBroker(array $data)
    {
        return $this->findPollerAndSetResourceData($data, 'cfg_nagios_id', 'cfg_nagios_broker_module.php');
    }

    public function setCfgCentreonBroker(array $data)
    {
        $configsOfRemote = array_filter($data, function ($pollerData) {
            return $pollerData['ns_nagios_server'] == $this->pollerID;
        });
        $this->brokerConfigIDs = array_column($configsOfRemote, 'config_id');

        return $this->findPollerAndSetResourceData($data, 'ns_nagios_server', 'cfg_centreonbroker.php');
    }

    public function setCfgCentreonBrokerInfo(array $data)
    {
        // Remove broker config info which is related to the broker module of the remote poller
        $data = array_filter($data, function ($pollerData) {
            return !in_array($pollerData['config_id'], $this->brokerConfigIDs);
        });

        $defaultData = $this->getResource('cfg_centreonbroker_info.php');

        return array_merge($defaultData, $data);
    }
}
