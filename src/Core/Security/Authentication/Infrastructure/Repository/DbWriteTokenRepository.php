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
use Core\Security\Authentication\Application\Repository\WriteTokenRepositoryInterface;
use Centreon\Domain\Log\LoggerTrait;
use Core\Security\Authentication\Domain\Model\AuthenticationTokens;
use Core\Security\Authentication\Domain\Model\NewProviderToken;
use Core\Security\Authentication\Domain\Model\ProviderToken;
use Exception;
use PDO;

class DbWriteTokenRepository extends AbstractRepositoryDRB implements WriteTokenRepositoryInterface
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
    public function deleteExpiredSecurityTokens(): void
    {
        $this->deleteExpiredProviderRefreshTokens();
        $this->deleteExpiredProviderTokens();
    }

    /**
     * Delete expired provider refresh tokens.
     */
    private function deleteExpiredProviderRefreshTokens(): void
    {
        $this->debug('Deleting expired refresh tokens');

        $this->db->query(
            $this->translateDbName(
                "DELETE st FROM `:db`.security_token st
                WHERE st.expiration_date < UNIX_TIMESTAMP(NOW())
                AND EXISTS (
                    SELECT 1
                    FROM `:db`.security_authentication_tokens sat
                    WHERE sat.provider_token_refresh_id = st.id
                    LIMIT 1
                )"
            )
        );
    }

    /**
     * Delete provider refresh tokens which are not linked to a refresh token.
     */
    private function deleteExpiredProviderTokens(): void
    {
        $this->debug('Deleting expired tokens which are not linked to a refresh token');

        $this->db->query(
            $this->translateDbName(
                "DELETE st FROM `:db`.security_token st
                WHERE st.expiration_date < UNIX_TIMESTAMP(NOW())
                AND NOT EXISTS (
                    SELECT 1
                    FROM `:db`.security_authentication_tokens sat
                    WHERE sat.provider_token_id = st.id
                    AND sat.provider_token_refresh_id IS NOT NULL
                    LIMIT 1
                )"
            )
        );
    }

    /**
     * @inheritDoc
     * @throws Exception
     */
    public function createAuthenticationTokens(
        string $token,
        int $providerConfigurationId,
        int $contactId,
        NewProviderToken $providerToken,
        ?NewProviderToken $providerRefreshToken
    ): void {
        // We avoid to start again a database transaction
        $isAlreadyInTransaction = $this->db->inTransaction();
        if ($isAlreadyInTransaction === false) {
            $this->db->beginTransaction();
        }
        try {
            $this->insertProviderTokens(
                $token,
                $providerConfigurationId,
                $contactId,
                $providerToken,
                $providerRefreshToken
            );
            if ($isAlreadyInTransaction === false) {
                $this->db->commit();
            }
        } catch (Exception $e) {
            if ($isAlreadyInTransaction === false) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }

    /**
     * Insert session, security and refresh tokens
     *
     * @param string $token
     * @param integer $providerConfigurationId
     * @param integer $contactId
     * @param NewProviderToken $providerToken
     * @param NewProviderToken|null $providerRefreshToken
     */
    private function insertProviderTokens(
        string $token,
        int $providerConfigurationId,
        int $contactId,
        NewProviderToken $providerToken,
        ?NewProviderToken $providerRefreshToken
    ): void {

        $this->insertSecurityToken($providerToken);
        $securityTokenId = (int)$this->db->lastInsertId();

        $securityRefreshTokenId = null;
        if ($providerRefreshToken !== null) {
            $this->insertSecurityToken($providerRefreshToken);
            $securityRefreshTokenId = (int)$this->db->lastInsertId();
        }

        $this->insertSecurityAuthenticationToken(
            $token,
            $contactId,
            $securityTokenId,
            $securityRefreshTokenId,
            $providerConfigurationId
        );
    }

    /**
     * Insert provider token into security_token table.
     *
     * @param NewProviderToken $providerToken
     */
    private function insertSecurityToken(NewProviderToken $providerToken): void
    {
        $insertSecurityTokenStatement = $this->db->prepare(
            $this->translateDbName(
                "INSERT INTO `:db`.security_token (`token`, `creation_date`, `expiration_date`) " .
                "VALUES (:token, :createdAt, :expireAt)"
            )
        );
        $insertSecurityTokenStatement->bindValue(':token', $providerToken->getToken(), PDO::PARAM_STR);
        $insertSecurityTokenStatement->bindValue(
            ':createdAt',
            $providerToken->getCreationDate()->getTimestamp(),
            PDO::PARAM_INT
        );
        $insertSecurityTokenStatement->bindValue(
            ':expireAt',
            $providerToken->getExpirationDate()?->getTimestamp(),
            PDO::PARAM_INT
        );
        $insertSecurityTokenStatement->execute();
    }

    /**
     * Insert tokens and configuration id in security_authentication_tokens table.
     *
     * @param string $sessionId
     * @param integer $contactId
     * @param integer $securityTokenId
     * @param integer|null $securityRefreshTokenId
     * @param integer $providerConfigurationId
     */
    private function insertSecurityAuthenticationToken(
        string $sessionId,
        int $contactId,
        int $securityTokenId,
        ?int $securityRefreshTokenId,
        int $providerConfigurationId
    ): void {
        $insertSecurityAuthenticationStatement = $this->db->prepare(
            $this->translateDbName(
                "INSERT INTO `:db`.security_authentication_tokens " .
                "(`token`, `provider_token_id`, `provider_token_refresh_id`, `provider_configuration_id`, `user_id`) " .
                "VALUES (:sessionTokenId, :tokenId, :refreshTokenId, :configurationId, :userId)"
            )
        );
        $insertSecurityAuthenticationStatement->bindValue(':sessionTokenId', $sessionId, PDO::PARAM_STR);
        $insertSecurityAuthenticationStatement->bindValue(':tokenId', $securityTokenId, PDO::PARAM_INT);
        $insertSecurityAuthenticationStatement->bindValue(':refreshTokenId', $securityRefreshTokenId, PDO::PARAM_INT);
        $insertSecurityAuthenticationStatement->bindValue(
            ':configurationId',
            $providerConfigurationId,
            PDO::PARAM_INT
        );
        $insertSecurityAuthenticationStatement->bindValue(':userId', $contactId, PDO::PARAM_INT);
        $insertSecurityAuthenticationStatement->execute();
    }

    /**
     * @inheritDoc
     */
    public function updateAuthenticationTokens(AuthenticationTokens $authenticationTokens): void
    {
        /** @var ProviderToken $providerToken */
        $providerToken = $authenticationTokens->getProviderToken();
        /** @var ProviderToken $providerRefreshToken */
        $providerRefreshToken = $authenticationTokens->getProviderRefreshToken();
        $updateTokenStatement = $this->db->prepare(
            $this->translateDbName(
                'UPDATE `:db`.security_token
                SET token=:token, creation_date=:creationDate, expiration_date=:expirationDate WHERE id =:tokenId'
            )
        );
        // Update Provider Token
        $updateTokenStatement->bindValue(':token', $providerToken->getToken(), \PDO::PARAM_STR);
        $updateTokenStatement->bindValue(
            ':creationDate',
            $providerToken->getCreationDate()->getTimestamp(),
            \PDO::PARAM_INT
        );
        $updateTokenStatement->bindValue(
            ':expirationDate',
            $providerToken->getExpirationDate()->getTimestamp(),
            \PDO::PARAM_INT
        );
        $updateTokenStatement->bindValue(':tokenId', $providerToken->getId(), \PDO::PARAM_INT);
        $updateTokenStatement->execute();

        //Update Refresh Token
        $updateTokenStatement->bindValue(':token', $providerRefreshToken->getToken(), \PDO::PARAM_STR);
        $updateTokenStatement->bindValue(
            ':creationDate',
            $providerRefreshToken->getCreationDate()->getTimestamp(),
            \PDO::PARAM_INT
        );
        $updateTokenStatement->bindValue(
            ':expirationDate',
            $providerRefreshToken->getExpirationDate()->getTimestamp(),
            \PDO::PARAM_INT
        );
        $updateTokenStatement->bindValue(':tokenId', $providerRefreshToken->getId(), \PDO::PARAM_INT);
        $updateTokenStatement->execute();
    }

    /**
     * @inheritDoc
     */
    public function updateProviderToken(ProviderToken $providerToken): void
    {
        $updateStatement = $this->db->prepare(
            $this->translateDbName(
                "UPDATE `:db`.security_token SET expiration_date = :expiredAt WHERE token = :token"
            )
        );
        $updateStatement->bindValue(
            ':expiredAt',
            $providerToken->getExpirationDate() !== null ? $providerToken->getExpirationDate()->getTimestamp() : null,
            \PDO::PARAM_INT
        );
        $updateStatement->bindValue(':token', $providerToken->getToken(), \PDO::PARAM_STR);
        $updateStatement->execute();
    }

    public function deleteSecurityToken(string $token): void
    {
        $deleteSecurityTokenStatement = $this->db->prepare(
            $this->translateDbName(
                "DELETE FROM `:db`.security_token WHERE token = :token"
            )
        );
        $deleteSecurityTokenStatement->bindValue(':token', $token, \PDO::PARAM_STR);
        $deleteSecurityTokenStatement->execute();
    }
}
