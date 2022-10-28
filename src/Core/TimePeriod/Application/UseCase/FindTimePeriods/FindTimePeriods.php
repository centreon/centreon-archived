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

namespace Core\TimePeriod\Application\UseCase\FindTimePeriods;

use Centreon\Domain\Log\LoggerTrait;
use Core\Application\Common\UseCase\ErrorResponse;
use Core\TimePeriod\Application\Repository\ReadTimePeriodRepositoryInterface;
use Core\TimePeriod\Domain\Model\TimePeriod;

class FindTimePeriods
{
    use LoggerTrait;

    /**
     * @param ReadTimePeriodRepositoryInterface $readTimePeriodRepository
     */
    public function __construct(private ReadTimePeriodRepositoryInterface $readTimePeriodRepository)
    {
    }

    /**
     * @param FindTimePeriodsPresenterInterface $presenter
     * @return void
     */
    public function __invoke(FindTimePeriodsPresenterInterface $presenter): void
    {
        try {
            $timePeriods = $this->readTimePeriodRepository->findAll();
            $presenter->present($this->createResponse($timePeriods));
        } catch (\Throwable $ex) {
            $presenter->setResponseStatus(new ErrorResponse('Error while searching for the time periods'));
            $this->error($ex->getMessage());
        }
    }

    /**
     * @param TimePeriod[] $timePeriods
     * @return FindTimePeriodsResponse
     */
    private function createResponse(array $timePeriods): FindTimePeriodsResponse
    {
        $response = new FindTimePeriodsResponse();
        foreach ($timePeriods as $timePeriod) {
            $response->timePeriods[] = [
                'id' => $timePeriod->getId(),
                'name' => $timePeriod->getName(),
                'alias' => $timePeriod->getAlias()
            ];
        }
        return $response;
    }
}
