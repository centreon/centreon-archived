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

require_once realpath(dirname(__FILE__) . "/../../../config/centreon.config.php");

require_once dirname(__FILE__) . '/backend.class.php';
require_once dirname(__FILE__) . '/abstract/object.class.php';
require_once dirname(__FILE__) . '/abstract/objectXML.class.php';
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
require_once dirname(__FILE__) . '/connector.class.php';
require_once dirname(__FILE__) . '/macro.class.php';
require_once dirname(__FILE__) . '/host.class.php';
require_once dirname(__FILE__) . '/severity.class.php';
require_once dirname(__FILE__) . '/escalation.class.php';
require_once dirname(__FILE__) . '/dependency.class.php';
require_once dirname(__FILE__) . '/meta_timeperiod.class.php';
require_once dirname(__FILE__) . '/meta_command.class.php';
require_once dirname(__FILE__) . '/meta_host.class.php';
require_once dirname(__FILE__) . '/meta_service.class.php';
require_once dirname(__FILE__) . '/resource.class.php';
require_once dirname(__FILE__) . '/engine.class.php';
require_once dirname(__FILE__) . '/broker.class.php';
require_once dirname(__FILE__) . '/correlation.class.php';
require_once dirname(__FILE__) . '/timezone.class.php';

class Generate {
    private $poller_cache = array();
    private $backend_instance = null;
    private $current_poller = null;
    private $installed_modules = null;
    private $module_objects = null;
    
    public function __construct() {
        $this->backend_instance = Backend::getInstance();
    }
    
    private function generateIndexData($localhost = 0) {
        $service_instance = Service::getInstance();
        $host_instance = Host::getInstance();
        $services = &$service_instance->getGeneratedServices();
        
        try {
            $stmt = $this->backend_instance->db_cs->prepare("INSERT INTO index_data (host_id, service_id, host_name, service_description) VALUES (:host_id, :service_id, :host_name, :service_description) ON DUPLICATE KEY UPDATE host_name=VALUES(host_name), service_description=VALUES(service_description)");
            $this->backend_instance->db_cs->beginTransaction();
            foreach ($services as $host_id => &$values) {
                foreach ($values as $service_id) {
                    $stmt->bindParam(':host_name', $host_instance->getString($host_id, 'host_name'), PDO::PARAM_STR);
                    $stmt->bindParam(':service_description', $service_instance->getString($service_id, 'service_description'), PDO::PARAM_STR);
                    $stmt->bindParam(':host_id', $host_id, PDO::PARAM_INT);
                    $stmt->bindParam(':service_id', $service_id, PDO::PARAM_INT);
                    $stmt->execute();
                }
            }
            
            # Meta services
            if ($localhost == 1) {
                $meta_services = &MetaService::getInstance()->getMetaServices();
                $host_id = MetaHost::getInstance()->getHostIdByHostName('_Module_Meta');
                foreach ($meta_services as $meta_id => $meta_service) {
                    $stmt->bindValue(':host_name', '_Module_Meta', PDO::PARAM_STR);
                    $stmt->bindValue(':service_description', 'meta_' . $meta_id, PDO::PARAM_STR);
                    $stmt->bindParam(':host_id', $host_id, PDO::PARAM_INT);
                    $stmt->bindParam(':service_id', $meta_service['service_id'], PDO::PARAM_INT);
                    $stmt->execute();
                }
            }
            
            $this->backend_instance->db_cs->commit();
        } catch (Exception $e) {
            $this->backend_instance->db_cs->rollback();
            throw new Exception('Exception received : ' .  $e->getMessage() . "\n");
            throw new Exception($e->getFile() . "\n");
        }
    }

    private function getPollerFromId($poller_id) {
        $stmt = $this->backend_instance->db->prepare("SELECT id, localhost, monitoring_engine, centreonconnector_path FROM nagios_server WHERE id = :poller_id");
        $stmt->bindParam(':poller_id', $poller_id, PDO::PARAM_INT);
        $stmt->execute();
        $this->current_poller = array_pop($stmt->fetchAll(PDO::FETCH_ASSOC));
        if (is_null($this->current_poller)) {
            throw new Exception("Cannot find poller id '" . $poller_id ."'");
        }
    }
    
    private function getPollerFromName($poller_name) {
        $stmt = $this->backend_instance->db->prepare("SELECT id, localhost, monitoring_engine, centreonconnector_path FROM nagios_server WHERE name = :poller_name");
        $stmt->bindParam(':poller_name', $poller_name, PDO::PARAM_STR);
        $stmt->execute();
        $this->current_poller = array_pop($stmt->fetchAll(PDO::FETCH_ASSOC));
        if (is_null($this->current_poller)) {
            throw new Exception("Cannot find poller name '" . $poller_name ."'");
        }
    }
    
    public function resetObjectsEngine() {
        Host::getInstance()->reset();
        HostTemplate::getInstance()->reset();
        Service::getInstance()->reset();
        ServiceTemplate::getInstance()->reset();
        Command::getInstance()->reset();
        Contact::getInstance()->reset();
        Contactgroup::getInstance()->reset();
        Hostgroup::getInstance()->reset();
        Servicegroup::getInstance()->reset();
        Timeperiod::getInstance()->reset();
        Escalation::getInstance()->reset();
        Dependency::getInstance()->reset();
        MetaCommand::getInstance()->reset();
        MetaTimeperiod::getInstance()->reset();
        MetaService::getInstance()->reset();
        MetaHost::getInstance()->reset();
        Connector::getInstance()->reset();
        Resource::getInstance()->reset();
        Engine::getInstance()->reset();
        Broker::getInstance()->reset();
        Correlation::getInstance()->reset();
        $this->resetModuleObjects();
    }

    private function configPoller($username = 'unknown') {
        $this->backend_instance->setUserName($username);
        $this->backend_instance->initPath($this->current_poller['id']);
        $this->backend_instance->setPollerId($this->current_poller['id']);
        $this->resetObjectsEngine();

        Host::getInstance()->generateFromPollerId($this->current_poller['id'], $this->current_poller['localhost']);
        $this->generateModuleObjects(1);
        Engine::getInstance()->generateFromPoller($this->current_poller);
        $this->backend_instance->movePath($this->current_poller['id']);

        $this->backend_instance->initPath($this->current_poller['id'], 2);
        # Correlation files are always generated on central poller
        if (Correlation::getInstance()->hasCorrelation()) {
            Correlation::getInstance()->generateFromPollerId($this->current_poller['id'], $this->current_poller['localhost']);
        }
        $this->generateModuleObjects(2);
        Broker::getInstance()->generateFromPoller($this->current_poller);
        $this->backend_instance->movePath($this->current_poller['id']);
        
        $this->generateIndexData($this->current_poller['localhost']);
    }
    
    public function configPollerFromName($poller_name) {
        try {
            $this->getPollerFromName($poller_name);
            $this->configPoller();
        } catch (Exception $e) {
            throw new Exception('Exception received : ' .  $e->getMessage() . " [file: " . $e->getFile()  . "] [line: " . $e->getLine() . "]\n");
            $this->backend_instance->cleanPath();
        }
    }

    public function configPollerFromId($poller_id, $username='unknown') {
        try {
            if (is_null($this->current_poller)) {
                $this->getPollerFromId($poller_id);
            }
            $this->configPoller($username);
        } catch (Exception $e) {
            throw new Exception('Exception received : ' .  $e->getMessage() . " [file: " . $e->getFile()  . "] [line: " . $e->getLine() . "]\n");
            $this->backend_instance->cleanPath();
        }
    }

    public function configPollers($username='unknown') {
        $stmt = $this->backend_instance->db->prepare("SELECT id, localhost, monitoring_engine, centreonconnector_path FROM nagios_server WHERE ns_activate = '1'");
        $stmt->execute();
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $value) {
            $this->current_poller = $value;
            $this->configPollerFromId($this->current_poller['id'], $username);
        }
    }

    public function getInstalledModules() {
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

    public function getModuleObjects() {
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

    public function generateModuleObjects($type = 1) {
        if (is_null($this->module_objects)) {
            $this->getModuleObjects();
        }
        if (is_array($this->module_objects)) {
            foreach ($this->module_objects as $module_object) {
                if (($type == 1 && $module_object::getInstance()->isEngineObject() == true) ||
                    ($type == 2 && $module_object::getInstance()->isBrokerObject() == true)) {
                    $module_object::getInstance()->generateFromPollerId($this->current_poller['id'],
                        $this->current_poller['localhost']);
                }
            }
        }
    }

    public function resetModuleObjects() {
        if (is_null($this->module_objects)) {
            $this->getModuleObjects();
        }
        if (is_array($this->module_objects)) {
            foreach ($this->module_objects as $module_object) {
                $module_object::getInstance()->reset();
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
