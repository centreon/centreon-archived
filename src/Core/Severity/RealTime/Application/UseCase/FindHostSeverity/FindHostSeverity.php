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

namespace Core\Severity\RealTime\Application\UseCase\FindHostSeverity;

use Centreon\Domain\Log\LoggerTrait;
use Core\Severity\RealTime\Domain\Model\Severity;
use Core\Application\Common\UseCase\ErrorResponse;
use Core\Severity\RealTime\Application\UseCase\FindHostSeverity\FindHostSeverityPresenterInterface;
use Core\Severity\RealTime\Application\Repository\ReadSeverityRepositoryInterface;

class FindHostSeverity
{
    use LoggerTrait;

    /**
     * @param ReadSeverityRepositoryInterface $repository
     */
    public function __construct(private ReadSeverityRepositoryInterface $repository)
    {
    }

    /**
     * @param FindHostSeverityPresenterInterface $presenter
     */
    public function __invoke(FindHostSeverityPresenterInterface $presenter): void
    {
        $this->info('Searching for host severities in the realtime');
        $severities = [];
        try {
            $severities = $this->repository->findAllByTypeId(Severity::HOST_SEVERITY_TYPE_ID);
        } catch (\Throwable $ex) {
            $this->error(
                'An error occured while retrieving host severities',
                [
                    'trace' => $ex->getTraceAsString()
                ]
            );

            $presenter->setResponseStatus(
                new ErrorResponse('An error occured while retrieving severities')
            );
        }

        $presenter->present($this->createResponse($severities));
    }

    /**
     * @param Severity[] $severities
     * @return FindHostSeverityResponse
     */
    private function createResponse(array $severities): FindHostSeverityResponse
    {
        return new FindHostSeverityResponse($severities);
    }
}
