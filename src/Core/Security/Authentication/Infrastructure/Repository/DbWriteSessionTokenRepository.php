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

namespace Core\Security\Authentication\Infrastructure\Repository;

use Centreon\Infrastructure\Repository\AbstractRepositoryDRB;
use Centreon\Infrastructure\DatabaseConnection;
use Core\Security\Authentication\Application\Repository\WriteSessionTokenRepositoryInterface;
use Security\Domain\Authentication\Model\Session;

class DbWriteSessionTokenRepository extends AbstractRepositoryDRB implements WriteSessionTokenRepositoryInterface
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
    public function deleteSession(string $token): void
    {
        $deleteSessionStatement = $this->db->prepare(
            $this->translateDbName(
                "DELETE FROM `:db`.session WHERE `session_id` = :token"
            )
        );
        $deleteSessionStatement->bindValue(':token', $token, \PDO::PARAM_STR);
        $deleteSessionStatement->execute();

        $deleteTokenStatement = $this->db->prepare(
            $this->translateDbName(
                "DELETE FROM `:db`.security_token WHERE `token` = :token"
            )
        );
        $deleteTokenStatement->bindValue(':token', $token, \PDO::PARAM_STR);
        $deleteTokenStatement->execute();
    }

    /**
     * @inheritDoc
     */
    public function createSession(Session $session): void
    {
        $insertSessionStatement = $this->db->prepare(
            $this->translateDbName(
                "INSERT INTO `:db`.session (`session_id` , `user_id` , `last_reload`, `ip_address`) " .
                "VALUES (:sessionId, :userId, :lastReload, :ipAddress)"
            )
        );
        $insertSessionStatement->bindValue(':sessionId', $session->getToken());
        $insertSessionStatement->bindValue(':userId', $session->getContactId(), \PDO::PARAM_INT);
        $insertSessionStatement->bindValue(':lastReload', time(), \PDO::PARAM_INT);
        $insertSessionStatement->bindValue(':ipAddress', $session->getClientIp());
        $insertSessionStatement->execute();
    }
}
