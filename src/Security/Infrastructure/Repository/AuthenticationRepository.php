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
CREATE TABLE `security_token` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `token` varchar(255) NOT NULL,
  `creation_date` datetime NOT NULL,
  `expiration_date` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1

CREATE TABLE `security_authentication_tokens` (
  `session_token` varchar(255) NOT NULL,
  `token_id` int(11) NOT NULL,
  `token_refresh_id` int(11) DEFAULT NULL,
  `provider_configuration_id` int(11) NOT NULL,
  PRIMARY KEY (`session_token`),
  KEY `security_authentication_tokens_id_fk` (`token_id`),
  KEY `security_authentication_tokens_refresh_id__fk` (`token_refresh_id`),
  KEY `security_authentication_tokens_configuration_id_fk` (`provider_configuration_id`),
  CONSTRAINT `security_authentication_tokens_configuration_id_fk` FOREIGN KEY (`provider_configuration_id`)
  REFERENCES `provider_configuration` (`id`) ON DELETE CASCADE,
  CONSTRAINT `security_authentication_tokens_id_fk` FOREIGN KEY (`token_id`)
  REFERENCES `security_token` (`id`) ON DELETE CASCADE,
  CONSTRAINT `security_authentication_tokens_refresh_id__fk` FOREIGN KEY (`token_refresh_id`)
  REFERENCES `security_token` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1

CREATE TABLE `provider_configuration` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `provider_name` varchar(255) NOT NULL,
  `provider_configuration_name` varchar(255) NOT NULL,
  `configuration` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1
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
        ?ProviderToken $providerToken,
        ?ProviderToken $providerRefreshToken
    ): void {
        $statement = $this->db->prepare(
            $this->translateDbName(
                "DELETE FROM `:db`.session WHERE user_id = :userId AND ip_address = :ipAddress"
            )
        );
        $statement->bindValue(':userId', $contactId, \PDO::PARAM_INT);
        $statement->bindValue(':ipAddress', $_SERVER["REMOTE_ADDR"], \PDO::PARAM_STR);
        $statement->execute();

        $query = "INSERT INTO `:db`.session (`session_id` , `user_id` , `last_reload`, `ip_address`) " .
            "VALUES (:sessionId, :userId, :lastReload, :ipAddress)";
        $statement = $this->db->prepare($this->translateDbName($query));
        $statement->bindValue(':sessionId', $sessionToken, \PDO::PARAM_STR);
        $statement->bindValue(':userId', $contactId, \PDO::PARAM_INT);
        $statement->bindValue(':lastReload', time(), \PDO::PARAM_INT);
        $statement->bindValue(':ipAddress', $_SERVER["REMOTE_ADDR"], \PDO::PARAM_STR); // @todo get addr from controller
        $statement->execute();
        // $statement = $this->db->prepare($this->translateDbName(
        //     "INSERT INTO `:db`.security_token (`token`, `creation_date`, `expiration_date`)
        //     VALUES (:token, :creationDate, :expirationDate)"
        // ));
        // $statement->bindValue(":token", $sessionToken, \PDO::PARAM_STR);
        // TODO: Implement addAuthenticationTokens() method.
    }

    /**
     * {@inheritDoc}
     * @throws \Assert\AssertionFailedException
     */
    public function findAuthenticationTokenBySessionToken(string $sessionToken): ?AuthenticationTokens
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
            WHERE provider_name = :name
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
