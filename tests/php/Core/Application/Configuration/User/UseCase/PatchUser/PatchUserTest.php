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

namespace Tests\Core\Application\Configuration\User\UseCase\PatchUser;

use Core\Application\Common\Session\Repository\ReadSessionRepositoryInterface;
use Core\Application\Common\Session\Repository\WriteSessionRepositoryInterface;
use Core\Application\Common\UseCase\ErrorResponse;
use Core\Application\Common\UseCase\NotFoundResponse;
use Core\Application\Configuration\User\Exception\UserException;
use Core\Application\Configuration\User\Repository\ReadUserRepositoryInterface;
use Core\Application\Configuration\User\Repository\WriteUserRepositoryInterface;
use Core\Application\Configuration\User\UseCase\PatchUser\PatchUser;
use Core\Application\Configuration\User\UseCase\PatchUser\PatchUserRequest;
use Core\Domain\Configuration\User\Model\User;
use Core\Infrastructure\Common\Presenter\JsonPresenter;
use Core\Infrastructure\Configuration\User\Api\PatchUser\PatchUserPresenter;

beforeEach(function () {
    $this->writeUserRepository = $this->createMock(WriteUserRepositoryInterface::class);
    $this->readUserRepository = $this->createMock(ReadUserRepositoryInterface::class);
    $this->readSessionRepository = $this->createMock(ReadSessionRepositoryInterface::class);
    $this->writeSessionRepository = $this->createMock(WriteSessionRepositoryInterface::class);
    $this->request = new PatchUserRequest();
    $this->request->theme = 'light';
    $this->request->userId = 1;
    $this->presenter = new PatchUserPresenter(new JsonPresenter());
});

it('tests the error message when user is not found', function () {
    $this->readUserRepository
        ->expects($this->once())
        ->method('findById')
        ->willReturn(null);
    $useCase = new PatchUser(
        $this->readUserRepository,
        $this->writeUserRepository,
        $this->readSessionRepository,
        $this->writeSessionRepository
    );
    $useCase($this->request, $this->presenter);
    expect($this->presenter->getResponseStatus())
        ->toEqual(new NotFoundResponse('User'));
});

it('tests the exception while searching for the user', function () {
    $this->readUserRepository
        ->expects($this->once())
        ->method('findById')
        ->willThrowException(new UserException());
    $useCase = new PatchUser(
        $this->readUserRepository,
        $this->writeUserRepository,
        $this->readSessionRepository,
        $this->writeSessionRepository
    );
    $useCase($this->request, $this->presenter);
    expect($this->presenter->getResponseStatus())
        ->toEqual(
            new ErrorResponse(UserException::errorWhileSearchingForUser(new \Exception())->getMessage())
        );
});

it('tests the error message when there are no available themes', function () {
    $user = new User(1, 'alias', 'name', 'email', true, 'light');
    $this->readUserRepository
        ->expects($this->once())
        ->method('findById')
        ->willReturn($user);

    $this->readUserRepository
        ->expects($this->once())
        ->method('findAvailableThemes')
        ->willReturn([]);
    $useCase = new PatchUser(
        $this->readUserRepository,
        $this->writeUserRepository,
        $this->readSessionRepository,
        $this->writeSessionRepository
    );
    $useCase($this->request, $this->presenter);
    expect($this->presenter->getResponseStatus())
        ->toEqual(new ErrorResponse('Abnormally empty list of themes'));
});

it('tests the error message when the given theme is not in the list of available themes', function () {
    $user = new User(1, 'alias', 'name', 'email', true, 'light');
    $this->readUserRepository
        ->expects($this->once())
        ->method('findById')
        ->willReturn($user);
    $this->readUserRepository
        ->expects($this->once())
        ->method('findAvailableThemes')
        ->willReturn(['blue', 'green']);
    $useCase = new PatchUser(
        $this->readUserRepository,
        $this->writeUserRepository,
        $this->readSessionRepository,
        $this->writeSessionRepository
    );
    $useCase($this->request, $this->presenter);
    expect($this->presenter->getResponseStatus())
        ->toEqual(new ErrorResponse('Requested theme not found'));
});

it('tests the exception while searching for available themes', function () {
    $user = new User(1, 'alias', 'name', 'email', true, 'light');
    $this->readUserRepository
        ->expects($this->once())
        ->method('findById')
        ->willReturn($user);
    $this->readUserRepository
        ->expects($this->once())
        ->method('findAvailableThemes')
        ->willThrowException(new UserException());
    $useCase = new PatchUser(
        $this->readUserRepository,
        $this->writeUserRepository,
        $this->readSessionRepository,
        $this->writeSessionRepository
    );
    $useCase($this->request, $this->presenter);
    expect($this->presenter->getResponseStatus())
        ->toEqual(
            new ErrorResponse(UserException::errorInReadingUserThemes(new \Exception())->getMessage())
        );
});

it('tests the exception while updating the theme of user', function () {
    $user = new User(1, 'alias', 'name', 'email', true, 'light');
    $this->readUserRepository
        ->expects($this->once())
        ->method('findById')
        ->willReturn($user);
    $this->readUserRepository
        ->expects($this->once())
        ->method('findAvailableThemes')
        ->willReturn([$this->request->theme]);
    $user->setTheme($this->request->theme);
    $this->writeUserRepository
        ->expects($this->once())
        ->method('update')
        ->with($user)
        ->willThrowException(new UserException());

    $useCase = new PatchUser(
        $this->readUserRepository,
        $this->writeUserRepository,
        $this->readSessionRepository,
        $this->writeSessionRepository
    );
    $useCase($this->request, $this->presenter);
    expect($this->presenter->getResponseStatus())
        ->toEqual(
            new ErrorResponse(
                UserException::errorWhenUpdatingUserTheme(new \Exception())->getMessage()
            )
        );
});
