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

final class ServiceTest extends ResourceTestCase
{
    protected const METHOD_UNDER_TEST = 'checkService';
    protected const REQUIRED_ROLE_FOR_ADMIN = Contact::ROLE_SERVICE_CHECK;

    /**
     * @test
     */
    public function exceptionIsThrownWhenValidationFails(): void
    {
        $this->expectException(ValidationFailedException::class);
        $this->expectExceptionMessage('Validation failed with 1 error(s).');

        $check = new Check();
        $validator = $this->mockCheckValidator($check, Check::VALIDATION_GROUPS_SERVICE_CHECK, 1);

        $this->executeMethodUnderTest($check, $validator);
    }

    /**
     * @test
     */
    public function checkHostValidatesChecks(): void
    {
        $check = new Check();
        $validator = $this->mockCheckValidator($check, Check::VALIDATION_GROUPS_SERVICE_CHECK, 0);

        $view = $this->executeMethodUnderTest($check, $validator);

        $this->assertDateIsRecent($check->getCheckTime());
        $this->assertEquals(1, $check->getParentResourceId());
        $this->assertEquals(2, $check->getResourceId());
        $this->assertInstanceOf(View::class, $view);
        $this->assertNull($view->getStatusCode());
        $this->assertNull($view->getData());
    }

    protected function getTestMethodArguments(): array
    {
        return [
            $this->mockRequest(self::DEFAULT_REQUEST_CONTENT),
            $this->mockEntityValidator(),
            $this->mockSerializer([]),
            1,
            2
        ];
    }

    /**
     * @param Check $check
     * @param EntityValidator $validator
     */
    private function executeMethodUnderTest(Check $check, EntityValidator $validator): View
    {
        $contact = $this->mockContact(isAdmin: true, expectedRole: Contact::ROLE_HOST_CHECK, hasRole: true);
        $container = $this->mockContainer(
            $this->mockAuthorizationChecker(isGranted: true),
            $this->mockTokenStorage($this->mockToken($contact))
        );

        $service = $this->mockService();
        $service->method('filterByContact')->with($this->equalTo($contact))->willReturn($service);
        $service->method('checkHost')->with($this->equalTo($check));

        $sut = new CheckController($service);
        $sut->setContainer($container);

        $methodUnderTest = static::METHOD_UNDER_TEST;

        return $sut->$methodUnderTest(
            $this->mockRequest(self::DEFAULT_REQUEST_CONTENT),
            $validator,
            $this->mockSerializer($check),
            1,
            2
        );
    }
}
