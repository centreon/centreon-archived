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

namespace Tests\Centreon\Domain\Authentication\UseCase;

use Centreon\Domain\Authentication\UseCase\AuthenticateApi;
use Centreon\Domain\Authentication\UseCase\AuthenticateApiRequest;
use Security\Domain\Authentication\Interfaces\AuthenticationServiceInterface;
use Centreon\Domain\Contact\Interfaces\ContactInterface;
use Centreon\Domain\Authentication\Exception\AuthenticationException;
use Security\Domain\Authentication\Exceptions\ProviderServiceException;
use Security\Domain\Authentication\Interfaces\ProviderServiceInterface;
use Security\Domain\Authentication\Interfaces\ProviderInterface;
use Security\Domain\Authentication\Model\ProviderToken;
use PHPUnit\Framework\TestCase;

/**
 * @package Tests\Centreon\Domain\Authentication\UseCase
 */
class AuthenticateApiTest extends TestCase
{
    /**
     * @var AuthenticationServiceInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $authenticationService;

    /**
     * @var ProviderServiceInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $providerService;

    /**
     * @var ProviderInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $provider;

    /**
     * @var ContactInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $contact;

    protected function setUp(): void
    {
        $this->authenticationService = $this->createMock(AuthenticationServiceInterface::class);
        $this->providerService = $this->createMock(ProviderServiceInterface::class);
        $this->provider = $this->createMock(ProviderInterface::class);
        $this->contact = $this->createMock(ContactInterface::class);
    }

    /**
     * test execute when provider configuration is not found
     */
    public function testExecuteProviderConfigurationNotFound(): void
    {
        $this->providerService
            ->expects($this->once())
            ->method('findProviderByConfigurationName')
            ->willReturn(null);

        $authenticate = new AuthenticateApi(
            $this->authenticationService,
            $this->providerService
        );

        $authenticateRequest = new AuthenticateApiRequest(
            [
                'login' => 'admin',
                'password' => 'centreon',
            ]
        );

        $this->expectException(ProviderServiceException::class);
        $this->expectExceptionMessage('Provider configuration (local) not found');

        $authenticate->execute($authenticateRequest);
    }

    /**
     * test execute when provider configuration is not found
     */
    public function testExecuteNotAuthenticated(): void
    {
        $this->providerService
            ->expects($this->once())
            ->method('findProviderByConfigurationName')
            ->willReturn($this->provider);

        $this->provider
            ->expects($this->once())
            ->method('isAuthenticated')
            ->willReturn(false);

        $authenticate = new AuthenticateApi(
            $this->authenticationService,
            $this->providerService
        );

        $authenticateRequest = new AuthenticateApiRequest(
            [
                'login' => 'admin',
                'password' => 'centreon',
            ]
        );

        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessage('Authentication failed');

        $authenticate->execute($authenticateRequest);
    }

    /**
     * test execute when user is not found by provider
     */
    public function testExecuteUserNotFound(): void
    {
        $this->providerService
            ->expects($this->once())
            ->method('findProviderByConfigurationName')
            ->willReturn($this->provider);

        $this->provider
            ->expects($this->once())
            ->method('isAuthenticated')
            ->willReturn(true);

        $this->provider
            ->expects($this->once())
            ->method('getUser')
            ->willReturn(null);

        $authenticate = new AuthenticateApi(
            $this->authenticationService,
            $this->providerService
        );

        $authenticateRequest = new AuthenticateApiRequest(
            [
                'login' => 'admin',
                'password' => 'centreon',
            ]
        );

        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessage('User cannot be retrieved from the provider');

        $authenticate->execute($authenticateRequest);
    }

    /**
     * test execute when user is not found by provider
     */
    public function testExecuteResponse(): void
    {
        $this->providerService
            ->expects($this->once())
            ->method('findProviderByConfigurationName')
            ->willReturn($this->provider);

        $this->provider
            ->expects($this->once())
            ->method('isAuthenticated')
            ->willReturn(true);

        $this->provider
            ->expects($this->once())
            ->method('getUser')
            ->willReturn($this->contact);

        $providerToken = $this->createMock(ProviderToken::class);

        $this->provider
            ->expects($this->once())
            ->method('getProviderToken')
            ->willReturn($providerToken);

        $this->authenticationService
            ->expects($this->once())
            ->method('createAPIAuthenticationTokens')
            ->with($this->matchesRegularExpression('/\w+/'), $this->contact, $providerToken, null);

        $authenticate = new AuthenticateApi(
            $this->authenticationService,
            $this->providerService
        );

        $authenticateRequest = new AuthenticateApiRequest(
            [
                'login' => 'admin',
                'password' => 'centreon',
            ]
        );

        $response = $authenticate->execute($authenticateRequest);

        //$this->assertEquals('//monitoring/resources', $response);
    }
}
