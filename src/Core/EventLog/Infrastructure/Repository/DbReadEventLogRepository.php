<?php


/**
 * api/latest/export/{$type}/default?search=...&limit=...
 *
 * api/latest/export/event_logs?search={}&limit=0&page=1
 * api/latest/export/event_logs?format=csv&search={}&limit=0&page=1
 */




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

namespace Core\EventLog\Infrastructure\Repository;

use Centreon\Infrastructure\RequestParameters\SqlRequestParametersTranslator;
use \PDO;
use \PDOStatement;
use Centreon\Infrastructure\Repository\AbstractRepositoryDRB;
use Centreon\Domain\Log\LoggerTrait;
use Core\EventLog\Application\Repository\ReadEventLogRepositoryInterface;
use Core\EventLog\Domain\EventLog;

class DbReadEventLogRepository extends AbstractRepositoryDRB implements ReadEventLogRepositoryInterface
{
    use LoggerTrait;

    /**
     * @param DatabaseConnection $db
     */
    public function __construct(DatabaseConnection $db, private SqlRequestParametersTranslator $sqlRequestTranslator)
    {
        $this->db = $db;
        $this->sqlRequestTranslator->getRequestParameters()
            ->setConcordanceStrictMode(RequestParameters::CONCORDANCE_MODE_STRICT);
    }

    /**
     * @return EventLog[]
     */
    public function findAll(): array
    {
        $this->info('Fetching event logs from database');
        $this->sqlRequestTranslator->setConcordanceArray([
            'log_type' => 'logs.type',
        ]);
        $query = $this->translateDbName('SELECT id FROM `:dbstg`.logs');
        // Search
        $searchRequest = $this->sqlRequestTranslator->translateSearchParameterToSql();
        $query .= !is_null($searchRequest)
            ? $searchRequest . ' AND hc.level IS NULL'
            : '  WHERE hc.level IS NULL';

        // Sort
        $sortRequest = $this->sqlRequestTranslator->translateSortParameterToSql();
        $query .= !is_null($sortRequest)
            ? $sortRequest
            : ' ORDER BY hc.hc_name ASC';

        $statement = $this->db->prepare($query);

        $statement->bindValue(':type', 1, PDO::PARAM_INT);
        $statement->execute();

        return $this->statementToArray($statement);
    }

    /**
     * @param array $accessGroups
     * @return EventLog[]
     */
    public function findByAccessGroups(array $accessGroups): array
    {
        // TODO: Implement findByAccessGroups() method.
    }

    private function statementToArray(PDOStatement $statement): array
    {
        $items = [];
        while ($record = $statement->fetch(PDO::FETCH_ASSOC)) {
            $items[] = DbEventLogFactory::createFromRecord($record);
        }

        return $items;
    }
}