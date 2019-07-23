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
