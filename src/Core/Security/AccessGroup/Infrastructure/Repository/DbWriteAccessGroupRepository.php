<?php

/*
* Copyright 2005 - 2022 Centreon (https://www.centreon.com/)
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

namespace Core\Security\AccessGroup\Infrastructure\Repository;

use Centreon\Infrastructure\DatabaseConnection;
use Centreon\Domain\Contact\Interfaces\ContactInterface;
use Centreon\Infrastructure\Repository\AbstractRepositoryDRB;
use Core\Security\AccessGroup\Application\Repository\WriteAccessGroupRepositoryInterface;

class DbWriteAccessGroupRepository extends AbstractRepositoryDRB implements WriteAccessGroupRepositoryInterface
{
    /**
     * @param DatabaseConnection $db
     */
    public function __construct(DatabaseConnection $db)
    {
        $this->db = $db;
    }

    /**
     * @inheritDoc
     */
    public function deleteAccessGroupsForUser(ContactInterface $user): void
    {
        $statement = $this->db->prepare($this->translateDbName(
            "DELETE FROM acl_group_contacts_relations WHERE contact_contact_id = :userId"
        ));
        $statement->bindValue(':userId', $user->getId(), \PDO::PARAM_INT);
        $statement->execute();
    }

    /**
     * @inheritDoc
     */
    public function insertAccessGroupsForUser(ContactInterface $user, array $accessGroups): void
    {
        $statement = $this->db->prepare($this->translateDbName(
            "INSERT INTO acl_group_contacts_relations VALUES (:userId, :accessGroupId)"
        ));
        $statement->bindValue(':userId', $user->getId(), \PDO::PARAM_INT);
        foreach ($accessGroups as $accessGroup) {
            $statement->bindValue(':accessGroupId', $accessGroup->getId(), \PDO::PARAM_INT);
            $statement->execute();
        }
    }
}
