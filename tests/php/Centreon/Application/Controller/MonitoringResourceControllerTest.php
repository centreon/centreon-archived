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

use Centreon\Domain\Contact\Contact;
use Centreon\Domain\Monitoring\Resources as ResourceEntity;
use Centreon\Domain\Monitoring\ResourceStatus;
use Centreon\Domain\Monitoring\ResourceFilter;
use Centreon\Domain\Monitoring\Serializer\ResourceExclusionStrategy;
use Centreon\Application\Controller\MonitoringResourceController;
use Centreon\Domain\Monitoring\Interfaces\MonitoringServiceInterface;
use Centreon\Domain\Monitoring\Interfaces\ResourceServiceInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Centreon\Application\Normalizer\IconUrlNormalizer;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Centreon\Domain\RequestParameters\Interfaces\RequestParametersInterface;
use Symfony\Component\HttpFoundation\Request;
use JMS\Serializer\SerializerInterface;
use Centreon\Domain\Entity\EntityValidator;
use FOS\RestBundle\Context\Context;
use FOS\RestBundle\View\View;
use Psr\Container\ContainerInterface;
use PHPUnit\Framework\TestCase;

class MonitoringResourceControllerTest extends TestCase
{
    protected $adminContact;

    protected $resource;

    protected $timelineEvent;

    protected $monitoringService;
    protected $resourceService;
    protected $urlGenerator;
    protected $iconUrlNormalizer;

    protected $container;

    protected $requestParameters;
    protected $request;
    protected $serializer;
    protected $entityValidator;

    protected function setUp()
    {
        $timezone = new \DateTimeZone('Europe/Paris');

        $this->adminContact = (new Contact())
            ->setId(1)
            ->setName('admin')
            ->setAdmin(true)
            ->setTimezone($timezone);
        $this->adminContact->addTopologyRule(Contact::ROLE_CONFIGURATION_HOSTS_WRITE);
        $this->adminContact->addTopologyRule(Contact::ROLE_MONITORING_EVENT_LOGS);
        $this->adminContact->addTopologyRule(Contact::ROLE_REPORTING_DASHBOARD_HOSTS);

        $resourceStatus = (new ResourceStatus())
            ->setCode(0)
            ->setName('UP')
            ->setSeverityCode(4);

        $this->resource = (new ResourceEntity())
            ->setId(1)
            ->setType('host')
            ->setName('host1')
            ->setIcon(null)
            ->setParent(null)
            ->setStatus($resourceStatus);

        $this->monitoringService = $this->createMock(MonitoringServiceInterface::class);
        $this->resourceService = $this->createMock(ResourceServiceInterface::class);
        $this->urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $this->iconUrlNormalizer = $this->createMock(IconUrlNormalizer::class);

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
                [$this->equalTo('security.token_storage')]
            )
            ->willReturnOnConsecutiveCalls(
                $authorizationChecker,
                $tokenStorage
            );

        $this->requestParameters = $this->createMock(RequestParametersInterface::class);
        $this->request = $this->createMock(Request::class);
        $this->serializer = $this->createMock(SerializerInterface::class);
        $this->entityValidator = $this->createMock(EntityValidator::class);
    }

    /**
     * test getHostTimeline
     */
    public function testList()
    {
        $this->resourceService->expects($this->once())
            ->method('filterByContact')
            ->willReturn($this->resourceService);

        $this->resourceService->expects($this->once())
            ->method('findResources')
            ->willReturn([$this->resource]);

        $this->urlGenerator->expects($this->any())
            ->method('generate')
            ->willReturn('/');

        $resourceController = new MonitoringResourceController(
            $this->monitoringService,
            $this->resourceService,
            $this->urlGenerator,
            $this->iconUrlNormalizer
        );
        $resourceController->setContainer($this->container);

        $this->request->query = new class () {
            public function all()
            {
                return [];
            }
        };

        $this->serializer->expects($this->once())
            ->method('deserialize')
            ->willReturn(new ResourceFilter());

        $view = $resourceController->list(
            $this->requestParameters,
            $this->request,
            $this->serializer,
            $this->entityValidator
        );

        $context = (new Context())
            ->setGroups(ResourceEntity::contextGroupsForListing())
            ->enableMaxDepth();
        $context->addExclusionStrategy(new ResourceExclusionStrategy());

        $this->assertEquals(
            $view,
            View::create([
                'result' => [$this->resource],
                'meta' => []
            ])->setContext($context)
        );

        /**
         * @var ResourceEntity $resource
         */
        $resource = $view->getData()['result'][0];

        $this->assertEquals($resource->getConfigurationUri(), '/main.php?p=60101&o=c&host_id=1');
        $this->assertEquals($resource->getLogsUri(), '/main.php?p=20301&h=1');
        $this->assertEquals($resource->getReportingUri(), '/main.php?p=307&host=1');
    }
}
