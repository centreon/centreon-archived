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

declare(strict_types=1);

namespace Tests\Centreon\Application\Controller\CheckController;

use InvalidArgumentException;
use FOS\RestBundle\View\View;
use JMS\Serializer\SerializerInterface;
use Centreon\Domain\Check\Check;
use Centreon\Domain\Check\CheckException;
use Centreon\Domain\Contact\Contact;
use Centreon\Domain\Monitoring\Resource as ResourceEntity;
use Centreon\Application\Controller\CheckController;
use Centreon\Application\Request\CheckRequest;

class ResourcesTest extends TestCase
{
    protected const METHOD_UNDER_TEST = 'checkResources';
    protected const REQUIRED_ROLE_FOR_ADMIN = Contact::ROLE_SERVICE_CHECK;

    private const CORRECT_REQUEST_DATA = [
        'resources' => [
            ['id' => 1,'type' => 'host', 'parent' => null,],
            ['id' => 2, 'type' => 'service', 'parent' => ['id' => 1,],],
        ],
    ];

    /**
     * @test
     */
    public function adminPrivilegeIsRequiredForAction(): void
    {
        $this->assertAdminPrivilegeIsRequired();
    }

    /**
     * @test
     * @dataProvider wrongRequestContentDataProvider
     */
    public function requestContentShouldContainSerializedArray(mixed $requestContent): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Error when decoding sent data');

        $contact = $this->mockContact(isAdmin: true, expectedRole: Contact::ROLE_SERVICE_CHECK, hasRole: true);
        $container = $this->mockContainer(
            $this->mockAuthorizationChecker(isGranted: true),
            $this->mockTokenStorage(
                $this->mockToken($contact)
            )
        );
        $sut = new CheckController($this->mockService());
        $sut->setContainer($container);

        $hostResource = new ResourceEntity();
        $hostResource->setType(ResourceEntity::TYPE_HOST);

        $sut->checkResources($this->mockRequest($requestContent), $this->mockSerializerWithResources([$hostResource]));
    }

    /**
     * @return iterable<string, mixed[]>
     */
    public function wrongRequestContentDataProvider(): iterable
    {
        yield 'null' => [null];
        yield 'empty string' => [''];
        yield 'boolean' => [false];
    }

    /**
     * @test
     * @dataProvider wrongRequestContentDataProviderForValidator
     */
    public function whenValidationFailsErrorsAreShown(
        string $serializedRequestContent,
        string $expectedExceptionMessage
    ): void {
        $this->expectException(CheckException::class);
        $this->expectExceptionMessage($expectedExceptionMessage);

        $contact = $this->mockContact(isAdmin: true, expectedRole: Contact::ROLE_SERVICE_CHECK, hasRole: true);
        $container = $this->mockContainer(
            $this->mockAuthorizationChecker(isGranted: true),
            $this->mockTokenStorage(
                $this->mockToken($contact)
            )
        );

        $sut = new CheckController($this->mockService());
        $sut->setContainer($container);

        $hostResource = new ResourceEntity();
        $hostResource->setType(ResourceEntity::TYPE_HOST);

        $sut->checkResources(
            $this->mockRequest($serializedRequestContent),
            $this->mockSerializerWithResources([$hostResource])
        );
    }

    /**
     * @return iterable<string, string[]>
     */
    public function wrongRequestContentDataProviderForValidator(): iterable
    {
        yield 'additional properties aren\'t allowed' => [
            json_encode(['additional property' => [], 'resources' => []]),
            'The property additional property is not defined'
        ];

        yield 'resource key not defined' => [
            json_encode(['key' => []]),
            '[] The property key is not defined and the definition does not allow additional properties'
        ];
    }

    /**
     * @test
     */
    public function resourceIsCheckedForAdmin(): void
    {
        $contact = $this->mockContact(isAdmin: true, expectedRole: Contact::ROLE_HOST_CHECK, hasRole: true);
        $container = $this->mockContainer(
            $this->mockAuthorizationChecker(isGranted: true),
            $this->mockTokenStorage($this->mockToken($contact))
        );
        $service = $this->mockService();
        $service->method('filterByContact')->with($this->equalTo($contact))->willReturn($service);
        $service->method('checkResource')->with(
            $this->isInstanceOf(Check::class),
            $this->isInstanceOf(ResourceEntity::class)
        );
        $sut = new CheckController($service);
        $sut->setContainer($container);

        $request = $this->mockRequest(json_encode(self::CORRECT_REQUEST_DATA));
        $hostResource = new ResourceEntity();
        $hostResource->setType(ResourceEntity::TYPE_HOST);
        $view = $sut->checkResources($request, $this->mockSerializerWithResources([$hostResource]));

        $this->assertEquals($view, View::create());
    }

    /**
     * @test
     * @dataProvider forbiddenRulesForResourceCheck
     */
    public function resourceIsNotCheckedForUserWithoutRights(string $expectedRole): void
    {
        $contact = new Contact();
        $contact->setAdmin(false);
        $contact->addRole($expectedRole);

        $container = $this->mockContainer(
            $this->mockAuthorizationChecker(isGranted: true),
            $this->mockTokenStorage($this->mockToken($contact))
        );
        $service = $this->mockService();
        $service->expects($this->never())->method('filterByContact');
        $service->expects($this->never())->method('checkResource');
        $sut = new CheckController($service);
        $sut->setContainer($container);

        $request = $this->mockRequest(json_encode(self::CORRECT_REQUEST_DATA));
        $hostResource = new ResourceEntity();
        $hostResource->setType(ResourceEntity::TYPE_HOST);
        $view = $sut->checkResources($request, $this->mockSerializerWithResources([$hostResource]));

        $this->assertEquals($view, View::create());
    }

    /**
     * @return iterable<string, string[]>
     */
    public function forbiddenRulesForResourceCheck(): iterable
    {
        yield 'could not check with acknowledgment role' => [Contact::ROLE_HOST_ACKNOWLEDGEMENT];
        yield 'could not check with disacknowledgment role' => [Contact::ROLE_HOST_DISACKNOWLEDGEMENT];
        yield 'could not check with host write role' => [Contact::ROLE_CONFIGURATION_HOSTS_WRITE];
        yield 'could not check with reporting dashboard role' => [Contact::ROLE_REPORTING_DASHBOARD_HOSTS];
    }

    /**
     * @param ResourceEntity[] $resources
     * @test
     * @dataProvider allowedRulesForResourceCheck
     */
    public function anUserCouldCheckResourceWhenHasRightRole(string $expectedRole, array $resources): void
    {
        $contact = $this->mockContact(isAdmin: false, expectedRole: $expectedRole, hasRole: true);
        $container = $this->mockContainer(
            $this->mockAuthorizationChecker(isGranted: true),
            $this->mockTokenStorage($this->mockToken($contact))
        );
        $service = $this->mockService();
        $service->method('filterByContact')->with($this->equalTo($contact))->willReturn($service);
        $service->method('checkResource')->with(
            $this->isInstanceOf(Check::class),
            $this->isInstanceOf(ResourceEntity::class)
        );
        $sut = new CheckController($service);
        $sut->setContainer($container);

        $request = $this->mockRequest(json_encode(self::CORRECT_REQUEST_DATA));
        $view = $sut->checkResources($request, $this->mockSerializerWithResources($resources));

        $this->assertEquals($view, View::create());
    }

    /**
     * @return iterable<string, mixed[]>
     */
    public function allowedRulesForResourceCheck(): iterable
    {
        $hostResource = new ResourceEntity();
        $hostResource->setType(ResourceEntity::TYPE_HOST);

        $serviceResource = new ResourceEntity();
        $serviceResource->setType(ResourceEntity::TYPE_SERVICE);

        yield 'could check with host role' => [Contact::ROLE_HOST_CHECK, [$hostResource]];
        yield 'could check with service role' => [Contact::ROLE_SERVICE_CHECK, [$serviceResource]];
    }

    /**
     * @param ResourceEntity[] $resources
     */
    protected function mockSerializerWithResources(array $resources): SerializerInterface
    {
        $mock = $this->createMock(SerializerInterface::class);

        $checkRequest = new CheckRequest();
        $checkRequest->setResources($resources);

        $mock
            ->method('deserialize')
            ->with(
                json_encode(self::CORRECT_REQUEST_DATA),
                CheckRequest::class,
                'json'
            )
            ->willReturn($checkRequest);

        return $mock;
    }

    protected function getTestMethodArguments(): array
    {
        $hostResource = new ResourceEntity();
        $hostResource->setType(ResourceEntity::TYPE_HOST);

        return [
            $this->mockRequest(json_encode(self::CORRECT_REQUEST_DATA)),
            $this->mockSerializerWithResources([$hostResource])
        ];
    }
}
