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

namespace Core\Infrastructure\Configuration\User\Api\FindUsers;

use Core\Application\Common\UseCase\AbstractPresenter;
use Core\Application\Configuration\User\UseCase\FindUsers\FindUsersResponse;
use Core\Application\Configuration\User\UseCase\FindUsers\FindUsersPresenterInterface;
use Centreon\Domain\RequestParameters\Interfaces\RequestParametersInterface;
use Core\Infrastructure\Common\Presenter\PresenterFormatterInterface;

class FindUsersPresenter extends AbstractPresenter implements FindUsersPresenterInterface
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
     * @inheritDoc
     */
    public function present(mixed $response): void
    {
        $presenterResponse = $response->users;

        $this->presenterFormatter->present([
            'result' => $presenterResponse,
            'meta' => $this->requestParameters->toArray(),
        ]);
    }
}
