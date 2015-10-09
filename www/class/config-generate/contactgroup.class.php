<?php

class Contactgroup extends AbstractObject {
    private $use_cache = 1;
    private $done_cache = 0;
    private $cg_service_linked_cache = array();
    private $cg_cache = array();
    private $cg = null;
    protected $generate_filename = 'contactgroups.cfg';
    protected $object_name = 'contactgroup';
    protected $attributes_select = '
        cg_id,
        cg_name as contactgroup_name,
        cg_alias as alias
    ';
    protected $attributes_write = array(
        'contactgroup_name',
        'alias',
    );
    protected $attributes_array = array(
        'members'
    );
    protected $stmt_cg = null;
    protected $stmt_contact = null;
    protected $stmt_cg_service = null;
    
    private function getCgCache() {
        $stmt = $this->backend_instance->db->prepare("SELECT 
                    $this->attributes_select
                FROM contactgroup
                WHERE cg_activate = '1'
        ");
        $stmt->execute();
        $this->cg_cache = $stmt->fetchAll(PDO::FETCH_GROUP|PDO::FETCH_UNIQUE|PDO::FETCH_ASSOC);
    }
    
    private function getCgForServiceCache() {
        $stmt = $this->backend_instance->db->prepare("SELECT 
                    contactgroup_cg_id, service_service_id
                FROM contactgroup_service_relation
        ");
        $stmt->execute();
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $value) {
            if (isset($this->cg_service_linked_cache[$value['service_service_id']])) {
                $this->cg_service_linked_cache[$value['service_service_id']][] = $value['contactgroup_cg_id'];
            } else {
                $this->cg_service_linked_cache[$value['service_service_id']] = array($value['contactgroup_cg_id']);
            }
        }
    }
    
    private function buildCache() {
        if ($this->done_cache == 1) {
            return 0;
        }
        
        $this->getCgCache();
        $this->getCgForServiceCache();
        $this->done_cache = 1;
    }
    
    public function getCgForService($service_id) {
        $this->buildCache();
        
        # Get from the cache
        if (isset($this->cg_service_linked_cache[$service_id])) {
            return $this->cg_service_linked_cache[$service_id];
        }
        if ($this->done_cache == 1) {
            return array();
        }
        
        if (is_null($this->stmt_cg_service)) {
            $this->stmt_cg_service = $this->backend_instance->db->prepare("SELECT 
                    contactgroup_cg_id
                FROM contactgroup_service_relation
                WHERE service_service_id = :service_id
            ");
        }

        $this->stmt_cg_service->bindParam(':service_id', $service_id, PDO::PARAM_INT);
        $this->stmt_cg_service->execute();
        $this->cg_service_linked_cache[$service_id] = $this->stmt_cg_service->fetchAll(PDO::FETCH_COLUMN);
        return $this->cg_service_linked_cache[$service_id];
    }
    
    private function getCgFromId($cg_id) {
        if (is_null($this->stmt_cg)) {
            $this->stmt_cg = $this->backend_instance->db->prepare("SELECT 
                    $this->attributes_select
                FROM contactgroup
                WHERE cg_id = :cg_id AND cg_activate = '1'
            ");
        }
        $this->stmt_cg->bindParam(':cg_id', $cg_id, PDO::PARAM_INT);
        $this->stmt_cg->execute();
        $results = $this->stmt_cg->fetchAll(PDO::FETCH_ASSOC);
        $this->cg[$cg_id] = array_pop($results);
    }
    
    protected function getContactFromCgId($cg_id) {
        if (!isset($this->cg[$cg_id]['members_cache'])) {
            if (is_null($this->stmt_contact)) {
                $this->stmt_contact = $this->backend_instance->db->prepare("SELECT 
                        contact_contact_id
                    FROM contactgroup_contact_relation
                    WHERE contactgroup_cg_id = :cg_id
                ");
            }
            $this->stmt_contact->bindParam(':cg_id', $cg_id, PDO::PARAM_INT);
            $this->stmt_contact->execute();
            $this->cg[$cg_id]['members_cache'] = $this->stmt_contact->fetchAll(PDO::FETCH_COLUMN);
        }
        
        $contact = Contact::getInstance();
        $this->cg[$cg_id]['members'] = array();
        foreach ($this->cg[$cg_id]['members_cache'] as $contact_id) {
            $member = $contact->generateFromContactId($contact_id);
            # Can have contact template in a contact group ???!!
            if (!is_null($member) && !$contact->isTemplate($contact_id)) {
                $this->cg[$cg_id]['members'][] = $member;
            }
        }
    }
    
    public function generateFromCgId($cg_id) {     
        if (is_null($cg_id)) {
            return null;
        }
        
        $this->buildCache();
        
        if ($this->use_cache == 1) {
            if (!isset($this->cg_cache[$cg_id])) {
                return null;
            }
            $this->cg[$cg_id] = &$this->cg_cache[$cg_id];
        } else if (!isset($this->cg[$cg_id])) {
            $this->getCgFromId($cg_id);
        }
        
        if (is_null($this->cg[$cg_id])) {
            return null;
        }
        if ($this->checkGenerate($cg_id)) {
            return $this->cg[$cg_id]['contactgroup_name'];
        }
        
        $this->getContactFromCgId($cg_id);
        
        $this->generateObjectInFile($this->cg[$cg_id], $cg_id);
        return $this->cg[$cg_id]['contactgroup_name'];
    }
}

?>
