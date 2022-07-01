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

namespace Core\Contact\Infrastructure\Repository;

use Core\Contact\Domain\Model\ContactGroup;
use Centreon\Infrastructure\DatabaseConnection;
use Centreon\Domain\Contact\Interfaces\ContactInterface;
use Centreon\Infrastructure\Repository\AbstractRepositoryDRB;
use Core\Contact\Application\Repository\WriteContactGroupRepositoryInterface;

class DbWriteContactGroupRepository extends AbstractRepositoryDRB implements WriteContactGroupRepositoryInterface
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
    public function deleteContactGroupsForUser(ContactInterface $user): void
    {
        $statement = $this->db->prepare($this->translateDbName(
            "DELETE FROM `:db`.contactgroup_contact_relation WHERE contact_contact_id = :userId"
        ));
        $statement->bindValue(':userId', $user->getId(), \PDO::PARAM_INT);
        $statement->execute();
    }

    /**
     * @inheritDoc
     */
    public function insertContactGroupForUser(ContactInterface $user, ContactGroup $contactGroup): void
    {
        $statement = $this->db->prepare($this->translateDbName(
            "INSERT INTO contactgroup_contact_relation VALUES (:userId, :contactGroupId)"
        ));
        $statement->bindValue(':userId', $user->getId(), \PDO::PARAM_INT);
        $statement->bindValue(':contactGroupId', $contactGroup->getId(), \PDO::PARAM_INT);
        $statement->execute();
    }
}
