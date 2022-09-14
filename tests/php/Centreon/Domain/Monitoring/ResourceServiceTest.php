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

namespace Tests\Centreon\Domain\Monitoring;

use Centreon\Domain\Contact\Interfaces\ContactInterface;
use Centreon\Domain\Monitoring\Resource;
use Centreon\Domain\Monitoring\ResourceFilter;
use Centreon\Domain\Monitoring\ResourceService;
use Core\Security\AccessGroup\Application\Repository\ReadAccessGroupRepositoryInterface;
use Centreon\Domain\Monitoring\Interfaces\MonitoringRepositoryInterface;
use Core\Resources\Application\Repository\ReadResourceRepositoryInterface;
use Core\Security\AccessGroup\Domain\Model\AccessGroup;

it('find resources and build uuids', function () {
    $hostResource = (new Resource())
        ->setType('host')
        ->setId(1)
        ->setName('host1');
    $serviceResource = (new Resource())
        ->setType('service')
        ->setId(1)
        ->setName('service1')
        ->setParent($hostResource);

    $resourceRepository = $this->createMock(ReadResourceRepositoryInterface::class);
    $resourceRepository->expects($this->any())
        ->method('findResources')
        ->willReturn([$hostResource, $serviceResource]); // values returned for the all next tests

    $accessGroup = $this->createMock(ReadAccessGroupRepositoryInterface::class);

    $resourceService = new ResourceService($accessGroup, $resourceRepository);
    $contact = $this->createMock(ContactInterface::class);
    $contact->method('isAdmin')->willReturn(true);
    $resourceService->filterByContact($contact);

    $resourcesFound = $resourceService->findResources(new ResourceFilter());

    expect($resourcesFound)->toHaveCount(2);
    expect($resourcesFound[0]->getUuid())->toBe('h1');
    expect($resourcesFound[1]->getUuid())->toBe('h1-s1');
});


it('find resources by access group if client is not an admin', function () {
    $hostResource = (new Resource())
        ->setType('host')
        ->setId(1)
        ->setName('host1');
    $serviceResource = (new Resource())
        ->setType('service')
        ->setId(1)
        ->setName('service1')
        ->setParent($hostResource);

    $filter = new ResourceFilter();
    $resourceRepository = $this->createMock(ReadResourceRepositoryInterface::class);
    $resourceRepository
        ->expects($this->once())
        ->method('findResourcesByAccessGroupIds')
        ->with($filter, [1])
        ->willReturn([$hostResource, $serviceResource]); // values returned for the all next tests

    $accessGroup = $this->createMock(ReadAccessGroupRepositoryInterface::class);
    $accessGroup->method('findByContact')->willReturn([new AccessGroup(1, 'access_group_1', 'acc_1')]);

    $resourceService = new ResourceService($accessGroup, $resourceRepository);
    $contact = $this->createMock(ContactInterface::class);
    $contact->method('isAdmin')->willReturn(false);
    $resourceService->filterByContact($contact);

    $resourcesFound = $resourceService->findResources($filter);

    expect($resourcesFound)->toHaveCount(2);
    expect($resourcesFound[0]->getUuid())->toBe('h1');
    expect($resourcesFound[1]->getUuid())->toBe('h1-s1');
});
