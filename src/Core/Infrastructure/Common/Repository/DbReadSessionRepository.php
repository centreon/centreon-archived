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

namespace Core\Infrastructure\Common\Repository;

use _PHPStan_61858e129\Nette\NotImplementedException;
use Centreon\Infrastructure\DatabaseConnection;
use Centreon\Infrastructure\Repository\AbstractRepositoryDRB;
use Core\Application\Common\Session\Repository\ReadSessionRepositoryInterface;

class DbReadSessionRepository extends AbstractRepositoryDRB implements ReadSessionRepositoryInterface
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
    public function findSessionIdsByUserid(int $userId): array
    {
        $statement = $this->db->prepare(
            $this->translateDbName(
                'SELECT session_id FROM session WHERE user_id = :user_id'
            )
        );
        $statement->bindValue(':user_id', $userId, \PDO::PARAM_INT);
        $statement->execute();

        $sessionIds = [];
        while ($result = $statement->fetch(\PDO::FETCH_ASSOC)) {
            $sessionIds[] = $result['session_id'];
        }
        return $sessionIds;
    }

    /**
     * @inheritDoc
     */
    public function getValueFromSession(string $sessionId, string $key): mixed
    {
        throw RepositoryException::notImplemented('DbReadRepository::getValueFromSession');
    }
}
