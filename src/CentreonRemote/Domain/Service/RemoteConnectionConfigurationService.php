<?php

namespace CentreonRemote\Domain\Service;

use Centreon\Infrastructure\CentreonLegacyDB\CentreonDBAdapter;
use Pimple\Container;

class RemoteConnectionConfigurationService
{

    /** @var CentreonDBAdapter */
    private $dbAdapter;

    private $ip;

    private $name;

    private $resourcesPath = '/Domain/Resources/remote_config/';


    public function __construct(Container $di)
    {
        $this->dbAdapter = $di['centreon.db-manager']->getAdapter('configuration_db');
    }

    public function setIp($ip)
    {
        $this->ip = $ip;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    private function getDbAdapter(): CentreonDBAdapter
    {
        return $this->dbAdapter;
    }

    private function getResource($resourceName): callable
    {
        return require_once dirname(dirname(dirname(__FILE__))) . "{$this->resourcesPath}{$resourceName}";
    }

    public function insert()
    {
        $this->getDbAdapter()->beginTransaction();

        $serverID = $this->insertNagiosServer();

        if (!$serverID) {
            throw new \Exception('Error inserting nagios server.');
        }

        $this->insertConfigNagios($serverID);

        $this->insertConfigNagiosBroker($serverID);

        $this->insertConfigResources($serverID);

        $this->insertConfigCentreonBroker();

        $this->insertConfigCentreonBrokerInfo();

        $this->getDbAdapter()->commit();

        return true;
    }

    private function insertNagiosServer()
    {
        $nagiosServerData = $this->getResource('nagios_server.php');

        return $this->insertWithAdapter('nagios_server', $nagiosServerData($this->name, $this->ip));
    }

    private function insertConfigNagios($serverID)
    {
        $configNagiosData = $this->getResource('cfg_nagios.php');

        return $this->insertWithAdapter('cfg_nagios', $configNagiosData($this->name, $serverID));
    }

    private function insertConfigNagiosBroker($serverID)
    {
        $configNagiosBrokerData = $this->getResource('cfg_nagios_broker_module.php');
        $data = $configNagiosBrokerData($serverID, $this->name);

        $configBrokerFirstID = $this->insertWithAdapter('cfg_nagios_broker_module', $data[0]);
        $configBrokerSecondID = $this->insertWithAdapter('cfg_nagios_broker_module', $data[1]);

        return $configBrokerFirstID && $configBrokerSecondID;
    }

    private function insertConfigResources($serverID)
    {
        $configResourceData = $this->getResource('cfg_resource.php');
        $configResourceRelationsData = $this->getResource('cfg_resource_instance_relations.php');
        $configResourceData = $configResourceData();

        $resourceOneID = $this->insertWithAdapter('cfg_resource', $configResourceData[0]);
        $resourceTwoID = $this->insertWithAdapter('cfg_resource', $configResourceData[1]);
        $resourceThreeID = $this->insertWithAdapter('cfg_resource', $configResourceData[2]);

        $relationData = [$resourceOneID, $resourceTwoID, $resourceThreeID];
        $configResourceRelationsData = $configResourceRelationsData($relationData, $serverID);

        $this->insertWithAdapter('cfg_resource_instance_relations', $configResourceRelationsData[0]);
        $this->insertWithAdapter('cfg_resource_instance_relations', $configResourceRelationsData[1]);
        $this->insertWithAdapter('cfg_resource_instance_relations', $configResourceRelationsData[2]);

        return $resourceOneID && $resourceTwoID && $resourceThreeID;
    }

    private function insertConfigCentreonBroker()
    {
        $configCentreonBrokerData = $this->getResource('cfg_centreonbroker.php');

        return $this->insertWithAdapter('cfg_centreonbroker', $configCentreonBrokerData($this->name));
    }

    private function insertConfigCentreonBrokerInfo()
    {
        //TODO some relation to the centreon broker id?
        $configCentreonBrokerInfoData = $this->getResource('cfg_centreonbroker_info.php');

        foreach ($configCentreonBrokerInfoData() as $infoGroup) {
            foreach ($infoGroup as $row) {
                $this->insertWithAdapter('cfg_centreonbroker_info', $row);
            }
        }

        return true;
    }

    private function insertWithAdapter($table, array $data)
    {
        try {
            $result = $this->getDbAdapter()->insert($table, $data);
        } catch(\Exception $e) {
            $this->getDbAdapter()->rollBack();
            throw new \Exception("Error inserting remote configuration. Rolling back. Table name: {$table}.");
        }

        if (!$result) {
            $this->getDbAdapter()->rollBack();
            throw new \Exception("Error inserting remote configuration. Rolling back. Table name: {$table}.");
        }

        return $result;
    }
}
