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

namespace Core\Security\User\Infrastructure\Api\RenewPassword;

use Symfony\Component\HttpFoundation\Request;
use Centreon\Application\Controller\AbstractController;
use Core\Security\User\Application\UseCase\RenewPassword\RenewPassword;
use Core\Security\User\Application\UseCase\RenewPassword\RenewPasswordRequest;
use Core\Security\User\Application\UseCase\RenewPassword\RenewPasswordPresenterInterface;

class RenewPasswordController extends AbstractController
{
    /**
     * @param RenewPassword $useCase
     * @param Request $request
     * @param RenewPasswordPresenterInterface $presenter
     * @param string $alias
     * @return object
     */
    public function __invoke(
        RenewPassword $useCase,
        Request $request,
        RenewPasswordPresenterInterface $presenter,
        string $alias
    ): object {
        $this->validateDataSent($request, __DIR__ . '/RenewPasswordSchema.json');
        $renewPasswordRequest = $this->createRenewPasswordRequest($request, $alias);
        $useCase($presenter, $renewPasswordRequest);

        return $presenter->show();
    }

    /**
     * @param Request $request
     * @param string $userAlias
     * @return RenewPasswordRequest
     */
    private function createRenewPasswordRequest(Request $request, string $userAlias): RenewPasswordRequest
    {
        $requestData = json_decode((string) $request->getContent(), true);
        $renewPasswordRequest = new RenewPasswordRequest();
        $renewPasswordRequest->userAlias = $userAlias;
        $renewPasswordRequest->oldPassword = $requestData['old_password'];
        $renewPasswordRequest->newPassword = $requestData['new_password'];

        return $renewPasswordRequest;
    }
}
