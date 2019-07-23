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

class trap extends AbstractObject
{
    private $use_cache = 1;
    private $done_cache = 0;

    private $trap_cache = array();
    private $service_linked_cache = array();

    protected $table = 'traps';
    protected $generate_filename = 'traps.infile';
    protected $stmt_service = null;
    
    protected $attributes_write = array(
        'traps_id',
        'traps_name',
        'traps_oid',
        'traps_args',
        'traps_status',
        'severity_id',
        'manufacturer_id',
        'traps_reschedule_svc_enable',
        'traps_execution_command',
        'traps_execution_command_enable',
        'traps_submit_result_enable',
        'traps_advanced_treatment',
        'traps_advanced_treatment_default',
        'traps_timeout',
        'traps_exec_interval',
        'traps_exec_interval_type',
        'traps_log',
        'traps_routing_mode',
        'traps_routing_value',
        'traps_routing_filter_services',
        'traps_exec_method',
        'traps_downtime',
        'traps_output_transform',
        'traps_customcode',
        'traps_comments'
    );

    public function __construct(\Pimple\Container $dependencyInjector)
    {
        parent::__construct($dependencyInjector);
        $this->buildCache();
    }

    private function cacheTrap()
    {
        $stmt = $this->backend_instance->db->prepare("SELECT 
                    * 
                FROM traps LEFT JOIN traps_vendor ON traps_vendor.id = traps.manufacturer_id
        ");

        $stmt->execute();
        $values = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($values as &$value) {
            $this->trap_cache[$value['traps_id']] = &$value;
        }
    }

    private function cacheTrapLinked()
    {
        $stmt = $this->backend_instance->db->prepare(
            'SELECT traps_id, service_id ' .
            'FROM traps_service_relation'
        );

        $stmt->execute();
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $value) {
            if (!isset($this->service_linked_cache[$value['service_id']])) {
                $this->service_linked_cache[$value['service_id']] = array();
            }
            $this->service_linked_cache[$value['service_id']][] = $value['traps_id'];
        }
    }

    private function buildCache()
    {
        if ($this->done_cache == 1) {
            return 0;
        }

        $this->cacheTrap();
        $this->cacheTrapLinked();
        $this->done_cache = 1;
    }

    public function generateObject($service_id, $service_linked_cache, &$object) {
        foreach ($service_linked_cache as $trap_id) {
            trapsServiceRelation::getInstance($this->dependencyInjector)->addRelation($trap_id, $service_id);
            if ($this->checkGenerate($trap_id)) {
                continue;
            }
            $this->generateObjectInFile($object[$trap_id], $trap_id);
            trapsVendor::getInstance($this->dependencyInjector)->add(
                $object[$trap_id]['id'],
                $object[$trap_id]['name'],
                $object[$trap_id]['alias'], 
                $object[$trap_id]['description']
            );
            trapsGroup::getInstance($this->dependencyInjector)->getTrapGroupsByTrapId($trap_id);
            trapsMatching::getInstance($this->dependencyInjector)->getTrapMatchingByTrapId($trap_id);
            trapsPreexec::getInstance($this->dependencyInjector)->getTrapPreexecByTrapId($trap_id);
            serviceCategory::getInstance($this->dependencyInjector)->generateObject($object[$trap_id]['severity_id']);
        }
    }

    public function getTrapsByServiceId($service_id)
    {
        # Get from the cache
        if (isset($this->service_linked_cache[$service_id])) {
            $this->generateObject($service_id, $this->service_linked_cache[$service_id], $this->trap_cache);
            return $this->service_linked_cache[$service_id];
        } else if ($this->use_cache == 1) {
            return null;
        }

        # We get unitary
        if (is_null($this->stmt_service)) {
            $this->stmt_service = $this->backend_instance->db->prepare("SELECT 
                    traps.*, traps_service_relation.service_id
                FROM traps_service_relation, 
                     traps LEFT JOIN traps_vendor ON traps_vendor.id = traps.manufacturer_id
                WHERE traps_service_relation.service_id = :service_id 
                    AND traps_service_relation.traps_id = traps.traps_id
                ");
        }

        $this->stmt_service->bindParam(':service_id', $service_id, PDO::PARAM_INT);
        $this->stmt_service->execute();
        $service_linked_cache = array();
        $trap_cache = array();
        foreach ($this->stmt_service->fetchAll(PDO::FETCH_ASSOC) as &$value) {
            $service_linked_cache[] = $value['traps_id'];
            $trap_cache[$value['traps_id']] = $value;
        }
        
        $this->generateObject($service_id, $service_linked_cache, $trap_cache);        
        return $service_linked_cache;
    }
}
