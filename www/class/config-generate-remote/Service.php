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
use ConfigGenerateRemote\Abstracts\AbstractService;

class Service extends AbstractService
{
    private $useCache = 0;
    private $useCachePoller = 1;
    private $doneCache = 0;
    protected $serviceCache = null;
    protected $table = 'service';
    protected $generateFilename = 'services.infile';
    public $pollerId = null; // for by poller cache

    /**
     * Set useCache to 1
     *
     * @return void
     */
    public function useCache()
    {
        $this->useCache = 1;
    }

    /**
     * Get servicegroups
     *
     * @param integer $serviceId
     * @param integer $hostId
     * @param string $hostName
     * @return void
     */
    private function getServiceGroups(int $serviceId, int $hostId, string $hostName)
    {
        $servicegroup = ServiceGroup::getInstance($this->dependencyInjector);
        $this->serviceCache[$serviceId]['sg'] = $servicegroup->getServiceGroupsForService($hostId, $serviceId);
        foreach ($this->serviceCache[$serviceId]['sg'] as &$value) {
            if (is_null($value['host_host_id']) || $hostId == $value['host_host_id']) {
                $servicegroup->addServiceInSg(
                    $value['servicegroup_sg_id'],
                    $serviceId,
                    $this->serviceCache[$serviceId]['service_description'],
                    $hostId,
                    $hostName
                );
                Relations\ServiceGroupRelation::getInstance($this->dependencyInjector)->addRelationHostService(
                    $value['servicegroup_sg_id'],
                    $hostId,
                    $serviceId
                );
            }
        }
    }

    /**
     * Build cache of services by poller
     *
     * @return void
     */
    private function getServiceByPollerCache()
    {
        $query = "SELECT $this->attributesSelect FROM ns_host_relation, host_service_relation, service " .
            "LEFT JOIN extended_service_information ON extended_service_information.service_service_id = " .
            "service.service_id WHERE ns_host_relation.nagios_server_id = :server_id " .
            "AND ns_host_relation.host_host_id = host_service_relation.host_host_id " .
            "AND host_service_relation.service_service_id = service.service_id AND service_activate = '1'";
        $stmt = $this->backendInstance->db->prepare($query);
        $stmt->bindParam(':server_id', $this->pollerId, PDO::PARAM_INT);
        $stmt->execute();

        while (($value = $stmt->fetch(PDO::FETCH_ASSOC))) {
            $this->serviceCache[$value['service_id']] = $value;
        }
    }

    /**
     * Build cache of services
     */
    private function getServiceCache()
    {
        $query = "SELECT $this->attributesSelect FROM service " .
            "LEFT JOIN extended_service_information ON extended_service_information.service_service_id = " .
            "service.service_id WHERE service_register = '1' AND service_activate = '1'";
        $stmt = $this->backendInstance->db->prepare($query);
        $stmt->execute();
        $this->serviceCache = $stmt->fetchAll(PDO::FETCH_GROUP | PDO::FETCH_UNIQUE | PDO::FETCH_ASSOC);
    }

    /**
     * Add service in cache
     *
     * @param integer $serviceId
     * @param array $attr
     * @return void
     */
    public function addServiceCache(int $serviceId, array $attr = [])
    {
        $this->serviceCache[$serviceId] = $attr;
    }

    /**
     * Get service from service id
     *
     * @param integer $serviceId
     * @return void
     */
    private function getServiceFromId(int $serviceId)
    {
        if (is_null($this->stmtService)) {
            $query = "SELECT $this->attributesSelect FROM service " .
                "LEFT JOIN extended_service_information ON extended_service_information.service_service_id = " .
                "service.service_id WHERE service_id = :service_id AND service_activate = '1'";
            $this->stmtService = $this->backendInstance->db->prepare($query);
        }
        $this->stmtService->bindParam(':service_id', $serviceId, PDO::PARAM_INT);
        $this->stmtService->execute();
        $results = $this->stmtService->fetchAll(PDO::FETCH_ASSOC);
        $this->serviceCache[$serviceId] = array_pop($results);
    }

    /**
     * Get severity from service id
     *
     * @param integer $hostId
     * @param integer $serviceId
     * @return void
     */
    protected function getSeverity($hostId, int $serviceId)
    {
        $severityId =
            ServiceCategory::getInstance($this->dependencyInjector)->getServiceSeverityByServiceId($serviceId);
        if (!is_null($severityId)) {
            Relations\ServiceCategoriesRelation::getInstance($this->dependencyInjector)
                ->addRelation($severityId, $serviceId);
        }
        return null;
    }

    /**
     * Clean (nothing)
     *
     * @param array $service
     * @return void
     */
    private function clean(array &$service)
    {
    }

    /**
     * Build cache
     *
     * @return void|int
     */
    private function buildCache()
    {
        if ($this->doneCache == 1 ||
            ($this->useCache == 0 && $this->useCachePoller == 0)
        ) {
            return 0;
        }

        if ($this->useCachePoller == 1) {
            $this->getServiceByPollerCache();
        } else {
            $this->getServiceCache();
        }

        $this->doneCache = 1;
    }

    /**
     * Generate service object from service id
     *
     * @param integer $hostId
     * @param string $hostName
     * @param null|integer $serviceId
     * @param integer $by_hg
     * @return void
     */
    public function generateFromServiceId(int $hostId, string $hostName, ?int $serviceId, $by_hg = 0)
    {
        if (is_null($serviceId)) {
            return null;
        }

        $this->buildCache();

        // No need to do it again for service by hostgroup
        if ($by_hg == 1 && isset($this->serviceCache[$serviceId])) {
            return $this->serviceCache[$serviceId]['service_description'];
        }

        if (($this->useCache == 0 || $by_hg == 1) && !isset($this->serviceCache[$serviceId])) {
            $this->getServiceFromId($serviceId);
        }
        if (!isset($this->serviceCache[$serviceId]) || is_null($this->serviceCache[$serviceId])) {
            return null;
        }
        if ($this->checkGenerate($hostId . '.' . $serviceId)) {
            return $this->serviceCache[$serviceId]['service_description'];
        }

        $this->getImages($this->serviceCache[$serviceId]);
        $this->getMacros($this->serviceCache[$serviceId]);
        $this->getTraps($this->serviceCache[$serviceId]);
        // useful for servicegroup on servicetemplate
        $serviceTemplate = ServiceTemplate::getInstance($this->dependencyInjector);
        $serviceTemplate->resetLoop();
        $serviceTemplate->currentHostId = $hostId;
        $serviceTemplate->currentHostName = $hostName;
        $serviceTemplate->currentServiceId = $serviceId;
        $serviceTemplate->currentServiceDescription = $this->serviceCache[$serviceId]['service_description'];
        $this->getServiceTemplates($this->serviceCache[$serviceId]);
        $this->getServiceCommands($this->serviceCache[$serviceId]);
        $this->getServicePeriods($this->serviceCache[$serviceId]);
        if ($this->backendInstance->isExportContact()) {
            $this->getContactGroups($this->serviceCache[$serviceId]);
            $this->getContacts($this->serviceCache[$serviceId]);
        }

        $this->getSeverity($hostId, $serviceId);
        $this->getServiceGroups($serviceId, $hostId, $hostName);

        $extendedInformation = $this->getExtendedInformation($this->serviceCache[$serviceId]);
        Relations\ExtendedServiceInformation::getInstance($this->dependencyInjector)
            ->add($extendedInformation, $serviceId);
        Graph::getInstance($this->dependencyInjector)->getGraphFromId($extendedInformation['graph_id']);

        $this->serviceCache[$serviceId]['service_id'] = $serviceId;
        $this->generateObjectInFile(
            $this->serviceCache[$serviceId],
            $hostId . '.' . $serviceId
        );
        $this->clean($this->serviceCache[$serviceId]);
        return $this->serviceCache[$serviceId]['service_description'];
    }

    /**
     * Set poller
     *
     * @param integer $pollerId
     * @return void
     */
    public function setPoller(int $pollerId)
    {
        $this->pollerId = $pollerId;
    }

    /**
     * Reset object
     *
     * @param boolean $resetParent
     * @param boolean $createfile
     * @return void
     */
    public function reset($resetParent = false, $createfile = false): void
    {
        // We reset it by poller (dont need all. We save memory)
        if ($this->useCachePoller == 1) {
            $this->serviceCache = [];
            $this->doneCache = 0;
        }
        if ($resetParent == true) {
            parent::reset($createfile);
        }
    }
}
