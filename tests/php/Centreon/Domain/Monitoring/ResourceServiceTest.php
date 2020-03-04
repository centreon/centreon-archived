<?php

/*
 * Copyright 2005 - 2020 Centreon (https://www.centreon.com/)
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

use Centreon\Domain\Monitoring\Interfaces\ResourceRepositoryInterface;
use Centreon\Domain\Monitoring\ResourceService;
use Centreon\Domain\Monitoring\Resource;
use Centreon\Domain\Security\Interfaces\AccessGroupRepositoryInterface;
use Centreon\Domain\Monitoring\Interfaces\ResourceServiceInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use PHPUnit\Framework\TestCase;

class ResourceServiceTest extends TestCase
{
    /**
     * @throws \Exception
     */
    public function testFindResources()
    {
        $resource = (new Resource())
            ->setId(1)
            ->setName('test');

        $repository = $this->createMock(ResourceRepositoryInterface::class);
        $repository->expects(self::any())
            ->method('findResources')
            ->willReturn([$resource]); // values returned for the all next tests

        $accessGroup = $this->createMock(AccessGroupRepositoryInterface::class);
        $router = $this->createMock(UrlGeneratorInterface::class);
        $router->expects(self::any())
            ->method('generate')
            ->willReturn('/api-endpoint');

        $resourceService = new ResourceService($repository, $accessGroup, $router);

        $resourcesFound = $resourceService->findResources(ResourceServiceInterface::STATE_ALL);
        $this->assertCount(
            1,
            $resourcesFound,
            "Error, this method must relay the 'findResources' method of the monitoring repository"
        );
    }
}
