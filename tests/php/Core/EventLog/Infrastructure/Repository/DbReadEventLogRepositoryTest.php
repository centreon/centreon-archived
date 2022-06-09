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

namespace Core\EventLog\Infrastructure\Repository;

use cebe\openapi\spec\Parameter;
use \PDO;
use \PDOStatement;
use Centreon\Domain\RequestParameters\RequestParameters;
use Centreon\Infrastructure\RequestParameters\SqlRequestParametersTranslator;
use PHPUnit\Framework\TestCase;
use Centreon\Infrastructure\DatabaseConnection;
use Centreon\Domain\RequestParameters\Interfaces\RequestParametersInterface;
use Core\EventLog\Domain\EventLog;

class DbReadEventLogRepositoryTest extends TestCase
{
    private DatabaseConnection $db;
    private SqlRequestParametersTranslator $sqlRequestTranslator;
    private int $currentPageNb = 1;
    private int $maxResultsPerPage = 30;
    private array $searchParams = [];
    private array $sortParams = ['time' => 'DESC'];

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
     * @dataProvider findAllDataProvider
     */
    public function testFindAll(array $searchParams, string $expectedWhere): void
    {
        $this->searchParams = $searchParams;
        $this->initSqlRequestTranslator();
        $expectedQuery = $this->generateExpectedQuery($expectedWhere);
        $this->initDbMock($expectedQuery);
        $repository = new DbReadEventLogRepository($this->db, $this->sqlRequestTranslator);

        $eventLogs = $repository->findAll();

        $this->assertIsArray($eventLogs);
        $this->assertCount(1, $eventLogs);
        $this->assertInstanceOf(EventLog::class, $eventLogs[0]);
    }

    public function findAllDataProvider(): iterable
    {
        yield 'filter with date' => [
            [
                '$and' =>
                    ['time' => ['$gt' => 123,]],
                    ['time' => ['$le' => 456,]],
            ],
            '(logs.ctime > :value_1 AND logs.ctime <= :value_2)',
        ];

        yield 'filter on message type' => [
            ['msgType' => ['$in' => [123, 456],]],
            'logs.msg_type IN (:value_1,:value_2)',
        ];

        yield 'filter on status' => [
            ['status' => ['$in' => [1, 2],]],
            'logs.status IN (:value_1,:value_2)',
        ];

        yield 'filter on type' => [['type' => 1], 'logs.type = :value_1',];

        yield 'filter with multiple fields' => [
            [
                '$or' => [
                    'msgType' => ['$in' => [123, 456]],
                    'time' => ['$gt' => 789,],
                ]
            ],
            '(logs.msg_type IN (:value_1,:value_2) OR logs.ctime > :value_3)',
        ];
    }

    private function initDbMock(string $expectedQuery): void
    {
        $this->db = $this->createMock(DatabaseConnection::class);

        $this->db
            ->method('prepare')
            ->with($this->equalTo($expectedQuery))
            ->willReturn($this->mockDbQuery());

        $this->db
            ->method('getStorageDbName')
            ->willReturn('centreon_storage');

        $this->db
            ->method('getCentreonDbName')
            ->willReturn('centreon');
    }

    private function mockDbQuery(): PDOStatement
    {
        $query = $this->createMock(PDOStatement::class);
        $query->method('execute')->willReturn(true);
        $query
            ->method('fetch')
            ->willReturnOnConsecutiveCalls(
                ['id' => 15,],
                false
            );

        return $query;
    }

    private function initSqlRequestTranslator(): void
    {
        $this->sqlRequestTranslator = new SqlRequestParametersTranslator($this->mockRequestParameters());
    }

    private function mockRequestParameters(): RequestParametersInterface
    {
        $requestParams = $this->createMock(RequestParametersInterface::class);
        $requestParams->method('getPage')->willReturn($this->currentPageNb);
        $requestParams->method('getLimit')->willReturn($this->maxResultsPerPage);
        $requestParams->method('getSearch')->willReturn($this->searchParams);
        $requestParams->method('getSort')->willReturn($this->sortParams);
        $requestParams
            ->method('getConcordanceStrictMode')
            ->willReturn(RequestParameters::CONCORDANCE_MODE_STRICT);
        $requestParams
            ->method('getConcordanceErrorMode')
            ->willReturn(RequestParameters::CONCORDANCE_ERRMODE_EXCEPTION);

        return $requestParams;
    }

    private function generateExpectedQuery(string $whereStatement): string
    {
        $selectStmt = sprintf(
            'SELECT SQL_CALC_FOUND_ROWS %s FROM `centreon_storage`.logs',
            join(', ', self::SELECTED_TABLE_COLS)
        );

        return $selectStmt . ' WHERE ' . $whereStatement . ' ORDER BY logs.ctime IS NULL, logs.ctime DESC';
    }
}
