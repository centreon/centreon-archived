<?php
/*
 * Copyright 2005 - 2019 Centreon (https://www.centreon.com/)
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
declare(strict_types=1);

namespace Centreon\Domain\MonitoringServer;

use JMS\Serializer\Annotation as Serializer;
use Centreon\Domain\Annotation\EntityDescriptor as Desc;

class MonitoringServer
{
    /**
     * @Serializer\Groups({"monitoringserver_main"})
     * @var int Unique id of the poller
     */
    private $id;

    /**
     * @Serializer\Groups({"monitoringserver_main"})
     * @var string Poller's name
     */
    private $name;

    /**
     * @Serializer\Groups({"monitoringserver_main"})
     * @Desc(column="localhost", modifier="setLocalhost")
     * @var bool
     */
    private $isLocalhost = false;

    /**
     * @Serializer\Groups({"monitoringserver_main"})
     * @var bool
     */
    private $isDefault = false;

    /**
     * @Serializer\Groups({"monitoringserver_main"})
     * @var \DateTime|null
     */
    private $lastRestart;

    /**
     * @Serializer\Groups({"monitoringserver_main"})
     * @Desc(column="ns_ip_address", modifier="setAddress")
     * @var string|null IP address
     */
    private $address;

    /**
     * @Serializer\Groups({"monitoringserver_main"})
     * @Desc(column="ns_activate", modifier="setActivate")
     * @var bool
     */
    private $isActivate = true;

    /**
     * @Serializer\Groups({"monitoringserver_main"})
     * @var string|null Command to start Engine
     */
    private $engineStartCommand;

    /**
     * @Serializer\Groups({"monitoringserver_main"})
     * @var string|null Command to stop Engine
     */
    private $engineStopCommand;

    /**
     * @Serializer\Groups({"monitoringserver_main"})
     * @var string|null Command to restart Engine
     */
    private $engineRestartCommand;

    /**
     * @Serializer\Groups({"monitoringserver_main"})
     * @var string|null Command to reload Engine
     */
    private $engineReloadCommand;

    /**
     * @Serializer\Groups({"monitoringserver_main"})
     * @var string|null Full path of the binary Engine
     */
    private $nagiosBin;

    /**
     * @Serializer\Groups({"monitoringserver_main"})
     * @var string|null Full binary path of the statistics engine
     */
    private $nagiostatsBin;

    /**
     * @Serializer\Groups({"monitoringserver_main"})
     * @var string|null
     */
    private $nagiosPerfdata;

    /**
     * @Serializer\Groups({"monitoringserver_main"})
     * @var string|null Command to reload Broker
     */
    private $brokerReloadCommand;

    /**
     * @Serializer\Groups({"monitoringserver_main"})
     * @var string|null
     */
    private $centreonbrokerCfgPath;

    /**
     * @Serializer\Groups({"monitoringserver_main"})
     * @var string|null
     */
    private $centreonbrokerModulePath;

    /**
     * @Serializer\Groups({"monitoringserver_main"})
     * @var string|null
     */
    private $centreonconnectorPath;

    /**
     * @Serializer\Groups({"monitoringserver_main"})
     * @var int SSH port
     */
    private $sshPort;

    /**
     * @Serializer\Groups({"monitoringserver_main"})
     * @var string|null
     */
    private $sshPrivateKey;

    /**
     * @Serializer\Groups({"monitoringserver_main"})
     * @var string|null
     */
    private $initScriptCentreontrapd;

    /**
     * @Serializer\Groups({"monitoringserver_main"})
     * @var string|null
     */
    private $snmpTrapdPathConf;

    /**
     * @Serializer\Groups({"monitoringserver_main"})
     * @var string|null
     */
    private $engineName;

    /**
     * @Serializer\Groups({"monitoringserver_main"})
     * @var string|null
     */
    private $engineVersion;

    /**
     * @Serializer\Groups({"monitoringserver_main"})
     * @var string|null
     */
    private $centreonbrokerLogsPath;

    /**
     * @Serializer\Groups({"monitoringserver_main"})
     * @var int|null
     */
    private $remoteId;

    /**
     * @Serializer\Groups({"monitoringserver_main"})
     * @var bool
     */
    private $remoteServerCentcoreSshProxy = true;

    /**
     * @Serializer\Groups({"monitoringserver_main"})
     * @var bool
     */
    private $isUpdated = false;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return MonitoringServer
     */
    public function setId(int $id): MonitoringServer
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return MonitoringServer
     */
    public function setName(string $name): MonitoringServer
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return bool
     */
    public function isLocalhost(): bool
    {
        return $this->isLocalhost;
    }

    /**
     * @param bool $isLocalhost
     * @return MonitoringServer
     */
    public function setLocalhost(bool $isLocalhost): MonitoringServer
    {
        $this->isLocalhost = $isLocalhost;
        return $this;
    }

    /**
     * @return bool
     */
    public function isDefault(): bool
    {
        return $this->isDefault;
    }

    /**
     * @param bool $isDefault
     * @return MonitoringServer
     */
    public function setIsDefault(bool $isDefault): MonitoringServer
    {
        $this->isDefault = $isDefault;
        return $this;
    }

    /**
     * @return \DateTime|null
     */
    public function getLastRestart(): ?\DateTime
    {
        return $this->lastRestart;
    }

    /**
     * @param \DateTime $lastRestart
     * @return MonitoringServer
     */
    public function setLastRestart(?\DateTime $lastRestart): MonitoringServer
    {
        $this->lastRestart = $lastRestart;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getAddress(): ?string
    {
        return $this->address;
    }

    /**
     * @param string|null $address
     * @return MonitoringServer
     */
    public function setAddress(?string $address): MonitoringServer
    {
        $this->address = $address;
        return $this;
    }

    /**
     * @return bool
     */
    public function isActivate(): bool
    {
        return $this->isActivate;
    }

    /**
     * @param bool $isActivate
     * @return MonitoringServer
     */
    public function setActivate(bool $isActivate): MonitoringServer
    {
        $this->isActivate = $isActivate;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getEngineStartCommand(): ?string
    {
        return $this->engineStartCommand;
    }

    /**
     * @param string|null $engineStartCommand
     * @return MonitoringServer
     */
    public function setEngineStartCommand(?string $engineStartCommand): MonitoringServer
    {
        $this->engineStartCommand = $engineStartCommand;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getEngineStopCommand(): ?string
    {
        return $this->engineStopCommand;
    }

    /**
     * @param string|null $engineStopCommand
     * @return MonitoringServer
     */
    public function setEngineStopCommand(?string $engineStopCommand): MonitoringServer
    {
        $this->engineStopCommand = $engineStopCommand;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getEngineRestartCommand(): ?string
    {
        return $this->engineRestartCommand;
    }

    /**
     * @param string|null $engineRestartCommand
     * @return MonitoringServer
     */
    public function setEngineRestartCommand(?string $engineRestartCommand): MonitoringServer
    {
        $this->engineRestartCommand = $engineRestartCommand;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getEngineReloadCommand(): ?string
    {
        return $this->engineReloadCommand;
    }

    /**
     * @param string|null $engineReloadCommand
     * @return MonitoringServer
     */
    public function setEngineReloadCommand(?string $engineReloadCommand): MonitoringServer
    {
        $this->engineReloadCommand = $engineReloadCommand;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getNagiosBin(): ?string
    {
        return $this->nagiosBin;
    }

    /**
     * @param string|null $nagiosBin
     * @return MonitoringServer
     */
    public function setNagiosBin(?string $nagiosBin): MonitoringServer
    {
        $this->nagiosBin = $nagiosBin;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getNagiostatsBin(): ?string
    {
        return $this->nagiostatsBin;
    }

    /**
     * @param string|null $nagiostatsBin
     * @return MonitoringServer
     */
    public function setNagiostatsBin(?string $nagiostatsBin): MonitoringServer
    {
        $this->nagiostatsBin = $nagiostatsBin;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getNagiosPerfdata(): ?string
    {
        return $this->nagiosPerfdata;
    }

    /**
     * @param string|null $nagiosPerfdata
     * @return MonitoringServer
     */
    public function setNagiosPerfdata(?string $nagiosPerfdata): MonitoringServer
    {
        $this->nagiosPerfdata = $nagiosPerfdata;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getBrokerReloadCommand(): ?string
    {
        return $this->brokerReloadCommand;
    }

    /**
     * @param string|null $brokerReloadCommand
     * @return MonitoringServer
     */
    public function setBrokerReloadCommand(?string $brokerReloadCommand): MonitoringServer
    {
        $this->brokerReloadCommand = $brokerReloadCommand;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getCentreonbrokerCfgPath(): ?string
    {
        return $this->centreonbrokerCfgPath;
    }

    /**
     * @param string|null $centreonbrokerCfgPath
     * @return MonitoringServer
     */
    public function setCentreonbrokerCfgPath(?string $centreonbrokerCfgPath): MonitoringServer
    {
        $this->centreonbrokerCfgPath = $centreonbrokerCfgPath;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getCentreonbrokerModulePath(): ?string
    {
        return $this->centreonbrokerModulePath;
    }

    /**
     * @param string|null $centreonbrokerModulePath
     * @return MonitoringServer
     */
    public function setCentreonbrokerModulePath(?string $centreonbrokerModulePath): MonitoringServer
    {
        $this->centreonbrokerModulePath = $centreonbrokerModulePath;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getCentreonconnectorPath(): ?string
    {
        return $this->centreonconnectorPath;
    }

    /**
     * @param string|null $centreonconnectorPath
     * @return MonitoringServer
     */
    public function setCentreonconnectorPath(?string $centreonconnectorPath): MonitoringServer
    {
        $this->centreonconnectorPath = $centreonconnectorPath;
        return $this;
    }

    /**
     * @return int
     */
    public function getSshPort(): int
    {
        return $this->sshPort;
    }

    /**
     * @param int $sshPort
     * @return MonitoringServer
     */
    public function setSshPort(int $sshPort): MonitoringServer
    {
        $this->sshPort = $sshPort;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getSshPrivateKey(): ?string
    {
        return $this->sshPrivateKey;
    }

    /**
     * @param string|null $sshPrivateKey
     * @return MonitoringServer
     */
    public function setSshPrivateKey(?string $sshPrivateKey): MonitoringServer
    {
        $this->sshPrivateKey = $sshPrivateKey;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getInitScriptCentreontrapd(): ?string
    {
        return $this->initScriptCentreontrapd;
    }

    /**
     * @param string|null $initScriptCentreontrapd
     * @return MonitoringServer
     */
    public function setInitScriptCentreontrapd(?string $initScriptCentreontrapd): MonitoringServer
    {
        $this->initScriptCentreontrapd = $initScriptCentreontrapd;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getSnmpTrapdPathConf(): ?string
    {
        return $this->snmpTrapdPathConf;
    }

    /**
     * @param string|null $snmpTrapdPathConf
     * @return MonitoringServer
     */
    public function setSnmpTrapdPathConf(?string $snmpTrapdPathConf): MonitoringServer
    {
        $this->snmpTrapdPathConf = $snmpTrapdPathConf;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getEngineName(): ?string
    {
        return $this->engineName;
    }

    /**
     * @param string|null $engineName
     * @return MonitoringServer
     */
    public function setEngineName(?string $engineName): MonitoringServer
    {
        $this->engineName = $engineName;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getEngineVersion(): ?string
    {
        return $this->engineVersion;
    }

    /**
     * @param string|null $engineVersion
     * @return MonitoringServer
     */
    public function setEngineVersion(?string $engineVersion): MonitoringServer
    {
        $this->engineVersion = $engineVersion;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getCentreonbrokerLogsPath(): ?string
    {
        return $this->centreonbrokerLogsPath;
    }

    /**
     * @param string|null $centreonbrokerLogsPath
     * @return MonitoringServer
     */
    public function setCentreonbrokerLogsPath(?string $centreonbrokerLogsPath): MonitoringServer
    {
        $this->centreonbrokerLogsPath = $centreonbrokerLogsPath;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getRemoteId(): ?int
    {
        return $this->remoteId;
    }

    /**
     * @param int|null $remoteId
     * @return MonitoringServer
     */
    public function setRemoteId(?int $remoteId): MonitoringServer
    {
        $this->remoteId = $remoteId;
        return $this;
    }

    /**
     * @return bool
     */
    public function isRemoteServerCentcoreSshProxy(): bool
    {
        return $this->remoteServerCentcoreSshProxy;
    }

    /**
     * @param bool $remoteServerCentcoreSshProxy
     * @return MonitoringServer
     */
    public function setRemoteServerCentcoreSshProxy(bool $remoteServerCentcoreSshProxy): MonitoringServer
    {
        $this->remoteServerCentcoreSshProxy = $remoteServerCentcoreSshProxy;
        return $this;
    }

    /**
     * @return bool
     */
    public function isUpdated(): bool
    {
        return $this->isUpdated;
    }

    /**
     * @param bool $isUpdated
     * @return MonitoringServer
     */
    public function setUpdated(bool $isUpdated): MonitoringServer
    {
        $this->isUpdated = $isUpdated;
        return $this;
    }
}
