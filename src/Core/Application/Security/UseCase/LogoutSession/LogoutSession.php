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

namespace Core\Application\Security\UseCase\LogoutSession;

use Core\Application\Security\Repository\WriteSessionRepositoryInterface;
use Core\Application\Security\Service\TokenServiceInterface;
use Centreon\Domain\Log\LoggerTrait;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class LogoutSession
{
    use LoggerTrait;

    /**
     * @param WriteSessionRepositoryInterface $writeSessionRepository
     * @param TokenServiceInterface $tokenService
     */
    public function __construct(
        private WriteSessionRepositoryInterface $writeSessionRepository,
        private TokenServiceInterface $tokenService,
        private RequestStack $requestStack,
        private SessionInterface $session
    ) {
    }

    /**
     * @param LogoutSessionRequest $request
     */
    public function __invoke(LogoutSessionRequest $request): void
    {
        $this->debug('Processing session logout...');
        $this->tokenService->deleteExpiredSecurityTokens();
        $this->writeSessionRepository->deleteSession($request->token);
        //$this->requestStack->getCurrentRequest()->getSession()->invalidate(); // move to application service ?
        $this->session->invalidate();
    }
}
