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

namespace Core\Infrastructure\Configuration\ContactGroup\Repository;

use Centreon\Infrastructure\DatabaseConnection;
use Centreon\Infrastructure\Repository\AbstractRepositoryDRB;
use Centreon\Infrastructure\CentreonLegacyDB\StatementCollector;
use Core\Application\Configuration\ContactGroup\Repository\ReadContactGroupRepositoryInterface;

class DbReadContactGroupRepository extends AbstractRepositoryDRB implements ReadContactGroupRepositoryInterface
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
    public function findByIds(array $contactGroupIds): array
    {
        $contactGroups = [];

        if (empty($contactGroupIds)) {
            return $contactGroups;
        }

        $collector = new StatementCollector();

        $request = $this->translateDbName(
            'SELECT
                cg_id AS `id`,
                cg_name AS `name`,
                cg_alias AS `alias`,
                cg_activate AS `activated`
            FROM `:db`.contactgroup'
        );

        foreach ($contactGroupIds as $index => $contactGroupId) {
            $key = ":contactGroupId_{$index}";

            $contactGroupIdList[] = $key;
            $collector->addValue($key, $contactGroupId, \PDO::PARAM_INT);
        }

        $request .= ' WHERE cg_id IN (' . implode(', ', $contactGroupIdList) . ')';

        $statement = $this->db->prepare($request);

        $collector->bind($statement);
        $statement->execute();

        $contacts = [];
        while (($row = $statement->fetch(\PDO::FETCH_ASSOC))) {
            $contacts[] = DbContactGroupFactory::createFromRecord($row);
        }

        return $contacts;
    }
}