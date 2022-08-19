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
use Core\Domain\RealTime\Model\PerformanceMetric;
use Core\Domain\RealTime\Model\MetricValue;
use Core\Application\RealTime\UseCase\FindPerformanceMetrics\FindPerformanceMetricResponse;

function createPerformanceMetric(
    string $date,
    float $rta,
    float $packetLoss,
    float $rtmax,
    float $rtmin
): PerformanceMetric {
    $metricValues = [];
    $metrics = ['rta' => $rta, 'pl' => $packetLoss, 'rtmax' => $rtmax, 'rtmin' => $rtmin];
    foreach ($metrics as $columnName => $columnValue) {
        $metricValues[] = new MetricValue($columnName, $columnValue);
    }

    return new PerformanceMetric(new DateTimeImmutable($date), $metricValues);
}

/**
 * @return array<string, int|string>
 */
function generateExpectedResponseData(string $date, float $rta, float $packetLoss, float $rtmax, float $rtmin): array
{
    $dateTime = new DateTimeImmutable($date);

    return [
        'time' => $dateTime->getTimestamp(),
        'humantime' => $dateTime->format('Y-m-d H:i:s'),
        'rta' => sprintf('%f', $rta),
        'pl' => sprintf('%f', $packetLoss),
        'rtmax' => sprintf('%f', $rtmax),
        'rtmin' => sprintf('%f', $rtmin),
    ];
}

it(
    'response contains properly formatted performanceMetrics',
    function (iterable $performanceMetrics, array $expectedResponseData) {
        $response = new FindPerformanceMetricResponse($performanceMetrics);

        $this->assertTrue(property_exists($response, 'performanceMetrics'));
        $this->assertInstanceOf(\Generator::class, $response->performanceMetrics);

        $actualResponseData = array(...$response->performanceMetrics);
        $this->assertSame($expectedResponseData, $actualResponseData);
    }
)->with([
    [
        [], []
    ],
    [
        [
            createPerformanceMetric('2022-01-01', 0.039, 0, 0.108, 0.0049)
        ],
        [
            generateExpectedResponseData('2022-01-01', 0.039, 0, 0.108, 0.0049)
        ]
    ],
    [
        [
            createPerformanceMetric('2022-01-01', 0.039, 0, 0.108, 0.0049),
            createPerformanceMetric('2022-01-01 11:00:05', 0.04, 0.1, 0.10, 0.006)
        ],
        [
            generateExpectedResponseData('2022-01-01', 0.039, 0, 0.108, 0.0049),
            generateExpectedResponseData('2022-01-01 11:00:05', 0.04, 0.1, 0.10, 0.006)
        ]
    ]
]);
