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
use Security\Domain\Authentication\Model\ProviderFactory;
use Security\Domain\Authentication\Model\ProviderToken;
use Security\Encryption;

/*
CREATE TABLE `security_token` (
  `id` int(11) NOT NULL,
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
  CONSTRAINT `security_authentication_tokens_configuration_id_fk` FOREIGN KEY (`provider_configuration_id`) REFERENCES `provider_configuration` (`id`) ON DELETE CASCADE,
  CONSTRAINT `security_authentication_tokens_id_fk` FOREIGN KEY (`token_id`) REFERENCES `security_token` (`id`) ON DELETE CASCADE,
  CONSTRAINT `security_authentication_tokens_refresh_id__fk` FOREIGN KEY (`token_refresh_id`) REFERENCES `security_token` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1

CREATE TABLE `provider_configuration` (
  `id` int(11) NOT NULL,
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
     * @var ProviderFactory
     */
    private $providerFactory;
    /**
     * @var SqlRequestParametersTranslator
     */
    private $sqlRequestTranslator;

    /**
     * @param DatabaseConnection $db
     * @param SqlRequestParametersTranslator $sqlRequestTranslator
     * @param ProviderFactory $providerFactory
     */
    public function __construct(
        DatabaseConnection $db,
        SqlRequestParametersTranslator $sqlRequestTranslator,
        ProviderFactory $providerFactory
    ) {
        $this->db = $db;
        $this->sqlRequestTranslator = $sqlRequestTranslator;
        $this->providerFactory = $providerFactory;
    }

    /**
     * @inheritDoc
     */
    public function addAuthenticationTokens(
        string $sessionToken,
        int $providerConfigurationId,
        int $contactId,
        ProviderToken $providerToken,
        ProviderToken $providerRefreshToken
    ): void {
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
        return (new ProviderConfiguration())
            ->setId(1)
            ->setProviderName('local')
            ->setConfigurationName('local_user')
            ->setConfiguration([
                'login_url' => 'centreon/login'
            ]);
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
}