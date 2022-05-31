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

use Centreon\Domain\Contact\Contact;
use Core\Application\Common\UseCase\ErrorResponse;
use Centreon\Domain\Contact\Interfaces\ContactInterface;
use Core\Application\Common\UseCase\ForbiddenResponse;
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
    $useCase($presenter);

    expect($presenter->getResponseStatus())->toBeInstanceOf(ErrorResponse::class);
    expect($presenter->getResponseStatus()?->getMessage())->toBe(
        'Impossible to get contact groups from data storage'
    );
});

it('should present an ForbiddenResponse if the user doesnt have the read menu access to contact group', function () {
    $useCase = new FindContactGroups($this->repository, $this->user);
    $this->user
        ->expects($this->once())
        ->method('isAdmin')
        ->willReturn(false);

    $this->user
        ->expects($this->once())
        ->method('hasTopologyRole')
        ->with(Contact::ROLE_CONFIGURATION_USERS_CONTACT_GROUPS_READ)
        ->willReturn(false);

    $presenter = new FindContactGroupsPresenterStub($this->presenterFormatter);
    $useCase($presenter);

    expect($presenter->getResponseStatus())->toBeInstanceOf(ForbiddenResponse::class);
    expect($presenter->getResponseStatus()?->getMessage())->toBe(
        'You are not allowed to access contact groups'
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
    $useCase($presenter);
});

it('should call the method FindAllByUserId if the user is not admin', function () {
    $useCase = new FindContactGroups($this->repository, $this->user);
    $this->user
        ->expects($this->once())
        ->method('getId')
        ->willReturn(1);

    $this->user
        ->expects($this->once())
        ->method('hasTopologyRole')
        ->with(Contact::ROLE_CONFIGURATION_USERS_CONTACT_GROUPS_READ)
        ->willReturn(true);

    $this->user
        ->expects($this->once())
        ->method('isAdmin')
        ->willReturn(false);

    $this->repository
        ->expects($this->once())
        ->method('FindAllByUserId')
        ->with(1);

    $presenter = new FindContactGroupsPresenterStub($this->presenterFormatter);
    $useCase($presenter);
});
