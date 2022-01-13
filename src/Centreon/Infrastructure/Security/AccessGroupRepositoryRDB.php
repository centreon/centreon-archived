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
declare(strict_types=1);

namespace Centreon\Infrastructure\Security;

use Centreon\Domain\Contact\Interfaces\ContactInterface;
use Centreon\Domain\Security\AccessGroup;
use Centreon\Domain\Security\Interfaces\AccessGroupRepositoryInterface;
use Centreon\Infrastructure\DatabaseConnection;

/**
 * Database repository for the access groups.
 *
 * @package Centreon\Infrastructure\Security
 */
final class AccessGroupRepositoryRDB implements AccessGroupRepositoryInterface
{
    /**
     * @var DatabaseConnection
     */
    private $pdo;

    /**
     * AccessGroupRepositoryRDB constructor.
     *
     * @param DatabaseConnection $pdo
     */
    public function __construct(DatabaseConnection $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * @inheritDoc
     */
    public function findByContact(ContactInterface $contact): array
    {
        $contactGroups = [];
        if (! is_null($contactId = $contact->getId())) {
            /**
             * Retrieve all access group from contact
             * and contact groups linked to contact
             */
            $prepare = $this->pdo->prepare(
                "SELECT * FROM acl_groups
                WHERE acl_group_activate = '1'
                AND (
                  acl_group_id IN (
                    SELECT acl_group_id FROM acl_group_contacts_relations
                    WHERE contact_contact_id = :contact_id
                  )
                  OR acl_group_id IN (
                    SELECT acl_group_id FROM acl_group_contactgroups_relations agcr
                    INNER JOIN contactgroup_contact_relation cgcr
                      ON cgcr.contactgroup_cg_id = agcr.cg_cg_id
                    WHERE cgcr.contact_contact_id = :contact_id
                  )
                )"
            );
            $prepare->bindValue(':contact_id', $contactId, \PDO::PARAM_INT);
            if ($prepare->execute()) {
                while ($result = $prepare->fetch(\PDO::FETCH_ASSOC)) {
                    $contactGroups[] = (new AccessGroup())
                        ->setId((int) $result['acl_group_id'])
                        ->setName($result['acl_group_name'])
                        ->setAlias($result['acl_group_alias'])
                        ->setActivate($result['acl_group_activate'] === '1');
                }
                return $contactGroups;
            }
        }
        return $contactGroups;
    }
}
