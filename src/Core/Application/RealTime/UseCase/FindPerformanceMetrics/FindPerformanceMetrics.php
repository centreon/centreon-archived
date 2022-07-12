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

use \DateTimeInterface ;
use Centreon\Domain\Log\LoggerTrait;
use Core\Application\RealTime\Repository\ReadDataBinRepositoryInterface;

class FindPerformanceMetrics
{
    use LoggerTrait;

    public function __construct(private ReadDataBinRepositoryInterface $repository)
    {

    }

    public function __invoke(array $metrics, DateTimeInterface  $startDate, DateTimeInterface  $endDate, FindPerformanceMetricsPresenterInterface $presenter): void
    {
        $this->info(
            'Searching date_bin',
            [
                'metrics' => json_encode($metrics),
                'startDate' => $startDate->format('c'),
                'endDate' => $endDate->format('c')
            ]
        );

        $dataBin = $this->repository->findDataByMetricsAndDates($metrics, $startDate, $endDate);
        $response = $this->createResponse($dataBin);

        $presenter->present($response);
    }

    private function createResponse(iterable $dataBin): FindPerformanceMetricsResponse
    {
        $findHostResponse = new FindPerformanceMetricsResponse($dataBin);

        return $findHostResponse;
    }
}