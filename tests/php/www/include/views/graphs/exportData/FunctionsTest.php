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

namespace Tests\www\include\views\graphs\exportData;

use PDOStatement;
use PHPUnit\Framework\TestCase;

require_once join(DIRECTORY_SEPARATOR, [dirname(__DIR__, 7), 'www', 'include', 'views', 'graphs', 'exportData']) . DIRECTORY_SEPARATOR . 'functions.php';

final class FunctionsTest extends TestCase
{
    /**
     * @test
     *
     * @dataProvider wellFormattedChartIDProvider
     */
    public function hostIdAndServiceIdExtractionWorksWhenChartIdIsWellFormatted(string $chartId, mixed $expectedHostId, mixed $expectedServiceId): void
    {
        /** @phpstan-ignore-next-line */
        $hostIdServiceId = extractHostIdAndServiceIdFromChartId($chartId);

        $this->assertSame($expectedHostId, $hostIdServiceId['hostId']);
        $this->assertSame($expectedServiceId, $hostIdServiceId['serviceId']);
    }

    /**
     * @return iterable<array<string, int>>
     */
    public function wellFormattedChartIDProvider(): iterable
    {
        yield ['1_2',       1, 2];
        yield ['25_20',     25, 20];
    }

    /**
     * @test
     *
     * @dataProvider badlyFormattedChartIDProvider
     */
    public function hostIdAndServiceIdExtractionFailsWhenChartIdIsBadlyFormatted(string $chartId, string $expectedExceptionMessage): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage($expectedExceptionMessage);

        /** @phpstan-ignore-next-line */
        extractHostIdAndServiceIdFromChartId($chartId);
    }

    /**
     * @return iterable<array<string>>
     */
    public function badlyFormattedChartIDProvider(): iterable
    {
        yield ['',          'Chart ID is missing'];
        yield ['1-2',       'Unable to parse chart ID'];
        yield ['12',        'Unable to parse chart ID'];
        yield ['a_b',       'Unable to parse chart ID'];
        yield ['-1_8',      'Unable to parse chart ID'];
        yield ['25_20_70',  'Unable to parse chart ID'];
        yield ['-1_8',      'Unable to parse chart ID'];
        yield ['1_-8',      'Unable to parse chart ID'];
    }

    /**
     * @test
     *
     * @dataProvider wellFormattedDataProvider
     *
     * @param array<int, string>        $metrics
     * @param iterable<array<string>>   $dataBinRawData
     * @param array<array<string>>      $expectedReturn
     */
    public function metricsDataAreWellFormatted(
        array $metrics,
        int $startDate,
        int $endDate,
        array $dataBinRawData,
        array $expectedReturn
    ): void {
        $stmt = $this->mockDataBinSelectStatement($startDate, $endDate, $dataBinRawData);
        $expectedSQL = $this->generateDataBinSqlQuery($metrics);
        $pearDBO = $this->mockPearDBO($expectedSQL, $stmt);

        /** @phpstan-ignore-next-line */
        $metricData = getDataByMetrics($metrics, $startDate, $endDate, $pearDBO);

        $formattedData = [];
        foreach ($metricData as $item) {
            $formattedData[] = $item;
        }
        $this->assertSame($expectedReturn, $formattedData);
    }

    /**
     * @return iterable<array<int,mixed>>
     */
    public function wellFormattedDataProvider(): iterable
    {
        yield 'without metric columns' => [
            [],
            1656658351,
            1656851352,
            [['time' => 1656658351]],
            [['time' => 1656658351, 'humantime' => date('Y-m-d H:i:s', 1656658351)]],
        ];

        yield 'with metric columns' => [
            [1 => 'rta'],
            10,
            20,
            [['time' => 15, 'rta' => 0.019000587]],
            [['time' => 15, 'humantime' => date('Y-m-d H:i:s', 15), 'rta' => sprintf('%f', 0.019000587)]],
        ];
    }

    /**
     * @param array<int, string> $metrics
     */
    private function generateDataBinSqlQuery(array $metrics): string
    {
        $columns = ['ctime AS time'];
        foreach ($metrics as $metricId => $metricName) {
            $columns[] = sprintf('AVG(CASE WHEN id_metric = %d THEN `value` end) AS %s', $metricId, $metricName);
        }

        return sprintf(
            'SELECT %s FROM data_bin WHERE ctime >= :start AND ctime < :end GROUP BY time',
            join(',', $columns)
        );
    }

    /**
     * @return \CentreonDB|\PHPUnit\Framework\MockObject\MockObject
     */
    private function mockPearDBO(string $expectedSQL, PDOStatement $stmt)
    {
        $mock = $this->createMock(\CentreonDB::class);

        $mock
            ->expects($this->once())
            ->method('prepare')
            ->with($expectedSQL)
            ->willReturn($stmt);

        return $mock;
    }

    /**
     * @param iterable<array<string>> $rawData
     */
    private function mockDataBinSelectStatement(int $startTime, int $endTime, iterable $rawData): PDOStatement
    {
        $stmt = $this->createMock(PDOStatement::class);

        $stmt
            ->method('bindValue')
            ->willReturnMap([
                [':start', $startTime, \PDO::PARAM_INT, true],
                [':end', $endTime, \PDO::PARAM_INT, true],
            ]);

        $stmt
            ->expects($this->once())
            ->method('execute')
            ->with(null)
            ->willReturn(true);

        $stmt
            ->expects($this->once())
            ->method('fetchAll')
            ->with(\PDO::FETCH_ASSOC)
            ->willReturn($rawData);

        return $stmt;
    }
}
