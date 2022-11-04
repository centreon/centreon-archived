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

use DateTime;
use DateInterval;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase as BaseTestCase;
use FOS\RestBundle\View\View;
use JMS\Serializer\DeserializationContext;
use JMS\Serializer\Exception\ValidationFailedException;
use JMS\Serializer\SerializerInterface;
use Psr\Container\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Validator\Constraints\GroupSequence;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Centreon\Domain\Check\Check;
use Centreon\Application\Controller\CheckController;
use Centreon\Application\Controller\AbstractController;
use Centreon\Domain\Check\Interfaces\CheckServiceInterface;
use Centreon\Domain\Contact\Contact;
use Centreon\Domain\Entity\EntityValidator;

abstract class TestCase extends BaseTestCase
{
    protected const METHOD_UNDER_TEST = '';
    protected const REQUIRED_ROLE_FOR_ADMIN = Contact::ROLE_HOST_CHECK;
    protected const DEFAULT_REQUEST_CONTENT = 'request content';

    /**
     * @return mixed[]
     */
    abstract protected function getTestMethodArguments(): array;

    protected function assertAdminPrivilegeIsRequired(): void
    {
        $this->expectException(AccessDeniedException::class);
        $this->expectExceptionMessage(AbstractController::ROLE_API_REALTIME_EXCEPTION_MESSAGE);

        $contact = $this->mockContact(isAdmin: false, expectedRole: Contact::ROLE_HOST_CHECK, hasRole: true);
        $container = $this->mockContainer(
            $this->mockAuthorizationChecker(isGranted: false),
            $this->mockTokenStorage(
                $this->mockToken($contact)
            )
        );
        $sut = new CheckController($this->mockService());
        $sut->setContainer($container);

        call_user_func_array([$sut, static::METHOD_UNDER_TEST], $this->getTestMethodArguments());
    }

    protected function assertAdminShouldHaveRole(): void
    {
        $contact = $this->mockContact(isAdmin: false, expectedRole: static::REQUIRED_ROLE_FOR_ADMIN, hasRole: false);
        $container = $this->mockContainer(
            $this->mockAuthorizationChecker(isGranted: true),
            $this->mockTokenStorage($this->mockToken($contact))
        );
        $sut = new CheckController($this->mockService());
        $sut->setContainer($container);

        $view = call_user_func_array([$sut, static::METHOD_UNDER_TEST], $this->getTestMethodArguments());

        $this->assertInstanceOf(View::class, $view);
        $this->assertSame(Response::HTTP_UNAUTHORIZED, $view->getStatusCode());
        $this->assertNull($view->getData());
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

        $view = call_user_func_array(
            [$sut, static::METHOD_UNDER_TEST],
            [
                $this->mockRequest(self::DEFAULT_REQUEST_CONTENT),
                $this->mockEntityValidator(),
                $this->mockSerializer($checks),
            ]
        );

        $this->assertDateIsRecent($check->getCheckTime());
        $this->assertInstanceOf(View::class, $view);
        $this->assertNull($view->getStatusCode());
        $this->assertNull($view->getData());
    }

    /**
     * @param Check[] $checks
     */
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

        $view = call_user_func_array(
            [$sut, static::METHOD_UNDER_TEST],
            [
                $this->mockRequest(static::DEFAULT_REQUEST_CONTENT),
                $validator,
                $this->mockSerializer($checks),
            ]
        );

        $this->assertInstanceOf(View::class, $view);
        $this->assertNull($view->getStatusCode());
        $this->assertNull($view->getData());
    }

    protected function mockContact(bool $isAdmin, string $expectedRole, bool $hasRole): Contact
    {
        $mock = $this->createMock(Contact::class);

        $mock->method('isAdmin')
            ->willReturn($isAdmin);

        $mock->method('hasRole')
            ->with($expectedRole)
            ->willReturn($hasRole);

        return $mock;
    }

    protected function mockContainer(
        AuthorizationCheckerInterface $authorizationChecker,
        TokenStorageInterface $tokenStorage
    ): ContainerInterface {
        $mock = $this->createMock(ContainerInterface::class);

        $mock
            ->method('has')
            ->willReturn(true);

        $mock
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
                        return __DIR__ . '/../../../../../../';
                    }
                }
            );

        return $mock;
    }

    protected function mockRequest(mixed $content = ''): Request
    {
        $mock = $this->createMock(Request::class);

        $mock->method('getContent')
            ->willReturn($content);

        return $mock;
    }

    protected function mockEntityValidator(): EntityValidator|MockObject
    {
        return $this->createMock(EntityValidator::class);
    }

    /**
     * @param Check[]|Check $deserializedObj
     */
    protected function mockSerializer(array|Check $deserializedObj): SerializerInterface
    {
        $mock = $this->createMock(SerializerInterface::class);

        $mock
            ->method('deserialize')
            ->with(
                static::DEFAULT_REQUEST_CONTENT,
                'array<' . Check::class . '>',
                'json',
                $this->isInstanceOf(DeserializationContext::class)
            )
            ->willReturn($deserializedObj);

        return $mock;
    }

    protected function mockService(): CheckServiceInterface|MockObject
    {
        return $this->createMock(CheckServiceInterface::class);
    }

    protected function mockAuthorizationChecker(bool $isGranted = true): AuthorizationCheckerInterface
    {
        $mock = $this->createMock(AuthorizationCheckerInterface::class);

        $mock
            ->method('isGranted')
            ->willReturn($isGranted);

        return $mock;
    }

    protected function mockTokenStorage(TokenInterface|null $token): TokenStorageInterface
    {
        $mock = $this->createMock(TokenStorageInterface::class);

        $mock
            ->method('getToken')
            ->willReturn($token);

        return $mock;
    }

    protected function mockToken(Contact $contact): TokenInterface
    {
        $mock = $this->createMock(TokenInterface::class);

        $mock->expects($this->any())
            ->method('getUser')
            ->willReturn($contact);

        return $mock;
    }

    /**
     * @param string|GroupSequence|(string|GroupSequence)[]|null $groups
     */
    protected function mockCheckValidator(Check $check, $groups = null, int $nbError = 0): EntityValidator
    {
        $constraint = $this->createMock(ConstraintViolationListInterface::class);
        $constraint->method('count')->willReturn($nbError);
        $validator = $this->mockEntityValidator();
        $validator
            ->method('validate')
            ->with($this->equalTo($check), null, $groups)
            ->willReturn($constraint);

        return $validator;
    }

    protected function assertDateIsRecent(DateTime $date): void
    {
        $timeSpan = new DateInterval('PT10S');

        $lowerDate = new DateTime();
        $lowerDate->sub($timeSpan);
        $upperDate = new DateTime();
        $upperDate->add($timeSpan);

        $this->assertTrue($date > $lowerDate && $date < $upperDate);
    }
}
