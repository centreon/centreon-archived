<?php

namespace CentreonRemote\Domain\Service\ConfigurationWizard;

use Centreon\Infrastructure\CentreonLegacyDB\CentreonDBAdapter;
use Pimple\Container;

abstract class ServerConnectionConfigurationService
{

    /** @var CentreonDBAdapter */
    protected $dbAdapter;

    protected $serverIp;

    protected $centralIp;

    protected $dbUser;

    protected $dbPassword;

    protected $name;

    protected $shouldInsertBamBrokers = false;

    protected $brokerID = null;

    protected $resourcesPath = '/Domain/Resources/remote_config/';


    public function __construct(Container $di)
    {
        $this->dbAdapter = $di['centreon.db-manager']->getAdapter('configuration_db');
    }

    abstract protected function insertConfigCentreonBroker($serverID);

    public function setServerIp($ip)
    {
        $this->serverIp = $ip;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function setCentralIp($ip)
    {
        $this->centralIp = $ip;
    }

    public function setDbUser($user)
    {
        $this->dbUser = $user;
    }

    public function setDbPassword($password)
    {
        $this->dbPassword = $password;
    }

    protected function getDbAdapter(): CentreonDBAdapter
    {
        return $this->dbAdapter;
    }

    protected function getResource($resourceName): callable
    {
        return require_once dirname(dirname(dirname(dirname(__FILE__)))) . "{$this->resourcesPath}{$resourceName}";
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

        $this->insertConfigCentreonBroker($serverID);

        if ($this->shouldInsertBamBrokers) {
            $this->insertBamBrokers();
        }

        $this->getDbAdapter()->commit();

        return $serverID;
    }

    protected function insertNagiosServer()
    {
        $nagiosServerData = $this->getResource('nagios_server.php');

        return $this->insertWithAdapter('nagios_server', $nagiosServerData($this->name, $this->serverIp));
    }

    protected function insertConfigNagios($serverID)
    {
        $configNagiosData = $this->getResource('cfg_nagios.php');

        return $this->insertWithAdapter('cfg_nagios', $configNagiosData($this->name, $serverID));
    }

    protected function insertConfigNagiosBroker($serverID)
    {
        $configNagiosBrokerData = $this->getResource('cfg_nagios_broker_module.php');
        $data = $configNagiosBrokerData($serverID, $this->name);

        $configBrokerFirstID = $this->insertWithAdapter('cfg_nagios_broker_module', $data[0]);
        $configBrokerSecondID = $this->insertWithAdapter('cfg_nagios_broker_module', $data[1]);

        return $configBrokerFirstID && $configBrokerSecondID;
    }

    protected function insertConfigResources($serverID)
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
    }

    protected function insertBamBrokers()
    {
        global $conf_centreon;

        if (!$this->brokerID) {
            throw new \Exception('Broker ID was not inserted in order to add BAM broker configs to it.');
        }

        $bamBrokerInfoData = $this->getResource('bam_broker_cfg_info.php');
        $bamBrokerInfoData = $bamBrokerInfoData($conf_centreon['password']);

        foreach ($bamBrokerInfoData['monitoring'] as $row) {
            $row['config_id'] = $this->brokerID;
            $this->insertWithAdapter('cfg_centreonbroker_info', $row);
        }

        foreach ($bamBrokerInfoData['reporting'] as $row) {
            $row['config_id'] = $this->brokerID;
            $this->insertWithAdapter('cfg_centreonbroker_info', $row);
        }
    }

    protected function insertWithAdapter($table, array $data)
    {
        try {
            $result = $this->getDbAdapter()->insert($table, $data);
        } catch(\Exception $e) {
            $this->getDbAdapter()->rollBack();
            throw new \Exception("Error inserting remote configuration. Rolling back. Table name: {$table}.");
        }

        return $result;
    }

    public function shouldInsertBamBrokers()
    {
        $this->shouldInsertBamBrokers = true;
    }
}
