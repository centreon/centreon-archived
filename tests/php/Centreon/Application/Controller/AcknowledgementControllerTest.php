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
use Centreon\Domain\Acknowledgement\Acknowledgement;
use Centreon\Domain\Acknowledgement\AcknowledgementService;
use Centreon\Application\Controller\AcknowledgementController;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Centreon\Domain\Monitoring\MonitoringResource\Model\MonitoringResource;
use Centreon\Infrastructure\Monitoring\MonitoringResource\API\v2110\Validator\Interfaces\MassiveDisacknowledgementValidatorInterface;

class AcknowledgementControllerTest extends TestCase
{
    private $adminContact;

    private $hostResource;
    private $serviceResource;

    private $correctJsonDisackResources;

    private $acknowledgementService;

    private $container;

    private $request;

    /**
     * @var MassiveDisacknowledgementValidatorInterface $massiveDisacknowledgementValidator
     */
    private $massiveDisacknowledgementValidator;

    protected function setUp(): void
    {
        $timezone = new \DateTimeZone('Europe/Paris');

        $this->adminContact = (new Contact())
            ->setId(1)
            ->setName('admin')
            ->setAdmin(true)
            ->setTimezone($timezone);

        $correctJsonDisackResources = [
            'disacknowledgement' => [
                'with_services' => true,
            ],
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
            $correctJsonDisackResources['resources'][0]['id'],
            $correctJsonDisackResources['resources'][0]['name'],
            $correctJsonDisackResources['resources'][0]['type'],
        );

        $this->serviceResource = (new MonitoringResource(
            $correctJsonDisackResources['resources'][1]['id'],
            $correctJsonDisackResources['resources'][1]['name'],
            $correctJsonDisackResources['resources'][1]['type'],
        ))->setParent($this->hostResource);

        $this->correctJsonDisackResources = json_encode($correctJsonDisackResources);
        $this->acknowledgementService = $this->createMock(AcknowledgementService::class);
        $this->massiveDisacknowledgementValidator = $this->createMock(
            MassiveDisacknowledgementValidatorInterface::class
        );

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
                    public function get()
                    {
                        return __DIR__ . '/../../../../../';
                    }
                }
            );

        $this->request = $this->createMock(Request::class);
    }

    /**
     * Testing with a correct JSON DELETE data and successful massDisacknowledgeResources
     */
    public function testMassDisacknowledgeResourcesSuccess(): void
    {
        $this->acknowledgementService->expects($this->any())
            ->method('filterByContact')
            ->willReturn($this->acknowledgementService);

        $disacknowledgement = new Acknowledgement();
        $disacknowledgement->setWithServices(true);

        $this->acknowledgementService->expects($this->at(1))
            ->method('disacknowledgeResource')
            ->with($this->equalTo($this->hostResource), $this->equalTo($disacknowledgement));
        $this->acknowledgementService->expects($this->at(2))
            ->method('disacknowledgeResource')
            ->with($this->equalTo($this->serviceResource), $this->equalTo($disacknowledgement));

        $acknowledgementController = new AcknowledgementController($this->acknowledgementService);
        $acknowledgementController->setContainer($this->container);

        $this->request->expects($this->any())
            ->method('getContent')
            ->willReturn($this->correctJsonDisackResources);

        $view = $acknowledgementController->massDisacknowledgeResources(
            $this->request,
            $this->massiveDisacknowledgementValidator
        );

        $this->assertEquals($view, View::create(null, Response::HTTP_NO_CONTENT));
    }
}
