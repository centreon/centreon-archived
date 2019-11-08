<?php
/*
 * Copyright 2005-2019 Centreon
 * Centreon is developed by : Julien Mathis and Romain Le Merlus under
 * GPL Licence 2.0.
 *
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License as published by the Free Software
 * Foundation ; either version 2 of the License.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A
 * PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with
 * this program; if not, see <http://www.gnu.org/licenses>.
 *
 * Linking this program statically or dynamically with other modules is making a
 * combined work based on this program. Thus, the terms and conditions of the GNU
 * General Public License cover the whole combination.
 *
 * As a special exception, the copyright holders of this program give Centreon
 * permission to link this program with independent modules to produce an executable,
 * regardless of the license terms of these independent modules, and to copy and
 * distribute the resulting executable under terms of Centreon choice, provided that
 * Centreon also meet, for each linked independent module, the terms  and conditions
 * of the license of that module. An independent module is a module which is not
 * derived from this program. If you modify this program, you may extend this
 * exception to your version of the program, but you are not obliged to do so. If you
 * do not wish to do so, delete this exception statement from your version.
 *
 * For more information : contact@centreon.com
 *
 *
 */

namespace Centreon\Domain\Entity;

use Centreon\Infrastructure\CentreonLegacyDB\Mapping;
use Symfony\Component\Serializer\Annotation as Serializer;
use PDO;

class NagiosServer implements Mapping\MetadataInterface
{
    const SERIALIZER_GROUP_LIST = 'nagios-server-list';

    /**
     * @Serializer\Groups({NagiosServer::SERIALIZER_GROUP_LIST})
     * @var int
     */
    private $id;

    /**
     * @Serializer\Groups({NagiosServer::SERIALIZER_GROUP_LIST})
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
    private $sshPort;

    /**
     * @var string
     */
    private $sshPrivateKey;

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
    private $remoteServerCentcoreSshProxy;

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
            ->add('sshPrivateKey', 'ssh_private_key')
            ->add('initScriptCentreontrapd', 'init_script_centreontrapd')
            ->add('snmpTrapdPathConf', 'snmp_trapd_path_conf')
            ->add('engineName', 'engine_name')
            ->add('engineVersion', 'engine_version')
            ->add('centreonbrokerLogsPath', 'centreonbroker_logs_path')
            ->add('remoteId', 'remote_id', PDO::PARAM_INT)
            ->add('remoteServerCentcoreSshProxy', 'remote_server_centcore_ssh_proxy');
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId($id = null): void
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

    public function getSshPrivateKey(): ?string
    {
        return $this->sshPrivateKey;
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

    public function getRemoteServerCentcoreSshProxy(): ?string
    {
        return $this->remoteServerCentcoreSshProxy;
    }

    public function setLocalhost(string $localhost = null): void
    {
        $this->localhost = $localhost;
    }

    public function setIsDefault($isDefault = null): void
    {
        $this->isDefault = (int)$isDefault;
    }

    public function setLastRestart($lastRestart = null): void
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

    public function setSshPort($sshPort = null): void
    {
        $this->sshPort = (int)$sshPort;
    }

    public function setSshPrivateKey(string $sshPrivateKey = null): void
    {
        $this->sshPrivateKey = $sshPrivateKey;
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

    public function setRemoteId($remoteId = null): void
    {
        $this->remoteId = (int)$remoteId;
    }

    public function setRemoteServerCentcoreSshProxy(string $remoteServerCentcoreSshProxy = null): void
    {
        $this->remoteServerCentcoreSshProxy = $remoteServerCentcoreSshProxy;
    }
}
