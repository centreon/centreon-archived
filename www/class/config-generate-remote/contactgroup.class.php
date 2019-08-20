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

class Contactgroup extends AbstractObject
{
    protected $use_cache = 1;
    private $done_cache = 0;
    private $cg_service_linked_cache = array();
    protected $cg_cache = array();
    protected $cg = null;
    protected $table = 'contactgroup';
    protected $generate_filename = 'contactgroups.infile';
    protected $attributes_select = '
        cg_id,
        cg_name,
        cg_alias,
        cg_comment
    ';
    protected $attributes_write = array(
        'cg_id',
        'cg_name',
        'cg_alias',
        'cg_comment'
    );
    protected $stmt_cg = null;
    protected $stmt_contact = null;
    protected $stmt_cg_service = null;

    protected function getCgCache()
    {
        $stmt = $this->backend_instance->db->prepare("SELECT 
                    $this->attributes_select
                FROM contactgroup
                WHERE cg_activate = '1'
        ");
        $stmt->execute();
        $this->cg_cache = $stmt->fetchAll(PDO::FETCH_GROUP | PDO::FETCH_UNIQUE | PDO::FETCH_ASSOC);
    }

    private function getCgForServiceCache()
    {
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

    protected function buildCache()
    {
        if ($this->done_cache == 1) {
            return 0;
        }

        $this->getCgCache();
        $this->getCgForServiceCache();
        $this->done_cache = 1;
    }

    public function getCgForService($service_id)
    {
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

    public function getCgFromId($cg_id)
    {
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

    public function getContactFromCgId($cg_id)
    {
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

        $contact = Contact::getInstance($this->dependencyInjector);
        foreach ($this->cg[$cg_id]['members_cache'] as $contact_id) {
            $contact->generateFromContactId($contact_id);
        }
    }

    public function generateFromCgId($cg_id)
    {
        if (is_null($cg_id)) {
            return null;
        }

        $this->buildCache();

        if ($this->use_cache == 1) {
            if (!isset($this->cg_cache[$cg_id])) {
                return null;
            }
            $this->cg[$cg_id] = &$this->cg_cache[$cg_id];
        } elseif (!isset($this->cg[$cg_id])) {
            $this->getCgFromId($cg_id);
        }

        if (is_null($this->cg[$cg_id])) {
            return null;
        }
        if ($this->checkGenerate($cg_id)) {
            return $this->cg[$cg_id]['cg_name'];
        }

        $this->getContactFromCgId($cg_id);

        $this->cg[$cg_id]['cg_id'] = $cg_id;
        $this->generateObjectInFile($this->cg[$cg_id], $cg_id);
        return $this->cg[$cg_id]['cg_name'];
    }
}
