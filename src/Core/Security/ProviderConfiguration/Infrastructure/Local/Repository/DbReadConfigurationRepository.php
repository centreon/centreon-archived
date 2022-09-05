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

namespace Core\Security\ProviderConfiguration\Infrastructure\Local\Repository;

use Centreon\Domain\Log\LoggerTrait;
use Centreon\Infrastructure\DatabaseConnection;
use Centreon\Infrastructure\Repository\AbstractRepositoryDRB;
use Core\Security\ProviderConfiguration\Application\Repository\ReadProviderConfigurationsRepositoryInterface;
use Core\Security\ProviderConfiguration\Domain\Model\Configuration;

/**
 * @deprecated
 */
class DbReadConfigurationRepository extends AbstractRepositoryDRB implements
    ReadProviderConfigurationsRepositoryInterface
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
    public function findConfigurations(): array
    {
        $configurations = [];

        $localConfiguration = $this->findConfiguration();
        if ($localConfiguration !== null) {
            $configurations[] = $localConfiguration;
        }

        return $configurations;
    }

    /**
     * @inheritDoc
     */
    public function findConfiguration(): ?Configuration
    {
        $statement = $this->db->query(
            $this->translateDbName(
                "SELECT *
                FROM `:db`.`provider_configuration`
                WHERE `name` = 'local'"
            )
        );
        $customConfiguration = null;
        $configuration = [];
        if ($statement !== false && $configuration = $statement->fetch(\PDO::FETCH_ASSOC)) {
            $this->validateJsonRecord(
                $configuration['custom_configuration'],
                __DIR__ . '/CustomConfigurationSchema.json',
            );
            $customConfiguration = json_decode($configuration['custom_configuration'], true);
        }
        if ($customConfiguration !== null && !empty($configuration)) {
            $excludedUsers = $this->findExcludedUsers();
            $configuration = DbConfigurationFactory::createFromRecord(
                $configuration,
                $customConfiguration,
                $excludedUsers
            );
        }

        return $configuration;
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
