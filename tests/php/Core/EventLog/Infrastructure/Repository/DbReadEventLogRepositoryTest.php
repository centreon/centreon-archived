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

use \PDOStatement;
use Centreon\Domain\RequestParameters\RequestParameters;
use Centreon\Infrastructure\RequestParameters\SqlRequestParametersTranslator;
use PHPUnit\Framework\TestCase;
use Centreon\Infrastructure\DatabaseConnection;
use Centreon\Domain\RequestParameters\Interfaces\RequestParametersInterface;

class DbReadEventLogRepositoryTest extends TestCase
{
    private DatabaseConnection $db;
    private SqlRequestParametersTranslator $sqlRequestTranslator;
    private int $currentPageNb = 1;
    private int $maxResultsPerPage = 30;
    private array $searchParams = ['logs.output' => 'LIKE :output'];
    private array $sortParams = ['ctime' => 'DESC'];
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

    protected function setUp(): void
    {
        parent::setUp();

        $this->initSqlRequestTranslator();
    }

    /**
     * @dataProvider findAllDataProvider
     */
    public function testFindAll(array $searchParams): void
    {
        $this->searchParams = $searchParams;
        $expectedQuery = $this->generateExpectedQuery($searchParams);
        $this->initDbMock($expectedQuery);
        $repository = new DbReadEventLogRepository($this->db, $this->sqlRequestTranslator);

        $eventLogs = $repository->findAll();

        $this->assertIsArray($eventLogs);
    }

    public function findAllDataProvider(): iterable
    {
        yield 'filter with date' => [
            ['logs.ctime'=> '>1654207200', 'logs.ctime' => '<= 1654293600'],
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

        return $query;
    }

    private function initSqlRequestTranslator()
    {
        $this->sqlRequestTranslator = new SqlRequestParametersTranslator($this->mockRequestParameters());
    }

    private function mockRequestParameters()
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

    private function generateExpectedQuery(array $searchParams): string
    {
        $selectStmt = sprintf(
            'SELECT SQL_CALC_FOUND_ROWS %s FROM `centreon_storage`.logs',
            join(', ', self::SELECTED_TABLE_COLS)
        );

        return $selectStmt;
    }
}
