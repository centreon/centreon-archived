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
use Centreon\Domain\Repository\AbstractRepositoryDRB;
use Security\Domain\Authentication\Model\ProviderConfiguration;
use Security\Domain\Authentication\Interfaces\ProviderRepositoryInterface;
use Security\Infrastructure\Repository\Model\ProviderConfigurationFactoryRdb;

class ProviderRepository extends AbstractRepositoryDRB implements ProviderRepositoryInterface
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
            $providerConfiguration = ProviderConfigurationFactoryRdb::create($result);
        }
        return $providerConfiguration;
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
            $providerConfiguration = ProviderConfigurationFactoryRdb::create($result);
        }
        return $providerConfiguration;
    }
}
