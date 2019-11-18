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

namespace Centreon\Domain\Poller;

use JMS\Serializer\Annotation as Serializer;
use Centreon\Domain\Annotation\EntityDescriptor as Desc;

class Poller
{
    /**
     * @Serializer\Groups({"poller_main"})
     * @var int Unique id of the poller
     */
    private $id;

    /**
     * @Serializer\Groups({"poller_main"})
     * @var string Poller's name
     */
    private $name;

    /**
     * @Serializer\Groups({"poller_main"})
     * @Desc(column="localhost", modifier="setLocalhost")
     * @var bool
     */
    private $isLocalhost = false;

    /**
     * @Serializer\Groups({"poller_main"})
     * @var bool
     */
    private $isDefault = false;

    /**
     * @Serializer\Groups({"poller_main"})
     * @var \DateTime
     */
    private $lastRestart = 0;

    /**
     * @Serializer\Groups({"poller_main"})
     * @Desc(column="ns_ip_address", modifier="setAddress")
     * @var string|null IP address
     */
    private $address;

    /**
     * @Serializer\Groups({"poller_main"})
     * @Desc(column="ns_activate", modifier="setActivate")
     * @var bool
     */
    private $isActivate = true;

    /**
     * @Serializer\Groups({"poller_main"})
     * @var string|null Command to start Engine
     */
    private $engineStartCommand;

    /**
     * @Serializer\Groups({"poller_main"})
     * @var string|null Command to stop Engine
     */
    private $engineStopCommand;

    /**
     * @Serializer\Groups({"poller_main"})
     * @var string|null Command to restart Engine
     */
    private $engineRestartCommand;

    /**
     * @Serializer\Groups({"poller_main"})
     * @var string|null Command to reload Engine
     */
    private $engineReloadCommand;

    /**
     * @Serializer\Groups({"poller_main"})
     * @var string|null Full path of the binary Engine
     */
    private $nagiosBin;

    /**
     * @Serializer\Groups({"poller_main"})
     * @var string|null Full binary path of the statistics engine
     */
    private $nagiostatsBin;

    /**
     * @Serializer\Groups({"poller_main"})
     * @var string|null
     */
    private $nagiosPerfdata;

    /**
     * @Serializer\Groups({"poller_main"})
     * @var string|null Command to reload Broker
     */
    private $brokerReloadCommand;

    /**
     * @Serializer\Groups({"poller_main"})
     * @var string|null
     */
    private $centreonbrokerCfgPath;

    /**
     * @Serializer\Groups({"poller_main"})
     * @var string|null
     */
    private $centreonbrokerModulePath;

    /**
     * @Serializer\Groups({"poller_main"})
     * @var string|null
     */
    private $centreonconnectorPath;

    /**
     * @Serializer\Groups({"poller_main"})
     * @var int SSH port
     */
    private $sshPort;

    /**
     * @Serializer\Groups({"poller_main"})
     * @var string|null
     */
    private $sshPrivateKey;

    /**
     * @Serializer\Groups({"poller_main"})
     * @var string|null
     */
    private $initScriptCentreontrapd;

    /**
     * @Serializer\Groups({"poller_main"})
     * @var string|null
     */
    private $snmpTrapdPathConf;

    /**
     * @Serializer\Groups({"poller_main"})
     * @var string|null
     */
    private $engineName;

    /**
     * @Serializer\Groups({"poller_main"})
     * @var string|null
     */
    private $engineVersion;

    /**
     * @Serializer\Groups({"poller_main"})
     * @var string|null
     */
    private $centreonbrokerLogsPath;

    /**
     * @Serializer\Groups({"poller_main"})
     * @var int|null
     */
    private $remoteId;

    /**
     * @Serializer\Groups({"poller_main"})
     * @var bool
     */
    private $remoteServerCentcoreSshProxy = true;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return Poller
     */
    public function setId(int $id): Poller
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
     * @return Poller
     */
    public function setName(string $name): Poller
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
     * @return Poller
     */
    public function setIsLocalhost(bool $isLocalhost): Poller
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
     * @return Poller
     */
    public function setIsDefault(bool $isDefault): Poller
    {
        $this->isDefault = $isDefault;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getLastRestart(): \DateTime
    {
        return $this->lastRestart;
    }

    /**
     * @param \DateTime $lastRestart
     * @return Poller
     */
    public function setLastRestart(\DateTime $lastRestart): Poller
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
     * @return Poller
     */
    public function setAddress(?string $address): Poller
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
     * @return Poller
     */
    public function setIsActivate(bool $isActivate): Poller
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
     * @return Poller
     */
    public function setEngineStartCommand(?string $engineStartCommand): Poller
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
     * @return Poller
     */
    public function setEngineStopCommand(?string $engineStopCommand): Poller
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
     * @return Poller
     */
    public function setEngineRestartCommand(?string $engineRestartCommand): Poller
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
     * @return Poller
     */
    public function setEngineReloadCommand(?string $engineReloadCommand): Poller
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
     * @return Poller
     */
    public function setNagiosBin(?string $nagiosBin): Poller
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
     * @return Poller
     */
    public function setNagiostatsBin(?string $nagiostatsBin): Poller
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
     * @return Poller
     */
    public function setNagiosPerfdata(?string $nagiosPerfdata): Poller
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
     * @return Poller
     */
    public function setBrokerReloadCommand(?string $brokerReloadCommand): Poller
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
     * @return Poller
     */
    public function setCentreonbrokerCfgPath(?string $centreonbrokerCfgPath): Poller
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
     * @return Poller
     */
    public function setCentreonbrokerModulePath(?string $centreonbrokerModulePath): Poller
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
     * @return Poller
     */
    public function setCentreonconnectorPath(?string $centreonconnectorPath): Poller
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
     * @return Poller
     */
    public function setSshPort(int $sshPort): Poller
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
     * @return Poller
     */
    public function setSshPrivateKey(?string $sshPrivateKey): Poller
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
     * @return Poller
     */
    public function setInitScriptCentreontrapd(?string $initScriptCentreontrapd): Poller
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
     * @return Poller
     */
    public function setSnmpTrapdPathConf(?string $snmpTrapdPathConf): Poller
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
     * @return Poller
     */
    public function setEngineName(?string $engineName): Poller
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
     * @return Poller
     */
    public function setEngineVersion(?string $engineVersion): Poller
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
     * @return Poller
     */
    public function setCentreonbrokerLogsPath(?string $centreonbrokerLogsPath): Poller
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
     * @return Poller
     */
    public function setRemoteId(?int $remoteId): Poller
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
     * @return Poller
     */
    public function setRemoteServerCentcoreSshProxy(bool $remoteServerCentcoreSshProxy): Poller
    {
        $this->remoteServerCentcoreSshProxy = $remoteServerCentcoreSshProxy;
        return $this;
    }
}
