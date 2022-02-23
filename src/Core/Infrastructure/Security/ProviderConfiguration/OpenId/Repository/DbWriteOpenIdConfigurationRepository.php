<?php

/*
 * Copyright 2005 - 2022 Centreon (https://www.centreon.com/)
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

namespace Core\Infrastructure\Security\ProviderConfiguration\OpenId\Repository;

use Centreon\Infrastructure\DatabaseConnection;
use Centreon\Infrastructure\Repository\AbstractRepositoryDRB;
use Core\Application\Security\ProviderConfiguration\OpenId\Repository\WriteOpenIdConfigurationRepositoryInterface
    as WriteRepositoryInterface;
use Core\Domain\Security\ProviderConfiguration\OpenId\Model\OpenIdConfiguration;

class DbWriteOpenIdConfigurationRepository extends AbstractRepositoryDRB implements WriteRepositoryInterface
{
    /**
     * @param DatabaseConnection $db
     */
    public function __construct(DatabaseConnection $db)
    {
        $this->db = $db;
    }

    /**
     * @param OpenIdConfiguration $configuration
     */
    public function updateConfiguration(OpenIdConfiguration $configuration): void
    {
        $statement = $this->db->prepare(
            $this->translateDbName(
                "UPDATE `:db`.`provider_configuration` SET
                `custom_configuration` = :customConfiguration, `is_active` = :isActive, `is_forced` = :isForced
                WHERE `name`='openid'"
            )
        );
        $statement->bindValue(
            ':customConfiguration',
            json_encode($this->buildCustomConfigurationFromOpenIdConfiguration($configuration)),
            \PDO::PARAM_STR
        );
        $statement->bindValue(':isActive', $configuration->isActive() ? '1' : '0', \PDO::PARAM_STR);
        $statement->bindValue(':isForced', $configuration->isForced() ? '1' : '0', \PDO::PARAM_STR);
        $statement->execute();
    }

    /**
     * Format OpenIdConfiguration for custom_configuration.
     *
     * @param OpenIdConfiguration $configuration
     * @return array<string, mixed>
     */
    private function buildCustomConfigurationFromOpenIdConfiguration(OpenIdConfiguration $configuration): array
    {
        return [
            'trusted_client_addresses' => $configuration->getTrustedClientAddresses(),
            'blacklist_client_addresses' => $configuration->getBlacklistClientAddresses(),
            'base_url' => $configuration->getBaseUrl(),
            'authorization_endpoint' => $configuration->getAuthorizationEndpoint(),
            'token_endpoint' => $configuration->getTokenEndpoint(),
            'introspection_token_endpoint' => $configuration->getIntrospectionTokenEndpoint(),
            'userinfo_endpoint' => $configuration->getUserInformationsEndpoint(),
            'endession_endpoint' => $configuration->getEndSessionEndpoint(),
            'connection_scopes' => $configuration->getConnectionScopes(),
            'login_claim' => $configuration->getLoginClaim(),
            'client_id' => $configuration->getClientId(),
            'client_secret' => $configuration->getClientSecret(),
            'authentication_type' => $configuration->getAuthenticationType(),
            'verify_peer' => $configuration->verifyPeer()
        ];
    }
}
