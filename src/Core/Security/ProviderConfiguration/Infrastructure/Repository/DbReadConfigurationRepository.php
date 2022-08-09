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
use Core\Security\ProviderConfiguration\Domain\Model\Configuration;
use Core\Security\ProviderConfiguration\Domain\Model\Provider;
use Core\Security\ProviderConfiguration\Domain\OpenId\Exceptions\OpenIdConfigurationException;
use Core\Security\ProviderConfiguration\Domain\Local\Model\CustomConfiguration as LocalCustomConfiguration;
use Core\Security\ProviderConfiguration\Domain\OpenId\Model\CustomConfiguration as OpenIdCustomConfiguration;
use Core\Security\ProviderConfiguration\Domain\WebSSO\Model\CustomConfiguration as WebSSOCustomConfiguration;
use PDO;
use Throwable;
use function json_decode;

final class DbReadConfigurationRepository extends AbstractRepositoryDRB implements ReadConfigurationRepositoryInterface
{
    use LoggerTrait;

    public function __construct(
        DatabaseConnection $db,
        private ReadOpenIdConfigurationRepositoryInterface $readOpenIdConfigurationRepository
    )
    {
        $this->db = $db;
    }

    /**
     * @param string $providerName
     * @return Configuration
     * @throws Throwable
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
     * @throws Throwable
     *
     * TODO handle not found
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
     * @throws RepositoryException|Throwable
     */
    private function loadCustomConfigurationFromConfiguration(Configuration $configuration): CustomConfigurationInterface
    {
        switch ($configuration->getName()) {
            case Provider::LOCAL:
                $jsonSchemaValidatorFile = __DIR__ . '/../Local/Repository/CustomConfigurationSchema.json';
                $this->validateJsonRecord($configuration->getJsonCustomConfiguration(), $jsonSchemaValidatorFile);
                $excludedUserAliases = array_map(
                    fn($user) => $user['contact_alias'],
                    $this->findExcludedUsers()
                );
                return LocalCustomConfiguration::createFromJsonArray(
                    json_decode($configuration->getJsonCustomConfiguration(), true),
                    $excludedUserAliases
                );
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
                    $this->readOpenIdConfigurationRepository->getAuthorizationRulesByConfigurationId($configuration->getId());

                return new OpenIdCustomConfiguration($jsonDecoded);
            case Provider::WEB_SSO:
                $jsonSchemaValidatorFile = __DIR__ . '/../WebSSO/Repository/CustomConfigurationSchema.json';
                $json = $configuration->getJsonCustomConfiguration();
                $this->validateJsonRecord($json, $jsonSchemaValidatorFile);
                return new WebSSOCustomConfiguration(json_decode($json, true));
            default:
                throw new \Exception("unknown configuration name, can't load custom config");

        }
    }

    /**
     * @param string $providerName
     * @return Configuration
     */
    private function loadConfigurationByName(string $providerName): Configuration
    {
        $query = $this->translateDbName(
            sprintf("SELECT *
                FROM `:db`.`provider_configuration`
                WHERE `name` = '%s'", $providerName)
        );

        $statement = $this->db->query($query);
        $result = $statement->fetch(PDO::FETCH_ASSOC);

        return new Configuration(
            (int)$result['id'],
            $result['name'],
            $result['type'],
            $result['custom_configuration'],
            (bool)$result['is_active'],
            (bool)$result['is_forced']);
    }

    /**
     * @param int $id
     * @return Configuration
     */
    private function loadConfigurationById(int $id): Configuration
    {
        $query = $this->translateDbName(
            sprintf("SELECT *
                FROM `:db`.`provider_configuration`
                WHERE `id` = %d", $id)
        );

        $statement = $this->db->query($query);
        $result = $statement->fetch(PDO::FETCH_ASSOC);

        return new Configuration(
            (int)$result['id'],
            $result['name'],
            $result['type'],
            $result['custom_configuration'],
            (bool)$result['is_active'],
            (bool)$result['is_forced']);
    }


    /**
     * @return array|Configuration[]
     * @throws Throwable
     */
    public function findConfigurations(): array
    {
        $configurations = [];
        $query = $this->translateDbName("SELECT name FROM `:db`.`provider_configuration` where name <> 'web-sso'");
        $statement = $this->db->query($query);
        while ($result = $statement->fetch(PDO::FETCH_ASSOC)) {
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
}
