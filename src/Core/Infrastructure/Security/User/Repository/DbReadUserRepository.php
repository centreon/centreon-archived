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

namespace Core\Infrastructure\Security\User\Repository;

use Centreon\Domain\Log\LoggerTrait;
use Core\Domain\Security\User\Model\User;
use Centreon\Infrastructure\DatabaseConnection;
use Core\Infrastructure\Security\User\Repository\DbUserFactory;
use Centreon\Infrastructure\Repository\AbstractRepositoryDRB;
use Core\Application\Security\User\Repository\ReadUserRepositoryInterface;

class DbReadUserRepository extends AbstractRepositoryDRB implements ReadUserRepositoryInterface
{
    use LoggerTrait;

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
    public function findUserByAlias(string $alias): ?User
    {
        $this->info('Searching for user in DBMS', [
            'user_alias' => $alias
        ])
        $statement = $this->db->prepare(
            "SELECT c.contact_alias, c.contact_id,  cp.password, cp.creation_date FROM contact c
            INNER JOIN contact_password cp ON c.contact_id = cp.contact_id
            WHERE c.contact_alias = :alias ORDER BY cp.creation_date ASC"
        );
        $statement->bindValue(':alias', $alias, \PDO::PARAM_STR);
        $statement->execute();
        $user = null;
        if (($result = $statement->fetchAll(\PDO::FETCH_ASSOC)) !== false) {
            if (empty($result)) {
                return null;
            }
            $user = DbUserFactory::createFromRecord($result);
        }

        return $user;
    }
}
