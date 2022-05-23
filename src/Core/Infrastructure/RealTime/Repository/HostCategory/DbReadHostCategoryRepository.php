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

namespace Core\Infrastructure\RealTime\Repository\HostCategory;

use Centreon\Domain\Log\LoggerTrait;
use Core\Domain\RealTime\Model\Tags;
use Centreon\Infrastructure\DatabaseConnection;
use Centreon\Infrastructure\Repository\AbstractRepositoryDRB;
use Core\Application\RealTime\Repository\ReadHostCategoryRepositoryInterface;

class DbReadHostCategoryRepository extends AbstractRepositoryDRB implements ReadHostCategoryRepositoryInterface
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
    public function findAll(): \Traversable
    {
        $this->info('Fetching categories from database');

        $statement = $this->db->prepare(
            $this->translateDbName('SELECT id, name FROM `:dbstg`.tags WHERE type = :type')
        );

        $statement->bindValue(':type', Tags::HOST_CATEGORY_TYPE_ID, \PDO::PARAM_INT);

        $statement->execute();

        while ($record = $statement->fetch(\PDO::FETCH_ASSOC)) {
            yield DbHostCategoryFactory::createFromRecord($record);
        }
    }
}
