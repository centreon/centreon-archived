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

use Centreon\Application\Controller\CheckController;
use Centreon\Domain\Check\Check;
use Centreon\Domain\Contact\Contact;
use Centreon\Domain\Entity\EntityValidator;
use FOS\RestBundle\View\View;
use JMS\Serializer\Exception\ValidationFailedException;

abstract class ResourcesTestCase extends TestCase
{
    /**
     * @test
     */
    public function adminPrivilegeIsRequiredForAction(): void
    {
        $this->assertAdminPrivilegeIsRequired();
    }

    /**
     * @test
     */
    public function adminShouldHaveCheckRule(): void
    {
        $this->assertAdminShouldHaveRole();
    }

    protected function getTestMethodArguments(): array
    {
        return [
            $this->mockRequest(self::DEFAULT_REQUEST_CONTENT),
            $this->mockEntityValidator(),
            $this->mockSerializer([]),
        ];
    }

    protected function assertResourcesLoopsOverDeserializedElements(string $expectedServiceMethodName): void
    {
        $contact = $this->mockContact(isAdmin: true, expectedRole: Contact::ROLE_HOST_CHECK, hasRole: true);
        $container = $this->mockContainer(
            $this->mockAuthorizationChecker(isGranted: true),
            $this->mockTokenStorage($this->mockToken($contact))
        );
        $check = new Check();
        $service = $this->mockService();
        $service->method($expectedServiceMethodName)->with($this->equalTo($check));

        $sut = new CheckController($service);
        $sut->setContainer($container);
        $checks = [$check];

        $methodUnderTest = static::METHOD_UNDER_TEST;
        $view = $sut->$methodUnderTest(
            $this->mockRequest(self::DEFAULT_REQUEST_CONTENT),
            $this->mockEntityValidator(),
            $this->mockSerializer($checks)
        );

        $this->assertDateIsRecent($check->getCheckTime());
        $this->assertInstanceOf(View::class, $view);
        $this->assertNull($view->getStatusCode());
        $this->assertNull($view->getData());
    }

    protected function assertResourceCheckValidatesChecks(EntityValidator $validator, array $checks): void
    {
        $this->expectException(ValidationFailedException::class);
        $this->expectExceptionMessage('Validation failed with 1 error(s).');

        $contact = $this->mockContact(isAdmin: true, expectedRole: Contact::ROLE_HOST_CHECK, hasRole: true);
        $container = $this->mockContainer(
            $this->mockAuthorizationChecker(isGranted: true),
            $this->mockTokenStorage($this->mockToken($contact))
        );

        $sut = new CheckController($this->mockService());
        $sut->setContainer($container);
        $methodUnderTest = static::METHOD_UNDER_TEST;

        $view = $sut->$methodUnderTest(
            $this->mockRequest(static::DEFAULT_REQUEST_CONTENT),
            $validator,
            $this->mockSerializer($checks)
        );

        $this->assertInstanceOf(View::class, $view);
        $this->assertNull($view->getStatusCode());
        $this->assertNull($view->getData());
    }
}
