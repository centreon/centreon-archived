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

use Centreon\Domain\Monitoring\Resource;
use Centreon\Domain\Monitoring\ResourceFilter;
use Centreon\Domain\Monitoring\ResourceService;
use Centreon\Domain\Monitoring\Interfaces\ResourceRepositoryInterface;
use Centreon\Domain\Security\Interfaces\AccessGroupRepositoryInterface;
use Centreon\Domain\Monitoring\Interfaces\MonitoringRepositoryInterface;

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

    $resourceRepository = $this->createMock(ResourceRepositoryInterface::class);
    $resourceRepository->expects($this->any())
        ->method('findResources')
        ->willReturn([$hostResource, $serviceResource]); // values returned for the all next tests

    $monitoringRepository = $this->createMock(MonitoringRepositoryInterface::class);

    $accessGroup = $this->createMock(AccessGroupRepositoryInterface::class);

    $resourceService = new ResourceService(
        $monitoringRepository,
        $accessGroup,
    );
    $resourceService->setResourceRepository($resourceRepository);

    $resourcesFound = $resourceService->findResources(new ResourceFilter());

    expect($resourcesFound)->toHaveCount(2);
    expect($resourcesFound[0]->getUuid())->toBe('h1');
    expect($resourcesFound[1]->getUuid())->toBe('h1-s1');
});
