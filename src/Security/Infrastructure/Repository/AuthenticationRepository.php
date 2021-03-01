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
        string $sessionToken,
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
            $this->deleteExistingToken($contactId);
            $this->insertTokens(
                $sessionToken,
                $providerConfigurationId,
                $contactId, $providerToken,
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
     * Delete the user existing token.
     *
     * @param integer $contactId
     */
    private function deleteExistingToken(int $contactId): void
    {
        // Get the current session token.
        $getSessionStatement = $this->db->prepare(
            $this->translateDbName(
                "SELECT session_id FROM `:db`.session WHERE user_id = :userId AND ip_address = :ipAddress"
            )
        );
        $getSessionStatement->bindValue(':userId', $contactId, \PDO::PARAM_INT);
        $getSessionStatement->bindValue(':ipAddress', $_SERVER["REMOTE_ADDR"], \PDO::PARAM_STR);
        $getSessionStatement->execute();

        // If any token found, also delete them from security_token
        if (($result = $getSessionStatement->fetch(\PDO::FETCH_ASSOC)) !== false) {
            $session = $result['session_id'];
            $deleteSecurityTokenStatement = $this->db->prepare(
                $this->translateDbName(
                    "DELETE FROM `:db`.security_token WHERE token = :token"
                )
            );
            $deleteSecurityTokenStatement->bindValue(':token', $session, \PDO::PARAM_STR);
            $deleteSecurityTokenStatement->execute();

            //Then delete it from session
            $statement = $this->db->prepare(
                $this->translateDbName(
                    "DELETE FROM `:db`.session WHERE user_id = :userId AND ip_address = :ipAddress"
                )
            );
            $statement->bindValue(':userId', $contactId, \PDO::PARAM_INT);
            $statement->bindValue(':ipAddress', $_SERVER["REMOTE_ADDR"], \PDO::PARAM_STR);
            $statement->execute();
        }
    }

    /**
     * Insert session, security and refresh tokens
     *
     * @param string $sessionToken
     * @param integer $providerConfigurationId
     * @param integer $contactId
     * @param ProviderToken $providerToken
     * @param ProviderToken|null $providerRefreshToken
     * @return void
     */
    private function insertTokens(
        string $sessionToken,
        int $providerConfigurationId,
        int $contactId,
        ProviderToken $providerToken,
        ?ProviderToken $providerRefreshToken
    ): void {
        $this->insertSessionToken($sessionToken, $contactId);
        $sessionId = (int) $this->db->lastInsertId();

        $this->insertSecurityToken($providerToken);
        $securityTokenId = (int) $this->db->lastInsertId();

        $securityRefreshTokenId = null;
        if ($providerRefreshToken !== null) {
            $this->insertSecurityToken($providerRefreshToken);
            $securityRefreshTokenId = (int) $this->db->lastInsertId();
        }

        $this->insertSecurityAuthenticationToken(
            $sessionId,
            $securityTokenId,
            $securityRefreshTokenId,
            $providerConfigurationId
        );
    }

    /**
     * Insert token into session table.
     *
     * @param string $sessionToken
     * @param integer $contactId
     */
    private function insertSessionToken(string $sessionToken, int $contactId): void
    {
        $insertSessionStatement = $this->db->prepare(
            $this->translateDbName(
                "INSERT INTO `:db`.session (`session_id` , `user_id` , `last_reload`, `ip_address`) " .
                "VALUES (:sessionId, :userId, :lastReload, :ipAddress)"
            )
        );
        $insertSessionStatement->bindValue(':sessionId', $sessionToken, \PDO::PARAM_STR);
        $insertSessionStatement->bindValue(':userId', $contactId, \PDO::PARAM_INT);
        $insertSessionStatement->bindValue(':lastReload', time(), \PDO::PARAM_INT);
        $insertSessionStatement->bindValue(':ipAddress', $_SERVER["REMOTE_ADDR"], \PDO::PARAM_STR); // @todo get addr from controller
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
        $insertSecurityTokenStatement->bindValue(':token', $providerToken->getToken());
        $insertSecurityTokenStatement->bindValue(':createdAt', $providerToken->getCreationDate()->format('Y-m-d H:i:s'));
        $insertSecurityTokenStatement->bindValue(':expireAt', $providerToken->getExpirationDate()->format('Y-m-d H:i:s'));
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
        int $sessionId,
        int $securityTokenId,
        ?int $securityRefreshTokenId,
        int $providerConfigurationId
    ): void {
        $insertSecurityAuthenticationStatement = $this->db->prepare(
            $this->translateDbName(
                "INSERT INTO `:db`.security_authentication_tokens " .
                "(`session_token_id`, `token_id`, `token_refresh_id`, `provider_configuration_id`) " .
                "VALUES (:sessionTokenId, :tokenId, :refreshTokenId, :configurationId)"
            )
        );
        $insertSecurityAuthenticationStatement->bindValue(':sessionTokenId', $sessionId, \PDO::PARAM_INT);
        $insertSecurityAuthenticationStatement->bindValue(':tokenId', $securityTokenId, \PDO::PARAM_INT);
        $insertSecurityAuthenticationStatement->bindValue(':refreshTokenId', $securityRefreshTokenId, \PDO::PARAM_INT);
        $insertSecurityAuthenticationStatement->bindValue(':configurationId', $providerConfigurationId, \PDO::PARAM_INT);
        $insertSecurityAuthenticationStatement->execute();
    }

    /**
     * {@inheritDoc}
     * @throws \Assert\AssertionFailedException
     */
    public function findAuthenticationTokensBySessionToken(string $sessionToken): ?AuthenticationTokens
    {
        // just for testing
        if ($sessionToken === '45pmbngq6r6s0c5rq9b2ob55en') {
            $providerToken = new ProviderToken(
                null,
                $sessionToken,
                new \DateTime(),
                (new \DateTime())->add(new \DateInterval('P1D'))
            );
            $providerRefreshToken = new ProviderToken(
                null,
                Encryption::generateRandomString(32),
                new \DateTime(),
                (new \DateTime())->add(new \DateInterval('P1Y'))
            );
            return new AuthenticationTokens(1, 1, $sessionToken, $providerToken, $providerRefreshToken);
        }
        return null;
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
                ->setProviderName($result['provider_name'])
                ->setConfigurationName($result['provider_configuration_name']);

            $configuration = json_decode($result['configuration'], true);
            $providerConfiguration->setConfiguration($configuration);
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
    public function deleteSession(string $sessionToken): void
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
            WHERE provider_configuration_name = :name
            "
        ));
        $statement->bindValue(':name', $providerConfigurationName, \PDO::PARAM_STR);
        $statement->execute();
        $providerConfiguration = null;
        if ($result = $statement->fetch(\PDO::FETCH_ASSOC)) {
            $providerConfiguration = (new ProviderConfiguration())
                ->setId((int) $result['id'])
                ->setProviderName($result['provider_name'])
                ->setConfigurationName($result['provider_configuration_name']);

            $configuration = json_decode($result['configuration'], true);
            $providerConfiguration->setConfiguration($configuration);
        }
        return $providerConfiguration;
    }
}
