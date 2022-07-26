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

namespace Core\Security\ProviderConfiguration\Infrastructure\WebSSO\Repository;

use Centreon\Infrastructure\DatabaseConnection;
use Centreon\Infrastructure\Repository\AbstractRepositoryDRB;
use Core\Security\ProviderConfiguration\Application\WebSSO\Repository\{
    ReadWebSSOConfigurationRepositoryInterface as ReadRepositoryInterface
};
use Core\Security\ProviderConfiguration\Domain\WebSSO\Model\WebSSOConfiguration;

class DbReadWebSSOConfigurationRepository extends AbstractRepositoryDRB implements ReadRepositoryInterface
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
    public function findConfiguration(): ?WebSSOConfiguration
    {
        $statement = $this->db->query(
            $this->translateDbName(
                "SELECT * FROM `:db`.provider_configuration WHERE name='web-sso'"
            )
        );
        $configuration = null;
        if ($statement !== false && $result = $statement->fetch(\PDO::FETCH_ASSOC)) {
            $this->validateJsonRecord($result['custom_configuration'], __DIR__ . '/CustomConfigurationSchema.json');
            $customConfiguration = json_decode($result['custom_configuration'], true);
            $configuration = DbWebSSOConfigurationFactory::createFromRecord($customConfiguration, $result);
        }

        return $configuration;
    }
}
