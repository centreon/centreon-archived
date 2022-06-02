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

namespace Tests\Core\Security\Application\UseCase\FindUserAccessGroups;

use Core\Application\Common\UseCase\ErrorResponse;
use Centreon\Domain\Contact\Interfaces\ContactInterface;
use Core\Infrastructure\Common\Presenter\PresenterFormatterInterface;
use Core\Security\Application\Repository\ReadAccessGroupRepositoryInterface;
use Core\Security\Application\UseCase\FindUserAccessGroups\FindUserAccessGroups;
use Core\Security\Application\UseCase\FindUserAccessGroups\FindUserAccessGroupsResponse;
use Core\Security\Domain\AccessGroup\Model\AccessGroup;
use Tests\Core\Security\Application\UseCase\FindUserAccessGroups\FindUserAccessGroupsPresenterStub;

beforeEach(function () {
    $this->repository = $this->createMock(ReadAccessGroupRepositoryInterface::class);
    $this->presenterFormatter = $this->createMock(PresenterFormatterInterface::class);
    $this->user = $this->createMock(ContactInterface::class);
});

it('should present an ErrorResponse while an exception occured', function () {
    $useCase = new FindUserAccessGroups($this->repository, $this->user);
    $this->user
        ->expects($this->once())
        ->method('isAdmin')
        ->willReturn(true);

    $this->repository
        ->expects($this->once())
        ->method('findAll')
        ->willThrowException(new \Exception());

    $presenter = new FindUserAccessGroupsPresenterStub($this->presenterFormatter);
    $useCase($presenter);

    expect($presenter->getResponseStatus())->toBeInstanceOf(ErrorResponse::class);
    expect($presenter->getResponseStatus()?->getMessage())->toBe(
        'Impossible to get contact groups from data storage'
    );
});

it('should call the method findAll if the user is admin', function () {
    $useCase = new FindUserAccessGroups($this->repository, $this->user);
    $this->user
        ->expects($this->once())
        ->method('isAdmin')
        ->willReturn(true);

    $this->repository
        ->expects($this->once())
        ->method('findAll');

    $presenter = new FindUserAccessGroupsPresenterStub($this->presenterFormatter);
    $useCase($presenter);
});

it('should call the method findByContact if the user is not admin', function () {
    $useCase = new FindUserAccessGroups($this->repository, $this->user);

    $this->user
        ->expects($this->once())
        ->method('isAdmin')
        ->willReturn(false);

    $this->repository
        ->expects($this->once())
        ->method('findByContact')
        ->with($this->user);

    $presenter = new FindUserAccessGroupsPresenterStub($this->presenterFormatter);
    $useCase($presenter);
});

it('should present a FindUserAccessGroupsResponse when no error occured', function () {
    $useCase = new FindUserAccessGroups($this->repository, $this->user);

    $accessGroup = (new AccessGroup(1, 'access_group'));
    $this->repository
        ->expects($this->once())
        ->method('findAll')
        ->willReturn([$accessGroup]);

    $this->user
        ->expects($this->once())
        ->method('isAdmin')
        ->willReturn(true);

    $presenter = new FindUserAccessGroupsPresenterStub($this->presenterFormatter);
    $useCase($presenter);
    expect($presenter->response)->toBeInstanceOf(FindUserAccessGroupsResponse::class);
    expect($presenter->response->accessGroups[0])->toBe(
        [
            'id' => 1,
            'name' => 'access_group'
        ]
    );
});
