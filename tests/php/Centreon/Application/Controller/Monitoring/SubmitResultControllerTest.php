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

namespace Tests\Centreon\Application\Controller\Monitoring;

use FOS\RestBundle\View\View;
use PHPUnit\Framework\TestCase;
use Centreon\Domain\Contact\Contact;
use Psr\Container\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Centreon\Domain\Monitoring\SubmitResult\SubmitResultService;
use Centreon\Application\Controller\Monitoring\SubmitResultController;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Centreon\Domain\Monitoring\MonitoringResource\Model\MonitoringResource;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class SubmitResultControllerTest extends TestCase
{
    /**
     * @var Contact
     */
    private $adminContact;

    /**
     * @var MonitoringResource
     */
    private $hostResource;

    /**
     * @var MonitoringResource
     */
    private $serviceResource;

    /**
     * @var string|false
     */
    private $correctJsonSubmitResult;

    /**
     * @var string|false
     */
    private $wrongJsonSubmitResult;

    /**
     * @var string|false
     */
    private $hostSubmitResultJson;

    /**
     * @var string|false
     */
    private $serviceSubmitResultJson;

    /**
     * @var SubmitResultService|\PHPUnit\Framework\MockObject\MockObject
     */
    private $submitResultService;

    /**
     * @var ContainerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $container;

    /**
     * @var Request|\PHPUnit\Framework\MockObject\MockObject
     */
    private $request;

    protected function setUp(): void
    {
        $timezone = new \DateTimeZone('Europe/Paris');

        $this->adminContact = (new Contact())
            ->setId(1)
            ->setName('admin')
            ->setAdmin(true)
            ->setTimezone($timezone);

        $correctJsonSubmitResult = [
            'resources' => [
                [
                    'type' => 'host',
                    'id' => 1,
                    'name' => 'hostName',
                    'parent' => null,
                    'status' => 2,
                    'output' => 'Host went down',
                    'performance_data' => 'ping: 0'
                ],
                [
                    'type' => 'service',
                    'name' => 'serviceName',
                    'id' => 1,
                    'parent' => [
                        'type' => 'host',
                        'id' => 1,
                        'name' => 'hostName',
                    ],
                    'status' => 2,
                    'output' => 'Service went critical',
                    'performance_data' => 'proc: 0'
                ],
            ],
        ];

        $hostSubmitResultJson = [
            'status' => 2,
            'output' => 'Host went down',
            'performance_data' => 'ping: 0'
        ];

        $serviceSubmitResultJson = [
            'status' => 2,
            'output' => 'Service went critical',
            'performance_data' => 'proc: 0'
        ];

        $this->hostResource = new MonitoringResource(
            $correctJsonSubmitResult['resources'][0]['id'],
            $correctJsonSubmitResult['resources'][0]['name'],
            $correctJsonSubmitResult['resources'][0]['type'],
        );

        $this->serviceResource = (new MonitoringResource(
            $correctJsonSubmitResult['resources'][1]['id'],
            $correctJsonSubmitResult['resources'][1]['name'],
            $correctJsonSubmitResult['resources'][1]['type'],
        ))->setParent($this->hostResource);

        $this->correctJsonSubmitResult = json_encode($correctJsonSubmitResult);
        $this->serviceSubmitResultJson = json_encode($serviceSubmitResultJson);
        $this->hostSubmitResultJson = json_encode($hostSubmitResultJson);

        $this->wrongJsonSubmitResult = json_encode([
            'unknown_property' => 'unknown',
        ]);

        $this->submitResultService = $this->createMock(SubmitResultService::class);

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

        $this->request = $this->createMock(Request::class);
    }

    /**
     * Testing wrongly formatted JSON POST data for submitResultResources
     */
    public function testSubmitResultResourcesBadJsonFormat(): void
    {
        $submitResultController = new SubmitResultController($this->submitResultService);
        $submitResultController->setContainer($this->container);

        $this->request->expects($this->once())
            ->method('getContent')
            ->willReturn('[}');
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Error when decoding sent data');
        $submitResultController->submitResultResources($this->request);
    }

    /**
     * Testing with wrong property added to the POST JSON for submitResultResources
     */
    public function testSubmitResultResourcesBadJsonProperties(): void
    {
        $submitResultController = new SubmitResultController($this->submitResultService);
        $submitResultController->setContainer($this->container);

        $this->request->expects($this->any())
            ->method('getContent')
            ->willReturn($this->wrongJsonSubmitResult);
        $this->expectException(\InvalidArgumentException::class);
        $submitResultController->submitResultResources($this->request);
    }

    /**
     * Testing with a correct JSON POST data and successful submit for submitResultResources
     */
    public function testSubmitResultResourcesSuccess(): void
    {
        $this->submitResultService->expects($this->any())
            ->method('filterByContact')
            ->willReturn($this->submitResultService);

        $submitResultController = new SubmitResultController($this->submitResultService);
        $submitResultController->setContainer($this->container);

        $this->request->expects($this->any())
            ->method('getContent')
            ->willReturn($this->correctJsonSubmitResult);
        $view = $submitResultController->submitResultResources($this->request);

        $this->assertEquals($view, View::create(null, Response::HTTP_NO_CONTENT));
    }

    /**
     * Tesring with wrongly formatted JSON POST data for submitResultHost
     */
    public function testSubmitResultHostBadJsonFormat(): void
    {
        $submitResultController = new SubmitResultController($this->submitResultService);
        $submitResultController->setContainer($this->container);

        $this->request->expects($this->once())
            ->method('getContent')
            ->willReturn('[}');
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Error when decoding sent data');
        $submitResultController->submitResultHost($this->request, $this->hostResource->getId());
    }
    /**
     * Testing with wrong property added to the POST JSON for submitResultHost
     */
    public function testSubmitResultHostBadJsonProperties(): void
    {
        $submitResultController = new SubmitResultController($this->submitResultService);
        $submitResultController->setContainer($this->container);

        $this->request->expects($this->any())
            ->method('getContent')
            ->willReturn($this->wrongJsonSubmitResult);
        $this->expectException(\InvalidArgumentException::class);
        //$this->expectExceptionMessage('[status] The property status is required');
        $submitResultController->submitResultHost($this->request, $this->hostResource->getId());
    }
    /**
     * Testing with a correct JSON POST data and successful submit for submitResultHost
     */
    public function testSubmitResultHostSuccess(): void
    {
        $this->submitResultService->expects($this->any())
        ->method('filterByContact')
        ->willReturn($this->submitResultService);

        $submitResultController = new SubmitResultController($this->submitResultService);
        $submitResultController->setContainer($this->container);

        $this->request->expects($this->any())
            ->method('getContent')
            ->willReturn($this->hostSubmitResultJson);
        $view = $submitResultController->submitResultHost($this->request, $this->hostResource->getId());

        $this->assertEquals($view, View::create(null, Response::HTTP_NO_CONTENT));
    }

    /**
     * Tesring with wrongly formatted JSON POST data for submitResultService
     */
    public function testSubmitResultServiceBadJsonFormat(): void
    {
        $submitResultController = new SubmitResultController($this->submitResultService);
        $submitResultController->setContainer($this->container);

        $this->request->expects($this->once())
            ->method('getContent')
            ->willReturn('[}');
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Error when decoding sent data');
        $submitResultController->submitResultService(
            $this->request,
            $this->serviceResource->getParent()->getId(),
            $this->serviceResource->getId()
        );
    }
    /**
     * Testing with wrong property added to the POST JSON for submitResultService
     */
    public function testSubmitResultServiceBadJsonProperties(): void
    {
        $submitResultController = new SubmitResultController($this->submitResultService);
        $submitResultController->setContainer($this->container);

        $this->request->expects($this->any())
            ->method('getContent')
            ->willReturn($this->wrongJsonSubmitResult);
        $this->expectException(\InvalidArgumentException::class);
        // $this->expectExceptionMessage('[status] The property status is required');
        $submitResultController->submitResultService(
            $this->request,
            $this->serviceResource->getParent()->getId(),
            $this->serviceResource->getId()
        );
    }
    /**
     * Testing with a correct JSON POST data and successful submit for submitResultService
     */
    public function testSubmitResultServiceSuccess(): void
    {
        $this->submitResultService->expects($this->any())
            ->method('filterByContact')
            ->willReturn($this->submitResultService);

        $submitResultController = new SubmitResultController($this->submitResultService);
        $submitResultController->setContainer($this->container);

        $this->request->expects($this->any())
            ->method('getContent')
            ->willReturn($this->serviceSubmitResultJson);

        $view = $submitResultController->submitResultService(
            $this->request,
            $this->serviceResource->getParent()->getId(),
            $this->serviceResource->getId()
        );

        $this->assertEquals($view, View::create(null, Response::HTTP_NO_CONTENT));
    }
}
