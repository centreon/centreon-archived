<?php

namespace CentreonRemote\Domain\Service;

use Pimple\Container;

class RemoteConnectionConfigurationService
{

    // Next steps:
    // - register this service in container
    // - use this service in CentreonConfigurationRemote
    // - setup $this->dbManager
    // - figure out what to do with src/CentreonRemote/todo/db.php
    // - figure out how can I use method insert of $this->dbManager
    // - continue with notes from this file and CentreonConfigurationRemote

    private $dbManager;

    private $ip;

    private $name;

    private $resourcesPath = '/src/CentreonRemote/Domain/Resources/remote_config/';


    public function __construct(Container $di)
    {
        $this->dbManager = null;
    }

    public function setIp($ip)
    {
        $this->ip = $ip;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    private function getDbManager()
    {
        return $this->dbManager;
    }

    private function getResource($resourceName): callable
    {
        return require_once getcwd() . "{$this->resourcesPath}{$resourceName}";
    }

    public function insert()
    {
        $serverID = $this->insertNagiosServer();

        if (!$serverID) {
            die(' ERROR at nagios_server ');//TODO
        }

        if (!$this->insertConfigNagios($serverID)) {
            die(' ERROR at cfg_nagios ');//TODO
        }

        if ($this->insertConfigNagiosBroker($serverID)) {
            die(' ERROR at cfg_nagios_broker_module ');//TODO
        }

        if (!$this->insertConfigResource()) {
            die(' ERROR at cfg_resource ');//TODO
        }

        if (!$this->insertConfigResoureRelations()) {
            die(' ERROR at cfg_resource_instance_relations ');//TODO
        }

        if (!$this->insertConfigCentreonBroker()) {
            die(' ERROR at cfg_centreonbroker ');//TODO
        }

        if (!$this->insertConfigCentreonBrokerInfo()) {
            die(' ERROR at cfg_centreonbroker_info ');//TODO
        }
    }

    private function insertNagiosServer()
    {
        $nagiosServerData = $this->getResource('nagios_server.php');

        return $this->getDbManager()->insert('nagios_server', $nagiosServerData($this->name, $this->ip));
    }

    private function insertConfigNagios($serverID)
    {
        $configNagiosData = $this->getResource('cfg_nagios.php');

        return $this->getDbManager()->insert('cfg_nagios', $configNagiosData($this->name, $serverID));
    }

    private function insertConfigNagiosBroker($serverID)
    {
        $configNagiosBrokerData = $this->getResource('cfg_nagios_broker_module.php');
        $data = $configNagiosBrokerData($serverID, $this->name);

        $configBrokerFirstID = $this->getDbManager()->insert('cfg_nagios_broker_module', $data[0]);
        $configBrokerSecondID = $this->getDbManager()->insert('cfg_nagios_broker_module', $data[1]);

        return $configBrokerFirstID && $configBrokerSecondID;
    }

    private function insertConfigResource()
    {
        //TODO add some ids to data?
        $configResourceData = $this->getResource('cfg_resource.php');

        return $this->getDbManager()->insert('cfg_resource', $configResourceData);
    }

    private function insertConfigResoureRelations()
    {
        //TODO add some ids to data?
        $configResourceRelationsData = $this->getResource('cfg_resource_instance_relations.php');

        return $this->getDbManager()->insert('cfg_resource_instance_relations', $configResourceRelationsData);
    }

    private function insertConfigCentreonBroker()
    {
        $configCentreonBrokerData = $this->getResource('cfg_centreonbroker.php');

        return $this->getDbManager()->insert('cfg_centreonbroker', $configCentreonBrokerData($this->name));
    }

    private function insertConfigCentreonBrokerInfo()
    {
        //TODO some relation to the centreon broker id?
        // - needs to be return the data
        $configCentreonBrokerInfoData = $this->getResource('cfg_centreonbroker_info.php');

        return $this->getDbManager()->insert('cfg_centreonbroker_info', $configCentreonBrokerInfoData);
    }
}
