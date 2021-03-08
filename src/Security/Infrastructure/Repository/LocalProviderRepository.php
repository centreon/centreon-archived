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
        $nowTimestamp = (new \DateTime())->getTimestamp();

        $deleteStatement = $this->db->prepare(
            $this->translateDbName(
                "DELETE FROM security_token WHERE expiration_date < :now"
            )
        );
        $deleteStatement->bindValue(':now', $nowTimestamp, \PDO::PARAM_INT);
        $deleteStatement->execute();
    }



    /**
     * Insert session, security and refresh tokens
     *
     * @param string $token
     * @param integer $providerConfigurationId
     * @param integer $contactId
     * @param ProviderToken $providerToken
     * @param ProviderToken|null $providerRefreshToken
     * @return void
     */
    private function insertTokens(
        string $token,
        int $providerConfigurationId,
        ProviderToken $providerToken,
        ?ProviderToken $providerRefreshToken
    ): void {

        $this->insertSecurityToken($providerToken);
        $securityTokenId = (int) $this->db->lastInsertId();

        $securityRefreshTokenId = null;
        if ($providerRefreshToken !== null) {
            $this->insertSecurityToken($providerRefreshToken);
            $securityRefreshTokenId = (int) $this->db->lastInsertId();
        }

        $this->insertSecurityAuthenticationToken(
            $token,
            $securityTokenId,
            $securityRefreshTokenId,
            $providerConfigurationId
        );
    }

        /**
     * Insert provider token into security_token table.
     *
     * @param ProviderToken $providerToken
     */
    private function insertSecurityToken(ProviderToken $providerToken): void
    {
        $insertSecurityTokenStatement = $this->db->prepare(
            $this->translateDbName(
                "INSERT INTO `:db`.security_token (`token`, `creation_date`, `expiration_date`) " .
                "VALUES (:token, :createdAt, :expireAt)"
            )
        );
        $insertSecurityTokenStatement->bindValue(':token', $providerToken->getToken(), \PDO::PARAM_STR);
        $insertSecurityTokenStatement->bindValue(
            ':createdAt',
            $providerToken->getCreationDate()->getTimestamp(),
            \PDO::PARAM_STR
        );
        $insertSecurityTokenStatement->bindValue(
            ':expireAt',
            $providerToken->getExpirationDate()->getTimestamp(),
            \PDO::PARAM_STR
        );
        $insertSecurityTokenStatement->execute();
    }

    /**
     * Insert tokens and configuration id in security_authentication_tokens table.
     *
     * @param integer $sessionId
     * @param integer $securityTokenId
     * @param integer|null $securityRefreshTokenId
     * @param integer $providerConfigurationId
     */
    private function insertSecurityAuthenticationToken(
        string $sessionId,
        int $securityTokenId,
        ?int $securityRefreshTokenId,
        int $providerConfigurationId
    ): void {
        $insertSecurityAuthenticationStatement = $this->db->prepare(
            $this->translateDbName(
                "INSERT INTO `:db`.security_authentication_tokens " .
                "(`token`, `provider_token_id`, `provider_token_refresh_id`, `provider_configuration_id`) " .
                "VALUES (:sessionTokenId, :tokenId, :refreshTokenId, :configurationId)"
            )
        );
        $insertSecurityAuthenticationStatement->bindValue(':sessionTokenId', $sessionId, \PDO::PARAM_STR);
        $insertSecurityAuthenticationStatement->bindValue(':tokenId', $securityTokenId, \PDO::PARAM_INT);
        $insertSecurityAuthenticationStatement->bindValue(':refreshTokenId', $securityRefreshTokenId, \PDO::PARAM_INT);
        $insertSecurityAuthenticationStatement->bindValue(
            ':configurationId',
            $providerConfigurationId,
            \PDO::PARAM_INT
        );
        $insertSecurityAuthenticationStatement->execute();
    }
}