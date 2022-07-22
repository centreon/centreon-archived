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
    final public const SERIALIZER_GROUP_REMOTE_LIST = 'nagios-server-remote-list';
    final public const SERIALIZER_GROUP_LIST = 'nagios-server-list';

    /**
     * @Serializer\Groups({
     *     NagiosServer::SERIALIZER_GROUP_REMOTE_LIST,
     *     NagiosServer::SERIALIZER_GROUP_LIST
     * })
     */
    private ?int $id = null;

    /**
     * @Serializer\Groups({
     *     NagiosServer::SERIALIZER_GROUP_REMOTE_LIST,
     *     NagiosServer::SERIALIZER_GROUP_LIST
     * })
     */
    private ?string $name = null;

    /**
     * @Serializer\Groups({NagiosServer::SERIALIZER_GROUP_LIST})
     */
    private ?string $localhost = null;

    /**
     * @Serializer\SerializedName("default")
     * @Serializer\Groups({NagiosServer::SERIALIZER_GROUP_LIST})
     */
    private ?int $isDefault = null;

    private ?int $lastRestart = null;

    /**
     * @Serializer\SerializedName("ip")
     * @Serializer\Groups({NagiosServer::SERIALIZER_GROUP_REMOTE_LIST})
     */
    private ?string $nsIpAddress = null;

    /**
     * @Serializer\SerializedName("activate")
     * @Serializer\Groups({NagiosServer::SERIALIZER_GROUP_LIST})
     */
    private ?string $nsActivate = null;

    private ?string $engineStartCommand = null;

    private ?string $engineStopCommand = null;

    private ?string $engineRestartCommand = null;

    private ?string $engineReloadCommand = null;

    private ?string $nagiosBin = null;

    private ?string $nagiostatsBin = null;

    private ?string $nagiosPerfdata = null;

    private ?string $brokerReloadCommand = null;

    private ?string $centreonbrokerCfgPath = null;

    private ?string $centreonbrokerModulePath = null;

    private ?string $centreonconnectorPath = null;

    private ?int $gorgoneCommunicationType = null;

    private ?int $sshPort = null;

    private ?int $gorgonePort = null;

    private ?string $initScriptCentreontrapd = null;

    private ?string $snmpTrapdPathConf = null;

    private ?string $engineName = null;

    private ?string $engineVersion = null;

    private ?string $centreonbrokerLogsPath = null;

    private ?int $remoteId = null;

    private ?string $remoteServerUseAsProxy = null;

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

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(string|int $id = null): void
    {
        $this->id = (int)$id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name = null): void
    {
        $this->name = $name;
    }

    public function getLocalhost(): ?string
    {
        return $this->localhost;
    }

    public function getIsDefault(): ?int
    {
        return $this->isDefault;
    }

    public function getLastRestart(): ?int
    {
        return $this->lastRestart;
    }

    public function getNsIpAddress(): ?string
    {
        return $this->nsIpAddress;
    }

    public function getNsActivate(): ?string
    {
        return $this->nsActivate;
    }

    public function getEngineStartCommand(): ?string
    {
        return $this->engineStartCommand;
    }

    public function getEngineStopCommand(): ?string
    {
        return $this->engineStopCommand;
    }

    public function getEngineRestartCommand(): ?string
    {
        return $this->engineRestartCommand;
    }

    public function getEngineReloadCommand(): ?string
    {
        return $this->engineReloadCommand;
    }

    public function getNagiosBin(): ?string
    {
        return $this->nagiosBin;
    }

    public function getNagiostatsBin(): ?string
    {
        return $this->nagiostatsBin;
    }

    public function getNagiosPerfdata(): ?string
    {
        return $this->nagiosPerfdata;
    }

    public function getBrokerReloadCommand(): ?string
    {
        return $this->brokerReloadCommand;
    }

    public function getCentreonbrokerCfgPath(): ?string
    {
        return $this->centreonbrokerCfgPath;
    }

    public function getCentreonbrokerModulePath(): ?string
    {
        return $this->centreonbrokerModulePath;
    }

    public function getCentreonconnectorPath(): ?string
    {
        return $this->centreonconnectorPath;
    }

    public function getSshPort(): ?int
    {
        return $this->sshPort;
    }

    public function getGorgoneCommunicationType(): ?int
    {
        return $this->gorgoneCommunicationType;
    }

    public function getGorgonePort(): ?int
    {
        return $this->gorgonePort;
    }

    public function getInitScriptCentreontrapd(): ?string
    {
        return $this->initScriptCentreontrapd;
    }

    public function getSnmpTrapdPathConf(): ?string
    {
        return $this->snmpTrapdPathConf;
    }

    public function getEngineName(): ?string
    {
        return $this->engineName;
    }

    public function getEngineVersion(): ?string
    {
        return $this->engineVersion;
    }

    public function getCentreonbrokerLogsPath(): ?string
    {
        return $this->centreonbrokerLogsPath;
    }

    public function getRemoteId(): ?int
    {
        return $this->remoteId;
    }

    public function getRemoteServerUseAsProxy(): ?string
    {
        return $this->remoteServerUseAsProxy;
    }

    public function setLocalhost(string $localhost = null): void
    {
        $this->localhost = $localhost;
    }

    public function setIsDefault(string|int $isDefault = null): void
    {
        $this->isDefault = (int)$isDefault;
    }

    public function setLastRestart(string|int $lastRestart = null): void
    {
        $this->lastRestart = (int)$lastRestart;
    }

    public function setNsIpAddress(string $nsIpAddress = null): void
    {
        $this->nsIpAddress = $nsIpAddress;
    }

    public function setNsActivate(string $nsActivate = null): void
    {
        $this->nsActivate = $nsActivate;
    }

    public function setEngineStartCommand(string $engineStartCommand = null): void
    {
        $this->engineStartCommand = $engineStartCommand;
    }

    public function setEngineStopCommand(string $engineStopCommand = null): void
    {
        $this->engineStopCommand = $engineStopCommand;
    }

    public function setEngineRestartCommand(string $engineRestartCommand = null): void
    {
        $this->engineRestartCommand = $engineRestartCommand;
    }

    public function setEngineReloadCommand(string $engineReloadCommand = null): void
    {
        $this->engineReloadCommand = $engineReloadCommand;
    }

    public function setNagiosBin(string $nagiosBin = null): void
    {
        $this->nagiosBin = $nagiosBin;
    }

    public function setNagiostatsBin(string $nagiostatsBin = null): void
    {
        $this->nagiostatsBin = $nagiostatsBin;
    }

    public function setNagiosPerfdata(string $nagiosPerfdata = null): void
    {
        $this->nagiosPerfdata = $nagiosPerfdata;
    }

    public function setBrokerReloadCommand(string $brokerReloadCommand = null): void
    {
        $this->brokerReloadCommand = $brokerReloadCommand;
    }

    public function setCentreonbrokerCfgPath(string $centreonbrokerCfgPath = null): void
    {
        $this->centreonbrokerCfgPath = $centreonbrokerCfgPath;
    }

    public function setCentreonbrokerModulePath(string $centreonbrokerModulePath = null): void
    {
        $this->centreonbrokerModulePath = $centreonbrokerModulePath;
    }

    public function setCentreonconnectorPath(string $centreonconnectorPath = null): void
    {
        $this->centreonconnectorPath = $centreonconnectorPath;
    }

    public function setSshPort(string|int $sshPort = null): void
    {
        $this->sshPort = (int)$sshPort;
    } 

    public function setGorgoneCommunicationType(string|int $gorgoneCommunicationType = null): void
    {
        $this->gorgoneCommunicationType = (int)$gorgoneCommunicationType;
    }

    public function setGorgonePort(string|int $gorgonePort = null): void
    {
        $this->gorgonePort = (int)$gorgonePort;
    }

    public function setInitScriptCentreontrapd(string $initScriptCentreontrapd = null): void
    {
        $this->initScriptCentreontrapd = $initScriptCentreontrapd;
    }

    public function setSnmpTrapdPathConf(string $snmpTrapdPathConf = null): void
    {
        $this->snmpTrapdPathConf = $snmpTrapdPathConf;
    }

    public function setEngineName(string $engineName = null): void
    {
        $this->engineName = $engineName;
    }

    public function setEngineVersion(string $engineVersion = null): void
    {
        $this->engineVersion = $engineVersion;
    }

    public function setCentreonbrokerLogsPath(string $centreonbrokerLogsPath = null): void
    {
        $this->centreonbrokerLogsPath = $centreonbrokerLogsPath;
    }

    public function setRemoteId(string|int $remoteId = null): void
    {
        $this->remoteId = (int)$remoteId;
    }

    public function setRemoteServerUseAsProxy(string $remoteServerUseAsProxy = null): void
    {
        $this->remoteServerUseAsProxy = $remoteServerUseAsProxy;
    }
}
