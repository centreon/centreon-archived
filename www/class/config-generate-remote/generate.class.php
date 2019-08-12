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
$configFile = realpath(dirname(__FILE__) . "/../../../config/centreon.config.php");
if ($configFile !== false) {
    require_once $configFile;
}

require_once dirname(__FILE__) . '/backend.class.php';
require_once dirname(__FILE__) . '/abstract/object.class.php';
require_once dirname(__FILE__) . '/hosttemplate.class.php';
require_once dirname(__FILE__) . '/command.class.php';
require_once dirname(__FILE__) . '/timeperiod.class.php';
require_once dirname(__FILE__) . '/hostgroup.class.php';
require_once dirname(__FILE__) . '/servicegroup.class.php';
require_once dirname(__FILE__) . '/contact.class.php';
require_once dirname(__FILE__) . '/contactgroup.class.php';
require_once dirname(__FILE__) . '/servicetemplate.class.php';
require_once dirname(__FILE__) . '/service.class.php';
require_once dirname(__FILE__) . '/media.class.php';
require_once dirname(__FILE__) . '/macroService.class.php';
require_once dirname(__FILE__) . '/host.class.php';
require_once dirname(__FILE__) . '/serviceCategory.class.php';
require_once dirname(__FILE__) . '/resource.class.php';
require_once dirname(__FILE__) . '/engine.class.php';
require_once dirname(__FILE__) . '/graph.class.php';
require_once dirname(__FILE__) . '/manifest.class.php';
require_once dirname(__FILE__) . '/hostCategory.class.php';
require_once dirname(__FILE__) . '/curves.class.php';
require_once dirname(__FILE__) . '/trap.class.php';
require_once dirname(__FILE__) . '/relations/viewImgDirRelation.class.php';
require_once dirname(__FILE__) . '/relations/viewImageDir.class.php';
require_once dirname(__FILE__) . '/relations/extendedServiceInformation.class.php';
require_once dirname(__FILE__) . '/relations/extendedHostInformation.class.php';
require_once dirname(__FILE__) . '/relations/hostServiceRelation.class.php';
require_once dirname(__FILE__) . '/relations/hostTemplateRelation.class.php';
require_once dirname(__FILE__) . '/relations/macroHost.class.php';
require_once dirname(__FILE__) . '/relations/timeperiodExceptions.class.php';
require_once dirname(__FILE__) . '/relations/contactgroupHostRelation.class.php';
require_once dirname(__FILE__) . '/relations/contactgroupServiceRelation.class.php';
require_once dirname(__FILE__) . '/relations/contactHostRelation.class.php';
require_once dirname(__FILE__) . '/relations/contactServiceRelation.class.php';
require_once dirname(__FILE__) . '/relations/contactHostcommandsRelation.class.php';
require_once dirname(__FILE__) . '/relations/contactServicecommandsRelation.class.php';
require_once dirname(__FILE__) . '/relations/hostcategoriesRelation.class.php';
require_once dirname(__FILE__) . '/relations/serviceCategoriesRelation.class.php';
require_once dirname(__FILE__) . '/relations/hostgroupRelation.class.php';
require_once dirname(__FILE__) . '/relations/servicegroupRelation.class.php';
require_once dirname(__FILE__) . '/relations/trapsServiceRelation.class.php';
require_once dirname(__FILE__) . '/relations/trapsVendor.class.php';
require_once dirname(__FILE__) . '/relations/trapsGroupRelation.class.php';
require_once dirname(__FILE__) . '/relations/trapsGroup.class.php';
require_once dirname(__FILE__) . '/relations/trapsMatching.class.php';
require_once dirname(__FILE__) . '/relations/trapsPreexec.class.php';
require_once dirname(__FILE__) . '/relations/nagiosServer.class.php';
require_once dirname(__FILE__) . '/relations/cfgResourceInstanceRelation.class.php';

class Generate
{
    private $poller_cache = array();
    private $backend_instance = null;
    private $current_poller = null;
    private $installedModules = null;
    private $module_objects = null;
    protected $dependencyInjector = null;

    public function __construct(\Pimple\Container $dependencyInjector)
    {
        $this->dependencyInjector = $dependencyInjector;
        $this->backend_instance = Backend::getInstance($this->dependencyInjector);
    }

    private function ucFirst($delimiters, $string)
    {
        $string = str_replace($delimiters, $delimiters[0], $string);
        $result = '';
        foreach (explode($delimiters[0], $string) as $value) {
            $result .= ucfirst($value);
        }
        return $result;
    }

    private function getPollerFromId($poller_id)
    {
        $query = "SELECT * FROM nagios_server " .
            "WHERE id = :poller_id";
        $stmt = $this->backend_instance->db->prepare($query);
        $stmt->bindParam(':poller_id', $poller_id, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $this->current_poller = array_pop($result);
        if (is_null($this->current_poller)) {
            throw new Exception("Cannot find poller id '" . $poller_id . "'");
        }
    }
    
    private function getPollersFromRemote($remote_id)
    {
        $query = "SELECT * FROM nagios_server " .
            "WHERE remote_id = :remote_id";
        $stmt = $this->backend_instance->db->prepare($query);
        $stmt->bindParam(':remote_id', $remote_id, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if (is_null($result)) {
            $result = array();
        }
        return $result;
    }

    public function resetObjectsEngine()
    {
        Host::getInstance($this->dependencyInjector)->reset();
        Service::getInstance($this->dependencyInjector)->reset();
    }

    private function configPoller($username = 'unknown')
    {
        $this->resetObjectsEngine();

        Host::getInstance($this->dependencyInjector)->generateFromPollerId(
            $this->current_poller['id'],
            $this->current_poller['localhost']
        );

        Engine::getInstance($this->dependencyInjector)->generateFromPoller($this->current_poller);
    }

    public function configRemoteServerFromId($remoteServerId, $username = 'unknown')
    {
        try {
            $this->backend_instance->setUserName($username);
            $this->backend_instance->initPath($remoteServerId);
            $this->backend_instance->setPollerId($remoteServerId);
            Manifest::getInstance($this->dependencyInjector)->addRemoteServer($remoteServerId);

            $this->getPollerFromId($remoteServerId);
            $this->current_poller['localhost'] = 1;
            $this->configPoller($username);
            nagiosServer::getInstance($this->dependencyInjector)->add($this->current_poller, $remoteServerId);

            $pollers = $this->getPollersFromRemote($remoteServerId);
            foreach ($pollers as $poller) {
                $poller['localhost'] = 0;
                $this->current_poller = $poller;
                $this->configPoller($username);
                nagiosServer::getInstance($this->dependencyInjector)->add($poller, $poller['id']);
                Manifest::getInstance($this->dependencyInjector)->addPoller($poller['id']);
            }

            $this->generateModuleObjects($remoteServerId);
            $this->backend_instance->movePath($remoteServerId);
            $this->resetObjects();
        } catch (Exception $e) {
            $this->resetObjects();
            $this->backend_instance->cleanPath();
            throw new Exception('Exception received : ' . $e->getMessage() . " [file: " . $e->getFile() .
                "] [line: " . $e->getLine() . "]\n");
        }
    }

    public function getInstalledModules()
    {
        if (!is_null($this->installedModules)) {
            return $this->installedModules;
        }
        $this->installedModules = array();
        $stmt = $this->backend_instance->db->prepare("SELECT name FROM modules_informations");
        $stmt->execute();
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $value) {
            $this->installedModules[] = $value['name'];
        }
    }

    public function getModuleObjects()
    {
        $this->getInstalledModules();

        foreach ($this->installedModules as $module) {
            if ($files = glob(_CENTREON_PATH_ . 'www/modules/' . $module . '/generate_files_remote/generate.class.php')) {
                foreach ($files as $full_file) {
                    require_once $full_file;
                    $module = $this->ucFirst(array('-', '_', ' '), $module);
                    $file_name = str_replace('.class.php', '', basename($full_file));
                    $class = $module . ucfirst($file_name);
                    if (class_exists('\ConfigGenerateRemote\\' . $class)) {
                        $this->module_objects[] = $class;
                    }
                }
            }
        }
    }

    public function generateModuleObjects($remoteServerId)
    {
        if (is_null($this->module_objects)) {
            $this->getModuleObjects();
        }
        if (is_array($this->module_objects)) {
            foreach ($this->module_objects as $module_object) {
                $module_object = '\ConfigGenerateRemote\\' . $module_object;
                $module = new $module_object($this->dependencyInjector);
                $module->configRemoteServerFromId($remoteServerId);
            }
        }
    }

    public function resetModuleObjects()
    {
        if (is_null($this->module_objects)) {
            $this->getModuleObjects();
        }
        if (is_array($this->module_objects)) {
            foreach ($this->module_objects as $module_object) {
                $module_object::getInstance($this->dependencyInjector)->reset();
            }
        }
    }

    private function resetObjects()
    {
        Host::getInstance($this->dependencyInjector)->reset(true);
        Service::getInstance($this->dependencyInjector)->reset(true);
        HostTemplate::getInstance($this->dependencyInjector)->reset();
        Servicegroup::getInstance($this->dependencyInjector)->reset();
        HostTemplate::getInstance($this->dependencyInjector)->reset();
        Command::getInstance($this->dependencyInjector)->reset();
        Contact::getInstance($this->dependencyInjector)->reset();
        Contactgroup::getInstance($this->dependencyInjector)->reset();
        Curves::getInstance($this->dependencyInjector)->reset();
        Engine::getInstance($this->dependencyInjector)->reset();
        Graph::getInstance($this->dependencyInjector)->reset();
        hostCategory::getInstance($this->dependencyInjector)->reset();
        Hostgroup::getInstance($this->dependencyInjector)->reset();
        macroService::getInstance($this->dependencyInjector)->reset();
        Media::getInstance($this->dependencyInjector)->reset();
        Resource::getInstance($this->dependencyInjector)->reset();
        serviceCategory::getInstance($this->dependencyInjector)->reset();
        ServiceTemplate::getInstance($this->dependencyInjector)->reset();
        Timeperiod::getInstance($this->dependencyInjector)->reset();
        trap::getInstance($this->dependencyInjector)->reset();
        cfgResourceInstanceRelation::getInstance($this->dependencyInjector)->reset();
        contactgroupHostRelation::getInstance($this->dependencyInjector)->reset();
        contactgroupServiceRelation::getInstance($this->dependencyInjector)->reset();
        contactHostcommandsRelation::getInstance($this->dependencyInjector)->reset();
        contactHostRelation::getInstance($this->dependencyInjector)->reset();
        contactServicecommandsRelation::getInstance($this->dependencyInjector)->reset();
        contactServiceRelation::getInstance($this->dependencyInjector)->reset();
        extendedHostInformation::getInstance($this->dependencyInjector)->reset();
        extendedServiceInformation::getInstance($this->dependencyInjector)->reset();
        hostcategoriesRelation::getInstance($this->dependencyInjector)->reset();
        hostgroupRelation::getInstance($this->dependencyInjector)->reset();
        hostServiceRelation::getInstance($this->dependencyInjector)->reset();
        hostTemplateRelation::getInstance($this->dependencyInjector)->reset();
        macroHost::getInstance($this->dependencyInjector)->reset();
        nagiosServer::getInstance($this->dependencyInjector)->reset();
        serviceCategoriesRelation::getInstance($this->dependencyInjector)->reset();
        servicegroupRelation::getInstance($this->dependencyInjector)->reset();        
        timeperiodExceptions::getInstance($this->dependencyInjector)->reset();
        trapsGroup::getInstance($this->dependencyInjector)->reset();
        trapsGroupRelation::getInstance($this->dependencyInjector)->reset();
        trapsMatching::getInstance($this->dependencyInjector)->reset();
        trapsPreexec::getInstance($this->dependencyInjector)->reset();
        trapsServiceRelation::getInstance($this->dependencyInjector)->reset();
        trapsVendor::getInstance($this->dependencyInjector)->reset();
        viewImageDir::getInstance($this->dependencyInjector)->reset();
        viewImgDirRelation::getInstance($this->dependencyInjector)->reset();
    }

    /**
     * Reset the cache and the instance
     */
    public function reset()
    {
        $this->poller_cache = array();
        $this->current_poller = null;
        $this->installedModules = null;
        $this->module_objects = null;
    }
}
