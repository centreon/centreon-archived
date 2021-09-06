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

namespace Tests\Centreon\Application\Controller;

use FOS\RestBundle\View\View;
use PHPUnit\Framework\TestCase;
use Centreon\Domain\Check\Check;
use Centreon\Domain\Contact\Contact;
use Psr\Container\ContainerInterface;
use JMS\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\Request;
use Centreon\Infrastructure\Monitoring\Check\API\Model\MassCheckResourceRequest;
use Centreon\Application\Controller\CheckController;
use Centreon\Domain\Check\Interfaces\CheckServiceInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Centreon\Domain\Monitoring\MonitoringResource\Model\MonitoringResource;
use Centreon\Infrastructure\Monitoring\MonitoringResource\API\v2110\Validator\Interfaces\MassiveCheckValidatorInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class CheckControllerTest extends TestCase
{
    /**
     * @var Contact
     */
    protected $adminContact;

    /**
     * @var string|false
     */
    protected $goodJsonCheck;

    /**
     * @var string|false
     */
    protected $badJsonCheck;

    /**
     * @var MassCheckResourceRequest
     */
    protected $massCheckResourceRequest;

    /**
     * @var Check
     */
    protected $check;

    /**
     * @var MonitoringResource
     */
    protected $hostResource;

    /**
     * @var MonitoringResource
     */
    protected $serviceResource;

    /**
     * @var CheckServiceInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $checkService;

    /**
     * @var ContainerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $container;

    /**
     * @var Request|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $request;

    /**
     * @var SerializerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $serializer;

    /**
     * @var MassiveCheckValidatorInterface
     */
    protected $massiveCheckValidator;

    protected function setUp(): void
    {
        $timezone = new \DateTimeZone('Europe/Paris');

        $this->adminContact = (new Contact())
            ->setId(1)
            ->setName('admin')
            ->setAdmin(true)
            ->setTimezone($timezone);

        $goodJsonCheck = [
            'resources' => [
                [
                    'type' => 'host',
                    'id' => 1,
                    'name' => 'hostName',
                    'parent' => null,
                ],
                [
                    'type' => 'service',
                    'id' => 1,
                    'name' => 'serviceName',
                    'parent' => [
                        'id' => 1,
                        'type' => 'host',
                        'name' => 'hostName'
                    ],
                ],
            ],
        ];

        $this->hostResource = new MonitoringResource(
            $goodJsonCheck['resources'][0]['id'],
            $goodJsonCheck['resources'][0]['name'],
            $goodJsonCheck['resources'][0]['type']
        );

        $this->serviceResource = (new MonitoringResource(
            $goodJsonCheck['resources'][1]['id'],
            $goodJsonCheck['resources'][1]['name'],
            $goodJsonCheck['resources'][1]['type']
        ))->setParent($this->hostResource);

        $this->goodJsonCheck = json_encode($goodJsonCheck);

        $this->check = (new Check())
            ->setCheckTime(new \DateTime());
        $this->massCheckResourceRequest = (new MassCheckResourceRequest())
            ->setMonitoringResource([$this->hostResource, $this->serviceResource])
            ->setCheck($this->check);

        $this->badJsonCheck = json_encode([
            'unknown_property' => 'unknown',
        ]);

        $this->checkService = $this->createMock(CheckServiceInterface::class);

        $authorizationChecker = $this->createMock(AuthorizationCheckerInterface::class);
        $authorizationChecker->expects($this->once())
            ->method('isGranted')
            ->willReturn(true);
        $token = $this->createMock(TokenInterface::class);
        $token->expects($this->any())
            ->method('getUser')
            ->willReturn($this->adminContact);
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
                [$this->equalTo('security.token_storage')],
                [$this->equalTo('parameter_bag')]
            )
            ->willReturnOnConsecutiveCalls(
                $authorizationChecker,
                $tokenStorage,
                new class () {
                    public function get(): string
                    {
                        return __DIR__ . '/../../../../../';
                    }
                }
            );

        $this->massiveCheckValidator = $this->createMock(MassiveCheckValidatorInterface::class);
        $this->request = $this->createMock(Request::class);
        $this->serializer = $this->createMock(SerializerInterface::class);
    }

    /**
     * test checkResources which succeed
     */
    public function testCheckResourcesSuccess(): void
    {
        $this->checkService->expects($this->any())
            ->method('filterByContact')
            ->willReturn($this->checkService);

        $this->serializer->expects($this->once())
            ->method('deserialize')
            ->willReturn($this->massCheckResourceRequest);

        $checkController = new CheckController($this->checkService);
        $checkController->setContainer($this->container);

        $this->request->expects($this->any())
            ->method('getContent')
            ->willReturn($this->goodJsonCheck);
        $view = $checkController->massCheckResources($this->request, $this->serializer, $this->massiveCheckValidator);

        $this->assertEquals($view, View::create());
    }
}
