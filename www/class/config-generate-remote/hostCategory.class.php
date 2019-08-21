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

class hostCategory extends AbstractObject
{
    private $use_cache = 1;
    private $done_cache = 0;

    private $host_severity_cache = [];
    private $host_linked_cache = [];

    protected $table = 'hostcategories';
    protected $generate_filename = 'hostcategories.infile';
    protected $stmt_host = null;
    protected $stmt_hc_name = null;

    protected $attributes_write = [
        'hc_id',
        'hc_name',
        'hc_alias',
        'level',
        'icon_id',
    ];

    public function __construct(\Pimple\Container $dependencyInjector)
    {
        parent::__construct($dependencyInjector);
        $this->buildCache();
    }

    private function cacheHostSeverity()
    {
        $stmt = $this->backend_instance->db->prepare("SELECT 
                    hc_name, hc_alias, hc_id, level, icon_id
                FROM hostcategories
                WHERE level IS NOT NULL AND hc_activate = '1'
        ");

        $stmt->execute();
        $values = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($values as &$value) {
            $this->host_severity_cache[$value['hc_id']] = &$value;
        }
    }

    private function cacheHostSeverityLinked()
    {
        $stmt = $this->backend_instance->db->prepare(
            'SELECT hc_id, host_host_id ' .
            'FROM hostcategories, hostcategories_relation ' .
            'WHERE level IS NOT NULL ' .
            'AND hc_activate = "1" ' .
            'AND hostcategories_relation.hostcategories_hc_id = hostcategories.hc_id'
        );

        $stmt->execute();
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $value) {
            if (isset($this->host_linked_cache[$value['host_host_id']])) {
                if ($this->host_severity_cache[$value['hc_id']]['level'] <
                    $this->host_severity_cache[$this->host_linked_cache[$value['host_host_id']]]
                ) {
                    $this->host_linked_cache[$value['host_host_id']] = $value['hc_id'];
                }
            } else {
                $this->host_linked_cache[$value['host_host_id']] = $value['hc_id'];
            }
        }
    }

    public function getHostSeverityByHostId($host_id)
    {
        # Get from the cache
        if (isset($this->host_linked_cache[$host_id])) {
            if (!$this->checkGenerate($this->host_linked_cache[$host_id])) {
                $this->generateObjectInFile($this->host_severity_cache[$this->host_linked_cache[$host_id]],
                    $this->host_linked_cache[$host_id]);
                Media::getInstance($this->dependencyInjector)
                    ->getMediaPathFromId($this->host_severity_cache[$this->host_linked_cache[$host_id]]['icon_id']);
            }
            return $this->host_linked_cache[$host_id];
        }
        if ($this->done_cache == 1) {
            return null;
        }

        # We get unitary
        if (is_null($this->stmt_host)) {
            $this->stmt_host = $this->backend_instance->db->prepare("SELECT 
                    hc_id, hc_name, hc_alias, level, icon_id
                FROM hostcategories_relation, hostcategories
                WHERE hostcategories_relation.host_host_id = :host_id 
                    AND hostcategories_relation.hostcategories_hc_id = hostcategories.hc_id
                    AND level IS NOT NULL AND hc_activate = '1'
                ORDER BY level DESC
                LIMIT 1
                ");
        }

        $this->stmt_host->bindParam(':host_id', $host_id, PDO::PARAM_INT);
        $this->stmt_host->execute();
        $severity = array_pop($this->stmt_host->fetchAll(PDO::FETCH_ASSOC));
        if (is_null($severity)) {
            $this->host_linked_cache[$host_id] = null;
            return null;
        }
        $this->host_linked_cache[$service_id] = $severity['hc_id'];
        $this->host_severity_cache[$severity['hc_id']] = &$severity;

        $this->generateObjectInFile($severity, $severity['hc_id']);
        Media::getInstance($this->dependencyInjector)
            ->getMediaPathFromId($this->host_severity_cache[$this->host_linked_cache[$host_id]]['icon_id']);
        return $severity['hc_id'];
    }

    public function getHostSeverityById($hc_id)
    {
        if (is_null($hc_id)) {
            return null;
        }
        if (!isset($this->host_severity_cache[$hc_id])) {
            return null;
        }

        return $this->host_severity_cache[$hc_id];
    }

    private function buildCache()
    {
        if ($this->done_cache == 1) {
            return 0;
        }

        $this->cacheHostSeverity();
        $this->cacheHostSeverityLinked();
        $this->done_cache = 1;
    }
}
