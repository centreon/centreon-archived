<?php

/*
 * Copyright 2005 - 2021 Centreon (https://www.centreon.com/)
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

namespace Core\Application\Security\UseCase\RenewPassword;

use Core\Application\Common\UseCase\ErrorResponse;
use Core\Application\Common\UseCase\NotFoundResponse;
use Core\Application\Common\UseCase\NoContentResponse;
use Core\Application\User\Repository\ReadUserRepositoryInterface;
use Core\Application\User\Repository\WriteUserRepositoryInterface;

class RenewPassword
{
    /**
     * @param ReadUserRepositoryInterface $readRepository
     * @param WriteUserRepositoryInterface $writeRepository
     */
    public function __construct(
        private ReadUserRepositoryInterface $readRepository,
        private WriteUserRepositoryInterface $writeRepository
    ) {
    }

    /**
     * @param RenewPasswordPresenterInterface $presenter
     * @param RenewPasswordRequest $renewPasswordRequest
     */
    public function __invoke(
        RenewPasswordPresenterInterface $presenter,
        RenewPasswordRequest $renewPasswordRequest
    ): void {
        $user = $this->readRepository->findUserByAlias($renewPasswordRequest->userAlias);
        if ($user === null) {
            $presenter->setResponseStatus(new NotFoundResponse('User'));
            return;
        }
        if (password_verify($renewPasswordRequest->oldPassword, $user->getPassword()) === false) {
            $presenter->setResponseStatus(new ErrorResponse('Invalid credentials'));
            return;
        }

        $newPassword = password_hash($renewPasswordRequest->newPassword, \CentreonAuth::PASSWORD_HASH_ALGORITHM);
        $user->setPassword($newPassword);
        $this->writeRepository->renewPassword($user);
        $presenter->setResponseStatus(new NoContentResponse());
    }
}
