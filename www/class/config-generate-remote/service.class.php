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

require_once __DIR__ . '/abstract/service.class.php';

class Service extends AbstractService
{
    private $use_cache = 0;
    private $use_cache_poller = 1;
    private $done_cache = 0;
    protected $service_cache = null;
    protected $table = 'service';
    protected $generate_filename = 'services.infile';
    public $poller_id = null; // for by poller cache

    public function use_cache()
    {
        $this->use_cache = 1;
    }

    private function getServiceGroups($service_id, $host_id, $host_name)
    {
        $servicegroup = Servicegroup::getInstance($this->dependencyInjector);
        $this->service_cache[$service_id]['sg'] = $servicegroup->getServiceGroupsForService($host_id, $service_id);
        foreach ($this->service_cache[$service_id]['sg'] as &$value) {
            if (is_null($value['host_host_id']) || $host_id == $value['host_host_id']) {
                $servicegroup->addServiceInSg(
                    $value['servicegroup_sg_id'],
                    $service_id,
                    $this->service_cache[$service_id]['service_description'],
                    $host_id,
                    $host_name
                );
                servicegroupRelation::getInstance($this->dependencyInjector)->addRelationHostService(
                    $value['servicegroup_sg_id'],
                    $host_id,
                    $service_id
                );
            }
        }
    }

    private function getServiceByPollerCache()
    {
        $query = "SELECT $this->attributes_select FROM ns_host_relation, host_service_relation, service " .
            "LEFT JOIN extended_service_information ON extended_service_information.service_service_id = " .
            "service.service_id WHERE ns_host_relation.nagios_server_id = :server_id " .
            "AND ns_host_relation.host_host_id = host_service_relation.host_host_id " .
            "AND host_service_relation.service_service_id = service.service_id AND service_activate = '1'";
        $stmt = $this->backend_instance->db->prepare($query);
        $stmt->bindParam(':server_id', $this->poller_id, PDO::PARAM_INT);
        $stmt->execute();

        while (($value = $stmt->fetch(PDO::FETCH_ASSOC))) {
            $this->service_cache[$value['service_id']] = $value;
        }
    }

    private function getServiceCache()
    {
        $query = "SELECT $this->attributes_select FROM service " .
            "LEFT JOIN extended_service_information ON extended_service_information.service_service_id = " .
            "service.service_id WHERE service_register = '1' AND service_activate = '1'";
        $stmt = $this->backend_instance->db->prepare($query);
        $stmt->execute();
        $this->service_cache = $stmt->fetchAll(PDO::FETCH_GROUP | PDO::FETCH_UNIQUE | PDO::FETCH_ASSOC);
    }

    public function addServiceCache($service_id, $attr = [])
    {
        $this->service_cache[$service_id] = $attr;
    }

    private function getServiceFromId($service_id)
    {
        if (is_null($this->stmt_service)) {
            $query = "SELECT $this->attributes_select FROM service " .
                "LEFT JOIN extended_service_information ON extended_service_information.service_service_id = " .
                "service.service_id WHERE service_id = :service_id AND service_activate = '1'";
            $this->stmt_service = $this->backend_instance->db->prepare($query);
        }
        $this->stmt_service->bindParam(':service_id', $service_id, PDO::PARAM_INT);
        $this->stmt_service->execute();
        $results = $this->stmt_service->fetchAll(PDO::FETCH_ASSOC);
        $this->service_cache[$service_id] = array_pop($results);
    }

    protected function getSeverity($host_id, $service_id)
    {
        $severity_id =
            serviceCategory::getInstance($this->dependencyInjector)->getServiceSeverityByServiceId($service_id);
        if (!is_null($severity_id)) {
            serviceCategoriesRelation::getInstance($this->dependencyInjector)->addRelation($severity_id, $service_id);
        }
        return null;
    }

    private function clean(&$service)
    {
        #if ($service['contact_from_host'] == 1) {
        #    $service['contacts'] = null;
        #    $service['contact_groups'] = null;
        #    $service['contact_from_host'] = 0;
        #}
    }

    private function buildCache()
    {
        if ($this->done_cache == 1 ||
            ($this->use_cache == 0 && $this->use_cache_poller == 0)
        ) {
            return 0;
        }

        if ($this->use_cache_poller == 1) {
            $this->getServiceByPollerCache();
        } else {
            $this->getServiceCache();
        }

        $this->done_cache = 1;
    }

    public function generateFromServiceId($host_id, $host_name, $service_id, $by_hg = 0)
    {
        if (is_null($service_id)) {
            return null;
        }

        $this->buildCache();

        # No need to do it again for service by hostgroup
        if ($by_hg == 1 && isset($this->service_cache[$service_id])) {
            return $this->service_cache[$service_id]['service_description'];
        }

        if (($this->use_cache == 0 || $by_hg == 1) && !isset($this->service_cache[$service_id])) {
            $this->getServiceFromId($service_id);
        }
        if (!isset($this->service_cache[$service_id]) || is_null($this->service_cache[$service_id])) {
            return null;
        }
        if ($this->checkGenerate($host_id . '.' . $service_id)) {
            return $this->service_cache[$service_id]['service_description'];
        }

        $this->getImages($this->service_cache[$service_id]);
        $this->getMacros($this->service_cache[$service_id]);
        $this->getTraps($this->service_cache[$service_id]);
        # useful for servicegroup on servicetemplate
        $service_template = ServiceTemplate::getInstance($this->dependencyInjector);
        $service_template->resetLoop();
        $service_template->current_host_id = $host_id;
        $service_template->current_host_name = $host_name;
        $service_template->current_service_id = $service_id;
        $service_template->current_service_description = $this->service_cache[$service_id]['service_description'];
        $this->getServiceTemplates($this->service_cache[$service_id]);
        $this->getServiceCommands($this->service_cache[$service_id]);
        $this->getServicePeriods($this->service_cache[$service_id]);
        if ($this->backend_instance->isExportContact()) {
            $this->getContactGroups($this->service_cache[$service_id]);
            $this->getContacts($this->service_cache[$service_id]);
        }

        $this->getSeverity($host_id, $service_id);
        $this->getServiceGroups($service_id, $host_id, $host_name);

        $extendedInformation = $this->getExtendedInformation($this->service_cache[$service_id]);
        extendedServiceInformation::getInstance($this->dependencyInjector)->add($extendedInformation, $service_id);
        graph::getInstance($this->dependencyInjector)->getGraphFromId($extendedInformation['graph_id']);

        $this->service_cache[$service_id]['service_id'] = $service_id;
        $this->generateObjectInFile(
            $this->service_cache[$service_id],
            $host_id . '.' . $service_id
        );
        $this->clean($this->service_cache[$service_id]);
        return $this->service_cache[$service_id]['service_description'];
    }

    public function set_poller($poller_id)
    {
        $this->poller_id = $poller_id;
    }

    public function reset($resetparent = false, $createfile = false)
    {
        # We reset it by poller (dont need all. We save memory)
        if ($this->use_cache_poller == 1) {
            $this->service_cache = [];
            $this->done_cache = 0;
        }
        if ($resetparent == true) {
            parent::reset($createfile);
        }
    }
}
