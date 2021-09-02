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

use PHPUnit\Framework\TestCase;
use FOS\RestBundle\Context\Context;
use Centreon\Domain\Contact\Contact;
use Psr\Container\ContainerInterface;
use Centreon\Application\Normalizer\IconUrlNormalizer;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Centreon\Application\Controller\MonitoringResourceController;
use Centreon\Domain\Monitoring\Interfaces\MonitoringServiceInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class MonitoringResourceControllerTest extends TestCase
{
    /**
     * @var Contact
     */
    private $adminContact;

    /**
     * @var MonitoringServiceInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $monitoringService;

    /**
     * @var UrlGeneratorInterface
     */
    private $urlGenerator;

    /**
     * @var IconUrlNormalizer|\PHPUnit\Framework\MockObject\MockObject
     */
    private $iconUrlNormalizer;

    /**
     * @var ContainerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $container;

    protected function setUp(): void
    {
        $kernel = new \App\Kernel('test', false);
        $kernel->boot();

        $timezone = new \DateTimeZone('Europe/Paris');

        $this->adminContact = (new Contact())
            ->setId(1)
            ->setName('admin')
            ->setAdmin(true)
            ->setTimezone($timezone);
        $this->adminContact->addTopologyRule(Contact::ROLE_CONFIGURATION_HOSTS_WRITE);
        $this->adminContact->addTopologyRule(Contact::ROLE_MONITORING_EVENT_LOGS);
        $this->adminContact->addTopologyRule(Contact::ROLE_REPORTING_DASHBOARD_HOSTS);

        $this->monitoringService = $this->createMock(MonitoringServiceInterface::class);
        $this->urlGenerator = $kernel->getContainer()->get('router')->getRouter()->getGenerator();
        $this->iconUrlNormalizer = $this->createMock(IconUrlNormalizer::class);

        $authorizationChecker = $this->createMock(AuthorizationCheckerInterface::class);
        $authorizationChecker->expects($this->any())
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
                [$this->equalTo('security.token_storage')]
            )
            ->willReturnOnConsecutiveCalls(
                $authorizationChecker,
                $tokenStorage
            );
    }

    /**
     * test buildHostDetailsUri
     */
    public function testBuildHostDetailsUri(): void
    {
        $resourceController = new MonitoringResourceController(
            $this->urlGenerator,
            $this->iconUrlNormalizer,
            $this->monitoringService
        );

        $this->assertEquals(
            urldecode($resourceController->buildHostDetailsUri(1)),
            '/monitoring/resources?details={"type":"host","id":1,"tab":"details","uuid":"h1"}'
        );
    }

    /**
     * test buildHostUri
     */
    public function testBuildHostUri(): void
    {
        $resourceController = new MonitoringResourceController(
            $this->urlGenerator,
            $this->iconUrlNormalizer,
            $this->monitoringService
        );

        $this->assertEquals(
            urldecode($resourceController->buildHostUri(1, 'graph')),
            '/monitoring/resources?details={"type":"host","id":1,"tab":"graph","uuid":"h1"}'
        );
    }

    /**
     * test buildServiceDetailsUri
     */
    public function testBuildServiceDetailsUri(): void
    {
        $resourceController = new MonitoringResourceController(
            $this->urlGenerator,
            $this->iconUrlNormalizer,
            $this->monitoringService
        );

        $this->assertEquals(
            urldecode($resourceController->buildServiceDetailsUri(1, 2)),
            '/monitoring/resources?details=' .
            '{"parentType":"host","parentId":1,"type":"service","id":2,"tab":"details","uuid":"s2"}'
        );
    }

    /**
     * test buildServiceUri
     */
    public function testBuildServiceUri(): void
    {
        $resourceController = new MonitoringResourceController(
            $this->urlGenerator,
            $this->iconUrlNormalizer,
            $this->monitoringService
        );

        $this->assertEquals(
            urldecode($resourceController->buildServiceUri(1, 2, 'timeline')),
            '/monitoring/resources?details=' .
            '{"parentType":"host","parentId":1,"type":"service","id":2,"tab":"timeline","uuid":"s2"}'
        );
    }
}
