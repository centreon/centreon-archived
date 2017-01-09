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

class Dependency extends AbstractObject {
    # Not done system without cache. TODO
    private $use_cache = 1;
    private $done_cache = 0;
    private $has_dependency = 1; # by default, we have.
    private $generated_dependencies = array();
    private $dependency_linked_host_parent_cache = array();
    private $dependency_linked_host_child_cache = array();
    private $dependency_linked_hg_parent_cache = array();
    private $dependency_linked_hg_child_cache = array();
    private $dependency_linked_service_parent_cache = array();
    private $dependency_linked_service_child_cache = array();
    private $dependency_linked_sg_parent_cache = array();
    private $dependency_linked_sg_child_cache = array();
    private $dependency_linked_meta_parent_cache = array();
    private $dependency_linked_meta_child_cache = array();
    protected $generate_filename = 'dependencies.cfg';
    protected $object_name = 'hostdependency';
    protected $attributes_select = "
        dep_id,
        dep_name as ';dependency_name',
        execution_failure_criteria,
        notification_failure_criteria,
        inherits_parent
    ";
    protected $attributes_write = array(
        ';dependency_name',
        'execution_failure_criteria',
        'notification_failure_criteria',
        'inherits_parent',
    );
    protected $attributes_array = array(
        'dependent_host_name',
        'host_name',
        'dependent_service_description',
        'service_description',
        'dependent_hostgroup_name',
        'hostgroup_name',
        'dependent_servicegroup_name',
        'servicegroup_name',
    );
    protected $host_instance = null;
    protected $service_instance = null;
    protected $hg_instance = null;
    protected $sg_instance = null;
    
    public function __construct() {
        parent::__construct();
        $this->host_instance = Host::getInstance();
        $this->service_instance = Service::getInstance();
        $this->hg_instance = Hostgroup::getInstance();
        $this->sg_instance = Servicegroup::getInstance();
        $this->buildCache();
    }
    
    private function getDependencyCache() {
        $stmt = $this->backend_instance->db->prepare("SELECT 
                    $this->attributes_select
                FROM dependency
        ");
        $stmt->execute();
        $this->dependency_cache = $stmt->fetchAll(PDO::FETCH_GROUP|PDO::FETCH_UNIQUE|PDO::FETCH_ASSOC);
        
        if (count($this->dependency_cache) == 0) {
            $this->has_dependency = 0;
        }
    }
    
    private function getDependencyLinkedCache() {
        if ($this->has_dependency == 0) {
            return 0;
        }
        
        # Host dependency
        $stmt = $this->backend_instance->db->prepare("SELECT 
                    dependency_dep_id, host_host_id
                FROM dependency_hostParent_relation
        ");
        $stmt->execute();
        $this->dependency_linked_host_parent_cache = $stmt->fetchAll(PDO::FETCH_GROUP|PDO::FETCH_COLUMN);
        $stmt = $this->backend_instance->db->prepare("SELECT 
                    dependency_dep_id, host_host_id
                FROM dependency_hostChild_relation
        ");
        $stmt->execute();
        $this->dependency_linked_host_child_cache = $stmt->fetchAll(PDO::FETCH_GROUP|PDO::FETCH_COLUMN);

        # Hostgroup dependency
        $stmt = $this->backend_instance->db->prepare("SELECT 
                    dependency_dep_id, hostgroup_hg_id
                FROM dependency_hostgroupParent_relation
        ");
        $stmt->execute();
        $this->dependency_linked_hg_parent_cache = $stmt->fetchAll(PDO::FETCH_GROUP|PDO::FETCH_COLUMN);
        $stmt = $this->backend_instance->db->prepare("SELECT 
                    dependency_dep_id, hostgroup_hg_id
                FROM dependency_hostgroupChild_relation
        ");
        $stmt->execute();
        $this->dependency_linked_hg_child_cache = $stmt->fetchAll(PDO::FETCH_GROUP|PDO::FETCH_COLUMN);
        
        # Servicegroup dependency
        $stmt = $this->backend_instance->db->prepare("SELECT 
                    dependency_dep_id, servicegroup_sg_id
                FROM dependency_servicegroupParent_relation
        ");
        $stmt->execute();
        $this->dependency_linked_sg_parent_cache = $stmt->fetchAll(PDO::FETCH_GROUP|PDO::FETCH_COLUMN);
        $stmt = $this->backend_instance->db->prepare("SELECT 
                    dependency_dep_id, servicegroup_sg_id
                FROM dependency_servicegroupChild_relation
        ");
        $stmt->execute();
        $this->dependency_linked_sg_child_cache = $stmt->fetchAll(PDO::FETCH_GROUP|PDO::FETCH_COLUMN);
        
        # Metaservice dependency
        $stmt = $this->backend_instance->db->prepare("SELECT 
                    dependency_dep_id, meta_service_meta_id
                FROM dependency_metaserviceParent_relation
        ");
        $stmt->execute();
        $this->dependency_linked_meta_parent_cache = $stmt->fetchAll(PDO::FETCH_GROUP|PDO::FETCH_COLUMN);
        $stmt = $this->backend_instance->db->prepare("SELECT 
                    dependency_dep_id, meta_service_meta_id
                FROM dependency_metaserviceChild_relation
        ");
        $stmt->execute();
        $this->dependency_linked_meta_child_cache = $stmt->fetchAll(PDO::FETCH_GROUP|PDO::FETCH_COLUMN);
        
        # Service dependency
        $stmt = $this->backend_instance->db->prepare("SELECT 
                    dependency_dep_id, host_host_id, service_service_id
                FROM dependency_serviceParent_relation
        ");
        $stmt->execute();
        $this->dependency_linked_service_parent_cache = $stmt->fetchAll(PDO::FETCH_GROUP|PDO::FETCH_ASSOC);
        $stmt = $this->backend_instance->db->prepare("SELECT 
                    dependency_dep_id, host_host_id, service_service_id 
                FROM dependency_serviceChild_relation
        ");
        $stmt->execute();
        $this->dependency_linked_service_child_cache = $stmt->fetchAll(PDO::FETCH_GROUP|PDO::FETCH_ASSOC);
    }
    
    private function buildCache() {
        if ($this->done_cache == 1) {
            return 0;
        }
        
        $this->getDependencyCache();
        $this->getDependencyLinkedCache();
        $this->done_cache = 1;
    }
    
    public function doHost() {
        $this->object_name = 'hostdependency';
        foreach ($this->dependency_cache as $dp_id => $dependency) {
            $dependency['host_name'] = array();
            if (isset($this->dependency_linked_host_parent_cache[$dp_id])) {
                foreach ($this->dependency_linked_host_parent_cache[$dp_id] as $value) {
                    if ($this->host_instance->checkGenerate($value)) {
                        $dependency['host_name'][] = $this->host_instance->getString($value, 'host_name');
                    }
                }
            }
            
            $dependency['dependent_host_name'] = array();
            if (isset($this->dependency_linked_host_child_cache[$dp_id])) {
                foreach ($this->dependency_linked_host_child_cache[$dp_id] as $value) {
                    if ($this->host_instance->checkGenerate($value)) {
                        $dependency['dependent_host_name'][] = $this->host_instance->getString($value, 'host_name');
                    }
                }
            }
            
            if (count($dependency['host_name']) == 0 || count($dependency['dependent_host_name']) == 0) {
                continue;
            }

            $this->generateObjectInFile($dependency, 0);
        }
    }
    
    public function doService() {
        $this->object_name = 'servicedependency';
        foreach ($this->dependency_cache as $dp_id => $dependency) {
            if (!isset($this->dependency_linked_service_parent_cache[$dp_id])) {
                continue;
            }
            foreach ($this->dependency_linked_service_parent_cache[$dp_id] as $value) {
                if (!isset($this->dependency_linked_service_child_cache[$dp_id])) {
                    continue;
                }
                if ($this->service_instance->checkGenerate($value['host_host_id'] . '.' . $value['service_service_id'])) {
                    $dependency['host_name'] = array($this->host_instance->getString($value['host_host_id'], 'host_name'));
                    $dependency['service_description'] = array($this->service_instance->getString($value['service_service_id'], 'service_description'));
                    
                    foreach ($this->dependency_linked_service_child_cache[$dp_id] as $value2) {
                        if ($this->service_instance->checkGenerate($value2['host_host_id'] . '.' . $value2['service_service_id'])) {
                            $dependency['dependent_host_name'] = array($this->host_instance->getString($value2['host_host_id'], 'host_name'));
                            $dependency['dependent_service_description'] = array($this->service_instance->getString($value2['service_service_id'], 'service_description'));

                            $this->generateObjectInFile($dependency, 0);
                        }
                    }
                }
            }
        }
    }
    
    public function doMetaService() {
        $meta_instance = MetaService::getInstance();
        if (!$meta_instance->hasMetaServices()) {
            return 0;
        }
        
        $this->object_name = 'servicedependency';
        foreach ($this->dependency_cache as $dp_id => $dependency) {
            if (!isset($this->dependency_linked_meta_parent_cache[$dp_id])) {
                continue;
            }
            foreach ($this->dependency_linked_meta_parent_cache[$dp_id] as $meta_id) {
                if (!isset($this->dependency_linked_meta_child_cache[$dp_id])) {
                    continue;
                }
                if ($meta_instance->checkGenerate($meta_id)) {
                    $dependency['host_name'] = array('_Module_Meta');
                    $dependency['service_description'] = array('meta_' . $meta_id);
                    
                    foreach ($this->dependency_linked_meta_child_cache[$dp_id] as $meta_id2) {
                        if ($meta_instance->checkGenerate($meta_id2)) {
                            $dependency['dependent_host_name'] = array('_Module_Meta');
                            $dependency['dependent_service_description'] = array('meta_' . $meta_id2);
                            
                            $this->generateObjectInFile($dependency, 0);
                        }
                    }
                }
            }
        }
    }
    
    public function doHostgroup() {
        $this->object_name = 'hostdependency';
        foreach ($this->dependency_cache as $dp_id => $dependency) {
            $dependency['hostgroup_name'] = array();
            if (isset($this->dependency_linked_hg_parent_cache[$dp_id])) {
                foreach ($this->dependency_linked_hg_parent_cache[$dp_id] as $value) {
                    if ($this->hg_instance->checkGenerate($value)) {
                        $dependency['hostgroup_name'][] = $this->hg_instance->getString($value, 'hostgroup_name');
                    }
                }
            }
            
            $dependency['dependent_hostgroup_name'] = array();
            if (isset($this->dependency_linked_hg_child_cache[$dp_id])) {
                foreach ($this->dependency_linked_hg_child_cache[$dp_id] as $value) {
                    if ($this->hg_instance->checkGenerate($value)) {
                        $dependency['dependent_hostgroup_name'][] = $this->hg_instance->getString($value, 'hostgroup_name');
                    }
                }
            }
            
            if (count($dependency['dependent_hostgroup_name']) == 0 || count($dependency['hostgroup_name']) == 0) {
                continue;
            }

            $this->generateObjectInFile($dependency, 0);
        }
    }
    
    public function doServicegroup() {
        $this->object_name = 'servicedependency';
        foreach ($this->dependency_cache as $dp_id => $dependency) {
            $dependency['servicegroup_name'] = array();
            if (isset($this->dependency_linked_sg_parent_cache[$dp_id])) {
                foreach ($this->dependency_linked_sg_parent_cache[$dp_id] as $value) {
                    if ($this->sg_instance->checkGenerate($value)) {
                        $dependency['servicegroup_name'][] = $this->sg_instance->getString($value, 'servicegroup_name');
                    }
                }
            }
            
            $dependency['dependent_servicegroup_name'] = array();
            if (isset($this->dependency_linked_sg_child_cache[$dp_id])) {
                foreach ($this->dependency_linked_sg_child_cache[$dp_id] as $value) {
                    if ($this->sg_instance->checkGenerate($value)) {
                        $dependency['dependent_servicegroup_name'][] = $this->sg_instance->getString($value, 'servicegroup_name');
                    }
                }
            }
            
            if (count($dependency['dependent_servicegroup_name']) == 0 || count($dependency['servicegroup_name']) == 0) {
                continue;
            }

            $this->generateObjectInFile($dependency, 0);
        }
    }
    
    public function generateObjects() {
        if ($this->has_dependency == 0) {
            return 0;
        }
        
        $this->doHost();
        $this->doService();
        $this->doHostgroup();
        $this->doServicegroup();
        $this->doMetaService();

    }
    
    public function reset() {
        $this->generated_dependencies = array();
        parent::reset();
    }

    public function hasDependency() {
        return $this->has_dependency;
    }

    public function getGeneratedDependencies() {
        return $this->generated_dependencies;
    }
}
