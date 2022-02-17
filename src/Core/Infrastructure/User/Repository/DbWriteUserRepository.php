<?php

namespace Core\Infrastructure\User\Repository;

use Core\Domain\User\Model\User;
use Core\Domain\User\Model\UserPassword;
use Centreon\Infrastructure\DatabaseConnection;
use Centreon\Infrastructure\Repository\AbstractRepositoryDRB;
use Core\Application\User\Repository\WriteUserRepositoryInterface;

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
    public function renewPassword(User $user): void
    {
        $this->addPassword($user->getPassword());
        $this->deleteOldPasswords($user->getId());
    }

    /**
     * Add new password to the user.
     *
     * @param UserPassword $password
     * @return void
     */
    private function addPassword(UserPassword $password): void
    {
        $statement = $this->db->prepare(
            'INSERT INTO `contact_password` (`password`, `contact_id`, `creation_date`)
            VALUES (:password, :contactId, :creationDate)'
        );
        $statement->bindValue(':password', $password->getPasswordValue(), \PDO::PARAM_STR);
        $statement->bindValue(':contactId', $password->getUserId(), \PDO::PARAM_INT);
        $statement->bindValue(':creationDate', $password->getCreationDate(), \PDO::PARAM_INT);
        $statement->execute();
    }

    /**
     * Delete old passwords to store only 3 last passwords
     *
     * @param integer $userId
     * @return void
     */
    private function deleteOldPasswords(int $userId): void
    {
        $statement = $this->db->prepare(
            'SELECT creation_date
            FROM `contact_password`
            WHERE `contact_id` = :contactId
            ORDER BY `creation_date` DESC'
        );
        $statement->bindValue(':contactId', $userId, \PDO::PARAM_INT);
        $statement->execute();

        //If 3 or more passwords are saved, delete the oldest ones.
        if (($result = $statement->fetchAll()) && count($result) > 3) {
            $maxCreationDateToDelete = $result[3]['creation_date'];
            $statement = $this->db->prepare(
                'DELETE FROM `contact_password`
                WHERE contact_id = :contactId
                AND creation_date <= :creationDate'
            );
            $statement->bindValue(':contactId', $userId, \PDO::PARAM_INT);
            $statement->bindValue(':creationDate', $maxCreationDateToDelete, \PDO::PARAM_INT);
            $statement->execute();
        }
    }
}
