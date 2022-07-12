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

use \DateTimeInterface;
use \DateTimeImmutable;
use \InvalidArgumentException;
use Symfony\Component\HttpFoundation\Request;
use Core\Application\RealTime\Repository\ReadMetricRepositoryInterface;
use Centreon\Application\Controller\AbstractController;
use Core\Application\RealTime\UseCase\FindPerformanceMetrics\FindPerformanceMetrics;
use Core\Application\RealTime\Repository\ReadIndexDataRepositoryInterface;

class DownloadPerformanceMetricsController extends AbstractController
{
    private const START_DATE_PARAMETER_NAME = 'start_date';
    private const END_DATE_PARAMETER_NAME = 'end_date';
    private ?DateTimeImmutable $startDate;
    private ?DateTimeImmutable $endDate;
    private Request $request;

    public function __invoke(int $hostId, int $serviceId, ReadIndexDataRepositoryInterface $indexDataRepository, ReadMetricRepositoryInterface $metricRepository, FindPerformanceMetrics $useCase, Request $request)
    {
        $this->denyAccessUnlessGrantedForApiRealtime();

        $this->request = $request;
        $index = $indexDataRepository->findIndexByHostIdAndServiceId($hostId, $serviceId);
        $metrics = $metricRepository->findMetricsByIndexId($index);
        $this->findStartDate();
        $this->findEndDate();
        $fileName = $this->generateDownloadFileNameByIndex($index, $indexDataRepository);

        return $useCase($metrics, $this->startDate, $this->endDate, $fileName);
    }

    private function findStartDate(): void
    {
        $this->startDate = $this->findDateInRequest(self::START_DATE_PARAMETER_NAME);
    }

    private function findEndDate(): void
    {
        $this->endDate = $this->findDateInRequest(self::END_DATE_PARAMETER_NAME);
    }

    private function findDateInRequest(string $parameterName): DateTimeInterface
    {
        $dateParameter = $this->request->query->get($parameterName);

        if (is_null($dateParameter)) {
            throw new InvalidArgumentException('Unable to find date parameter ' . $parameterName .' into the http request');
        }

        $dateTime = new DateTimeImmutable($dateParameter);

        if (!$dateTime instanceof DateTimeImmutable) {
            $errorMessage = sprintf('Unable to parse date parameter %s.', $parameterName);
            $errorMessage.= 'Expected is a date in ISO 8601 format (YYYY-MM-DDTHH:MM:SSZ)';
            throw new InvalidArgumentException($errorMessage);
        }

        return $dateTime;
    }

    private function generateDownloadFileNameByIndex(int $index, ReadIndexDataRepositoryInterface $indexDataRepository): string
    {
        $row = $indexDataRepository->findHostNameAndServiceDescriptionByIndex($index);

        $hostName = $row['host_name'];
        $serviceDescription = $row['service_description'];

        if ($hostName !== '' && $serviceDescription !== '') {
            return sprintf('%s_%s.csv', $hostName, $serviceDescription);
        }

        return sprintf('%s.csv', $index);
    }
}