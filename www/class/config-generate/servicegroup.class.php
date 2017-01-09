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

class Servicegroup extends AbstractObject {
    private $use_cache = 1;
    private $done_cache = 0;
    
    private $sg = array();
    private $sg_relation_cache = array();
    protected $generate_filename = 'servicegroups.cfg';
    protected $object_name = 'servicegroup';
    protected $attributes_select = '
        sg_id,
        sg_name as servicegroup_name,
        sg_alias as alias
    ';
    protected $attributes_write = array(
        'servicegroup_id',
        'servicegroup_name',
        'alias',
    );
    protected $attributes_array = array(
        'members'
    );
    protected $stmt_sg = null;
    protected $stmt_service_sg = null;
    protected $stmt_stpl_sg = null;
    
    public function __construct() {
        parent::__construct();
        $this->buildCache();
    }
    
    private function getServicegroupFromId($sg_id) {
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
        $this->sg[$sg_id]['members_cache'] = array();
        $this->sg[$sg_id]['members'] = array();
    }
    
    public function addServiceInSg($sg_id, $service_id, $service_description, $host_id, $host_name) {
        if (!isset($this->sg[$sg_id])) {
            $this->getServicegroupFromId($sg_id);
        }
        if (is_null($this->sg[$sg_id]) || isset($this->sg[$sg_id]['members_cache'][$host_id . '_' . $service_id])) {
            return 1;
        }
        
        $this->sg[$sg_id]['members_cache'][$host_id . '_' . $service_id] = array($host_name, $service_description);
        return 0;
    }
    
    private function buildCache() {
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
                $this->sg_relation_cache[$value['service_service_id']] = array($value);
            }
        }
        
        $this->done_cache = 1;
    }
    
    public function getServiceGroupsForStpl($service_id) {        
        # Get from the cache
        if (isset($this->sg_relation_cache[$service_id])) {
            return $this->sg_relation_cache[$service_id];
        }
        if ($this->done_cache == 1) {
            return array();
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
        $this->sg_relation_cache[$service_id] = array_merge($this->stmt_stpl_sg->fetchAll(PDO::FETCH_ASSOC), $this->sg_relation_cache[$service_id]);
        return $this->sg_relation_cache[$service_id];
    }
    
    public function getServiceGroupsForService($host_id, $service_id) {        
        # Get from the cache
        if (isset($this->sg_relation_cache[$service_id])) {
            return $this->sg_relation_cache[$service_id];
        }
        if ($this->done_cache == 1) {
            return array();
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
        $this->sg_relation_cache[$service_id] = array_merge($this->stmt_service_sg->fetchAll(PDO::FETCH_ASSOC), $this->sg_relation_cache[$service_id]);
        return $this->sg_relation_cache[$service_id];
    }
    
    public function generateObjects() {
        foreach ($this->sg as $id => &$value) {
            if (count($value['members_cache']) == 0) {
                continue;
            }
            
            $value['servicegroup_id'] = $value['sg_id'];
            
            foreach ($value['members_cache'] as $content) {
                array_push($this->sg[$id]['members'], $content[0], $content[1]);
            }
            $this->generateObjectInFile($this->sg[$id], $id);
        }
    }
    
    public function getServicegroups() {
        $result = array();
        foreach ($this->sg as $id => &$value) {
            if (is_null($value) || count($value['members_cache']) == 0) {
                continue;
            }
            $result[$id] = &$value;
        }
        return $result;
    }
    
    public function reset() {
        parent::reset();
        foreach ($this->sg as &$value) {
            if (!is_null($value)) {
                $value['members_cache'] = array();
                $value['members'] = array();
            }
        }
    }
    
    public function getString($sg_id, $attr) {
        if (isset($this->sg[$sg_id][$attr])) {
            return $this->sg[$sg_id][$attr];
        }
        return null;
    }
}
