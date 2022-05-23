<?php

/*
 * Copyright 2005 - 2022 Centreon (https://www.centreon.com/)
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * For more information : contact@centreon.com
 *
 */

namespace CentreonRemote\Domain\Service\ConfigurationWizard;

use Centreon\Infrastructure\CentreonLegacyDB\CentreonDBAdapter;
use CentreonRemote\Domain\Resources\RemoteConfig\NagiosServer;
use CentreonRemote\Domain\Resources\RemoteConfig\CfgNagios;
use CentreonRemote\Domain\Resources\RemoteConfig\CfgNagiosLogger;
use CentreonRemote\Domain\Resources\RemoteConfig\CfgNagiosBrokerModule;
use CentreonRemote\Domain\Resources\RemoteConfig\BamBrokerCfgInfo;

abstract class ServerConnectionConfigurationService
{
    /** @var CentreonDBAdapter */
    protected $dbAdapter;

    /** @var string|null */
    protected $serverIp;

    /** @var string|null */
    protected $centralIp;

    /** @var string|null */
    protected $dbUser;

    /** @var string|null */
    protected $dbPassword;

    /** @var string|null */
    protected $name;

    /** @var bool */
    protected $onePeerRetention = false;

    /** @var bool */
    protected $shouldInsertBamBrokers = false;

    /** @var bool */
    protected $isLinkedToCentralServer = false;

    /** @var int|null */
    protected $brokerID = null;

    public function __construct(CentreonDBAdapter $dbAdapter)
    {
        $this->dbAdapter = $dbAdapter;
    }

    abstract protected function insertConfigCentreonBroker(int $serverID): void;

    /** @param string|null $ip */
    public function setServerIp($ip): void
    {
        $this->serverIp = $ip;
    }

    /** @param string|null $name */
    public function setName($name): void
    {
        $this->name = $name;
    }

    /** @param string|null $ip */
    public function setCentralIp($ip): void
    {
        $this->centralIp = $ip;
    }

    /** @param string|null $user */
    public function setDbUser($user): void
    {
        $this->dbUser = $user;
    }

    /** @param string|null $password */
    public function setDbPassword($password): void
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
     * @throws \Exception
     * @return int
     */
    public function insert(): int
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

    protected function insertNagiosServer(): int
    {
        return $this->insertWithAdapter('nagios_server', NagiosServer::getConfiguration($this->name, $this->serverIp));
    }

    /**
     * @param int $serverID
     * @return int
     */
    protected function insertConfigNagios($serverID): int
    {
        $configID = $this->insertWithAdapter('cfg_nagios', CfgNagios::getConfiguration($this->name, $serverID));

        $this->insertWithAdapter('cfg_nagios_logger', CfgNagiosLogger::getConfiguration($configID));

        $configBroker = CfgNagiosBrokerModule::getConfiguration($configID, $this->name);

        $this->insertWithAdapter('cfg_nagios_broker_module', $configBroker[0]);
        $this->insertWithAdapter('cfg_nagios_broker_module', $configBroker[1]);

        return $configID;
    }

    /**
     * @throws \Exception
     * @param int $serverID
     */
    protected function insertConfigResources($serverID): void
    {
        $sql = 'SELECT `resource_id`, `resource_name` FROM `cfg_resource`';
        $sql .= "WHERE `resource_name` IN('\$USER1$', '\$CENTREONPLUGINS$') ORDER BY `resource_name` DESC";
        $this->getDbAdapter()->query($sql);
        $results = $this->getDbAdapter()->results();

        if (count($results) < 2) {
            throw new \Exception('Resources records from `cfg_resource` could not be fetched.');
        }

        if (
            $results[0]->resource_name !== '$USER1$'
            || $results[1]->resource_name !== '$CENTREONPLUGINS$'
        ) {
            throw new \Exception('Resources records from `cfg_resource` are not as expected.');
        }

        $userResourceData = ['resource_id' => $results[0]->resource_id, 'instance_id' => $serverID];
        $pluginResourceData = ['resource_id' => $results[1]->resource_id, 'instance_id' => $serverID];

        $this->insertWithAdapter('cfg_resource_instance_relations', $userResourceData);
        $this->insertWithAdapter('cfg_resource_instance_relations', $pluginResourceData);
    }

    /**
     * insert broker log information
     *
     * @param \Generator<array<string,string|int>> $brokerLogs
     */
    protected function insertBrokerLog(\Generator $brokerLogs): void
    {
        foreach ($brokerLogs as $brokerLog) {
            $this->insertWithAdapter('cfg_centreonbroker_log', $brokerLog);
        }
    }

    /**
     * @throws \Exception
     */
    protected function insertBamBrokers(): void
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

    /**
     * @param string $table
     * @param array<string,mixed> $data
     * @throws \Exception
     * @return int
     */
    protected function insertWithAdapter($table, array $data): int
    {
        try {
            $result = $this->getDbAdapter()->insert($table, $data);
        } catch (\Exception $e) {
            $this->getDbAdapter()->rollBack();
            throw new \Exception("Error inserting remote configuration. Rolling back. Table name: {$table}.");
        }

        return $result;
    }

    public function shouldInsertBamBrokers(): void
    {
        $this->shouldInsertBamBrokers = true;
    }

    public function isLinkedToCentralServer(): void
    {
        $this->isLinkedToCentralServer = true;
    }

    protected function isRemote(): bool
    {
        return false;
    }
}
