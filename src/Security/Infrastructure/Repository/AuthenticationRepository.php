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
use Security\Domain\Authentication\Model\ProviderToken;
use Centreon\Domain\Contact\Interfaces\ContactInterface;
use Centreon\Infrastructure\Repository\AbstractRepositoryDRB;
use Security\Domain\Authentication\Model\AuthenticationTokens;
use Security\Domain\Authentication\Interfaces\AuthenticationRepositoryInterface;
use Security\Domain\Authentication\Interfaces\AuthenticationTokenRepositoryInterface;

/**
 * @package Security\Repository
 */
class AuthenticationRepository extends AbstractRepositoryDRB implements
    AuthenticationRepositoryInterface,
    AuthenticationTokenRepositoryInterface
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
    public function addAuthenticationTokens(
        string $token,
        int $providerConfigurationId,
        int $contactId,
        ProviderToken $providerToken,
        ?ProviderToken $providerRefreshToken
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
        } catch (\Exception $e) {
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
     * @param ProviderToken $providerToken
     * @param ProviderToken|null $providerRefreshToken
     * @return void
     */
    private function insertProviderTokens(
        string $token,
        int $providerConfigurationId,
        int $contactId,
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
            $contactId,
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
            \PDO::PARAM_INT
        );
        $insertSecurityTokenStatement->bindValue(
            ':expireAt',
            $providerToken->getExpirationDate() !== null ? $providerToken->getExpirationDate()->getTimestamp() : null,
            \PDO::PARAM_INT
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
        $insertSecurityAuthenticationStatement->bindValue(':sessionTokenId', $sessionId, \PDO::PARAM_STR);
        $insertSecurityAuthenticationStatement->bindValue(':tokenId', $securityTokenId, \PDO::PARAM_INT);
        $insertSecurityAuthenticationStatement->bindValue(':refreshTokenId', $securityRefreshTokenId, \PDO::PARAM_INT);
        $insertSecurityAuthenticationStatement->bindValue(
            ':configurationId',
            $providerConfigurationId,
            \PDO::PARAM_INT
        );
        $insertSecurityAuthenticationStatement->bindValue(':userId', $contactId, \PDO::PARAM_INT);
        $insertSecurityAuthenticationStatement->execute();
    }

    /**
     * {@inheritDoc}
     * @throws \Assert\AssertionFailedException
     */
    public function findAuthenticationTokensByToken(string $token): ?AuthenticationTokens
    {
        $statement = $this->db->prepare($this->translateDbName("
            SELECT sat.user_id, sat.provider_configuration_id,
              provider_token.id as pt_id,
              provider_token.token AS provider_token,
              provider_token.creation_date as provider_token_creation_date,
              provider_token.expiration_date as provider_token_expiration_date,
              refresh_token.id as rt_id,
              refresh_token.token AS refresh_token,
              refresh_token.creation_date as refresh_token_creation_date,
              refresh_token.expiration_date as refresh_token_expiration_date
            FROM `:db`.security_authentication_tokens sat
            INNER JOIN `:db`.security_token provider_token ON provider_token.id = sat.provider_token_id
            LEFT JOIN `:db`.security_token refresh_token ON refresh_token.id = sat.provider_token_refresh_id
            WHERE sat.token = :token
        "));
        $statement->bindValue(':token', $token, \PDO::PARAM_STR);
        $statement->execute();

        if ($result = $statement->fetch(\PDO::FETCH_ASSOC)) {
            $expirationDate = $result['provider_token_expiration_date'] !== null
                ? (new \DateTime())->setTimestamp((int) $result['provider_token_expiration_date'])
                : null;
            $providerToken = new ProviderToken(
                (int) $result['pt_id'],
                $result['provider_token'],
                (new \Datetime())->setTimestamp((int) $result['provider_token_creation_date']),
                $expirationDate
            );

            $providerRefreshToken = null;
            if ($result['refresh_token'] !== null) {
                $expirationDate = $result['refresh_token_expiration_date'] !== null
                    ? (new \DateTime())->setTimestamp((int) $result['refresh_token_expiration_date'])
                    : null;
                $providerRefreshToken = new ProviderToken(
                    (int) $result['rt_id'],
                    $result['refresh_token'],
                    (new \Datetime())->setTimestamp((int) $result['refresh_token_creation_date']),
                    $expirationDate
                );
            }

            return new AuthenticationTokens(
                (int) $result['user_id'],
                (int) $result['provider_configuration_id'],
                $token,
                $providerToken,
                $providerRefreshToken
            );
        }

        return null;
    }


     /**
     * @inheritDoc
     */
    public function findAuthenticationTokensByContact(ContactInterface $contact): ?AuthenticationTokens
    {
        $statement = $this->db->prepare($this->translateDbName("
            SELECT sat.user_id, sat.provider_configuration_id,
              provider_token.token AS provider_token,
              provider_token.creation_date as provider_token_creation_date,
              provider_token.expiration_date as provider_token_expiration_date,
              refresh_token.token AS refresh_token,
              refresh_token.creation_date as refresh_token_creation_date,
              refresh_token.expiration_date as refresh_token_expiration_date
            FROM `:db`.security_authentication_tokens sat
            INNER JOIN `:db`.security_token provider_token ON provider_token.id = sat.provider_token_id
            LEFT JOIN `:db`.security_token refresh_token ON refresh_token.id = sat.provider_token_refresh_id
            WHERE sat.user_id = :user_id
        "));
        $statement->bindValue(':user_id', $contact->getId(), \PDO::PARAM_INT);
        $statement->execute();

        if ($result = $statement->fetch(\PDO::FETCH_ASSOC)) {
            $expirationDate = $result['provider_token_expiration_date'] !== null
                ? (new \DateTime())->setTimestamp((int) $result['provider_token_expiration_date'])
                : null;
            $providerToken = new ProviderToken(
                null,
                $result['provider_token'],
                (new \Datetime())->setTimestamp((int) $result['provider_token_creation_date']),
                $expirationDate
            );

            $providerRefreshToken = null;
            if ($result['refresh_token'] !== null) {
                $expirationDate = $result['refresh_token_expiration_date'] !== null
                    ? (new \DateTime())->setTimestamp((int) $result['refresh_token_expiration_date'])
                    : null;
                $providerRefreshToken = new ProviderToken(
                    null,
                    $result['refresh_token'],
                    (new \Datetime())->setTimestamp((int) $result['refresh_token_creation_date']),
                    $expirationDate
                );
            }

            return new AuthenticationTokens(
                (int) $result['user_id'],
                (int) $result['provider_configuration_id'],
                'fake_value',
                $providerToken,
                $providerRefreshToken
            );
        }

        return null;
    }

    /**
     * @inheritDoc
     */
    public function updateAuthenticationTokens(AuthenticationTokens $authenticationTokens): void
    {
        $providerToken = $authenticationTokens->getProviderToken();
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
