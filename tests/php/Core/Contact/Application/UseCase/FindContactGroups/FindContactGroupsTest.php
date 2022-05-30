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

namespace Tests\Core\Contact\Application\UseCase\FindContactGroups;

use Core\Application\Common\UseCase\ErrorResponse;
use Centreon\Domain\Contact\Interfaces\ContactInterface;
use Core\Infrastructure\Common\Presenter\PresenterFormatterInterface;
use Core\Contact\Application\UseCase\FindContactGroups\FindContactGroups;
use Core\Contact\Application\Repository\ReadContactGroupRepositoryInterface;

beforeEach(function () {
    $this->repository = $this->createMock(ReadContactGroupRepositoryInterface::class);
    $this->presenterFormatter = $this->createMock(PresenterFormatterInterface::class);
    $this->user = $this->createMock(ContactInterface::class);
});

it('should present an ErrorResponse while an exception occured', function () {
    $useCase = new FindContactGroups($this->repository, $this->user);
    $this->user
        ->expects($this->once())
        ->method('isAdmin')
        ->willReturn(true);

    $this->repository
        ->expects($this->once())
        ->method('findAll')
        ->willThrowException(new \Exception());

    $presenter = new FindContactGroupsPresenterStub($this->presenterFormatter);
    $useCase($presenter, $this->user);

    expect($presenter->getResponseStatus())->toBeInstanceOf(ErrorResponse::class);
    expect($presenter->getResponseStatus()?->getMessage())->toBe(
        'Impossible to get contact groups from data storage'
    );
});


it('should call the method findAll if the user is admin', function () {
    $useCase = new FindContactGroups($this->repository, $this->user);
    $this->user
        ->expects($this->once())
        ->method('isAdmin')
        ->willReturn(true);

    $this->repository
        ->expects($this->once())
        ->method('findAll');

    $presenter = new FindContactGroupsPresenterStub($this->presenterFormatter);
    $useCase($presenter, $this->user);
});

it('should call the method findAllForCurrentUser if the user is not admin', function () {
    $useCase = new FindContactGroups($this->repository, $this->user);
    $this->user
        ->expects($this->once())
        ->method('getId')
        ->willReturn(1);

    $this->user
        ->expects($this->once())
        ->method('isAdmin')
        ->willReturn(false);

    $this->repository
        ->expects($this->once())
        ->method('findAllForCurrentUser')
        ->with(1);

    $presenter = new FindContactGroupsPresenterStub($this->presenterFormatter);
    $useCase($presenter, $this->user);
});
