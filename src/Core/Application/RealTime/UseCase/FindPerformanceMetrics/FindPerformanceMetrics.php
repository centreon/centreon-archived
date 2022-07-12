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
use Symfony\Component\HttpFoundation\StreamedResponse;

class FindPerformanceMetrics
{
    use LoggerTrait;

    public function __construct(private ReadDataBinRepositoryInterface $repository)
    {
    }

    public function __invoke(array $metrics, DateTimeInterface  $startDate, DateTimeInterface  $endDate, string $fileName): StreamedResponse
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

        return $this->show($metrics, $dataBin, $fileName);
    }

    public function show(array $metrics, iterable $dataBin, string $fileName): StreamedResponse
    {
        $response = new StreamedResponse();
        $response->setCallback(function () use($dataBin, $metrics) {
            $handle = fopen('php://output', 'r+');
            if ($handle === false) {
                throw new \RuntimeException('Unable to generate file');
            }
            $header = ['time', 'humantime', ...$metrics];
            fputcsv($handle, $header, ';');

            foreach ($dataBin as $data) {
                fputcsv($handle, $data, ';');
            }

            fclose($handle);
        });
        $response->headers->set('Content-Type', 'application/force-download');
        $response->headers->set('Content-Disposition', 'attachment; filename="' . $fileName . '"');

        return $response;
    }
}