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

namespace Core\Infrastructure\RealTime\Api\DownloadPerformanceMetrics;

use Symfony\Component\HttpFoundation\Request;
use Centreon\Application\Controller\AbstractController;
use Core\Application\RealTime\UseCase\FindPerformanceMetrics\FindPerformanceMetrics;
use Core\Application\RealTime\UseCase\FindPerformanceMetrics\FindPerformanceMetricRequest;
use Core\Application\RealTime\UseCase\FindPerformanceMetrics\FindPerformanceMetricPresenterInterface;
use Symfony\Component\HttpFoundation\Response;

class DownloadPerformanceMetricsController extends AbstractController
{
    private const START_DATE_PARAMETER_NAME = 'start_date';
    private const END_DATE_PARAMETER_NAME = 'end_date';
    private \DateTimeInterface $startDate;
    private \DateTimeInterface $endDate;
    private Request $request;
    private FindPerformanceMetricRequest $performanceMetricRequest;

    public function __invoke(
        int $hostId,
        int $serviceId,
        FindPerformanceMetrics $useCase,
        Request $request,
        FindPerformanceMetricPresenterInterface $presenter
    ): Response {
        $this->denyAccessUnlessGrantedForApiRealtime();

        $this->request = $request;
        $this->createPerformanceMetricRequest($hostId, $serviceId);

        $useCase($this->performanceMetricRequest, $presenter);
        return $presenter->show();
    }

    /**
     * @param int $hostId
     * @param int $serviceId
     * @return void
     * @throws \Exception
     */
    private function createPerformanceMetricRequest(int $hostId, int $serviceId): void
    {
        $this->findStartDate();
        $this->findEndDate();

        $this->performanceMetricRequest = new FindPerformanceMetricRequest(
            $hostId,
            $serviceId,
            $this->startDate,
            $this->endDate
        );
    }

    /**
     * @return void
     * @throws \Exception
     */
    private function findStartDate(): void
    {
        $this->startDate = $this->findDateInRequest(self::START_DATE_PARAMETER_NAME);
    }

    /**
     * @return void
     * @throws \Exception
     */
    private function findEndDate(): void
    {
        $this->endDate = $this->findDateInRequest(self::END_DATE_PARAMETER_NAME);
    }

    /**
     * @param string $parameterName
     * @return \DateTimeInterface
     * @throws \Exception
     */
    private function findDateInRequest(string $parameterName): \DateTimeInterface
    {
        $dateParameter = $this->request->query->get($parameterName);

        if (is_null($dateParameter)) {
            $errorMessage = 'Unable to find date parameter ' . $parameterName . ' into the http request';
            throw new \InvalidArgumentException($errorMessage);
        }

        return new \DateTimeImmutable((string) $dateParameter);
    }
}
