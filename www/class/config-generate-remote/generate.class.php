<?php
/*
 * Copyright 2005-2015 Centreon
 * Centreon is developped by : Julien Mathis and Romain Le Merlus under
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
    private $installed_modules = null;
    private $module_objects = null;
    protected $dependencyInjector = null;

    public function __construct(\Pimple\Container $dependencyInjector)
    {
        $this->dependencyInjector = $dependencyInjector;
        $this->backend_instance = Backend::getInstance($this->dependencyInjector);
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

    private function getPollerFromName($poller_name)
    {
        $query = "SELECT id, localhost, monitoring_engine, centreonconnector_path FROM nagios_server " .
            "WHERE name = :poller_name";
        $stmt = $this->backend_instance->db->prepare($query);
        $stmt->bindParam(':poller_name', $poller_name, PDO::PARAM_STR);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $this->current_poller = array_pop($result);
        if (is_null($this->current_poller)) {
            throw new Exception("Cannot find poller name '" . $poller_name . "'");
        }
    }

    public function resetObjectsEngine()
    {
        Host::getInstance($this->dependencyInjector)->reset();
        Service::getInstance($this->dependencyInjector)->reset();
        #MetaCommand::getInstance($this->dependencyInjector)->reset();
        #MetaTimeperiod::getInstance($this->dependencyInjector)->reset();
        #MetaService::getInstance($this->dependencyInjector)->reset();
        #MetaHost::getInstance($this->dependencyInjector)->reset();
        #Resource::getInstance($this->dependencyInjector)->reset();
        #Engine::getInstance($this->dependencyInjector)->reset();
        $this->resetModuleObjects();
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

    public function configRemoteServerFromId($remote_server_id, $username = 'unknown')
    {
        try {
            $this->backend_instance->setUserName($username);
            $this->backend_instance->initPath($remote_server_id);
            $this->backend_instance->setPollerId($remote_server_id);
            Manifest::getInstance($this->dependencyInjector)->addRemoteServer($remote_server_id);

            $this->getPollerFromId($remote_server_id);
            $this->current_poller['localhost'] = 1;
            $this->configPoller($username);
            nagiosServer::getInstance($this->dependencyInjector)->add($this->current_poller, $remote_server_id);

            $pollers = $this->getPollersFromRemote($remote_server_id);
            foreach ($pollers as $poller) {
                $poller['localhost'] = 0;
                $this->current_poller = $poller;
                $this->configPoller($username);
                nagiosServer::getInstance($this->dependencyInjector)->add($poller, $poller['id']);
                Manifest::getInstance($this->dependencyInjector)->addPoller($poller['id']);
            }

            $this->backend_instance->movePath($remote_server_id);
        } catch (Exception $e) {
            throw new Exception('Exception received : ' . $e->getMessage() . " [file: " . $e->getFile() .
                "] [line: " . $e->getLine() . "]\n");
            $this->backend_instance->cleanPath();
        }
    }

    public function configPollerFromName($poller_name)
    {
        try {
            $this->getPollerFromName($poller_name);
            $this->configPoller();
        } catch (Exception $e) {
            throw new Exception('Exception received : ' . $e->getMessage() . " [file: " . $e->getFile() .
                "] [line: " . $e->getLine() . "]\n");
            $this->backend_instance->cleanPath();
        }
    }

    public function configPollerFromId($poller_id, $username = 'unknown')
    {
        try {
            if (is_null($this->current_poller)) {
                $this->getPollerFromId($poller_id);
            }
            $this->configPoller($username);
        } catch (Exception $e) {
            throw new Exception('Exception received : ' . $e->getMessage() . " [file: " . $e->getFile() .
                "] [line: " . $e->getLine() . "]\n");
            $this->backend_instance->cleanPath();
        }
    }

    public function configPollers($username = 'unknown')
    {
        $query = "SELECT id, localhost, monitoring_engine, centreonconnector_path FROM " .
            "nagios_server WHERE ns_activate = '1'";
        $stmt = $this->backend_instance->db->prepare($query);
        $stmt->execute();
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $value) {
            $this->current_poller = $value;
            $this->configPollerFromId($this->current_poller['id'], $username);
        }
    }

    public function getInstalledModules()
    {
        if (!is_null($this->installed_modules)) {
            return $this->installed_modules;
        }
        $this->installed_modules = array();
        $stmt = $this->backend_instance->db->prepare("SELECT name FROM modules_informations");
        $stmt->execute();
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $value) {
            $this->installed_modules[] = $value['name'];
        }
    }

    public function getModuleObjects()
    {
        $this->getInstalledModules();

        foreach ($this->installed_modules as $module) {
            if ($files = glob(_CENTREON_PATH_ . 'www/modules/' . $module . '/generate_files/*.class.php')) {
                foreach ($files as $full_file) {
                    require_once $full_file;
                    $file_name = str_replace('.class.php', '', basename($full_file));
                    if (class_exists(ucfirst($file_name))) {
                        $this->module_objects[] = ucfirst($file_name);
                    }
                }
            }
        }
    }

    public function generateModuleObjects($type = 1)
    {
        if (is_null($this->module_objects)) {
            $this->getModuleObjects();
        }
        if (is_array($this->module_objects)) {
            foreach ($this->module_objects as $module_object) {
                if (($type == 1 && $module_object::getInstance($this->dependencyInjector)->isEngineObject() == true) ||
                    ($type == 2 && $module_object::getInstance($this->dependencyInjector)->isBrokerObject() == true)
                ) {
                    $module_object::getInstance($this->dependencyInjector)->generateFromPollerId(
                        $this->current_poller['id'],
                        $this->current_poller['localhost']
                    );
                }
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

    /**
     * Reset the cache and the instance
     */
    public function reset()
    {
        $this->poller_cache = array();
        $this->current_poller = null;
        $this->installed_modules = null;
        $this->module_objects = null;
    }
}
