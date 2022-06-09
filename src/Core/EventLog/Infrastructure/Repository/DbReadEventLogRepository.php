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

use cebe\openapi\spec\Parameter;
use Centreon\Infrastructure\DatabaseConnection;
use Centreon\Infrastructure\RequestParameters\SqlRequestParametersTranslator;
use PDO;
use PDOStatement;
use Centreon\Infrastructure\Repository\AbstractRepositoryDRB;
use Centreon\Domain\Log\LoggerTrait;
use Core\EventLog\Application\Repository\ReadEventLogRepositoryInterface;
use Core\EventLog\Domain\EventLog;
use Centreon\Domain\RequestParameters\RequestParameters;

class DbReadEventLogRepository extends AbstractRepositoryDRB implements ReadEventLogRepositoryInterface
{
    use LoggerTrait;

    private const SELECTED_TABLE_COLS = [
        'logs.ctime',
        'logs.host_id',
        'logs.host_name',
        'logs.service_id',
        'logs.service_description',
        'logs.msg_type',
        'logs.notification_cmd',
        'logs.notification_contact',
        'logs.output',
        'logs.retry',
        'logs.status',
        'logs.type',
        'logs.instance_name'
    ];

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
            'time' => 'logs.ctime',
            'msgType' => 'logs.msg_type',
            'status' => 'logs.status',
            'type' => 'logs.type',
        ]);
        $selectStmt = sprintf(
            'SELECT SQL_CALC_FOUND_ROWS %s FROM `:dbstg`.logs',
            join(', ', self::SELECTED_TABLE_COLS)
        );
        $query = $this->translateDbName($selectStmt);

        // add where clause
        $searchRequest = $this->sqlRequestTranslator->translateSearchParameterToSql();
        if (is_null($searchRequest)) {
            throw new \InvalidArgumentException('Event logs should be filtered');
        }
        $query .= $searchRequest;

        // add order statement
        $sortRequest = $this->sqlRequestTranslator->translateSortParameterToSql();
        $query .= $sortRequest ?? ' ORDER BY logs.ctime DESC';

        $statement = $this->db->prepare($query);

        //bind values to where statement
        foreach ($this->sqlRequestTranslator->getSearchValues() as $key => $data) {
            $type = key($data);
            $value = $data[$type];
            $statement->bindValue($key, $value, $type);
        }

        $statement->execute();

        return $this->statementToArray($statement);
    }

    /**
     * @param array $accessGroups
     * @return EventLog[]
     */
    public function findByAccessGroups(array $accessGroups): array
    {
        return [];
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
