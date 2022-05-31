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

namespace Core\Infrastructure\RealTime\Repository\Tag;

use Core\Domain\RealTime\Model\Tag;
use Centreon\Domain\Log\LoggerTrait;
use Centreon\Infrastructure\DatabaseConnection;
use Centreon\Infrastructure\Repository\AbstractRepositoryDRB;
use Core\Application\RealTime\Repository\ReadTagRepositoryInterface;

class DbReadTagRepository extends AbstractRepositoryDRB implements ReadTagRepositoryInterface
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
    public function findAllByTypeId(int $typeId): array
    {
        $this->info('Fetching tags from database');

        $query = $this->translateDbName('SELECT id, name, `type` FROM `:dbstg`.tags WHERE type = :type');
        $statement = $this->db->prepare($query);
        $statement->bindValue(':type', $typeId, \PDO::PARAM_INT);
        $statement->execute();

        $tags = [];
        while ($record = $statement->fetch(\PDO::FETCH_ASSOC)) {
            $tags[] = DbTagFactory::createFromRecord($record);
        }

        return $tags;
    }
}
