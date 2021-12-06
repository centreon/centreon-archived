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

namespace Tests\Centreon\Application\Controller\Filter;

use Centreon\Domain\Contact\Contact;
use Centreon\Application\Controller\FilterController;
use Centreon\Domain\Filter\Filter;
use Centreon\Domain\Filter\FilterException;
use Centreon\Domain\Filter\Interfaces\FilterServiceInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Context\Context;
use FOS\RestBundle\View\View;
use Psr\Container\ContainerInterface;
use PHPUnit\Framework\TestCase;

class FilterControllerTest extends TestCase
{
    protected $adminContact;

    protected $goodJsonFilter;
    protected $badJsonFilter;
    protected $filterObject;

    protected $filterService;

    protected $container;

    protected $request;

    protected function setUp(): void
    {
        $timezone = new \DateTimeZone('Europe/Paris');

        $this->adminContact = (new Contact())
            ->setId(1)
            ->setName('admin')
            ->setAdmin(true)
            ->setTimezone($timezone);

        $goodJsonFilter = [
            'name' => 'filter1',
            'criterias' => [
                [
                    'name' => 'name1',
                    'type' => 'type1',
                    'value' => 'value1',
                ],
            ],
        ];

        $this->goodJsonFilter = json_encode($goodJsonFilter);

        $this->filterObject = (new Filter())
            ->setPageName('events-view')
            ->setUserId(1)
            ->setName($goodJsonFilter['name'])
            ->setCriterias($goodJsonFilter['criterias']);
        $this->goodJsonFilterWithId = json_encode(array_merge($goodJsonFilter, ['id' => 1]));

        $this->badJsonFilter = json_encode([
            'unknown_property' => 'unknown',
        ]);

        $this->filterService = $this->createMock(FilterServiceInterface::class);

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
     * test addFilter with bad json format
     */
    public function testAddFilterBadJsonFormat()
    {
        $filterController = new FilterController($this->filterService);
        $filterController->setContainer($this->container);

        $this->request->expects($this->once())
            ->method('getContent')
            ->willReturn('[}');
        $this->expectException(FilterException::class);
        $this->expectExceptionMessage('Error when decoding sent data');
        $filterController->addFilter($this->request, 'events-view');
    }

    /**
     * test addFilter with bad json properties
     */
    public function testAddFilterBadJsonProperties()
    {
        $filterController = new FilterController($this->filterService);
        $filterController->setContainer($this->container);

        $this->request->expects($this->once())
            ->method('getContent')
            ->willReturn($this->badJsonFilter);
        $this->expectException(FilterException::class);
        $filterController->addFilter($this->request, 'events-view');
    }

    /**
     * test addFilter which succeed
     */
    public function testAddFilterSuccess()
    {
        $this->filterService->expects($this->once())
            ->method('addFilter')
            ->willReturn(1);

        $this->filterService->expects($this->once())
            ->method('findFilterByUserId')
            ->willReturn($this->filterObject);

        $filterController = new FilterController($this->filterService);
        $filterController->setContainer($this->container);

        $this->request->expects($this->once())
            ->method('getContent')
            ->willReturn($this->goodJsonFilter);
        $view = $filterController->addFilter($this->request, 'events-view');

        $context = (new Context())->setGroups(FilterController::SERIALIZER_GROUPS_MAIN);
        $this->assertEquals(
            $view,
            View::create($this->filterObject)->setContext($context)
        );
    }

    /**
     * test updateFilter with bad json format
     */
    public function testUpdateFilterBadJsonFormat()
    {
        $filterController = new FilterController($this->filterService);
        $filterController->setContainer($this->container);

        $this->request->expects($this->once())
            ->method('getContent')
            ->willReturn('[}');
        $this->expectException(FilterException::class);
        $this->expectExceptionMessage('Error when decoding sent data');
        $filterController->updateFilter($this->request, 'events-view', 1);
    }

    /**
     * test updateFilter with bad json properties
     */
    public function testUpdateFilterBadJsonProperties()
    {
        $filterController = new FilterController($this->filterService);
        $filterController->setContainer($this->container);

        $this->request->expects($this->once())
            ->method('getContent')
            ->willReturn($this->badJsonFilter);
        $this->expectException(FilterException::class);
        $filterController->updateFilter($this->request, 'events-view', 1);
    }

    /**
     * test updateFilter which succeed
     */
    public function testUpdateFilterSuccess()
    {
        $this->filterService->expects($this->any())
            ->method('findFilterByUserId')
            ->willReturn($this->filterObject);

        $filterController = new FilterController($this->filterService);
        $filterController->setContainer($this->container);

        $this->request->expects($this->once())
            ->method('getContent')
            ->willReturn($this->goodJsonFilter);
        $view = $filterController->updateFilter($this->request, 'events-view', 1);

        $context = (new Context())->setGroups(FilterController::SERIALIZER_GROUPS_MAIN);
        $this->assertEquals(
            $view,
            View::create($this->filterObject)->setContext($context)
        );
    }
}
