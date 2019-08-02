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

class serviceCategory extends AbstractObject
{
    private $use_cache = 1;
    private $done_cache = 0;

    private $service_severity_cache = array();
    private $service_severity_by_name_cache = array();
    private $service_linked_cache = array();

    protected $table = 'service_categories';
    protected $generate_filename = 'servicecategories.infile';
    protected $stmt_service = null;
    protected $stmt_hc_name = null;
    
    protected $attributes_write = array(
        'sc_id',
        'sc_name',
        'sc_description',
        'level',
        'icon_id',
    );

    public function __construct(\Pimple\Container $dependencyInjector)
    {
        parent::__construct($dependencyInjector);
        $this->buildCache();
    }

    private function cacheServiceSeverity()
    {
        $stmt = $this->backend_instance->db->prepare("SELECT 
                    sc_name, sc_id, level, icon_id
                FROM service_categories
                WHERE level IS NOT NULL AND sc_activate = '1'
        ");

        $stmt->execute();
        $values = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($values as &$value) {
            $this->service_severity_by_name_cache[$value['sc_name']] = &$value;
            $this->service_severity_cache[$value['sc_id']] = &$value;
        }
    }

    private function cacheServiceSeverityLinked()
    {
        $stmt = $this->backend_instance->db->prepare(
            'SELECT service_categories.sc_id, service_service_id ' .
            'FROM service_categories, service_categories_relation ' .
            'WHERE level IS NOT NULL ' .
            'AND sc_activate = "1" ' .
            'AND service_categories_relation.sc_id = service_categories.sc_id'
        );

        $stmt->execute();
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $value) {
            if (isset($this->service_linked_cache[$value['service_service_id']])) {
                if ($this->service_severity_cache[$value['sc_id']]['level'] <
                    $this->service_severity_cache[$this->service_linked_cache[$value['service_service_id']]]
                ) {
                    $this->service_linked_cache[$value['service_service_id']] = $value['sc_id'];
                }
            } else {
                $this->service_linked_cache[$value['service_service_id']] = $value['sc_id'];
            }
        }
    }

    private function buildCache()
    {
        if ($this->done_cache == 1) {
            return 0;
        }

        $this->cacheServiceSeverity();
        $this->cacheServiceSeverityLinked();
        $this->done_cache = 1;
    }
    
    public function generateObject($sc_id) {
        if (is_null($sc_id) || $this->checkGenerate($sc_id)) {
            return null;
        }
        
        if (!isset($this->service_severity_cache[$sc_id])) {
            return null;
        }
        $this->generateObjectInFile($this->service_severity_cache[$sc_id], $sc_id);
        Media::getInstance($this->dependencyInjector)->getMediaPathFromId($this->service_severity_cache[$sc_id]['icon_id']);
    }

    public function getServiceSeverityByServiceId($service_id)
    {
        # Get from the cache
        if (isset($this->service_linked_cache[$service_id])) {
            if (!$this->checkGenerate($this->service_linked_cache[$service_id])) {
                $this->generateObjectInFile($this->service_severity_cache[ $this->service_linked_cache[$service_id] ], $this->service_linked_cache[$service_id]);
                Media::getInstance($this->dependencyInjector)->getMediaPathFromId($this->service_severity_cache[ $this->service_linked_cache[$service_id] ]['icon_id']);
            }
            return $this->service_linked_cache[$service_id];
        }
        if ($this->done_cache == 1) {
            return null;
        }

        # We get unitary
        if (is_null($this->stmt_service)) {
            $this->stmt_service = $this->backend_instance->db->prepare("SELECT 
                    service_categories.sc_id, sc_name, level, icon_id
                FROM service_categories_relation, service_categories
                WHERE service_categories_relation.service_service_id = :service_id 
                    AND service_categories_relation.sc_id = service_categories.sc_id
                    AND level IS NOT NULL AND sc_activate = '1'
                ORDER BY level DESC
                LIMIT 1
                ");
        }

        $this->stmt_service->bindParam(':service_id', $service_id, PDO::PARAM_INT);
        $this->stmt_service->execute();
        $severity = array_pop($this->stmt_service->fetchAll(PDO::FETCH_ASSOC));
        if (is_null($severity)) {
            $this->service_linked_cache[$service_id] = null;
            return null;
        }

        $this->service_linked_cache[$service_id] = $severity['sc_id'];
        $this->service_severity_by_name_cache[$severity['sc_name']] = &$severity;
        $this->service_severity_cache[$severity['sc_id']] = &$severity;
        
        $this->generateObjectInFile($severity, $severity['sc_id']);
        Media::getInstance($this->dependencyInjector)->getMediaPathFromId($this->service_severity_cache[ $this->service_linked_cache[$service_id] ]['icon_id']);
        return $severity['sc_id'];
    }

    public function getServiceSeverityById($sc_id)
    {
        if (is_null($sc_id)) {
            return null;
        }
        if (!isset($this->service_severity_cache[$sc_id])) {
            return null;
        }

        return $this->service_severity_cache[$sc_id];
    }

    public function getServiceSeverityMappingHostSeverityByName($hc_name)
    {
        if (isset($this->service_severity_by_name_cache[$hc_name])) {
            return $this->service_severity_by_name_cache[$hc_name];
        }
        if ($this->done_cache == 1) {
            return null;
        }

        # We get unitary
        if (is_null($this->stmt_hc_name)) {
            $this->stmt_hc_name = $this->backend_instance->db->prepare("SELECT 
                    sc_name, sc_id, level
                FROM service_categories
                WHERE sc_name = :sc_name AND level IS NOT NULL AND sc_activate = '1'
                ");
        }

        $this->stmt_hc_name->bindParam(':sc_name', $hc_name, PDO::PARAM_STR);
        $this->stmt_hc_name->execute();
        $severity = array_pop($this->stmt_hc_name->fetchAll(PDO::FETCH_ASSOC));
        if (is_null($severity)) {
            $this->service_severity_by_name_cache[$hc_name] = null;
            return null;
        }

        $this->service_severity_by_name_cache[$hc_name] = &$severity;
        $this->service_severity_cache[$hc_name] = &$severity;
        return $severity['sc_id'];
    }
}
