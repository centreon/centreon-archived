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

class Escalation extends AbstractObject {
    private $use_cache = 1;
    private $done_cache = 0;
    private $has_escalation = 1; # by default, we have.
    private $escalation_cache = array();
    private $escalation_linked_cg_cache = array();
    private $escalation_linked_host_cache = array();
    private $escalation_linked_hg_cache = array();
    private $escalation_linked_service_cache = array();
    private $escalation_linked_sg_cache = array();
    private $escalation_linked_meta_cache = array();
    private $hosts_build = array();
    private $services_build = array();
    protected $generate_filename = 'escalations.cfg';
    protected $object_name = 'hostescalation';
    protected $attributes_select = "
        esc_id,
        esc_name as ';escalation_name',
        first_notification,
        last_notification,
        notification_interval,
        escalation_period as escalation_period_id,
        escalation_options1 as escalation_options_host,
        escalation_options2 as escalation_options_service,
        host_inheritance_to_services,
        hostgroup_inheritance_to_services
    ";
    protected $attributes_write = array(
        ';escalation_name',
        'first_notification',
        'last_notification',
        'notification_interval',
        'escalation_period',
        'escalation_options',
    );
    protected $attributes_array = array(
        'hostgroup_name',
        'host_name',
        'servicegroup_name',
        'service_description',
        'contact_groups',
    );
    protected $host_instance = null;
    protected $service_instance = null;
    protected $hg_instance = null;
    protected $sg_instance = null;
    protected $stmt_escalation = null;
    protected $stmt_cg = null;
    protected $stmt_host = null;
    protected $stmt_service = null;
    protected $stmt_hg = null;
    protected $stmt_sg = null;
    protected $stmt_meta = null;
    
    public function __construct() {
        parent::__construct();
        $this->host_instance = Host::getInstance();
        $this->service_instance = Service::getInstance();
        $this->hg_instance = Hostgroup::getInstance();
        $this->sg_instance = Servicegroup::getInstance();
        $this->buildCache();
    }
    
    private function getEscalationCache() {
        $stmt = $this->backend_instance->db->prepare("SELECT 
                    $this->attributes_select
                FROM escalation
        ");
        $stmt->execute();
        $this->escalation_cache = $stmt->fetchAll(PDO::FETCH_GROUP|PDO::FETCH_UNIQUE|PDO::FETCH_ASSOC);
        
        $stmt = $this->backend_instance->db->prepare("SELECT 
                    escalation_esc_id, contactgroup_cg_id
                FROM escalation_contactgroup_relation
        ");
        $stmt->execute();
        $this->escalation_linked_cg_cache = $stmt->fetchAll(PDO::FETCH_GROUP|PDO::FETCH_COLUMN);
        
        if (count($this->escalation_cache) == 0) {
            $this->has_escalation = 0;
        }
    }
    
    private function getEscalationLinkedCache() {
        if ($this->has_escalation == 0) {
            return 0;
        }
        
        $stmt = $this->backend_instance->db->prepare("SELECT 
                    host_host_id, escalation_esc_id
                FROM escalation_host_relation
        ");
        $stmt->execute();
        $this->escalation_linked_host_cache = $stmt->fetchAll(PDO::FETCH_GROUP|PDO::FETCH_COLUMN);
        
        $stmt = $this->backend_instance->db->prepare("SELECT 
                    hostgroup_hg_id, escalation_esc_id
                FROM escalation_hostgroup_relation
        ");
        $stmt->execute();
        $this->escalation_linked_hg_cache = $stmt->fetchAll(PDO::FETCH_GROUP|PDO::FETCH_COLUMN);
        
        $stmt = $this->backend_instance->db->prepare("SELECT 
                    servicegroup_sg_id, escalation_esc_id
                FROM escalation_servicegroup_relation
        ");
        $stmt->execute();
        $this->escalation_linked_sg_cache = $stmt->fetchAll(PDO::FETCH_GROUP|PDO::FETCH_COLUMN);
        
        $stmt = $this->backend_instance->db->prepare("SELECT 
                    meta_service_meta_id, escalation_esc_id
                FROM escalation_meta_service_relation
        ");
        $stmt->execute();
        $this->escalation_linked_meta_cache = $stmt->fetchAll(PDO::FETCH_GROUP|PDO::FETCH_COLUMN);
        
        $stmt = $this->backend_instance->db->prepare("SELECT 
                    CONCAT(host_host_id, '_', service_service_id), escalation_esc_id
                FROM escalation_service_relation
        ");
        $stmt->execute();
        $this->escalation_linked_service_cache = $stmt->fetchAll(PDO::FETCH_GROUP|PDO::FETCH_COLUMN);
    }
    
    private function buildCache() {
        if ($this->done_cache == 1) {
            return 0;
        }
        
        $this->getEscalationCache();
        $this->getEscalationLinkedCache();
        $this->done_cache = 1;
    }
    
    private function generateSubObjects(&$escalation, $esc_id) {
        $period = Timeperiod::getInstance();
        $cg = Contactgroup::getInstance();

        $escalation['escalation_period'] = $period->generateFromTimeperiodId($escalation['escalation_period_id']);
        $escalation['contact_groups'] = array();
        foreach ($this->escalation_linked_cg_cache[$esc_id] as $cg_id) {
            $escalation['contact_groups'][] = $cg->generateFromCgId($cg_id);
        }
    }
    
    private function getEscalationFromId($escalation_id) {
        if (isset($this->escalation_cache[$escalation_id])) {
            return $this->escalation_cache[$escalation_id];
        }
        if ($this->use_cache == 1) {
            return null;
        }

        if (is_null($this->stmt_escalation)) {
            $this->stmt_escalation = $this->backend_instance->db->prepare("SELECT 
                    $this->attributes_select
                FROM escalation
                WHERE esc_id = :esc_id
            ");
        }
        $this->stmt_escalation->bindParam(':esc_id', $escalation_id, PDO::PARAM_INT);
        $this->stmt_escalation->execute();
        $this->escalation_cache[$escalation_id] = array_pop($this->stmt_escalation->fetchAll(PDO::FETCH_ASSOC));
        if (is_null($this->escalation_cache[$escalation_id])) {
            return null;
        }
        
        if (is_null($this->stmt_cg)) {
            $this->stmt_cg = $this->backend_instance->db->prepare("SELECT 
                    contactgroup_cg_id
                FROM escalation_contactgroup_relation
                WHERE escalation_esc_id = :esc_id
            ");
        }
        $this->stmt_cg->bindParam(':esc_id', $escalation_id, PDO::PARAM_INT);
        $this->stmt_cg->execute();
        $this->escalation_linked_cg_cache[$escalation_id] = $this->stmt_cg->fetchAll(PDO::FETCH_COLUMN);
        
        return $this->escalation_cache[$escalation_id];
    }
    
    private function addHost($host_id) {
        if ($this->use_cache == 0) {
            if (is_null($this->stmt_host)) {
                $this->stmt_host = $this->backend_instance->db->prepare("SELECT 
                        escalation_esc_id
                    FROM escalation_host_relation
                    WHERE host_host_id = :host_id
                ");
            }

            $this->stmt_host->bindParam(':host_id', $host_id, PDO::PARAM_INT);
            $this->stmt_host->execute();
            $this->escalation_linked_host_cache[$host_id] = $this->stmt_host->fetchAll(PDO::FETCH_COLUMN);
        }
        if (!isset($this->escalation_linked_host_cache[$host_id])) {
            return 0;
        }
        
        foreach ($this->escalation_linked_host_cache[$host_id] as $escalation_id) {
            if (!isset($this->hosts_build[$escalation_id])) {
                $this->hosts_build[$escalation_id] = array();
            }
            $this->hosts_build[$escalation_id][] = $this->host_instance->getString($host_id, 'host_name');
            
            if (isset($this->escalation_cache[$escalation_id]['host_inheritance_to_services']) && 
                $this->escalation_cache[$escalation_id]['host_inheritance_to_services'] == 1) {
                $services = &$this->service_instance->getGeneratedServices();
                // host without services
                if (!isset($services[$host_id])) {
                    continue;
                }
                foreach ($services[$host_id] as $service_id) {
                    if (!isset($this->services_build[$escalation_id])) {
                        $this->services_build[$escalation_id] = array($host_id => array());
                    }
                    $this->services_build[$escalation_id][$host_id][$service_id] = 1;
                }
            }
        }
    }
    
    private function addHostgroup($hg_id, $hostgroup) {
        if ($this->use_cache == 0) {
            if (is_null($this->stmt_hg)) {
                $this->stmt_hg = $this->backend_instance->db->prepare("SELECT 
                        escalation_esc_id
                    FROM escalation_hostgroup_relation
                    WHERE hostgroup_hg_id = :hg_id
                ");
            }

            $this->stmt_hg->bindParam(':hg_id', $hg_id, PDO::PARAM_INT);
            $this->stmt_hg->execute();
            $this->escalation_linked_hg_cache[$hg_id] = $this->stmt_hg->fetchAll(PDO::FETCH_COLUMN);
        }
        if (!isset($this->escalation_linked_hg_cache[$hg_id])) {
            return 0;
        }
        
        foreach ($this->escalation_linked_hg_cache[$hg_id] as $escalation_id) {
            if (isset($this->escalation_cache[$escalation_id]['hostgroup_inheritance_to_services']) && 
                $this->escalation_cache[$escalation_id]['hostgroup_inheritance_to_services'] == 1) {
                $services = &$this->service_instance->getGeneratedServices();
                
                foreach ($hostgroup['members'] as $host_name) {
                    $host_id = $this->host_instance->getHostIdByHostName($host_name);
                    // host without services
                    if (!isset($services[$host_id])) {
                        continue;
                    }
                    foreach ($services[$host_id] as $service_id) {
                        if (!isset($this->services_build[$escalation_id])) {
                            $this->services_build[$escalation_id] = array($host_id => array());
                        }
                        $this->services_build[$escalation_id][$host_id][$service_id] = 1;
                    }
                }
            }
            
            
            if (!isset($this->hg_build[$escalation_id])) {
                $this->hg_build[$escalation_id] = array();
            }
            $hostgroup_name = $this->hg_instance->getString($hg_id, 'hostgroup_name');
            if (!is_null($hostgroup_name)) {
                $this->hg_build[$escalation_id][] = $hostgroup_name;
            }
        }
    }
    
    private function addService($host_id, $service_id) {
        if ($this->use_cache == 0) {
            if (is_null($this->stmt_service)) {
                $this->stmt_service = $this->backend_instance->db->prepare("SELECT 
                         escalation_esc_id
                    FROM escalation_service_relation
                    WHERE host_host_id = :host_id AND service_service_id = :service_id
                ");
            }

            $this->stmt_service->bindParam(':host_id', $host_id, PDO::PARAM_INT);
            $this->stmt_service->bindParam(':service_id', $service_id, PDO::PARAM_INT);
            $this->stmt_service->execute();
            $this->escalation_linked_service_cache[$host_id . '_' . $service_id] = $this->stmt_service->fetchAll(PDO::FETCH_COLUMN);
        }
        if (!isset($this->escalation_linked_service_cache[$host_id . '_' . $service_id])) {
            return 0;
        }
        
        foreach ($this->escalation_linked_service_cache[$host_id . '_' . $service_id] as $escalation_id) {
            if (!isset($this->services_build[$escalation_id])) {
                $this->services_build[$escalation_id] = array($host_id => array());
            }
            $this->services_build[$escalation_id][$host_id][$service_id] = 1;
        }
    }
    
    private function addServicegroup($sg_id) {
        if ($this->use_cache == 0) {
            if (is_null($this->stmt_sg)) {
                $this->stmt_sg = $this->backend_instance->db->prepare("SELECT 
                        escalation_esc_id
                    FROM escalation_servicegroup_relation
                    WHERE servicegroup_sg_id = :sg_id
                ");
            }

            $this->stmt_sg->bindParam(':sg_id', $sg_id, PDO::PARAM_INT);
            $this->stmt_sg->execute();
            $this->escalation_linked_sg_cache[$sg_id] = $this->stmt_sg->fetchAll(PDO::FETCH_COLUMN);
        }
        if (!isset($this->escalation_linked_sg_cache[$sg_id])) {
            return 0;
        }
        
        foreach ($this->escalation_linked_sg_cache[$sg_id] as $escalation_id) {
            if (!isset($this->sg_build[$escalation_id])) {
                $this->sg_build[$escalation_id] = array();
            }
            $servicegroup_name = $this->sg_instance->getString($sg_id, 'servicegroup_name');
            if (!is_null($servicegroup_name)) {
                $this->sg_build[$escalation_id][] = $servicegroup_name;
            }
        }
    }
    
    private function getEscalationFromMetaId($meta_id) {
        if ($this->use_cache == 0) {
            if (is_null($this->stmt_meta)) {
                $this->stmt_service = $this->backend_instance->db->prepare("SELECT 
                         escalation_esc_id
                    FROM escalation_meta_service_relation
                    WHERE meta_service_meta_id = :meta_id
                ");
            }

            $this->stmt_service->bindParam(':meta_id', $meta_id, PDO::PARAM_INT);
            $this->stmt_service->execute();
            $this->escalation_linked_meta_cache[$meta_id] = $this->stmt_service->fetchAll(PDO::FETCH_COLUMN);
        }
        if (!isset($this->escalation_linked_meta_cache[$meta_id])) {
            return array();
        }
        
        return $this->escalation_linked_meta_cache[$meta_id];
    }
    
    private function generateHosts() {
        $this->object_name = 'hostescalation';
        foreach ($this->hosts_build as $escalation_id => $values) {
            $object = $this->getEscalationFromId($escalation_id);
            $object['host_name'] = &$values;
            $object['escalation_options'] = $object['escalation_options_host'];
            # Dont care of the id (we set 0)
            $this->generateSubObjects($object, $escalation_id);
            $this->generateObjectInFile($object, 0);
        }
    }
    
    private function generateServices() {
        $this->object_name = 'serviceescalation';
        foreach ($this->services_build as $escalation_id => $hosts) {
            foreach ($hosts as $host_id => $services) {
                foreach ($services as $service_id => $service) {
                    $object = $this->getEscalationFromId($escalation_id);
                    $object['host_name'] = array($this->host_instance->getString($host_id, 'host_name'));
                    $object['service_description'] = array($this->service_instance->getString($service_id, 'service_description'));
                    $object['escalation_options'] = $object['escalation_options_service'];                
                    # Dont care of the id (we set 0)
                    $this->generateSubObjects($object, $escalation_id);
                    $this->generateObjectInFile($object, 0);
                }
            }
        }
    }
    
    private function generateHostgroups() {
        $this->object_name = 'hostescalation';
        foreach ($this->hg_build as $escalation_id => $values) {
            $object = $this->getEscalationFromId($escalation_id);            
            # No hosgroup enabled
            if (count($values) == 0) {
                continue;
            }
            $object['hostgroup_name'] = &$values;
            $object['escalation_options'] = $object['escalation_options_host'];
            # Dont care of the id (we set 0)
            $this->generateSubObjects($object, $escalation_id);
            $this->generateObjectInFile($object, 0);
        }
    }
    
    private function generateServicegroups() {
        $this->object_name = 'serviceescalation';
        foreach ($this->sg_build as $escalation_id => $values) {
            $object = $this->getEscalationFromId($escalation_id);
            # No servicegroup enabled
            if (count($values) == 0) {
                continue;
            }
            $object['servicegroup_name'] = &$values;
            $object['escalation_options'] = $object['escalation_options_service'];
            # Dont care of the id (we set 0)
            $this->generateSubObjects($object, $escalation_id);
            $this->generateObjectInFile($object, 0);
        }
    }
    
    public function doHostService() {
        $services = &$this->service_instance->getGeneratedServices();
        foreach ($services as $host_id => &$values) {
            $this->addHost($host_id);
            foreach ($values as $service_id) {
                $this->addService($host_id, $service_id);
            }
        }        
        
        $this->generateHosts();
        $this->generateServices();
    }
    
    public function doHostgroup() {
        $hostgroups = &$this->hg_instance->getHostgroups();
        foreach ($hostgroups as $hg_id => &$value) {            
            $this->addHostgroup($hg_id, $value);
        }
        
        $this->generateHostgroups();
    }
    
    public function doServicegroup() {
        $servicegroups = &$this->sg_instance->getServicegroups();
        foreach ($servicegroups as $sg_id => &$value) {            
            $this->addServicegroup($sg_id);
        }
        
        $this->generateServicegroups();
    }
    
    public function doMetaService() {
        if (!MetaService::getInstance()->hasMetaServices()) {
            return 0;
        }
        $this->object_name = 'serviceescalation';
        foreach (MetaService::getInstance()->getGeneratedServices() as $meta_id) {
            $escalation = $this->getEscalationFromMetaId($meta_id);
            foreach ($escalation as $escalation_id) {
                $object = $this->getEscalationFromId($escalation_id);
                $object['host_name'] = array('_Module_Meta');
                $object['service_description'] = array('meta_' . $meta_id);
                $object['escalation_options'] = $object['escalation_options_service'];                
                # Dont care of the id (we set 0)
                $this->generateSubObjects($object, $escalation_id);
                $this->generateObjectInFile($object, 0);
            }
        }
    }
    
    public function generateObjects() {
        if ($this->has_escalation == 0) {
            return 0;
        }
        $this->doHostgroup();
        $this->doHostService();
        $this->doServicegroup();
        $this->doMetaService();
    }
    
    public function reset() {
        $this->hosts_build = array();
        $this->services_build = array();
        $this->hg_build = array();
        $this->sg_build = array();
        parent::reset();
    }
}
