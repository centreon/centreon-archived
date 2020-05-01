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

namespace Centreon\Domain\Entity;

use Centreon\Infrastructure\CentreonLegacyDB\Mapping;
use Symfony\Component\Serializer\Annotation as Serializer;
use PDO;

class NagiosServer implements Mapping\MetadataInterface
{
    public const SERIALIZER_GROUP_REMOTE_LIST = 'nagios-server-remote-list';
    public const SERIALIZER_GROUP_LIST = 'nagios-server-list';

    /**
     * @Serializer\Groups({
     *     NagiosServer::SERIALIZER_GROUP_REMOTE_LIST,
     *     NagiosServer::SERIALIZER_GROUP_LIST
     * })
     * @var int
     */
    private $id;

    /**
     * @Serializer\Groups({
     *     NagiosServer::SERIALIZER_GROUP_REMOTE_LIST,
     *     NagiosServer::SERIALIZER_GROUP_LIST
     * })
     * @var string
     */
    private $name;

    /**
     * @Serializer\Groups({NagiosServer::SERIALIZER_GROUP_LIST})
     * @var string
     */
    private $localhost;

    /**
     * @Serializer\SerializedName("default")
     * @Serializer\Groups({NagiosServer::SERIALIZER_GROUP_LIST})
     * @var int
     */
    private $isDefault;

    /**
     * @var int
     */
    private $lastRestart;

    /**
     * @Serializer\SerializedName("ip")
     * @Serializer\Groups({NagiosServer::SERIALIZER_GROUP_REMOTE_LIST})
     * @var string
     */
    private $nsIpAddress;

    /**
     * @Serializer\SerializedName("activate")
     * @Serializer\Groups({NagiosServer::SERIALIZER_GROUP_LIST})
     * @var string
     */
    private $nsActivate;

    /**
     * @var string
     */
    private $engineStartCommand;

    /**
     * @var string
     */
    private $engineStopCommand;

    /**
     * @var string
     */
    private $engineRestartCommand;

    /**
     * @var string
     */
    private $engineReloadCommand;

    /**
     * @var string
     */
    private $nagiosBin;

    /**
     * @var string
     */
    private $nagiostatsBin;

    /**
     * @var string
     */
    private $nagiosPerfdata;

    /**
     * @var string
     */
    private $brokerReloadCommand;

    /**
     * @var string
     */
    private $centreonbrokerCfgPath;

    /**
     * @var string
     */
    private $centreonbrokerModulePath;

    /**
     * @var string
     */
    private $centreonconnectorPath;

    /**
     * @var int
     */
    private $gorgoneCommunicationType;

    /**
     * @var int
     */
    private $sshPort;

    /**
     * @var int
     */
    private $gorgonePort;

    /**
     * @var string
     */
    private $initScriptCentreontrapd;

    /**
     * @var string
     */
    private $snmpTrapdPathConf;

    /**
     * @var string
     */
    private $engineName;

    /**
     * @var string
     */
    private $engineVersion;

    /**
     * @var string
     */
    private $centreonbrokerLogsPath;

    /**
     * @var int
     */
    private $remoteId;

    /**
     * @var string
     */
    private $remoteServerUseAsProxy;

    /**
     * {@inheritdoc}
     */
    public static function loadMetadata(Mapping\ClassMetadata $metadata): void
    {
        $metadata->setTableName('nagios_server')
            ->add('id', 'id', PDO::PARAM_INT, null, true)
            ->add('name', 'name')
            ->add('localhost', 'localhost')
            ->add('isDefault', 'is_default', PDO::PARAM_INT)
            ->add('lastRestart', 'last_restart', PDO::PARAM_INT)
            ->add('nsIpAddress', 'ns_ip_address')
            ->add('nsActivate', 'ns_activate')
            ->add('engineStartCommand', 'engine_start_command')
            ->add('engineStopCommand', 'engine_stop_command')
            ->add('engineRestartCommand', 'engine_restart_command')
            ->add('engineReloadCommand', 'engine_reload_command')
            ->add('nagiosBin', 'nagios_bin')
            ->add('nagiostatsBin', 'nagiostats_bin')
            ->add('nagiosPerfdata', 'nagios_perfdata')
            ->add('brokerReloadCommand', 'broker_reload_command')
            ->add('centreonbrokerCfgPath', 'centreonbroker_cfg_path')
            ->add('centreonbrokerModulePath', 'centreonbroker_module_path')
            ->add('centreonconnectorPath', 'centreonconnector_path')
            ->add('sshPort', 'ssh_port', PDO::PARAM_INT)
            ->add('gorgoneCommunicationType', 'gorgone_communication_type', PDO::PARAM_INT)
            ->add('gorgonePort', 'gorgone_port', PDO::PARAM_INT)
            ->add('initScriptCentreontrapd', 'init_script_centreontrapd')
            ->add('snmpTrapdPathConf', 'snmp_trapd_path_conf')
            ->add('engineName', 'engine_name')
            ->add('engineVersion', 'engine_version')
            ->add('centreonbrokerLogsPath', 'centreonbroker_logs_path')
            ->add('remoteId', 'remote_id', PDO::PARAM_INT)
            ->add('remoteServerUseAsProxy', 'remote_server_use_as_proxy');
    }

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param string|int $id
     * @return void
     */
    public function setId($id = null): void
    {
        $this->id = (int)$id;
    }

    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return void
     */
    public function setName(string $name = null): void
    {
        $this->name = $name;
    }

    /**
     * @return string|null
     */
    public function getLocalhost(): ?string
    {
        return $this->localhost;
    }

    /**
     * @return int|null
     */
    public function getIsDefault(): ?int
    {
        return $this->isDefault;
    }

    /**
     * @return int|null
     */
    public function getLastRestart(): ?int
    {
        return $this->lastRestart;
    }

    /**
     * @return string|null
     */
    public function getNsIpAddress(): ?string
    {
        return $this->nsIpAddress;
    }

    /**
     * @return string|null
     */
    public function getNsActivate(): ?string
    {
        return $this->nsActivate;
    }

    /**
     * @return string|null
     */
    public function getEngineStartCommand(): ?string
    {
        return $this->engineStartCommand;
    }

    /**
     * @return string|null
     */
    public function getEngineStopCommand(): ?string
    {
        return $this->engineStopCommand;
    }

    /**
     * @return string|null
     */
    public function getEngineRestartCommand(): ?string
    {
        return $this->engineRestartCommand;
    }

    /**
     * @return string|null
     */
    public function getEngineReloadCommand(): ?string
    {
        return $this->engineReloadCommand;
    }

    /**
     * @return string|null
     */
    public function getNagiosBin(): ?string
    {
        return $this->nagiosBin;
    }

    /**
     * @return string|null
     */
    public function getNagiostatsBin(): ?string
    {
        return $this->nagiostatsBin;
    }

    /**
     * @return string|null
     */
    public function getNagiosPerfdata(): ?string
    {
        return $this->nagiosPerfdata;
    }

    /**
     * @return string|null
     */
    public function getBrokerReloadCommand(): ?string
    {
        return $this->brokerReloadCommand;
    }

    /**
     * @return string|null
     */
    public function getCentreonbrokerCfgPath(): ?string
    {
        return $this->centreonbrokerCfgPath;
    }

    /**
     * @return string|null
     */
    public function getCentreonbrokerModulePath(): ?string
    {
        return $this->centreonbrokerModulePath;
    }

    /**
     * @return string|null
     */
    public function getCentreonconnectorPath(): ?string
    {
        return $this->centreonconnectorPath;
    }

    /**
     * @return integer|null
     */
    public function getSshPort(): ?int
    {
        return $this->sshPort;
    }

    /**
     * @return int|null
     */
    public function getGorgoneCommunicationType(): ?int
    {
        return $this->gorgoneCommunicationType;
    }

    /**
     * @return int|null
     */
    public function getGorgonePort(): ?int
    {
        return $this->gorgonePort;
    }

    /**
     * @return string|null
     */
    public function getInitScriptCentreontrapd(): ?string
    {
        return $this->initScriptCentreontrapd;
    }

    /**
     * @return string|null
     */
    public function getSnmpTrapdPathConf(): ?string
    {
        return $this->snmpTrapdPathConf;
    }

    /**
     * @return string|null
     */
    public function getEngineName(): ?string
    {
        return $this->engineName;
    }

    /**
     * @return string|null
     */
    public function getEngineVersion(): ?string
    {
        return $this->engineVersion;
    }

    /**
     * @return string|null
     */
    public function getCentreonbrokerLogsPath(): ?string
    {
        return $this->centreonbrokerLogsPath;
    }

    /**
     * @return int|null
     */
    public function getRemoteId(): ?int
    {
        return $this->remoteId;
    }

    /**
     * @return string|null
     */
    public function getRemoteServerUseAsProxy(): ?string
    {
        return $this->remoteServerUseAsProxy;
    }

    /**
     * @param string $localhost
     * @return void
     */
    public function setLocalhost(string $localhost = null): void
    {
        $this->localhost = $localhost;
    }

    /**
     * @param string|int $isDefault
     * @return void
     */
    public function setIsDefault($isDefault = null): void
    {
        $this->isDefault = (int)$isDefault;
    }

    /**
     * @param string|int $lastRestart
     * @return void
     */
    public function setLastRestart($lastRestart = null): void
    {
        $this->lastRestart = (int)$lastRestart;
    }

    /**
     * @param string $nsIpAddress
     * @return void
     */
    public function setNsIpAddress(string $nsIpAddress = null): void
    {
        $this->nsIpAddress = $nsIpAddress;
    }

    /**
     * @param string $nsActivate
     * @return void
     */
    public function setNsActivate(string $nsActivate = null): void
    {
        $this->nsActivate = $nsActivate;
    }

    /**
     * @param string $engineStartCommand
     * @return void
     */
    public function setEngineStartCommand(string $engineStartCommand = null): void
    {
        $this->engineStartCommand = $engineStartCommand;
    }

    /**
     * @param string $engineStopCommand
     * @return void
     */
    public function setEngineStopCommand(string $engineStopCommand = null): void
    {
        $this->engineStopCommand = $engineStopCommand;
    }

    /**
     * @param string $engineRestartCommand
     * @return void
     */
    public function setEngineRestartCommand(string $engineRestartCommand = null): void
    {
        $this->engineRestartCommand = $engineRestartCommand;
    }

    /**
     * @param string $engineReloadCommand
     * @return void
     */
    public function setEngineReloadCommand(string $engineReloadCommand = null): void
    {
        $this->engineReloadCommand = $engineReloadCommand;
    }

    /**
     * @param string $nagiosBin
     * @return void
     */
    public function setNagiosBin(string $nagiosBin = null): void
    {
        $this->nagiosBin = $nagiosBin;
    }

    /**
     * @param string $nagiostatsBin
     * @return void
     */
    public function setNagiostatsBin(string $nagiostatsBin = null): void
    {
        $this->nagiostatsBin = $nagiostatsBin;
    }

    /**
     * @param string $nagiosPerfdata
     * @return void
     */
    public function setNagiosPerfdata(string $nagiosPerfdata = null): void
    {
        $this->nagiosPerfdata = $nagiosPerfdata;
    }

    /**
     * @param string $brokerReloadCommand
     * @return void
     */
    public function setBrokerReloadCommand(string $brokerReloadCommand = null): void
    {
        $this->brokerReloadCommand = $brokerReloadCommand;
    }

    /**
     * @param string $centreonbrokerCfgPath
     * @return void
     */
    public function setCentreonbrokerCfgPath(string $centreonbrokerCfgPath = null): void
    {
        $this->centreonbrokerCfgPath = $centreonbrokerCfgPath;
    }

    /**
     * @param string $centreonbrokerModulePath
     * @return void
     */
    public function setCentreonbrokerModulePath(string $centreonbrokerModulePath = null): void
    {
        $this->centreonbrokerModulePath = $centreonbrokerModulePath;
    }

    /**
     * @param string $centreonconnectorPath
     * @return void
     */
    public function setCentreonconnectorPath(string $centreonconnectorPath = null): void
    {
        $this->centreonconnectorPath = $centreonconnectorPath;
    }

    /**
     * @param string|int $sshPort
     * @return void
     */
    public function setSshPort($sshPort = null): void
    {
        $this->sshPort = (int)$sshPort;
    } 

    /**
     * @param string|int $gorgoneCommunicationType
     * @return void
     */
    public function setGorgoneCommunicationType($gorgoneCommunicationType = null): void
    {
        $this->gorgoneCommunicationType = (int)$gorgoneCommunicationType;
    }

    /**
     * @param string|int $gorgonePort
     * @return void
     */
    public function setGorgonePort($gorgonePort = null): void
    {
        $this->gorgonePort = (int)$gorgonePort;
    }

    /**
     * @param string $initScriptCentreontrapd
     * @return void
     */
    public function setInitScriptCentreontrapd(string $initScriptCentreontrapd = null): void
    {
        $this->initScriptCentreontrapd = $initScriptCentreontrapd;
    }

    /**
     * @param string $snmpTrapdPathConf
     * @return void
     */
    public function setSnmpTrapdPathConf(string $snmpTrapdPathConf = null): void
    {
        $this->snmpTrapdPathConf = $snmpTrapdPathConf;
    }

    /**
     * @param string $engineName
     * @return void
     */
    public function setEngineName(string $engineName = null): void
    {
        $this->engineName = $engineName;
    }

    /**
     * @param string $engineVersion
     * @return void
     */
    public function setEngineVersion(string $engineVersion = null): void
    {
        $this->engineVersion = $engineVersion;
    }

    /**
     * @param string $centreonbrokerLogsPath
     * @return void
     */
    public function setCentreonbrokerLogsPath(string $centreonbrokerLogsPath = null): void
    {
        $this->centreonbrokerLogsPath = $centreonbrokerLogsPath;
    }

    /**
     * @param string|int $remoteId
     * @return void
     */
    public function setRemoteId($remoteId = null): void
    {
        $this->remoteId = (int)$remoteId;
    }

    /**
     * @param string $remoteServerUseAsProxy
     * @return void
     */
    public function setRemoteServerUseAsProxy(string $remoteServerUseAsProxy = null): void
    {
        $this->remoteServerUseAsProxy = $remoteServerUseAsProxy;
    }
}
