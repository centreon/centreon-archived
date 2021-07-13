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

use PHPUnit\Framework\TestCase;
use Centreon\Domain\Authentication\UseCase\AuthenticateApi;
use Centreon\Domain\Authentication\UseCase\AuthenticateApiRequest;
use Centreon\Domain\Authentication\UseCase\AuthenticateApiResponse;
use Security\Domain\Authentication\Interfaces\AuthenticationServiceInterface;
use Security\Domain\Authentication\Interfaces\ProviderServiceInterface;
use Security\Domain\Authentication\Interfaces\AuthenticationRepositoryInterface;
use Security\Domain\Authentication\Interfaces\ProviderInterface;
use Security\Domain\Authentication\Model\LocalProvider;
use Security\Domain\Authentication\Model\ProviderConfiguration;
use Security\Domain\Authentication\Model\ProviderToken;
use Centreon\Domain\Contact\Contact;
use Centreon\Domain\Authentication\Exception\AuthenticationException;
use Security\Domain\Authentication\Exceptions\ProviderException;

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
     * @var AuthenticationRepositoryInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $authenticationRepository;

    /**
     * @var ProviderInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $provider;

    /**
     * @var ProviderConfiguration|\PHPUnit\Framework\MockObject\MockObject
     */
    private $providerConfiguration;

    /**
     * @var ProviderToken|\PHPUnit\Framework\MockObject\MockObject
     */
    private $providerToken;

    /**
     * @var Contact|\PHPUnit\Framework\MockObject\MockObject
     */
    private $contact;

    protected function setUp(): void
    {
        $this->authenticationService = $this->createMock(AuthenticationServiceInterface::class);
        $this->providerService = $this->createMock(ProviderServiceInterface::class);
        $this->authenticationRepository = $this->createMock(AuthenticationRepositoryInterface::class);
        $this->provider = $this->createMock(ProviderInterface::class);
        $this->providerConfiguration = $this->createMock(ProviderConfiguration::class);
        $this->providerToken = $this->createMock(ProviderToken::class);
        $this->contact = (new Contact())
            ->setId(1)
            ->setName('contact_name1')
            ->setAlias('contact_alias1')
            ->setEmail('root@localhost')
            ->setAdmin(true);
    }

    /**
     * test execute when local provider is not found
     */
    public function testExecuteLocalProviderNotFound(): void
    {
        $authenticateApi = new AuthenticateApi(
            $this->authenticationService,
            $this->providerService,
            $this->authenticationRepository
        );

        $authenticateApiRequest = new AuthenticateApiRequest('admin', 'centreon');

        $authenticateApiResponse = new AuthenticateApiResponse();

        $this->authenticationService
            ->expects($this->once())
            ->method('deleteExpiredSecurityTokens');

        $this->providerService
            ->expects($this->once())
            ->method('findProviderByConfigurationName')
            ->with(LocalProvider::NAME)
            ->willReturn(null);

        $this->expectException(ProviderException::class);
        $this->expectExceptionMessage('Provider configuration (local) not found');

        $authenticateApi->execute($authenticateApiRequest, $authenticateApiResponse);
    }

    /**
     * test execute when user is not authenticated by provider
     */
    public function testExecuteUserNotAuthenticated(): void
    {
        $authenticateApi = new AuthenticateApi(
            $this->authenticationService,
            $this->providerService,
            $this->authenticationRepository
        );

        $authenticateApiRequest = new AuthenticateApiRequest('admin', 'centreon');

        $authenticateApiResponse = new AuthenticateApiResponse();

        $this->authenticationService
            ->expects($this->once())
            ->method('deleteExpiredSecurityTokens');

        $this->providerService
            ->expects($this->once())
            ->method('findProviderByConfigurationName')
            ->with(LocalProvider::NAME)
            ->willReturn($this->provider);

        $this->provider
            ->expects($this->once())
            ->method('authenticate')
            ->with([
                'login' => 'admin',
                'password' => 'centreon',
            ]);

        $this->provider
            ->expects($this->once())
            ->method('isAuthenticated')
            ->willReturn(false);

        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessage('Invalid Credentials');

        $authenticateApi->execute($authenticateApiRequest, $authenticateApiResponse);
    }

    /**
     * test execute when user is not found
     */
    public function testExecuteUserNotFound(): void
    {
        $authenticateApi = new AuthenticateApi(
            $this->authenticationService,
            $this->providerService,
            $this->authenticationRepository
        );

        $authenticateApiRequest = new AuthenticateApiRequest('admin', 'centreon');

        $authenticateApiResponse = new AuthenticateApiResponse();

        $this->authenticationService
            ->expects($this->once())
            ->method('deleteExpiredSecurityTokens');

        $this->providerService
            ->expects($this->once())
            ->method('findProviderByConfigurationName')
            ->with(LocalProvider::NAME)
            ->willReturn($this->provider);

        $this->provider
            ->expects($this->once())
            ->method('authenticate')
            ->with([
                'login' => 'admin',
                'password' => 'centreon',
            ]);

        $this->provider
            ->expects($this->once())
            ->method('isAuthenticated')
            ->willReturn(true);

        $this->provider
            ->expects($this->once())
            ->method('getUser')
            ->willReturn(null);

        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessage('User cannot be retrieved from the provider');

        $authenticateApi->execute($authenticateApiRequest, $authenticateApiResponse);
    }

    /**
     * test execute when tokens cannot be created
     */
    public function testExecuteCannotCreateTokens(): void
    {
        $authenticateApi = new AuthenticateApi(
            $this->authenticationService,
            $this->providerService,
            $this->authenticationRepository
        );

        $authenticateApiRequest = new AuthenticateApiRequest('admin', 'centreon');

        $authenticateApiResponse = new AuthenticateApiResponse();

        $this->authenticationService
            ->expects($this->once())
            ->method('deleteExpiredSecurityTokens');

        $this->providerService
            ->expects($this->once())
            ->method('findProviderByConfigurationName')
            ->with(LocalProvider::NAME)
            ->willReturn($this->provider);

        $this->provider
            ->expects($this->once())
            ->method('authenticate')
            ->with([
                'login' => 'admin',
                'password' => 'centreon',
            ]);

        $this->provider
            ->expects($this->once())
            ->method('isAuthenticated')
            ->willReturn(true);

        $this->provider
            ->expects($this->once())
            ->method('getUser')
            ->willReturn($this->contact);

        $this->provider
            ->expects($this->once())
            ->method('getConfiguration')
            ->willReturn($this->providerConfiguration);

        $this->provider
            ->expects($this->once())
            ->method('getProviderToken')
            ->willReturn($this->providerToken);

        $this->providerConfiguration
            ->expects($this->once())
            ->method('getId')
            ->willReturn(null);

        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessage('Error while adding authentication token');

        $authenticateApi->execute($authenticateApiRequest, $authenticateApiResponse);
    }

    /**
     * test execute succeed
     */
    public function testExecuteSucceed(): void
    {
        $authenticateApi = new AuthenticateApi(
            $this->authenticationService,
            $this->providerService,
            $this->authenticationRepository
        );

        $authenticateApiRequest = new AuthenticateApiRequest('admin', 'centreon');

        $authenticateApiResponse = new AuthenticateApiResponse();

        $this->authenticationService
            ->expects($this->once())
            ->method('deleteExpiredSecurityTokens');

        $this->providerService
            ->expects($this->once())
            ->method('findProviderByConfigurationName')
            ->with(LocalProvider::NAME)
            ->willReturn($this->provider);

        $this->provider
            ->expects($this->once())
            ->method('authenticate')
            ->with([
                'login' => 'admin',
                'password' => 'centreon',
            ]);

        $this->provider
            ->expects($this->once())
            ->method('isAuthenticated')
            ->willReturn(true);

        $this->provider
            ->expects($this->once())
            ->method('getUser')
            ->willReturn($this->contact);

        $this->provider
            ->expects($this->once())
            ->method('getConfiguration')
            ->willReturn($this->providerConfiguration);

        $this->provider
            ->expects($this->once())
            ->method('getProviderToken')
            ->willReturn($this->providerToken);

        $this->providerConfiguration
            ->expects($this->exactly(2))
            ->method('getId')
            ->willReturn(1);

        $authenticateApi->execute($authenticateApiRequest, $authenticateApiResponse);

        $this->assertEqualsCanonicalizing(
            [
                'id' => 1,
                'name' => 'contact_name1',
                'alias' => 'contact_alias1',
                'email' => 'root@localhost',
                'is_admin' => true,
            ],
            $authenticateApiResponse->getApiAuthentication()['contact']
        );

        $this->assertIsString($authenticateApiResponse->getApiAuthentication()['security']['token']);
    }
}
