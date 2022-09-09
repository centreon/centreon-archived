<?php

/*
 * Copyright 2005 - 2021 Centreon (https://www.centreon.com/)
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

namespace Tests\Security;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Security\TokenAPIAuthenticator;
use Security\Domain\Authentication\Model\LocalProvider;
use Security\Domain\Authentication\Interfaces\AuthenticationRepositoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Centreon\Domain\Contact\Interfaces\ContactRepositoryInterface;

class TokenAPIAuthenticatorTest extends TestCase
{
    /**
     * @var AuthenticationRepositoryInterface|MockObject
     */
    private $authenticationRepository;

    /**
     * @var ContactRepositoryInterface|MockObject
     */
    private $contactRepository;

    /**
     * @var LocalProvider
     */
    private $localProvider;

    public function setUp(): void
    {
        $this->authenticationRepository = $this->createMock(AuthenticationRepositoryInterface::class);
        $this->contactRepository = $this->createMock(ContactRepositoryInterface::class);
        $this->localProvider = $this->createMock(LocalProvider::class);
    }

    public function testStart(): void
    {
        $authenticator = new TokenAPIAuthenticator(
            $this->authenticationRepository,
            $this->contactRepository,
            $this->localProvider
        );

        $request = new Request();

        $this->assertEquals(
            new JsonResponse(
                [
                    'message' => 'Authentication Required'
                ],
                Response::HTTP_UNAUTHORIZED
            ),
            $authenticator->start($request)
        );
    }

    public function testSupports(): void
    {
        $authenticator = new TokenAPIAuthenticator(
            $this->authenticationRepository,
            $this->contactRepository,
            $this->localProvider
        );

        $request = new Request();
        $request->headers->set('X-AUTH-TOKEN', 'my_token');

        $this->assertTrue($authenticator->supports($request));
    }

    public function testNotSupports(): void
    {
        $authenticator = new TokenAPIAuthenticator(
            $this->authenticationRepository,
            $this->contactRepository,
            $this->localProvider
        );

        $request = new Request();

        $this->assertFalse($authenticator->supports($request));
    }

    public function testOnAuthenticationFailure(): void
    {
        $authenticator = new TokenAPIAuthenticator(
            $this->authenticationRepository,
            $this->contactRepository,
            $this->localProvider
        );

        $request = new Request();
        $exception = new AuthenticationException();

        $this->assertEquals(
            new JsonResponse(
                [
                    'message' => 'An authentication exception occurred.'
                ],
                Response::HTTP_UNAUTHORIZED
            ),
            $authenticator->onAuthenticationFailure($request, $exception)
        );
    }

    public function testOnAuthenticationSuccess(): void
    {
        $authenticator = new TokenAPIAuthenticator(
            $this->authenticationRepository,
            $this->contactRepository,
            $this->localProvider
        );

        $request = new Request();
        $token = $this->createMock(TokenInterface::class);

        $this->assertNull(
            $authenticator->onAuthenticationSuccess($request, $token, 'local')
        );
    }

    public function testAuthenticateSuccess(): void
    {
        $authenticator = new TokenAPIAuthenticator(
            $this->authenticationRepository,
            $this->contactRepository,
            $this->localProvider
        );

        $request = new Request();
        $request->headers->set('X-AUTH-TOKEN', 'my_token');

        $this->assertInstanceOf(
            SelfValidatingPassport::class,
            $authenticator->authenticate($request)
        );
    }
}
