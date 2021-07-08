<?php

/*
 * Copyright 2005 - 2021 Centreon (https://www.centreon.com/)
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

namespace Security\Infrastructure\Repository;

use Centreon\Infrastructure\DatabaseConnection;
use Security\Domain\Authentication\Model\Session;
use Centreon\Domain\Repository\AbstractRepositoryDRB;
use Security\Domain\Authentication\Interfaces\SessionRepositoryInterface;

class SessionRepository extends AbstractRepositoryDRB implements SessionRepositoryInterface
{

    /**
     * @param DatabaseConnection $db
     */
    public function __construct(
        DatabaseConnection $db
    ) {
        $this->db = $db;
    }

    /**
     * @inheritDoc
     */
    public function deleteSession(string $token): void
    {
        $deleteSessionStatement = $this->db->prepare(
            $this->translateDbName(
                "DELETE FROM `:db`.session WHERE session_id = :token"
            )
        );
        $deleteSessionStatement->bindValue(':token', $token, \PDO::PARAM_STR);
        $deleteSessionStatement->execute();
    }

    /**
     * @inheritDoc
     */
    public function deleteExpiredSession(): void
    {
        $sessionIdStatement = $this->db->query(
            'SELECT session_id FROM `session`
            WHERE last_reload <
                (SELECT UNIX_TIMESTAMP(NOW() - INTERVAL (`value` * 60) SECOND)
                FROM `options`
                wHERE `key` = \'session_expire\')
            OR last_reload IS NULL'
        );
        if ($results = $sessionIdStatement->fetchAll(\PDO::FETCH_ASSOC)) {
            foreach ($results as $result) {
                $this->deleteSession($result['session_id']);
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function addSession(Session $session): void
    {
        $insertSessionStatement = $this->db->prepare(
            $this->translateDbName(
                "INSERT INTO `:db`.session (`session_id` , `user_id` , `last_reload`, `ip_address`) " .
                "VALUES (:sessionId, :userId, :lastReload, :ipAddress)"
            )
        );
        $insertSessionStatement->bindValue(':sessionId', $session->getToken(), \PDO::PARAM_STR);
        $insertSessionStatement->bindValue(':userId', $session->getContactId(), \PDO::PARAM_INT);
        $insertSessionStatement->bindValue(':lastReload', time(), \PDO::PARAM_INT);
        // @todo get addr from controller
        $insertSessionStatement->bindValue(':ipAddress', $_SERVER["REMOTE_ADDR"], \PDO::PARAM_STR);
        $insertSessionStatement->execute();
    }
}