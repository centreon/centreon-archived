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
use Core\Application\Security\ProviderConfiguration\Repository\ReadProviderConfigurationsRepositoryInterface;
use Core\Application\Security\ProviderConfiguration\OpenId\Repository\ReadOpenIdConfigurationRepositoryInterface
    as ReadRepositoryInterface;
use Core\Domain\Security\ProviderConfiguration\OpenId\Model\OpenIdConfiguration;

class DbReadOpenIdConfigurationRepository extends AbstractRepositoryDRB implements
    ReadProviderConfigurationsRepositoryInterface, ReadRepositoryInterface
{
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

        $openIdConfiguration = $this->findConfiguration();
        if ($openIdConfiguration !== null) {
            $configurations[] = $openIdConfiguration;
        }

        return $configurations;
    }

    /**
     * @inheritDoc
     */
    public function findConfiguration(): ?OpenIdConfiguration
    {
        $statement = $this->db->query(
            $this->translateDbName("SELECT * FROM `:db`.`provider_configuration` WHERE name = 'openid'")
        );
        $configuration = null;
        if ($statement !== false && $result = $statement->fetch(\PDO::FETCH_ASSOC)) {
            $this->validateJsonRecord($result['custom_configuration'], __DIR__ . '/CustomConfigurationSchema.json');
            $customConfiguration = json_decode($result['custom_configuration'], true);
            $configuration = DbOpenIdConfigurationFactory::createFromRecord($result, $customConfiguration);
        }

        return $configuration;
    }
}
