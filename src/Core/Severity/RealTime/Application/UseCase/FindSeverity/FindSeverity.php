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

namespace Core\Severity\RealTime\Application\UseCase\FindSeverity;

use Centreon\Domain\Log\LoggerTrait;
use Core\Severity\RealTime\Domain\Model\Severity;
use Core\Application\Common\UseCase\ErrorResponse;
use Core\Severity\RealTime\Application\UseCase\FindSeverity\FindSeverityPresenterInterface;
use Core\Severity\RealTime\Application\Repository\ReadSeverityRepositoryInterface;

class FindSeverity
{
    use LoggerTrait;

    /**
     * @param ReadSeverityRepositoryInterface $repository
     */
    public function __construct(private ReadSeverityRepositoryInterface $repository)
    {
    }

    /**
     * @param integer $severityTypeId
     * @param FindSeverityPresenterInterface $presenter
     */
    public function __invoke(int $severityTypeId, FindSeverityPresenterInterface $presenter): void
    {
        $this->info('Searching for severities in the realtime', ['typeId' => $severityTypeId]);
        $severities = [];
        try {
            $severities = $this->repository->findAllByTypeId($severityTypeId);
        } catch (\Throwable $ex) {
            $this->error(
                'An error occured while retrieving severities from real-time data',
                [
                    'typeId' => $severityTypeId,
                    'trace' => $ex->getTraceAsString()
                ]
            );

            $presenter->setResponseStatus(
                new ErrorResponse('An error occured while retrieving severities')
            );
            return;
        }

        $presenter->present($this->createResponse($severities));
    }

    /**
     * @param Severity[] $severities
     * @return FindSeverityResponse
     */
    private function createResponse(array $severities): FindSeverityResponse
    {
        return new FindSeverityResponse($severities);
    }
}
