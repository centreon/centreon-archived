<?php

namespace Security\Infrastructure\Repository;

use Centreon\Infrastructure\DatabaseConnection;
use Centreon\Domain\Repository\AbstractRepositoryDRB;

class LocalProviderRepository extends AbstractRepositoryDRB implements LocalProviderRepositoryInterface
{

    /**
     * @param DatabaseConnection $db
     */
    public function __construct(
        DatabaseConnection $db,
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

        $deleteSecurityTokenStatement = $this->db->prepare(
            $this->translateDbName(
                "DELETE FROM `:db`.security_token WHERE token = :token"
            )
        );
        $deleteSecurityTokenStatement->bindValue(':token', $token, \PDO::PARAM_STR);
        $deleteSecurityTokenStatement->execute();

    }

    /**
     * @inheritDoc
     */
    public function deleteExpiredAPITokens(): void
    {
        //@TODO: reimplement this without using ws_token
        $this->db->query(
            'DELETE FROM ws_token WHERE generate_date < DATE_SUB(NOW(), INTERVAL 1 HOUR)'
        );
    }

}