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

use Core\Domain\RealTime\Model\IndexData;
use DateTimeImmutable;
use Core\Domain\RealTime\Model\MetricValue;
use Core\Domain\RealTime\Model\PerformanceMetric;
use Core\Application\RealTime\Repository\ReadMetricRepositoryInterface;
use Core\Application\RealTime\Repository\ReadIndexDataRepositoryInterface;
use Core\Application\RealTime\Repository\ReadPerformanceDataRepositoryInterface;
use Core\Application\RealTime\UseCase\FindPerformanceMetrics\FindPerformanceMetrics;
use Core\Application\RealTime\UseCase\FindPerformanceMetrics\FindPerformanceMetricRequest;
use Core\Application\RealTime\UseCase\FindPerformanceMetrics\FindPerformanceMetricResponse;
use Core\Application\RealTime\UseCase\FindPerformanceMetrics\FindPerformanceMetricPresenterInterface;

beforeEach(function () {
    $this->hostId = 1;
    $this->serviceId = 2;
    $this->indexId = 15;
});

it(
    'download file name is properly generated',
    function (string $hostName, string $serviceDescription, string $expectedFileName) {
        $indexData = new IndexData($hostName, $serviceDescription);

        $indexDataRepository = $this->createMock(ReadIndexDataRepositoryInterface::class);
        $indexDataRepository
            ->expects($this->once())
            ->method('findIndexByHostIdAndServiceId')
            ->with(
                $this->equalTo($this->hostId),
                $this->equalTo($this->serviceId),
            )
            ->willReturn($this->indexId);

        $indexDataRepository
            ->expects($this->once())
            ->method('findHostNameAndServiceDescriptionByIndex')
            ->willReturn($indexData);

        $metricRepository = $this->createMock(ReadMetricRepositoryInterface::class);
        $performanceDataRepository = $this->createMock(ReadPerformanceDataRepositoryInterface::class);
        $presenter = $this->createMock(FindPerformanceMetricPresenterInterface::class);
        $presenter
            ->expects($this->once())
            ->method('setDownloadFileName')
            ->with($this->equalTo($expectedFileName));

        $performanceMetricRequest = new FindPerformanceMetricRequest(
            $this->hostId,
            $this->serviceId,
            new DateTimeImmutable('2022-01-01'),
            new DateTimeImmutable('2023-01-01')
        );

        $sut = new FindPerformanceMetrics($indexDataRepository, $metricRepository, $performanceDataRepository);

        $sut($performanceMetricRequest, $presenter);
    }
)->with([
    ['Centreon-Server', 'Ping', 'Centreon-Server_Ping'],
    ['',                'Ping', '15'],
    ['Centreon-Server', '',     '15'],
    ['',                '',     '15'],
]);

it(
    'validate presenter response',
    function (iterable $performanceData, FindPerformanceMetricResponse $expectedResponse) {
        $indexDataRepository = $this->createMock(ReadIndexDataRepositoryInterface::class);
        $indexDataRepository
            ->expects($this->once())
            ->method('findIndexByHostIdAndServiceId')
            ->with(
                $this->equalTo($this->hostId),
                $this->equalTo($this->serviceId),
            )
            ->willReturn($this->indexId);
        $indexDataRepository
            ->expects($this->once())
            ->method('findHostNameAndServiceDescriptionByIndex')
            ->willReturn(null);

        $metricRepository = $this->createMock(ReadMetricRepositoryInterface::class);
        $performanceDataRepository = $this->createMock(ReadPerformanceDataRepositoryInterface::class);
        $performanceDataRepository
            ->expects($this->once())
            ->method('findDataByMetricsAndDates')
            ->willReturn($performanceData);

        $presenter = $this->createMock(FindPerformanceMetricPresenterInterface::class);
        $presenter
            ->expects($this->once())
            ->method('present')
            ->with($this->equalTo($expectedResponse));


        $performanceMetricRequest = new FindPerformanceMetricRequest(
            $this->hostId,
            $this->serviceId,
            new DateTimeImmutable('2022-02-01'),
            new DateTimeImmutable('2023-01-01')
        );

        $sut = new FindPerformanceMetrics($indexDataRepository, $metricRepository, $performanceDataRepository);

        $sut($performanceMetricRequest, $presenter);
    }
)->with([
    [
        [['rta' => 0.01]],
        new FindPerformanceMetricResponse(
            [
                new PerformanceMetric(
                    new DateTimeImmutable(),
                    [new MetricValue('rta', 0.001)]
                )
            ]
        )
    ],
    [
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
    ]
]);
