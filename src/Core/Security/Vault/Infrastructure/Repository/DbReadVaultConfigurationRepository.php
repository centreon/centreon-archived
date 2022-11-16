<?php

/*
 * Copyright 2005 - 2022 Centreon (https://www.centreon.com/)
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * https://www.apache.org/licenses/LICENSE-2.0
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
use Core\Security\Vault\Application\Repository\{
    ReadVaultConfigurationRepositoryInterface as ReadVaultConfigurationInterface
};
use Core\Security\Vault\Domain\Model\VaultConfiguration;

class DbReadVaultConfigurationRepository extends AbstractRepositoryDRB implements ReadVaultConfigurationInterface
{
    use LoggerTrait;

    /**
     * @param DatabaseConnection $db
     * @param DbVaultConfigurationFactory $factory
     */
    public function __construct(DatabaseConnection $db, private DbVaultConfigurationFactory $factory)
    {
        $this->db = $db;
    }

    /**
     * @inheritDoc
     */
    public function findByAddressAndPortAndStorage(
        string $address,
        int $port,
        string $storage
    ): ?VaultConfiguration {
        $this->info('Getting existing vault configuration by address, port and storage');

        $record = [];
        $statement = $this->db->prepare(
            $this->translateDbName(
                'SELECT * FROM `:db`.`vault_configuration` WHERE `url`=:address AND `port`=:port AND `storage`=:storage'
            )
        );
        $statement->bindValue(':address', $address, \PDO::PARAM_STR);
        $statement->bindValue(':port', $port, \PDO::PARAM_INT);
        $statement->bindValue(':storage', $storage, \PDO::PARAM_STR);
        $statement->execute();

        /**
         * @var array<string,int|string> $record
         */
        $record = $statement->fetch(\PDO::FETCH_ASSOC);

        if (! $record) {
            return null;
        }

        return $this->factory->createFromRecord($record);
    }
}
