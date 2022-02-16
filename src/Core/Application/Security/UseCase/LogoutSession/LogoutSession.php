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
use Core\Application\Security\Repository\WriteTokenRepositoryInterface;
use Core\Application\Common\UseCase\ErrorResponse;
use Core\Application\Common\UseCase\NoContentResponse;
use Centreon\Domain\Log\LoggerTrait;

class LogoutSession
{
    use LoggerTrait;

    /**
     * @param WriteSessionTokenRepositoryInterface $writeSessionTokenRepository
     * @param WriteSessionRepositoryInterface $writeSessionRepository
     * @param WriteTokenRepositoryInterface $writeTokenRepository
     */
    public function __construct(
        private WriteSessionTokenRepositoryInterface $writeSessionTokenRepository,
        private WriteSessionRepositoryInterface $writeSessionRepository,
        private WriteTokenRepositoryInterface $writeTokenRepository,
    ) {
    }

    /**
     * @param mixed $token
     * @param LogoutSessionPresenterInterface $presenter
     */
    public function __invoke(
        mixed $token,
        LogoutSessionPresenterInterface $presenter,
    ): void {
        $this->info('Processing session logout...');

        if ($token === null || is_string($token) === false) {
            $this->debug('Try to logout without token');
            $presenter->setResponseStatus(new ErrorResponse(_('No session token provided')));
            return;
        }

        $this->writeTokenRepository->deleteExpiredSecurityTokens();
        $this->writeSessionTokenRepository->deleteSession($token);
        $this->writeSessionRepository->invalidate();

        $presenter->setResponseStatus(new NoContentResponse());
    }
}
