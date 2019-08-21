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

class Servicegroup extends AbstractObject
{
    private $use_cache = 1;
    private $done_cache = 0;

    private $sg = [];
    private $sg_relation_cache = [];
    protected $table = 'servicegroup';
    protected $generate_filename = 'servicegroups.infile';
    protected $attributes_select = '
        sg_id,
        sg_name,
        sg_alias,
        geo_coords
    ';
    protected $attributes_write = [
        'sg_id',
        'sg_name',
        'sg_alias',
        'geo_coords'
    ];
    protected $stmt_sg = null;
    protected $stmt_service_sg = null;
    protected $stmt_stpl_sg = null;

    public function __construct(\Pimple\Container $dependencyInjector)
    {
        parent::__construct($dependencyInjector);
        $this->buildCache();
    }

    private function getServicegroupFromId($sg_id)
    {
        if (is_null($this->stmt_sg)) {
            $this->stmt_sg = $this->backend_instance->db->prepare("SELECT 
                $this->attributes_select
            FROM servicegroup
            WHERE sg_id = :sg_id AND sg_activate = '1'
            ");
        }

        $this->stmt_sg->bindParam(':sg_id', $sg_id, PDO::PARAM_INT);
        $this->stmt_sg->execute();
        $results = $this->stmt_sg->fetchAll(PDO::FETCH_ASSOC);
        $this->sg[$sg_id] = array_pop($results);
        if (is_null($this->sg[$sg_id])) {
            return 1;
        }
        $this->sg[$sg_id]['members_cache'] = [];
    }

    public function addServiceInSg($sg_id, $service_id, $service_description, $host_id, $host_name)
    {
        if (!isset($this->sg[$sg_id])) {
            $this->getServicegroupFromId($sg_id);
            $this->generateObjectInFile($this->sg[$sg_id], $sg_id);
        }
        if (is_null($this->sg[$sg_id]) || isset($this->sg[$sg_id]['members_cache'][$host_id . '_' . $service_id])) {
            return 1;
        }

        $this->sg[$sg_id]['members_cache'][$host_id . '_' . $service_id] = [$host_name, $service_description];
        return 0;
    }

    private function buildCache()
    {
        if ($this->done_cache == 1) {
            return 0;
        }

        $stmt = $this->backend_instance->db->prepare("SELECT 
                  service_service_id, servicegroup_sg_id, host_host_id
                FROM servicegroup_relation
        ");
        $stmt->execute();
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $value) {
            if (isset($this->sg_relation_cache[$value['service_service_id']])) {
                $this->sg_relation_cache[$value['service_service_id']][] = $value;
            } else {
                $this->sg_relation_cache[$value['service_service_id']] = [$value];
            }
        }

        $this->done_cache = 1;
    }

    public function getServiceGroupsForStpl($service_id)
    {
        # Get from the cache
        if (isset($this->sg_relation_cache[$service_id])) {
            return $this->sg_relation_cache[$service_id];
        }
        if ($this->done_cache == 1) {
            return [];
        }

        if (is_null($this->stmt_stpl_sg)) {
            # Meaning, linked with the host or hostgroup (for the null expression)
            $this->stmt_stpl_sg = $this->backend_instance->db->prepare("SELECT 
                    servicegroup_sg_id, host_host_id, service_service_id
                FROM servicegroup_relation
                WHERE service_service_id = :service_id
            ");
        }
        $this->stmt_stpl_sg->bindParam(':service_id', $service_id, PDO::PARAM_INT);
        $this->stmt_stpl_sg->execute();
        $this->sg_relation_cache[$service_id] = array_merge(
            $this->stmt_stpl_sg->fetchAll(PDO::FETCH_ASSOC),
            $this->sg_relation_cache[$service_id]
        );
        return $this->sg_relation_cache[$service_id];
    }

    public function getServiceGroupsForService($host_id, $service_id)
    {
        # Get from the cache
        if (isset($this->sg_relation_cache[$service_id])) {
            return $this->sg_relation_cache[$service_id];
        }
        if ($this->done_cache == 1) {
            return [];
        }

        if (is_null($this->stmt_service_sg)) {
            # Meaning, linked with the host or hostgroup (for the null expression)
            $this->stmt_service_sg = $this->backend_instance->db->prepare("SELECT 
                    servicegroup_sg_id, host_host_id, service_service_id
                FROM servicegroup_relation
                WHERE service_service_id = :service_id AND (host_host_id = :host_id OR host_host_id IS NULL)
            ");
        }
        $this->stmt_service_sg->bindParam(':service_id', $service_id, PDO::PARAM_INT);
        $this->stmt_service_sg->bindParam(':host_id', $host_id, PDO::PARAM_INT);
        $this->stmt_service_sg->execute();
        $this->sg_relation_cache[$service_id] = array_merge(
            $this->stmt_service_sg->fetchAll(PDO::FETCH_ASSOC),
            $this->sg_relation_cache[$service_id]
        );
        return $this->sg_relation_cache[$service_id];
    }

    public function generateObject($sg_id)
    {
        if ($this->checkGenerate($sg_id)) {
            return null;
        }

        $this->generateObjectInFile($this->sg[$sg_id], $sg_id);
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
            #    list($host_id, $service_id) = explode('_', $key);
            #    servicegroupRelation::getInstance($this->dependencyInjector)->addRelationHostService(
            #        $id,
            #        $host_id,
            #        $service_id
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

    public function getString($sg_id, $attr)
    {
        if (isset($this->sg[$sg_id][$attr])) {
            return $this->sg[$sg_id][$attr];
        }
        return null;
    }
}
