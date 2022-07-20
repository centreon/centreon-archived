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

namespace Tests\Core\Application\RealTime\UseCase\FindPerformanceMetrics;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Core\Domain\RealTime\Model\MetricValue;
use Core\Domain\RealTime\Model\PerformanceMetric;
use Core\Application\RealTime\Repository\ReadMetricRepositoryInterface;
use Core\Application\RealTime\Repository\ReadIndexDataRepositoryInterface;
use Core\Application\RealTime\Repository\ReadPerformanceDataRepositoryInterface;
use Core\Application\RealTime\UseCase\FindPerformanceMetrics\FindPerformanceMetrics;
use Core\Application\RealTime\UseCase\FindPerformanceMetrics\FindPerformanceMetricRequest;
use Core\Application\RealTime\UseCase\FindPerformanceMetrics\FindPerformanceMetricResponse;
use Core\Application\RealTime\UseCase\FindPerformanceMetrics\FindPerformanceMetricPresenterInterface;

class FindPerformanceMetricsTest extends TestCase
{
    private const HOST_ID = 1;
    private const SERVICE_ID = 2;
    private const INDEX_ID = 15;

    /**
     * @test
     * @dataProvider downloadFileNameDataProvider
     */
    public function downloadFileNameGenerationIsProperlyGenerated(
        string $hostName,
        string $serviceDescription,
        string $expectedFileName
    ): void {
        $indexDataRepository = $this->mockIndexDataRepository($hostName, $serviceDescription);
        $metricRepository = $this->createMock(ReadMetricRepositoryInterface::class);
        $performanceDataRepository = $this->createMock(ReadPerformanceDataRepositoryInterface::class);
        $presenter = $this->mockPresenterWithDownloadFileName($expectedFileName);
        $performanceMetricRequest = new FindPerformanceMetricRequest(
            self::HOST_ID,
            self::SERVICE_ID,
            new DateTimeImmutable('2022-01-01'),
            new DateTimeImmutable('2023-01-01')
        );

        $sut = new FindPerformanceMetrics($indexDataRepository, $metricRepository, $performanceDataRepository);

        $sut($performanceMetricRequest, $presenter);
    }

    /**
     * @return iterable<String[]>
     */
    public function downloadFileNameDataProvider(): iterable
    {
        yield 'host name and service description exists' => [
            'Centreon-Server', 'Ping', 'Centreon-Server_Ping.csv'
        ];

        yield 'host name does not exists' => [
            '', 'Ping', sprintf('%s.csv', self::INDEX_ID)
        ];

        yield 'service description does not exists' => [
            'Centreon-Server', '', sprintf('%s.csv', self::INDEX_ID)
        ];

        yield 'host name and service description does not exist' => [
            '', '', sprintf('%s.csv', self::INDEX_ID)
        ];
    }

    /**
     * @test
     * @dataProvider presenterResponseDataProvider
     * @param iterable<array<mixed>> $performanceData
     * @return void
     */
    public function validatePresenterResponse(
        iterable $performanceData,
        FindPerformanceMetricResponse $expectedResponse
    ): void {
        $indexDataRepository = $this->mockIndexDataRepository('', '');
        $metricRepository = $this->createMock(ReadMetricRepositoryInterface::class);
        $performanceDataRepository = $this->mockPerformanceDataRepository($performanceData);

        $presenter = $this->mockPresenterWithPresent($expectedResponse);
        $performanceMetricRequest = new FindPerformanceMetricRequest(
            self::HOST_ID,
            self::SERVICE_ID,
            new DateTimeImmutable('2022-02-01'),
            new DateTimeImmutable('2023-01-01')
        );

        $sut = new FindPerformanceMetrics($indexDataRepository, $metricRepository, $performanceDataRepository);

        $sut($performanceMetricRequest, $presenter);
    }

    /**
     * @return iterable<array<mixed>>
     */
    public function presenterResponseDataProvider(): iterable
    {
        yield 'one record' => [
            [['rta' => 0.01]],
            new FindPerformanceMetricResponse(
                [
                    new PerformanceMetric(
                        new DateTimeImmutable(),
                        [new MetricValue('rta', 0.001)]
                    )
                ]
            )
        ];

        yield 'multiple records' => [
            [['rta' => 0.01], ['pl' => 0.02]],
            new FindPerformanceMetricResponse(
                [
                    new PerformanceMetric(
                        new DateTimeImmutable(),
                        [
                            new MetricValue('rta', 0.001),
                            new MetricValue('pl', 0.002),
                        ]
                    ),
                ]
            )
        ];
    }

    private function mockIndexDataRepository(
        string $hostName,
        string $serviceDescription
    ): ReadIndexDataRepositoryInterface {
        $mock = $this->createMock(ReadIndexDataRepositoryInterface::class);

        $mock
            ->expects($this->once())
            ->method('findIndexByHostIdAndServiceId')
            ->with(
                $this->equalTo(self::HOST_ID),
                $this->equalTo(self::SERVICE_ID),
            )
            ->willReturn(self::INDEX_ID)
        ;

        $mock
            ->expects($this->once())
            ->method('findHostNameAndServiceDescriptionByIndex')
            ->willReturn([
                'host_name' => $hostName,
                'service_description' => $serviceDescription
            ]);

        return $mock;
    }

    private function mockPresenterWithDownloadFileName(
        string $expectedDownloadFileName
    ): FindPerformanceMetricPresenterInterface {
        $mock = $this->createMock(FindPerformanceMetricPresenterInterface::class);

        $mock
            ->expects($this->once())
            ->method('setDownloadFileName')
            ->with($this->equalTo($expectedDownloadFileName));

        return $mock;
    }

    private function mockPresenterWithPresent(
        FindPerformanceMetricResponse $expectedPresentValue
    ): FindPerformanceMetricPresenterInterface {
        $mock = $this->createMock(FindPerformanceMetricPresenterInterface::class);

        $mock
            ->expects($this->once())
            ->method('present')
            ->with($this->equalTo($expectedPresentValue));

        return $mock;
    }

    /**
     * @param iterable<array<mixed>> $performanceData
     */
    private function mockPerformanceDataRepository(iterable $performanceData): ReadPerformanceDataRepositoryInterface
    {
        $mock = $this->createMock(ReadPerformanceDataRepositoryInterface::class);

        $mock
            ->expects($this->once())
            ->method('findDataByMetricsAndDates')
            ->willReturn($performanceData);

        return $mock;
    }
}
