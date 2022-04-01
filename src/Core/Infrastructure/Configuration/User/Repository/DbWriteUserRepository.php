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

namespace Core\Infrastructure\Configuration\User\Repository;

use Centreon\Infrastructure\DatabaseConnection;
use Centreon\Infrastructure\Repository\AbstractRepositoryDRB;
use Core\Application\Configuration\User\Repository\WriteUserRepositoryInterface;
use Core\Domain\Configuration\User\Model\User;

class DbWriteUserRepository extends AbstractRepositoryDRB implements WriteUserRepositoryInterface
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
    public function updateUser(User $user): void
    {
        $statement = $this->db->prepare(
            $this->translateDbName(
                'UPDATE `:db`.`contact`
                    SET contact_name = :name,
                    contact_alias = :alias,
                    contact_email = :email,
                    contact_admin = :is_admin,
                    contact_theme = :theme
                WHERE contact_id = :id'
            )
        );
        $statement->bindValue(':name', $user->getName(), \PDO::PARAM_STR);
        $statement->bindValue(':alias', $user->getAlias(), \PDO::PARAM_STR);
        $statement->bindValue(':email', $user->getEmail(), \PDO::PARAM_STR);
        $statement->bindValue(':is_admin', $user->isAdmin() ? '1' : '0', \PDO::PARAM_STR);
        $statement->bindValue(':theme', $user->getTheme(), \PDO::PARAM_STR);
        $statement->bindValue(':id', $user->getId(), \PDO::PARAM_INT);
        $statement->execute();
    }
}
