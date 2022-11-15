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

namespace Core\Security\Vault\Infrastructure\Repository;

use Centreon\Domain\Log\LoggerTrait;
use Centreon\Infrastructure\DatabaseConnection;
use Centreon\Infrastructure\Repository\AbstractRepositoryDRB;
use Core\Security\Vault\Application\Repository\WriteVaultConfigurationRepositoryInterface;
use Core\Security\Vault\Domain\Model\NewVaultConfiguration;

class DbWriteVaultConfigurationRepository extends AbstractRepositoryDRB
    implements WriteVaultConfigurationRepositoryInterface
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
     *
     * @param NewVaultConfiguration $vaultConfiguration
     */
    public function create(NewVaultConfiguration $vaultConfiguration): void
    {
        $this->info('Adding new vault configuration');

        $statement = $this->db->prepare(
            $this->translateDbName(
                'INSERT INTO `:db`.`vault_configuration` (`name`, `type_id`, `url`, `port`, `storage`, `role_id`, '
                    . '`secret_id`, `salt`) VALUES (:name, :type_id, :url, :port, :storage, :role_id, :secret_id, '
                    . ':salt)'
            )
        );
        $statement->bindValue(':name', $vaultConfiguration->getName(), \PDO::PARAM_STR);
        $statement->bindValue(':type_id', $vaultConfiguration->getVault()->getId(), \PDO::PARAM_INT);
        $statement->bindValue(':url', $vaultConfiguration->getAddress(), \PDO::PARAM_STR);
        $statement->bindValue(':port', $vaultConfiguration->getPort(), \PDO::PARAM_INT);
        $statement->bindValue(':storage', $vaultConfiguration->getStorage(), \PDO::PARAM_STR);
        $statement->bindValue(':role_id', $vaultConfiguration->getRoleId(), \PDO::PARAM_STR);
        $statement->bindValue(':secret_id', $vaultConfiguration->getSecretId(), \PDO::PARAM_STR);
        $statement->bindValue(':salt', $vaultConfiguration->getSalt(), \PDO::PARAM_STR);

        $statement->execute();
    }
}
