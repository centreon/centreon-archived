<?php

namespace Security\Infrastructure\Repository;

use Centreon\Infrastructure\DatabaseConnection;
use Centreon\Domain\Repository\AbstractRepositoryDRB;
use Security\Domain\Authentication\Model\ProviderToken;
use Security\Domain\Authentication\Interfaces\LocalProviderRepositoryInterface;

class LocalProviderRepository extends AbstractRepositoryDRB implements LocalProviderRepositoryInterface
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
        $deleteStatement = $this->db->prepare(
            $this->translateDbName(
                "DELETE FROM `:db`.security_token WHERE expiration_date < :now"
            )
        );
        $deleteStatement->bindValue(':now', (new \DateTime())->getTimestamp(), \PDO::PARAM_INT);
        $deleteStatement->execute();
    }
}