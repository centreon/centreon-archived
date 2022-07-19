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

use Centreon\Domain\Log\LoggerTrait;
use Core\Application\RealTime\Repository\ReadDataBinRepositoryInterface;
use Core\Application\RealTime\Repository\ReadIndexDataRepositoryInterface;
use Core\Application\RealTime\Repository\ReadMetricRepositoryInterface;

class FindPerformanceMetrics
{
    use LoggerTrait;

    public function __construct(
        private ReadIndexDataRepositoryInterface $indexDataRepository,
        private ReadMetricRepositoryInterface $metricRepository,
        private ReadDataBinRepositoryInterface $dataBinRepository
    ) {
    }

    public function __invoke(
        FindPerformanceMetricRequest $request,
        FindPerformanceMetricPresenterInterface $presenter
    ): void {
        $index = $this->indexDataRepository->findIndexByHostIdAndServiceId($request->hostId, $request->serviceId);
        $metrics = $this->metricRepository->findMetricsByIndexId($index);

        $fileName = $this->generateDownloadFileNameByIndex($index);

        $dataBin = $this->dataBinRepository->findDataByMetricsAndDates(
            $metrics,
            $request->startDate,
            $request->endDate
        );

        $presenter->setDownloadFileName($fileName);
        $presenter->present(new FindPerformanceMetricResponse($dataBin));
    }

    private function generateDownloadFileNameByIndex(int $index): string
    {
        $row = $this->indexDataRepository->findHostNameAndServiceDescriptionByIndex($index);

        $hostName = $row['host_name'];
        $serviceDescription = $row['service_description'];

        if ($hostName !== '' && $serviceDescription !== '') {
            return sprintf('%s_%s.csv', $hostName, $serviceDescription);
        }

        return sprintf('%s.csv', $index);
    }
}
