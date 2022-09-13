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

namespace Core\Security\ProviderConfiguration\Infrastructure\Repository;

use Centreon\Domain\Log\LoggerTrait;
use Centreon\Domain\Repository\RepositoryException;
use Centreon\Infrastructure\DatabaseConnection;
use Centreon\Infrastructure\Repository\AbstractRepositoryDRB;
use Core\Security\ProviderConfiguration\Application\Repository\ReadConfigurationRepositoryInterface;
use Core\Security\ProviderConfiguration\Application\OpenId\Repository\ReadOpenIdConfigurationRepositoryInterface;
use Core\Security\ProviderConfiguration\Domain\CustomConfigurationInterface;
use Core\Security\ProviderConfiguration\Domain\Local\Model\SecurityPolicy;
use Core\Security\ProviderConfiguration\Domain\Model\Configuration;
use Core\Security\ProviderConfiguration\Domain\Model\Provider;
use Core\Security\ProviderConfiguration\Domain\OpenId\Exceptions\OpenIdConfigurationException;
use Core\Security\ProviderConfiguration\Domain\Local\Model\CustomConfiguration as LocalCustomConfiguration;
use Core\Security\ProviderConfiguration\Domain\OpenId\Model\AuthenticationConditions;
use Core\Security\ProviderConfiguration\Domain\OpenId\Model\CustomConfiguration as OpenIdCustomConfiguration;
use Core\Security\ProviderConfiguration\Domain\WebSSO\Model\CustomConfiguration as WebSSOCustomConfiguration;

final class DbReadConfigurationRepository extends AbstractRepositoryDRB implements ReadConfigurationRepositoryInterface
{
    use LoggerTrait;

    /**
     * @param DatabaseConnection $db
     * @param ReadOpenIdConfigurationRepositoryInterface $readOpenIdConfigurationRepository
     */
    public function __construct(
        DatabaseConnection $db,
        private ReadOpenIdConfigurationRepositoryInterface $readOpenIdConfigurationRepository
    ) {
        $this->db = $db;
    }

    /**
     * @param string $providerName
     * @return Configuration
     * @throws \Throwable
     */
    public function getConfigurationByName(string $providerName): Configuration
    {
        $configuration = $this->loadConfigurationByName($providerName);
        $customConfiguration = $this->loadCustomConfigurationFromConfiguration($configuration);
        $configuration->setCustomConfiguration($customConfiguration);

        return $configuration;
    }

    /**
     * @param int $id
     * @return Configuration
     * @throws OpenIdConfigurationException
     * @throws RepositoryException
     * @throws \Throwable
     */
    public function getConfigurationById(int $id): Configuration
    {
        $configuration = $this->loadConfigurationById($id);
        $customConfiguration = $this->loadCustomConfigurationFromConfiguration($configuration);

        $configuration->setCustomConfiguration($customConfiguration);

        return $configuration;
    }

    /**
     * @param Configuration $configuration
     * @return CustomConfigurationInterface
     * @throws OpenIdConfigurationException
     * @throws RepositoryException|\Throwable
     */
    private function loadCustomConfigurationFromConfiguration(
        Configuration $configuration
    ): CustomConfigurationInterface {

        switch ($configuration->getName()) {
            case Provider::LOCAL:
                $jsonSchemaValidatorFile = __DIR__ . '/../Local/Repository/CustomConfigurationSchema.json';
                $this->validateJsonRecord($configuration->getJsonCustomConfiguration(), $jsonSchemaValidatorFile);
                $excludedUserAliases = array_map(
                    fn($user) => $user['contact_alias'],
                    $this->findExcludedUsers()
                );

                $json =  json_decode($configuration->getJsonCustomConfiguration(), true);
                $securityPolicy = new SecurityPolicy(
                    $json['password_security_policy']['password_length'],
                    $json['password_security_policy']['has_uppercase_characters'],
                    $json['password_security_policy']['has_lowercase_characters'],
                    $json['password_security_policy']['has_numbers'],
                    $json['password_security_policy']['has_special_characters'],
                    $json['password_security_policy']['can_reuse_passwords'],
                    $json['password_security_policy']['attempts'],
                    $json['password_security_policy']['blocking_duration'],
                    $json['password_security_policy']['password_expiration_delay'],
                    $excludedUserAliases,
                    $json['password_security_policy']['delay_before_new_password'],
                );

                return new LocalCustomConfiguration($securityPolicy);

            case Provider::OPENID:
                $jsonSchemaValidatorFile = __DIR__ . '/../OpenId/Repository/CustomConfigurationSchema.json';
                $json = $configuration->getJsonCustomConfiguration();
                $this->validateJsonRecord($json, $jsonSchemaValidatorFile);
                $jsonDecoded = json_decode($json, true);
                $jsonDecoded['contact_template'] = $jsonDecoded['contact_template_id'] !== null
                    ? $this->readOpenIdConfigurationRepository->getContactTemplate($jsonDecoded['contact_template_id'])
                    : null;
                $jsonDecoded['contact_group'] = $jsonDecoded['contact_group_id'] !== null
                    ? $this->readOpenIdConfigurationRepository->getContactGroup($jsonDecoded['contact_group_id'])
                    : null;
                $jsonDecoded['authorization_rules'] =
                    $this->readOpenIdConfigurationRepository->getAuthorizationRulesByConfigurationId(
                        $configuration->getId()
                    );
                $jsonDecoded['authentication_conditions'] = $this->createAuthenticationConditionsFromRecord(
                    $jsonDecoded['authentication_conditions']
                );

                return new OpenIdCustomConfiguration($jsonDecoded);
            case Provider::WEB_SSO:
                $jsonSchemaValidatorFile = __DIR__ . '/../WebSSO/Repository/CustomConfigurationSchema.json';
                $json = $configuration->getJsonCustomConfiguration();
                $this->validateJsonRecord($json, $jsonSchemaValidatorFile);
                $json = json_decode($json, true);

                return new WebSSOCustomConfiguration(
                    $json['trusted_client_addresses'],
                    $json['blacklist_client_addresses'],
                    $json['login_header_attribute'],
                    $json['pattern_matching_login'],
                    $json['pattern_replace_login']
                );
            default:
                throw new \Exception("Unknown provider configuration name, can't load custom configuration");
        }
    }

    /**
     * @param string $providerName
     * @return Configuration
     * @throws \Exception
     */
    private function loadConfigurationByName(string $providerName): Configuration
    {
        $query = $this->translateDbName(
            sprintf("SELECT *
                FROM `:db`.`provider_configuration`
                WHERE `name` = '%s'", $providerName)
        );

        $statement = $this->db->query($query);
        if ($result = $statement->fetch(\PDO::FETCH_ASSOC)) {
            return new Configuration(
                (int)$result['id'],
                $result['name'],
                $result['type'],
                $result['custom_configuration'],
                (bool)$result['is_active'],
                (bool)$result['is_forced']
            );
        }

        throw new \Exception(sprintf("Provider configuration with name %s not found", $providerName));
    }

    /**
     * @param int $id
     * @return Configuration
     * @throws \Exception
     */
    private function loadConfigurationById(int $id): Configuration
    {
        $query = $this->translateDbName(
            sprintf("SELECT *
                FROM `:db`.`provider_configuration`
                WHERE `id` = %d", $id)
        );

        $statement = $this->db->query($query);
        if ($result = $statement->fetch(\PDO::FETCH_ASSOC)) {
            return new Configuration(
                (int)$result['id'],
                $result['name'],
                $result['type'],
                $result['custom_configuration'],
                (bool)$result['is_active'],
                (bool)$result['is_forced']
            );
        }

        throw new \Exception(sprintf("provider configuration with id %d not found", $id));
    }


    /**
     * @return Configuration[]
     * @throws \Throwable
     */
    public function findConfigurations(): array
    {
        $configurations = [];
        $query = $this->translateDbName("SELECT name FROM `:db`.`provider_configuration`");
        $statement = $this->db->query($query);
        while ($result = $statement->fetch(\PDO::FETCH_ASSOC)) {
            $configurations[] = $this->getConfigurationByName($result['name']);
        }

        return $configurations;
    }

    /**
     * Find excluded users from password expiration
     *
     * @return array<string,mixed>
     */
    public function findExcludedUsers(): array
    {
        $statement = $this->db->query(
            $this->translateDbName(
                "SELECT c.`contact_alias`
                FROM `:db`.`password_expiration_excluded_users` peeu
                INNER JOIN `:db`.`provider_configuration` pc ON pc.`id` = peeu.`provider_configuration_id`
                AND pc.`name` = 'local'
                INNER JOIN `:db`.`contact` c ON c.`contact_id` = peeu.`user_id`
                AND c.`contact_register` = 1"
            )
        );

        $excludedUsers = [];
        if ($statement !== false && $rows = $statement->fetchAll(\PDO::FETCH_ASSOC)) {
            $excludedUsers = $rows;
        }

        return $excludedUsers;
    }

    /**
     * Create Authentication Conditions from record.
     *
     * @param array<string,bool|string|string[]> $authenticationConditionsRecord
     * @return AuthenticationConditions
     */
    public function createAuthenticationConditionsFromRecord(
        array $authenticationConditionsRecord
    ): AuthenticationConditions {
        $authenticationConditions = new AuthenticationConditions(
            $authenticationConditionsRecord["is_enabled"],
            $authenticationConditionsRecord["attribute_path"],
            $authenticationConditionsRecord["endpoint"],
            $authenticationConditionsRecord["authorized_values"]
        );

        if (! empty($authenticationConditionsRecord["trusted_client_addresses"])) {
            $authenticationConditions->setTrustedClientAddresses(
                $authenticationConditionsRecord["trusted_client_addresses"]
            );
        }
        if (! empty($authenticationConditionsRecord["blacklist_client_addresses"])) {
            $authenticationConditions->setBlacklistClientAddresses(
                $authenticationConditionsRecord["blacklist_client_addresses"]
            );
        }

        return $authenticationConditions;
    }
}
