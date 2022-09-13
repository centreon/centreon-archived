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

namespace Tests\Core\Security\AccessGroup\Application\UseCase\FindLocalUserAccessGroups;

use Core\Application\Common\UseCase\ErrorResponse;
use Centreon\Domain\Contact\Interfaces\ContactInterface;
use Core\Infrastructure\Common\Presenter\PresenterFormatterInterface;
use Core\Security\AccessGroup\Application\Repository\ReadAccessGroupRepositoryInterface;
use Core\Security\AccessGroup\Application\UseCase\FindLocalUserAccessGroups\FindLocalUserAccessGroups;
use Core\Security\AccessGroup\Application\UseCase\FindLocalUserAccessGroups\FindLocalUserAccessGroupsResponse;
use Core\Security\AccessGroup\Domain\Model\AccessGroup;
use Tests\Core\Security\AccessGroup\Application\UseCase\FindLocalUserAccessGroups\{
    FindLocalUserAccessGroupsPresenterStub
};

beforeEach(function () {
    $this->repository = $this->createMock(ReadAccessGroupRepositoryInterface::class);
    $this->presenterFormatter = $this->createMock(PresenterFormatterInterface::class);
    $this->user = $this->createMock(ContactInterface::class);
});

it('should present an ErrorResponse while an exception occured', function () {
    $useCase = new FindLocalUserAccessGroups($this->repository, $this->user);
    $this->user
        ->expects($this->once())
        ->method('isAdmin')
        ->willReturn(true);

    $this->repository
        ->expects($this->once())
        ->method('findAllWithFilter')
        ->willThrowException(new \Exception());

    $presenter = new FindLocalUserAccessGroupsPresenterStub($this->presenterFormatter);
    $useCase($presenter);

    expect($presenter->getResponseStatus())->toBeInstanceOf(ErrorResponse::class);
    expect($presenter->getResponseStatus()?->getMessage())->toBe(
        'Impossible to get contact groups from data storage'
    );
});

it('should call the method findAllWithFilter if the user is admin', function () {
    $useCase = new FindLocalUserAccessGroups($this->repository, $this->user);
    $this->user
        ->expects($this->once())
        ->method('isAdmin')
        ->willReturn(true);

    $this->repository
        ->expects($this->once())
        ->method('findAllWithFilter');

    $presenter = new FindLocalUserAccessGroupsPresenterStub($this->presenterFormatter);
    $useCase($presenter);
});

it('should call the method findByContact if the user is not admin', function () {
    $useCase = new FindLocalUserAccessGroups($this->repository, $this->user);

    $this->user
        ->expects($this->once())
        ->method('isAdmin')
        ->willReturn(false);

    $this->repository
        ->expects($this->once())
        ->method('findByContactWithFilter')
        ->with($this->user);

    $presenter = new FindLocalUserAccessGroupsPresenterStub($this->presenterFormatter);
    $useCase($presenter);
});

it('should present a FindLocalUserAccessGroupsResponse when no error occured', function () {
    $useCase = new FindLocalUserAccessGroups($this->repository, $this->user);

    $accessGroup = (new AccessGroup(1, 'access_group', 'access_group_alias'))
        ->setActivate(true)
        ->setChanged(false);
    $this->repository
        ->expects($this->once())
        ->method('findAllWithFilter')
        ->willReturn([$accessGroup]);

    $this->user
        ->expects($this->once())
        ->method('isAdmin')
        ->willReturn(true);

    $presenter = new FindLocalUserAccessGroupsPresenterStub($this->presenterFormatter);
    $useCase($presenter);
    expect($presenter->response)->toBeInstanceOf(FindLocalUserAccessGroupsResponse::class);
    expect($presenter->response->accessGroups[0])->toBe(
        [
            'id' => 1,
            'name' => 'access_group',
            'alias' => 'access_group_alias',
            'has_changed' => false,
            'is_activated' => true
        ]
    );
});
