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

namespace Core\Security\Authentication\Infrastructure\Api\Login\Local;

use Core\Application\Common\UseCase\AbstractPresenter;
use Core\Security\Authentication\Application\UseCase\Login\LoginPresenterInterface;

class LoginPresenter extends AbstractPresenter implements LoginPresenterInterface
{
    /**
     * @param mixed $response
     */
    public function present(mixed $response): void
    {
        $presenterResponse = [
            'redirect_uri' => $this->responseStatus->getMessage()
        ];

        parent::present($presenterResponse);
    }
}
