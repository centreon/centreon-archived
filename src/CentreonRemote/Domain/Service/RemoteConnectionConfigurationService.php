<?php

namespace CentreonRemote\Domain\Service;

use Centreon\Infrastructure\CentreonLegacyDB\CentreonDBAdapter;
use Pimple\Container;

class RemoteConnectionConfigurationService
{

    // Next steps:
    // - continue with notes from this file and CentreonConfigurationRemote

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

        return $this->getDbAdapter()->insert('nagios_server', $nagiosServerData($this->name, $this->ip));
    }

    private function insertConfigNagios($serverID)
    {
        $configNagiosData = $this->getResource('cfg_nagios.php');

        return $this->getDbAdapter()->insert('cfg_nagios', $configNagiosData($this->name, $serverID));
    }

    private function insertConfigNagiosBroker($serverID)
    {
        $configNagiosBrokerData = $this->getResource('cfg_nagios_broker_module.php');
        $data = $configNagiosBrokerData($serverID, $this->name);

        $configBrokerFirstID = $this->getDbAdapter()->insert('cfg_nagios_broker_module', $data[0]);
        $configBrokerSecondID = $this->getDbAdapter()->insert('cfg_nagios_broker_module', $data[1]);

        return $configBrokerFirstID && $configBrokerSecondID;
    }

    private function insertConfigResource()
    {
        //TODO add some ids to data?
        $configResourceData = $this->getResource('cfg_resource.php');

        return $this->getDbAdapter()->insert('cfg_resource', $configResourceData);
    }

    private function insertConfigResoureRelations()
    {
        //TODO add some ids to data?
        $configResourceRelationsData = $this->getResource('cfg_resource_instance_relations.php');

        return $this->getDbAdapter()->insert('cfg_resource_instance_relations', $configResourceRelationsData);
    }

    private function insertConfigCentreonBroker()
    {
        $configCentreonBrokerData = $this->getResource('cfg_centreonbroker.php');

        return $this->getDbAdapter()->insert('cfg_centreonbroker', $configCentreonBrokerData($this->name));
    }

    private function insertConfigCentreonBrokerInfo()
    {
        //TODO some relation to the centreon broker id?
        // - needs to be return the data
        $configCentreonBrokerInfoData = $this->getResource('cfg_centreonbroker_info.php');

        return $this->getDbAdapter()->insert('cfg_centreonbroker_info', $configCentreonBrokerInfoData);
    }
}
