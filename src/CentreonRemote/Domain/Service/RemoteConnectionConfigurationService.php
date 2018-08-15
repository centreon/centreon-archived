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

    /**
     * @return bool
     *
     * @throws \Exception
     */
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
        $sql = 'SELECT `resource_id`, `resource_name` FROM `cfg_resource`';
        $sql .= "WHERE `resource_name` IN('\$USER1$', '\$CENTREONPLUGINS$') ORDER BY `resource_id` ASC";
        $this->getDbAdapter()->query($sql);
        $results = $this->getDbAdapter()->results();

        if (count($results) < 2) {
            throw new \Exception('Resources records from `cfg_resource` could not be fetched.');
        }

        if (
            $results[0]->resource_name != '$USER1$' ||
            $results[1]->resource_name != '$CENTREONPLUGINS$'
        ) {
            throw new \Exception('Resources records from `cfg_resource` are not as expected.');
        }

        $userResourceData = ['resource_id' => $results[0]->resource_id, 'instance_id' => $serverID];
        $pluginResourceData = ['resource_id' => $results[1]->resource_id, 'instance_id' => $serverID];

        $this->insertWithAdapter('cfg_resource_instance_relations', $userResourceData);
        $this->insertWithAdapter('cfg_resource_instance_relations', $pluginResourceData);

        return true;
    }

    private function insertConfigCentreonBroker()
    {
        $configCentreonBrokerData = $this->getResource('cfg_centreonbroker.php');
        $configCentreonBrokerInfoData = $this->getResource('cfg_centreonbroker_info.php');

        $configID = $this->insertWithAdapter('cfg_centreonbroker', $configCentreonBrokerData($this->name));

        foreach ($configCentreonBrokerInfoData($configID) as $infoGroup) {
            foreach ($infoGroup as $row) {
                $row['config_id'] = $configID;
                $this->insertWithAdapter('cfg_centreonbroker_info', $row);
            }
        }

        return $configID;
    }

    private function insertWithAdapter($table, array $data)
    {
        try {
            $result = $this->getDbAdapter()->insert($table, $data);
        } catch(\Exception $e) {
            $this->getDbAdapter()->rollBack();
            throw new \Exception("Error inserting remote configuration. Rolling back. Table name: {$table}.");
        }

        return $result;
    }
}
