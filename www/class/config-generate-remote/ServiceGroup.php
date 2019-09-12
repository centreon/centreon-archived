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
use ConfigGenerateRemote\Abstracts\AbstractObject;

class ServiceGroup extends AbstractObject
{
    private $useCache = 1;
    private $doneCache = 0;

    private $sg = [];
    private $sgRelationCache = [];
    protected $table = 'servicegroup';
    protected $generateFilename = 'servicegroups.infile';
    protected $attributesSelect = '
        sg_id,
        sg_name,
        sg_alias,
        geo_coords
    ';
    protected $attributesWrite = [
        'sg_id',
        'sg_name',
        'sg_alias',
        'geo_coords'
    ];
    protected $stmtSg = null;
    protected $stmtServiceSg = null;
    protected $stmtStplSg = null;

    public function __construct(\Pimple\Container $dependencyInjector)
    {
        parent::__construct($dependencyInjector);
        $this->buildCache();
    }

    private function getServicegroupFromId($sgId)
    {
        if (is_null($this->stmtSg)) {
            $this->stmtSg = $this->backendInstance->db->prepare("SELECT 
                $this->attributesSelect
            FROM servicegroup
            WHERE sg_id = :sg_id AND sg_activate = '1'
            ");
        }

        $this->stmtSg->bindParam(':sg_id', $sgId, PDO::PARAM_INT);
        $this->stmtSg->execute();
        $results = $this->stmtSg->fetchAll(PDO::FETCH_ASSOC);
        $this->sg[$sgId] = array_pop($results);
        if (is_null($this->sg[$sgId])) {
            return 1;
        }
        $this->sg[$sgId]['members_cache'] = [];
    }

    public function addServiceInSg($sgId, $serviceId, $serviceDescription, $hostId, $hostName)
    {
        if (!isset($this->sg[$sgId])) {
            $this->getServicegroupFromId($sgId);
            $this->generateObjectInFile($this->sg[$sgId], $sgId);
        }
        if (is_null($this->sg[$sgId]) || isset($this->sg[$sgId]['members_cache'][$hostId . '_' . $serviceId])) {
            return 1;
        }

        $this->sg[$sgId]['members_cache'][$hostId . '_' . $serviceId] = [$hostName, $serviceDescription];
        return 0;
    }

    private function buildCache()
    {
        if ($this->doneCache == 1) {
            return 0;
        }

        $stmt = $this->backendInstance->db->prepare("SELECT 
                  service_service_id, servicegroup_sg_id, host_host_id
                FROM servicegroup_relation
        ");
        $stmt->execute();
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $value) {
            if (isset($this->sgRelationCache[$value['service_service_id']])) {
                $this->sgRelationCache[$value['service_service_id']][] = $value;
            } else {
                $this->sgRelationCache[$value['service_service_id']] = [$value];
            }
        }

        $this->doneCache = 1;
    }

    public function getServiceGroupsForStpl($serviceId)
    {
        # Get from the cache
        if (isset($this->sgRelationCache[$serviceId])) {
            return $this->sgRelationCache[$serviceId];
        }
        if ($this->doneCache == 1) {
            return [];
        }

        if (is_null($this->stmt_stpl_sg)) {
            # Meaning, linked with the host or hostgroup (for the null expression)
            $this->stmt_stpl_sg = $this->backendInstance->db->prepare("SELECT 
                    servicegroup_sg_id, host_host_id, service_service_id
                FROM servicegroup_relation
                WHERE service_service_id = :service_id
            ");
        }
        $this->stmt_stpl_sg->bindParam(':service_id', $serviceId, PDO::PARAM_INT);
        $this->stmt_stpl_sg->execute();
        $this->sgRelationCache[$serviceId] = array_merge(
            $this->stmt_stpl_sg->fetchAll(PDO::FETCH_ASSOC),
            $this->sgRelationCache[$serviceId]
        );
        return $this->sgRelationCache[$serviceId];
    }

    public function getServiceGroupsForService($hostId, $serviceId)
    {
        # Get from the cache
        if (isset($this->sgRelationCache[$serviceId])) {
            return $this->sgRelationCache[$serviceId];
        }
        if ($this->doneCache == 1) {
            return [];
        }

        if (is_null($this->stmtServiceSg)) {
            # Meaning, linked with the host or hostgroup (for the null expression)
            $this->stmtServiceSg = $this->backendInstance->db->prepare("SELECT 
                    servicegroup_sg_id, host_host_id, service_service_id
                FROM servicegroup_relation
                WHERE service_service_id = :service_id AND (host_host_id = :host_id OR host_host_id IS NULL)
            ");
        }
        $this->stmtServiceSg->bindParam(':service_id', $serviceId, PDO::PARAM_INT);
        $this->stmtServiceSg->bindParam(':host_id', $hostId, PDO::PARAM_INT);
        $this->stmtServiceSg->execute();
        $this->sgRelationCache[$serviceId] = array_merge(
            $this->stmtServiceSg->fetchAll(PDO::FETCH_ASSOC),
            $this->sgRelationCache[$serviceId]
        );
        return $this->sgRelationCache[$serviceId];
    }

    public function generateObject($sgId)
    {
        if ($this->checkGenerate($sgId)) {
            return null;
        }

        $this->generateObjectInFile($this->sg[$sgId], $sgId);
    }

    public function generateObjects()
    {
        foreach ($this->sg as $id => &$value) {
            if (count($value['members_cache']) == 0) {
                continue;
            }

            $this->sg[$id]['sg_id'] = $id;
            $this->generateObjectInFile($this->sg[$id], $id);
            #foreach ($value['members_cache'] as $key => $value) {
            #    list($hostId, $serviceId) = explode('_', $key);
            #    servicegroupRelation::getInstance($this->dependencyInjector)->addRelationHostService(
            #        $id,
            #        $hostId,
            #        $serviceId
            #    );
            #}
        }
    }

    public function getServicegroups()
    {
        $result = [];
        foreach ($this->sg as $id => &$value) {
            if (is_null($value) || count($value['members_cache']) == 0) {
                continue;
            }
            $result[$id] = &$value;
        }
        return $result;
    }

    public function reset($createfile = false)
    {
        parent::reset($createfile);
        foreach ($this->sg as &$value) {
            if (!is_null($value)) {
                $value['members_cache'] = [];
            }
        }
    }

    public function getString($sgId, $attr)
    {
        if (isset($this->sg[$sgId][$attr])) {
            return $this->sg[$sgId][$attr];
        }
        return null;
    }
}
