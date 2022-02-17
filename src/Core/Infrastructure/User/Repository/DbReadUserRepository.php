<?php

namespace Core\Infrastructure\User\Repository;

use Core\Domain\User\Model\User;
use Centreon\Infrastructure\DatabaseConnection;
use Core\Infrastructure\User\Repository\DbUserFactory;
use Centreon\Infrastructure\Repository\AbstractRepositoryDRB;
use Core\Application\User\Repository\ReadUserRepositoryInterface;

class DbReadUserRepository extends AbstractRepositoryDRB implements ReadUserRepositoryInterface
{
    /**
     * @param DatabaseConnection $db
     */
    public function __construct(DatabaseConnection $db)
    {
        $this->db = $db;
    }

    public function findUserByAlias(string $alias): ?User
    {
        $statement = $this->db->prepare(
            "SELECT c.contact_alias, c.contact_id,  cp.password, cp.creation_date FROM contact c
            INNER JOIN contact_password cp ON c.contact_id = cp.contact_id
            WHERE c.contact_alias = :alias ORDER BY cp.creation_date ASC"
        );
        $statement->bindValue(':alias', $alias, \PDO::PARAM_STR);
        $statement->execute();
        $user = null;
        if (($result = $statement->fetchAll(\PDO::FETCH_ASSOC)) !== false) {
            $user = DbUserFactory::createFromRecord($result);
        }

        return $user;
    }
}
