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

namespace Core\Application\RealTime\UseCase\FindPerformanceMetrics;

use Core\Domain\RealTime\Model\PerformanceMetric;

class FindPerformanceMetricResponse
{
    public ?string $filename = null;
    /**
     * @var iterable<mixed>
     */
    public iterable $performanceMetrics = [];

    /**
     * @param iterable<PerformanceMetric> $performanceMetrics
     */
    public function __construct(iterable $performanceMetrics, string $fileName = null)
    {
        $this->performanceMetrics = $this->performanceMetricToArray($performanceMetrics);
        $this->filename = $fileName;
    }

    /**
     * @param iterable<PerformanceMetric> $performanceMetrics
     * @return iterable<mixed>
     */
    private function performanceMetricToArray(iterable $performanceMetrics): iterable
    {
        foreach ($performanceMetrics as $performanceMetric) {
            yield $this->createPerformanceMetricToArray($performanceMetric);
        }
    }

    /**
     * @param PerformanceMetric $performanceMetric
     * @return array<string, mixed>
     */
    private function createPerformanceMetricToArray(PerformanceMetric $performanceMetric): array
    {
        $tmp = [
            'date' => $performanceMetric->getDateValue()->format(\DateTime::ISO8601)
        ];

        foreach ($performanceMetric->getMetricValues() as $metricValue) {
            $tmp[$metricValue->getName()] = $metricValue->getValue();
        }

        return $tmp;
    }
}
