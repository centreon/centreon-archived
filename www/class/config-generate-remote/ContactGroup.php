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

use PDO;
use ConfigGenerateRemote\Abstracts\AbstractObject;

class ContactGroup extends AbstractObject
{
    protected $useCache = 1;
    private $doneCache = 0;
    private $cgServiceLinkedCache = [];
    protected $cgCache = [];
    protected $cg = null;
    protected $table = 'contactgroup';
    protected $generateFilename = 'contactgroups.infile';
    protected $attributesSelect = '
        cg_id,
        cg_name,
        cg_alias,
        cg_comment
    ';
    protected $attributesWrite = [
        'cg_id',
        'cg_name',
        'cg_alias',
        'cg_comment'
    ];
    protected $stmtCg = null;
    protected $stmtContact = null;
    protected $stmtCgService = null;

    /**
     * Generate contact group cache
     *
     * @return void
     */
    protected function getCgCache(): void
    {
        $stmt = $this->backendInstance->db->prepare(
            "SELECT $this->attributesSelect
            FROM contactgroup
            WHERE cg_activate = '1'"
        );
        $stmt->execute();
        $this->cgCache = $stmt->fetchAll(PDO::FETCH_GROUP | PDO::FETCH_UNIQUE | PDO::FETCH_ASSOC);
    }

    /**
     * Get contact groups linked to services
     *
     * @return void
     */
    private function getCgForServiceCache(): void
    {
        $stmt = $this->backendInstance->db->prepare(
            "SELECT contactgroup_cg_id, service_service_id
            FROM contactgroup_service_relation"
        );
        $stmt->execute();
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $value) {
            if (isset($this->cgServiceLinkedCache[$value['service_service_id']])) {
                $this->cgServiceLinkedCache[$value['service_service_id']][] = $value['contactgroup_cg_id'];
            } else {
                $this->cgServiceLinkedCache[$value['service_service_id']] = [$value['contactgroup_cg_id']];
            }
        }
    }

    /**
     * Build cache
     *
     * @return void|int
     */
    protected function buildCache()
    {
        if ($this->doneCache == 1) {
            return 0;
        }

        $this->getCgCache();
        $this->getCgForServiceCache();
        $this->doneCache = 1;
    }

    /**
     * Get service linked contact groups
     *
     * @param int $serviceId
     * @return array
     */
    public function getCgForService(int $serviceId): array
    {
        $this->buildCache();

        # Get from the cache
        if (isset($this->cgServiceLinkedCache[$serviceId])) {
            return $this->cgServiceLinkedCache[$serviceId];
        }
        if ($this->doneCache == 1) {
            return [];
        }

        if (is_null($this->stmtCgService)) {
            $this->stmtCgService = $this->backendInstance->db->prepare("SELECT 
                    contactgroup_cg_id
                FROM contactgroup_service_relation
                WHERE service_service_id = :service_id
            ");
        }

        $this->stmtCgService->bindParam(':service_id', $serviceId, PDO::PARAM_INT);
        $this->stmtCgService->execute();
        $this->cgServiceLinkedCache[$serviceId] = $this->stmtCgService->fetchAll(PDO::FETCH_COLUMN);

        return $this->cgServiceLinkedCache[$serviceId];
    }

    /**
     * Get contact group information
     *
     * @param int $cgId
     * @return void
     */
    public function getCgFromId(int $cgId)
    {
        if (is_null($this->stmtCg)) {
            $this->stmtCg = $this->backendInstance->db->prepare(
                "SELECT $this->attributesSelect
                FROM contactgroup
                WHERE cg_id = :cg_id AND cg_activate = '1'"
            );
        }
        $this->stmtCg->bindParam(':cg_id', $cgId, PDO::PARAM_INT);
        $this->stmtCg->execute();
        $results = $this->stmtCg->fetchAll(PDO::FETCH_ASSOC);
        $this->cg[$cgId] = array_pop($results);
        return $this->cg[$cgId];
    }

    /**
     * Get contact group linked contacts
     *
     * @param integer $cgId
     * @return void
     */
    public function getContactFromCgId(int $cgId)
    {
        if (!isset($this->cg[$cgId]['members_cache'])) {
            if (is_null($this->stmtContact)) {
                $this->stmtContact = $this->backendInstance->db->prepare(
                    "SELECT contact_contact_id
                    FROM contactgroup_contact_relation
                    WHERE contactgroup_cg_id = :cg_id"
                );
            }
            $this->stmtContact->bindParam(':cg_id', $cgId, PDO::PARAM_INT);
            $this->stmtContact->execute();
            $this->cg[$cgId]['members_cache'] = $this->stmtContact->fetchAll(PDO::FETCH_COLUMN);
        }

        $contact = Contact::getInstance($this->dependencyInjector);
        foreach ($this->cg[$cgId]['members_cache'] as $contactId) {
            $contact->generateFromContactId($contactId);
        }
    }

    /**
     * Generate contact group and get contact group name
     *
     * @param null|integer $cgId
     * @return void|string
     */
    public function generateFromCgId(?int $cgId)
    {
        if (is_null($cgId)) {
            return null;
        }

        $this->buildCache();

        if ($this->useCache == 1) {
            if (!isset($this->cgCache[$cgId])) {
                return null;
            }
            $this->cg[$cgId] = &$this->cgCache[$cgId];
        } elseif (!isset($this->cg[$cgId])) {
            $this->getCgFromId($cgId);
        }

        if (is_null($this->cg[$cgId])) {
            return null;
        }
        if ($this->checkGenerate($cgId)) {
            return $this->cg[$cgId]['cg_name'];
        }

        $this->getContactFromCgId($cgId);

        $this->cg[$cgId]['cg_id'] = $cgId;
        $this->generateObjectInFile($this->cg[$cgId], $cgId);

        return $this->cg[$cgId]['cg_name'];
    }
}
