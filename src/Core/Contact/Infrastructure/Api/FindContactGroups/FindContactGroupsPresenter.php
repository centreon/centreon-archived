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

namespace Core\Contact\Infrastructure\Api\FindContactGroups;

use Core\Application\Common\UseCase\AbstractPresenter;
use Core\Contact\Application\UseCase\FindContactGroups\{
    FindContactGroupsPresenterInterface,
    FindContactGroupsResponse
};
use Core\Infrastructure\Common\Presenter\PresenterFormatterInterface;
use Centreon\Domain\RequestParameters\Interfaces\RequestParametersInterface;

class FindContactGroupsPresenter extends AbstractPresenter implements FindContactGroupsPresenterInterface
{
    /**
     * @param RequestParametersInterface $requestParameters
     * @param PresenterFormatterInterface $presenterFormatter
     */
    public function __construct(
        private RequestParametersInterface $requestParameters,
        protected PresenterFormatterInterface $presenterFormatter,
    ) {
    }

    /**
     * @param FindContactGroupsResponse $presentedData
     * @return void
     */
    public function present(mixed $presentedData): void
    {
        parent::present([
            'result' => $presentedData->contactGroups,
            'meta' => $this->requestParameters->toArray()
        ]);
    }
}
