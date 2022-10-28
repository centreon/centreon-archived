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

namespace Core\TimePeriod\Infrastructure\API\FindTimePeriod;

use Core\Application\Common\UseCase\AbstractPresenter;
use Core\TimePeriod\Application\UseCase\FindTimePeriods\FindTimePeriodsPresenterInterface;
use Core\TimePeriod\Application\UseCase\FindTimePeriods\FindTimePeriodsResponse;

class FindTimePeriodsPresenter extends AbstractPresenter implements FindTimePeriodsPresenterInterface
{
    /**
     * {@inheritDoc}
     * @param FindTimePeriodsResponse $presentedData
     */
    public function present(mixed $presentedData): void
    {
        $response = [];
        foreach ($presentedData->timePeriods as $timePeriod) {
            $response[] = [
                'id' => $timePeriod['id'],
                'name' => $timePeriod['name'],
                'alias' => $timePeriod['alias']
            ];
        }
        parent::present($response);
    }
}
