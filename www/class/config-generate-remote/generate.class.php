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
    private $installedModules = null;
    private $module_objects = null;
    protected $dependencyInjector = null;

    public function __construct(\Pimple\Container $dependencyInjector)
    {
        $this->dependencyInjector = $dependencyInjector;
        $this->backend_instance = Backend::getInstance($this->dependencyInjector);
    }

    
    private function ucFirst($delimiters, $string) {
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
        } catch (Exception $e) {
            throw new Exception('Exception received : ' . $e->getMessage() . " [file: " . $e->getFile() .
                "] [line: " . $e->getLine() . "]\n");
            $this->backend_instance->cleanPath();
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
