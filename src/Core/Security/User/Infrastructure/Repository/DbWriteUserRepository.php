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

namespace Core\Security\User\Infrastructure\Repository;

use Centreon\Domain\Log\LoggerTrait;
use Core\Security\User\Domain\Model\User;
use Core\Security\User\Domain\Model\UserPassword;
use Centreon\Infrastructure\DatabaseConnection;
use Centreon\Infrastructure\Repository\AbstractRepositoryDRB;
use Core\Security\User\Application\Repository\WriteUserRepositoryInterface;

class DbWriteUserRepository extends AbstractRepositoryDRB implements WriteUserRepositoryInterface
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
    public function updateBlockingInformation(User $user): void
    {
        $statement = $this->db->prepare(
            $this->translateDbName(
                'UPDATE `:db`.`contact`
                SET login_attempts = :loginAttempts, blocking_time = :blockingTime
                WHERE contact_id = :contactId'
            )
        );
        $statement->bindValue(':loginAttempts', $user->getLoginAttempts(), \PDO::PARAM_INT);
        $statement->bindValue(':blockingTime', $user->getBlockingTime()?->getTimestamp(), \PDO::PARAM_INT);
        $statement->bindValue(':contactId', $user->getId(), \PDO::PARAM_INT);
        $statement->execute();
    }

    /**
     * @inheritDoc
     */
    public function renewPassword(User $user): void
    {
        $this->addPassword($user->getPassword());
        $this->deleteOldPasswords($user->getId());
    }

    /**
     * Add new password to the user.
     *
     * @param UserPassword $password
     */
    private function addPassword(UserPassword $password): void
    {
        $this->info('add new password for user in DBMS', [
            'user_id' => $password->getUserId()
        ]);
        $statement = $this->db->prepare(
            $this->translateDbName(
                'INSERT INTO `:db`.`contact_password` (`password`, `contact_id`, `creation_date`)
                VALUES (:password, :contactId, :creationDate)'
            )
        );
        $statement->bindValue(':password', $password->getPasswordValue(), \PDO::PARAM_STR);
        $statement->bindValue(':contactId', $password->getUserId(), \PDO::PARAM_INT);
        $statement->bindValue(':creationDate', $password->getCreationDate()->getTimestamp(), \PDO::PARAM_INT);
        $statement->execute();
    }

    /**
     * Delete old passwords to store only 3 last passwords
     *
     * @param integer $userId
     */
    private function deleteOldPasswords(int $userId): void
    {
        $this->info('removing old passwords for user from DBMS', [
            'user_id' => $userId
        ]);
        $statement = $this->db->prepare(
            $this->translateDbName(
                'SELECT creation_date
                FROM `:db`.`contact_password`
                WHERE `contact_id` = :contactId
                ORDER BY `creation_date` DESC'
            )
        );
        $statement->bindValue(':contactId', $userId, \PDO::PARAM_INT);
        $statement->execute();

        //If 3 or more passwords are saved, delete the oldest ones.
        if (($result = $statement->fetchAll()) && count($result) > 3) {
            $maxCreationDateToDelete = $result[3]['creation_date'];
            $statement = $this->db->prepare(
                $this->translateDbName(
                    'DELETE FROM `:db`.`contact_password`
                    WHERE contact_id = :contactId
                    AND creation_date <= :creationDate'
                )
            );
            $statement->bindValue(':contactId', $userId, \PDO::PARAM_INT);
            $statement->bindValue(':creationDate', $maxCreationDateToDelete, \PDO::PARAM_INT);
            $statement->execute();
        }
    }
}
