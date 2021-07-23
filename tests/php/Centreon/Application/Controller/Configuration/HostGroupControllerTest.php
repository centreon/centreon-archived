<?php

/*
 * Copyright 2005 - 2021 Centreon (https://www.centreon.com/)
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

namespace Tests\Centreon\Application\Controller\Configuration;

use Centreon\Application\Controller\Configuration\HostGroupController;
use Centreon\Domain\Contact\Contact;
use Centreon\Domain\HostConfiguration\UseCase\V2110\HostGroup\FindHostGroups;
use Centreon\Domain\HostConfiguration\UseCase\V2110\HostGroup\FindHostGroupsResponse;
use Centreon\Domain\RequestParameters\Interfaces\RequestParametersInterface;
use Centreon\Domain\RequestParameters\RequestParameters;
use Centreon\Infrastructure\HostConfiguration\API\Model\HostGroup\HostGroupV2110Factory;
use FOS\RestBundle\View\View;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Tests\Centreon\Domain\HostConfiguration\Model\HostGroupTest;

/**
 * @package Tests\Centreon\Application\Controller\Configuration
 */
class HostGroupControllerTest extends TestCase
{
    /**
     * @var RequestParametersInterface&\PHPUnit\Framework\MockObject\MockObject
     */
    private $requestParammeters;

    /**
     * @var FindHostGroups&\PHPUnit\Framework\MockObject\MockObject
     */
    private $findHostGroup;

    /**
     * @var ContainerInterface&\PHPUnit\Framework\MockObject\MockObject
     */
    private $container;

    protected function setUp(): void
    {
        $this->requestParammeters = $this->createMock(RequestParametersInterface::class);
        $this->requestParammeters->expects($this->any())
            ->method('toArray')
            ->willReturn(
                [
                    "page" => 1,
                    "limit" => 10,
                    "search" => new \stdClass(),
                    "sort_by" => new \stdClass(),
                    "total" => 0
                ]
            );

        $this->findHostGroup = $this->createMock(FindHostGroups::class);

        $adminContact = (new Contact())
            ->setId(1)
            ->setName('admin')
            ->setAdmin(true)
            ->setTimezone(new \DateTimeZone('Europe/Paris'));
        $adminContact->addTopologyRule(Contact::ROLE_API_CONFIGURATION);

        $authorizationChecker = $this->createMock(AuthorizationCheckerInterface::class);
        $authorizationChecker->expects($this->any())
            ->method('isGranted')
            ->willReturn(true);
        $token = $this->createMock(TokenInterface::class);
        $token->expects($this->any())
            ->method('getUser')
            ->willReturn($adminContact);
        $tokenStorage = $this->createMock(TokenStorageInterface::class);
        $tokenStorage->expects($this->any())
            ->method('getToken')
            ->willReturn($token);

        $this->container = $this->createMock(ContainerInterface::class);
        $this->container->expects($this->any())
            ->method('has')
            ->willReturn(true);
        $this->container->expects($this->any())
            ->method('get')
            ->withConsecutive(
                [$this->equalTo('security.authorization_checker')],
                [$this->equalTo('security.token_storage')]
            )
            ->willReturnOnConsecutiveCalls(
                $authorizationChecker,
                $tokenStorage
            );
    }

    public function testFindHostGroups(): void
    {
        $controller = new HostGroupController();
        $controller->setContainer($this->container);
        $findHostGroupResponse = new FindHostGroupsResponse();
        $findHostGroupResponse->setHostGroups([HostGroupTest::createEntity()]);
        $this->findHostGroup->expects($this->any())
            ->method('execute')
            ->willReturn($findHostGroupResponse);
        $view = $controller->findHostGroups(
            $this->requestParammeters,
            $this->findHostGroup
        );
        $this->assertEquals(
            View::create([
                'result' => HostGroupV2110Factory::createFromResponse($findHostGroupResponse),
                'meta' => (new RequestParameters())->toArray()
            ]),
            $view
        );
    }
}
