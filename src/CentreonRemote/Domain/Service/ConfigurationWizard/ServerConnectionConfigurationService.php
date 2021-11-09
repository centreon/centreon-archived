<?php

namespace CentreonRemote\Domain\Service\ConfigurationWizard;

use Centreon\Infrastructure\CentreonLegacyDB\CentreonDBAdapter;

use CentreonRemote\Domain\Resources\RemoteConfig\NagiosServer;
use CentreonRemote\Domain\Resources\RemoteConfig\CfgNagios;
use CentreonRemote\Domain\Resources\RemoteConfig\CfgNagiosBrokerModule;
use CentreonRemote\Domain\Resources\RemoteConfig\BamBrokerCfgInfo;

abstract class ServerConnectionConfigurationService
{

    /** @var CentreonDBAdapter */
    protected $dbAdapter;

    protected $serverIp;

    protected $centralIp;

    protected $dbUser;

    protected $dbPassword;

    protected $name;

    protected $onePeerRetention = false;

    protected $shouldInsertBamBrokers = false;

    protected $isLinkedToCentralServer = false;

    protected $brokerID = null;

    public function __construct(CentreonDBAdapter $dbAdapter)
    {
        $this->dbAdapter = $dbAdapter;
    }

    abstract protected function insertConfigCentreonBroker(int $serverID): void;

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

    /**
     * Set one peer retention mode
     *
     * @param bool $onePeerRetention if one peer retention mode is enabled
     */
    public function setOnePeerRetention(bool $onePeerRetention): void
    {
        $this->onePeerRetention = $onePeerRetention;
    }

    protected function getDbAdapter(): CentreonDBAdapter
    {
        return $this->dbAdapter;
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

        $this->insertConfigResources($serverID);

        $this->insertConfigCentreonBroker($serverID);

        if ($this->shouldInsertBamBrokers && $this->isRemote()) {
            $this->insertBamBrokers();
        }

        $this->getDbAdapter()->commit();

        return $serverID;
    }

    protected function insertNagiosServer()
    {
        return $this->insertWithAdapter('nagios_server', NagiosServer::getConfiguration($this->name, $this->serverIp));
    }

    protected function insertConfigNagios($serverID)
    {
        $configID = $this->insertWithAdapter('cfg_nagios', CfgNagios::getConfiguration($this->name, $serverID));

        $configBroker = CfgNagiosBrokerModule::getConfiguration($configID, $this->name);

        $this->insertWithAdapter('cfg_nagios_broker_module', $configBroker[0]);
        $this->insertWithAdapter('cfg_nagios_broker_module', $configBroker[1]);

        return $configID;
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

        if ($results[0]->resource_name != '$USER1$' || $results[1]->resource_name != '$CENTREONPLUGINS$') {
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

        $bamBrokerInfoData = BamBrokerCfgInfo::getConfiguration($conf_centreon['password']);

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
        } catch (\Exception $e) {
            $this->getDbAdapter()->rollBack();
            throw new \Exception("Error inserting remote configuration. Rolling back. Table name: {$table}.");
        }

        return $result;
    }

    public function shouldInsertBamBrokers()
    {
        $this->shouldInsertBamBrokers = true;
    }

    public function isLinkedToCentralServer()
    {
        $this->isLinkedToCentralServer = true;
    }

    protected function isRemote(): bool
    {
        return false;
    }
}
