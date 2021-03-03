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
use Centreon\Infrastructure\Repository\AbstractRepositoryDRB;
use Centreon\Infrastructure\RequestParameters\SqlRequestParametersTranslator;
use Security\Domain\Authentication\Interfaces\AuthenticationRepositoryInterface;
use Security\Domain\Authentication\Model\AuthenticationTokens;
use Security\Domain\Authentication\Model\ProviderConfiguration;
use Security\Domain\Authentication\Model\ProviderToken;
use Security\Encryption;

/*

*/

/**
 * @package Security\Repository
 */
class AuthenticationRepository extends AbstractRepositoryDRB implements AuthenticationRepositoryInterface
{
    /**
     * @var SqlRequestParametersTranslator
     */
    private $sqlRequestTranslator;

    /**
     * @param DatabaseConnection $db
     * @param SqlRequestParametersTranslator $sqlRequestTranslator
     */
    public function __construct(
        DatabaseConnection $db,
        SqlRequestParametersTranslator $sqlRequestTranslator
    ) {
        $this->db = $db;
        $this->sqlRequestTranslator = $sqlRequestTranslator;
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
        if (!$isAlreadyInTransaction) {
            $this->db->beginTransaction();
        }
        try {
            $this->insertTokens(
                $token,
                $providerConfigurationId,
                $contactId,
                $providerToken,
                $providerRefreshToken
            );
            if (!$isAlreadyInTransaction) {
                $this->db->commit();
            }
        } catch (\Exception $e) {
            if (!$isAlreadyInTransaction) {
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
    private function insertTokens(
        string $token,
        int $providerConfigurationId,
        int $contactId,
        ProviderToken $providerToken,
        ?ProviderToken $providerRefreshToken
    ): void {
        $this->insertSessionToken($token, $contactId);

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
     * Insert token into session table.
     *
     * @param string $token
     * @param integer $contactId
     */
    private function insertSessionToken(string $token, int $contactId): void
    {
        $insertSessionStatement = $this->db->prepare(
            $this->translateDbName(
                "INSERT INTO `:db`.session (`session_id` , `user_id` , `last_reload`, `ip_address`) " .
                "VALUES (:sessionId, :userId, :lastReload, :ipAddress)"
            )
        );
        $insertSessionStatement->bindValue(':sessionId', $token, \PDO::PARAM_STR);
        $insertSessionStatement->bindValue(':userId', $contactId, \PDO::PARAM_INT);
        $insertSessionStatement->bindValue(':lastReload', time(), \PDO::PARAM_INT);
        // @todo get addr from controller
        $insertSessionStatement->bindValue(':ipAddress', $_SERVER["REMOTE_ADDR"], \PDO::PARAM_STR);
        $insertSessionStatement->execute();
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

    /**
     * {@inheritDoc}
     * @throws \Assert\AssertionFailedException
     */
    public function findAuthenticationTokensByToken(string $token): ?AuthenticationTokens
    {
        $statement = $this->db->prepare($this->translateDbName("
            SELECT s.user_id, sat.provider_configuration_id,
              provider_token.token AS provider_token, refresh_token.token AS refresh_token
            FROM `:db`.security_authentication_tokens sat
            INNER JOIN `:db`.session s ON s.session_id = sat.token
              AND s.session_id = :token
            INNER JOIN `:db`.security_token provider_token ON provider_token.id = sat.provider_token_id
            LEFT JOIN `:db`.security_token refresh_token ON refresh_token.id = sat.provider_token_refresh_id
        "));
        $statement->bindValue(':token', $token, \PDO::PARAM_STR);
        $statement->execute();

        if ($result = $statement->fetch(\PDO::FETCH_ASSOC)) {
            $providerToken = new ProviderToken(
                null,
                $result['provider_token'],
                new \DateTime(),
                (new \DateTime())->add(new \DateInterval('P1D'))
            );

            $providerRefreshToken = null;
            if ($result['refresh_token'] !== null) {
                $providerRefreshToken = new ProviderToken(
                    null,
                    $result['refresh_token'],
                    new \DateTime(),
                    (new \DateTime())->add(new \DateInterval('P1Y'))
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
     * {@inheritDoc}
     * @throws \Assert\AssertionFailedException
     */
    public function findProvidersConfigurations(): array
    {
        $statement = $this->db->prepare($this->translateDbName("SELECT * FROM `:db`.provider_configuration"));
        $statement->execute();
        $providersConfigurations = [];
        while ($result = $statement->fetch(\PDO::FETCH_ASSOC)) {
            $providerConfiguration = (new ProviderConfiguration())
                ->setId((int) $result['id'])
                ->setType($result['type'])
                ->setName($result['name'])
                ->setForced((bool) $result['isForced'])
                ->setActive((bool) $result['isActive']);

            $providersConfigurations[] = $providerConfiguration;
        }
        return $providersConfigurations;
    }

    /**
     * {@inheritDoc}
     * @throws \Assert\AssertionFailedException
     */
    public function findProviderConfiguration(int $id): ?ProviderConfiguration
    {
        $statement = $this->db->prepare($this->translateDbName(
            "SELECT * FROM `:db`.provider_configuration
            WHERE id = :id
            "
        ));
        $statement->bindValue(':id', $id, \PDO::PARAM_INT);
        $statement->execute();
        $providerConfiguration = null;
        if ($result = $statement->fetch(\PDO::FETCH_ASSOC)) {
            $providerConfiguration = (new ProviderConfiguration())
                ->setId((int) $result['id'])
                ->setType($result['type'])
                ->setName($result['name'])
                ->setForced((bool) $result['isForced'])
                ->setActive((bool) $result['isActive']);
        }
        return $providerConfiguration;
    }

    /**
     * @inheritDoc
     */
    public function updateAuthenticationTokens(AuthenticationTokens $authenticationTokens): void
    {
        // TODO: Implement updateProviderToken() method.
    }

    /**
     * @inheritDoc
     */
    public function deleteSession(string $token): void
    {
        // TODO: Implement deleteSession() method.
    }

    /**
     * @inheritDoc
     */
    public function findProviderConfigurationByConfigurationName(
        string $providerConfigurationName
    ): ?ProviderConfiguration {
        $statement = $this->db->prepare($this->translateDbName(
            "SELECT * FROM `:db`.provider_configuration
            WHERE name = :name
            "
        ));
        $statement->bindValue(':name', $providerConfigurationName, \PDO::PARAM_STR);
        $statement->execute();
        $providerConfiguration = null;
        if ($result = $statement->fetch(\PDO::FETCH_ASSOC)) {
            $providerConfiguration = (new ProviderConfiguration())
                ->setId((int) $result['id'])
                ->setType($result['type'])
                ->setName($result['name'])
                ->setForced((bool) $result['isForced'])
                ->setActive((bool) $result['isActive']);
        }
        return $providerConfiguration;
    }
}
