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

namespace Core\Application\Configuration\User\UseCase\PatchUser;

use Centreon\Domain\Log\LoggerTrait;
use Core\Application\Common\UseCase\ErrorResponse;
use Core\Application\Common\UseCase\NoContentResponse;
use Core\Application\Common\UseCase\NotFoundResponse;
use Core\Application\Common\UseCase\PresenterInterface;
use Core\Application\Configuration\User\Exception\UserException;
use Core\Application\Configuration\User\Repository\ReadUserRepositoryInterface;
use Core\Application\Configuration\User\Repository\WriteUserRepositoryInterface;

final class PatchUser
{
    use LoggerTrait;

    /**
     * @param ReadUserRepositoryInterface $readUserRepository
     * @param WriteUserRepositoryInterface $writeUserRepository
     */
    public function __construct(
        private ReadUserRepositoryInterface $readUserRepository,
        private WriteUserRepositoryInterface $writeUserRepository
    ) {
    }

    public function __invoke(PatchUserRequest $request, PatchUserPresenterInterface $presenter): void
    {
        $this->info('Update user');
        try {
            try {
                $this->debug('Find user', ['user_id' => $request->userId]);
                $user = $this->readUserRepository->findUserById($request->userId);
                if ($user === null) {
                    $this->userNotFound($request->userId, $presenter);

                    return;
                }
            } catch (\Throwable $ex) {
                throw UserException::errorWhileSearchingForUser($ex);
            }

            try {
                $themes = $this->readUserRepository->findAvailableThemes();
                $this->debug('User themes available', ['themes' => $themes]);
                if (empty($themes)) {
                    $this->unexpectedError('Abnormally empty list of themes', $presenter);

                    return;
                }
                if (!in_array($request->theme, $themes)) {
                    $this->themeNotFound($request->theme, $presenter);

                    return;
                }
            } catch (\Throwable $ex) {
                throw UserException::errorInReadingUserThemes($ex);
            }

            try {
                $this->debug('New theme', ['theme' => $request->theme]);
                $user->setTheme($request->theme);
                $this->writeUserRepository->updateUser($user);
            } catch (\Throwable $ex) {
                throw UserException::errorWhenUpdatingUserTheme($ex);
            }
            $presenter->setResponseStatus(new NoContentResponse());
        } catch (\Throwable $ex) {
            $this->error($ex->getTraceAsString());
            $this->unexpectedError($ex->getMessage(), $presenter);
        }
    }

    /**
     * Handle user not found.
     *
     * @param int $userId
     * @param PresenterInterface $presenter
     */
    private function userNotFound(int $userId, PresenterInterface $presenter): void
    {
        $this->error(
            'User not found',
            ['user_id' => $userId]
        );
        $presenter->setResponseStatus(new NotFoundResponse('User'));
    }

    /**
     * @param string $errorMessage
     * @param PresenterInterface $presenter
     */
    private function unexpectedError(string $errorMessage, PresenterInterface $presenter): void
    {
        $this->error($errorMessage);
        $presenter->setResponseStatus(new ErrorResponse($errorMessage));
    }

    /**
     * @param string $theme
     * @param PresenterInterface $presenter
     */
    private function themeNotFound(string $theme, PresenterInterface $presenter): void
    {
        $this->error('Requested theme not found', ['theme' => $theme]);
        $presenter->setResponseStatus(new ErrorResponse(_('Requested theme not found')));
    }
}
