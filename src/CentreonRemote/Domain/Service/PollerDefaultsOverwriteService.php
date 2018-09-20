<?php

namespace CentreonRemote\Domain\Service;

class PollerDefaultsOverwriteService
{

    private $pollerID = null;

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
        // Find the remote poller resource in the array by the column name and pollerID
        $pollersArray = array_filter($data, function ($pollerData) use ($columnName) {
            return $pollerData[$columnName] == $this->pollerID;
        });

        // The remote poller data is set to this $key in the array of pollers
        $key = key($pollersArray);

        // Overwrite the data of the remote poller with default data of the specified resource
        $data[$key] = $this->getResource($resourceName);

        // Set the correct pollerID in the column name which is FK to the poller
        $data[$key][$columnName] = $this->pollerID;

        return $data;
    }

    public function setNagiosServer(array $data)
    {
        return $this->findPollerAndSetResourceData($data, 'id', 'nagios_server.php');
    }

    public function setCfgNagios(array $data)
    {
        return $this->findPollerAndSetResourceData($data, 'nagios_server_id', 'cfg_nagios.php');
    }
}
