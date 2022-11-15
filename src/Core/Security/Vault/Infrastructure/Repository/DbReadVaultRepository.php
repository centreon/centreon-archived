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
use Core\Security\Vault\Application\Repository\ReadVaultRepositoryInterface;
use Core\Security\Vault\Domain\Model\Vault;

class DbReadVaultRepository extends AbstractRepositoryDRB implements ReadVaultRepositoryInterface
{
    use LoggerTrait;

    /**
     * @param DatabaseConnection $db
     * @param DbVaultFactory $factory
     */
    public function __construct(DatabaseConnection $db, private DbVaultFactory $factory)
    {
        $this->db = $db;
    }

    /**
     * @param int $id
     *
     * @return Vault|null
     */
    public function findById(int $id): ?Vault
    {
        $this->info('Getting vault by id');
        $record = [];
        $statement = $this->db->prepare($this->translateDbName('SELECT * FROM `:db`.`vault` WHERE `id`=:id'));
        $statement->bindValue(':id', $id, \PDO::PARAM_INT);

        if ($statement->execute() === false) {
            return null;
        }

        /**
         * @var array<string,int|string> $record
         */
        $record = $statement->fetch(\PDO::FETCH_ASSOC);

        return $this->factory->createFromRecord($record);
    }
}
