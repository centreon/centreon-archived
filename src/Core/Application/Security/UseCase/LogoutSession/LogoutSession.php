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

namespace Core\Application\Security\UseCase\LogoutSession;

use Core\Application\Security\Repository\WriteSessionTokenRepositoryInterface;
use Core\Application\Security\Repository\WriteSessionRepositoryInterface;
use Core\Application\Security\Service\TokenServiceInterface;
use Centreon\Domain\Log\LoggerTrait;

class LogoutSession
{
    use LoggerTrait;

    /**
     * @param WriteSessionTokenRepositoryInterface $writeSessionTokenRepository
     * @param WriteSessionRepositoryInterface $writeSessionRepository
     * @param TokenServiceInterface $tokenService
     */
    public function __construct(
        private WriteSessionTokenRepositoryInterface $writeSessionTokenRepository,
        private WriteSessionRepositoryInterface $writeSessionRepository,
        private TokenServiceInterface $tokenService,
    ) {
    }

    /**
     * @param LogoutSessionRequest $request
     */
    public function __invoke(
        LogoutSessionRequest $request,
        LogoutPresenterInterface $presenter,
    ): void{
        $this->debug('Processing session logout...');
        $this->tokenService->deleteExpiredSecurityTokens();
        $this->writeSessionTokenRepository->deleteSession($request->token);
        $this->writeSessionRepository->invalidate();
        $presenter->setResponseStatus(new LogoutResponse());
    }
}
