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

class Contactgroup extends AbstractObject {
    private $use_cache = 1;
    private $done_cache = 0;
    private $cg_service_linked_cache = array();
    protected $cg_cache = array();
    protected $cg = null;
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
    
    protected function getCgCache() {
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
    
    protected function buildCache() {
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
    
    public function getCgFromId($cg_id) {
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
        return $this->cg[$cg_id];
    }
    
    public function getContactFromCgId($cg_id) {
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
