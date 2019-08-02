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

class trapsGroup extends AbstractObject
{
    private $use_cache = 1;
    private $done_cache = 0;

    private $trapgroup_cache = array();
    private $trap_linked_cache = array();

    protected $table = 'traps_group';
    protected $generate_filename = 'traps_group.infile';
    protected $stmt_trap = null;
    
    protected $attributes_write = array(
        'traps_group_id',
        'traps_group_name'
    );

    public function __construct(\Pimple\Container $dependencyInjector)
    {
        parent::__construct($dependencyInjector);
        $this->buildCache();
    }

    private function cacheTrapGroup()
    {
        $stmt = $this->backend_instance->db->prepare("SELECT 
                    * 
                FROM traps_group
        ");

        $stmt->execute();
        $values = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($values as &$value) {
            $this->trapgroup_cache[$value['traps_group_id']] = &$value;
        }
    }

    private function cacheTrapLinked()
    {
        $stmt = $this->backend_instance->db->prepare(
            'SELECT traps_group_id, traps_id ' .
            'FROM traps_group_relation'
        );

        $stmt->execute();
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $value) {
            if (!isset($this->service_linked_cache[$value['traps_id']])) {
                $this->trap_linked_cache[$value['traps_id']] = array();
            }
            $this->trap_linked_cache[$value['traps_id']][] = $value['traps_group_id'];
        }
    }

    private function buildCache()
    {
        if ($this->done_cache == 1) {
            return 0;
        }

        $this->cacheTrapGroup();
        $this->cacheTrapLinked();
        $this->done_cache = 1;
    }

    public function generateObject($trap_id, $trap_linked_cache, &$object) {
        foreach ($trap_linked_cache as $trap_group_id) {
            trapsGroupRelation::getInstance($this->dependencyInjector)->addRelation($trap_id, $trap_group_id);
            if ($this->checkGenerate($trap_group_id)) {
                continue;
            }
            $this->generateObjectInFile($object[$trap_group_id], $trap_group_id);
        }
    }

    public function getTrapGroupsByTrapId($trap_id)
    {
        # Get from the cache
        if (isset($this->trap_linked_cache[$trap_id])) {
            $this->generateObject($trap_id, $this->trap_linked_cache[$trap_id], $this->trapgroup_cache);
            return $this->trap_linked_cache[$trap_id];
        } else if ($this->use_cache == 1) {
            return null;
        }

        # We get unitary
        if (is_null($this->stmt_trap)) {
            $this->stmt_trap = $this->backend_instance->db->prepare("SELECT 
                    traps_group.*
                FROM traps_service_relation, 
                     traps_group
                WHERE traps_group_relation.traps_id = :trap_id 
                    AND traps_group_relation.traps_group_id = traps_group.traps_group_id
                ");
        }

        $this->stmt_trap->bindParam(':trap_id', $trap_id, PDO::PARAM_INT);
        $this->stmt_trap->execute();
        $trap_linked_cache = array();
        $trapgroup_cache = array();
        foreach ($this->stmt_trap->fetchAll(PDO::FETCH_ASSOC) as &$value) {
            $trap_linked_cache[] = $value['traps_group_id'];
            $trapgroup_cache[$value['traps_id']] = $value;
        }
        
        $this->generateObject($trap_id, $trap_linked_cache, $trapgroup_cache);        
        return $trap_linked_cache;
    }
}
