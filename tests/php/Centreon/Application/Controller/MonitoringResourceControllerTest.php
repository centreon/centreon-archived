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

use Core\Domain\RealTime\Model\ResourceTypes\ServiceResourceType;
use Core\Infrastructure\RealTime\Hypermedia\ServiceHypermediaProvider;
use FOS\RestBundle\View\View;
use PHPUnit\Framework\TestCase;
use FOS\RestBundle\Context\Context;
use Centreon\Domain\Contact\Contact;
use Psr\Container\ContainerInterface;
use JMS\Serializer\SerializerInterface;
use Centreon\Domain\Entity\EntityValidator;
use Symfony\Component\HttpFoundation\InputBag;
use Symfony\Component\HttpFoundation\Request;
use Centreon\Domain\Monitoring\ResourceFilter;
use Centreon\Domain\Monitoring\ResourceStatus;
use Centreon\Application\Normalizer\IconUrlNormalizer;
use Centreon\Domain\Monitoring\Resource as ResourceEntity;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Centreon\Application\Controller\MonitoringResourceController;
use Centreon\Domain\Monitoring\Interfaces\ResourceServiceInterface;
use Centreon\Domain\Monitoring\Serializer\ResourceExclusionStrategy;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Centreon\Domain\RequestParameters\Interfaces\RequestParametersInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\HttpFoundation\ServerBag;
use Symfony\Component\HttpFoundation\RequestStack;

class MonitoringResourceControllerTest extends TestCase
{
    /**
     * @var Contact
     */
    protected $adminContact;

    /**
     * @var ResourceEntity
     */
    protected $resource;

    /**
     * @var ResourceServiceInterface&\PHPUnit\Framework\MockObject\MockObject
     */
    protected $resourceService;

    /**
     * @var UrlGeneratorInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $urlGenerator;

    /**
     * @var IconUrlNormalizer&\PHPUnit\Framework\MockObject\MockObject
     */
    protected $iconUrlNormalizer;

    /**
     * @var ContainerInterface&\PHPUnit\Framework\MockObject\MockObject
     */
    protected $container;

    /**
     * @var RequestParametersInterface&\PHPUnit\Framework\MockObject\MockObject
     */
    protected $requestParameters;

    /**
     * @var Request&\PHPUnit\Framework\MockObject\MockObject
     */
    protected $request;

    /**
     * @var SerializerInterface&\PHPUnit\Framework\MockObject\MockObject
     */
    protected $serializer;

    /**
     * @var EntityValidator&\PHPUnit\Framework\MockObject\MockObject
     */
    protected $entityValidator;

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

        $this->resourceService = $this->createMock(ResourceServiceInterface::class);
        $this->urlGenerator = $kernel->getContainer()->get('router'); //@phpstan-ignore-line
        $this->request = $this->createMock(Request::class);
        $this->request->server = new ServerBag([]);
        $requestStack = new RequestStack();
        $requestStack->push($this->request);
        $this->urlGenerator->setHttpServerBag($requestStack); //@phpstan-ignore-line
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

        $this->requestParameters = $this->createMock(RequestParametersInterface::class);
        $this->serializer = $this->createMock(SerializerInterface::class);
        $this->entityValidator = $this->createMock(EntityValidator::class);
    }

    /**
     * test getHostTimeline
     */
    public function testList(): void
    {
        $serviceHypermediaProvider = $this->createMock(ServiceHypermediaProvider::class);
        $serviceHypermediaProvider->method('isValidFor')->willReturn(true);
        $serviceHypermediaProvider->method('createEndpoints')->willReturn(
            [
               'details' => 'details',
               'performance_graph' => 'performance_graph',
               'status_graph' => 'status_graph',
               'downtime' => 'downtime',
               'acknowledgement' => 'acknowledgement',
               'timeline' => 'timeline',
            ]
        );
        $serviceHypermediaProvider->method('createInternalUris')->willReturn(
            [
               'configuration' => 'configuration',
               'reporting' => 'reporting',
               'logs' => 'logs',
            ]
        );
        $serviceResourceType = $this->createMock(ServiceResourceType::class);

        $this->resourceService->expects($this->once())
            ->method('filterByContact')
            ->willReturn($this->resourceService);

        $this->resourceService->expects($this->once())
            ->method('findResources')
            ->willReturn([$this->resource]);

        $resourceController = new MonitoringResourceController(
            $this->resourceService,
            $this->iconUrlNormalizer,
            new \ArrayIterator([$serviceResourceType]),
            new \ArrayIterator([$serviceHypermediaProvider])
        );
        $resourceController->setContainer($this->container);

        $this->request->query = new InputBag();

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
            ->setGroups(MonitoringResourceController::SERIALIZER_GROUPS_LISTING)
            ->enableMaxDepth();
        $context->addExclusionStrategy(new ResourceExclusionStrategy());

        $this->assertEquals(
            $view,
            View::create(
                [
                'result' => [$this->resource],
                'meta' => []
                ]
            )->setContext($context)
        );
    }

        /**
         * @var ResourceEntity $resource
         */
    /*         $resource = $view->getData()['result'][0];

        $this->assertEquals($resource->getLinks()->getUris()->getConfiguration(), '/main.php?p=60101&o=c&host_id=1');
        $this->assertEquals($resource->getLinks()->getUris()->getLogs(), '/main.php?p=20301&h=1');
        $this->assertEquals($resource->getLinks()->getUris()->getReporting(), '/main.php?p=307&host=1');

        $this->assertEquals(
            '/api/latest/monitoring/resources/hosts/1',
            $resource->getLinks()->getEndpoints()->getDetails(),
        );
        $this->assertEquals(
            '/api/latest/monitoring/hosts/1/timeline',
            $resource->getLinks()->getEndpoints()->getTimeline(),
        );
        $this->assertEquals(
            '/api/latest/monitoring/hosts/1/acknowledgements?limit=1',
            $resource->getLinks()->getEndpoints()->getAcknowledgement(),
        );
        $this->assertMatchesRegularExpression(
            '#/api/latest/monitoring/hosts/1/downtimes\?'
                . 'search=\{"\$and":\[\{"start_time":\{"\$lt":\d+\},"end_time":\{"\$gt":\d+\},'
                . '"0":\{"\$or":\{"is_cancelled":\{"\$neq":1\},"deletion_time":\{"\$gt":\d+\}\}\}\}\]\}#',
            urldecode($resource->getLinks()->getEndpoints()->getDowntime() ?? '')
        );
        $this->assertNull($resource->getLinks()->getEndpoints()->getStatusGraph());
        $this->assertNull($resource->getLinks()->getEndpoints()->getPerformanceGraph());
    } */

    /**
     * test buildHostDetailsUri
     */
    /*     public function testBuildHostDetailsUri(): void
    {
        $resourceController = new MonitoringResourceController(
            $this->resourceService,
            $this->urlGenerator,
            $this->iconUrlNormalizer
        );

        $this->assertEquals(
            urldecode($resourceController->buildHostDetailsUri(1)),
            '/monitoring/resources?details={"type":"host","id":1,"tab":"details","uuid":"h1"}'
        );
    } */

    /**
     * test buildHostUri
     */
    /*     public function testBuildHostUri(): void
    {
        $resourceController = new MonitoringResourceController(
            $this->resourceService,
            $this->urlGenerator,
            $this->iconUrlNormalizer
        );

        $this->assertEquals(
            urldecode($resourceController->buildHostUri(1, 'graph')),
            '/monitoring/resources?details={"type":"host","id":1,"tab":"graph","uuid":"h1"}'
        );
    } */

    /**
     * test buildServiceDetailsUri
     */
    /* public function testBuildServiceDetailsUri(): void
    {
        $resourceController = new MonitoringResourceController(
            $this->resourceService,
            $this->urlGenerator,
            $this->iconUrlNormalizer
        );

        $this->assertEquals(
            urldecode($resourceController->buildServiceDetailsUri(1, 2)),
            '/monitoring/resources?details=' .
            '{"parentType":"host","parentId":1,"type":"service","id":2,"tab":"details","uuid":"s2"}'
        );
    } */

    /**
     * test buildServiceUri
     */
    /*  public function testBuildServiceUri(): void
    {
        $resourceController = new MonitoringResourceController(
            $this->resourceService,
            $this->urlGenerator,
            $this->iconUrlNormalizer
        );

        $this->assertEquals(
            urldecode($resourceController->buildServiceUri(1, 2, 'timeline')),
            '/monitoring/resources?details=' .
            '{"parentType":"host","parentId":1,"type":"service","id":2,"tab":"timeline","uuid":"s2"}'
        );
    } */
}
