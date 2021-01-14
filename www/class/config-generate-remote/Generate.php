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

namespace ConfigGenerateRemote;

use \PDO;
use \Exception;

// file centreon.config.php may not exist in test environment
$configFile = realpath(__DIR__ . "/../../../config/centreon.config.php");
if ($configFile !== false) {
    require_once $configFile;
}

require_once __DIR__ . '/Backend.php';
require_once __DIR__ . '/Abstracts/AbstractObject.php';
require_once __DIR__ . '/HostTemplate.php';
require_once __DIR__ . '/Command.php';
require_once __DIR__ . '/TimePeriod.php';
require_once __DIR__ . '/HostGroup.php';
require_once __DIR__ . '/ServiceGroup.php';
require_once __DIR__ . '/Contact.php';
require_once __DIR__ . '/ContactGroup.php';
require_once __DIR__ . '/ServiceTemplate.php';
require_once __DIR__ . '/Service.php';
require_once __DIR__ . '/Media.php';
require_once __DIR__ . '/MacroService.php';
require_once __DIR__ . '/Host.php';
require_once __DIR__ . '/ServiceCategory.php';
require_once __DIR__ . '/Resource.php';
require_once __DIR__ . '/Engine.php';
require_once __DIR__ . '/Broker.php';
require_once __DIR__ . '/Graph.php';
require_once __DIR__ . '/Manifest.php';
require_once __DIR__ . '/HostCategory.php';
require_once __DIR__ . '/Curves.php';
require_once __DIR__ . '/Trap.php';
require_once __DIR__ . '/PlatformTopology.php';
require_once __DIR__ . '/Relations/BrokerInfo.php';
require_once __DIR__ . '/Relations/ViewImgDirRelation.php';
require_once __DIR__ . '/Relations/ViewImageDir.php';
require_once __DIR__ . '/Relations/ExtendedServiceInformation.php';
require_once __DIR__ . '/Relations/ExtendedHostInformation.php';
require_once __DIR__ . '/Relations/HostServiceRelation.php';
require_once __DIR__ . '/Relations/HostTemplateRelation.php';
require_once __DIR__ . '/Relations/MacroHost.php';
require_once __DIR__ . '/Relations/TimePeriodExceptions.php';
require_once __DIR__ . '/Relations/ContactGroupHostRelation.php';
require_once __DIR__ . '/Relations/ContactGroupServiceRelation.php';
require_once __DIR__ . '/Relations/ContactHostRelation.php';
require_once __DIR__ . '/Relations/ContactServiceRelation.php';
require_once __DIR__ . '/Relations/ContactHostCommandsRelation.php';
require_once __DIR__ . '/Relations/ContactServiceCommandsRelation.php';
require_once __DIR__ . '/Relations/HostCategoriesRelation.php';
require_once __DIR__ . '/Relations/ServiceCategoriesRelation.php';
require_once __DIR__ . '/Relations/HostGroupRelation.php';
require_once __DIR__ . '/Relations/ServiceGroupRelation.php';
require_once __DIR__ . '/Relations/TrapsServiceRelation.php';
require_once __DIR__ . '/Relations/TrapsVendor.php';
require_once __DIR__ . '/Relations/TrapsGroupRelation.php';
require_once __DIR__ . '/Relations/TrapsGroup.php';
require_once __DIR__ . '/Relations/TrapsMatching.php';
require_once __DIR__ . '/Relations/TrapsPreexec.php';
require_once __DIR__ . '/Relations/NagiosServer.php';
require_once __DIR__ . '/Relations/CfgResourceInstanceRelation.php';

class Generate
{
    private $pollerCache = [];
    private $backendInstance = null;
    private $currentPoller = null;
    private $installedModules = null;
    private $moduleObjects = null;
    protected $dependencyInjector = null;

    /**
     * Constructor
     *
     * @param \Pimple\Container $dependencyInjector
     */
    public function __construct(\Pimple\Container $dependencyInjector)
    {
        $this->dependencyInjector = $dependencyInjector;
        $this->backendInstance = Backend::getInstance($this->dependencyInjector);
    }

    /**
     * Remove delimiters and add ucfirst on following string
     *
     * @param array $delimiters
     * @param string $string
     * @return string
     */
    private function ucFirst(array $delimiters, string $string): string
    {
        $string = str_replace($delimiters, $delimiters[0], $string);
        $result = '';
        foreach (explode($delimiters[0], $string) as $value) {
            $result .= ucfirst($value);
        }
        return $result;
    }

    /**
     * Get poller information
     *
     * @param integer $pollerId
     * @return void
     */
    private function getPollerFromId(int $pollerId)
    {
        $stmt = $this->backendInstance->db->prepare(
            "SELECT * FROM nagios_server
            WHERE id = :poller_id"
        );
        $stmt->bindParam(':poller_id', $pollerId, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $this->currentPoller = array_pop($result);
        if (is_null($this->currentPoller)) {
            throw new Exception("Cannot find poller id '" . $pollerId . "'");
        }
    }

    /**
     * Get pollers information
     *
     * @param integer $remoteId
     * @return void
     */
    private function getPollersFromRemote(int $remoteId)
    {
        $stmt = $this->backendInstance->db->prepare(
            "SELECT ns1.*
            FROM nagios_server AS ns1
            WHERE ns1.remote_id = :remote_id
            GROUP BY ns1.id
            UNION
            SELECT ns2.*
            FROM nagios_server AS ns2
            INNER JOIN rs_poller_relation AS rspr ON rspr.poller_server_id = ns2.id
            AND rspr.remote_server_id = :remote_id
            GROUP BY ns2.id"
        );
        $stmt->bindParam(':remote_id', $remoteId, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if (is_null($result)) {
            $result = [];
        }

        return $result;
    }

    /**
     * Reset linked objects
     *
     * @return void
     */
    public function resetObjectsEngine()
    {
        Host::getInstance($this->dependencyInjector)->reset();
        Service::getInstance($this->dependencyInjector)->reset();
    }

    /**
     * Generate host and engine objects
     *
     * @param string $username
     * @return void
     */
    private function configPoller($username = 'unknown')
    {
        $this->resetObjectsEngine();

        Host::getInstance($this->dependencyInjector)->generateFromPollerId(
            $this->currentPoller['id'],
            $this->currentPoller['localhost']
        );

        Engine::getInstance($this->dependencyInjector)->generateFromPoller($this->currentPoller);
        Broker::getInstance($this->dependencyInjector)->generateFromPoller($this->currentPoller);
    }

    /**
     * Generate remote server configuration
     *
     * @param int $remoteServerId
     * @param string $username
     * @return void
     */
    public function configRemoteServerFromId(int $remoteServerId, $username = 'unknown')
    {
        try {
            $this->backendInstance->setUserName($username);
            $this->backendInstance->initPath($remoteServerId);
            $this->backendInstance->setPollerId($remoteServerId);
            Manifest::getInstance($this->dependencyInjector)->clean();
            Manifest::getInstance($this->dependencyInjector)->addRemoteServer($remoteServerId);

            $this->getPollerFromId($remoteServerId);
            $this->currentPoller['localhost'] = 1;
            $this->currentPoller['remote_id'] = 'NULL';
            $this->currentPoller['remote_server_use_as_proxy'] = 0;
            $this->configPoller($username);
            Relations\NagiosServer::getInstance($this->dependencyInjector)->add($this->currentPoller, $remoteServerId);
            PlatformTopology::getInstance($this->dependencyInjector)->generateFromRemoteServerId($remoteServerId);

            $pollers = $this->getPollersFromRemote($remoteServerId);
            foreach ($pollers as $poller) {
                $poller['localhost'] = 0;
                $poller['remote_id'] = 'NULL';
                $poller['remote_server_use_as_proxy'] = 0;
                $this->currentPoller = $poller;
                $this->configPoller($username);
                Relations\NagiosServer::getInstance($this->dependencyInjector)->add($poller, $poller['id']);
                Manifest::getInstance($this->dependencyInjector)->addPoller($poller['id']);
            }

            $this->generateModuleObjects($remoteServerId);
            $this->backendInstance->movePath($remoteServerId);
            $this->resetObjects();
        } catch (Exception $e) {
            $this->resetObjects();
            $this->backendInstance->cleanPath();
            throw new Exception(
                'Exception received : ' . $e->getMessage() . ' [file: ' . $e->getFile() .
                '] [line: ' . $e->getLine() . "]\n"
            );
        }
    }

    /**
     * Get installed modules
     *
     * @return void
     */
    public function getInstalledModules()
    {
        if (!is_null($this->installedModules)) {
            return $this->installedModules;
        }
        $this->installedModules = [];
        $stmt = $this->backendInstance->db->prepare("SELECT name FROM modules_informations");
        $stmt->execute();
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $value) {
            $this->installedModules[] = $value['name'];
        }
    }

    /**
     * Get module generate objects
     *
     * @return void
     */
    public function getModuleObjects()
    {
        $this->moduleObjects = [];

        $this->getInstalledModules();

        foreach ($this->installedModules as $module) {
            $generateFile = __DIR__ . '/../../modules/' . $module . '/GenerateFilesRemote/Generate.php';
            if (file_exists($generateFile)) {
                require_once $generateFile;
                $module = $this->ucFirst(['-', '_', ' '], $module);
                $class = '\\' . $module . '\ConfigGenerateRemote\\Generate';
                if (class_exists($class)) {
                    $this->moduleObjects[] = $class;
                }
            }
        }
    }

    /**
     * Generate objects from modules
     *
     * @param int $remoteServerId
     * @return void
     */
    public function generateModuleObjects(int $remoteServerId)
    {
        if (is_null($this->moduleObjects)) {
            $this->getModuleObjects();
        }

        foreach ($this->moduleObjects as $moduleObject) {
            $module = new $moduleObject($this->dependencyInjector);
            $module->configRemoteServerFromId($remoteServerId);
        }
    }

    /**
     * Reset objects from modules
     *
     * @return void
     */
    public function resetModuleObjects()
    {
        if (is_null($this->moduleObjects)) {
            $this->getModuleObjects();
        }
        if (is_array($this->moduleObjects)) {
            foreach ($this->moduleObjects as $module_object) {
                $module_object::getInstance($this->dependencyInjector)->reset();
            }
        }
    }

    /**
     * Reset objects
     *
     * @return void
     */
    private function resetObjects()
    {
        Host::getInstance($this->dependencyInjector)->reset(true);
        Service::getInstance($this->dependencyInjector)->reset(true);
        HostTemplate::getInstance($this->dependencyInjector)->reset();
        ServiceGroup::getInstance($this->dependencyInjector)->reset();
        HostTemplate::getInstance($this->dependencyInjector)->reset();
        Command::getInstance($this->dependencyInjector)->reset();
        Contact::getInstance($this->dependencyInjector)->reset();
        ContactGroup::getInstance($this->dependencyInjector)->reset();
        Curves::getInstance($this->dependencyInjector)->reset();
        Engine::getInstance($this->dependencyInjector)->reset();
        Broker::getInstance($this->dependencyInjector)->reset();
        Graph::getInstance($this->dependencyInjector)->reset();
        HostCategory::getInstance($this->dependencyInjector)->reset();
        HostGroup::getInstance($this->dependencyInjector)->reset();
        MacroService::getInstance($this->dependencyInjector)->reset();
        Media::getInstance($this->dependencyInjector)->reset();
        Resource::getInstance($this->dependencyInjector)->reset();
        ServiceCategory::getInstance($this->dependencyInjector)->reset();
        ServiceTemplate::getInstance($this->dependencyInjector)->reset();
        TimePeriod::getInstance($this->dependencyInjector)->reset();
        Trap::getInstance($this->dependencyInjector)->reset();
        PlatformTopology::getInstance($this->dependencyInjector)->reset();
        Relations\BrokerInfo::getInstance($this->dependencyInjector)->reset();
        Relations\CfgResourceInstanceRelation::getInstance($this->dependencyInjector)->reset();
        Relations\ContactGroupHostRelation::getInstance($this->dependencyInjector)->reset();
        Relations\ContactGroupServiceRelation::getInstance($this->dependencyInjector)->reset();
        Relations\ContactHostcommandsRelation::getInstance($this->dependencyInjector)->reset();
        Relations\ContactHostRelation::getInstance($this->dependencyInjector)->reset();
        Relations\ContactServicecommandsRelation::getInstance($this->dependencyInjector)->reset();
        Relations\ContactServiceRelation::getInstance($this->dependencyInjector)->reset();
        Relations\ExtendedHostInformation::getInstance($this->dependencyInjector)->reset();
        Relations\ExtendedServiceInformation::getInstance($this->dependencyInjector)->reset();
        Relations\HostCategoriesRelation::getInstance($this->dependencyInjector)->reset();
        Relations\HostGroupRelation::getInstance($this->dependencyInjector)->reset();
        Relations\HostServiceRelation::getInstance($this->dependencyInjector)->reset();
        Relations\HostTemplateRelation::getInstance($this->dependencyInjector)->reset();
        Relations\HostPollerRelation::getInstance($this->dependencyInjector)->reset();
        Relations\MacroHost::getInstance($this->dependencyInjector)->reset();
        Relations\NagiosServer::getInstance($this->dependencyInjector)->reset();
        Relations\ServiceCategoriesRelation::getInstance($this->dependencyInjector)->reset();
        Relations\ServiceGroupRelation::getInstance($this->dependencyInjector)->reset();
        Relations\TimePeriodExceptions::getInstance($this->dependencyInjector)->reset();
        Relations\TrapsGroup::getInstance($this->dependencyInjector)->reset();
        Relations\TrapsGroupRelation::getInstance($this->dependencyInjector)->reset();
        Relations\TrapsMatching::getInstance($this->dependencyInjector)->reset();
        Relations\TrapsPreexec::getInstance($this->dependencyInjector)->reset();
        Relations\TrapsServiceRelation::getInstance($this->dependencyInjector)->reset();
        Relations\TrapsVendor::getInstance($this->dependencyInjector)->reset();
        Relations\ViewImageDir::getInstance($this->dependencyInjector)->reset();
        Relations\ViewImgDirRelation::getInstance($this->dependencyInjector)->reset();
    }

    /**
     * Reset the cache and the instance
     */
    public function reset(): void
    {
        $this->pollerCache = [];
        $this->currentPoller = null;
        $this->installedModules = null;
        $this->moduleObjects = null;
    }
}
