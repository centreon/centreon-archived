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

use Core\Application\RealTime\Repository\ReadPerformanceDataRepositoryInterface;
use Core\Application\RealTime\Repository\ReadIndexDataRepositoryInterface;
use Core\Application\RealTime\Repository\ReadMetricRepositoryInterface;
use Core\Domain\RealTime\Model\IndexData;

class FindPerformanceMetrics
{
    public function __construct(
        private ReadIndexDataRepositoryInterface $indexDataRepository,
        private ReadMetricRepositoryInterface $metricRepository,
        private ReadPerformanceDataRepositoryInterface $performanceDataRepository
    ) {
    }

    public function __invoke(
        FindPerformanceMetricRequest $request,
        FindPerformanceMetricPresenterInterface $presenter
    ): void {
        $index = $this->indexDataRepository->findIndexByHostIdAndServiceId($request->hostId, $request->serviceId);
        $metrics = $this->metricRepository->findMetricsByIndexId($index);

        $performanceMetrics = $this->performanceDataRepository->findDataByMetricsAndDates(
            $metrics,
            $request->startDate,
            $request->endDate
        );

        $fileName = $this->generateDownloadFileNameByIndex($index);
        $presenter->setDownloadFileName($fileName);
        $presenter->present(new FindPerformanceMetricResponse($performanceMetrics));
    }

    private function generateDownloadFileNameByIndex(int $index): string
    {
        $indexData = $this->indexDataRepository->findHostNameAndServiceDescriptionByIndex($index);

        if (!$indexData instanceof IndexData) {
            return (string) $index;
        }

        $hostName = $indexData->getHostName();
        $serviceDescription = $indexData->getServiceDescription();

        if ($hostName !== '' && $serviceDescription !== '') {
            return sprintf('%s_%s', $hostName, $serviceDescription);
        }

        return (string) $index;
    }
}
