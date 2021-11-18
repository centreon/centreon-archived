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

// file centreon.config.php may not exist in test environment
$configFile = realpath(dirname(__FILE__) . "/../../../config/centreon.config.php");
if ($configFile !== false) {
    require_once $configFile;
}

require_once dirname(__FILE__) . '/backend.class.php';
require_once dirname(__FILE__) . '/abstract/object.class.php';
require_once dirname(__FILE__) . '/abstract/objectJSON.class.php';
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
require_once dirname(__FILE__) . '/timezone.class.php';

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

    /**
     * Insert services in index_data
     *
     * @param integer $localhost
     * @return void
     */
    private function generateIndexData($localhost = 0)
    {
        $serviceInstance = Service::getInstance($this->dependencyInjector);
        $hostInstance = Host::getInstance($this->dependencyInjector);
        $services = $serviceInstance->getGeneratedServices();

        $bulkLimit = 2000;

        $valuesQueries = [];
        $bindParams = [];
        $bulkCount = 0;

        $bulkInsert = function () use (&$valuesQueries, &$bindParams, &$bulkCount) {
            $stmt = $this->backend_instance->db_cs->prepare(
                'INSERT INTO index_data (host_id, service_id, host_name, service_description) VALUES '
                . implode(',', $valuesQueries)
                . ' ON DUPLICATE KEY UPDATE '
                . ' host_name=VALUES(host_name), service_description=VALUES(service_description) '
            );

            foreach ($bindParams as $bindKey => list($bindValue, $bindType)) {
                $stmt->bindValue($bindKey, $bindValue, $bindType);
            }

            $stmt->execute();

            $valuesQueries = [];
            $bindParams = [];
            $bulkCount = 0;
        };

        foreach ($services as $hostId => &$values) {
            $hostName = $hostInstance->getString($hostId, 'host_name');
            foreach ($values as $serviceId) {
                $serviceDescription = $serviceInstance->getString($serviceId, 'service_description');
                $bindParams[":host_id_{$hostId}"] = [$hostId, \PDO::PARAM_INT];
                $bindParams[":service_id_{$serviceId}"] = [$serviceId, \PDO::PARAM_INT];
                $bindParams[":host_name_{$hostId}"] = [$hostName, \PDO::PARAM_STR];
                $bindParams[":service_description_{$serviceId}"] = [$serviceDescription, \PDO::PARAM_STR];
                $valuesQueries[] = "(
                    :host_id_{$hostId},
                    :service_id_{$serviceId},
                    :host_name_{$hostId},
                    :service_description_{$serviceId}
                )";
                $bulkCount++;
                if ($bulkCount === $bulkLimit) {
                    $bulkInsert();
                }
            }
        }

        # Meta services
        if ($localhost == 1) {
            $metaServices = MetaService::getInstance($this->dependencyInjector)->getMetaServices();
            $hostId = MetaHost::getInstance($this->dependencyInjector)->getHostIdByHostName('_Module_Meta');
            foreach ($metaServices as $metaId => $metaService) {
                $bindParams[":host_id_{$hostId}"] = [$hostId, \PDO::PARAM_INT];
                $bindParams[":meta_service_id_{$metaId}"] = [$metaService['service_id'], \PDO::PARAM_INT];
                $bindParams[":host_name_{$hostId}"] = ['_Module_Meta', \PDO::PARAM_STR];
                $bindParams[":meta_service_description_{$metaId}"] = ['meta_' . $metaId, \PDO::PARAM_STR];
                $valuesQueries[] = "(
                    :host_id_{$hostId},
                    :meta_service_id_{$metaId},
                    :host_name_{$hostId},
                    :meta_service_description_{$metaId}
                )";
                $bulkCount++;
                if ($bulkCount === $bulkLimit) {
                    $bulkInsert();
                }
            }
        }

        if ($bulkCount > 0) {
            $bulkInsert();
        }
    }

    /**
     * Insert services in index_data
     *
     * @param integer $localhost
     * @return void
     */
    private function generateModulesIndexData($localhost = 0)
    {
        if (is_null($this->module_objects)) {
            $this->getModuleObjects();
        }
        if (is_array($this->module_objects)) {
            foreach ($this->module_objects as $module_object) {
                $moduleInstance = $module_object::getInstance($this->dependencyInjector);
                if (
                    $moduleInstance->isEngineObject() == true
                    && method_exists($moduleInstance, 'generateModuleIndexData')
                ) {
                    $moduleInstance->generateModuleIndexData($localhost);
                }
            }
        }

    }

    private function getPollerFromId($poller_id)
    {
        $query = "SELECT id, localhost,  centreonconnector_path FROM nagios_server " .
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

    private function getPollerFromName($poller_name)
    {
        $query = "SELECT id, localhost, centreonconnector_path FROM nagios_server " .
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
        HostTemplate::getInstance($this->dependencyInjector)->reset();
        Service::getInstance($this->dependencyInjector)->reset();
        ServiceTemplate::getInstance($this->dependencyInjector)->reset();
        Command::getInstance($this->dependencyInjector)->reset();
        Contact::getInstance($this->dependencyInjector)->reset();
        Contactgroup::getInstance($this->dependencyInjector)->reset();
        Hostgroup::getInstance($this->dependencyInjector)->reset();
        Servicegroup::getInstance($this->dependencyInjector)->reset();
        Timeperiod::getInstance($this->dependencyInjector)->reset();
        Escalation::getInstance($this->dependencyInjector)->reset();
        Dependency::getInstance($this->dependencyInjector)->reset();
        MetaCommand::getInstance($this->dependencyInjector)->reset();
        MetaTimeperiod::getInstance($this->dependencyInjector)->reset();
        MetaService::getInstance($this->dependencyInjector)->reset();
        MetaHost::getInstance($this->dependencyInjector)->reset();
        Connector::getInstance($this->dependencyInjector)->reset();
        Resource::getInstance($this->dependencyInjector)->reset();
        Engine::getInstance($this->dependencyInjector)->reset();
        Broker::getInstance($this->dependencyInjector)->reset();
        $this->resetModuleObjects();
    }

    private function configPoller($username = 'unknown')
    {
        $this->backend_instance->setUserName($username);
        $this->backend_instance->initPath($this->current_poller['id']);
        $this->backend_instance->setPollerId($this->current_poller['id']);
        $this->resetObjectsEngine();

        Host::getInstance($this->dependencyInjector)->generateFromPollerId(
            $this->current_poller['id'],
            $this->current_poller['localhost']
        );
        $this->generateModuleObjects(1);
        Engine::getInstance($this->dependencyInjector)->generateFromPoller($this->current_poller);
        $this->backend_instance->movePath($this->current_poller['id']);

        $this->backend_instance->initPath($this->current_poller['id'], 2);
        $this->generateModuleObjects(2);
        Broker::getInstance($this->dependencyInjector)->generateFromPoller($this->current_poller);
        $this->backend_instance->movePath($this->current_poller['id']);

        $this->generateIndexData($this->current_poller['localhost']);
        $this->generateModulesIndexData($this->current_poller['localhost']);
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
        $query = "SELECT id, localhost, centreonconnector_path FROM " .
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
