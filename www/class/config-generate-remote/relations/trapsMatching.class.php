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

class trapsMatching extends AbstractObject
{
    private $use_cache = 1;
    private $done_cache = 0;

    private $trap_match_cache = array();

    protected $table = 'traps_matching_properties';
    protected $generate_filename = 'traps_matching_properties.infile';
    protected $stmt_trap = null;
    
    protected $attributes_write = array(
        'tmo_id',
        'trap_id',
        'tmo_order',
        'tmo_regexp',
        'tmo_string',
        'tmo_status',
        'severity_id'
    );

    public function __construct(\Pimple\Container $dependencyInjector)
    {
        parent::__construct($dependencyInjector);
        $this->buildCache();
    }

    private function cacheTrapMatch()
    {
        $stmt = $this->backend_instance->db->prepare("SELECT 
                    * 
                FROM traps_matching_properties
        ");

        $stmt->execute();
        $values = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($values as &$value) {
            if (!isset($this->trap_match_cache[$value['trap_id']])) {
                $this->trap_match_cache[$value['trap_id']] = array();
            }
            $this->trap_match_cache[$value['trap_id']][] = &$value;
        }
    }

    private function buildCache()
    {
        if ($this->done_cache == 1) {
            return 0;
        }

        $this->cacheTrapMatch();
        $this->done_cache = 1;
    }

    public function generateObject($trap_id, $trap_match_cache) {
        foreach ($trap_match_cache as $value) {
            if ($this->checkGenerate($value['tmo_id'])) {
                continue;
            }
            $this->generateObjectInFile($value, $value['tmo_id']);
            serviceCategory::getInstance($this->dependencyInjector)->generateObject($value['severity_id']);
        }
    }

    public function getTrapMatchingByTrapId($trap_id)
    {
        # Get from the cache
        if (isset($this->trap_match_cache[$trap_id])) {
            $this->generateObject($trap_id, $this->trap_match_cache[$trap_id]);
            return $this->trap_match_cache[$trap_id];
        } else if ($this->use_cache == 1) {
            return null;
        }

        # We get unitary
        if (is_null($this->stmt_trap)) {
            $this->stmt_trap = $this->backend_instance->db->prepare("SELECT 
                    *
                FROM traps_matching_properties
                WHERE trap_id = :trap_id 
                ");
        }

        $this->stmt_trap->bindParam(':trap_id', $trap_id, PDO::PARAM_INT);
        $this->stmt_trap->execute();
        $trap_match_cache = array();
        foreach ($this->stmt_trap->fetchAll(PDO::FETCH_ASSOC) as &$value) {
            $trap_match_cache[$value['traps_id']] = $value;
        }
        
        $this->generateObject($trap_id, $trap_match_cache[$trap_id]);        
        return $trap_match_cache;
    }
}
